Yii 2 Benchmark Application
===========================

Yii 2 Benchmark Application is an application built to demonstrate the minimal overhead
introduced by the Yii framework. The application contains a single page which only renders
the "hello world" string.

The application attempts to simulate the scenario in which you can achieve the best performance
when using Yii. It does so by assuming that both of the main application configuration and the page
content are cached in memory, and the application enables pretty URLs.


DIRECTORY STRUCTURE
-------------------

      protected/          contains application source code
          controllers/    contains Web controller classes
      index.php           the entry script


REQUIREMENTS
------------

The minimum requirement by Yii is that your Web server supports PHP 5.4.0.


INSTALLATION
------------

If you do not have [Composer](http://getcomposer.org/), you may download it from
[http://getcomposer.org/](http://getcomposer.org/) or run the following command on Linux/Unix/MacOS:

```
curl -s http://getcomposer.org/installer | php
mv composer.phar /usr/local/bin/composer
```

You can then install the Bootstrap Application using the following command:

```
composer global require "fxp/composer-asset-plugin:1.0.0"
composer create-project --prefer-dist yiisoft/yii2-app-benchmark yii-benchmark
```

Now you should be able to access the benchmark page using the URL

```
http://localhost/yii-benchmark/index.php/site/hello
```

In the above, we assume `yii-benchmark` is directly under the document root of your Web server.

Note that in order to install some dependencies you must have PHP with OpenSSL support.
