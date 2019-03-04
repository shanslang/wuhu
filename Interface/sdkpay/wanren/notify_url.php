<?php
/* *
 * 功能：支付宝服务器异步通知页面
 * 版本：2.0
 * 修改日期：2016-11-01
 * 说明：
 * 以下代码只是为了方便商户测试而提供的样例代码，商户可以根据自己网站的需要，按照技术文档编写,并非一定要使用该代码。

 *************************页面功能说明*************************
 * 创建该页面文件时，请留心该页面文件中无任何HTML代码及空格。
 * 该页面不能在本机电脑测试，请到服务器上做测试。请确保外部可以访问该页面。
 * 如果没有收到该页面返回的 success 信息，支付宝会在24小时内按一定的时间策略重发通知
 */
require_once("../function.php");
require_once("../config.php");
date_default_timezone_set('Asia/Shanghai');
//require_once("config.php");
//require_once 'wappay/service/AlipayTradeService.php';
//require_once 'wappay/function.php';
error_reporting(0);
$return=array();
$ip=GetIP();
writeslog($ip.'--'.json_encode($_GET));
/*
if(!isset($_GET['content']) || !isset($_GET['payId']) || !isset($_GET['state']) || !isset($_GET['amount']) || !isset($_GET['modifyTime'])){
	$return['status']='1';
	$return['msg']='参数有误';
	echo json_encode($return);
	exit;
}
*/

$ips=array('116.62.194.184','47.52.115.8');

if(!in_array($ip,$ips)){
	$return['status']='2';
	$return['msg']='IP不允许';
	echo json_encode($return);
	exit;
}

$content=object_to_array(json_decode($_GET['content']));

$OrderID=$content['client_order'];
$PayAmount=$content['amount'];

if($content['status']<>'0'){
	$return['status']='3';
	$return['msg']='支付状态失败';
	writeslog(json_encode($return));
	echo json_encode($return);
	exit;
}
$serverName = $config['db']['hostname'];
$connectionInfo = array( "Database"=>$config['db']['database'], "UID"=>$config['db']['username'], "PWD"=>$config['db']['password']);
$conn = sqlsrv_connect( $serverName, $connectionInfo);
writeslog($content['status'].'--'.$OrderID.'--'.$PayAmount);
$status = CompleteOrder($OrderID,$PayAmount,$conn);
writeslog(json_encode($status));
if($status==0){
	$return['status']='0';
	$return['msg']='';
}else{
	$return['status']=$status;
	$return['msg']='fail';
}
writeslog(json_encode($return));
echo json_encode($return);
exit;