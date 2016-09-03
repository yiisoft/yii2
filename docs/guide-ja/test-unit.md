単体テスト
==========

> Note: この節はまだ執筆中です。

単体テストは、一かたまりのコードが期待通りに動作することを検証するものです。
オブジェクト指向プログラミングでは、最も基本的なコードのかたまりはクラスです。
単体テストで主として必要となることは、従って、クラスの全てのインタフェイスメソッドが正しく動作することを検証することです。
つまり、テストは、さまざまな入力パラメータに対してメソッドが期待通りの結果を返すかどうかを検証します。
単体テストは、通常は、テストされるクラスを書く人によって開発されます。

Yii における単体テストは、PHPUnit と Codeception (こちらはオプションです) の上に構築されます。
従って、それらのドキュメントを通読することが推奨されます。

- [PHPUnit のドキュメントの第2章以降](http://phpunit.de/manual/current/en/writing-tests-for-phpunit.html).
- [Codeception Unit Tests](http://codeception.com/docs/05-UnitTests).

ベーシックおよびアドバンストのテンプレートのテストを実行する
------------------------------------------------------------

アドバンストテンプレートでプロジェクトを開始した場合、テストの実行については、
["テスト" のガイド](https://github.com/yiisoft/yii2-app-advanced/blob/master/docs/guide-ja/start-testing.md) を参照して下さい。

ベーシックテンプレートでプロジェクトを開始した場合は、
check its [README の "testing" の節](https://github.com/yiisoft/yii2-app-basic/blob/master/README.md#testing) を参照して下さい。


フレームワークの単体テスト
--------------------------

Yii フレームワーク自体に対する単体テストを走らせたい場合は、"[Yii 2 の開発を始めよう](https://github.com/yiisoft/yii2/blob/master/docs/internals-ja/getting-started.md)" の説明に従ってください。
