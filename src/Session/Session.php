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

    /**设置
     * @param $data
     * @param null $prefix
     * @throws SdkException
     */
    public function set($data,$prefix=null){
        $key = $this->getKey();
        $oldKey = $key;
        !empty($prefix)&&$key=$prefix.$key;
        $objRedis = ConsistentHash::getInstance()->lookUp($key);
        if(empty($objRedis)){
            throw new SdkException("获取redis节点实例失败");
        }
        $objRedis->setex($key,600,$data);
        $this->saveToCookie($oldKey);
    }

    /** 获取
     * @param null $prefix
     * @return bool|mixed|string
     * @throws SdkException
     */
    public function get($prefix=null){
        $key = $this->getKey();
        !empty($prefix)&&$key=$prefix.$key;
        if(empty($key)){
            return false;
        }
        $objRedis = ConsistentHash::getInstance()->lookUp($key);
        if(empty($objRedis)){
            throw new TSdkException("获取redis节点实例失败");
        }
        return $objRedis->get($key);
    }

    /**
     *
     */
    public function del(){
        setcookie(self::TAG,'',time()-1,"/");
    }

    /** 生成cookie
     * @param $key
     */
    private function saveToCookie($key){
        setcookie(self::TAG,$key,0,"/");
    }

    /** 获取key
     * @return mixed|string
     */
    private function getKey(){
        $key = isset($_COOKIE[self::TAG])?$_COOKIE[self::TAG]:"";
        empty($key)&&$key = $this->generateKey();
        return $key;
    }

    /** 生成唯一key
     * @return string
     */
    private function generateKey(){
        $hash = hash("sha256",$_SERVER['REMOTE_ADDR'].$_SERVER['HTTP_USER_AGENT'].microtime());
        $uniqid = str_replace(".","",uniqid('',true));
        $key = md5(strtoupper($hash.$uniqid));
        return $key;
    }

    public static function test(){
        echo 1;
    }

}