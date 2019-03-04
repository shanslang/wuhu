<?php
$ProductList=array(
    '1'=>array(
        '12'=>'120钻石',
        '30'=>'330钻石',
        '60'=>'700钻石',
        '108'=>'1280钻石',
        '198'=>'2500钻石',
        '328'=>'4300钻石',
        '518'=>'7180钻石',
        '698'=>'10800钻石'
        ),
    '2'=>array(
        '0.01'=>'测试商品',
        '6'=>'60000金币',
        '18'=>'210000金币',
        '50'=>'600000金币',
        '98'=>'1250000金币',
        '188'=>'2500000金币',
        '328'=>'4580000金币',
        '518'=>'7580000金币',
        '698'=>'10900000金币'
        ),
    '3'=>array(
        '8'=>'8元特惠礼包',
        '28'=>'28元特惠礼包',
        '40'=>'40元特惠礼包',
        '68'=>'68元特惠礼包'
        ),
    '4'=>array(
        '3'=>'首充1折礼包'
        )
    );

function GetOrderID(){

        $str = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $count = strlen($str);
        $OrderID='WR'.date('YmdHis');
        $str1 = '';
        for($i=0;$i<6;$i++){
            $str1 = $str{rand(0, $count-1)};
            $OrderID = $OrderID.$str1;
        }
        return $OrderID; 

    }

function GetTransOrderID(){

        $str = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $count = strlen($str);
        $OrderID='AliT'.date('YmdHis');
        $str1 = '';
        for($i=0;$i<6;$i++){
            $str1 = $str{rand(0, $count-1)};
            $OrderID = $OrderID.$str1;
        }
        return $OrderID; 

    }

