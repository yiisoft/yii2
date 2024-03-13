Versionamento
==========

Uma boa API é *versionada*: Mudanças e novos recursos são implementados em novas versões da API em vez de alterar continuamente apenas uma versão. Diferente de aplicações Web, com a qual você tem total controle do código de ambos os lados cliente e servidor, APIs são destinadas a ser utilizadas por clientes além de seu controle. Por esta razão, a compatibilidade (BC) entre as APIs deve ser mantida sempre que possível. Se uma mudança que pode quebrar esta compatibilidade é necessária, você deve introduzi-la em uma nova versão de API e subir o número da versão. Os clientes existentes podem continuar a usar a versão antiga da API; e os clientes novos ou atualizados podem obter a nova funcionalidade na nova versão da API.

> Dica: Consulte o artigo [Semantic Versioning](https://semver.org/) para obter mais informações sobre como projetar números de versão da API.

Uma maneira comum de implementar versionamento de API é incorporar o número da versão nas URLs da API. Por exemplo, `https://example.com/v1/users` representa o terminal `/users` da API versão 1.

Outro método de versionamento de API, que tem sido muito utilizado recentemente, é colocar o número da versão nos cabeçalhos das requisições HTTP. Isto é tipicamente feito através do cabeçalho `Accept`:

```
// Através de um parâmetro
Accept: application/json; version=v1
// através de um vendor content type
Accept: application/vnd.company.myapp-v1+json
```

Ambos os métodos tem seus prós e contras, e há uma série de debates sobre cada abordagem. A seguir, você verá uma estratégia prática para o controle de versão de API que é uma mistura dos dois métodos:

* Coloque cada versão principal de implementação da API em um módulo separado cuja identificação é o número de versão principal (ex. `v1`, `v2`). Naturalmente, as URLs da API irão conter os números da versão principal.
* Dentro de cada versão principal (e, assim, dentro do módulo correspondente), utilize o cabeçalho `Accept` da requisição HTTP para determinar o número de versão secundária e escrever código condicional para responder às versões menores em conformidade.

Para cada módulo destinado a uma versão principal, deve incluir o recurso e a classe de controller destinados a esta versão específica.
Para melhor separar a responsabilidade do código, você pode manter um conjunto comum de classes base de recursos e de controller e criar subclasses delas para cada versão individual do módulo. Dentro das subclasses, implementar o código concreto, tais como `Model::fields()`.

Seu código pode ser organizado da seguinte maneira:

```
api/
   common/
       controllers/
           UserController.php
           PostController.php
       models/
           User.php
           Post.php
   modules/
       v1/
           controllers/
               UserController.php
               PostController.php
           models/
               User.php
               Post.php
           Module.php
       v2/
           controllers/
               UserController.php
               PostController.php
           models/
               User.php
               Post.php
           Module.php
```

A configuração da sua aplicação seria algo como:

```php
return [
   'modules' => [
       'v1' => [
           'class' => 'app\modules\v1\Module',
       ],
       'v2' => [
           'class' => 'app\modules\v2\Module',
       ],
   ],
   'components' => [
       'urlManager' => [
           'enablePrettyUrl' => true,
           'enableStrictParsing' => true,
           'showScriptName' => false,
           'rules' => [
               ['class' => 'yii\rest\UrlRule', 'controller' => ['v1/user', 'v1/post']],
               ['class' => 'yii\rest\UrlRule', 'controller' => ['v2/user', 'v2/post']],
           ],
       ],
   ],
];
```

Como resultado do código acima, `https://example.com/v1/users` retornará a lista de usuários na versão 1, enquanto `https://example.com/v2/users` retornará a lista de usuários na versão 2.

Graças aos módulos, o código para diferentes versões principais pode ser bem isolado. Entretanto esta abordagem torna possível a reutilização de código entre os módulos através de classes bases comuns e outros recursos partilhados.
 
Para lidar com números de subversões, você pode tirar proveito da negociação de conteúdo oferecida pelo behavior [[yii\filters\ContentNegotiator|contentNegotiator]]. O behavior `contentNegotiator` irá configurar a propriedade [[yii\web\Response::acceptParams]] que determinará qual versão é suportada.

Por exemplo, se uma requisição é enviada com o cabeçalho HTTP `Accept: application/json; version=v1`, Após a negociação de conteúdo, [[yii\web\Response::acceptParams]] terá o valor `['version' => 'v1']`.

Com base na informação da versão em `acceptParams`, você pode escrever um código condicional em lugares tais como ações, classes de recursos, serializadores, etc. para fornecer a funcionalidade apropriada.


Uma vez que subversões por definição devem manter compatibilidades entre si, esperamos que não haja muitas verificações de versão em seu código. De outra forma, você pode precisar criar uma nova versão principal.
