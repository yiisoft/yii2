<?php

namespace yiiunit\framework\filters;

use Yii;
use yiiunit\TestCase;
use Prophecy\Argument;
use yiiunit\framework\filters\stubs\UserIdentity;
use yii\web\User;
use yii\web\Request;
use yii\web\Response;
use yii\log\Logger;
use yii\filters\RateLimiter;

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

        Yii::$container->setSingleton(Logger::className(), $logger->reveal());

        $this->mockWebApplication();
    }
    protected function tearDown()
    {
        parent::tearDown();
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
        /* @var $rateLimiter RateLimiter|PHPUnit_Framework_MockObject_MockObject */
        $rateLimiter = $this->getMockBuilder(RateLimiter::className())
            ->setMethods(['checkRateLimit'])
            ->getMock();
        $rateLimiter->expects(self::at(0))
            ->method('checkRateLimit')
            ->willReturn(true);
        $rateLimiter->user = new UserIdentity;

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
        $user = new User(['identityClass' => UserIdentity::className()]);
        Yii::$container->setSingleton(User::className(), $user);
        $rateLimiter = new RateLimiter();

        $result = $rateLimiter->beforeAction('test');

        $this->assertContains('Rate limit skipped: user not logged in.', Yii::getLogger()->messages);
        $this->assertTrue($result);
    }

    public function testCheckRateLimitTooManyRequests()
    {
        /* @var $user UserIdentity|\Prophecy\ObjectProphecy */
        $user = $this->prophesize(UserIdentity::className());
        $user->getRateLimit(Argument::any(), Argument::any())
            ->willReturn([1, 1]);
        $user->loadAllowance(Argument::any(), Argument::any())
            ->willReturn([1, time() + 2]);
        $user->saveAllowance(Argument::any(), Argument::any(), Argument::any(), Argument::any())
            ->willReturn(null);
        $rateLimiter = new RateLimiter();

        $this->setExpectedException('yii\web\TooManyRequestsHttpException');
        $rateLimiter->checkRateLimit($user->reveal(), Yii::$app->request, Yii::$app->response, 'testAction');
    }

    public function testCheckRateaddRateLimitHeaders()
    {
        /* @var $user UserIdentity|\Prophecy\ObjectProphecy */
        $user = $this->prophesize(UserIdentity::className());
        $user->getRateLimit(Argument::any(), Argument::any())
            ->willReturn([1, 1]);
        $user->loadAllowance(Argument::any(), Argument::any())
            ->willReturn([1, time()]);
        $user->saveAllowance(Argument::any(), Argument::any(), Argument::any(), Argument::any())
            ->willReturn(null);
        $rateLimiter = $this->getMockBuilder(RateLimiter::className())
            ->setMethods(['addRateLimitHeaders'])
            ->getMock();
        $rateLimiter->expects(self::at(0))
            ->method('addRateLimitHeaders')
            ->willReturn(null);

        $rateLimiter->checkRateLimit($user->reveal(), Yii::$app->request, Yii::$app->response, 'testAction');
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
