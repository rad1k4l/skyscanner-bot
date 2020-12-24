<?php

use Facebook\WebDriver\WebDriverBy;
use \Facebook\WebDriver\Remote\DesiredCapabilities as Capabilities;
use \Facebook\WebDriver\Remote\RemoteWebDriver as WebDriver;

class Browser
{
    private $session;

    public function __construct()
    {
        $this->session = WebDriver::create(
            conf("browser.host"),
            Capabilities::firefox()
        );
    }

    public function url(){
        return $this->session->getCurrentURL();
    }

    public function select(string $selector) : \Facebook\WebDriver\Remote\RemoteWebElement {

        return $this->session->findElement(WebDriverBy::cssSelector($selector));
    }

    public function source() : string  {
        return $this->session->getPageSource();
    }

    public function click(string $selector) : bool{
        try{
            $this->select($selector)->click();
        }catch (Exception $exception)
        {
            return false;
        }

        return true;
    }

    public function get( string $url = "https://google.com" ){

        return $this->session->get($url);
    }

    public function getSession()
    {
        return $this->session;
    }

    public function __destruct()
    {
        try {
            $this->session->quit();
        }catch (\Exception $exception) {
            // set critival error
            $msg = $exception
                ->getMessage();
            $code = $exception->getCode();
            \out\console::print("Quit error With message -> " . $msg);
            \out\console::print("With error code " . $code);
//          TODO : set system status to critical error
        }
    }

}