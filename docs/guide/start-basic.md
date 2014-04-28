Basic application template
==========================

The basic Yii application template is a perfect fit for small projects or when you're just learning the framework.

The basic application template includes four pages: a homepage, an about page, a contact page, and a login page.
The contact page displays a contact form that users can fill in to submit their inquiries to the webmaster. Assuming the site has access to a mail server and that the administrator's email address is entered in the configuration file, the contact form will work. The same goes for the login page, which allows users to be authenticated before accessing privileged content.

Installation
------------

Installation of the framework requires [Composer](http://getcomposer.org/). If you do not have Composer on your system yet, you may download it from
[http://getcomposer.org/](http://getcomposer.org/), or run the following command on Linux/Unix/MacOS:

~~~
curl -s http://getcomposer.org/installer | php
~~~

You can then create a basic Yii application using the following :

~~~
php composer.phar create-project --prefer-dist --stability=dev yiisoft/yii2-app-basic /path/to/yii-application
~~~

Now set document root directory of your Web server to /path/to/yii-application/web and you should be able to access the application using the URL `http://localhost/`.

Directory structure
-------------------

The basic application does not divide application directories much. Here's the basic structure:

- `assets` - application asset files.
  - `AppAsset.php` - definition of application assets such as CSS, JavaScript etc. Check [Managing assets](assets.md) for
    details.
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
- `requirements.php` - Yii requirements checker.
- `yii` - console application bootstrap.
- `yii.bat` - same for Windows.


### config

This directory contains configuration files:

- `console.php` - console application configuration.
- `params.php` - common application parameters.
- `web.php` - web application configuration.
- `web-test.php` - web application configuration used when running functional tests.

All these files are returning arrays used to configure corresponding application properties. Check
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

After application template is installed it's a good idea to adjust default `composer.json` that can be found in the root
directory:

```json
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
        "php": ">=5.4.0",
        "yiisoft/yii2": "*",
        "yiisoft/yii2-swiftmailer": "*",
        "yiisoft/yii2-bootstrap": "*",
        "yiisoft/yii2-debug": "*",
        "yiisoft/yii2-gii": "*"
    },
    "scripts": {
        "post-create-project-cmd": [
            "yii\\composer\\Installer::setPermission"
        ]
    },
    "extra": {
        "writable": [
            "runtime",
            "web/assets"
        ],
        "executable": [
            "yii"
        ]
    }
}
```

First we're updating basic information. Change `name`, `description`, `keywords`, `homepage` and `support` to match
your project.

Now the interesting part. You can add more packages your application needs to `require` section.
All these packages are coming from [packagist.org](https://packagist.org/) so feel free to browse the website for useful code.

After your `composer.json` is changed you can run `php composer.phar update --prefer-dist`, wait till packages are downloaded and
installed and then just use them. Autoloading of classes will be handled automatically.
