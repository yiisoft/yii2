Installing Yii
==============

You can install Yii in two ways, using [Composer](http://getcomposer.org/) or downloading an archive file.
The former is the preferred way as it allows you to install new [extensions](structure-extensions.md)
or update Yii by running a single command.


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


Installing from an Archive File
-------------------------------

Installing Yii from an archive file involves two steps:

1. Download the archive file from [yiiframework.com](http://www.yiiframework.com/download/yii2-basic);
2. Unpack the downloaded file to a Web accessible folder.


Other Installation Options
--------------------------

The above installation instructions show how to install Yii in terms of a basic Web application that works out of box.
It is a good start for small projects or if you just start learning Yii.

There are other installation options available:

* If you only want to install the core framework and would like to build an application from scratch,
  you may follow the instructions as explained in [Building Application from Scratch](tutorial-start-from-scratch.md).
* If you want to start with a more sophisticated application that supports team development environment,
  you may consider [Advanced Application Template](tutorial-advanced-app.md).


Verifying Installation
----------------------

After installation, you can use your browser to access the installed Yii application with the following URL,
assuming you have installed Yii in a directory named `basic` that is under the document root of your Web server,

```
http://localhost/basic/web/index.php
```

You should see a "Congratulations!" page in your browser. If not, please check if your PHP installation satisfies
Yii's requirements by using one of the following approaches:

* Use a browser to access the URL `http://localhost/basic/requirements.php`
* Run the following commands:

  ```
  cd basic
  php requirements.php
  ```

You should configure your PHP installation so that it meets the minimum requirement of Yii.

Yii has been tested with the [Apache HTTP server](http://httpd.apache.org/) and [Nginx HTTP server](http://nginx.org/)
on both Windows and Linux. It requires PHP 5.4 or above. And you should install
the [PDO PHP Extension](http://www.php.net/manual/en/pdo.installation.php) and a corresponding database driver
(such as `pdo_mysql` for MySQL databases), if your application needs a database.


Adjusting Document Root
-----------------------

The above installation is fine in a development environment which can only be accessed from the local machine
or the local network.

In a production environment, you should configure the Web server by pointing its document root
to the `basic/web` folder. This is necessary because besides Web accessible files under the `basic/web` folder,
the `basic` folder also contains your application code and/or sensitive data files that you do not want to
expose to the Web. After the adjustment, you should be able to access the installed application via URL:

```
http://localhost/index.php
```

If you plan to deploy your application to a shared hosting environment and you do not have the permission
to modify its Web server setting, please refer to the [Shared Hosting Environment](tutorial-shared-hosting.md) section
on how to adjust your application.
