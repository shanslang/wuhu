<?php
error_reporting(E_ALL);
header('Content-Type:application/json; charset=utf-8');

require_once 'config.php';
require_once 'function.php';

if(isset($_GET['data']) && isset($_GET['sign'])){
    $hh = trim($_GET['data']);
    $sign = $_GET['sign'];
    writeslog('query 接收的data : '.$hh);
}else{
    $hh['message'] = '未获取到信息';
    $hh['status'] = false;
    $hh['data'] = '';
    writeslog('query 未获取到信息'.$_GET['data']);
    echo json_encode($hh, JSON_UNESCAPED_UNICODE);
    return;
}

$key = $config['key'];
$strings = $key.$hh;
$md = strtoupper(md5($strings));

if($md != $sign){
    $data['message'] = '签名不正确';
    $data['status'] = false;
    $data['data'] = '';
    writeslog('query 加密后字符串: '.$md);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    return;
}

$h2 = json_decode($hh,true);
$UserID = $h2['userid'];
$takePart = 0;

$serverName = $config['db']['hostname'];
$connectionInfo = array( "Database"=>$config['db']['database'], "UID"=>$config['db']['username'], "PWD"=>$config['db']['password']);
$conn = sqlsrv_connect( $serverName, $connectionInfo);

$acqu = "select top 1 convert(varchar,[starttime],23) starttime,convert(varchar,[endtime],23) endtime FROM [QPActivityDB].[dbo].[RobotCount](nolock) ";
$acq = sqlsrv_query($conn,$acqu);
$acqjg = sqlsrv_fetch_array($acq);
$starttime = $acqjg['starttime'];
$endtime = $acqjg['endtime'];

if(time()<strtotime($starttime)){
	$hh['message'] = '活动暂未开始';
    $hh['status'] = false;
    // $hh['data'] = '';
    $err['data']['starttime'] = $starttime;
	$err['data']['endtime'] = $endtime;
    writeslog('query 未获取到信息'.$_GET['data']);
    echo json_encode($hh, JSON_UNESCAPED_UNICODE);
    return;
}else if(time()>strtotime($endtime)){
	$hh['message'] = '活动暂未开始';
    $hh['status'] = false;
    $err['data']['starttime'] = $starttime;
	$err['data']['endtime'] = $endtime;
    writeslog('query 未获取到信息'.$_GET['data']);
    echo json_encode($hh, JSON_UNESCAPED_UNICODE);
    return;
}

