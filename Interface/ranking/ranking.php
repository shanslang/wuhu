<?php
header('Content-Type:application/json; charset=utf-8');
date_default_timezone_set('Asia/Shanghai');
error_reporting(E_ALL);

require_once 'function.php';
require_once '../config.php';

if(isset($_GET['data'])){
    $hh = trim($_GET['data']);
    $sign = $_GET['sign'];
}else{
    $hh['message'] = '未获取到userid';
    $hh['status'] = false;
    $hh['data'] = null;
    $info = $hh;
    writeslog('ranking未获取到userid');
    echo json_encode($info, JSON_UNESCAPED_UNICODE);
    return;
} 

$key = $config['key'];
$str_sign = trim($key.$hh);
$my_sign = strtoupper(md5($str_sign));

writeslog('ranking页接收的parameter：'.$hh);
if($my_sign != $sign){
	writeslog('ranking 签名错误 加密字符串：'.$str_sign.' 加密后='.$my_sign);
	$errin = array();
	$errin['message'] = '签名错误';
    $errin['status'] = false;
    $errin['data'] = null;
    echo json_encode($errin, JSON_UNESCAPED_UNICODE);
    return;
}

$serverName = $config['db']['hostname'];
$connectionInfo = array( "Database"=>$config['db']['database'], "UID"=>$config['db']['username'], "PWD"=>$config['db']['password']);
$conn = sqlsrv_connect($serverName, $connectionInfo);

$list = rankinglist($conn);

shuffle($list);
$rows = count($list);
for ($i=0; $i < $rows; $i++) { 
    $list[$i]['ranking'] = $i+1;
}

$data = json_encode($list, JSON_UNESCAPED_UNICODE);
writeslog('ranking 排行榜：'.$data);
$info = array();
$info['message'] = '';
$info['status'] = true;
$info['data'] = $list;
echo json_encode($info, JSON_UNESCAPED_UNICODE);
return;