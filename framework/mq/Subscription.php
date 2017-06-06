<?php

namespace yii\mq;

/**
 * The Subscription class represents a relation between a subscribent and a queue.
 */
class Subscription
{
	/**
	 * @var string $subscriber_id Unique identifier of the subscriber (user) to which messages will be delivered to
	 */
	public $subscriber_id;
	/**
	 * @var string $created_on Date and time when the message has been created, in Y-m-d H:i:s format
	 */
	public $created_on;
	/**
	 * @var string $label Human readable label for the subscription
	 */
	public $label;
	/**
	 * @var array $categories List of categories of messages (e.g. 'system.web') that should be delivered to this subscription
	 */
	public $categories = array();
	/**
	 * @var array $exceptions List of categories of messages (e.g. 'system.web') that should NOT be delivered to this subscription
	 */
	public $exceptions = array();

	/**
	 * Sets the properties values in a massive way.
	 * @param array $values properties values (name=>value) to be set.
	 */
	public function setAttributes($values)
	{
		if(!is_array($values))
			return;
		foreach($values as $name=>$value) {
			$this->$name=$value;
		}
	}

	/**
	 * Tests if specified category matches any category and doesn't match any exception of this subscription.
	 *
	 * @param string $category
	 * @return boolean
	 */
	public function matchCategory($category)
	{
		$result = empty($this->categories);
		foreach($this->categories as $allowedCategory) {
			if ($this->categoryContains($allowedCategory, $category))
				$result = true;
		}
		foreach($this->exceptions as $deniedCategory) {
			if ($this->categoryContains($deniedCategory, $category))
				$result = false;
		}
		return $result;
	}

	/**
	 * Checkes if category $a contains $b.
	 * @param string $a category name that can contain wildcards 
	 * @param string $b category name without wildcards
	 */
	private function categoryContains($container, $category)
	{
		if (($c=rtrim($container,'*'))!==$container) {
			if (($c2=rtrim($c,'.'))!==$c) {
				if ($c2 == $category || strpos($category, $c2.'.') === 0)
					return true;
			} elseif (strpos($category, $c) === 0) {
				return true;
			}
		} elseif ($container == $category) {
			return true;
		}
		return false;
	}
}
