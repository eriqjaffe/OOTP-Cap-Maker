<?php

echo $_SERVER['CONTENT_LENGTH'];
die();
$teamname = strlen($_POST['information']['teamName']) < 1 ? "Unknown_Team" : preg_replace('/([^a-z0-9]+)/i', '_', $_POST['information']['teamName']);
$teamname = preg_replace('/([^a-z0-9]+)/i', '_', $teamname);
$fnJerseyType = $_POST['information']['jerseyType'];
$fnStartYear = $_POST['information']['startYear'] == "0" ? "" : "_".$_POST['information']['startYear'];
$fnEndYear = $_POST['information']['endYear'] == "0" ? "" : "_".$_POST['information']['endYear'];
$filename = "jerseys_".$teamname."_".$fnJerseyType.$fnStartYear.$fnEndYear;
$jerseyname = "jerseys_".$teamname."_".$fnJerseyType.$fnStartYear.$fnEndYear.".png";

$textureString = strpos($_POST['information']['texture'],"image/png;base64") > -1 ? "Custom" : $_POST['information']['texture'];

$jerseyXML = new SimpleXMLElement("<jersey></jersey>");

$XMLComment = $jerseyXML->addChild("Information");
$XMLComment->addChild('URL','http://www.ootputilities.com');
$XMLComment->addChild('CreatedOn',date("Y/n/j g:i:sa"));

$mainJerseyElements = $jerseyXML->addChild('MainJerseyElements');
$mainJerseyElements->addChild('TeamName',xmlEscape($_POST['information']['teamName']));
$mainJerseyElements->addChild('JerseyType',$_POST['information']['jerseyType']);
$mainJerseyElements->addChild('Start',$_POST['information']['startYear']);
$mainJerseyElements->addChild('End',$_POST['information']['endYear']);
$mainJerseyElements->addChild('Texture',$textureString);
$mainJerseyElements->addChild('SleeveType',$_POST['information']['sleeveType']);
$mainJerseyElements->addChild('WordmarkTextBox',$_POST['information']['wordmarkTextBox']);
$mainJerseyElements->addChild('FontTypeCombo',$_POST['information']['fontTypeCombo']);
$mainJerseyElements->addChild('MakersMark',$_POST['information']['makersMark']);

foreach ($_POST['ranges'] as $key => $val) {
	$mainJerseyElements->addChild($val['element'],$val['value']);
}

$mainJerseyElements->addChild('WarpEffect',$_POST['information']['warpEffect']);
$mainJerseyElements->addChild('SwashCombo',$_POST['information']['swashType']);

$elements = $jerseyXML->addChild('JerseyElements');

foreach ($_POST['elements'] as $key => $val) {
	$tmp = $elements->addChild($val['element'],$val['checked']);
}

$colors = $jerseyXML->addChild('Colors');

foreach ($_POST['colors'] as $key => $val) {
	$tmp = $colors->addChild($val['spectrum'],$val['color']);
}

$doc = new DOMDocument('1.0');
$doc->formatOutput = true;
$domnode = dom_import_simplexml($jerseyXML);
$domnode->preserveWhiteSpace = false;
$domnode = $doc->importNode($domnode, true);
$domnode = $doc->appendChild($domnode);

$base = decode($_POST['image']);

$imBelow = new Imagick();
$imBelow->readImageBlob($base);
$imBelow->setImageFormat("png");

$imWatermark = new Imagick('../images/jm_watermark.png');

$imFinal = new Imagick();
$imFinal->newImage(512, 512, new ImagickPixel('transparent'));
$imFinal->setImageFormat('png');
$imFinal->setImageVirtualPixelMethod(Imagick::VIRTUALPIXELMETHOD_TRANSPARENT);
$imFinal->compositeImage($imBelow,imagick::COMPOSITE_DEFAULT,0,0);
$imFinal->compositeImage($imWatermark,imagick::COMPOSITE_DEFAULT,0,0);
$imFinal->writeImage('../temp/'.$jerseyname);

$zip = new ZipArchive();
$file = "../temp/".$filename.".cap";
$zip->open($file, ZipArchive::OVERWRITE);
$zip->addFromString("jersey.xml",$doc->saveXML());
$zip->addFromString("below.json",json_encode($_POST['canvases']['below']));
$zip->addFromString("texture.json",$_POST['canvases']['texture']);
$zip->addFromString("above.json",$_POST['canvases']['above']);
$zip->addFromString("logos.json",$_POST['canvases']['logos']);
$zip->close();

$zip2 = new ZipArchive();
$file2 = "../temp/".$filename.".zip";
$zip2->open($file2, ZipArchive::OVERWRITE);
$zip2->addFile('../temp/'.$filename.".cap","jerseymaker_".$filename.".jrs");
$zip2->addFile('../temp/'.$jerseyname,$jerseyname);
$zip2->close();

header('Content-Type: application/zip');
header('Content-Length: ' . filesize($file2));
header('Content-Disposition: attachment; filename="'.$filename.'.zip"');
readfile($file2);
unlink($file); 
unlink($file2);
unlink('../temp/'.$jerseyname);

function xmlEscape($string) {
    return str_replace(array('&', '<', '>', '\'', '"'), array('&amp;', '&lt;', '&gt;', '&apos;', '&quot;'), $string);
}

function decode($string) {
	$encoded = str_replace(' ', '+', $string);
	$encoded = str_replace('data:image/png;base64,','',$encoded);
	return base64_decode($encoded);
}
