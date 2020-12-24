<?php

namespace crawler;

use DiDom\Document;
use out\console;

class Crawler
{
    /**
     * Secure search dom element
     * @author Orkhan Zeynalli
     * @param Document $doc
     * @param array $selectors
     * @param string $url
     * @return \DiDom\Element|\DOMElement|null
     */
    public static function f( $doc, array $selectors , string $url =  "undefined", bool $debug = false)  {
        if (is_string($doc))
            $doc = new Document($doc);
        foreach ($selectors as $selector) {
            $find = $doc->first($selector);
            if($find !== null ){
                if ($debug) console::green("[I] crawler::f selector debug selected '{$selector}'");
                return  $find;
            }
        }
        return null;
    }

    /**
     * @param Document $doc
     * @param array $selectors
     * @param string $url
     * @return array|null
     */
    public static function fd( $doc, array $selectors , string $url ="undefined") : ?  array  {
        if (is_string($doc))
            $doc = new Document($doc);
        foreach ($selectors as $selector) {
            $find = $doc->find($selector);
            if($find !== null )
                return  $find;
        }
        return null;
    }



    public static function onlyDigit(string $str){
        $digits = [
            '.', '3', '7',
            '0', '4', '8',
            '1', '5',  '9',
            '2', '6',
        ];
        $count = strlen($str);
        $newstr = "";
        for ($i = 0; $i < $count; $i++) {
            foreach ($digits as $digit) {
                $symb = $str[$i];
                if ($symb === $digit) {
                    $newstr .= $symb;
                }
            }

        }
        return $newstr;
    }
}