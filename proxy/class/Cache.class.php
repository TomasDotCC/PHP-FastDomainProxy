<?php

// Caching is VERY VERY important
// This proxy script was successfully tested with 3 millions IPs per month (avg. 500 requests per second) only due to caching.
//
// In my situation I found out that people use proxy for so many different sites
// that short-term caching is almost useless and take large amount of memory.
// Better is to get as many hard drives as you can and as big as you can.
// Few (for high traffic 10TB) TBs are recommended.
//
// Also I recommend to make /var/www/html/cache (by default) dedicated partition.
//
// TODO short-term caching for html, css and js

class Cache
{

    private $type = 'file';

    public $lastex = false;
    public $lastold = false;

    private $proxy;

    public $dirs;
    public $files;

    function __construct(Proxy $proxy)
    {
        $this->proxy = $proxy;
    }

    public function setType($val)
    {
        $this->type = $val;
    }

    public function get(string $key): string
    {
        if ($this->type == 'file') return $this->getFile($key);
        $this->proxy->d("ERROR! Cache type is not set!");
        return false;
    }

    public function set(string $key, string $val): bool
    {
        if ($this->type == 'file') return $this->setFile($key, $val);
        $this->proxy->d("ERROR! Cache type is not set!");
        return false;
    }

    private function getFile(string $key): string
    {

        $okeyarr = str_split($key);
        $keyarr = array();
        for ($i = 0; $i < 7; $i++) {
            $keyarr[] = $okeyarr[$i];
        }
        $keypath = implode("/", $keyarr);
        $mtime = filemtime($this->proxy->getConfig("location", "cache") . "/" . $keypath . "/" . $key);
        $dif = (time() - $mtime);
        $c = false;
        $cc = false;

        $this->lastex = false;
        $this->lastold = false;

        if (file_exists($this->proxy->getConfig("location", "cache") . "/" . $keypath . "/" . $key)) {


            if ($dif > $this->proxy->getConfig("minage", "cache")) {
                if ($dif < $this->proxy->getConfig("maxage", "cache")) {
                    $c = file_get_contents($this->proxy->getConfig("location", "cache") . "/" . $keypath . "/" . $key);
                    $cc = true;
                    $pc = "old=" . $dif;
                    $this->lastex = $this->proxy->getConfig("location", "cache") . "/" . $keypath . "/" . $key;
                } else {
                    $pc = "expired=" . $dif;
                    $this->lastold = file_get_contents($this->proxy->getConfig("location", "cache") . "/" . $keypath . "/" . $key);
                }
            } else {
                $pc = "fresh=" . $dif;
                $c = file_get_contents($this->proxy->getConfig("location", "cache") . "/" . $keypath . "/" . $key);
                $cc = true;
                $this->proxy->d("Getting file from cache...", array("location" => $this->proxy->getConfig("location", "cache") . "/" . $keypath . "/" . $key, "diff" => $dif));
            }


        } else {
            $pc = "miss";
        }


        if ($cc AND !trim($c)) {
            @unlink($this->proxy->getConfig("location", "cache") . "/" . $keypath . "/" . $key);
            $pc = "NBlank";
        }
        if ($pc) {
            $this->proxy->server_request->headersout['Pc'] = $pc;
        }
        if (!trim($c)) return false;
        return $c;

    }

    private function setFile(string $key, string $val): bool
    {
        $okeyarr = str_split($key);
        $keyarr = array();
        for ($i = 0; $i < 7; $i++) {
            $keyarr[] = $okeyarr[$i];
        }
        $keypath = implode("/", $keyarr);
        if (!@file_put_contents($this->proxy->getConfig("location", "cache") . "/" . $keypath . "/" . $key, $val)) {
            $ap = $this->proxy->getConfig("location", "cache") . "/" . $keypath;
            @mkdir($ap, true);
            file_put_contents($this->proxy->getConfig("location", "cache") . "/" . $keypath . "/" . $key, $val);
        }
        $this->proxy->d("Saving file to cache... (will be done at end of script) ", array("location" => $this->proxy->getConfig("location", "cache") . "/" . $keypath . "/" . $key, "size" => strlen($val)));
        return true;


    }


    public function end()
    {

        foreach ($this->dirs as $d) {
            @mkdir($d, true);
        }

        foreach ($this->files as $f => $v) {
            file_put_contents($f, $v);
        }

        return true;
    }

}