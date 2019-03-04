<?php
error_reporting(E_ALL);

require_once 'config.php';
require_once 'function.php';

if(isset($_GET['data']) && isset($_GET['sign'])){
    $hh = trim($_GET['data']);
    $sign = $_GET['sign'];
    writeslog('接收的data : '.$hh);
}else{
    $hh['message'] = '未获取到信息';
    $hh['status'] = false;
    $hh['data'] = '';
    writeslog('未获取到信息'.$_GET['data']);
    echo json_encode($hh, JSON_UNESCAPED_UNICODE);
    return;
}

$key = $config['key'];
$strings = $key.$hh;
$md = strtoupper(md5($strings));

// if($md != $sign){
//     $data['message'] = '签名不正确';
//     $data['status'] = false;
//     $data['data'] = '';
//     writeslog('加密后字符串: '.$md);
//     echo json_encode($data, JSON_UNESCAPED_UNICODE);
//     return;
// }

$h2 = json_decode($hh,true);
$UserID = $h2['userid'];

$today1 = strtotime(date('Y-m-d').' 21:00:00');
$today2 = strtotime(date('Y-m-d').' 14:00:00');
$day3 = date('Y-m-d',strtotime('+1 days'));
$day33 = strtotime($day3.' 14:00:00');
if(time()<$today2){
	$today1 = $today2;
}else if(time()>$today1){
	$today1 = $day33;
}
$arr_time = time_diff(time(),$today1);
var_dump($arr_time);