<?php
header('Content-Type:application/json; charset=utf-8');  
ini_set('date.timezone','Asia/Shanghai');
error_reporting(0);

require_once 'function.php';
require_once 'config.php';

if(!$_GET['data'] || !$_GET['sign']){
	$data['message'] = '没有传递参数';
	$data['status'] = false; 
	$data['data'] = '';
	writeslog('reward.php 没有传递参数');
	echo json_encode($data,JSON_UNESCAPED_UNICODE);
}

$hh = trim($_GET['data']);
writeslog('reward.php 接受到的参数'.$hh);
$sign = $_GET['sign'];
$key = $config['key'];
$sign2 = strtoupper(md5($key.$hh));
writeslog('reward.php 加密后 '.$sign2);

if($sign == $sign2){
	$parameter = json_decode($hh,true);
	$UserID = $parameter['userid'];
	$AID = $parameter['aid'];
	$sql1 = "select [UserID],[MemberOrder]  FROM [THAccountsDB].[dbo].[AccountsInfo](nolock) where [UserID] = ?";
	$serverName = $config['db']['hostname'];
	$connectionInfo = array("Database"=>$config['db']['database'],"UID"=>$config['db']['username'],"PWD"=>$config['db']['password']);
	$conn = sqlsrv_connect($serverName,$connectionInfo);
	$params = array($UserID);
	$rs = sqlsrv_query($conn,$sql1,$params);
	$row_count = sqlsrv_has_rows( $rs );
	if($row_count>=1){
		$status = TakeReward($UserID,$AID,$conn);
		if($status['ret'] == 0){
			$data = array();
			$data['message'] = '领取成功';
			$data['status'] = true; 
			$inf['pid'] = $status['PID'];
			$inf['pcount'] = $status['PCount'];
			$data['data'] = $inf;
			writeslog('reward.php 领取成功 '.$UserID.' AID '.$AID);
			echo json_encode($data,JSON_UNESCAPED_UNICODE);
		}else{
			$msgs = '';
			if($status['ret']==1){
				$msgs = '无活动记录';
			}else if($status['ret']==2){
				$msgs = '活动已过期';
			}else if($status['ret']==3){
				$msgs = '领取已过期';
			}else if($status['ret']==4){
				$msgs = '已经领过奖励了';
			}else if($status['ret']==5){
				$msgs = '未达到领取标准';
			}else if($status['ret']==6){
				$msgs = '请先退出游戏再领取。';
			}
			$data = array();
			$data['message'] = $msgs;
			$data['status'] = false; 
			$data['data'] = '';
			writeslog('reward.php 领取失败 '.$UserID.' AID '.$AID.' info '.$msgs);
			echo json_encode($data,JSON_UNESCAPED_UNICODE);
		}
		
	}else{
		$data = array();
		$data['message'] = '查询不到结果';
		$data['status'] = false; 
		$data['data'] = '';
		writeslog('reward.php 查询失败 ');
		echo json_encode($data,JSON_UNESCAPED_UNICODE);
	}	
	sqlsrv_close($conn);
}else{
	writeslog('reward.php 签名错误');
	$data = array();
	$data['message'] = '签名错误';
	$data['status'] = false; 
	$data['data'] = '';
	echo json_encode($data,JSON_UNESCAPED_UNICODE);
}
