
<?php
 header('Content-Type:application/json; charset=utf-8'); 

 $hh = array('compellation'=>'目43','passportid'=>'6332484848484950Z','infomobile'=>'17392917382','userid'=>'63908');
$zd = json_encode($hh);
// echo "<script>window.location.href='perfect.php?zd=".$zd." ';</script>";
header("location:perfect.php?data=".$zd."&sign=030A297A8C7E3F5FEBF7ACDAAFA39E44");

