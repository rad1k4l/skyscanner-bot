<?php

namespace crawler;
use DiDom\Document;
use Help\Str;
use http\Client;
use HttpClient;
use out\console;

class SkyScannerCrawler
{
    public $client;
    public static $dateSplitter = "-";
    private $tabs = [
        'optimal' => [
            'selector' =>   [
                'li[role=tab]:nth-child(1) > button',
                'button[role=button]:nth-child(1)',
            ],
        ],
        'cheap' => [
            'selector' =>   [
                'li[role=tab]:nth-child(2) > button',
                'button[role=button]:nth-child(2)',
            ],
        ],
        'fast' => [
            'selector' =>   [
                'li[role=tab]:nth-child(3) > button',
                'button[role=button]:nth-child(3)',
            ],
        ],
    ];
    private $results = [];

    /**
     * @return array
     */
    public function getResults()
    {
        return $this->results;
    }

    public function print() : SkyScannerCrawler {
        console::yellow(
            "ticks:"
        );
        console::yellow(
           $this->params
        );
        $tickects = $this->results;
        foreach ($tickects as $type =>  $ticket){

            console::green(
                "|\t{$type}\t|"
            );
            if (empty($ticket))
                console::yellow("(empty)");
            foreach ($ticket as $route){
                extract($route);
                console::yellow("{$origin} {$origin_time} ->", false);
                foreach ($stops as $stop) {
                    console::yellow(" $stop ->", false);
                }

                console::yellow(" {$destination} {$destination_time} {$price}");
                unset($origin, $destination, $price);
            }
        }
        unset($ticket, $tickects);
        return $this;
    }

    public function __construct($browser = null)
    {
        if (!$browser)
            $this->client = new \Browser();
        else if ($browser instanceof  \Browser)
            $this->client = $browser;
    }
    private $params;
    public function get($param) {

        $url = is_array($param) ? $this->createUrl($param) : $param;
        $this->params =  $param;
        console::yellow(
            "GETticks:"
        );
        console::yellow(
            $this->params
        );
        $tabs = [
            "optimal",
            "cheap",
            "fast"
        ];

        $this->getPayload($url);
        $tabcount = count($this->tabs);
        $success = 0;
        for ($i = 0;;$i++)
        {
            $tab = $tabs[$success];
            if ($tab !='optimal')
            if (!$this->openTab($tab))
            {
                $this->getPayload($url . "&tms=".time());
                continue;
            }
//            console::yellow("waiting load tab");

            while ( !$loaded) {

                $payload = $this->client->source();
                $loaded = self::loadedPayload($payload);
            }

            sleep(1);
            $payload = $this->client->source();

//            console::green("tab loaded");
            $crawed =  $this->craw($payload);
            $tickets[$tab] = $crawed;
            if ($tab == 'fast')
                break;
            $success++;
        }
        $this->results = $tickets;
        return $this;
    }

    public function openTab($tabname) : bool {
        $selectors = $this->tabs[$tabname]['selector'];
        foreach ($selectors as $k => $selector) {
            try {
//                console::green("opening tab {$tabname}");
                $this->client
                    ->select($selector)
                    ->click();
                return true;
            } catch (\Exception $exception) {
                console::red("[X] error ocurred while open {$tabname} tab try selector {$k}");
                console::blue("[*]send restart signal\t[RESTART]");
//                return false;
            }
        }
        return false;
    }

    public function checkVar($data){
        $msg = $data === null ? "null" : "not_null";
        return $msg;
    }



    public function closeCookieBanner(){
        $sbanner = "div#cookie-banner-root";
        $payload = $this->client->source();

        $banner = Crawler::f($payload, [$sbanner]);

        if ($banner !== null)
        {
            if(!empty(trim($banner->text())))
            {
                while(!$this->client->click($sbanner . " button"));
                return true;
            }
        }

        return false;
    }


    public function openModal(int $num)
    {
        while(!$this->client->click("div.EcoTicketWrapper_itineraryContainer__1VGlu:nth-child({$num}) button"));
    }

