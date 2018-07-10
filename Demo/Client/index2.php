<?php
header('Content-Type:text/html; charset=utf-8');

$sso_url = 'https://user.aiku.fun/index.php'; //你SSO所在的域名,不是当前项目地址
$url = 'https://www.aiku.fun/index2.php';
if(isset($_COOKIE['sign'])){
    exit("欢迎您{$_COOKIE['sign']} <a href='{$sso_url}?logout=1&callback={$_SERVER['HTTP_REFERER']}'>退出</a>");
}else{
    echo '您还未登录 <a href="'.$sso_url.'?callback='.$url.'">点击登录</a>';
}
?>