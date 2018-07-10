<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/7/4
 * Time: 19:20
 */

class Core
{

    //$api_url 为各个网站接口的地址
    private $api_url = array(
        'http://test2.aiku.fun/sso/callback.php',
    );
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
        $this->public_key = openssl_pkey_get_public($this->public_key);//格式化秘钥
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
        $this->private_key =  openssl_pkey_get_private($this->private_key);//格式化秘钥
        $decrypted = '';
        openssl_private_decrypt(base64_decode($data), $decrypted, $this->private_key);//私钥解密
        return json_decode($decrypted, true);
    }

    /**
     * get_cookie 获取cookie并解密  （可自定义）
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
                return array();
            }else{
                return $this->decrypted($_COOKIE[$key]);
            }
        }
    }

    /**
     * $array
     * set_cookie 设置cookie并解密  （可自定义）
     */
    function set_cookie($info){
        foreach($info as $key=>$val){
            $val = $this->encryption($val);
            setcookie($key,$val,0,'/');
        }
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

        echo '<script src="https://cdn.bootcss.com/jquery/3.3.1/jquery.min.js"></script><script type="text/javascript">$(document).ready(function(){';
        $this->ajax($this->api_url,$info);
        echo '    });</script>';
    }

    /**
     * 递归发起ajax  （可自定义）
     */

    function ajax($arr,$info){
        if(empty($arr)) {
            return;
        }
        $code = $this->code($arr[0],$info);
        $url = $arr[0]."?code={$code}&type=login";
        array_shift($arr);

        echo '        
            $.ajax({
                url: "'.$url.'", //url
                type: "get", //方法
                dataType: "jsonp", //数据格式为 jsonp 支持跨域提交
                jsonpCallback : "callback",
                async:false,
                success: function(data){ //读取返回结果
                    ';

        if(!empty($arr)){
            $this->ajax($arr,$info);
        }else{
            echo 'window.setTimeout("window.location=\''.$_GET['callback'].'\'",0);';
        }
        echo '
                }
            });
        ';
        return;
    }

    /**
     * 退出通知  （可自定义）
     */
    function logout(){
        //通知全部网站接口登出
        foreach ($this->api_url as $url){
            $js_url = $url."?type=logout";
            echo '<script src="'.$js_url.'" type="text/javascript"></script>';
        }
        //跳转到发起退出登录的网站
        echo '<script type="text/javascript">window.onload=function(){window.location.href = document.referrer;}</script>';
    }


}