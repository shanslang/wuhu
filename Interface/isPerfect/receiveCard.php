<?php
error_reporting(E_ALL);
require_once 'function.php';
require_once 'config.php';
header('Content-Type:application/json; charset=utf-8'); 
date_default_timezone_set('Asia/Shanghai');

if(isset($_GET['data'])){
    $hh = trim($_GET['data']);
    $sign = $_GET['sign'];
}else{
    $hh['message'] = '未获取到userid';
    $hh['status'] = false;
    $hh['data'] = null;
    $info = $hh;
    writeslog('receiveCard  not found this userid');
    echo json_encode($info, JSON_UNESCAPED_UNICODE);
    return;
}

$sign2 = 'FFDFEE8B01CF3D7109DDB4909BCC8266';
$strings = trim($sign2.$hh);

$md = strtoupper(md5($strings));
if($md != $sign){
    $info['message'] = '签名不正确';
    writeslog('receiveCard sign err='.$md);
    $info['status'] = false;
    $info['data'] = null;
    echo json_encode($info, JSON_UNESCAPED_UNICODE);
    return;
}
writeslog('logins '.$hh);
$h2 = json_decode($hh,true);
$hh = $h2['userid'];
$wm = $h2['cardType']; // 1周卡，0月卡

$serverName = $config['db']['hostname'];
$connectionInfo = array( "Database"=>$config['db']['database'], "UID"=>$config['db']['username'], "PWD"=>$config['db']['password']);
$conn = sqlsrv_connect($serverName, $connectionInfo); 

$sts = receiveGoldCard($hh,$wm,$conn);

$data['message'] = $sts['msg'];
$data['status'] = true;
$data['data']['uid'] = $hh;
$data['data']['bs'] = $sts['bs'];
echo json_encode($data,JSON_UNESCAPED_UNICODE);