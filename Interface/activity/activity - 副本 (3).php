<?php
error_reporting(0);
header('Content-Type:application/json; charset=utf-8');  
date_default_timezone_set('Asia/Shanghai');

require_once 'function.php';
require_once 'config.php';

if(!$_GET['data'] || !$_GET['sign']){
    $data['message'] = '没有传递参数';
    $data['status'] = false; 
    $data['data'] = '';
    writeslog('activvit.php 没有传递参数');
    echo json_encode($data,JSON_UNESCAPED_UNICODE);
}



$hh = trim($_GET['data']);
writeslog('activvit.php 接受到的参数'.$hh);
$sign = $_GET['sign'];
$key = $config['key'];
writeslog('----- '.$key.$hh);
$sign2 = strtoupper(md5($key.$hh));
writeslog('activvit.php 加密后 '.$sign2);

if($sign == $sign2){
    $parameter = json_decode($hh,true);
    $UserID = $parameter['userid'];
    $sql1 = "select [UserID],[MemberOrder]  FROM [THAccountsDB].[dbo].[AccountsInfo](nolock) where [UserID] = ?";
    $serverName = $config['db']['hostname'];
    $connectionInfo = array("Database"=>$config['db']['database'],"UID"=>$config['db']['username'],"PWD"=>$config['db']['password']);
    $conn = sqlsrv_connect($serverName,$connectionInfo);

    $isuse = selPlatform($conn);

    $status = LoadUserActive($UserID,$conn);

    $para = array($UserID);
    $rs1 = sqlsrv_query($conn,$sql1,$para);
    $rss = sqlsrv_fetch_array($rs1,SQLSRV_FETCH_ASSOC);

    // 红包雨，幸运转盘
    if($rss['MemberOrder'] >0){
        $huod = "select ID,Title,ImgUrl,convert(varchar,startTime,120) startTime,convert(varchar,endTime,120) endTime,AMask from [QPActivityDB].[dbo].[activeInfo](nolock) where [startTime] <= '".date('Y-m-d H:i:s')."' and [endTime] >= '".date('Y-m-d H:i:s')."' and (AMask = 0 or  AMask = 2)";
    }else{
        $huod = "select ID,Title,ImgUrl,convert(varchar,startTime,120) startTime,convert(varchar,endTime,120) endTime,AMask from [QPActivityDB].[dbo].[activeInfo](nolock) where [startTime] <= '".date('Y-m-d H:i:s')."' and [endTime] >= '".date('Y-m-d H:i:s')."' and (AMask = 0 or  AMask = 1)";
    }
    $jghuo = sqlsrv_query($conn,$huod);
    $arr2_2 = array();
    while($row = sqlsrv_fetch_array($jghuo,SQLSRV_FETCH_ASSOC) ){
        $arr2_2[] = $row;
    }

    if(!isset($status[0]['AID']) && empty($arr2_2)){
       
        if($rss['MemberOrder'] >0){
            $rs2 = "select min(PValue) as NextValue,min(PID) PID,min(PCount) PCount,min([QPActivityDB].[dbo].[ARewards].AID) AID,min(convert(varchar,t2.[StartTime],120)) [StartTime],min(convert(varchar,t2.[EndTime],120)) [EndTime],min(convert(varchar,t2.[LastModify],120)) [LastModify],
  min(t3.LastTakeValue) LastTakeValue
   from [QPActivityDB].[dbo].[ARewards] 
   left join [QPActivityDB].[dbo].[AInfo] t2 on [QPActivityDB].[dbo].[ARewards].AID = t2.ID
   left join (select [AID],[LastTakeValue] from [QPActivityDB].[dbo].[UserAProgress] where [UID] = ".$UserID." and isUse = $isuse) t3 on [QPActivityDB].[dbo].[ARewards].AID = t3.[AID]
  where [QPActivityDB].[dbo].[ARewards].AID in (select [ID] from [QPActivityDB].[dbo].[AInfo](nolock) where [AMask] = 2 and isUse = $isuse) and [QPActivityDB].[dbo].[ARewards].isUse = $isuse and t2.isUse = $isuse";
        }else{
            $rs2 = "select min(PValue) as NextValue,min(PID) PID,min(PCount) PCount,min([QPActivityDB].[dbo].[ARewards].AID) AID,min(convert(varchar,t2.[StartTime],120)) [StartTime],min(convert(varchar,t2.[EndTime],120)) [EndTime],min(convert(varchar,t2.[LastModify],120)) [LastModify],
  min(t3.LastTakeValue) LastTakeValue
   from [QPActivityDB].[dbo].[ARewards] 
   left join [QPActivityDB].[dbo].[AInfo] t2 on [QPActivityDB].[dbo].[ARewards].AID = t2.ID
   left join (select [AID],[LastTakeValue] from [QPActivityDB].[dbo].[UserAProgress] where [UID] = ".$UserID." and isUse = $isuse) t3 on [QPActivityDB].[dbo].[ARewards].AID = t3.[AID]
  where [QPActivityDB].[dbo].[ARewards].AID in (select [ID] from [QPActivityDB].[dbo].[AInfo](nolock) where [AMask] = 1 and isUse = $isuse) and [QPActivityDB].[dbo].[ARewards].isUse = $isuse and t2.isUse = $isuse";
        }
        $jg2 = sqlsrv_query($conn,$rs2);
        $arr2 = sqlsrv_fetch_array($jg2,SQLSRV_FETCH_ASSOC);

          if(!isset($status[0]['ATitle']) && empty($arr2_2)){
            $hd = "select t1.[ATitle],t1.[AType],t1.[AMask],t2.[URL] url1,t3.[URL] url2,t1.[CanMutiple] from [QPActivityDB].[dbo].[AInfo](nolock) t1
  left join [QPActivityDB].[dbo].[AImages](nolock) t2 on t1.[ATitleBarImage] = t2.[ID]
  left join [QPActivityDB].[dbo].[AImages](nolock) t3 on t1.[AContentImage] = t3.[ID] where t1.[ID] = ? and t1.isUse = $isuse";
            $cs = array($arr2['AID']);
            $jghd = sqlsrv_query($conn,$hd,$cs);
            $jgg = sqlsrv_fetch_array($jghd,SQLSRV_FETCH_ASSOC);
            //var_dump($jgg);
            $ins[0]['ATitle'] = iconv('GBK', 'utf-8', $jgg['ATitle']);
            $ins[0]['AType'] = isset($jgg['AType'])?$jgg['AType'] : 0;
            $ins[0]['AMask'] = isset($jgg['AMask'])?$jgg['AMask'] : 0;
            $ins[0]['url1'] = $jgg['url1'];
            $ins[0]['url2'] = $jgg['url2'];
            $ins[0]['CanMutiple'] = $jgg['CanMutiple'];
            $ins[0]['OverDue'] = true;
            writeslog('activvit.php 活动已过期');
              
          }else{
               $ins[0]['ATitle'] = iconv('GBK', 'utf-8', $status[0]['ATitle']);
               $ins[0]['AType'] = isset($status[0]['AType'])?$status[0]['AType'] : 0;
               $ins[0]['AMask'] = isset($status[0]['AMask'])?$status[0]['AMask'] : 0;
               $ins[0]['url1'] = $status[0]['url1'];
               $ins[0]['url2'] = $status[0]['url2'];
               $ins[0]['CanMutiple'] = $status[0]['CanMutiple'];
               $ins[0]['OverDue'] = false;
          }
           
           $ins[0]['AID'] = $arr2['AID'];
           $ins[0]['AValue'] = isset($status[0]['AValue'])?$status[0]['AValue']:0;
           $ins[0]['NextValue'] = $arr2['NextValue'];
           $ins[0]['PValue'] = $arr2['NextValue'];
           $ins[0]['pid'] = $arr2['PID'];
           $ins[0]['pcount'] = $arr2['PCount'];
           $ins[0]['StartTime'] = $arr2['StartTime'];
           $ins[0]['EndTime'] = $arr2['EndTime'];
           $ins[0]['LastTakeValue'] = isset($status[0]['LastTakeValue'])?$status[0]['LastTakeValue']:0;

        writeslog('activity.php 查询不到数据 '.$status[0]['StartTime']->date.' -- '.$status[0]['EndTime']->date.' ++ '.$status[0]['url2']);
        $data = array();
        $data['message'] = '';
        $data['status'] = true; 
        $data['data'] = $ins;
        echo json_encode($data,JSON_UNESCAPED_UNICODE);
    }else if(!isset($status[0]['AID']) && !empty($arr2_2)){
        $day2 = date('d',time());
        writeslog('activity.php 查询成功 '.json_encode($status,JSON_UNESCAPED_UNICODE));
        $data = array();
        $data['message'] = '';
        $data['status'] = true; 
        $hs = count($arr2_2);
        // echo $hs;
        for ($i=0; $i < $hs; $i++) { 
           $ins[$i]['ATitle'] = iconv('GBK', 'utf-8', $arr2_2[$i]['Title']);
           $ins[$i]['AType'] = $arr2_2[$i]['ID'];
           $ins[$i]['AMask'] = isset($arr2_2[$i]['AMask'])?$arr2_2[$i]['AMask'] : 0;
           $ins[$i]['url1'] = $arr2_2[$i]['ImgUrl'];
           $ins[$i]['url2'] = $arr2_2[$i]['ImgUrl'];
           $ins[$i]['AID'] = $arr2_2[$i]['ID'];
           $ins[$i]['AValue'] = 0;
           $ins[$i]['NextValue'] = 0;
           $ins[$i]['PValue'] = 0;
           $ins[$i]['pid'] = 0;
           $ins[$i]['pcount'] = 0;
           $ins[$i]['StartTime'] = $arr2_2[$i]['startTime'];
           $ins[$i]['EndTime'] = $arr2_2[$i]['endTime'];
           $ins[$i]['CanMutiple'] = 0;
           $ins[$i]['LastTakeValue'] = 0;
           $ins[$i]['OverDue'] = false;
           
        }
        $data['data'] = $ins;
        echo json_encode($data,JSON_UNESCAPED_UNICODE);
    }else{
          $day2 = date('d',time());
        writeslog('activity.php 查询成功 '.json_encode($status,JSON_UNESCAPED_UNICODE));
        $data = array();
        $data['message'] = '';
        $data['status'] = true; 
        $hs = count($status);
        for ($i=0; $i < $hs; $i++) { 
           $ins[$i]['ATitle'] = iconv('GBK', 'utf-8', $status[$i]['ATitle']);
           $ins[$i]['AType'] = isset($status[$i]['AType'])?$status[$i]['AType'] : 0;
           $ins[$i]['AMask'] = isset($status[$i]['AMask'])?$status[$i]['AMask'] : 0;
           $ins[$i]['url1'] = $status[$i]['url1'];
           $ins[$i]['url2'] = $status[$i]['url2'];
           $ins[$i]['AID'] = isset($status[$i]['AID'])?$status[$i]['AID'] : 0;
           $ins[$i]['AValue'] = isset($status[$i]['AValue'])?$status[$i]['AValue'] : 0;
           $ins[$i]['NextValue'] = isset($status[$i]['NextValue'])?$status[$i]['NextValue'] : 0;
           $ins[$i]['PValue'] = isset($status[$i]['PValue'])?$status[$i]['PValue'] : 0;
           $ins[$i]['pid'] = isset($status[$i]['pid'])?$status[$i]['pid'] : 0;
           $ins[$i]['pcount'] = isset($status[$i]['pcount'])?$status[$i]['pcount'] : 0;
           $ins[$i]['StartTime'] = $status[$i]['StartTime']->date;
           $ins[$i]['EndTime'] = $status[$i]['EndTime']->date;
           $ins[$i]['CanMutiple'] = $status[$i]['CanMutiple'];
           $ins[$i]['LastTakeValue'] = $status[$i]['LastTakeValue'];
           $ins[$i]['OverDue'] = false;
        }
        for($i=0;$i<count($arr2_2);$i++){
          $ins22[$i]['ATitle'] = iconv('GBK', 'utf-8', $arr2_2[$i]['Title']);
           $ins22[$i]['AType'] = $arr2_2[$i]['ID'];
           $ins22[$i]['AMask'] = isset($arr2_2[$i]['AMask'])?$arr2_2[$i]['AMask'] : 0;
           $ins22[$i]['url1'] = $arr2_2[$i]['ImgUrl'];
           $ins22[$i]['url2'] = $arr2_2[$i]['ImgUrl'];
           $ins22[$i]['AID'] = $arr2_2[$i]['ID'];
           $ins22[$i]['AValue'] = 0;
           $ins22[$i]['NextValue'] = 0;
           $ins22[$i]['PValue'] = 0;
           $ins22[$i]['pid'] = 0;
           $ins22[$i]['pcount'] = 0;
           $ins22[$i]['StartTime'] = $arr2_2[$i]['startTime'];
           $ins22[$i]['EndTime'] = $arr2_2[$i]['endTime'];
           $ins22[$i]['CanMutiple'] = 0;
           $ins22[$i]['LastTakeValue'] = 0;
           $ins22[$i]['OverDue'] = false;
           $ins[] = $ins22[$i];
        }
        $data['data'] = $ins;
        echo json_encode($data,JSON_UNESCAPED_UNICODE);
    }
   
    sqlsrv_close($conn);
}else{
    writeslog('activvit.php 签名错误');
    $data = array();
    $data['message'] = '签名错误';
    $data['status'] = false; 
    $data['data'] = '';
    echo json_encode($data,JSON_UNESCAPED_UNICODE);
}
