<?php
error_reporting(0);
require_once 'function.php';
header('Content-Type:application/json; charset=utf-8');     



$config = array (	
		'db' => array(
    // 数据库类型
        'type'        => 'sqlsrv',
        // 服务器地址
        'hostname'    => '120.78.149.107',

        // 数据库名
        'database'    => 'THPlatformDB',
        
        // 数据库用户名
        //'username'    => 'sa',
        'username'    => 'gm',
        //'username'    => 'ht',

        // 数据库密码
        //'password'    => '123456',
        'password'    => 'Cn0bgJ4uulSYLAhQgIBy',
        //'password'    => 'THds158331',

        // 数据库编码默认采用utf8
        'charset'     => 'utf8',
        
        // 数据库表前缀
        'prefix'      => ''
	)
);
date_default_timezone_set('Asia/Shanghai');

if(isset($_GET['data'])){
    $hh = trim($_GET['data']);
    $sign = $_GET['sign'];
}else{
    $hh['message'] = '未获取到userid';
    $hh['status'] = false;
    $hh['data'] = null;
    $info = $hh;
    echo json_encode($info, JSON_UNESCAPED_UNICODE);
    return;
}

$sign2 = 'FFDFEE8B01CF3D7109DDB4909BCC8266';
$strings = trim($sign2.$hh);

$md = strtoupper(md5($strings));
writeslog('加密字符串: '.$strings);
writeslog('加密后字符串: '.$md);
if($md != $sign){
    $info['message'] = '签名不正确';
    $info['status'] = false;
    $info['data'] = null;
    echo json_encode($info, JSON_UNESCAPED_UNICODE);
    return;
}
    $h2 = json_decode($hh,true);
    $hh = $h2['kindid'];
$filename = "test.txt";
$cachetime = 300;  //缓存时间
//判断缓存文件是否存在
if(!file_exists($filename) || filemtime($filename)+$cachetime<time())  //filemtime($filename)获取文件修改时间，加上定义的缓存时间小于当前时间
{
     //开启内存缓存
    ob_start();

    $serverName = $config['db']['hostname'];
    $connectionInfo = array( "Database"=>$config['db']['database'], "UID"=>$config['db']['username'], "PWD"=>$config['db']['password']);
    $conn = sqlsrv_connect($serverName, $connectionInfo); 
    $array = roomlist($conn);
    $rss = count($array);
    for($i=0;$i<$rss;$i++){
        $array[$i]['dwOnLineCount'] = $array[$i]['dwOnLineCount']==''?'0':$array[$i]['dwOnLineCount'];
        $array[$i]['dwAndroidCount'] = $array[$i]['dwAndroidCount']==''?'0':$array[$i]['dwAndroidCount'];
    }
    $array = eval('return '.iconv("gbk","utf-8",var_export($array,true).';'));
// print_r($array);
// exit;
    if($array == null){
        $errinfo['message'] = 'userid查询不到结果';
        $errinfo['status'] = false;
        $errinfo['data'] = null;
        $info = $errinfo;
        writeslog('roomlist - userid查询不到结果'.$hh);
        echo json_encode($info, JSON_UNESCAPED_UNICODE);
        return;
    }else{
        //var_dump($row);   
        sqlsrv_close($conn);
        for($i=0;$i<$rss;$i++){
            if($array[$i]['wKindID'] == $hh){
                    $trues['data'][] = $array[$i];
                   // print_r($array[$i]);exit;
                   
            }
        }
         //$trues['data'] = $array;
         // print_r($trues['data']);exit;
        $trues['message'] = "";
        $trues['status']  = true;
        $info = $trues;
        echo json_encode($info, JSON_UNESCAPED_UNICODE);
    }

    //从内存缓存中获取页面代码
    //$content = ob_get_contents($array);
    //file_put_content($filename, $array);  
         
        //将获取到的内容存放到缓存文件
   // print_r($array);exit;
    $hj = json_encode($array,JSON_UNESCAPED_UNICODE);
    // file_put_contents($filename,$array);
    $ll = file_put_contents($filename,$hj);
   // file_put_contents("test.txt","Hello World. Testing!");
    //echo substr(sprintf('%o', fileperms('roomList/test.txt')), -4);
    writeslog('数据库: '.$ll);
     //清掉内存缓存
    ob_flush();
     
//echo "######################################";  //测试是否调用了缓存文件，缓存文件不输出这句话
 
}
else
{
  $ms=array();
     //include($filename);  //如果存在，调用缓存文件
     $fp = file_get_contents($filename);
     $hh2 = json_decode($fp,true);
     //$hh2 = eval('return '.iconv("gbk","utf-8",var_export($hh2,true).';'));
     $ro = count($hh2);
     for($i=0;$i<$ro;$i++){
//echo $hh2[$i]['wKindID']."--";
        if($hh2[$i]['wKindID'] == $hh){
          //echo $hh2[$i]['wKindID'];
              $ms['data'][] = $hh2[$i];           
        }
     }
     //echo $hh."++";
    $ms['message'] = "";
    $ms['status']  = true;
    $data = $ms;
    echo json_encode($data,JSON_UNESCAPED_UNICODE);
    writeslog('缓存: ');
}
