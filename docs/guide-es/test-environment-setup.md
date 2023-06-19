Preparación del entorno de pruebas
==================================

Yii 2 ha mantenido oficialmente integración con el _framework_ de testeo [`Codeception`](https://github.com/Codeception/Codeception),
que le permite crear los siguientes tipos de tests:

- [Unitarias](test-unit.md) - verifica que una unidad simple de código funciona como se espera;
- [Funcional](test-functional.md) - verifica escenarios desde la perspectiva de un usuario a través de la emulación de un navegador;
- [De aceptación](test-acceptance.md) - verifica escenarios desde la perspectiva de un usuario en un navegador.

Yii provee grupos de pruebas listos para utilizar para los tres tipos de test, tanto en la plantilla de proyecto
[`yii2-basic`](https://github.com/yiisoft/yii2-app-basic) como en
[`yii2-advanced`](https://github.com/yiisoft/yii2-app-advanced).

Codeception viene preinstalado tanto en la plantilla de proyecto básica como en la avanzada.
En caso de que no use una de estas plantillas, puede instalar Codeception ejecutando
las siguientes órdenes de consola:

```
composer require codeception/codeception
composer require codeception/specify
composer require codeception/verify
```
