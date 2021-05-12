<?php

require_once(__DIR__ . "/src/Osiris/PHPInfo.php");

\Pantheon\Osiris\PHPInfo::getCacheControl();

$fg = userSelectedImage('fg', __DIR__ . '/images/pantheon.png');
$bg = userSelectedImage('bg', __DIR__ . '/images/sunrise.png');

$composite_image = compositeImage($fg, $bg);

header("Content-Type: image/jpg");
echo $composite_image;

function compositeImage($src_fg, $src_bg)
{
    try {
        $img1 = new \Imagick();
        $img1->readImage($src_bg);

        $img2 = new \Imagick();
        $img2->readImage($src_fg);

        $img1->resizeimage(
            $img2->getImageWidth(),
            $img2->getImageHeight(),
            \Imagick::FILTER_LANCZOS,
            1
        );

        $opacity = new \Imagick();
        $opacity->newPseudoImage(
            $img1->getImageHeight(),
            $img1->getImageWidth(),
            "gradient:gray(10%)-gray(90%)"
        );
        $opacity->rotateimage('black', 90);

        $img2->compositeImage($opacity, \Imagick::COMPOSITE_COPYOPACITY, 0, 0);
        $img1->compositeImage($img2, \Imagick::COMPOSITE_ATOP, 0, 0);
        return $img1->getImageBlob();

    } catch (\Exception $e) {
        echo print($e->getMessage());
    } catch (\Throwable $t) {
        echo print($t->getMessage());
    }
    return null;

}

function userSelectedImage($key, $default)
{
    if (!isset($_GET[$key])) {
        return $default;
    }
    $user_selected = __DIR__ . '/images/' . preg_replace('#[^a-z0-9_.-]#', '', $_GET[$key]);

    if (!file_exists($user_selected)) {
        return $default;
    }

    return $user_selected;
}
