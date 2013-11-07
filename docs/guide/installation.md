Installation
============

There are two ways you can install the Yii framework:

* Using [Composer](http://getcomposer.org/)
* Via manual download

Installing via Composer
-----------------------

The recommended way to install Yii is to use the [Composer](http://getcomposer.org/) package manager. If you do not already
have Composer installed, you may download it from [http://getcomposer.org/](http://getcomposer.org/) or run the following command:

```
curl -s http://getcomposer.org/installer | php
```

For problems or more information, see the official Composer guide:

* [Linux](http://getcomposer.org/doc/00-intro.md#installation-nix) 
* [Windows](http://getcomposer.org/doc/00-intro.md#installation-windows)

With Composer installed, you can create a new Yii site using one of Yii's ready-to-use application templates. Based on your needs, choosing the right template can help bootstrap your project.

Currently, there are two application templates available:

- [basic](https://github.com/yiisoft/yii2-app-basic), just a basic frontend application template.
- [advanced](https://github.com/yiisoft/yii2-app-advanced), consisting of a  frontend, a backend, console resources, common (shared code), and support for environments.

For installation instructions for these templates, see the above linked pages. To read more about ideas behind these application templates and
proposed usage, refer to the [basic application template](apps-basic.md) and [advanced application template](apps-advanced.md) documents.

Installing from zip
-------------------

Installation from a zip file involves two steps:

   1. Downloading the Yii Framework from [yiiframework.com](http://www.yiiframework.com/).
   2. Unpacking the downloaded file.

> Tip: The Yii framework itself does not need to be installed under a web-accessible directory.
A Yii application has one entry script which is usually the only file that absolutely must be exposed to web users (i.e., placed within the web directory). Other PHP scripts, including those part of the
Yii framework, should be protected from web access to prevent possible exploitation by hackers.

Requirements
------------

After installing Yii, you may want to verify that your server satisfies
Yii's requirements. You can do so by running the requirement checker
script in a web browser.

1. Copy the `requirements` folder from the downloaded Yii directory to your web directory.
2. Access `http://hostname/path/to/yii/requirements/index.php` in your browser.

Yii 2 requires PHP 5.4.0 or higher. Yii has been tested with the [Apache HTTP server](http://httpd.apache.org/) on Windows and Linux. Yii may also be usable on other web servers and platforms, provided that PHP 5.4 or higher is supported.

Recommended Apache Configuration
--------------------------------

Yii is ready to work with a default Apache web server configuration. As a security measure, Yii comes with `.htaccess` files in the Yii framework and application folders to deny access to thoe restricted resources. 

By default, requests for pages in a Yii-based site go through the boostrap file, usually named `index.php`, and placed in the application's root directory. The result will be URLs in the format `http://hostname/index.php/controller/action/param/value`. 

To hide the bootstrap file in your URLs, add `mod_rewrite` instructions to the `.htaccess` file found in your web document root (or add the instructions to the virtual host configuration in Apache's `httpd.conf` file). The applicable instructions are:

~~~
RewriteEngine on

# If a directory or a file exists, use it directly
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
# Otherwise forward it to index.php
RewriteRule . index.php
~~~

Recommended Nginx Configuration
-------------------------------

Yii can also be used with the popular [Nginx](http://wiki.nginx.org/) web server, so long it has PHP installed as an [FPM SAPI](http://php.net/install.fpm). Below is a sample host configuration for a Yii-based site on Nginx. The configuration identifies tells the server to send all requests for non-existent resources through the bootstrap file, resulting in "prettier" URLs without the need for `index.php` references.

~~~
server {
    charset utf-8;

    listen       80;
    server_name  mysite.local;
    root         /path/to/project/webroot/directory

    access_log  /path/to/project/log/access.log  main;

    location / {
        try_files   $uri $uri/ /index.php?$args; # Redirect everything that isn't real file to index.php including arguments.
    }

    location ~ \.php$ {
        include fastcgi.conf;
        fastcgi_pass   127.0.0.1:9000;
        #fastcgi_pass unix:/var/run/php5-fpm.sock;
    }

    location ~ /\.(ht|svn|git) {
        deny all;
    }
}
~~~

When using this configuration, you should set `cgi.fix_pathinfo=0` in the `php.ini` file in order to avoid many unnecessary system `stat()` calls.
