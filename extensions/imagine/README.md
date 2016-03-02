Imagine Extension for Yii 2
===========================

This extension adds most common image functions and also acts as a wrapper to [Imagine](http://imagine.readthedocs.org/)
image manipulation library.

<<<<<<< HEAD
This repository is a git submodule of <https://github.com/yiisoft/yii2>.
Please submit issue reports and pull requests to the main repository.
For license information check the [LICENSE](LICENSE.md)-file.

=======
>>>>>>> yiichina/master
Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
<<<<<<< HEAD
php composer.phar require --prefer-dist yiisoft/yii2-imagine
=======
php composer.phar require --prefer-dist yiisoft/yii2-imagine "*"
>>>>>>> yiichina/master
```

or add

```json
<<<<<<< HEAD
"yiisoft/yii2-imagine": "~2.0.0"
=======
"yiisoft/yii2-imagine": "*"
>>>>>>> yiichina/master
```

to the `require` section of your composer.json.


Usage & Documentation
---------------------

This extension is a wrapper to the [Imagine](http://imagine.readthedocs.org/) and also adds the most commonly used
image manipulation methods.

The following example shows how to use this extension:

```php
use yii\imagine\Image;

// frame, rotate and save an image
Image::frame('path/to/image.jpg', 5, '666', 0)
    ->rotate(-8)
    ->save('path/to/destination/image.jpg', ['quality' => 50]);
```

Note that each `Image` method returns an instance of `\Imagine\Image\ImageInterface`.
This means you can make use of the methods included in the `Imagine` library:
