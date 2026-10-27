<?php
/**
 * Public request form: validation, storage and e-mail notification.
 */
declare(strict_types=1);

/**
 * Validate and store a public request.
 * @return array{ok:bool, msg:string, id?:int}
 */
function handle_request_submit(): array
{
    $err = function (string $k) { return ['ok' => false, 'msg' => tx($k)]; };

    if (!csrf_ok()) return $err('f.err_server');
    if (str_post('website') !== '') return ['ok' => true, 'msg' => tx('f.ok')];               // honeypot → pretend success
    $ts = (int)str_post('ts');
    if ($ts && time() - $ts < 3) return ['ok' => true, 'msg' => tx('f.ok')];                  // filled too fast → bot
    if (!turnstile_ok($_POST['cf-turnstile-response'] ?? null)) return $err('f.err');

    $ip = client_ip();
    $since = date('Y-m-d H:i:s', time() - 600);
    if ((int)val('SELECT COUNT(*) FROM requests WHERE ip=? AND created_at>?', [$ip, $since]) >= 5) return $err('f.err_rate');

    $key = str_post('service', 40);
    $svc = $key === 'diger' ? null : service_by_key($key);
    if ($key !== 'diger' && !$svc) return $err('f.err');

    $name = str_post('name', 160);
    $phone = str_post('phone', 60);
    $email = str_post('email', 160);
    if ($name === '' || $phone === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) return $err('f.err');

    $details = [];
    $add = function (string $label, $v) use (&$details) {
        $v = is_string($v) ? trim($v) : $v;
        if ($v !== '' && $v !== null) $details[] = [$label, (string)$v];
    };
    $files = [];
    $type = $svc['form_type'] ?? '';
    $date = function (string $k) { $v = str_post($k, 10); return preg_match('~^\d{4}-\d{2}-\d{2}$~', $v) ? $v : ''; };

    if ($type === 'transport') {
        $trMap = ['airport' => 'f.tr_air', 'city' => 'f.tr_city', 'intercity' => 'f.tr_inter'];
        $tr = str_post('tr_type', 20);
        $from = str_post('from', 200);
        $to = str_post('to', 200);
        $d = $date('date');
        $pax = max(0, (int)str_post('pax', 5));
        if ($from === '' || $to === '' || $d === '' || $pax < 1) return $err('f.err');
        $carMap = ['any' => 'f.car_any', 'vip' => 'f.car_vip'];
        $car = str_post('car', 20);
        $add('f.tr_type', isset($trMap[$tr]) ? tx($trMap[$tr], 'az') : '');
        $add('f.from', $from);
        $add('f.to', $to);
        $add('f.date', $d);
        $add('f.time', preg_match('~^\d{2}:\d{2}$~', str_post('time', 5)) ? str_post('time', 5) : '');
        $add('f.pax', $pax);
        $add('f.car', isset($carMap[$car]) ? tx($carMap[$car], 'az') : ucfirst($car));
        $add('f.flight', str_post('flight', 30));
        if (!empty($_POST['roundtrip'])) {
            $rd = $date('return_date');
            if ($rd === '') return $err('f.err');
            $add('f.return', $rd);
        }
    } elseif ($type === 'event') {
        $et = str_post('ev_type', 200);
        $d = $date('ev_date');
        $g = (int)str_post('ev_guests', 7);
        if ($et === '' || $d === '' || $g < 1) return $err('f.err');
        $add('f.ev_type', $et);
        $add('f.ev_date', $d);
        $add('f.ev_guests', $g);
        $add('f.ev_city', str_post('ev_city', 200));
    } elseif ($type === 'hotel') {
        $city = str_post('city', 200);
        $in = $date('checkin');
        $out = $date('checkout');
        $g = (int)str_post('guests', 5);
        if ($city === '' || $in === '' || $out === '' || $g < 1 || strtotime($out) <= strtotime($in)) return $err('f.err');
        $roomMap = ['standard' => 'f.room_std', 'business' => 'f.room_biz', 'luxury' => 'f.room_lux'];
        $room = str_post('room', 20);
        $add('f.city', $city);
        $add('f.checkin', $in);
        $add('f.checkout', $out);
        $add('f.nights', (int)round((strtotime($out) - strtotime($in)) / 86400));
        $add('f.guests', $g);
        $add('f.room', isset($roomMap[$room]) ? tx($roomMap[$room], 'az') : '');
        $add('f.budget', str_post('budget', 120));
    } elseif ($type === 'supply') {
        $prods = $_POST['products'] ?? [];
        if (!is_array($prods)) $prods = [];
        $n = 0;
        try {
            foreach ($prods as $i => $p) {
                if (!is_array($p)) continue;
                $pn = trim(mb_substr((string)($p['name'] ?? ''), 0, 200));
                $qty = trim(mb_substr((string)($p['qty'] ?? ''), 0, 20));
                if ($pn === '') continue;
                $n++;
                $line = $pn . ' — ' . ($qty !== '' ? $qty : '1');
                if (!empty($_FILES['products']['name'][$i]['doc'])) {
                    $f = ['name' => $_FILES['products']['name'][$i]['doc'], 'type' => $_FILES['products']['type'][$i]['doc'],
                        'tmp_name' => $_FILES['products']['tmp_name'][$i]['doc'], 'error' => $_FILES['products']['error'][$i]['doc'],
                        'size' => $_FILES['products']['size'][$i]['doc']];
                    $saved = save_doc_upload($f);
                    if ($saved) { $saved['product'] = $pn; $files[] = $saved; $line .= ' 📎 ' . $saved['name']; }
                }
                $details[] = ['f.product', $n . '. ' . $line];
                if ($n >= 30) break;
            }
        } catch (InvalidArgumentException $e) {
            return $err('f.err_file');
        } catch (Throwable $e) {
            log_line('app.log', 'upload: ' . $e->getMessage());
            return $err('f.err_server');
        }
        if ($n === 0) return $err('f.err');
    }

    try {
        $id = insert('requests', [
            'service_key' => $svc['skey'] ?? 'diger',
            'service_title' => $svc ? $svc['title_az'] : tx('f.other', 'az'),
            'name' => $name, 'company' => str_post('company', 160), 'phone' => $phone, 'email' => $email,
            'message' => str_post('message', 5000),
            'details' => json_encode($details, JSON_UNESCAPED_UNICODE),
            'files' => json_encode($files, JSON_UNESCAPED_UNICODE),
            'lang' => lang(), 'ip' => $ip, 'ua' => mb_substr((string)($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 250),
            'status' => 'new', 'note' => '', 'created_at' => now(), 'updated_at' => now(),
        ]);
    } catch (Throwable $e) {
        log_line('app.log', 'request save: ' . $e->getMessage());
        return $err('f.err_server');
    }

    send_request_mail($id);
    return ['ok' => true, 'msg' => tx('f.ok'), 'id' => $id];
}

/** e-mail notification about a new request (PHP mail(); failures are only logged) */
function send_request_mail(int $id): void
{
    $to = setting('notify_email');
    $r = row('SELECT * FROM requests WHERE id=?', [$id]);
    if ($to === '' || !$r) return;
    $host = preg_replace('~^www\.~', '', strtok((string)($_SERVER['HTTP_HOST'] ?? 'alizadehgroup.net'), ':'));
    $lines = [
        'Yeni sorğu #' . $id . ' — ' . $r['service_title'],
        str_repeat('—', 32),
        'Ad soyad: ' . $r['name'],
        'Şirkət: ' . ($r['company'] ?: '—'),
        'Telefon: ' . $r['phone'],
        'E-poçt: ' . $r['email'],
        '',
    ];
    foreach (jlist($r['details']) as $d) $lines[] = tx($d[0], 'az') . ': ' . $d[1];
    $files = jlist($r['files']);
    if ($files) $lines[] = 'Fayllar: ' . count($files) . ' (admin paneldə)';
    if ($r['message'] !== '') { $lines[] = ''; $lines[] = 'Mesaj:'; $lines[] = $r['message']; }
    $lines[] = '';
    $lines[] = 'Admin: ' . site_origin() . url('admin/index.php?section=requests&id=' . $id);

    $subject = '=?UTF-8?B?' . base64_encode('Yeni sorğu #' . $id . ' — ' . $r['service_title']) . '?=';
    $headers = [
        'From: Alizadeh Group <noreply@' . $host . '>',
        'Reply-To: ' . str_replace(["\r", "\n"], '', $r['email']),
        'MIME-Version: 1.0',
        'Content-Type: text/plain; charset=UTF-8',
        'Content-Transfer-Encoding: 8bit',
    ];
    if (!@mail($to, $subject, implode("\n", $lines), implode("\r\n", $headers))) {
        log_line('mail.log', 'mail() failed for request #' . $id);
    }
}
