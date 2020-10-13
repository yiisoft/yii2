Controladores
=============

Los controladores son parte del patrón o arquitectura [MVC](http://es.wikipedia.org/wiki/Modelo%E2%80%93vista%E2%80%93controlador).
Son objetos que extienden de [[yii\base\Controller]] y se encargan de procesar los `requests` (consultas)
generando `responses` (respuestas). Particularmente, después de tomar el control desde las [aplicaciones](structure-applications.md),
los controladores analizarán los datos que entran en el `request`, los pasan a los [modelos](structure-models.md), inyectan los
modelos resultantes a las [vistas](structure-views.md), y finalmente generan los `responses` (respuestas) de salida.


## Acciones <span id="actions"></span>

Los Controladores están compuestos por *acciones* que son las unidades más básicas a las que los usuarios pueden
dirigirse y solicitar ejecución. Un controlador puede tener una o múltiples acciones.

El siguiente ejemplo muestra un controlador `post` con dos acciones: `view` y `create`:

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

        return $this->render('view', [
            'model' => $model,
        ]);
    }

    public function actionCreate()
    {
        $model = new Post;

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('create', [
                'model' => $model,
            ]);
        }
    }
}
```

En la acción `view` (definida en el método `actionView()`), el código primero carga el [modelo](structure-models.md)
de acuerdo el ID del modelo solicitado; Si el modelo es cargado satisfactoriamente, lo mostrará usando una [vista](structure-views.md)
llamada `view`. Si no, arrojará una excepción.

En la acción `create` (definida por el método `actionCreate()`), el código es similar. Primero intenta poblar 
el [modelo](structure-models.md) usando datos del `request` y guardarlo. Si ambas cosas suceden correctamente, se redireccionará
el navegador a la acción `view` con el ID del modelo recientemente creado. De otro modo mostrará
la vista `create` a través de la cual el usuario puede completar los campos necesarios.


## Routes <span id="routes"></span>

Los usuarios ejecutan las acciones a través de las llamadas *routes* (rutas). una ruta es una cadena que consiste en las siguientes partes:

* un ID de módulo: este existe solamente si el controlador pertenece a un [módulo](structure-modules.md) que no es de la aplicación;
* un ID de controlador: una cadena que identifica exclusivamente al controlador entre todos los controladores dentro de la misma aplicación
  (o el mismo módulo si el controlador pertenece a uno);
* un ID de acción: una cadena que identifica exclusivamente a la acción entre todas las acciones del mismo controlador.

Las rutas pueden usar el siguiente formato:

```
ControllerID/ActionID
```

o el siguiente formato si el controlador pertenece a un módulo:

```php
ModuleID/ControllerID/ActionID
```

Entonces si un usuario solicita la URL `http://hostname/index.php?r=site/index`, la acción `index` del controlador `site`
será ejecutado. Para más detalles acerca de cómo las son resueltas en acciones, por favor consulta
la sección [Routing](runtime-routing.md).


## Creando Controladores <span id="creating-controllers"></span>

En [[yii\web\Application|aplicaciones Web]], los controladores deben extender de [[yii\web\Controller]] o cualquier
clase hija. De forma similar los controladores de [[yii\console\Application|aplicaciones de consola]], deben extender
de [[yii\console\Controller]] o cualquier clase hija de esta. El siguiente código define un controlador `site`:

```php
namespace app\controllers;

use yii\web\Controller;

class SiteController extends Controller
{
}
```


### IDs de Controladores <span id="controller-ids"></span>

Normalmente, un controlador está diseñado para manejar los `requests` de acuerdo a un tipo de recurso.
Por esta razón, los IDs de controladores son a menudo sustantivos de los tipos de recurso que están manejando.
Por ejemplo, podrías utilizar `article` como el ID de un controlador que maneja datos de artículos.

Por defecto, los IDs de controladores deberían contener sólo estos caracteres: letras del Inglés en minúscula, dígitos,
guiones bajos y medios, y barras. Por ejemplo, `article`, `post-comment`, `admin/post-comment` son todos
IDs de controladores válidos, mientras que `article?`, `PostComment`, `admin\post` no lo son.

Los guiones en un ID de controlador son utilizados para separar palabras, mientras que las barras diagonales lo son para
organizar los controladores en sub-directorios.


### Nombres de Clases de Controladores <span id="controller-class-naming"></span>

Los nombres de clases de controladores pueden ser derivados de los IDs de acuerdo a las siguientes reglas:

* Transforma la primera letra de cada palabra separada por guiones en mayúscula. Nota que si el ID del controlador
  contiene barras, esta regla sólo aplica a la porción después de la última barra dentro del ID.
* Elimina guiones y reemplaza cualquier barra diagonal por barras invertidas.
* Agrega el sufijo `Controller`.
* Agrega al principio el [[yii\base\Application::controllerNamespace|controller namespace]].

