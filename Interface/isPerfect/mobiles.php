<?php
header('Content-Type:application/json; charset=utf-8');     
error_reporting(E_ALL);
date_default_timezone_set('Asia/Shanghai');
require_once 'function.php';

if(isset($_GET['data'])){
    $hh = trim($_GET['data']);
    $sign = $_GET['sign'];
}else{
    $hh['message'] = '未获取到信息';
    $hh['status'] = false;
    $hh['data'] = null;
    echo json_encode($hh, JSON_UNESCAPED_UNICODE);
    return;
}
$sign2 = 'FFDFEE8B01CF3D7109DDB4909BCC8266';
$strings = $sign2.$hh;
writeslog('sign'.$sign);
writeslog('mobiles加密前字符串: '.$strings);
$md = strtoupper(md5($strings));
writeslog('mobiles加密后字符串: '.$md);
if($md != $sign){
    $data['message'] = '签名不正确';
    $data['status'] = false;
    $data['data'] = null;
    writeslog('加密字符串: '.$strings);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

$h2 = json_decode($hh,true);

$data = array();

$data['mobile'] = $h2['mobile'];
$data['userid'] = (int)$h2['userid'];

$data = json_encode($data,JSON_UNESCAPED_UNICODE);
// var_dump($data);exit;
$sign = strtoupper(md5($sign2.$data));
writeslog('sign: '.$sign2.$data );
// echo "<script>window.location.href='http://120.78.149.107:8084/sj/1009?sign=".$sign."&data=".$data."';</script>";
$url = "http://120.78.149.107:8084/sj/1009?sign=".$sign."&data=".$data."";
writeslog('url : '.$url);
$qq = curlNe($url);
writeslog('mobile---------'.$qq);
$dats  = json_decode($qq,true);
echo $qq;
//header("location:".$url."");
