<?php
	
header('Content-type: image/png');

$action = $_POST['action'];
$filename = $_POST['filename'];
$encoded = $_POST['imgdata'];
$x = $_POST['x'];
$y = $_POST['y'];
$color = $_POST['color'];
$fuzz = $_POST['fuzz'];
$newcolor = (isset($_POST['newcolor'])) ? $_POST['newcolor'] : "#000000";
$encoded = str_replace(' ', '+', $encoded);
$decoded = base64_decode($encoded);

echo $action($decoded,$x,$y,$color,$newcolor,$fuzz,$filename);

function removeColorRange($image,$x,$y,$color,$newcolor,$threshold,$filename) {
	file_put_contents('../temp/'.$filename, base64_decode($_POST['imgdata']));
	$cmd = "/usr/bin/convert \"../temp/".$filename."\" -alpha set -channel alpha -fuzz ".$threshold."% -fill none -floodfill +".$x."+".$y." \"".$color."\" ../temp/outfile.png";
	exec($cmd, $retval);
	$im = new Imagick();
	$im->readImage('../temp/outfile.png');
	unlink('../temp/'.$filename);
	unlink('../temp/outfile.png');
	return base64_encode($im);
}

function removeBorder($image,$x,$y,$color,$newcolor,$threshold,$filename) {
	$im = new Imagick();
	$im->readimageblob($image);
	$target = $im->getImagePixelColor(1,1);
	$im->setBackgroundColor(new ImagickPixel('transparent'));
	$im->borderImage($target,10,10);
	$im->setImageVirtualPixelMethod( imagick::VIRTUALPIXELMETHOD_BACKGROUND );
	$im->writeImage('../temp/'.$filename);
	$color = iMagickColorToHEX($target->getColorAsString());
	$cmd = "/usr/bin/convert \"../temp/".$filename."\" -alpha set -channel alpha -fuzz ".$threshold."% -fill none -floodfill +1+1 \"".$color."\" -trim ../temp/outfile.png";
	exec($cmd, $retval);
	$im->clear();
	$im = new Imagick('../temp/outfile.png');
	unlink('../temp/'.$filename);
	unlink('../temp/outfile.png');
	return base64_encode($im);
}

function removeAllColor($image,$x,$y,$color,$newcolor,$threshold,$filename) {
	$im = new Imagick();
	$im->setBackgroundColor(new ImagickPixel('transparent')); 
	$im->readimageblob($image);
	$target = $im->getImagePixelColor(10,10);
	$im->setImageVirtualPixelMethod( imagick::VIRTUALPIXELMETHOD_BACKGROUND );
	$im->trimImage(0);
	$im->setImagePage(0, 0, 0, 0);
	$quantum = $im->getQuantumDepth()['quantumDepthLong'];
	$fuzz = $threshold * pow(2,$quantum);
	$im->paintTransparentImage($color, 0.0, $fuzz);
	$im->trimImage(0);
	$im->setImagePage(0, 0, 0, 0);
	return base64_encode($im);		
}

function replaceColorRange($image,$x,$y,$color,$newcolor,$threshold,$filename) {
	$im = new Imagick();
	$im->setBackgroundColor(new ImagickPixel('transparent')); 
	$im->readimageblob($image);
	$im->setImageVirtualPixelMethod( imagick::VIRTUALPIXELMETHOD_BACKGROUND );
	$im->trimImage(0);
	$im->setImagePage(0, 0, 0, 0);
	$target = $im->getImagePixelColor($x, $y);
	$quantum = $im->getQuantumDepth()['quantumDepthLong'];
	$fuzz = $threshold * pow(2,$quantum);
	$im->floodfillPaintImage($newcolor, $fuzz, $target, $x, $y, false);
	$im->trimImage(0);
	$im->setImagePage(0, 0, 0, 0);
	return base64_encode($im);		
}

function replaceAllColor($image,$x,$y,$color,$newcolor,$threshold,$filename) {
	$im = new Imagick();
	$im->setBackgroundColor(new ImagickPixel('transparent')); 
	$im->readimageblob($image);
	$target = $im->getImagePixelColor(10,10);
	$im->setImageVirtualPixelMethod( imagick::VIRTUALPIXELMETHOD_BACKGROUND );
	$im->trimImage(0);
	$im->setImagePage(0, 0, 0, 0);
	$quantum = $im->getQuantumDepth()['quantumDepthLong'];
	$fuzz = $threshold * pow(2,$quantum);
	$im->paintOpaqueImage($color, $newcolor, $fuzz);
	$im->trimImage(0);
	$im->setImagePage(0, 0, 0, 0);
	return base64_encode($im);		
}

function iMagickColorToHEX($string)
{
    $pixel = new ImagickPixel($string);
    $color = $pixel->getColor();
	
    return sprintf('#%s%s%s', 
	dechex($color['r']), 
	dechex($color['g']),
	dechex($color['b'])
    );
}