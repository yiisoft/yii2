限流 (Rate Limiting)
==================

为防止滥用，你应该考虑对您的 API 限流。
例如，您可以限制每个用户 10 分钟内最多调用 API 100 次。
如果在规定的时间内接收了一个用户大量的请求，将返回响应状态代码 429 (这意味着过多的请求)。

要启用限流, [[yii\web\User::identityClass|user identity class]] 应该实现 [[yii\filters\RateLimitInterface]]。
这个接口需要实现以下三个方法：

* `getRateLimit()`：返回允许的请求的最大数目及时间，例如，`[100, 600]` 表示在 600 秒内最多 100 次的 API 调用。
* `loadAllowance()`：返回剩余的允许的请求和最后一次速率限制检查时
  相应的 UNIX 时间戳数。
* `saveAllowance()`：保存剩余的允许请求数和当前的 UNIX 时间戳。

你可以在 user 表中使用两列来记录容差和时间戳信息。
`loadAllowance()` 和 `saveAllowance()` 
可以通过实现对符合当前身份验证的用户的这两列值的读和保存。
为了提高性能，你也可以考虑使用缓存或 NoSQL 存储这些信息。

Implementation in the `User` model could look like the following:

```php
public function getRateLimit($request, $action)
{
    return [$this->rateLimit, 1]; // $rateLimit requests per second
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

一旦 identity 实现所需的接口，Yii 会自动使用 [[yii\filters\RateLimiter]]
为 [[yii\rest\Controller]] 配置一个行为过滤器来执行速率限制检查。如果速度超出限制，
该速率限制器将抛出一个 [[yii\web\TooManyRequestsHttpException]]。

你可以参考以下代码
在你的 REST 控制器类里配置速率限制：

```php
public function behaviors()
{
    $behaviors = parent::behaviors();
    $behaviors['rateLimiter']['enableRateLimitHeaders'] = false;
    return $behaviors;
}
```

当速率限制被激活，默认情况下每个响应将包含以下
HTTP 头发送目前的速率限制信息：

- `X-Rate-Limit-Limit`：同一个时间段所允许的请求的最大数目；
- `X-Rate-Limit-Remaining`：在当前时间段内剩余的请求的数量；
- `X-Rate-Limit-Reset`：为了得到最大请求数所等待的秒数。

你可以禁用这些头信息通过配置 [[yii\filters\RateLimiter::enableRateLimitHeaders]] 为 false，
就像在上面的代码示例所示。
