機能テスト
==========

機能テストはユーザの視点からシナリオを検証するものです。
[受入テスト](test-acceptance.md) と似ていますが、HTTP によって通信する代りに、
POST や GET のパラメータなどの環境変数を設定しておいてから、アプリケーションのインスタンスをコードから直接に実行します。

機能テストは一般的に受入テストより高速であり、失敗した場合に詳細なスタックトレースを提供してくれます。
経験則から言うと、特別なウェブ・サーバ設定や JavaScript による複雑な UI を持たない場合は、
機能テストの方を選ぶべきです。

機能テストは Codeception フレームワークの助けを借りて実装されています。これについては、優れたドキュメントがあります。

- [Codeception for Yii framework](https://codeception.com/for/yii)
- [Codeception Functional Tests](https://codeception.com/docs/04-FunctionalTests)

## ベーシック・テンプレート、アドバンスト・テンプレートのテストを実行する

アドバンスト・テンプレートで開発をしている場合は、テスト実行の詳細について、
["テスト" のガイド](https://github.com/yiisoft/yii2-app-advanced/blob/master/docs/guide-ja/start-testing.md) を参照して下さい。

ベーシック・テンプレートで開発をしている場合は、[README の "testing" のセクション](https://github.com/yiisoft/yii2-app-basic/blob/master/README.md#testing) を参照して下さい。
