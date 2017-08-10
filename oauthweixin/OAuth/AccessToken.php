<?php

namespace OAuth\WeChat;

use Bridge\WeChat\Util;
class AccessToken
{
    /**
     * 刷新 access_token
     */
    const REFRESH = 'https://api.weixin.qq.com/sns/oauth2/refresh_token';

    /**
     * 检测 access_token 是否有效
     */
    const IS_VALID = 'https://api.weixin.qq.com/sns/auth';

    /**
     * 网页授权获取用户信息
     */
    const USERINFO = 'https://api.weixin.qq.com/sns/userinfo';

    /**
     * 用户 access_token 和公众号是一一对应的
     */
    protected $appid;

    /**
     * 用于刷新access_token
     */
    public $refresh_token;

    /**
     * access_token
     */
    public $access_token;

    /**
     * 用户对应应用唯一表示openid
     */
    public $openid;

    /**
     * 用户对应多应用（公众平台，开放平台）唯一表示unionid
     */
    public $unionid;

    /**
     * 构造方法
     */
    public function __construct($appid,$response)
    {
        $this->appid = $appid;
        if(is_array($response)){
            $this->access_token  = $response['access_token'];
            $this->refresh_token = $response['refresh_token'];
            $this->openid        = $response['openid'];
            $this->unionid       = $response['unionid'];
        }else
            throw new \Exception(sprintf('Invalid response "%s"', $response));
    }

    /**
     * 公众号 appid
     */
    public function getAppid()
    {
        return $this->appid;
    }

    /**
     * 获取用户信息
     */
    public function getUser($lang = 'zh_CN')
    {
        if( !$this->isValid() ) {
            $this->refresh();
        }

        $query = array(
            'access_token'  => $this->access_token,
            'openid'        => $this->openid,
            'lang'          => $lang
        );

        $response = Util::_request(static::USERINFO.'?'.http_build_query($query));

        $err = Util::checkErrorCode($response);
        if($err) {
            return $err;
        }

        return $response;
    }

    /**
     * 刷新用户 access_token
     */
    public function refresh()
    {
        $query = array(
            'appid'         => $this->appid,
            'grant_type'    => 'refresh_token',
            'refresh_token' => $this->refresh_token,
        );

        $response = Util::_request(static::REFRESH.'?'.http_build_query($query));

        $err = Util::checkErrorCode($response);
        if( $err) {
            return $err;
        }

        // update new access_token from ArrayCollection
        return $response;
    }

    /**
     * 检测用户 access_token 是否有效
     */
    public function isValid()
    {
        $query = array(
            'access_token'  => $this->access_token,
            'openid'        => $this->openid,
        );

        $response = Util::_request(static::IS_VALID.'?'.http_build_query($query));
        $err = Util::checkErrorCode($response);
        if($err) {
            return $err;
        }
        return true;
    }
}
