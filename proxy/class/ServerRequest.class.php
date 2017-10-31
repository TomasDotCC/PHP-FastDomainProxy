<?php

class ServerRequest extends Base
{

    public $headersout = array();
    private $contentout = "";

    private $cache;

    private $defaultoptions = array(
        CURLOPT_CONNECTTIMEOUT => 8,
        CURLOPT_TIMEOUT => 9,
        CURLOPT_MAXREDIRS => 5,
        CURLOPT_ENCODING => "",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_AUTOREFERER => true,
        CURLOPT_HEADER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false
    );

    public function setCache($cache)
    {
        $this->cache = $cache;
    }

    public function getHeadersOut(): array
    {
        $this->proxy->d("#FCE: " . get_class($this) . "::" . __FUNCTION__ . "()" . " <br /> <small>" . __FILE__ . ":" . __LINE__ . "</small>", func_get_args());
        return $this->headersout;
    }

    public function getContentOut(): string
    {
        $this->proxy->d("#FCE: " . get_class($this) . "::" . __FUNCTION__ . "()" . " <br /> <small>" . __FILE__ . ":" . __LINE__ . "</small>", func_get_args());
        return $this->contentout;
    }

    public function exec($refresh = false): bool
    {
        $this->proxy->d("#FCE: " . get_class($this) . "::" . __FUNCTION__ . "()" . " <br /> <small>" . __FILE__ . ":" . __LINE__ . "</small>", func_get_args());

        $curl = curl_init();

        // URL
        $blacklist = explode("\n", file_get_contents($this->proxy->root() . "/proxy/etc/blacklist.txt"));
        foreach ($blacklist as $b) {
            if (isset($b) AND $b != '' AND strstr($this->url, $b)) {
                $this->proxy->end("Sorry, but requested URL has been blocked.");
            }
        }
        $ip = gethostbyname($this->proxy->getConfig('gethost'));
        if (!$ip OR $ip == $_SERVER['SERVER_ADDR'] OR $ip == "127.0.0.1") {
            $this->proxy->end("Weird IP or No IP!");
        }

        // Options
        foreach ($this->defaultoptions as $key => $val) {
            curl_setopt($curl, $key, $val);
        }

        // HTTP Method
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $_SERVER['REQUEST_METHOD']);

        // Headers
        $headers = array();
        foreach ($this->headers as $name => $val) {
            $headers[] = $name . ": " . trim($val);
        }
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);


        //TODO unused - proxy this proxy :)  - make config compatible

        /*

        $proxy = "178.94.173.22:8080";

        if(0 AND $proxy) {
            $this->proxy->d('___ Setting proxy ___', $proxy);
            curl_setopt($curl, CURLOPT_PROXY, $proxy);
        }

        */


        // TODO unused - make config compatible
        // Pick random IP as source IP - all IPs need to be correctly assigned to server
        // Save every IP as content of dedicated file /proxy/etc/ip[number 1-9999].txt
        // Example:
        // File /proxy/etc/ip1.txt - 1.2.3.4
        // File /proxy/etc/ip2.txt - 1.2.3.5
        // File /proxy/etc/ip3.txt - 1.2.3.6

        /*

        $ip = rand(1, 3);
        $ip = file_get_contents($this->proxy->root() . "/proxy/etc/ip" . $ip . ".txt");

        $this->proxy->d("__USING IP__", $ip);

        curl_setopt($curl, CURLOPT_INTERFACE, $ip);

        */

        $key = md5($this->url);

        if (!$refresh) $ret = $this->cache->get($key);

        if (!isset($ret) OR !$ret) {
            $this->proxy->d("____GETTING REMOTE URL_______ ", $this->url);
            if (isset($this->url) AND $this->url) curl_setopt($curl, CURLOPT_URL, trim(str_replace("/Styles/", "/styles/", $this->url)));
            $ret = curl_exec($curl);
            $err = curl_error($curl);
            if ($err AND 0) {
                if ($refresh) exit;
                $this->proxy->d("CURL ERROR!!!!!!!!!!!!!!", $err);
                if ($this->proxy->debug) {
                    die("CURL ERROR!!!! " . $err . print_r(curl_getinfo($curl), true));
                } else {
                    header("Location: //" . $this->proxy->getConfig("myhost") . "?error=1");
                    exit;
                }
            }
            $this->proxy->d("CURL info", curl_getinfo($curl));
            if (trim($ret)) $this->cache->set($key, $ret);

            if (!trim($ret) AND $this->cache->lastold) $ret = $this->cache->lastold;

        }

        $content = explode("\r\n\r\n", $ret);

        $d = 0;
        foreach ($content as $contx) {
            $cont = explode("\n", $contx);
            $cont = explode(" ", $cont[0]);
            if ($d == 0 AND isset($cont[1]) AND substr($cont[1], 0, 1) == "3") continue;
            if ($d == 0) {
                $headers = explode("\n", $contx);
                foreach ($headers as $h) {
                    $h = explode(": ", $h);
                    if (isset($h[1]) AND trim($h[1])) $this->headersout[$h[0]] = trim($h[1]);
                }
                $d = 1;
                continue;
            } else {
                $this->contentout .= $contx;
            }
        }
        return true;
    }

}