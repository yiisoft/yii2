クラスのオートローディング
=================

Yiiは、必要となるすべてのクラスファイルを特定してインクルードするにあたり、 [クラスのオートローディングメカニズム](http://www.php.net/manual/ja/language.oop5.autoload.php)
を頼りにします。Yii は、[PSR-4 標準](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-4-autoloader.md) に準拠した、高性能なクラスのオートローダーを提供しています。
このオートローダーは、あなたが `Yii.php` ファイルをインクルードするときにインストールされます。

> Note: 説明を簡単にするため、このセクションではクラスのオートローディングについてのみお話しします。しかし、
  ここに記述されている内容は、同様に、インタフェースとトレイトのオートロードにも適用されることに注意してください。


Yii オートローダーの使用 <span id="using-yii-autoloader"></span>
------------------------

Yii のクラスオートローダーを使用するには、自分のクラスを作成して名前を付けるとき、次の2つの単純なルールに従わなければなりません:

* 各クラスは [名前空間](http://php.net/manual/ja/language.namespaces.php) の下になければなりません (例 `foo\bar\MyClass`)
* 各クラスは次のアルゴリズムで決定される個別のファイルに保存されなければなりません:

```php
// $className は先頭にバックスラッシュを持たない完全修飾クラス名
$classFile = Yii::getAlias('@' . str_replace('\\', '/', $className) . '.php');
```
たとえば、クラス名と名前空間が `foo\bar\MyClass` であれば、対応するクラスファイルのパスの [エイリアス](concept-aliases.md) は、
`@foo/bar/MyClass.php` になります。このエイリアスがファイルパスとして解決できるようにするためには、`@foo` または `@foo/bar`
のどちらかが、 [ルートエイリアス](concept-aliases.md#defining-aliases) でなければなりません。

[ベーシックプロジェクトテンプレート](start-installation.md) を使用している場合、最上位の名前空間 `app` の下にクラスを置くことができ、
そうすると、新しいエイリアスを定義しなくても、Yii によってそれらをオートロードできるようになります。これは `@app`
が [事前定義されたエイリアス](concept-aliases.md#predefined-aliases) であるためで、`app\components\MyClass` のようなクラス名を
今説明したアルゴリズムに従って、クラスファイル `AppBasePath/components/MyClass.php` であると解決することが出来ます。

[アドバンストプロジェクトテンプレート](https://github.com/yiisoft/yii2-app-advanced/blob/master/docs/guide-ja/README.md) では、各層がそれ自身のルートエイリアスを持っています。たとえば、
フロントエンド層はルートエイリアス `@frontend` を持ち、バックエンド層のルートエイリアスは `@backend` です。その結果、名前空間 `frontend` の下に
フロントエンドクラスを置き、バックエンドクラスを `backend` の下に置けます。これで、これらのクラスは Yii のオートローダーによって
オートロードできるようになります。


クラスマップ <span id="class-map"></span>
---------

Yii のクラスオートローダーは、 *クラスマップ* 機能をサポートしており、クラス名を対応するクラスファイルのパスにマップできます。
オートローダーがクラスをロードしているとき、クラスがマップに見つかるかどうかを最初にチェックします。もしあれば、対応する
ファイルのパスは、それ以上チェックされることなく、直接インクルードされます。これでクラスのオートローディングを非常に高速化できます。
実際に、すべての Yii のコアクラスは、この方法でオートロードされています。

次の方法で、 `Yii::$classMap` に格納されるクラスマップにクラスを追加できます:

```php
Yii::$classMap['foo\bar\MyClass'] = 'path/to/MyClass.php';
```

クラスファイルのパスを指定するのに、 [エイリアス](concept-aliases.md) を使うことができます。クラスが使用される前にマップが準備できるように、
[ブートストラップ](runtime-bootstrapping.md) プロセス内でクラスマップを設定する必要があります。


他のオートローダーの使用 <span id="using-other-autoloaders"></span>
-----------------------

Yii はパッケージ依存関係マネージャとして Composer を包含しているので、Composer のオートローダーもインストールすることをお勧めします。
あなたが独自のオートローダーを持つサードパーティライブラリを使用している場合、それらもインストールする必要があります。

Yii オートローダーを他のオートローダーと一緒に使うときは、他のすべてのオートローダーがインストールされた *後で* 、 `Yii.php`
ファイルをインクルードする必要があります。これで Yii のオートローダーが、任意クラスのオートローディング要求に応答する最初のものになります。
たとえば、次のコードは [ベーシックプロジェクトテンプレート](start-installation.md) の [エントリスクリプト](structure-entry-scripts.md) から抜粋したものです。
最初の行は、Composer のオートローダーをインストールしており、二行目は Yii のオートローダーをインストールしています。

```php
require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../vendor/yiisoft/yii2/Yii.php';
```

あなたは Yii のオートローダーを使わず、Composer のオートローダーだけを単独で使用することもできます。しかし、そうすることによって、
あなたのクラスのオートローディングのパフォーマンスは低下し、クラスをオートロード可能にするために Composer が設定したルールに従わなければならなくなります。

> Info: Yiiのオートローダーを使用したくない場合は、 `Yii.php` ファイルのあなた独自のバージョンを作成し、
  それを [エントリスクリプト](structure-entry-scripts.md) でインクルードする必要があります。


エクステンションクラスのオートロード <span id="autoloading-extension-classes"></span>
-----------------------------

Yii のオートローダーは、 [エクステンション](structure-extensions.md) クラスのオートロードが可能です。唯一の要件は、
エクステンションがその `composer.json` ファイルに正しく `autoload` セクションを指定していることです。
`autoload` 指定方法の詳細については [Composer のドキュメント](https://getcomposer.org/doc/04-schema.md#autoload) 参照してください。

Yii のオートローダーを使用しない場合でも、まだ Composer のオートローダーがエクステンションクラスをオートロードすることが可能です。
