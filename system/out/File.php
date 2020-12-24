<?php

namespace out;

class File
{
    public static $lastFileName = "";
    public static $dir = DIRECTORY_SEPARATOR;
    public static function output(string $name = "") : string
    {
        $dir = DIRECTORY_SEPARATOR;
        self::$lastFileName =   __DIR__ .$dir .  "..". $dir."..". $dir."output" . $dir . $name;
        return self::$lastFileName;
    }

    public static function copy(string $source, string $destination){
        $src  = self::content($source);
        return self::save($destination , $src);
    }

    public static function view(string $name = ""){
        $dir = DIRECTORY_SEPARATOR;
        self::$lastFileName =   __DIR__ . $dir.".." . $dir . "renderer" . $dir . "views". $dir . $name;
        return self::$lastFileName;
    }

    public static function content(string $path){
        return file_get_contents($path);
    }

    public static function save(string $filename , string $data){
        return file_put_contents($filename, $data);
    }

    public static function delete(string $file){
        return unlink($file);
    }

    public static function inc(string $file){
        return include $file;
    }

    public static function folder(string $dir, string $dirname){
        $path = $dir . $dirname;
        mkdir($path);
        return $path;
    }
}