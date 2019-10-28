URL Helper
==========

URL helper fornece um conjunto de métodos estáticos para o gerenciamento de URLs.


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
 
Você pode especificar uma URL como string, ou seja, `site/index`. Você pode também usar um array se você precisa especificar parâmetros adicionais para a URL a ser criada. O formato do array deve ser:

```php
// generates: /index.php?r=site/index&param1=value1&param2=value2
['site/index', 'param1' => 'value1', 'param2' => 'value2']
```

Se você quiser criar uma URL com uma ancôra (anchor), você pode usar no array o parâmetro `#`. Por exemplo,

```php
// generates: /index.php?r=site/index&param1=value1#name
['site/index', 'param1' => 'value1', '#' => 'name']
```

Uma rota pode ser absoluta ou relativa. Uma rota absoluta tem uma barra inicial (e.g. `/site/index`) enquanto uma rota relativa não (e.g. `site/index` or `index`). Uma rota relativa pode ser convertida em absoluta seguinda as seguintes regras:

- Se a rota é uma string vazia, será usada a corrente rota [[\yii\web\Controller::route|route]];
- Se a rota não contém nenhuma barra (e.g. `index`), considera-se ser o ID da acão corrente do controlador;
  e serão precedidas por [[\yii\web\Controller::uniqueId]];
- Se a rota não tem uma barra inicial (e.g. `site/index`), isto é considerado um URL relativa para para o modulo corrente
  e será precedido por [[\yii\base\Module::uniqueId|uniqueId]].
  
A partir da versão 2.0.2, você pode espeficiar uma rota como um [alias](concept-aliases.md). Se esse é o caso,
o alias será primeiro convertido para rota atual que irá então ser transformado em uma rota absoluta de acordo
às regras acima .

Abaixo estão alguns exemplos de como usar este método:

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

Há um outro método `Url::to()` que é muito semelhante a [[toRoute()]]. A única diferença é que este método requer uma rota a ser especificado como apenas como array. Se for dado uma string, ela será tratada como um URL.

O primeiro argumento pode ser:
         
- um array: [[toRoute()]]  irá ser chamado para gerar a URL. Por exemplo:
  `['site/index']`, `['post/index', 'page' => 2]`. Por favor consulte [[toRoute()]] para mais detalhes de como especificar uma rota.
- uma string com inicio `@`: ele é tratado como um alias, e as strings correspondentes ao alias serão devolvidos.
- uma string vazia: A URL da requisição corrente será retornado;
- uma string normal: será devolvida como ele foi passada (como ela é).

Quando `$scheme` é espefificado (como uma string ou `true`),uma URL absoluta com informações do host (obtida de
[[\yii\web\UrlManager::hostInfo]]) será retornada. Se `$url` já é uma URL absoluta, seu scheme
irá ser substituído pelo o especificado.

Abaixo estão alguns exemplos de uso:

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

A partir da versão 2.0.3, você pode usar [[yii\helpers\Url::current()]] para criar uma URL base para rota solicitada e parâmetros GET. Você pode modificar ou remover alguns dos parâmetros GET ou adicionar novos por
passando o parâmetro `$ params` para o método. Por exemplo,

```php
// assume $_GET = ['id' => 123, 'src' => 'google'], current route is "post/view"

// /index.php?r=post/view&id=123&src=google
echo Url::current();

// /index.php?r=post/view&id=123
echo Url::current(['src' => null]);
// /index.php?r=post/view&id=100&src=google
echo Url::current(['id' => 100]);
```


## Relembrar URLs <span id="remember-urls"></span>

Há casos em que você precisa se lembrar URL e depois usá-lo durante o processamento de uma das requisições sequenciais.
Pode ser conseguida da seguinte forma:
 
```php
// Remember current URL 
Url::remember();

// Remember URL specified. See Url::to() for argument format.
Url::remember(['product/view', 'id' => 42]);

// Remember URL specified with a name given
Url::remember(['product/view', 'id' => 42], 'product');
```

Na próxima requisição, podemos obter URL lembrada da seguinte forma:

```php
$url = Url::previous();
$productUrl = Url::previous('product');
```
                        
## Verificar URLs relativas <span id="checking-relative-urls"></span>

Para saber se a URL é relativa, ou seja, ele não tem informações do host, você pode usar o seguinte código:
                             
```php
$isRelative = Url::isRelative('test/it');
```
