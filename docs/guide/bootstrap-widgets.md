Bootstrap widgets
=================

Out of the box, Yii includes support for the [Bootstrap 3](http://getbootstrap.com/) markup and components framework (also known as "Twitter Bootstrap"). Bootstrap is an excellent, responsive framework that can greatly speed up the client-side of your development process.

The core of Bootstrap is represented by two parts:

- CSS basics, such as a grid layout system, typography, helper classes, and responsive utilities.
- Ready to use components, such as menus, pagination, modal boxes, tabs etc.

Basics
------

Yii doesn't wrap the bootstrap basics into PHP code since HTML is very simple by itself in this case. You can find details
about using the basics at [bootstrap documentation website](http://getbootstrap.com/css/). Still Yii provides a
convenient way to include bootstrap assets in your pages with a single line added to `AppAsset.php` located in your
`config` directory:

```php
public $depends = [
	'yii\web\YiiAsset',
	'yii\bootstrap\BootstrapAsset', // this line
	// 'yii\bootstrap\BootstrapThemeAsset' // uncomment to apply bootstrap 2 style to bootstrap 3
];
```

Using bootstrap through Yii asset manager allows you to minimize its resources and combine with your own resources when
needed.

Yii widgets
-----------

Most complex bootstrap components are wrapped into Yii widgets to allow more robust syntax and integrate with
framework features. All widgets belong to `\yii\bootstrap` namespace:

- Alert
- Button
- ButtonDropdown
- ButtonGroup
- Carousel
- Collapse
- Dropdown
- Modal
- Nav
- NavBar
- Progress
- Tabs
