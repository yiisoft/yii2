ロギング
========

Yii は高度なカスタマイズ性と拡張性を持った強力なロギングフレームワークを提供しています。このフレームワークを使用すると、
さまざまな種類のメッセージを記録し、フィルタして、ファイル、データベース、メールなど、さまざまなターゲットに収集することが簡単に出来ます。

Yii のロギングフレームワークを使うためには、下記のステップを踏みます。
 
* コードのさまざまな場所で [ログメッセージ](#log-messages) を記録する。
* アプリケーションのコンフィギュレーションで [ログターゲット](#log-targets) を構成して、ログメッセージをフィルタしてエクスポートする。
* さまざまなターゲット (例えば [Yii デバッガ](tool-debugger.md)) によって、フィルタされエクスポートされたログメッセージを調査する。

この節では、主として最初の二つのステップについて説明します。


## メッセージを記録する <a name="log-messages"></a>

ログメッセージを記録することは、次のログ記録メソッドのどれかを呼び出すだけの簡単なことです。

* [[Yii::trace()]]: コードの断片がどのように走るかをトレースするメッセージを記録します。主として開発のために使用します。
* [[Yii::info()]]: 何らかの有用な情報を伝えるメッセージを記録します。
* [[Yii::warning()]]: 何か予期しないことが発生したことを示す警告メッセージを記録します。
* [[Yii::error()]]: 出来るだけ早急に調査すべき致命的なエラーを記録します。

これらのログ記録メソッドは、ログメッセージをさまざまな *重大性レベル* と *カテゴリ* で記録するものです。
これらのメソッドは `function ($message, $category = 'application')` という関数シグニチャを共有しており、`$message`
は記録されるログメッセージを示し、`$category` はログメッセージのカテゴリを示します。
次のコードサンプルは、トレースメッセージをデフォルトのカテゴリである `application` の下に記録するものです。

```php
Yii::trace('平均収益の計算を開始');
```

> Info|情報: ログメッセージは文字列でも、配列やオブジェクトのような複雑なデータでも構いません。
ログメッセージを適切に取り扱うのは [ログターゲット](#log-targets) の責任です。
既定では、ログメッセージが文字列でない場合は、[[yii\helpers\VarDumper::export()]] が呼ばれて文字列に変換されることになります。

ログメッセージを上手に整理しフィルタするために、すべてのログメッセージにそれぞれ適切なカテゴリを指定することが推奨されます。
カテゴリに階層的な命名方法を採用すると、[ログターゲット](#log-targets) がカテゴリに基づいてメッセージをフィルタすることが容易になります。
簡単でしかも効果的な命名方法は、カテゴリ名に PHP のマジック定数 `__METHOD__` を使用することです。
これは、Yii フレームワークのコアコードでも使われている方法です。例えば、

```php
Yii::trace('平均収益の計算を開始', __METHOD__);
```

`__METHOD__` という定数は、それが出現する場所のメソッド名 (完全修飾のクラス名が前置されます) として評価されます。
例えば、上記のコードが `app\controllers\RevenueController::calculate` というメソッドの中で呼ばれている場合は、
`__METHOD__` は `'app\controllers\RevenueController::calculate'` という文字列と同じになります。

> Info|情報: 上記で説明したメソッドは、実際には、[[yii\log\Logger|ロガーオブジェクト]] の [[yii\log\Logger::log()|log()]] メソッドへのショートカットです。
[[yii\log\Logger|ロガーオブジェクト]] は `Yii::getLogger()` という式でアクセス可能なシングルトンです。
ロガーオブジェクトは、十分な量のメッセージが記録されたとき、または、アプリケーションが終了するときに、[[yii\log\Dispatcher|メッセージディスパッチャ]]
を呼んで、登録された [ログターゲット](#log-targets) に記録されたログメッセージを送信します。


## ログターゲット <a name="log-targets"></a>

ログターゲットは [[yii\log\Target]] クラスまたはその子クラスのインスタンスです。ログターゲットは、
ログメッセージを重大性レベルとカテゴリによってフィルタして、何らかの媒体にエクスポートします。
例えば、[[yii\log\DbTarget|データベースターゲット]] は、フィルタされたログメッセージをデータベーステーブルにエクスポートし、
[[yii\log\EmailTarget|メールターゲット]] は、ログメッセージを指定されたメールアドレスにエクスポートします。

一つのアプリケーションの中で複数のログターゲットを登録することが出来ます。そのためには、次のように、
アプリケーションのコンフィギュレーションの中で、`log` [アプリケーションコンポーネント](structure-application-components.md) によってログターゲットを構成します。

```php
return [
    // "log" コンポーネントはブートストラップ時にロードされなければならない
    'bootstrap' => ['log'],
    
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
                       'subject' => 'Database errors at example.com',
                    ],
                ],
            ],
        ],
    ],
];
```

> Note|注意: `log` コンポーネントは、ログメッセージをターゲットに即座に送付することが出来るように、
[ブートストラップ](runtime-bootstrapping.md) 時にロードされなければなりません。
この理由により、上記の例で示されているように、`bootstrap` の配列に `log` をリストアップしています。

上記のコードでは、二つのログターゲットが [[yii\log\Dispatcher::targets]] プロパティに登録されています。

* 最初のターゲットは、エラーと警告のメッセージを選択して、データベーステーブルに保存します。
* 第二のターゲットは、名前が `yii\db\` で始まるカテゴリの下のエラーメッセージを選んで、
  `admin@example.com` と `developer@example.com` の両方にメールで送信します。

Yii は下記のログターゲットをあらかじめ内蔵しています。その構成方法と使用方法を学ぶためには、
これらのクラスの API ドキュメントを参照してください。

* [[yii\log\DbTarget]]: ログメッセージをデータベーステーブルに保存する。
* [[yii\log\EmailTarget]]: ログメッセージを事前に指定されたメールアドレスに送信する。
* [[yii\log\FileTarget]]: ログメッセージをファイルに保存する。
* [[yii\log\SyslogTarget]]: ログメッセージを PHP 関数 `syslog()` を呼んでシステムログに保存する。

以下では、全てのターゲットに共通する機能について説明します。

  
### メッセージのフィルタリング <a name="message-filtering"></a>

全てのログターゲットについて、それぞれ、[[yii\log\Target::levels|levels]] と [[yii\log\Target::categories|categories]]
のプロパティを構成して、ターゲットが処理すべきメッセージの重要性レベルとカテゴリを指定することが出来ます。

[[yii\log\Target::levels|levels]] プロパティは、次のレベルの一つまたは複数からなる配列を値として取ります。

* `error`: [[Yii::error()]] によって記録されたメッセージに対応。
* `warning`: [[Yii::warning()]] によって記録されたメッセージに対応。
* `info`: [[Yii::info()]] によって記録されたメッセージに対応。
* `trace`: [[Yii::trace()]] によって記録されたメッセージに対応。
* `profile`: [[Yii::beginProfile()]] と [[Yii::endProfile()]] によって記録されたメッセージに対応。
  これについては、[プロファイリング](#performance-profiling) の項で詳細に説明します。

[[yii\log\Target::levels|levels]] プロパティを指定しない場合は、ターゲットが *全ての* 重大性レベルのメッセージを処理することを意味します。

[[yii\log\Target::categories|categories]] プロパティは、メッセージカテゴリの名前またはパターンからなる配列を値として取ります。
ターゲットは、カテゴリの名前がこの配列にあるか、または配列にあるパターンに合致する場合にだけ、メッセージを処理します。
カテゴリパターンというのは、最後にアスタリスク `*` を持つカテゴリ名接頭辞です。カテゴリ名は、パターンと同じ接頭辞で始まる場合に、カテゴリパターンに合致します。
例えば、`yii\db\Command::execute` と `yii\db\Command::query` は、[[yii\db\Command]] クラスで記録されるログメッセージのためのカテゴリ名です。
そして、両者は共に `yii\db\*` というパターンに合致します。

[[yii\log\Target::categories|categories]] プロパティを指定しない場合は、ターゲットが *全ての* カテゴリのメッセージを処理することを意味します。

カテゴリを [[yii\log\Target::categories|categories]] プロパティでホワイトリストとして登録する以外に、一定のカテゴリを [[yii\log\Target::except|except]]
プロパティによってブラックリストとして登録することも可能です。
カテゴリの名前がこの配列にあるか、または配列にあるパターンに合致する場合は、メッセージはターゲットによって処理されません。

次のターゲットのコンフィギュレーションは、ターゲットが、`yii\db\*` または `yii\web\HttpException:*`
に合致するカテゴリ名を持つエラーおよび警告のメッセージだけを処理すべきこと、ただし、`yii\web\HttpException:404`
は除外すべきことを指定するものです。

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

> Info|情報: HTTP 例外が [エラーハンドラ](runtime-handling-errors.md) によって捕捉されたときは、
  `yii\web\HttpException:ErrorCode` という書式のカテゴリ名でエラーメッセージがログに記録されます。
  例えば、[[yii\web\NotFoundHttpException]] は、`yii\web\HttpException:404` というカテゴリのエラーメッセージを発生させます。


### Message Formatting <a name="message-formatting"></a>

Log targets export the filtered log messages in a certain format. For example, if you install
a log target of the class [[yii\log\FileTarget]], you may find a log message similar to the following in the
`runtime/log/app.log` file:

```
2014-10-04 18:10:15 [::1][][-][trace][yii\base\Module::getModule] Loading module: debug
```

By default, log messages will be formatted as follows by the [[yii\log\Target::formatMessage()]]:

```
Timestamp [IP address][User ID][Session ID][Severity Level][Category] Message Text
```

You may customize this format by configuring the [[yii\log\Target::prefix]] property which takes a PHP callable
returning a customized message prefix. For example, the following code configures a log target to prefix each
log message with the current user ID (IP address and Session ID are removed for privacy reasons).

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

Besides message prefixes, log targets also append some context information to each batch of log messages.
By default, the values of these global PHP variables are included: `$_GET`, `$_POST`, `$_FILES`, `$_COOKIE`,
`$_SESSION` and `$_SERVER`. You may adjust this behavior by configuring the [[yii\log\Target::logVars]] property
with the names of the global variables that you want to include by the log target. For example, the following
log target configuration specifies that only the value of the `$_SERVER` variable will be appended to the log messages.

```php
[
    'class' => 'yii\log\FileTarget',
    'logVars' => ['_SERVER'],
]
```

You may configure `logVars` to be an empty array to totally disable the inclusion of context information.
Or if you want to implement your own way of providing context information, you may override the
[[yii\log\Target::getContextMessage()]] method.


### Message Trace Level <a name="trace-level"></a>

During development, it is often desirable to see where each log message is coming from. This can be achieved by
configuring the [[yii\log\Dispatcher::traceLevel|traceLevel]] property of the `log` component like the following:

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

The above application configuration sets [[yii\log\Dispatcher::traceLevel|traceLevel]] to be 3 if `YII_DEBUG` is on
and 0 if `YII_DEBUG` is off. This means, if `YII_DEBUG` is on, each log message will be appended with at most 3
levels of the call stack at which the log message is recorded; and if `YII_DEBUG` is off, no call stack information
will be included.

> Info: Getting call stack information is not trivial. Therefore, you should only use this feature during development
or when debugging an application.


### Message Flushing and Exporting <a name="flushing-exporting"></a>

As aforementioned, log messages are maintained in an array by the [[yii\log\Logger|logger object]]. To limit the
memory consumption by this array, the logger will flush the recorded messages to the [log targets](#log-targets)
each time the array accumulates a certain number of log messages. You can customize this number by configuring
the [[yii\log\Dispatcher::flushInterval|flushInterval]] property of the `log` component:


```php
return [
    'bootstrap' => ['log'],
    'components' => [
        'log' => [
            'flushInterval' => 100,   // default is 1000
            'targets' => [...],
        ],
    ],
];
```

> Info: Message flushing also occurs when the application ends, which ensures log targets can receive complete log messages.

When the [[yii\log\Logger|logger object]] flushes log messages to [log targets](#log-targets), they do not get exported
immediately. Instead, the message exporting only occurs when a log target accumulates certain number of the filtered
messages. You can customize this number by configuring the [[yii\log\Target::exportInterval|exportInterval]]
property of individual [log targets](#log-targets), like the following,

```php
[
    'class' => 'yii\log\FileTarget',
    'exportInterval' => 100,  // default is 1000
]
```

Because of the flushing and exporting level setting, by default when you call `Yii::trace()` or any other logging
method, you will NOT see the log message immediately in the log targets. This could be a problem for some long-running
console applications. To make each log message appear immediately in the log targets, you should set both
[[yii\log\Dispatcher::flushInterval|flushInterval]] and [[yii\log\Target::exportInterval|exportInterval]] to be 1,
as shown below:

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

> Note: Frequent message flushing and exporting will degrade the performance of your application.


### Toggling Log Targets <a name="toggling-log-targets"></a>

You can enable or disable a log target by configuring its [[yii\log\Target::enabled|enabled]] property.
You may do so via the log target configuration or by the following PHP statement in your code:

```php
Yii::$app->log->targets['file']->enabled = false;
```

The above code requires you to name a target as `file`, as shown below by using string keys in the
`targets` array:

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


### Creating New Targets <a name="new-targets"></a>

Creating a new log target class is very simple. You mainly need to implement the [[yii\log\Target::export()]] method
sending the content of the [[yii\log\Target::messages]] array to a designated medium. You may call the
[[yii\log\Target::formatMessage()]] method to format each message. For more details, you may refer to any of the
log target classes included in the Yii release.


## Performance Profiling <a name="performance-profiling"></a>

Performance profiling is a special type of message logging that is used to measure the time taken by certain
code blocks and find out what are the performance bottlenecks. For example, the [[yii\db\Command]] class uses
performance profiling to find out the time taken by each DB query.

To use performance profiling, first identify the code blocks that need to be profiled. Then enclose each
code block like the following:

```php
\Yii::beginProfile('myBenchmark');

...code block being profiled...

\Yii::endProfile('myBenchmark');
```

where `myBenchmark` stands for a unique token identifying a code block. Later when you examine the profiling
result, you will use this token to locate the time spent by the corresponding code block.

It is important to make sure that the pairs of `beginProfile` and `endProfile` are properly nested.
For example,

```php
\Yii::beginProfile('block1');

    // some code to be profiled

    \Yii::beginProfile('block2');
        // some other code to be profiled
    \Yii::endProfile('block2');

\Yii::endProfile('block1');
```

If you miss `\Yii::endProfile('block1')` or switch the order of `\Yii::endProfile('block1')` and
`\Yii::endProfile('block2')`, the performance profiling will not work.

For each code block being profiled, a log message with the severity level `profile` is recorded. You can configure
a [log target](#log-targets) to collect such messages and export them. The [Yii debugger](tool-debugger.md) has
a built-in performance profiling panel showing the profiling results.
