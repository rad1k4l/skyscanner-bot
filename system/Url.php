<?php


class Url
{

    public static function type(string $word) : string {
        $word = explode(" " , $word);
        return implode("+" , $word);
    }

    public static function unique(array $links) : array {
        $result = [];
        foreach ($links as $link) {
            if (self::in($result , $link)=== false){
                $result[]  = $link;
            }
        }
        return $result;
    }

    public static function in(array $data , string $query) : bool {
        foreach ($data as $qu) {
            if ($qu == $query)
                return true;
        }
        return false;
    }

}