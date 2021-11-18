<?php
header('Content-type: image/png');
//header('Content-Disposition: attachment; filename="test.png"');

$deformation = $_POST['deform'];
$amount = $_POST['amount'];
$encoded = $_POST['imgdata'];
$encoded = str_replace(' ', '+', $encoded);
$decoded = base64_decode($encoded);

echo $deformation($decoded,$amount);

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
	return(base64_encode($im));
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
	return(base64_encode($im));
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
	$im->writeImage('../temp/wordmark.png');
	$im->clear();
	$cmd = "./bilinearwarp -v transparent -b none \"0,0 $x1,0 $x1,$y1, 1,$y2\" ../temp/wordmark.png ../temp/wordmark_out.png";
	exec($cmd, $retval);
	$im = new Imagick();
	$im->readImage('../temp/wordmark_out.png');
	//unlink('../temp/wordmark.png');
	//unlink('../temp/wordmark_out.png');
	return base64_encode($im);
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
	$im->writeImage('../temp/wordmark.png');
	$im->clear();
	$cmd = "./bilinearwarp -v transparent -b none \"0,0 $x1,0 $x1,$y2, 1,$y1\" ../temp/wordmark.png ../temp/wordmark_out.png";
	exec($cmd, $retval);
	$im = new Imagick();
	$im->readImage('../temp/wordmark_out.png');
	//unlink('../temp/wordmark.png');
	//unlink('../temp/wordmark_out.png');
	return base64_encode($im);
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