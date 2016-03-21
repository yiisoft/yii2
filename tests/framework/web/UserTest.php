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
use yii\web\Cookie;
use yii\web\CookieCollection;
use yii\web\IdentityInterface;
use yii\web\UrlManager;
use yii\web\UrlRule;
use yii\web\Request;
use yii\web\Response;
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
    
    public function testCookieCleanup()
    {
        global $mock_cookies;

        $mock_cookies = new CookieCollection( );

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

        Yii::$app->user->login(UserIdentity::findIdentity('user1'),3600);
        $this->assertFalse(Yii::$app->user->isGuest);
        $this->assertTrue(Yii::$app->user->id == 'user1');
        $this->assertFalse(strlen($mock_cookies->getValue(Yii::$app->user->identityCookie['name'])) == 0);

        Yii::$app->user->login(UserIdentity::findIdentity('user2'),0);
        $this->assertFalse(Yii::$app->user->isGuest);
        $this->assertTrue(Yii::$app->user->id == 'user2');
        $this->assertTrue(strlen($mock_cookies->getValue(Yii::$app->user->identityCookie['name'])) == 0);
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
        return $authKey == 'ABCD1234';
    }
}

static $mock_cookies;

class MockRequest extends \yii\web\Request
{
    public function getCookies()
    {
        global $mock_cookies;

        return $mock_cookies;
   }
}

class MockResponse extends \yii\web\Response
{
    public function getCookies()
    {
        global $mock_cookies;
      
        return $mock_cookies;
    }
}
