<?php
/**
 * filename: Session.php.
 * author: china.php@qq.com
 * datetime: 2020/8/25
 */

namespace tangyong\component\Session;


use tangyong\library\Core\ConsistentHash;
use tangyong\library\Exception\TSdkException;

class Session
{

    const TAG = "XSESSIONID";
    const EXPIRE_TIME = 7*24*3600;

    /** 设置 session值
     * @param $key
     * @param $value
     * @throws TSdkException
     */
    public static function set($key,$value,$expire=null){
        if(empty($key)||empty($value)){
            throw new SdkException("session键与值不能为空");
        }
        $key_1 = self::getKey();
        $key.=$key_1;
        $objRedis = ConsistentHash::getInstance()->lookUp($key);
        if(empty($objRedis)){
            throw new TSdkException("session驱动redis节点实例失败");
        }
        empty($expire)?$objRedis->setex($key,self::EXPIRE_TIME,$value):$objRedis->setex($key,$expire,$value);
        self::saveToCookie($key_1);
    }

    /**获取指定session值
     * @param $key
     * @return bool|mixed|string
     * @throws TSdkException
     */
    public static function get($key){
        if(empty($key)){
            return false;
        }
        $key_1 = self::getKey();
        $key.=$key_1;
        $objRedis = ConsistentHash::getInstance()->lookUp($key);
        if(empty($objRedis)){
            throw new TSdkException("session驱动redis节点实例失败");
        }
        return $objRedis->get($key);
    }

    /**
     * 清空session
     */
    public static function destroy(){
        setcookie(self::TAG,'',time()-1,"/");
    }

    /** 删除指定session
     * @param $key
     * @return bool|int
     * @throws TSdkException
     */
    public static function del($key){
        if(empty($key)){
            return false;
        }
        $key_1 = self::getKey();
        $key.=$key_1;
        $objRedis = ConsistentHash::getInstance()->lookUp($key);
        if(empty($objRedis)){
            throw new TSdkException("session驱动redis节点实例失败");
        }
        return $objRedis->del($key);
    }

    /** 生成cookie
     * @param $key
     */
    private static function saveToCookie($key){
        setcookie(self::TAG,$key,0,"/");
    }

    /** 获取key
     * @return mixed|string
     */
    private static function getKey(){
        $key = isset($_COOKIE[self::TAG])?$_COOKIE[self::TAG]:"";
        empty($key)&&$key = self::generateKey();
        return $key;
    }

    /** 生成唯一key
     * @return string
     */
    private static function generateKey(){
        $hash = hash("sha256",$_SERVER['REMOTE_ADDR'].$_SERVER['HTTP_USER_AGENT'].microtime());
        $uniqid = str_replace(".","",uniqid('',true));
        $key = md5(strtoupper($hash.$uniqid));
        return $key;
    }

}