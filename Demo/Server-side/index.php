<?php
header('Content-Type:text/html; charset=utf-8');
ini_set('display_errors', '1');
error_reporting(E_ALL);
require_once './vendor/autoload.php';

use sso\Server_core;
//客户端接口地址
$api_url = array('http://test2.aiku.fun/callback.php',);
//加密用公钥
$public_key = '-----BEGIN PUBLIC KEY-----
MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQC46V0gBZv78t4MFGlRE5kWeN3j
GpOEx+p6Sac9mebIEQox5tYGohuBh+1+xn9MesZeA/JcYLdgTS9tmJ0GbjXm3HlD
BsAJAVeY05/GLpAzVdDRpN6QhftP/6ZnscrlTWlp2kgrvayAuMBqzgtMTezrAdQE
kVrdYzKxMEsI1f+H9wIDAQAB
-----END PUBLIC KEY-----
';

//加密用私钥
$private_key = '-----BEGIN PRIVATE KEY-----
MIICdwIBADANBgkqhkiG9w0BAQEFAASCAmEwggJdAgEAAoGBALjpXSAFm/vy3gwU
aVETmRZ43eMak4TH6npJpz2Z5sgRCjHm1gaiG4GH7X7Gf0x6xl4D8lxgt2BNL22Y
nQZuNebceUMGwAkBV5jTn8YukDNV0NGk3pCF+0//pmexyuVNaWnaSCu9rIC4wGrO
C0xN7OsB1ASRWt1jMrEwSwjV/4f3AgMBAAECgYB5DkYqRaHV8y0NaXt9WcA6ZwyU
tnxnTF4kiv2TJaNhzU4IV2A83Xn2dh+0hI0oa6RcPmc3tRW4VS+8p1H9uL9N8+cR
zPLHadCv6ycj6IroaL/A7TdOiyKz4+OKgu37Tsc2nve7rXziQvatsMEk42DL4D2c
2k0h+TN5WXRLVLxGSQJBAOmJJEtAd1DJkFcKJUtqO62ns2iv17rKhkbHRpgvhDlg
R7EFWCMpAgz4BT1Sa5/nK7nKlpiLOfk+RRNB1hWBzpsCQQDKstgXyAa0V9JSejMG
XuPFoo9HoZNi0fKVmBTuYaZ3Uu1FgthYg1E60nh1jQrrsiJ9L11394YdlCcb+2F2
/XPVAkEA5elmGc+1p4tI1ufeH4jOh//52K6FLBgGadf14A2nlvT6n4QraTIOGsZy
IhTqb9oeaiLQcA1hXce4KWU/Zp0M0QJBAIxx6LpplQOmCgutsecLHmTU4tPuBzIk
aCHwsygMrwvkgJR2ObLyofjQ1jgU1ulCjxUQGYJDFkEuYv7HadvJd1UCQHeOhsco
A6jOLyVp4zhceVLp7ZOztY8qbe7/ylqQVRmrJKatCz+6VjGWMAXFGH+2y0FHKXEd
zmD24uz8gSKXDk0=
-----END PRIVATE KEY-----';

//code 解密用秘钥
$md5_key = 'jie';
var_dump($public_key,$private_key,$md5_key,$api_url);
$core = new Server_core($api_url,$public_key,$private_key,$md5_key);


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