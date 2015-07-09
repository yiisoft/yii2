Recursos
=========

APIs RESTful tratam de como acessar e manipular *recursos*. Você pode ver recursos como [models](structure-models.md) no paradigma MVC.

Embora não haja restrição na forma de representar um recurso, no Yii você normalmente representaria recursos como objetos de [[yii\base\Model]] ou de uma classe filha (ex. [[yii\db\ActiveRecord]]), pelas seguintes razões:

* [[yii\base\Model]] implementa a interface [[yii\base\Arrayable]], que permite que você personalize como você deseja expor dados de recursos através das APIs RESTful.
* [[yii\base\Model]] suporta [input validation](input-validation.md), que é importante se as suas APIs RESTful precisarem suportar entrada de dados.
* [[yii\db\ActiveRecord]] fornece acesso poderoso a banco de dados com suporte a manipulação dos dados, o que o torna um ajuste perfeito se seus dados de recursos estiverem armazenado em bases de dados.

Nesta seção, vamos principalmente descrever como uma classe de recurso que se estende de [[yii\base\Model]] (ou alguma classe filha) pode especificar quais os dados podem ser retornados via APIs RESTful. Se a classe de recurso não estender de [[yii\base\Model]], antão todas as suas variáveis públicas serão retornadas.

## Campos<span id="fields"></span>

Ao incluir um recurso em uma resposta da API RESTful, o recurso precisa ser serializado em uma string. Yii quebra este processo em duas etapas. Primeiro, o recurso é convertido em um array utilizando [[yii\rest\Serializer]]. Por último, o array é serializado em uma string no formato solicitado (ex. JSON, XML) através do [[yii\web\ResponseFormatterInterface|response formatters]]. O primeiro passo é o que você deve centrar-se principalmente no desenvolvimento de uma classe de recurso.

Sobrescrevendo [[yii\base\Model::fields()|fields()]] e/ou [[yii\base\Model::extraFields()|extraFields()]], você pode especificar quais os dados, chamados *fields*, no recurso podem ser colocados no array.
A diferença entre estes dois métodos é que o primeiro especifica o conjunto padrão de campos que devem ser incluídos no array, enquanto que o último especifica campos adicionais que podem ser incluídos no array, se um usuário final solicitá-los via o parâmetro de pesquisa `expand`. Por exemplo,

```
// retorna todos os campos declarados em fields()
http://localhost/users

// retorna apenas os campos id e email, desde que estejam declarados em fields()
http://localhost/users?fields=id,email

// retorna todos os campos de fields() e o campo profile se este estiver no extraFields()
http://localhost/users?expand=profile

// retorna apenas o campo id, email e profile, desde que estejam em fields() e extraFields()
http://localhost/users?fields=id,email&expand=profile
```


### Sobrescrevendo `fields()` <span id="overriding-fields"></span>

Por padrão, [[yii\base\Model::fields()]] retorna todos os atributos do model como campos, enquanto [[yii\db\ActiveRecord::fields()]] só retorna os atributos que tenham sido preenchidos a partir do DB.

Você pode sobrescrever `fields()` para adicionar, remover, renomear ou redefinir campos. O valor do retorno de `fields()` deve ser um array. As chaves do array são os nomes dos campos, e os valores são as definições dos campos correspondentes, que podem ser tanto nomes de propriedade/atributo ou funções anônimas retornando o valor do campo correspondente. No caso especial quando um nome de um campo é o mesmo que sua definição de nome de atributo, você pode omitir a chave do array. For example,

```php
//explicitamente listar todos os campos, 
//melhor usado quando você quer ter certeza de que as alterações
//na sua tabela ou atributo do model não causam alteração 
//nos seus campos (Manter compatibilidade da API).
public function fields()
{
   return [
       // Nome do campo é igual ao nome do atributo
       'id',
       // nme do campo é "email", o nome do atributo correspondente é  "email_address"
       'email' => 'email_address',
       // nome do campo é "name", seu valor é definido por um PHP callback
       'name' => function ($model) {
           return $model->first_name . ' ' . $model->last_name;
       },
   ];
}

// filtrar alguns campos, melhor usado quando você deseja herdar a implementação do pai
// e deseja esconder alguns campos confidenciais.
public function fields()
{
   $fields = parent::fields();

   // remove campos que contém informações confidenciais
   unset($fields['auth_key'], $fields['password_hash'], $fields['password_reset_token']);

   return $fields;
}
```

> Aviso: Porque, por padrão, todos os atributos de um model serão incluídos 
> no resultados da API, você deve examinar os seus dados para se certificar de que 
> eles não contenham informações confidenciais. 
> Se existirem tais informações, você deve sobrescrever `fields()` para filtrá-los. 
> No exemplo acima, nós escolhemos filtrar `auth_key`, 
> `password_hash` e `password_reset_token`.



### Sobrescrevendo `extraFields()` <span id="overriding-extra-fields"></span>

Por padrão, [[yii\base\Model::extraFields()]] não retorna nada, enquanto [[yii\db\ActiveRecord::extraFields()]] retorna os nomes das relações que foram populadas a partir do DB.

O formato do retorno dos dados do `extraFields()` é o mesmo de `fields()`. Geralmente, `extraFields()` é mais usado para especificar os campos cujos valores são objetos
. Por exemplo, Dada a seguinte declaração de campo,

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

o request com `http://localhost/users?fields=id,email&expand=profile` pode retornar o seguinte dados em formato JSON:

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


## Links <span id="links"></span>

[HATEOAS](http://en.wikipedia.org/wiki/HATEOAS), Uma abreveação de “Hypermedia as the Engine of Application State”, promove as APIs Restfull retornar informações para permitir aos clientes descobrir as ações suportadas pelos recursos retornados. O sentido de HATEOAS é retornar um conjunto de hiperlinks em relação às informações quando os recursos de dados são servidos pelas APIs.

Suas classes de recurso podem suportar HATEOAS implementando a interface [[yii\web\Linkable]]. Esta interface contém um único método [[yii\web\Linkable::getLinks()|getLinks()]] que deve retornar uma lista de [[yii\web\Link|links]].
Tipicamente, Você deve retornar pelo menos o link `self` representando a URL para o mesmo objeto de recurso. Por exemplo,

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

quando o `User` object is returned in a response, it will contain a `_links` element representing the links related
to the user, for example,

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


## Collections (Coleções) <span id="collections"></span>

Objetos de recurso podem ser agrupados em *collections*. Cada collection contém uma lista de objetos de recurso de mesmo tipo.

Embora collections podem ser representados como arrays, Normalmente, é preferível representá-los como [data providers](output-data-providers.md). Isto porque data providers suportam ordenação e paginação de recursos, que é um recurso comumente necessária para APIs RESTful retornando collections. Por exemplo,
a seguinte ação retorna um data provider sobre o recurso post:

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

Quando um data provider está enviando uma resposta com a API RESTful, [[yii\rest\Serializer]] pegará a  a página atual de recursos e serializa-la como uma rray de objetos de recurso. Adicionalmente, [[yii\rest\Serializer]] Também incluirá as informações de paginação pelo seguinte cabeçalho HTTP:

* `X-Pagination-Total-Count`: The total number of resources;
* `X-Pagination-Page-Count`: The number of pages;
* `X-Pagination-Current-Page`: The current page (1-based);
* `X-Pagination-Per-Page`: The number of resources in each page;
* `Link`: A set of navigational links allowing client to traverse the resources page by page.

Um exemplo pode ser encontrado na seção [Quick Start](rest-quick-start.md#trying-it-out).


