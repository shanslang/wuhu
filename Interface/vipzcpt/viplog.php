<?php
header("Access-Control-Allow-Origin: *");
error_reporting(E_ALL);

require_once 'function.php';
require_once 'config.php';

header('Content-Type:application/json; charset=utf-8'); 
date_default_timezone_set('Asia/Shanghai');

if(!$_POST['name'] || !$_POST['psw']){
	writeslog('viplog 缺少参数');
	$err['code'] = 300;
	$err['msg'] = '缺少参数';
	echo json_encode($err,JSON_UNESCAPED_UNICODE);
}

$serverName = $config['db']['hostname'];
$connectionInfo = array( "Database"=>$config['db']['database'], "UID"=>$config['db']['username'], "PWD"=>$config['db']['password']);
$conn = sqlsrv_connect( $serverName, $connectionInfo);

$zh = strtoupper(md5($_POST['psw']));
$name = iconv('utf-8','GBK',$_POST['name']);
$res = viplog($name, $zh, $conn);
$res['msg'] = iconv('GBK','utf-8',$res['msg']);
writeslog('viplog '.$_POST['name'].' 状态：'.$res['ret']);
echo json_encode($res,JSON_UNESCAPED_UNICODE);