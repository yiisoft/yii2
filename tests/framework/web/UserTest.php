<?php


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

use yii\base\NotSupportedException;
use yii\base\Component;
use yii\rbac\PhpManager;
use yii\web\ForbiddenHttpException;
use yii\web\IdentityInterface;
use yii\web\UrlManager;
use yii\web\UrlRule;
use yii\web\Request;
use Yii;
use yiiunit\TestCase;

/**
 * @group web
 */
class UserTest extends TestCase
{
    /**
     * @var integer virtual time to be returned by mocked time() function.
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
                ]
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
        Yii::$app->set('response',['class' => 'yii\web\Response']);
        Yii::$app->set('request',[
            'class' => 'yii\web\Request',
            'scriptFile' => __DIR__ .'/index.php',
            'scriptUrl' => '/index.php',
            'url' => ''
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
                ]
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
        $_SERVER['HTTP_ACCEPT'] = '*;q=0.1';
        $user->loginRequired();
        $this->assertEquals('accept-all', $user->getReturnUrl());
        $this->assertTrue(Yii::$app->response->getIsRedirection());

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

        // Confirm that returnUrl is not set.
        $this->reset();
        Yii::$app->request->setUrl('json-only');
        $_SERVER['HTTP_ACCEPT'] = 'text/json;q=0.1';
        try {
            $user->loginRequired();
        } catch (ForbiddenHttpException $e) {}
        $this->assertNotEquals('json-only', $user->getReturnUrl());


        $this->reset();
        $_SERVER['HTTP_ACCEPT'] = 'text/json;q=0.1';
        $this->setExpectedException('yii\\web\\ForbiddenHttpException');
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
                ]
            ],
        ];

        $this->mockWebApplication($appConfig);
        $this->reset();
        $_SERVER['HTTP_ACCEPT'] = 'text/json,q=0.1';
        $this->setExpectedException('yii\\web\\ForbiddenHttpException');
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
        throw new NotSupportedException();
    }

    public function validateAuthKey($authKey)
    {
        throw new NotSupportedException();
    }
}