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

function curlNe($url,$second=30){
    $ch = curl_init();
        //设置超时
        curl_setopt($ch, CURLOPT_TIMEOUT, $second);
        
        //如果有配置代理这里就设置代理
        curl_setopt($ch,CURLOPT_URL, $url);
        curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,FALSE);
        curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,FALSE);
        //设置header
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        //要求结果为字符串且输出到屏幕上
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
       // curl_setopt($ch, CURLOPT_REFERER, "http://wxzf.nzbuyu.com/");
      //  curl_setopt($ch, CURLOPT_REFERER, "http://pay.nzbuyu.com/");
    
        //post提交方式
        //运行curl
        $data = curl_exec($ch);
        //返回结果
        if($data){
            curl_close($ch);
            writeslog('返回结果'.$data);
            return $data;
        } else { 
            $error = curl_errno($ch);
            curl_close($ch);
            throw new WxPayException("curl出错，错误码:$error");
        }
}

function VerificationCode($paramUserID,$paramCheckCode,$paramTelephone,$paramClientIP,$paramErrorDescribe,$conn){  // 验证手机验证码是否正确


        $sql="{call  [THNativeWebDB].[dbo].[NET_PM_BindPhone] (?,?,?,?,?)}"; //存储过程语句
        $params = array($paramUserID,$paramCheckCode,$paramTelephone,$paramClientIP,$paramErrorDescribe);  
        writeslog('手机验证码 +用户UserID+ '.$paramUserID.' +验证码+ '.$paramCheckCode.' +手机号+ '.$paramTelephone.' +paramClientIP+ '.$paramClientIP.' +paramErrorDescribe+ '.$paramErrorDescribe);
        $stmt = sqlsrv_query( $conn, $sql ,$params);
        $ret = '';
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

function perfectinfo($infomobile,$UID,$name,$passportid,$IP,$goldsum,$conn){
    $sql = "{call [THGameScoreDB].[dbo].[PHP_IntPerfectPer] (?,?,?,?,?,?)}";
    $params = array($infomobile,$UID,$name,$passportid,$IP,$goldsum);
    $stmt = sqlsrv_query( $conn, $sql ,$params);
    $row = sqlsrv_fetch_array($stmt);
    return $row['ret'];
}

function receives($UID,$conn){
    $sql = "{call [QPActivityDB].[dbo].[PHP_singleDoubleReceive] (?)}";
    $params = array($UID);
    $stmt = sqlsrv_query( $conn, $sql ,$params);
    $row = sqlsrv_fetch_array($stmt,SQLSRV_FETCH_ASSOC);
    sqlsrv_free_stmt( $stmt); //释放$stmt
    $row['msg'] = iconv('GBK','UTF-8',$row['msg']);
    return $row;
}

function joinSD($UID,$mv,$number,$conn){
    $sql = "{call [QPActivityDB].[dbo].[PHP_joinSingleDouble] (?,?,?)}";
    $params = array($UID,$mv,$number);
    $stmt = sqlsrv_query( $conn, $sql ,$params);
    $row = sqlsrv_fetch_array($stmt,SQLSRV_FETCH_ASSOC);
    sqlsrv_free_stmt( $stmt); //释放$stmt
    $row['msg'] = iconv('GBK','UTF-8',$row['msg']);
    return $row;
}

function queryc($UID,$conn){
    $sql = "{call [QPActivityDB].[dbo].[PHP_query_singleD] (?)}";
    $params = array($UID);
    $stmt = sqlsrv_query( $conn, $sql ,$params);
    $row = sqlsrv_fetch_array($stmt,SQLSRV_FETCH_ASSOC);
    sqlsrv_free_stmt( $stmt); //释放$stmt
    $row['msg'] = iconv('GBK','UTF-8',$row['msg']);
    return $row;
}

function arrsuser($scene,$conn){
    $sql = "{call [QPActivityDB].[dbo].[PHP_queryPrizeSingleD] (?)}";
    $params = array($scene);
    $stmt = sqlsrv_query( $conn, $sql ,$params);
    $arr = array();
    while ($row=sqlsrv_fetch_array($stmt,SQLSRV_FETCH_ASSOC)) {
        $row['NickName'] = iconv('GBK','UTF-8',$row['NickName']);
        $arr[] = $row;
    }
    sqlsrv_free_stmt( $stmt); //释放$stmt
    return $arr;
}