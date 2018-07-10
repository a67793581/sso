<?php


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
    curl_setopt($ch, CURLOPT_TIMEOUT, 1);

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

header('Content-Type:text/html; charset=utf-8');
if(empty($_GET)){
    exit('测试接口url是否正确');
}else{
    require_once 'core.php';
    $core = new Core();
    switch ($_GET['type']){
        case 'login':
            $key = md5($_GET['code'].$core->md5_key);
            $url = $core->__get('sso_url')."index.php?code={$key}";
            var_dump($url);
            $info = get_curl_data($url);
            empty($info) && exit($_GET['callback'] . '(2)');
            $user = $core->get_user($info);
            empty($user) && exit($_GET['callback'] . '(3)');
            foreach($user as $key=>$val){
                setcookie($key,$val,0,'/');
            }

            exit($_GET['callback'] . '(0)');
            break;
        case 'logout':
            setcookie('sign','',0,'/');
            break;
    }


}