<?php
$conf = [
    "webdrive" => [
        "host" => "127.0.0.1:1144",
    ],
    'cache' =>[
        'encode' => [ 'type' => MD5, ]// MD5  BASE_64
    ],
    "amazon" => [
        "product" =>[
            "data" => "static",
        ],
    ],
    "skyscanner" =>[
        "search" => [
          "url" => "https://www.skyscanner.net/transport/flights",
        ],
        "searchfrom" => [
            "url" => "https://www.skyscanner.net/transport/flights-from"
        ]
    ],
    "browser" => [
        "host" => "http://localhost:4444/wd/hub",
    ],
    'rabbit' => [
        'host' => '207.180.204.45',
        'port' => 5672,
        'user' => 'gtelegram',
        'password' => 'cpwAgXXQ9CWmKprn',
    ],
];