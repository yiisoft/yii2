Composer
========

Yii2 uses Composer as its dependency management tool. Composer is a PHP utility that can automatically handle the installation of needed libraries and
extensions, thereby keeping those third-party resources up to date while absolving you of the need to manually manage the project's dependencies.

Installing Composer
-------------------

In order to install Composer, check the official guide for your operating system:

* [Linux](http://getcomposer.org/doc/00-intro.md#installation-nix)
* [Windows](http://getcomposer.org/doc/00-intro.md#installation-windows)

All of the details can be found in the guide, but you'll either download Composer directly from [http://getcomposer.org/](http://getcomposer.org/), or run the following command:

```
curl -s http://getcomposer.org/installer | php
```

We strongly recommend a global composer installation.

Working with composer
---------------------

The act of [installing a Yii application](installation.md) with 

```
composer.phar create-project --stability dev yiisoft/yii2-app-basic
``` 

creates a new root directory for your project along with the `composer.json` and `compoer.lock` file.

While the former lists the packages, which your application requires directly together with a version constraint, while the latter keeps track of all installed packages and their dependencies in a specific revision. Therefore the `composer.lock` file should also be [committed to your version control system](https://getcomposer.org/doc/01-basic-usage.md#composer-lock-the-lock-file).

These two files are strongly linked to the two composer commands `update` and `install`.
Usually, when working with your project, such as creating another copy for development or deployment, you will use 

```
composer.phar install
```

to make sure you get exactly the same packages and versions as specified in `composer.lock`. 

Only if want to intentionally update the packages in your project you should run 

```
composer.phar update
```

As an example, packages on `dev-master` will constantly get new updates when you run `update`, while running `install` won't, unless you've pulled an update of the `composer.lock` file.

There are several paramaters available to the above commands. Very commonly used ones are `--no-dev`, which would skip packages in the `require-dev` section and `--prefer-dist`, which downloads archives if available, instead of checking out repositories to your `vendor` folder.

> Composer commands must be executed within your Yii project's directory, where the `composer.json` file can be found.
Depending upon your operating system and setup, you may need to provide paths to the PHP executable and
to the `composer.phar` script.


Adding more packages to your project
------------------------------------

To add two new packages to your project run the follwing command:

```
composer.phar require "michelf/php-markdown:>=1.3" "ezyang/htmlpurifier:>4.5.0"
```

This will resolve the dependencies and then update your `composer.json` file.
The above example says that a version greater than or equal to 1.3 of Michaelf's PHP-Markdown package is required
and version 4.5.0 or greater of Ezyang's HTMLPurifier.

For details of this syntax, see the [official Composer documentation](https://getcomposer.org/doc/01-basic-usage.md#package-versions).

The full list of available Composer-supported PHP packages can be found at [packagist](http://packagist.org/). You may also search packages interactively just by entering `composer.phar require`.

### Manually editing your version constraints

You may also edit the `composer.json` file manually. Within the `require` section, you specify the name and version of each required package, same as with the command above.

```json
{
    "require": {
        "michelf/php-markdown": ">=1.4",
        "ezyang/htmlpurifier": ">=4.6.0"
    }
}
```

Once you have edited the `composer.json`, you can invoke Composer to download the updated dependencies. Run 

```
composer.phar update michelf/php-markdown ezyang/htmlpurifier
``` 

afterwards.

> Depending on the package additional configuration may be required (eg. you have to register a module in the config), but autoloading of the classes should be handled by composer.


Using a specifc version of a package
------------------------------------

Yii always comes with the latest version of a required library that it is compatible with, but allows you to use an older version if you need to.

A good example for this is jQuery which has [dropped old IE browser support](http://jquery.com/browser-support/) in version 2.x.
When installing Yii via composer the installed jQuery version will be the latest 2.x release. When you want to use jQuery 1.10
because of IE browser support you can adjust your composer.json by requiring a specific version of jQuery like this:

```json
{
    "require": {
        ...
        "yiisoft/jquery": "1.10.*"
    }
}
```


FAQ
---

### Getting "You must enable the openssl extension to download files via https"

If you're using WAMP check [this answer at StackOverflow](http://stackoverflow.com/a/14265815/1106908).

### Getting "Failed to clone <URL here>, git was not found, check that it is installed and in your Path env."

Either install git or try adding `--prefer-dist` to the end of `install` or `update` command.

### Should I Commit The Dependencies In My Vendor Directory?

Short answer: No. Long answer, see [here](https://getcomposer.org/doc/faqs/should-i-commit-the-dependencies-in-my-vendor-directory.md).


See also
--------

- [Official Composer documentation](http://getcomposer.org).
