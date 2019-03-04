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
        'username'    => 'gm',

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
writeslog('perfect加密前 ：'.$strings);
$md = strtoupper(md5($strings));
writeslog('perfect加密后 ：'.$md);
if($md != $sign){
    $data['message'] = '签名不正确';
    $data['status'] = false;
    $data['data'] = null;
    writeslog('加密字符串: '.$strings);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}
$IP = GetIP();
$h2 = json_decode($hh,true);
$h2['ip'] = '00.00.00';
 // var_dump($h2);
// echo $h2['name'];
$name = iconv("UTF-8",'GBK',$h2['compellation']);
$serverName = $config['db']['hostname'];
$connectionInfo = array( "Database"=>$config['db']['database'], "UID"=>$config['db']['username'], "PWD"=>$config['db']['password']);
$conn = sqlsrv_connect($serverName, $connectionInfo);

$status = VerificationCode($h2['userid'],$h2['code'],$h2['infomobile'],$h2['ip'],'',$conn);
writeslog('perfect-- : --'.$status);
$messages = '';
switch ($status) {
    case '1':
        $messages = '您输入的验证短信有误或者已经过期，请仔细确认！';
        break;
    
    case '2':
        $messages = '您的账号已经手机认证过，不能再次绑定！';
        break;
    case '3':
        $messages = '该手机号码已经被其他帐号认证过，请更换手机号码再试！';
        break;
}
if($status > 0){
    $data['message'] = $messages;
    $data['status'] = false;
    $data['data'] = null;
    writeslog('perfect.status : '.$status);
    $hhh = json_encode($status);
    writeslog('array '.$hhh);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}else{
    $goldsum = 200000;  // 送20万
    $roww = perfectinfo($h2['infomobile'],$h2['userid'],$name,$h2['passportid'],$IP,$goldsum,$conn);
    sqlsrv_close($conn); 
    if($roww==0){
        writeslog('perfect完善成功');
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
        writeslog('perfect完善未成功');
        $errinfo['message'] = '完善资料未成功';
        $errinfo['status'] = false;
        $errinfo['data'] = null;
        echo json_encode($errinfo, JSON_UNESCAPED_UNICODE);
    }
}



