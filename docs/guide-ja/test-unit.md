単体テスト
==========

単体テストは、一かたまりのコードが期待通りに動作することを検証するものです。
すなわち、さまざまな入力パラメータを与えて、クラスのメソッドが期待通りの結果を返すかどうかを検証します。
単体テストは、通常は、テストされるクラスを書く人によって開発されます。

Yii における単体テストは、PHPUnit と Codeception (こちらはオプションです) の上に構築されます。従って、それらのドキュメントを通読することが推奨されます。

- [Codeception for Yii framework](https://codeception.com/for/yii)
- [Codeception Unit Tests](https://codeception.com/docs/05-UnitTests)
- [PHPUnit のドキュメントの第2章以降](https://phpunit.readthedocs.io/en/9.5/writing-tests-for-phpunit.html).

## ベーシック・テンプレート、アドバンスト・テンプレートのテストを実行する

アドバンスト・テンプレートでプロジェクトを開始した場合、テストの実行については、
["テスト" のガイド](https://github.com/yiisoft/yii2-app-advanced/blob/master/docs/guide-ja/start-testing.md) を参照して下さい。

ベーシック・テンプレートでプロジェクトを開始した場合は、[README の "testing" のセクション](https://github.com/yiisoft/yii2-app-basic/blob/master/README.md#testing) を参照して下さい。

## フレームワークの単体テスト

Yii フレームワーク自体に対する単体テストを走らせたい場合は、"[Yii 2 の開発を始めよう](https://github.com/yiisoft/yii2/blob/master/docs/internals-ja/getting-started.md)"
の説明に従ってください。
