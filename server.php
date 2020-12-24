<?php


// unlimited execution time
set_time_limit(0);

include_once "connector.php";


use Psr\Http\Message\ServerRequestInterface;
use React\EventLoop\Factory;
use React\Http\Response;
use React\Http\Server;

$host = 'http://localhost:4444/wd/hub';
function newDriver(&$driver) {
    global $host;
    $driver = \Facebook\WebDriver\Remote\RemoteWebDriver::create($host, \Facebook\WebDriver\Remote\DesiredCapabilities::firefox());
}
$driver = \Facebook\WebDriver\Remote\RemoteWebDriver::create($host, \Facebook\WebDriver\Remote\DesiredCapabilities::firefox());


echo "Established CONNECTION TO BROWSER\t[SUCCESS]\n";
$loop = Factory::create();

$server = new Server(function (ServerRequestInterface $request) use($driver) {
    $payload = $request->getBody()->getContents();
    $payload = trim($payload);
    $input = json_decode($payload, true);
    $data = "no output";

    if (isset($input['url'])){
        $site = $driver->get($input['url']);
        $data = $site->getPageSource();

        echo "requested GET " . $input['url'] . "\n";
    }else if(isset($input['source']) ){
         if(isset($driver)) { $data =  $driver->getPageSource(); } else echo "driver not isset\n";
    }elseif(isset($input['cmd']) ){
        if ($input['cmd'] == "restart"){
            $driver->executeScript("return window.open()");
//            $driver->quit();
            unset($driver);
            \out\console::print("quit webdriver");
//            newDriver($driver);
            $data= "[CMD] restart SUCCESS";

            \out\console::print($data);
        }
    }else{
        echo "not isset :\n";
        var_dump($input);
    }

   return  new Response(
        200,
        array(
            'Content-Type' => 'application/json'
        ),
        $data
    );

});
$socket = new \React\Socket\Server(isset($argv[1]) ? $argv[1] : '127.0.0.1:1144', $loop);
$server->listen($socket);
echo 'Listening' . str_replace('tcp:', 'http:', $socket->getAddress())  . "\t\t[SUCCESS]". PHP_EOL;

\out\console::print("Server is running\t[SUCCESS]");
$loop->run();