$goldsql = "select top 1 [redTotal],RobotCount FROM [QPActivityDB].[dbo].[RobotCount](nolock)";
$gstmt = sqlsrv_query($conn,$goldsql);
$goldct = sqlsrv_fetch_array($gstmt);
if($goldct['redTotal'] == ''){
	$activeZGold = 50000000;
}
$activeZGold = $goldct['redTotal'];
$isReceive = 0;
$date1 = date('Y-m-d').' 13:50:00';
$date2 = date('Y-m-d').' 15:10:00';
$date3 = date('Y-m-d').' 20:50:00';
$lc = date('Y-m-d',strtotime('+1 days')).' 00:00:00';
$lc2 = date('Y-m-d',strtotime('+1 days'));
$ampm = 0;
// $activeZGold = 50000000; // 红包总金币
$mlcard = 10; // 魅力卡数量
$takePrizeTime = ' 14:00 - 15:00, 21:00 - 22:00';
$signupTime = ' 00:00 - 13:50, 15:10 - 20:50';
if(time()<=strtotime(date('Y-m-d').' 13:50:00')){
	$sql2 = "select top 1 ID FROM FieldRed(nolock) where DayTime = '".date('Y-m-d')."' ";
	$activeStatus = 1; // 报名
	// $signupTime = ' 00:00 ~ 13:50, 15:10-20:50';
	$openPrizeTime = date('Y-m-d').' 13:50:00 ~ '.date('Y-m-d').' 14:00:00';
	// $takePrizeTime = ' 14:00 ~ 15:00, 21:00-22:00';
	$ampm = 1; // 上午
}else if(time()>strtotime(date('Y-m-d').' 13:50:00') && time()<strtotime(date('Y-m-d').' 14:00:00')){
	$activeStatus = 2; // 开奖
	// $signupTime = date('Y-m-d').' 00:00:00 ~ '.date('Y-m-d').' 13:50:00';
	$openPrizeTime = date('Y-m-d').' 13:50:00 ~ '.date('Y-m-d').' 14:00:00';
	// $takePrizeTime = date('Y-m-d').' 14:00:00 ~ '.date('Y-m-d').' 15:00:00';
	$ampm = 1; // 上午
}else if(time()>=strtotime(date('Y-m-d').' 14:00:00') && time()<=strtotime(date('Y-m-d').' 15:00:00')){
	$activeStatus = 3; // 领奖
	// $signupTime = date('Y-m-d').' 00:00:00 ~ '.date('Y-m-d').' 13:50:00';
	$openPrizeTime = date('Y-m-d').' 13:50:00 ~ '.date('Y-m-d').' 14:00:00';
	// $takePrizeTime = date('Y-m-d').' 14:00:00 ~ '.date('Y-m-d').' 15:00:00';
	$ampm = 1; // 上午	
}else if(time()>strtotime(date('Y-m-d').' 15:00:00') && time()<=strtotime(date('Y-m-d').' 15:10:00')){
	$activeStatus = 4; // 等待
	// $signupTime = date('Y-m-d').' 15:10:00 ~ '.date('Y-m-d').' 20:50:00';
	$openPrizeTime = date('Y-m-d').' 20:50:00 ~ '.date('Y-m-d').' 21:00:00';
	// $takePrizeTime = date('Y-m-d').' 21:00:00 ~ '.date('Y-m-d').' 22:00:00';
	$ampm = 0;
}else if(time()>strtotime(date('Y-m-d').' 15:10:00') && time()<=strtotime(date('Y-m-d').' 20:50:00')){
	$activeStatus = 1; // 报名
	// $signupTime = date('Y-m-d').' 15:10:00 ~ '.date('Y-m-d').' 20:50:00';
	$openPrizeTime = date('Y-m-d').' 20:50:00 ~ '.date('Y-m-d').' 21:00:00';
	// $takePrizeTime = date('Y-m-d').' 21:00:00 ~ '.date('Y-m-d').' 22:00:00';
	$ampm = 2; // 下午
}else if(time()>strtotime(date('Y-m-d').' 20:50:00') && time()<strtotime(date('Y-m-d').' 21:00:00')){
	$activeStatus = 2; // 开奖
	// $signupTime = date('Y-m-d').' 15:10:00 ~ '.date('Y-m-d').' 20:50:00';
	$openPrizeTime = date('Y-m-d').' 20:50:00 ~ '.date('Y-m-d').' 21:00:00';
	// $takePrizeTime = date('Y-m-d').' 21:00:00 ~ '.date('Y-m-d').' 22:00:00';
	$ampm = 2; // 下午
}else if(time()>=strtotime(date('Y-m-d').' 21:00:00') && time()<=strtotime(date('Y-m-d').' 22:00:00')){
	$activeStatus = 3; // 领奖
	// $signupTime = date('Y-m-d').' 15:10:00 ~ '.date('Y-m-d').' 20:50:00';
	$openPrizeTime = date('Y-m-d').' 20:50:00 ~ '.date('Y-m-d').' 21:00:00';
	// $takePrizeTime = date('Y-m-d').' 21:00:00 ~ '.date('Y-m-d').' 22:00:00';
	$ampm = 2; // 下午
}else if(time()>strtotime(date('Y-m-d').' 22:00:00') && time()<strtotime($lc)){
	$activeStatus = 4; // 等待
	// $signupTime = $lc.' ~ '.$lc2.' 13:50:00';
	$openPrizeTime = $lc2.' 13:50:00 ~ '.$lc2.' 14:00:00';
	// $takePrizeTime = $lc2.' 14:00:00 ~ '.$lc2.' 15:00:00';
	$ampm = 0;
}
if($ampm == 1){
	$sql = "select top 1 ID FROM [QPActivityDB].[dbo].[FieldRed](nolock) where DayTime = '".date('Y-m-d')."' ";
}else if($ampm == 2){
	$sql = "select top 1 ID FROM [QPActivityDB].[dbo].[FieldRed](nolock) where DayTime = '".date('Y-m-d')."' order by ID desc";
}else{
	$err['message'] = '活动暂未开始';
	$err['status'] = true;
	$err['data']['userid'] = $UserID;
	$err['data']['activestatus'] = 4;
	$err['data']['activecount'] = 0;
	$err['data']['activezgold'] = $activeZGold;
	$err['data']['activeMlcard'] = $mlcard; // 魅力卡
	$err['data']['winning'] = '';
	$err['data']['isReceive'] = $isReceive;
	$err['data']['starttime'] = $starttime;
	$err['data']['endtime'] = $endtime;
	$err['data']['signuptime'] = $signupTime;
	$err['data']['openprizetime'] = $openPrizeTime;
	$err['data']['takeprizetime'] = $takePrizeTime;
	$err['data']['takepart'] = 0;// 是否参加活动
	$data = $err;
	echo json_encode($data, JSON_UNESCAPED_UNICODE);
	return;
}

