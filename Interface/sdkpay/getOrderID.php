<?php
/* *
 * 功能：支付宝手机网站支付接口(alipay.trade.wap.pay)接口调试入口页面
 * 版本：2.0
 * 修改日期：2016-11-01
 * 说明：
 * 以下代码只是为了方便商户测试而提供的样例代码，商户可以根据自己网站的需要，按照技术文档编写,并非一定要使用该代码。
 请确保项目文件有可写权限，不然打印不了日志。
 */

header("Content-type: text/html; charset=utf-8");
ini_set('date.timezone','Asia/Shanghai');error_reporting(0);
$return=array();
require_once 'config.php';
require_once 'function.php';


if(!$_GET['Accounts'] || !$_GET['OrderAmount'] || !$_GET['CurrencyType'] || !$_GET['PlatformID']){
    $return['Status']='5';
	$return['OrderID']='';
	echo 'Status='.$return['Status'].'&OrderID='.$return['OrderID'];
    return;
}


$Accounts = iconv("UTF-8","GB2312",FilterStr($_GET['Accounts']));

$IP = GetIP();


$OrderAmount =$_GET['OrderAmount'];
$CurrencyType = intval($_GET['CurrencyType']);
$PlatformID = intval($_GET['PlatformID']);
if($PlatformID<>6000){
	$return['Status']='6';
	$return['OrderID']='';
	echo 'Status='.$return['Status'].'&OrderID='.$return['OrderID'];
    return;
}
$OrderID = GetOrderID();
$return=array();
$serverName = $config['db']['hostname'];
$connectionInfo = array( "Database"=>$config['db']['database'], "UID"=>$config['db']['username'], "PWD"=>$config['db']['password']);
$conn = sqlsrv_connect( $serverName, $connectionInfo);

$status = ProduceOrder($Accounts,$OrderAmount,$CurrencyType,$PlatformID,$OrderID,$IP,$conn);
if($status==0){
	$return['Status']='0';
	$return['OrderID']=$OrderID;
}else{
	$return['Status']=$status;
	$return['OrderID']='';
}
echo 'Status='.$return['Status'].'&OrderID='.$return['OrderID'];
