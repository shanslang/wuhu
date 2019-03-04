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

function rankinglist($conn){
    $sql = "{call [THGameScoreDB].[dbo].[PHP_Int_Ranking]}";
    $stmt = sqlsrv_query( $conn, $sql);
    $arr = array();
    while($row = sqlsrv_fetch_array($stmt,SQLSRV_FETCH_ASSOC) ) { 
        $row['NickName'] = iconv('GBK', 'UTF-8', $row['NickName']);
        $row['zcdpt'] = number_format($row['zcdpt']);
        $arr[] = $row;
    }
    sqlsrv_free_stmt( $stmt);
    
    return $arr;
}