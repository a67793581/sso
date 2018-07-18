<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/7/4
 * Time: 19:20
 */

class Server_core
{
    
    //$api_url 为各个网站接口的地址
    private $api_url = array(
        'http://test2.aiku.fun/sso/callback.php',
    );

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
        ini_set('error_reporting', -1); //关闭错误提示
        ini_set('display_errors', -1);  //关闭错误提示
        $this->public_key = openssl_pkey_get_public($this->public_key);//格式化秘钥
        $this->private_key =  openssl_pkey_get_private($this->private_key);//格式化秘钥
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
     * 加密方法  （可自定义 如果自定义那么公钥私钥也需自行修改）
     */
    function encryption($data){

        $data = json_encode($data);
        $encrypted = '';
        openssl_public_encrypt($data, $encrypted, $this->public_key);//公钥加密
        $encrypted = base64_encode($encrypted);// base64传输
        return $encrypted;
    }

    /**
     * 解密方法  （可自定义 如果自定义那么公钥私钥也需自行修改）
     */
    function decrypted($data){

        $decrypted = '';
        openssl_private_decrypt(base64_decode($data), $decrypted, $this->private_key);//私钥解密
        return json_decode($decrypted, true);
    }


    /**
     * $array
     * for_encryption 循环加密返回数组 （可自定义）
     */
    function for_encryption($info){
        $arr = array();
        foreach($info as $key=>$val){
            $arr[$key] = $val = $this->encryption($val);
        }
        return $arr;
    }


    /**
     * 生成code并将用户信息存到缓存数据库  （可自定义）
     */
    function code($url='',$info){
        $json = json_encode($info);

        $code = md5($json.$url);
        $key = md5($code.$this->md5_key);
        //实例化redis
        $redis = new Redis();
        //连接
        $redis->connect('127.0.0.1', 6379);
        $redis->setex($key,100,$json);//key=value，有效期为10秒
        return $code;
    }

    /**
     * 根据code查找缓存数据库 并返回信息  （可自定义）
     */
    function get_info($key){

        //实例化redis
        $redis = new Redis();
        //连接
        $redis->connect('127.0.0.1', 6379);
        $info = $redis->get($key);
        $redis->del($key);
        return $info;
    }



    /**
     * 将获取到的用户信息解密  （可自定义）
     */
    function get_user($info){
        $info = json_decode($info);
        $res = array();
        foreach ($info as $k => $v){
            $res[$k] = $this->decrypted($v);
        }
        return $res;
    }

    /**
     * 登陆通知  （可自定义）
     */
    function login($info){

        //通知全部网站接口登出
        foreach ($this->api_url as $url){
            $code = $this->code($url,$info);
            $time = time();
            $params = array('time'=>$time,'type'=>'login','code'=>$code);
            $sign = $this->sign($params);
            $params['sign'] = $sign;
            $url = $url.'?'.http_build_query($params);
            echo '<script src="'.$url.'" type="text/javascript"></script>';
        }
        //跳转到发起退出登录的网站
        echo '<script type="text/javascript">window.onload=function(){window.location.href = "'.$_GET['callback'].'";}</script>';
    }


    /**
     * 退出通知  （可自定义）
     */
    function logout(){
        //通知全部网站接口登出
        foreach ($this->api_url as $url){
            $time = time();
            $params = array('time'=>$time,'type'=>'logout');
            $sign = $this->sign($params);
            $params['sign'] = $sign;
            $js_url = $url.'?'.http_build_query($params);
            echo '<script src="'.$js_url.'" type="text/javascript"></script>';
        }
        //跳转到发起退出登录的网站
        echo '<script type="text/javascript">window.onload=function(){window.location.href = document.referrer;}</script>';
    }

    /**
     * 加密sign
     * @param $params
     * @return string
     */
    function sign($params)
    {
        ksort($params);
        $sign = '';
        foreach ($params as $key => $val) {
            $sign .= $key . $val;
        }
        $sign .= 'keysecret' . $this->md5_key;
        $sign = md5($sign);
        return $sign;
    }
}