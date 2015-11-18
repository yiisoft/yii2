<?php

namespace yiiunit\framework\filters;

use Yii;
use yii\base\Component;
use yii\base\NotSupportedException;
use yii\filters\auth\QueryParamAuth;
use yii\helpers\ArrayHelper;
use yii\rest\Controller;
use yii\web\IdentityInterface;

/**
 * @group filters
 */
class QueryParamAuthTest extends \yiiunit\TestCase
{
    protected function setUp()
    {
        parent::setUp();

        $_SERVER['SCRIPT_FILENAME'] = "/index.php";
        $_SERVER['SCRIPT_NAME'] = "/index.php";

        $appConfig = [
            'components' => [
                'user' => [
                    'identityClass' => UserIdentity::className()
                ],
            ],
            'controllerMap' => [
                'test-auth' => TestAuthController::className()
            ]
        ];

        $this->mockWebApplication($appConfig);
    }

    public function tokenProvider()
    {
        return [
            ['token1', 'user1'],
            ['token2', 'user2'],
            ['token3', 'user3'],
            ['unknown', null],
        ];
    }

    /**
     * @dataProvider tokenProvider
     */
    public function testAuthOnly($token, $login)
    {
        $_GET['access-token'] = $token;
        /** @var TestAuthController $controller */
        $controller = Yii::$app->createController('test-auth')[0];
        $controller->authenticatorConfig = ['only' => ['a']];
        if ($login === null) {
            $this->setExpectedException('\yii\web\UnauthorizedHttpException');
        }
        $this->assertEquals($login, $controller->run('a'));
    }

    /**
     * @dataProvider tokenProvider
     */
    public function testAuthOptional($token, $login)
    {
        $_GET['access-token'] = $token;
        /** @var TestAuthController $controller */
        $controller = Yii::$app->createController('test-auth')[0];
        $controller->authenticatorConfig = ['optional' => ['a']];
        $this->assertEquals($login, $controller->run('a'));
    }

    /**
     * @dataProvider tokenProvider
     */
    public function testAuthExcept($token, $login)
    {
        $_GET['access-token'] = $token;
        /** @var TestAuthController $controller */
        $controller = Yii::$app->createController('test-auth')[0];
        $controller->authenticatorConfig = ['except' => ['b']];
        if ($login === null) {
            $this->setExpectedException('\yii\web\UnauthorizedHttpException');
        }
        $this->assertEquals($login, $controller->run('a'));
    }
}


class TestAuthController extends Controller
{
    public $authenticatorConfig = [];

    public function behaviors()
    {
        return [
            'authenticator' => ArrayHelper::merge([
                'class' => QueryParamAuth::className(),
            ], $this->authenticatorConfig)
        ];
    }

    public function actionA()
    {
        return Yii::$app->user->id;
    }
}

class UserIdentity extends Component implements IdentityInterface
{
    private static $ids = [
        'user1',
        'user2',
        'user3',
    ];

    private static $tokens = [
        'token1' => 'user1',
        'token2' => 'user2',
        'token3' => 'user3',
    ];

    private $_id;

    private $_token;

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
        if (isset(static::$tokens[$token])) {
            $id = static::$tokens[$token];
            $identitiy = new static();
            $identitiy->_id = $id;
            $identitiy->_token = $token;
            return $identitiy;
        }
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