A continuación mostramos algunos ejemplos, asumiendo que el [[yii\base\Application::controllerNamespace|controller namespace]]
toma el valor por defecto: `app\controllers`:

* `article` deriva en `app\controllers\ArticleController`;
* `post-comment` deriva en `app\controllers\PostCommentController`;
* `admin/post-comment` deriva en `app\controllers\admin\PostCommentController`.

Las clases de controladores deben ser [autocargables](concept-autoloading.md). Por esta razón, en los ejemplos anteriores,
la clase del controlador `article` debe ser guardada en un archivo cuyo alias [alias](concept-aliases.md)
es `@app/controllers/ArticleController.php`; mientras que el controlador `admin/post-comment` debería estar
en `@app/controllers/admin/PostCommentController.php`.

> Info: En el último ejemplo, `admin/post-comment`, demuestra cómo puedes poner un controlador bajo un sub-directorio
  del [[yii\base\Application::controllerNamespace|controller namespace]]. Esto es útil cuando quieres organizar
  tus controladores en varias categorías pero sin utilizar [módulos](structure-modules.md).


### Controller Map <span id="controller-map"></span>

Puedes configurar [[yii\base\Application::controllerMap|controller map]] (mapeo de controladores) para superar las restricciones
de los IDs de controladores y sus nombres de clase descritos arriba. Esto es principalmente útil cuando estás utilizando un
controlador de terceros del cual no tienes control alguno sobre sus nombres de clase.

