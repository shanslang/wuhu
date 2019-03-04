<?php
error_reporting(0);
require_once 'config.php';
$t=$_GET['t'];
$n=$_GET['n'];
$ip=GetIP();
$area='1';
if($t>3){
	$t=1;
}
if($n=='null'){
	$n=100;
};

writeslog('download'.$t.'--'.$n.'--'.$ip);
$serverName = $config['db']['hostname'];
$connectionInfo = array( "Database"=>$config['db']['database'], "UID"=>$config['db']['username'], "PWD"=>$config['db']['password']);
$conn = sqlsrv_connect($serverName, $connectionInfo);

$sql="{call  [THRecordDB].[dbo].[PHP_InsertChannelDownload] (?,?,?,?)}"; //存储过程语句
$params = array($n,$t,$ip,$area);              
$stmt = sqlsrv_query( $conn, $sql ,$params);
$row = sqlsrv_fetch_array($stmt);
sqlsrv_free_stmt( $stmt); //释放$stmt



function GetIP(){
    if(!empty($_SERVER["HTTP_CLIENT_IP"])){
            $cip = $_SERVER["HTTP_CLIENT_IP"];
    }
    elseif(!empty($_SERVER["HTTP_X_FORWARDED_FOR"])){
            $cip = $_SERVER["HTTP_X_FORWARDED_FOR"];
    }
    elseif(!empty($_SERVER["REMOTE_ADDR"])){
            $cip = $_SERVER["REMOTE_ADDR"];
    }
    else{
            $cip = "1";
    }
    return $cip;
}
function writeslog($log){ 
    $log_path = 'sql_log/'.date('Y-m-d',time()).'-sql_log.txt';  
    $ts = fopen($log_path,"a+");  
    fputs($ts,date('Y-m-d H:i:s',time()).'  '.$log."\r\n");  
    fclose($ts);  
} 
