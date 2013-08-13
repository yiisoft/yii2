Bootstrap widgets
=================

Yii includes support of [Bootstrap 3](http://getbootstrap.com/) markup and components framework out of the box. It is an
excellent framework that allows you to speed up development a lot.

Bootstrap is generally about two parts:

- Basics such as grid system, typography, helper classes and responsive utilities.
- Ready to use components such as menus, pagination, modal boxes, tabs etc.

Basics
------

Yii doesn't wrap bootstrap basics into PHP code since HTML is very simple by itself in this case. You can find details
about using the basics at [bootstrap documentation website](http://getbootstrap.com/css/). Still Yii provides a
convenient way to include bootstrap assets in your pages with a single line added to `AppAsset.php` located in your
`config` directory:

```php
public $depends = array(
	'yii\web\YiiAsset',
	'yii\bootstrap\BootstrapAsset', // this line
);
```

Using bootstrap through Yii asset manager allows you to combine and minimize its resources with your own ones when
needed.

Yii widgets
-----------

Most complex bootstrap components are wrapped into Yii widgets to allow more robust syntax and integrate with
framework features. All widgets belong to `\yii\bootstrap` namespace. Let's review these.

### Alert

### Button

### ButtonDropdown

### ButtonGroup

### Carousel

### Collapse

### Dropdown

### Modal

### Nav

### NavBar

### Progress

### Tabs

### Typeahead
