<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\filters;

use Closure;
use Yii;
use yii\base\Action;
use yii\filters\AccessRule;
use yii\web\Controller;
use yii\web\Request;
use yii\web\User;
use yiiunit\framework\filters\stubs\MockAuthManager;
use yiiunit\framework\filters\stubs\UserIdentity;
use yiiunit\framework\rbac\AuthorRule;

/**
 * @group filters
 */
class AccessRuleTest extends \yiiunit\TestCase
{
    protected function setUp()
    {
        parent::setUp();

        $_SERVER['SCRIPT_FILENAME'] = '/index.php';
        $_SERVER['SCRIPT_NAME'] = '/index.php';

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
     * @param string $userid optional user id
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

        // add "updateOwnPost" permission
        $updateOwnPost = $auth->createPermission('updateOwnPost');
        $updateOwnPost->description = 'Update post';
        $updateRule = new AuthorRule();
        $auth->add($updateRule);
        $updateOwnPost->ruleName = $updateRule->name;
        $auth->add($updateOwnPost);
        $auth->addChild($updateOwnPost, $updatePost);

        // add "author" role and give this role the "createPost" permission
        $author = $auth->createRole('author');
        $auth->add($author);
        $auth->addChild($author, $createPost);
        $auth->addChild($author, $updateOwnPost);

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
        $user = false;
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

    public function testMatchController()
    {
        $action = $this->mockAction();
        $user = false;
        $request = $this->mockRequest();

        $rule = new AccessRule([
            'allow' => true,
            'controllers' => ['test', 'other'],
        ]);

        $action->controller->id = 'test';
        $this->assertTrue($rule->allows($action, $user, $request));

        $action->controller->id = 'other';
        $this->assertTrue($rule->allows($action, $user, $request));
        $action->controller->id = 'foreign';
        $this->assertNull($rule->allows($action, $user, $request));

        $rule->allow = false;

        $action->controller->id = 'test';
        $this->assertFalse($rule->allows($action, $user, $request));
        $action->controller->id = 'other';
        $this->assertFalse($rule->allows($action, $user, $request));
        $action->controller->id = 'foreign';
        $this->assertNull($rule->allows($action, $user, $request));
    }

    /**
     * @depends testMatchController
     */
    public function testMatchControllerWildcard()
    {
        $action = $this->mockAction();
        $user = false;
        $request = $this->mockRequest();

        $rule = new AccessRule([
            'allow' => true,
            'controllers' => ['module/*', '*/controller'],
        ]);

        $action->controller->id = 'module/test';
        $this->assertTrue($rule->allows($action, $user, $request));
        $action->controller->id = 'any-module/controller';
        $this->assertTrue($rule->allows($action, $user, $request));
        $action->controller->id = 'other/other';
        $this->assertNull($rule->allows($action, $user, $request));

        $rule->allow = false;

        $action->controller->id = 'module/test';
        $this->assertFalse($rule->allows($action, $user, $request));
        $action->controller->id = 'any-module/controller';
        $this->assertFalse($rule->allows($action, $user, $request));
        $action->controller->id = 'other/other';
        $this->assertNull($rule->allows($action, $user, $request));
    }

    /**
     * Data provider for testMatchRole.
     *
     * @return array or arrays
     *           the id of the action
     *           should the action allow (true) or disallow (false)
     *           test user id
     *           expected match result (true, false, null)
     */
    public function matchRoleProvider()
    {
        return [
            ['create', true,  'user1',   [], true],
            ['create', true,  'user2',   [], true],
            ['create', true,  'user3',   [], null],
            ['create', true,  'unknown', [], null],
            ['create', false, 'user1',   [], false],
            ['create', false, 'user2',   [], false],
            ['create', false, 'user3',   [], null],
            ['create', false, 'unknown', [], null],

            // user2 is author, can only edit own posts
            ['update', true,  'user2',   ['authorID' => 'user2'], true],
            ['update', true,  'user2',   ['authorID' => 'user1'], null],
            // user1 is admin, can update all posts
            ['update', true,  'user1',   ['authorID' => 'user1'], true],
            ['update', true,  'user1',   ['authorID' => 'user2'], true],
            // unknown user can not edit anything
            ['update', true,  'unknown', ['authorID' => 'user1'], null],
            ['update', true,  'unknown', ['authorID' => 'user2'], null],

            // user2 is author, can only edit own posts
            ['update', true,  'user2',   function () { return ['authorID' => 'user2']; }, true],
            ['update', true,  'user2',   function () { return ['authorID' => 'user1']; }, null],
            // user1 is admin, can update all posts
            ['update', true,  'user1',   function () { return ['authorID' => 'user1']; }, true],
            ['update', true,  'user1',   function () { return ['authorID' => 'user2']; }, true],
            // unknown user can not edit anything
            ['update', true,  'unknown', function () { return ['authorID' => 'user1']; }, null],
            ['update', true,  'unknown', function () { return ['authorID' => 'user2']; }, null],
        ];
    }

    /**
     * Test that a user matches certain roles.
     *
     * @dataProvider matchRoleProvider
     * @param string $actionid the action id
     * @param bool $allow whether the rule should allow access
     * @param string $userid the userid to check
     * @param array|Closure $roleParams params for $roleParams
     * @param bool $expected the expected result or null
     */
    public function testMatchRole($actionid, $allow, $userid, $roleParams, $expected)
    {
        $action = $this->mockAction();
        $auth = $this->mockAuthManager();
        $request = $this->mockRequest();

        $rule = new AccessRule([
            'allow' => $allow,
            'roles' => [$actionid === 'create' ? 'createPost' : 'updatePost'],
            'actions' => [$actionid],
            'roleParams' => $roleParams,
        ]);

        $action->id = $actionid;

        $user = $this->mockUser($userid);
        $user->accessChecker = $auth;
        $this->assertEquals($expected, $rule->allows($action, $user, $request));
    }

    /**
     * Test that matching role is not possible without User component.
     *
     * @see https://github.com/yiisoft/yii2/issues/4793
     */
    public function testMatchRoleWithoutUser()
    {
        $action = $this->mockAction();
        $request = $this->mockRequest();

        $rule = new AccessRule([
            'allow' => true,
            'roles' => ['@'],
        ]);

        $this->expectException('yii\base\InvalidConfigException');
        $rule->allows($action, false, $request);
    }

    public function testMatchRoleSpecial()
    {
        $action = $this->mockAction();
        $request = $this->mockRequest();
        $authenticated = $this->mockUser('user1');
        $guest = $this->mockUser('unknown');

        $rule = new AccessRule();
        $rule->allow = true;
        $rule->roleParams = function () {
            $this->assertTrue(false, 'Should not be executed');
        };

        $rule->roles = ['@'];
        $this->assertTrue($rule->allows($action, $authenticated, $request));
        $this->assertNull($rule->allows($action, $guest, $request));

        $rule->roles = ['?'];
        $this->assertNull($rule->allows($action, $authenticated, $request));
        $this->assertTrue($rule->allows($action, $guest, $request));

        $rule->roles = ['?', '@'];
        $this->assertTrue($rule->allows($action, $authenticated, $request));
        $this->assertTrue($rule->allows($action, $guest, $request));
    }

    public function testMatchRolesAndPermissions()
    {
        $action = $this->mockAction();
        $user = $this->getMockBuilder('\yii\web\User')->getMock();
        $user->identityCLass = UserIdentity::className();

        $rule = new AccessRule([
            'allow' => true,
        ]);

        $request = $this->mockRequest('GET');
        $this->assertTrue($rule->allows($action, $user, $request));

        $rule->roles = ['allowed_role_1', 'allowed_role_2'];
        $this->assertNull($rule->allows($action, $user, $request));

        $rule->roles = [];
        $rule->permissions = ['allowed_permission_1', 'allowed_permission_2'];
        $this->assertNull($rule->allows($action, $user, $request));

        $rule->roles = ['allowed_role_1', 'allowed_role_2'];
        $rule->permissions = ['allowed_permission_1', 'allowed_permission_2'];
        $this->assertNull($rule->allows($action, $user, $request));

        $user->method('can')->willReturn(true);
        $rule->roles = ['allowed_role_1', 'allowed_role_2'];
        $this->assertTrue($rule->allows($action, $user, $request));

        $rule->roles = [];
        $rule->permissions = ['allowed_permission_1', 'allowed_permission_2'];
        $this->assertTrue($rule->allows($action, $user, $request));

        $rule->roles = ['allowed_role_1', 'allowed_role_2'];
        $rule->permissions = ['allowed_permission_1', 'allowed_permission_2'];
        $this->assertTrue($rule->allows($action, $user, $request));
    }

    /**
     * Test that callable object can be used as roleParams values
     */
    public function testMatchRoleWithRoleParamsCallable()
    {
        $action = $this->mockAction();
        $action->id = 'update';

        $auth = $this->mockAuthManager();
        $request = $this->mockRequest();

        $rule = new AccessRule([
            'allow' => true,
            'roles' => ['updatePost'],
            'actions' => ['update'],
            'roleParams' => new RoleParamCallableObject(),
        ]);

        $user = $this->mockUser('user2');
        $user->accessChecker = $auth;

        $this->assertEquals(true, $rule->allows($action, $user, $request));
    }

    public function testMatchVerb()
    {
        $action = $this->mockAction();
        $user = false;

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
        $user = false;

        $rule = new AccessRule();

        // by default match all IPs
        $request = $this->mockRequest();
        $rule->allow = true;
        $this->assertTrue($rule->allows($action, $user, $request));
        $rule->allow = false;
        $this->assertFalse($rule->allows($action, $user, $request));

        // empty IPs = match all IPs
        $request = $this->mockRequest();
        $rule->ips = [];
        $rule->allow = true;
        $this->assertTrue($rule->allows($action, $user, $request));
        $rule->allow = false;
        $this->assertFalse($rule->allows($action, $user, $request));

        // match, one IP
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
        $request = $this->mockRequest();
        $rule->ips = ['127.0.0.1'];
        $rule->allow = true;
        $this->assertTrue($rule->allows($action, $user, $request));
        $rule->allow = false;
        $this->assertFalse($rule->allows($action, $user, $request));

        // no match, one IP
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
        $request = $this->mockRequest();
        $rule->ips = ['192.168.0.1'];
        $rule->allow = true;
        $this->assertNull($rule->allows($action, $user, $request));
        $rule->allow = false;
        $this->assertNull($rule->allows($action, $user, $request));

        // no partial match, one IP
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
        $request = $this->mockRequest();
        $rule->ips = ['127.0.0.10'];
        $rule->allow = true;
        $this->assertNull($rule->allows($action, $user, $request));
        $rule->allow = false;
        $this->assertNull($rule->allows($action, $user, $request));
        $_SERVER['REMOTE_ADDR'] = '127.0.0.10';
        $request = $this->mockRequest();
        $rule->ips = ['127.0.0.1'];
        $rule->allow = true;
        $this->assertNull($rule->allows($action, $user, $request));
        $rule->allow = false;
        $this->assertNull($rule->allows($action, $user, $request));

        // match, one IP IPv6
        $_SERVER['REMOTE_ADDR'] = '::1';
        $request = $this->mockRequest();
        $rule->ips = ['::1'];
        $rule->allow = true;
        $this->assertTrue($rule->allows($action, $user, $request));
        $rule->allow = false;
        $this->assertFalse($rule->allows($action, $user, $request));

        // no match, one IP IPv6
        $_SERVER['REMOTE_ADDR'] = '::1';
        $request = $this->mockRequest();
        $rule->ips = ['dead::beaf::1'];
        $rule->allow = true;
        $this->assertNull($rule->allows($action, $user, $request));
        $rule->allow = false;
        $this->assertNull($rule->allows($action, $user, $request));

        // no partial match, one IP IPv6
        $_SERVER['REMOTE_ADDR'] = '::1';
        $request = $this->mockRequest();
        $rule->ips = ['::123'];
        $rule->allow = true;
        $this->assertNull($rule->allows($action, $user, $request));
        $rule->allow = false;
        $this->assertNull($rule->allows($action, $user, $request));
        $_SERVER['REMOTE_ADDR'] = '::123';
        $request = $this->mockRequest();
        $rule->ips = ['::1'];
        $rule->allow = true;
        $this->assertNull($rule->allows($action, $user, $request));
        $rule->allow = false;
        $this->assertNull($rule->allows($action, $user, $request));

        // undefined IP
        $_SERVER['REMOTE_ADDR'] = null;
        $request = $this->mockRequest();
        $rule->ips = ['192.168.*'];
        $rule->allow = true;
        $this->assertNull($rule->allows($action, $user, $request));
        $rule->allow = false;
        $this->assertNull($rule->allows($action, $user, $request));
    }

    public function testMatchIPWildcard()
    {
        $action = $this->mockAction();
        $user = false;

        $rule = new AccessRule();

        // no match
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
        $request = $this->mockRequest();
        $rule->ips = ['192.168.*'];
        $rule->allow = true;
        $this->assertNull($rule->allows($action, $user, $request));
        $rule->allow = false;
        $this->assertNull($rule->allows($action, $user, $request));

        // match
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
        $request = $this->mockRequest();
        $rule->ips = ['127.0.*'];
        $rule->allow = true;
        $this->assertTrue($rule->allows($action, $user, $request));
        $rule->allow = false;
        $this->assertFalse($rule->allows($action, $user, $request));

        // match, IPv6
        $_SERVER['REMOTE_ADDR'] = '2a01:4f8:120:7202::2';
        $request = $this->mockRequest();
        $rule->ips = ['2a01:4f8:120:*'];
        $rule->allow = true;
        $this->assertTrue($rule->allows($action, $user, $request));
        $rule->allow = false;
        $this->assertFalse($rule->allows($action, $user, $request));

        // no match, IPv6
        $_SERVER['REMOTE_ADDR'] = '::1';
        $request = $this->mockRequest();
        $rule->ips = ['2a01:4f8:120:*'];
        $rule->allow = true;
        $this->assertNull($rule->allows($action, $user, $request));
        $rule->allow = false;
        $this->assertNull($rule->allows($action, $user, $request));
    }

    public function testMatchIPMask()
    {
        $action = $this->mockAction();
        $user = false;

        $rule = new AccessRule();

        // no match
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
        $request = $this->mockRequest();
        $rule->ips = ['127.0.0.32/27'];
        $rule->allow = true;
        $this->assertNull($rule->allows($action, $user, $request));
        $rule->allow = false;
        $this->assertNull($rule->allows($action, $user, $request));

        // match
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
        $request = $this->mockRequest();
        $rule->ips = ['127.0.0.1/27'];
        $rule->allow = true;
        $this->assertTrue($rule->allows($action, $user, $request));
        $rule->allow = false;
        $this->assertFalse($rule->allows($action, $user, $request));

        // match, IPv6
        $_SERVER['REMOTE_ADDR'] = '2a01:4f8:120:7202::2';
        $request = $this->mockRequest();
        $rule->ips = ['2a01:4f8:120:7202::2/127'];
        $rule->allow = true;
        $this->assertTrue($rule->allows($action, $user, $request));
        $rule->allow = false;
        $this->assertFalse($rule->allows($action, $user, $request));

        // no match, IPv6
        $_SERVER['REMOTE_ADDR'] = '2a01:4f8:120:7202::ffff';
        $request = $this->mockRequest();
        $rule->ips = ['2a01:4f8:120:7202::2/123'];
        $rule->allow = true;
        $this->assertNull($rule->allows($action, $user, $request));
        $rule->allow = false;
        $this->assertNull($rule->allows($action, $user, $request));
    }
}

class RoleParamCallableObject
{
    public function __invoke()
    {
        return ['authorID' => 'user2'];
    }
}
