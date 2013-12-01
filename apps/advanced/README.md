Yii 2 Advanced Application Template
===================================

Yii 2 Advanced Application Template is a skeleton Yii 2 application best for
developing complex Web applications with multiple tiers.

The template includes three tiers: front end, back end, and console, each of which
is a separate Yii application.

The template is designed to work in a team development environment. It supports
deploying the application in different environments.


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

The minimum requirement by this application template that your Web server supports PHP 5.4.0.


INSTALLATION
------------

### Install from an Archive File

Extract the archive file downloaded from [yiiframework.com](http://www.yiiframework.com/download/) to
a directory named `advanced` that is directly under the Web root.

Then follow the instructions given in "GETTING STARTED".


### Install via Composer

If you do not have [Composer](http://getcomposer.org/), you may install it by following the instructions
at [getcomposer.org](http://getcomposer.org/doc/00-intro.md#installation-nix).

You can then install the application using the following command:

~~~
php composer.phar create-project --stability=alpha yiisoft/yii2-app-advanced advanced
~~~

After the application is installed, switch to the project folder and run the following command
to initialize the application:

~~~
./init         (or "init" on Windows)
~~~

You can then access the application through the following URLs,

~~~
http://localhost/advanced/frontend/web/
http://localhost/advanced/backend/web/
~~~


GETTING STARTED
---------------

After you install the application, you have to conduct the following steps to initialize
the installed application. You only need to do these once for all.

1. Run command `init` to initialize the application with a specific environment.
2. Create a new database and adjust the `components.db` configuration in `common/config/params.php` accordingly.
3. Run command `yii migrate` to apply DB migrations.

Now you should be able to access:

- the frontend using the URL `http://localhost/advanced/frontend/web/`
- the backend using the URL `http://localhost/advanced/backend/web/`
