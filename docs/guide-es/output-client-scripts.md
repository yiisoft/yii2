Trabajar con Scripts del Cliente
================================

> Note: Esta sección se encuentra en desarrollo.

### Registrar scripts

Con el objeto [[yii\web\View]] puedes registrar scripts. Hay dos métodos dedicados a esto:
[[yii\web\View::registerJs()|registerJs()]] para scripts en línea
[[yii\web\View::registerJsFile()|registerJsFile()]] para scripts externos.
Los scripts en línea son útiles para configuración y código generado dinámicamente.
El método para agregarlos puede ser utilizado así:

```php
$this->registerJs("var options = ".json_encode($options).";", View::POS_END, 'my-options');
```

El primer argumento es el código JS real que queremos insertar en la página. El segundo argumento
determina en qué parte de la página debería ser insertado el script. Los valores posibles son:

- [[yii\web\View::POS_HEAD|View::POS_HEAD]] para la sección head.
- [[yii\web\View::POS_BEGIN|View::POS_BEGIN]] justo después de la etiqueta `<body>`.
- [[yii\web\View::POS_END|View::POS_END]] justo antes de cerrar la etiqueta `</body>`.
- [[yii\web\View::POS_READY|View::POS_READY]] para ejecutar código en el evento `ready` del documento. Esto registrará [[yii\web\JqueryAsset|jQuery]] automáticamente.
- [[yii\web\View::POS_LOAD|View::POS_LOAD]] para ejecutar código en el evento `load` del documento. Esto registrará [[yii\web\JqueryAsset|jQuery]] automáticamente.

El último argumento es un ID único del script, utilizado para identificar el bloque de código y reemplazar otro con el mismo ID
en vez de agregar uno nuevo. En caso de no proveerlo, el código JS en sí será utilizado como ID.

Un script externo puede ser agregado de esta manera:

```php
$this->registerJsFile('https://example.com/js/main.js', ['depends' => [\yii\web\JqueryAsset::class]]);
```

Los argumentos para [[yii\web\View::registerJsFile()|registerJsFile()]] son similares a los de
[[yii\web\View::registerCssFile()|registerCssFile()]]. En el ejemplo anterior,
registramos el archivo `main.js` con dependencia de `JqueryAsset`. Esto quiere decir que el archivo `main.js`
será agregado DESPUÉS de `jquery.js`. Si esta especificación de dependencia, el orden relativo entre
`main.js` y `jquery.js` sería indefinido.

Como para [[yii\web\View::registerCssFile()|registerCssFile()]], es altamente recomendable que utilices
[asset bundles](structure-assets.md) para registrar archivos JS externos más que utilizar [[yii\web\View::registerJsFile()|registerJsFile()]].


### Registrar asset bundles

Como mencionamos anteriormente, es preferible utilizar asset bundles en vez de usar CSS y JavaScript directamente. Puedes obtener detalles
de cómo definir asset bundles en la sección [gestor de assets](structure-assets.md) de esta guía. Utilizar asset bundles
ya definidos es muy sencillo:

```php
\frontend\assets\AppAsset::register($this);
```



### Registrar CSS

Puedes registrar CSS utilizando [[yii\web\View::registerCss()|registerCss()]] o [[yii\web\View::registerCssFile()|registerCssFile()]].
El primero registra un bloque de código CSS mientras que el segundo registra un archivo CSS externo. Por ejemplo,

```php
$this->registerCss("body { background: #f00; }");
```

El código anterior dará como resultado que se agregue lo siguiente a la sección head de la página:

```html
<style>
body { background: #f00; }
</style>
```

Si quieres especificar propiedades adicionales a la etiqueta style, pasa un array de claves-valores como tercer argumento.
Si necesitas asegurarte que haya sólo una etiqueta style utiliza el cuarto argumento como fue mencionado en las descripciones de meta etiquetas.

```php
$this->registerCssFile("https://example.com/css/themes/black-and-white.css", [
    'depends' => [BootstrapAsset::class],
    'media' => 'print',
], 'css-print-theme');
```

El código de arriba agregará un link al archivo CSS en la sección head de la página.

* El primer argumento especifica el archivo CSS a ser registrado.
* El segundo argumento especifica los atributos HTML de la etiqueta `<link>` resultante. La opción `depends`
  es especialmente tratada. Esta especifica de qué asset bundles depende este archivo CSS. En este caso, depende
  del asset bundle [[yii\bootstrap\BootstrapAsset|BootstrapAsset]]. Esto significa que el archivo CSS será agregado
  *después* de los archivos CSS de [[yii\bootstrap\BootstrapAsset|BootstrapAsset]].
* El último argumento especifica un ID que identifica al archivo CSS. Si no es provisto, se utilizará la URL
  del archivo.


Es altamente recomendable que ustilices [asset bundles](structure-assets.md) para registrar archivos CSS en vez de
utilizar [[yii\web\View::registerCssFile()|registerCssFile()]]. Utilizar asset bundles te permite combinar y comprimir
varios archivos CSS, deseable en sitios web de tráfico alto.
