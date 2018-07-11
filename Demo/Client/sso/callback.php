<?php

header('Content-Type:text/html; charset=utf-8');
if(empty($_GET)){
    exit('测试接口url是否正确');
}else{
    require_once 'core.php';
    $core = new Core();
    switch ($_GET['type']){
        case 'login':
            if(!empty($_GET['time']) && !empty($_GET['sign']) && !empty($_GET['code'])){
                $params = array('time'=>$_GET['time'],'type'=>'login','code'=>$_GET['code']);
                $user = $core->login($_GET['code'],$params,$_GET['sign']);
                if(is_int($user)){
                    exit($_GET['callback'] . "($user)");
                }else{
                    foreach($user as $key=>$val){
                        setcookie($key,$val,0,'/');
                    }
                    exit($_GET['callback'] . '(0)');
                }
            }else{
                exit($_GET['callback'] . '(4)');
            }
            break;
        case 'logout':
            if(!empty($_GET['time']) && !empty($_GET['sign']) ){
                $params = array('time'=>$_GET['time'],'type'=>'logout');
                $res = $core->logout($_GET['sign'],$params);
                if($res){
                    setcookie('sign','',0,'/');
                }else{
                    echo '校验失败';
                }
            }else{
                echo '非法请求';
            }
            break;
    }


}