Puedes configurar [[yii\base\Application::controllerMap|controller map]] en la
[configuración de la aplicación](structure-applications.md#application-configurations) de la siguiente manera:

```php
[
    'controllerMap' => [
        [
            // declara el controlador "account" usando un nombre de clase
            'account' => 'app\controllers\UserController',

            // declara el controlador "article" utilizando un array de configuración
            'article' => [
                'class' => 'app\controllers\PostController',
                'enableCsrfValidation' => false,
            ],
        ],
    ],
]
```


### Controller por Defecto <span id="default-controller"></span>

Cada aplicación tiene un controlador por defecto especificado a través de la propiedad [[yii\base\Application::defaultRoute]].
Cuando un `request` no especifica una [ruta](#ids-routes), se utilizará la ruta especificada en esta propiedad.
Para [[yii\web\Application|aplicaciones Web]], el valor es `'site'`, mientras que para [[yii\console\Application|aplicaciones de consola]]
es `help`. Por lo tanto, si la URL es `http://hostname/index.php`, significa que el `request` será manejado por el controlador `site`.

Puedes cambiar el controlador por defecto con la siguiente [configuración de la aplicación](structure-applications.md#application-configurations):

```php
[
    'defaultRoute' => 'main',
]
```


## Creando Acciones <span id="creating-actions"></span>

Crear acciones puede ser tan simple como definir un llamado *método de acción* en una clase controlador. Un método de acción es
un método *public* cuyo nombre comienza con la palabra `action`. El valor de retorno de uno de estos métodos representa
los datos de respuesta (response) a ser enviado a los usuarios. El siguiente código define dos acciones: `index` y `hello-world`:

```php
namespace app\controllers;

use yii\web\Controller;

class SiteController extends Controller
{
    public function actionIndex()
    {
        return $this->render('index');
    }

    public function actionHelloWorld()
    {
        return 'Hola Mundo!';
    }
}
```


### IDs de Acciones <span id="action-ids"></span>

Una acción está a menudo diseñada para realizar una manipulación particular de un recurso. Por esta razón,
los IDs de acciones son usualmente verbos, como `view` (ver), `update` (actualizar), etc.

Por defecto, los IDs de acciones deberían contener estos caracteres solamente: letras en Inglés en minúsculas, dígitos,
guiones bajos y barras. Los guiones en un ID de acción son utilizados para separar palabras. Por ejemplo,
`view`, `update2`, `comment-post` son todos IDs válidos, mientras que `view?` y `Update` no lo son.

Puedes crear acciones de dos maneras: acciones en línea (inline) o acciones independientes (standalone). Una acción en línea
es definida como un método en la clase del controlador, mientras que una acción independiente es una clase que extiende
[[yii\base\Action]] o sus clases hijas. Las acciones en línea son más fáciles de crear y por lo tanto preferidas
si no tienes intenciones de volver a utilizarlas. Las acciones independientes, por otro lado, son principalmente
creadas para ser reutilizadas en otros controladores o para ser redistribuidas como [extensiones](structure-extensions.md).


### Acciones en Línea <span id="inline-actions"></span>

Como acciones en línea nos referimos a acciones que son definidas en términos de métodos como acabamos de describir.

Los nombre de métodos de acciones derivan de los IDs de acuerdo al siguiente criterio:

* Transforma la primera letra de cada palabra del ID de la acción a mayúscula;
* Elimina guiones;
* Prefija la palabra `action`.

Por ejemplo, `index` se vuelve `actionIndex`, y `hello-world` se vuelve `actionHelloWorld`.

> Note: Los nombres de los métodos de acción son *case-sensitive* (distinguen entre minúsculas y mayúsculas). Si tienes un
  método llamado `ActionIndex`, no será considerado como un método de acción, y como resultado, solicitar la acción `index`
  resultará en una excepción. También ten en cuenta que los métodos de acción deben ser `public`. Un método `private` o `protected`
  NO define un método de acción.


Las acciones en línea son las más comúnmente definidas ya que requieren muy poco esfuerzo de creación. De todos modos,
si planeas reutilizar la misma acción en diferentes lugares, o quieres redistribuir una acción,
deberías considerar definirla como un *acción independiente*.


### Acciones Independientes <span id="standalone-actions"></span>

Las acciones independientes son acciones definidas en términos de clases de acción que extienden de [[yii\base\Action]] o cualquiera de sus clases hijas.
Por ejemplo, en Yii se encuentran las clases [[yii\web\ViewAction]] y [[yii\web\ErrorAction]], de las cuales ambas son acciones independientes.

Para utilizar una acción independiente, debes declararla en el *action map* (mapeo de acciones) sobrescribiendo el método
[[yii\base\Controller::actions()]] en tu controlador de la siguiente manera:

```php
public function actions()
{
    return [
        // declara la acción "error" utilizando un nombre de clase
        'error' => 'yii\web\ErrorAction',

        // declara la acción "view" utilizando un array de configuración
        'view' => [
            'class' => 'yii\web\ViewAction',
            'viewPrefix' => '',
        ],
    ];
}
```

Como puedes ver, el método `actions()` debe devolver un array cuyas claves son los IDs de acciones y sus valores los nombres
de clases de acciones o [configuraciones](concept-configurations.md). Al contrario de acciones en línea, los IDs de acciones independientes
pueden contener caracteres arbitrarios, mientras sean declarados en el método `actions()`.


Para crear una acción independiente, debes extender de [[yii\base\Action]] o una clase hija, e implementar un
método `public` llamado `run()`. El rol del método `run()` es similar al de un método de acción. Por ejemplo:

```php
<?php
namespace app\components;

use yii\base\Action;

class HelloWorldAction extends Action
{
    public function run()
    {
        return "Hola Mundo!";
    }
}
```


### Resultados de Acción <span id="action-results"></span>

El valor de retorno de una método de acción o del método `run()` de una acción independiente son significativos. Este se refiere
al resultado de la acción correspondiente.

El valor devuelto puede ser un objeto [response](runtime-responses.md) que será enviado como respuesta a
los usuarios.

* Para [[yii\web\Application|aplicaciones Web]], el valor de retorno pueden ser también datos arbitrarios que serán
  asignados a [[yii\web\Response::data]] y más adelante convertidos a una cadena representando el cuerpo de la respuesta.
* Para [[yii\console\Application|aplicaciones de consola]], el valor de retorno puede ser también un entero representando
  el [[yii\console\Response::exitStatus|status de salida]] de la ejecución del comando.

En los ejemplos mostrados arriba, los resultados de las acciones son todas cadenas que serán tratadas como el cuerpo de la respuesta
a ser enviado a los usuarios. El siguiente ejemplo demuestra cómo una acción puede redirigir el navegador del usuario a una nueva URL
devolviendo un objeto `response` (debido a que el método [[yii\web\Controller::redirect()|redirect()]] devuelve
un objeto `response`):

```php
public function actionForward()
{
    // redirige el navegador del usuario a http://example.com
    return $this->redirect('http://example.com');
}
```


### Parámetros de Acción <span id="action-parameters"></span>

Los métodos de acción para acciones en línea y el método `run()` de acciones independientes pueden tomar parámetros,
llamados *parámetros de acción*. Sus valores son obtenidos del `request`. Para [[yii\web\Application|aplicaciones Web]],
el valor de cada parámetro de acción es tomado desde `$_GET` usando el nombre del parámetro como su clave;
para [[yii\console\Application|aplicaciones de consola]], estos corresponden a los argumentos de la línea de comandos.

En el siguiente ejemplo, la acción `view` (una acción en línea) declara dos parámetros: `$id` y `$version`.

```php
namespace app\controllers;

use yii\web\Controller;

class PostController extends Controller
{
    public function actionView($id, $version = null)
    {
        // ...
    }
}
```

Los parámetros de acción serán poblados como se muestra a continuación para distintos `requests`:

* `http://hostname/index.php?r=post/view&id=123`: el parámetro `$id` tomará el valor
  `'123'`,  mientras que `$version` queda como `null` debido a que no hay un parámetro `version` en la URL.
* `http://hostname/index.php?r=post/view&id=123&version=2`: los parámetros `$id` y `$version` serán llenados con
  `'123'` y `'2'`, respectivamente.
* `http://hostname/index.php?r=post/view`: se lanzará una excepción [[yii\web\BadRequestHttpException]]
  dado que el parámetro `$id` es requerido pero no es provisto en el `request`.
* `http://hostname/index.php?r=post/view&id[]=123`: una excepción [[yii\web\BadRequestHttpException]] será lanzada
  porque el parámetro `$id` está recibiendo un valor inesperado, el array `['123']`.

Si quieres que un parámetro de acción acepte un array como valor, deberías utilizar el `type-hinting` (especificación de tipo) `array`,
como a continuación:

```php
public function actionView(array $id, $version = null)
{
    // ...
}
```

Ahora si el `request` es `http://hostname/index.php?r=post/view&id[]=123`, el parámetro `$id` tomará el valor
de `['123']`. Si el `request` es `http://hostname/index.php?r=post/view&id=123`, el parámetro `$id` recibirá aún
el mismo array como valor ya que el valor escalar `'123'` será convertido automáticamente en array.

Los ejemplos de arriba muestran principalmente como funcionan los parámetros de acción de una aplicación Web. Para aplicaciones de consola,
por favor consulta la sección [Comandos de Consola](tutorial-console.md) para más detalles.


### Acción por Defecto <span id="default-action"></span>

Cada controlador tiene una acción por defecto especificada a través de la propiedad [[yii\base\Controller::defaultAction]].
Cuando una [ruta](#ids-routes) contiene sólo el ID del controlador, implica que se está solicitando la acción por defecto
del controlador especificado.

Por defecto, la acción por defecto (valga la redundancia) definida es `index`. Si quieres cambiar dicho valor, simplemente sobrescribe
esta propiedad en la clase del controlador, como se muestra a continuación:

```php
namespace app\controllers;

use yii\web\Controller;

class SiteController extends Controller
{
    public $defaultAction = 'home';

    public function actionHome()
    {
        return $this->render('home');
    }
}
```


## Ciclo de Vida del Controlador <span id="controller-lifecycle"></span>

Cuando se procesa un `request`, la [aplicación](structure-applications.md) creará un controlador
basado en la [ruta](#routes) solicitada. El controlador entonces irá a través del siguiente ciclo de vida
para completar el `request`:

1. El método [[yii\base\Controller::init()]] es llamado después de que el controlador es creado y configurado.
2. El controlador crea un objecto `action` basado en el ID de acción solicitado:
   * Si el ID de la acción no es especificado, el [[yii\base\Controller::defaultAction|ID de la acción por defecto]] será utilizado.
   * Si el ID de la acción es encontrado en el [[yii\base\Controller::actions()|mapeo de acciones]], una acción independiente
     será creada;
   * Si el ID de la acción es coincide con un método de acción, una acción en línea será creada;
   * De otra manera, se lanzará una excepción [[yii\base\InvalidRouteException]].
3. El controlador llama secuencialmente al método `beforeAction()` de la aplicación, al del módulo (si el controlador
   pertenece a uno) y al del controlador.
   * Si alguna de las llamadas devuelve `false`, el resto de los llamados subsiguientes a `beforeAction()` serán saltados y
     la ejecución de la acción será cancelada.
   * Por defecto, cada llamada al método `beforeAction()` lanzará un evento `beforeAction` al cual le puedes conectar un manejador.
4. El controlador ejecuta la acción:
   * Los parámetros de la acción serán analizados y poblados con los datos del `request`;
5. El controlador llama secuencialmente al método `afterAction()` del controlador, del módulo (si el controlador
   pertenece a uno) y de la aplicación.
   * Por defecto, cada llamada al método `afterAction()` lanzará un evento `afterAction` al cual le puedes conectar un manejador.
6. La aplicación tomará el resultado de la acción y lo asignará al [response](runtime-responses.md).


## Buenas Prácticas <span id="best-practices"></span>

En una aplicación bien diseñada, los controladores son a menudo muy pequeños con cada acción conteniendo unas pocas líneas de código.
Si tu controlador se torna muy complejo, es usualmente un indicador de que deberías realizar una refactorización y mover algo de
código a otras clases.

En resumen, los controladores

* pueden acceder a los datos del [request](runtime-requests.md);
* puede llamar a métodos del [modelo](structure-models.md) y otros componentes con data del `request`;
* pueden utilizar [vistas](structure-views.md) para componer `responses`;
* NO debe procesar datos del `request - esto debe ser realizado en los [modelos](structure-models.md);
* deberían evitar insertar HTML o cualquier código de presentación - para esto están las [vistas](structure-views.md).
