<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\web;

/**
 * Mock for the time() function for web classes
 * @return int
 */
function time()
{
    return \yiiunit\framework\web\UserTest::$time ?: \time();
}

namespace yiiunit\framework\web;

use Yii;
use yii\base\Component;
use yii\base\NotSupportedException;
use yii\rbac\PhpManager;
use yii\web\Cookie;
use yii\web\CookieCollection;
use yii\web\ForbiddenHttpException;
use yii\web\IdentityInterface;
use yiiunit\TestCase;

/**
 * @group web
 */
class UserTest extends TestCase
{
    /**
     * @var int virtual time to be returned by mocked time() function.
     * Null means normal time() behavior.
     */
    public static $time;

    protected function tearDown()
    {
        Yii::$app->session->removeAll();
        static::$time = null;
        parent::tearDown();
    }

    public function testLoginExpires()
    {
        if (getenv('TRAVIS') == 'true') {
            $this->markTestSkipped('Can not reliably test this on travis-ci.');
        }

        $appConfig = [
            'components' => [
                'user' => [
                    'identityClass' => UserIdentity::className(),
                    'authTimeout' => 10,
                ],
                'authManager' => [
                    'class' => PhpManager::className(),
                    'itemFile' => '@runtime/user_test_rbac_items.php',
                     'assignmentFile' => '@runtime/user_test_rbac_assignments.php',
                     'ruleFile' => '@runtime/user_test_rbac_rules.php',
                ],
            ],
        ];
        $this->mockWebApplication($appConfig);

        $am = Yii::$app->authManager;
        $am->removeAll();
        $am->add($role = $am->createPermission('rUser'));
        $am->add($perm = $am->createPermission('doSomething'));
        $am->addChild($role, $perm);
        $am->assign($role, 'user1');

        Yii::$app->session->removeAll();
        static::$time = \time();
        Yii::$app->user->login(UserIdentity::findIdentity('user1'));

//        print_r(Yii::$app->session);
//        print_r($_SESSION);

        $this->mockWebApplication($appConfig);
        $this->assertFalse(Yii::$app->user->isGuest);
        $this->assertTrue(Yii::$app->user->can('doSomething'));

        static::$time += 5;
        $this->mockWebApplication($appConfig);
        $this->assertFalse(Yii::$app->user->isGuest);
        $this->assertTrue(Yii::$app->user->can('doSomething'));

        static::$time += 11;
        $this->mockWebApplication($appConfig);
        $this->assertTrue(Yii::$app->user->isGuest);
        $this->assertFalse(Yii::$app->user->can('doSomething'));
    }

    public function testCookieCleanup()
    {
        global $cookiesMock;

        $cookiesMock = new CookieCollection();

        $appConfig = [
            'components' => [
                'user' => [
                    'identityClass' => UserIdentity::className(),
                    'enableAutoLogin' => true,
                ],
                'response' => [
                    'class' => MockResponse::className(),
                ],
                'request' => [
                    'class' => MockRequest::className(),
                ],
            ],
        ];

        $this->mockWebApplication($appConfig);
        Yii::$app->session->removeAll();

        $cookie = new Cookie(Yii::$app->user->identityCookie);
        $cookie->value = 'junk';
        $cookiesMock->add($cookie);
        Yii::$app->user->getIdentity();
        $this->assertEquals(strlen($cookiesMock->getValue(Yii::$app->user->identityCookie['name'])), 0);

        Yii::$app->user->login(UserIdentity::findIdentity('user1'), 3600);
        $this->assertFalse(Yii::$app->user->isGuest);
        $this->assertSame(Yii::$app->user->id, 'user1');
        $this->assertNotEquals(strlen($cookiesMock->getValue(Yii::$app->user->identityCookie['name'])), 0);

        Yii::$app->user->login(UserIdentity::findIdentity('user2'), 0);
        $this->assertFalse(Yii::$app->user->isGuest);
        $this->assertSame(Yii::$app->user->id, 'user2');
        $this->assertEquals(strlen($cookiesMock->getValue(Yii::$app->user->identityCookie['name'])), 0);
    }

