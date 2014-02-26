<?= $generator->title ?>

<?= str_repeat('=', mb_strlen($generator->title, \Yii::$app->charset)) ?>

<?= $generator->description ?>


Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require --prefer-dist <?= $generator->vendorName ?>/<?= $generator->packageName ?> "*"
```

or add

```
"<?= $generator->vendorName ?>/<?= $generator->packageName ?>": "*"
```

to the require section of your `composer.json` file.


Usage
-----

Once the extension is installed, simply use it in your code by  :

```php
<?= "<?= \\{$generator->namespace}AutoloadExample::wiget(); ?>" ?>
```