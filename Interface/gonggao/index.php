<?php
error_reporting(0);
require_once 'function.php';
require_once '../config.php';
// header('Content-Type:application/json; charset=utf-8');
header("Content-type: text/html; charset=utf-8");
date_default_timezone_set('Asia/Shanghai');  

$filename = 'ggao.json';
$cachetime = 300;
if(!file_exists($filename))  //filemtime($filename)
{
    ob_start();
    $serverName = $config['db']['hostname'];
    $connectionInfo = array( "Database"=>$config['db']['database'], "UID"=>$config['db']['username'], "PWD"=>$config['db']['password']);
    $conn = sqlsrv_connect($serverName, $connectionInfo);

    $sql="{call  [THGameScoreDB].[dbo].[PHP_IntGonggao]}"; 
    $rs=sqlsrv_query($conn,$sql); 
    $ret = array();  
    $row = sqlsrv_fetch_array($rs);    
    $ret['statue']=$row['Statue'];
    $ret['info']=iconv('GBK','UTF-8',$row['Info']);
    sqlsrv_close($conn);
    echo json_encode($ret);
    $hj = json_encode($ret);
    // file_put_contents($filename,$array);
    $ll = file_put_contents($filename,$hj);
    writeslog('数据库: '.$ll);
     //清掉内存缓存
    ob_flush();
}else{
    $ms=array();
    $fp = file_get_contents($filename);
    $hh = json_decode($fp, true);
    echo json_encode($hh);
    writeslog('缓存: '.$fp);
}

exit;
