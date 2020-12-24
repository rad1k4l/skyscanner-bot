<?php
namespace processor;

use crawler\SkyScannerCrawler;
use crawler\SkyscannerFromCrawler;
use out\console;

class CombinationProcessor extends Processor implements IProcessable
{

    private $params;
    private $results;
    private $browser;
    public function __construct()
    {
        $this->browser = new \Browser();
    }

    public function getFormatted() {
        $str = '';
        foreach ($this->results as $k =>  $result) {
            $ticket_n = $k +1 ;
            $str .= "Bilet {$ticket_n}\n";


            $str .= "{$result['origin1']} => {$result['destination1']} qiymet: {$result['price1']} {$this->params['currency']}\n";

            $str .= "{$result['origin2']} => {$result['destination2']} qiymet: {$result['price2']} {$this->params['currency']}\n";

            $str .= "Total: {$result['price']} {$this->params['currency']}\n";
            $str .= "---------\n";
        }
        return $str;
    }

    /**
     * @return mixed
     */
    public function getResults()
    {
        return $this->results;
    }

    public function start( $params ) {
        $this->params = $params;

        return $this->processing();
    }

    public function print(){

        print_r($this->results);
    }


    private function processing(){
        $result = [ ];

        $fromAnyWayRoutes = (new SkyscannerFromCrawler($this->browser))
            ->get($this->params, [
                'direct' => true
            ])
            ->getResult();

        console::yellow("from anyway routes: ");
        console::green($fromAnyWayRoutes);

        foreach ($fromAnyWayRoutes as $k => $route)
        {
            $rlink =  $route['link'];
            if ( empty($rlink) )
                continue;
            if ($rlink[0] !== '/')
                $rlink = '/'.$rlink;
            $url = "https://www.skyscanner.net" . $rlink;
            $params = $this->params;
            $cheap = [];
            for ($tries = 0; $tries < 2; $tries++) {
                $proute = (new SkyScannerCrawler($this->browser))
                    ->get($url)
                    ->print()
                    ->getResults();
                $cheap = $proute['cheap'];
                if (empty($cheap))
                    console::red("[i] FR COMB HYSTATE 1: empty pair detected");
                else break;
            }
            if (empty($cheap))
            {
                throw new \Exception("Error Route parse etmek olmur {$url}");
            }
            $pairRoute[] = $proute;
            unset($proute, $tries);
            $cheapOrigin = $cheap[0]['origin'];
            $cheapDestination = $cheap[0]['destination'];

            $params['origin'] = $cheapDestination;

            for ($tries = 0; $tries < 2; $tries++) {
                $proute  = (new SkyScannerCrawler($this->browser))
                    ->get($params)
                    ->print()
                    ->getResults();
                $cheap = $proute['cheap'];
                if (empty($cheap))
                    console::red("[i] FR COMB HYSTATE 2: empty pair detected");
                else break;
            }
            if (empty($cheap)){
                throw new \Exception("Error Route parse etmek olmur \n".
                json_encode($params));
            }
            $pairRoute[] = $proute;
            unset($proute, $tries);

            $p1 =  $pairRoute[0]['cheap'][0];
            $p2 =  $pairRoute[1]['cheap'][0];
            $result[] = [
                "origin1" => $p1['origin'],
                'destination1' => $p1['destination'],
                'price1' => $p1['price'],

                'origin2' => $p2['origin'],
                'destination2' => $p2['destination'],
                'price2' => $p2['price'],
                'price' => ((int)$p1['price']) + ((int)$p2['price'])
            ];
            unset($pairRoute, $p1 , $p2);
            console::yellow("[*]\tTICKET -> {$k}\t[COLLECTED]");
            if ($k > 3)
                break;
        }



        $this->results = $result;
        return $this;
    }


}