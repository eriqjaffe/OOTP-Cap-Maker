<?php
	header('Content-type: image/png');
	header('Content-Disposition: attachment; filename="' . $_POST['name'] .'"');
	
	$encoded = $_POST['imgdata'];
	$type = $_POST['type'];
    $encoded = str_replace(' ', '+', $encoded);
	$decoded = base64_decode($_POST['imgdata']);
	$size = 512;
	
	$finalImg = imagecreatefromstring($decoded);
	if ($finalImg !== false) {
		header('Content-Type: image/png');
		switch ($type) {
			case "cap":
				$watermark = imagecreatefrompng('../images/cm_watermark.png');
				break;
			case "jersey":
				$watermark = imagecreatefrompng('../images/jm_watermark.png');
				break;
			case "sweater":
				$watermark = imagecreatefrompng('../images/fhm_watermark.png');
				$size = 1024;
				break;
			default:
				$watermark = imagecreatefrompng('../images/ou_watermark.png');
		}
		imagecopy ($finalImg,$watermark,0,0,0,0,$size,$size);
		imagepng($finalImg);
		imagedestroy($finalImg);
		imagedestroy($watermark);
	}
	else {
		echo 'An error occurred.';
	}
?>