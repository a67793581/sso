<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/7/4
 * Time: 19:20
 */
namespace sso;
require_once 'Core.php';
class Client_core extends  Core
{

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