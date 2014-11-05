Yii 2.0 公式ガイド
==================

このチュートリアルは [Yii ドキュメンテーション規約](http://www.yiiframework.com/doc/terms/) の下に
リリースされています。

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
* [ルーティング](runtime-routing.md)
* [リクエスト](runtime-requests.md)
* [レスポンス](runtime-responses.md)
* [セッションとクッキー](runtime-sessions-cookies.md)
* [URL の解析と生成](runtime-url-handling.md)
* [エラー処理](runtime-handling-errors.md)
* [ログ](runtime-logging.md)


鍵となる概念
------------

* [コンポーネント](concept-components.md)
* [プロパティ](concept-properties.md)
* [イベント](concept-events.md)
* [ビヘイビア](concept-behaviors.md)
* [コンフィギュレーション](concept-configurations.md)
* [エイリアス](concept-aliases.md)
* [クラスのオートロード](concept-autoloading.md)
* [サービスロケータ](concept-service-locator.md)
* [依存性注入コンテナ](concept-di-container.md)


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

* [データの書式設定](output-formatter.md)
* **TBD** [ページネーション](output-pagination.md)
* **TBD** [並べ替え](output-sorting.md)
* [データプロバイダ](output-data-providers.md)
* [データウィジェット](output-data-widgets.md)
* [クライアントスクリプトを使う](output-client-scripts.md)
* [テーマを使う](output-theming.md)


セキュリティ
------------

* [認証](security-authentication.md)
* [権限](security-authorization.md)
* [パスワードを扱う](security-passwords.md)
* **TBD** [Auth クライアント](security-auth-clients.md)
* **TBD** [最善の慣行](security-best-practices.md)


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
* [リソース](rest-resources.md)
* [コントローラ](rest-controllers.md)
* [ルーティング](rest-routing.md)
* [レスポンスの書式設定](rest-response-formatting.md)
* [認証](rest-authentication.md)
* [速度制限](rest-rate-limiting.md)
* [バージョン管理](rest-versioning.md)
* [エラー処理](rest-error-handling.md)


開発ツール
----------

* [デバッグツールバーとデバッガ](tool-debugger.md)
* [Gii を使ってコードを生成する](tool-gii.md)
* **TBD** [API ドキュメンテーションを生成する](tool-api-doc.md)


テスト
------

* [概要](test-overview.md)
* [テスト環境の構築](test-environment-setup.md)
* [ユニットテスト](test-unit.md)
* [機能テスト](test-functional.md)
* [承認テスト](test-acceptance.md)
* [フィクスチャ](test-fixtures.md)


スペシャルトピック
------------------

* [アドバンストアプリケーションテンプレート](tutorial-advanced-app.md)
* [アプリケーションを一から構築する](tutorial-start-from-scratch.md)
* [コンソールコマンド](tutorial-console.md)
* [コアのバリデータ](tutorial-core-validators.md)
* [国際化](tutorial-i18n.md)
* [メール](tutorial-mailing.md)
* [パフォーマンスチューニング](tutorial-performance-tuning.md)
* **TBD** [共有ホスト環境](tutorial-shared-hosting.md)
* [テンプレートエンジン](tutorial-template-engines.md)
* [サードパーティのコードを扱う](tutorial-yii-integration.md)


ウィジェット
------------

* GridView: link to demo page
* ListView: link to demo page
* DetailView: link to demo page
* ActiveForm: link to demo page
* Pjax: link to demo page
* Menu: link to demo page
* LinkPager: link to demo page
* LinkSorter: link to demo page
* [Bootstrap ウィジェット](widget-bootstrap.md)
* [Jquery UI ウィジェット](widget-jui.md)


ヘルパー
--------

* [概要](helper-overview.md)
* **TBD** [ArrayHelper](helper-array.md)
* **TBD** [Html](helper-html.md)
* **TBD** [Url](helper-url.md)
* **TBD** [Security](helper-security.md)

