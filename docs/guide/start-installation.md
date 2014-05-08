Installing Yii
==============

You can install Yii in two ways, using [Composer](http://getcomposer.org/) or downloading an archive file.
The former is the preferred way as it allows you to install new [extensions](structure-extensions.md)
or update Yii by running a single command.


<a name="installing-via-composer"></a>
Installing via Composer
-----------------------

If you do not already have Composer installed, you may get it by following the instructions at
[getcomposer.org](https://getcomposer.org/download/), or simply

* on Linux or Mac, run the following commands:

  ```
  curl -s http://getcomposer.org/installer | php
  mv composer.phar /usr/local/bin/composer
  ```
* on Windows, download and run [Composer-Setup.exe](https://getcomposer.org/Composer-Setup.exe).

Please refer to the [Composer Documentation](https://getcomposer.org/doc/) if you encounter any
problems or want to learn more about the Composer usage.

With Composer installed, you can install Yii by running the following command under a Web accessible folder:

```
composer create-project --prefer-dist yiisoft/yii2-app-basic basic
```

The above command installs Yii as a directory named `basic`.

> Tip: If you want to install the latest development version of Yii, you may use the following command
which adds a `stability` option:
```
composer create-project --prefer-dist --stability=dev yiisoft/yii2-app-basic basic
```
Note that the development version of Yii should not be used for production as it may break your running code.


<a name="installing-from-archive-file"></a>
Installing from an Archive File
-------------------------------

Installing Yii from an archive file involves two steps:

1. Download the archive file from [yiiframework.com](http://www.yiiframework.com/download/yii2-basic);
2. Unpack the downloaded file to a Web accessible folder.


<a name="other-installation-options"></a>
Other Installation Options
--------------------------

The above installation instructions show how to install Yii in terms of a basic Web application that works out of box.
It is a good start for small projects or if you just start learning Yii.

There are other installation options available:

* If you only want to install the core framework and would like to build an application from scratch,
  you may follow the instructions as explained in [Building Application from Scratch](tutorial-start-from-scratch.md).
* If you want to start with a more sophisticated application that supports team development environment,
  you may consider [Advanced Application Template](tutorial-advanced-app.md).


<a name="verifying-installation"></a>
Verifying Installation
----------------------

After installation, you can use your browser to access the installed Yii application with the following URL,
assuming you have installed Yii in a directory named `basic` that is under the document root of your Web server
and the server name is `hostname`,

```
http://hostname/basic/web/index.php
```

You should see a "Congratulations!" page in your browser. If not, please check if your PHP installation satisfies
Yii's requirements by using one of the following approaches:

* Use a browser to access the URL `http://hostname/basic/requirements.php`
* Run the following commands:

  ```
  cd basic
  php requirements.php
  ```

You should configure your PHP installation so that it meets the minimum requirement of Yii.
In general, you should have PHP 5.4 or above. And you should install
the [PDO PHP Extension](http://www.php.net/manual/en/pdo.installation.php) and a corresponding database driver
(such as `pdo_mysql` for MySQL databases), if your application needs a database.


<a name="configuring-web-servers"></a>
Configuring Web Servers
-----------------------

> Info: You may skip this sub-section for now if you are just testing driving Yii with no intention
  of deploying it to a production server.

The application installed according to the above instructions should work out of box with either
an [Apache HTTP server](http://httpd.apache.org/) or an [Nginx HTTP server](http://nginx.org/), on
either Windows or Linux.

On a production server, you may want to configure your Web server so that the application can be accessed
via the URL `http://hostname/index.php` instead of `http://hostname/basic/web/index.php`. This
requires pointing the document root of your Web server to the `basic/web` folder. And you may also
want to hide `index.php` from the URL, as described in the [URL Parsing and Generation](runtime-url-handling.md) section.
In this subsection, we will show how to configure your Apache or Nginx server to achieve these goals.

> Info: By setting `basic/web` as the document root, you also prevent end users from accessing
your private application code and sensitive data files that are stored in the sibling directories
of `basic/web`. This makes your application more secure.

> Info: If your application will run in a shared hosting environment where you do not have the permission
to modify its Web server setting, you may adjust the structure of your application. Please refer to
the [Shared Hosting Environment](tutorial-shared-hosting.md) section for more details.


<a name="recommended-apache-configuration"></a>
### Recommended Apache Configuration

Use the following configuration in Apache's `httpd.conf` file or within a virtual host configuration. Note that you
should replace `path/to/basic/web` with the actual path of `basic/web`.

```
# Set document root to be "basic/web"
DocumentRoot "path/to/basic/web"

<Directory "path/to/basic/web">
    RewriteEngine on

    # If a directory or a file exists, use the request directly
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    # Otherwise forward the request to index.php
    RewriteRule . index.php

    # ...other settings...
</Directory>
```


<a name="recommended-nginx-configuration"></a>
### Recommended Nginx Configuration

You should have installed PHP as an [FPM SAPI](http://php.net/install.fpm) for [Nginx](http://wiki.nginx.org/).
Use the following Nginx configuration and replace `path/to/basic/web` with the actual path of `basic/web`.

```
server {
    charset utf-8;
    client_max_body_size 128M;

    listen 80; ## listen for ipv4
    #listen [::]:80 default_server ipv6only=on; ## listen for ipv6

    server_name mysite.local;
    root        /path/to/basic/web;
    index       index.php;

    access_log  /path/to/project/log/access.log main;
    error_log   /path/to/project/log/error.log;

    location / {
        # Redirect everything that isn't a real file to index.php
        try_files $uri $uri/ /index.php?$args;
    }

    # uncomment to avoid processing of calls to non-existing static files by Yii
    #location ~ \.(js|css|png|jpg|gif|swf|ico|pdf|mov|fla|zip|rar)$ {
    #    try_files $uri =404;
    #}
    #error_page 404 /404.html;

    location ~ \.php$ {
        include fastcgi.conf;
        fastcgi_pass   127.0.0.1:9000;
        #fastcgi_pass unix:/var/run/php5-fpm.sock;
    }

    location ~ /\.(ht|svn|git) {
        deny all;
    }
}
```

When using this configuration, you should set `cgi.fix_pathinfo=0` in the `php.ini` file
in order to avoid many unnecessary system `stat()` calls.

Also note that when running an HTTPS server you need to add `fastcgi_param HTTPS on;` so that Yii
can properly detect if a connection is secure.
