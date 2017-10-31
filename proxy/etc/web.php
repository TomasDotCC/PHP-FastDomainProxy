<?php

    // This is proxy homepage (by default)

    $l = $_SERVER['HTTP_HOST'];
    if(preg_match("/\..*\./", $l)){
        header("Location: http://" . preg_replace("/(.*\.)([^\.]*\.)/", "$2", $l));
        exit;
    }

?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Unblock your favorite sites with our Public Proxy Server. Download movie torrents and search for many more torrents from anywhere.">
    <title>Public Proxy Server - Unblock Movie Torrent sites and more!</title>
    <link href="data:image/x-icon;base64,AAABAAEAEBAQAAEABAAoAQAAFgAAACgAAAAQAAAAIAAAAAEABAAAAAAAgAAAAAAAAAAAAAAAEAAAAAAAAADc3NwAAAAAAENDQwD///8AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAERIiESIREhERIwAiMyIjIRESMzMzMzMhERIzMzMzMyERIjMzMjMzIRICMzMgIzMhEjMzMzIzMhERIzMzMzMyEREjMyIjMzIRESMzMzMzMhERIzIzMjMyEREjMzMzMzIRERIzMzMzIREREjMzMzMhERERIiIiIhERERERERERERHjOwAAwAEAAOABAADgAQAAwAEAAIABAACAAwAAwAMAAMADAADAAwAAwAMAAMADAADgBwAA4AcAAPAPAAD//wAA" rel="icon" type="image/x-icon"/>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.0.0-alpha.6/css/bootstrap.min.css" rel="stylesheet">
    <style>
        @media (min-width: 768px) {
            .container {
                max-width: 730px;
            }
        }
        .btn {
            cursor: pointer;
        }
        a:hover, a:active {
            text-decoration: none;
        }
        .back {
            position: fixed;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-image: url("http://wallpapercave.com/wp/is8uXJK.jpg");
            background-size: cover;
            -webkit-filter: blur(3px);
            -moz-filter: blur(3px);
            -o-filter: blur(3px);
            -ms-filter: blur(3px);
            filter: blur(3px);
        }
    </style>
</head>
<body>
<div class="back"></div>
<div class="container">
    <a href="/"><h1 class="text-muted my-4">Public Proxy Server</h1></a>
    <div class="jumbotron py-3">
        <div class="input-group">
            <input type="text" class="form-control" placeholder="URL address or search query" id="q" onkeypress="s(event);">
            <span class="input-group-btn">
                <button class="btn btn-secondary" type="button" onclick="s(false);">Go!</button>
            </span>
        </div>
    </div>
</div>
<script>
    var l = document.location.hostname;
    if(/\..*\./.test(l)){
        document.location.hostname = l.replace(/(.*\.)([^\.]*\.)/, "$2");
    }
    function s(e) {
        if (e && e.keyCode !== 13) return;
        var v = document.getElementById("q").value;
        if (!v) return;
        if ((v.indexOf("http://") != '-1' || v.indexOf("https://") != '-1' || /\.[a-zA-Z]{2,4}/.test(v)) && !/ /.test(v)) {
            if (v.indexOf("http://") == '-1' && v.indexOf("https://") == '-1') v = "http://" + v;
            var parser = document.createElement('a');
            parser.href = v;
            document.location = parser.protocol + "//" + parser.hostname + "." + document.location.hostname + parser.pathname + parser.search + parser.hash;
        } else {
            document.location = "http://duckduckgo.com." + document.location.hostname + "/html?q=" + v + "&ia=web";
        }
    }

    var _paq = _paq || [];
    _paq.push(["setDocumentTitle", "HOMEPAGE /" + document.title]);
    _paq.push(["setDomains", ["*." + document.location.hostname]]);
    _paq.push(["setDoNotTrack", false]);
    _paq.push(['trackPageView']);
    _paq.push(['enableLinkTracking']);
    _paq.push(['setCustomVariable', 1, 'Homepage', 'Homepage', 'page']);
    (function() {
        var d=/\.?([^\.]+\.[^\.]+)$/.exec(document.location.hostname);
        var u="//" + d[1] + "/piwik/";
        _paq.push(['setTrackerUrl', u+'piwik.php']);
        _paq.push(['setSiteId', 1]);
        var d=document, g=d.createElement('script'), s=d.getElementsByTagName('script')[0];
        g.type='text/javascript'; g.async=true; g.defer=true; g.src=u+'piwik.js'; s.parentNode.insertBefore(g,s);
    })();

</script>
</body>
</html>