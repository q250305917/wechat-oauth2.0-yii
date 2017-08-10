<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2017/1/5
 * Time: 14:33
 */

namespace OAuth\WeChat;
include_once 'protected/extensions/oauthweixin/OAuth/AbstractClient.php';

class Client extends AbstractClient
{
    /**
     * 授权接口地址
     */
    public function resolveAuthorizeUrl()
    {
        return 'https://open.weixin.qq.com/connect/oauth2/authorize';
    }

    /**
     * 授权作用域
     */
    public function resolveScope()
    {
        return $this->scope ?: 'snsapi_base';
    }
}
