<?php

class bot
{

    public static function start(){

//        print_r($splitted);

        self::load();
//        echo conf("am
//azon")['product']['price.selectors'][0];
//        $data = \cache::get('productanalyzer.list');
//        $var= $data[100];
//////        var_dump($var);
//        print_r($var);
////        var_dump($var);
//        $g = new \Request\Google();
//        $l = $g->search(trim($var['name'] ));
//        print_r($l->getLinks());
//        echo $l::getReqcount();
//        $links = $l->getLinks();
//        $safe= \crawler\Amazon::link($links);
//        print_r($safe);
//        $amazon = new \crawler\Amazon();
//        $url  = "https://www.amazon.com/UGREEN-Network-Ethernet-Supports-Nintendo/dp/B00MYTSN18";
//        $img = $amazon->craw($url);
////        echo "<img src = '{$img}'>";
//        var_dump($img);
//        print_r($img);
//        echo cache::get($url);
//        $request = new HttpClient();
//        $q = $_GET['q'];
//        $q = explode(" " , $q);
//        $q = implode("+" , $q);
//
//        $ungoogle = new \Request\UnGoogle();
//        $ungoogle->search($q);
//        print_r($ungoogle->getPayload());
//        echo "started check products\n";
//        self::checkProducts();

//        $req = $request->send("https://www.amazon.com/Phantom-YoYo-Dupont-Cable-Female/dp/B00KOL5BCC" , false);
//
//        echo "<pre>";
//            print_r($req['obj']->getHeaders());
//        echo "</pre>";
//
////        echo $req['payload'];
//        $doc  = new \DiDom\Document($req['payload']);
//        $hidden1 = $doc->first("body > div > div.a-row.a-spacing-double-large > div.a-section > div > div > form > input[type=hidden]:nth-child(1)");
//        $hidden2 = $doc->first("body > div > div.a-row.a-spacing-double-large > div.a-section > div > div > form > input[type=hidden]:nth-child(2)");
//
//        $img = $doc->first("body > div > div.a-row.a-spacing-double-large > div.a-section > div > div > form > div.a-row.a-spacing-large > div > div > div.a-row.a-text-center > img");
//        echo "hidden1 value: " . $hidden1->attr("value") ."<br>";
//        echo "hidden1 name: " . $hidden1->attr("name") ."<br>";
//        echo "hidden2 value: " . $hidden2->attr("value") ."<br>";
//        echo "hidden2 name: " . $hidden2->attr("name") ."<br>";
//
//
//        echo $img->attr('src') ."<br>";
//        $payload = $request->browser("https://azebot.ga", false) ['payload'];
//        print_r($payload);

//        print_r(\crawler\Amazon::link(["https://www.amazon.com/dsf-review"]));
    }

    public static  function checkProducts() {
        $products = \cache::get('productanalyzer.list');
        $passed = false;
        foreach ($products as $k =>  $product) {
            if( !isset($product['name'])  || empty($product['name']) || $product['name'] ==null ){
                $ans = \out\console::input("index {$k} is empty var dump ? ");
                if($ans == "y") var_dump($product);
                elseif ($ans =='exit') break;

            }
            $passed = true;
        }

        echo $passed ? "check passed\n" : '';
    }

    public static function load(){  
    
        return (new \processor\Processing())
                                        ->start();
    }

}