<?php
	
$base = decode($_POST['imgdata']);
$wm = $_POST['watermark'];

$tmpfname = tempnam("../temp/", "tmpCap");

$imBelow = new Imagick();
$imBelow->readImageBlob($base);
$imBelow->setImageFormat("png");

$imWatermark = new Imagick('../images/'.$wm);

$size = substr($wm,0,3) == "fhm" ? 1024 : 512;

$imFinal = new Imagick();
$imFinal->newImage($size, $size, new ImagickPixel('transparent'));
$imFinal->setImageFormat('png');
$imFinal->setImageVirtualPixelMethod(Imagick::VIRTUALPIXELMETHOD_TRANSPARENT);
$imFinal->compositeImage($imBelow,imagick::COMPOSITE_DEFAULT,0,0);
$imFinal->compositeImage($imWatermark,imagick::COMPOSITE_DEFAULT,0,0);
$imFinal->writeImage($tmpfname);

$imgur = new ImgurUploader;
$imgur->setClientId('c9ff708b19a4996');
$result = $imgur->uploadImage($tmpfname);
if (!$result) {
  $error = $imgur->getError();
  echo json_encode(array(success=>false,error=>$error));
}
else if ($result['status'] != 200) {
  //print "Upload failed. Error: " . $result['data']['error'];
  //$output = array(success=>false,error=>$result['data']['error']);
  echo json_encode(array(success=>false,error=>$result['data']['error']));
}
else {
  //print "Upload successful!";
  echo json_encode($result);
}

unlink($tmpfname);

class ImgurUploader {
  private $clientId;
  private $error;
  const TIMEOUT = 30;
  const UPLOAD_URL = 'https://api.imgur.com/3/image.json';
 
  public function setClientId($clientId) {
    $this->clientId = $clientId;
  }
  public function getError () {
    return $this->error;
  }
  public function uploadImage($filename) {
    if (empty($this->clientId)) {
      $this->setError("No clientID set");
      return FALSE;
    }
    if (!file_exists($filename)) {
      $this->setError("Filename '$filename' doesn't exist");
      return FALSE;
    }
    if (!is_readable($filename)) {
      $this->setError("Filename '$filename' isn't readable");
      return FALSE;
    }
    $handle = fopen($filename, "rb");
    if (!$handle) {
      $this->setError("Failed opening '$filename' for reading");
      return FALSE;
    }
    $image = file_get_contents($filename);
    if ($image === FALSE) {
      fclose($handle);
      $this->setError("Failed reading contents of '$filename'");
      return FALSE;
    }
    fclose($handle);
    return $this->curlUpload(base64_encode($image));
  }
  public function uploadImageBase64($imageBase64) {
    if (empty($this->clientId)) {
      $this->setError("No clientID set");
      return FALSE;
    }
    return $this->curlUpload($imageBase64);
  }
  private function curlUpload($imageBase64) {
    $curl = curl_init();
    if (!$curl) {
      $errstr = $this->getCurlStrError($curl);
      $this->setError("Failed initializing curl: $errstr");
      return FALSE;
    }
    $ret = curl_setopt($curl, CURLOPT_URL, self::UPLOAD_URL);
    if (!$ret) {
      $errstr = $this->getCurlStrError($curl);
      $this->setError("Failed setting CURLOPT_URL: $errstr");
      return FALSE;
    }
    $ret = curl_setopt($curl, CURLOPT_TIMEOUT, self::TIMEOUT);
    if (!$ret) {
      $errstr = $this->getCurlStrError($curl);
      $this->setError("Failed setting CURLOPT_TIMEOUT: $errstr");
      return FALSE;
    }
    $ret = curl_setopt($curl, CURLOPT_HTTPHEADER, [ 'Authorization: Client-ID ' . $this->clientId ]);
    if (!$ret) {
      $errstr = $this->getCurlStrError($curl);
      $this->setError("Failed setting CURLOPT_HTTPHEADER: $errstr");
      return FALSE;
    }
    $ret = curl_setopt($curl, CURLOPT_POST, 1);
    if (!$ret) {
      $errstr = $this->getCurlStrError($curl);
      $this->setError("Failed setting CURLOPT_POST: $errstr");
      return FALSE;
    }
    $ret = curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    if (!$ret) {
      $errstr = $this->getCurlStrError($curl);
      $this->setError("Failed setting CURLOPT_RETURNTRANSFER: $errstr");
      return FALSE;
    }
    $ret = curl_setopt($curl, CURLOPT_POSTFIELDS, [
      'image' => $imageBase64
    ]);
    if (!$ret) {
      $errstr = $this->getCurlStrError($curl);
      $this->setError("Failed setting CURLOPT_POSTFIELDS: $errstr");
      return FALSE;
    }
    $out = curl_exec($curl);
    if (!$out) {
      $errstr = $this->getCurlStrError($curl);
      $this->setError("Curl session failed: $errstr");
      return FALSE;
    }
    curl_close($curl);
    return json_decode($out, true);      
  }
  private function getCurlStrError($curl) {
    $errno = curl_errno($curl);
    $errstr = curl_strerror($errno);
    return $errstr;
  }
  private function setError($error) {
    $this->error = $error;
  }
}

function decode($string) {
	$encoded = str_replace(' ', '+', $string);
	$encoded = str_replace('data:image/png;base64,','',$encoded);
	return base64_decode($encoded);
}