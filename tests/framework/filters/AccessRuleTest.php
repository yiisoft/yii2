<?php

namespace yiiunit\framework\filters;

use Yii;
use yii\base\Action;
use yii\filters\AccessRule;
use yii\filters\HttpCache;
use yii\web\Controller;
use yii\web\Request;
use yii\web\User;
use yiiunit\framework\filters\stubs\MockAuthManager;
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

    /**
     * @param string $method
     * @return Request
     */
    protected function mockRequest($method = 'GET')
    {
        /** @var Request $request */
        $request = $this->getMockBuilder('\yii\web\Request')
            ->setMethods(['getMethod'])
            ->getMock();
        $request->method('getMethod')->willReturn($method);
        return $request;
    }

    /**
     * @param string optional user id
     * @return User
     */
    protected function mockUser($userid = null)
    {
        $user = new User([
            'identityClass' => UserIdentity::className(),
            'enableAutoLogin' => false,
        ]);
        if ($userid !== null) {
            $user->setIdentity(UserIdentity::findIdentity($userid));
        }
        return $user;
    }

    /**
     * @return Action
     */
    protected function mockAction()
    {
        $controller = new Controller('site', Yii::$app);
        return new Action('test', $controller);
    }

    /**
     * @return \yii\rbac\BaseManager
     */
    protected function mockAuthManager()
    {
        $auth = new MockAuthManager();
        // add "createPost" permission
        $createPost = $auth->createPermission('createPost');
        $createPost->description = 'Create a post';
        $auth->add($createPost);

        // add "updatePost" permission
        $updatePost = $auth->createPermission('updatePost');
        $updatePost->description = 'Update post';
        $auth->add($updatePost);

        // add "author" role and give this role the "createPost" permission
        $author = $auth->createRole('author');
        $auth->add($author);
        $auth->addChild($author, $createPost);

        // add "admin" role and give this role the "updatePost" permission
        // as well as the permissions of the "author" role
        $admin = $auth->createRole('admin');
        $auth->add($admin);
        $auth->addChild($admin, $updatePost);
        $auth->addChild($admin, $author);

        // Assign roles to users. 1 and 2 are IDs returned by IdentityInterface::getId()
        // usually implemented in your User model.
        $auth->assign($author, 'user2');
        $auth->assign($admin, 'user1');

        return $auth;
    }

    public function testMatchAction()
    {
        $action = $this->mockAction();
        $user = $this->mockUser();
        $request = $this->mockRequest();

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

    /**
     * Data provider for testMatchRole
     *
     * @return array or arrays
     *           the id of the action
     *           should the action allow (true) or disallow (false)
     *           test user id
     *           expected match result (true, false, null)
     */
    public function matchRoleProvider() {
        return [
            ['create', true, 'user1', true],
            ['create', true, 'user2', true],
            ['create', true, 'user3', null],
            ['create', true, 'unknown', null],
            ['create', false, 'user1', false],
            ['create', false, 'user2', false],
            ['create', false, 'user3', null],
            ['create', false, 'unknown', null],
        ];
    }

    /**
     * Test that a user matches certain roles
     *
     * @dataProvider matchRoleProvider
     * @param string $actionid the action id
     * @param boolean $allow whether the rule should allow access
     * @param string $userid the userid to check
     * @param boolean $expected the expected result or null
     */
    public function testMatchRole($actionid, $allow, $userid, $expected) {
        $action = $this->mockAction();
        $auth = $this->mockAuthManager();
        $request = $this->mockRequest();

        $rule = new AccessRule([
            'allow' => $allow,
            'roles' => ['createPost'],
            'actions' => ['create'],
        ]);

        $action->id = $actionid;

        $user = $this->mockUser($userid);
        $user->accessChecker = $auth;
        $this->assertEquals($expected, $rule->allows($action, $user, $request));
    }

    public function testMatchVerb()
    {
        $action = $this->mockAction();
        $user = $this->mockUser();

        $rule = new AccessRule([
            'allow' => true,
            'verbs' => ['POST', 'get'],
        ]);

        $request = $this->mockRequest('GET');
        $this->assertTrue($rule->allows($action, $user, $request));

        $request = $this->mockRequest('POST');
        $this->assertTrue($rule->allows($action, $user, $request));

        $request = $this->mockRequest('HEAD');
        $this->assertNull($rule->allows($action, $user, $request));

        $request = $this->mockRequest('get');
        $this->assertTrue($rule->allows($action, $user, $request));

        $request = $this->mockRequest('post');
        $this->assertTrue($rule->allows($action, $user, $request));

        $request = $this->mockRequest('head');
        $this->assertNull($rule->allows($action, $user, $request));
    }

    // TODO test match custom callback

    public function testMatchIP()
    {
        $action = $this->mockAction();
        $user = $this->mockUser();
        $request = $this->mockRequest();

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
        $action = $this->mockAction();
        $user = $this->mockUser();
        $request = $this->mockRequest();

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
