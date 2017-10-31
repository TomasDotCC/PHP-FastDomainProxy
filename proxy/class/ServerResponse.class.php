<?php

class ServerResponse extends Base
{

    public function __construct(Proxy $proxy)
    {
        parent::__construct($proxy);
        $this->proxy->d("#FCE: " . get_class($this) . "::" . __FUNCTION__ . "()" . " <br /> <small>" . __FILE__ . ":" . __LINE__ . "</small>", func_get_args());
        return true;
    }

    public function replaceLinks(): bool
    {
        $this->proxy->d("#FCE: " . get_class($this) . "::" . __FUNCTION__ . "()" . " <br /> <small>" . __FILE__ . ":" . __LINE__ . "</small>", func_get_args());
        $type = explode(";", $this->getHeader("Content-Type"));
        $type = $type[0];
        if ($type == "text/html") {
            $this->replaceLinksHTML();
            $this->injectCode();
            return $this->content;

        } elseif ($type == "text/css") {
            return $this->replaceLinksCSS();

        } elseif ($type == "text/plain") {
            return $this->replaceLinksTXT();

        } elseif ($type == "application/rss+xml" OR $type == "application/xml") {
            return $this->replaceLinksRSS();

        } elseif ($type == "application/javascript" OR $type == "application/x-javascript") {
            return $this->replaceLinksJS();

        }
        return true;
    }


    private function replaceLinksHTML(): bool
    {
        $this->proxy->d("#FCE: " . get_class($this) . "::" . __FUNCTION__ . "()" . " <br /> <small>" . __FILE__ . ":" . __LINE__ . "</small>", func_get_args());

        $this->content = str_replace(str_replace("www.", "", $this->proxy->getConfig("gethost")) . ".", "##CDN-PROTECT##.", $this->content);
        $this->content = str_replace("//" . str_replace("www.", "", $this->proxy->getConfig("gethost")) . "/", "//" . str_replace("www.", "", $this->proxy->getConfig("gethost")) . "." . $this->proxy->getConfig("myhost") . "/", $this->content);
        //$this->content = str_replace(array("href='//", 'href="//'), array("href='http://", 'href="http://'), $this->content);
        preg_match_all("@(https?:)?//[-a-zA-Z0-9_&\@./?=#]+@", $this->content, $links);
        foreach ($links[0] as $l) {
            if (strstr($l, $this->proxy->getConfig("myhost"))) continue;
            $e = explode("/", $l);
            $e[2] = $e[2] . "." . $this->proxy->getConfig("myhost");
            $replace[$l] = implode("/", $e);
        }
        if (isset($replace)) foreach ($replace as $k => $v) {
            $this->content = str_replace($k, $v, $this->content);
        }

        $this->content = preg_replace("/.*<body[^>]*>/", "\\0 <script> setInterval(\"document.my = '" . $this->proxy->getConfig("myhost") . "';\", 50); </script>", $this->content);

        //$this->content = str_replace("https://", "http://", $this->content);
        $this->content = str_replace("type='password'", "type='text' value='Sorry, Can't Login via Proxy!' disabled style='cursor: pointer !important;' ", $this->content);
        $this->content = str_replace('type="password"', 'type="text" value="Sorry, Can\'t Login via Proxy!" disabled style="cursor: pointer !important;" ', $this->content);
        $this->content = str_replace("##CDN-PROTECT##.", str_replace("www.", "", $this->proxy->getConfig("gethost")) . ".", $this->content);
        $this->content = str_replace($this->proxy->getConfig("myhost") . "." . $this->proxy->getConfig("myhost"), $this->proxy->getConfig("myhost"), $this->content);
        $this->content = str_replace($this->proxy->getConfig("myhost") . "." . $this->proxy->getConfig("myhost"), $this->proxy->getConfig("myhost"), $this->content);

        return true;
    }

    private function injectCode(): bool
    {
        $this->proxy->d("#FCE: " . get_class($this) . "::" . __FUNCTION__ . "()" . " <br /> <small>" . __FILE__ . ":" . __LINE__ . "</small>", func_get_args());
        //if(!strstr("</html>", $this->content)) return false;
        ob_start();
        include("./proxy/etc/script.php");
        $script = ob_get_contents();
        ob_end_clean();
        $code = str_replace(array("\n", "\t"), " ", $script);
        $this->content = str_replace("</head>", $code . "</head>", $this->content);
        return true;
    }

    private function replaceLinksCSS(): bool
    {
        $this->proxy->d("#FCE: " . get_class($this) . "::" . __FUNCTION__ . "()" . " <br /> <small>" . __FILE__ . ":" . __LINE__ . "</small>", func_get_args());
        return true;
    }

    private function replaceLinksRSS(): bool
    {
        $this->proxy->d("#FCE: " . get_class($this) . "::" . __FUNCTION__ . "()" . " <br /> <small>" . __FILE__ . ":" . __LINE__ . "</small>", func_get_args());
        return $this->replaceLinksJS($this->content);
    }

    private function replaceLinksTXT(): bool
    {
        $this->proxy->d("#FCE: " . get_class($this) . "::" . __FUNCTION__ . "()" . " <br /> <small>" . __FILE__ . ":" . __LINE__ . "</small>", func_get_args());
        return $this->replaceLinksJS($this->content);
    }

    private function replaceLinksJS(): bool
    {
        $this->proxy->d("#FCE: " . get_class($this) . "::" . __FUNCTION__ . "()" . " <br /> <small>" . __FILE__ . ":" . __LINE__ . "</small>", func_get_args());;
        $this->content = str_replace(str_replace("www.", "", $this->proxy->getConfig("gethost")), str_replace("www.", "", $this->proxy->getConfig("gethost")) . "." . $this->proxy->getConfig("myhost"), $this->content);
        $this->content = str_replace("https://", "http://", $this->content);
        return true;
    }

    public function replaceText(): bool
    {
        $this->proxy->d("#FCE: " . get_class($this) . "::" . __FUNCTION__ . "()" . " <br /> <small>" . __FILE__ . ":" . __LINE__ . "</small>", func_get_args());
        $this->proxy->d("Replace from INI", print_r($this->proxy->getConfig("replace", "general"), true));
        if ($this->proxy->getConfig("replace", "general")) {
            $this->proxy->d("General replace", $this->proxy->getConfig("replace", "general"));
            foreach ($this->proxy->getConfig("replace", "general") as $r) {
                $r = explode("###", $r);
                $this->content = str_replace($r[0], $r[1], $this->content);
            }
        }

        if ($this->proxy->homepage) {
            $this->proxy->d("Homepage replace", $this->proxy->getConfig("replace", "homepage"));
            if ($this->proxy->getConfig("replace", "homepage")) {
                foreach ($this->proxy->getConfig("replace", "homepage") as $r) {
                    $r = explode("###", $r);
                    $this->content = str_replace($r[0], $r[1], $this->content);
                }
            }
            $this->content = str_replace($this->proxy->getConfig("gethost") . "." . $this->proxy->getConfig("myhost"), $this->proxy->getConfig("myhost"), $this->content);
        }

        $this->content = str_replace(array("http://", "https://"), "//", $this->content);

        return true;
    }


}