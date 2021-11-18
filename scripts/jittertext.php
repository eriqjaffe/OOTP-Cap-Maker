<?php

$fonts = array(
	"Acme"=>"Acme-Regular.ttf",
	"athletic_gothicregular"=>"athletic_gothic-webfont.ttf",
	"athletic_gothic_shadowregular"=>"athletic_gothic_shadow-webfont.ttf",
	"beaverton_scriptregular"=>"beaverton_script-webfont.ttf",
	"'Berkshire Swash'"=>"BerkshireSwash-Regular.ttf",
	"'Cantora One'"=>"CantoraOne-Regular.ttf",
	"caxton_romanregular"=>"caxton_roman-webfont.ttf",
	"'Chela One'"=>"ChelaOne-Regular.ttf",
	"russell_circusregular"=>"circus-webfont.ttf",
	"Condiment"=>"Condiment-Regular.ttf",
	"Cookie"=>"Cookie-Regular.ttf",
	"Courgette"=>"Courgette-Regular.ttf",
	"'Croissant One'"=>"CroissantOne-Regular.ttf",
	"Damion"=>"Damion-Regular.ttf",
	"Engagement"=>"Engagement-Regular.ttf",
	"rawlings_fancy_blockregular"=>"rawlingsfancyblock-regular-webfont.ttf",
	"'Germania One'"=>"GermaniaOne-Regular.ttf",
	"Graduate"=>"Graduate-Regular.ttf",
	"'Grand Hotel'"=>"GrandHotel-Regular.ttf",
	"'Jockey One'"=>"JockeyOne-Regular.ttf",
	"kansasregular"=>"tuscan-webfont.ttf",
	"'Kaushan Script'"=>"KaushanScript-Regular.ttf",
	"'Leckerli One'"=>"LeckerliOne-Regular.ttf",
	"'Lily Script One'"=>"LilyScriptOne-Regular.ttf",
	"Lobster"=>"Lobster-Regular.ttf",
	"'Lobster Two'"=>"LobsterTwo-Regular.ttf",
	"'Metal Mania'"=>"MetalMania-Regular.ttf",
	"Miniver"=>"Miniver-Regular.ttf",
	"Molle, italic"=>"Molle-Regular.ttf",
	"'New Rocker'"=>"NewRocker-Regular.ttf",
	"Norican"=>"Norican-Regular.ttf",
	"rawlings_old_englishmedium"=>"rawlingsoldenglish-webfont.ttf",
	"'Oleo Script'"=>"OleoScript-Regular.ttf",
	"'Oleo Script Swash Caps'"=>"OleoScriptSwashCaps-Regular.ttf",
	"Pacifico"=>"Pacifico.ttf",
	"'Pirata One'"=>"PirataOne-Regular.ttf",
	"Playball"=>"Playball-Regular.ttf",
	"pro_full_blockregular"=>"pro_full_block-webfont.ttf",
	"richardson_fancy_blockregular"=>"richardson_fancy_block-webfont.ttf",
	"'Rubik One'"=>"RubikOne-Regular.ttf",
	"'Rum Raisin'"=>"RumRaisin-Regular.ttf",
	"Satisfy"=>"Satisfy-Regular.ttf",
	"'Seymour One'"=>"SeymourOne-Regular.ttf",
	"spl28scriptregular"=>"spl28script-webfont.ttf",
	"ua_tiffanyregular"=>"tiffany-webfont.ttf",
	"'Trade Winds'"=>"TradeWinds-Regular.ttf",
	"mlb_tuscan_newmedium"=>"mlb_tuscan_new-webfont.ttf",
	"UnifrakturCook"=>"UnifrakturCook-Bold.ttf",
	"UnifrakturMaguntia"=>"UnifrakturMaguntia-Book.ttf",
	"Vibur"=>"Vibur-Regular.ttf",
	"Viga"=>"Viga-Regular.ttf",
	"Wellfleet"=>"Wellfleet-Regular.ttf",
	"'Wendy One'"=>"WendyOne-Regular.ttf",
	"Yellowtail"=>"Yellowtail-Regular.ttf"
);

$nextX = 1024;
$nextY = 1024;
$text = $_POST['text'];
$font = $_POST['font'];
$size = $_POST['size'];
$h = $_POST['hSpacing']*2;
$v = $_POST['vSpacing']*2;

$chars = str_split($text);

$im = new Imagick();
$draw = new ImagickDraw();
$color = new ImagickPixel('#ffffff');
$background = new ImagickPixel('none');
$draw->setFont("../fonts/".$fonts[$_POST['font']]);
$draw->setFontSize($_POST['size']*2);
$draw->setFillColor($_POST['fill']);
$draw->setTextAntialias(true);
$draw->setStrokeAntialias(true);
$draw->setViewbox(0,0,2048,2048);

if ($_POST['stroke2Visible'] == 'true') {
	$draw->setStrokeColor($_POST['stroke2Color']);
	$draw->setStrokeWidth(10);
	foreach ($chars as $letter) {
		$draw->annotation($nextX,$nextY,$letter);
		$metrics = $im->queryFontMetrics($draw, $letter);
		$nextX = $nextX + $h;
		$nextY = $nextY + $v;
	}
	$nextX = 1024;
	$nextY = 1024;
}

if ($_POST['stroke1Visible'] == 'true') {
	$draw->setStrokeColor($_POST['stroke1Color']);
	$draw->setStrokeWidth(6);
	foreach ($chars as $letter) {
		$draw->annotation($nextX,$nextY,$letter);
		$metrics = $im->queryFontMetrics($draw, $letter);
		$nextX = $nextX + $h;
		$nextY = $nextY + $v;
	}
	$nextX = 1024;
	$nextY = 1024;
}

$draw->setStrokeColor('none');
$draw->setStrokeWidth(0);

foreach ($chars as $letter) {
	$draw->annotation($nextX+1,$nextY+1,$letter);
	$metrics = $im->queryFontMetrics($draw, $letter);
	$nextX = $nextX + $h;
	$nextY = $nextY + $v;
}	

$im->newImage(2048,2048,$background);
$im->setImageFormat('png');
$im->drawImage($draw);
$im->trimImage(0);

//echo "<img src='data:image/png;base64,".base64_encode($im)."' />";
echo base64_encode($im);