<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/7/4
 * Time: 19:20
 */

class Core
{

    //设置sso的url
    private $sso_url = 'https://test1.aiku.fun/';

    //加密用公钥
    private $public_key = '-----BEGIN PUBLIC KEY-----
MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQC46V0gBZv78t4MFGlRE5kWeN3j
GpOEx+p6Sac9mebIEQox5tYGohuBh+1+xn9MesZeA/JcYLdgTS9tmJ0GbjXm3HlD
BsAJAVeY05/GLpAzVdDRpN6QhftP/6ZnscrlTWlp2kgrvayAuMBqzgtMTezrAdQE
kVrdYzKxMEsI1f+H9wIDAQAB
-----END PUBLIC KEY-----
';

    //加密用私钥
    private  $private_key = '-----BEGIN PRIVATE KEY-----
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
    private $md5_key = 'jie';


    /**
     * 初始化
     */
    public function __construct()
    {
        ini_set('error_reporting', -1);
        ini_set('display_errors', -1);
        //初始化秘钥
        $this->private_key =  openssl_pkey_get_private($this->private_key);//
        $this->public_key = openssl_pkey_get_public($this->public_key);
    }

    /**
     * 获取对象属性
     */
    function __get($property_name) {
        return isset($this->$property_name) ? $this->$property_name : null;
    }

    /**
     * 设置对象属性
     */
    function __set($property_name, $value) {
        $this->$property_name = $value;
    }

    /**
     * 加密方法
     */
    function encryption($data){
        $data = json_encode($data);
        $encrypted = '';
        openssl_public_encrypt($data, $encrypted, $this->public_key);//公钥加密
        $encrypted = base64_encode($encrypted);// base64传输
        return $encrypted;
    }

    /**
     * 解密方法
     */
    function decrypted($data){
        $decrypted = '';
        openssl_private_decrypt(base64_decode($data), $decrypted, $this->private_key);//私钥解密
        return json_decode($decrypted, true);
    }

    /**
     * get_cookie 获取cookie并解密
     */
    function get_cookie($key=''){
        if(empty($key)){
            $list = array();
            foreach($_COOKIE as $k=>$v){
                if(empty($v)){
                    continue;
                }
                $list[$k]= $this->decrypted($v);
            }
            return $list;
        }else{
            if(empty($_COOKIE[$key])){
                return;
            }else{
                return $this->decrypted($_COOKIE[$key]);
            }
        }
    }

    /**
     * $array
     * set_cookie 设置cookie并解密
     */
    function set_cookie($info){
        foreach($info as $key=>$val){
            $val = $this->encryption($val);
            setcookie($key,$val,0,'/');
        }
    }


    /**
     * 将获取到的用户信息解密
     */
    function get_user($info){
        $info = json_decode($info);
        $res = array();
        foreach ($info as $k => $v){
            $res[$k] = $this->decrypted($v);
        }
        return $res;
    }


}