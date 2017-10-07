<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yiiunit\framework\filters;

use Prophecy\Argument;
use Yii;
use yii\filters\RateLimiter;
use yii\log\Logger;
use yii\web\Request;
use yii\web\Response;
use yii\web\User;
use yiiunit\framework\filters\stubs\RateLimit;
use yiiunit\TestCase;

/**
 *  @group filters
 */
class RateLimiterTest extends TestCase
{
    protected function setUp()
    {
        parent::setUp();

        /* @var $logger Logger|\Prophecy\ObjectProphecy */
        $logger = $this->prophesize(Logger::className());
        $logger
            ->log(Argument::any(), Argument::any(), Argument::any())
            ->will(function ($parameters, $logger) {
                $logger->messages = $parameters;
            });

        Yii::setLogger($logger->reveal());

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

        $this->setExpectedException('yii\web\TooManyRequestsHttpException');
        $rateLimiter->checkRateLimit($rateLimit, Yii::$app->request, Yii::$app->response, 'testAction');
    }

    public function testCheckRateaddRateLimitHeaders()
    {
        /* @var $user UserIdentity|\Prophecy\ObjectProphecy */
        $rateLimit = new RateLimit();
        $rateLimit
            ->setRateLimit([1, 1])
            ->setAllowance([1, time()]);
        $rateLimiter = $this->getMockBuilder(RateLimiter::className())
            ->setMethods(['addRateLimitHeaders'])
            ->getMock();
        $rateLimiter->expects(self::at(0))
            ->method('addRateLimitHeaders')
            ->willReturn(null);

        $rateLimiter->checkRateLimit($rateLimit, Yii::$app->request, Yii::$app->response, 'testAction');
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
}
