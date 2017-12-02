<?php
namespace yii\base;

/**
 * A simple Priority Queue class that used for Events' Handlers
 * and solves the two following problems regarding SplPriorityQueue:
 *
 * - By default it does not work as a Queue
 * - You can't iterate this more than once
 *
 * @author Ioannis Bekiaris <info@ibekiaris.me>
 */
class EventPriorityQueue implements \IteratorAggregate, \Countable
{
    /**
     * A simple counter which ensures that
     * EventPriorityQueue is working as a queue (FIFO)
     * for handlers with the same priority
     *
     * @see EventPriorityQueue::insert()
     *
     * @var int
     */
    protected $priorityCounter = PHP_INT_MAX;

    /**
     * The highest priority among
     * Event's Handlers.
     *
     * The Handler with the highest priority
     * is the one invoked first
     *
     * @var int
     */
    protected $maxPriority = 0;

    /**
     * An SplPriorityQueue used as the main data structure
     * in order to store handlers with priority given
     *
     * @var \SplPriorityQueue
     */
    protected $innerQueue;

    /**
     * Although we use SplPriorityQueue as the main data structure
     * we use also this simple array in order to remove items from Queue
     *
     * @see EventPriorityQueue::remove()
     *
     * @var array
     */
    protected $items = [];

    /**
     * Insert data into queue with the given priority
     *
     * @param array $data
     * an array [
     *   0 => callable $handler the event handler
     *   1 => mixed the data to be passed to the event handler when the event is triggered.
     *        When the event handler is invoked, this data can be accessed via [[Event::data]].
     * ]
     * @param mixed $priority
     */
    public function insert(array $data, $priority = 1)
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
     * Remove the item from the queue by the given
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
        $this->maxPriority = 0;
        $this->getInnerQueue();

        if (count($this->items)) {
            foreach ($this->items as $item) {
                $priority = $item['priority'];
                $this->decideMaxPriority($priority);
                $this->innerQueue->insert($item['data'], $priority);
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
        if (!$this->innerQueue) {
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
        if (is_array($priority)) {
            $priority = $priority[0];
        }

        $this->maxPriority = ($this->maxPriority < $priority) ? $priority : $this->maxPriority;
    }
}
