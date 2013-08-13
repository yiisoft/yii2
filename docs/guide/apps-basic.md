Basic application template
==========================

This template is a perfect fit for small projects or learning Yii2.

The application has four pages: the homepage, the about page, the contact page and the login page.
The contact page displays a contact form that users can fill in to submit their inquiries to the webmaster,
and the login page allows users to be authenticated before accessing privileged contents.

Directory structure
-------------------

The basic application does not divide application directories much. Here's the basic structure:

- `commands` - console controllers.
- `config` - configuration.
- `controllers` - web controllers.
- `models` - application models.
- `runtime` - logs, states, file cache.
- `views` - view templates.
- `web` - webroot.

Root directory contains a set of files.

- `.gitignore` contains a list of directories ignored by git version system. If you need something never get to your source
code repository, add it there.
- `codeception.yml` - Codeception config.
- `composer.json` - Composer config described in detail below.
- `LICENSE.md` - license info. Put your project license there. Especially when opensourcing.
- `README.md` - basic info about installing template. Consider replacing it with information about your project and its
  installation.
- `requirements.php` - Yii requirements checker. Don't forget to delete it when deployed to the server.
- `yii` - console application bootstrap.
- `yii.bat` - console application bootstrap for Windows.


### config

This directory contains configuration files:

- `AppAsset.php` - definition of application assets such as CSS, JavaScript etc. Check [Managing assets](assets.md) for
  details.
- `console.php` - console application configuration.
- `params.php` - common application parameters.
- `web.php` - web application configuration.
- `web-test.php` - web application configuration used when running functional tests.

All these files except `AppAsset.php` are returning arrays used to configure corresponding application properties. Check
[Configuration](configuration.md) guide section for details.

### views

Views directory contains templates your application is using. In the basic template there are:

```
layouts
	main.php
site
	about.php
	contact.php
	error.php
	index.php
	login.php
```

`layouts` contains HTML layouts i.e. page markup except content: doctype, head section, main menu, footer etc.
The rest are typically controller views. By convention these are located in subdirectories matching controller id. For
`SiteController` views are under `site`. Names of the views themselves are typically match controller action names.
Partials are often named starting with underscore.

### web

Directory is a webroot. Typically a webserver is pointed into it.

```
assets
css
index.php
index-test.php
```

`assets` contains published asset files such as CSS, JavaScript etc. Publishing process is automatic so you don't need
to do anything with this directory other than making sure Yii has enough permissions to write to it.

`css` contains plain CSS files and is useful for global CSS that isn't going to be compressed or merged by assets manager.

`index.php` is the main web application bootstrap and is the central entry point for it. `index-test.php` is the entry
point for functional testing.

Configuring Composer
--------------------

After application template is installed it's a good idea to adjust defaul `composer.json` that can be found in the root
directory:

```javascript
{
	"name": "yiisoft/yii2-app-basic",
	"description": "Yii 2 Basic Application Template",
	"keywords": ["yii", "framework", "basic", "application template"],
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
			"runtime",
			"web/assets"
		],
		"yii-install-executable": [
			"yii"
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