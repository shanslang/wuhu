<?php
error_reporting(E_ALL);
require_once 'function.php';
header('Content-Type:application/json; charset=utf-8');         
$config = array (	
		'db' => array(
    // 数据库类型
        'type'        => 'sqlsrv',
        // 服务器地址
        //'hostname'    => '(local)',
        //'hostname'    => '59.37.85.36',
        //'hostname'    => '116.31.119.218',
        'hostname'    => '120.78.149.107',

        // 数据库名
        'database'    => 'THAccountsDB',
        
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
    $hh['message'] = '未获取到信息';
    $hh['status'] = false;
    $hh['data'] = null;
    echo json_encode($hh, JSON_UNESCAPED_UNICODE);
    return;
}
$sign2 = 'FFDFEE8B01CF3D7109DDB4909BCC8266';
$strings = $sign2.$hh;
$md = strtoupper(md5($strings));
if($md != $sign){
    $data['message'] = '签名不正确';
    $data['status'] = false;
    $data['data'] = null;
    writeslog('加密字符串: '.$strings);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

$h2 = json_decode($hh,true);

 // var_dump($h2);
// echo $h2['name'];
$name = iconv("UTF-8",'GBK',$h2['compellation']);
$serverName = $config['db']['hostname'];
$connectionInfo = array( "Database"=>$config['db']['database'], "UID"=>$config['db']['username'], "PWD"=>$config['db']['password']);
$conn = sqlsrv_connect($serverName, $connectionInfo);
$sql0 =  "select [Mobile] FROM [THAccountsDB].[dbo].[InfoMobile](nolock) where [Mobile] = '".$h2['infomobile']."' ";
$isex=sqlsrv_query($conn,$sql0); 
$rows = sqlsrv_fetch_array($isex); 
$reason2 = iconv('UTF-8','GBK','完善资料送50万');
// echo $sql0."<br>row:".$rows."<br>";
// var_dump($isex);
// exit;
if($rows != null){
    $sco = "select Score,[InsureScore] from  [THTreasureDB].[dbo].[GameScoreInfo](nolock) where [UserID] = ".$h2['userid']." ";
    $CurScore = sqlsrv_query($conn,$sco);
    $row = sqlsrv_fetch_array($CurScore);
    $z_g = $row['Score'] + $row['InsureScore'];
    $sql="update [THAccountsDB].[dbo].[AccountsInfo] set Compellation = '".$name."',PassPortID = '".$h2['passportid']."',infoMobile = '".$h2['infomobile']."',[isPerfect] =1 where UserId = ".$h2['userid']." ";
    $up = "update [THTreasureDB].[dbo].[GameScoreInfo] set [InsureScore] = ([InsureScore] + 500000) where [UserID] = ".$h2['userid']." ";
    $del = "delete FROM [THAccountsDB].[dbo].[InfoMobile] where [Mobile] = '".$h2['infomobile']."' ";
    $inse = "insert into [THAccountsDB].[dbo].[InfoPerfect]([Mobile],[UserID],[InputDate]) values('".$h2['infomobile']."',".$h2['userid'].",'".date('Y-m-d H:i:s')."')";
    $ins2 = "insert into [THRecordDB].[dbo].[RecordGrantGameScore]([MasterID],[ClientIP],[CollectDate],[UserID],[KindID],[CurScore],[AddScore],[Reason]) values(1,'0.0.0.0','".date('Y-m-d H:i:s')."',".$h2['userid'].",6,".$z_g.",500000,'".$reason2."')";
    $rs = sqlsrv_query($conn,$sql);
    $rs2 = sqlsrv_rows_affected($rs);
    $ups = sqlsrv_query($conn,$up); 
    $ups2 = sqlsrv_rows_affected($ups);
    $dels = sqlsrv_query($conn,$del);
    $dels2 = sqlsrv_rows_affected($dels); 
    $inses = sqlsrv_query($conn,$inse);
    $inses2 = sqlsrv_rows_affected($inses); 
    $inss = sqlsrv_query($conn,$ins2); 
    $inss2 = sqlsrv_rows_affected($inss);
    if($rs2>0 && $ups2>0 && $dels2>0 && $inses2>0 && $inss2>0){
        $roww = 1;
        sqlsrv_commit($conn);
    }else{
        $roww = 0;
        sqlsrv_rollback($conn);
    }

}else{
    $sql="update [THAccountsDB].[dbo].[AccountsInfo] set Compellation = '".$name."',PassPortID = '".$h2['passportid']."',infoMobile = '".$h2['infomobile']."',[isPerfect] =1 where UserId = ".$h2['userid']." ";
    $rs = sqlsrv_query($conn,$sql);
    $roww = sqlsrv_rows_affected($rs);
}
sqlsrv_close($conn); 
if($roww>0){
    $infos = array();
    $infos['userid'] = $h2['userid'];
    $infos['compellation'] = $h2['compellation']; //真实姓名
    $infos['isperfect'] = 1;   //  是否完善信息
    $infos['infomobile'] = $h2['infomobile'];  //
    $infos['passportid'] = $h2['passportid']; //身份证
    $trues['data'] = $infos;
    $trues['message'] = '';
    $trues['status']  = true;
    echo json_encode($trues);
}else{
    $errinfo['message'] = '完善资料未成功';
    $errinfo['status'] = false;
    $errinfo['data'] = null;
    echo json_encode($errinfo, JSON_UNESCAPED_UNICODE);
}

