Url Helper
==========

Url helper fornece um conjunto de métodos estáticos para o gerenciamento de URLs.


## Obtendo URLs comuns <span id="getting-common-urls"></span>

Há dois métodos que você pode usar para obter URLs comuns: URL home e URL base para a requisição corrente. Para obter a URL home, use o seguinte:

```php
$relativeHomeUrl = Url::home();
$absoluteHomeUrl = Url::home(true);
$httpsAbsoluteHomeUrl = Url::home('https');
```

Se nenhum parâmetro for passado, a URL gerada é relativa. Você pode passar `true` para obter uma URL absoluta para a o esquema corrente ou especificar um esquema explicitamente (`https`, `http`).

Para obter a URL base da requisição corrente, use o seguinte :
 
```php
$relativeBaseUrl = Url::base();
$absoluteBaseUrl = Url::base(true);
$httpsAbsoluteBaseUrl = Url::base('https');
```

O único parâmetro do metódo funciona exatamente da mesma que em  `Url::home()`.


## Criando URLs <span id="creating-urls"></span>

Afim de criar ma URL para uma rota utilize o metódo `Url::toRoute()`. O metódo usa [[\yii\web\UrlManager]] para criar a URL:

```php
$url = Url::toRoute(['product/view', 'id' => 42]);
```
 
Você pode especificar uma URL como string, e.g., `site/index`. Você pode também usar um array se você precisa especificar parâmetros adicionais para a URL a ser criada. O formato do array deve ser:

```php
// generates: /index.php?r=site/index&param1=value1&param2=value2
['site/index', 'param1' => 'value1', 'param2' => 'value2']
```

Se você quiser criar uma URL como uma ancôra (anchor), você pode usar no array com o parâmetro `#`. Por exemplo,

```php
// generates: /index.php?r=site/index&param1=value1#name
['site/index', 'param1' => 'value1', '#' => 'name']
```

Uma rota pode ser absoluta ou relativa. An absolute route has a leading slash (e.g. `/site/index`) while a relative
route has none (e.g. `site/index` or `index`). A relative route will be converted into an absolute one by the following rules:

- If the route is an empty string, the current [[\yii\web\Controller::route|route]] will be used;
- If the route contains no slashes at all (e.g. `index`), it is considered to be an action ID of the current controller
  and will be prepended with [[\yii\web\Controller::uniqueId]];
- If the route has no leading slash (e.g. `site/index`), it is considered to be a route relative to the current module
  and will be prepended with the module's [[\yii\base\Module::uniqueId|uniqueId]].
  
Starting from version 2.0.2, you may specify a route in terms of an [alias](concept-aliases.md). If this is the case,
the alias will first be converted into the actual route which will then be turned into an absolute route according
to the above rules.

Below are some examples of using this method:

```php
// /index.php?r=site/index
echo Url::toRoute('site/index');

// /index.php?r=site/index&src=ref1#name
echo Url::toRoute(['site/index', 'src' => 'ref1', '#' => 'name']);

// /index.php?r=post/edit&id=100     assume the alias "@postEdit" is defined as "post/edit"
echo Url::toRoute(['@postEdit', 'id' => 100]);

// http://www.example.com/index.php?r=site/index
echo Url::toRoute('site/index', true);

// https://www.example.com/index.php?r=site/index
echo Url::toRoute('site/index', 'https');
```

There's another method `Url::to()` that is very similar to [[toRoute()]]. The only difference is that this method
requires a route to be specified as an array only. If a string is given, it will be treated as a URL.

The first argument could be:
         
- an array: [[toRoute()]] will be called to generate the URL. For example:
  `['site/index']`, `['post/index', 'page' => 2]`. Please refer to [[toRoute()]] for more details
  on how to specify a route.
- a string with a leading `@`: it is treated as an alias, and the corresponding aliased string
  will be returned.
- an empty string: the currently requested URL will be returned;
- a normal string: it will be returned as is.

When `$scheme` is specified (either a string or true), an absolute URL with host info (obtained from
[[\yii\web\UrlManager::hostInfo]]) will be returned. If `$url` is already an absolute URL, its scheme
will be replaced with the specified one.

Below are some usage examples:

```php
// /index.php?r=site/index
echo Url::to(['site/index']);

// /index.php?r=site/index&src=ref1#name
echo Url::to(['site/index', 'src' => 'ref1', '#' => 'name']);

// /index.php?r=post/edit&id=100     assume the alias "@postEdit" is defined as "post/edit"
echo Url::to(['@postEdit', 'id' => 100]);

// the currently requested URL
echo Url::to();

// /images/logo.gif
echo Url::to('@web/images/logo.gif');

// images/logo.gif
echo Url::to('images/logo.gif');

// http://www.example.com/images/logo.gif
echo Url::to('@web/images/logo.gif', true);

// https://www.example.com/images/logo.gif
echo Url::to('@web/images/logo.gif', 'https');
```

Starting from version 2.0.3, you may use [[yii\helpers\Url::current()]] to create a URL based on the currently
requested route and GET parameters. You may modify or remove some of the GET parameters or add new ones by
passing a `$params` parameter to the method. For example,

```php
// assume $_GET = ['id' => 123, 'src' => 'google'], current route is "post/view"

// /index.php?r=post/view&id=123&src=google
echo Url::current();

// /index.php?r=post/view&id=123
echo Url::current(['src' => null]);
// /index.php?r=post/view&id=100&src=google
echo Url::current(['id' => 100]);
```


## Remember URLs <span id="remember-urls"></span>

There are cases when you need to remember URL and afterwards use it during processing of the one of sequential requests.
It can be achieved in the following way:
 
```php
// Remember current URL 
Url::remember();

// Remember URL specified. See Url::to() for argument format.
Url::remember(['product/view', 'id' => 42]);

// Remember URL specified with a name given
Url::remember(['product/view', 'id' => 42], 'product');
```

In the next request we can get URL remembered in the following way:

```php
$url = Url::previous();
$productUrl = Url::previous('product');
```
                        
## Checking Relative URLs <span id="checking-relative-urls"></span>

To find out if URL is relative i.e. it doesn't have host info part, you can use the following code:
                             
```php
$isRelative = Url::isRelative('test/it');
```
