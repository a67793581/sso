<?php
require_once './vendor/autoload.php';
use sso\Client_core;
header('Content-Type:text/html; charset=utf-8');
if(empty($_GET)){
    exit('测试接口url是否正确');
}else{
    ini_set('display_errors', '1');
    error_reporting(E_ALL);

//设置sso的code验证地址
    $sso_code_url = 'http://test1.aiku.fun/index.php?code=';
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
    $core = new Client_core($public_key,$private_key,$md5_key,$sso_code_url);
    $callback = empty($_GET['callback']) ? 'function' : $_GET['callback'];
    $callback2 = empty($_GET['callback']) ? '{}' : '';
    switch ($_GET['type']){
        case 'login':

            if(!empty($_GET['time']) && !empty($_GET['sign']) && !empty($_GET['code'])){
                $params = array('time'=>$_GET['time'],'type'=>'login','code'=>$_GET['code']);
                $user = $core->login($_GET['code'],$params,$_GET['sign']);
                if(is_int($user)){
                    exit($callback ." ($user)" . $callback2);
                }else{
                    foreach($user as $key=>$val){
                        setcookie($key,$val,0,'/');
                    }
                    exit($callback .' ("登录成功")' . $callback2);
                }
            }else{
                exit($callback .' ("非法请求")' . $callback2);
            }
            break;
        case 'logout':
            if(!empty($_GET['time']) && !empty($_GET['sign']) ){
                $params = array('time'=>$_GET['time'],'type'=>'logout');
                $res = $core->logout($_GET['sign'],$params);
                if($res){
                    setcookie('sign','',0,'/');
                    exit($callback .' ("退出成功")' . $callback2);
                }else{
                    echo $callback .' ("校验失败")' . $callback2;
                }
            }else{
                echo $callback .' ("非法请求")' . $callback2;
            }
            break;
    }


}