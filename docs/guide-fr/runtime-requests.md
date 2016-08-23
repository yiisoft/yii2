Requêtes
========

Les requêtes faites à l'application sont représentées en terme d'objets [[yii\web\Request]] qui fournissent des informations telles que les paramètres de requête, les entêtes HTTP, les cookies, etc. Pour une requête donnée, vous avez accès au [composant d'application](structure-application-components.md)`request` qui, par défaut,  est une instance de [[yii\web\Request]]. Dans cette section, nous décrivons comment utiliser ce composant dans vos applications.


## Paramètres de requête <span id="request-parameters"></span>

Pour obtenir les paramètres de requête, vous pouvez appeler les méthodes  [[yii\web\Request::get()|get()]] et [[yii\web\Request::post()|post()]] du composant `request` component. Elles retournent les valeurs de `$_GET` et `$_POST`, respectivement. Pas exemple :

```php
$request = Yii::$app->request;

$get = $request->get(); 
// équivalent à : $get = $_GET;

$id = $request->get('id');   
// équivalent à : $id = isset($_GET['id']) ? $_GET['id'] : null;

$id = $request->get('id', 1);   
// équivalent à : $id = isset($_GET['id']) ? $_GET['id'] : 1;

$post = $request->post(); 
// équivalent à : $post = $_POST;

$name = $request->post('name');   
// equivalent to: $name = isset($_POST['name']) ? $_POST['name'] : null;

$name = $request->post('name', '');   
// équivalent à : $name = isset($_POST['name']) ? $_POST['name'] : '';
```

> Info: plutôt que d'accéder directement à `$_GET` et `$_POST` pour récupérer les paramètres de requête, il est recommandé de les obtenir via le composant `request` comme indiqué ci-dessus. Cela rend l'écriture des tests plus facile parce que vous pouvez créer un simulacre de composant request avec des données de requête factices.  

Lorsque vous mettez en œuvre des [API pleinement REST](rest-quick-start.md), vous avez souvent besoin de récupérer les paramètres qui sont soumis via les [méthodes de requête](#request-methods) PUT, PATCH ou autre . Vous pouvez obtenir ces paramètres en appelant la méthode [[yii\web\Request::getBodyParam()]]. par exemple : 

```php
$request = Yii::$app->request;

// retourne tous les paramètres
$params = $request->bodyParams;

// retourne le paramètre  "id"
$param = $request->getBodyParam('id');
```

> Info: à la différence des paramètres  de `GET`, les paramètres soumis via `POST`, `PUT`, `PATCH` etc. sont envoyés dans le corps de la requête. Le composant `request` analyse ces paramètres lorsque vous y accédez via les méthodes décrites ci-dessus. Vous pouvez personnaliser la manière dont ces paramètres sont analysés en configurant la propriété [[yii\web\Request::parsers]].
  

## Méthodes de requête <span id="request-methods"></span>

Vous pouvez obtenir la méthode HTTP utilisée par la requête courante via l'expression `Yii::$app->request->method`. Un jeu entier de propriétés booléennes est également fourni pour que vous puissiez déterminer le type de la méthode courante. Par exemple :
For example,

```php
$request = Yii::$app->request;

if ($request->isAjax) { /* la méthode de requête est requête AJAX */ }
if ($request->isGet)  { /* la méthode de requête est requête GET */ }
if ($request->isPost) { /* la méthode de requête est requête POST */ }
if ($request->isPut)  { /* la méthode de requête est requête PUT */ }
```

## URL de requête <span id="request-urls"></span>

Le composant `request` fournit plusieurs manières d'inspecter l'URL couramment requise.

En supposant que l'URL requise soit `http://example.com/admin/index.php/product?id=100`, vous pouvez obtenir différentes parties de cette URL comme c'est résumé ci-dessous :

* [[yii\web\Request::url|url]]: retourne`/admin/index.php/product?id=100`, qui est l'URL sans la partie hôte. 
* [[yii\web\Request::absoluteUrl|absoluteUrl]]: retourne `http://example.com/admin/index.php/product?id=100`, qui est l'URL complète y compris la partie hôte.
* [[yii\web\Request::hostInfo|hostInfo]]: retourne `http://example.com`, qui est la partie hôte de l'URL.
* [[yii\web\Request::pathInfo|pathInfo]]: retourne `/product`, qui est la partie après le script d'entrée et avant le point d'interrogation (chaîne de requête).
* [[yii\web\Request::queryString|queryString]]: retourne `id=100`, qui est la partie après le point d'interrogation.
* [[yii\web\Request::baseUrl|baseUrl]]: retourne `/admin`, qui est la partie après l'hôte et avant le nom du script d'entrée. 
* [[yii\web\Request::scriptUrl|scriptUrl]]: retourne `/admin/index.php`, qui set l'URL sans le chemin et la chaîne de requête. 
* [[yii\web\Request::serverName|serverName]]: retourne `example.com`, qui est le nom d'hôte dans l'URL.
* [[yii\web\Request::serverPort|serverPort]]: retourne 80, qui est le numéro de port utilisé par le serveur  Web.


## Enntêtes HTTP  <span id="http-headers"></span> 

Vous pouvez obtenir les entêtes HTTP via la [[yii\web\HeaderCollection|collection d'entêtes]] qui est retournée par la propriété [[yii\web\Request::headers]]. Par exemple :

```php
// $headers est un objet   yii\web\HeaderCollection 
$headers = Yii::$app->request->headers;

// retourne la valeur de l'entête  Accept
$accept = $headers->get('Accept');

if ($headers->has('User-Agent')) { /* il existe un entête User-Agent  */ }
```

Le composant `request` fournit aussi la prise en charge de l'accès rapide à quelques entêtes couramment utilisés. Cela inclut :

* [[yii\web\Request::userAgent|userAgent]]: retourne la valeur de l'entête  `User-Agent`.
* [[yii\web\Request::contentType|contentType]]: retourne la valeur de l'entête `Content-Type` qui indique le type MIME des données dans le corps de la requête. 
* [[yii\web\Request::acceptableContentTypes|acceptableContentTypes]]: retourne les types MIME acceptés par l'utilisateur. Les types retournés sont classés par ordre de score de qualité. Les types avec les plus hauts scores sont retournés en premier. 
* [[yii\web\Request::acceptableLanguages|acceptableLanguages]]: retourne les langues acceptées par l'utilisateur. Les langues retournées sont classées par niveau de préférence. Le premier élément représente la langue préférée. Si votre application prend en charge plusieurs langues et que vous voulez afficher des pages dans la langue préférée de l'utilisateur, vous pouvez utiliser la méthode de négociation de la langue [[yii\web\Request::getPreferredLanguage()]].
Cette méthode accepte une liste des langues prises en charge par votre application, la compare avec les [[yii\web\Request::acceptableLanguages (langues acceptées)|acceptableLanguages]], et retourne la langue la plus appropriée. 

> Tip: vous pouvez également utiliser le filtre [[yii\filters\ContentNegotiator|ContentNegotiator]] pour déterminer dynamiquement quel type de contenu et quelle langue utiliser dans la réponse. Le filtre met en œuvre la négociation de contenu en plus des propriétés et  méthodes décrites ci-dessus. 


## Informations sur le client <span id="client-information"></span>

Vous pouvez obtenir le nom d'hôte et l'adresse IP de la machine cliente via  [[yii\web\Request::userHost|userHost]] et [[yii\web\Request::userIP|userIP]], respectivement. Par exemple :

```php
$userHost = Yii::$app->request->userHost;
$userIP = Yii::$app->request->userIP;
```
