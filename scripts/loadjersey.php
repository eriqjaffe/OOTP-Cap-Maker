<?php
$expectedFiles = array("above.json","below.json","texture.json","jersey.xml");
$zipFiles = array();

$filetype = $_FILES['file']['type'];
$filename = $_FILES["file"]["name"];
$source = $_FILES["file"]["tmp_name"];
$target_path = "../temp/".$filename;

if ($filetype != "application/octet-stream" && substr($filename, -4) != ".jrs") {
	die("Invalid file uploaded!");
}

if(move_uploaded_file($source, $target_path)) {
	$zip = new ZipArchive();
	$x = $zip->open($target_path);
    if($x === true) {
		for( $i = 0; $i < $zip->numFiles; $i++ ){ 
            $stat = $zip->statIndex($i); 
            array_push($zipFiles,basename($stat['name']));   
        }   
        foreach ($expectedFiles as $file) {
            if (!in_array($file,$zipFiles)) {
                die("Invalid file uploaded!");
            }
        }
    	$xml = $zip->getFromName('jersey.xml');
    	$above = $zip->getFromName('above.json');
    	$texture = $zip->getFromName('texture.json');
    	$below = $zip->getFromName('below.json');
		$logos = $zip->getFromName('logos.json');
    	$return = array(json_decode($below),$texture,json_decode($above),json_decode($logos),$xml);
    	echo json_encode($return);
		//echo $texture;
        unlink($target_path);
    } else {
        die("There was a problem. Please try again!");
    }
} else {
	echo "failure";
}