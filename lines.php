<?php
define('sep', DIRECTORY_SEPARATOR);
$folder  = __DIR__.sep.'system';

function c($folder){

    $lines = 0;
    $files = scandir($folder);
    foreach ($files as $file) {

        if ($file == '.' || $file == '..'  ) continue;

        $slice = explode('.',$file);
        $count = count($slice);
        if ($count > 1){
            if ($slice[$count-1] !== 'php') continue;
        }

        if (is_dir($folder.sep.$file)){
            $lines += c($folder.sep.$file);
        }else{
            $fl = file($folder.sep.$file);
            $lines += count( $fl == false ?  [] : $fl);
        }


    }

    return $lines;
}

echo c($folder);