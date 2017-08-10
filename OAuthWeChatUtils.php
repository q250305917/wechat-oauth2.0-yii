<?php
/**
 * Created by pengjch.
 * Date: 17-1-05
 * Time: 下午4:33
 * To change this template use File | Settings | File Templates.
 */
use OAuth\WeChat\Qrcode;
use OAuth\WeChat\Client;


class OAuthWeChatUtils{

    /**
     * @param $appid  应用唯一ID
     * @param $secret 应用唯一secret
     * @param null $setRedirectUri 设置回调地址用于扫码后自动跳转
     * @param null $scope   登录方式  授权||扫码
     * @param null $state
     * @param bool $isMp    是否在订阅号内做授权
     * @param string $authorizeUrl 启用微信跳转服务
     * @return object
     */
    public static function OAuthInit($appid,$secret,$setRedirectUri=null,$scope=null,$state=null,$isMp=false,$authorizeUrl=null){
        if(($scope == null || $scope == 'snsapi_login')&&!$isMp){  //PC端只有扫码登录
            include_once 'protected/extensions/oauthweixin/OAuth/Qrcode.php';
            $object = new Qrcode($appid,$secret);
        }elseif($scope == 'snsapi_login' && $isMp){//订阅号内使用开放平台授权
            include_once 'protected/extensions/oauthweixin/OAuth/Client.php';
            $object = new Client($appid,$secret);
        }elseif($scope == 'snsapi_base'){   //静态授权只能获取openid
            include_once 'protected/extensions/oauthweixin/OAuth/Client.php';
            $object = new Client($appid,$secret);
        }elseif($scope == 'snsapi_userinfo'){ //动态授权能获取用户其他信息
            include_once 'protected/extensions/oauthweixin/OAuth/Client.php';
            $object = new Client($appid,$secret);
        }else
            return false;
        !$setRedirectUri ?: $object->setRedirectUri($setRedirectUri);
        !$scope ?: $object->setScope($scope);
        !$state ?: $object->setState($state);
        !$isMp  ?: $object->setIsMp($isMp);
        !$authorizeUrl ?: $object->setAuthorizeUrl($authorizeUrl);
        return $object;
    }

    /**
     * @return bool
     * 判断是否来自微信浏览器
     */
    public static function IsWeChat(){
        if (strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') !== false) {
            return true;
        }
        return false;
    }
    

}