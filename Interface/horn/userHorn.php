<?php
error_reporting(0);
header('Content-Type:application/json; charset=utf-8');    
date_default_timezone_set('Asia/Shanghai');

require_once 'function.php';
require_once 'config.php';

if(isset($_GET['data']) && isset($_GET['sign'])){
    $hh = trim($_GET['data']);
    $sign = $_GET['sign'];
    writeslog('接收的data : '.$hh);
}else{
    $hh['message'] = '未获取到信息';
    $hh['status'] = false;
    $hh['data'] = '';
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
    writeslog('userHorn 加密后字符串: '.$md);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    return;
}

$h2 = json_decode($hh,true);
$UserID = $h2['userid'];

$serverName = $config['db']['hostname'];
$connectionInfo = array( "Database"=>$config['db']['database'], "UID"=>$config['db']['username'], "PWD"=>$config['db']['password']);
$conn = sqlsrv_connect($serverName, $connectionInfo);

$rs = UserHorn($UserID, $conn);

if($rs['ret'] == 1){
    $err['message'] = 'userid错误或不是VIP';
    $err['status'] = false;
    $err['data'] = '';
    $data = $err;
    writeslog('userid错误或不是VIP: '.$UserID);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    return;
}

if($rs['ret'] == 2){
    $err['message'] = '还没有喇叭';
    $err['status'] = true;
    $err['data']['status'] = false;
    $err['data']['userid'] = $UserID;
    $err['data']['status'] = 33;
    $err['data']['msgcontent'] = '';
    $err['data']['displaytime'] = '';
    $err['data']['lastmodify'] = '';
    $data = $err;
    writeslog('userHorn 还没有喇叭: '.$UserID);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    return;
}else{
    $info['userid'] = $rs['UserID'];
    $info['msgcontent'] = iconv('GBK', 'utf-8', $rs['MsgContent']);
    $info['status'] = $rs['Status'];
    $info['lastmodify'] = $rs['LastModify'];
    $info['displaytime'] = empty($rs['DisplayTime'])?'':$rs['DisplayTime'];
    $datas['message'] = '';
    $datas['status'] = true;
    $datas['data'] = $info;
    writeslog('userHorn 喇叭查询成功: '.$UserID);
    $hh22 = json_encode($datas, JSON_UNESCAPED_UNICODE);
    writeslog($hh22);
    echo json_encode($datas, JSON_UNESCAPED_UNICODE);
    return;
}
