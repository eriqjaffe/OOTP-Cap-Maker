<?php
header('Content-type: image/png');
header('Content-Disposition: attachment; filename="test.png"');

$deformation = $_POST['deform'];
$amount = $_POST['amount'];
$encoded = $_POST['imgdata'];
$encoded = str_replace(' ', '+', $encoded);
$decoded = base64_decode($encoded);

echo $deformation($decoded,40);

function sandbox($image,$amount) {
	$im = new Imagick();
	$im->setBackgroundColor(new ImagickPixel('transparent'));
	$im->readimageblob($image);
	$im->setImageVirtualPixelMethod( imagick::VIRTUALPIXELMETHOD_BACKGROUND );
	$im->trimImage(0);
	$im->setImagePage(0, 0, 0, 0);
	$x1 = $im->getImageWidth();
	$x2 = $im->getImageWidth();
	$y1 = $im->getImageHeight();
	$y2 = $im->getImageHeight()/((100-$amount)*0.01);
	$im->setGravity(imagick::GRAVITY_NORTH);
	$im->extentImage ($x2,$y2,0,0);
	$points = array(
		0, 0, 0, 0, #top left
		0, $y1, 0, $y1, #bottom left
		$x1, 0, $x1, 0, #top right
		$x1, $y1, $x1, $y2 #bottom right
	);
	$im->distortImage( Imagick::DISTORTION_BILINEAR, $points, TRUE );
	$im->trimImage(0);
	$im->setImagePage(0, 0, 0, 0);
	return($im);
}

function none($image,$null) {
	$im = new Imagick();
	$im->setBackgroundColor(new ImagickPixel('transparent'));
	$im->readimageblob($image);
	$im->setImageVirtualPixelMethod( imagick::VIRTUALPIXELMETHOD_BACKGROUND );
	$im->trimImage(0);
	$im->setImagePage(0, 0, 0, 0);
	return($im);
}

function arch($image,$wavelengh) {
	$im = new Imagick();
	$im->setBackgroundColor(new ImagickPixel('transparent')); 
	$im->readimageblob($image);
	$im->setImageVirtualPixelMethod( imagick::VIRTUALPIXELMETHOD_BACKGROUND );
	$im->trimImage(0);
	$im->setImagePage(0, 0, 0, 0);
	$w = $im->getImageWidth();
	$im->waveImage($wavelengh*-1,$w*2);
	$im->trimImage(0);
	$im->setImagePage(0, 0, 0, 0);
	return($im);
}

function arc($image,$degrees) {
	$x = array($degrees);
	$im = new Imagick();
	$im->setBackgroundColor(new ImagickPixel('transparent'));
	$im->readimageblob($image);
	$im->setImageVirtualPixelMethod( imagick::VIRTUALPIXELMETHOD_BACKGROUND );
	$im->trimImage(0);
	$im->setImagePage(0, 0, 0, 0);
	$im->distortImage( Imagick::DISTORTION_ARC, $x, TRUE ); 
	$im->trimImage(0);
	$im->setImagePage(0, 0, 0, 0);
	return($im);
}

function bilinearUp($image,$amount) {
	$im = new Imagick();
	$im->setBackgroundColor(new ImagickPixel('transparent'));
	$im->readimageblob($image);
	$im->setImageVirtualPixelMethod( imagick::VIRTUALPIXELMETHOD_BACKGROUND );
	$im->trimImage(0);
	$im->setImagePage(0, 0, 0, 0);
	$x1 = $im->getImageWidth();
	$x2 = $im->getImageWidth();
	$y1 = $im->getImageHeight();
	$y2 = $im->getImageHeight()/((100-$amount)*0.01);
	$im->setGravity(imagick::GRAVITY_NORTH);
	$im->extentImage ($x2,$y2,0,0);
	$points = array(
		0, 0, 0, 0, #top left
		0, $y1, 0, $y2, #bottom left
		$x1, 0, $x1, 0, #top right
		$x1, $y1, $x1, $y1 #bottom right
	);
	$im->distortImage( Imagick::DISTORTION_BILINEAR, $points, TRUE );
	$im->trimImage(0);
	$im->setImagePage(0, 0, 0, 0);
	return($im);	
}

