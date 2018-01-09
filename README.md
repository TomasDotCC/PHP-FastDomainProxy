# PHP Fast Domain Proxy

This script is very fast alternative to Glype proxy. It doesn't offer much anonymity, but is made to drive as much traffic as is possible from search engines.

With this proxy you practically "duplicate" whole internet, I call this method "anti-Google".

## BE AWARE
Script as is now is heavily against search engines policies, you may get blocked by Google very fast.
Homepage is set to be Pirate Bay, check config to change.

You will be receiving many and many DMCA removal requests, be prepared, have a off-shore server.

## Installation

Important: You must have a dedicated server, NO HOSTING! Best if bare metal SSD server.
For 3 millions IPs per month, I used bare metal server with 24-core Xeon, 64GB RAM, SSD 120GB, HDD 3TB for cache and around 45TB bandwidth per month.

1) Install 
    1) NGINX
    2) PHP-FPM
    3) PHP-CURL
    
2) Copy all files to /var/www/html
   
3) Copy all files from /var/www/html/proxy/sysconfig/etc/ to /etc/
   
4) Create config file for domain(s) in /var/www/html/proxy/domains . See provided example, check code for complete list of possible configs.

5) Create folder and make writable /var/www/html/cache - best to use dedicated partition

6) Create folder and make writable /var/www/html/tpbcache - best on SSD - this step is required only if you want to use remote website as homepage, see config.

7) Create folders and make writable /var/www/html/debug and /var/log/proxy - this step is optional

8) Restart nginx and php-fpm or whole server


## Revenue
To get some good revenue, you will need traffic at least 10k IPs per day. To achieve this you should build tens of very quality links, make them manually!

I tested this script with 3 millions IPs per month (avg. 500 requests per second) with $4k revenue per month.


## Other info
Code is quite simple, so be sure to check all scripts to know all features!
