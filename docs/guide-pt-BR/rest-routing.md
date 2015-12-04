Roteamento
=======

Com as classes de recurso e controller prontas, você pode acessar os recursos utilizando uma URL como `http://localhost/index.php?r=user/create`, semelhante ao que você pode fazer com aplicações Web normais.

Na prática, normalmente você desejará utilizar URLs amigáveis e tirar proveito dos métodos HTTP.
Por exemplo, uma requisição `POST /users` seria o mesmo que a ação `user/create`.
Isto pode ser feito facilmente através da configuração do [componente de aplicação](structure-application-components.md) `urlManager` conforme mostrado a seguir:

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

Em comparação com o gerenciamento de URL para aplicações Web, a principal novidade acima é o uso de [[yii\rest\UrlRule]] para rotear requisições API RESTful. Esta classe especial criará um conjunto de regras de URL filhas para dar suporte ao roteamento e a criação de URL para o controller especificado.
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

E as seguintes URLs (também chamadas de *endpoints*) da API são suportados por esta regra:

* `GET /users`: lista todos os usuários página por página;
* `HEAD /users`: mostrar a informações gerais da listagem de usuários;
* `POST /users`: cria um novo usuário;
* `GET /users/123`: retorna detalhes do usuário 123;
* `HEAD /users/123`: mostrar a informações gerais do usuário 123;
* `PATCH /users/123` and `PUT /users/123`: atualiza o usuário 123;
* `DELETE /users/123`: deleta o usuário 123;
* `OPTIONS /users`: exibe os métodos suportados pela URL `/users`;
* `OPTIONS /users/123`: exibe os métodos suportados pela URL `/users/123`.

Você pode configurar as opções `only` e `except` para listar explicitamente quais ações são suportadas ou quais ações devem ser desativadas, respectivamente. Por exemplo,

```php
[
   'class' => 'yii\rest\UrlRule',
   'controller' => 'user',
   'except' => ['delete', 'create', 'update'],
],
```

Você também pode configurar `patterns` ou `extraPatterns` para redefinir padrões existentes ou adicionar novos padrões suportados por esta regra. Por exemplo, para acessar a uma nova ação `search` pela URL `GET /users/search`, configure a opção `extraPatterns` como a seguir,

```php
[
   'class' => 'yii\rest\UrlRule',
   'controller' => 'user',
   'extraPatterns' => [
       'GET search' => 'search',
   ],
]
```

Você deve ter notado que o ID `user` de controller aparece no plural como `users` na extremidade das  URLs. Isto acontece porque [[yii\rest\UrlRule]] pluraliza os IDs de controllers automaticamente na criação de regras de URLs filhas.
Você pode desabilitar este comportamento configurando [[yii\rest\UrlRule::pluralize]] para `false`.

> Observação: A pluralização dos IDs de controllers são feitas pelo método [[yii\helpers\Inflector::pluralize()]]. O método respeita as regras especiais de pluralização. Por exemplo, a palavra `box` será pluralizada para `boxes` em vez de `boxs`.


Caso a pluralização automática não encontre uma opção para a palavra requerida, você pode configurar a propriedade [[yii\rest\UrlRule::controller]] para especificar explicitamente como mapear um nome para ser usado como uma URL para um ID de controller. Por exemplo, o seguinte código mapeia o nome `u` para o ID `user` de controller.  

```php
[
   'class' => 'yii\rest\UrlRule',
   'controller' => ['u' => 'user'],
]
```


