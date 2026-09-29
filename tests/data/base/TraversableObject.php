<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

declare(strict_types=1);

namespace yiiunit\data\base;

use Iterator;
use Countable;
use ReturnTypeWillChange;
use Exception;

/**
 * TraversableObject
 * Object that implements `\Traversable` and `\Countable`, but counting throws an exception;
 * Used for testing support for traversable objects instead of arrays.
 * @author Sam Mousa <sam@mousa.nl>
 * @since 2.0.8
 */
class TraversableObject implements Iterator, Countable
{
    protected $data;
    private $position = 0;

    public function __construct(array $array)
    {
        $this->data = $array;
    }

    /**
     * @throws Exception
     * @since 5.1.0
     */
    #[ReturnTypeWillChange]
    public function count()
    {
        throw new Exception('Count called on object that should only be traversed.');
    }

    /**
     * {@inheritdoc}
     */
    #[ReturnTypeWillChange]
    public function current()
    {
        return $this->data[$this->position];
    }

    /**
     * {@inheritdoc}
     */
    public function next(): void
    {
        $this->position++;
    }

    /**
     * {@inheritdoc}
     */
    public function key(): int
    {
        return $this->position;
    }

    /**
     * {@inheritdoc}
     */
    public function valid(): bool
    {
        return array_key_exists($this->position, $this->data);
    }

    /**
     * {@inheritdoc}
     */
    public function rewind(): void
    {
        $this->position = 0;
    }
}
