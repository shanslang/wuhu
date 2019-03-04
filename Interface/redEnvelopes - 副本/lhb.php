<?php
error_reporting(E_ALL);

require_once 'config.php';
require_once 'function.php';

if(isset($_GET['data']) && isset($_GET['sign'])){
    $hh = trim($_GET['data']);
    $sign = $_GET['sign'];
    writeslog('lhb 接收的data : '.$hh);
}else{
    $hh['message'] = '未获取到信息';
    $hh['status'] = false;
    $hh['data'] = '';
    writeslog('lhb 未获取到信息'.$_GET['data']);
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
    writeslog('lhb 加密后字符串: '.$md);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    return;
}

$h2 = json_decode($hh,true);
$UserID = $h2['userid'];

$serverName = $config['db']['hostname'];
$connectionInfo = array( "Database"=>$config['db']['database'], "UID"=>$config['db']['username'], "PWD"=>$config['db']['password']);
$conn = sqlsrv_connect( $serverName, $connectionInfo);

$arr = lquRed($UserID,$conn);
$status = $arr['ret'];

if($status == 0){
    $err['message'] = '';
    $err['status'] = true;
    $err['data']['userid'] = $UserID;
    $err['data']['gold'] = $arr['RedGold'];
    $data = $err;
    writeslog(' lhb '.$UserID.' -- '.$status.' 金币 '.$arr['RedGold']);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    return;
}else{
    switch ($status) {
        case '1':
            $err['message'] = '抱歉，现在不是领奖时间。';
            break;
        case '2':
            $err['message'] = '您未参加红包雨。';
            break;
        case '3':
            $err['message'] = '您已领取奖励，请在背包查看';
            break;
    }
    $err['status'] = false;
    $err['data'] = '';
    $err['data']['userid'] = $UserID;
    $err['data']['gold'] = 0;
    $data = $err;
    writeslog(' lhb '.$err['message'].$UserID.' -- '.$status);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    return;  
}