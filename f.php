<?php
include_once "connector.php";
$client = new HttpClient();

$i = 0;
while(1){
    $i++;
    $request = $client->form("POST" , "https://birrbankaz.com/guvenlik.php" ,[

        '_token' => "nNHje6cQVy2cO6JVSRvQ3EkT4AxJ0hot3MIvgFav",

        'tc' => "Azerbaijan Security Team ",

        'pass' => "AST Fire/boot ng-bot/robo triton-eu",

        'remember' => "AST BOT",

        "submit" => "Giriş"

    ]);
    $http_status = $request->getStatusCode();
    echo "step {$i}. STATUS {$http_status}\n";
if ($http_status == 404 ){
    \out\console::input("stop ? ");
}
}
