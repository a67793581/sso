<?php
header('Content-Type:text/html; charset=utf-8');

require_once 'Server_core.php';
$core = new Server_core();

//退出登录操作
if(isset($_GET['logout'])){
    //删除登录中心的cookie数据
    setcookie('sign','',-300);
    $core->logout();
}

//登录操作
else if(isset($_POST['username']) && isset($_POST['password']) && isset($_GET['callback'])){
    //自定义校验
    if(true){
        //保存用户信息到cookie
        $info = array('sign'=>$_POST['username']);
        foreach($info as $key=>$val){
            setcookie($key,$val,0,'/');
        }
        $info = $core->for_encryption($info);
        $core->login($info);
    }
}

//根据code返回客户加密信息
else if(isset($_GET['code'])){
   $res =  $core->get_info($_GET['code']);
   echo  empty($res)? '0':$res;
}


//没有检查到登录中心的cookie信息则显示登录界面
else if(empty($_COOKIE['sign'])){
    require_once 'login.php';
}

//检查到登录中心的cookie信息则显示管理界面
else{
    require_once 'logout.php';
}