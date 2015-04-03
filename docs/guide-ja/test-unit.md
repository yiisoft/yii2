単体テスト
==========

> Note|注意: この節はまだ執筆中です。

単体テストは、一かたまりのコードが期待通りに動作することを検証するものです。
オブジェクト指向プログラミングでは、最も基本的なコードのかたまりはクラスです。
単体テストで主として必要となることは、従って、クラスの全てのインタフェイスメソッドが正しく動作することを検証することです。
つまり、テストは、さまざまな入力パラメータに対してメソッドが期待通りの結果を返すかどうかを検証します。
単体テストは、通常は、テストされるクラスを書く人によって開発されます。

Yii における単体テストは、PHPUnit と Codeception (こちらはオプションです) の上に構築されます。
従って、それらのドキュメントを通読することが推奨されます。

- [PHPUnit のドキュメントの第2章以降](http://phpunit.de/manual/current/en/writing-tests-for-phpunit.html).
- [Codeception Unit Tests](http://codeception.com/docs/06-UnitTests).

アプリケーションテンプレートの単体テストを走らせる
--------------------------------------------------

`apps/advanced/tests/README.md` および `apps/basic/tests/README.md` で提供されている説明を参照してください。

フレームワークの単体テスト
--------------------------

Yii フレームワーク自体に対する単体テストを走らせたい場合は、"[Yii2 の開発を始めよう](https://github.com/yiisoft/yii2/blob/master/docs/internals-ja/getting-started.md)" の説明に従ってください。
