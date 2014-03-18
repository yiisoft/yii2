<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\rest;

use yii\base\Component;
use yii\base\Action;
use yii\web\Request;
use yii\web\Response;
use yii\web\TooManyRequestsHttpException;

/**
 * RateLimiter implements a rate limiting algorithm based on the [leaky bucket algorithm](http://en.wikipedia.org/wiki/Leaky_bucket).
 *
 * You may call [[check()]] to enforce rate limiting.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class RateLimiter extends Component
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
     * Checks whether the rate limit exceeds.
     * @param  RateLimitInterface           $user     the current user
     * @param  Request                      $request
     * @param  Response                     $response
     * @param  Action                       $action   the action to be executed
     * @throws TooManyRequestsHttpException if rate limit exceeds
     */
    public function check($user, $request, $response, $action)
    {
        $current = time();
        $params = [
            'request' => $request,
            'action' => $action,
        ];

        list ($limit, $window) = $user->getRateLimit($params);
        list ($allowance, $timestamp) = $user->loadAllowance($params);

        $allowance += (int) (($current - $timestamp) * $limit / $window);
        if ($allowance > $limit) {
            $allowance = $limit;
        }

        if ($allowance < 1) {
            $user->saveAllowance(0, $current, $params);
            $this->addRateLimitHeaders($response, $limit, 0, $window);
            throw new TooManyRequestsHttpException($this->errorMessage);
        } else {
            $user->saveAllowance($allowance - 1, $current, $params);
            $this->addRateLimitHeaders($response, $limit, 0, (int) (($limit - $allowance) * $window / $limit));
        }
    }

    /**
     * Adds the rate limit headers to the response
     * @param Response $response
     * @param integer  $limit     the maximum number of allowed requests during a period
     * @param integer  $remaining the remaining number of allowed requests within the current period
     * @param integer  $reset     the number of seconds to wait before having maximum number of allowed requests again
     */
    protected function addRateLimitHeaders($response, $limit, $remaining, $reset)
    {
        if ($this->enableRateLimitHeaders) {
            $response->getHeaders()
                ->set('X-Rate-Limit-Limit', $limit)
                ->set('X-Rate-Limit-Remaining', $remaining)
                ->set('X-Rate-Limit-Reset', $reset);
        }
    }
}
