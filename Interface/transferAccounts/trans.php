<?php
error_reporting(0);
require_once 'function.php';
header('Content-Type:application/json; charset=utf-8');         
$config = array (	
		'db' => array(
    // 数据库类型
        'type'        => 'sqlsrv',
        // 服务器地址
        'hostname'    => '120.78.149.107',

        // 数据库名
        'database'    => 'THAccountsDB',
        
        // 数据库用户名
        //'username'    => 'sa',
        'username'    => 'gm',
        //'username'    => 'ht',

        // 数据库密码
        //'password'    => '123456',
        'password'    => 'Cn0bgJ4uulSYLAhQgIBy',

        // 数据库编码默认采用utf8
        'charset'     => 'utf8',
        
        // 数据库表前缀
        'prefix'      => ''
	)
);
date_default_timezone_set('Asia/Shanghai');

if(isset($_GET['data'])){
    $hh = trim($_GET['data']);
    $sign = '';
    if(isset($_GET['sign'])){$sign = $_GET['sign'];}
    writeslog('接收的data : '.$hh);
}else{
    $hh['message'] = '未获取到信息';
    $hh['status'] = false;
    $hh['data'] = null;
    echo json_encode($hh, JSON_UNESCAPED_UNICODE);
    return;
}
$sign2 = 'FFDFEE8B01CF3D7109DDB4909BCC8266';
$strings = $sign2.$hh;
$md = strtoupper(md5($strings));
if($md != $sign){
    $data['message'] = '签名不正确';
    $data['status'] = false;
    $data['data'] = null;
    writeslog('加密后字符串: '.$md);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

$h2 = json_decode($hh,true);

$dwUserID = $h2['userid'];
$psw =  $h2['psw'];
$cbAll = $h2['cball'];

$serverName = $config['db']['hostname'];
$connectionInfo = array( "Database"=>$config['db']['database'], "UID"=>$config['db']['username'], "PWD"=>$config['db']['password']);
$conn = sqlsrv_connect($serverName, $connectionInfo);

writeslog($dwUserID.'--'.$cbAll);
$rs = querymm($dwUserID, $psw, $conn);
if($rs == 1){
    $err['message'] = 'userid或密码错误';
    $err['status'] = false;
    $err['data'] = null;
    $data = $err;
    writeslog('trans userid或密码错误'.$dwUserID);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    return;
}
$stTransferTime = date('Y-m-d H:i:s');
$strErrorDescribe = '';
$arr = recodeTransfer($cbAll,$dwUserID,$stTransferTime,$strErrorDescribe,$conn);

if(empty($arr)){
    $err['message'] = '暂无转帐记录';
    $err['status'] = false;
    $err['data'] = null;
    $data = $err;
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    return;
}else{
    $rows = count($arr);
    $arrr3 = array();
    for($i=0;$i<$rows;$i++){
        $arr[$i]['TargetNickName'] = iconv('GBK','utf-8',$arr[$i]['TargetNickName']);
        $arr[$i]['SourceNickName'] = iconv('GBK','utf-8',$arr[$i]['SourceNickName']);
        $arrr3[$i]['dwRecordID'] = $arr[$i]['RecordID'];
        $arrr3[$i]['dwSourceUserID'] = $arr[$i]['SourceUserID'];
        $arrr3[$i]['dwTargetUserID'] = $arr[$i]['TargetUserID'];
        $arrr3[$i]['szSourceNickName'] = $arr[$i]['SourceNickName'];
        $arrr3[$i]['szTargetNickName'] = $arr[$i]['TargetNickName'];
        $arrr3[$i]['lScore'] = $arr[$i]['SwapScore'];
        $arrr3[$i]['cbTradeType'] = $arr[$i]['TradeType'];
        $arrr3[$i]['dtTime'] = $arr[$i]['CollectDate']->date;
    }
    $err['message'] = '';
    $err['status'] = true;
    $err['data'] = $arrr3;
    $data = $err;
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
}
sqlsrv_close($conn); 