function ProduceOrder($Accounts,$OrderAmount,$CurrencyType,$PlatformID,$OrderID,$IP,$conn){


        $sql="{call  [THTreasureDB].[dbo].[PHP_ProduceOrder] (?,?,?,?,?,?)}"; //存储过程语句

        $params = array($Accounts,$CurrencyType,$OrderAmount,$PlatformID,$IP,$OrderID);              
        $stmt = sqlsrv_query( $conn, $sql ,$params);
        //  $stmt是sqlsrv_query返回的声明资源
        //sqlsrv_fetch_array 返回下一个数据作为数组 .
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

function ProduceTransOrder($OrderID,$dwUserID,$strPassword,$dwAmount,$IP,$conn){


        $sql="{call  [THTreasureDB].[dbo].[NET_PW_WithdrawOrder] (?,?,?,?,?)}"; //存储过程语句
        //writeslog($OrderID.'--'.$dwUserID.'--'.$strPassword.'--'.$dwAmount.'--'.$IP);
        $params = array($OrderID,$dwUserID,$strPassword,$dwAmount,$IP);              
        $stmt = sqlsrv_query( $conn, $sql ,$params);
        //writeslog(json_encode($stmt));
        //  $stmt是sqlsrv_query返回的声明资源
        //sqlsrv_fetch_array 返回下一个数据作为数组 .
        while($row = sqlsrv_fetch_array($stmt) ) { 
            $ret=$row['ret'];
            $PayeeAccount=$row['PayeeAccount'];
            $PayeeRealName=iconv("GB2312", "UTF-8", $row['PayeeRealName']);
            //writeslog(json_encode($row));
        } 
        //writeslog($ret.'--'.$PayeeAccount.'--'.$PayeeRealName);
        sqlsrv_free_stmt( $stmt); //释放$stmt
        $rs=array();
        switch($ret){
            case "0":$rs['msg']='';break;
            case "1":$rs['msg']='您的账户信息有误，请查证后再次尝试！';break;
            case "2":$rs['msg']='您的密码输入有误，请查证后再次尝试！';break;
            case "3":$rs['msg']='您正在游戏房间中，不能进行当前操作！';break;
            case "4":$rs['msg']='您尚未绑定支付宝账号，请先绑定支付宝账号再进行操作!';break;
            case "5":$rs['msg']='您的提现金额大于红包数目，请查证后再次尝试！';break;
            case "6":$rs['msg']='当日提现次数已达上限，请明日再来！';break;
        }
        if($ret == '0'){
            $rs['ret']='0';
            $rs['PayeeAccount']=$PayeeAccount;
            $rs['PayeeRealName']=$PayeeRealName;
        }else{
            $rs['ret']=strval($ret);
        }       
        writeslog(json_encode($rs));
        return $rs;
}


function CompleteOrder($OrderID,$PayAmount,$conn){
        
        $sql="{call  [THTreasureDB].[dbo].[PHP_CompleteOrder] (?,?)}"; //存储过程语句
		file_put_contents('aaa.txt',$sql);
        $params = array($OrderID,$PayAmount);              
        $stmt = sqlsrv_query( $conn, $sql ,$params);
        //  $stmt是sqlsrv_query返回的声明资源
        //sqlsrv_fetch_array 返回下一个数据作为数组 .
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

function CompleteTransOrder($OrderID,$conn){
        
        $sql="{call  [THTreasureDB].[dbo].[NET_PW_WithdrawRecorded] (?)}"; //存储过程语句

        $params = array($OrderID);              
        $stmt = sqlsrv_query( $conn, $sql ,$params);
        //  $stmt是sqlsrv_query返回的声明资源
        //sqlsrv_fetch_array 返回下一个数据作为数组 .
        while($row = sqlsrv_fetch_array($stmt) ) { 
            $ret=$row['ret'];
        } 
        sqlsrv_free_stmt( $stmt); //释放$stmt
        $rs=array();
        switch($ret){
            case "0":$rs['msg']='';break;
            case "11":$rs['msg']='入账订单不存在，请查证后再次尝试！';break;
            case "12":$rs['msg']='该笔订单已经入账，无需重复入账！';break;
        }

        if($ret == '0'){
            $rs['ret']='0';
        }else{
            $rs['ret']=strval($ret);
        }  
        return $rs;     

} 


function FilterStr($str)
{
    $str=str_check($str);
    return addslashes(trim($str));
}

function inject_check($sql_str) {  
    //return eregi('select|insert|update|delete|\'|\/\*|\*|\.\.\/|\.\/|union|into|load_file|outfile', $sql_str);    
    return  preg_match('/select|insert|update|delete|\'|\/\*|\*|\.\.\/|\.\/|union|into|load_file|outfile/i', $sql_str);    
  
} 

function str_check($str){
    if (inject_check($str)) { exit('提交的参数非法！'); }
    if (!get_magic_quotes_gpc()){
        $str=addslashes($str);
    }
    $str=str_replace("_","/_",$str);
    $str=str_replace("%","/%",$str);
    $str=htmldecode($str);  
    return $str;
}

function htmldecode($str) { 

     if (empty ( $str ) || "" == $str) { 
        return ""; 
     } 
     $str = strip_tags ( $str ); 
     $str = htmlspecialchars ( $str ); 
     //$str = nl2br ( $str ); 
     $str = str_replace ( "?", "", $str ); 
     $str = str_replace ( "*", "", $str ); 
     $str = str_replace ( "!", "", $str ); 
     $str = str_replace ( "~", "", $str ); 
     $str = str_replace ( "$", "", $str ); 
     $str = str_replace ( "%", "", $str ); 
     $str = str_replace ( "^", "", $str ); 
     $str = str_replace ( "^", "", $str ); 
     $str = str_replace ( "select", "", $str ); 
     $str = str_replace ( "join", "", $str ); 
     $str = str_replace ( "union", "", $str ); 
     $str = str_replace ( "where", "", $str ); 
     $str = str_replace ( "insert", "", $str ); 
     $str = str_replace ( "delete", "", $str ); 
     $str = str_replace ( "update", "", $str ); 
     $str = str_replace ( "like", "", $str ); 
     $str = str_replace ( "drop", "", $str ); 
     $str = str_replace ( "create", "", $str ); 
     $str = str_replace ( "modify", "", $str ); 
     $str = str_replace ( "rename", "", $str ); 
     $str = str_replace ( "alter", "", $str ); 
     $str = str_replace ( "cast", "", $str );    
     $str = str_replace ( "truncate", "", $str ); 
     $str = str_replace ( "exec", "", $str );   
     $str = str_replace ( ";", "", $str ); 
     //$str = str_replace ( ",", "", $str );
     $str = str_replace ( "=", "", $str );
    
     $filter = array("/\f\r\t\v/" , "/<(\/?)(script|i?frame|object|meta|\?|\%)([^>]*?)>/isU" , "/(<[^>]*)on[a-zA-Z]\s*=([^>]*>)/isU");
     $replace = array(" " , "" , "\\1\\2");
     $str = preg_replace($filter, $replace, $str);
     //过滤影响页面代码
     $filter = array("/\f\r\t\v/" , "/<(\/?)(style|html|body|title|link|\?|\%)([^>]*?)>/isU" , "/(<[^>]*)on[a-zA-Z]\s*=([^>]*>)/isU");
     $replace = array(" " , "&lt;\\1\\2\\3&gt;" , "\\1\\2");
     $str = preg_replace($filter, $replace, $str);  
     return $str;
 
 }
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
function checkword($str){
	writeslog($str);
	if(strlen($str)>20){
		return false;
	}
	$arr=array('and','or','|','=','>','\\','/','<','*','?','!','@','#','$','%','+','-','&',' ','EXE','UNION','SELECT','UPDATE','INSERT','INTO','VALUES','DELETE','FROM','CREATE','ALTER','DROP','TRUNCATE','TABLE','SELECT','DATABASE','select','update','insert','into','values','delete','from','alter','drop','table','exe','union','database','truncate','AND','OR','script','SCRIPT');
	foreach ($arr as $value){
		$check=strstr($str, $value); //搜索一个字符串在另一个字符串中的第一次出现
		if($check==true || !empty($check)){
			return false;
		}
	}
	return $str;
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
