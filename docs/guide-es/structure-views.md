Vistas
======

Las Vistas (views) son una parte de la arquitectura [MVC](http://es.wikipedia.org/wiki/Modelo%E2%80%93vista%E2%80%93controlador).
Estas son el código responsable de presentar los datos al usuario final. En una aplicación Web, las vistas son usualmente creadas
en términos de *templates* que son archivos PHP que contienen principalmente HTML y PHP.
Estas son manejadas por el [componente de la aplicación](structure-application-components.md) [[yii\web\View|view]], el cual provee los métodos comúnmente utilizados
para facilitar la composición y renderizado. Por simplicidad, a menudo nos referimos a los templates de vistas o archivos de templates
como vistas.


## Crear Vistas <span id="creating-views"></span>

Como fue mencionado, una vista es simplemente un archivo PHP que mezcla código PHP y HTML. La siguiente es una vista
que muestra un formulario de login. Como puedes ver, el código PHP utilizado es para generar contenido dinámico, como el
título de la página y el formulario mismo, mientras que el código HTML organiza estos elementos en una página HTML mostrable.

```php
<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $form yii\widgets\ActiveForm */
/* @var $model app\models\LoginForm */

$this->title = 'Login';
?>
<h1><?= Html::encode($this->title) ?></h1>

<p>Por favor completa los siguientes campos para loguearte:</p>

<?php $form = ActiveForm::begin(); ?>
    <?= $form->field($model, 'username') ?>
    <?= $form->field($model, 'password')->passwordInput() ?>
    <?= Html::submitButton('Login') ?>
<?php ActiveForm::end(); ?>
```

Dentro de una vista, puedes acceder a la variable `$this` referida al [[yii\web\View|componente view]]
que maneja y renderiza la vista actual.

Además de `$this`, puede haber otras variables predefinidas en una vista, como `$form` y `$model` en el
ejemplo anterior. Estas variables representan los datos que son *inyectados* a la vista desde el [controlador](structure-controllers.md)
o algún otro objeto que dispara la [renderización de la vista](#rendering-views).

> Tip: La lista de variables predefinidas están listadas en un bloque de comentario al principio de la vista así
  pueden ser reconocidas por las IDEs. Esto es también una buena manera de documentar tus propias vistas.


### Seguridad <span id="security"></span>

Al crear vistas que generan páginas HTML, es importante que codifiques (encode) y/o filtres los datos
provenientes de los usuarios antes de mostrarlos. De otro modo, tu aplicación puede estar expuesta
a ataques tipo [cross-site scripting](http://es.wikipedia.org/wiki/Cross-site_scripting).

Para mostrar un texto plano, codifícalos previamente utilizando [[yii\helpers\Html::encode()]]. Por ejemplo, el siguiente código aplica
una codificación del nombre de usuario antes de mostrarlo:

```php
<?php
use yii\helpers\Html;
?>

<div class="username">
    <?= Html::encode($user->name) ?>
</div>
```

Para mostrar contenido HTML, utiliza [[yii\helpers\HtmlPurifier]] para filtrarlo antes. Por ejemplo, el siguiente código
filtra el contenido del post antes de mostrarlo en pantalla:

```php
<?php
use yii\helpers\HtmlPurifier;
?>

<div class="post">
    <?= HtmlPurifier::process($post->text) ?>
</div>
```

> Tip: Aunque HTMLPurifier hace un excelente trabajo al hacer la salida más segura, no es rápido. Deberías considerar
el aplicar un [caching](caching-overview.md) al resultado de aplicar el filtro si tu aplicación requiere un gran desempeño (performance).


### Organizar las Vistas <span id="organizing-views"></span>

Así como en [controladores](structure-controllers.md) y [modelos](structure-models.md), existen convenciones para organizar las vistas.

* Para vistas renderizadas por controladores, deberían colocarse en un directorio tipo `@app/views/ControllerID` por defecto,
  donde `ControllerID` se refiere al [ID del controlador](structure-controllers.md#routes). Por ejemplo,
  si la clase del controlador es `PostController`, el directorio sería `@app/views/post`; Si fuera `PostCommentController`,
  el directorio sería `@app/views/post-comment`. En caso de que el controlador pertenezca a un módulo,
  el directorio sería `views/ControllerID` bajo el [[yii\base\Module::basePath|directorio del módulo]].
* Para vistas renderizadas por un [widget](structure-widgets.md), deberían ser puestas en un directorio
  tipo `WidgetPath/views` por defecto, donde `WidgetPath` se refiere al directorio que contiene a la clase del widget.
* Para vistas renderizadas por otros objetos, se recomienda seguir una convención similar a la utilizada con los widgets.

Puedes personalizar estos directorios por defecto sobrescribiendo el método [[yii\base\ViewContextInterface::getViewPath()]]
en el controlador o widget necesario.


## Renderizando Vistas <span id="rendering-views"></span>

Puedes renderizar vistas desde [controllers](structure-controllers.md), [widgets](structure-widgets.md), o cualquier otro lugar
llamando a los métodos de renderización de vistas. Estos métodos comparten una firma similar, como se muestra a continuación:

```
/**
 * @param string $view nombre de la vista o ruta al archivo, dependiendo del método de renderización utilizado
 * @param array $params los datos pasados a la vista
 * @return string el resultado de la renderización
 */
methodName($view, $params = [])
```


### Renderizando en Controladores <span id="rendering-in-controllers"></span>

Dentro de los [controladores](structure-controllers.md), puedes llamar al siguiente método del controlador para renderizar una vista:

* [[yii\base\Controller::render()|render()]]: renderiza la [vista nombrada](#named-views) y aplica un [layout](#layouts)
  al resultado de la renderización.
* [[yii\base\Controller::renderPartial()|renderPartial()]]: renderiza la [vista nombrada](#named-views) sin ningún layout aplicado.
* [[yii\web\Controller::renderAjax()|renderAjax()]]: renderiza la [vista nombrada](#named-views) sin layout,
  e inyecta todos los scripts y archivos JS/CSS registrados. Esto sucede usualmente en respuestas a peticiones AJAX.
* [[yii\base\Controller::renderFile()|renderFile()]]: renderiza la vista especificada en términos de la ruta al archivo o
  [alias](concept-aliases.md).
* [[yii\base\Controller::renderContent()|renderContent()]]: renderiza un string fijo, inscrustándolo en
  el [layout](#layouts) actualmente aplicable. Este método está disponible desde la versión 2.0.1.

Por ejemplo:

```php
namespace app\controllers;

use Yii;
use app\models\Post;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

class PostController extends Controller
{
    public function actionView($id)
    {
        $model = Post::findOne($id);
        if ($model === null) {
            throw new NotFoundHttpException;
        }

        // renderiza una vista llamada "view" y le aplica el layout
        return $this->render('view', [
            'model' => $model,
        ]);
    }
}
```


### Renderizando en Widgets <span id="rendering-in-widgets"></span>

Dentro de [widgets](structure-widgets.md), puedes llamar a cualquier de los siguientes métodos de widget para renderizar una vista.

* [[yii\base\Widget::render()|render()]]: renderiza la [vista nombrada](#named-views).
* [[yii\base\Widget::renderFile()|renderFile()]]: renderiza la vista especificada en términos de ruta al archivo
  o [alias](concept-aliases.md).

Por ejemplo:

```php
namespace app\components;

use yii\base\Widget;
use yii\helpers\Html;

class ListWidget extends Widget
{
    public $items = [];

    public function run()
    {
        // renderiza una vista llamada "list"
        return $this->render('list', [
            'items' => $this->items,
        ]);
    }
}
```


### Renderizar en Vistas <span id="rendering-in-views"></span>

Puedes renderizar una vista dentro de otra vista llamando a algunos de los siguientes métodos provistos por el [[yii\base\View|componente view]]:

* [[yii\base\View::render()|render()]]: renderiza la [vista nombrada](#named-views).
* [[yii\web\View::renderAjax()|renderAjax()]]: renderiza la [vista nombrada](#named-views) e inyecta
  todos los archivos y scripts JS/CSS. Esto sucede usualmente en respuestas a las peticiones AJAX.
* [[yii\base\View::renderFile()|renderFile()]]: renderiza la vista especificada en términos de ruta al archivo
  o [alias](concept-aliases.md).

Por ejemplo, el siguiente código en una vista renderiza el template `_overview.php` encontrado en el mismo directorio
de la vista renderizada actualmente. Recuerda que la variable `$this` en una vista se refiere al componente [[yii\base\View|view]]:

```php
<?= $this->render('_overview') ?>
```


### Renderizar en Otros Lugares <span id="rendering-in-other-places"></span>

En cualquier lugar, puedes tener acceso al componente [[yii\base\View|view]] utilizando la expresión
`Yii::$app->view` y entonces llamar a los métodos previamente mencionados para renderizar una vista. Por ejemplo:

```php
// muestra el template "@app/views/site/license.php"
echo \Yii::$app->view->renderFile('@app/views/site/license.php');
```


### Vistas Nombradas <span id="named-views"></span>

Cuando renderizas una vista, puedes especificar el template utilizando tanto el nombre de la vista o la ruta/alias al archivo. En la mayoría de los casos,
utilizarías la primera porque es más concisa y flexible. Las *vistas nombradas* son vistas especificadas mediante un nombre en vez de una ruta al archivo o alias.

Un nombre de vista es resuelto a su correspondiente ruta de archivo siguiendo las siguientes reglas:

* Un nombre de vista puede omitir la extensión del archivo. En estos casos se utilizará `.php` como extensión del archivo. Por ejemplo,
  el nombre de vista `about` corresponde al archivo `about.php`.
* Si el nombre de la vista comienza con doble barra (`//`), la ruta al archivo correspondiente será `@app/views/ViewName`.
  Esto quiere decir que la vista es buscada bajo el [[yii\base\Application::viewPath|ruta de vistas de la aplicación]].
  Por ejemplo, `//site/about` será resuelto como `@app/views/site/about.php`.
* Si el nombre de la vista comienza con una barra simple `/`, la ruta al archivo de la vista utilizará como prefijo el nombre de la vista
  con el [[yii\base\Module::viewPath|view path]] del [módulo](structure-modules.md) utilizado actualmente.
  Si no hubiera módulo activo se utilizará `@app/views/ViewName`. Por ejemplo, `/user/create` será resuelto como
  `@app/modules/user/views/user/create.php` si el módulo activo es `user`. Si no hubiera módulo activo,
  la ruta al archivo será `@app/views/user/create.php`.
* Si la vista es renderizada con un [[yii\base\View::context|context]] y dicho contexto implementa [[yii\base\ViewContextInterface]],
  la ruta al archivo se forma utilizando como prefijo la [[yii\base\ViewContextInterface::getViewPath()|ruta de vistas]] del contexto
  de la vista. Esto principalmente aplica a vistas renderizadas en controladores y widgets. Por ejemplo,
  `about` será resuelto como `@app/views/site/about.php` si el contexto es el controlador `SiteController`.
* Si la vista es renderizada dentro de otra vista, el directorio que contiene la otra vista será prefijado
  al nuevo nombre de la vista para formar la ruta a la vista. Por ejemplo, `item` sera resuelto como `@app/views/post/item`
  si está siendo renderizado desde la vista `@app/views/post/index.php`.

De acuerdo a las reglas mencionadas, al llamar a `$this->render('view')` en el controlador `app\controllers\PostController`
se renderizará el template `@app/views/post/view.php`, mientras que llamando a `$this->render('_overview')` en la vista
renderizará el template `@app/views/post/_overview.php`.


### Acceder a Datos en la Vista <span id="accessing-data-in-views"></span>

Hay dos modos posibles de acceder a los datos en la vista: push (inyectar) y pull (traer).

Al pasar los datos como segundo parámetro en algún método de renderización, estás utilizando el modo push.
Los datos deberían ser representados como un array de pares clave-valor. Cuando la vista está siendo renderizada, la función PHP `extract()`
será llamada sobre este array así se extraen las variables que contiene a la vista actual.
Por ejemplo, el siguiente código de renderización en un controlador inyectará dos variables a la vista `report`:
`$foo = 1` y `$bar = 2`.

```php
echo $this->render('report', [
    'foo' => 1,
    'bar' => 2,
]);
```

El modo pull obtiene los datos del [[yii\base\View|componente view]] u otros objetos accesibles
en las vistas (ej. `Yii::$app`). Utilizando el código anterior como ejemplo, dentro de una vista puedes acceder al objeto del controlador
a través de la expresión `$this->context`. Como resultado, te es posible acceder a cualquier propiedad o método
del controlador en la vista `report`, tal como el ID del controlador como se muestra a continuación:

```php
El ID del controlador es: <?= $this->context->id ?>
```

Para acceder a datos en la vista, normalmente se prefiere el modo push, ya que hace a la vista menos dependiente
de los objetos del contexto. La contra es que tienes que construir el array manualmente cada vez, lo que podría
volverse tedioso y propenso al error si la misma vista es compartida y renderizada desde diferentes lugares.


### Compartir Datos Entre las Vistas <span id="sharing-data-among-views"></span>

El [[yii\base\View|componente view]] provee la propiedad [[yii\base\View::params|params]] para que puedas compartir datos
entre diferentes vistas.

Por ejemplo, en una vista `about`, podrías tener el siguiente código que especifica el segmento actual
del breadcrumbs (migas de pan).

```php
$this->params['breadcrumbs'][] = 'Acerca de Nosotros';
```

Entonces, en el archivo del [layout](#layouts), que es también una vista, puedes mostrar el breadcrumbs utilizando los datos
pasados a través de [[yii\base\View::params|params]]:

```php
<?= yii\widgets\Breadcrumbs::widget([
    'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
]) ?>
```


## Layouts <span id="layouts"></span>

Los layouts son un tipo especial de vista que representan partes comunes de otras múltiples vistas. Por ejemplo, las páginas
de la mayoría de las aplicaciones Web comparten el mismo encabezado y pie de página. Aunque puedes repetirlos en todas y cada una de las vistas,
una mejor forma es hacerlo sólo en el layout e incrustar el resultado de la renderización de la vista
en un lugar apropiado del mismo.


### Crear Layouts <span id="creating-layouts"></span>

Dado que los layouts son también vistas, pueden ser creados de manera similar a las vistas comunes. Por defecto, los layouts
son guardados en el directorio `@app/views/layouts`. Para layouts utilizados dentro de un [módulo](structure-modules.md), deberían ser guardados
en el directorio `views/layouts` bajo el [[yii\base\Module::basePath|directorio del módulo]].
Puedes personalizar el directorio de layouts por defecto configurando la propiedad [[yii\base\Module::layoutPath]]
de la aplicación o módulos.

El siguiente ejemplo muestra cómo debe verse un layout. Ten en cuenta que por motivos ilustrativos, hemos simplificado
bastante el código del layout. En la práctica, probablemente le agregues más contenido, como tags en el `head`, un menú principal, etc.

```php
<?php
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $content string */
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"/>
    <?= Html::csrfMetaTags() ?>
    <title><?= Html::encode($this->title) ?></title>
    <?php $this->head() ?>
</head>
<body>
<?php $this->beginBody() ?>
    <header>Mi Compañía</header>
    <?= $content ?>
    <footer>&copy; 2014 - Mi Compañía</footer>
<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
```

Como puedes ver, el layout genera los tags HTML comunes a todas las páginas. Dentro de la sección `<body>`,
el layout imprime la variable `$content`, que representa el resultado de la renderización del contenido de cada vista
y es incrustado dentro del layout cuando se llama al método [[yii\base\Controller::render()]].

La mayoría de layouts deberían llamar a los siguientes métodos (como fue mostrado recién). Estos métodos principalmente disparan eventos
acerca del proceso de renderizado así los scripts y tags registrados en otros lugares pueden ser propiamente inyectados
en los lugares donde los métodos son llamados.

- [[yii\base\View::beginPage()|beginPage()]]: Este método debería ser llamado bien al principio del layout.
  Esto dispara el evento [[yii\base\View::EVENT_BEGIN_PAGE|EVENT_BEGIN_PAGE]], el cual indica el comienzo de la página.
- [[yii\base\View::endPage()|endPage()]]: Este método debería ser llamado al final del layout.
  Esto dispara el evento [[yii\base\View::EVENT_END_PAGE|EVENT_END_PAGE]], indicando el final de la página.
- [[yii\web\View::head()|head()]]: Este método debería llamarse dentro de la sección `<head>` de una página HTML.
  Esto genera un espacio vacío que será reemplazado con el código del head HTML registrado (ej. link tags, meta tags)
  cuando una página finaliza el renderizado.
- [[yii\base\View::beginBody()|beginBody()]]: Este método debería llamarse al principio de la sección `<body>`.
  Esto dispara el evento [[yii\web\View::EVENT_BEGIN_BODY|EVENT_BEGIN_BODY]] y genera un espacio vacío que será reemplazado
  con el código HTML registrado (ej. JavaScript) que apunta al principio del body.
- [[yii\base\View::endBody()|endBody()]]: Este método debería llamarse al final de la sección `<body>`.
  Esto dispara el evento [[yii\web\View::EVENT_END_BODY|EVENT_END_BODY]], que genera un espacio vacío a ser reemplazado
  por el código HTML registrado (ej. JavaScript) que apunta al final del body.


### Acceder a Datos en Layouts <span id="accessing-data-in-layouts"></span>

Dentro de un layout, tienes acceso a dos variables predefinidas: `$this` y `$content`. La primera se refiere al componente [[yii\base\View|view]],
como en cualquier vista, mientras que la última contiene el resultado de la renderización del contenido de la vista que está siendo renderizada
al llamar al método [[yii\base\Controller::render()|render()]] en los controladores.

Si quieres acceder a otros datos en los layouts, debes utilizar el modo pull que fue descrito en la sub-sección [Accediendo a Datos en la Vista](#accessing-data-in-views).
Si quieres pasar datos desde al contenido de la vista a un layout, puedes utilizar el método descrito en la
sub-sección [Compartiendo Datos Entre las Vistas](#sharing-data-among-views).


### Utilizar Layouts <span id="using-layouts"></span>

Como se describe en la sub-sección [Renderizando en Controllers](#rendering-in-controllers), cuando renderizas una vista
llamando al método [[yii\base\Controller::render()|render()]] en un controlador, al resultado de dicha renderización le será aplicado un layout.
Por defecto, el layout `@app/views/layouts/main.php` será el utilizado. 

Puedes utilizar un layout diferente configurando la propiedad [[yii\base\Application::layout]] o [[yii\base\Controller::layout]]. El primero
se refiere al layout utilizado por todos los controladores, mientras que el último sobrescribe el layout en controladores individuales.
Por ejemplo, el siguiente código hace que el controlador `post` utilice `@app/views/layouts/post.php` como layout al renderizar sus vistas.
Otros controladores, asumiendo que su propiedad `layout` no fue modificada,
utilizarán `@app/views/layouts/main.php` como layout.
 
```php
namespace app\controllers;

use yii\web\Controller;

class PostController extends Controller
{
    public $layout = 'post';
    
    // ...
}
```

Para controladores que pertencen a un módulo, puedes también configurar la propiedad [[yii\base\Module::layout|layout]] y así utilizar un layout
en particular para esos controladores. 

Dado que la propiedad `layout` puede ser configurada en diferentes niveles (controladores, módulos, aplicación), detrás de escena
Yii realiza dos pasos para determinar cuál es el archivo de layout siendo utilizado para un controlador en particular.

En el primer paso, determina el valor del layout y el módulo de contexto:

- Si la propiedad [[yii\base\Controller::layout]] no es `null`, la utiliza como valor del layout y el [[yii\base\Controller::module|módulo]]
  del controlador como el módulo de contexto.
- Si [[yii\base\Controller::layout|layout]] es `null`, busca a través de todos los módulos ancestros del controlador
  y encuentra el primer módulo cuya propiedad [[yii\base\Module::layout|layout]] no es `null`.
  Utiliza ese módulo y su valor de [[yii\base\Module::layout|layout]] como módulo de contexto y como layout seleccionado.
  Si tal módulo no puede ser encontrado, significa que no se aplicará ningún layout.
  
En el segundo paso, se determina el archivo de layout actual de acuerdo al valor de layout y el módulo de contexto determinado en el primer paso.
El valor de layout puede ser:

- un alias de ruta (ej. `@app/views/layouts/main`).
- una ruta absoluta (ej. `/main`): el valor del layout comienza con una barra. El archivo de layout actual será buscado
  bajo el [[yii\base\Application::layoutPath|layout path]] de la aplicación,
  que es por defecto `@app/views/layouts`.
- una ruta relativa (ej. `main`): El archivo de layout actual será buscado bajo el [[yii\base\Module::layoutPath|layout path]]
  del módulo de contexto, que es por defecto el directorio `views/layouts`
  bajo el [[yii\base\Module::basePath|directorio del módulo]].
- el valor booleano `false`: no se aplicará ningún layout.

Si el valor de layout no contiene una extensión de tipo de archivo, utilizará por defecto `.php`.


### Layouts Anidados <span id="nested-layouts"></span>

A veces podrías querer anidar un layout dentro de otro. Por ejemplo, en diferentes secciones de un sitio Web,
podrías querer utilizar layouts diferentes, mientras que todos esos layouts comparten el mismo layout básico que genera
la estructura general de la página en HTML5. Esto es posible llamando a los métodos
[[yii\base\View::beginContent()|beginContent()]] y [[yii\base\View::endContent()|endContent()]] en los layouts hijos como se muestra a continuación:

```php
<?php $this->beginContent('@app/views/layouts/base.php'); ?>

...contenido del layout hijo aquí...

<?php $this->endContent(); ?>
```

Como se acaba de mostrar, el contenido del layout hijo debe ser encerrado dentro de [[yii\base\View::beginContent()|beginContent()]]
y [[yii\base\View::endContent()|endContent()]]. El parámetro pasado a [[yii\base\View::beginContent()|beginContent()]]
especifica cuál es el módulo padre. Este puede ser tanto un archivo layout como un alias.

Utilizando la forma recién mencionada, puedes anidar layouts en más de un nivel.


### Utilizar Blocks <span id="using-blocks"></span>

Los bloques te permiten especificar el contenido de la vista en un lugar y mostrarlo en otro. Estos son a menudo utilizados junto a
los layouts. Por ejemplo, puedes definir un bloque un una vista de contenido y mostrarla en el layout.

Para definir un bloque, llamas a [[yii\base\View::beginBlock()|beginBlock()]] y [[yii\base\View::endBlock()|endBlock()]].
El bloque puede ser accedido vía `$view->blocks[$blockID]`, donde `$blockID` se refiere al ID único que le asignas
al bloque cuando lo defines.

El siguiente ejemplo muestra cómo utilizar bloques para personalizar partes especificas del layout in una vista.

Primero, en una vista, define uno o varios bloques:

```php
...

<?php $this->beginBlock('block1'); ?>

...contenido de block1...

<?php $this->endBlock(); ?>

...

<?php $this->beginBlock('block3'); ?>

...contenido de block3...

<?php $this->endBlock(); ?>
```

Entonces, en la vista del layout, renderiza los bloques si están disponibles, o muestra un contenido por defecto si el bloque
no está definido.

```php
...
<?php if (isset($this->blocks['block1'])): ?>
    <?= $this->blocks['block1'] ?>
<?php else: ?>
    ... contenido por defecto de block1 ...
<?php endif; ?>

...

<?php if (isset($this->blocks['block2'])): ?>
    <?= $this->blocks['block2'] ?>
<?php else: ?>
    ... contenido por defecto de block2 ...
<?php endif; ?>

...

<?php if (isset($this->blocks['block3'])): ?>
    <?= $this->blocks['block3'] ?>
<?php else: ?>
    ... contenido por defecto de block3 ...
<?php endif; ?>
...
```


## Utilizar Componentes de Vista <span id="using-view-components"></span>

Los [[yii\base\View|componentes de vista]] proveen características relacionadas a las vistas. Aunque puedes obtener componentes de vista
creando instancias individuales de [[yii\base\View]] o sus clases hijas, en la mayoría de los casos utilizarías el componente `view` del a aplicación.
Puedes configurar este componente en la [configuración de la aplicación](structure-applications.md#application-configurations)
como a continuación:

```php
[
    // ...
    'components' => [
        'view' => [
            'class' => 'app\components\View',
        ],
        // ...
    ],
]
```

Los componentes de vista proveen las siguientes características útiles, cada una descrita en mayor detalle en su propia sección:

* [temas](output-theming.md): te permite desarrollar y cambiar el tema (theme) de tu sitio Web.
* [caché de fragmentos](caching-fragment.md): te permite guardar en cache un fragmento de una página Web.
* [manejo de scripts del cliente](output-client-scripts.md): soporte para registro y renderización de CSS y JavaScript.
* [manejo de asset bundle](structure-assets.md): soporte de registro y renderización de [asset bundles](structure-assets.md).
* [motores de template alternativos](tutorial-template-engines.md): te permite utilizar otros motores de templates, como
  [Twig](http://twig.sensiolabs.org/) o [Smarty](http://www.smarty.net/).

Puedes también utilizar frecuentemente el siguiente menor pero útil grupo de características al desarrollar páginas Web.


### Definiendo Títulos de Página <span id="setting-page-titles"></span>

Toda página Web debería tener un título. Normalmente el tag de título es generado en [layout](#layouts). De todos modos, en la práctica
el título es determinado en el contenido de las vistas más que en layouts. Para resolver este problema, [[yii\web\View]] provee
la propiedad [[yii\web\View::title|title]] para que puedas pasar información del título desde el contenido de la vista a los layouts.

Para utilizar esta característica, en cada contenido de la vista, puedes definir el título de la siguiente manera:

```php
<?php
$this->title = 'Título de mi página';
?>
```

Entonces en el layout, asegúrate de tener el siguiente código en la sección `<head>` de la página:

```php
<title><?= Html::encode($this->title) ?></title>
```


### Registrar Meta Tags <span id="registering-meta-tags"></span>

Las páginas Web usualmente necesitan generar varios meta tags necesarios para diferentes grupos. Cómo los títulos de página, los meta tags
aparecen en la sección `<head>` y son usualmente generado en los layouts.

Si quieres especificar cuáles meta tags generar en las vistas, puedes llamar a [[yii\web\View::registerMetaTag()]]
dentro de una de ellas, como se muestra a continuación:

```php
<?php
$this->registerMetaTag(['name' => 'keywords', 'content' => 'yii, framework, php']);
?>
```

El código anterior registrará el meta tag "keywords" a través del componente view. El meta tag registrado
no se renderiza hasta que finaliza el renderizado del layout. Para entonces, el siguiente código HTML será insertado
en el lugar donde llamas a [[yii\web\View::head()]] en el layout, generando el siguiente HTML:

```php
<meta name="keywords" content="yii, framework, php">
```

Ten en cuenta que si llamas a [[yii\web\View::registerMetaTag()]] varias veces, esto registrará varios meta tags,
sin tener en cuenta si los meta tags son los mismo o no.

Para asegurarte de que sólo haya una instancia de cierto tipo de meta tag, puedes especificar una clave al llamar al método.
Por ejemplo, el siguiente código registra dos meta tags "description", aunque sólo el segundo será renderizado.

```php
$this->registerMetaTag(['name' => 'description', 'content' => 'Este es mi sitio Web cool hecho con Yii!'], 'description');
$this->registerMetaTag(['name' => 'description', 'content' => 'Este sitio Web es sobre mapaches graciosos.'], 'description');
```


### Registrar Link Tags <span id="registering-link-tags"></span>

Tal como los [meta tags](#adding-meta-tags), los link tags son útiles en muchos casos, como personalizar el ícono (favicon) del sitio,
apuntar a una fuente de RSS o delegar OpenID a otro servidor. Puedes trabajar con link tags, al igual que con meta tags,
utilizando [[yii\web\View::registerLinkTag()]]. Por ejemplo, en el contenido de una vista, puedes registrar un link tag como se muestra a continuación:

```php
$this->registerLinkTag([
    'title' => 'Noticias en Vivo de Yii',
    'rel' => 'alternate',
    'type' => 'application/rss+xml',
    'href' => 'http://www.yiiframework.com/rss.xml/',
]);
```

El resultado del código es el siguiente:

```html
<link title="Noticias en Vivo de Yii" rel="alternate" type="application/rss+xml" href="http://www.yiiframework.com/rss.xml/">
```

Al igual que con [[yii\web\View::registerMetaTag()|registerMetaTags()]], puedes especificar una clave al llamar
a [[yii\web\View::registerLinkTag()|registerLinkTag()]] para evitar registrar link tags repetidos.


## Eventos de Vistas <span id="view-events"></span>

Los [[yii\base\View|componentes de vistas]] disparan varios eventos durante el proceso de renderizado de la vista. Puedes responder
a estos eventos para inyectar contenido a la vista o procesar el resultado de la renderización antes de que sea enviada al usuario final.

- [[yii\base\View::EVENT_BEFORE_RENDER|EVENT_BEFORE_RENDER]]: disparado al principio del renderizado de un archivo
  en un controlador. Los manejadores de este evento pueden definir [[yii\base\ViewEvent::isValid]] como `false` para cancelar el proceso de renderizado.
- [[yii\base\View::EVENT_AFTER_RENDER|EVENT_AFTER_RENDER]]: disparado luego de renderizar un archivo con la llamada de [[yii\base\View::afterRender()]].
  Los manejadores de este evento pueden obtener el resultado del renderizado a través de [[yii\base\ViewEvent::output]] y modificar
  esta propiedad para cambiar dicho resultado.
- [[yii\base\View::EVENT_BEGIN_PAGE|EVENT_BEGIN_PAGE]]: disparado por la llamada a [[yii\base\View::beginPage()]] en layouts.
- [[yii\base\View::EVENT_END_PAGE|EVENT_END_PAGE]]: disparado por la llamada a [[yii\base\View::endPage()]] en layouts.
- [[yii\web\View::EVENT_BEGIN_BODY|EVENT_BEGIN_BODY]]: disparado por la llamada a [[yii\web\View::beginBody()]] en layouts.
- [[yii\web\View::EVENT_END_BODY|EVENT_END_BODY]]: disparado por la llamada a [[yii\web\View::endBody()]] en layouts.

Por ejemplo, el siguiente código inyecta la fecha actual al final del body de la página:

```php
\Yii::$app->view->on(View::EVENT_END_BODY, function () {
    echo date('Y-m-d');
});
```


## Renderizar Páginas Estáticas <span id="rendering-static-pages"></span>

Con páginas estáticas nos referimos a esas páginas cuyo contenido es mayormente estático y sin necesidad de acceso
a datos dinámicos enviados desde los controladores.

Puedes generar páginas estáticas utilizando un código como el que sigue dentro de un controlador:

```php
public function actionAbout()
{
    return $this->render('about');
}
```

Si un sitio Web contiene muchas páginas estáticas, resultaría tedioso repetir el mismo código en muchos lados.
Para resolver este problema, puedes introducir una [acción independiente](structure-controllers.md#standalone-actions)
llamada [[yii\web\ViewAction]] en el controlador. Por ejemplo,

```php
namespace app\controllers;

use yii\web\Controller;

class SiteController extends Controller
{
    public function actions()
    {
        return [
            'page' => [
                'class' => 'yii\web\ViewAction',
            ],
        ];
    }
}
```

Ahora, si creamos una vista llamada `about` bajo el directorio `@app/views/site/pages`, serás capáz de mostrarla
en la siguiente URL:

```
http://localhost/index.php?r=site%2Fpage&view=about
```

El parámetro `GET` `view` le comunica a [[yii\web\ViewAction]] cuál es la vista solicitada. La acción entonces buscará
esta vista dentro de `@app/views/site/pages`. Puedes configurar la propiedad [[yii\web\ViewAction::viewPrefix]]
para cambiar el directorio en el que se buscarán dichas páginas.


## Buenas Prácticas <span id="best-practices"></span>

Las vistas son responsables de la presentación de modelos en el formato que el usuario final desea. En general, las vistas

* deberían contener principalmente sólo código de presentación, como HTML, y PHP simple para recorrer, dar formato y renderizar datos.
* no deberían contener código que realiza consultas a la base de datos. Ese tipo de código debe ir en los modelos.
* deberían evitar el acceso directo a datos del `request`, como `$_GET` y/o `$_POST`. Esto es una responsabilidad de los controladores.
  Si se necesitan datos del `request`, deben ser inyectados a la vista desde el controlador.
* pueden leer propiedades del modelo, pero no debería modificarlas.

Para hacer las vistas más manejables, evita crear vistas que son demasiado complejas o que contengan código redundante.
Puedes utilizar estas técnicas para alcanzar dicha meta:

* utiliza [layouts](#layouts) para representar secciones comunes (ej. encabezado y footer de la página).
* divide una vista compleja en varias más simples. Las vistas pequeñas pueden ser renderizadas y unidas una mayor
  utilizando los métodos de renderización antes descritos.
* crea y utiliza [widgets](structure-widgets.md) como bloques de construcción de la vista.
* crea y utilizar helpers para transformar y dar formato a los datos en la vista.

