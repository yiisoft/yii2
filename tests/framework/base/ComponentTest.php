<?php

declare(strict_types=1);

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\base;

use PHPUnit\Framework\Attributes\DataProviderExternal;
use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\Attributes\Group;
use yii\base\Event;
use yii\base\InvalidCallException;
use yii\base\InvalidConfigException;
use yii\base\UnknownMethodException;
use yii\base\UnknownPropertyException;
use yiiunit\data\base\ComponentWithBehaviors;
use yiiunit\data\base\NewBehavior;
use yiiunit\data\base\NewBehavior2;
use yiiunit\data\base\NewComponent;
use yiiunit\framework\base\provider\ComponentProvider;
use yiiunit\TestCase;

use function get_class;

/**
 * Unit tests for the {@see Component} class.
 *
 * {@see ComponentProvider} for test case data providers.
 */
#[Group('base')]
final class ComponentTest extends TestCase
{
    protected NewComponent|null $component = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mockApplication();

        $this->component = new NewComponent();
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->component = null;
    }

    public function testClone(): void
    {
        $component = new NewComponent();

        $behavior = new NewBehavior();

        $component->attachBehavior('a', $behavior);

        self::assertSame(
            $behavior,
            $component->getBehavior('a'),
            'Should be attached to the component.',
        );

        $component->on(
            'test',
            'fake',
        );

        self::assertTrue(
            $component->hasEventHandlers('test'),
            "Should have handlers for 'test' event.",
        );

        $component->on(
            '*',
            'fakeWildcard',
        );

        self::assertTrue(
            $component->hasEventHandlers('foo'),
            "Should match 'foo' event.",
        );

        $clone = clone $component;

        self::assertNotSame(
            $component,
            $clone,
            'Clone should be a different instance.',
        );
        self::assertNull(
            $clone->getBehavior('a'),
            "Clone should not have behavior 'a'.",
        );
        self::assertFalse(
            $clone->hasEventHandlers('test'),
            "Clone should not have handlers for 'test' event.",
        );
        self::assertFalse(
            $clone->hasEventHandlers('foo'),
            "Clone should not have handlers for 'foo' event.",
        );
        self::assertFalse(
            $clone->hasEventHandlers('*'),
            'Clone should not have wildcard handlers.',
        );
    }

    #[DataProviderExternal(ComponentProvider::class, 'hasPropertyCheckVars')]
    public function testHasPropertyCheckVars(string $property, bool $checkVars, bool $expected): void
    {
        self::assertSame(
            $expected,
            $this->component->hasProperty($property, $checkVars),
            "Should return '" . ($expected ? 'true' : 'false') . "' for '$property'.",
        );
    }

    #[DataProviderExternal(ComponentProvider::class, 'canGetPropertyCheckVars')]
    public function testCanGetPropertyCheckVars(string $property, bool $checkVars, bool $expected): void
    {
        self::assertSame(
            $expected,
            $this->component->canGetProperty($property, $checkVars),
            "Should return '" . ($expected ? 'true' : 'false') . "' for '$property'.",
        );
    }

    #[DataProviderExternal(ComponentProvider::class, 'canSetPropertyCheckVars')]
    public function testCanSetPropertyCheckVars(string $property, bool $checkVars, bool $expected): void
    {
        self::assertSame(
            $expected,
            $this->component->canSetProperty($property, $checkVars),
            "Should return '" . ($expected ? 'true' : 'false') . "' for '$property'.",
        );
    }

    #[DataProviderExternal(ComponentProvider::class, 'hasPropertyCheckBehaviors')]
    public function testHasPropertyCheckBehaviors(string $property, bool $checkBehaviors, bool $expected): void
    {
        $component = new ComponentWithBehaviors();

        self::assertSame(
            $expected,
            $component->hasProperty($property, true, $checkBehaviors),
            "Should return '" . ($expected ? 'true' : 'false') . "' for '$property'.",
        );
    }

    #[DataProviderExternal(ComponentProvider::class, 'canGetPropertyCheckBehaviors')]
    public function testCanGetPropertyCheckBehaviors(string $property, bool $checkBehaviors, bool $expected): void
    {
        $component = new ComponentWithBehaviors();

        self::assertSame(
            $expected,
            $component->canGetProperty($property, true, $checkBehaviors),
            "Should return '" . ($expected ? 'true' : 'false') . "' for '$property'.",
        );
    }

    #[DataProviderExternal(ComponentProvider::class, 'canSetPropertyCheckBehaviors')]
    public function testCanSetPropertyCheckBehaviors(string $property, bool $checkBehaviors, bool $expected): void
    {
        $component = new ComponentWithBehaviors();

        self::assertSame(
            $expected,
            $component->canSetProperty($property, true, $checkBehaviors),
            "Should return '" . ($expected ? 'true' : 'false') . "' for '$property'.",
        );
    }

    public function testCanSetPropertyAfterAttachBehavior(): void
    {
        self::assertFalse(
            $this->component->canSetProperty('p2'),
            "Should return 'false' for 'p2' before attaching behavior.",
        );

        $this->component->attachBehavior('a', new NewBehavior());

        self::assertTrue(
            $this->component->canSetProperty('p2'),
            "Should return 'true' for 'p2' after attaching behavior.",
        );

        $this->component->detachBehavior('a');
    }

    public function testGetProperty(): void
    {
        self::assertSame(
            'default',
            $this->component->Text,
            "Should return default value for 'Text' property.",
        );
        self::expectException(
            UnknownPropertyException::class,
        );
        self::expectExceptionMessage(
            'Getting unknown property: yiiunit\data\base\NewComponent::Caption',
        );

        // @phpstan-ignore property.notFound
        $value2 = $this->component->Caption;
    }

    public function testSetProperty(): void
    {
        $value = 'new value';

        $this->component->Text = $value;

        self::assertSame(
            $value,
            $this->component->Text,
            "Should return the newly set value for 'Text' property.",
        );
        self::expectException(
            UnknownPropertyException::class,
        );
        self::expectExceptionMessage(
            'Setting unknown property: yiiunit\data\base\NewComponent::NewMember',
        );

        // @phpstan-ignore property.notFound
        $this->component->NewMember = $value;
    }

    public function testIsset(): void
    {
        self::assertTrue(
            isset($this->component->Text),
            'Should be set by default.',
        );
        self::assertNotEmpty(
            $this->component->Text,
            'Should not be empty by default.',
        );

        $this->component->Text = '';

        self::assertTrue(
            isset($this->component->Text),
            "Should be set even when empty 'string'.",
        );
        self::assertEmpty(
            $this->component->Text,
            "Should be empty after setting to empty 'string'.",
        );

        $this->component->Text = null;

        self::assertFalse(
            isset($this->component->Text),
            "Should not be set after setting to 'null'.",
        );
        self::assertEmpty(
            $this->component->Text,
            "Should be empty after setting to 'null'.",
        );
        self::assertFalse(
            isset($this->component->p2),
            'Should not be set before attaching behavior.',
        );

        $component = $this->component;

        $component->attachBehavior('a', new NewBehavior());
        $component->setP2('test');

        self::assertTrue(
            isset($component->p2),
            'Should be set after attaching behavior and setting value.',
        );
    }

    public function testCallUnknownMethod(): void
    {
        self::expectException(
            UnknownMethodException::class,
        );
        self::expectExceptionMessage(
            'Calling unknown method: yiiunit\data\base\NewComponent::unknownMethod()',
        );

        // @phpstan-ignore method.notFound
        $this->component->unknownMethod();
    }

    public function testUnset(): void
    {
        unset($this->component->Text);

        self::assertFalse(
            isset($this->component->Text),
            'Should not be set after unsetting.',
        );
        self::assertEmpty(
            $this->component->Text,
            'Should be empty after unsetting.',
        );

        $component = $this->component;

        $component->attachBehavior('a', new NewBehavior());
        $component->setP2('test');

        self::assertSame(
            'test',
            $component->getP2(),
            "Should return the set value for behavior property 'p2'.",
        );

        unset($component->p2);

        self::assertNull(
            $component->getP2(),
            "Should be 'null' for behavior property 'p2' after unsetting.",
        );
    }

    public function testUnsetReadonly(): void
    {
        self::expectException(
            InvalidCallException::class,
        );
        self::expectExceptionMessage(
            'Unsetting an unknown or read-only property: yiiunit\data\base\NewComponent::object',
        );

        unset($this->component->object);
    }

    public function testOn(): void
    {
        self::assertFalse(
            $this->component->hasEventHandlers('click'),
            "Should have no handlers for 'click' initially.",
        );

        $this->component->on(
            'click',
            'foo',
        );

        self::assertTrue(
            $this->component->hasEventHandlers('click'),
            "Should have handlers for 'click' after on.",
        );
        self::assertFalse(
            $this->component->hasEventHandlers('click2'),
            "Should have no handlers for 'click2' initially.",
        );

        $p = 'on click2';

        // @phpstan-ignore property.notFound
        $this->component->$p = 'foo2';

        self::assertTrue(
            $this->component->hasEventHandlers('click2'),
            "Should have handlers for 'click2' after property assignment.",
        );
    }

    #[Depends('testOn')]
    public function testOff(): void
    {
        self::assertFalse(
            $this->component->hasEventHandlers('click'),
            "Should have no handlers for 'click' initially.",
        );

        $this->component->on(
            'click',
            'foo',
        );

        self::assertTrue(
            $this->component->hasEventHandlers('click'),
            "Should have handlers for 'click' after on.",
        );

        $this->component->off('click', 'foo');

        self::assertFalse(
            $this->component->hasEventHandlers('click'),
            "Should have no handlers for 'click' after off.",
        );

        $this->component->on(
            'click2',
            'foo',
        );
        $this->component->on(
            'click2',
            'foo2',
        );
        $this->component->on(
            'click2',
            'foo3',
        );

        self::assertTrue(
            $this->component->hasEventHandlers('click2'),
            "Should have handlers for 'click2' after attaching three.",
        );

        $this->component->off(
            'click2',
            'foo3',
        );

        self::assertTrue(
            $this->component->hasEventHandlers('click2'),
            "Should still have handlers for 'click2' after removing one.",
        );

        $this->component->off(
            'click2',
        );

        self::assertFalse(
            $this->component->hasEventHandlers('click2'),
            "Should have no handlers for 'click2' after removing all.",
        );
    }

    #[Depends('testOn')]
    public function testTrigger(): void
    {
        $this->component->on(
            'click',
            [$this->component, 'myEventHandler'],
        );

        self::assertFalse(
            $this->component->eventHandled,
            'Should not be handled before triggering.',
        );
        self::assertNull(
            $this->component->event,
            "Should be 'null' before triggering.",
        );

        $this->component->raiseEvent();

        self::assertTrue(
            $this->component->eventHandled,
            'Should be handled after triggering.',
        );
        self::assertSame(
            'click',
            $this->component->event->name,
            "Should be 'click'.",
        );
        self::assertSame(
            $this->component,
            $this->component->event->sender,
            'Should be the component.',
        );
        self::assertFalse(
            $this->component->event->handled,
            'Should not be marked as handled.'
        );

        $eventRaised = false;

        $this->component->on(
            'click',
            static function ($event) use (&$eventRaised): void {
                $eventRaised = true;
            },
        );
        $this->component->raiseEvent();

        self::assertTrue(
            $eventRaised,
            'Closure event handler should be triggered.',
        );

        // raise event w/o parameters
        $eventRaised = false;

        $this->component->on(
            'test',
            static function ($event) use (&$eventRaised): void {
                $eventRaised = true;
            },
        );
        $this->component->trigger('test');

        self::assertTrue(
            $eventRaised,
            'Should invoke handler when event is triggered without parameters.',
        );
    }

    #[Depends('testOn')]
    public function testOnWildcard(): void
    {
        self::assertFalse(
            $this->component->hasEventHandlers('group.click'),
            "Should have no handlers for 'group.click' initially.",
        );

        $this->component->on(
            'group.*',
            'foo',
        );

        self::assertTrue(
            $this->component->hasEventHandlers('group.click'),
            "Should match 'group.click'.",
        );
        self::assertFalse(
            $this->component->hasEventHandlers('category.click'),
            "Should not match 'category.click'.",
        );
    }

    #[Depends('testOnWildcard')]
    #[Depends('testOff')]
    public function testOffWildcard(): void
    {
        self::assertFalse(
            $this->component->hasEventHandlers('group.click'),
            "Should have no handlers for 'group.click' initially.",
        );

        $this->component->on(
            'group.*',
            'foo',
        );

        self::assertTrue(
            $this->component->hasEventHandlers('group.click'),
            "Should have handlers for 'group.click' after 'on()'.",
        );

        $this->component->off(
            '*',
            'foo',
        );

        self::assertTrue(
            $this->component->hasEventHandlers('group.click'),
            "Should not remove 'group.*' handler with 'off('*')'.",
        );

        $this->component->off(
            'group.*',
            'foo',
        );

        self::assertFalse(
            $this->component->hasEventHandlers('group.click'),
            "Should have no handlers for 'group.click' after 'off('group.*')'.",
        );

        $this->component->on(
            'category.*',
            'foo',
        );
        $this->component->on(
            'category.*',
            'foo2',
        );
        $this->component->on(
            'category.*',
            'foo3',
        );

        self::assertTrue(
            $this->component->hasEventHandlers('category.click'),
            "Should have handlers for 'category.click' after attaching three.",
        );

        $this->component->off(
            'category.*',
            'foo3',
        );

        self::assertTrue(
            $this->component->hasEventHandlers('category.click'),
            "Should still have handlers for 'category.click' after removing one.",
        );

        $this->component->off(
            'category.*',
        );

        self::assertFalse(
            $this->component->hasEventHandlers('category.click'),
            "Should have no handlers for 'category.click' after removing all.",
        );
    }

    #[Depends('testTrigger')]
    public function testTriggerWildcard(): void
    {
        $this->component->on(
            'cli*',
            [$this->component, 'myEventHandler'],
        );

        self::assertFalse(
            $this->component->eventHandled,
            'Should not be handled before triggering.',
        );
        self::assertNull(
            $this->component->event,
            "Should be 'null' before triggering wildcard.",
        );

        $this->component->raiseEvent();

        self::assertTrue(
            $this->component->eventHandled,
            'Should be handled after triggering.',
        );
        self::assertSame(
            'click',
            $this->component->event->name,
            "Should be 'click' for wildcard trigger.",
        );
        self::assertSame(
            $this->component,
            $this->component->event->sender,
            'Should be the component for wildcard trigger.',
        );
        self::assertFalse(
            $this->component->event->handled,
            'Should not be marked as handled.',
        );

        $eventRaised = false;

        $this->component->on(
            'cli*',
            static function ($event) use (&$eventRaised): void {
                $eventRaised = true;
            },
        );

        $this->component->raiseEvent();

        self::assertTrue(
            $eventRaised,
            'Wildcard closure handler should be triggered.',
        );

        // raise event w/o parameters
        $eventRaised = false;

        $this->component->on(
            'group.*',
            static function ($event) use (&$eventRaised): void {
                $eventRaised = true;
            },
        );
        $this->component->trigger('group.test');

        self::assertTrue(
            $eventRaised,
            'Should invoke handler when event is triggered without parameters.',
        );
    }

    public function testHasEventHandlers(): void
    {
        self::assertFalse(
            $this->component->hasEventHandlers('click'),
            "Should have no handlers for 'click' initially.",
        );

        $this->component->on(
            'click',
            'foo',
        );

        self::assertTrue(
            $this->component->hasEventHandlers('click'),
            "Should have handlers for 'click' after on.",
        );

        $this->component->on(
            '*',
            'foo',
        );

        self::assertTrue(
            $this->component->hasEventHandlers('some'),
            'Should match any event name.',
        );
    }

    public function testStopEvent(): void
    {
        $component = new NewComponent();

        $component->on(
            'click',
            static function (Event $event): void {
                $event->sender->eventHandled = true;
                $event->handled = true;
            },
        );
        $component->on(
            'click',
            [$this->component, 'myEventHandler'],
        );

        $component->raiseEvent();

        self::assertTrue(
            $component->eventHandled,
            'Should have been executed.',
        );
        self::assertFalse(
            $this->component->eventHandled,
            'Should not execute second handler after event was stopped.',
        );
    }

    public function testAttachBehavior(): void
    {
        $component = new NewComponent();

        self::assertFalse(
            $component->hasProperty('p'),
            "Should not have property 'p' before attaching behavior.",
        );
        self::assertFalse(
            $component->behaviorCalled,
            'Should not be called initially.',
        );
        self::assertNull(
            $component->getBehavior('a'),
            'Should not exist initially.',
        );

        $behavior = new NewBehavior();

        $component->attachBehavior('a', $behavior);

        self::assertSame(
            $behavior,
            $component->getBehavior('a'),
            'Should be the attached instance.',
        );
        self::assertTrue(
            $component->hasProperty('p'),
            "Should have property 'p' after attaching behavior.",
        );

        $component->test();

        self::assertTrue(
            $component->behaviorCalled,
            'Should be called via component.',
        );
        self::assertSame(
            $behavior,
            $component->detachBehavior('a'),
            'Should be the same instance.',
        );
        self::assertFalse(
            $component->hasProperty('p'),
            "Should not have property 'p' after detaching behavior.",
        );

        try {
            $component->test();

            self::fail('Expected exception ' . UnknownMethodException::class . " wasn't thrown");
        } catch (UnknownMethodException $e) {
            // Expected
        }

        $component = new NewComponent();

        // @phpstan-ignore property.notFound
        $component->{'as b'} = ['class' => NewBehavior::class];

        self::assertInstanceOf(
            NewBehavior::class,
            $component->getBehavior('b'),
            'Should be an instance of NewBehavior.',
        );
        self::assertTrue(
            $component->hasProperty('p'),
            "Should have property 'p' via behavior 'b'.",
        );

        $component->test();

        self::assertTrue(
            $component->behaviorCalled,
            "Should be called via 'as' property syntax.",
        );

        // @phpstan-ignore property.notFound
        $component->{'as c'} = ['__class' => NewBehavior::class];

        self::assertNotNull(
            $component->getBehavior('c'),
            "Should be attached via '__class' config.",
        );

        // @phpstan-ignore property.notFound
        $component->{'as d'} = [
            '__class' => NewBehavior2::class,
            'class' => NewBehavior::class,
        ];

        self::assertInstanceOf(
            NewBehavior2::class,
            $component->getBehavior('d'),
            "Should use '__class' over 'class'.",
        );

        // CVE-2024-4990
        try {
            // since the property contains a space in its name, the error cannot be resolved using PHPDoc.
            // @phpstan-ignore property.notFound
            $component->{'as e'} = [
                '__class' => 'NotExistsBehavior',
                'class' => NewBehavior::class,
            ];

            self::fail('Expected exception ' . InvalidConfigException::class . " wasn't thrown");
        } catch (InvalidConfigException $e) {
            self::assertSame(
                'Class is not of type yii\base\Behavior or its subclasses',
                $e->getMessage(),
                'Should indicate invalid behavior class.',
            );
        }

        $component = new NewComponent();

        $component->{'as f'} = static fn() => new NewBehavior();

        self::assertNotNull(
            $component->getBehavior('f'),
            'Should be attached via closure factory.',
        );
    }

    public function testAttachBehaviors(): void
    {
        $component = new NewComponent();

        self::assertNull(
            $component->getBehavior('a'),
            'Should not exist initially.',
        );
        self::assertNull(
            $component->getBehavior('b'),
            'Should not exist initially.',
        );

        $behavior = new NewBehavior();

        $component->attachBehaviors(
            [
                'a' => $behavior,
                'b' => $behavior,
            ],
        );

        self::assertSame(
            ['a' => $behavior, 'b' => $behavior],
            $component->getBehaviors(),
            'Both behaviors should be attached.',
        );
    }

    public function testDetachBehavior(): void
    {
        $component = new NewComponent();
        $behavior = new NewBehavior();

        $component->attachBehavior('a', $behavior);

        self::assertSame(
            $behavior,
            $component->getBehavior('a'),
            'Should be the attached instance.',
        );

        $detachedBehavior = $component->detachBehavior('a');

        self::assertSame(
            $detachedBehavior,
            $behavior,
            'Detached behavior should be the same instance.',
        );
        self::assertNull(
            $component->getBehavior('a'),
            "Should be 'null' after detaching.",
        );

        $detachedBehavior = $component->detachBehavior('z');

        self::assertNull(
            $detachedBehavior,
            "Should return 'null'.",
        );
    }

    public function testDetachBehaviors(): void
    {
        $component = new NewComponent();
        $behavior = new NewBehavior();

        $component->attachBehavior('a', $behavior);

        self::assertSame(
            $behavior,
            $component->getBehavior('a'),
            'Should be attached.',
        );

        $component->attachBehavior('b', $behavior);

        self::assertSame(
            $behavior,
            $component->getBehavior('b'),
            'Should be attached.',
        );

        $component->detachBehaviors();

        self::assertNull(
            $component->getBehavior('a'),
            "Should be 'null' after detach behaviors.",
        );
        self::assertNull(
            $component->getBehavior('b'),
            "Should be 'null' after detach behaviors.",
        );
    }

    public function testSetReadOnlyProperty(): void
    {
        self::expectException(
            InvalidCallException::class,
        );
        self::expectExceptionMessage(
            'Setting read-only property: yiiunit\data\base\NewComponent::object',
        );

        $this->component->object = 'z';
    }

    public function testSetPropertyOfBehavior(): void
    {
        self::assertNull(
            $this->component->getBehavior('a'),
            'Should not exist initially.',
        );

        $behavior = new NewBehavior();

        $this->component->attachBehaviors(['a' => $behavior]);

        $this->component->p = 'Yii is cool.';

        /** @var NewBehavior $aBehavior */
        $aBehavior = $this->component->getBehavior('a');

        self::assertSame(
            'Yii is cool.',
            $aBehavior->p,
            'Should reflect the value set via component.',
        );
    }

    public function testSettingBehaviorWithSetter(): void
    {
        $behaviorName = 'foo';

        self::assertNull(
            $this->component->getBehavior($behaviorName),
            'Should not exist initially.',
        );

        $p = "as {$behaviorName}";

        // @phpstan-ignore property.notFound
        $this->component->$p = NewBehavior::class;

        self::assertSame(
            NewBehavior::class,
            get_class($this->component->getBehavior($behaviorName)),
            'Should be of class NewBehavior after setter assignment.',
        );
    }

    public function testWriteOnlyProperty(): void
    {
        self::expectException(
            InvalidCallException::class,
        );
        self::expectExceptionMessage(
            'Getting write-only property: yiiunit\data\base\NewComponent::writeOnly',
        );

        $this->component->writeOnly;
    }

    public function testSuccessfulMethodCheck(): void
    {
        self::assertTrue(
            $this->component->hasMethod('hasProperty'),
            "Should return 'true' for 'hasProperty'.",
        );
    }

    public function testTurningOffNonExistingBehavior(): void
    {
        self::assertFalse(
            $this->component->hasEventHandlers('foo'),
            "Should have no handlers for 'foo' event.",
        );
        self::assertFalse(
            $this->component->off('foo'),
            "Should return 'false' for event with no handlers.",
        );
    }

    public function testDetachNotAttachedHandler(): void
    {
        $obj = new NewComponent();

        $obj->on(
            'test',
            [$this, 'handler'],
        );

        self::assertFalse(
            $obj->off(
                'test',
                [$this, 'handler2']
            ),
            'Trying to remove the handler that is not attached',
        );
        self::assertTrue(
            $obj->off('test', [$this, 'handler']),
            'Trying to remove the attached handler',
        );
    }

    /**
     * @see https://github.com/yiisoft/yii2/issues/17223
     */
    public function testEventClosureDetachesItself(): void
    {
        $obj = require __DIR__ . '/stub/AnonymousComponentClass.php';

        $obj->trigger('barEventOnce');

        self::assertSame(
            1,
            $obj->foo,
            "Should be '1' after first trigger.",
        );

        $obj->trigger('barEventOnce');

        self::assertSame(
            1,
            $obj->foo,
            "Should still be '1' after second trigger since handler detached itself.",
        );
    }

    public function testGetPropertyViaBehavior(): void
    {
        $this->component->attachBehavior('a', new NewBehavior());

        $this->component->p = 'behavior value';

        self::assertSame(
            'behavior value',
            $this->component->p,
            'Should get property via attached behavior.',
        );
    }

    public function testHasMethodViaBehavior(): void
    {
        self::assertFalse(
            $this->component->hasMethod('test'),
            'Should return false for method before attaching behavior.',
        );

        $this->component->attachBehavior('a', new NewBehavior());

        self::assertTrue(
            $this->component->hasMethod('test'),
            'Should return true for method after attaching behavior.',
        );
    }

    public function testHasMethodWithoutBehaviors(): void
    {
        self::assertFalse(
            $this->component->hasMethod('test', false),
            "Should return false for behavior method when checkBehaviors is 'false'.",
        );
        self::assertTrue(
            $this->component->hasMethod('raiseEvent', false),
            "Should return true for own method when checkBehaviors is 'false'.",
        );
    }

    public function testOnPrependPlainHandler(): void
    {
        $order = [];

        $this->component->on(
            'click',
            static function () use (&$order): void {
                $order[] = 'first';
            },
        );
        $this->component->on(
            'click',
            static function () use (&$order): void {
                $order[] = 'prepended';
            },
            null,
            false
        );

        $this->component->trigger('click');

        self::assertSame(
            ['prepended', 'first'],
            $order,
            'Should execute before the first registered handler.',
        );
    }

    public function testOnPrependWildcardHandler(): void
    {
        $order = [];

        $this->component->on(
            'click.*',
            static function () use (&$order): void {
                $order[] = 'first';
            },
        );
        $this->component->on(
            'click.*',
            static function () use (&$order): void {
                $order[] = 'prepended';
            },
            null,
            false,
        );
        $this->component->trigger('click.test');

        self::assertSame(
            ['prepended', 'first'],
            $order,
            'Should execute before the first registered handler.',
        );
    }

    public function testSetBehaviorInstanceViaProperty(): void
    {
        $component = new NewComponent();
        $behavior = new NewBehavior();

        $p = 'as myBehavior';

        // @phpstan-ignore property.notFound
        $component->$p = $behavior;

        self::assertSame(
            $behavior,
            $component->getBehavior('myBehavior'),
            "Should be attached via 'as' property syntax.",
        );
    }

    public function testBehaviorsDeclaredInMethod(): void
    {
        $component = new ComponentWithBehaviors();

        $component->ensureBehaviors();
        $behaviors = $component->getBehaviors();

        self::assertCount(
            2,
            $behaviors,
            'Should have two behaviors declared in behaviors method.',
        );
        self::assertInstanceOf(
            NewBehavior::class,
            $behaviors['named'],
            'Should be an instance of NewBehavior.',
        );
        self::assertInstanceOf(
            NewBehavior2::class,
            $behaviors[0],
            'Should be an instance of NewBehavior2.',
        );
    }

    public function testAttachBehaviorReplacesExisting(): void
    {
        $behavior1 = new NewBehavior();
        $behavior2 = new NewBehavior();

        $this->component->attachBehavior('a', $behavior1);

        self::assertSame(
            $behavior1,
            $this->component->getBehavior('a'),
            'Should be attached.',
        );

        $this->component->attachBehavior('a', $behavior2);

        self::assertSame(
            $behavior2,
            $this->component->getBehavior('a'),
            'Should replace the first one under the same name.',
        );
    }
}
