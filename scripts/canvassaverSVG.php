<?php

//var_dump($_POST);

// $image = new Imagick();
// $image->setBackgroundColor(new ImagickPixel('transparent'));
// $image->readImageBlob($_POST['theImage']);
// echo $image;

$filename = $_POST['fileName'];
$below = $_POST['belowTexture'];
$texture = $_POST['texture'];
$above = $_POST['aboveTexture'];

$imBelow = new Imagick();
$imBelow->setResolution(144,144);
$imBelow->readImageBlob($below);
$imBelow->setImageFormat("png");
$imBelow->scaleImage(1024,1024);

$imTexture = new Imagick();
$imTexture->setResolution(144,144);
$imTexture->readImageBlob($texture);
$imTexture->setImageFormat("png");
$imTexture->scaleImage(1024,1024);

$imAbove = new Imagick();
$imAbove->setResolution(144,144);
$imAbove->setBackgroundColor(new ImagickPixel('transparent'));
$imAbove->readImageBlob($above);
$imAbove->setImageFormat("png");
$imAbove->scaleImage(1024,1024);

$imOverlay = new Imagick("../images/fhm_watermark.png");

$imFinal = new Imagick();
$imFinal->newImage(1024, 1024, new ImagickPixel('transparent'));
$imFinal->setImageFormat('png');
$imFinal->setImageVirtualPixelMethod(Imagick::VIRTUALPIXELMETHOD_TRANSPARENT);
$imFinal->compositeImage($imBelow,imagick::COMPOSITE_DEFAULT,0,0);
$imFinal->compositeImage($imTexture,imagick::COMPOSITE_MULTIPLY,0,0);
$imFinal->compositeImage($imAbove,imagick::COMPOSITE_DEFAULT,0,0);
$imFinal->compositeImage($imOverlay,imagick::COMPOSITE_DEFAULT,0,0);

header("Content-Type: image/png");
header('Content-Disposition: attachment; filename="'.$filename.'.png"');
echo $imFinal;