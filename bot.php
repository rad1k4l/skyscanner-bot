<?php
include_once "connector.php";

//    $sky =  new \crawler\SkyscannerFromCrawler();
    $sky = new \crawler\SkyScannerCrawler();
    //    $comb = new \processor\CombinationProcessor();


    $param = [
        "origin" =>         "gyd",  //\out\console::input("from ->"),
        "destination" =>    "shja",  //\out\console::input("to -> "),
        "out-date" =>       "21-01-20",
        "children" =>       0,
        "adults" =>         1,
        "infants" =>        0,
        "redirect" =>       false,
        "cabin" =>          "economy",
        "locale" =>         "en-US",
        "currency" =>       "azn",
    ];
    $data = $sky->get($param, true, 100);
//    $data = $comb->start($param);

    ($data->print());
    \out\console::input();