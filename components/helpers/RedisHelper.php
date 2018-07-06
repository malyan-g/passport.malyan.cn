<?php

/**
 * Created by PhpStorm.
 * User: M
 * Date: 17/10/26
 * Time: 下午1:06
 */
namespace app\components\helpers;

use Yii;
use yii\base\Object;
use yii\redis\Connection;

/**
 * Redis
 * Class RedisHelper
 * @package app\components\helpers
 */
class RedisHelper extends Object
{
    /**
     * RedisHelper
     * @var $_Instance
     */
    private static $_Instance = null;

    /**
     * @var Connection $redis
     */
    private $redis = null;

    /**
     * @var $key
     */
    private $key;

    /**
     * 实例化redis
     */
    public function init()
    {
        $this->redis = Yii::$app->redis;
    }

    /**
     * 获取实例
     * @return RedisHelper|null
     */
    public static function getInstance()
    {
        if(!self::$_Instance instanceof RedisHelper){
            self::$_Instance = new self;
        }
        return self::$_Instance;
    }

    /**
     * @param $key
     */
    private function setKey($key)
    {
        $this->key = $key;
        return $this;
    }

    /**
     * 返回匹配某个模式的键
     * @param string $pattern
     * @return mixed
     */
    public function keys($pattern = '*')
    {
        return $this->redis->keys($pattern);
    }

    /**
     * 清除当前库的所有键
     * @return mixed
     */
    public function flushDB()
    {
        return $this->redis->flushdb();
    }

    /**
     * 验证指定的键是否存在
     * @param $key
     * @return bool
     */
    public function exists($key)
    {
        return $this->redis->exists($key);
    }

    /**
     * 删除指定的键
     * @param $key
     * @return int
     */
    public function del($key)
    {
        return $this->redis->del($key);
    }

    /**
     * 设置指定键的过期时间(秒)
     * @param int $seconds
     * @return bool
     */
    public function expire($seconds = 0)
    {
        return $this->redis->expire($this->key, $seconds);
    }

    /**
     * 设置指定键的过期时间(毫秒)
     * @param int $milliseconds
     * @return bool
     */
    public function pExpire($milliseconds = 0)
    {
        return $this->redis->pexpire($this->key, $milliseconds);
    }

    /**
     * 哈希类型 设置指定键的值
     * @param $key
     * @param $hashKey
     * @param $value
     * @return $this
     */
    public function hSet($key, $hashKey, $value)
    {
        $this->redis->hset($this->setKey($key), $hashKey, $value);
        return $this;
    }

    /**
     * 哈希类型 获取指定键的值
     * @param $key
     * @param null $hashKey
     * @return string
     */
    public function hGet($key, $hashKey = null)
    {
        return $hashKey ? $this->redis->hget($key, $hashKey) : $this->redis->hgetall($key);
    }

    /**
     * 字符串类型 设置指定键的值
     * @param $key
     * @param $value
     * @return $this
     */
    public function set($key, $value)
    {
        $this->redis->set($this->setKey($key), $value);
        return $this;
    }

    /**
     * 字符串类型 获取指定键的值
     * @param $key
     * @return bool|string
     */
    public function get($key)
    {
        return $this->redis->get($key);
    }

    /**
     * 列表类型 设置指定键的值
     * @param $key
     * @param $value
     * @param bool $type
     * @return $this
     */
    public function listSet($key, $value, $type = true)
    {
        if($type){
            $this->redis->lpush($this->setKey($key), $value);
        }else{
            $this->redis->rpush($this->setKey($key), $value);
        }
        return $this;
    }

    /**
     * 列表类型 获取指定键的值
     * @param $key
     * @param int $start
     * @param int $end
     * @return array
     */
    public function listGet($key, $start = 0, $end = -1)
    {
        return $this->redis->lrange($key, $start, $end);
    }

    /**
     *
     * @param $key
     * @param $value
     * @return int
     */
    /**
     * 集合类型 设置指定键的值
     * @param $key
     * @param $value
     * @return $this
     */
    public function sAdd($key, $value)
    {
        $this->redis->sadd($this->setKey($key), $value);
        return $this;
    }

    /**
     * 集合类型 获取指定键的值
     * @param $key
     * @return array
     */
    public function sMembers($key)
    {
        return $this->redis->smembers($key);
    }

    /**
     *  集合类型 获取集合的成员数
     * @param $key
     * @return mixed
     */
    public function sCard($key)
    {
        return $this->redis->scard($key);
    }

    /**
     * 集合类型 检测指定的键中是否存在值
     * @param $key
     * @param $value
     * @return bool
     */
    public function sIsMembers($key, $value)
    {
        return $this->redis->sismember($key, $value);
    }

    /**
     * 集合类型 在指定的键中删除指定的值
     * @param $key
     * @param $value
     */
    public function sRem($key, $value)
    {
        $this->redis->srem($key, $value);
    }

    /**
     * 有序集合类型 设置指定键的值
     * @param $key
     * @param $score
     * @param $value
     * @return $this
     */
    public function zAdd($key, $score, $value)
    {
        $this->redis->zadd($this->setKey($key), $score, $value);
        return $this;
    }

    /**
     * 有序集合类型 获取指定键的值(按score从低到高)
     * @param $key
     * @param int $start
     * @param int $end
     * @return array
     */
    public function zRange($key, $start = 0, $end = -1)
    {
        return $this->redis->zrange($key, $start, $end);
    }

    /**
     * 有序集合类型 获取指定键的值(按score从高到低)
     * @param $key
     * @param int $start
     * @param int $end
     * @return array
     */
    public function zRevRange($key, $start = 0, $end = -1)
    {
        return $this->redis->zrevrange($key, $start, $end);
    }

    /**
     * 有序集合类型 获取指定键元素的个数
     * @param $key
     * @return int
     */
    public function zCard($key)
    {
        return $this->redis->zcard($key);
    }

    /**
     * 有序集合类型 在指定的键中删除指定的值
     * @param $key
     * @param $value
     * @return int
     */
    public function zRem($key, $value)
    {
        return $this->redis->zrem($key, $value);
    }
}