    public function closeModal()
    {
        while (!$this->client->click("button.BpkCloseButton_bpk-close-button__JyGa2"));
    }
    public function getModal(int $num) : array
    {
        $result = [];
        $this->openModal($num);
        while (!$this->client->click("div#modal-container button.LegSummary_container__25maG"));
        $segmentInfo = [];
        $modal = Crawler::f($this->client->source(),["#modal-container"])->html();
        $legSegmentDetails = Crawler::fd($modal, ['div.LegSegmentSummary_container__3A9zq > div.LegSegmentDetails_container__6E_85']);
        $connectionRows = Crawler::fd($modal, ['div.LegSegmentSummary_container__3A9zq > div.Connection_connectionRow__2omfG']);
        $airlineLogos = Crawler::fd($modal, ['div.LegSegmentSummary_container__3A9zq > div.AirlineLogoTitle_container__2k6xV']);
        foreach ($legSegmentDetails as $k => $segment) {

            console::green("[+] segment {$k}");
            $duration = Crawler::f($segment, [
                '.Duration_duration__1QA_S',
                'span',
            ])->text();
            $times = Crawler::fd($segment, ['.Times_segmentTimes__2eToH > div > span:nth-child(1)']);
            $routes = Crawler::fd($segment, ['.Routes_routes__3lp8G > span']);
            $airlogo = Crawler::f($airlineLogos[$k], ['.TicketLogo_image__86dhu img'])->attr('src');
            $airname = Crawler::f($airlineLogos[$k], ['span'])->text();

            $connection = isset($connectionRows[$k]) ? Crawler::f($connectionRows[$k], [
                '.Connection_splitDuration__Xyckw > span',
                'div:nth-child(1) > span'
            ])->text() : '';


            $segmentInfo[] = [
                'origin' => [ 'code' => $routes[0]->text() , 'time' => $times[0]->text() ],
                'destination' => [ 'code' => $routes[1]->text(), 'time' => $times[1]->text() ],
                'airline' => ['name' => $airname, 'logo' => $airlogo ],
                'connection' => isset($connection) ? $connection : '',
            ];
        }
        $pricingItems = Crawler::fd($modal, ['.PricingItem_container__1rH_B']);

        $prices = [];
        foreach ($pricingItems as $k => $pricingItem) {
            $agentName = Crawler::f($pricingItem, ['.AgentDetails_agentContainer__kp8JA > span:nth-child(1)'])->text();
            $price = Crawler::f($pricingItem, [
                'div.Price_mainPriceContainer__1dqsw > span',
                '.PricingItem_price__3bRSw span',
                '.PricingItem_price__3bRSw',
                'div.Price_mainPriceContainer__1dqsw',

            ]);
            if (is_null($price)) continue;
            $prices[] = [
                'name' => $agentName,
                'price' => $price->text(),
            ];

        }



        $result = [
            'segment' => $segmentInfo,
            'prices' => $prices
        ];
        console::yellow($result);

        $this->closeModal();
        return $result;
    }

    private function craw(string $payload){

        $doc = new Document($payload);
        $tickets = Crawler::fd($doc ,[
            'div.EcoTicketWrapper_itineraryContainer__1VGlu',
        ]);

        console::yellow(
            "tickets type: " . $this->checkVar($tickets) . " => ". count($tickets)
        );


        $result = [];
        foreach ($tickets as $k =>  $ticket)
        {
            $this->closeCookieBanner();
            console::green("[x] ticket {$k}");
            $num = $k + 1;
            // open modal
            $modalInformation = $this->getModal($num);
            // craw modal

            $modal = Crawler::f($ticket, ['div#modal-container']);

            $price = Crawler::f($ticket, [
                'div.Price_mainPriceContainer__1dqsw > span ',
                "div.EcoTicketWrapper_itineraryContainer__1VGlu:nth-child({$num}) > a:nth-child(1) > div:nth-child(1) > div:nth-child(3) > div:nth-child(1) > div:nth-child(2) > div:nth-child(1) > span:nth-child(1)",
                ' a:nth-child(1) > div:nth-child(1) > div:nth-child(3) > div:nth-child(1) > div:nth-child(2) > div:nth-child(1) > span:nth-child(1)',
                'a > div > div > div > div > div > span',
                'a.FlightsTicket_link__kl4DL:nth-child(3) > div:nth-child(1) > div:nth-child(3) > div:nth-child(1) > div:nth-child(2) > div:nth-child(1) > span:nth-child(1)'
            ]);

            $origin  = Crawler::f($ticket, [
                '.LegInfo_routePartialDepart__37kr9 > span:nth-child(2) > span',
                "span:nth-child(2) > span ",
            ]);

            $originTime  = Crawler::f($ticket, [
                '.LegInfo_routePartialDepart__37kr9 > .LegInfo_routePartialTime__2HfzB > div > span',
                "span:nth-child(2) > span ",
            ]);

            $destination = Crawler::f($ticket , [
                "div.LegInfo_routePartialArrive__ZsZxc > span:nth-child(2) > span"
            ]);
            $destinationTime = Crawler::f($ticket, [
                "div.LegInfo_routePartialArrive__ZsZxc > span:nth-child(1) > div > span",
            ]);

            $stops = Crawler::fd($ticket, [
                '.LegInfo_stopStation__Ec5OU > span'
            ]);


            $st = [];
            foreach ($stops as $stop) {
                $st[] = $stop->text();
            }

            $undefined = 'undefined';
            if ($origin!==null)
            $result[] = [
                'origin' => $origin !== null ? $origin->text() : $undefined,
                'origin_time' => $originTime !== null ? $originTime->text() : $undefined,

                'destination' => $destination !== null ? $destination->text() : $undefined,
                'destination_time' => $destinationTime !== null ? $destinationTime->text() : $undefined,

                'price' => $price != null ? Crawler::onlyDigit($price->text()) : $undefined,
                'stops' => $st
            ];
        }
        unset($ticket, $tickets);

        return $result;
    }


