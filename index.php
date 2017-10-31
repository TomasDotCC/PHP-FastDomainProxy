<?php

$took = microtime(true);

try {

    include_once("./proxy/class/Proxy.class.php");
    include_once("./proxy/class/Cache.class.php");

    include_once("./proxy/class/Base.class.php");
    include_once("./proxy/class/UserRequest.class.php");
    include_once("./proxy/class/ServerRequest.class.php");
    include_once("./proxy/class/ServerResponse.class.php");
    include_once("./proxy/class/UserResponse.class.php");

    // =========================================================
    // Make wrapper object proxy	
    $proxy = new Proxy();
    $proxy->root(dirname(__FILE__));
    $proxy->d("ROOT PATH", $proxy->root());
    // ---------------------------------------------------------


    // =========================================================
    // Load config
    $proxy->baseConfig();
    $proxy->iniConfig();
    $proxy->finalConfig();
    // ---------------------------------------------------------


    // =========================================================
    // Make user request object AND get user request headers AND manipulate request headers and URL
    $proxy->user_request = new UserRequest($proxy);
    $proxy->user_request->setUrl(str_replace("." . $proxy->getConfig("myhost"), "", $proxy->user_request->getUrl()));
    if ($proxy->getConfig("https", "general")) $proxy->user_request->setUrl(str_replace("http:", "https:", $proxy->user_request->getUrl()));
    if ($proxy->getConfig("isip")) $proxy->user_request->setUrl(str_replace($proxy->getConfig("myhost"), $proxy->getConfig("gethost"), $proxy->user_request->getUrl()));
    // ---------------------------------------------------------


    // =========================================================
    // Check if static file    
    $proxy->checkStatic();
    // ---------------------------------------------------------

    // =========================================================
    // Make Cache object
    $cache = new Cache($proxy);
    $cache->setType($proxy->getConfig("type", "cache"));
    // ---------------------------------------------------------

    // =========================================================
    // Make server request object AND set server request headers AND exec remote call
    $proxy->server_request = new ServerRequest($proxy);
    $proxy->server_request->setCache($cache);
    $proxy->server_request->setUrl($proxy->user_request->getUrl());
    $proxy->server_request->setHeaders($proxy->user_request->getHeaders());
    $proxy->server_request->setHeader("Host", $proxy->getConfig("gethost"));
    $proxy->server_request->setHeader("DNT", 1);
    $proxy->server_request->unsetHeaders(array(
        "Referer",
        "If-Modified-Since",
        "If-None-Match",
        "Cache-Control",
        "Dnt",
        "Origin",
        "Cf-Connecting-Ip",
        "Cf-Visitor",
        "X-Forwarded-Proto",
        "Cf-Ray",
        "X-Forwarded-For",
        "Cf-Ipcountry"
    ));
    if ($proxy->getConfig("setheader", "server_request")) foreach ($proxy->getConfig("setheader", "server_request") as $k => $v) $proxy->server_request->setHeader($k, $v);
    if ($proxy->getConfig("unsetheader", "server_request")) foreach ($proxy->getConfig("unsetheader", "server_request") as $k => $v) $proxy->server_request->unsetHeaders(array($k, $v));
    $proxy->d("Executing remote request...");
    $proxy->server_request->exec();
    $proxy->d("Executing remote request... DONE!");
    $proxy->d("Printing final ServerRequest", $proxy->server_request);
    // ---------------------------------------------------------


    // =========================================================
    // Make server response object AND parse server response headers and content AND manipulate server response text
    $proxy->d("Making server response object...");
    $proxy->server_response = new ServerResponse($proxy);
    $proxy->d("Making server response object... DONE!");
    $proxy->server_response->setHeaders($proxy->server_request->getHeadersOut());
    $proxy->d("Setting content... Size: " . strlen($proxy->server_request->getContentOut()));
    $proxy->server_response->setContent($proxy->server_request->getContentOut());
    $proxy->d("Setting content... Size: " . strlen($proxy->server_response->getContent()) . " | DONE!");
    $proxy->server_response->replaceText();
    $proxy->server_response->replaceLinks();
    $proxy->server_response->replaceText();
    // ---------------------------------------------------------

    // =========================================================
    // TODO
    // Check if content-type && host is allowed AND log request if HTML
    /*if(preg_match("/html/", $proxy->server_response->getHeader("Content-Type"))){
        $proxy->log($proxy->server_response->getHeader("Content-Type"), strlen($proxy->server_response->getContent()), $proxy->server_response->getUrl());
    }
    if(!$proxy->checkAllowed($proxy->server_response->getHeader("Content-Type"))){
        include_once("./proxy/etc/web.php");
        exit;
    }*/
    // ---------------------------------------------------------


    // =========================================================
    // Make server response object AND parse server response headers and content AND manipulate server response text
    $proxy->server_response = new ServerResponse($proxy);
    $proxy->server_response->setHeaders($proxy->server_request->getHeadersOut());
    $proxy->server_response->setContent($proxy->server_request->getContentOut());
    $proxy->server_response->replaceText();
    $proxy->server_response->replaceLinks();
    $proxy->server_response->replaceText();
    // ---------------------------------------------------------


    // =========================================================
    // Make user request object AND parse server response data AND manipulate user response headers AND send back to user
    $proxy->user_response = new UserResponse($proxy);
    $proxy->user_response->setHeaders($proxy->server_response->getHeaders());
    $proxy->user_response->setContent($proxy->server_response->getContent());
    $proxy->user_response->unsetHeader("Location");
    $proxy->user_response->unsetHeader("CF-RAY");
    $proxy->user_response->unsetHeader("X-Frame-Options");
    $proxy->user_response->setHeader("Server", "proxy");
    $proxy->user_response->setHeader("Content-Security-Policy", "default-src *; child-src * 'self' 'unsafe-inline' blob:; script-src 'unsafe-inline' 'unsafe-eval' *; connect-src *; img-src * data:; style-src 'unsafe-inline' *; font-src * data:; object-src *; frame-src *; frame-ancestors *; form-action *;");
    $proxy->user_response->setHeader("Access-Control-Allow-Origin", "*");
    if ($proxy->getConfig("setheader", "user_response")) foreach ($proxy->getConfig("setheader", "user_response") as $k => $v) $proxy->user_response->setHeader($k, $v);
    if ($proxy->getConfig("unsetheader", "user_response")) foreach ($proxy->getConfig("unsetheader", "user_response") as $k => $v) $proxy->user_response->unsetHeaders(array($k, $v));
    if (!strlen(trim($proxy->user_response->getContent())) OR $proxy->user_response->getHeader("pc")) {
        header("Location: //" . $proxy->getConfig("myhost"));
        exit;
    }
    if ($proxy->debug != 2) $proxy->user_response->send();
    // ---------------------------------------------------------	


    // =========================================================
    // End, Log & refresh cache

    if (!$proxy->debug) fastcgi_finish_request();

    $proxy->log($proxy->server_response->getHeader("Content-Type"), strlen($proxy->server_response->getContent()), $proxy->server_request->getUrl(), round(microtime(true) - $took, 2));

    $cache->end();
    $proxy->end();

    // ---------------------------------------------------------


    exit;

} catch (Throwable $e) {
    echo "<br /><br /><center><h1>Sorry, we will be back soon.</h1></center><!--";
    // !!! If you want more security, comment next line (add // at the begging of line, as this line) !!!
    echo(print_r($e, true));
    echo "-->";
}