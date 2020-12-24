<?php


class cache
{
    public static $dir  = "cache";
    public static $ext = ".static";


    public static function encode(string $key){
        $encoded = $key;

        switch (conf("cache.encode.type")) {
            case MD5:
                $encoded = md5($key);
                break;
            case BASE_64:
                $encoded = base64_encode($key);
                break;
        }

        return $encoded;
    }

    public static function set($key , $data){
        return;
        $file = __DIR__ . DIRECTORY_SEPARATOR . self::$dir . DIRECTORY_SEPARATOR .  self::encode($key) . self::$ext ;
        if(file_exists($file)){
//            echo "FATAL ERROR ON CACHE SYSTEM : DUBLICATE ENTRY !\n debug info ";
//            echo "file : " . $file;
        }
        $data = serialize($data);
        file_put_contents($file, $data);
    }

    public static function has(string $key , $default = false){

        $file = __DIR__ . DIRECTORY_SEPARATOR . self::$dir . DIRECTORY_SEPARATOR .   self::encode($key) . self::$ext ;

        return file_exists($file) ? true : $default;
    }

    public static function get($key, $default = null ){
        $file = __DIR__ . DIRECTORY_SEPARATOR . self::$dir . DIRECTORY_SEPARATOR .  self::encode($key). self::$ext ;
        if(file_exists($file)){
            $data = file_get_contents($file);
            return unserialize($data);
        }
        return $default;
    }

    public static function setorget($key, $callable)
    {
        if(self::has($key)){
            return self::get($key);
        }
        $data = $callable();
        self::set($key , $data);

        return $data;
    }

    public static function delete(string $key) : bool{

        $file  = __DIR__ . DIRECTORY_SEPARATOR . self::$dir . DIRECTORY_SEPARATOR .  self::encode($key). self::$ext ;
        return unlink($file);
    }

}