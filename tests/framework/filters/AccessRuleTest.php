<?php

namespace yiiunit\framework\filters;

use Yii;
use yii\base\Action;
use yii\filters\AccessRule;
use yii\filters\HttpCache;
use yii\web\Controller;
use yii\web\Request;
use yii\web\User;
use yiiunit\framework\filters\stubs\UserIdentity;

/**
 * @group filters
 */
class AccessRuleTest extends \yiiunit\TestCase
{
    protected function setUp()
    {
        parent::setUp();

        $_SERVER['SCRIPT_FILENAME'] = "/index.php";
        $_SERVER['SCRIPT_NAME'] = "/index.php";

        $this->mockWebApplication();
    }

    protected function mockObjects()
    {
        $controller = new Controller('site', Yii::$app);
        $action = new Action('test', $controller);

        $user = new User([
            'identityClass' => UserIdentity::className(),
            'enableAutoLogin' => false,
        ]);

        return [$action, $user, Yii::$app->request];
    }

    public function testMatchAction()
    {
        /** @var $action Action */
        /** @var $user User */
        /** @var $request Request */
        list($action, $user, $request) = $this->mockObjects();


        $rule = new AccessRule([
            'allow' => true,
            'actions' => ['test', 'other'],
        ]);

        $action->id = 'test';
        $this->assertTrue($rule->allows($action, $user, $request));
        $action->id = 'other';
        $this->assertTrue($rule->allows($action, $user, $request));
        $action->id = 'foreign';
        $this->assertNull($rule->allows($action, $user, $request));

        $rule->allow = false;

        $action->id = 'test';
        $this->assertFalse($rule->allows($action, $user, $request));
        $action->id = 'other';
        $this->assertFalse($rule->allows($action, $user, $request));
        $action->id = 'foreign';
        $this->assertNull($rule->allows($action, $user, $request));
    }

    // TODO test match controller

    // TODO test match roles

    // TODO test match verb

    // TODO test match custom callback

    public function testMatchIP()
    {
        /** @var $action Action */
        /** @var $user User */
        /** @var $request Request */
        list($action, $user, $request) = $this->mockObjects();

        $rule = new AccessRule();

        // by default match all IPs
        $rule->allow = true;
        $this->assertTrue($rule->allows($action, $user, $request));
        $rule->allow = false;
        $this->assertFalse($rule->allows($action, $user, $request));

        // empty IPs = match all IPs
        $rule->ips = [];
        $rule->allow = true;
        $this->assertTrue($rule->allows($action, $user, $request));
        $rule->allow = false;
        $this->assertFalse($rule->allows($action, $user, $request));

        // match, one IP
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
        $rule->ips = ['127.0.0.1'];
        $rule->allow = true;
        $this->assertTrue($rule->allows($action, $user, $request));
        $rule->allow = false;
        $this->assertFalse($rule->allows($action, $user, $request));

        // no match, one IP
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
        $rule->ips = ['192.168.0.1'];
        $rule->allow = true;
        $this->assertNull($rule->allows($action, $user, $request));
        $rule->allow = false;
        $this->assertNull($rule->allows($action, $user, $request));

        // no partial match, one IP
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
        $rule->ips = ['127.0.0.10'];
        $rule->allow = true;
        $this->assertNull($rule->allows($action, $user, $request));
        $rule->allow = false;
        $this->assertNull($rule->allows($action, $user, $request));
        $_SERVER['REMOTE_ADDR'] = '127.0.0.10';
        $rule->ips = ['127.0.0.1'];
        $rule->allow = true;
        $this->assertNull($rule->allows($action, $user, $request));
        $rule->allow = false;
        $this->assertNull($rule->allows($action, $user, $request));

        // match, one IP IPv6
        $_SERVER['REMOTE_ADDR'] = '::1';
        $rule->ips = ['::1'];
        $rule->allow = true;
        $this->assertTrue($rule->allows($action, $user, $request));
        $rule->allow = false;
        $this->assertFalse($rule->allows($action, $user, $request));

        // no match, one IP IPv6
        $_SERVER['REMOTE_ADDR'] = '::1';
        $rule->ips = ['dead::beaf::1'];
        $rule->allow = true;
        $this->assertNull($rule->allows($action, $user, $request));
        $rule->allow = false;
        $this->assertNull($rule->allows($action, $user, $request));

        // no partial match, one IP IPv6
        $_SERVER['REMOTE_ADDR'] = '::1';
        $rule->ips = ['::123'];
        $rule->allow = true;
        $this->assertNull($rule->allows($action, $user, $request));
        $rule->allow = false;
        $this->assertNull($rule->allows($action, $user, $request));
        $_SERVER['REMOTE_ADDR'] = '::123';
        $rule->ips = ['::1'];
        $rule->allow = true;
        $this->assertNull($rule->allows($action, $user, $request));
        $rule->allow = false;
        $this->assertNull($rule->allows($action, $user, $request));
    }

    public function testMatchIPWildcard()
    {
        /** @var $action Action */
        /** @var $user User */
        /** @var $request Request */
        list($action, $user, $request) = $this->mockObjects();

        $rule = new AccessRule();

        // no match
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
        $rule->ips = ['192.168.*'];
        $rule->allow = true;
        $this->assertNull($rule->allows($action, $user, $request));
        $rule->allow = false;
        $this->assertNull($rule->allows($action, $user, $request));

        // match
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
        $rule->ips = ['127.0.*'];
        $rule->allow = true;
        $this->assertTrue($rule->allows($action, $user, $request));
        $rule->allow = false;
        $this->assertFalse($rule->allows($action, $user, $request));

        // match, IPv6
        $_SERVER['REMOTE_ADDR'] = '2a01:4f8:120:7202::2';
        $rule->ips = ['2a01:4f8:120:*'];
        $rule->allow = true;
        $this->assertTrue($rule->allows($action, $user, $request));
        $rule->allow = false;
        $this->assertFalse($rule->allows($action, $user, $request));

        // no match, IPv6
        $_SERVER['REMOTE_ADDR'] = '::1';
        $rule->ips = ['2a01:4f8:120:*'];
        $rule->allow = true;
        $this->assertNull($rule->allows($action, $user, $request));
        $rule->allow = false;
        $this->assertNull($rule->allows($action, $user, $request));
    }

}
