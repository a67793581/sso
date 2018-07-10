<?php
header('Content-Type:text/html; charset=utf-8');

$sso_url = 'https://user.aiku.fun/index.php'; //你SSO所在的域名,不是当前项目地址
$url = 'https://www.aiku.fun/index2.php';

#测试网址:     http://localhost/blog/testurl.php?id=5

//获取域名或主机地址
echo $_SERVER['HTTP_HOST']."<br>"; #localhost

//获取网页地址
echo $_SERVER['PHP_SELF']."<br>"; #/blog/testurl.php

//获取网址参数
echo $_SERVER["QUERY_STRING"]."<br>"; #id=5

//获取用户代理
echo $_SERVER['HTTP_REFERER']."<br>";

//获取完整的url
echo '<br>http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
echo '<br>http://'.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING'];
#http://localhost/blog/testurl.php?id=5

//包含端口号的完整url
echo '<br>http://'.$_SERVER['SERVER_NAME'].':'.$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];


//只取路径
$url='<br>http://'.$_SERVER['SERVER_NAME'].$_SERVER["REQUEST_URI"];
echo dirname($url);



if(isset($_COOKIE['sign'])){
    exit("欢迎您{$_COOKIE['sign']} <a href='{$sso_url}?logout=1&callback={$_SERVER['HTTP_REFERER']}'>退出</a>");
}else{
    echo '您还未登录 <a href="'.$sso_url.'?callback='.$url.'">点击登录</a>';
}
?>