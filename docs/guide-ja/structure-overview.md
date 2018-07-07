概要
====

Yii のアプリケーションは [モデル・ビュー・コントローラ (MVC)](http://ja.wikipedia.org/wiki/Model_View_Controller) アーキテクチャ・パターンに従って編成されています。
[モデル](structure-models.md) は、データ、ビジネス・ロジック、規則を表現します。
[ビュー](structure-views.md) は、モデルの出力表現です。
そして [コントローラ](structure-controllers.md) は入力を受け取って、それを [モデル](structure-models.md) と [ビュー](structure-views.md) のためのコマンドに変換します。

MVC 以外にも、Yii のアプリケーションは下記の要素を持っています。

* [エントリ・スクリプト](structure-entry-scripts.md): エンド・ユーザから直接アクセスできる PHP スクリプトです。
  これはリクエスト処理サイクルを開始する役目を持っています。
* [アプリケーション](structure-applications.md): グローバルにアクセス可能なオブジェクトであり、
  アプリケーション・コンポーネントを管理し、連携させて、リクエストに応えます。
* [アプリケーション・コンポーネント](structure-application-components.md): アプリケーションと共に登録されたオブジェクトであり、
  リクエストに応えるための様々なサービスを提供します。
* [モジュール](structure-modules.md): それ自身に完全な MVC を含む自己完結的なパッケージです。
  アプリケーションは複数のモジュールとして編成することが出来ます。
* [フィルタ](structure-filters.md): 各リクエストが実際に処理される前と後に、
  コントローラから呼び出される必要があるコードを表現します。
* [ウィジェット](structure-widgets.md): [ビュー](structure-views.md) に埋め込むことが出来るオブジェクトです。
  コントローラのロジックを含むことが可能で、異なるビューで再利用することが出来ます。

下の図がアプリケーションの静的な構造を示すものです。

![アプリケーションの静的な構造](images/application-structure.png)
