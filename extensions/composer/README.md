Yii 2 Composer Installer
========================

This is the composer installer for Yii 2 extensions. It implements a new composer package type named `yii2-extension`,
which should be used by all Yii 2 extensions if they are distributed as composer packages.


Usage
-----

To use Yii 2 composer installer, simply set `type` to be `yii2-extension` in your `composer.json`,
like the following:

```json
{
	"type": "yii2-extension",
	"require": {
		"yiisoft/yii2": "*"
	},
	...
}
```

You may specify a bootstrap class in the `extra` section. The `init()` method of the class will be executed each time
the Yii 2 application is responding to a request. For example,

```json
{
	"type": "yii2-extension",
	...,
	"extra": {
		"bootstrap": "yii\\jui\\Extension"
	}
}
```

The `Installer` class also implements a static method `setPermission()` that can be called after
a Yii 2 projected is installed, through the `post-create-project-cmd` composer script.
The method will set specified directories or files to be writable or executable, depending on
the corresponding parameters set in the `extra` section of the `composer.json` file.
For example,

```json
{
	"name": "yiisoft/yii2-app-basic",
	"type": "project",
	...
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
