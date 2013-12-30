Image Extension for Yii 2
==============================

This extension adds most common image functions and also acts as a wrapper to [Imagine](http://imagine.readthedocs.org/)
image manipulation library.

Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require yiisoft/yii2-imagine "*"
```

or add

```json
"yiisoft/yii2-imagine": "*"
```

to the `require` section of your composer.json.


Usage & Documentation
---------------------

This extension is a wrapper to the [Imagine](http://imagine.readthedocs.org/) and also adds the most common methods
used for Image manipulation.

To use this extension, you can use it in to ways, whether you configure it on your application file or you use it
directly.

The following shows how to use it via application configuration file:  

```
// configuring on your application configuration file
'components' => [
    'image' => [
        'class' => 'yii\imagine\Image',
        'driver' => \yii\imagine\Image::DRIVER_GD2,
    ]
    ...
]

// Once configured you can access to the extension like this:
$img = Yii::$app->image->thumb('path/to/image.jpg', 120, 120);

```

This is how to use it directly:

```
use yii\imagine\Image;

$image = new Image();
$img = $image->thumb('path/to/image.jpg', 120, 120);
```
**About the methods**  
Each method returns an instance to `\Imagine\Image\ManipulatorInterface`, that means that you can easily make use of the methods included in the `Imagine` library: 

```
// frame, rotate and save an image 

Yii::$app->image->frame('path/to/image.jpg', 5, '666', 0)
    ->rotate(-8)
    ->save('path/to/destination/image.jpg', ['quality' => 50]);
```