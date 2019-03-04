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

if($md != $sign){
    $data['message'] = '签名不正确';
    $data['status'] = false;
    $data['data'] = '';
    writeslog('加密后字符串: '.$md);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    return;
}

$h2 = json_decode($hh,true);
$UserID = $h2['userid'];

$serverName = $config['db']['hostname'];
$connectionInfo = array( "Database"=>$config['db']['database'], "UID"=>$config['db']['username'], "PWD"=>$config['db']['password']);
$conn = sqlsrv_connect( $serverName, $connectionInfo);
$date1 = date('Y-m-d').' 13:50:00';
$date2 = date('Y-m-d').' 15:10:00';
$date3 = date('Y-m-d').' 20:50:00';

$status = partakeAction($UserID,$date1,$date2,$date3,$conn);

if($status == 0){
	$err['message'] = '';
	$err['status'] = true;
	$err['data']['userid'] = $UserID;
	$data = $err;
	echo json_encode($data, JSON_UNESCAPED_UNICODE);
	return;
}else{
	if($status == 1 || $status == 2){
		$err['message'] = '抱歉！已参加红包雨活动。';
	}else if($status == 3){
		$err['message'] = '红包雨活动未开启。';
	}else{
		$err['message'] = '魅力值不足。';
	}
	$err['status'] = false;
	$err['data'] = '';
	$data = $err;
	writeslog($err['message'].$UserID.' -- '.$status);
	echo json_encode($data, JSON_UNESCAPED_UNICODE);
	return;
	
}