function bilinearDown($image,$amount) {
	$im = new Imagick();
	$im->setBackgroundColor(new ImagickPixel('transparent'));
	$im->readimageblob($image);
	$im->setImageVirtualPixelMethod( imagick::VIRTUALPIXELMETHOD_BACKGROUND );
	$im->trimImage(0);
	$im->setImagePage(0, 0, 0, 0);
	$x1 = $im->getImageWidth();
	$x2 = $im->getImageWidth();
	$y1 = $im->getImageHeight();
	$y2 = $im->getImageHeight()/((100-$amount)*0.01);
	$im->setGravity(imagick::GRAVITY_NORTH);
	$im->extentImage ($x2,$y2,0,0);
	$points = array(
		0, 0, 0, 0, #top left
		0, $y1, 0, $y1, #bottom left
		$x1, 0, $x1, 0, #top right
		$x1, $y1, $x1, $y2 #bottom right
	);
	$im->distortImage( Imagick::DISTORTION_BILINEAR, $points, TRUE );
	$im->trimImage(0);
	$im->setImagePage(0, 0, 0, 0);
	return($im);
}

function test($image,$amount) {
	$im = new Imagick();
	$im->setBackgroundColor(new ImagickPixel('transparent'));
	$im->readimageblob($image);
	$im->setImageVirtualPixelMethod( imagick::VIRTUALPIXELMETHOD_BACKGROUND );
	$im->trimImage(0);
	$im->setImagePage(0, 0, 0, 0);
	$im->setGravity(imagick::GRAVITY_CENTER);
	$im->extentImage (512,512,0,0);
	$points = array(0.0, 0.0, 0.0, 1.0,   0.0, 0.0, -0.1, 1.1);
	$im->distortImage( Imagick::DISTORTION_BARREL, $points, TRUE );
	$im->trimImage(0);
	// $im->setImagePage(0, 0, 0, 0);
	return($im->getImageHeight().' '.$im->getImageWidth());
}

function pinch($image) {
	$im = new Imagick();
	$im->setBackgroundColor(new ImagickPixel('transparent'));
	$im->readimageblob($image);
	$im->setImageVirtualPixelMethod( imagick::VIRTUALPIXELMETHOD_BACKGROUND );
	$im->trimImage(0);
	$im->setImagePage(0, 0, 0, 0);
	// $im->setGravity(imagick::GRAVITY_CENTER);
	// $im->extentImage (512,512,0,0);
	$points = array(0,0, 0.5, 0.0, 5.0);
	$im->distortImage( Imagick::DISTORTION_BARREL, $points, TRUE );
	$im->trimImage(0);
	$im = new Imagick();
	$im->setBackgroundColor(new ImagickPixel('transparent'));
	$im->readimageblob($image);
	$im->trimImage(0);
	// $im->setGravity(imagick::GRAVITY_NORTH);
	$im->extentImage ($im->getImageWidth(),($im->getImageHeight()*2),0,0);
	$im->setImagePage(0, 0, 0, 0);
	$distort = array( 0.0, -.05, .05, 1  );
	$im->setImageVirtualPixelMethod( Imagick::VIRTUALPIXELMETHOD_TRANSPARENT );
	$im->setImageMatte( TRUE );
	$im->distortImage( Imagick::DISTORTION_BARREL, $distort, TRUE );  
	return($im);
}

function archUp($image,$amount) {
	$im = new Imagick();
	$im->setBackgroundColor(new ImagickPixel('transparent'));
	$im->readimageblob($image);
	$im->setImageVirtualPixelMethod( imagick::VIRTUALPIXELMETHOD_BACKGROUND );
	$im->trimImage(0);
	$imOut = new Imagick();
	$imOut->setBackgroundColor(new ImagickPixel('transparent')); 
	$imOut->newImage($im->getImageWidth()*2, $im->getImageHeight(), new ImagickPixel('transparent'));
	$imOut->setImageVirtualPixelMethod( Imagick::VIRTUALPIXELMETHOD_TRANSPARENT );	
	$imOut->setImageFormat('png');
	$imOut->compositeimage($im, Imagick::COMPOSITE_DEFAULT, 0, 0);
	$imOut->compositeimage($im, Imagick::COMPOSITE_DEFAULT, $im->getImageWidth()+1, 0);
	$imOut->waveImage($amount*-2,$imOut->getImageWidth()*2);
	$imOut->cropImage($imOut->getImageWidth()/2,$imOut->getImageHeight(),0,0);
	$imOut->trimImage(0);
	$imOut->setImagePage(0, 0, 0, 0);
	return(base64_encode($imOut));
}

