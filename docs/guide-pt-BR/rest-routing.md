Roteamento
=======

Com as classes de recurso e controller prontas, você pode acessar os recursos utilizando uma URL como `http://localhost/index.php?r=user/create`, semelhante ao que você pode fazer com aplicações Web normais.

Na prática, você geralmente deseja utilizar URLs amigáveis e tirar proveito dos métodos HTTP.
Por exemplo, uma requisição `POST /users` seria o mesmo que a ação `user/create`.
Isto pode ser feito facilmente através da configuração do `urlManager` [application component](structure-application-components.md) na configuração da aplicação conforme mostrado abaixo:

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

Em comparação com o gerenciamento de URL para aplicações web, a principal novidade acima é o uso de [[yii\rest\UrlRule]] para rotear requisições API RESTful API. Esta classe especial regra de URL irá criar um conjunto de regras de URL filhas para dar suporte ao roteamento e a criação de URL para o controller especificado.
Por exemplo, o código acima é mais ou menos equivalente às seguintes regras:

```php
[
   'PUT,PATCH users/<id>' => 'user/update',
   'DELETE users/<id>' => 'user/delete',
   'GET,HEAD users/<id>' => 'user/view',
   'POST users' => 'user/create',
   'GET,HEAD users' => 'user/index',
   'users/<id>' => 'user/options',
   'users' => 'user/options',
]
```

E os seguintes terminais de API são suportados por esta regra:

* `GET /users`: lista todos os usuários página por página;
* `HEAD /users`: mostrar a informações gerais da listagem de usuários;
* `POST /users`: cria um novo usuário;
* `GET /users/123`: retorna detalhes do usuário 123;
* `HEAD /users/123`: mostrar a informações gerais do usuário 123;
* `PATCH /users/123` and `PUT /users/123`: atualiza o usuário 123;
* `DELETE /users/123`: deleta o usuário 123;
* `OPTIONS /users`: mostrar os metodos suportados pelo terminal `/users`;
* `OPTIONS /users/123`: mostrar os metodos suportados pelo terminal  `/users/123`.

Você pode configurar as opções `only` e `except` para listar explicitamente que ações são suportadas ou quais ações devem ser desativada, respectivamente. Por exemplo,

```php
[
   'class' => 'yii\rest\UrlRule',
   'controller' => 'user',
   'except' => ['delete', 'create', 'update'],
],
```

Você também pode configurar `patterns` ou `extraPatterns` para redefinir padrões existentes ou adicionar novos padrões suportados por esta regra. Por exemplo, para acessar a uma nova ação `search` pelo terminal `GET /users/search`, configure a opção `extraPatterns` como a seguir,

```php
[
   'class' => 'yii\rest\UrlRule',
   'controller' => 'user',
   'extraPatterns' => [
       'GET search' => 'search',
   ],
]
```

Você deve ter notado que o controller ID `user` aparece no plural como `users` na extremidade das  URLs. Isto acontece porque [[yii\rest\UrlRule]] pluraliza os controller IDs automaticamente na criação de regras de Urls filhas.
Você pode desabilitar este comportamento configurando [[yii\rest\UrlRule::pluralize]] para falso.

> Observação: A pluralização dos controller IDs é feita por [[yii\helpers\Inflector::pluralize()]]. O método respeita as regras especiais de pluralização. Por exemplo, a palavra `box` será pluralizada coo `boxes` em vez de `boxs`.


Caso a pluralização automática não encontre uma opção para a palavra requerida, você pode configurar a propriedade [[yii\rest\UrlRule::controller]] para especificar explicitamente como mapear um nome para ser usado como terminal da URL para um controller ID. Por exemplo, o seguinte código mapeia o nome `u` para o controller ID `user`.  

```php
[
   'class' => 'yii\rest\UrlRule',
   'controller' => ['u' => 'user'],
]
```


