<?php

class UserRequest extends Base
{

    public $parsed_url = array();

    public function __construct(Proxy $proxy)
    {
        parent::__construct($proxy);
        $this->proxy->d("#FCE: " . get_class($this) . "::" . __FUNCTION__ . "()" . " <br /> <small>" . __FILE__ . ":" . __LINE__ . "</small>", func_get_args());
        // Get headers
        foreach ($_SERVER as $name => $value) {
            if (substr($name, 0, 5) == 'HTTP_') {
                $this->headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
            }
        }

        // Get URL
        if (isset($_SERVER['REQUEST_SCHEME'])) $url[0] = $_SERVER['REQUEST_SCHEME'] . "://";
        if (isset($_SERVER['HTTP_HOST'])) $url[1] = $_SERVER['HTTP_HOST'];
        if (isset($_SERVER['REQUEST_URI'])) $path_query = explode("?", $_SERVER['REQUEST_URI']);
        if (isset($path_query[0])) $url[2] = $path_query[0];
        if (isset($path_query[1])) $url[3] = "?" . $path_query[1];
        $this->url = implode("", $url);
        $this->parsed_url = parse_url($this->url);
        $this->proxy->d("Parsed URL from user request.", $this->url);

        return true;
    }

}