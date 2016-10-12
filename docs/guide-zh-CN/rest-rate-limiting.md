速率限制
=============

为防止滥用，你应该考虑增加速率限制到您的 API。
例如，您可以限制每个用户的 API 的使用是在 10 分钟内最多 100 次的 API 调用。
如果一个用户在规定的时间内太多的请求被接收，将返回响应状态代码 429 (这意味着过多的请求)。

要启用速率限制, [[yii\web\User::identityClass|user identity class]] 应该实现 [[yii\filters\RateLimitInterface]].
这个接口需要实现以下三个方法：

* `getRateLimit()`: 返回允许的请求的最大数目及时间，例如，`[100, 600]` 表示在 600 秒内最多 100 次的 API 调用。
* `loadAllowance()`: 返回剩余的允许的请求和最后一次速率限制检查时相应的 UNIX 时间戳数。
* `saveAllowance()`: 保存剩余的允许请求数和当前的 UNIX 时间戳。

你可以在 user 表中使用两列来记录容差和时间戳信息。
`loadAllowance()` 和 `saveAllowance()` 可以通过实现对符合当前身份验证的用户的这两列值的读和保存。为了提高性能，你也可以
考虑使用缓存或 NoSQL 存储这些信息。

一旦 identity 实现所需的接口， Yii 会自动使用 [[yii\filters\RateLimiter]]
为 [[yii\rest\Controller]] 配置一个行为过滤器来执行速率限制检查。如果速度超出限制，该速率限制器将抛出一个 [[yii\web\TooManyRequestsHttpException]]。你可以参考以下代码在你的 REST 控制器类里配置速率限制：

```php
public function behaviors()
{
    $behaviors = parent::behaviors();
    $behaviors['rateLimiter']['enableRateLimitHeaders'] = false;
    return $behaviors;
}
```

当速率限制被激活，默认情况下每个响应将包含以下 HTTP 头发送目前的速率限制信息：

* `X-Rate-Limit-Limit`: 同一个时间段所允许的请求的最大数目;
* `X-Rate-Limit-Remaining`: 在当前时间段内剩余的请求的数量;
* `X-Rate-Limit-Reset`: 为了得到最大请求数所等待的秒数。

你可以禁用这些头信息通过配置 [[yii\filters\RateLimiter::enableRateLimitHeaders]] 为 false,
就像在上面的代码示例所示。
