<?php

$teamname = strlen($_POST['information']['teamName']) < 1 ? "Unknown_Team" : preg_replace('/([^a-z0-9]+)/i', '_', $_POST['information']['teamName']);
$teamname = preg_replace('/([^a-z0-9]+)/i', '_', $teamname);
$fnCapType = $_POST['information']['capType'];
$fnStartYear = $_POST['information']['startYear'] == "0" ? "" : "_".$_POST['information']['startYear'];
$fnEndYear = $_POST['information']['endYear'] == "0" ? "" : "_".$_POST['information']['endYear'];
$filename = "caps_".$teamname."_".$fnCapType.$fnStartYear.$fnEndYear;
$capname = "caps_".$teamname."_".$fnCapType.$fnStartYear.$fnEndYear.".png";

$textureString = strpos($_POST['information']['texture'],"image/png;base64") > -1 ? "Custom" : $_POST['information']['texture'];

$capXML = new SimpleXMLElement("<cap></cap>");

$XMLComment = $capXML->addChild("Information");
$XMLComment->addChild('URL','http://www.ootputilities.com');
$XMLComment->addChild('CreatedOn',date("Y/n/j g:i:sa"));

$mainCapElements = $capXML->addChild('MainCapElements');
$mainCapElements->addChild('TeamName',xmlEscape($_POST['information']['teamName']));
$mainCapElements->addChild('CapType',$_POST['information']['capType']);
$mainCapElements->addChild('Start',$_POST['information']['startYear']);
$mainCapElements->addChild('End',$_POST['information']['endYear']);
$mainCapElements->addChild('Texture',$textureString);
$mainCapElements->addChild('CapText',$_POST['information']['capText']);
$mainCapElements->addChild('CapText2',$_POST['information']['capText2']);
$mainCapElements->addChild('FontTypeCombo',$_POST['information']['fontTypeCombo']);

$elements = $capXML->addChild('CapElements');

foreach ($_POST['elements'] as $key => $val) {
	$tmp = $elements->addChild($val['element'],$val['checked']);
}

$colors = $capXML->addChild('Colors');

foreach ($_POST['colors'] as $key => $val) {
	$tmp = $colors->addChild($val['spectrum'],$val['color']);
}

$doc = new DOMDocument('1.0');
$doc->formatOutput = true;
$domnode = dom_import_simplexml($capXML);
$domnode->preserveWhiteSpace = false;
$domnode = $doc->importNode($domnode, true);
$domnode = $doc->appendChild($domnode);

$base = decode($_POST['image']);

$imBelow = new Imagick();
$imBelow->readImageBlob($base);
$imBelow->setImageFormat("png");

$imWatermark = new Imagick('../images/cm_watermark.png');

$imFinal = new Imagick();
$imFinal->newImage(512, 512, new ImagickPixel('transparent'));
$imFinal->setImageFormat('png');
$imFinal->setImageVirtualPixelMethod(Imagick::VIRTUALPIXELMETHOD_TRANSPARENT);
$imFinal->compositeImage($imBelow,imagick::COMPOSITE_DEFAULT,0,0);
$imFinal->compositeImage($imWatermark,imagick::COMPOSITE_DEFAULT,0,0);
$imFinal->writeImage('../temp/'.$capname);

$zip = new ZipArchive();
$file = "../temp/".$filename.".cap";
$zip->open($file, ZipArchive::OVERWRITE);
$zip->addFromString("cap.xml",$doc->saveXML());
$zip->addFromString("below.json",json_encode($_POST['canvases']['below']));
$zip->addFromString("texture.json",$_POST['canvases']['texture']);
$zip->addFromString("above.json",$_POST['canvases']['above']);
$zip->close();

$zip2 = new ZipArchive();
$file2 = "../temp/".$filename.".zip";
$zip2->open($file2, ZipArchive::OVERWRITE);
$zip2->addFile('../temp/'.$filename.".cap","capmaker_".$filename.".cap");
$zip2->addFile('../temp/'.$capname,$capname);
$zip2->close();

header('Content-Type: application/zip');
header('Content-Length: ' . filesize($file2));
header('Content-Disposition: attachment; filename="'.$filename.'.zip"');
readfile($file2);
unlink($file); 
unlink($file2);
unlink('../temp/'.$capname);

function xmlEscape($string) {
    return str_replace(array('&', '<', '>', '\'', '"'), array('&amp;', '&lt;', '&gt;', '&apos;', '&quot;'), $string);
}

function decode($string) {
	$encoded = str_replace(' ', '+', $string);
	$encoded = str_replace('data:image/png;base64,','',$encoded);
	return base64_decode($encoded);
}
