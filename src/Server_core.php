<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/7/4
 * Time: 19:20
 */
namespace sso;
require_once 'Core.php';
class Server_core extends  Core
{


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

    function  logout(){
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

//    /**
//     * 退出通知  （可自定义）
//     */
//    function logout(){
//        echo '<script src="https://cdn.bootcss.com/jquery/3.3.1/jquery.min.js"></script><script type="text/javascript">$(document).ready(function(){';
//        $this->ajax($this->api_url,'logout',$_SERVER['HTTP_REFERER'],'');
//        echo '    });</script>';
//    }
//
//    /**
//     * 登陆通知  （可自定义）
//     */
//    function login($info){
//
//        echo '<script src="https://cdn.bootcss.com/jquery/3.3.1/jquery.min.js"></script><script type="text/javascript">$(document).ready(function(){';
//        $this->ajax($this->api_url,'login',$_GET['callback'],$info);
//        echo '    });</script>';
//    }
//
//    /**
//     * 递归发起ajax  （可自定义）
//     */
//    function ajax($arr,$type='',$callback,$info=''){
//        if(empty($arr)) {
//            return;
//        }
//
//        $params = array();
//        $params['time'] = time();
//        $params['type'] = $type;
//
//        if(!empty($info)){
//            $params['code'] = $this->code($arr[0],$info);
//        }
//        $params['sign'] = $this->sign($params);
//        $url = $arr[0].'?'.http_build_query($params);
//
//        array_shift($arr);
//
//        echo '
//            $.ajax({
//                url: "'.$url.'", //url
//                type: "get", //方法
//                dataType: "jsonp", //数据格式为 jsonp 支持跨域提交
//                jsonpCallback : "callback",
//                async:false,
//                success: function(data){ //读取返回结果
//                    ';
//
//        if(!empty($arr)){
//            $this->ajax($arr,$type,$callback,$info);
//        }else{
//            echo 'window.setTimeout("window.location=\''.$callback.'\'",0);';
//        }
//        echo '
//                }
//            });
//        ';
//        return;
//    }
//


}