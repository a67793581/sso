# sso
##php sso  用php设计的认证中心  

##认证流程  
###用户打开任意网站→点击登录→将登录请求发送到认证中心（带上该页面的完整url地址）
###↓认证中心接到登录请求:
###展示登录页面（自己设计）→用户输入账号密码→点击登录→验证账号密码（自己设计）
###↓验证通过后调用核心组件:
###加密需要分发的用户信息并保存到cookie，并发起同步登录请求给全部网站
###↓实例代码:
``` 
        $info = $core->set_cookie(array('sign'=>$_POST['username']));//加密需要分发的用户信息并保存到cookie
        $core->login($info);//并发起同步登录请求给全部网站
```
 ###↓客户端接收到登录请求：
 ###自行校验请求来源→调用核心登录方法获取用户信息→自行保存用户信息完成登录->返回登录完成的信息
 ###↓实例代码:
 ```
        $user = $core->login($_GET['code'],$_GET['callback']);//调用核心登录方法获取用户信息
        foreach($user as $key=>$val){//自行保存用户信息完成登录
            setcookie($key,$val,0,'/');
        }
        exit($_GET['callback'] . '(0)');//返回登录完成的信息
```
###至此完成登录

##服务端核心类的必要配置信息
```
    //$api_url 为各个网站接口的地址
    private $api_url = array(
        'http://test2.aiku.fun/sso/callback.php',
    );
    //以下3个参数 2个核心类要一致
    //加密用公钥
    private $public_key = '';

    //加密用私钥
    private  $private_key = '';

    //code 加密用秘钥
    private $md5_key = '';
```
##客户端核心类的必要配置信息
```$xslt
    //设置服务端code获取用户信息的完整url包含变量名不包含变量值
    private $sso_code_url = 'http://test1.aiku.fun/index.php?code=';
    //以下3个参数 2个核心类要一致
    //加密用公钥 
    private $public_key = '';

    //加密用私钥
    private  $private_key = '';

    //code 加密用秘钥
    private $md5_key = '';
```