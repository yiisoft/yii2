<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\filters;

use Yii;
use yii\filters\RateLimiter;
use yii\web\Request;
use yii\web\Response;
use yii\web\User;
use yiiunit\framework\filters\stubs\ExposedLogger;
use yiiunit\framework\filters\stubs\RateLimit;
use yiiunit\framework\filters\stubs\UserIdentity;
use yiiunit\TestCase;

/**
 *  @group filters
 */
class RateLimiterTest extends TestCase
{
    protected function setUp()
    {
        parent::setUp();

        Yii::setLogger(new ExposedLogger());

        $this->mockWebApplication();
    }
    protected function tearDown()
    {
        parent::tearDown();
        Yii::setLogger(null);
    }

    public function testInitFilledRequest()
    {
        $rateLimiter = new RateLimiter(['request' => 'Request']);

        $this->assertEquals('Request', $rateLimiter->request);
    }

    public function testInitNotFilledRequest()
    {
        $rateLimiter = new RateLimiter();

        $this->assertInstanceOf(Request::className(), $rateLimiter->request);
    }

    public function testInitFilledResponse()
    {
        $rateLimiter = new RateLimiter(['response' => 'Response']);

        $this->assertEquals('Response', $rateLimiter->response);
    }

    public function testInitNotFilledResponse()
    {
        $rateLimiter = new RateLimiter();

        $this->assertInstanceOf(Response::className(), $rateLimiter->response);
    }

    public function testBeforeActionUserInstanceOfRateLimitInterface()
    {
        $rateLimiter = new RateLimiter();
        $rateLimit = new RateLimit();
        $rateLimit->setAllowance([1, time()])
            ->setRateLimit([1, 1]);
        $rateLimiter->user = $rateLimit;

        $result = $rateLimiter->beforeAction('test');

        $this->assertContains('Check rate limit', Yii::getLogger()->messages);
        $this->assertTrue($result);
    }

    public function testBeforeActionUserNotInstanceOfRateLimitInterface()
    {
        $rateLimiter = new RateLimiter(['user' => 'User']);

        $result = $rateLimiter->beforeAction('test');

        $this->assertContains('Rate limit skipped: "user" does not implement RateLimitInterface.', Yii::getLogger()->messages);
        $this->assertTrue($result);
    }

    public function testBeforeActionEmptyUser()
    {
        $user = new User(['identityClass' => RateLimit::className()]);
        Yii::$app->set('user', $user);
        $rateLimiter = new RateLimiter();

        $result = $rateLimiter->beforeAction('test');

        $this->assertContains('Rate limit skipped: user not logged in.', Yii::getLogger()->messages);
        $this->assertTrue($result);
    }

    public function testCheckRateLimitTooManyRequests()
    {
        /* @var $rateLimit UserIdentity|\Prophecy\ObjectProphecy */
        $rateLimit = new RateLimit();
        $rateLimit
            ->setRateLimit([1, 1])
            ->setAllowance([1, time() + 2]);
        $rateLimiter = new RateLimiter();

        $this->expectException('yii\web\TooManyRequestsHttpException');
        $rateLimiter->checkRateLimit($rateLimit, Yii::$app->request, Yii::$app->response, 'testAction');
    }

    public function testCheckRateaddRateLimitHeaders()
    {
        /* @var $user UserIdentity|\Prophecy\ObjectProphecy */
        $rateLimit = new RateLimit();
        $rateLimit
            ->setRateLimit([2, 10])
            ->setAllowance([2, time()]);

        $rateLimiter = new RateLimiter();
        $response = Yii::$app->response;
        $rateLimiter->checkRateLimit($rateLimit, Yii::$app->request, $response, 'testAction');
        $headers = $response->getHeaders();
        $this->assertEquals(2, $headers->get('X-Rate-Limit-Limit'));
        $this->assertEquals(1, $headers->get('X-Rate-Limit-Remaining'));
        $this->assertEquals(5, $headers->get('X-Rate-Limit-Reset'));
    }

    public function testAddRateLimitHeadersDisabledRateLimitHeaders()
    {
        $rateLimiter = new RateLimiter();
        $rateLimiter->enableRateLimitHeaders = false;
        $response = Yii::$app->response;

        $rateLimiter->addRateLimitHeaders($response, 1, 0, 0);
        $this->assertCount(0, $response->getHeaders());
    }

    public function testAddRateLimitHeadersEnabledRateLimitHeaders()
    {
        $rateLimiter = new RateLimiter();
        $rateLimiter->enableRateLimitHeaders = true;
        $response = Yii::$app->response;

        $rateLimiter->addRateLimitHeaders($response, 1, 0, 0);
        $this->assertCount(3, $response->getHeaders());
    }

    /**
     * @see https://github.com/yiisoft/yii2/issues/18236
     */
    public function testUserWithClosureFunction()
    {
        $rateLimiter = new RateLimiter();
        $rateLimiter->user = function($action) {
            return new User(['identityClass' => RateLimit::className()]);
        };
        $rateLimiter->beforeAction('test');

        // testing the evaluation of user closure, which in this case returns not the expect object and therefore
        // the log message "does not implement RateLimitInterface" is expected.
        $this->assertInstanceOf(User::className(), $rateLimiter->user);
        $this->assertContains('Rate limit skipped: "user" does not implement RateLimitInterface.', Yii::getLogger()->messages);
    }
}
