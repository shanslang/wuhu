<?php
error_reporting(0);
header('Content-Type:application/json; charset=utf-8');    

// $filename = "cach.php";
// $cachetime = 5;  
// //判断缓存文件是否存在
// if(!file_exists($filename) || filemtime($filename)+$cachetime<time())  //filemtime($filename)获取文件修改时间，加上定义的缓存时间小于当前时间
// {
//      //开启内存缓存
//     ob_start();

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

// $sign2 = 'FFDFEE8B01CF3D7109DDB4909BCC8266';
// $strings = trim($sign2.$hh);

// $md = strtoupper(md5($strings));
// if($md != $sign){
//     $info['message'] = '签名不正确';
//     $info['status'] = false;
//     $info['data'] = null;
//     echo json_encode($info, JSON_UNESCAPED_UNICODE);
//     return;
// }
$h2 = json_decode($hh,true);
$hh = $h2['kindid'];


$serverName = $config['db']['hostname'];
$connectionInfo = array( "Database"=>$config['db']['database'], "UID"=>$config['db']['username'], "PWD"=>$config['db']['password']);
$conn = sqlsrv_connect($serverName, $connectionInfo);
 $sql="select t1.[GameID] wKindID,t1.[NodeID] wNodeID,t1.[SortID] wSortID,t1.[ServerID] wServerID,t1.[LoginID] wLoginID,t1.[ServerKind] wServerKind,t1.[ServerType] wServerType,t1.[ServerPort] wServerPort,t1.[CellScore] lCellScore,t1.[ServerRule] dwServerRule,
  t1.[DataBaseAddr] dwServerAddr,t1.[ServerName] szServerName,t1.[MinEnterScore] lEnterScore,t1.[MaxPlayer] dwFullCount,t2.OnLineCount dwOnLineCount,t3.AndroidCount dwAndroidCount
  from [THPlatformDB].[dbo].[GameRoomInfo](nolock) t1 
  left join (select [ServerID],count([UserID]) OnLineCount from [THTreasureDB].[dbo].[GameScoreLocker](nolock) group by [ServerID] ) t2 on t1.[ServerID] = t2.[ServerID]
  left join (select [ServerID],count([UserID]) AndroidCount from [THTreasureDB].[dbo].[GameScoreLocker](nolock) where [EnterIP] = '0.0.0.0' group by [ServerID]) t3 
  on t1.[ServerID] = t3.[ServerID] where t1.[GameID] = $hh";
$rs=sqlsrv_query($conn,$sql);   
$array = array();
while($rows = sqlsrv_fetch_array($rs,SQLSRV_FETCH_ASSOC)){
    $array[] = $rows;
}

$rss = count($array);
for($i=0;$i<$rss;$i++){
    $array[$i]['dwOnLineCount'] = $array[$i]['dwOnLineCount']==''?'0':$array[$i]['dwOnLineCount'];
    $array[$i]['dwAndroidCount'] = $array[$i]['dwAndroidCount']==''?'0':$array[$i]['dwAndroidCount'];
}

$array = eval('return '.iconv("gbk","utf-8",var_export($array,true).';'));

if($rs == null){
    $errinfo['message'] = 'userid查询不到结果';
    $errinfo['status'] = false;
    $errinfo['data'] = null;
    $info = $errinfo;
    echo json_encode($info, JSON_UNESCAPED_UNICODE);
    return;
}else{
 //var_dump($row);   
 sqlsrv_close($conn);

         $trues['data'] = $array;
//$trues['data'] = array();
 // for($i=0;$i<$rss;$i++){
 //    if($array[$i]['wKindID'] == $hh){
 //            $trues['data'] = $array[$i];
 //           // print_r($array[$i]);exit;
 //            return;
 //    }
    
 // }
 // print_r($array[$i]);exit;
 $trues['message'] = "";
 $trues['status']  = true;
 $info = $trues;
echo json_encode($info, JSON_UNESCAPED_UNICODE);
}


// $content = ob_get_contents();
     
//     //将获取到的内容存放到缓存文件
// file_put_contents($filename,$content);

//  //清掉内存缓存
// ob_flush();
     
// //echo "######################################";  //测试是否调用了缓存文件，缓存文件不输出这句话
 
// }
// else
// {
//      include($filename);  //如果存在，调用缓存文件
// }
