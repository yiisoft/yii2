Composer
========

Yii2 uses Composer as its package manager. Composer is a PHP utility that can automatically handle the installation of needed libraries and
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

Adding more packages to your project
------------------------------------

The act of [installing a Yii application](installation.md) creates the `composer.json` file in the root directory of your project.
In this file you list the packages that your application requires. For Yii sites, the most important part of the file is the `require` section:

```
{
    "require": {
        "Michelf/php-markdown": ">=1.3",
        "ezyang/htmlpurifier": ">=4.6.0"
    }
}
```

Within the `require` section, you specify the name and version of each required package.
The above example says that a version greater than or equal to 1.3 of Michaelf's PHP-Markdown package is required,
as is version 4.5 or greater of Ezyang's HTMLPurifier.
For details of this syntax, see the [official Composer documentation](http://getcomposer.org).

The full list of available Composer-supported PHP packages can be found at [packagist](http://packagist.org/).

Once you have edited the `composer.json`, you can invoke Composer to install the identified dependencies.
For the first installation of the dependencies, use this command:

```
php composer.phar install
```

This must be executed within your Yii project's directory, where the `composer.json` file can be found.
Depending upon your operating system and setup, you may need to provide paths to the PHP executable and
to the `composer.phar` script.

For an existing installation, you can have Composer update the dependencies using:

```
php composer.phar update
```

Again, you may need to provide specific path references.

In both cases, after some waiting, the required packages will be installed and ready to use in your Yii application.
No additional configuration of those packages will be required.


FAQ
---

### Getting "You must enable the openssl extension to download files via https"

If you're using WAMP check [this answer at StackOverflow](http://stackoverflow.com/a/14265815/1106908).

### Getting "Failed to clone <URL here>, git was not found, check that it is installed and in your Path env."

Either install git or try adding `--prefer-dist` to the end of `install` or `update` command.


See also
--------

- [Official Composer documentation](http://getcomposer.org).