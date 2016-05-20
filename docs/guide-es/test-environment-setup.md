Preparación del entorno de test
===============================

> Note: Esta sección se encuentra en desarrollo.

Yii 2 ha mantenido integración oficial con el framework de testing [`Codeception`](https://github.com/Codeception/Codeception),
que te permite crear los siguientes tipos de tests:

- [Test de unidad](test-unit.md) - verifica que una unidad simple de código funciona como se espera;
- [Test funcional](test-functional.md) - verifica escenarios desde la perspectiva de un usuario a través de la emulación de un navegador;
- [Test de aceptación](test-acceptance.md) - verifica escenarios desde la perspectiva de un usuario en un navegador.

Yii provee grupos de pruebas listos para utilizar en ambos
[`yii2-basic`](https://github.com/yiisoft/yii2-app-basic) y
[`yii2-advanced`](https://github.com/yiisoft/yii2-app-advanced) templates de proyectos.

Para poder ejecutar estos tests es necesario instalar [Codeception](https://github.com/Codeception/Codeception).
Puedes instalarlo tanto localmente - únicamente para un proyecto en particular, o globalmente - para tu máquina de desarrollo.

Para la instalación local utiliza los siguientes comandos:

```
composer require "codeception/codeception=2.1.*"
composer require "codeception/specify=*"
composer require "codeception/verify=*"
```

Para la instalación global necesitarás la directiva `global`:

```
composer global require "codeception/codeception=2.1.*"
composer global require "codeception/specify=*"
composer global require "codeception/verify=*"
```

En caso de que nunca hayas utilizado Composer para paquetes globales, ejecuta `composer global status`. Esto debería mostrar la salida:

```
Changed current directory to <directory>
```

Entonces agrega `<directory>/vendor/bin` a tu variable de entorno `PATH`. Ahora podrás utilizar el `codecept` en la línea
de comandos a nivel global.

> Note: la instalación global te permite usar Codeception para todos los proyectos en los que trabajes en tu máquina de desarrollo y
  te permite ejecutar el comando `codecept` globalmente sin especificar su ruta. De todos modos, ese acercamiento podría ser inapropiado,
  por ejemplo, si 2 proyectos diferentes requieren diferentes versiones de Codeception instaladas.
  Por simplicidad, todos los comandos relacionados a tests en esta guía están escritos asumiendo que Codeception
  ha sido instalado en forma global.
