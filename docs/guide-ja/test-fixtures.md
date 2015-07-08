フィクスチャ
============

フィクスチャはテストの重要な部分です。
フィクスチャの主な目的は、テストを期待されている方法で繰り返して実行できるように、環境を固定された既知の状態に設定することです。
Yii は、フィクスチャを正確に定義して容易に使うことを可能にするフィクスチャフレームワークを提供しています。

Yii のフィクスチャフレームワークにおける鍵となる概念は、いわゆる *フィクスチャオブジェクト* です。
フィクスチャオブジェクトはテスト環境のある特定の側面を表現するもので、[[yii\test\Fixture]] またはその子クラスのインスタンスです。
例えば、ユーザの DB テーブルが固定されたデータセットを含むことを保証するために `UserFixture` を使う、という具合です。
テストを実行する前に一つまたは複数のフィクスチャオブジェクトをロードし、テストの完了時にアンロードします。

フィクスチャは他のフィクスチャに依存する場合があります。依存は [[yii\test\Fixture::depends]] プロパティによって定義されます。
フィクスチャがロードされるとき、依存するフィクスチャはそのフィクスチャの前に自動的にロードされます。
そしてフィクスチャがアンロードされるときには、依存するフィクスチャはそのフィクスチャの後にアンロードされます。


フィクスチャを定義する
----------------------

フィクスチャを定義するためには、[[yii\test\Fixture]] または [[yii\test\ActiveFixture]] を拡張して新しいクラスを作ります。
前者は汎用目的のフィクスチャに最も適しています。
一方、後者はデータベースとアクティブレコードを扱うために専用に設計された拡張機能を持っています。

次のコードは、`User` アクティブレコードとそれに対応するテーブルに関して、フィクスチャを定義するものです。

```php
<?php
namespace app\tests\fixtures;

use yii\test\ActiveFixture;

class UserFixture extends ActiveFixture
{
    public $modelClass = 'app\models\User';
}
```

> Tip|ヒント: すべての `ActiveFixture` は、テストの目的のために DB テーブルを準備するものです。
> [[yii\test\ActiveFixture::tableName]] プロパティまたは [[yii\test\ActiveFixture::modelClass]] プロパティを設定することによって、テーブルを指定することが出来ます。
> 後者を使う場合は、`modelClass` によって指定される `ActiveRecord` クラスからテーブル名が取得されます。

> Note|注意: [[yii\test\ActiveFixture]] は SQL データベースにのみ適しています。
> NoSQL データベースのためには、Yii は以下の `ActiveFixture` クラスを提供しています。
>
> - Mongo DB: [[yii\mongodb\ActiveFixture]]
> - Elasticsearch: [[yii\elasticsearch\ActiveFixture]] (バージョン 2.0.2 以降)


`ActiveFixture` フィクスチャのフィクスチャデータは通常は `FixturePath/data/TableName.php` として配置されるファイルで提供されます。
ここで `FixturePath` はフィクスチャクラスファイルを含むディレクトリを意味し、`TableName` はフィクスチャと関連付けられているテーブルの名前です。
上記の例では、ファイルは `@app/tests/fixtures/data/user.php` となります。
データファイルは、ユーザのテーブルに挿入されるデータ行の配列を返さなければなりません。
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

また、オートインクリメントのカラムに対してはデータを指定する必要はありません。
フィクスチャがロードされるときに Yii が自動的に実際の値を行に入れます。

> Tip|ヒント: [[yii\test\ActiveFixture::dataFile]] プロパティを設定して、データファイルの所在をカスタマイズすることが出来ます。
> [[yii\test\ActiveFixture::getData()]] をオーバーライドしてデータを提供することも可能です。

前に説明したように、フィクスチャは別のフィクスチャに依存する場合があります。
例えば、ユーザプロファイルのテーブルはユーザのテーブルを指す外部キーを含んでいるため、`UserProfileFixture` は `UserFixture` に依存します。
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
DB と関係しないフィクスチャ (例えば、何らかのファイルやディレクトリに関するフィクスチャ) を定義するためには、より汎用的な基底クラス [[yii\test\Fixture]] から拡張して、[[yii\test\Fixture::load()|load()]] と [[yii\test\Fixture::unload()|unload()]] のメソッドをオーバーライドすることが出来ます。


フィクスチャを使用する
----------------------

