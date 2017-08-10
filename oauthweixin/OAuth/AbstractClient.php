<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2017/1/5
 * Time: 14:34
 */

namespace OAuth\WeChat;
include_once 'protected/extensions/oauthweixin/Bridge/Util.php';
include_once 'protected/extensions/oauthweixin/OAuth/StateManager.php';
include_once 'protected/extensions/oauthweixin/OAuth/AccessToken.php';
use Bridge\WeChat\Util;
//use OAuth\WeChat\StateManager;
use OAuth\WeChat\AccessToken;

abstract class AbstractClient
{
    /**
     * AccessToken URL
     */
    const ACCESS_TOKEN = 'https://api.weixin.qq.com/sns/oauth2/access_token';

    /**
     * 公众号 Appid
     */
    protected $appid;

    /**
     * 公众号 AppSecret
     */
    protected $appsecret;

    /**
     * scope
     */
    protected $scope;

    /**
     * state
     */
    protected $state;

    /**
     * redirect url
     */
    protected $redirectUri;

    /**
     * state manager
     */
    protected $stateManager;

    /**
     * 启用第三方跳转
     */
    protected $authorizeUrl;

    /**
     * 是否使用开放平台做授权登录
     */
    protected $isMp;

    /**
     * 构造方法
     */
    public function __construct($appid, $appsecret)
    {
        $this->appid        = $appid;
        $this->appsecret    = $appsecret;
        $this->stateManager = new StateManager;
    }

    /**
     * 设置 scope
     */
    public function setScope($scope)
    {
        $this->scope = $scope;
    }

    /**
     * 设置 state
     */
    public function setState($state)
    {
        $this->state = $state;
    }

    /**
     * 设置 redirect uri
     */
    public function setRedirectUri($redirectUri)
    {
        $this->redirectUri = $redirectUri;
    }

    /**
     * 设置第三方跳转地址
     */
    public function setAuthorizeUrl($authorizeUrl){
        $this->authorizeUrl = $authorizeUrl;
    }

    /**
     * 设置是否使用开放平台做授权登录
     */
    public function setIsMp($isMp){
        $this->isMp = $isMp;
    }

    /**
     * 获取授权 URL
     *
     * 获取跳转至微信二维码页面
     */
    public function getAuthorizeUrl()
    {
        if (null === $this->state) {
            $this->state = Util::getRandomString(16);
        }

//        $this->stateManager->setState($this->state);
        \Yii::app()->session['state'] = $this->state;

        $query = array(
            'appid'         => $this->appid,
            'redirect_uri'  => $this->redirectUri ?: Util::getCurrentUrl(),
            'response_type' => 'code',
            'scope'         => $this->resolveScope(),
            'state'         => $this->state,
            'isMp'          => $this->isMp,
        );

        return ($this->authorizeUrl ? :$this->resolveAuthorizeUrl()).'?'.http_build_query($query);
    }

    /**
     * 通过 code 换取 AccessToken
     *
     * 保存session信息必须使用yii方法，否则出现session信息全局不统一
     */
    public function getAccessToken($code, $state = null)
    {
        if (null === $state && !isset($_GET['state'])) {
            throw new \Exception('Invalid Request');
        }

        if(\Yii::app()->session['access_token']){
            $response = $this->checkTokenExpired(\Yii::app()->session['create_token'], \Yii::app()->session['refresh_token']);
            $result = array(
                'access_token' => $response,
                'refresh_token'=> \Yii::app()->session['refresh_token'],
                'openid'       => \Yii::app()->session['openid'],
                'unionid'      => \Yii::app()->session['unionid'],
            );
            $accessToken = new AccessToken($this->appid,$result);

            return $accessToken;
        }

        // http://www.twobotechnologies.com/blog/2014/02/importance-of-state-in-oauth2.html
        $state = $state ?: $_GET['state'];
//        if (!$this->stateManager->isValid($state)) {
//            throw new \Exception(sprintf('Invalid Authentication State "%s"', $state));
//        }

        if (!\Yii::app()->session['state'] === $state ) {
            throw new \Exception(sprintf('Invalid Authentication State "%s"', $state));
        }

        $query = array(
            'appid'         => $this->appid,
            'secret'        => $this->appsecret,
            'code'          => $code,
            'grant_type'    => 'authorization_code'
        );

        $response = Util::_request(static::ACCESS_TOKEN.'?'.http_build_query($query));
        $err = Util::checkErrorCode($response);
        if($err) {
            return $err;
        }
        /**使用yii保存session**/
        \Yii::app()->session['create_token'] = date('Y-m-d H:i:s',time());
        \Yii::app()->session['access_token'] = $response['access_token'];
        \Yii::app()->session['refresh_token'] = $response['refresh_token'];
        \Yii::app()->session['openid'] = $response['openid'];
        \Yii::app()->session['unionid'] = $response['unionid'];
        $accessToken = new AccessToken($this->appid,$response);
        return $accessToken;
    }

    /**
     * 用于持续化access_token或防止当用户打开扫码页面后长时间不操作access_token失效
     * 检测access_token是否过期
     * 过期则刷新并返回新的access_token
     * @param $create
     * @param $refreshToken
     * @return bool|mixed
     */
    public function checkTokenExpired($create,$refreshToken){
        if(Util::timeDifference($create,2,'hour'))
            return \Yii::app()->session['access_token'];
        else {
            $refresh = new AccessToken($this->appid,array('refresh_token'=>$refreshToken));
            $access_token = $refresh->refresh();
            return $access_token['access_token'];
        }
    }

    /**
     * 授权接口地址
     */
    abstract public function resolveAuthorizeUrl();

    /**
     * 授权作用域
     */
    abstract public function resolveScope();
}