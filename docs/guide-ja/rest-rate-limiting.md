レート制限
==========

悪用を防止するために、あなたの API に *レート制限* を加えることを検討すべきです。
例えば、各ユーザの API 使用を 10 分間で最大 100 回までの API 呼び出しに制限したいとしましょう。
ユーザから上記の期間内に多すぎるリクエストを受け取った場合は、ステータスコード 429 (「リクエストが多すぎる」の意味) を持つレスポンスを返さなければなりません。

レート制限を可能にするためには、[[yii\web\User::identityClass|ユーザアイデンティティクラス]] で [[yii\filters\RateLimitInterface]] を実装しなければなりません。
このインタフェイスは次の三つのメソッドを実装することを要求します。

* `getRateLimit()`: 許可されているリクエストの最大数と期間を返します
  (例えば、`[100, 600]` は 600 秒間に最大 100 回の API 呼び出しが出来ることを意味します)。
* `loadAllowance()`: 許可されているリクエストの残り数と、レート制限が最後にチェックされたときの対応する UNIX タイムスタンプを返します。
* `saveAllowance()`: 許可されているリクエストの残り数と現在の UNIX タイムスタンプの両方を保存します。

ユーザテーブルに二つのカラムを追加して、許容されているリクエスト数とタイムスタンプの情報を記録するのが良いでしょう。
それらを定義すれば、`loadAllowance()` と `saveAllowance()` は、認証された現在のユーザに対応する二つのカラムの値を読み書きするものとして実装することが出来ます。
パフォーマンスを向上させるために、これらの情報をキャッシュや NoSQL ストレージに保存することを検討しても構いません。

`User` モデルにおける実装は次のようなものになります。

```php
public function getRateLimit($request, $action)
{
    return [$this->rateLimit, 1]; // 1秒間に $rateLimit 回のリクエスト
}

public function loadAllowance($request, $action)
{
    return [$this->allowance, $this->allowance_updated_at];
}

public function saveAllowance($request, $action, $allowance, $timestamp)
{
    $this->allowance = $allowance;
    $this->allowance_updated_at = $timestamp;
    $this->save();
}
```

アイデンティティのクラスに必要なインタフェイスを実装すると、Yii は [[yii\rest\Controller]] のアクションフィルタとして構成された [[yii\filters\RateLimiter]] を使って、自動的にレート制限のチェックを行うようになります。
レート制限を超えると、レートリミッタが [[yii\web\TooManyRequestsHttpException]] を投げます。

レートリミッタは、REST コントローラクラスの中で、次のようにして構成することが出来ます。

```php
public function behaviors()
{
    $behaviors = parent::behaviors();
    $behaviors['rateLimiter']['enableRateLimitHeaders'] = false;
    return $behaviors;
}
```

レート制限が有効にされると、デフォルトでは、送信される全てのレスポンスに、現在のレート制限の情報を含む次の HTTP ヘッダが付加されます。

* `X-Rate-Limit-Limit` - 一定期間内に許可されるリクエストの最大数
* `X-Rate-Limit-Remaining` - 現在の期間において残っている許可されているリクエスト数
* `X-Rate-Limit-Reset` - 許可されているリクエストの最大数にリセットされるまで待たなければならない秒数

これらのヘッダは、上記のコード例で示されているように、[[yii\filters\RateLimiter::enableRateLimitHeaders]] を `false` に設定することで無効にすることが出来ます。
