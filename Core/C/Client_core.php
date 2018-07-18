<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/7/4
 * Time: 19:20
 */
namespace C\Client_core;
class Client_core
{

    //设置服务端code获取用户信息的完整url包含变量名不包含变量值
    private $sso_code_url = 'http://test1.aiku.fun/index.php?code=';
    //以下3个参数 2个核心类要一致
    //加密用RSA公钥 秘钥格式PKCS#1
    private $public_key = '';

    //加密用RSA私钥 秘钥格式PKCS#1
    private  $private_key = '';

    //code 加密用秘钥
    private $md5_key = '';

    /**
     * 初始化
     */
    public function __construct()
    {
        ini_set('error_reporting', -1); //关闭错误提示
        ini_set('display_errors', -1);  //关闭错误提示
        $this->private_key =  openssl_pkey_get_private($this->private_key);//格式化秘钥
        $this->public_key = openssl_pkey_get_public($this->public_key);//格式化秘钥
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
     * 请求远程数据
     * @param type $url
     * @param type $parm
     * @return type
     */
    function get_curl_data($url, $param = array())
    {
        // 创建一个cURL资源
        $ch = curl_init();

        // 设置URL和相应的选项
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);//绕过ssl验证
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);

        if (!empty($param)) {
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($param));
        }
        // 抓取URL并把它传递给浏览器
        $res = curl_exec($ch);
        // 关闭cURL资源，并且释放系统资源
//    var_dump($res,$ch,$url,curl_error($ch));
        curl_close($ch);
        return $res;
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
     * 获取登录请求并请求获取用户信息  （可自定义）
     */
    function login($code,$params,$sign1){
        $sign = $this->sign($params);
        if($sign1 != $sign){
            return 1;
        }
        $key = md5($code.$this->md5_key);
        $url = $this->sso_code_url.$key;
        $info = $this->get_curl_data($url);

        if(empty($info)){
            return 2;
        }
        $user = $this->get_user($info);
        if(empty($user)){
            return 3;
        }
        return $user;
    }

    /**
     * 退出校验  （可自定义）
     */
    function logout($code,$params){

        $sign = $this->sign($params);
        if($code == $sign){
            return true;
        }
        return false;
    }

}