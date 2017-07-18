ブートストラップ
================

ブートストラップとは、アプリケーションが入ってくるリクエストの解決と処理を開始する前の、環境を準備する過程を指すものです。
ブートストラップは二つの場所、すなわち、[エントリスクリプト](structure-entry-scripts.md) と [アプリケーション](structure-applications.md)で行われます。

[エントリスクリプト](structure-entry-scripts.md) では、さまざまなライブラリのためのクラスオートローダが登録されます。
この中には、Composer の `autoload.php` によるオートローダと、Yii の `Yii` クラスファイルによるオートローダが含まれます。
エントリスクリプトは、次に、アプリケーションの [構成情報](concept-configurations.md) をロードして、[アプリケーション](structure-applications.md) のインスタンスを作成します。

アプリケーションのコンストラクタでは、次のようなブートストラップの仕事が行われます。

1. [[yii\base\Application::preInit()|preInit()]] が呼ばれます。
   このメソッドは、いくつかの優先度の高いアプリケーションプロパティ、例えば [[yii\base\Application::basePath|basePath]] などを構成します。
2. [[yii\base\Application::errorHandler|エラーハンドラ]] を登録します。
3. 与えられたアプリケーションの構成情報を使って、アプリケーションのプロパティを初期化します。
4. [[yii\base\Application::init()|init()]] が呼ばれます。
   そして `init()` が [[yii\base\Application::bootstrap()|bootstrap()]] を呼んで、ブートストラップコンポーネントを走らせます。
   - エクステンションマニフェストファイル `vendor/yiisoft/extensions.php` をインクルードします。
   - エクステンションによって宣言された [ブートストラップコンポーネント](structure-extensions.md#bootstrapping-classes) を作成して実行します。
   - アプリケーションの [bootstrap プロパティ](structure-applications.md#bootstrap) に宣言されている [アプリケーションコンポーネント](structure-application-components.md) および/または [モジュール](structure-modules.md) を作成して実行します。

ブートストラップの仕事は *全て* のリクエストを処理する前に、毎回しなければなりませんので、この過程を軽いものに保って可能な限り最適化することは非常に重要なことです。

あまりに多くのブートストラップコンポーネントを登録しないように努めてください。
ブートストラップコンポーネントが必要になるのは、リクエスト処理のライフサイクル全体に関与する必要がある場合だけです。
例えば、モジュールが追加の URL 解析規則を登録する必要がある場合は、モジュールを [bootstrap プロパティ](structure-applications.md#bootstrap) のリストに挙げなければなりません。
なぜなら、URL 規則を使ってリクエストが解決される前に、新しい URL 規則を有効にしなければならないからです。

本番運用モードにおいては、[PHP OPCache] や [APC]  など、バイトコードキャッシュを有効にして、PHP ファイルをインクルードして解析するのに要する時間を最小化してください。

[PHP OPcache]: http://php.net/manual/ja/book.opcache.php
[APC]: http://php.net/manual/ja/book.apc.php

大規模なアプリケーションには、多数の小さな構成情報ファイルに分割された、非常に複雑なアプリケーション [構成情報](concept-configurations.md) を持つものがあります。
そのような場合には、構成情報配列全体をキャッシュしておき、エントリスクリプトでアプリケーションのインスタンスを作成する前に構成情報をロードするときには、配列全体を直接にキャッシュからロードするという方法を考慮してください。
