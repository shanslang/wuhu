<?php

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
                $cip = "无法获取！";
        }
        return $cip;
}
function writeslog($log){ 
    $log_path = 'sql_log/'.date('Y-m-d',time()).'-sql_log.txt';  
    $ts = fopen($log_path,"a+");  
    fputs($ts,date('Y-m-d H:i:s',time()).'  '.$log."\r\n");  
    fclose($ts);  
}  
function object_to_array($obj) {
	$obj = (array)$obj;
	foreach ($obj as $k => $v) {
		if (gettype($v) == 'resource') {
			return;
		}
		if (gettype($v) == 'object' || gettype($v) == 'array') {
			$obj[$k] = (array)object_to_array($v);
		}
	}

	return $obj;
}

function time_diff($timestamp1, $timestamp2)
{
    if ($timestamp2 <= $timestamp1)
    {
        return ['hours'=>0, 'minutes'=>0, 'seconds'=>0];
    }
    $timediff = $timestamp2 - $timestamp1;
    // 时
    $remain = $timediff%86400;
    $hours = intval($remain/3600);
 
    // 分
    $remain = $timediff%3600;
    $mins = intval($remain/60);
    // 秒
    $secs = $remain%60;
 
    $time = ['hours'=>$hours, 'minutes'=>$mins, 'seconds'=>$secs];
 
    return $time;
}

function partakeAction($UserID,$date1,$date2,$date3,$conn){
    $sql="{call  [QPActivityDB].[dbo].[php_PartakeRedRain] (?,?,?,?)}";
    $params = array($UserID,$date1,$date2,$date3);
    $stmt = sqlsrv_query( $conn, $sql ,$params);
    while($row = sqlsrv_fetch_array($stmt) ) { 
        $ret=$row['ret'];
    } 
    sqlsrv_free_stmt( $stmt); //释放$stmt

    if($ret == '0'){
        return 0;
    }else{
        return $ret;
    }  
}

function redgetReady($conn){
    $sql = "{call [QPActivityDB].[dbo].[php_RedGrant]}";
    $stmt = sqlsrv_query($conn,$sql);
    while($row = sqlsrv_fetch_array($stmt) ) { 
        $ret[]=$row['UserID'];
        $isam=$row['isam'];
        $redTotal = $row['redTotal'];
        $redmin = $row['redmin'];
        $redmax = $row['redmax'];
        $sta = $row['ret'];
        if($sta == 0){
            $Robotct = $row['Robotct'];
            $andGold = $row['andGold'];
            $redminAnd = $row['redminAnd'];
            $redmaxAnd = $row['redmaxAnd'];
        }
    } 
    sqlsrv_free_stmt($stmt);
    $arrs['ret'] = $ret;
    $arrs['sta'] = $sta;
    $arrs['isam'] = $isam;
    $arrs['redTotal'] = $redTotal;
    $arrs['redmin'] = $redmin;
    $arrs['redmax'] = $redmax;
    $arrs['Robotct'] = isset($Robotct)?$Robotct:0;
    $arrs['andGold'] = isset($andGold)?$andGold:0;
    $arrs['redminAnd'] = isset($redminAnd)?$redminAnd:0;
    $arrs['redmaxAnd'] = isset($redmaxAnd)?$redmaxAnd:0;
    return $arrs;
}

function lquRed($UserID,$conn){
    $sql = "{call [QPActivityDB].[dbo].[php_ReceiveRed] (?)}";
    $params = array($UserID);
    $stmt = sqlsrv_query( $conn, $sql ,$params);
    while($row = sqlsrv_fetch_array($stmt) ) { 
        // $ret=$row['ret'];
        $arr['ret'] = $row['ret'];
        $arr['RedGold'] = $row['RedGold'];
    } 
    sqlsrv_free_stmt( $stmt); //释放$stmt

    return $arr;
}

function queryC($ampm,$UID, $conn){
    $sql = "{call [QPActivityDB].[dbo].[PHP_queryCount] (?,?)}";
    $params = array($ampm,$UID);
    $stmt = sqlsrv_query( $conn, $sql ,$params);
    $row = sqlsrv_fetch_array($stmt);
    sqlsrv_free_stmt( $stmt); 
    return $row;
}

function activepope($jushu, $conn){
    $sql = "{call [QPActivityDB].[dbo].[PHP_redPople] (?)}";
    $params = array($jushu);
    $stmt = sqlsrv_query( $conn, $sql ,$params);
    $arr = array();
    while($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC) ) { 
        $row['NickName'] = iconv('GBK','utf-8',$row['NickName']);
        $arr[] = $row;
    } 
    sqlsrv_free_stmt( $stmt); 
    return $arr;
}