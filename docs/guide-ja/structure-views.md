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

ビューの中でアクセスできる `$this` は、このビューテンプレートを管理しレンダリングしている
[[yii\web\View|ビューコンポーネント]] を参照します。

`$this` 以外に、上記の例の `$model` のように、前もって定義された変数がビューの中にあることがあります。
このような変数は、[コントローラ](structure-controllers.md) または [ビューのレンダリング](#rendering-views) をトリガするオブジェクトによってビューに *プッシュ* されたデータを表します。

> Tip|ヒント: 上の例では、事前に定義された変数は、IDE に認識されるように、
  ビューの先頭のコメントブロックの中にリストされています。これは、ビューに
  ドキュメントを付けるのにも良い方法です。


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
* 他のオブジェクトによって表示されるビューについても、ウィジェットの場合と同じ規約に従うことが
  推奨されます。

これらの既定のビューディレクトリは、コントローラやウィジェットの [[yii\base\ViewContextInterface::getViewPath()]]
メソッドをオーバーライドすることでカスタマイズすることが可能です。


## ビューをレンダリングする <a name="rendering-views"></a>

[コントローラ](structure-controllers.md) の中でも、[ウィジェット](structure-widgets.md) の中でも、
または、その他のどんな場所でも、ビューをレンダリングするメソッドを呼ぶことによって
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

ビューの中でデータにアクセスするためのアプローチが二つあります: 「プッシュ」と「プル」です。

ビューをレンダリングするメソッドに二番目のパラメータとしてデータを渡すのが「プッシュ」のアプローチです。
データは、「名前-値」のペアの配列として表されなければなりません。
ビューがレンダリングされるときに、PHP の `extract()` 関数がこの配列に対して呼び出され、
ビューの中でこの配列から変数が抽出されます。
例えば、次のコードはコントローラの中でビューをレンダリングしていますが、`report` ビューに
二つの変数、すなわち、`$foo = 1` と `$bar = 2` をプッシュしています。

```php
echo $this->render('report', [
    'foo' => 1,
    'bar' => 2,
]);
```

「プル」のアプローチは、[[yii\base\View|ビューコンポーネント]] またはビューからアクセス出来るその他のオブジェクト (例えば `Yii::$app`) から
積極的にデータを読み出すものです。
下記のコードを例として使って、ビューの中で `$this->context` という式でコントローラオブジェクト
を取得することが出来ます。その結果、`report` ビューの中でコントローラの全てのプロパティや
メソッドにアクセスすることが出来ます。次の例ではコントローラ ID にアクセスしています:

```php
The controller ID is: <?= $this->context->id ?>
?>
```

通常は「プッシュ」アプローチが、ビューでデータにアクセスする方法として推奨されます。
なぜなら、ビューのコンテキストオブジェクトに対する依存がより少ないからです。
その短所は、常にデータ配列を手作業で作成する必要がある、ということです。
ビューが共有されてさまざまな場所でレンダリングされる場合、その作業が面倒くさくなり、また、
間違いも生じやすくなります。


### ビューの間でデータを共有する <a name="sharing-data-among-views"></a>

[[yii\base\View|ビューコンポーネント]] が提供する [[yii\base\View::params|params]] プロパティを使うと
ビューの間でデータを共有することが出来ます。

例えば、`about` というビューで、次のようなコードを使って、
パン屑リストの現在の区分を指定することが出来ます。

```php
$this->params['breadcrumbs'][] = 'About Us';
```

そして、[レイアウト](#layouts) ファイル (これも一つのビューです) の中で、[[yii\base\View::params|params]]
によって渡されたデータを使って、パン屑リストを表示することが出来ます:

```php
<?= yii\widgets\Breadcrumbs::widget([
    'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
]) ?>
```


## レイアウト <a name="layouts"></a>

レイアウトは、複数のビューの共通部分をあらわす特殊なタイプのビューです。
例えば、たいていのウェブアプリケーションでは、ページは共通のヘッダとフッタを持っています。
すべてのビューで同じヘッダとフッタを繰り返すことも出来ますが、もっと良い方法は、
そういうことはレイアウトの中で一度だけして、コンテンツビューのレンダリング結果を
レイアウトの中の適切な場所に埋め込むことです。


### レイアウトを作成する <a name="creating-layouts"></a>

レイアウトもまたビューですので、通常のビューと同様な方法で作成することが出来ます。既定では、
レイアウトは `@app/views/layouts` ディレクトリに保存されます。[モジュール](structure-modules.md)
の中で使用されるレイアウトについては、[[yii\base\Module::basePath|モジュールディレクトリ]] の下の
`views/layouts` ディレクトリに保存されるべきものとなります。既定のレイアウトディレクトリは、
アプリケーションまたはモジュールの [[yii\base\Module::layoutPath]] プロパティを構成することで
カスタマイズすることが出来ます。

次の例は、レイアウトがどのようなものであるかを示すものです。説明のために、レイアウトの中のコードを
大幅に単純化していることに注意してください。実際には、ヘッドのタグやメインメニューなど、もっと
多くのコンテンツを追加する必要があるでしょう。

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

見ると分かるように、レイアウトはすべてのページに共通な HTML タグを生成しています。`<body>`
セクションの中でレイアウトが `$content` という変数をエコーしていますが、これは、
コンテンツビューのレンダリング結果を表すものであり、[[yii\base\Controller::render()]] が呼ばれるときに、レイアウトにプッシュされるものです。

上記のコードに示されているように、たいていのレイアウトは次に挙げるメソッドを呼び出すべきです。
これらのメソッドは主としてレンダリングの過程に関するイベントをトリガして、他の場所で登録された
スクリプトやタグが、メソッドが呼ばれた場所に正しく注入されるようにするためのものです。

- [[yii\base\View::beginPage()|beginPage()]]: このメソッドがレイアウトの一番初めに呼ばれるべきです。
  これは、ページの開始を示す [[yii\base\View::EVENT_BEGIN_PAGE|EVENT_BEGIN_PAGE]] イベントをトリガします。
- [[yii\base\View::endPage()|endPage()]]: このメソッドがレイアウトの最後で呼ばれるべきです。
  これは、ページの終了を示す [[yii\base\View::EVENT_END_PAGE|EVENT_END_PAGE]] イベントをトリガします。
- [[yii\web\View::head()|head()]]: このメソッドが HTML ページの `<head>` セクションの中で呼ばれるべきです。
  このメソッドは、ページのレンダリングが完了したときに、登録された head の HTML コード (リンクタグ、メタタグなど) に置き換えられるプレースホルダを生成します。
- [[yii\web\View::beginBody()|beginBody()]]: このメソッドが `<body>` セクションの最初で呼ばれるべきです。
  このメソッドは [[yii\web\View::EVENT_BEGIN_BODY|EVENT_BEGIN_BODY]] イベントをトリガし、
  body の開始位置を目的とする登録された HTML コード (JavaScript など) によって置き換えられる
  プレースホルダを生成します。
- [[yii\web\View::endBody()|endBody()]]: このメソッドが `<body`> セクションの最後で呼ばれるべきです。
  このメソッドは  [[yii\web\View::EVENT_END_BODY|EVENT_END_BODY]] イベントをトリガし、
  body の終了位置を目的とする登録された HTML コード (JavaScript など) によって置き換えられる
  プレースホルダを生成します。


### レイアウトでデータにアクセスする <a name="accessing-data-in-layouts"></a>

レイアウトの中では、事前定義された二つの変数にアクセス出来ます: `$this` と `$content` です。前者は、
通常のビューにおいてと同じく、[[yii\base\View|ビュー]] コンポーネントを参照します。一方、後者は、
コントローラの中で [[yii\base\Controller::render()|render()]] メソッドを呼ぶことによってレンダリングされる、
コンテンツビューのレンダリング結果を含むものです。

レイアウトの中で他のデータにアクセスする必要があるときは、[ビューの中でデータにアクセスする](#accessing-data-in-views)
の項で説明されている「プル」の方法を使う必要があります。コンテンツビューからレイアウトにデータを渡す必要があるときは、
[ビューの間でデータを共有する](#sharing-data-among-views) の項で説明されている方法を使うことが出来ます。


### レイアウトを使う <a name="using-layouts"></a>

[コントローラでのレンダリング](#rendering-in-controllers) の項で説明されているように、コントローラの中で
[[yii\base\Controller::render()|render()]] メソッドを呼んでビューをレンダリングすると、レンダリング結果に
レイアウトが適用されます。既定では、`@app/views/layouts/main.php` というレイアウトが使用されます。

[[yii\base\Application::layout]] または [[yii\base\Controller::layout]] のどちらかを構成することによって、異なるレイアウトを
使うことが出来ます。前者は全てのコントローラによって使用されるレイアウトを決定するものですが、後者は個々のコントローラについて
前者をオーバーライドするものです。例えば、次のコードは、`post` コントローラがビューをレンダリングするときに
`@app/views/layouts/post.php` をレイアウトとして使うようにするものです。その他のコントローラは、`layout` プロパティに
触れられていないと仮定すると、引き続き既定の `@app/views/layouts/main.php` をレイアウトとして使います。
 
```php
namespace app\controllers;

use yii\web\Controller;

class PostController extends Controller
{
    public $layout = 'post';
    
    // ...
}
```

モジュールに属するコントローラについては、モジュールの [[yii\base\Module::layout|layout]] プロパティを構成して、モジュール内の
コントローラに特定のレイアウトを使用することも出来ます。

`layout` プロパティは異なるレベル (コントローラ、モジュール、アプリケーション) で構成されうるものですので、
Yii は舞台裏で二つのステップを践んで、特定のコントローラで実際に使われるレイアウトファイルが何であるかを決定します。

最初のステップで、Yii はレイアウトの値とコンテキストモジュールを決定します:

- コントローラの [[yii\base\Controller::layout]] プロパティが null でないときは、それをレイアウトの値として使い、
  コントローラの [[yii\base\Controller::module|モジュール]] をコンテキストモジュールとして使う。
- [[yii\base\Controller::layout|layout]] が null のときは、コントローラの祖先となっている全てのモジュール
  (アプリケーション自体も含む) を探して、[[yii\base\Module::layout|layout]] プロパティが null でない最初のモジュールを見つける。
  見つかったモジュールとその [[yii\base\Module::layout|layout]] の値をコンテキストモジュールと選ばれたレイアウトの値とする。
  そのようなモジュールが見つからなかったときは、レイアウトは適用されないということを意味する。

第二のステップでは、最初のステップで決定されたレイアウトの値とコンテキストモジュールに従って、実際のレイアウトファイルを決定します。
レイアウトの値は下記のいずれかであり得ます:

- パスエイリアス (例えば、`@app/views/layouts/main`)。
- 絶対パス (例えば、`/main`): すなわち、スラッシュで始まるレイアウトの値の場合。
  実際のレイアウトファイルはアプリケーションの [[yii\base\Application::layoutPath|レイアウトパス]]
  (デフォルトでは `@app/views/layouts`) の下で探される。
- 相対パス (例えば、`main`): 実際のレイアウトファイルはコンテキストモジュールの [[yii\base\Module::layoutPath|レイアウトパス]]
  (デフォルトでは [[yii\base\Module::basePath|モジュールディレクトリ]] の下の `views/layouts` ディレクトリ) の下で探される。
- 真偽値 `false`: レイアウトは適用されない。

レイアウトの値がファイル拡張子を含んでいない場合は、デフォルト値である `.php` を使います。


### 入れ子のレイアウト <a name="nested-layouts"></a>

ときとして、あるレイアウトの中に別のレイアウトを入れたい場合があるでしょう。例えば、
ウェブサイトの別々のセクションにおいて、違うレイアウトを使いたいけれども、
それらのレイアウトは全て、全体としての HTML5 ページ構造を生成する同一の基本レイアウトを
共有している、という場合です。この目的を達することは、次のように、子レイアウトの中で
[[yii\base\View::beginContent()|beginContent()]] と [[yii\base\View::endContent()|endContent()]]
を呼ぶことで可能になります:

```php
<?php $this->beginContent('@app/views/layouts/base.php'); ?>

... 子レイアウトのコンテンツをここに ...

<?php $this->endContent(); ?>
```

上のコードが示すように、子レイアウトのコンテンツは [[yii\base\View::beginContent()|beginContent()]] と
[[yii\base\View::endContent()|endContent()]] によって囲まれなければなりません。
[[yii\base\View::beginContent()|beginContent()]] に渡されるパラメータは、
親レイアウトで何であるかを指定するものです。レイアウトのファイルまたはエイリアスのどちらかを使うことが出来ます。

上記のアプローチを使って、2レベル以上のレイアウトを入れ子にすることも出来ます。


### ブロックを使う <a name="using-blocks"></a>

ブロックを使うと、ある場所でビューコンテンツを規定して、別の場所でそれを表示することが可能になります。
ブロックはたいていはレイアウトと一緒に使われます。例えば、ブロックをコンテンツビューで定義して、
それをレイアウトで表示する、ということが出来ます。

[[yii\base\View::beginBlock()|beginBlock()]] と [[yii\base\View::endBlock()|endBlock()]]
を呼んでブロックを定義します。
すると、そのブロックを `$view->blocks[$blockID]` によってアクセス出来るようになります。
ここで `$blockID` は、定義したときにブロックに割り当てたユニークな ID を指します。

次の例は、どのようにブロックを使えば、レイアウトの特定の部分をコンテンツビューで
カスタマイズすることが出来るかを示すものです。

最初に、コンテンツビューで、一つまたは複数のブロックを定義します。

```php
...

<?php $this->beginBlock('block1'); ?>

... block1 のコンテンツ ...

<?php $this->endBlock(); ?>

...

<?php $this->beginBlock('block3'); ?>

... block3 のコンテンツ ...

<?php $this->endBlock(); ?>
```

次に、レイアウトビューで、得ることが出来ればブロックをレンダリングし、ブロックが定義されていないときは
何らかの既定のコンテンツを表示します。

```php
...
<?php if (isset($this->blocks['block1'])): ?>
    <?= $this->blocks['block1'] ?>
<?php else: ?>
    ... block1 の既定のコンテンツ ...
<?php endif; ?>

...

<?php if (isset($this->blocks['block2'])): ?>
    <?= $this->blocks['block2'] ?>
<?php else: ?>
    ... block2 の既定のコンテンツ ...
<?php endif; ?>

...

<?php if (isset($this->blocks['block3'])): ?>
    <?= $this->blocks['block3'] ?>
<?php else: ?>
    ... block3 の既定のコンテンツ ...
<?php endif; ?>
...
```


## ビューコンポーネントを使う <a name="using-view-components"></a>

[[yii\base\View|ビューコンポーネント]] はビューに関連する多くの機能を提供します。
ビューコンポーネントは、[[yii\base\View]] またはその子クラスの個別のインスタンスを作成することによっても取得できますが、
たいていの場合は、`view` アプリケーションコンポーネントを主として使うことになるでしょう。
このコンポーネントは [アプリケーションのコンフィギュレーション](structure-applications.md#application-configurations) の中で、次のようにして構成することが出来ます:

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

ビューコンポーネントは、次に挙げるビュー関連の有用な機能を提供します。それぞれについては、
独立の節で更に詳細に説明されます。

* [テーマ](output-theming.md): ウェブサイトのテーマを開発し変更することを可能にします。
* [フラグメントキャッシュ](caching-fragment.md): ウェブページの中の断片をキャッシュすることを可能にします。
* [クライアントスクリプトの取り扱い](output-client-scripts.md): CSS と JavaScript の登録とレンダリングをサポートします。
* [アセットバンドルの取り扱い](structure-assets.md): [アセットバンドル](structure-assets.md) の登録とレンダリングをサポート.
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

