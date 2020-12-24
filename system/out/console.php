<?php
namespace out;

class console{


    private static $colors = [
        'black'	=>'0;30',
        'dark-grey'	=>'1;30',
        'red'	=>'0;31',
        'light-red'	=>'1;31',
        'green'	=>'0;32',
        'light-green'	=>'1;32',
        'brown'	=>'0;33',
        'yellow'	=>'1;33',
        'blue'	=>'0;34',
        'light-blue'=>'1;34',
        'magenta'	=>'0;35',
        'light-magenta'	=>'1;35',
        'cyan'	=>'0;36',
        'light-cyan'	=>'1;36',
        'light-grey'	=>'0;37',
        'white'	=>'1;37',

    ];

    /**
     * @return bool|resource
     */
    public static function clean(){
        return popen('cls', 'w');
    }
    public static function print($data, $color = "white", $endl = true){

        $color = self::$colors[$color];

        echo "\e[{$color};40m";
        print_r($data);
        echo "\e[0m";
        if($endl == true)  echo "\n";

    }

    public static function input($text = false){
        if($text !== false) self::print($text);
        return trim(fgets(STDIN));
    }


    public static function __callStatic($name, $arguments)
    {
        if (isset(self::$colors[$name])) {
            self::print($arguments[0] , $name, isset($arguments[1]) ? $arguments[1] : true);
        }
    }

    public function __call($name, $arguments)
    {
        if (isset(self::$colors[$name])){
            self::print($arguments[0], $name , $arguments[1]);
        }
    }
}