$rs = sqlsrv_query($conn,$sql);   
$row = sqlsrv_fetch_array($rs);
$jushu = $row['ID'];

$quer = "select ID,isReceive FROM [QPActivityDB].[dbo].[PartakeRedRain](nolock) where [Film] = ".$jushu." and [UserID] = ".$UserID." ";
$qu = sqlsrv_query($conn,$quer);
$isqu = sqlsrv_has_rows( $qu ); 
$ishb = sqlsrv_fetch_array($qu);


$isReceive = $ishb['isReceive'];
if($isqu === true){
	$takePart = 1;
}

if(empty($isqu))
{
	$isReceive = 0;
}


$sql2 = "select a.[NickName],b.[RedGold] FROM [THAccountsDB].[dbo].[AccountsInfo](nolock) a
  left join [QPActivityDB].[dbo].[PartakeRedRain](nolock) b on a.[UserID] = b.[UserID] where b.[Film] = $jushu ";



$stmt = sqlsrv_query($conn,$sql2);

$arr = array();

while( $arrs = sqlsrv_fetch_array( $stmt, SQLSRV_FETCH_ASSOC))  
{   
	$arrs['NickName'] = iconv('GBK','utf-8',$arrs['NickName']);
    $arr[] = $arrs;
}  
$top = 0;
if(time()<strtotime(date('Y-m-d').' 13:53:00') || (time()>strtotime(date('Y-m-d').' 15:10:00') && time() < strtotime(date('Y-m-d').' 20:51:00') )){
	$h = date('H');
	$mi = date('i');
	$mm1 = floor($goldct['RobotCount']/26);
	$mm2 = floor($goldct['RobotCount']/10);
	if($h==0){
		if($mi<=30){
			$top = $mm1;
		}else{
			$top = $mm1*2;
		}
	}else if($h<14){
		if($mi<=30){
			$top = (($h+1)*2-1)*$mm1;
		}else{
			$top = (($h+1)*2)*$mm1;
		}
	}else if($h>=15){
		if($h==15 && $mi<=30){
			$top = $mm2;
		}else if($h==15 && $mi>30){
			$top = $mm2*2;
		}else{
			if($mi<=30){
				$top = (($h-15)*2-1)*$mm2;
			}else{
				$top = (($h-15)*2)*$mm2;
			}
		}
	}
}

$aj = "select top 2 ID FROM [QPActivityDB].[dbo].[PartakeRedRain](nolock) where [IsAndroid] = 1 and [Film] = ".$jushu." ";
$ajj = sqlsrv_query($conn,$aj);
$isaj = sqlsrv_has_rows( $ajj );

if($isaj===true){
	$hshu = count($arr);
}else{
	$hshu = count($arr) + $top;
}

$err['message'] = '';
$err['status'] = true;
$err['data']['userid'] = $UserID;
$err['data']['activestatus'] = $activeStatus;
$err['data']['activecount'] = $hshu;
$err['data']['activezgold'] = $activeZGold;
$err['data']['activeMlcard'] = $mlcard; // 魅力卡
shuffle($arr);
$err['data']['winning'] = $arr;
$err['data']['isReceive'] = $isReceive;
$err['data']['starttime'] = $starttime;
$err['data']['endtime'] = $endtime;
$err['data']['signuptime'] = $signupTime;
$err['data']['openprizetime'] = $openPrizeTime;
$err['data']['takeprizetime'] = $takePrizeTime;
$err['data']['takepart'] = $takePart;// 是否参加活动

$data = $err;

sqlsrv_free_stmt($stmt);
// var_dump($data);
$hh = json_encode($data, JSON_UNESCAPED_UNICODE);
echo json_encode($data, JSON_UNESCAPED_UNICODE);
return;


