フィクスチャ
============

フィクスチャはテストの重要な部分です。
フィクスチャの主な目的は、テストを期待されている方法で繰り返して実行できるように、環境を固定された既知の状態に設定することです。
Yii は、Codeception でテストを実行する場合でも、単独でテストを実行する場合でも、
フィクスチャを正確に定義して容易に使うことが出来るように、フィクスチャ・フレームワークを提供しています。

Yii のフィクスチャ・フレームワークにおける鍵となる概念は、いわゆる *フィクスチャ・オブジェクト* です。
フィクスチャ・オブジェクトはテスト環境のある特定の側面を表現するもので、[[yii\test\Fixture]] またはその子クラスのインスタンスです。
例えば、ユーザの DB テーブルが固定されたデータセットを含むことを保証するために `UserFixture` を使う、という具合です。
テストを実行する前に一つまたは複数のフィクスチャ・オブジェクトをロードし、テストの完了時にアンロードします。

フィクスチャは他のフィクスチャに依存する場合があります。依存は [[yii\test\Fixture::depends]] プロパティによって定義されます。
フィクスチャがロードされるとき、依存するフィクスチャはそのフィクスチャの前に自動的にロードされます。
そしてフィクスチャがアンロードされるときには、依存するフィクスチャはそのフィクスチャの後にアンロードされます。


## フィクスチャを定義する

フィクスチャを定義するためには、[[yii\test\Fixture]] または [[yii\test\ActiveFixture]] を拡張して新しいクラスを作ります。
前者は汎用目的のフィクスチャに最も適しています。
一方、後者はデータベースとアクティブ・レコードを扱うために専用に設計された拡張機能を持っています。

次のコードは、`User` アクティブ・レコードとそれに対応するテーブルに関して、フィクスチャを定義するものです。

```php
<?php
namespace app\tests\fixtures;

use yii\test\ActiveFixture;

class UserFixture extends ActiveFixture
{
    public $modelClass = 'app\models\User';
}
```

> Tip: すべての `ActiveFixture` は、テストの目的のために DB テーブルを準備するものです。
> [[yii\test\ActiveFixture::tableName]] プロパティまたは [[yii\test\ActiveFixture::modelClass]] プロパティを設定することによって、テーブルを指定することが出来ます。
> 後者を使う場合は、`modelClass` によって指定される `ActiveRecord` クラスからテーブル名が取得されます。

> Note: [[yii\test\ActiveFixture]] は SQL データベースにのみ適しています。
> NoSQL データベースのためには、Yii は以下の `ActiveFixture` クラスを提供しています。
>
> - Mongo DB: [[yii\mongodb\ActiveFixture]]
> - Elasticsearch: [[yii\elasticsearch\ActiveFixture]] (バージョン 2.0.2 以降)


`ActiveFixture` フィクスチャのフィクスチャ・データは通常は `FixturePath/data/TableName.php` として配置されるファイルで提供されます。
ここで `FixturePath` はフィクスチャ・クラス・ファイルを含むディレクトリを意味し、`TableName` はフィクスチャと関連付けられているテーブルの名前です。
上記の例では、ファイルは `@app/tests/fixtures/data/user.php` となります。
データ・ファイルは、ユーザのテーブルに挿入されるデータ行の配列を返さなければなりません。
例えば、

```php
<?php
return [
    'user1' => [
        'username' => 'lmayert',
        'email' => 'strosin.vernice@jerde.com',
        'auth_key' => 'K3nF70it7tzNsHddEiq0BZ0i-OU8S3xV',
        'password' => '$2y$13$WSyE5hHsG1rWN2jV8LRHzubilrCLI5Ev/iK0r3jRuwQEs2ldRu.a2',
    ],
    'user2' => [
        'username' => 'napoleon69',
        'email' => 'aileen.barton@heaneyschumm.com',
        'auth_key' => 'dZlXsVnIDgIzFgX4EduAqkEPuphhOh9q',
        'password' => '$2y$13$kkgpvJ8lnjKo8RuoR30ay.RjDf15bMcHIF7Vz1zz/6viYG5xJExU6',
    ],
];
```

データ行にはエイリアスを付けることが出来て、後でテストのときにエイリアスを使って行を参照することが出来ます。
上の例では、二つの行はそれぞれ `user1` および `user2` というエイリアスを付けられています。