[CodeCeption](http://codeception.com/) を使ってコードをテストしている場合は、フィクスチャのローディングとアクセスを内蔵でサポートしている `yii2-codeception` を使用することを検討すべきです。
その他のテストフレームワークを使っている場合は、テストケースで [[yii\test\FixtureTrait]] を使って同じ目的を達することが出来ます。

次に `yii2-codeception` を使って `UserProfile` 単体テストを書く方法を説明します。

[[yii\codeception\DbTestCase]] または [[yii\codeception\TestCase]] を拡張する単体テストクラスにおいて、どのフィクスチャを使用したいかを [[yii\test\FixtureTrait::fixtures()|fixtures()]] メソッドの中で宣言します。
例えば、

```php
namespace app\tests\unit\models;

use yii\codeception\DbTestCase;
use app\tests\fixtures\UserProfileFixture;

class UserProfileTest extends DbTestCase
{
    public function fixtures()
    {
        return [
            'profiles' => UserProfileFixture::className(),
        ];
    }

    // ... テストのメソッド ...
}
```

`fixtures()` メソッドにリストされたメソッドは、テストケースの中のどのテストメソッドが走る前にも自動的にロードされ、テストメソッドが完了した後にアンロードされます。
前に説明したように、フィクスチャがロードされるときには、それが依存するフィクスチャのすべてが最初に自動的にロードされます。
上の例では、`UserProfileFixture` は `UserFixture` に依存しているので、テストクラスのどのテストメソッドを走らせるときでも、二つのフィクスチャが連続してロードされます。
すなわち、最初に `UserFixture` がロードされ、次に `UserProfileFixture` がロードされます。

`fixtures()` でフィクスチャを指定するときは、クラス名あるいはフィクスチャを指す構成情報配列を使うことが出来ます。
構成情報配列を使うと、フィクスチャがロードされるときのフィクスチャのプロパティをカスタマイズすることが出来ます。

また、フィクスチャにエイリアスを割り当てることも出来ます。
上記の例では、`UserProfileFixture` に `profiles` というエイリアスが与えられています。
そうすると、テストメソッドの中でエイリアスを使ってフィクスチャオブジェクトにアクセスすることが出来るようになります。
例えば、`$this->profiles` が `UserProfileFixture` を返すことになります。

さらには、`UserProfileFixture` は `ActiveFixture` を拡張するものですので、フィクスチャによって提供されたデータに対して、次の構文を使ってアクセスすることも出来ます。

```php
// 'user1' というエイリアスのデータ行を返す
$row = $this->profiles['user1'];
// 'user1' というエイリアスのデータ行に対応する UserProfileModel を返す
$profile = $this->profiles('user1');
// フィクスチャにある全てのデータ行をたどる
foreach ($this->profiles as $row) ...
```

> Info|情報: `$this->profiles` は依然として `UserProfileFixture` という型です。
> 上記のアクセス機能は PHP マジックメソッドによって実装されています。


グローバルフィクスチャを定義して使用する
----------------------------------------

上記で説明されたフィクスチャは主として個別のテストケースによって使われます。
たいていの場合、全てまたは多くのテストケースに適用されるグローバルなフィクスチャもいくつか必要になります。
一例は、[[yii\test\InitDbFixture]] で、これは二つのことをします。

* `@app/tests/fixtures/initdb.php` に配置されたスクリプトを実行して、いくつかの共通の初期化作業を行う。
* 他の DB フィクスチャをロードする前に、データベースの整合性チェックを無効化し、他の DB フィクスチャがアンロードされた後で、それを再び有効化する。

グローバルフィクスチャの使い方は、グローバルでないフィクスチャと同じです。
違うところは、グローバルフィクスチャは `fixtures()` ではなく [[yii\codeception\TestCase::globalFixtures()]] で宣言するという点です。
テストケースがフィクスチャをロードするときは、最初にグローバルフィクスチャをロードし、次にグローバルでないものをロードします。

デフォルトでは、[[yii\codeception\DbTestCase]] は `InitDbFixture` を `globalFixtures()` メソッドの中で既に宣言しています。
このことは、どのテストの前にも何らかの初期化作業をしたい場合は、`@app/tests/fixtures/initdb.php` だけを触ればよいことを意味します。
その必要がなければ、単にそれぞれの個別テストケースとそれに対応するフィクスチャの開発に専念することが出来ます。


フィクスチャクラスとデータファイルを編成する
--------------------------------------------

デフォルトでは、フィクスチャクラスは対応するデータファイルを探すときに、フィクスチャのクラスファイルを含むフォルダのサブフォルダである `data` フォルダの中を見ます。
簡単なプロジェクトではこの規約に従うことができます。
大きなプロジェクトでは、おそらくは、同じフィクスチャクラスを異なるテストに使うために、データファイルを切り替える必要がある場合が頻繁に生じるでしょう。
従って、クラスの名前空間と同じように、データファイルを階層的な方法で編成することを推奨します。
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

このようにして、テスト間でフィクスチャのデータファイルが衝突するのを回避し、必要に応じてデータファイルを使い分けます。


> Note|注意: 上の例では、フィクスチャファイルには例示目的だけの名前が付けられています。
> 実際の現場では、フィクスチャクラスの拡張元である基底クラスに従って名前を付けるべきです。
> 例えば、DB フィクスチャを [[yii\test\ActiveFixture]] から拡張している場合は、DB テーブルの名前をフィクスチャのデータファイル名として使うべきです。
> MongoDB フィクスチャを [[yii\mongodb\ActiveFixture]] から拡張している場合は、コレクション名をファイル名として使うべきです。

同様な階層は、フィクスチャクラスファイルを編成するのにも使うことが出来ます。
`data` をルートディレクトリとして使うのでなく、データファイルとの衝突を避けるために `fixtures` をルートディレクトリとして使うのが良いでしょう。


まとめ
------

以上、フィクスチャを定義して使用する方法を説明しました。
下記に、DB に関連したユニットテストを走らせる場合の典型的なワークフローをまとめておきます。

1. `yii migrate` ツールを使って、テストのデータベースを最新版にアップグレードする
2. テストケースを走らせる
   - フィクスチャをロードする - 関係する DB テーブルをクリーンアップし、フィクスチャデータを投入する
   - 実際のテストを実行する
   - フィクスチャをアンロードする
3. 全てのテストが完了するまで、ステップ 2 を繰り返す


(以下は削除または大幅に改稿される可能性が高いので、当面、翻訳を見合わせます)

**To be cleaned up below**

Managing Fixtures
=================

> Note: This section is under development.
>
> todo: this tutorial may be merged with the above part of test-fixtures.md

Fixtures are important part of testing. Their main purpose is to populate you with data that needed by testing
different cases. With this data using your tests becoming more efficient and useful.

Yii supports fixtures via the `yii fixture` command line tool. This tool supports:

* Loading fixtures to different storage such as: RDBMS, NoSQL, etc;
* Unloading fixtures in different ways (usually it is clearing storage);
* Auto-generating fixtures and populating it with random data.

Fixtures format
---------------

Fixtures are objects with different methods and configurations, refer to official [documentation](https://github.com/yiisoft/yii2/blob/master/docs/guide/test-fixture.md) on them.
Lets assume we have fixtures data to load:

```
#users.php file under fixtures data path, by default @tests\unit\fixtures\data

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
If we are using fixture that loads data into database then these rows will be applied to `users` table. If we are using nosql fixtures, for example `mongodb`
fixture, then this data will be applied to `users` mongodb collection. In order to learn about implementing various loading strategies and more, refer to official [documentation](https://github.com/yiisoft/yii2/blob/master/docs/guide/test-fixture.md).
Above fixture example was auto-generated by `yii2-faker` extension, read more about it in these [section](#auto-generating-fixtures).
Fixture classes name should not be plural.

Loading fixtures
----------------

Fixture classes should be suffixed by `Fixture` class. By default fixtures will be searched under `tests\unit\fixtures` namespace, you can
change this behavior with config or command options. You can exclude some fixtures due load or unload by specifying `-` before its name like `-User`.

To load fixture, run the following command:

```
yii fixture/load <fixture_name>
```

The required `fixture_name` parameter specifies a fixture name which data will be loaded. You can load several fixtures at once.
Below are correct formats of this command:

```
// load `User` fixture
yii fixture/load User

// same as above, because default action of "fixture" command is "load"
yii fixture User

// load several fixtures
yii fixture User UserProfile

// load all fixtures
yii fixture/load "*"

// same as above
yii fixture "*"

// load all fixtures except ones
yii fixture "*" -DoNotLoadThisOne

// load fixtures, but search them in different namespace. By default namespace is: tests\unit\fixtures.
yii fixture User --namespace='alias\my\custom\namespace'

// load global fixture `some\name\space\CustomFixture` before other fixtures will be loaded.
// By default this option is set to `InitDbFixture` to disable/enable integrity checks. You can specify several
// global fixtures separated by comma.
yii fixture User --globalFixtures='some\name\space\Custom'
```

Unloading fixtures
------------------

To unload fixture, run the following command:

```
// unload Users fixture, by default it will clear fixture storage (for example "users" table, or "users" collection if this is mongodb fixture).
yii fixture/unload User

// Unload several fixtures
yii fixture/unload User,UserProfile

// unload all fixtures
yii fixture/unload "*"

// unload all fixtures except ones
yii fixture/unload "*" -DoNotUnloadThisOne

```

Same command options like: `namespace`, `globalFixtures` also can be applied to this command.

Configure Command Globally
--------------------------
While command line options allow us to configure the migration command
on-the-fly, sometimes we may want to configure the command once for all. For example you can configure
different migration path as follows:

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

Auto-generating fixtures
------------------------

Yii also can auto-generate fixtures for you based on some template. You can generate your fixtures with different data on different languages and formats.
These feature is done by [Faker](https://github.com/fzaninotto/Faker) library and `yii2-faker` extension.
See extension [guide](https://github.com/yiisoft/yii2-faker) for more docs.
