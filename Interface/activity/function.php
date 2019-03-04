<?php
function TakeReward($UserID,$AID,$conn){
        
        $sql="{call  [QPActivityDB].[dbo].[proc_TakeReward] (?,?)}"; //存储过程语句
        writeslog($UserID.'---'.$AID);
        $params = array($UserID,$AID);              
        $stmt = sqlsrv_query( $conn, $sql ,$params);
        //  $stmt是sqlsrv_query返回的声明资源
        //sqlsrv_fetch_array 返回下一个数据作为数组 .
        while($row = sqlsrv_fetch_array($stmt) ) { 
            $ret = $row['ret'];
            sqlsrv_free_stmt( $stmt); //释放$stmt
            if($ret == 0){
                $inf['ret']=$row['ret'];
                $inf['PID']=$row['PID'];
                $inf['PCount']=$row['PCount'];
                //return $inf;
            }else{
                $inf['ret'] = $ret;
                //return $inf;
            }
            return $inf;
        } 
          
} 

function hdinfo($UID,$Type, $isUse,$ID, $conn){
    $sql = "{call [THGameScoreDB].[dbo].[PHP_IntActivi_2] (?,?,?,?)}";
    $params = array($UID, $Type, $isUse,$ID);
    $stmt = sqlsrv_query( $conn, $sql ,$params);
    $row = sqlsrv_fetch_array($stmt);
    sqlsrv_free_stmt( $stmt);
    return $row;
}

function getmem($UID, $conn){
    $sql = "{call [THGameScoreDB].[dbo].[PHP_IntQueryMem] (?)}";
    $params = array($UID);
    $stmt = sqlsrv_query( $conn, $sql ,$params);
    $row = sqlsrv_fetch_array($stmt);
    sqlsrv_free_stmt( $stmt);
    return $row;
}

function getActivi($UID, $conn){
    $sql = "{call [THGameScoreDB].[dbo].[PHP_IntActivityAc] (?)}";
    $params = array($UID);
    $stmt = sqlsrv_query( $conn, $sql ,$params);
    $arr = array();
    while($row = sqlsrv_fetch_array($stmt) ) { 
        $arr[] = $row;
    } 
    sqlsrv_free_stmt( $stmt);
    return $arr;
}

function LoadUserActive($UserID,$conn){
        $sql="{call  [QPActivityDB].[dbo].[proc_LoadUserActive] (?)}"; //存储过程语句
        writeslog($UserID);
        $params = array($UserID);              
        $stmt = sqlsrv_query( $conn, $sql ,$params);
        //  $stmt是sqlsrv_query返回的声明资源
        //sqlsrv_fetch_array 返回下一个数据作为数组 .
        $arr = array();
        while($row = sqlsrv_fetch_array($stmt,SQLSRV_FETCH_ASSOC) ) { 
             $arr[] = $row;
        } 
        sqlsrv_free_stmt( $stmt); //释放$stmt
        return $arr;         
} 

function removeEmoji($nickname) {

    $clean_text = "";

    // Match Emoticons
    $regexEmoticons = '/[\x{1F600}-\x{1F64F}]/u';
    $clean_text = preg_replace($regexEmoticons, '&', $nickname);

    // Match Miscellaneous Symbols and Pictographs
    $regexSymbols = '/[\x{1F300}-\x{1F5FF}]/u';
    $clean_text = preg_replace($regexSymbols, '&', $clean_text);

    // Match Transport And Map Symbols
    $regexTransport = '/[\x{1F680}-\x{1F6FF}]/u';
    $clean_text = preg_replace($regexTransport, '&', $clean_text);

    // Match Miscellaneous Symbols
    $regexMisc = '/[\x{2600}-\x{26FF}]/u';
    $clean_text = preg_replace($regexMisc, '&', $clean_text);

    // Match Dingbats
    $regexDingbats = '/[\x{2700}-\x{27BF}]/u';
    $clean_text = preg_replace($regexDingbats, '&', $clean_text);
    
    $clean_text=substr($clean_text,0,31);

    return $clean_text;
}

function ChangeEncodeU2G($s)
{
    return iconv("UTF-8", "GB2312//IGNORE", $s);
}

function CheckToken($Token,$OpenID){
        

        $url='https://api.weixin.qq.com/sns/auth?access_token='.$Token.'&openid='.$OpenID;
        $rs=file_get_contents($url);
        $rs=object2array(json_decode($rs));
        return $rs;
} 

function GetWxUserInfo($Token,$OpenID){
        

        $url='https://api.weixin.qq.com/sns/userinfo?access_token='.$Token.'&openid='.$OpenID;
        $rs=file_get_contents($url);
        $rs=object2array(json_decode($rs));
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
function postXmlCurl($xml, $url, $second = 30)
    {       
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
        curl_setopt($ch, CURLOPT_REFERER, "http://pay.nzbuyu.com/");
    
        //post提交方式
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
        //运行curl
        $data = curl_exec($ch);
        //返回结果
        if($data){
            curl_close($ch);
            return $data;
        } else { 
            $error = curl_errno($ch);
            curl_close($ch);
            throw new WxPayException("curl出错，错误码:$error");
        }
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

function object2array($object) {
  if (is_object($object)) {
    foreach ($object as $key => $value) {
      $array[$key] = $value;
    }
  }
  else {
    $array = $object;
  }
  return $array;
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


function selPlatform($conn){
    $sql = "{call [THPlatformDB].[dbo].[PHP_selPlatform]}";
    $stmt = sqlsrv_query($conn,$sql);
    while($row = sqlsrv_fetch_array($stmt) ) { 
        $ret = $row['ret'];
        $isUse = $row['isUse'];
    }
    return $isUse;
}