また、オート・インクリメントのカラムに対してはデータを指定する必要はありません。
フィクスチャがロードされるときに Yii が自動的に実際の値を行に入れます。

> Tip: [[yii\test\ActiveFixture::dataFile]] プロパティを設定して、データ・ファイルの所在をカスタマイズすることが出来ます。
> [[yii\test\ActiveFixture::getData()]] をオーバーライドしてデータを提供することも可能です。

前に説明したように、フィクスチャは別のフィクスチャに依存する場合があります。
例えば、ユーザ・プロファイルのテーブルはユーザのテーブルを指す外部キーを含んでいるため、`UserProfileFixture` は `UserFixture` に依存します。
依存関係は、次のように、[[yii\test\Fixture::depends]] プロパティによって指定されます。

```php
namespace app\tests\fixtures;

use yii\test\ActiveFixture;

class UserProfileFixture extends ActiveFixture
{
    public $modelClass = 'app\models\UserProfile';
    public $depends = ['app\tests\fixtures\UserFixture'];
}
```

依存関係は、また、複数のフィクスチャが正しく定義された順序でロードされ、アンロードされることを保証します。
上記の例では、全ての外部キー参照が存在することを保証するために `UserFixture` は常に `UserProfileFixture` の前にロードされます。
また、同じ理由によって、`UserFixture` は常に `UserProfileFixture` がアンロードされた後でアンロードされます。

上記では、DB テーブルに関してフィクスチャを定義する方法を示しました。
DB と関係しないフィクスチャ (例えば、何らかのファイルやディレクトリに関するフィクスチャ) を定義するためには、より汎用的な基底クラス [[yii\test\Fixture]] から拡張して、
[[yii\test\Fixture::load()|load()]] と [[yii\test\Fixture::unload()|unload()]] のメソッドをオーバーライドすることが出来ます。


## フィクスチャを使用する

