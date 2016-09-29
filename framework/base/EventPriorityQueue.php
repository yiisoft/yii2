<?php
namespace yii\base;

/**
 * A simple Priority Queue class that used for Events' Handlers
 * and solves the two following problems regarding SplPriorityQueue:
 *
 * a) By default it does not work as a Queue
 * b) You can't iterate this more than one Times
 *
 * @author Ioannis Bekiaris <info@ibekiaris.me>
 */
class EventPriorityQueue implements \IteratorAggregate, \Countable
{
	/**
	 * @var int
	 */
	protected $priorityCounter = PHP_INT_MAX;

	/**
	 * @var int
	 */
	protected $maxPriority;

	/**
	 * @var \SplPriorityQueue
	 */
	protected $innerQueue;

	/**
	 * @var array
	 */
	protected $items = [];

	/**
	 * Insert data into queue with given priority
	 *
	 * @param $data
	 * @param mixed $priority
	 */
	public function insert($data, $priority = 1)
	{
		if (is_int($priority)) {
			$priority = [$priority, $this->priorityCounter--];
		}

		$this->decideMaxPriority($priority);

		$this->items[] = [
			'data' => $data,
			'priority' => $priority,
			'id' => $data[0]
		];

		$this->getInnerQueue()->insert($data, $priority);
	}

	/**
	 * Remove item from queue by given
	 * handler. This method is the reason why we
	 * use $items params because SplPriorityQueue
	 * has not this functionality.
	 *
	 * So we remove the Item from $items and
	 * we repopulate the queue.
	 *
	 * @param $handler
	 *
	 * @return bool
	 */
	public function remove($handler)
	{
		$removeStatus = false;

		foreach ($this->items as $itemKey => $item) {
			if ($handler === $item['id']) {
				unset($this->items[$itemKey]);
				$removeStatus = true;
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

	/**
	 * @return int
	 */
	public function count()
	{
		return count($this->items);
	}

	/**
	 * @return \SplPriorityQueue
	 */
	public function getIterator()
	{
		return clone $this->getInnerQueue();
	}

	/**
	 * @return \SplPriorityQueue
	 */
	public function getInnerQueue()
	{
		if (! $this->innerQueue) {
			$this->innerQueue = new \SplPriorityQueue();
		}

		return $this->innerQueue;
	}

	/**
	 * Returns the max priority
	 * of Items in the queue
	 *
	 * @return int
	 */
	public function getMaxPriority()
	{
		return $this->maxPriority;
	}

	/**
	 * Compares the given priority to the
	 * current max priority and sets new
	 * max priority if needed
	 *
	 * @param $priority
	 *
	 * @return void
	 */
	protected function decideMaxPriority($priority)
	{
		if(is_array($priority)) {
			$priority = $priority[0];
		}

		$this->maxPriority = ($this->maxPriority < $priority) ? $priority : $this->maxPriority;
	}
}
