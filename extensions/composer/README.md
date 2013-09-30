Yii 2.0 Public Preview - Composer Installer
===========================================

Thank you for choosing Yii - a high-performance component-based PHP framework.

If you are looking for a production-ready PHP framework, please use
[Yii v1.1](https://github.com/yiisoft/yii).

Yii 2.0 is still under heavy development. We may make significant changes
without prior notices. **Yii 2.0 is not ready for production use yet.**

[![Build Status](https://secure.travis-ci.org/yiisoft/yii2.png)](http://travis-ci.org/yiisoft/yii2)

This is the yii2 composer installer.


Installation
------------

This extension offers you enhanced Composer handling for your yii2-project. It will therefore require you to use Composer.

```
php composer.phar require yiisoft/yii2-composer "*"
```

*Note: You might have to run `php composer.phar selfupdate` before using this extension.*


Usage & Documentation
---------------------

This extension allows you to hook to certain composer events and automate preparing your Yii2 application for further usage.

After the package is installed, the `composer.json` file has to be modified to enable this extension.

To see it in action take a look at the example apps in the repository:

- [Basic](https://github.com/suralc/yii2/blob/master/apps/basic/composer.json#L27)
- [Advanced](https://github.com/suralc/yii2/blob/extensions-readme/apps/advanced/composer.json)

However it might be useful to read through the official composer [documentation](http://getcomposer.org/doc/articles/scripts.md)
to understand what this extension can do for you and what it can't.

You can also use this as a template to create your own composer additions to ease development and deployment of your app.
