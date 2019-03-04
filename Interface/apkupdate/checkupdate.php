<?php

$Md5Code =$_GET['Md5Code'];
$VersionName =$_GET['VersionName'];
$VersionCode =$_GET['VersionCode'];  //版本号
$Platform =$_GET['Platform'];  //平台号 
$Channel =$_GET['Channel'];  //渠道号 
$PackageName =$_GET['PackageName'];  //包名


$v=array("needupdate"=>"false" , "url"=>"http://appzy1.nzbuyu.com/apk/niuzai6014.apk" ,"md5"=>"E4570714757783E6CF17B26FB8E2298F","examine"=>"true","message"=>"找到最新版本，需要更新");
$json=json_encode($v);
header("content-type:application/json; charset=utf-8");
echo $json;  

?>