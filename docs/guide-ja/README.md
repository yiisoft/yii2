Yii 2.0 決定版ガイド
====================

このチュートリアルは [Yii ドキュメント規約](http://www.yiiframework.com/doc/terms/) の下にリリースされています。

All Rights Reserved.

2014 (c) Yii Software LLC.


前書き
------

* [Yii について](intro-yii.md)
* [バージョン 1.1 からのアップグレード](intro-upgrade-from-v1.md)


始めよう
--------

* [Yii をインストールする](start-installation.md)
* [アプリケーションを走らせる](start-workflow.md)
* [「こんにちは」と言う](start-hello.md)
* [フォームを扱う](start-forms.md)
* [データベースを扱う](start-databases.md)
* [Gii でコードを生成する](start-gii.md)
* [この先を見通す](start-looking-ahead.md)


アプリケーションの構造
----------------------

* [概要](structure-overview.md)
* [エントリスクリプト](structure-entry-scripts.md)
* [アプリケーション](structure-applications.md)
* [アプリケーションコンポーネント](structure-application-components.md)
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

* [概要](runtime-overview.md)
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
* [サービスロケータ](concept-service-locator.md)
* [依存注入コンテナ](concept-di-container.md)


データベースの取り扱い
----------------------

* [データアクセスオブジェクト](db-dao.md): データベースへの接続、基本的なクエリ、トランザクション、および、スキーマ操作
* [クエリビルダ](db-query-builder.md): シンプルな抽象レイヤを使ってデータベースに対してクエリを行う
* [アクティブレコード](db-active-record.md): アクティブレコード ORM、レコードの読み出しと操作、リレーションの定義
* [マイグレーション](db-migrations.md): チーム開発環境においてデータベースにバージョンコントロールを適用
* **TBD** [Sphinx](db-sphinx.md)
* **TBD** [Redis](db-redis.md)
* **TBD** [MongoDB](db-mongodb.md)
* **TBD** [ElasticSearch](db-elasticsearch.md)


ユーザからのデータ取得
----------------------

* [フォームを作成する](input-forms.md)
* [入力を検証する](input-validation.md)
* [ファイルをアップロードする](input-file-upload.md)
* **TBD** [複数モデルのためのデータ取得](input-multiple-models.md)


データの表示
------------

* [データのフォーマット](output-formatter.md)
* **TBD** [ページネーション](output-pagination.md)
* **TBD** [並べ替え](output-sorting.md)
* [データプロバイダ](output-data-providers.md)
* [データウィジェット](output-data-widgets.md)
* [クライアントスクリプトを扱う](output-client-scripts.md)
* [テーマ](output-theming.md)


セキュリティ
------------

* [認証](security-authentication.md)
* [権限付与](security-authorization.md)
* [パスワードを扱う](security-passwords.md)
* **TBD** [Auth クライアント](security-auth-clients.md)
* [ベストプラクティス](security-best-practices.md)


キャッシュ
----------

* [概要](caching-overview.md)
* [データキャッシュ](caching-data.md)
* [フラグメントキャッシュ](caching-fragment.md)
* [ページキャッシュ](caching-page.md)
* [HTTP キャッシュ](caching-http.md)


RESTful ウェブサービス
----------------------

* [クイックスタート](rest-quick-start.md)
* **翻訳中** [リソース](rest-resources.md)
* **翻訳中** [コントローラ](rest-controllers.md)
* **翻訳中** [ルーティング](rest-routing.md)
* **翻訳中** [レスポンスの書式設定](rest-response-formatting.md)
* **翻訳中** [認証](rest-authentication.md)
* **翻訳中** [転送レート制限](rest-rate-limiting.md)
* **翻訳中** [バージョン管理](rest-versioning.md)
* **翻訳中** [エラー処理](rest-error-handling.md)


開発ツール
----------

* **翻訳未着手** [デバッグツールバーとデバッガ](tool-debugger.md)
* **翻訳未着手** [Gii を使ってコードを生成する](tool-gii.md)
* **TBD** [API ドキュメントを生成する](tool-api-doc.md)


テスト
------

* **翻訳未着手** [概要](test-overview.md)
* **翻訳未着手** [テスト環境の構築](test-environment-setup.md)
* **翻訳未着手** [ユニットテスト](test-unit.md)
* **翻訳未着手** [機能テスト](test-functional.md)
* **翻訳未着手** [承認テスト](test-acceptance.md)
* **翻訳未着手** [フィクスチャ](test-fixtures.md)


スペシャルトピック
------------------

* **翻訳未着手** [アドバンストアプリケーションテンプレート](tutorial-advanced-app.md)
* **翻訳未着手** [アプリケーションを一から構築する](tutorial-start-from-scratch.md)
* **翻訳未着手** [コンソールコマンド](tutorial-console.md)
* **翻訳未着手** [コアのバリデータ](tutorial-core-validators.md)
* **翻訳未着手** [国際化](tutorial-i18n.md)
* **翻訳未着手** [メール](tutorial-mailing.md)
* **翻訳未着手** [パフォーマンスチューニング](tutorial-performance-tuning.md)
* **翻訳未着手** [共有ホスト環境](tutorial-shared-hosting.md)
* **翻訳未着手** [テンプレートエンジン](tutorial-template-engines.md)
* **翻訳未着手** [サードパーティのコードを扱う](tutorial-yii-integration.md)


ウィジェット
------------

* GridView: **TBD** link to demo page
* ListView: **TBD** link to demo page
* DetailView: **TBD** link to demo page
* ActiveForm: **TBD** link to demo page
* Pjax: **TBD** link to demo page
* Menu: **TBD** link to demo page
* LinkPager: **TBD** link to demo page
* LinkSorter: **TBD** link to demo page
* **翻訳未着手** [Bootstrap ウィジェット](widget-bootstrap.md)
* **翻訳未着手** [Jquery UI ウィジェット](widget-jui.md)


ヘルパ
------

* **翻訳未着手** [概要](helper-overview.md)
* **翻訳未着手** [ArrayHelper](helper-array.md)
* **翻訳未着手** [Html](helper-html.md)
* **翻訳未着手** [Url](helper-url.md)
* **TBD** [Security](helper-security.md)

