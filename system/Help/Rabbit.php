<?php


namespace Help;

use Help\RabbitMessage;
use PhpAmqpLib\Connection\AMQPStreamConnection as RabbitConnection;

class Rabbit
{
    private $channel;
    private $link;
    private $qType = 'topic';

    public function __construct(\Closure $onConnect = null)
    {
        try {
            $this->link = new RabbitConnection(
                conf('rabbit.host'),
                conf('rabbit.port'),
                conf('rabbit.user'),
                conf('rabbit.password'));
        } catch (\Exception $e) {
            exit("Error\n");
        }
        if ($onConnect !== null)
            $onConnect();
        $this->channel = $this->link->channel();
    }

    public function listen(string $route, $callback, $exchange = 'root'){
        $this->channel->exchange_declare($exchange,
            $this->qType,
            false,
            false,
            false);
        list($queue_name, ,) = $this->channel->queue_declare("",
            false,
            false,
            true,
            false);
        $this->channel->queue_bind($queue_name,
            $exchange,
            $route);


        $this->channel->basic_consume($queue_name,
            '',
            false,
            true,
            false,
            false,
            $callback);

        while ($this->channel->is_consuming()) {
            $this->channel->wait();
        }
    }

    public function send($route, $msg, $exchange = 'root') : Rabbit
    {
        $this->channel->exchange_declare($exchange,
            $this->qType,
            false,
            false,
            false);

        $this->channel->basic_publish(
            RabbitMessage::msg($msg),
            $exchange,
            $route);
        return $this;
    }

    public function __destruct()
    {
        $this->channel->close();
        $this->link->close();
    }
}
