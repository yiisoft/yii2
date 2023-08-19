ロギング
========

Yii は高度なカスタマイズ性と拡張性を持った強力なロギング・フレームワークを提供しています。
このフレームワークを使用すると、さまざまな種類のメッセージを記録し、それをフィルタして、ファイル、データベース、メールなど、
さまざまなターゲットに収集することが簡単に出来ます。

Yii のロギング・フレームワークを使うためには、下記のステップを踏みます。
 
* コードのさまざまな場所で [ログ・メッセージ](#log-messages) を記録する。
* ログ・メッセージをフィルタしてエクスポートするために、アプリケーションの構成情報で [ログ・ターゲット](#log-targets) を構成する。
* さまざまなターゲット (例えば [Yii デバッガ](tool-debugger.md)) によって、フィルタされエクスポートされたログ・メッセージを調査する。

このセクションでは、主として最初の二つのステップについて説明します。


## メッセージを記録する <span id="log-messages"></span>

ログ・メッセージを記録することは、次のログ記録メソッドのどれかを呼び出すだけの簡単なことです。

* [[Yii::debug()]]: コードの断片がどのように走ったかをトレースするメッセージを記録します。主として開発のために使用します。
* [[Yii::info()]]: 何らかの有用な情報を伝えるメッセージを記録します。
* [[Yii::warning()]]: 何か予期しないことが発生したことを示す警告メッセージを記録します。
* [[Yii::error()]]: 出来るだけ早急に調査すべき致命的なエラーを記録します。

これらのログ記録メソッドは、ログ・メッセージをさまざまな *重大性レベル* と *カテゴリ* で記録するものです。
これらのメソッドは `function ($message, $category = 'application')` という関数シグニチャを共有しており、
`$message` は記録されるログ・メッセージを示し、`$category` はログ・メッセージのカテゴリを示します。
次のコード・サンプルは、トレース・メッセージをデフォルトのカテゴリである `application` の下に記録するものです。

```php
Yii::debug('平均収益の計算を開始');
```

> Info: ログ・メッセージは文字列でも、配列やオブジェクトのような複雑なデータでも構いません。
ログ・メッセージを適切に取り扱うのは [ログ・ターゲット](#log-targets) の責任です。
デフォルトでは、ログ・メッセージが文字列でない場合は、[[yii\helpers\VarDumper::export()]] が呼ばれて文字列に変換されることになります。

ログ・メッセージを上手に編成しフィルタするために、すべてのログ・メッセージにそれぞれ適切なカテゴリを指定することが推奨されます。
カテゴリに階層的な命名方法を採用すると、[ログ・ターゲット](#log-targets) がカテゴリに基づいてメッセージをフィルタすることが容易になります。
簡単でしかも効果的な命名方法は、カテゴリ名に PHP のマジック定数 `__METHOD__` を使用することです。
これは、Yii フレームワークのコアコードでも使われている方法です。
例えば、

```php
Yii::debug('平均収益の計算を開始', __METHOD__);
```

`__METHOD__` という定数は、それが出現する場所のメソッド名 (完全修飾のクラス名が前置されます) として評価されます。
例えば、上記のコードが `app\controllers\RevenueController::calculate` というメソッドの中で呼ばれている場合は、
`__METHOD__` は `'app\controllers\RevenueController::calculate'` という文字列と同じになります。

> Info: 上記で説明したメソッドは、実際には、[[yii\log\Logger|ロガー・オブジェクト]] の [[yii\log\Logger::log()|log()]] メソッドへのショートカットです。
[[yii\log\Logger|ロガー・オブジェクト]] は `Yii::getLogger()` という式でアクセス可能なシングルトンです。
ロガー・オブジェクトは、十分な量のメッセージが記録されたとき、または、アプリケーションが終了するときに、
[[yii\log\Dispatcher|メッセージ・ディスパッチャ]] を呼んで、登録された [ログ・ターゲット](#log-targets) に記録されたログ・メッセージを送信します。


## ログ・ターゲット <span id="log-targets"></span>

ログ・ターゲットは [[yii\log\Target]] クラスまたはその子クラスのインスタンスです。
ログ・ターゲットは、ログ・メッセージを重大性レベルとカテゴリによってフィルタして、何らかの媒体にエクスポートします。
例えば、[[yii\log\DbTarget|データベース・ターゲット]] は、フィルタされたログ・メッセージをデータベース・テーブルにエクスポートし、
[[yii\log\EmailTarget|メール・ターゲット]] は、ログ・メッセージを指定されたメール・アドレスにエクスポートします。

一つのアプリケーションの中で複数のログ・ターゲットを登録することが出来ます。
そのためには、次のように、アプリケーションの構成情報の中で、`log` [アプリケーション・コンポーネント](structure-application-components.md) によってログ・ターゲットを構成します。

```php
return [
    // "log" コンポーネントはブートストラップ時にロードされなければならない
    'bootstrap' => ['log'],
    // "log" コンポーネントはタイムスタンプを持つメッセージを処理するので、正しいタイムスタンプを出力するように PHP タイムゾーンを設定
    'timeZone' => 'America/Los_Angeles',
    'components' => [
        'log' => [
            'targets' => [
                [
                    'class' => 'yii\log\DbTarget',
                    'levels' => ['error', 'warning'],
                ],
                [
                    'class' => 'yii\log\EmailTarget',
                    'levels' => ['error'],
                    'categories' => ['yii\db\*'],
                    'message' => [
                       'from' => ['log@example.com'],
                       'to' => ['admin@example.com', 'developer@example.com'],
                       'subject' => 'example.com で、データベースエラー発生',
                    ],
                ],
            ],
        ],
    ],
];
```

> Note: `log` コンポーネントは、ログ・メッセージをターゲットに即座に送付することが出来るように、[ブートストラップ](runtime-bootstrapping.md) 時にロードされなければなりません。
上記の例で `bootstrap` の配列に `log` がリストアップされているのは、そのためです。

上記のコードでは、二つのログ・ターゲットが [[yii\log\Dispatcher::targets]] プロパティに登録されています。

* 最初のターゲットは、エラーと警告のメッセージを選択して、データベース・テーブルに保存します。
* 第二のターゲットは、名前が `yii\db\` で始まるカテゴリのエラー・メッセージを選んで、`admin@example.com` と `developer@example.com`
  の両方にメールで送信します。

Yii は下記のログ・ターゲットをあらかじめ内蔵しています。
その構成方法と使用方法を学ぶためには、これらのクラスの API ドキュメントを参照してください。

* [[yii\log\DbTarget]]: ログ・メッセージをデータベース・テーブルに保存する。
* [[yii\log\EmailTarget]]: ログ・メッセージを事前に指定されたメール・アドレスに送信する。
* [[yii\log\FileTarget]]: ログ・メッセージをファイルに保存する。
* [[yii\log\SyslogTarget]]: ログ・メッセージを PHP 関数 `syslog()` を呼んでシステム・ログに保存する。

以下では、全てのターゲットに共通する機能について説明します。

  
### メッセージのフィルタリング <span id="message-filtering"></span>

全てのログ・ターゲットについて、それぞれ、[[yii\log\Target::levels|levels]] と [[yii\log\Target::categories|categories]] のプロパティを構成して、
ターゲットが処理すべきメッセージの重要性レベルとカテゴリを指定することが出来ます。

[[yii\log\Target::levels|levels]] プロパティは、次のレベルの一つまたは複数からなる配列を値として取ります。

* `error`: [[Yii::error()]] によって記録されたメッセージに対応。
* `warning`: [[Yii::warning()]] によって記録されたメッセージに対応。
* `info`: [[Yii::info()]] によって記録されたメッセージに対応。
* `trace`: [[Yii::debug()]] によって記録されたメッセージに対応。
* `profile`: [[Yii::beginProfile()]] と [[Yii::endProfile()]] によって記録されたメッセージに対応。
  これについては、[プロファイリング](#performance-profiling) の項で詳細に説明します。

[[yii\log\Target::levels|levels]] プロパティを指定しない場合は、
ターゲットが *全ての* 重大性レベルのメッセージを処理することを意味します。

[[yii\log\Target::categories|categories]] プロパティは、メッセージ・カテゴリの名前またはパターンからなる配列を値として取ります。
ターゲットは、カテゴリの名前がこの配列にあるか、または配列にあるパターンに合致する場合にだけ、メッセージを処理します。
カテゴリ・パターンというのは、最後にアスタリスク `*` を持つカテゴリ名接頭辞です。
カテゴリ名は、パターンと同じ接頭辞で始まる場合に、カテゴリ・パターンに合致します。
例えば、`yii\db\Command::execute` と `yii\db\Command::query` は、[[yii\db\Command]] クラスで記録されるログ・メッセージのためのカテゴリ名です。
そして、両者は共に `yii\db\*` というパターンに合致します。

[[yii\log\Target::categories|categories]] プロパティを指定しない場合は、
ターゲットが *全ての* カテゴリのメッセージを処理することを意味します。

処理するカテゴリを [[yii\log\Target::categories|categories]] プロパティで指定する以外に、
処理から除外するカテゴリを [[yii\log\Target::except|except]] プロパティによって指定することも可能です。
カテゴリの名前がこの配列にあるか、または配列にあるパターンに合致する場合は、メッセージはターゲットによって処理されません。

次のターゲットの構成は、ターゲットが、`yii\db\*` または `yii\web\HttpException:*` に合致するカテゴリ名を持つエラーおよび警告のメッセージだけを処理すべきこと、
ただし、`yii\web\HttpException:404` は除外すべきことを指定するものです。

```php
[
    'class' => 'yii\log\FileTarget',
    'levels' => ['error', 'warning'],
    'categories' => [
        'yii\db\*',
        'yii\web\HttpException:*',
    ],
    'except' => [
        'yii\web\HttpException:404',
    ],
]
```

> Info: HTTP 例外が [エラー・ハンドラ](runtime-handling-errors.md) によって捕捉されたときは、
  `yii\web\HttpException:ErrorCode` という書式のカテゴリ名でエラー・メッセージがログに記録されます。
  例えば、[[yii\web\NotFoundHttpException]] は、`yii\web\HttpException:404` というカテゴリのエラー・メッセージを発生させます。


### メッセージの書式設定 <span id="message-formatting"></span>

ログ・ターゲットはフィルタされたログ・メッセージを一定の書式でエクスポートします。
例えば、[[yii\log\FileTarget]] クラスのログ・ターゲットをインストールした場合は、
`runtime/log/app.log` ファイルに、下記と同様なログ・メッセージが書き込まれます。

```
2014-10-04 18:10:15 [::1][][-][trace][yii\base\Module::getModule] Loading module: debug
```

デフォルトでは、ログ・メッセージは [[yii\log\Target::formatMessage()]] によって、下記のように書式設定されます。

```
タイムスタンプ [IP アドレス][ユーザ ID][セッション ID][重要性レベル][カテゴリ] メッセージテキスト
```

この書式は、[[yii\log\Target::prefix]] プロパティを構成することでカスタマイズすることが出来ます。
[[yii\log\Target::prefix]] プロパティは、カスタマイズされたメッセージ前置情報を返す PHP コーラブルを値として取ります。
例えば、次のコードは、ログ・ターゲットが全てのログ・メッセージの前にカレント・ユーザの ID を置くようにさせるものです(IP アドレスとセッション ID はプライバシー上の理由から削除されています)。

```php
[
    'class' => 'yii\log\FileTarget',
    'prefix' => function ($message) {
        $user = Yii::$app->has('user', true) ? Yii::$app->get('user') : null;
        $userID = $user ? $user->getId(false) : '-';
        return "[$userID]";
    }
]
```

メッセージ前置情報以外にも、ログ・ターゲットは、一群のログ・メッセージごとに一定のコンテキスト情報を追加します。
デフォルトでは、その情報には、次のグローバル PHP 変数、すなわち、`$_GET`、`$_POST`、`$_FILES`、`$_COOKIE`、`$_SESSION` および `$_SERVER` の値が含まれます。
ログ・ターゲットに含ませたいグローバル変数の名前を [[yii\log\Target::logVars]] プロパティに設定することによって、
この動作を調整することが出来ます。
例えば、次のログ・ターゲットの構成は、`$_SERVER` の値だけをログ・メッセージに追加するように指定するものです。

```php
[
    'class' => 'yii\log\FileTarget',
    'logVars' => ['_SERVER'],
]
```

`logVars` を空の配列として構成して、コンテキスト情報をまったく含ませないようにすることも出来ます。
あるいは、また、コンテキスト情報の提供方法を自分で実装したい場合は、
[[yii\log\Target::getContextMessage()]] メソッドをオーバーライドすることも出来ます。

ログに出力したくない機密情報 (例えば、パスワードやアクセス・トークン) を含んでいるリクエストのフィールドについては、`maskVars` プロパティを追加で構成することが出来ます。
デフォルトでは、`$_SERVER[HTTP_AUTHORIZATION]`、`$_SERVER[PHP_AUTH_USER]`、`$_SERVER[PHP_AUTH_PW]` のリクエスト・パラメータが `***` でマスクされまが、
自分自身で設定することも出来ます。例えば、

```php
[
    'class' => 'yii\log\FileTarget',
    'logVars' => ['_SERVER'],
    'maskVars' => ['_SERVER.HTTP_X_PASSWORD']
]
```

### メッセージのトレース・レベル <span id="trace-level"></span>

開発段階では、各ログ・メッセージがどこから来ているかを知りたい場合がよくあります。
これは、次のように、`log` コンポーネントの [[yii\log\Dispatcher::traceLevel|traceLevel]] プロパティを構成することによって達成できます。

```php
return [
    'bootstrap' => ['log'],
    'components' => [
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [...],
        ],
    ],
];
```

上記のアプリケーションの構成は、[[yii\log\Dispatcher::traceLevel|traceLevel]] を `YII_DEBUG` が on のときは 3、`YII_DEBUG` が off のときは 0 に設定します。
これは、`YII_DEBUG` が on のときは、各ログ・メッセージに対して、ログ・メッセージが記録されたときのコール・スタックを最大 3 レベルまで追加し、
`YII_DEBUG` が 0 のときはコール・スタックを含めない、
ということを意味します。

> Info: コール・スタック情報の取得は軽微な処理ではありません。
  従って、この機能は開発時またはアプリケーションをデバッグするときに限って使用するべきです。


### メッセージの吐き出しとエクスポート <span id="flushing-exporting"></span>

既に述べたように、ログ・メッセージは [[yii\log\Logger|ロガー・オブジェクト]] によって配列の中に保持されます。
この配列のメモリ消費を制限するために、この配列に一定数のログ・メッセージが蓄積されるたびに、
ロガーは記録されたメッセージを [ログ・ターゲット](#log-targets) に吐き出します。
この数は、`log` コンポーネントの [[yii\log\Dispatcher::flushInterval|flushInterval]] プロパティを構成することによってカスタマイズすることが出来ます。


```php
return [
    'bootstrap' => ['log'],
    'components' => [
        'log' => [
            'flushInterval' => 100,   // デフォルトは 1000
            'targets' => [...],
        ],
    ],
];
```

> Info: メッセージの吐き出しは、アプリケーションの終了時にも実行されます。これによって、ログ・ターゲットが完全なログ・メッセージを受け取ることが保証されます。

[[yii\log\Logger|ロガー・オブジェクト]] が [ログ・ターゲット](#log-targets) にログ・メッセージを吐き出しても、ログ・メッセージはただちにはエクスポートされません。
そうではなく、ログ・ターゲットが一定数のフィルタされたメッセージを蓄積して初めて、メッセージのエクスポートが発生します。
この数は、下記のように、個々の [ログ・ターゲット](#log-targets) の [[yii\log\Target::exportInterval|exportInterval]]
プロパティを構成することによってカスタマイズすることが出来ます。

```php
[
    'class' => 'yii\log\FileTarget',
    'exportInterval' => 100,  // デフォルトは 1000
]
```

デフォルトの状態では、吐き出しとエクスポートの間隔の設定のために、`Yii::debug()` やその他のログ記録メソッドを呼んでも、
ただちには、ログ・メッセージはログ・ターゲットに出現しません。
このことは、長時間にわたって走るコンソール・アプリケーションでは、問題になる場合もあります。
各ログ・メッセージがただちにログ・ターゲットに出現するようにするためには、下記のように、[[yii\log\Dispatcher::flushInterval|flushInterval]] と
[[yii\log\Target::exportInterval|exportInterval]] の両方を 1 に設定しなければなりません。

```php
return [
    'bootstrap' => ['log'],
    'components' => [
        'log' => [
            'flushInterval' => 1,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'exportInterval' => 1,
                ],
            ],
        ],
    ],
];
```

> Note: 頻繁なメッセージの吐き出しとエクスポートはアプリケーションのパフォーマンスを低下させます。


### ログ・ターゲットの 有効/無効 を切り替える <span id="toggling-log-targets"></span>

[[yii\log\Target::enabled|enabled]] プロパティを構成することによって、ログ・ターゲットを有効にしたり無効にしたりすることが出来ます。
この切り替えは、ログ・ターゲットのコンフィギュレーションでも出来ますが、コードの中で次の PHP 文を使っても出来ます。

```php
Yii::$app->log->targets['file']->enabled = false;
```

上記のコードでは、ターゲットが `file` という名前であることが必要とされています。
下記のように、`targets` の配列で文字列のキーを使ってターゲットの名前を指定して下さい。

```php
return [
    'bootstrap' => ['log'],
    'components' => [
        'log' => [
            'targets' => [
                'file' => [
                    'class' => 'yii\log\FileTarget',
                ],
                'db' => [
                    'class' => 'yii\log\DbTarget',
                ],
            ],
        ],
    ],
];
```

バージョン 2.0.13 以降は、[[yii\log\Target::enabled|enabled]] を設定するのに、
ログ・ターゲットを有効にすべきか否かの動的な条件を定義するコーラブルを指定することが可能です。
[[yii\log\Target::setEnabled()]] のドキュメントに一例がありますので参照して下さい。

### 新しいターゲットを作る <span id="new-targets"></span>

新しいログ・ターゲット・クラスを作ることは非常に簡単です。
必要なことは、主として、[[yii\log\Target::messages]] 配列の中身を指定された媒体に送出する [[yii\log\Target::export()]] メソッドを実装することです。
各メッセージに書式を設定するためには、[[yii\log\Target::formatMessage()]] を呼ぶことが出来ます。
詳細については、Yii リリースに含まれているログ・ターゲット・クラスのどれか一つを参照してください。

> Tip: あなた自身のロガーを書く代りに、[PSR ログ・ターゲット・エクステンション](https://github.com/samdark/yii2-psr-log-target) によって、
  [Monolog](https://github.com/Seldaek/monolog) のような
  PSR-3 互換ロガーのどれかを使ってみるのも良いでしょう。

## パフォーマンス・プロファイリング <span id="performance-profiling"></span>

パフォーマンス・プロファイリングは、特定のコード・ブロックに要した時間を測定してパフォーマンスのボトルネックになっている所を見つけ出すために使われる、
特殊なタイプのメッセージ・ロギングです。
例えば、[[yii\db\Command]] クラスは、各 DB クエリに要した時間を知るために、パフォーマンス・プロファイリングを使用しています。

パフォーマンス・プロファイリングを使用するためには、最初に、プロファイリングが必要なコード・ブロックを特定します。
そして、各コード・ブロックを次のように囲みます。

```php
\Yii::beginProfile('myBenchmark');

... プロファイリングされるコード・ブロック ...

\Yii::endProfile('myBenchmark');
```

ここで `myBenchmark` はコード・ブロックを特定するユニークなトークンを表します。
後でプロファイリング結果を検査するときに、このトークンを使って、対応するコード・ブロックによって消費された時間を調べます。

`beginProfile` と `endProfile` のペアが適正な入れ子になっていることを確認することが非常に重要なことです。
例えば、

```php
\Yii::beginProfile('block1');

    // プロファイリングされる何らかのコード

    \Yii::beginProfile('block2');
        // プロファイリングされる別のコード
    \Yii::endProfile('block2');

\Yii::endProfile('block1');
```

`\Yii::endProfile('block1')` を忘れたり、`\Yii::endProfile('block1')` と `\Yii::endProfile('block2')` の順序を入れ替えたりすると、
パフォーマンス・プロファイリングは機能しません。

プロファイルされるコード・ブロックの全てについて、おのおの、重大性レベルが `profile` であるログ・メッセージが記録されます。
そのようなメッセージを集めてエクスポートする [ログ・ターゲット](#log-targets) を構成してください。
[Yii デバッガ](tool-debugger.md) が、プロファイリング結果を表示するパフォーマンス・プロファイリング・パネルを内蔵しています。
