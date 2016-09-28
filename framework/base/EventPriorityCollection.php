<?php
namespace yii\base;

/**
 * Short description for file
 *
 * A simple Priority Queue Class that solves the two following problems:
 * a)
 * b)
 *
 * @author Ioannis Bekiaris <info@ibekiaris.me>
 */
class EventPriorityCollection implements \IteratorAggregate, \Countable
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

	public function insert($data, $priority = 1)
	{
		if (is_int($priority)) {
			$priority = [$priority, $this->priorityCounter--];
		}

		$this->calculateMaxPriority($priority);

		$this->items[] = [
			'data' => $data,
			'priority' => $priority,
			'id' => $data[0]
		];

		$this->getInnerQueue()->insert($data, $priority);
	}

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

	public function count()
	{
		return count($this->items);
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

	public function getMaxPriority()
	{
		return $this->maxPriority;
	}

	protected function calculateMaxPriority($priority)
	{
		if(is_array($priority)) {
			$priority = $priority[0];
		}

		$this->maxPriority = ($this->maxPriority < $priority) ? $priority : $this->maxPriority;
	}
}
