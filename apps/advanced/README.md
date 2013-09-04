Yii 2 Advanced Application Template
===================================

**NOTE** Yii 2 and the relevant applications and extensions are still under heavy
development. We may make significant changes without prior notices. Please do not
use them for production. Please consider using [Yii v1.1](https://github.com/yiisoft/yii)
if you have a project to be deployed for production soon.


Thank you for using Yii 2 Advanced Application Template - an application template
that works out-of-box and can be easily customized to fit for your needs.

Yii 2 Advanced Application Template is best suitable for large projects requiring frontend and backend separation,
deployment in different environments, configuration nesting etc.


DIRECTORY STRUCTURE
-------------------

```
common
	config/             contains shared configurations
	models/             contains model classes used in both backend and frontend
console
	config/             contains console configurations
	controllers/        contains console controllers (commands)
	migrations/         contains database migrations
	models/             contains console-specific model classes
	runtime/            contains files generated during runtime
backend
	assets/             contains application assets such as JavaScript and CSS
	config/             contains backend configurations
	controllers/        contains Web controller classes
	models/             contains backend-specific model classes
	runtime/            contains files generated during runtime
	views/              contains view files for the Web application
	web/                contains the entry script and Web resources
frontend
	assets/             contains application assets such as JavaScript and CSS
	config/             contains frontend configurations
	controllers/        contains Web controller classes
	models/             contains frontend-specific model classes
	runtime/            contains files generated during runtime
	views/              contains view files for the Web application
	web/                contains the entry script and Web resources
vendor/                 contains dependent 3rd-party packages
environments/                contains environment-based overrides
```



REQUIREMENTS
------------

The minimum requirement by Yii is that your Web server supports PHP 5.3.?.

In order for captcha to work you need either GD2 extension or ImageMagick PHP extension.

INSTALLATION
------------

### Install via Composer

If you do not have [Composer](http://getcomposer.org/), you may download it from
[http://getcomposer.org/](http://getcomposer.org/) or run the following command on Linux/Unix/MacOS:

~~~
curl -s http://getcomposer.org/installer | php
~~~

You can then install the application using the following command:

~~~
php composer.phar create-project --stability=dev yiisoft/yii2-app-advanced yii-advanced
~~~

Note that in order to install some dependencies you must have `php_openssl` extension enabled.


### Install from an Archive File

This is not currently available. We will provide it when Yii 2 is formally released.


### Install from development repository

If you've cloned the [Yii 2 framework main development repository](https://github.com/yiisoft/yii2) you
can bootstrap your application with:

~~~
cd yii2/apps/advanced
php composer.phar create-project
~~~

*Note: If the above command fails with `[RuntimeException] Not enough arguments.` run
`php composer.phar self-update` to obtain an updated version of composer which supports creating projects
from local packages.*


GETTING STARTED
---------------

After you install the application, you have to conduct the following steps to initialize
the installed application. You only need to do these once for all.

1. Execute the `init` command and select `dev` as environment.
2. Create a new database. It is assumed that MySQL InnoDB is used. If not, adjust `console/migrations/m130524_201442_init.php`.
3. In `common/config/params.php` set your database details in `components.db` values.

Now you should be able to access:

- the frontend using the URL `http://localhost/yii-advanced/frontend/web/`
- the backend using the URL `http://localhost/yii-advanced/backend/web/`

assuming `yii-advanced` is directly under the document root of your Web server.

