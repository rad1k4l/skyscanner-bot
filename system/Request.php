<?php

class Request
{
    private $dataType = "string";

    /*
     * string
     * */
    public function str() : Request
    {
        $this->dataType = 'string';
        return $this;
    }

    public function get(string $key , $default = false )
    {
        if(isset($_GET[$key]))
        {
            if ($this->dataType === 'string' && is_string($_GET[$key])){
                return (string)$_GET[$key];
            }elseif ($this->dataType !== 'string'){
                return $_GET[$key];
            }else{
                return $default;
            }
        }
        else {
            return $default;
        }
    }

    public function post(string $key , $default = false )
    {
        if(isset($_POST[$key]))
        {
            if ($this->dataType === 'string' && is_string($_POST[$key])){
                return (string)$_POST[$key];
            }elseif ($this->dataType !== 'string'){
                return $_POST[$key];
            }else{
                return $default;
            }
        }
        else {
            return $default;
        }
    }

    public function urn()
    {
        $scheme = $this->server('REQUEST_SCHEME');
        $domain = $this->server('SERVER_NAME');
        $urn = $scheme . "://" . $domain . $this->path();
        return $urn;
    }

    public function path() : string
    {
        $uri = $this->server('REQUEST_URI');
        return explode('?' , $uri)[0];
    }

    public function server(string $key)
    {
        return $_SERVER[$key];
    }
}