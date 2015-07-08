テスト環境の構築
================

> Note|注意: この節はまだ執筆中です。

Yii2 は [`Codeception`](https://github.com/Codeception/Codeception) テストフレームワークとの統合を公式にサポートしており、次のタイプのテストを作成することを可能にしています。

- [単体テスト](test-unit.md) - 一かたまりのコードが期待通りに動くことを検証する。
- [機能テスト](test-functional.md) - ブラウザのエミュレーションによって、ユーザの視点からシナリオを検証する。
- [受入テスト](test-acceptance.md) - ブラウザの中で、ユーザの視点からシナリオを検証する。

これら三つのタイプのテスト全てについて、Yii は、[`yii2-basic`](https://github.com/yiisoft/yii2-app-basic) と [`yii2-advanced`](https://github.com/yiisoft/yii2-app-advanced) の両方のプロジェクトテンプレートで、そのまま使えるテストセットを提供しています。

テストを走らせるためには、[Codeception](https://github.com/Codeception/Codeception) をインストールする必要があります。
Codeception は、特定のプロジェクトのためだけにローカルにインストールするか、開発マシンのためにグローバルにインストールするかを選ぶことが出来ます。

ローカルのインストールのためには、次のコマンドを使います。

```
composer require "codeception/codeception=2.0.*"
composer require "codeception/specify=*"
composer require "codeception/verify=*"
```

グローバルのインストールのためには、`global` 命令を使う必要があります。

```
composer global require "codeception/codeception=2.0.*"
composer global require "codeception/specify=*"
composer global require "codeception/verify=*"
```

以前にグローバルパッケージのために Composer を使ったことが一度もない場合は、`composer global status` を実行してください。
次のように出力される筈です。

```
Changed current directory to <directory>
```

そうしたら、`<directory>/vendor/bin` をあなたの `PATH` 環境変数に追加してください。
これでコマンドラインから `codecept` をグローバルに使うことが出来ます。

> Note|注意: グローバルにインストールすると、あなたの開発環境で扱っている全てのプロジェクトに対して Codeception を使うことが出来るようになります。
  パスを指定せずに `codecept` シェルコマンドをグローバルに走らせることが可能になります。
  しかしながら、例えば、二つのプロジェクトが異なるバージョンの Codeception のインストールを要求している場合など、この方法が不適切なこともあり得ます。
  話を単純にするために、このガイドで実行しているテストに関するシェルコマンドは、全て、Codeception がグローバルにインストールされていることを前提にしています。
