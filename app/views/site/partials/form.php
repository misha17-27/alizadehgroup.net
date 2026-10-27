<?php
/** request form (shared by home and contact pages) */
$svcs = services_list();
$evSvc = null;
foreach ($svcs as $s) { if ($s['form_type'] === 'event') { $evSvc = $s; break; } }
$evTypes = $evSvc ? array_values(array_filter(array_map(function ($x) { return jl($x, 't'); }, jlist($evSvc['extra_items'])))) : [];
$pre = (string)($_GET['xidmet'] ?? '');
$req = '<i class="rq-req" aria-hidden="true">*</i>';
$f = function (string $key, string $control, string $id, string $cls = '', bool $required = false) use ($req) {
    return '<div class="rq-f ' . $cls . '"><label for="' . $id . '">' . e(tx($key)) . ($required ? $req : '') . '</label>' . $control . '</div>';
};
$sel = function (string $id, string $name, array $opts, bool $required = false) {
    $o = '';
    foreach ($opts as $v => $t) $o .= '<option value="' . e((string)$v) . '">' . e($t) . '</option>';
    return '<div class="rq-select"><select id="' . $id . '" name="' . $name . '"' . ($required ? ' required' : '') . '>' . $o . '</select>' . icon('chev', 'ico rq-chev') . '</div>';
};
$ts = setting('turnstile_site');
foreach (flashes() as [$ft, $fm]): ?>
  <p class="rq-flash rq-flash-<?= e($ft) ?>" role="status"><?= icon($ft === 'ok' ? 'check' : 'x') ?><?= e($fm) ?></p>
