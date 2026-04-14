<?php

declare(strict_types=1);

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\data\base;

use yii\base\Component;
use yii\base\Event;

/**
 * Stub {@see Component} with magic properties, events, and method routing for Component tests.
 *
 * @property mixed $Text
 * @property mixed $text
 * @property-read self $object
 * @property-read callable $execute
 * @property-read array<array-key, mixed> $items
 * @property-write mixed $writeOnly
 *
 * @mixin NewBehavior We use `mixin` here to avoid PHPStan errors when testing `attachBehavior`.
 */
final class NewComponent extends Component
{
    public $behaviorCalled = false;
    public $content;
    public $event;

    public $eventHandled = false;
    private $_items = [];
    private $_object = null;
    private $_text = 'default';

    public function getExecute()
    {
        return function ($param) {
            return $param * 2;
        };
    }

    public function getItems()
    {
        return $this->_items;
    }

    public function getObject()
    {
        if (!$this->_object) {
            $this->_object = new self();
            $this->_object->_text = 'object text';
        }

        return $this->_object;
    }

    public function getText()
    {
        return $this->_text;
    }

    public function myEventHandler($event): void
    {
        $this->eventHandled = true;
        $this->event = $event;
    }

    public function raiseEvent(): void
    {
        $this->trigger('click', new Event());
    }

    public function setText($value): void
    {
        $this->_text = $value;
    }

    public function setWriteOnly()
    {
    }
}
