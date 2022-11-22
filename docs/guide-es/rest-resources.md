Recursos
=========

Las APIs RESTful lo son todos para acceder y manipular *recursos (resources)*. Puedes observar los recursos en el paradigma MVC en [Modelos (models)](structure-models.md) .

Mientras que no hay restricción a cómo representar un recurso, en YII usualmente, puedes representar recursos como objetos de la clase [[yii\base\Model]] o sus clases hijas (p.e. [[yii\db\ActiveRecord]]), por las siguientes razones:

* [[yii\base\Model]] implementa el interface [[yii\base\Arrayable]] , el cual te permite personalizar como exponer los datos de los recursos a travès de las APIs RESTful.
* [[yii\base\Model]] soporta [Validación de entrada (input validation)](input-validation.md), lo cual es muy usado en las APIs RESTful para soportar la entrada de datos.
* [[yii\db\ActiveRecord]] provee un poderoso soporte para el acceso a datos en bases de datos y su manipulación, lo que lo le hace servir perfectamente si sus recursos de datos están en bases de datos.

En esta sección, vamos principalmente a describir como la clase con recursos que extiende de [[yii\base\Model]] (o sus clases hijas) puede especificar qué datos puede ser devueltos vía las APIs RESTful. Si la clase de los recursos no extiende de [[yii\base\Model]], entonces todas las variables públicas miembro serán devueltas.


## Campos (fields) <span id="fields"></span>

Cuando incluimos un recurso en una respuesta de la API RESTful, el recurso necesita ser serializado en una cadena.
Yii divide este proceso en dos pasos. Primero, el recurso es convertido en un array por [[yii\rest\Serializer]].
Segundo, el array es serializado en una cadena en  el formato requerido (p.e. JSON, XML) por [[yii\web\ResponseFormatterInterface|response formatters]]. El primer paso es en el que debes de concentrarte principalmente cuando desarrolles una clase de un recurso.

Sobreescribiendo [[yii\base\Model::fields()|fields()]] y/o [[yii\base\Model::extraFields()|extraFields()]],
puedes especificar qué datos, llamados *fields*, en el recursos, pueden ser colocados en el array que le representa.
La diferencia entre estos dos métodos es que el primero especifica el conjunto de campos por defecto que deben ser incluidos en el array que los representa, mientras que el último especifica campos adicionales que deben de ser incluidos en el array si una petición del usuario final para ellos vía el parámetro de consulta `expand`. Por ejemplo,

```
// devuelve todos los campos declarados en fields()
http://localhost/users

// sólo devuelve los campos id y email, provistos por su declaración en fields()
http://localhost/users?fields=id,email

// devuelve todos los campos en fields() y el campo profile siempre y cuando esté declarado en extraFields()
http://localhost/users?expand=profile

// sólo devuelve los campos id, email y profile, siempre y cuando ellos estén declarados en fields() y extraFields()
http://localhost/users?fields=id,email&expand=profile
```


### Sobreescribiendo `fields()` <span id="overriding-fields"></span>

Por defecto, [[yii\base\Model::fields()]] devuelve todos los atributos de los modelos como si fueran campos, mientras [[yii\db\ActiveRecord::fields()]] sólo devuelve los atributos que tengan datos en la base de datos.

Puedes sobreescribir `fields()` para añadir, quitar, renombrar o redefinir campos. El valor de retorno de `fields()` ha de estar en un array. Las claves del array son los nombres de los campos y los valores del array son las correspondientes definiciones de los campos que pueden ser tanto nombres de propiedades/atributos o funciones anónimas que devuelven los correspondientes valores del los campos. En el caso especial de que el nombre de un campo sea el mismo que su definición puedes omitir la clave en el array. Por ejemplo,

```php
// explícitamente lista cada campo, siendo mejor usarlo cuando quieras asegurarte que los cambios
// en una tabla de la base de datos o en un atributo del modelo no provoque el cambio de tu campo (para mantener la compatibilidad anterior).
public function fields()
{
    return [
        // el nombre de campo es el mismo nombre del atributo
        'id',
        // el nombre del campo es "email", su atributo se denomina "email_address"
        'email' => 'email_address',
        // el nombre del campo es "name", su valor es definido está definido por una función anónima de retrollamada (callback)
        'name' => function () {
            return $this->first_name . ' ' . $this->last_name;
        },
    ];
}

// el ignorar algunos campos, es mejor usarlo cuando heredas de una implementación padre
// y pones en la lista negra (blacklist) algunos campos sensibles
public function fields()
{
    $fields = parent::fields();

    // quita los campos con información sensible
    unset($fields['auth_key'], $fields['password_hash'], $fields['password_reset_token']);

    return $fields;
}
```

