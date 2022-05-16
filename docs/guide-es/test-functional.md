Tests funcionales
=================

Los tests funcionales verifican escenarios desde la perspectiva de un usuario.
Son similares a los [tests de aceptación](test-acceptance.md) pero en lugar de
comunicarse vía HTTP rellena el entorno como parámetros POST y GET y después ejecuta
una instancia de la aplicación directamente desde el código.

Los tests funcionales son generalmente más rápidos que los tests de aceptación y
proporcionan _stack traces_ detalladas en los fallos.
Como regla general, debería preferirlos salvo que tenga una configuración de servidor
web especial o una interfaz de usuario compleja en Javascript.

Las pruebas funcionales se implementan con ayuda del _framework_ Codeception, que tiene
una buena documentación:

- [Codeception para el _framework_ Yii](https://codeception.com/for/yii)
- [Tests funcionales de Codeception](https://codeception.com/docs/04-FunctionalTests)

## Ejecución de tests en las plantillas básica y avanzada

Si ha empezado con la plantilla avanzada, consulte la [guía de testeo](https://github.com/yiisoft/yii2-app-advanced/blob/master/docs/guide/start-testing.md)
para más detalles sobre la ejecución de tests.

Si ha empezado con la plantilla básica, consulte la [sección sobre testeo de su README](https://github.com/yiisoft/yii2-app-basic/blob/master/README.md#testing).
