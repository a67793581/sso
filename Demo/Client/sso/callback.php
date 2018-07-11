<?php

header('Content-Type:text/html; charset=utf-8');
if(empty($_GET)){
    exit('测试接口url是否正确');
}else{
    require_once 'core.php';
    $core = new Core();
    switch ($_GET['type']){
        case 'login':
            $user = $core->login($_GET['code'],$_GET['callback']);
            foreach($user as $key=>$val){
                setcookie($key,$val,0,'/');
            }
            exit($_GET['callback'] . '(0)');
            break;
        case 'logout':
            $params = array('time'=>$_GET['time'],'type'=>'logout');
            $res = $core->logout($_GET['code'],$params);
            if($res){
                setcookie('sign','',0,'/');
            }else{
                echo '校验失败';
            }
            break;
    }


}