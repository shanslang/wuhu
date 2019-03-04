<?php
error_reporting(E_ALL);

require_once 'config.php';
require_once 'function.php';
require_once 'classfun.php';

writeslog(date('Y-m-d H:i:s').' receiveRed 发红包了 ');

$serverName = $config['db']['hostname'];
$connectionInfo = array( "Database"=>$config['db']['database'], "UID"=>$config['db']['username'], "PWD"=>$config['db']['password']);
$conn = sqlsrv_connect( $serverName, $connectionInfo);

$arrs = redgetReady($conn);

if($arrs['sta'] == 1){
    writeslog('receiveRed 红包发放时间已过 ');
    echo '红包发放时间已过 ';
    return;exit;
}else if($arrs['sta'] == 2){
	writeslog('receiveRed 红包雨活动已过期 ');
    return;exit;
}
$arr = $arrs['ret'];
$isam = $arrs['isam'];
$popelenum = count($arr);
$Robotct = $arrs['Robotct'];  // 机器人个数
$andGold = $arrs['andGold'];  // 机器人金币

#总共要发的红包金额，留出一个最大值;
if($arrs['redTotal']>0){
    $total = $arrs['redTotal'];
}else{
    $total = 50000000;
}
if($arrs['redmin']>0){
    $min = $arrs['redmin'];
}else{
    $min = 10000;
}
if($arrs['redmax']>0){
    $max = $arrs['redmax'];
}else{
    $max = 1000000;
}

// -------机器人
$numan = $Robotct;
if($numan > 1){
	if($numan*$arrs['redmaxAnd']<=$andGold){
		for($i=0;$i<$numan;$i++){
			$result_merge[$i] = rand($arrs['redminAnd'],$arrs['redmaxAnd']);
		}
	}else{
		$totalan = $andGold;
		$totalan = $totalan - $arrs['redmaxAnd'];
		$reward = new Reward();
		$result_mergeAnd = $reward->splitReward($totalan, $numan, $arrs['redmaxAnd'] - 1, $arrs['redminAnd']);
		// sort($result_mergeAnd);
		$result_mergeAnd[1] = $result_mergeAnd[1] + $result_mergeAnd[0];
		$result_mergeAnd[0] = $arrs['redmaxAnd'] * 100;
		foreach ($result_mergeAnd as &$v) {
		    $v = floor(floor($v) / 100);
		}
		shuffle($result_mergeAnd);
	}
	
}else if($numan==0){
	$result_mergeAnd = array();
}else if($numan==1){
	$result_mergeAnd[0] = rand($arrs['redminAnd'],$arrs['redmaxAnd']);
}

// -------真人
$num = $popelenum-$Robotct;
if($num > 1){
	if($num*$max<=($total-$andGold)){
		for($i=0;$i<$num;$i++){
			$result_merge[$i] = rand($min,$max);
		}
		writeslog('receiveRed 随机');
	}else{

		$total = $total - $andGold;
		$total = $total - $max;
		writeslog('receiveRed 金币 '.$total.' 人数 = '.$num.' > '.$max.' 小 '.$min);
		$reward = new Reward();

		$result_merge = $reward->splitReward($total, $num, $max - 1, $min);
		// sort($result_merge);
		$result_merge[1] = $result_merge[1] + $result_merge[0];
		$result_merge[0] = $max * 100;
		foreach ($result_merge as &$v) {
		    $v = floor(floor($v) / 100);
		}
	}
}else if($num==0){
	$result_merge = array();
}else if($num==1){
	$result_merge[0] = rand($min,$max);
}
shuffle($result_merge);
// -------

$jbhh = array();
for($i=0;$i<count($result_mergeAnd);$i++){
	if($result_mergeAnd[$i]<0){
		$result_mergeAnd[$i]=0;
	}
	$jbhh[] = $result_mergeAnd[$i];
}
if($num > 0){
	for($i=0;$i<count($result_merge);$i++){
		$jbhh[] = $result_merge[$i];
	}
}

$arr_str = implode(',', $arr);
$jbhh_str= implode(',', $jbhh);

$ups = "{call [QPActivityDB].[dbo].[PHP_upRedGold] (?,?,?)}";
$params = array($isam,$arr_str, $jbhh_str);

$stmtup = sqlsrv_query($conn,$ups, $params);
$row = sqlsrv_fetch_array($stmtup);
if($row['ret'] == 0){
	sqlsrv_free_stmt($stmtup);  
	sqlsrv_close($conn); 
	echo 'true';
}else{
	$msg = iconv('GBK','UTF-8',$row['msg']);
	sqlsrv_close($conn);
	writeslog('receiveRed '.$msg);
}

