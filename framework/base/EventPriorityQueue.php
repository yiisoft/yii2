<?php
namespace yii\base;

use Traversable;

/**
 * Short description for file
 *
 * A simple Priority Queue Class that solves the two following problems:
 * a)
 * b)
 *
 * @author Ioannis Bekiaris <info@ibekiaris.me>
 */
class EventPriorityQueue implements \IteratorAggregate
{
	/**
	 * @var int
	 */
	protected $priorityCounter = PHP_INT_MAX;

	/**
	 * @var \SplPriorityQueue
	 */
	protected $innerQueue;

	/**
	 * @var array
	 */
	protected $items = [];

	public function insert($data, $priority)
	{

	}

	public function getIterator()
	{
		return clone $this->getInnerQueue();
	}

	public function getInnerQueue()
	{
		if (! $this->innerQueue) {
			$this->innerQueue = new \SplPriorityQueue();
		}

		return $this->innerQueue;
	}
}
