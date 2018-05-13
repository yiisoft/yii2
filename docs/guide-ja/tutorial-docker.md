Yii と Docker
=============

開発および配備の際に Yii アプリケーションを Docker コンテナとして実行することが出来ます。コンテナは隔絶された軽量の仮想マシンのようなもので、そのサービスをホストのポートにマップします。例えば、コンテナ内の 80 番ポートにあるウェブ・サーバが(ローカル)ホストの 8888 番で利用できます。

コンテナによって、開発用コンピュータと実運用サーバでソフトウェアのバージョンを全く同一にすること、迅速な配備、開発時におけるマルチ・サーバ・アーキテクチャのシミュレーションなど、数多くの問題を解決することが出来ます。

Docker コンテナの詳細については、[docker.com](https://www.docker.com/what-docker) を参照して下さい。

## 必要なもの

- `docker`
- `docker-compose`

[ダウンロード・ページ](https://www.docker.com/community-edition) で Docker のツールを取得して下さい。

## インストール

インストール後、`docker ps` を実行すると、以下と同様の出力が得られるはずです。

```
CONTAINER ID   IMAGE   COMMAND   CREATED   STATUS   PORTS
```

これは Docker デーモンが起動して走っていることを意味します。

これに加えて `docker-compose version` を実行すると、出力は次のようになるはずです。

```
docker-compose version 1.20.0, build unknown
docker-py version: 3.1.3
CPython version: 3.6.4
OpenSSL version: OpenSSL 1.1.0g  2 Nov 2017
```

Compose を使って、データベースやキャッシュなど、アプリケーションに必要な全てのサービスを設定して管理することが出来ます。

## リソース

- Yii のための PHP ベースのイメージが [yii2-docker](https://github.com/yiisoft/yii2-docker) にあります
- [yii2-app-basic](https://github.com/yiisoft/yii2-app-basic#install-with-docker) のための Docker サポート
- [yii2-app-advanced](https://github.com/yiisoft/yii2-app-advanced/pull/347) のための Docker サポートは開発中です

## 使用方法

Docker の基本的なコマンド:

    docker-compose up -d
    
スタックにある全てのサービスをバックグラウンドで実行

    docker-compose ps
    
実行中のサービスをリストアップ

    docker-compose logs -f
    
全てのサービスのログを連続的に表示

    docker-compose stop
    
スタックにある全てのサービスを穏やかに停止

    docker-compose kill
    
スタックにある全てのサービスを即座に停止

    docker-compose down -v
    
全てのサービスを停止して削除、**ホスト・ボリュームを使っていない場合のデータ損失に注意**

コンテナの中でのコマンドの実行:

    docker-compose run --rm php composer install
    
新しいコンテナの中で composer install を実行

    docker-compose exec php bash
    
*実行中の* `php` サービスの中で bash を実行


## 高度なトピック

### Yii フレームワークのテスト

[ここ](https://github.com/yiisoft/yii2/blob/master/tests/README.md#dockerized-testing) で説明されているように、Yii 自体に対する Docker を使ったフレームワーク・テストを実行することが出来ます。

### データベース管理ツール

MySQL を (`mysql`) として実行するときは、以下のようにして phpMyAdmin コンテナをスタックに追加することが出来ます。

```
    phpmyadmin:
        image: phpmyadmin/phpmyadmin
        ports:
            - '8888:80'
        environment:
            - PMA_ARBITRARY=1
            - PMA_HOST=mysql
        depends_on:
            - mysql
```