function archDown($image,$amount) {
	$im = new Imagick();
	$im->setBackgroundColor(new ImagickPixel('transparent'));
	$im->readimageblob($image);
	$im->setImageVirtualPixelMethod( imagick::VIRTUALPIXELMETHOD_BACKGROUND );
	$im->trimImage(0);
	$imOut = new Imagick();
	$imOut->setBackgroundColor(new ImagickPixel('transparent')); 
	$imOut->newImage($im->getImageWidth()*2, $im->getImageHeight(), new ImagickPixel('transparent'));
	$imOut->setImageVirtualPixelMethod( Imagick::VIRTUALPIXELMETHOD_TRANSPARENT );	
	$imOut->setImageFormat('png');
	$imOut->compositeimage($im, Imagick::COMPOSITE_DEFAULT, 0, 0);
	$imOut->compositeimage($im, Imagick::COMPOSITE_DEFAULT, $im->getImageWidth()+1, 0);
	$imOut->waveImage($amount*-2,$imOut->getImageWidth()*2);
	$imOut->cropImage($imOut->getImageWidth()/2,$imOut->getImageHeight(),$im->getImageWidth()+1,0);
	$imOut->trimImage(0);
	$imOut->setImagePage(0, 0, 0, 0);
	return(base64_encode($imOut));
}

function skewUp($image,$amount) {
	$im = new Imagick();
	$im->setBackgroundColor(new ImagickPixel('transparent'));
	$im->readimageblob($image);
	$im->setImageVirtualPixelMethod( imagick::VIRTUALPIXELMETHOD_BACKGROUND );
	$im->trimImage(0);
	$im->setImagePage(0, 0, 0, 0);
	$x1 = $im->getImageWidth();
	$x2 = $im->getImageWidth();
	$y1 = $im->getImageHeight();
	$y2 = $im->getImageHeight()/((100-$amount)*0.01);
	$im->setGravity(imagick::GRAVITY_NORTH);
	$im->extentImage ($x2,$y2,0,0);
	$points = array(
		0, 0, 0, $im->getImageHeight()-$y1, #top left
		0, $y1, 0, $y2, #bottom left
		$x1, 0, $x1, 0, #top right
		$x1, $y1, $x1, $y1 #bottom right
	);
	$im->distortImage( Imagick::DISTORTION_BILINEAR, $points, TRUE );
	$im->trimImage(0);
	$im->setImagePage(0, 0, 0, 0);
	return($im);	
}

function skewDown($image,$amount) {
	$im = new Imagick();
	$im->setBackgroundColor(new ImagickPixel('transparent'));
	$im->readimageblob($image);
	$im->setImageVirtualPixelMethod( imagick::VIRTUALPIXELMETHOD_BACKGROUND );
	$im->trimImage(0);
	$im->setImagePage(0, 0, 0, 0);
	$x1 = $im->getImageWidth();
	$x2 = $im->getImageWidth();
	$y1 = $im->getImageHeight();
	$y2 = $im->getImageHeight()/((100-$amount)*0.01);
	$im->setGravity(imagick::GRAVITY_NORTH);
	$im->extentImage ($x2,$y2,0,0);
	$points = array(
		0, 0, 0, 0, #top left
		0, $y1, 0, $y1, #bottom left
		$x1, 0, $x1, $im->getImageHeight()-$y1, #top right
		$x1, $y1, $x1, $y2 #bottom right
	);
	$im->distortImage( Imagick::DISTORTION_BILINEAR, $points, TRUE );
	$im->trimImage(0);
	$im->setImagePage(0, 0, 0, 0);
	return($im);	
}