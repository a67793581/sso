<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/7/19
 * Time: 16:39
 */

namespace sso;


class Core
{

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
    public function __construct(  $public_key, $private_key, $md5_key)
    {
        // 初始化
        // 判断openssl扩展存在
        extension_loaded('openssl') or die('openssl 扩展未开启');

        $this->public_key = openssl_pkey_get_public($public_key);
        $this->private_key = openssl_pkey_get_private($private_key);
        $this->md5_key = $md5_key;

        if($this->public_key === false) {die('公钥错误');}
        if($this->private_key === false) {die('秘钥错误');}

        $bool = $this->decrypted($this->encryption('b'));
        if(empty($bool)){
            die('秘钥和公钥不匹配');
        }
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