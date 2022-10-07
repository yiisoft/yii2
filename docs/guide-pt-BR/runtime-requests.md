Requisições
===========

As requisições realizadas na aplicação são representadas pelo objeto [[yii\web\Request]] 
que fornece informações como os parâmetros da requisição, cabeçalhos HTTP, cookies 
e etc. Em uma determinada requisição, você pode acessar o objeto da requisição 
correspondente através do [componente da aplicação](structure-application-components.md) 
`request`, que é uma instância de [[yii\web\Request]], por padrão. Nesta seção, 
descreveremos como você pode usar este componente em sua aplicação.


## Parâmetros da Requisição <span id="request-parameters"></span>

Para obter os parâmetros da requisição, você pode chamar os métodos 
[[yii\web\Request::get()|get()]] e [[yii\web\Request::post()|post()]] do 
componente `request`. Estes métodos retornam os valores de `$_GET` e `$_POST`, 
respectivamente. Por exemplo,

```php
$request = Yii::$app->request;

$get = $request->get(); 
// equivalente à: $get = $_GET;

$id = $request->get('id');   
// equivalente à: $id = isset($_GET['id']) ? $_GET['id'] : null;

$id = $request->get('id', 1);   
// equivalente à: $id = isset($_GET['id']) ? $_GET['id'] : 1;

$post = $request->post(); 
// equivalente à: $post = $_POST;

$name = $request->post('name');   
// equivalente à: $name = isset($_POST['name']) ? $_POST['name'] : null;

$name = $request->post('name', '');   
// equivalente à: $name = isset($_POST['name']) ? $_POST['name'] : '';
```

> Informação: Ao invés de acessar diretamente o `$_GET` e o `$_POST` para recuperar 
  os parâmetros da requisição, é recomendável que os utilizem através do componente 
  `request`, como mostrado nos exemplos acima. Isto permite que você escreva testes 
  de forma mais simples, utilizando um componente da requisição que retornem valores 
  pré-determinados.

