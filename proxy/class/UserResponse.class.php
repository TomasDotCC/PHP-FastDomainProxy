<?php

	class UserResponse extends Base {

        public function __construct(Proxy $proxy){
            parent::__construct($proxy);
            $this->proxy->d("#FCE: " . get_class($this) . "::" . __FUNCTION__ . "()" . " <br /> <small>" . __FILE__ . ":" . __LINE__ . "</small>", func_get_args());
            return true;
        }        

		public function send(){
            $this->proxy->d("#FCE: " . get_class($this) . "::" . __FUNCTION__ . "()" . " <br /> <small>" . __FILE__ . ":" . __LINE__ . "</small>", func_get_args());

            $gziped = gzencode($this->content);

            $this->unsetHeader('Transfer-Encoding');
            $this->setHeader('Content-Encoding', 'gzip');
            $this->setHeader('Content-Length', strlen($gziped));

            foreach($this->headers as $key => $val){
                header(trim($key) . ": " . trim($val));
            }

            if($this->proxy->debug==true){
                header("Debug: http://" . $this->proxy->getConfig('myhost') . "/" . $this->proxy->debug_file);
            }

            $temp_debug = ob_get_contents();
            ob_end_clean();

            echo $gziped;

            ob_start();
            echo $temp_debug;
		}

	}