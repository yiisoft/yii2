Flujo de Trabajo de Traducción
==============================

Yii se traduce en muchos idiomas con el fin de ser útil para desarrolladores de aplicaciones e internacionales.
Dos áreas principales donde la contribución es muy bienvenida son la documentación y los mensajes del framework.

Mensajes del Framework
----------------------

El framework tiene dos tipos de mensajes: excepciones que están destinadas al desarrollador y nunca se traducen, y mensajes
que en realidad son visibles para el usuario final, tales como errores de validación.

El orden para comenzar con la traducción de mensajes:

1. Comprobar que en `framework/messages/config.php` su idioma aparece en `languages`. Si no, añade tu idioma allí (recuerda que debes mantener la lista en orden alfabético).
El formato de código de idioma debe seguir el [Código de Idiomas IETF](https://es.wikipedia.org/wiki/C%C3%B3digo_de_idioma_IETF), por ejemplo, `es`.
2. Ir al directorio `framework` y ejecutar el comando `yii message/extract @yii/messages/config.php --languages=<tu-idioma>`.
3. Traducir los mensajes en `framework/messages/tu-idioma/yii.php`. Asegúrate de guardar el archivo con codificación UTF-8.
4. [Crear un pull request](https://github.com/yiisoft/yii2/blob/master/docs/internals-es/git-workflow.md).

Con el fin de mantener la traducción al día puedes ejecutar `yii message/extract @yii/messages/config.php --languages=<tu-idioma>` nuevamente.
Se volverán a extraer automáticamente los mensajes de mantenimiento intactos sin los cambios.

En el archivo de traducción de cada elemento del `array` representa un mensaje (clave) y su la traducción (valor). Si el valor está vacío, el mensaje se considera como no traducido.
Los mensajes que ya no necesiten traducción tendrán sus traducciones encerrado entre un par de marcas '@@'. El texto de los mensajes se puede utilizar con el formato de formas plurales.
Chequea la [sección i18n de la guía](../guide-es/tutorial-i18n.md) para más detalles.

Documentación
-------------

Coloca las traducciones de la documentación bajo `docs/<original>-<language>` donde `<original>` es el nombre de la documentación original como `guide` o `internals`
y `<language>` es el código del idioma al que se está traduciendo. Para la traducción al español de la guía, es `docs/guide-es`.

Después de que el trabajo inicial está hecho, puedes obtener los cambios desde la última traducción del archivo usando un comando especial del directorio `build`:

```
php build translation "../docs/guide" "../docs/guide-es" "Reporte de traducción guia en Español" > report_guide_es.html
```

Si recibes un error de composer, ejecuta `composer install` en el directorio raíz.

Convenios para la traducción
----------------------------

Las palabras en inglés que son propias del framework o de PHP se pueden dejar en el idioma original. Ejemplos: `namespace`, `assets`, `helper`, `widget`, etc.

Para las palabras que están muy ligadas a conceptos extendidos se deben traducir y poner entre paréntesis su equivalente en el idioma original. Ejemplos : `petición` (request), `respuesta` (response), `comportamiento` (behavior), etc.

> Aclaraciones :
* Sólo mencionar una vez entre paréntesis la palabra original en su primera aparición en el texto o en el fichero README.md,
evitando redundancias. Ejemplo: vista(view), controlador(controller), etc.
* Si una palabra se refiere a un concepto o acción se aplicará la traducción, si por el contrario se refiere a un tipo de dato de php o del framework no se debe traducir.
* El equipo de traductores hemos escogido el Español-latino para elaborar las traducciones de las guías en Español, eviten usar expresiones o palabras autóctonas de su región para un mayor acercamiento al resto de hispano hablantes.