> Warning: Dado que, por defecto, todos los atributos de un modelo pueden ser incluidos en la devolución del API, debes
> examinar tus datos para estar seguro de que no contiene información sensible. Si se da este tipo de información,
> debes sobreescribir `fields()` para filtrarlos. En el ejemplo anterior, escogemos
> quitar `auth_key`, `password_hash` y `password_reset_token`.


### Sobreescribiendo `extraFields()` <span id="overriding-extra-fields"></span>

Por defecto, [[yii\base\Model::extraFields()]] no devuelve nada, mientras que [[yii\db\ActiveRecord::extraFields()]] devuelve los nombres de las relaciones que tienen datos (populated) obtenidos de la base de datos.

El formato de devolución de los datos de `extraFields()` es el mismo que el de `fields()`. Usualmente, `extraFields()` es principalmente usado para especificar campos cuyos valores sean objetos. Por ejemplo, dado la siguiente declaración de campo,

```php
public function fields()
{
    return ['id', 'email'];
}

public function extraFields()
{
    return ['profile'];
}
```

la petición `http://localhost/users?fields=id,email&expand=profile` puede devolver los siguientes datos en formato JSON :

```php
[
    {
        "id": 100,
        "email": "100@example.com",
        "profile": {
            "id": 100,
            "age": 30,
        }
    },
    ...
]
```


## Enlaces (Links) <span id="links"></span>

[HATEOAS](https://es.wikipedia.org/wiki/HATEOAS), es una abreviación de Hipermedia es el Motor del Estado de la Aplicación (Hypermedia as the Engine of Application State), promueve que las APIs RESTfull devuelvan información que permita a los clientes descubrir las acciones que soportan los recursos devueltos. El sentido de HATEOAS es devolver un conjunto de hiperenlaces con relación a la información, cuando los datos de los recursos son servidos por las APIs.

Las clases con recursos pueden soportar HATEOAS implementando el interfaz [[yii\web\Linkable]] . El interfaz contiene sólo un método [[yii\web\Linkable::getLinks()|getLinks()]] el cual debe de de devolver una lista de  [[yii\web\Link|links]].
Típicamente, debes devolver al menos un enlace `self` representando la URL al mismo recurso objeto. Por ejemplo,

```php
use yii\db\ActiveRecord;
use yii\web\Link;
use yii\web\Linkable;
use yii\helpers\Url;

class User extends ActiveRecord implements Linkable
{
    public function getLinks()
    {
        return [
            Link::REL_SELF => Url::to(['user/view', 'id' => $this->id], true),
        ];
    }
}
```

Cuando un objeto `User` es devuelto en una respuesta, puede contener un elemento  `_links` representando los enlaces relacionados con el usuario, por ejemplo,

```
{
    "id": 100,
    "email": "user@example.com",
    // ...
    "_links" => {
        "self": {
            "href": "https://example.com/users/100"
        }
    }
}
```


## Colecciones <span id="collections"></span>

Los objetos de los recursos pueden ser agrupados en  *collections*. Cada colección contiene una lista de recursos objeto del mismo tipo.

Las colecciones pueden ser representadas como arrays pero, es usualmente más deseable representarlas como [proveedores de datos (data providers)](output-data-providers.md). Esto es así porque los proveedores de datos soportan paginación y ordenación de los recursos, lo cual es comunmente necesario en las colecciones devueltas con las APIs RESTful. Por ejemplo, la siguiente acción devuelve un proveedor de datos sobre los recursos post:

```php
namespace app\controllers;

use yii\rest\Controller;
use yii\data\ActiveDataProvider;
use app\models\Post;

class PostController extends Controller
{
    public function actionIndex()
    {
        return new ActiveDataProvider([
            'query' => Post::find(),
        ]);
    }
}
```

Cuando un proveedor de datos está enviando una respuesta con el API RESTful, [[yii\rest\Serializer]] llevará la actual página de los recursos y los serializa como un array de recursos objeto. Adicionalmente, [[yii\rest\Serializer]]
puede incluir también la información de paginación a través de las cabeceras HTTP siguientes:

* `X-Pagination-Total-Count`: Número total de recursos;
* `X-Pagination-Page-Count`: Número de páginas;
* `X-Pagination-Current-Page`: Página actual (iniciando en 1);
* `X-Pagination-Per-Page`: Número de recursos por página;
* `Link`: Un conjunto de enlaces de navegación permitiendo al cliente recorrer los recursos página a página.

Un ejemplo se puede ver en la sección [Inicio rápido (Quick Start)](rest-quick-start.md#trying-it-out).
