Advanced application template
=============================

This template is for large projects developed in teams where backend is divided from frontend, application is deployed
to multiple servers etc. This application template also goes a bit further regarding features and provides essential
database, signup and password restore out of the box.

Directory structure
-------------------

The root directory contains the following subdirectories:

- `backend` - backend web application.
- `common` - files common to all applications.
- `console` - console application.
- `environments` - environment configs.
- `frontend` - frontend web application.

Root directory contains a set of files.

- `.gitignore` contains a list of directories ignored by git version system. If you need something never get to your source
  code repository, add it there.
- `composer.json` - Composer config described in detail below.
- `init` - initialization script described in "Composer config described in detail below".
- `init.bat` - same for Windows.
- `LICENSE.md` - license info. Put your project license there. Especially when opensourcing.
- `README.md` - basic info about installing template. Consider replacing it with information about your project and its
  installation.
- `requirements.php` - Yii requirements checker.
- `yii` - console application bootstrap.
- `yii.bat` - same for Windows.

Applications
------------

There are three applications in advanced template: frontend, backend and console. Frontend is typically what is presented
to end user, the project itself. Backend is admin panel, analytics and such functionality. Console is typically used for
cron jobs and low-level server management. Also it's used during application deployment and handles migrations and assets.

There's also a `common` directory that contains files used by more than one application. For example, `User` model.

frontend and backend are both web applications and both contain `web` directory. That's the webroot you should point your
webserver to.

Configuration and environments
------------------------------



Configuring Composer
--------------------

After application template is installed it's a good idea to adjust defaul `composer.json` that can be found in the root
directory:

```javascript
{
	"name": "yiisoft/yii2-app-advanced",
	"description": "Yii 2 Advanced Application Template",
	"keywords": ["yii", "framework", "advanced", "application template"],
	"homepage": "http://www.yiiframework.com/",
	"type": "project",
	"license": "BSD-3-Clause",
	"support": {
		"issues": "https://github.com/yiisoft/yii2/issues?state=open",
		"forum": "http://www.yiiframework.com/forum/",
		"wiki": "http://www.yiiframework.com/wiki/",
		"irc": "irc://irc.freenode.net/yii",
		"source": "https://github.com/yiisoft/yii2"
	},
	"minimum-stability": "dev",
	"require": {
		"php": ">=5.3.0",
		"yiisoft/yii2": "dev-master",
		"yiisoft/yii2-composer": "dev-master"
	},
	"scripts": {
		"post-create-project-cmd": [
			"yii\\composer\\InstallHandler::setPermissions"
		]
	},
	"extra": {
		"yii-install-writable": [
			"backend/runtime",
			"backend/web/assets",

			"console/runtime",
			"console/migrations",

			"frontend/runtime",
			"frontend/web/assets"
		]
	}
}

```

First we're updating basic information. Change `name`, `description`, `keywords`, `homepage` and `support` to match
your project.

Now the interesting part. You can add more packages your application needs to `require` section.
For example, to use markdown helper you need to add `michelf/php-markdown`. All these packages are coming from
[packagist.org](https://packagist.org/) so feel free to browse the website for useful code.

After your `composer.json` is changed you can run `php composer.phar update`, wait till packages are downloaded and
installed and then just use them. Autoloading of classes will be handled automatically.
