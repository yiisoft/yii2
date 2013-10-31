Composer
========

Yii2 uses Composer as its package manager. It is a PHP utility that allows you to automatically install libraries and
extensions keeping them up to date and handling dependencies.

Installing Composer
-------------------

Check the official guide for [linux](http://getcomposer.org/doc/00-intro.md#installation-nix) or
[Windows](http://getcomposer.org/doc/00-intro.md#installation-windows).

Adding more packages to your project
------------------------------------

After [installing an application](installing.md) you will find `composer.json` in the root directory of your project.
This file lists packages that your application uses. The part we're interested in is `require` section.

```
{
    "require": {
        "Michelf/php-markdown": ">=1.3",
        "ezyang/htmlpurifier": ">=4.5.0"
    }
}
```

Here you can specify package name and version. Additionally to Yii extensions you may check
[packagist](http://packagist.org/) repository for general purpose PHP packages.

After packages are specified you can type either

```
php composer.phar install
```

or

```
php composer.phar update
```

depending if you're doing it for the first time or not. Then, after some waiting, packages will be installed and ready
to use. You don't need anything to be configured additionally.


See also
--------

- [Official Composer documentation](http://getcomposer.org).