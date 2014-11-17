ルーティングと URL 生成
=======================

Yii のアプリケーションがリクエストされた URL の処理を開始するときに、最初に実行するステップは URL を解析して
[ルート](structure-controllers.md#routes) にすることです。次に、リクエストを処理するために、このルートを使って、
対応する [コントローラアクション](structure-controllers.md) のインスタンスが作成されます。
このプロセスの全体が *ルーティング* と呼ばれます。

ルーティングの逆のプロセスが *URL 生成* と呼ばれます。これは、与えられたルートとそれに結び付いたクエリパラメータから
URL を生成するものです。生成された URL が後でリクエストされたとき、ルーティングのプロセスは、その URL を解決して、
元のルートとクエリパラメータに戻すことが出来ます。

ルーティングと URL 生成について責任を持つ主要コンポーネントが [[yii\web\UrlManager|URL マネージャ]] であり、`urlManager`
アプリケーションコンポーネントとして登録されているものです。[[yii\web\UrlManager|URL マネージャ]] は、入ってくるリクエストを
ルートとそれに結び付いたクエリパラメータとして解析するための [[yii\web\UrlManager::parseRequest()|parseRequest()]] メソッドと、
与えられたルートとそれに結び付いたクエリパラメータから URL を生成するための [[yii\web\UrlManager::createUrl()|createUrl()]]
メソッドを提供するものです。

アプリケーションコンフィギュレーションの `urlManager` コンポーネントを構成することによって、既存のアプリケーションコードを
修正することなく、任意の URL 形式をアプリケーションに認識させることが出来ます。例えば、`post/view` アクションのための URL
を生成するために、次のコードを使うことが出来ます:

```php
use yii\helpers\Url;

// Url::to() は UrlManager::createUrl() を呼び出して URL を生成します
$url = Url::to(['post/view', 'id' => 100]);
```

このコードによって生成される URL は、`urlManager` のコンフィギュレーションに応じて、下記の形式のうちの一つ (またはその他の形式)
になります。そしてまた、生成された URL が後でリクエストされたときには、解析されて元のルートとクエリパラメータに戻されます。

```
/index.php?r=post/view&id=100
/index.php/post/100
/posts/100
```


## URL 形式 <a name="url-formats"></a>

[[yii\web\UrlManager|URL マネージャ]] は二つの URL 形式をサポートします: デフォルトの URL 形式と、綺麗な URL 形式です。

既定の URL 形式は、`r` というクエリパラメータを使用してルートを表し、通常のクエリパラメータを使用してルートに結び付いたクエリパラメータを表します。
例えば、`/index.php?r=post/view&id=100` という URL は、`post/view` というルートと、`id` というクエリパラメータが 100 であることを表します。
既定の URL 形式は、[[yii\web\UrlManager|URL マネージャ]] についてのコンフィギュレーションを何も必要とせず、
ウェブサーバの設定がどのようなものでも動作します。

綺麗な URL 形式は、エントリスクリプトの名前に続く追加のパスを使用してルートとそれに結び付いたクエリパラメータを表します。
例えば、`/index.php/post/100` という URL の追加のパスは `/post/100` ですが、適切な [[yii\web\UrlManager::rules|URL 規則]]
があれば、この URL が `post/view` というルートと `id` というクエリパラメータが 100 であることを表すことが出来ます。
綺麗な URL 形式を使用するためには、URL をどのように表現すべきかという実際の要求に従って、一連の [[yii\web\UrlManager::rules|URL 規則]]
を設計する必要があります。

この二つの URL 形式は、[[yii\web\UrlManager|URL マネージャ]] の [[yii\web\UrlManager::enablePrettyUrl|enablePrettyUrl]]
プロパティを ON/OFF することによって、他のアプリケーションコードを少しも変えることなく、切り替えることが出来ます。


## ルーティング <a name="routing"></a>

ルーティングは二つのステップを含みます。最初のステップでは、入ってくるリクエストが解析されて、ルートとそれに結び付いたクエリパラメータに分解されます。そして、第二のステップでは、解析されたルートに対応する [コントローラアクション](structure-controllers.md)
がリクエストを処理するために生成されます。

既定の URL 形式を使っている場合は、リクエストからルートを解析することは、`r` という名前の `GET` クエリパラメータを取得するだけの
簡単なことです。

綺麗な URL 形式を使っている場合は、[[yii\web\UrlManager|URL マネージャ]] が登録されている [[yii\web\UrlManager::rules|URL 規則]]
を調べます。合致する規則が見つかれば、リクエストをルートに解決することが出来ます。そういう規則が見つからなかったら、
[[yii\web\NotFoundHttpException]] 例外が投げられます。

いったんリクエストからルートが解析されたら、今度はルートによって特定されるコントローラアクションを生成する番です。
ルートはその中にあるスラッシュによって複数の部分に分けられます。例えば、`site/index` は `site` と `index` に分割されます。
その各部分がモジュール、コントローラ、アクションを参照する ID となります。アプリケーションは、ルートの中の最初の部分から始めて、
下記のステップを踏んで、モジュール (もし有れば)、コントローラ、アクションを生成します。

1. アプリケーションをカレントモジュールとして設定します。
2. カレントモジュールの [[yii\base\Module::controllerMap|コントローラマップ]] がカレント ID を含むかどうかを調べます。
   もしそうであれば、マップの中で見つかったコントローラコンフィギュレーションに従ってコントローラオブジェクトが生成され、
   ルートの残りの部分を処理するために、ステップ 5 に飛びます。
3. ID がカレントモジュールの [[yii\base\Module::modules|modules]] プロパティのリストに挙げられたモジュールを指すかどうかを調べます。
   もしそうであれば、モジュールのリストで見つかったコンフィギュレーションに従ってモジュールが生成されます。そして、ステップ 2
   に戻って、新しく生成されたモジュールのコンテキストのもとで、ルートの次の部分を処理します。
4. ID をコントローラ ID として扱ってコントローラオブジェクトを生成します。そしてルートの残りの部分を持って次のステップに進みます。
5. コントローラは、[[yii\base\Controller::actions()|アクションマップ]] の中にカレント ID があるかどうかを調べます。もし有れば、
   マップの中で見つかったコンフィギュレーションに従ってアクションを生成します。もし無ければ、カレント ID
   に対応するアクションメソッドによって定義されるインラインアクションを生成しようと試みます。

上記のステップの中で、何かエラーが発生すると、[[yii\web\NotFoundHttpException]] が投げられて、
ルーティングのプロセスが失敗したことが示されます。


### デフォルトルート <a name="default-route"></a>

リクエストから解析されたルートが空っぽになった場合は、いわゆる *デフォルトルート* が代りに使用されることになります。既定では、
デフォルトルートは `site/index` であり、`site` コントローラの `index` アクションを指します。デフォルトルートは、次のように、
アプリケーションコンフィギュレーションの中でアプリケーションの [[yii\web\Application::defaultRoute|defaultRoute]]
プロパティを構成することによって、カスタマイズすることが出来ます。

```php
[
    // ...
    'defaultRoute' => 'main/index',
];
```


### `catchAll` ルート <a name="catchall-route"></a>

たまには、ウェブアプリケーションを一時的にメンテナンスモードにして、全てのリクエストに対して同じ「お知らせ」のページを表示したいことがあるでしょう。
この目的を達する方法はたくさんありますが、最も簡単な方法の一つは、次のように、
アプリケーションのコンフィギュレーションの中で [[yii\web\Application::catchAll]] プロパティを構成することです。

```php
[
    // ...
    'catchAll' => ['site/offline'],
];
```

上記のコンフィギュレーションによって、入ってくる全てのリクエストを処理するために
`site/offline` アクションが使われるようになります。

`catchAll` プロパティは配列を取り、最初の要素はルートを指定し、残りの要素 (「名前-値」のペア) は
[アクションのパラメータ](structure-controllers.md#action-parameters) を指定するものでなければなりません。


## URL を生成する <a name="creating-urls"></a>

Yii は、与えられたルートとそれに結び付けられるクエリパラメータからさまざまな URL を生成する
[[yii\helpers\Url::to()]] というヘルパーメソッドを提供しています。例えば、

```php
use yii\helpers\Url;

// ルートへの URL を生成する: /index.php?r=post/index
echo Url::to(['post/index']);

// パラメータを持つルートへの URL を生成する: /index.php?r=post/view&id=100
echo Url::to(['post/view', 'id' => 100]);

// アンカー付きの URL を生成する: /index.php?r=post/view&id=100#content
echo Url::to(['post/view', 'id' => 100, '#' => 'content']);

// 絶対 URL を生成する: http://www.example.com/index.php?r=post/index
echo Url::to(['post/index'], true);

// https スキームを使って絶対 URL を生成する: https://www.example.com/index.php?r=post/index
echo Url::to(['post/index'], 'https');
```

上記の例では、既定の URL 形式が使われていると仮定していることに注意してください。綺麗な URL
形式が有効になっている場合は、生成される URL は、使われている [[yii\web\UrlManager::rules|URL 規則]]
に従って、違うものになります。

[[yii\helpers\Url::to()]] メソッドに渡されるルートの意味は、コンテキストに依存します。ルートは
*相対* ルートか *絶対* ルートかのどちらかであり、下記の規則によって正規化されます。

- ルートが空文字列である場合は、現在リクエストされている [[yii\web\Controller::route|ルート]] が使用されます。
- ルートがスラッシュを全く含まない場合は、カレントコントローラのアクション ID であると見なされて、
  カレントコントローラの [[\yii\web\Controller::uniqueId|uniqueId]] の値が前置されます。
- ルートが先頭にスラッシュを含まない場合は、カレントモジュールに対する相対ルートと見なされて、
  カレントモジュールの [[\yii\base\Module::uniqueId|uniqueId]] の値が前置されます。

例えば、カレントモジュールが `admin` であり、カレントコントローラが `post` であると仮定すると、

```php
use yii\helpers\Url;

// 現在リクエストされているルート: /index.php?r=admin/post/index
echo Url::to(['']);

// アクション ID だけの相対ルート: /index.php?r=admin/post/index
echo Url::to(['index']);

// 相対ルート: /index.php?r=admin/post/index
echo Url::to(['post/index']);

// 絶対ルート: /index.php?r=post/index
echo Url::to(['/post/index']);
```

[[yii\helpers\Url::to()]] メソッドは、[[yii\web\UrlManager|URL マネージャ]] の
[[yii\web\UrlManager::createUrl()|createUrl()]] メソッド、および、[[yii\web\UrlManager::createAbsoluteUrl()|createAbsoluteUrl()]]
を呼び出すことによって実装されています。
次に続くいくつかの項では、[[yii\web\UrlManager|URL マネージャ]] を構成して、生成される URL
の形式をカスタマイズする方法を説明します。

[[yii\helpers\Url::to()]] メソッドは、特定のルートとの関係を持たない URL の生成もサポートしています。
その場合、最初のパラメータとして配列を渡す代りに文字列を渡さなければなりません。例えば、
 
```php
use yii\helpers\Url;

// 現在リクエストされている URL: /index.php?r=admin/post/index
echo Url::to();

// エイリアス化された URL: http://example.com
Yii::setAlias('@example', 'http://example.com/');
echo Url::to('@example');

// 絶対 URL: http://example.com/images/logo.gif
echo Url::to('/images/logo.gif', true);
```

`to()` メソッドの他にも、[[yii\helpers\Url]]` ヘルパークラスは、便利な URL 生成メソッドをいくつか提供しています。
例えば、

```php
use yii\helpers\Url;

// ホームページの URL: /index.php?r=site/index
echo Url::home();

// ベース URL。アプリケーションがウェブルートのサブディレクトリに配置されているときに便利
echo Url::base();

// 現在リクエストされている URL の canonical URL。
// https://en.wikipedia.org/wiki/Canonical_link_element を参照
echo Url::canonical();

// 現在リクエストされている URL を記憶し、それを後のリクエストの中で呼び戻す。
Url::remember();
echo Url::previous();
```


## 綺麗な URL を使う <a name="using-pretty-urls"></a>

綺麗な URL を使うためには、アプリケーションコンフィギュレーションの中で `urlManager` コンポーネントを次のように構成します。

```php
[
    'components' => [
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'enableStrictParsing' => true,
            'rules' => [
                // ...
            ],
        ],
    ],
]
```

[[yii\web\UrlManager::enablePrettyUrl|enablePrettyUrl]] プロパティは、綺麗な URL 形式の有効/無効を切り替えますので、必須です。
その他のプロパティはオプションですが、上記で示されているコンフィギュレーションが最もよく用いられているものです。

* [[yii\web\UrlManager::showScriptName|showScriptName]]: このプロパティは、エントリスクリプトを生成される URL に含めるべきかどうかを
  決定します。例えば、このプロパティを true にすると、`/index.php/post/100` という URL を生成する代りに、`/post/100` という URL
  を生成することが出来ます。
* [[yii\web\UrlManager::enableStrictParsing|enableStrictParsing]]: このプロパティは、厳密なリクエスト解析を有効にするかどうかを決定します。
  厳密な解析が有効にされた場合、リクエストされた URL が有効なリクエストとして扱われるためには、それが [[yii\web\UrlManager::rules|rules]]
  の少なくとも一つに合致しなければなりません。そうでなければ、[[yii\web\NotFoundHttpException]] が投げられます。
  厳密な解析が無効にされると、リクエストされた URL が [[yii\web\UrlManager::rules|rules]] のどれにも合致しない場合は、
  URL のパス情報の部分がリクエストされたルートとして扱われます。
* [[yii\web\UrlManager::rules|rules]]: このプロパティが URL を解析および生成するための一連の規則を含みます。
  あなたが主として作業しなければならないプロパティがこれです。このプロパティを設定することによって、
  あなたの特定のアプリケーションの要求を満たす形式の URL を生成します。

> Note: In order to hide the entry script name in the created URLs, besides setting
  [[yii\web\UrlManager::showScriptName|showScriptName]] to be true, you may also need to configure your Web server
  so that it can correctly identify which PHP script should be executed when a requested URL does not explicitly 
  specify one. If you are using Apache Web server, you may refer to the recommended configuration as described in the
  [Installation](start-installation.md#recommended-apache-configuration) section.


### URL Rules <a name="url-rules"></a>

A URL rule is an instance of [[yii\web\UrlRule]] or its child class. Each URL rule consists of a pattern used 
for matching the path info part of URLs, a route, and a few query parameters. A URL rule can be used to parse a request
if its pattern matches the requested URL; and a URL rule can be used to create a URL if its route and query parameter 
names match those that are given. 

When the pretty URL format is enabled, the [[yii\web\UrlManager|URL manager]] uses the URL rules declared in its
[[yii\web\UrlManager::rules|rules]] property to parse incoming requests and create URLs. In particular,
to parse an incoming request, the [[yii\web\UrlManager|URL manager]] examines the rules in the order they are
declared and looks for the *first* rule that matches the requested URL. The matching rule is then used to
parse the URL into a route and its associated parameters. Similarly, to create a URL, the [[yii\web\UrlManager|URL manager]] 
looks for the first rule that matches the given route and parameters and uses that to create a URL.

You can configure [[yii\web\UrlManager::rules]] as an array with keys being the patterns and values the corresponding
routes. Each pattern-route pair constructs a URL rule. For example, the following [[yii\web\UrlManager::rules|rules]]
configuration declares two URL rules. The first rule matches a URL `posts` and maps it into the route `post/index`.
The second rule matches a URL matching the regular expression `post/(\d+)` and maps it into the route `post/view` and 
a parameter named `id`.

```php
[
    'posts' => 'post/index', 
    'post/<id:\d+>' => 'post/view',
]
```

> Info: The pattern in a rule is used to match the path info part of a URL. For example, the path info of 
  `/index.php/post/100?source=ad` is `post/100` (the leading and ending slashes are ignored) which matches
  the pattern `post/(\d+)`.

Besides declaring URL rules as pattern-route pairs, you may also declare them as configuration arrays. Each configuration
array is used to configure a single URL rule object. This is often needed when you want to configure other
properties of a URL rule. For example,

```php
[
    // ...other url rules...
    
    [
        'pattern' => 'posts',
        'route' => 'post/index',
        'suffix' => '.json',
    ],
]
```

By default if you do not specify the `class` option for a rule configuration, it will take the default
class [[yii\web\UrlRule]].


### Named Parameters <a name="named-parameters"></a>

A URL rule can be associated with a few named query parameters which are specified in the pattern in the format
of `<ParamName:RegExp>`, where `ParamName` specifies the parameter name and `RegExp` is an optional regular 
expression used to match parameter values. If `RegExp` is not specified, it means the parameter value should be
a string without any slash.

> Note: You can only specify regular expressions for parameters. The rest part of a pattern is considered as plain text.

When a rule is used to parse a URL, it will fill the associated parameters with values matching the corresponding
parts of the URL, and these parameters will be made available in `$_GET` later by the `request` application component.
When the rule is used to create a URL, it will take the values of the provided parameters and insert them at the 
places where the parameters are declared.

Let's use some examples to illustrate named parameters work. Assume we have declared the following three URL rules:

```php
[
    'posts' => 'post/index',
    'post/<id:\d+>' => 'post/view',
    'posts/<year:\d{4}>/<category>' => 'post/index',
]
```

When the rules are used to parse URLs:

- `/index.php/posts` is parsed into the route `post/index` using the first rule;
- `/index.php/posts/2014/php` is parsed into the route `post/index`, the `year` parameter whose value is 2014
  and the `category` parameter whose value is `php` using the third rule;
- `/index.php/post/100` is parsed into the route `post/view` and the `id` parameter whose value is 100 using
  the second rule;
- `/index.php/posts/php` will cause a [[yii\web\NotFoundHttpException]] when [[yii\web\UrlManager::enableStrictParsing]]
  is true, because it matches none of the patterns. If [[yii\web\UrlManager::enableStrictParsing]] is false (the
  default value), the path info part `posts/php` will be returned as the route.
 
And when the rules are used to create URLs:

- `Url::to(['post/index'])` creates `/index.php/posts` using the first rule;
- `Url::to(['post/index', 'year' => 2014, 'category' => 'php'])` creates `/index.php/posts/2014/php` using the
  third rule;
- `Url::to(['post/view', 'id' => 100])` creates `/index.php/post/100` using the second rule;
- `Url::to(['post/view', 'id' => 100, 'source' => 'ad'])` creates `/index.php/post/100?source=ad` using the second rule.
  Because the `source` parameter is not specified in the rule, it is appended as a query parameter in the created URL.
- `Url::to(['post/index', 'category' => 'php'])` creates `/index.php/post/index?category=php` using none of rules.
  Note that since none of the rules applies, the URL is created by simply appending the route as the path info
  and all parameters as the query string part.
   

### Parameterizing Routes <a name="parameterizing-routes"></a>

You can embed parameter names in the route of a URL rule. This allows a URL rule to be used for matching multiple 
routes. For example, the following rules embed `controller` and `action` parameters in the routes.

```php
[
    '<controller:(post|comment)>/<id:\d+>/<action:(create|update|delete)>' => '<controller>/<action>',
    '<controller:(post|comment)>/<id:\d+>' => '<controller>/view',
    '<controller:(post|comment)>s' => '<controller>/index',
]
```

To parse a URL `/index.php/comment/100/create`, the first rule will apply, which sets the `controller` parameter to
be `comment` and `action` parameter to be `create`. The route `<controller>/<action>` is thus resolved as `comment/create`.
 
Similarly, to create a URL for the route `comment/index`, the third rule will apply, which creates a URL `/index.php/comments`.

> Info: By parameterizing routes, it is possible to greatly reduce the number of URL rules, which can significantly
  improve the performance of [[yii\web\UrlManager|URL manager]]. 
  
By default, all parameters declared in a rule are required. If a requested URL does not contain a particular parameter, 
or if a URL is being created without a particular parameter, the rule will not apply. To make some of the parameters
optional, you can configure the [[yii\web\UrlRule::defaults|defaults]] property of a rule. Parameters listed in this 
property are optional and will take the specified values when they are not provided. 

In the following rule declaration, the `page` and `tag` parameters are both optional and will take the value of 1 and 
empty string, respectively, when they are not provided. 

```php
[
    // ...other rules...
    [
        'pattern' => 'posts/<page:\d+>/<tag>',
        'route' => 'post/index',
        'defaults' => ['page' => 1, 'tag' => ''],
    ],
]
```

The above rule can be used to parse or create any of the following URLs:

* `/index.php/posts`: `page` is 1, `tag` is ''.
* `/index.php/posts/2`: `page` is 2, `tag` is ''.
* `/index.php/posts/2/news`: `page` is 2, `tag` is `'news'`.
* `/index.php/posts/news`: `page` is 1, `tag` is `'news'`.

Without using optional parameters, you would have to create 4 rules to achieve the same result.


### Rules with Server Names <a name="rules-with-server-names"></a>

It is possible to include Web server names in the patterns of URL rules. This is mainly useful when your application 
should behave differently for different Web server names. For example, the following rules will parse the URL 
`http://admin.example.com/login` into the route `admin/user/login` and `http://www.example.com/login` into `site/login`.

```php
[
    'http://admin.example.com/login' => 'admin/user/login',
    'http://www.example.com/login' => 'site/login',
]
```

You can also embed parameters in the server names to extract dynamic information from them. For example, the following rule
will parse the URL `http://en.example.com/posts` into the route `post/index` and the parameter `language=en`.

```php
[
    'http://<language:\w+>.example.com/posts' => 'post/index',
]
```

> Note: Rules with server names should NOT include subfolder of the entry script in their patterns. For example, if 
  the application is under `http://www.example.com/sandbox/blog`, then you should use the pattern
  `http://www.example.com/posts` instead of `http://www.example.com/sandbox/blog/posts`. This will allow your application
  to be deployed under any directory without the need to change your application code.


### URL Suffixes <a name="url-suffixes"></a>

You may want to add suffixes to the URLs for various purposes. For example, you may add `.html` to the URLs so that they
look like URLs for static HTML pages; you may also add `.json` to the URLs to indicate that the expected content type
of the response to the URLs. You can achieve this goal by configuring the [[yii\web\UrlManager::suffix]] property like
the following in the application configuration:

```php
[
    'components' => [
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'enableStrictParsing' => true,
            'suffix' => '.html',
            'rules' => [
                // ...
            ],
        ],
    ],
]
```

The above configuration will let the [[yii\web\UrlManager|URL manager]] to recognize requested URLs and also create
URLs with `.html` as their suffix.

> Tip: You may set `/` as URL suffix so that the URLs are all ended with a slash.

> Note: When you configure a URL suffix, if a requested URL does not have the suffix, it will be considered as
  an unrecognized URL. This is a recommended practice for SEO purpose.
  
Sometimes you may want to use different suffixes for different URLs. This can be achieved by configuring the
[[yii\web\UrlRule::suffix|suffix]] property of individual URL rules. When a URL rule has this property set, it will
override the suffix setting at the [[yii\web\UrlManager|URL manager]] level. For example, the following configuration
contains a customized URL rule which uses `.json` as its suffix instead of the global one `.html`.

```php
[
    'components' => [
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'enableStrictParsing' => true,
            'suffix' => '.html',
            'rules' => [
                // ...
                [
                    'pattern' => 'posts',
                    'route' => 'post/index',
                    'suffix' => '.json',
                ],
            ],
        ],
    ],
]
```


### HTTP Methods <a name="http-methods"></a>

When implementing RESTful APIs, it is commonly needed that the same URL be parsed into different routes according to
the HTTP methods being used. This can be easily achieved by prefixing the supported HTTP methods to the patterns of
the rules. If a rule supports multiple HTTP methods, separate the method names with commas. For example, the following
rules have the same pattern `post/<id:\d+>` with different HTTP method support. A request for `PUT post/100` will
be parsed into `post/create`, while a request for `GET post/100` will be parsed into `post/view`.

```php
[
    'PUT,POST post/<id:\d+>' => 'post/create',
    'DELETE post/<id:\d+>' => 'post/delete',
    'post/<id:\d+>' => 'post/view',
]
```

> Note: If a URL rule contains HTTP method(s) in its pattern, the rule will only be used for parsing purpose.
  It will be skipped when the [[yii\web\UrlManager|URL manager]] is called to create URLs.

> Tip: To simplify the routing of RESTful APIs, Yii provides a special URL rule class [[yii\rest\UrlRule]]
  which is very efficient and supports some fancy features such as automatic pluralization of controller IDs.
  For more details, please refer to the [Routing](rest-routing.md) section about developing RESTful APIs.


### Customizing Rules <a name="customizing-rules"></a>

In the previous examples, URL rules are mainly declared in terms of pattern-route pairs. This is a commonly used
shortcut format. In certain scenarios, you may want to customize a URL rule by configuring its other properties, such
as [[yii\web\UrlRule::suffix]]. This can be done by using a full configuration array to specify a rule. The following
example is extracted from the [URL Suffixes](#url-suffixes) subsection,

```php
[
    // ...other url rules...
    
    [
        'pattern' => 'posts',
        'route' => 'post/index',
        'suffix' => '.json',
    ],
]
```

> Info: By default if you do not specify the `class` option for a rule configuration, it will take the default
  class [[yii\web\UrlRule]].
  

### Adding Rules Dynamically <a name="adding-rules"></a>

URL rules can be dynamically added to the [[yii\web\UrlManager|URL manager]]. This is often needed by redistributable 
[modules](structure-modules.md) which want to manage their own URL rules. In order for the dynamically added rules
to take effect during the routing process, you should add them during the [bootstrapping](runtime-bootstrapping.md)
stage. For modules, this means they should implement [[yii\base\BootstrapInterface]] and add the rules in the
[[yii\base\BootstrapInterface::bootstrap()|bootstrap()]] method like the following:

```php
public function bootstrap($app)
{
    $app->getUrlManager()->addRules([
        // rule declarations here
    ], false);
}
```

Note that you should also list these modules in [[yii\web\Application::bootstrap]] so that they can participate the
[bootstrapping](runtime-bootstrapping.md) process.


### Creating Rule Classes <a name="creating-rules"></a>

Despite the fact that the default [[yii\web\UrlRule]] class is flexible enough for the majority of projects, there 
are situations when you have to create your own rule classes. For example, in a car dealer Web site, you may want 
to support the URL format like `/Manufacturer/Model`, where both `Manufacturer` and `Model` must match some data
stored in a database table. The default rule class will not work here because it relies on statically declared patterns.

We can create the following URL rule class to solve this problem.

```php
namespace app\components;

use yii\web\UrlRule;

class CarUrlRule extends UrlRule
{
    public $db = 'db';

    public function init()
    {
        parent::init();
        if ($this->name === null) {
            $this->name = __CLASS__;
        }
    }

    public function createUrl($manager, $route, $params)
    {
        if ($route === 'car/index') {
            if (isset($params['manufacturer'], $params['model'])) {
                return $params['manufacturer'] . '/' . $params['model'];
            } elseif (isset($params['manufacturer'])) {
                return $params['manufacturer'];
            }
        }
        return false;  // this rule does not apply
    }

    public function parseRequest($manager, $request)
    {
        $pathInfo = $request->getPathInfo();
        if (preg_match('%^(\w+)(/(\w+))?$%', $pathInfo, $matches)) {
            // check $matches[1] and $matches[3] to see
            // if they match a manufacturer and a model in the database
            // If so, set $params['manufacturer'] and/or $params['model']
            // and return ['car/index', $params]
        }
        return false;  // this rule does not apply
    }
}
```

And use the new rule class in the [[yii\web\UrlManager::rules]] configuration:

```php
[
    // ...other rules...
    
    [
        'class' => 'app\components\CarUrlRule', 
        // ...configure other properties...
    ],
]
```


## Performance Consideration <a name="performance-consideration"></a>

When developing a complex Web application, it is important to optimize URL rules so that it takes less time to parse
requests and create URLs.

By using parameterized routes, you may reduce the number of URL rules, which can significantly improve the performance.

When parsing or creating URLs, [[yii\web\UrlManager|URL manager]] examines URL rules in the order they are declared.
Therefore, you may consider adjusting the order of the URL rules so that more commonly used rules are placed before
less used ones.

If some URL rules share the same prefix in their patterns or routes, you may consider using [[yii\web\GroupUrlRule]]
so that they can be more efficiently examined by [[yii\web\UrlManager|URL manager]] as a group. This is often the case
when your application is composed by modules, each having its own set of URL rules with module ID as their common prefixes.
