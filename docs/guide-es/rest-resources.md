Controladores
=============

Después de crear las clases de recursos y especificar cómo deben ser el formato de datos de recursos, el siguiente paso es crear acciones del controlador para exponer los recursos a los usuarios a través de las APIs RESTful finales.

Yii ofrece dos clases de controlador de base para simplificar su trabajo de crear acciones REST: [[yii\rest\Controller]] y [[yii\rest\ActiveController]]. La diferencia entre estos dos controladores es que este último proporciona un conjunto predeterminado de acciones que están específicamente diseñadas para hacer frente a los recursos representados a [Active Record](db-active-record.md). Así que si usted está utilizando [Active Record](db-active-record.md) y se siente cómodo con las acciones integradas que proporciona, es posible considerar la prolongación de sus clases de controlador de [[yii\rest\ActiveController]], que le permitirá crear potentes APIs RESTful con código mínimo.

Ambos [[yii\rest\Controller]] y [[yii\rest\ActiveController]] proporcionar las siguientes características, algunas de las cuales se describen en detalle en las siguientes secciones:

* Métodos de Validación HTTP;
* [Negociación de contenido y formato de datos](rest-response-formatting.md);
* [Autenticación](rest-authentication.md);
* [Límite de Rango](rest-rate-limiting.md).

[[yii\rest\ActiveController]] además provee de las siguientes características:

* Un conjunto de acciones comunes necesarias: `index`, `view`, `create`, `update`, `delete`, `options`;
* La autorización del usuario en cuanto a que la acción solicitada y recursos.


## Creando Clases Controladoras <a name="creating-controller"></a>

Al crear una nueva clase de controlador, una convención para nombrar la clase del controlador es utilizar el nombre del tipo de recurso y el uso en singular. Por ejemplo, para servir información del usuario, el controlador puede ser nombrado como `UserController`.

Creación de una nueva acción es similar a crear una acción para una aplicación Web. La única diferencia es que en lugar de hacer que el resultado utilicé una vista llamando al método `render()`, para las acciones REST regresá directamente los datos. El [[yii\rest\Controller::serializer|serializer]] y el [[yii\web\Response|response object]] se encargará de la conversión de los datos originales, al formato solicitado. Por ejemplo,

```php
public function actionView($id)
{
    return User::findOne($id);
}
```


## Filtros <a name="filters"></a>

La mayoria de las características API REST son proporcionadas por [[yii\rest\Controller]] que son implementadas por los terminos de los [filtros](structure-filters.md).
En particular, los siguientes filtros se ejecutarán en el orden en que aparecen:

* [[yii\filters\ContentNegotiator|contentNegotiator]]: apoya la negociación de contenido, que se explica en la sección el [Formateo de respuestas](rest-response-formatting.md);
* [[yii\filters\VerbFilter|verbFilter]]: apoya métodos de validación HTTP;
* [[yii\filters\AuthMethod|authenticator]]: apoya autenticación de usuarios, que se explica en la sección [Autenticación](rest-authentication.md);
* [[yii\filters\RateLimiter|rateLimiter]]: apoya la limitación de rango, que se explica en la sección [Límite de Rango](rest-rate-limiting.md).

Estos filtros se declaran nombrándolos en el metodo [[yii\rest\Controller::behaviors()|behaviors()]]. Puede reemplazar este método para configurar los filtros individuales, desactivar algunos de ellos, o añadir sus propios filtros. Por ejemplo, si sólo desea utilizar la autenticación básica HTTP, puede escribir el siguiente código:

```php
use yii\filters\auth\HttpBasicAuth;

public function behaviors()
{
    $behaviors = parent::behaviors();
    $behaviors['authenticator'] = [
        'class' => HttpBasicAuth::className(),
    ];
    return $behaviors;
}
```


## Extendiendo `ActiveController` <a name="extending-active-controller"></a>

Si su clase controlador extiende de [[yii\rest\ActiveController]], debe establecer su propiedad [[yii\rest\ActiveController::modelClass||modelClass]] a ser el nombre del recurso la clase que va a servir a través de este controlador. La clase debe extender de [[yii\db\ActiveRecord]].


### Personalizando Acciones <a name="customizing-actions"></a>

Por defecto, [[yii\rest\ActiveController]] provee de las siguientes acciones:

* [[yii\rest\IndexAction|index]]: lista de recursos pagina por pagina;
* [[yii\rest\ViewAction|view]]: retorna el detalle de un recurso específico;
* [[yii\rest\CreateAction|create]]: crear un nuevo recurso;
* [[yii\rest\UpdateAction|update]]: actualizar un recurso existente;
* [[yii\rest\DeleteAction|delete]]: eliminar un recurso específico;
* [[yii\rest\OptionsAction|options]]: retorna los métodos HTTP soportados.

Todas esta acciones se declaran a través del métodos [[yii\rest\ActiveController::actions()|actions()]]. Usted puede configurar todas estas acciones o desactivar alguna de ellas reescribiendo el método `actions()`, como se muestra a continuación,

```php
public function actions()
{
    $actions = parent::actions();

    // disable the "delete" and "create" actions
    unset($actions['delete'], $actions['create']);

    // customize the data provider preparation with the "prepareDataProvider()" method
    $actions['index']['prepareDataProvider'] = [$this, 'prepareDataProvider'];

    return $actions;
}

public function prepareDataProvider()
{
    // prepare and return a data provider for the "index" action
}
```

Por favor, consulte las referencias de clase para las clases de acción individuales para aprender las opciones de configuración que se dispone.


### Realizando Comprobación de Acceso <a name="performing-access-check"></a>

Al exponer los recursos a través de RESTful APIs, a menudo es necesario comprobar si el usuario actual tiene permiso para acceder y manipular el recurso solicitado. Con [[yii\rest\ActiveController]], puede hacerse reemplazando el método [[yii\rest\ActiveController::checkAccess()|checkAccess()]] con lo siguiente, 

```php
/**
 * Checks the privilege of the current user.
 *
 * This method should be overridden to check whether the current user has the privilege
 * to run the specified action against the specified data model.
 * If the user does not have access, a [[ForbiddenHttpException]] should be thrown.
 *
 * @param string $action the ID of the action to be executed
 * @param \yii\base\Model $model the model to be accessed. If null, it means no specific model is being accessed.
 * @param array $params additional parameters
 * @throws ForbiddenHttpException if the user does not have access
 */
public function checkAccess($action, $model = null, $params = [])
{
    // check if the user can access $action and $model
    // throw ForbiddenHttpException if access should be denied
}
```

El método `checkAccess()` será llamado por defecto en las acciones predeterminadas de [[yii\rest\ActiveController]]. Si crea nuevas acciones y también desea llevar a cabo la comprobación de acceso, debe llamar a este método de forma explícita en las nuevas acciones.

> Consejo: Usted puede implementar `checkAccess()` mediante el uso del [Componente Role-Based Access Control (RBAC)](security-authorization.md).
