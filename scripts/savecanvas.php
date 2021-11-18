<?php
	
// build file name	
$teamname = strlen($_POST['teamname']) < 1 ? "Unknown_Team" : preg_replace('/([^a-z0-9]+)/i', '_', $_POST['teamname']);
$teamname = preg_replace('/([^a-z0-9]+)/i', '_', $teamname);
$fnCapType = $_POST['capType'];
$fnStartYear = $_POST['startYear'] == "0" ? "" : "_".$_POST['startYear'];
$fnEndYear = $_POST['endYear'] == "0" ? "" : "_".$_POST['endYear'];
$filename = $teamname."_".$fnCapType.$fnStartYear.$fnEndYear.".cap";

// generate XML file
$capXML = new SimpleXMLElement("<cap></cap>");

$mainCapElements = $capXML->addChild('MainCapElements');
$teamName = $mainCapElements->addChild('TeamName',xmlEscape($_POST["teamname"]));
$capType = $mainCapElements->addChild('CapType',$_POST['capType']);
$startYear = $mainCapElements->addChild('Start',$_POST['startYear']);
$endYear = $mainCapElements->addChild('End',$_POST['endYear']);
$textureString = strpos($_POST['texture'],"image/png;base64") > -1 ? "Custom" : $_POST['texture'];
$texture = $mainCapElements->addChild('Texture',$textureString);

$colors = $capXML->addChild('CapElements');


$doc = new DOMDocument('1.0');
$doc->formatOutput = true;
$domnode = dom_import_simplexml($capXML);
$domnode->preserveWhiteSpace = false;
$domnode = $doc->importNode($domnode, true);
$domnode = $doc->appendChild($domnode);

$file = tempnam("tmp", "zip");
$zip = new ZipArchive();
$zip->open($file, ZipArchive::OVERWRITE);
$zip->addFromString("cap.xml",$doc->saveXML());
$zip->addFromString("below.json",$_POST['below']);
$zip->addFromString("texture.json",$_POST['texture']);
$zip->addFromString("above.json",$_POST['above']);
$zip->close();
header('Content-Type: application/zip');
header('Content-Length: ' . filesize($file));
header('Content-Disposition: attachment; filename="'.$filename.'"');
readfile($file);
unlink($file); 	

function xmlEscape($string) {
    return str_replace(array('&', '<', '>', '\'', '"'), array('&amp;', '&lt;', '&gt;', '&apos;', '&quot;'), $string);
}