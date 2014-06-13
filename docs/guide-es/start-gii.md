Generando Código con Gii
========================

En esta sección, explicaremos cómo utilizar [Gii](tool-gii.md) para generar código que automáticamente
implementa algunas características comunes de una aplicación. Para lograrlo, todo lo que tienes que hacer es
ingresar la información de acuerdo a las instrucciones mostradas en la páginas web de Gii.

A lo largo de este tutorial, aprenderás

* Cómo activar Gii en tu aplicación;
* Cómo utilizar Gii para generar una clase Active Record;
* Cómo utilizar Gii para generar el código que implementa las operaciones ABM de una tabla de la base de datos.
* Cómo personalizar el código generado por Gii.


Comenzando con Gii <a name="starting-gii"></a>
------------------

[Gii](tool-gii.md) es provisto por Yii en forma de [módulo](structure-modules.md). Puedes habilitar Gii
configurándolo en la propiedad [[yii\base\Application::modules|modules]] de la aplicación. En particular,
puedes encontrar que el siguiente código ya está incluido en el archivo `config/web.php` - la configuración de la aplicación,

```php
$config = [ ... ];

if (YII_ENV_DEV) {
    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = 'yii\gii\Module';
}
```

La configuración mencionada arriba dice que al estar en el [entorno de desarrollo](concept-configurations.md#environment-constants),
la aplicación debe incluir un módulo llamado `gii`, cuya clase es [[yii\gii\Module]].

Si chequeas el [script de entrada](structure-entry-scripts.md) `web/index.php` de tu aplicación, encontrarás la línea
que esencialmente define la constante `YII_ENV_DEV` como true.

```php
defined('YII_ENV') or define('YII_ENV', 'dev');
```

De esta manera, tu aplicación ha habilitado Gii, y puedes acceder al módulo a través de la URL:

```
http://hostname/index.php?r=gii
```

![Gii](images/start-gii.png)


Generando una Clase Active Record <a name="generating-ar"></a>
---------------------------------

Para poder generar una clase Active Record con Gii, selecciona "Model Generator" completa el formulario de la siguiente manera,

* Table Name: `country`
* Model Class: `Country`

![Model Generator](images/start-gii-model.png)

Haz click el el botón "Preview". Verás que `models/Country.php` es listado en el cuadro de resultado.
Puedes hacer click en él para previsualizar su contenido.

Debido a que en la última sección ya habías creado el archivo `models/Country.php`, si haces click
en el botón `diff` cercano al nombre del archivo, verás las diferencias entre el código a ser generado
y el código que ya habías escrito.

![Previsualización del Model Generator](images/start-gii-model-preview.png)

Marca el checkbox que se encuentra al lado de "overwrite" y entonces haz click en el botón "Generate".
Verás una página de confirmación indicando que el código ha sido generado correctamente y tu archivo `models/Country.php`
ha sido sobrescrito con el código generado ahora.


Generando código de ABM (CRUD en inglés) <a name="generating-crud"></a>
----------------------------------------

Para generar un ABM, selecciona "CRUD Generator". Completa el formulario de esta manera:

* Model Class: `app\models\Country`
* Search Model Class: `app\models\CountrySearch`
* Controller Class: `app\controllers\CountryController`

![Generador de ABM](images/start-gii-crud.png)

Al hacer click en el botón "Preview" verás la lista de archivos a ser generados.

Asegúrate de haber marcado el checkbox de sobrescribir (overwrite) para ambos archivos: `controllers/CountryController.php` y
`views/country/index.php`. Esto es necesario ya que los archivos habían sido creados manualmente antes
en la sección anterior y ahora los quieres sobrescribir para poder tener un ABM funcional.


Intentándolo <a name="trying-it-out"></a>
------------

Para ver cómo funciona, accede desde tu navegador a la siguiente URL:

```
http://hostname/index.php?r=country/index
```

Verás una grilla de datos mostrando los países de la base de datos. Puedes ordenar la grilla
o filtrar los resultados escribiendo alguna condición en los encabezados de las columnas.

Por cada país mostrado en la grilla, puedes elegir ver el registro en completo, actualizarlo o eliminarlo.
Puedes incluso hacer click en el botón "Create Country" que se encuentra sobre la grilla y así cargar
un nuevo país en la base de datos.

![Grilla de Países](images/start-gii-country-grid.png)

![Actualizando un País](images/start-gii-country-update.png)

La siguiente es la lista de archivos generados en caso de que quieras inspeccionar cómo el ABM está generado,
o si quisieras personalizarlos.

* Controlador: `controllers/CountryController.php`
* Modelos: `models/Country.php` y `models/CountrySearch.php`
* Vistas: `views/country/*.php`

> Información: Gii está diseñado para ser una herramienta altamente configurable. Utilizándola con sabiduría
  puede acelerar enormemente la velocidad de desarrollo de tu aplicación. Para más detalles, consulta la
  sección [Gii](tool-gii.md).


Resumen <a name="summary"></a>
-------

En esta sección, has aprendido a utilizar Gii para generar el código que implementa completamente las características
de un ABM de acuerdo a una determinada tabla de la base de datos.
