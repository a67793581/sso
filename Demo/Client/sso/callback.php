<?php

header('Content-Type:text/html; charset=utf-8');
if(empty($_GET)){
    exit('测试接口url是否正确');
}else{
    require_once 'Client_core.php';
    $core = new Client_core();
    switch ($_GET['type']){
        case 'login':
            if(!empty($_GET['time']) && !empty($_GET['sign']) && !empty($_GET['code'])){
                $params = array('time'=>$_GET['time'],'type'=>'login','code'=>$_GET['code']);
                $user = $core->login($_GET['code'],$params,$_GET['sign']);
                if(is_int($user)){
                    exit("function ($user){}");
                }else{
                    foreach($user as $key=>$val){
                        setcookie($key,$val,0,'/');
                    }
                    exit('function (0){}');
                }
            }else{
                exit('function ("非法请求"){}');
            }
            break;
        case 'logout':
            if(!empty($_GET['time']) && !empty($_GET['sign']) ){
                $params = array('time'=>$_GET['time'],'type'=>'logout');
                $res = $core->logout($_GET['sign'],$params);
                if($res){
                    setcookie('sign','',0,'/');
                }else{
                    echo 'function ("校验失败"){}';
                }
            }else{
                echo 'function ("非法请求"){}';
            }
            break;
    }


}