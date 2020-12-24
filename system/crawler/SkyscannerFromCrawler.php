<?php
namespace crawler;

use DiDom\Document;
use out\console;

class SkyscannerFromCrawler extends Crawler
{
    private $browser;
    private $result;
    private $direct;
    private $limit = 0;

    public function __construct( $browser = null)
    {
        if (!$browser)
            $this->browser = new \Browser();
        else if ($browser instanceof  \Browser)
            $this->browser = $browser;
    }

    public function print()
    {
        print_r($this->result);
    }

    /**
     * @return mixed
     */
    public function getResult()
    {
        return $this->result;
    }

    public function get(array $params = [], bool $direct = false, int $limit = 0) : SkyscannerFromCrawler {
        $this->limit = $limit;
        $this->direct = $direct;
        $crawd = [];
        $url = $this->createUrl($params);
        for ($i = 0; ; $i++) {
            console::print("Try craw products {$i}");
            $this->getPayload($url);

            if ($direct)
            {
                $selected = false;
                while(!$selected)
                    $selected = $this->browser->click("#filter-direct-stops-input");
            }
            $payload = $this->browser->source();

            console::yellow("[I] checkbox clicked state: " .(int)$selected);

            $opened = $this->open($payload);

            if($i > 4) {
//                TODO : set system failure / restart browser
                throw new \Exception("Cannot open tickets", 100120);
            }

            if($opened === false) continue;
            $payload = $this->browser->source();
            $crawd = $this->craw($payload);
            break;
        }
        $this->result = $crawd;
        return $this;
    }

    public function getTicketSelectors($num) {
        return "#browse-section > div.result-list > ul > li:nth-child({$num}) > a > div.chevron-down ";
    }


    public function _open($tickets) : bool {
        if (empty($tickets)) {
            console::print("tickets empty -> " . count($ticket));
            return false;
        }
        foreach ($tickets as $k =>  $ticket) {
           $num  = $k+1;
           $selector = $this->getTicketSelectors($num);
           for ($i = 0; ; $i++)
           {
               try{
                   $this->browser
                       ->select($selector)
                       ->click();
                   break;
               }catch (\Exception $exception)
               {
                   console::print("Error ocurred try {$i}");
                   if ( $i >= 3 )
                   {
                       console::print("Error ocurred try limit exceed");
                       console::print("Send restart signal\t[RESTART]");
                       return false;
                   }
               }
           }
            console::print($selector."\t[OPENED]");
            if ($num >= $this->limit ) break;
        }
        console::print("Opened all success");
        return true;
    }

    public function getPayload( string  $url) {
        $site = $this->browser->get($url);
        sleep(1);
        while (true){
            $payload = $site->getPageSource();

            if ( self::loadedPayload($payload) ){
                console::print("\tContent loaded");
                return $payload;
            }
            console::print("\tContent not loaded");
            sleep(1);
        }
    }

    public function open(string $payload) : bool {
        $doc = new Document($payload);
        $tickets = Crawler::fd($doc, [
            "#browse-section > div.result-list > ul > li"
        ]);
        $opened = $this->_open($tickets);
        $str =  $opened == false ? "false" : "true";
        console::print("open returned {$str}");
        return $opened;
    }


    private function craw(string $payload = ""){
        $doc = new Document($payload);
        $tickets = Crawler::fd($doc, [
            "#browse-section > div.result-list > ul > li",
            ".browse-list-category",
        ]);
        foreach ($tickets as $k => $ticket) {
            $num = $k + 1;
            if($num >= $this->limit) break;

            $cities = Crawler::fd($ticket, [ 'ul > li ']);

            foreach ($cities as $city) {
                $crawdCity = $this->crawCity($city);
                $result[] = $crawdCity;
            }

        }
        return $result;
    }

    public function crawCity($city)
    {
        $link = Crawler::f($city, [
            "a.flightLink",
            " div > div.browse-data-entry > a.flightLink"
        ], '', false);

        $price = Crawler::f($city, [
                ".price.flightLink",
                "div > div.browse-data-entry > a.flightLink > div > span",
        ]);

        return  [
            "link" => $link !== null ? $link->attr("href"): -1,
            "currency" => "AZN",
            "price" => $price !== null ? Crawler::onlyDigit($price->text()) : -1,
        ];
    }

    public function createUrl(array $params){

        $slices = [
            "/{$params['origin']}",
            "/" . SkyScannerCrawler::date($params['out-date']),
            "/?adults={$params['adults']}",
            "&children={$params['children']}",
            "&adultsv2={$params['adults']}",
            "&childrenv2={$params['children']}",
            "&infants={$params['infants']}",
            "&cabinclass={$params['cabin']}",
            "&preferdirects={$params['redirect']}",
            "&outboundaltsenabled=false&inboundaltsenabled=false&ref=home",
            "&locale={$params['locale']}",
            "&currency={$params['currency']}",
            "&ref=home",
        ];

        return $this->slices(conf("skyscanner.searchfrom.url") , $slices);
    }

    public function slices(string $url , array $slices = []) : string {
        foreach ($slices as $slice) {
            $url .= $slice;
        }
        return $url;
    }

    public static function loadedPayload(string $payload) : bool {
        $doc = new Document($payload);
        $find = Crawler::f($doc, [
            "#browse-section > div.result-list > ul > li:nth-child(1) > a > div.chevron-down"
        ]);
        return $find !== null ? true : false;
    }


}