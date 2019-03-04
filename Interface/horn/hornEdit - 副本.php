<?php
error_reporting(0);
header('Content-Type:application/json; charset=utf-8');    
date_default_timezone_set('Asia/Shanghai');

require_once 'function.php';
require_once 'config.php';

if(isset($_GET['data']) && isset($_GET['sign'])){
    $hh = trim($_GET['data']);
    $sign = $_GET['sign'];
    writeslog('接收的data : '.$hh);
}else{
    $hh['message'] = '未获取到信息';
    $hh['status'] = false;
    $hh['data'] = '';
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
    writeslog('加密后字符串: '.$md);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    return;
}

$h2 = json_decode($hh,true);
$UserID = $h2['userid'];
$MsgContent =  iconv('utf-8','GBK',$h2['msgcontent']);

$serverName = $config['db']['hostname'];
$connectionInfo = array( "Database"=>$config['db']['database'], "UID"=>$config['db']['username'], "PWD"=>$config['db']['password']);
$conn = sqlsrv_connect($serverName, $connectionInfo);

$sql = "select t1.[Score],t1.[InsureScore] FROM [THTreasureDB].[dbo].[GameScoreInfo](nolock) t1 
  left join [THAccountsDB].[dbo].[AccountsInfo](nolock) t2 on t1.[UserID] = t2.[UserID]
  where t2.[MemberOrder] > 0 and t1.[UserID] = ? ";
$parames = array($UserID);
$res = sqlsrv_query($conn,$sql,$parames);
$rs = sqlsrv_has_rows($res);

if($rs == false){
    $err['message'] = 'userid错误或不是VIP';
    $err['status'] = false;
    $err['data'] = '';
    $data = $err;
    writeslog('userid错误或不是VIP: '.$UserID);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    return;
}

$sqll3 = "select [ID],convert(varchar,[DisplayTime],120) DisplayTime,[MsgContent] FROM [THPlatformDB].[dbo].[Horn](nolock) where [UserID] = ?";
$parames = array($UserID);
$ress3 = sqlsrv_query($conn,$sqll3,$parames);
$rss3 = sqlsrv_has_rows($ress3);
if($rss3 != false){
    $arrr = sqlsrv_fetch_array( $ress3, SQLSRV_FETCH_ASSOC);
    if($arrr['DisplayTime'] != '' && (time()-strtotime($arrr['DisplayTime']))/60<3){
        $errs = array();
        $errs['message'] = '未到3分钟';
        $errs['status'] = false;
        $errs['data']['userid'] = $UserID;
        $errs['data']['msgcontent'] = iconv('GBK','utf-8',$arrr['MsgContent']);
        $errs['data']['displaytime'] = $arrr['DisplayTime'];
        writeslog('未到一个小时: '.$UserID);
        echo json_encode($errs, JSON_UNESCAPED_UNICODE);
        return;
        exit;
    }
}

$arr = sqlsrv_fetch_array( $res, SQLSRV_FETCH_ASSOC);
$InsureScore = $arr['InsureScore'];
if($InsureScore < 200000){
	$err['message'] = '银行余额不足';
    $err['status'] = false;
    $err['data'] = '';
    $data = $err;
    writeslog('银行余额不足: '.$UserID.' InsureScore ='.$InsureScore);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    return;
}
$hs = 1;
$sql2 = "update [THTreasureDB].[dbo].[GameScoreInfo] set [InsureScore] = [InsureScore] - 200000 where [UserID] = ?";
$IP = GetIP();
$LastModify = date('Y-m-d H:i:s');
$z_g = $arr['InsureScore']+$arr['Score'];
$reason = iconv('utf-8', 'GBK', '发喇叭-');
$add ="insert into [THRecordDB].[dbo].[RecordGrantGameScore]([MasterID],[ClientIP],[CollectDate],[UserID],[KindID],[CurScore],[AddScore],[Reason]) values(0,?,?,?,6,?,-200000,?)";
$addpara = array($IP,$LastModify,$UserID,$z_g,$reason);
$addstmt = sqlsrv_prepare( $conn, $add, $addpara);
sqlsrv_execute( $addstmt);

$sql3 = "select [ID],MsgContent,[Status] FROM [THPlatformDB].[dbo].[Horn](nolock) where [UserID] = ?";
$params2 = array($UserID);
$stmt = sqlsrv_prepare( $conn, $sql2, $params2); 
if( $stmt ){}else{
	print_r( sqlsrv_errors());
}
if( sqlsrv_execute( $stmt))  
{  
	$res3 = sqlsrv_query($conn,$sql3,$parames);
	$rs3 = sqlsrv_has_rows($res3);
	if($rs3 != false){  // horn表中存在则修改
        $LastModify = date('Y-m-d H:i:s');
        $jg3 = sqlsrv_fetch_array($res3,SQLSRV_FETCH_ASSOC);
        if($jg3['MsgContent'] == $MsgContent && $jg3['Status'] == 1){
            $hs = 22;
            $insert = "update [THPlatformDB].[dbo].[Horn] set [DisplayTime] = ?,Counts = Counts +1 where [UserID] = ?";
            $params3 = array($LastModify,$UserID);
        }else{
            $insert = "update [THPlatformDB].[dbo].[Horn] set [MsgContent] = ?,[LastModify]=?,[Status]= 0 where [UserID] = ?";
            $params3 = array($MsgContent,$LastModify,$UserID);  
        }	
	}else{
		$insert = "insert into [THPlatformDB].[dbo].[Horn]([MsgType],[clrRed],[clrBlue],[clrGreen],[TextHeight],[SpaceTime],[MsgContent],[LastModify],[Status]
      ,[UserID]) values(4,0,0,0,12,60,?,?,0,?)";
    $LastModify = date('Y-m-d H:i:s');
    $params3 = array($MsgContent,$LastModify,$UserID);
	}
	
    $stmt3 = sqlsrv_prepare( $conn, $insert, $params3); 
    if(sqlsrv_execute( $stmt3)){
        if($hs == 22){
            $LastModify = date('Y-m-d H:i:s');
            $endtime =date('Y-m-d H:i:s',strtotime('+4 min'));
            //$endtime =date('Y-m-d H:i:s',strtotime('+30 second'));
            $notice = "insert into [THPlatformDB].[dbo].[Notice]([MsgType],[clrRed],[clrBlue],[clrGreen],[TextHeight],[StartTime],[EndTime],[SpaceTime],[MsgContent],[LastModify]) values(10,0,0,0,12,?,?,3,?,?)";
            $nopara = array($LastModify,$endtime,$MsgContent,$LastModify);
            $stmtno = sqlsrv_prepare( $conn, $notice, $nopara); 
            if(sqlsrv_execute( $stmtno)){}else{writeslog('添加到notice失败 '.$UserID);};
        }
    	$err['message'] = '';
	    $err['status'] = true;
	    $err['data']['userid'] = $UserID;
	    $err['data']['msgcontent'] = $h2['msgcontent'];
	    $data = $err;
	    echo json_encode($data, JSON_UNESCAPED_UNICODE);
	    return;
    }else{
    	$err['message'] = '错误';
	    $err['status'] = false;
	    $err['data'] = '';
	    $data = $err;
	    echo json_encode($data, JSON_UNESCAPED_UNICODE);
	    return;
    }
}else{
	$err['message'] = '扣钱错误';
	$err['status'] = false;
	$err['data'] = '';
	$data = $err;
	echo json_encode($data, JSON_UNESCAPED_UNICODE);
	return;
}