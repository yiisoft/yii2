エラー処理
==========

RESTful API リクエストを処理していて、ユーザのリクエストにエラーがあったり、何か予期しないことがサーバ上で起ったりしたときには、何かがうまく行かなかったことをユーザに知らせるために単に例外を投げることも出来ます。
エラーの原因 (例えば、リクエストされたリソースが存在しない、など) を特定することが出来るなら、適切な HTTP ステータスコード (例えば、404 ステータスコードを表わす [[yii\web\NotFoundHttpException]]) とともに例外を投げることを検討すべきです。
そうすれば、Yii は対応する HTTP ステータスのコードとテキストをレスポンスとともに送信します。
Yii はまた、レスポンスボディにも、シリアライズされた表現形式の例外を含めます。
例えば、

```
HTTP/1.1 404 Not Found
Date: Sun, 02 Mar 2014 05:31:43 GMT
Server: Apache/2.2.26 (Unix) DAV/2 PHP/5.4.20 mod_ssl/2.2.26 OpenSSL/0.9.8y
Transfer-Encoding: chunked
Content-Type: application/json; charset=UTF-8

{
    "name": "Not Found Exception",
    "message": "The requested resource was not found.",
    "code": 0,
    "status": 404
}
```

次のリストは、Yii の REST フレームワークで使われる HTTP ステータスコードの要約です。

* `200`: OK。すべて期待されたとおりに動作しました。
* `201`: `POST` リクエストに対するレスポンスとしてリソースが成功裡に作成されました。
  `Location` ヘッダが、新しく作成されたリソースを指し示す URL を含んでいます。
* `204`: リクエストは成功裡に処理されましたが、レスポンスはボディコンテントを含んでいません (`DELTE` リクエストなどの場合)。
* `304`: リソースは修正されていません。キャッシュしたバージョンを使うことが可能です。
* `400`: 無効なリクエストです。これはユーザのさまざまな行為によって引き起こされます。例えば、リクエストのボディに無効な JSON データを入れたり、無効なアクションパラメータを指定したり、など。
* `401`: 認証が失敗しました。
* `403`: 認証されたユーザは指定された API エンドポイントにアクセスすることを許可されていません。
* `404`: リクエストされたリソースは存在しません。
* `405`: メソッドが許可されていません。どの HTTP メソッドが許可されているか、`Allow` ヘッダをチェックしてください。
* `415`: サポートされていないメディアタイプです。リクエストされたコンテントタイプまたはバージョン番号が無効です。
* `422`: データのバリデーションが失敗しました (例えば `POST` リクエストに対するレスポンスで)。
  レスポンスボディで詳細なエラーメッセージをチェックしてください。
* `429`: リクエストの数が多すぎます。レート制限のためにリクエストが拒絶されました。
* `500`: 内部的サーバエラー。これは内部的なプログラムエラーによって生じ得ます。


## エラーレスポンスをカスタマイズする <span id="customizing-error-response"></span>

場合によっては、デフォルトのエラーレスポンス形式をカスタマイズしたいことがあるでしょう。
例えば、さまざまな HTTP ステータスを使ってさまざまなエラーを示すという方法によるのではなく、次に示すように、HTTP ステータスとしては常に 200 を使い、実際の HTTP ステータスコードはレスポンスの JSON 構造の一部として包み込む、という方式です。

```
HTTP/1.1 200 OK
Date: Sun, 02 Mar 2014 05:31:43 GMT
Server: Apache/2.2.26 (Unix) DAV/2 PHP/5.4.20 mod_ssl/2.2.26 OpenSSL/0.9.8y
Transfer-Encoding: chunked
Content-Type: application/json; charset=UTF-8

{
    "success": false,
    "data": {
        "name": "Not Found Exception",
        "message": "The requested resource was not found.",
        "code": 0,
        "status": 404
    }
}
```

アプリケーションの構成情報で `response` コンポーネントの `beforeSend` イベントに応答することで、この目的を達することが出来ます。

```php
return [
    // ...
    'components' => [
        'response' => [
            'class' => 'yii\web\Response',
            'on beforeSend' => function ($event) {
                $response = $event->sender;
                if ($response->data !== null && Yii::$app->request->get('suppress_response_code')) {
                    $response->data = [
                        'success' => $response->isSuccessful,
                        'data' => $response->data,
                    ];
                    $response->statusCode = 200;
                }
            },
        ],
    ],
];
```

上記のコードは、`suppress_response_code` が `GET` のパラメータとして渡された場合に、レスポンスを (成功したものも、失敗したものも) 上記で説明したように再フォーマットします。
