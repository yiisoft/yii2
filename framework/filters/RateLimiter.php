<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\filters;

use Yii;
use yii\base\ActionFilter;
use yii\web\Request;
use yii\web\Response;
use yii\web\TooManyRequestsHttpException;

/**
 * RateLimiter implements a rate limiting algorithm based on the [leaky bucket algorithm](http://en.wikipedia.org/wiki/Leaky_bucket).
 *
 * 您可以通过将 RateLimiter 作为行为附加到控制器或模块来使用，如下所示，
 *
 * ```php
 * public function behaviors()
 * {
 *     return [
 *         'rateLimiter' => [
 *             'class' => \yii\filters\RateLimiter::className(),
 *         ],
 *     ];
 * }
 * ```
 *
 * 当用户超过其速率限制时，RateLimiter 将引发 [[TooManyRequestsHttpException]] 异常。
 *
 * 请注意 RateLimiter 需要 [[user]] 实现[RateLimitInterface]。
 * 如果 [[user]] 未设置或未实现 [[RateLimitInterface]] 则不会执行任何操作。
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class RateLimiter extends ActionFilter
{
    /**
     * @var bool 是否在响应中包含速率限制 headers
     */
    public $enableRateLimitHeaders = true;
    /**
     * @var string 超过速率限制时显示的消息
     */
    public $errorMessage = 'Rate limit exceeded.';
    /**
     * @var RateLimitInterface 实现 RateLimitInterface 的用户对象。
     * 如果未设置，它将从 `Yii::$app->user->getIdentity(false)` 取值。
     */
    public $user;
    /**
     * @var Request 当前的请求。如果未设置，则将使用 `request` 应用程序组件。
     */
    public $request;
    /**
     * @var Response 要发送的响应。如果未设置，则将使用 `response` 应用程序组件。
     */
    public $response;


    /**
     * {@inheritdoc}
     */
    public function init()
    {
        if ($this->request === null) {
            $this->request = Yii::$app->getRequest();
        }
        if ($this->response === null) {
            $this->response = Yii::$app->getResponse();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function beforeAction($action)
    {
        if ($this->user === null && Yii::$app->getUser()) {
            $this->user = Yii::$app->getUser()->getIdentity(false);
        }

        if ($this->user instanceof RateLimitInterface) {
            Yii::debug('Check rate limit', __METHOD__);
            $this->checkRateLimit($this->user, $this->request, $this->response, $action);
        } elseif ($this->user) {
            Yii::info('Rate limit skipped: "user" does not implement RateLimitInterface.', __METHOD__);
        } else {
            Yii::info('Rate limit skipped: user not logged in.', __METHOD__);
        }

        return true;
    }

    /**
     * 检查是否超过了比率限额。
     * @param RateLimitInterface $user 当前用户
     * @param Request $request
     * @param Response $response
     * @param \yii\base\Action $action 将要执行的动作
     * @throws TooManyRequestsHttpException 如果超过比率限制
     */
    public function checkRateLimit($user, $request, $response, $action)
    {
        list($limit, $window) = $user->getRateLimit($request, $action);
        list($allowance, $timestamp) = $user->loadAllowance($request, $action);

        $current = time();

        $allowance += (int) (($current - $timestamp) * $limit / $window);
        if ($allowance > $limit) {
            $allowance = $limit;
        }

        if ($allowance < 1) {
            $user->saveAllowance($request, $action, 0, $current);
            $this->addRateLimitHeaders($response, $limit, 0, $window);
            throw new TooManyRequestsHttpException($this->errorMessage);
        }

        $user->saveAllowance($request, $action, $allowance - 1, $current);
        $this->addRateLimitHeaders($response, $limit, $allowance - 1, (int) (($limit - $allowance + 1) * $window / $limit));
    }

    /**
     * 将速率限制 headers 添加到响应中。
     * @param Response $response
     * @param int $limit 一段时间内允许的最大请求数
     * @param int $remaining 当前期间内允许的剩余请求数
     * @param int $reset 再次具有允许的最大请求数之前等待的秒数
     */
    public function addRateLimitHeaders($response, $limit, $remaining, $reset)
    {
        if ($this->enableRateLimitHeaders) {
            $response->getHeaders()
                ->set('X-Rate-Limit-Limit', $limit)
                ->set('X-Rate-Limit-Remaining', $remaining)
                ->set('X-Rate-Limit-Reset', $reset);
        }
    }
}
