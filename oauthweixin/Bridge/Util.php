<?php

namespace Bridge\WeChat;

class Util
{
    /**
     * 检测是否为微信中打开
     */
    public static function isWechat()
    {
        return (false === strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger'));
    }

    /**
     * 获取当前时间缀
     */
    public static function getTimestamp()
    {
        return (string) time();
    }

    /**
     * 获取当前 URL
     */
    public static function getCurrentUrl()
    {
        $protocol = (isset($_SERVER['HTTPS']) && ($_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443))
            ? 'https://' : 'http://';

        return $protocol.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
    }

    /**
     * 获取客户端 IP
     */
    public static function getClientIp()
    {
        $headers = function_exists('apache_request_headers')
            ? apache_request_headers()
            : $_SERVER;

        return isset($headers['REMOTE_ADDR']) ? $headers['REMOTE_ADDR'] : '0.0.0.0';
    }

    /**
     * 获取随机字符
     */
    public static function getRandomString($length = 10)
    {
        $pool = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

        return substr(str_shuffle(str_repeat($pool, ceil($length / strlen($pool)))), 0, $length);
    }

    /**
     * 过滤微信昵称中的表情（不过滤 HTML 符号）
     */
    public static function filterNickname($nickname)
    {
        $pattern = array(
            '/\xEE[\x80-\xBF][\x80-\xBF]/',
            '/\xEF[\x81-\x83][\x80-\xBF]/',
            '/[\x{1F600}-\x{1F64F}]/u',
            '/[\x{1F300}-\x{1F5FF}]/u',
            '/[\x{1F680}-\x{1F6FF}]/u',
            '/[\x{2600}-\x{26FF}]/u',
            '/[\x{2700}-\x{27BF}]/u',
            '/[\x{20E3}]/u'
        );

        $nickname = preg_replace($pattern, '', $nickname);

        return trim($nickname);
    }

    /**
     * 请求URL链接
     * @param $url
     * @return mixed
     */
    public static function _request($url){
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_REFERER, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        $result = curl_exec($ch);
        curl_close($ch);
        return json_decode($result,true);
    }

    /**
     * 检测是否获取access_token成功
     * 0表示成功
     */
    public static function checkErrorCode($res){
        if($res['errcode'] != 0)
            return $res;
        return false;
    }

    /**
     * 传入一个开始时间 结束长度  类型
     * 返回剩余时间
     * @param null $beginTime
     * @param $len 时间长度
     * @param $type 时间类型 （day || week || month || year）
     * @return array
     * @author pengjch 2016-11-16
     */
    public static function timeDifference($beginTime=null,$len=1,$type='day'){
        if(!$beginTime)
            $beginTime = time();
        $beginTime = strtotime($beginTime);
        $endTime = strtotime("+$len $type",$beginTime);
        $nowTime = time();
        $second = $endTime - $nowTime;
        if($second<0){
            return '0';
        }
        $day = floor($second/(3600*24));
        $second = $second%(3600*24);//除去整天之后剩余的时间
        $hour = floor($second/3600);
        $second = $second%3600;//除去整小时之后剩余的时间
        $minute = floor($second/60);
        $second = $second%60;//除去整分钟之后剩余的时间
        return array('day'=>$day,'hour'=>$hour,'minute'=>$minute,'second'=>$second);
    }
}
