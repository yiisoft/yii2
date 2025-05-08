メール送信
==========

> Note: このセクションはまだ執筆中です。

Yii は電子メールの作成と送信をサポートしています。
ただし、フレームワークのコアが提供するのは、コンテント作成の機能と基本的なインタフェイスだけです。
実際のメール送信メカニズムはエクステンションによって提供されなければなりません。
と言うのは、メール送信はプロジェクトが異なるごとに異なる実装が必要とされるでしょうし、通常、外部のサービスやライブラリに依存するものだからです。

ごく一般的な場合であれば、yii2-symfonymailer](https://www.yiiframework.com/extension/yiisoft/yii2-symfonymailer) 公式エクステンションを使用することが出来ます。


構成
----

メール・コンポーネントの構成は、あなたが選んだエクステンションに依存します。
一般的には、アプリケーションの構成情報は次のようなものになる筈です。

```php
return [
    //....
    'components' => [
        'mailer' => [
            'class' => 'yii\symfonymailer\Mailer',
            'useFileTransport' => false,
            'transport' => [
                'dsn' => 'smtp://user:pass@smtp.example.com:465',
            ],
        ],
    ],
];
```


基本的な使用方法
----------------

いったん `mailer` コンポーネントを構成すれば、次のコードを使って電子メールのメッセージを送信することが出来るようになります。

```php
Yii::$app->mailer->compose()
    ->setFrom('from@domain.com')
    ->setTo('to@domain.com')
    ->setSubject('メッセージの題')
    ->setTextBody('プレーンテキストのコンテント')
    ->setHtmlBody('<b>HTML のコンテント</b>')
    ->send();
```

上の例では、`compose()` メソッドでメール・メッセージのインスタンスを作成し、それに値を投入して送信しています。
必要であれば、このプロセスにもっと複雑なロジックを置くことも可能です。

```php
$message = Yii::$app->mailer->compose();
if (Yii::$app->user->isGuest) {
    $message->setFrom('from@domain.com');
} else {
    $message->setFrom(Yii::$app->user->identity->email);
}
$message->setTo(Yii::$app->params['adminEmail'])
    ->setSubject('メッセージの題')
    ->setTextBody('プレーン・テキストのコンテント')
    ->send();
```

> Note: すべての `mailer` エクステンションは、二つの主要なクラス、すなわち、`Mailer` と `Message` のセットとして提供されます。
  `Mailer` は常に `Message` のクラス名と仕様を知っています。
  `Message` オブジェクトのインスタンスを直接に作成しようとしてはいけません。常に `compose()` メソッドを使って作成してください。

いくつかのメッセージを一度に送信することも出来ます。

```php
$messages = [];
foreach ($users as $user) {
    $messages[] = Yii::$app->mailer->compose()
        // ...
        ->setTo($user->email);
}
Yii::$app->mailer->sendMultiple($messages);
```

メール・エクステンションの中には、単一のネットワーク・メッセージを使うなどして、この手法の恩恵を享受することが出来るものもいくつかあるでしょう。


メールのコンテントを作成する
----------------------------

Yii は実際のメール・メッセージを特別なビュー・ファイルによって作成することを許容しています。
デフォルトでは、それらのファイルは `@app/mail` というパスに配置されなければなりません。

以下はメール・ビュー・ファイルの内容の例です。

```php
<?php
use yii\helpers\Html;
use yii\helpers\Url;

/* @var $this \yii\web\View ビュー・コンポーネントのインスタンス */
/* @var $message \yii\mail\BaseMessage 新しく作成されたメール・メッセージのインスタンス */

?>
<h2>ワン・クリックで私たちのサイトのホームページを訪問することが出来ます</h2>
<?= Html::a('ホームページへ', Url::home('http')) ?>
```

ビュー・ファイルによってメッセージを作成するためには、単に `compose()` メソッドにビューの名前を渡すだけで十分です。

```php
Yii::$app->mailer->compose('home-link') // ここでビューのレンダリング結果がメッセージのボディになります
    ->setFrom('from@domain.com')
    ->setTo('to@domain.com')
    ->setSubject('メッセージの題')
    ->send();
```

ビュー・ファイルの中で利用できる追加のビュー・パラメータを `compose()` メソッドに渡すことができます。

```php
Yii::$app->mailer->compose('greetings', [
    'user' => Yii::$app->user->identity,
    'advertisement' => $adContent,
]);
```

HTML と平文テキストのメッセージ・コンテントに違うビューを指定することが出来ます。

```php
Yii::$app->mailer->compose([
    'html' => 'contact-html',
    'text' => 'contact-text',
]);
```

ビュー名をスカラーの文字列として渡した場合は、そのレンダリング結果は HTML ボディとして使われます。
そして、平文テキストのボディは HTML のボディから全ての HTML 要素を削除することによって作成されます。

ビューのレンダリング結果はレイアウトで包むことが出来ます。レイアウトは、[[yii\mail\BaseMailer::htmlLayout]] と [[yii\mail\BaseMailer::textLayout]] を使ってセットアップすることが可能です。
レイアウトは、通常のウェブ・アプリケーションのレイアウトと同じように働きます。
レイアウトは、メールの CSS スタイルや、その他の共有されるコンテントをセットアップするために使うことが出来ます。

```php
<?php
use yii\helpers\Html;

/* @var $this \yii\web\View ビュー・コンポーネントのインスタンス */
/* @var $message \yii\mail\MessageInterface 作成されるメッセージ */
/* @var $content string メイン・ビューのレンダリング結果 */
?>
<?php $this->beginPage() ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=<?= Yii::$app->charset ?>" />
    <style type="text/css">
        .heading {...}
        .list {...}
        .footer {...}
    </style>
    <?php $this->head() ?>
</head>
<body>
    <?php $this->beginBody() ?>
    <?= $content ?>
    <div class="footer">よろしくお願いします。<?= Yii::$app->name ?> チーム</div>
    <?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
```


ファイルの添付
--------------

`attach()` メソッド、`attachContent()` メソッドを使って、メッセージにファイルを添付することが出来ます。

```php
$message = Yii::$app->mailer->compose();

// ローカル・ファイル・システムからファイルを添付する
$message->attach('/path/to/source/file.pdf');

// 添付ファイルをその場で生成する
$message->attachContent('添付される内容', ['fileName' => 'attach.txt', 'contentType' => 'text/plain']);
```


画像の埋め込み
--------------

`embed()` メソッドを使って、メッセージのコンテントに画像を埋め込むことが出来ます。
このメソッドは添付ファイルの ID を返しますので、それを `img` タグで使わなければなりません。
このメソッドはビュー・ファイルによってメッセージのコンテントを作成するときに簡単に使うことが出来ます。

```php
Yii::$app->mailer->compose('embed-email', ['imageFileName' => '/path/to/image.jpg'])
    // ...
    ->send();
```

そして、ビュー・ファイルの中では、次のコードを使うことが出来ます。

```php
<img src="<?= $message->embed($imageFileName); ?>">
```


テストとデバッグ
----------------

開発者は、実際にどのようなメールがアプリケーションによって送信されたか、その内容はどのようなものであったか、等をチェックしなければならないことが多くあります。
Yii は、そのようなチェックが出来ることを `yii\mail\BaseMailer::useFileTransport` によって保証しています。
このオプションを有効にすると、メールのメッセージ・データが、通常のように送信される代りに、ローカル・ファイルに強制的に保存されます。
ファイルは、`yii\mail\BaseMailer::fileTransportPath`、デフォルトでは `@runtime/mail` の下に保存されます。

> Note: メッセージをファイルに保存するか、実際の受信者に送信するか、どちらかを選ぶことが出来ますが、両方を同時に実行することは出来ません。

メール・メッセージのファイルは通常のテキストエディタで開くことが出来ますので、実際のメッセージ・ヘッダやコンテントなどを閲覧することが出来ます。
このメカニズムは、アプリケーションのデバッグや単体テストを実行する際に、真価を発揮するでしょう。

> Note: メール・メッセージのファイルの内容は `\yii\mail\MessageInterface::toString()` によって作成されますので、
  あなたのアプリケーションで使用している実際のメール・エクステンションに依存したものになります。


あなた自身のメール・ソリューションを作成する
--------------------------------------------

あなた自身のカスタム・メール・ソリューションを作成するためには、二つのクラスを作成する必要があります。
すなわち、一つは `Mailer` であり、もう一つは `Message` です。
`yii\mail\BaseMailer` と `yii\mail\BaseMessage` をあなたのソリューションの基底クラスとして使うことが出来ます。
これらのクラスが、このガイドで説明された基本的なロジックを既に持っています。しかし、それを使用することは強制されていません。
`yii\mail\MailerInterface` と `yii\mail\MessageInterface` のインタフェイスを実装すれば十分です。
そして、あなたのソリューションをビルドするために、全ての抽象メソッドを実装しなければなりません。
