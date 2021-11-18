<?php
$expectedFiles = array("above.json","below.json","texture.json","cap.xml");
$zipFiles = array();

$filetype = $_FILES['file']['type'];
$filename = $_FILES["file"]["name"];
$source = $_FILES["file"]["tmp_name"];
$target_path = "../temp/".$filename;

if ($filetype != "application/octet-stream" && substr($filename, -4) != ".cap") {
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
    	$xml = $zip->getFromName('cap.xml');
    	$above = $zip->getFromName('above.json');
    	$texture = $zip->getFromName('texture.json');
    	$below = $zip->getFromName('below.json');
    	$return = array(json_decode($below),$texture,json_decode($above),$xml);
        unlink($target_path);
		echo json_encode($return);
    } else {
        die("There was a problem. Please try again!");
    }
} else {
	echo "failure";
}