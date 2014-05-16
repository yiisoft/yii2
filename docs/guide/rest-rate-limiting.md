Rate Limiting
=============

To prevent abuse, you should consider adding rate limiting to your APIs. For example, you may limit the API usage
of each user to be at most 100 API calls within a period of 10 minutes. If too many requests are received from a user
within the period of the time, a response with status code 429 (meaning Too Many Requests) should be returned.

To enable rate limiting, the [[yii\web\User::identityClass|user identity class]] should implement [[yii\filters\RateLimitInterface]].
This interface requires implementation of the following three methods:

* `getRateLimit()`: returns the maximum number of allowed requests and the time period, e.g., `[100, 600]` means
  at most 100 API calls within 600 seconds.
* `loadAllowance()`: returns the number of remaining requests allowed and the corresponding UNIX timestamp
  when the rate limit is checked last time.
* `saveAllowance()`: saves the number of remaining requests allowed and the current UNIX timestamp.

You may use two columns in the user table to record the allowance and timestamp information.
And `loadAllowance()` and `saveAllowance()` can then be implementation by reading and saving the values
of the two columns corresponding to the current authenticated user. To improve performance, you may also
consider storing these information in cache or some NoSQL storage.

Once the identity class implements the required interface, Yii will automatically use [[yii\filters\RateLimiter]]
configured as an action filter for [[yii\rest\Controller]] to perform rate limiting check. The rate limiter
will thrown a [[yii\web\TooManyRequestsHttpException]] if rate limit is exceeded. You may configure the rate limiter
as follows in your REST controller classes,

```php
public function behaviors()
{
    $behaviors = parent::behaviors();
    $behaviors['rateLimiter']['enableRateLimitHeaders'] = false;
    return $behaviors;
}
```

When rate limiting is enabled, by default every response will be sent with the following HTTP headers containing
the current rate limiting information:

* `X-Rate-Limit-Limit`: The maximum number of requests allowed with a time period;
* `X-Rate-Limit-Remaining`: The number of remaining requests in the current time period;
* `X-Rate-Limit-Reset`: The number of seconds to wait in order to get the maximum number of allowed requests.

You may disable these headers by configuring [[yii\filters\RateLimiter::enableRateLimitHeaders]] to be false,
like shown in the above code example.
