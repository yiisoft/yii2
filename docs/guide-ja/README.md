Yii 2.0 決定版ガイド
====================

このチュートリアルは [Yii ドキュメント許諾条件](https://www.yiiframework.com/doc/terms/) の下にリリースされています。

All Rights Reserved.

2014 (c) Yii Software LLC.


導入
----

* [Yii について](intro-yii.md)
* [バージョン 1.1 からのアップグレード](intro-upgrade-from-v1.md)


始めよう
--------

* [何を知っている必要があるか](start-prerequisites.md)
* [Yii をインストールする](start-installation.md)
* [アプリケーションを走らせる](start-workflow.md)
* [こんにちは、と言う](start-hello.md)
* [フォームを扱う](start-forms.md)
* [データベースを扱う](start-databases.md)
* [Gii でコードを生成する](start-gii.md)
* [先を見通す](start-looking-ahead.md)


アプリケーションの構造
----------------------

* [アプリケーションの構造の概要](structure-overview.md)
* [エントリ・スクリプト](structure-entry-scripts.md)
* [アプリケーション](structure-applications.md)
* [アプリケーション・コンポーネント](structure-application-components.md)
* [コントローラ](structure-controllers.md)
* [モデル](structure-models.md)
* [ビュー](structure-views.md)
* [モジュール](structure-modules.md)
* [フィルタ](structure-filters.md)
* [ウィジェット](structure-widgets.md)
* [アセット](structure-assets.md)
* [エクステンション](structure-extensions.md)


リクエストの処理
----------------

* [リクエストの処理の概要](runtime-overview.md)
* [ブートストラップ](runtime-bootstrapping.md)
* [ルーティングと URL 生成](runtime-routing.md)
* [リクエスト](runtime-requests.md)
* [レスポンス](runtime-responses.md)
* [セッションとクッキー](runtime-sessions-cookies.md)
* [エラー処理](runtime-handling-errors.md)
* [ロギング](runtime-logging.md)


鍵となる概念
------------

* [コンポーネント](concept-components.md)
* [プロパティ](concept-properties.md)
* [イベント](concept-events.md)
* [ビヘイビア](concept-behaviors.md)
* [構成情報](concept-configurations.md)
* [エイリアス](concept-aliases.md)
* [クラスのオートロード](concept-autoloading.md)
* [サービス・ロケータ](concept-service-locator.md)
* [依存注入コンテナ](concept-di-container.md)


データベースの取り扱い
----------------------

* [データベース・アクセス・オブジェクト](db-dao.md): データベースへの接続、基本的なクエリ、トランザクション、および、スキーマ操作
* [クエリ・ビルダ](db-query-builder.md): シンプルな抽象レイヤを使ってデータベースに対してクエリを行う
* [アクティブ・レコード](db-active-record.md): アクティブ・レコード ORM、レコードの読み出しと操作、リレーションの定義
* [マイグレーション](db-migrations.md): チーム開発環境においてデータベースにバージョン・コントロールを適用
* [Sphinx](https://www.yiiframework.com/extension/yiisoft/yii2-sphinx/doc/guide)
* [Redis](https://www.yiiframework.com/extension/yiisoft/yii2-redis/doc/guide)
* [MongoDB](https://www.yiiframework.com/extension/yiisoft/yii2-mongodb/doc/guide)
* [ElasticSearch](https://www.yiiframework.com/extension/yiisoft/yii2-elasticsearch/doc/guide)


ユーザからのデータ取得
----------------------

* [フォームを作成する](input-forms.md)
* [入力を検証する](input-validation.md)
* [ファイルをアップロードする](input-file-upload.md)
* [表形式インプットのデータ収集](input-tabular-input.md)
* [複数のモデルのデータを取得する](input-multiple-models.md)
* [クライアント・サイドで ActiveForm を拡張する](input-form-javascript.md)


データの表示
------------

* [データのフォーマット](output-formatting.md)
* [ページネーション](output-pagination.md)
* [並べ替え](output-sorting.md)
* [データ・プロバイダ](output-data-providers.md)
* [データ・ウィジェット](output-data-widgets.md)
* [クライアント・スクリプトを扱う](output-client-scripts.md)
* [テーマ](output-theming.md)


セキュリティ
------------

* [セキュリティの概要](security-overview.md)
* [認証](security-authentication.md)
* [権限付与](security-authorization.md)
* [パスワードを扱う](security-passwords.md)
* [暗号化](security-cryptography.md)
* [認証クライアント](https://www.yiiframework.com/extension/yiisoft/yii2-authclient/doc/guide)
* [ベスト・プラクティス](security-best-practices.md)


キャッシュ
----------

* [キャッシュの概要](caching-overview.md)
* [データ・キャッシュ](caching-data.md)
* [フラグメント・キャッシュ](caching-fragment.md)
* [ページ・キャッシュ](caching-page.md)
* [HTTP キャッシュ](caching-http.md)


RESTful ウェブ・サービス
------------------------

* [クイック・スタート](rest-quick-start.md)
* [リソース](rest-resources.md)
* [コントローラ](rest-controllers.md)
* [ルーティング](rest-routing.md)
* [レスポンス形式の設定](rest-response-formatting.md)
* [認証](rest-authentication.md)
* [レート制限](rest-rate-limiting.md)
* [バージョン管理](rest-versioning.md)
* [エラー処理](rest-error-handling.md)


開発ツール
----------

* [デバッグ・ツールバーとデバッガ](https://www.yiiframework.com/extension/yiisoft/yii2-debug/doc/guide)
* [Gii を使ってコードを生成する](https://www.yiiframework.com/extension/yiisoft/yii2-gii/doc/guide)
* [API ドキュメントを生成する](https://www.yiiframework.com/extension/yiisoft/yii2-apidoc)


テスト
------

* [テストの概要](test-overview.md)
* [テスト環境の構築](test-environment-setup.md)
* [単体テスト](test-unit.md)
* [機能テスト](test-functional.md)
* [受入テスト](test-acceptance.md)
* [フィクスチャ](test-fixtures.md)


スペシャル・トピック
--------------------

* [アドバンスト・プロジェクト・テンプレート](https://www.yiiframework.com/extension/yiisoft/yii2-app-advanced/doc/guide)
* [アプリケーションを一から構築する](tutorial-start-from-scratch.md)
* [コンソール・コマンド](tutorial-console.md)
* [コア・バリデータ](tutorial-core-validators.md)
* [国際化](tutorial-i18n.md)
* [メール送信](tutorial-mailing.md)
* [パフォーマンス・チューニング](tutorial-performance-tuning.md)
* [共有ホスティング環境](tutorial-shared-hosting.md)
* [テンプレート・エンジン](tutorial-template-engines.md)
* [サードパーティのコードを扱う](tutorial-yii-integration.md)
* [Yii をマイクロ・フレームワークとして使う](tutorial-yii-as-micro-framework.md)


ウィジェット
------------

* [GridView](https://www.yiiframework.com/doc-2.0/yii-grid-gridview.html)
* [ListView](https://www.yiiframework.com/doc-2.0/yii-widgets-listview.html)
* [DetailView](https://www.yiiframework.com/doc-2.0/yii-widgets-detailview.html)
* [ActiveForm](https://www.yiiframework.com/doc/guide/2.0/ja/input-forms#activerecord-based-forms-activeform)
* [Pjax](https://www.yiiframework.com/doc-2.0/yii-widgets-pjax.html)
* [Menu](https://www.yiiframework.com/doc-2.0/yii-widgets-menu.html)
* [LinkPager](https://www.yiiframework.com/doc-2.0/yii-widgets-linkpager.html)
* [LinkSorter](https://www.yiiframework.com/doc-2.0/yii-widgets-linksorter.html)
* [Bootstrap ウィジェット](https://www.yiiframework.com/extension/yiisoft/yii2-bootstrap/doc/guide)
* [jQuery UI ウィジェット](https://www.yiiframework.com/extension/yiisoft/yii2-jui/doc/guide)


ヘルパ
------

* [ヘルパの概要](helper-overview.md)
* [配列ヘルパ](helper-array.md)
* [Html ヘルパ](helper-html.md)
* [Url ヘルパ](helper-url.md)