[Codeception](http://codeception.com/) を使ってコードをテストしている場合は、フィクスチャのローディングとアクセスについては、
内蔵されているサポートを使用することが出来ます。

その他のテスト・フレームワークを使っている場合は、テスト・ケースで [[yii\test\FixtureTrait]]
を使って同じ目的を達することが出来ます。

次に、Codeception を使って `UserProfile` 単体テストクラスを書く方法を説明します。

`\Codeception\Test\Unit` を拡張するあなたの単体テスト・クラスにおいて、 `_fixtures()` メソッドの中で使いたいフィクスチャを宣言するか、
または、アクターの `haveFixtures()` メソッドを直接に使用します。例えば、

```php
namespace app\tests\unit\models;


use app\tests\fixtures\UserProfileFixture;

class UserProfileTest extends \Codeception\Test\Unit
{   
    public function _fixtures()
    {
        return [
            'profiles' => [
                'class' => UserProfileFixture::class,
                // フィクスチャ・データは tests/_data/user.php に配置されている
                'dataFile' => codecept_data_dir() . 'user.php'
            ],
        ];
    }

    // ... テストのメソッド ...
}
```

`_fixtures()` メソッドにリストされたフィクスチャは、テストが実行される前に自動的にロードされます。
前に説明したように、フィクスチャがロードされるときには、それが依存するフィクスチャのすべてが自動的に先にロードされます。
上の例では、`UserProfileFixture` は `UserFixture` に依存しているので、テスト・クラスのどのテスト・メソッドを走らせるときでも、二つのフィクスチャが連続してロードされます。
すなわち、最初に `UserFixture` がロードされ、次に `UserProfileFixture` がロードされます。

`_fixtures()` でフィクスチャを指定するときも、`haveFixtures()` でフィクスチャを指定するときも、
クラス名あるいはフィクスチャを指す構成情報配列を使うことが出来ます。
構成情報配列を使うと、フィクスチャがロードされるときのフィクスチャのプロパティをカスタマイズすることが出来ます。

また、フィクスチャにエイリアスを割り当てることも出来ます。上記の例では、`UserProfileFixture` に `profiles` というエイリアスが与えられています。
そうすると、テスト・メソッドの中でエイリアスを使ってフィクスチャ・オブジェクトにアクセスすることが出来るようになります。例えば、

```php
$profile = $I->grabFixture('profiles', 'user1');
```

は `UserProfileFixture` オブジェクトを返します。

さらには、`UserProfileFixture` は `ActiveFixture` を拡張するものですので、フィクスチャによって提供されたデータに対して、
次の構文を使ってアクセスすることも出来ます。

```php
// 'user1' というエイリアスのデータ行に対応する UserProfileModel を返す
$profile = $I->grabFixture('profiles', 'user1');
// フィクスチャにある全てのデータ行をたどる
foreach ($I->grabFixture('profiles') as $profile) ...
```

## フィクスチャ・クラスとデータ・ファイルを編成する

デフォルトでは、フィクスチャ・クラスは対応するデータ・ファイルを探すときに、フィクスチャのクラス・ファイルを含むフォルダのサブ・フォルダである `data` フォルダの中を見ます。
簡単なプロジェクトではこの規約に従うことができます。
大きなプロジェクトでは、おそらくは、同じフィクスチャ・クラスを異なるテストに使うために、データ・ファイルを切り替える必要がある場合が頻繁に生じるでしょう。
従って、クラスの名前空間と同じように、データ・ファイルを階層的な方法で編成することを推奨します。
例えば、

```
# tests\unit\fixtures フォルダの下に

data\
    components\
        fixture_data_file1.php
        fixture_data_file2.php
        ...
        fixture_data_fileN.php
    models\
        fixture_data_file1.php
        fixture_data_file2.php
        ...
        fixture_data_fileN.php
# 等々
```

このようにして、テスト間でフィクスチャのデータ・ファイルが衝突するのを回避し、必要に応じてデータ・ファイルを使い分けます。

> Note: 上の例では、フィクスチャ・ファイルには例示目的だけの名前が付けられています。
> 実際の仕事では、フィクスチャ・クラスがどのフィクスチャ・クラスを拡張したものであるかに従って名前を付けるべきです。
> 例えば、DB フィクスチャを [[yii\test\ActiveFixture]] から拡張している場合は、DB テーブルの名前をフィクスチャのデータ・ファイル名として使うべきです。
> MongoDB フィクスチャを [[yii\mongodb\ActiveFixture]] から拡張している場合は、コレクション名をファイル名として使うべきです。

同様な階層は、フィクスチャ・クラス・ファイルを編成するのにも使うことが出来ます。
`data` をルート・ディレクトリとして使うのでなく、データ・ファイルとの衝突を避けるために `fixtures` をルート・ディレクトリとして使うのが良いでしょう。

## `yii fixture` でフィクスチャを管理する

Yii は `yii fixture` コマンドライン・ツールでフィクスチャをサポートしています。以下の機能をサポートしています。

* 異なるストレージ (RDBMS、NoSQL など) へのフィクスチャのロード
* 様々な方法でのフィクスチャのアンロード (通常はストレージをクリア)
* フィクスチャの自動生成およびランダム・データの投入

### フィクスチャのデータ形式

次のようなフィクスチャ・データをロードするとしましょう。

```
# users.php ファイル - フィクスチャ・データ・パス (デフォルトでは @tests\unit\fixtures\data) に保存

return [
    [
        'name' => 'Chase',
        'login' => 'lmayert',
        'email' => 'strosin.vernice@jerde.com',
        'auth_key' => 'K3nF70it7tzNsHddEiq0BZ0i-OU8S3xV',
        'password' => '$2y$13$WSyE5hHsG1rWN2jV8LRHzubilrCLI5Ev/iK0r3jRuwQEs2ldRu.a2',
    ],
    [
        'name' => 'Celestine',
        'login' => 'napoleon69',
        'email' => 'aileen.barton@heaneyschumm.com',
        'auth_key' => 'dZlXsVnIDgIzFgX4EduAqkEPuphhOh9q',
        'password' => '$2y$13$kkgpvJ8lnjKo8RuoR30ay.RjDf15bMcHIF7Vz1zz/6viYG5xJExU6',
    ],
];
```
データベースにデータをロードするフィクチャを使う場合は、これらの行が `users` テーブルに対して適用されます。NoSQL フィクスチャ、例えば `mongodb` フィクチャを使う場合は、このデータは `users` コレクションに対して適用されます。
さまざまなロード戦略を実装する方法などについて公式 [ドキュメント](https://github.com/yiisoft/yii2/blob/master/docs/guide/test-fixtures.md)を参照して下さい。
上記のフィクスチャのサンプルは `yii2-faker` エクステンションによって生成されました。これについての詳細は、[自動生成のセクション](#auto-generating-fixtures) を参照して下さい。
フィクスチャ・クラスの名前は複数形であってはいけません。

### フィクスチャをロードする

フィクスチャ・クラスは `Fixture` という接尾辞を持たなければいけません。デフォルトでは、フィクスチャは `tests\unit\fixtures` 名前空間の下で探されます。
この挙動は構成またはコマンド・オプションによって変更することが出来ます。`-User` のように名前の前に `-` を指定することで、ロードまたはアンロードから除外するフィクスチャを指定することが出来ます。

フィクスチャをロードするためには、次のコマンドを実行します。

> Note: データをロードする前に、アンロードのシーケンスが実行されます。これによって、通常は、前に実行されたフィクスチャによって挿入された 既存のデータが全てクリーンアップされることになります。

```
yii fixture/load <fixture_name>
```

要求される `fixture_name` パラメータが、データがロードされるフィクスチャの名前を指定するものです。
いくつかのフィクスチャを一度にロードすることが出来ます。下記はこのコマンドの正しい形式です。

```
// `User` フィクスチャをロードする
yii fixture/load User

// 上記と同じ、"fixture" コマンドのデフォルトのアクションは "load" であるため
yii fixture User

// いくつかのフィクスチャをロードする
yii fixture "User, UserProfile"

// 全てのフィクスチャをロードする
yii fixture/load "*"

// 同上
yii fixture "*"

// 一つを除いて全てのフィクスチャをロードする
yii fixture "*, -DoNotLoadThisOne"

// 異なる名前空間からフィクスチャをロードする (デフォルトの名前空間は tests\unit\fixtures)
yii fixture User --namespace='alias\my\custom\namespace'

// 他のフィクスチャをロードする前に、グローバルフィクスチャ `some\name\space\CustomFixture` をロードする。
// デフォルトでは、このオプションが `InitDbFixture` について適用され、整合性チェックが無効化/有効化されます。
// カンマで区切って複数のグローバル・フィクスチャを指定することが出来ます。
yii fixture User --globalFixtures='some\name\space\Custom'
```

### フィクスチャをアンロードする

フィクスチャをアンロードするためには、次のコマンドを実行します。

```
// Users フィクスチャをアンロードする。デフォルトではフィクスチャのストレージをクリアします。(例えば、"users" テーブル、または、mongodb フィクスチャなら "users" コレクションがクリアされます。)
yii fixture/unload User

// いくつかのフィクスチャをアンロードする
yii fixture/unload "User, UserProfile"

// すべてのフィクスチャをアンロードする
yii fixture/unload "*"

// 一つを除いて全てのフィクスチャをアンロードする
yii fixture/unload "*, -DoNotUnloadThisOne"

```

このコマンドでも、`namespace` や `globalFixtures` という同じオプションを適用することが出来ます。

### コマンドをグローバルに構成する

コマンドライン・オプションはフィクスチャ・コマンドをその場で構成することを可能にするものですが、
コマンドを一度だけ構成して済ませたい場合もあります。
例えば、次のように、異なるフィクスチャのパスを構成することが出来ます。

```
'controllerMap' => [
    'fixture' => [
        'class' => 'yii\console\controllers\FixtureController',
        'namespace' => 'myalias\some\custom\namespace',
        'globalFixtures' => [
            'some\name\space\Foo',
            'other\name\space\Bar'
        ],
    ],
]
```

### フィクスチャを自動生成する

Yii は、あなたの代りに、何らかのテンプレートに従ってフィクスチャを自動生成することが出来ます。さまざまなデータで、また、いろいろな言語と形式で、フィクスチャを生成することが出来ます。
この機能は、[Faker](https://github.com/fzaninotto/Faker) ライブラリと `yii2-faker` エクステンションによって実現されています。
詳細については、エクステンションの [ガイド](https://github.com/yiisoft/yii2-faker/tree/master/docs/guide-ja) を参照して下さい。.

## まとめ

以上、フィクスチャを定義して使用する方法を説明しました。
下記に、DB に関連した単体テストを走らせる場合の典型的なワークフローをまとめておきます。

1. `yii migrate` ツールを使って、テストのデータベースを最新版にアップグレードする
2. テスト・ケースを走らせる
   - フィクスチャをロードする - 関係する DB テーブルをクリーンアップし、フィクスチャ・データを投入する
   - 実際のテストを実行する
   - フィクスチャをアンロードする
3. 全てのテストが完了するまで、ステップ 2 を繰り返す
