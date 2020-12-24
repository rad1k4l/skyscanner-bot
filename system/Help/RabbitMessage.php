<?php


namespace Help;


use PhpAmqpLib\Message\AMQPMessage;

class RabbitMessage
{
    public static function msg($data)
    {
        if (is_array($data))
            $msg = json_encode($data);
        else if (is_string($data))
            $msg = $data;
        else
            return false;

        return self::create($msg);
    }

    public static function create(string $text)
    {
        return new AMQPMessage($text);
    }
}
