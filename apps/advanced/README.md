Yii 2 Advanced Application Template
===================================

**NOTE** Yii 2 and the relevant applications and extensions are still under heavy
development. We may make significant changes without prior notices. Please do not
use them for production. Please consider using [Yii v1.1](https://github.com/yiisoft/yii)
if you have a project to be deployed for production soon.


Thank you for using Yii 2 Advanced Application Template - an application template
that works out-of-box and can be easily customized to fit for your needs.

Yii 2 Advanced Application Template is best suitable for large projects requiring frontend and backstage separation,
deployment in different environments, configuration nesting etc.


DIRECTORY STRUCTURE
-------------------

```
common
	config/             contains shared configurations
	models/             contains model classes used in both backstage and frontend
console
	config/             contains console configurations
	controllers/        contains console controllers (commands)
	migrations/         contains database migrations
	models/             contains console-specific model classes
	runtime/            contains files generated during runtime
backstage
	assets/             contains application assets such as JavaScript and CSS
	config/             contains backstage configurations
	controllers/        contains Web controller classes
	models/             contains backstage-specific model classes
	runtime/            contains files generated during runtime
	views/              contains view files for the Web application
	www/                contains the entry script and Web resources
frontend
	assets/             contains application assets such as JavaScript and CSS
	config/             contains frontend configurations
	controllers/        contains Web controller classes
	models/             contains frontend-specific model classes
	runtime/            contains files generated during runtime
	views/              contains view files for the Web application
	www/                contains the entry script and Web resources
vendor/                 contains dependent 3rd-party packages
environments/                contains environment-based overrides
```



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
php composer.phar create-project --stability=dev yiisoft/yii2-app-advanced yii-advanced
~~~

Now you should be able to access:

- the frontend using the URL `http://localhost/yii-advanced/frontend/www/`
- the backstage using the URL `http://localhost/yii-advanced/backstage/www/`

assuming `yii-advanced` is directly under the document root of your Web server.


### Install from an Archive File

This is not currently available. We will provide it when Yii 2 is formally released.

GETTING STARTED
---------------

After template application and its dependencies are downloaded you need to initialize it and set some config values to
match your application requirements.

1. Execute `install` command selecting `dev` as environment.
2. Set `id` value in `console/config/main.php`, `frontend/config/main.php`, `backstage/config/main.php`.
3. Create new database. It is assumed that MySQL InnoDB is used. If not, adjust `console/migrations/m130524_201442_init.php`.
4. In `common/config/params.php` set your database details in `components.db` values.