    /**
     * Resets request, response and $_SERVER.
     */
    protected function reset()
    {
        static $server;

        if (!isset($server)) {
            $server = $_SERVER;
        }

        $_SERVER = $server;
        Yii::$app->set('response', ['class' => 'yii\web\Response']);
        Yii::$app->set('request', [
            'class' => 'yii\web\Request',
            'scriptFile' => __DIR__ . '/index.php',
            'scriptUrl' => '/index.php',
            'url' => '',
        ]);
        Yii::$app->user->setReturnUrl(null);
    }
    public function testLoginRequired()
    {
        $appConfig = [
            'components' => [
                'user' => [
                    'identityClass' => UserIdentity::className(),
                ],
                'authManager' => [
                    'class' => PhpManager::className(),
                    'itemFile' => '@runtime/user_test_rbac_items.php',
                    'assignmentFile' => '@runtime/user_test_rbac_assignments.php',
                    'ruleFile' => '@runtime/user_test_rbac_rules.php',
                ],
            ],
        ];
        $this->mockWebApplication($appConfig);


        $user = Yii::$app->user;

        $this->reset();
        Yii::$app->request->setUrl('normal');
        $user->loginRequired();
        $this->assertEquals('normal', $user->getReturnUrl());
        $this->assertTrue(Yii::$app->response->getIsRedirection());


        $this->reset();
        Yii::$app->request->setUrl('ajax');
        $_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';

        $user->loginRequired();
        $this->assertEquals(Yii::$app->getHomeUrl(), $user->getReturnUrl());
        // AJAX requests don't update returnUrl but they do cause redirection.
        $this->assertTrue(Yii::$app->response->getIsRedirection());

        $user->loginRequired(false);
        $this->assertEquals('ajax', $user->getReturnUrl());
        $this->assertTrue(Yii::$app->response->getIsRedirection());

        $this->reset();
        Yii::$app->request->setUrl('json-only');
        $_SERVER['HTTP_ACCEPT'] = 'Accept:  text/json, q=0.1';
        $user->loginRequired(true, false);
        $this->assertEquals('json-only', $user->getReturnUrl());
        $this->assertTrue(Yii::$app->response->getIsRedirection());

        $this->reset();
        Yii::$app->request->setUrl('json-only');
        $_SERVER['HTTP_ACCEPT'] = 'text/json,q=0.1';
        $user->loginRequired(true, false);
        $this->assertEquals('json-only', $user->getReturnUrl());
        $this->assertTrue(Yii::$app->response->getIsRedirection());

        $this->reset();
        Yii::$app->request->setUrl('accept-all');
        $_SERVER['HTTP_ACCEPT'] = '*/*;q=0.1';
        $user->loginRequired();
        $this->assertEquals('accept-all', $user->getReturnUrl());
        $this->assertTrue(Yii::$app->response->getIsRedirection());

        $this->reset();
        Yii::$app->request->setUrl('json-and-accept-all');
        $_SERVER['HTTP_ACCEPT'] = 'text/json, */*; q=0.1';
        try {
            $user->loginRequired();
        } catch (ForbiddenHttpException $e) {
        }
        $this->assertFalse(Yii::$app->response->getIsRedirection());

        $this->reset();
        Yii::$app->request->setUrl('accept-html-json');
        $_SERVER['HTTP_ACCEPT'] = 'text/json; q=1, text/html; q=0.1';
        $user->loginRequired();
        $this->assertEquals('accept-html-json', $user->getReturnUrl());
        $this->assertTrue(Yii::$app->response->getIsRedirection());

        $this->reset();
        Yii::$app->request->setUrl('accept-html-json');
        $_SERVER['HTTP_ACCEPT'] = 'text/json;q=1,application/xhtml+xml;q=0.1';
        $user->loginRequired();
        $this->assertEquals('accept-html-json', $user->getReturnUrl());
        $this->assertTrue(Yii::$app->response->getIsRedirection());

        $this->reset();
        $_SERVER['REQUEST_METHOD'] = 'POST';
        Yii::$app->request->setUrl('dont-set-return-url-on-post-request');
        Yii::$app->getSession()->set($user->returnUrlParam, null);
        $user->loginRequired();
        $this->assertNull(Yii::$app->getSession()->get($user->returnUrlParam));

        $this->reset();
        $_SERVER['REQUEST_METHOD'] = 'GET';
        Yii::$app->request->setUrl('set-return-url-on-get-request');
        Yii::$app->getSession()->set($user->returnUrlParam, null);
        $user->loginRequired();
        $this->assertEquals('set-return-url-on-get-request', Yii::$app->getSession()->get($user->returnUrlParam));

        // Confirm that returnUrl is not set.
        $this->reset();
        Yii::$app->request->setUrl('json-only');
        $_SERVER['HTTP_ACCEPT'] = 'text/json;q=0.1';
        try {
            $user->loginRequired();
        } catch (ForbiddenHttpException $e) {
        }
        $this->assertNotEquals('json-only', $user->getReturnUrl());

        $this->reset();
        $_SERVER['HTTP_ACCEPT'] = 'text/json;q=0.1';
        $this->expectException('yii\\web\\ForbiddenHttpException');
        $user->loginRequired();
    }

    public function testLoginRequiredException1()
    {
        $appConfig = [
            'components' => [
                'user' => [
                    'identityClass' => UserIdentity::className(),
                ],
                'authManager' => [
                    'class' => PhpManager::className(),
                    'itemFile' => '@runtime/user_test_rbac_items.php',
                    'assignmentFile' => '@runtime/user_test_rbac_assignments.php',
                    'ruleFile' => '@runtime/user_test_rbac_rules.php',
                ],
            ],
        ];

        $this->mockWebApplication($appConfig);
        $this->reset();
        $_SERVER['HTTP_ACCEPT'] = 'text/json,q=0.1';
        $this->expectException('yii\\web\\ForbiddenHttpException');
        Yii::$app->user->loginRequired();
    }
}

class UserIdentity extends Component implements IdentityInterface
{
    private static $ids = [
        'user1',
        'user2',
        'user3',
    ];

    private $_id;

    public static function findIdentity($id)
    {
        if (in_array($id, static::$ids)) {
            $identitiy = new static();
            $identitiy->_id = $id;
            return $identitiy;
        }
    }

    public static function findIdentityByAccessToken($token, $type = null)
    {
        throw new NotSupportedException();
    }

    public function getId()
    {
        return $this->_id;
    }

    public function getAuthKey()
    {
        return 'ABCD1234';
    }

    public function validateAuthKey($authKey)
    {
        return $authKey === 'ABCD1234';
    }
}

static $cookiesMock;

class MockRequest extends \yii\web\Request
{
    public function getCookies()
    {
        global $cookiesMock;

        return $cookiesMock;
    }
}

class MockResponse extends \yii\web\Response
{
    public function getCookies()
    {
        global $cookiesMock;

        return $cookiesMock;
    }
}
