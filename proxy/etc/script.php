<script type="text/javascript">


    // Input custom JS here, for example Google Analytics or Piwik



    // End of custom JS

    // Scroll to top to see advertisement, comment next line to make #example links working again
    window.scrollTo(0, 0);

    <?php if(!$this->proxy->homepage AND !$this->proxy->getConfig("isip")){ ?>

    proxyUnload();
    proxyLinks();


    function proxyUnload() {
        window.onbeforeunload = function (e) {
            if (!e.target) return;
            if (e.target.activeElement.hostname.indexOf('<?php echo $this->proxy->getConfig('myhost'); ?>') > 0 || e.target.activeElement.href.indexOf('magnet:?') >= 0 || e.target.activeElement.id == 'proxyorg' || e.target.activeElement.nocheck == 'true') {
                return;
            } else {
                // If we failed to replace some link, intercept request and try to force user to click cancel AND replace link
                e.target.activeElement.hostname = e.target.activeElement.hostname + ".<?php echo $this->proxy->getConfig('myhost'); ?>";
                return "You are about to directly visit blocked site!!!\n\n# Click on 'Stay on this page!' and click link again TO CONTINUE! #";
            }
        };
    }

    function proxyLinks() {
        document.getElementsByTagName("a").onmouseover = function (e) {
            if (!e.target) return;
            // Replace all links on mouseover, ignore links with attribute nocheck=true or id='proxyorg'
            if (e.target.activeElement.hostname.indexOf('<?php echo $this->proxy->getConfig('myhost'); ?>') > 0 || e.target.activeElement.href.indexOf('magnet:?') >= 0 || e.target.activeElement.id == 'proxyorg' || e.target.activeElement.nocheck == 'true') {
            } else {
                e.target.activeElement.hostname = e.target.activeElement.hostname + ".<?php echo $this->proxy->getConfig('myhost'); ?>";
            }
        };
    }

<?php } ?>