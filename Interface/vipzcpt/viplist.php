<?php
header("Access-Control-Allow-Origin: *");
error_reporting(E_ALL);

require_once 'function.php';
require_once 'config.php';

header('Content-Type:application/json; charset=utf-8'); 
date_default_timezone_set('Asia/Shanghai');

$cc = $_POST['cc'];
$name = $_POST['name'];
$filename = $name.$cc.'.json';
$cachetime = 30;  //缓存时间

//判断缓存文件是否存在
if(!file_exists($filename) || filemtime($filename)+$cachetime<time())  //filemtime($filename)获取文件修改时间，加上定义的缓存时间小于当前时间
{
	//开启内存缓存
    ob_start();

    $serverName = $config['db']['hostname'];
    $connectionInfo = array( "Database"=>$config['db']['database'], "UID"=>$config['db']['username'], "PWD"=>$config['db']['password']);
    $conn = sqlsrv_connect($serverName, $connectionInfo); 

    $zh = strtoupper(md5($_POST['psw']));
	$name = iconv('utf-8','GBK',$_POST['name']);

	$cc = $_POST['cc'];
    $array = viplist($name,$cc, $conn);
    $rss = count($array);
    writeslog('viplist '.$_POST['name'].' 数据条数 '.$rss.' cc '.$cc);
    echo json_encode($array,JSON_UNESCAPED_UNICODE);

    $hj = json_encode($array,JSON_UNESCAPED_UNICODE);
    $ll = file_put_contents($filename,$hj);
    writeslog('数据库: '.$ll);
    writeslog($hj);
}else{
	$fp = file_get_contents($filename);
    // $hh2 = json_decode($fp,true);
    // $ro = count($hh2);
    // writeslog('viplist '.$_POST['name'].' 数据条2数 '.$ro);
    // echo json_encode($fp,JSON_UNESCAPED_UNICODE);
    // writeslog('json: '.$fp);
    echo $fp;
}