<?php
error_reporting(E_ALL);
require_once 'function.php';
header('Content-Type:application/json; charset=utf-8');               
$config = array (	
		'db' => array(
    // 数据库类型
        'type'        => 'sqlsrv',
        // 服务器地址
        'hostname'    => '120.78.149.107',

        // 数据库名
        'database'    => 'THAccountsDB',
        
        // 数据库用户名
        //'username'    => 'sa',
        'username'    => 'gm',

        // 数据库密码
        //'password'    => '123456',
        'password'    => 'Cn0bgJ4uulSYLAhQgIBy',

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
    writeslog('logins 未获取到userid');
    echo json_encode($info, JSON_UNESCAPED_UNICODE);
    return;
}

$sign2 = 'FFDFEE8B01CF3D7109DDB4909BCC8266';
$strings = trim($sign2.$hh);

$md = strtoupper(md5($strings));
if($md != $sign){
    $info['message'] = '签名不正确';
    writeslog('logins '.$hh.'签名不正确'.$md);
    $info['status'] = false;
    $info['data'] = null;
    echo json_encode($info, JSON_UNESCAPED_UNICODE);
    return;
}
writeslog('logins '.$hh);
$h2 = json_decode($hh,true);
$hh = $h2['userid'];
$deviceName = isset($h2['deviceName']) ? $h2['deviceName'] : '';
$deviceUniqueIdentifier = isset($h2['deviceUniqueIdentifier']) ? $h2['deviceUniqueIdentifier'] : '';
$graphicsDeviceID = isset($h2['graphicsDeviceID']) ? $h2['graphicsDeviceID'] : 0;
$graphicsDeviceName = isset($h2['graphicsDeviceName']) ? $h2['graphicsDeviceName'] : '';
$processorType = isset($h2['processorType']) ? $h2['processorType'] : '';
$systemMemorySize = isset($h2['systemMemorySize']) ? $h2['systemMemorySize'] : 0;
$operatingSystem = isset($h2['operatingSystem']) ? $h2['operatingSystem'] : '';
$deviceType = isset($h2['deviceType']) ? $h2['deviceType'] : 0; // 0=PC; 16=ANDROID ;  32=IOS

$serverName = $config['db']['hostname'];
$connectionInfo = array( "Database"=>$config['db']['database'], "UID"=>$config['db']['username'], "PWD"=>$config['db']['password']);
$conn = sqlsrv_connect($serverName, $connectionInfo);
$sqls = "{call  [THGameScoreDB].[dbo].[PHP_IntPerfectLogWu] (?,?,?,?,?,?,?,?,?)}";
$params = array($hh,$deviceName,$deviceUniqueIdentifier,$graphicsDeviceID,$graphicsDeviceName,$processorType,$systemMemorySize,$operatingSystem,$deviceType);
// echo json_encode($params);
$rs2 = sqlsrv_query($conn,$sqls, $params);
$row = sqlsrv_fetch_array($rs2);
 
if($row['ret'] == 1){
    $errinfo['message'] = 'userid查询不到结果';
    $errinfo['status'] = false;
    $errinfo['data'] = null;
    $info = $errinfo;
    writeslog('logins '.$hh.'查询不到结果');
    echo json_encode($info, JSON_UNESCAPED_UNICODE);
    return;
}else{ 
 $row['Compellation'] = iconv('GBK','UTF-8',$row['Compellation']);
 $infos = array();
 $infos['userid'] = $row['UserID'];
 $infos['compellation'] = ($row['Compellation'])?$row['Compellation']:""; //真实姓名
 $infos['isperfect'] = ($row['isPerfect'])?$row['isPerfect']:0;   //  是否完善信息
if($row['infoMobile']){
    $row['infoMobile'] = substr_replace($row['infoMobile'],'*****',3,5);
}
if($row['PassPortID']){
    $row['PassPortID'] = substr($row['PassPortID'],0,3).'*********'.substr($row['PassPortID'],-3);
}
 $infos['infomobile'] = ($row['infoMobile'])?$row['infoMobile']:'';  //
 $infos['passportid'] = $row['PassPortID']; //身份证
 $infos['conversion'] = $row['conversion']; // 领取绑定渠道奖励的标识，1刚刚就是领取了，0 就是刚刚没领取
$infos['bs'] = $row['bs']; //  1   -- 已领取救济金次数用完;0  -- 未领取救济金;2  不是微信登陆,3 大于1000
$infos['goldcoin'] = $row['StatusValue'];
writeslog('logins '.$row['UserID'].'完善状态'.$row['isPerfect'].' 设备='.$deviceType);
sqlsrv_close($conn);

$trues['data'] = $infos;
$trues['message'] = "";
$trues['status']  = true;
$info = $trues;
echo json_encode($info, JSON_UNESCAPED_UNICODE);
}
