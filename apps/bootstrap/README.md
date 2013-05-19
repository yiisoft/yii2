Yii 2 Bootstrap Application
===========================

**NOTE** Yii 2 and the relevant applications and extensions are still under heavy
development. We may make significant changes without prior notices. Please do not
use them for production. Please consider using [Yii v1.1](https://github.com/yiisoft/yii)
if you have a project to be deployed for production soon.


Thank you for choosing Yii 2 - the new generation of high-performance PHP framework.

The Yii 2 Bootstrap Application is a Web application template that you can easily customize
to fit for your needs. It is particularly suitable for small Websites which mainly contain
a few informational pages.


DIRECTORY STRUCTURE
-------------------

      commands/           contains console commands (controllers)
      config/             contains application configurations
      controllers/        contains Web controller classes
      models/             contains model classes
      runtime/            contains files generated during runtime
      vendor/             contains dependent 3rd-party packages
      views/              contains view files for the Web application
      www/                contains the entry script and Web resources



REQUIREMENTS
------------

The minimum requirement by Yii is that your Web server supports PHP 5.3.?.


INSTALLATION
------------

### Install via Composer

If you do not have [Composer](http://getcomposer.org/), you may download it from
[http://getcomposer.org/](http://getcomposer.org/) or run the following command on Linux/Unix/MacOS:

~~~
curl -s http://getcomposer.org/installer | php
~~~

You can then install the Bootstrap Application using the following command:

~~~
php composer.phar create-project --stability=dev yiisoft/yii2-bootstrap bootstrap
~~~

Now you should be able to access the Bootstrap Application using the URL `http://localhost/bootstrap/www/`,
assuming `bootstrap` is directly under the document root of your Web server.


### Install from an Archive File

This is not currently available. We will provide it when Yii 2 is formally released.