Ao implementar o [RESTful APIs](rest-quick-start.md), muitas vezes você precisará 
recuperar os parâmetros que foram enviados pelos [métodos de requisição](#request-methods) 
PUT, PATCH ou outro. Você pode recuperá-los chamando o método [[yii\web\Request::getBodyParam()]]. 
Por exemplo,

```php
$request = Yii::$app->request;

// retorna todos os parâmetros 
$params = $request->bodyParams;

// retorna o parâmetro "id"
$param = $request->getBodyParam('id');
```

> Informação: Tirando os parâmetros `GET`, os parâmetros `POST`, `PUT`, `PATCH` 
  e etc são enviados no corpo da requisição. O componente `request` analisará 
  estes parâmetros quando você acessá-los através dos métodos descritos acima.
  Você pode personalizar a forma como estes parâmetros são analisados pela 
  configuração da propriedade [[yii\web\Request::parsers]].


## Métodos da Requisição <span id="request-methods"></span>

Você pode obter o método HTTP usado pela requisição atual através da expressão 
`Yii::$app->request->method`. Um conjunto de propriedades booleanas também são 
fornecidos para que você consiga verificar se o método atual é o correto.
Por exemplo,

```php
$request = Yii::$app->request;

if ($request->isAjax) { /* a requisição é uma requisição Ajax */ }
if ($request->isGet)  { /* o método da requisição é GET */ }
if ($request->isPost) { /* o método da requisição é POST */ }
if ($request->isPut)  { /* o método da requisição é PUT */ }
```

## URLs da Requisição <span id="request-urls"></span>

O componente `request` fornece muitas formas de inspecionar a atual URL da requisição.
Assumindo que a URL da requisição seja `https://example.com/admin/index.php/product?id=100`, 
você pode obter várias partes desta URL através das propriedades explicadas a seguir:

* [[yii\web\Request::url|url]]: retorna `/admin/index.php/product?id=100`, que é 
  a URL sem as informações de protocolo e de domínio. 
* [[yii\web\Request::absoluteUrl|absoluteUrl]]: retorna `https://example.com/admin/index.php/product?id=100`, 
  que é a URL completa, incluindo as informações de protocolo e de domínio.
* [[yii\web\Request::hostInfo|hostInfo]]: retorna `https://example.com`, que são 
  as informações de protocolo e de domínio da URL.
* [[yii\web\Request::pathInfo|pathInfo]]: retorna `/product`, que é a informação 
  depois do script de entrada e antes da interrogação (da query string).
* [[yii\web\Request::queryString|queryString]]: retorna `id=100`, que é a 
  informação depois da interrogação. 
* [[yii\web\Request::baseUrl|baseUrl]]: retorna `/admin`, que é a informação 
  depois do domínio e antes do script de entrada.
* [[yii\web\Request::scriptUrl|scriptUrl]]: retorna `/admin/index.php`, que é a 
  informação depois do domínio até o script de entrada, inclusive.
* [[yii\web\Request::serverName|serverName]]: retorna `example.com`, que é o 
  domínio da URL.
* [[yii\web\Request::serverPort|serverPort]]: retorna 80, que é a porta usada 
  pelo servidor Web.


## Cabeçalho HTTP <span id="http-headers"></span> 

Você pode obter as informações do cabeçalho HTTP através da 
[[yii\web\HeaderCollection|coleção de cabeçalho]] retornado pela propriedade 
[[yii\web\Request::headers]]. Por exemplo,

```php
// $headers é um objeto de yii\web\HeaderCollection 
$headers = Yii::$app->request->headers;

// retorna o valor do cabeçalho Accept
$accept = $headers->get('Accept');

if ($headers->has('User-Agent')) { /* existe o cabeçalho User-Agent */ }
```

O componente `request` também fornece suporte para fácil acesso de alguns 
cabeçalhos mais utilizados, incluindo:

* [[yii\web\Request::userAgent|userAgent]]: retorna o valor do cabeçalho `User-Agent`.
* [[yii\web\Request::contentType|contentType]]: retorna o valor do cabeçalho 
  `Content-Type` que indica o tipo MIME dos dados do corpo da requisição.
* [[yii\web\Request::acceptableContentTypes|acceptableContentTypes]]: retorna os 
  tipos MIME acessíveis pelos usuários. Os tipos retornados são ordenados pela 
  sua pontuação de qualidade.  Tipos com mais pontuação aparecerão nas primeiras posições.
* [[yii\web\Request::acceptableLanguages|acceptableLanguages]]: retorna os idiomas 
  acessíveis pelos usuários. Os idiomas retornados são ordenados pelo nível de 
  preferência. O primeiro elemento representa o idioma de maior preferência.

Se a sua aplicação suportar diversos idiomas e quiser exibir páginas no idioma 
de maior preferência do usuário, você pode usar o método de negociação 
[[yii\web\Request::getPreferredLanguage()]]. Este método pega uma lista de 
idiomas suportadas pela sua aplicação e compara com 
[[yii\web\Request::acceptableLanguages|acceptableLanguages]], para retornar o 
idioma mais adequado.

> Dica: Você também pode utilizar o filtro [[yii\filters\ContentNegotiator|ContentNegotiator]] 
  para determinar dinamicamente qual tipo de conteúdo e idioma que deve ser utilizado 
  na resposta. O filtro implementa negociação de conteúdo em cima das propriedades 
  e métodos descritos acima.


## Informações do Cliente <span id="client-information"></span>

Você pode obter o nome do domínio ou endereço IP da máquina do cliente através 
das propriedades [[yii\web\Request::userHost|userHost]] e 
[[yii\web\Request::userIP|userIP]], respectivamente. Por exemplo,

```php
$userHost = Yii::$app->request->userHost;
$userIP = Yii::$app->request->userIP;
```
