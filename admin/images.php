<?php
// Загрузка картинок из админки: файл пересохраняется (метаданные и всё лишнее отбрасываются)
// и нарезается на ширины из content.php в AVIF, WebP и запасной JPEG (или PNG, если нужна прозрачность).
// Результат — public/uploads/<блок>-<поле>-<случайный хвост>-<ширина>.<формат>: у новой картинки новый адрес,
// поэтому браузеры не покажут старую из кэша.

const UPLOADS_URL = '/uploads';
const UPLOAD_MAX_PIXELS = 50_000_000;

/** Загруженный файл для поля image: null — файла нет или он с ошибкой (тогда ошибка в $errors). */
function upload_image(array $f, string $block, string $key, array &$errors): ?array
{
    $name = "content[$block][$key]";
    $error = $_FILES['upload']['error'][$block][$key] ?? UPLOAD_ERR_NO_FILE;
    if ($error === UPLOAD_ERR_NO_FILE) {
        return null;
    }
    if ($error === UPLOAD_ERR_INI_SIZE || $error === UPLOAD_ERR_FORM_SIZE) {
        $errors[$name] = 'Файл слишком большой, максимум — ' . ini_get('upload_max_filesize') . 'Б.';
        return null;
    }
    if ($error !== UPLOAD_ERR_OK || !is_uploaded_file($_FILES['upload']['tmp_name'][$block][$key])) {
        $errors[$name] = 'Файл не загрузился, попробуйте ещё раз.';
        return null;
    }
    try {
        return make_image($_FILES['upload']['tmp_name'][$block][$key], $f, "$block-$key");
    } catch (Throwable $e) {
        error_log('Админка, картинка ' . $name . ': ' . $e->getMessage());
        $errors[$name] = 'Не получилось обработать картинку. Загрузите JPG, PNG, WebP или AVIF.';
        return null;
    }
}

function make_image(string $path, array $f, string $slug): array
{
    $probe = new Imagick();
    $probe->pingImage($path);
    if (!in_array($probe->getImageFormat(), ['JPEG', 'PNG', 'WEBP', 'AVIF', 'HEIC'], true)) {
        throw new RuntimeException('формат ' . $probe->getImageFormat());
    }
    if ($probe->getImageWidth() * $probe->getImageHeight() > UPLOAD_MAX_PIXELS) {
        throw new RuntimeException('слишком много пикселей');
    }

    $im = new Imagick($path);
    $im->setIteratorIndex(0);
    auto_orient($im);
    $im->transformImageColorspace(Imagick::COLORSPACE_SRGB);
    $im->stripImage();
    $alpha = !empty($f['alpha']);
    if (!$alpha) {
        // прозрачные места — белым, иначе в JPEG они станут чёрными
        $im->setImageBackgroundColor('white');
        $im = $im->mergeImageLayers(Imagick::LAYERMETHOD_FLATTEN);
    }

    $dir = dirname(__DIR__) . '/public' . UPLOADS_URL;
    if (!is_dir($dir)) {
        mkdir($dir, 0775, true);
    }
    $src = UPLOADS_URL . '/' . $slug . '-' . bin2hex(random_bytes(4));
    $ext = $alpha ? 'png' : 'jpg';

    $desktop = isset($f['ratio']) ? crop_to_ratio($im, $f['ratio']) : clone $im;
    $value = ['src' => $src, 'w' => image_widths($f['widths'], $desktop), 'ext' => $ext];
    $value['width'] = end($value['w']);
    $value['height'] = (int) round($desktop->getImageHeight() * $value['width'] / $desktop->getImageWidth());
    save_variants($desktop, $value['w'], $src, $ext);

    if (isset($f['mobile'])) {
        $mobile = crop_to_ratio($im, $f['mobile']['ratio']);
        $value['mw'] = image_widths($f['mobile']['widths'], $mobile);
        save_variants($mobile, $value['mw'], "$src-mobile", $ext);
    }
    return $value;
}

/** Ширины нарезки не больше самой картинки — маленькую не растягиваем; совсем маленькая остаётся в своём размере. */
function image_widths(array $widths, Imagick $im): array
{
    $max = $im->getImageWidth();
    $fit = array_values(array_filter($widths, fn($w) => $w <= $max));
    return $fit ?: [$max];
}

function crop_to_ratio(Imagick $im, float $ratio): Imagick
{
    $w = $im->getImageWidth();
    $h = $im->getImageHeight();
    $out = clone $im;
    if ($w / $h > $ratio) {
        $cw = (int) round($h * $ratio);
        $out->cropImage($cw, $h, intdiv($w - $cw, 2), 0);
    } else {
        $ch = (int) round($w / $ratio);
        $out->cropImage($w, $ch, 0, intdiv($h - $ch, 2));
    }
    $out->setImagePage(0, 0, 0, 0);
    return $out;
}

function save_variants(Imagick $im, array $widths, string $src, string $ext): void
{
    $dir = dirname(__DIR__) . '/public';
    foreach ($widths as $w) {
        $img = clone $im;
        if ($w < $img->getImageWidth()) {
            $img->resizeImage($w, 0, Imagick::FILTER_LANCZOS, 1);
        }
        foreach (['avif' => 55, 'webp' => 78, $ext => 80] as $format => $quality) {
            $out = clone $img;
            $out->setImageFormat($format === 'jpg' ? 'jpeg' : $format);
            if ($format === 'png') {
                // запасной PNG нужен только очень старым браузерам — 256 цветов, чтобы не весил мегабайты
                $out->quantizeImage(256, Imagick::COLORSPACE_SRGB, 0, true, false);
                $out->setOption('png:compression-level', '9');
            } else {
                $out->setImageCompressionQuality($quality);
            }
            if ($format === 'jpg') {
                $out->setInterlaceScheme(Imagick::INTERLACE_PLANE);
            }
            $out->writeImage("$dir$src-$w.$format");
        }
    }
}

/** Поворот по EXIF: фото с телефона иначе может лечь на бок. */
function auto_orient(Imagick $im): void
{
    switch ($im->getImageOrientation()) {
        case Imagick::ORIENTATION_TOPRIGHT: $im->flopImage(); break;
        case Imagick::ORIENTATION_BOTTOMRIGHT: $im->rotateImage('none', 180); break;
        case Imagick::ORIENTATION_BOTTOMLEFT: $im->flipImage(); break;
        case Imagick::ORIENTATION_LEFTTOP: $im->transposeImage(); break;
        case Imagick::ORIENTATION_RIGHTTOP: $im->rotateImage('none', 90); break;
        case Imagick::ORIENTATION_RIGHTBOTTOM: $im->transverseImage(); break;
        case Imagick::ORIENTATION_LEFTBOTTOM: $im->rotateImage('none', -90); break;
    }
    $im->setImageOrientation(Imagick::ORIENTATION_TOPLEFT);
}

/** Удалить файлы картинки, загруженной через админку (картинки из assets/img не трогаем). */
function delete_image(array $img): void
{
    if (!preg_match('~^' . UPLOADS_URL . '/[a-z0-9_-]+-[0-9a-f]{8}$~', $img['src'] ?? '')) {
        return;
    }
    foreach (glob(dirname(__DIR__) . '/public' . $img['src'] . '-*') as $file) {
        unlink($file);
    }
}
