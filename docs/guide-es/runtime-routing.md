Routing
=======
Cuando se llama al método [[yii\web\Application::run()|run()]] a través del [script de entrada](structure-entry-scripts.md), lo primero que hace es resolver la petición entrante e instanciar una [accion de controlador](structure-controllers.md) apropiada para gestionar la petición. A este proceso se le llama *routing*.

## Resolver una Ruta <a name="resolving-route"></a>

El primer paso el primer paso de routing es convertir la petición entrante una ruta que, tal y como se describe en la sección [Controladores](structure-controllers.md#routes), se usa para dirigirse a una acción de controlador. El método invoca al [gestor de URLs](runtime-url-handling.md) para hacer que la conversión de la petición actual funcione.

Por defecto, si la petición entrante contiene un parámetro 'GET' llamado 'r', su valor será considerado como la ruta. Sin embargo, si la [[yii\web\UrlManager::enablePrettyUrl|pretty URL feature]] esta habilitada, se tendrá que hacer más trabajo para determinar la ruta solicitada. Para conocer más detalles, por favor refiérase a la sección [generación y conversión de URLs](runtime-url-handling.md).

En el caso que una ruta no pueda ser determinada, el componente 'petición' lanzará una [[yii\web\NotFoundHttpException]].

### Ruta por defecto <a name="default-route"></a>

Si una petición entrante no especifica una ruta, cosa que sucede habitualmente en las paginas de inicio, se usará la ruta especificada por [[yii\web\Application::defaultRoute]]. El valor por defecto de esta propiedad es 'site/index', que hace referencia a la acción 'index' del controlador 'site'. Se puede personalizar esta propiedad en la configuración de aplicación como en el siguiente ejemplo:

```php
return [
    // ...
    'defaultRoute' => 'main/index',
];
```

### La ruta `catchAll` <a name="catchall-route"></a>

A veces, queremos poner una aplicación Web en modo de mantenimiento temporalmente y mostrar la misma pagina de información para todas las peticiones. Hay varias maneras de llevar esta operación a cabo. Pero una de las maneras más simples es configurando la propiedad [[yii\web\Application::catchAll]] como en la siguiente configuración de aplicación:

```php
return [
    // ...
    'catchAll' => ['site/offline'],
];
```

La propiedad 'catchAll' debe componerse de un array cuyo primer elemento especifique la ruta, y el resto de elementos(pares de nombre-valor) especifiquen los parámetros que van ligados a la acción. 

Cuando se especifica la propiedad 'catchAll', esta reemplazará cualquier otra ruta resuelta a partir de la petición entrante. Con la anterior configuración, la misma acción 'site/offline' se usará para gestionar todas las peticiones entrantes.

## Crear una Acción <a name="creating-action"></a>

Una vez que se determina la ruta solicitada, el siguiente paso es crear el objecto de la acción correspondiente a la ruta.

La ruta se desglosa en múltiples partes mediante barras oblicuas '/'. Por ejemplo, 'site/index' será desglosado en 'site' y 'index'. Cada parte es un ID que puede hacer referencia a un modulo, un controlador o una acción.

Empezando por la primera parte de la ruta, la aplicación lleva a cabo los siguientes pasos para crear módulos(si los hay), el controlador y la acción.

1. Establece la aplicación como el modulo actual.
2. Comprueba si el [[yii\base\Module::controllerMap|controller map]] del modulo actual contiene un ID actual. Si lo tiene, se creará un objecto controlador de acuerdo con la configuración encontrada en el mapa, y ejecuta el Paso 5 con el resto de partes de la ruta.
3. Comprueba si el ID hace referencia a un modulo de la lista de la propiedad[[yii\base\Module::modules|modules]] del actual modulo. Si es así, se crea un modulo de acuerdo con la configuración encontrada en la lista del modulo, y se ejecuta el Paso 2 con la siguiente parte de la ruta dentro del contexto del modulo recién creado.
4. Trata el ID como un ID de controlador y crea un objeto controlador. Ejecuta el siguiente paso con el resto de la ruta.
5. El controlador busca el ID actual en su [[yii\base\Controller::actions()|action map]]. Si lo encuentra, crea una acción de acuerdo con la configuración encontrada en el mapa. De lo contrario, el controlador intentará crear una acción en linea que esta definida por el método de la acción correspondiente con el ID actual.
