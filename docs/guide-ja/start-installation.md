Yii をインストールする
======================

Yii は二つの方法でインストールできます。[Composer](http://getcomposer.org/) を使う方法とアーカイブファイルをダウンロードする方法です。
前者がお薦めの方法です。と言うのは、一つのコマンドを走らせるだけで、新しい[エクステンション](structure-extensions.md) をインストールしたり、Yii をアップデートしたり出来るからです。

> Note|注意: Yii 1 とは異なり、Yii 2 の標準的なインストールを実行すると、フレームワークとアプリケーションスケルトンの両方がダウンロードされてインストールされます。


Composer によるインストール<a name="installing-via-composer"></a>
---------------------------

まだ Composer をインストールしていない場合は、[getcomposer.org](https://getcomposer.org/download/) の指示に従ってインストールすることが出来ます。
Linux や Mac OS X では、次のコマンドを実行してください:

    curl -s http://getcomposer.org/installer | php
    mv composer.phar /usr/local/bin/composer

Windows では、[Composer-Setup.exe](https://getcomposer.org/Composer-Setup.exe) をダウンロードして実行してください。

何か問題が生じたときや、Composer の使い方に関してもっと学習したいときは、[Composer Documentation](https://getcomposer.org/doc/) を参照してください。

以前に Composer をインストールしたことがある場合は、確実に最新のバージョンを使うようにしてください。
Composer は `composer self-update` コマンドを走らせてアップデートすることが出来ます。

Composer がインストールされたら、ウェブからアクセスできるフォルダーで下記のコマンドを実行することによって Yii をインストールすることが出来ます:

    composer global require "fxp/composer-asset-plugin:1.0.0-beta4"
    composer create-project --prefer-dist yiisoft/yii2-app-basic basic

最初のコマンドは [composer asset plugin](https://github.com/francoispluchino/composer-asset-plugin/) をインストールします。
これにより、Composer を通じて bower と npm のパッケージ依存関係を管理することが出来るようになります。
このコマンドは全体で一度だけ走らせれば十分です。
第二のコマンドは `basic` という名前のディレクトリに Yii をインストールします。
必要なら別のディレクトリ名を選ぶことも出来ます。

> Note|注意: インストール実行中に Composer が あなたの Github アカウントの認証情報を尋ねてくることがあるかも知れません。
> これは、Comoser が Github API の転送レート制限にひっかかったためです。
> Composer は全てのパッケージのための大量の情報を Github から読み出さなければならないので、こうなるのは普通のことです。
> Github にログインすると API の転送レート制限が緩和され、Composer が仕事を続けることが出来るようになります。
> 更なる詳細については、[Composer documentation](https://getcomposer.org/doc/articles/troubleshooting.md#api-rate-limit-and-oauth-tokens) を参照してください。

> Tip|ヒント: Yii の最新の開発バージョンをインストールしたい場合は、[stability option](https://getcomposer.org/doc/04-schema.md#minimum-stability) を追加した次のコマンドを代りに使うことが出来ます:
>
>     composer create-project --prefer-dist --stability=dev yiisoft/yii2-app-basic basic
>
> 開発バージョンは動いているあなたのコードを動かなくするかもしれませんので、実運用環境では使うべきでないことに注意してください。


アーカイブファイルからインストールする<a name="installing-from-archive-file"></a>
--------------------------------------

アーカイブファイルから Yii をインストールするには、三つのステップを踏みます:

1. [yiiframework.com](http://www.yiiframework.com/download/) からアーカイブファイルをダウンロードする。
2. ダウンロードしたファイルをウェブからアクセスできるフォルダーに展開する。
3. `config/web.php` ファイルを編集して、`cookieValidationKey` というコンフィギュレーション項目に秘密キーを入力する
   (Composer を使って Yii をインストールするときは、これは自動的に実行されます):

   ```php
   // !!! 下記に(もし空白なら)秘密キーを入力する - これはクッキー検証のために必要
   'cookieValidationKey' => '秘密キーをここに入力',
   ```


他のインストールオプション<a name="other-installation-options"></a>
--------------------------

上記のインストール方法の説明は Yii のインストールの仕方を示すものですが、それは同時にそのままで動作する基本的なウェブアプリケーションを作成するものでもあります。
これは小さなプロジェクトを開始するのに良い方法です。あるいは Yii の学習を始めたばかりの場合にもこれで良いでしょう。

しかし、他のインストールオプションも利用可能です:

* コアフレームワークだけをインストールし、アプリケーション全体を一から構築したい場合は、
  [アプリケーションを一から構築する](tutorial-start-from-scratch.md) で説明されている指示に従うことが出来ます。
* もっと洗練された、チーム開発環境により適したアプリケーションから開始したい場合は、
  [アドバンストアプリケーションテンプレート](tutorial-advanced-app.md) をインストールすることを考慮することが出来ます。


インストールを検証する<a name="verifying-installation"></a>
----------------------

インストール完了後、ブラウザで下記の URL によってインストールされた Yii アプリケーションにアクセスすることが出来ます:

```
http://localhost/basic/web/index.php
```

この URL は、あなたが Yii を ウェブサーバのドキュメントルートディレクトリの下の `basic` という名前のディレクトリにインストールしたこと、
そしてウェブサーバがローカルマシン (`localhost`) で走つていると想定しています。
インストールされた環境に合うように URL を変更してください。

![Yii のインストールが成功](images/start-app-installed.png)

ブラウザに上のような "おめでとう!" のページが表示されるはずです。
もし表示されなかったら、PHP のインストールが Yii の必要条件を満たしているかどうか、チェックしてください。
最低限の必要条件を満たしているかどうかは、次の方法のどちらかによってチェックすることが出来ます:

* ブラウザを使って `http://localhost/basic/requirements.php` という URL にアクセスする。
* 次のコマンドを実行する:

  ```
  cd basic
  php requirements.php
  ```

Yii の最低必要条件を満たすように PHP のインストールを構成しなければなりません。
最も重要なことは、PHP 5.4 以上でなければならないということです。
また、アプリケーションがデータベースを必要とする場合は、[PDO PHP 拡張](http://www.php.net/manual/ja/pdo.installation.php) および対応するデータベースドライバ (MySQL データベースのための `pdo_mysql` など) をインストールしなければなりません。


ウェブサーバを構成する<a name="configuring-web-servers"></a>
----------------------

> Info|情報: もし Yii の試運転をしているだけで、実運用のサーバに配置する意図がないのであれば、当面、この項は飛ばしても構いません。

上記の説明に従ってインストールされたアプリケーションは、[Apache HTTP サーバ](http://httpd.apache.org/) と [Nginx HTTP サーバ](http://nginx.org/) のどちらでも、また、Windows、Mac OS X、Linux のどれでも、PHP 5.4 以上を走らせている環境であれば、そのままの状態で動作するはずです。
Yii 2.0 は、また、facebook の [HHVM](http://hhvm.com/) とも互換性があります。
ただし HHVM がネイティブの PHP とは異なる振舞いをする特殊なケースもいくつかありますので、HHVM を使うときはいくらか余分に注意を払う必要があります。

実運用のサーバでは、`http://www.example.com/basic/web/index.php` の代りに `http://www.example.com/index.php` という URL でアプリケーションにアクセス出来るようにウェブサーバを設定したいと思うかもしれません。
そういう設定をするためには、ウェブサーバのドキュメントルートを `basic/web` フォルダに向けることが必要になります。
また、[URL の解析と生成](runtime-url-handling.md) の節で述べられているように、URL から `index.php` を隠したいと思うかも知れません。
この節では、これらの目的を達するために Apache または Nginx サーバをどのように設定すれば良いかを学びます。

> Info|情報: `basic/web` をドキュメントルートに設定することは、`basic/web` の兄弟ディレクトリに保管されたプライベートなアプリケーションコードや取り扱いに注意を要するデータファイルにエンドユーザがアクセスすることを防止することにもなります。
これらの他のフォルダに対するアクセスを拒否することはセキュリティ強化の一つです。

> Info|情報: ウェブサーバの設定を修正する権限のない共用ホスティング環境でアプリケーションが走る場合でも、
セキュリティ強化のためにアプリケーションの構造を調整することが出来ます。
更なる詳細については、[共有ホスティング環境](tutorial-shared-hosting.md) の節を参照してください。


### 推奨される Apache の構成<a name="recommended-apache-configuration"></a>

下記の設定を Apache の `httpd.conf` ファイルまたはバーチャルホスト設定の中で使います。
`path/to/basic/web` の部分を `basic/web` の実際のパスに置き換えなければならないことに注意してください。

```
# ドキュメントルートを "basic/web" に設定
DocumentRoot "path/to/basic/web"

<Directory "path/to/basic/web">
    # 綺麗な URL をサポートするために mod_rewrite を使う
    RewriteEngine on
    # ディレクトリかファイルが存在する場合は、リクエストを直接使う
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    # そうでなければ、リクエストを index.php に回送する
    RewriteRule . index.php

    # ... 他の設定 ...
</Directory>
```


### 推奨される Nginx の構成<a name="recommended-nginx-configuration"></a>

[Nginx](http://wiki.nginx.org/) を使うためには、PHP を [FPM SAPI](http://jp1.php.net/install.fpm) としてインストールしていなければなりません。
下記の設定を使うことができます (`path/to/basic/web` の部分を `basic/web` の実際のパスに置き換え、
`mysite.local` を実際のサーバのホスト名に置き換えてください)。

```
server {
    charset utf-8;
    client_max_body_size 128M;

    listen 80; ## listen for ipv4
    #listen [::]:80 default_server ipv6only=on; ## listen for ipv6

    server_name mysite.local;
    root        /path/to/basic/web;
    index       index.php;

    access_log  /path/to/basic/log/access.log main;
    error_log   /path/to/basic/log/error.log;

    location / {
        # 本当のファイルでないものは全て index.php にリダイレクト
        try_files $uri $uri/ /index.php?$args;
    }

    # 存在しないスタティックファイルの呼び出しを Yii が処理するのを
    # 防止したい場合は、コメントを外すこと
    #location ~ \.(js|css|png|jpg|gif|swf|ico|pdf|mov|fla|zip|rar)$ {
    #    try_files $uri =404;
    #}
    #error_page 404 /404.html;

    location ~ \.php$ {
        include fastcgi.conf;
        fastcgi_pass   127.0.0.1:9000;
        #fastcgi_pass unix:/var/run/php5-fpm.sock;
        try_files $uri =404;
    }

    location ~ /\.(ht|svn|git) {
        deny all;
    }
}
```

この構成を使う場合は、同時に `php.ini` ファイルで `cgi.fix_pathinfo=0` も設定して、
多数の不要な `stat()` の呼び出しを避けるべきです。

また、HTTPS サーバを走らせている場合には、安全な接続であることを Yii が正しく検知できるように、
`fastcgi_param HTTPS on;` を追加しなければならないことにも注意を払ってください。
