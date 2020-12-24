<?php
include_once "connector.php";


(new \Help\Rabbit(function (){
    echo "[X] Connected\n";
}))->listen('root.ticket.processing',  function ($msg) {
    $raw = $msg->body;
    $entity = json_decode($raw);
    echo "[x] Received {$raw}\n";
    telegram(
        "#{$entity->req_id} nomreli sorgunuz ticket serverinde qebul olundu.",
        $entity->chat_id);
    echo "Telegram ack sended\n";


    $comb = new \processor\CombinationProcessor();

    $param = [
            "origin" =>         $entity->origin,  //\out\console::input("from ->"),
            "destination" =>    $entity->destination,  //\out\console::input("to -> "),
            "out-date" =>       implode('-', explode('.', $entity->out_date)),
            "children" =>       0,
            "adults" =>         1,
            "infants" =>        0,
            "redirect" =>       false,
            "cabin" =>          "economy",
            "locale" =>         "ru-RU",
            "currency" =>       "Azn",
        ];

    try{

        $res = $comb->start($param);
        telegram("#{$entity->req_id} nomreli sorgunuz:\n ". $res->getFormatted(),
            $entity->chat_id);
        \out\console::green("ticket sended {$entity->req_id}");
        unset($res, $comb);
    }catch (\Exception $exception){
        telegram("#{$entity->req_id} nomreli sorgunuzda xeta bash verdi\n\nXeta id: {$exception->getMessage()}", $entity->chat_id);
    }


});