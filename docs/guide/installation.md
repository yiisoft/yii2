Installation
============

Installing via Composer
-----------------------

The recommended way of installing Yii is by using [Composer](http://getcomposer.org/) package manager. If you do not
have it, you may download it from [http://getcomposer.org/](http://getcomposer.org/) or run the following command:

```
curl -s http://getcomposer.org/installer | php
```

Yii provides a few ready-to-use application templates. Based on your needs, you may choose one of them to bootstrap
your project.

There are two application templates available:

- [basic](https://github.com/yiisoft/yii2-app-basic) that is just a basic frontend application template.
- [advanced](https://github.com/yiisoft/yii2-app-advanced) that is a set of frontend, backend, console, common
 (shared code) and environments support.

Please refer to installation instructions on these pages. To read more about ideas behing these application templates and
proposed usage refer to [basic application template](apps-basic.md) and [advanced application template](apps-advanced.md).

Installing from zip
-------------------

Installation from zip mainly involves the following two steps:

   1. Download Yii Framework from [yiiframework.com](http://www.yiiframework.com/).
   2. Unpack the Yii release file to a Web-accessible directory.

> Tip: Yii does not need to be installed under a Web-accessible directory.
A Yii application has one entry script which is usually the only file that
needs to be exposed to Web users. Other PHP scripts, including those from
Yii, should be protected from Web access; otherwise they might be exploited
by hackers.

Requirements
------------

After installing Yii, you may want to verify that your server satisfies
Yii's requirements. You can do so by accessing the requirement checker
script via the following URL in a Web browser:

~~~
http://hostname/path/to/yii/requirements/index.php
~~~

Yii requires PHP 5.4.0, so the server must have PHP 5.4.0 or above installed and
available to the web server. Yii has been tested with [Apache HTTP server](http://httpd.apache.org/)
on Windows and Linux. It may also run on other Web servers and platforms,
provided PHP 5.4 is supported.


Recommended Apache Configuration
--------------------------------

Yii is ready to work with a default Apache web server configuration.
The `.htaccess` files in Yii framework and application folders deny
access to the restricted resources. To hide the bootstrap file (usually `index.php`)
in your URLs you can add `mod_rewrite` instructions to the `.htaccess` file
in your document root or to the virtual host configuration:

~~~
RewriteEngine on

# if a directory or a file exists, use it directly
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
# otherwise forward it to index.php
RewriteRule . index.php
~~~


Recommended Nginx Configuration
-------------------------------

You can use Yii with [Nginx](http://wiki.nginx.org/) and PHP with [FPM SAPI](http://php.net/install.fpm).
Here is a sample host configuration. It defines the bootstrap file and makes
Yii to catch all requests to nonexistent files, which allows us to have nice-looking URLs.

~~~
server {
    set $host_path "/www/mysite";
    access_log  /www/mysite/log/access.log  main;

    server_name  mysite;
    root  $host_path/htdocs;
    set $yii_bootstrap "index.php";

    charset utf-8;

    location / {
        index  index.html $yii_bootstrap;
        try_files $uri $uri/ /$yii_bootstrap?$args;
    }

    location ~ ^/(protected|framework|themes/\w+/views) {
        deny  all;
    }

    #avoid processing of calls to unexisting static files by yii
    location ~ \.(js|css|png|jpg|gif|swf|ico|pdf|mov|fla|zip|rar)$ {
        try_files $uri =404;
    }

    # pass the PHP scripts to FastCGI server listening on 127.0.0.1:9000
    #
    location ~ \.php {
        fastcgi_split_path_info  ^(.+\.php)(.*)$;

        #let yii catch the calls to unexising PHP files
        set $fsn /$yii_bootstrap;
        if (-f $document_root$fastcgi_script_name){
            set $fsn $fastcgi_script_name;
        }

        #for php-cgi
        fastcgi_pass   127.0.0.1:9000;
        #for php-fpm
        #fastcgi_pass unix:/var/run/php5-fpm.sock;
        include fastcgi_params;
        fastcgi_param  SCRIPT_FILENAME  $document_root$fsn;

        #PATH_INFO and PATH_TRANSLATED can be omitted, but RFC 3875 specifies them for CGI
        fastcgi_param  PATH_INFO        $fastcgi_path_info;
        fastcgi_param  PATH_TRANSLATED  $document_root$fsn;
    }

    location ~ /\.ht {
        deny  all;
    }
}
~~~

Using this configuration you can set `cgi.fix_pathinfo=0` in php.ini to avoid
many unnecessary system `stat()` calls.