<?php endforeach; ?>
<form class="rq" id="rq-form" method="post" action="<?= e(url((lang() === 'en' ? 'en/' : '') . 'api/request')) ?>" enctype="multipart/form-data" data-rq novalidate
      data-msg-err="<?= e(tx('f.err')) ?>" data-msg-server="<?= e(tx('f.err_server')) ?>" data-msg-sending="<?= e(tx('f.sending')) ?>">
  <?= csrf_field() ?>
  <input type="hidden" name="ts" value="<?= time() ?>">
  <div class="rq-hp" aria-hidden="true"><label>Website <input type="text" name="website" tabindex="-1" autocomplete="off"></label></div>

  <div class="rq-step">
    <div class="rq-step-h"><span class="rq-n">1</span><?= e(tx('f.step1')) ?></div>
    <div class="rq-grid">
      <div class="rq-f rq-full"><label for="rq-service"><?= e(tx('f.service')) ?><?= $req ?></label>
        <div class="rq-select"><select id="rq-service" name="service" required data-rq-service>
          <option value=""><?= e(tx('f.choose')) ?></option>
          <?php foreach ($svcs as $s): ?><option value="<?= e($s['skey']) ?>" data-form="<?= e($s['form_type']) ?>"<?= $pre === $s['skey'] ? ' selected' : '' ?>><?= e(L($s, 'title')) ?></option><?php endforeach; ?>
          <option value="diger" data-form=""<?= $pre === 'diger' ? ' selected' : '' ?>><?= e(tx('f.other')) ?></option>
        </select><?= icon('chev', 'ico rq-chev') ?></div></div>
    </div>
    <p class="rq-hint" data-rq-hint><?= e(tx('f.hint')) ?></p>
  </div>

  <div class="rq-step" data-rq-details hidden>
    <div class="rq-step-h"><span class="rq-n">2</span><?= e(tx('f.step2')) ?></div>

    <fieldset class="rq-set" data-for="transport" hidden disabled>
      <div class="rq-f rq-full"><span class="rq-label"><?= e(tx('f.tr_type')) ?></span>
        <div class="rq-seg" role="radiogroup">
          <label><input type="radio" name="tr_type" value="airport" checked><span><?= e(tx('f.tr_air')) ?></span></label>
          <label><input type="radio" name="tr_type" value="city"><span><?= e(tx('f.tr_city')) ?></span></label>
          <label><input type="radio" name="tr_type" value="intercity"><span><?= e(tx('f.tr_inter')) ?></span></label>
        </div>
      </div>
      <div class="rq-grid">
        <?= $f('f.from', '<input id="rq-from" name="from" type="text" required placeholder="' . e(tx('f.ph_from')) . '">', 'rq-from', '', true) ?>
        <?= $f('f.to', '<input id="rq-to" name="to" type="text" required placeholder="' . e(tx('f.ph_to')) . '">', 'rq-to', '', true) ?>
        <?= $f('f.date', '<input id="rq-date" name="date" type="date" required data-min-today>', 'rq-date', '', true) ?>
        <?= $f('f.time', '<input id="rq-time" name="time" type="time">', 'rq-time') ?>
        <?= $f('f.pax', '<input id="rq-pax" name="pax" type="number" min="1" max="999" value="1" required inputmode="numeric">', 'rq-pax', '', true) ?>
        <?= $f('f.car', $sel('rq-car', 'car', ['any' => tx('f.car_any'), 'sedan' => tx('f.car_sedan'), 'minivan' => tx('f.car_minivan'), 'vip' => tx('f.car_vip')]), 'rq-car') ?>
        <?= $f('f.flight', '<input id="rq-flight" name="flight" type="text" placeholder="' . e(tx('f.ph_flight')) . '">', 'rq-flight') ?>
        <div class="rq-f rq-check"><label class="rq-switch"><input type="checkbox" name="roundtrip" value="1" data-roundtrip><span class="rq-switch-ui" aria-hidden="true"></span><?= e(tx('f.roundtrip')) ?></label></div>
        <div class="rq-f" data-return hidden><label for="rq-return"><?= e(tx('f.return')) ?><?= $req ?></label><input id="rq-return" name="return_date" type="date" required data-min-today disabled></div>
      </div>
    </fieldset>

    <fieldset class="rq-set" data-for="event" hidden disabled>
      <div class="rq-grid">
        <?php if ($evTypes): ?>
          <?= $f('f.ev_type', $sel('rq-ev-type', 'ev_type', array_combine($evTypes, $evTypes), true), 'rq-ev-type', 'rq-full', true) ?>
        <?php else: ?>
          <?= $f('f.ev_type', '<input id="rq-ev-type" name="ev_type" type="text" required>', 'rq-ev-type', 'rq-full', true) ?>
        <?php endif; ?>
        <?= $f('f.ev_date', '<input id="rq-ev-date" name="ev_date" type="date" required data-min-today>', 'rq-ev-date', '', true) ?>
        <?= $f('f.ev_guests', '<input id="rq-ev-guests" name="ev_guests" type="number" min="1" required inputmode="numeric">', 'rq-ev-guests', '', true) ?>
        <?= $f('f.ev_city', '<input id="rq-ev-city" name="ev_city" type="text" placeholder="' . e(tx('f.ph_ev_city')) . '">', 'rq-ev-city', 'rq-full') ?>
      </div>
    </fieldset>

    <fieldset class="rq-set" data-for="hotel" hidden disabled>
      <div class="rq-grid">
        <?= $f('f.city', '<input id="rq-city" name="city" type="text" required placeholder="' . e(tx('f.ph_city')) . '">', 'rq-city', 'rq-full', true) ?>
        <?= $f('f.checkin', '<input id="rq-in" name="checkin" type="date" required data-min-today data-checkin>', 'rq-in', '', true) ?>
        <?= $f('f.checkout', '<input id="rq-out" name="checkout" type="date" required data-min-today data-checkout data-msg="' . e(tx('f.err_checkout')) . '">', 'rq-out', '', true) ?>
        <div class="rq-f rq-nights"><span class="rq-label"><?= e(tx('f.nights')) ?></span><output data-nights data-one="<?= e(tx('f.nights_one')) ?>" data-many="<?= e(tx('f.nights_many')) ?>" aria-live="polite">—</output></div>
        <?= $f('f.guests', '<input id="rq-guests" name="guests" type="number" min="1" value="1" required inputmode="numeric">', 'rq-guests', '', true) ?>
        <?= $f('f.room', $sel('rq-room', 'room', ['standard' => tx('f.room_std'), 'business' => tx('f.room_biz'), 'luxury' => tx('f.room_lux')]), 'rq-room') ?>
        <?= $f('f.budget', '<input id="rq-budget" name="budget" type="text" placeholder="' . e(tx('f.ph_budget')) . '">', 'rq-budget') ?>
      </div>
    </fieldset>

    <fieldset class="rq-set" data-for="supply" hidden disabled>
      <span class="rq-label"><?= e(tx('f.products')) ?></span>
      <div class="rq-products" data-products></div>
      <button type="button" class="rq-add" data-add-product><?= icon('plus') ?><?= e(tx('f.add_prod')) ?></button>
      <template data-product-tpl>
        <div class="rq-product" data-product>
          <div class="rq-product-h"><b><?= e(tx('f.product')) ?> <span data-product-no>1</span></b>
            <button type="button" class="rq-remove" data-remove-product><?= icon('x') ?><?= e(tx('f.remove')) ?></button></div>
          <div class="rq-grid">
            <div class="rq-f"><label data-for-field="name"><?= e(tx('f.prod_name')) ?><?= $req ?></label><input type="text" data-field="name" required placeholder="<?= e(tx('f.ph_prod')) ?>"></div>
            <div class="rq-f"><label data-for-field="qty"><?= e(tx('f.qty')) ?><?= $req ?></label><input type="text" data-field="qty" value="1" required></div>
            <div class="rq-f rq-full"><label class="rq-file" data-for-field="doc"><?= icon('upload') ?><span class="rq-file-txt"><b><?= e(tx('f.doc')) ?></b><small><?= e(tx('f.doc_hint')) ?></small></span><input type="file" data-field="doc" accept=".pdf,.jpg,.jpeg,.png,.webp,.doc,.docx,.xls,.xlsx"></label></div>
          </div>
        </div>
      </template>
    </fieldset>
  </div>

  <div class="rq-step">
    <div class="rq-step-h"><span class="rq-n" data-step-no>2</span><?= e(tx('f.step3')) ?></div>
    <div class="rq-grid">
      <?= $f('f.name', '<input id="rq-name" name="name" type="text" required maxlength="160" autocomplete="name" placeholder="' . e(tx('f.ph_name')) . '">', 'rq-name', '', true) ?>
      <?= $f('f.company', '<input id="rq-company" name="company" type="text" maxlength="160" autocomplete="organization" placeholder="' . e(tx('f.ph_company')) . '">', 'rq-company') ?>
      <?= $f('f.phone', '<input id="rq-phone" name="phone" type="tel" required maxlength="60" autocomplete="tel" placeholder="' . e(tx('f.ph_phone')) . '">', 'rq-phone', '', true) ?>
      <?= $f('f.email', '<input id="rq-email" name="email" type="email" required maxlength="160" autocomplete="email" placeholder="' . e(tx('f.ph_email')) . '">', 'rq-email', '', true) ?>
      <?= $f('f.message', '<textarea id="rq-message" name="message" rows="4" maxlength="5000" placeholder="' . e(tx('f.ph_message')) . '"></textarea>', 'rq-message', 'rq-full') ?>
    </div>
  </div>
  <?php if ($ts): ?>
  <!-- the Turnstile script is loaded by site.js once the form comes near the screen -->
  <div class="rq-captcha" data-ts-src="https://challenges.cloudflare.com/turnstile/v0/api.js"><div class="cf-turnstile" data-sitekey="<?= e($ts) ?>" data-theme="dark" data-language="<?= e(lang()) ?>"></div></div>
  <?php endif; ?>
  <div class="rq-foot">
    <small><?= e(tx('f.req')) ?> · <?= e(tx('f.privacy')) ?></small>
    <button class="btn btn-lime" type="submit" data-rq-submit><span><?= e(tx('f.send')) ?></span><?= icon('arrow') ?></button>
  </div>
  <p class="rq-ok" role="status" hidden><?= icon('check') ?><span><?= e(tx('f.ok')) ?></span></p>
  <p class="rq-err" role="alert" hidden><?= icon('x') ?><span></span></p>
</form>
