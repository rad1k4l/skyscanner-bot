<?php
    $client = new HttpClient();
//    $request = new Request();

    function client() : HttpClient{
        global $client;
        return $client;
    }


    function endKey($arr)
    {
        end($arr);
        return key($arr);
    }

//    function request(){
//        global $request;
//        return $request;
//    }


    function telegram(string $text, $chat_id)
    {
        (new \Help\Rabbit())->send('root.telegram.text.sender', [
            'chat_id'=> $chat_id,
            'text' => $text
        ]);
    }

    function conf(string $confname ) : string {
        global $conf;
        $confs = explode('.',$confname);
        $data = $conf;
        foreach ($confs as $config){
            if (isset($data[$config])){
                $data = $data[$config];
            }else{
                throw  new Exception("Configuration not found exception" , 10008);
            }
        }
        return $data;
    }

    function dd($data){
        print_r($data);
        exit;
    }