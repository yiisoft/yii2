Flujo de Trabajo de Traducción
==============================

Yii se traduce en muchos idiomas con el fin de ser útil para desarrolladores de aplicaciones e internacionales. Dos áreas principales donde la contribución es muy bienvenida son la documentación y los mensajes del framework.

Framework Mensajes 
------------------

Framework tiene dos tipos de mensajes: excepciones que están destinados al desarrollador y nunca se traducen y mensajes 
que en realidad son visibles para el usuario final, tales como errores de validación.

El orden para comenzar con la traducción de mensajes:

1. Comprobar `framework/messages/config.php` y asegúrese de que su lenguaje aparece en `lenguajes`. Si no, añadir su lenguaje allí (recuerde que debe mantener la lista en orden alfabético). El formato de código de idioma debe seguir [Código de Idiomas IETF](http://es.wikipedia.org/wiki/C%C3%B3digo_de_idioma_IETF), por ejemplo, `es`.
2. Ir al `framework` y ejecutar `yii message/extract messages/config.php`.
3. Traducir los mensajes en `framework/messages/your_lenguaje/yii.php`. Asegúrese de guardar el archivo con codificación UTF-8.
4. [Crear un pull request](https://github.com/yiisoft/yii2/blob/master/docs/internals-es/git-workflow.md).

Con el fin de mantener la traducción al día puede ejecutar `yii message/extract messages/config.php` nuevamente. Se volverán a extraer automáticamente los mensajes de mantenimiento intactos sin los cambios.

En el archivo de traducción de cada elemento de la matriz representa la traducción (valor) de un mensaje (clave). Si el valor está vacío, el mensaje se considera como no traducida. Los mensajes que ya no necesiten traducción tendrán sus traducciones encerrado entre un par de marcas »@@. Cadena de mensaje se puede utilizar con el formato de formas plurales. Compruebe [sección i18n de la guía](../guide-es/tutorial-i18n.md) para más detalles.

Documentación
-------------

Coloque traducciones de documentación bajo `docs/<original>-<lenguaje>` donde `<original>` es el nombre de la documentación original como `guide` o `internals` y `<lenguaje>` es el código de Lenguaje de los docs Lenguaje se convierten a. Para la traducción de guias es `docs/guide-es`.

Después del trabajo inicial se lleva a cabo usted puede conseguir lo que ha cambiado desde la última traducción del fichero usando un comando especial del directorio `build`:

```
php build translation "../docs/guide" "../docs/guide-es" "Reporte de traducción guia en Español" > report_guide_es.html
```

Si se quejan de composer, ejecutar `composer install` en el directorio raíz.

Convenios para la traducción
----------------------------

- active record — sin traducción
- cache — sin traducción
- framework — sin traducción
- helper — sin traducción
- hash — sin traducción
- id — sin traducción
- widget — sin traducción
- script — sin traducción
- assets — sin traducción
- bootstrapping | bootstrap — sin traducción
- routing — sin traducción
- logging — sin traducción
- cookies — sin traducción
- controller — controlador
- model — modelo
- view — vista
- themes — temas o plantillas
- behaviors — comportamientos
- handlers — manipuladores
- instantiating — instanciando
- link — enlace
- render — sin traducción
- DatePicker — sin traducción
- rendering — renderizando
