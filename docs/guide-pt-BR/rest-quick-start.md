Início Rápido
===========

Yii fornece um conjunto de ferramentas para simplificar a tarefa de implementar APIs RESTful Web Service. Em particular, Yii suporta os seguintes recursos sobre APIs RESTful:

* Prototipagem rápida com suporte para APIs comuns de [Active Record](db-active-record.md);
* Negociação de formato do Response (suporte JSON e XML por padrão);
* Serialização de objeto configurável com suporte a campos de saída selecionáveis;
*  formatação adequada para data collection e validação de erros;
* Suporte a [HATEOAS](http://en.wikipedia.org/wiki/HATEOAS);
* Roteamento eficiente  com verificação à HTTP verbs (métodos);
* Construído com suporte aos métodos `OPTIONS` e `HEAD`;
* Autenticação e autorização;
* Data caching e HTTP caching;
* Limitação de taxa;


Abaixo, utilizamos um exemplo para ilustrar como você pode construir um conjunto de APIs RESTful com um mínimo de codificação.

Suponha que você deseja expor os dados do usuário via APIs RESTful. Os dados do usuário estão guardados na tabela `user`, e você já criou a classe [active record](db-active-record.md) `app\models\User` para acessar os dados do usuário.


## Criando um Controller (Controlador)<span id="creating-controller"></span>

Primeiro, crie uma classe [controller](structure-controllers.md) `app\controllers\UserController` como a seguir,

```php
namespace app\controllers;

use yii\rest\ActiveController;

class UserController extends ActiveController
{
   public $modelClass = 'app\models\User';
}
```

A classe controller estende de [[yii\rest\ActiveController]], que implementa um conjunto comum de ações RESTful. Especificando [[yii\rest\ActiveController::modelClass|modelClass]]
como `app\models\User`, o controller sabe qual o model que pode ser usado para a recuperação e manipulação de dados.


## Configurando regras de URL<span id="configuring-url-rules"></span>


Em seguida, modifique a configuração do componente `urlManager` na configuração da aplicação:

```php
'urlManager' => [
   'enablePrettyUrl' => true,
   'enableStrictParsing' => true,
   'showScriptName' => false,
   'rules' => [
       ['class' => 'yii\rest\UrlRule', 'controller' => 'user'],
   ],
]
```

A configuração acima primeiramente adiciona uma regra de URL para o controller `user` de modo que os dados do usuário podem ser acessados e manipulados com URLs amigáveis e métodos HTTP significativos.

## Ativando o input via JSON<span id="enabling-json-input"></span>

Para fazer a API aceitar dados no formato JSON, configure a propriedade [[yii\web\Request::$parsers|parsers]] do [application component](structure-application-components.md) `request` para usar o [[yii\web\JsonParser]] para realizar input via JSON:

```php
'request' => [
   'parsers' => [
       'application/json' => 'yii\web\JsonParser',
   ]
]
```

> Observação: A configuração acima é opcional. Sem a configuração acima, a API só iria reconhecer os formatos de input `application/x-www-form-urlencoded` e `multipart/form-data`.


## Testando <span id="trying-it-out"></span>

Com o mínimo de esforço acima, você já terminou sua tarefa de criar as APIs RESTful para acessar os dados do usuário. As APIs que você criou incluem:

* `GET /users`: listar todos os usuários página por página;
* `HEAD /users`: mostrar a informações gerais da listagem de usuários;
* `POST /users`: criar um novo usuário;
* `GET /users/123`: retorna detalhes do usuário 123;
* `HEAD /users/123`: mostra informações gerais do usuário 123;
* `PATCH /users/123` and `PUT /users/123`: atualiza o usuário 123;
* `DELETE /users/123`: deleta o usuário 123;
* `OPTIONS /users`: mostra os métodos suportados em relação ao endpoint `/users`;
* `OPTIONS /users/123`: mostra os métodos suportados em relação ao endpoint `/users/123`.

> Observação: Yii vai pluralizar automaticamente nomes de controller para uso em endpoints.
> Você pode configurar isso usando a propriedade [[yii\rest\UrlRule::$pluralize]].

Você pode acessar suas APIs com o comando `curl` mostrado abaixo,

```
$ curl -i -H "Accept:application/json" "http://localhost/users"

HTTP/1.1 200 OK
...
X-Pagination-Total-Count: 1000
X-Pagination-Page-Count: 50
X-Pagination-Current-Page: 1
X-Pagination-Per-Page: 20
Link: <http://localhost/users?page=1>; rel=self, 
     <http://localhost/users?page=2>; rel=next, 
     <http://localhost/users?page=50>; rel=last
Transfer-Encoding: chunked
Content-Type: application/json; charset=UTF-8

[
   {
       "id": 1,
       ...
   },
   {
       "id": 2,
       ...
   },
   ...
]
```

Tente alterar o tipo de conteúdo para `application/xml`, e você vai ver o resultado retornado em formato XML:

```
$ curl -i -H "Accept:application/xml" "http://localhost/users"

HTTP/1.1 200 OK
...
X-Pagination-Total-Count: 1000
X-Pagination-Page-Count: 50
X-Pagination-Current-Page: 1
X-Pagination-Per-Page: 20
Link: <http://localhost/users?page=1>; rel=self, 
     <http://localhost/users?page=2>; rel=next, 
     <http://localhost/users?page=50>; rel=last
Transfer-Encoding: chunked
Content-Type: application/xml

<?xml version="1.0" encoding="UTF-8"?>
<response>
   <item>
       <id>1</id>
       ...
   </item>
   <item>
       <id>2</id>
       ...
   </item>
   ...
</response>
```

O seguinte comando irá criar um novo usuário, enviando uma solicitação POST com os dados do usuário em formato JSON:

```
$ curl -i -H "Accept:application/json" -H "Content-Type:application/json" -XPOST "http://localhost/users" -d '{"username": "example", "email": "user@example.com"}'

HTTP/1.1 201 Created
...
Location: http://localhost/users/1
Content-Length: 99
Content-Type: application/json; charset=UTF-8

{"id":1,"username":"example","email":"user@example.com","created_at":1414674789,"updated_at":1414674789}
```

> Dica: Você também pode acessar suas APIs via navegador da Web, digitando a URL `http://localhost/users`. No entanto, você pode precisar de alguns plugins do navegador para enviar cabeçalhos de solicitações específicas.

Como você pode ver, no cabeçalho da resposta, há informações sobre a contagem total, número de páginas, etc. Há também links que permitem navegar para outras páginas de dados. Por exemplo, `http://localhost/users?page=2` lhe daria a próxima página dos dados de usuário.

Usando os parâmetros `fields` e `expand`, você também pode especificar os campos que devem ser incluídos no resultado. Por exemplo, a URL `http://localhost/users?fields=id,email` só retornará os campos `id` e `email`.


> Observação: Você deve ter notado que o resultado de `http://localhost/users` 
> inclui alguns campos confidenciais,
> Tal como `password_hash`, `auth_key`. Você certamente não quer que 
> eles apareçam no resultado da sua API.
> Você pode e deve filtrar esses campos, conforme descrito na secção 
> [Response Formatting](rest-response-formatting.md).


## Resumo <span id="summary"></span>

Usando o Yii RESTful API framework, você implementa um endpoint desses campos, conforme descrito na secção a nível de ações do controller e você usa um controller para organizar as ações que implementam os endpoints para um único tipo de recurso.

Os recursos são representados como modelos de dados, que se estendem a partir da classe [[yii\base\Model]]. Se você estiver trabalhando com bancos de dados (relational or NoSQL), é recomendado que você use [[yii\db\ActiveRecord|ActiveRecord]] para representar recursos.

Você pode usar [[yii\rest\UrlRule]] para simplificar o roteamento para seus endpoints API.


Embora não seja exigido, é recomendável que você desenvolva suas APIs RESTful  como uma aplicação separada, diferente do seu frontend e backend para facilitar a manutenção.

