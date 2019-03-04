<?php
include 'phpqrcode.php';  
$qr = 61456; // userid
$tid = 1;
// $value = 'http://www.nzbuyu.com?qr='.$qr.'&tid='.$tid;
 //二维码内容   
$value = 'http://t.cn/E58tX5P';
$errorCorrectionLevel = 'L';//容错级别   
$matrixPointSize = 6;//生成图片大小   
//生成二维码图片   
QRcode::png($value, 'source/qrcode.png', $errorCorrectionLevel, $matrixPointSize, 2);   
$logo = 'pic_icon.png';//准备好的logo图片   
$QR = 'source/qrcode.png';//已经生成的原始二维码图   

if ($logo !== FALSE) {   
    $QR = imagecreatefromstring(file_get_contents($QR));   
    // $logo = imagecreatefromstring(file_get_contents($logo));   
    $QR_width = imagesx($QR);//二维码图片宽度   
    $QR_height = imagesy($QR);//二维码图片高度   
    // $logo_width = imagesx($logo);//logo图片宽度   
    // $logo_height = imagesy($logo);//logo图片高度   
    // $logo_qr_width = $QR_width / 5;   
    // $scale = $logo_width/$logo_qr_width;   
    // $logo_qr_height = $logo_height/$scale;   
    // $from_width = ($QR_width - $logo_qr_width) / 2;   
    //重新组合图片并调整大小   
    imagecopyresampled($QR, $logo, $from_width, $from_width, 0, 0, $logo_qr_width,   
    $logo_qr_height, $logo_width, $logo_height);   
}   
//输出图片   
imagepng($QR, 'generate/'.$qr.'_'.$tid.'.png');   
echo '<img src="generate/'.$qr.'_'.$tid.'.png">'; 

// $localfile = 'generate/hh.txt';
// $fp = fopen ($localfile, "r");
// $arr_ip = gethostbyname('appzy.nzbuyu.com');
// echo $arr_ip;
// $ftp = "ftp://".$arr_ip."/".$localfile; 
// $ch = curl_init();
// curl_setopt($ch, CURLOPT_VERBOSE, 1);
// curl_setopt($ch, CURLOPT_USERPWD, 'nzbuyu2/nzbuyu:ZHds@123456');
// curl_setopt($ch, CURLOPT_URL, $ftp);
// curl_setopt($ch, CURLOPT_PUT, 1);
// curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
// curl_setopt($ch, CURLOPT_INFILE, $fp);
// curl_setopt($ch, CURLOPT_INFILESIZE, filesize($localfile));
// $http_result = curl_exec($ch);
// $error = curl_error($ch);
// echo $error."<br>";
// $http_code = curl_getinfo($ch ,CURLINFO_HTTP_CODE);
// curl_close($ch);
// fclose($fp);  