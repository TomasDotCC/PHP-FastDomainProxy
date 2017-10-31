<?php

    class Proxy {

        public $user_request;
        public $server_request;
        public $server_response;
        public $user_response;

        public $memcache;
        public $config = array();

        public $debug = false; 
        public $debug_file = "";

        private $root = "";

        public $homepage = false;

        public function __construct(){

            ob_start();

            if($_COOKIE['debug'] OR $_GET['debug']){
                $this->debug = true;
                $this->debug_file = "debug/log-" . rand(0, 200) . ".html";
            }

            if($this->debug==true){
                ini_set( "display_errors", "on" );
                error_reporting( E_ALL );
                ini_set('display_errors', 1);
                ini_set('display_startup_errors', 1);
            } else {
                ini_set( "display_errors", "off" );
                error_reporting( -1 );
                ini_set('display_errors', 0);
                ini_set('display_startup_errors', 0);              
            }

        }

        public function baseConfig(): bool {
            $this->d("#FCE: " . get_class($this) . "::" . __FUNCTION__ . "()" . " <br /> <small>" . __FILE__ . ":" . __LINE__ . "</small>", explode(".", $_SERVER['HTTP_HOST']));
            $host = explode(".", $_SERVER['HTTP_HOST']);
            $this->d("Pre-set myhost", $host);
            $host_count = count($host) - 1;
            $this->setConfig("myhost", $host[$host_count-1] . "." . $host[$host_count]);
            $this->d("Set myhost", $host[$host_count-1] . "." . $host[$host_count]);
            return true;
        }

        public function iniConfig(): bool {
            $this->d("#FCE: " . get_class($this) . "::" . __FUNCTION__ . "()" . " <br /> <small>" . __FILE__ . ":" . __LINE__ . "</small>", $this->config);

            if(file_exists("./proxy/domains/global.ini")){
                $this->mergeConfig(parse_ini_file("./proxy/domains/global.ini", true));
            } else {
                $this->d("Can't load global INI config file!", "./proxy/domains/global.ini");
            }
            if(file_exists("./proxy/domains/" . $this->getConfig("myhost") . ".ini")){
                $this->mergeConfig(parse_ini_file("./proxy/domains/" . $this->getConfig("myhost") . ".ini", true));
                $this->d("Domain INI loaded");
            } else {
                $this->d("Can't load domain INI config file!", "./proxy/domains/" . $this->getConfig("myhost") . ".ini");
            }

            return true;
        }

        public function finalConfig(): bool {
            $this->d("#FCE: " . get_class($this) . "::" . __FUNCTION__ . "()" . " <br /> <small>" . __FILE__ . ":" . __LINE__ . "</small>", func_get_args());
            $host = explode(".", $_SERVER['HTTP_HOST']);
            $host_count = count($host) - 1;
            unset($host[$host_count-1]);
            unset($host[$host_count]);

            $this->d("Final Config 1", $this->config);

            if(!$this->getConfig("gethost") AND !is_numeric($host[count($host)-1])) $this->setConfig("gethost", implode(".", $host));

            $this->d("Final Config 2", $this->config);

            if($this->getConfig("include", "general")){
                $this->d("Merge general include configs", $this->getConfig("include", "general"));
                foreach($this->getConfig("include", "general") as $h){
                    $this->mergeConfig(parse_ini_file("./proxy/domains/" . $h, true));
                }
            }

            $this->d("Final Config 3", $this->config);

            if((!trim($this->getConfig("gethost")) OR $this->getConfig("isip")) AND $this->getConfig("include", "homepage")){
                $this->d("Merge homepage include configs", $this->getConfig("include", "homepage"));
                foreach($this->getConfig("include", "homepage") as $h){
                    $this->mergeConfig(parse_ini_file("./proxy/domains/" . $h, true));
                }
            }

            $this->d("Final Config 4", $this->config);

            if($this->getConfig("debug", "general")){
                $this->debug = $this->getConfig("debug", "general");
                $this->debug_file = "debug/log-" . rand(0, 200) . ".html";
            }

            $this->d("Final Config 5", $this->config);

            return true;
        }

        public function serveStatic(){
            $this->d("#FCE: " . get_class($this) . "::" . __FUNCTION__ . "()" . " <br /> <small>" . __FILE__ . ":" . __LINE__ . "</small>", func_get_args());
            die("static");
        }

        public function setConfig(string $key, string $val, string $group="default"): bool {
            $this->config[$group][$key] = $val;
            return true;
        }

        public function getConfig(string $key, string $group="default") {
            if(isset($this->config[$group]) AND isset($this->config[$group][$key])) return $this->config[$group][$key];
            return "";
        }

        public function mergeConfig(array $newconfig): bool {
            $this->d("#FCE: " . get_class($this) . "::" . __FUNCTION__ . "()" . " <br /> <small>" . __FILE__ . ":" . __LINE__ . "</small>", func_get_args());
            $this->config = array_replace_recursive($this->config, $newconfig);
            return true;
        }

        public function checkStatic() {

            if(!$this->getConfig("gethost") OR !strstr($this->getConfig("gethost"), ".") OR $this->getConfig("isip")){

                if($this->getConfig("gethost") AND !strstr($this->getConfig("gethost"), ".")){
                    header("Location: //" . $this->getConfig("myhost") . $this->user_request->parsed_url['path'] . "?" . $this->user_request->parsed_url['query']);
                    exit;
                }

                if((preg_match("/\.well-known\//", $this->user_request->parsed_url['path']) OR preg_match("@\.[a-zA-Z0-9]{1,5}$@", $this->user_request->parsed_url['path'])) AND file_exists("." . $this->user_request->parsed_url['path']) AND !strstr($this->user_request->parsed_url['path'], ".php")){
                    echo file_get_contents("." . $this->user_request->parsed_url['path']);
                    exit;
                }

                if($this->getConfig("site", "homepage") OR $this->getConfig("isip")){
                    $this->d("Marking request as homepage!");
                    $this->setConfig("gethost", $this->getConfig("site", "homepage"));
                    $this->user_request->setHeader("Host", $this->getConfig("gethost"));
                    $this->user_request->setUrl(str_replace($this->getConfig("myhost"), $this->getConfig("gethost"), $this->user_request->getUrl()));
                    $this->homepage = true;
                    return;
                }

                if(($this->user_request->parsed_url['path']=="/" OR $this->user_request->parsed_url['path']=="/index.php") AND file_exists($this->root() . "/proxy/etc/web.php")){
                    ob_end_clean();
                    include_once($this->root() . "/proxy/etc/web.php");
                    exit;
                }

                $g = "";
                foreach($_GET as $gg) $g .= " " . $gg;
                $query = $this->user_request->parsed_url['path'] . " " . $g;

                $query = urldecode($query);

                $query = preg_replace("@[-_ \?\.()\[\]\+\\/,0-9&=%:]+@", " ", $query);
                $query = " " . preg_replace("@[ ]+@", " ", $query) . " ";
                $query = str_replace(array(" php ", " html ", " htm ", " aspx ", " asp ", "https", "http"), "", $query);
                $query = trim($query);


                $query = urlencode($query);


                header("Location: http://duckduckgo.com." . $this->getConfig('myhost') . "/html?q=" . $query . "&ia=web");
                exit;

            }

            return;
        }

        public function d(string $msg, $data=false): bool {
            //if(!$this->debug==true) return false;
            $t = round(microtime(true) - $_SERVER["REQUEST_TIME_FLOAT"], 3);
            echo "\n\n\n<br><br><br>\n";
            echo "<hr><h1>" . $t . "s - " . $msg . "</h1><hr><br><pre>" . print_r($data, true);
            echo "</pre>\n\n\n<br><br><br>\n";
            return true;
        }

        public function end(string $msg=""){
            $this->d("#FCE: " . get_class($this) . "::" . __FUNCTION__ . "()" . " <br /> <small>" . __FILE__ . ":" . __LINE__ . "</small>", func_get_args());
            if($this->debug){
                print_r(debug_backtrace());
                print_r($this);
                $f = get_defined_functions();
                print_r(get_declared_classes());
                print_r($f['user']);
                print_r(get_defined_vars());
                $debug = ob_get_contents();
                file_put_contents("./" . $this->debug_file, $debug);
                if($this->debug==2) exit;
            }

            ob_end_clean();
            if($msg!=""){
                if($this->debug){
                    header("Debug: http://" . $this->getConfig('myhost') . "/" . $this->debug_file);
                }
                header("Location: //" . $this->getConfig('myhost'));
                //die($msg);
            }
        }

        public function root(string $set="") : string {
            if($set) $this->root = $set;
            return $this->root;
        }

        public function checkAllowed($type) : bool {
            $host = $this->getConfig("gethost");
            if(preg_match("/html/", $type)){
                if($this->getConfig("host", "allowed")) foreach($this->getConfig("host", "allowed") as $h){
                    if(strstr($host, $h)) return true;
                }
                return false;
            }
            return true;
        }

        public function log($type, $size, $url, $took){

            if(!$this->getConfig("enabled", "log")) return;

            $line[] = time();
            $line[] = $this->getConfig("myhost");
            $line[] = $this->getConfig("gethost");
            $line[] = $type;
            $line[] = $size;
            $line[] = $took;
            $line[] = $url;
            $line[] = $_SERVER['REMOTE_ADDR'];
            $line[] = $_SERVER['HTTP_USER_AGENT'];

            $line = '"' . implode('","', $line) . "\"\n";

            $loc = "/var/log/proxy";
            if($this->getConfig("location", "log")) $loc = $this->getConfig("location", "log");

            file_put_contents($loc . "/access_" . date("Y-n-j") . ".log", $line, FILE_APPEND | LOCK_EX);

        }

    }
