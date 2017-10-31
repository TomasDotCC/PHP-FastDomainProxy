<?php

class Base
{


    protected $proxy;

    protected $headers = array();
    protected $content;
    protected $url;


    public function __construct(Proxy $proxy)
    {
        $this->proxy = $proxy;
        $this->proxy->d("#FCE: " . get_class($this) . "::" . __FUNCTION__ . "()" . " <br /> <small>" . __FILE__ . ":" . __LINE__ . "</small>", func_get_args());
        $this->proxy->d("CONFIG", $this->proxy->config);
        return true;
    }


    public function setHeaders(array $headers): bool
    {
        $this->proxy->d("#FCE: " . get_class($this) . "::" . __FUNCTION__ . "()" . " <br /> <small>" . __FILE__ . ":" . __LINE__ . "</small>", func_get_args());
        $this->headers = $headers;
        return true;
    }

    public function setHeader(string $key, string $val): bool
    {
        $this->proxy->d("#FCE: " . get_class($this) . "::" . __FUNCTION__ . "()" . " <br /> <small>" . __FILE__ . ":" . __LINE__ . "</small>", func_get_args());
        $this->headers[$key] = $val;
        return true;
    }

    public function unsetHeader(string $key): bool
    {
        $this->proxy->d("#FCE: " . get_class($this) . "::" . __FUNCTION__ . "()" . " <br /> <small>" . __FILE__ . ":" . __LINE__ . "</small>", func_get_args());
        unset($this->headers[$key]);
        return true;
    }

    public function unsetHeaders(array $keys): bool
    {
        $this->proxy->d("#FCE: " . get_class($this) . "::" . __FUNCTION__ . "()" . " <br /> <small>" . __FILE__ . ":" . __LINE__ . "</small>", func_get_args());
        foreach ($keys as $key) unset($this->headers[$key]);
        return true;
    }

    public function setContent(string $content): bool
    {
        $this->proxy->d("#FCE: " . get_class($this) . "::" . __FUNCTION__ . "()" . " <br /> <small>" . __FILE__ . ":" . __LINE__ . "</small>", func_get_args());
        $this->content = $content;
        return true;
    }

    public function setUrl(string $url): bool
    {
        $this->proxy->d("#FCE: " . get_class($this) . "::" . __FUNCTION__ . "()" . " <br /> <small>" . __FILE__ . ":" . __LINE__ . "</small>", func_get_args());
        $this->url = $url;
        return true;
    }

    public function getHeaders(): array
    {
        $this->proxy->d("#FCE: " . get_class($this) . "::" . __FUNCTION__ . "()" . " <br /> <small>" . __FILE__ . ":" . __LINE__ . "</small>", func_get_args());
        if (isset($this->headers)) return $this->headers;
        return array();
    }

    public function getHeader(string $key): string
    {
        $this->proxy->d("#FCE: " . get_class($this) . "::" . __FUNCTION__ . "()" . " <br /> <small>" . __FILE__ . ":" . __LINE__ . "</small>", func_get_args());
        if (isset($this->headers[$key])) return $this->headers[$key];
        return "";
    }

    public function getContent(): string
    {
        $this->proxy->d("#FCE: " . get_class($this) . "::" . __FUNCTION__ . "()" . " <br /> <small>" . __FILE__ . ":" . __LINE__ . "</small>", func_get_args());
        if (isset($this->content)) return $this->content;
        return "";
    }

    public function getUrl(): string
    {
        $this->proxy->d("#FCE: " . get_class($this) . "::" . __FUNCTION__ . "()" . " <br /> <small>" . __FILE__ . ":" . __LINE__ . "</small>", func_get_args());
        if (isset($this->url)) return $this->url;
        return "";
    }

}