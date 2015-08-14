Yii 2.0 決定版ガイド
====================

このチュートリアルは [Yii ドキュメント許諾条件](http://www.yiiframework.com/doc/terms/) の下にリリースされています。

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
* [こんにちは、と言う](start-hello.md)
* [フォームを扱う](start-forms.md)
* [データベースを扱う](start-databases.md)
* [Gii でコードを生成する](start-gii.md)
* [先を見通す](start-looking-ahead.md)


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
* [Sphinx](https://github.com/yiisoft/yii2-sphinx/blob/master/docs/guide-ja/README.md)
* [Redis](https://github.com/yiisoft/yii2-redis/blob/master/docs/guide-ja/README.md)
* [MongoDB](https://github.com/yiisoft/yii2-mongodb/blob/master/docs/guide-ja/README.md)
* [ElasticSearch](https://github.com/yiisoft/yii2-elasticsearch/blob/master/docs/guide-ja/README.md)


ユーザからのデータ取得
----------------------

* [フォームを作成する](input-forms.md)
* [入力を検証する](input-validation.md)
* [ファイルをアップロードする](input-file-upload.md)
* [表形式インプットのデータ収集](input-tabular-input.md)
* [複数のモデルのデータを取得する](input-multiple-models.md)


データの表示
------------

* [データのフォーマット](output-formatting.md)
* [ページネーション](output-pagination.md)
* [並べ替え](output-sorting.md)
* [データプロバイダ](output-data-providers.md)
* [データウィジェット](output-data-widgets.md)
* [クライアントスクリプトを扱う](output-client-scripts.md)
* [テーマ](output-theming.md)


セキュリティ
------------

* [認証](security-authentication.md)
* [権限付与](security-authorization.md)
* [パスワードを扱う](security-passwords.md)
* [認証クライアント](https://github.com/yiisoft/yii2-authclient/blob/master/docs/guide-ja/README.md)
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

* [デバッグツールバーとデバッガ](https://github.com/yiisoft/yii2-debug/blob/master/docs/guide-ja/README.md)
* [Gii を使ってコードを生成する](https://github.com/yiisoft/yii2-gii/blob/master/docs/guide/README.md)


テスト
------

* [概要](test-overview.md)
* [テスト環境の構築](test-environment-setup.md)
* [単体テスト](test-unit.md)
* [機能テスト](test-functional.md)
* [受入テスト](test-acceptance.md)
* [フィクスチャ](test-fixtures.md)


スペシャルトピック
------------------

* [アドバンストプロジェクトテンプレート](https://github.com/yiisoft/yii2-app-advanced/blob/master/docs/guide-ja/README.md)
* [アプリケーションを一から構築する](tutorial-start-from-scratch.md)
* [コンソールコマンド](tutorial-console.md)
* [コアバリデータ](tutorial-core-validators.md)
* [国際化](tutorial-i18n.md)
* [メール送信](tutorial-mailing.md)
* [パフォーマンスチューニング](tutorial-performance-tuning.md)
* [共有ホスティング環境](tutorial-shared-hosting.md)
* [テンプレートエンジン](tutorial-template-engines.md)
* [サードパーティのコードを扱う](tutorial-yii-integration.md)


ウィジェット
------------

* GridView: **未定** デモページへリンク
* ListView: **未定** デモページへリンク
* DetailView: **未定** デモページへリンク
* ActiveForm: **未定** デモページへリンク
* Pjax: **未定** デモページへリンク
* Menu: **未定** デモページへリンク
* LinkPager: **未定** デモページへリンク
* LinkSorter: **未定** デモページへリンク
* [Bootstrap ウィジェット](https://github.com/yiisoft/yii2-bootstrap/blob/master/docs/guide-ja/README.md)
* [jQuery UI ウィジェット](https://github.com/yiisoft/yii2-jui/blob/master/docs/guide-ja/README.md)


ヘルパ
------

* [概要](helper-overview.md)
* [配列ヘルパ](helper-array.md)
* [Html ヘルパ](helper-html.md)
* [Url ヘルパ](helper-url.md)
