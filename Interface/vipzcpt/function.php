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

function viplog($name, $psw, $conn){
        $sql = "{call [THGameScoreDB].[dbo].[PHP_IntVipzcPtLogin] (?,?)}";
        $params = array($name, $psw);
        $stmt = sqlsrv_query($conn, $sql, $params);
        $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
        sqlsrv_free_stmt( $stmt);
        return $row;
}

function viplist($name,$cc, $conn){
    $sql = "{call [THGameScoreDB].[dbo].[PHP_IntViplist] (?,?)}";
    $params = array($name,$cc);
    $stmt = sqlsrv_query($conn, $sql, $params);
    $arr = array();
    while($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)){  
        // $row['NickName'] = substr(iconv('GBK','utf-8',$row['NickName']),0,2);
        $row['remainder'] = number_format($row['remainder']);
        $row['z_amout'] = number_format($row['z_amout']);
        $row['z_ptzr'] = number_format($row['z_ptzr']);
        $arr[] = $row;
    }
    sqlsrv_free_stmt( $stmt);
    return $arr;
}
