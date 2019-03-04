<?php
error_reporting(0);
header('Content-Type:application/json; charset=utf-8');    
date_default_timezone_set('Asia/Shanghai');

require_once 'function.php';
require_once 'config.php';

if(isset($_GET['data']) && isset($_GET['sign'])){
    $hh = trim($_GET['data']);
    $sign = $_GET['sign'];
    writeslog('hornEdit -接收的data : '.$hh);
}else{
    $hh['message'] = '未获取到信息';
    $hh['status'] = false;
    $hh['data'] = '';
    writeslog('hornEdit 未获取到信息');
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
    writeslog($hh.' hornEdit 签名不正确加密后: '.$md);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    return;
}

$h2 = json_decode($hh,true);
$UserID = $h2['userid'];
$MsgContent = iconv('utf-8','GBK',$h2['msgcontent']);

$serverName = $config['db']['hostname'];
$connectionInfo = array( "Database"=>$config['db']['database'], "UID"=>$config['db']['username'], "PWD"=>$config['db']['password']);
$conn = sqlsrv_connect($serverName, $connectionInfo);

if($h2['msgcontent'] == ''){
    $err = array();
    $err['message'] = '喇叭内容不能为空,请重新提交';
    $err['status'] = false;
    $err['data'] = '';
    $data = $err;
    writeslog('喇叭内容不能为空,请重新提交: '.$UserID.'-'.$h2['msgcontent']);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    return;
}

$IP = GetIP();
$row = Hornedit($UserID, $IP, $MsgContent, $conn);

if($row['ret'] == 1){
    $err['message'] = 'userid错误或不是VIP';
    $err['status'] = false;
    $err['data'] = '';
    $data = $err;
    writeslog('userid错误或不是VIP: '.$UserID);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    return;
}else if($row['ret'] == 2){
    $errs['message'] = '未到1小时';
    $errs['status'] = false;
    $errs['data']['userid'] = $UserID;
    $errs['data']['msgcontent'] = iconv('GBK','utf-8',$row['oldMsgContent']);
    $errs['data']['displaytime'] = $row['DisplayTime'];
    writeslog('未到1小时: '.$UserID);
    echo json_encode($errs, JSON_UNESCAPED_UNICODE);
    return;
    exit;
}else if($row['ret'] == 3){
    $err['message'] = '银行余额不足';
    $err['status'] = false;
    $err['data'] = '';
    $data = $err;
    writeslog('银行余额不足: '.$UserID);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    return;
}else if($row['ret'] == 4){
    $err['message'] = '喇叭内容不能为空,请重新提交';
    $err['status'] = false;
    $err['data'] = '';
    $data = $err;
    writeslog('喇叭内容不能为空,请重新提交: '.$UserID);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    return;
}else{
    $err['message'] = '';
    $err['status'] = true;
    $err['data']['userid'] = $UserID;
    $err['data']['msgcontent'] = iconv('GBK','utf-8',$row['newMsgContent']);
    $data = $err;
    writeslog('hornEdit success:'.$UserID);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    return;
}

