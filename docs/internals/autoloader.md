Yii2 class loader
=================

Yii 2 class loader is PSR-0 compliant. That means it can handle most of the PHP
libraries and frameworks out there.

In order to autoload a library you need to set a root alias for it.

PEAR-style libraries
--------------------

```php
\Yii::setAlias('@Twig', '@app/vendors/Twig');
```

References
----------

- BaseYii::autoload