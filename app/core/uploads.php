<?php
/**
 * File uploads: admin images (uploads/) and request attachments (storage/requests/).
 */
declare(strict_types=1);

const DOC_EXT = ['pdf', 'jpg', 'jpeg', 'png', 'webp', 'doc', 'docx', 'xls', 'xlsx'];
const DOC_MAX = 10 * 1024 * 1024;
const IMG_EXT = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
const IMG_MAX = 8 * 1024 * 1024;

/** normalised list of uploaded files for a field (supports name="x" and name="x[]") */
function uploaded_files(string $field): array
{
    if (empty($_FILES[$field])) return [];
    $f = $_FILES[$field];
    if (!is_array($f['name'])) return [$f];
    $out = [];
    foreach ($f['name'] as $i => $n) {
        $out[] = ['name' => $n, 'type' => $f['type'][$i], 'tmp_name' => $f['tmp_name'][$i], 'error' => $f['error'][$i], 'size' => $f['size'][$i]];
    }
    return $out;
}

function file_ext(string $name): string
{
    return strtolower(pathinfo($name, PATHINFO_EXTENSION));
}

/** save an admin image upload into uploads/YYYY/MM; returns the relative path or null */
function save_image_upload(array $file, int $maxSide = 2400): ?string
{
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) return null;
    $ext = file_ext((string)$file['name']);
    if (!in_array($ext, IMG_EXT, true) || $file['size'] > IMG_MAX) return null;
    $info = @getimagesize($file['tmp_name']);
    if (!$info || !in_array($info[2], [IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_WEBP, IMAGETYPE_GIF], true)) return null;
    $ext = [IMAGETYPE_JPEG => 'jpg', IMAGETYPE_PNG => 'png', IMAGETYPE_WEBP => 'webp', IMAGETYPE_GIF => 'gif'][$info[2]];
    $rel = 'uploads/' . date('Y/m');
    $dir = APP_ROOT . '/' . $rel;
    if (!is_dir($dir) && !@mkdir($dir, 0775, true)) return null;
    $name = date('His') . '-' . bin2hex(random_bytes(4)) . '.' . $ext;
    $dest = $dir . '/' . $name;
    if (!move_uploaded_file($file['tmp_name'], $dest)) return null;
    resize_image($dest, $info, $maxSide);
    return $rel . '/' . $name;
}

/** downscale very large images (GD); silently keeps the original if GD is unavailable */
function resize_image(string $path, array $info, int $maxSide): void
{
    [$w, $h, $type] = $info;
    if (!function_exists('imagecreatetruecolor') || max($w, $h) <= $maxSide || $type === IMAGETYPE_GIF) return;
    $r = $maxSide / max($w, $h);
    $nw = (int)round($w * $r);
    $nh = (int)round($h * $r);
    switch ($type) {
        case IMAGETYPE_JPEG: $src = @imagecreatefromjpeg($path); break;
        case IMAGETYPE_PNG: $src = @imagecreatefrompng($path); break;
        case IMAGETYPE_WEBP: $src = function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($path) : false; break;
        default: $src = false;
    }
    if (!$src) return;
    $dst = imagecreatetruecolor($nw, $nh);
    if ($type !== IMAGETYPE_JPEG) { imagealphablending($dst, false); imagesavealpha($dst, true); }
    imagecopyresampled($dst, $src, 0, 0, 0, 0, $nw, $nh, $w, $h);
    if ($type === IMAGETYPE_JPEG) imagejpeg($dst, $path, 84);
    elseif ($type === IMAGETYPE_PNG) imagepng($dst, $path, 7);
    elseif ($type === IMAGETYPE_WEBP) imagewebp($dst, $path, 84);
    // the GD images are freed when the function returns (imagedestroy() is deprecated in PHP 8.5)
}

/** save a request attachment into storage/requests (not web-accessible) */
function save_doc_upload(array $file): ?array
{
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) return null;
    if ($file['error'] !== UPLOAD_ERR_OK || $file['size'] > DOC_MAX || $file['size'] <= 0) throw new InvalidArgumentException('file');
    $ext = file_ext((string)$file['name']);
    if (!in_array($ext, DOC_EXT, true)) throw new InvalidArgumentException('file');
    $rel = 'requests/' . date('Y/m');
    $dir = STORAGE_DIR . '/' . $rel;
    if (!is_dir($dir) && !@mkdir($dir, 0775, true)) throw new RuntimeException('storage');
    $stored = date('dHis') . '-' . bin2hex(random_bytes(5)) . '.' . $ext;
    if (!move_uploaded_file($file['tmp_name'], $dir . '/' . $stored)) throw new RuntimeException('move');
    $orig = mb_substr(preg_replace('~[^\pL\pN ._()-]+~u', '_', basename((string)$file['name'])) ?: ('file.' . $ext), 0, 120);
    return ['path' => $rel . '/' . $stored, 'name' => $orig, 'size' => (int)$file['size']];
}
