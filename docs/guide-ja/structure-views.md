ビュー
======

ビューは [MVC](http://ja.wikipedia.org/wiki/Model_View_Controller) アーキテクチャの一部を成すものです。
ビューはエンドユーザにデータを表示することに責任を持つコードです。
ウェブアプリケーションにおいては、ビューは、通常、主として HTML コードと表示目的の PHP コードを含む PHP スクリプトファイルである、
*ビューテンプレート* の形式で作成されます。
そして、ビューテンプレートを管理する [[yii\web\View|ビュー]] [アプリケーションコンポーネント](structure-application-components.md) は、
ビューの構築とレンダリングを助けるためによく使われるメソッドを提供します。
なお、簡潔さを重視して、ビューテンプレートまたはビューテンプレートファイルを単にビューと呼ぶことがよくあります。


## ビューを作成する <a name="creating-views"></a>

前述のように、ビューは HTML と PHP コードが混ざった単なる PHP スクリプトです。
次に示すのは、ログインフォームを表示するビューです。
見ると分るように、PHP コードがタイトルやフォームなど動的なコンテンツを生成するのに使われ、HTML コードがそれらを編成して表示可能な HTML ページを作っています。

```php
<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $form yii\widgets\ActiveForm */
/* @var $model app\models\LoginForm */

$this->title = 'ログイン';
?>
<h1><?= Html::encode($this->title) ?></h1>

<p>次の項目を入力してログインしてください:</p>

<?php $form = ActiveForm::begin(); ?>
    <?= $form->field($model, 'username') ?>
    <?= $form->field($model, 'password')->passwordInput() ?>
    <?= Html::submitButton('ログイン') ?>
<?php ActiveForm::end(); ?>
```

ビューの中でアクセスできる `$this` は、このビューテンプレートを管理し表示している [[yii\web\View|ビューコンポーネント]] を参照します。

`$this` 以外に、上記の例の `$model` のように、前もって定義された変数がビューの中にあることがあります。
このような変数は、[コントローラ](structure-controllers.md) または [ビューのレンダリング](#rendering-views) をトリガするオブジェクトによってビューに *プッシュ* されたデータを表します。

> Tip|ヒント: 上の例では、事前に定義された変数は、IDE に認識されるように、
  ビューの先頭のコメントブロックの中にリストされています。これは、ビューに
  ドキュメントを加えるためにも良い方法です。


### セキュリティ <a name="security"></a>

HTML ページを生成するビューを作成するときは、エンドユーザから受け取るデータを
表示する前に エンコード および/または フィルター することが重要です。
そうしなければ、あなたのアプリケーションは [クロスサイトスクリプティング](http://ja.wikipedia.org/wiki/%E3%82%AF%E3%83%AD%E3%82%B9%E3%82%B5%E3%82%A4%E3%83%88%E3%82%B9%E3%82%AF%E3%83%AA%E3%83%97%E3%83%86%E3%82%A3%E3%83%B3%E3%82%B0) 攻撃をこうむるおそれがあります。

平文テキストを表示するためには、まず [[yii\helpers\Html::encode()]] を呼んでエンコードします。
例えば、次のコードはユーザの名前を表示する前にエンコードしています:

```php
<?php
use yii\helpers\Html;
?>

<div class="username">
    <?= Html::encode($user->name) ?>
</div>
```

HTML コンテンツを表示するためには、[[yii\helpers\HtmlPurifier]] を使って、最初にコンテンツをフィルターします。
例えば、次のコードは、投稿のコンテンツを表示する前にフィルターしています:

```php
<?php
use yii\helpers\HtmlPurifier;
?>

<div class="post">
    <?= HtmlPurifier::process($post->text) ?>
</div>
```

> Tip|ヒント: HTMLPurifier は、出力を安全なものにすることにおいては素晴らしい仕事をしますが、
  速くはありません。アプリケーションが高いパフォーマンスを要求する場合は、
  フィルター結果を [キャッシュ](caching-overview.md) することを考慮すべきです。


### ビューを整理する <a name="organizing-views"></a>

[コントローラ](structure-controllers.md) や [モデル](structure-models.md) と同じように、
ビューを整理するための規約があります。.

* コントローラによって表示されるビューは、既定では、ディレクトリ
  `@app/views/ControllerID` の下に置かれるべきものです。
  ここで、`ControllerID` は [コントローラ ID](structure-controllers.md#routes) を指します。
  例えば、コントローラクラスが `PostController` である場合、ディレクトリは `@app/views/post`
  となります。`PostCommentController` の場合は、ディレクトリは `@app/views/post-comment` です。
  また、コントローラがモジュールに属する場合は、ディレクトリは [[yii\base\Module::basePath|モジュールディレクトリ]]
  の下の `views/ControllerID` です。
* [ウィジェット](structure-widgets.md) で表示されるビューは、既定では、`WidgetPath/views`
  ディレクトリの下に置かれるべきものです。ここで、`WidgetPath` は、ウィジェットのクラスファイル
  を含んでいるディレクトリを指します。
* 他のオブジェクトによって表示されるビューについても、ウィジェットの場合と規約に従うことが
  推奨されます。

これらの既定のビューディレクトリは、コントローラやウィジェットの [[yii\base\ViewContextInterface::getViewPath()]]
メソッドをオーバーライドすることでカスタマイズすることが可能です。


## ビューをレンダリングする <a name="rendering-views"></a>

[コントローラ](structure-controllers.md) の中でも、[ウィジェット](structure-widgets.md) の中でも、
または、その他のどんな場所でも、ビューのレンダリングメソッドを呼ぶことによって
ビューをレンダリングすることが出来ます。
これらのメソッドは、下記に示されるような類似のシグニチャを共有します。

```
/**
 * @param string $view ビュー名またはファイルパス、実際のレンダリングメソッドに依存する
 * @param array $params ビューに引き渡されるデータ
 * @return string レンダリングの結果
 */
methodName($view, $params = [])
```


### コントローラでのレンダリング <a name="rendering-in-controllers"></a>

[コントローラ](structure-controllers.md) の中では、ビューをレンダリングするために
次のコントローラメソッドを呼ぶことが出来ます:

* [[yii\base\Controller::render()|render()]]: [名前付きビュー](#named-views) をレンダリングし、
  その結果に [レイアウト](#layouts) を適用する。
* [[yii\base\Controller::renderPartial()|renderPartial()]]: [名前付きビュー](#named-views) を
  レイアウトなしでレンダリングする。
* [[yii\web\Controller::renderAjax()|renderAjax()]]: [名前付きビュー](#named-views) を
  レイアウトなしでレンダリングし、登録されている全ての JS/CSS スクリプトおよびファイルを注入する。
  通常、AJAX ウェブリクエストに対するレスポンスにおいて使用される。
* [[yii\base\Controller::renderFile()|renderFile()]]: ビューファイルのパスまたは [エイリアス](concept-aliases.md)
  の形式で指定されたビューをレンダリングする。

例えば、

```php
namespace app\controllers;

use Yii;
use app\models\Post;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

class PostController extends Controller
{
    public function actionView($id)
    {
        $model = Post::findOne($id);
        if ($model === null) {
            throw new NotFoundHttpException;
        }

        // "view" という名前のビューをレンダリングし、レイアウトを適用する
        return $this->render('view', [
            'model' => $model,
        ]);
    }
}
```


### ウィジェットでのレンダリング <a name="rendering-in-widgets"></a>

[ウィジェット](structure-widgets.md) の中では、ビューをレンダリングするために、
次のウィジェットメソッドを使用することが出来ます。

* [[yii\base\Widget::render()|render()]]: [名前付きのビュー](#named-views) をレンダリングする。
* [[yii\base\Widget::renderFile()|renderFile()]]: ビューファイルのパスまたは [エイリアス](concept-aliases.md)
  の形式で指定されたビューをレンダリングする。

例えば、

```php
namespace app\components;

use yii\base\Widget;
use yii\helpers\Html;

class ListWidget extends Widget
{
    public $items = [];

    public function run()
    {
        // "list" という名前のビューをレンダリングする
        return $this->render('list', [
            'items' => $this->items,
        ]);
    }
}
```


### ビューでのレンダリング <a name="rendering-in-views"></a>

[[yii\base\View|ビューコンポーネント]] によって提供される下記のメソッドのどれかを使うと、
ビューの中で、別のビューをレンダリングすることが出来ます:

* [[yii\base\View::render()|render()]]: [名前付きのビュー](#named-views) をレンダリングする。
* [[yii\web\View::renderAjax()|renderAjax()]]: [名前付きビュー](#named-views) をレンダリングし、
  登録されている全ての JS/CSS スクリプトおよびファイルを注入する。
  通常、AJAX ウェブリクエストに対するレスポンスにおいて使用される。
* [[yii\base\View::renderFile()|renderFile()]]: ビューファイルのパスまたは [エイリアス](concept-aliases.md)
  の形式で指定されたビューをレンダリングする。

例えば、ビューの中の次のコードは、現在レンダリングされているビューと同じディレクトリにある
`_overview.php` というビューファイルをレンダリングします。
ビューでは `$this` が [[yii\base\View|ビュー]] コンポーネントを参照することを思い出してください:

```php
<?= $this->render('_overview') ?>
```


### 他の場所でのレンダリング <a name="rendering-in-other-places"></a>

場所がどこであれ、`Yii::$app->view` という式によって [[yii\base\View|ビュー]] アプリケーションコンポーネントにアクセスすることが出来ますから、
前述の [[yii\base\View|ビュー]] コンポーネントメソッドを使ってビューをレンダリングすることが出来ます。
例えば、

```php
// ビューファイル "@app/views/site/license.php" を表示
echo \Yii::$app->view->renderFile('@app/views/site/license.php');
```


### 名前付きビュー <a name="named-views"></a>

ビューをレンダリングするとき、ビューを指定するのには、ビューの名前か、
ビューファイルのパス/エイリアスか、どちらかを使うことが出来ます。
たいていの場合は、より簡潔で柔軟な前者を使います。
名前を使って指定されるビューを *名前付きビュー* と呼びます。

ビューの名前は、以下の規則に従って、対応するビューファイルのパスに解決されます。

* ビュー名はファイル拡張子を省略することが出来ます。その場合、`.php` が拡張子として使われます。
  例えば、`about` というビュー名は `about.php` というファイル名に対応します。
* ビュー名が二つのスラッシュ (`//`) で始まる場合は、対応するビューファイルのパスは `@app/views/ViewName`
  となります。つまり、ビューファイルは [[yii\base\Application::viewPath|アプリケーションのビューパス]]
  の下で探されます。例えば、`//site/about` は `@app/views/site/about.php` へと解決されます。
* ビュー名が一つのスラッシュ (`/`) で始まる場合は、ビューファイルのパスは、ビュー名の前に、現在
  アクティブな [モジュール](structure-modules.md) の [[yii\base\Module::viewPath|ビューパス]]
  を置くことによって形成されます。アクティブなモジュールが無い場合は、`@app/views/ViewName`
  が使用されます。例えば、`/user/create` は、現在アクティブなモジュールが `user` である場合は、
  `@app/modules/user/views/user/create.php` へと解決されます。アクティブなモジュールが無い場合は、
  ビューファイルのパスは `@app/views/user/create.php` となります。
* ビューが [[yii\base\View::context|コンテキスト]] を伴ってレンダリングされ、そのコンテキストが
  [[yii\base\ViewContextInterface]] を実装している場合は、ビューファイルのパスは、コンテキストの
  [[yii\base\ViewContextInterface::getViewPath()|ビューパス]] をビュー名の前に置くことによって
  形成されます。これは、主として、コントローラとウィジェットの中でレンダリングされるビューに当てはまります。
  例えば、コンテキストが `SiteController` コントローラである場合、`site/about` は `@app/views/site/about.php`
  へと解決されます。
* あるビューが別のビューの中でレンダリングされる場合は、後者のビューファイルを含んでいるディレクトリが
  前者のビュー名の前に置かれて、実際のビューファイルのパスが形成されます。例えば、`item` は、
  `@app/views/post/index.php` というビューの中でレンダリングされる場合、`@app/views/post/item`
  へと解決されます。

上記の規則によると、コントローラ `app\controllers\PostController` の中で `$this->render('view')` を呼ぶと、
実際には、ビューファイル `@app/views/post/view.php` がレンダリングされ、一方、そのビューの中で
`$this->render('_overview')` を呼ぶと、ビューファイル `@app/views/post/_overview.php`
がレンダリングされることになります。


### ビューの中でデータにアクセスする <a name="accessing-data-in-views"></a>

ビューの中でデータにアクセスするためのアプローチが二つあります: プッシュとプルです。

By passing the data as the second parameter to the view rendering methods, you are using the push approach.
The data should be represented as an array of name-value pairs. When the view is being rendered, the PHP
`extract()` function will be called on this array so that the array is extracted into variables in the view.
For example, the following view rendering code in a controller will push two variables to the `report` view:
`$foo = 1` and `$bar = 2`.

```php
echo $this->render('report', [
    'foo' => 1,
    'bar' => 2,
]);
```

The pull approach actively retrieves data from the [[yii\base\View|view component]] or other objects accessible
in views (e.g. `Yii::$app`). Using the code below as an example, within the view you can get the controller object
by the expression `$this->context`. And as a result, it is possible for you to access any properties or methods
of the controller in the `report` view, such as the controller ID shown in the following:

```php
The controller ID is: <?= $this->context->id ?>
?>
```

The push approach is usually the preferred way of accessing data in views, because it makes views less dependent
on context objects. Its drawback is that you need to manually build the data array all the time, which could
become tedious and error prone if a view is shared and rendered in different places.


### Sharing Data among Views <a name="sharing-data-among-views"></a>

The [[yii\base\View|view component]] provides the [[yii\base\View::params|params]] property that you can use
to share data among views.

For example, in an `about` view, you can have the following code which specifies the current segment of the
breadcrumbs.

```php
$this->params['breadcrumbs'][] = 'About Us';
```

Then, in the [layout](#layouts) file, which is also a view, you can display the breadcrumbs using the data
passed along [[yii\base\View::params|params]]:

```php
<?= yii\widgets\Breadcrumbs::widget([
    'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
]) ?>
```


## Layouts <a name="layouts"></a>

Layouts are a special type of views that represent the common parts of multiple views. For example, the pages
for most Web applications share the same page header and footer. While you can repeat the same page header and footer
in every view, a better way is to do this once in a layout and embed the rendering result of a content view at
an appropriate place in the layout.


### Creating Layouts <a name="creating-layouts"></a>

Because layouts are also views, they can be created in the similar way as normal views. By default, layouts
are stored in the directory `@app/views/layouts`. For layouts used within a [module](structure-modules.md),
they should be stored in the `views/layouts` directory under the [[yii\base\Module::basePath|module directory]].
You may customize the default layout directory by configuring the [[yii\base\Module::layoutPath]] property of
the application or modules.

The following example shows how a layout looks like. Note that for illustrative purpose, we have greatly simplified
the code in the layout. In practice, you may want to add more content to it, such as head tags, main menu, etc.

```php
<?php
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $content string */
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"/>
    <?= Html::csrfMetaTags() ?>
    <title><?= Html::encode($this->title) ?></title>
    <?php $this->head() ?>
</head>
<body>
<?php $this->beginBody() ?>
    <header>My Company</header>
    <?= $content ?>
    <footer>&copy; 2014 by My Company</footer>
<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
```

As you can see, the layout generates the HTML tags that are common to all pages. Within the `<body>` section,
the layout echoes the `$content` variable which represents the rendering result of content views and is pushed
into the layout when [[yii\base\Controller::render()]] is called.

Most layouts should call the following methods like shown in the above code. These methods mainly trigger events
about the rendering process so that scripts and tags registered in other places can be properly injected into
the places where these methods are called.

- [[yii\base\View::beginPage()|beginPage()]]: This method should be called at the very beginning of the layout.
  It triggers the [[yii\base\View::EVENT_BEGIN_PAGE|EVENT_BEGIN_PAGE]] event which indicates the beginning of a page.
- [[yii\base\View::endPage()|endPage()]]: This method should be called at the end of the layout.
  It triggers the [[yii\base\View::EVENT_END_PAGE|EVENT_END_PAGE]] event which indicates the end of a page.
- [[yii\web\View::head()|head()]]: This method should be called within the `<head>` section of an HTML page.
  It generates a placeholder which will be replaced with the registered head HTML code (e.g. link tags, meta tags)
  when a page finishes rendering.
- [[yii\web\View::beginBody()|beginBody()]]: This method should be called at the beginning of the `<body>` section.
  It triggers the [[yii\web\View::EVENT_BEGIN_BODY|EVENT_BEGIN_BODY]] event and generates a placeholder which will
  be replaced by the registered HTML code (e.g. JavaScript) targeted at the body begin position.
- [[yii\web\View::endBody()|endBody()]]: This method should be called at the end of the `<body>` section.
  It triggers the [[yii\web\View::EVENT_END_BODY|EVENT_END_BODY]] event and generates a placeholder which will
  be replaced by the registered HTML code (e.g. JavaScript) targeted at the body end position.


### Accessing Data in Layouts <a name="accessing-data-in-layouts"></a>

Within a layout, you have access to two predefined variables: `$this` and `$content`. The former refers to
the [[yii\base\View|view]] component, like in normal views, while the latter contains the rendering result of a content
view which is rendered by calling the [[yii\base\Controller::render()|render()]] method in controllers.

If you want to access other data in layouts, you have to use the pull method as described in
the [Accessing Data in Views](#accessing-data-in-views) subsection. If you want to pass data from a content view
to a layout, you may use the method described in the [Sharing Data among Views](#sharing-data-among-views) subsection.


### Using Layouts <a name="using-layouts"></a>

As described in the [Rendering in Controllers](#rendering-in-controllers) subsection, when you render a view
by calling the [[yii\base\Controller::render()|render()]] method in a controller, a layout will be applied
to the rendering result. By default, the layout `@app/views/layouts/main.php` will be used. 

You may use a different layout by configuring either [[yii\base\Application::layout]] or [[yii\base\Controller::layout]].
The former governs the layout used by all controllers, while the latter overrides the former for individual controllers.
For example, the following code makes the `post` controller to use `@app/views/layouts/post.php` as the layout
when rendering its views. Other controllers, assuming their `layout` property is untouched, will still use the default
`@app/views/layouts/main.php` as the layout.
 
```php
namespace app\controllers;

use yii\web\Controller;

class PostController extends Controller
{
    public $layout = 'post';
    
    // ...
}
```

For controllers belonging to a module, you may also configure the module's [[yii\base\Module::layout|layout]] property to
use a particular layout for these controllers. 

Because the `layout` property may be configured at different levels (controllers, modules, application),
behind the scene Yii takes two steps to determine what is the actual layout file being used for a particular controller.

In the first step, it determines the layout value and the context module:

- If the [[yii\base\Controller::layout]] property of the controller is not null, use it as the layout value and
  the [[yii\base\Controller::module|module]] of the controller as the context module.
- If [[yii\base\Controller::layout|layout]] is null, search through all ancestor modules (including the application itself) of the controller and 
  find the first module whose [[yii\base\Module::layout|layout]] property is not null. Use that module and
  its [[yii\base\Module::layout|layout]] value as the context module and the chosen layout value.
  If such a module cannot be found, it means no layout will be applied.
  
In the second step, it determines the actual layout file according to the layout value and the context module
determined in the first step. The layout value can be:

- a path alias (e.g. `@app/views/layouts/main`).
- an absolute path (e.g. `/main`): the layout value starts with a slash. The actual layout file will be
  looked for under the application's [[yii\base\Application::layoutPath|layout path]] which defaults to
  `@app/views/layouts`.
- a relative path (e.g. `main`): the actual layout file will be looked for under the context module's
  [[yii\base\Module::layoutPath|layout path]] which defaults to the `views/layouts` directory under the
  [[yii\base\Module::basePath|module directory]].
- the boolean value `false`: no layout will be applied.

If the layout value does not contain a file extension, it will use the default one `.php`.


### Nested Layouts <a name="nested-layouts"></a>

Sometimes you may want to nest one layout in another. For example, in different sections of a Web site, you
want to use different layouts, while all these layouts share the same basic layout that generates the overall
HTML5 page structure. You can achieve this goal by calling [[yii\base\View::beginContent()|beginContent()]] and
[[yii\base\View::endContent()|endContent()]] in the child layouts like the following:

```php
<?php $this->beginContent('@app/views/layouts/base.php'); ?>

...child layout content here...

<?php $this->endContent(); ?>
```

As shown above, the child layout content should be enclosed within [[yii\base\View::beginContent()|beginContent()]] and
[[yii\base\View::endContent()|endContent()]]. The parameter passed to [[yii\base\View::beginContent()|beginContent()]]
specifies what is the parent layout. It can be either a layout file or alias.

Using the above approach, you can nest layouts in more than one levels.


### Using Blocks <a name="using-blocks"></a>

Blocks allow you to specify the view content in one place while displaying it in another. They are often used together
with layouts. For example, you can define a block in a content view and display it in the layout.

You call [[yii\base\View::beginBlock()|beginBlock()]] and [[yii\base\View::endBlock()|endBlock()]] to define a block.
The block can then be accessed via `$view->blocks[$blockID]`, where `$blockID` stands for a unique ID that you assign
to the block when defining it.

The following example shows how you can use blocks to customize specific parts of a layout in a content view.

First, in a content view, define one or multiple blocks:

```php
...

<?php $this->beginBlock('block1'); ?>

...content of block1...

<?php $this->endBlock(); ?>

...

<?php $this->beginBlock('block3'); ?>

...content of block3...

<?php $this->endBlock(); ?>
```

Then, in the layout view, render the blocks if they are available, or display some default content if a block is
not defined.

```php
...
<?php if (isset($this->blocks['block1'])): ?>
    <?= $this->blocks['block1'] ?>
<?php else: ?>
    ... default content for block1 ...
<?php endif; ?>

...

<?php if (isset($this->blocks['block2'])): ?>
    <?= $this->blocks['block2'] ?>
<?php else: ?>
    ... default content for block2 ...
<?php endif; ?>

...

<?php if (isset($this->blocks['block3'])): ?>
    <?= $this->blocks['block3'] ?>
<?php else: ?>
    ... default content for block3 ...
<?php endif; ?>
...
```


## Using View Components <a name="using-view-components"></a>

[[yii\base\View|View components]] provides many view-related features. While you can get view components
by creating individual instances of [[yii\base\View]] or its child class, in most cases you will mainly use
the `view` application component. You can configure this component in [application configurations](structure-applications.md#application-configurations)
like the following:

```php
[
    // ...
    'components' => [
        'view' => [
            'class' => 'app\components\View',
        ],
        // ...
    ],
]
```

View components provide the following useful view-related features, each described in more details in a separate section:

* [theming](output-theming.md): allows you to develop and change the theme for your Web site.
* [fragment caching](caching-fragment.md): allows you to cache a fragment within a Web page.
* [client script handling](output-client-scripts.md): supports CSS and JavaScript registration and rendering.
* [asset bundle handling](structure-assets.md): supports registering and rendering of [asset bundles](structure-assets.md).
* [alternative template engines](tutorial-template-engines.md): allows you to use other template engines, such as
  [Twig](http://twig.sensiolabs.org/), [Smarty](http://www.smarty.net/).

You may also frequently use the following minor yet useful features when you are developing Web pages.


### Setting Page Titles <a name="setting-page-titles"></a>

Every Web page should have a title. Normally the title tag is being displayed in a [layout](#layouts). However, in practice
the title is often determined in content views rather than layouts. To solve this problem, [[yii\web\View]] provides
the [[yii\web\View::title|title]] property for you to pass the title information from content views to layouts.

To make use of this feature, in each content view, you can set the page title like the following:

```php
<?php
$this->title = 'My page title';
?>
```

Then in the layout, make sure you have the following code in the `<head>` section:

```php
<title><?= Html::encode($this->title) ?></title>
```


### Registering Meta Tags <a name="registering-meta-tags"></a>

Web pages usually need to generate various meta tags needed by different parties. Like page titles, meta tags
appear in the `<head>` section and are usually generated in layouts.

If you want to specify what meta tags to generate in content views, you can call [[yii\web\View::registerMetaTag()]]
in a content view, like the following:

```php
<?php
$this->registerMetaTag(['name' => 'keywords', 'content' => 'yii, framework, php']);
?>
```

The above code will register a "keywords" meta tag with the view component. The registered meta tag is
rendered after the layout finishes rendering. By then, the following HTML code will be inserted
at the place where you call [[yii\web\View::head()]] in the layout and generate the following HTML code:

```php
<meta name="keywords" content="yii, framework, php">
```

Note that if you call [[yii\web\View::registerMetaTag()]] multiple times, it will register multiple meta tags,
regardless whether the meta tags are the same or not.

To make sure there is only a single instance of a meta tag type, you can specify a key as a second parameter when calling the method.
For example, the following code registers two "description" meta tags. However, only the second one will be rendered.

```html
$this->registerMetaTag(['name' => 'description', 'content' => 'This is my cool website made with Yii!'], 'description');
$this->registerMetaTag(['name' => 'description', 'content' => 'This website is about funny raccoons.'], 'description');
```


### Registering Link Tags <a name="registering-link-tags"></a>

Like [meta tags](#adding-meta-tags), link tags are useful in many cases, such as customizing favicon, pointing to
RSS feed or delegating OpenID to another server. You can work with link tags in the similar way as meta tags
by using [[yii\web\View::registerLinkTag()]]. For example, in a content view, you can register a link tag like follows,

```php
$this->registerLinkTag([
    'title' => 'Live News for Yii',
    'rel' => 'alternate',
    'type' => 'application/rss+xml',
    'href' => 'http://www.yiiframework.com/rss.xml/',
]);
```

The code above will result in

```html
<link title="Live News for Yii" rel="alternate" type="application/rss+xml" href="http://www.yiiframework.com/rss.xml/">
```

Similar as [[yii\web\View::registerMetaTag()|registerMetaTags()]], you can specify a key when calling
[[yii\web\View::registerLinkTag()|registerLinkTag()]] to avoid generated repeated link tags.


## View Events <a name="view-events"></a>

[[yii\base\View|View components]] trigger several events during the view rendering process. You may respond
to these events to inject content into views or process the rendering results before they are sent to end users.

- [[yii\base\View::EVENT_BEFORE_RENDER|EVENT_BEFORE_RENDER]]: triggered at the beginning of rendering a file
  in a controller. Handlers of this event may set [[yii\base\ViewEvent::isValid]] to be false to cancel the rendering process.
- [[yii\base\View::EVENT_AFTER_RENDER|EVENT_AFTER_RENDER]]: triggered after rendering a file by the call of [[yii\base\View::afterRender()]].
  Handlers of this event may obtain the rendering result through [[yii\base\ViewEvent::output]] and may modify
  this property to change the rendering result.
- [[yii\base\View::EVENT_BEGIN_PAGE|EVENT_BEGIN_PAGE]]: triggered by the call of [[yii\base\View::beginPage()]] in layouts.
- [[yii\base\View::EVENT_END_PAGE|EVENT_END_PAGE]]: triggered by the call of [[yii\base\View::endPage()]] in layouts.
- [[yii\web\View::EVENT_BEGIN_BODY|EVENT_BEGIN_BODY]]: triggered by the call of [[yii\web\View::beginBody()]] in layouts.
- [[yii\web\View::EVENT_END_BODY|EVENT_END_BODY]]: triggered by the call of [[yii\web\View::endBody()]] in layouts.

For example, the following code injects the current date at the end of the page body:

```php
\Yii::$app->view->on(View::EVENT_END_BODY, function () {
    echo date('Y-m-d');
});
```


## Rendering Static Pages <a name="rendering-static-pages"></a>

Static pages refer to those Web pages whose main content are mostly static without the need of accessing
dynamic data pushed from controllers.

You can output static pages by putting their code in the view, and then using the code like the following in a controller:

```php
public function actionAbout()
{
    return $this->render('about');
}
```

If a Web site contains many static pages, it would be very tedious repeating the similar code many times.
To solve this problem, you may introduce a [standalone action](structure-controllers.md#standalone-actions)
called [[yii\web\ViewAction]] in a controller. For example,

```php
namespace app\controllers;

use yii\web\Controller;

class SiteController extends Controller
{
    public function actions()
    {
        return [
            'page' => [
                'class' => 'yii\web\ViewAction',
            ],
        ];
    }
}
```

Now if you create a view named `about` under the directory `@app/views/site/pages`, you will be able to
display this view by the following URL:

```
http://localhost/index.php?r=site/page&view=about
```

The `GET` parameter `view` tells [[yii\web\ViewAction]] which view is requested. The action will then look
for this view under the directory `@app/views/site/pages`. You may configure [[yii\web\ViewAction::viewPrefix]]
to change the directory for searching these views.


## Best Practices <a name="best-practices"></a>

Views are responsible for presenting models in the format that end users desire. In general, views

* should mainly contain presentational code, such as HTML, and simple PHP code to traverse, format and render data.
* should not contain code that performs DB queries. Such code should be done in models.
* should avoid direct access to request data, such as `$_GET`, `$_POST`. This belongs to controllers.
  If request data is needed, they should be pushed into views by controllers.
* may read model properties, but should not modify them.

To make views more manageable, avoid creating views that are too complex or contain too much redundant code.
You may use the following techniques to achieve this goal:

* use [layouts](#layouts) to represent common presentational sections (e.g. page header, footer).
* divide a complicated view into several smaller ones. The smaller views can be rendered and assembled into a bigger
  one using the rendering methods that we have described.
* create and use [widgets](structure-widgets.md) as building blocks of views.
* create and use helper classes to transform and format data in views.

