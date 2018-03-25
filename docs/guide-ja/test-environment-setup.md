テスト環境の構築
================

Yii 2 は [`Codeception`](https://github.com/Codeception/Codeception) テストフレームワークとの統合を公式にサポートしており、次のタイプのテストを作成することを可能にしています。

- [単体テスト](test-unit.md) - 一かたまりのコードが期待通りに動くことを検証する。
- [機能テスト](test-functional.md) - ブラウザのエミュレーションによって、ユーザの視点からシナリオを検証する。
- [受入テスト](test-acceptance.md) - ブラウザの中で、ユーザの視点からシナリオを検証する。

これら三つのタイプのテスト全てについて、Yii は、[`yii2-basic`](https://github.com/yiisoft/yii2-app-basic) と [`yii2-advanced`](https://github.com/yiisoft/yii2-app-advanced) の両方のプロジェクトテンプレートで、そのまま使えるテストセットを提供しています。

ベーシックテンプレート、アドバンストテンプレートの両方とも、Codeception がプリインストールされて付いて来ます。
これらのテンプレートの一つを使っていない場合は、下記のコンソールコマンドを発行することで Codeception をインストールすることが出来ます。

```
composer require codeception/codeception
composer require codeception/specify
composer require codeception/verify
```
