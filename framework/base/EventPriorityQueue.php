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
		if (is_int($priority)) {
			$priority = [$priority, $this->priorityCounter--];
		}

		$this->items[] = [
			'data' => $data,
			'priority' => $priority,
			'id' => $data[0]
		];

		$this->getInnerQueue()->insert($data, $priority);
	}

	public function remove($data)
	{
		$removeStatus = false;
		foreach ($this->items as $itemKey => $item) {
			if ($data === $item['id']) {
				unset($this->items[$itemKey]);
				$removeStatus = false;
				break;
			}
		}

		$this->innerQueue = null;
		$this->getInnerQueue();

		if (count($this->items)) {
			foreach ($this->items as $item) {
				$this->innerQueue->insert($item['data'], $item['priority']);
			}
		}

		return $removeStatus;
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
