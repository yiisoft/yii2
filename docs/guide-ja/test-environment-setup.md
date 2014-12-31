テスト環境の構築
================

> Note|注意: この節はまだ執筆中です。

Yii2 は [`Codeception`](https://github.com/Codeception/Codeception) テストフレームワークとの統合を公式にサポートしており、次のタイプのテストを作成することを可能にしています。

- [ユニットテスト](test-unit.md) - 一つのコードユニットが期待通りに動くことを検証する。
- [機能テスト](test-functional.md) - ブラウザのエミュレーションによって、ユーザの視点からシナリオを検証する。
- [承認テスト](test-acceptance.md) - ブラウザの中で、ユーザの視点からシナリオを検証する。

これら三つのタイプのテスト全てについて、Yii は、[`yii2-basic`](https://github.com/yiisoft/yii2/tree/master/apps/basic) と [`yii2-advanced`](https://github.com/yiisoft/yii2/tree/master/apps/advanced) の両方のテンプレートで、そのまま使えるテストセットを提供しています。

テストを走らせるためには、[Codeception](https://github.com/Codeception/Codeception) をインストールする必要があります。
インストールするのに良い方法は次のとおりです。

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
