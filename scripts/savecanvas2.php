<?php
$teamname = strlen($_POST['information']['teamName']) < 1 ? "Unknown_Team" : preg_replace('/([^a-z0-9]+)/i', '_', $_POST['information']['teamName']);
$teamname = preg_replace('/([^a-z0-9]+)/i', '_', $teamname);
$fnCapType = $_POST['information']['capType'];
$fnStartYear = $_POST['information']['startYear'] == "0" ? "" : "_".$_POST['information']['startYear'];
$fnEndYear = $_POST['information']['endYear'] == "0" ? "" : "_".$_POST['information']['endYear'];
$filename = $teamname."_".$fnCapType.$fnStartYear.$fnEndYear.".cap";
$capname = "caps_".$teamname."_".$fnCapType.$fnStartYear.$fnEndYear.".png";

$textureString = strpos($_POST['information']['texture'],"image/png;base64") > -1 ? "Custom" : $_POST['information']['texture'];

$capXML = new SimpleXMLElement("<cap></cap>");

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
	$tmp = $elements->addChild($val['element']);
	$tmp->addAttribute('checked',$val['checked']);
}

$colors = $capXML->addChild('Colors');

foreach ($_POST['colors'] as $key => $val) {
	$tmp = $colors->addChild($val['element']);
	$tmp->addAttribute('color',$val['color']);
}

$doc = new DOMDocument('1.0');
$doc->formatOutput = true;
$domnode = dom_import_simplexml($capXML);
$domnode->preserveWhiteSpace = false;
$domnode = $doc->importNode($domnode, true);
$domnode = $doc->appendChild($domnode);

//$filename = $_POST['fileName'];
$below = $_POST['images']['below'];
$texture = $_POST['images']['texture'];
$above = $_POST['images']['above'];

$encoded = str_replace(' ', '+', $above);
$encoded = str_replace('data:image/png;base64,','',$encoded);
$decoded = base64_decode($encoded);

$imBelow = new Imagick();
$imBelow->readImageBlob($below);
$imBelow->setImageFormat("png");
//$imBelow->writeImage('../temp/'.$capname);

$imTexture = new Imagick();
$imTexture->readImageBlob($texture);
$imTexture->setImageFormat("png");
//$imTexture->writeImage('../temp/'.$capname);

$imAbove = new Imagick();
$imAbove->setBackgroundColor(new ImagickPixel('transparent'));
$imAbove->readImageBlob($decoded);
$imAbove->setImageFormat("png");

$imFinal = new Imagick();
$imFinal->newImage(512, 512, new ImagickPixel('transparent'));
$imFinal->setImageFormat('png');
$imFinal->setImageVirtualPixelMethod(Imagick::VIRTUALPIXELMETHOD_TRANSPARENT);
$imFinal->compositeImage($imBelow,imagick::COMPOSITE_DEFAULT,0,0);
$imFinal->compositeImage($imTexture,imagick::COMPOSITE_MULTIPLY,0,0);
$imFinal->compositeImage($imAbove,imagick::COMPOSITE_DEFAULT,0,0);
$imFinal->writeImage('../temp/'.$capname);

$file = tempnam("tmp", "zip");
$zip = new ZipArchive();
$zip->open($file, ZipArchive::OVERWRITE);
$zip->addFromString("cap.xml",$doc->saveXML());
$zip->addFromString("below.json",$_POST['canvases']['below']);
$zip->addFromString("texture.json",$_POST['canvases']['texture']);
$zip->addFromString("above.json",$_POST['canvases']['above']);
$zip->addFile('../temp/'.$capname,$capname);
$zip->close();
header('Content-Type: application/zip');
header('Content-Length: ' . filesize($file));
header('Content-Disposition: attachment; filename="'.$filename.'"');
readfile($file);
unlink($file); 	
//unlink('../temp/'.$capname);

function xmlEscape($string) {
    return str_replace(array('&', '<', '>', '\'', '"'), array('&amp;', '&lt;', '&gt;', '&apos;', '&quot;'), $string);
}
