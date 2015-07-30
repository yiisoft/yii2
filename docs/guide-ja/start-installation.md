Yii をインストールする
======================

Yii は二つの方法でインストールすることが出来ます。すなわち、[Composer](https://getcomposer.org/) を使うか、アーカイブファイルをダウンロードするかです。
前者がお薦めの方法です。と言うのは、一つのコマンドを走らせるだけで、新しい [エクステンション](structure-extensions.md) をインストールしたり、Yii をアップデートしたりすることが出来るからです。

Yii の標準的なインストールを実行すると、フレームワークとプロジェクトテンプレートの両方がダウンロードされてインストールされます。
プロジェクトテンプレートは、いくつかの基本的な機能、例えば、ログインやコンタクトフォームなどを実装した、動作する Yii アプリケーションです。
そのコードは推奨される方法に従って編成されています。
そのため、プロジェクトテンプレートは、あなたのプロジェクトのための良い開始点としての役割を果たしうるものです。

この節と後続のいくつかの節においては、いわゆる *ベーシックプロジェクトテンプレート* とともに Yii をインストールする方法、および、このテンプレート上に新しい機能を実装する方法を説明します。
Yii はもう一つ、[アドバンストプロジェクトテンプレート](https://github.com/yiisoft/yii2-app-advanced/blob/master/docs/guide-ja/README.md) と呼ばれるテンプレートも提供しています。
こちらは、チーム開発環境において多層構造のアプリケーションを開発するときに使用する方が望ましいものです。

> Info|情報: ベーシックプロジェクトテンプレートは、ウェブアプリケーションの 90 パーセントを開発するのに適したものです。
  アドバンストプロジェクトテンプレートとの主な違いは、コードがどのように編成されているかという点にあります。
  あなたが Yii は初めてだという場合は、シンプルでありながら十分な機能を持っているベーシックプロジェクトテンプレートに留まることを強く推奨します。


Composer によるインストール <span id="installing-via-composer"></span>
---------------------------

まだ Composer をインストールしていない場合は、[getcomposer.org](https://getcomposer.org/download/) の指示に従ってインストールすることが出来ます。
Linux や Mac OS X では、次のコマンドを実行します。

    curl -sS https://getcomposer.org/installer | php
    mv composer.phar /usr/local/bin/composer

Windows では、[Composer-Setup.exe](https://getcomposer.org/Composer-Setup.exe) をダウンロードして実行します。

何か問題が生じたときや、Composer の使い方に関してもっと学習したいときは、[Composer ドキュメント](https://getcomposer.org/doc/) を参照してください。

以前に Composer をインストールしたことがある場合は、確実に最新のバージョンを使うようにしてください。
Composer は `composer self-update` コマンドを実行してアップデートすることが出来ます。

Composer がインストールされたら、ウェブからアクセスできるフォルダで下記のコマンドを実行することによって Yii をインストールすることが出来ます。

    composer global require "fxp/composer-asset-plugin:~1.0.0"
    composer create-project --prefer-dist yiisoft/yii2-app-basic basic

最初のコマンドは [composer アセットプラグイン](https://github.com/francoispluchino/composer-asset-plugin/) をインストールします。
これにより、Composer を通じて bower と npm の依存パッケージを管理することが出来るようになります。
このコマンドは一度だけ実行すれば十分です。
第二のコマンドは `basic` という名前のディレクトリに Yii をインストールします。
必要なら別のディレクトリ名を選ぶことも出来ます。

> Note|注意: インストール実行中に Composer が あなたの Github のログイン認証情報を求めることがあるかも知れません。
> これは、Comoser が依存パッケージの情報を Github から読み出すために十分な API レートを必要とするためで、普通にあることです。
> 詳細については、[Composer ドキュメント](https://getcomposer.org/doc/articles/troubleshooting.md#api-rate-limit-and-oauth-tokens) を参照してください。

> Tip|ヒント: Yii の最新の開発バージョンをインストールしたい場合は、[stability option](https://getcomposer.org/doc/04-schema.md#minimum-stability) を追加した次のコマンドを代りに使うことが出来ます。
>
>     composer create-project --prefer-dist --stability=dev yiisoft/yii2-app-basic basic
>
> 開発バージョンは動いているあなたのコードを動かなくするかもしれませんので、本番環境では使うべきでないことに注意してください。


アーカイブファイルからインストールする <span id="installing-from-archive-file"></span>
--------------------------------------

アーカイブファイルから Yii をインストールするには、三つの手順を踏みます。

1. [yiiframework.com](http://www.yiiframework.com/download/) からアーカイブファイルをダウンロードする。
2. ダウンロードしたファイルをウェブからアクセスできるフォルダーに展開する。
3. `config/web.php` ファイルを編集して、`cookieValidationKey` という構成情報の項目に秘密キーを入力する
   (Composer を使って Yii をインストールするときは、これは自動的に実行されます)。

   ```php
   // !!! 下記に(もし空白なら)秘密キーを入力する - これはクッキー検証のために必要
   'cookieValidationKey' => '秘密キーをここに入力',
   ```


他のインストールオプション <span id="other-installation-options"></span>
--------------------------

上記のインストール方法の説明は Yii のインストールの仕方を示すものですが、それは同時に、直ちに動作する基本的なウェブアプリケーションを作成するものでもあります。
これは、規模の大小に関わらず、ほとんどのプロジェクトを開始するのに良い方法です。
特に、Yii の学習を始めたばかりの場合には、この方法が適しています。

しかし、他のインストールオプションも利用可能です。

* コアフレームワークだけをインストールし、アプリケーション全体を一から構築したい場合は、[アプリケーションを一から構築する](tutorial-start-from-scratch.md)
  で説明されている指示に従うことが出来ます。
* もっと洗練された、チーム開発環境により適したアプリケーションから開始したい場合は、 [アドバンストプロジェクトテンプレート](https://github.com/yiisoft/yii2-app-advanced/blob/master/docs/guide-ja/README.md) をインストールすることを考慮することが出来ます。


インストールを検証する <span id="verifying-installation"></span>
----------------------

インストール完了後、下記の URL によって、インストールされた Yii アプリケーションにブラウザを使ってアクセスすることが出来ます。

```
http://localhost/basic/web/index.php
```

この URL は、あなたが Yii を ウェブサーバのドキュメントルートディレクトリ直下の `basic` という名前のディレクトリにインストールしたこと、
そして、ウェブサーバがローカルマシン (`localhost`) で走っていることを想定しています。
あなたのインストールの環境に合うように URL を変更する必要があるかもしれません。

![Yii のインストールが成功](images/start-app-installed.png)

ブラウザに上のような "おめでとう!" のページが表示されるはずです。
もし表示されなかったら、PHP のインストールが Yii の必要条件を満たしているかどうか、チェックしてください。
最低限の必要条件を満たしているかどうかは、次の方法のどちらかによってチェックすることが出来ます。

* ブラウザを使って URL `http://localhost/basic/requirements.php` にアクセスする。
* 次のコマンドを実行する。

  ```
  cd basic
  php requirements.php
  ```

Yii の最低必要条件を満たすように PHP のインストールを構成しなければなりません。
最も重要なことは、PHP 5.4 以上でなければならないということです。
また、アプリケーションがデータベースを必要とする場合は、[PDO PHP 拡張](http://www.php.net/manual/ja/pdo.installation.php) および対応するデータベースドライバ (MySQL データベースのための `pdo_mysql` など) をインストールしなければなりません。


ウェブサーバを構成する <span id="configuring-web-servers"></span>
----------------------

> Info|情報: もし Yii の試運転をしているだけで、本番サーバに配備する意図がないのであれば、当面、この項は飛ばしても構いません。

上記の説明に従ってインストールされたアプリケーションは、[Apache HTTP サーバ](http://httpd.apache.org/) と [Nginx HTTP サーバ](http://nginx.org/) のどちらでも、また、Windows、Mac OS X、Linux のどれでも、PHP 5.4 以上を走らせている環境であれば、そのままの状態で動作するはずです。
Yii 2.0 は、また、facebook の [HHVM](http://hhvm.com/) とも互換性があります。
ただし HHVM がネイティブの PHP とは異なる振舞いをする特殊なケースもいくつかありますので、HHVM を使うときはいくらか余分に注意を払う必要があります。

本番用のサーバでは、`http://www.example.com/basic/web/index.php` の代りに `http://www.example.com/index.php` という URL でアプリケーションにアクセス出来るようにウェブサーバを設定したいでしょう。
そういう設定をするためには、ウェブサーバのドキュメントルートを `basic/web` フォルダに向けることが必要になります。
また、[ルーティングと URL 生成](runtime-routing.md) の節で述べられているように、URL から `index.php` を隠したいとも思うでしょう。
この節では、これらの目的を達するために Apache または Nginx サーバをどのように設定すれば良いかを学びます。

> Info|情報: `basic/web` をドキュメントルートに設定することは、`basic/web` の兄弟ディレクトリに保存されたプライベートなアプリケーションコードや公開できないデータファイルにエンドユーザがアクセスすることを防止することにもなります。
`basic/web` 以外のフォルダに対するアクセスを拒否することはセキュリティ強化の一つです。

> Info|情報: あなたがウェブサーバの設定を修正する権限を持たない共用ホスティング環境でアプリケーションが走る場合であっても、セキュリティ強化のためにアプリケーションの構造を調整することがまだ出来ます。
詳細については、[共有ホスティング環境](tutorial-shared-hosting.md) の節を参照してください。


### 推奨される Apache の構成 <span id="recommended-apache-configuration"></span>

下記の設定を Apache の `httpd.conf` ファイルまたはバーチャルホスト設定の中で使います。
`path/to/basic/web` の部分を `basic/web` の実際のパスに置き換えなければならないことに注意してください。

```
# ドキュメントルートを "basic/web" に設定
DocumentRoot "path/to/basic/web"

<Directory "path/to/basic/web">
    # 綺麗な URL をサポートするために mod_rewrite を使う
    RewriteEngine on
    # ディレクトリかファイルが存在する場合は、リクエストをそのまま通す
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    # そうでなければ、リクエストを index.php に送付する
    RewriteRule . index.php

    # ... 他の設定 ...
</Directory>
```


### 推奨される Nginx の構成 <span id="recommended-nginx-configuration"></span>

[Nginx](http://wiki.nginx.org/) を使うためには、PHP を [FPM SAPI](http://jp1.php.net/install.fpm) としてインストールしなければなりません。
下記の Nginx の設定を使うことができます。
`path/to/basic/web` の部分を `basic/web` の実際のパスに置き換え、`mysite.local` を実際のサーバのホスト名に置き換えてください。

```
server {
    charset utf-8;
    client_max_body_size 128M;

    listen 80; ## listen for ipv4
    #listen [::]:80 default_server ipv6only=on; ## listen for ipv6

    server_name mysite.local;
    root        /path/to/basic/web;
    index       index.php;

    access_log  /path/to/basic/log/access.log;
    error_log   /path/to/basic/log/error.log;

    location / {
        # 本当のファイルでないものは全て index.php にリダイレクト
        try_files $uri $uri/ /index.php?$args;
    }

    # 存在しない静的ファイルの呼び出しを Yii に処理させたくない場合はコメントを外す
    #location ~ \.(js|css|png|jpg|gif|swf|ico|pdf|mov|fla|zip|rar)$ {
    #    try_files $uri =404;
    #}
    #error_page 404 /404.html;

    location ~ \.php$ {
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $document_root/$fastcgi_script_name;
        fastcgi_pass   127.0.0.1:9000;
        #fastcgi_pass unix:/var/run/php5-fpm.sock;
        try_files $uri =404;
    }

    location ~ /\.(ht|svn|git) {
        deny all;
    }
}
```

この構成を使う場合は、多数の不要な `stat()` システムコールを避けるために、`php.ini` ファイルで `cgi.fix_pathinfo=0` を同時に設定しておくべきです。

また、HTTPS サーバを走らせている場合には、安全な接続であることを Yii が正しく検知できるように、`fastcgi_param HTTPS on;` を追加しなければならないことにも注意を払ってください。
