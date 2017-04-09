<?php

namespace yii\filters;

use Yii;
use yiiunit\TestCase;
use Prophecy\Argument;
use yii\filters\AccessControl;
use yii\web\User;
use yii\filters\AccessRule;

/**
 * @group filters
 */
class AccessControlTest extends TestCase
{

    protected function setUp()
    {
        parent::setUp();

        $config = [
            'components' => [
                'user' => [
                    'identityClass' => 'identityClass'
                ]
            ]
        ];
        
        $this->mockWebApplication($config);

    }

    public function testInitRuleIsArrayLoadedRule()
    {
        $config = [
            'rules' => [
                ['allow' => false]
            ]
        ];

        $filter = new AccessControl($config);

        $expectedFilters = [Yii::createObject(array_merge($filter->ruleConfig, ['allow' => false]))];

        $this->assertEquals($expectedFilters, $filter->rules);
    }

    public function testInitRuleNotArrayEmptyRules()
    {
        $filter = new AccessControl();

        $this->assertCount(0, $filter->rules);
    }

    public function testInitDenyCallbackEqualsNotFunctionGenerateException()
    {
        $this->setExpectedException('yii\base\InvalidConfigException');

        new AccessControl(['denyCallback' => 'Invalid function']);
    }

    public function testInitDenyCallbackIsClosure()
    {
        $filter = new AccessControl(['denyCallback' => function () {}]);

        $this->assertInstanceOf('\Closure', $filter->denyCallback);
    }

    public function testBeforeActionAccessRuleAllowReturnTrue()
    {
        /* @var $fakeRule AccessRule|ObjectProphecy */
        $fakeRule = $this->prophesize(AccessRule::className());
        $fakeRule->allows(Argument::any(), Argument::any(), Argument::any())->willReturn(true);
        Yii::$container->set(AccessRule::className(), $fakeRule->reveal());
        $config = [
            'rules' => [[]]
        ];
        $filter = new AccessControl($config);

        $allow = $filter->beforeAction(null);

        $this->assertTrue($allow);
    }

    public function testBeforeActionAccessRuleDenyReturnFalse()
    {
        /* @var $fakeRule AccessRule|ObjectProphecy */
        $fakeRule = $this->prophesize(AccessRule::className());
        $fakeRule->allows(Argument::any(), Argument::any(), Argument::any())->willReturn(false);
        Yii::$container->set(AccessRule::className(), $fakeRule->reveal());

        $config = [
            'rules' => [[]],
            'denyCallback' => function () {},
        ];
        $filter = new AccessControl($config);

        $deny = $filter->beforeAction(null);

        $this->assertFalse($deny);
    }
    
    public function testBeforeActionAccessRuleNotSuitableReturnFalse()
    {
        /* @var $fakeRule AccessRule|ObjectProphecy */
        $fakeRule = $this->prophesize(AccessRule::className());
        $fakeRule->allows(Argument::any(), Argument::any(), Argument::any())->willReturn(null);
        Yii::$container->set(AccessRule::className(), $fakeRule->reveal());
        $config = [
            'rules' => [[]],
            'denyCallback' => function () {},
        ];
        $filter = new AccessControl($config);

        $deny = $filter->beforeAction(null);

        $this->assertFalse($deny);
    }

    public function testExecDenyCallbackEqualsNullUsedDefaultFunction()
    {
        $fakeFilter = $this->getMockBuilder(AccessControl::className())
            ->setMethods(['denyAccess'])
            ->disableOriginalConstructor()
            ->getMock();
        $fakeFilter->expects(self::at(0))
            ->method('denyAccess');
        
        $this->invokeMethod($fakeFilter, 'execDenyCallback', [null, null, null]);
    }

    public function testExecDenyCallbackEqualsFunctionUsedFunction()
    {
        $executeCallback = false;
        $config = [
            'rules' => [[]],
            'denyCallback' => function() use(&$executeCallback) {
                $executeCallback = true;
            },

        ];
        $filter = new AccessControl($config);

        $this->invokeMethod($filter, 'execDenyCallback', [null, null, null]);
        $this->assertTrue($executeCallback, 'Callback not run');
    }

    public function testDenyAccessUserIsGuest()
    {
        $filter = new AccessControl();
        
        /* @var $fakeUser \yii\web\User */
        $fakeUser = $this->getMockBuilder(User::className())
            ->setMethods(['getIsGuest', 'loginRequired'])
            ->disableOriginalConstructor()
            ->getMock();
        $fakeUser->expects(self::at(0))
            ->method('getIsGuest')
            ->willReturn(true);
        $fakeUser->expects(self::at(1))
            ->method('loginRequired')
            ->willReturn(true);

        $this->invokeMethod($filter, 'denyAccess', [$fakeUser]);
    }
    
    public function testDenyAccessUserNotGuest()
    {
        $filter = new AccessControl();
        /* @var $fakeUser \yii\web\User|ObjectProphecy */
        $fakeUser = $this->prophesize(User::className());
        $fakeUser->getIsGuest()->willReturn(false);

        $this->setExpectedException('yii\web\ForbiddenHttpException');

        $this->invokeMethod($filter, 'denyAccess', [$fakeUser->reveal()]);
    }
}
