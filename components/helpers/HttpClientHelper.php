<?php

/**
 * Created by PhpStorm.
 * User: M
 * Date: 17/10/26
 * Time: 下午2:29
 */
namespace app\components\helpers;

use yii\base\Object;
use yii\httpclient\Client;
use Codeception\Command\Clean;

/**
 * HttpClient ( php composer.phar require --prefer-dist yiisoft/yii2-httpclient )
 * Class HttpClientHelper
 * @package app\components\helpers
 */
class HttpClientHelper extends Object
{
    /**
     * 请求
     * @param $url
     * @param string $method
     * @param string $data
     * @param array $headers
     * @param string $format
     * @return array|mixed
     */
    public static function request($url, $method = 'get', $data = '', $headers = [], $format = '')
    {
        $request = ( new Client())->createRequest();
        $request->setMethod($method)->setUrl($url);
        if($headers){
            $request->setHeaders($headers);
        }
        if($data){
            $request->setData($data);
        }
        if($format){
            $request->setFormat($format);
        }
        $response = $request->send();
        return $response->getData();
    }
}
