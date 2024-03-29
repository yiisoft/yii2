バージョン 1.1 からアップグレードする
=====================================

Yii フレームワークは 2.0 のために完全に書き直されたため、バージョン 1.1 と 2.0 の間には数多くの違いがあります。
結果として、バージョン 1.1 からのアップグレードは、マイナー・バージョン間でのアップグレードのような些細な仕事ではなくなりました。
このセクションでは、二つのバージョン間の主要な違いを説明します。

もしあなたが以前に Yii 1.1 を使ったことがなければ、このガイドを飛ばして直接に "[始めよう](start-installation.md)" に進んでも、問題はありません。

Yii 2.0 はこの要約でカバーされているよりも多くの新機能を導入していることに注意してください。
決定版ガイド全体を通読して全ての新機能について学習することを強く推奨します。
おそらく、以前は自分自身で開発する必要があったいくつかの機能が、今ではコア・コードの一部になっていることに気付くでしょう。


インストール
------------

Yii 2.0 は、事実上の標準的 PHP パッケージ管理ソフトである [Composer](https://getcomposer.org/) を全面的に採用しています。
コア・フレームワークも、エクステンションも、インストールは Composer を通じて処理されます。
[Yii をインストールする](start-installation.md) のセクションを参照して、Yii 2.0 をインストールする方法を学習してください。
新しいエクステンションを作成したい場合、または既存の 1.1 エクステンションを 2.0 互換のエクステンションに作り直したい場合は、ガイドの
[エクステンションを作成する](structure-extensions.md#creating-extensions) のセクションを参照してください。


PHP の必要条件
--------------

Yii 2.0 は PHP 5.4 以上を必要とします。PHP 5.4 は、Yii 1.1 によって必要とされていた PHP 5.2 に比べて、非常に大きく改良されています。
この結果として、注意を払うべき言語レベルでの違いが数多くあります。
以下は PHP に関する主要な変更点の要約です。

- [名前空間](https://www.php.net/manual/ja/language.namespaces.php)。
- [無名関数](https://www.php.net/manual/ja/functions.anonymous.php)。
- 配列の短縮構文 `[...要素...]` が `array(...要素...)` の代りに使われています。
- 短縮形の echo タグ `<?=` がビュー・ファイルに使われています。PHP 5.4 以降は、この形を使っても安全です。
- [SPL のクラスとインタフェイス](https://www.php.net/manual/ja/book.spl.php)。
- [遅延静的束縛(Late Static Bindings)](https://www.php.net/manual/ja/language.oop5.late-static-bindings.php)。
- [日付と時刻](https://www.php.net/manual/ja/book.datetime.php)。
- [トレイト](https://www.php.net/manual/ja/language.oop5.traits.php)。
- [国際化(intl)](https://www.php.net/manual/ja/book.intl.php)。
  Yii 2.0 は国際化の機能をサポートするために `intl` PHP 拡張を利用しています。


名前空間
--------

Yii 2.0 での最も顕著な変更は名前空間の使用です。
ほとんど全てのコア・クラスが、例えば、`yii\web\Request` のように名前空間に属します。
クラス名に "C" の接頭辞はもう使われません。
命名のスキームはディレクトリ構造に従うようになりました。
例えば、`yii\web\Request` は、対応するクラス・ファイルが Yii フレームワーク・フォルダの下の `web/Request.php` であることを示します。

(全てのコア・クラスは、Yii のクラス・ローダのおかげで、そのクラス・ファイルを明示的にインクルードせずに使うことが出来ます。)


コンポーネントとオブジェクト
----------------------------

Yii 2.0 は、1.1 の `CComponent` クラスを二つのクラス、すなわち、[[yii\base\BaseObject]] と [[yii\base\Component]] に分割しました。
[[yii\base\BaseObject|BaseObject]] クラスは、ゲッターとセッターを通じて [オブジェクト・プロパティ](concept-properties.md) を定義することを可能にする、軽量な基底クラスです。
[[yii\base\Component|Component]] クラスは [[yii\base\BaseObject|BaseObject]] からの拡張であり、
[イベント](concept-events.md) と [ビヘイビア](concept-behaviors.md) をサポートします。

あなたのクラスがイベントやビヘイビアの機能を必要としない場合は、[[yii\base\BaseObject|BaseObject]]
を基底クラスとして使うことを考慮すべきです。
基本的なデータ構造を表すクラスに対して、通常、このことが当てはまります。


オブジェクトの構成
------------------

[[yii\base\BaseObject|BaseObject]] クラスはオブジェクトを構成するための統一された方法を導入しています。
[[yii\base\BaseObject|BaseObject]] の全ての派生クラスは、コンストラクタが必要な場合には、インスタンスが正しく構成されるように、
コンストラクタを以下のようにして宣言しなければなりません。

```php
class MyClass extends \yii\base\BaseObject
{
    public function __construct($param1, $param2, $config = [])
    {
        // ... 構成情報が適用される前の初期化処理

        parent::__construct($config);
    }

    public function init()
    {
        parent::init();

        // ... 構成情報が適用された後の初期化処理
    }
}
```

上記のように、コンストラクタは最後のパラメータとして構成情報の配列を取らなければなりません。
構成情報の配列に含まれる「名前・値」のペアが、コンストラクタの最後でプロパティを構成します。
[[yii\base\BaseObject::init()|init()]] メソッドをオーバーライドして、
構成情報が適用された後に行うべき初期化処理を行うことが出来ます。

この規約に従うことによって、構成情報配列を使って新しいオブジェクトを生成して構成することが
出来るようになります。

```php
$object = Yii::createObject([
    'class' => 'MyClass',
    'property1' => 'abc',
    'property2' => 'cde',
], [$param1, $param2]);
```

構成情報に関する詳細は、[構成情報](concept-configurations.md) のセクションで見ることが出来ます。


イベント
--------

Yii 1 では、イベントは `on` メソッド (例えば、`onBeforeSave`) を定義することによって作成されました。Yii 2 では、どのようなイベント名でも使うことが出来るようになりました。
[[yii\base\Component::trigger()|trigger()]] メソッドを呼んでイベントを発生させます。

```php
$event = new \yii\base\Event;
$component->trigger($eventName, $event);
```

イベントにハンドラをアタッチするためには、[[yii\base\Component::on()|on()]] メソッドを使います。

```php
$component->on($eventName, $handler);
// ハンドラをデタッチするためには、以下のようにします。
// $component->off($eventName, $handler);
```

イベントの機能には数多くの改良がなされました。詳細は [イベント](concept-events.md) のセクションを参照してください。


パス・エイリアス
----------------

Yii 2.0 は、パス・エイリアスの使用を、ファイル/ディレクトリのパスと URL の両方に広げました。
また、Yii 2.0 では、通常のファイル/ディレクトリのパスや URL と区別するために、エイリアス名は `@` という文字で始まることが要求されるようになりました。
例えば、`@yii` というエイリアスは Yii のインストール・ディレクトリを指します。
パス・エイリアスは Yii のコア・コードのほとんどの場所でサポートされています。
例えば [[yii\caching\FileCache::cachePath]] はパス・エイリアスと通常のディレクトリ・パスの両方を受け取ることが出来ます。

パス・エイリアスは、また、クラスの名前空間とも密接に関係しています。
ルートの名前空間に対しては、それぞれ、パス・エイリアスを定義することが推奨されます。
そうすれば、余計な構成をしなくても、Yii のクラス・オートローダを使うことが出来るようになります。
例えば、`@yii` が Yii のインストール・ディレクトリを指しているので、`yii\web\Request` というようなクラスをオートロードすることが出来る訳です。
サード・パーティのライブラリ、例えば Zend フレームワークなどを使う場合にも、そのフレームワークのインストール・ディレクトリを指す `@Zend` というパス・エイリアスを定義することが出来ます。
一旦そうしてしまえば、その Zend フレームワークのライブラリ内のどんなクラスでも、Yii からオートロードすることが出来るようになります。

パス・エイリアスに関する詳細は [エイリアス](concept-aliases.md) のセクションを参照してください。


ビュー
------

Yii 2 のビューについての最も顕著な変更は、ビューの中の `$this` という特殊な変数が現在のコントローラやウィジェットを指すものではなくなった、ということです。
今や `$this` は 2.0 で新しく導入された概念である *ビュー*・オブジェクトを指します。
*ビュー*・オブジェクトは [[yii\web\View]] という型であり、MVC パターンのビューの部分を表すものです。
ビューにおいてコントローラやウィジェットにアクセスしたい場合は、`$this->context` を使うことが出来ます。

パーシャル・ビューを別のビューの中でレンダリングするためには、`$this->renderPartial()` ではなく、`$this->render()` を使います。さらに、`render` の呼び出しは、2.0 では明示的に echo しなくてはなりません。
と言うのは、`render()` メソッドは、レンダリング結果を返すものであり、それを直接に表示するものではないからです。例えば、

```php
echo $this->render('_item', ['item' => $item]);
```

PHP を主たるテンプレート言語として使う以外に、Yii 2.0 は人気のある二つのテンプレート・エンジン、Smarty と Twig に対する正式なサポートを備えています。
Prado テンプレート・エンジンはもはやサポートされていません。
これらのテンプレート・エンジンを使うためには、`view` アプリケーション・コンポーネントを構成して
[[yii\base\View::$renderers|View::$renderers]] プロパティをセットする必要があります。
詳細は [テンプレート・エンジン](tutorial-template-engines.md) のセクションを参照してください。


モデル
------

Yii 2.0 は  1.1 における `CModel` と同様な [[yii\base\Model]] を基底モデルとして使います。`CFormModel` というクラスは完全に廃止されました。
Yii 2 では、それの代りに [[yii\base\Model]] を拡張して、フォームのモデル・クラスを作成しなければなりません。

Yii 2.0 は サポートされるシナリオを宣言するための [[yii\base\Model::scenarios()|scenarios()]] という新しいメソッドを導入しました。
このメソッドを使って、どのシナリオの下で、ある属性が検証される必要があるか、また、安全とみなされるか否か、などを宣言します。例えば、

```php
public function scenarios()
{
    return [
        'backend' => ['email', 'role'],
        'frontend' => ['email', '!role'],
    ];
}
```

上記では二つのシナリオ、すなわち、`backend` と `frontend` が宣言されています。
`backend` シナリオでは、`email` と `role` の属性が両方とも安全であり、一括代入が可能です。
`frontend` シナリオでは、`email` は一括代入が可能ですが、`role` は不可能です。`email` と `role` は、両方とも、規則を使って検証されなければなりません。

[[yii\base\Model::rules()|rules()]] メソッドが、Yii 1.1 に引き続き、検証規則を宣言するために使われます。
[[yii\base\Model::scenarios()|scenarios()]] が導入されたことにより、`unsafe` バリデータが無くなったことに注意してください。

ほとんどの場合、すなわち、[[yii\base\Model::rules()|rules()]] メソッドが存在しうるシナリオを完全に指定しており、
そして `unsafe` な属性を宣言する必要が無い場合であれば、[[yii\base\Model::scenarios()|scenarios()]] をオーバーライドする必要はありません。

モデルについての詳細を学習するためには、[モデル](structure-models.md) のセクションを参照してください。


コントローラ
------------

Yii 2.0 は [[yii\web\Controller]] を基底のコントローラ・クラスとして使います。これは Yii 1.1 における`CController` と同様なクラスです。
[[yii\base\Action]] がアクション・クラスの基底クラスです。

コントローラに関して、あなたのコードに最も顕著な影響を及ぼす変更点は、
コントローラのアクションは表示したいコンテントを、エコーするのでなく、返さなければならなくなった、ということです。

```php
public function actionView($id)
{
    $model = \app\models\Post::findOne($id);
    if ($model) {
        return $this->render('view', ['model' => $model]);
    } else {
        throw new \yii\web\NotFoundHttpException;
    }
}
```

コントローラに関する詳細については [コントローラ](structure-controllers.md) のセクションを参照してください。


ウィジェット
------------

Yii 2.0 は [[yii\base\Widget]] を基底のウィジェット・クラスとして使用します。これは Yii 1.1 の `CWidget` と同様なクラスです。

いろんな IDE においてフレームワークに対するより良いサポートを得るために、Yii 2.0 はウィジェットを使うための新しい構文を導入しました。
スタティックなメソッド [[yii\base\Widget::begin()|begin()]]、[[yii\base\Widget::end()|end()]]、そして [[yii\base\Widget::widget()|widget()]] が導入されました。
以下のようにして使います。

```php
use yii\widgets\Menu;
use yii\widgets\ActiveForm;

// 表示するためには結果を "echo" しなければならないことに注意
echo Menu::widget(['items' => $items]);

// オブジェクトのプロパティを初期化するための配列を渡す
$form = ActiveForm::begin([
    'options' => ['class' => 'form-horizontal'],
    'fieldConfig' => ['inputOptions' => ['class' => 'input-xlarge']],
]);
... ここにフォームの入力フィールド ...
ActiveForm::end();
```

詳細については [ウィジェット](structure-widgets.md) のセクションを参照してください。


テーマ
------

テーマは 2.0 では完全に違う動き方をします。
テーマは、ソースのビュー・ファイル・パスをテーマのビュー・ファイル・パスにマップするパス・マッピング機構に基づくものになりました。
例えば、あるテーマのパス・マップが `['/web/views' => '/web/themes/basic']` である場合、ビュー・ファイル `/web/views/site/index.php` のテーマ版は `/web/themes/basic/site/index.php` になります。
この理由により、テーマはどのようなビュー・ファイルに対してでも適用することが出来るようになりました。
コントローラやウィジェットのコンテキストの外で表示されるビューであっても適用できます。

また、`CThemeManager` コンポーネントはもうありません。
その代りに、`theme` は `view` アプリケーション・コンポーネントの構成可能なプロパティになりました。

詳細については [テーマ](output-theming.md) のセクションを参照してください。


コンソール・アプリケーション
----------------------------

コンソール・アプリケーションは、ウェブ・アプリケーションと同じように、コントローラとして編成されるようになりました。
1.1 における `CConsoleCommand` と同様に、コンソール・コントローラは [[yii\console\Controller]] を拡張したものでなければなりません。

コンソール・コマンドを走らせるためには、`yii <route>` という構文を使います。
ここで `<route>` はコントローラのルート (例えば `sitemap/index`) を表します。
追加の無名の引数は、対応するコントローラのアクション・メソッドに引数として渡されます。
一方、名前付きの引数は、[[yii\console\Controller::options()]] での宣言に従って解析されます。

Yii 2.0 はコメント・ブロックからコマンドのヘルプ情報を自動的に生成する機能をサポートしています。

詳細については [コンソール・コマンド](tutorial-console.md) のセクションを参照してください。


国際化
------

Yii 2.0 は [PECL intl PHP モジュール](https://pecl.php.net/package/intl) に賛同して、内蔵の日付フォーマッタと数字フォーマッタの部品を取り除きました。

メッセージは `i18n` アプリケーション・コンポーネント経由で翻訳されるようになりました。
このコンポーネントは一連のメッセージ・ソースを管理するもので、
メッセージのカテゴリに基づいて異なるメッセージ・ソースを使うことを可能にするものです。

詳細については [国際化](tutorial-i18n.md) のセクションを参照してください。


アクション・フィルタ
--------------------

アクション・フィルタはビヘイビアによって実装されるようになりました。新しいカスタム・フィルタを定義するためには、[[yii\base\ActionFilter]] を拡張します。
フィルタを使うためには、そのフィルタ・クラスをビヘイビアとしてコントローラにアタッチします。
例えば、[[yii\filters\AccessControl]] フィルタを使うためには、コントローラに次のコードを書くことになります。

```php
public function behaviors()
{
    return [
        'access' => [
            'class' => 'yii\filters\AccessControl',
            'rules' => [
                ['allow' => true, 'actions' => ['admin'], 'roles' => ['@']],
            ],
        ],
    ];
}
```

詳細については [フィルタ](structure-filters.md) のセクションを参照してください。


アセット
--------

Yii 2.0 は、*アセット・バンドル* と呼ばれる新しい概念を導入しました。これは、Yii 1.1 にあったスクリプト・パッケージの概念を置き換えるものです。

アセット・バンドルは、あるディレクトリの下に集められた一群のアセット・ファイル (例えば、JavaScript ファイル、CSS ファイル、イメージ・ファイルなど) です。
それぞれのアセット・バンドルは [[yii\web\AssetBundle]] を拡張したクラスとして表わされます。
アセット・バンドルを [[yii\web\AssetBundle::register()]] を通じて登録することによって、
そのバンドルに含まれるアセットにウェブ経由でアクセスできるようになります。
Yii 1 とは異なり、バンドルを登録したページは、そのバンドルで指定されている JavaScript と CSS ファイルへの参照を自動的に含むようになります。

詳細については [アセット](structure-assets.md) のセクションを参照してください。


ヘルパ
------

Yii 2.0 はよく使われるスタティックなヘルパ・クラスを数多く導入しました。それには以下のものが含まれます。

* [[yii\helpers\Html]]
* [[yii\helpers\ArrayHelper]]
* [[yii\helpers\StringHelper]]
* [[yii\helpers\FileHelper]]
* [[yii\helpers\Json]]

詳細については、[ヘルパの概要](helper-overview.md) のセクションを参照してください。

フォーム
--------

Yii 2.0 は [[yii\widgets\ActiveForm]] を使ってフォームを作成する際に使用する *フィールド* の概念を導入しました。
フィールドは、ラベル、インプット、エラー・メッセージ および/または ヒント・テキストを含むコンテナです。
フィールドは [[yii\widgets\ActiveField|ActiveField]] のオブジェクトとして表現されます。
フィールドを使うことによって、以前よりもすっきりとフォームを作成することが出来るようになりました。

```php
<?php $form = yii\widgets\ActiveForm::begin(); ?>
    <?= $form->field($model, 'username') ?>
    <?= $form->field($model, 'password')->passwordInput() ?>
    <div class="form-group">
        <?= Html::submitButton('ログイン') ?>
    </div>
<?php yii\widgets\ActiveForm::end(); ?>
```

詳細については [フォームを作成する](input-forms.md) のセクションを参照してください。


クエリ・ビルダ
--------------

1.1 においては、クエリの構築が `CDbCommand`、`CDbCriteria`、`CDbCommandBuilder` など、いくつかのクラスに散らばっていました。
Yii 2.0 は DB クエリを [[yii\db\Query|Query]] オブジェクトの形で表現します。
このオブジェクトが舞台裏で [[yii\db\QueryBuilder|QueryBuilder]] の助けを得て SQL 文に変換されます。
例えば、

```php
$query = new \yii\db\Query();
$query->select('id, name')
      ->from('user')
      ->limit(10);

$command = $query->createCommand();
$sql = $command->sql;
$rows = $command->queryAll();
```

何より良いのは、このようなクエリ構築メソッドが [アクティブ・レコード](db-active-record.md) を扱う時にも使える、ということです。

詳細については [クエリ・ビルダ](db-query-builder.md) のセクションを参照してください。


アクティブ・レコード
--------------------

Yii 2.0 は [アクティブ・レコード](db-active-record.md) に数多くの変更を導入しました。
最も顕著な違いは、クエリの構築方法とリレーショナル・クエリの処理の二つです。

1.1 の `CDbCriteria` クラスは Yii 2 では [[yii\db\ActiveQuery]] に置き換えられました。このクラスは [[yii\db\Query]] を拡張したものであり、従って全てのクエリ構築メソッドを継承します。
以下のように、[[yii\db\ActiveRecord::find()]] を呼んでクエリの構築を開始します。

```php
// 全てのアクティブな顧客を読み出し、ID によって並べる
$customers = Customer::find()
    ->where(['status' => $active])
    ->orderBy('id')
    ->all();
```

リレーションを宣言するために必要なことは、[[yii\db\ActiveQuery|ActiveQuery]] オブジェクトを返す getter メソッドを定義するだけのことです。
getter によって定義されたプロパティの名前がリレーションの名前を表します。例えば、以下のコードは `orders` リレーションを宣言するものです
(1.1 では `relations()` という一個の中枢でリレーションを宣言しなければなりませんでした)。

```php
class Customer extends \yii\db\ActiveRecord
{
    public function getOrders()
    {
        return $this->hasMany('Order', ['customer_id' => 'id']);
    }
}
```

こうすることで、`$customer->orders` という構文によって関連テーブルにある顧客のオーダにアクセスすることが出来るようになります。
また、下記のコードを用いて、カスタマイズしたクエリ条件によるリレーショナル・クエリをその場で実行することも出来ます。

```php
$orders = $customer->getOrders()->andWhere('status=1')->all();
```

リレーションをイーガー・ロードするとき、Yii 2.0 は 1.1 とは異なる動きをします。
具体的に言うと、1.1 では JOIN クエリが生成されて、主レコードと関連レコードの両方がセレクトされていました。
Yii 2.0 では、JOIN を使わずに二つの SQL 文が実行されます。
すなわち、第一の SQL 文が主たるレコードを返し、第二の SQL 文は主レコードのプライマリ・キーを使うフィルタリングによって関連レコードを返します。

多数のレコードを返すクエリを構築するときは、[[yii\db\ActiveRecord|ActiveRecord]] オブジェクトを返す代りに、[[yii\db\ActiveQuery::asArray()|asArray()]] メソッドをチェインすることが出来ます。
そうすると、クエリ結果は配列として返されることになり、レコードの数が多い場合は、必要とされる CPU 時間とメモリを著しく削減することが出来ます。
例えば、

```php
$customers = Customer::find()->asArray()->all();
```

もう一つの変更点は、属性のデフォルト値を public なプロパティによって定義することは出来なくなった、ということです。
デフォルト値を定義する必要がある場合は、アクティブ・レコード・クラスの `init` メソッドの中で設定しなければなりません。

```php
public function init()
{
    parent::init();
    $this->status = self::STATUS_NEW;
}
```

1.1 では、アクティブ・レコード・クラスのコンストラクタをオーバーライドすることについて、いくつか問題がありました。バージョン 2.0 では、もう問題はありません。
コンストラクタにパラメータを追加する場合は、[[yii\db\ActiveRecord::instantiate()]] をオーバーライドする必要があるかもしれないことに注意してください。

アクティブ・レコードについては、他にも多くの変更と機能強化がなされています。
詳細については [アクティブ・レコード](db-active-record.md) のセクションを参照してください。


アクティブ・レコードのビヘイビア
--------------------------------

2.0 では基底のビヘイビア・クラス `CActiveRecordBehavior` を廃止しました。
アクティブ・レコードのビヘイビアを作成したいときは、直接に `yii\base\Behavior` を拡張しなければなりません。
ビヘイビア・クラスがオーナーの何らかのイベントに反応する必要がある場合は、以下のように `events()` メソッドをオーバーライドしなければなりません。

```php
namespace app\components;

use yii\db\ActiveRecord;
use yii\base\Behavior;

class MyBehavior extends Behavior
{
    // ...

    public function events()
    {
        return [
            ActiveRecord::EVENT_BEFORE_VALIDATE => 'beforeValidate',
        ];
    }

    public function beforeValidate($event)
    {
        // ...
    }
}
```


User と IdentityInterface
-------------------------

1.1 の `CWebUser` クラスは [[yii\web\User]] に取って換られました。
そして `CUserIdentity` クラスはもうありません。代りに、使い方がもっと単純な [[yii\web\IdentityInterface]] を実装しなければなりません。
アドバンスト・プロジェクト・テンプレートがそういう例を提供しています。

詳細は [認証](security-authentication.md)、[権限付与](security-authorization.md)、そして [アドバンスト・プロジェクト・テンプレート](https://www.yiiframework.com/extension/yiisoft/yii2-app-advanced/doc/guide) のセクションを参照してください。


URL 管理
--------

Yii 2 の URL 管理は 1.1 のそれと似たようなものです。
主な機能強化は、URL 管理がオプションのパラメータをサポートするようになったことです。
例えば、下記のような規則を宣言した場合に、`post/popular` と `post/1/popular` の両方に合致するようになります。
1.1 では、同じ目的を達成するためには、二つの規則を使う必要がありました。

```php
[
    'pattern' => 'post/<page:\d+>/<tag>',
    'route' => 'post/index',
    'defaults' => ['page' => 1],
]
```

詳細については [ルーティングと URL 生成](runtime-routing.md) のセクションを参照してください。

ルートの命名規約における重要な変更は、コントローラとアクションのキャメル・ケースの名前が
各単語をハイフンで分けた小文字の名前になるようになった、という点です。
例えば、`CamelCaseController` のコントローラ ID は `camel-case` となります。
詳細については、[コントローラ ID](structure-controllers.md#controller-ids) と [アクション ID](structure-controllers.md#action-ids) のセクションを参照してください。


Yii 1.1 と 2.x を一緒に使う
---------------------------

Yii 2.0 と一緒に使いたい Yii 1.1 のレガシー・コードを持っている場合は、
[Yii 1.1 と 2.0 を一緒に使う](tutorial-yii-integration.md#using-both-yii2-yii) のセクションを参照してください。

