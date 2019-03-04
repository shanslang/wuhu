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
function recodeTransfer($cbAll,$dwUserID,$stTransferTime,$strErrorDescribe,$conn){  //转帐记录 cbAll=0就是当天否则就是全部
        $sql="{call  [THTreasureDB].[dbo].[GSP_GR_TransferRecord] (?,?,?,?)}"; //存储过程语句
        writeslog('UserID:'.$dwUserID.'-cbAll:-'.$cbAll);
        $params = array($dwUserID,$cbAll,$stTransferTime,$strErrorDescribe);   
        //writeslog("errinfo : ".sqlsrv_errors());
        $par = json_encode($params);
        writeslog('params'.$par);           
        $stmt = sqlsrv_query( $conn, $sql ,$params);
        //writeslog(json_encode($stmt));
        //  $stmt是sqlsrv_query返回的声明资源
        $arr  = array();
        //sqlsrv_fetch_array 返回下一个数据作为数组 .
        while($row = sqlsrv_fetch_array($stmt,SQLSRV_FETCH_ASSOC) ) { 
            //$ret=$row['ret'];
            //writeslog(json_encode($row));
            $arr[] = $row;
        } 
        $arr2 = json_encode($arr);
        writeslog("arr2 : ".$arr2.'--');
        sqlsrv_free_stmt( $stmt); //释放$stmt
        // $rs=array();
        // switch($ret){
        //     case "0":$rs['msg']='';break;
        //     case "1":$rs['msg']='您的账户信息有误，请查证后再次尝试！';break;
        //     case "2":$rs['msg']='您的密码输入有误，请查证后再次尝试！';break;
        //     case "3":$rs['msg']='您正在游戏房间中，不能进行当前操作！';break;
        //     case "4":$rs['msg']='您尚未绑定支付宝账号，请先绑定支付宝账号再进行操作!';break;
        //     case "5":$rs['msg']='您的提现金额大于红包数目，请查证后再次尝试！';break;
        //     case "6":$rs['msg']='当日提现次数已达上限，请明日再来！';break;
        // }
        // if($ret == '0'){
        //     $rs['ret']='0';
        //     $rs['PayeeAccount']=$PayeeAccount;
        //     $rs['PayeeRealName']=$PayeeRealName;
        // }else{
        //     $rs['ret']=strval($ret);
        // }       
        // writeslog('红包订单结果:'.json_encode($rs)."++");
        return $arr;
}
function writeslog($log){ 
    $log_path = 'sql_log/'.date('Y-m-d',time()).'-sql_log.txt';  
    $ts = fopen($log_path,"a+");  
    fputs($ts,date('Y-m-d H:i:s',time()).'  '.$log."\r\n");  
    fclose($ts);  
} 

function querymm($UID, $psw, $conn){
    $sql = "{call [THGameScoreDB].[dbo].[PHP_IntTrans] (?,?)}";
    $params = array($UID, $psw);
    $stmt = sqlsrv_query( $conn, $sql ,$params);
    $row = sqlsrv_fetch_array($stmt);
    sqlsrv_free_stmt( $stmt);
    return $row['ret'];
}