    public function getPayload(string  $url){
        $site = $this->client->get($url);
        sleep(1);
        for ($i= 0;;$i++) {
            $payload = $site->getPageSource();
            if ( self::loadedPayload($payload) ) {
//                \out\console::green("Content loaded");
                sleep(2);
                break;
//                if ($i >= 1) break;
            } else {
//                \out\console::red("Content Not loaded");
            }
            sleep(1);
        }
        return $payload;
    }

    public function parseUrl(string $url) : array {

        $slashSlices = Str::separate($url, '/');
        $params = [
            'origin' => $slashSlices[0],
            'destination' => $slashSlices[1],

        ];
        return $params;
    }

    public function createUrl(array $params){
        $slices = [
            "/{$params['origin']}",
            "/{$params['destination']}",
            "/" . self::date($params['out-date']),
                isset($params['in-date']) ? "/". self::date($params['in-date']) : '',
             "/?adults={$params['adults']}",
             "&children={$params['children']}",
             "&adultsv2={$params['adults']}",
             "&childrenv2=",
             "&infants={$params['infants']}",
             "&cabinclass={$params['cabin']}",
             "&preferdirects={$params['redirect']}",
             "&outboundaltsenabled=false&inboundaltsenabled=false&ref=home",
             "&locale={$params['locale']}",
             "&currency={$params['currency']}",
//             "#results",
         ];
        return $this->slices(conf("skyscanner.search.url") , $slices);
    }

    public function slices(string $url , array $slices = []) : string {
        foreach ($slices as $slice) {
            $url .= $slice;
        }

        return $url;
    }

    public static function date(string $date): string{

        $splitted = explode(self::$dateSplitter ,trim($date));
        $splitted = array_reverse($splitted);
        return implode("" , $splitted);
    }

    public static function loadedPayload(string $payload):bool {
        sleep(2);
        $doc = new Document($payload);
        $loader = Crawler::f($doc, [
//            'div.FlightsInline_link__3gl0m', // reklama !
            'div.Results_dayViewItems__3dVwy > div > div > div > section > span',
            'div.EcoTicketWrapper_itineraryContainer__1VGlu > div > div > section > span',
            '#app-root > div > svg',
            '.BpkSpinner_bpk-spinner--align-to-button__2zevS > path:nth-child(5)',
            'h3[class=updating-filters]',
            'div.FlightsDayView_container__2bGun > div.FlightsDayView_results__1kZSn > div:nth-child(1) > div.ResultsSummary_container__u1KEN > div.ResultsSummary_innerContainer__EqwUq > div.ResultsSummary_summaryContainer__3_ZX_ > div.SummaryInfo_progressTextContainer__3Y_uW > span:nth-child(2)',
            'div.FlightsDayView_container__2bGun > div.FlightsDayView_results__1kZSn > div:nth-child(1) > div.ResultsSummary_container__u1KEN > div.ResultsSummary_innerContainer__EqwUq > div.ResultsSummary_summaryContainer__3_ZX_ > div.SummaryInfo_progressTextContainer__3Y_uW',
            'div.FlightsDayView_container__2bGun > div.FlightsDayView_results__1kZSn > div:nth-child(1) > div.ResultsSummary_container__u1KEN > div.ProgressBar_container__1EjEG > div > div',
            'div.FlightsDayView_container__2bGun > div.FlightsDayView_results__1kZSn > div:nth-child(1) > div.Results_dayViewItems__3dVwy > div:nth-child(1) > a > div > div.BpkTicket_bpk-ticket__paper__2gPSe.BpkTicket_bpk-ticket__stub__UMQSf.Ticket_stub__3xY04.BpkTicket_bpk-ticket__stub--padded__3Sxur.BpkTicket_bpk-ticket__stub--horizontal__2mGXV.BpkTicket_bpk-ticket__paper--with-notches__19yQc > div > div > div > svg',
        ]);
        $element = Crawler::f($doc, [
            'li.day-list-item:nth-child(1)',
            'div.EcoTicketWrapper_itineraryContainer__1VGlu:nth-child(2)',

        ]);

//        console::yellow("check payload status loader: ");
//        console::green($loader == null ? "null" : "object");
//        console::yellow("check payload status element: ");
//        console::green($element == null ? "null" : "object");

        return $loader === null   ? true : false;
    }
}