安装 Yii
==============

你可以通过两种方式安装 Yii：使用 [Composer](http://getcomposer.org/) 或下载一个存档文件。推荐使用前者，
这样只需执行一条简单的命令就可以安装新的 [扩展](structure-extensions.md) 或更新 Yii 了。

> Note: 和 Yii 1 版本不同，以标准方式安装 Yii 2 时会同时下载并安装框架本身和一个应用程序骨架。


通过 Composer 安装<a name="installing-via-composer"></a>
-----------------------

如果还没有安装 Composer，你可以按 [getcomposer.org](https://getcomposer.org/download/) 中的方法安装。
在 Linux 和 Mac OS X 中，可以运行如下命令安装：

    curl -s http://getcomposer.org/installer | php
    mv composer.phar /usr/local/bin/composer

在 Windows 中，你需要下载并运行 [Composer-Setup.exe](https://getcomposer.org/Composer-Setup.exe)。

如果遇到了任何问题或者想更深入地学习 Composer，请参考  [Composer 文档](https://getcomposer.org/doc/) 。

Composer 安装后，切换到一个可通过 Web 访问的目录，执行如下命令即可安装 Yii ：

    composer create-project --prefer-dist yiisoft/yii2-app-basic basic

如上命令会将 Yii 安装在一个名为 `basic` 的目录中。

> Tip: 如果你想安装 Yii 的最新开发版本，可以使用如下命令，它添加了一个
> [stability 选项](https://getcomposer.org/doc/04-schema.md#minimum-stability):
>
>     composer create-project --prefer-dist --stability=dev yiisoft/yii2-app-basic basic
>
> 注意，Yii 的开发版不应该用于生产环境中，因为它可能会破坏运行中的代码。.


通过归档文件安装 <a name="installing-from-archive-file"></a>
-------------------------------

通过归档文件安装 Yii 包括两个步骤：

1. 从 [yiiframework.com](http://www.yiiframework.com/download/yii2-basic) 下载归档文件。
2. 将下载的文件解压缩到 Web 目录中。


Other Installation Options <a name="other-installation-options"></a>
--------------------------

The above installation instructions show how to install Yii, which also creates a basic Web application that works out of the box.
This approach is a good starting point for small projects, or for when you just start learning Yii.

But there are other installation options available:

* If you only want to install the core framework and would like to build an entire  application from scratch,
  you may follow the instructions as explained in [Building Application from Scratch](tutorial-start-from-scratch.md).
* If you want to start with a more sophisticated application, better suited to team development environments,
  you may consider installing the [Advanced Application Template](tutorial-advanced-app.md).


Verifying the Installation <a name="verifying-installation"></a>
--------------------------

After installation, you can use your browser to access the installed Yii application with the following URL:

```
http://localhost/basic/web/index.php
```

This URL assumes you have installed Yii in a directory named `basic`, directly under the Web server's document root directory,
and that the Web server is running on your local machine (`localhost`). You may need to adjust it to your installation environment.

![Successful Installation of Yii](images/start-app-installed.png)

You should see the above "Congratulations!" page in your browser. If not, please check if your PHP installation satisfies
Yii's requirements. You can check if the minimum requirements are met using one of the following approaches:

* Use a browser to access the URL `http://localhost/basic/requirements.php`
* Run the following commands:

  ```
  cd basic
  php requirements.php
  ```

You should configure your PHP installation so that it meets the minimum requirements of Yii. Most importantly, you should have PHP 5.4 or above. You should also install
the [PDO PHP Extension](http://www.php.net/manual/en/pdo.installation.php) and a corresponding database driver
(such as `pdo_mysql` for MySQL databases), if your application needs a database.


Configuring Web Servers <a name="configuring-web-servers"></a>
-----------------------

> Info: You may skip this subsection for now if you are just test driving Yii with no intention
  of deploying it to a production server.

The application installed according to the above instructions should work out of box with either
an [Apache HTTP server](http://httpd.apache.org/) or an [Nginx HTTP server](http://nginx.org/), on
 Windows, Mac OS X, or Linux.

On a production server, you may want to configure your Web server so that the application can be accessed
via the URL `http://www.example.com/index.php` instead of `http://www.example.com/basic/web/index.php`. Such configuration
requires pointing the document root of your Web server to the `basic/web` folder. You may also
want to hide `index.php` from the URL, as described in the [URL Parsing and Generation](runtime-url-handling.md) section.
In this subsection, you'll learn how to configure your Apache or Nginx server to achieve these goals.

> Info: By setting `basic/web` as the document root, you also prevent end users from accessing
your private application code and sensitive data files that are stored in the sibling directories
of `basic/web`. Denying access to those other folders is a producent security improvement.

> Info: If your application will run in a shared hosting environment where you do not have permission
to modify its Web server configuration, you may still adjust the structure of your application for better security. Please refer to
the [Shared Hosting Environment](tutorial-shared-hosting.md) section for more details.


### Recommended Apache Configuration <a name="recommended-apache-configuration"></a>

Use the following configuration in Apache's `httpd.conf` file or within a virtual host configuration. Note that you
should replace `path/to/basic/web` with the actual path for `basic/web`.

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


### Recommended Nginx Configuration <a name="recommended-nginx-configuration"></a>

You should have installed PHP as an [FPM SAPI](http://php.net/install.fpm) to use [Nginx](http://wiki.nginx.org/).
Use the following Nginx configuration, replacing `path/to/basic/web` with the actual path for `basic/web` and `mysite.local` with
the actual hostname to serve.

```
server {
    charset utf-8;
    client_max_body_size 128M;

    listen 80; ## listen for ipv4
    #listen [::]:80 default_server ipv6only=on; ## listen for ipv6

    server_name mysite.local;
    root        /path/to/basic/web;
    index       index.php;

    access_log  /path/to/basic/log/access.log main;
    error_log   /path/to/basic/log/error.log;

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

When using this configuration, you should also set `cgi.fix_pathinfo=0` in the `php.ini` file
in order to avoid many unnecessary system `stat()` calls.

Also note that when running an HTTPS server, you need to add `fastcgi_param HTTPS on;` so that Yii
can properly detect if a connection is secure.
