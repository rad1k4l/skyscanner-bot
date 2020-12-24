<?php
namespace out;

class status {


    private static $now;
    private static $history = [];

    public static function set(string $status , int $code)
    {
        $this->now = $status;
        $this->history[ ] = $this->now;
        echo $status;
    }

    public static function get()
    {

    }
}