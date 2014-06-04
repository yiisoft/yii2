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
 * You may use RateLimiter by attaching it as a behavior to a controller or module, like the following,
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
 * When the user has exceeded his rate limit, RateLimiter will throw a [[TooManyRequestsHttpException]] exception.
 *
 * Note that RateLimiter requires [[user]] to implement the [[RateLimitInterface]]. RateLimiter will
 * do nothing if [[user]] is not set or does not implement [[RateLimitInterface]].
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class RateLimiter extends ActionFilter
{
    /**
     * @var boolean whether to include rate limit headers in the response
     */
    public $enableRateLimitHeaders = true;
    /**
     * @var string the message to be displayed when rate limit exceeds
     */
    public $errorMessage = 'Rate limit exceeded.';
    /**
     * @var RateLimitInterface the user object that implements the RateLimitInterface.
     * If not set, it will take the value of `Yii::$app->user->getIdentity(false)`.
     */
    public $user;
    /**
     * @var Request the current request. If not set, the `request` application component will be used.
     */
    public $request;
    /**
     * @var Response the response to be sent. If not set, the `response` application component will be used.
     */
    public $response;


    /**
     * @inheritdoc
     */
    public function beforeAction($action)
    {
        $user = $this->user ? : Yii::$app->getUser()->getIdentity(false);
        if ($user instanceof RateLimitInterface) {
            Yii::trace('Check rate limit', __METHOD__);
            $this->checkRateLimit(
                $user,
                $this->request ? : Yii::$app->getRequest(),
                $this->response ? : Yii::$app->getResponse(),
                $action
            );
        } elseif ($user) {
            Yii::info('Rate limit skipped: "user" does not implement RateLimitInterface.');
        } else {
            Yii::info('Rate limit skipped: user not logged in.');
        }
        return true;
    }

    /**
     * Checks whether the rate limit exceeds.
     * @param RateLimitInterface $user the current user
     * @param Request $request
     * @param Response $response
     * @param \yii\base\Action $action the action to be executed
     * @throws TooManyRequestsHttpException if rate limit exceeds
     */
    public function checkRateLimit($user, $request, $response, $action)
    {
        $current = time();

        list ($limit, $window) = $user->getRateLimit($request, $action);
        list ($allowance, $timestamp) = $user->loadAllowance($request, $action);

        $allowance += (int) (($current - $timestamp) * $limit / $window);
        if ($allowance > $limit) {
            $allowance = $limit;
        }

        if ($allowance < 1) {
            $user->saveAllowance($request, $action, 0, $current);
            $this->addRateLimitHeaders($response, $limit, 0, $window);
            throw new TooManyRequestsHttpException($this->errorMessage);
        } else {
            $user->saveAllowance($request, $action, $allowance - 1, $current);
            $this->addRateLimitHeaders($response, $limit, $allowance - 1, (int) (($limit - $allowance) * $window / $limit));
        }
    }

    /**
     * Adds the rate limit headers to the response
     * @param Response $response
     * @param integer $limit the maximum number of allowed requests during a period
     * @param integer $remaining the remaining number of allowed requests within the current period
     * @param integer $reset the number of seconds to wait before having maximum number of allowed requests again
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
