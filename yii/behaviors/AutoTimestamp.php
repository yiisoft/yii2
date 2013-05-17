<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\behaviors;

use yii\base\Behavior;
use yii\db\Expression;
use yii\db\ActiveRecord;

/**
 * AutoTimestamp will automatically fill the attributes about creation time and updating time.
 *
 * AutoTimestamp fills the attributes when the associated AR model is being inserted or updated.
 * You may specify an AR to use this behavior like the following:
 *
 * ~~~
 * public function behaviors()
 * {
 *     return array(
 *         'timestamp' => array(
 *             'class' => 'yii\behaviors\AutoTimestamp',
 *         ),
 *     );
 * }
 * ~~~
 *
 * By default, AutoTimestamp will fill the `insert_time` attribute with the current timestamp
 * when the associated AR object is being inserted; it will fill the `update_time` attribute
 * with the timestamp when the AR object is being updated.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class AutoTimestamp extends Behavior
{
	/**
	 * @var array list of attributes that are to be automatically filled with timestamps.
	 * The array keys are the ActiveRecord events upon which the attributes are to be filled with timestamps,
	 * and the array values are the corresponding attribute to be updated. You can use a string to represent
	 * a single attribute, or an array to represent a list of attributes.
	 * The default setting is to update the `insert_time` attribute upon AR insertion,
	 * and update the `update_time` attribute upon AR updating.
	 */
	public $attributes = array(
		ActiveRecord::EVENT_BEFORE_INSERT => 'insert_time',
		ActiveRecord::EVENT_BEFORE_UPDATE => 'update_time',
	);
	/**
	 * @var \Closure|Expression The expression that will be used for generating the timestamp.
	 * This can be either an anonymous function that returns the timestamp value,
	 * or an [[Expression]] object representing a DB expression (e.g. `new Expression('NOW()')`).
	 * If not set, it will use the value of `time()` to fill the attributes.
	 */
	public $timestamp;


	/**
	 * Declares event handlers for the [[owner]]'s events.
	 * @return array events (array keys) and the corresponding event handler methods (array values).
	 */
	public function events()
	{
		$events = array();
		$behavior = $this;
		foreach ($this->attributes as $event => $attributes) {
			if (!is_array($attributes)) {
				$attributes = array($attributes);
			}
			$events[$event] = function () use ($behavior, $attributes) {
				$behavior->updateTimestamp($attributes);
			};
		}
		return $events;
	}

	/**
	 * Updates the attributes with the current timestamp.
	 * @param array $attributes list of attributes to be updated.
	 */
	public function updateTimestamp($attributes)
	{
		foreach ($attributes as $attribute) {
			$this->owner->$attribute = $this->evaluateTimestamp($attribute);
		}
	}

	/**
	 * Gets the appropriate timestamp for the specified attribute.
	 * @param string $attribute attribute name
	 * @return mixed the timestamp value
	 */
	protected function evaluateTimestamp($attribute)
	{
		if ($this->timestamp instanceof Expression) {
			return $this->timestamp;
		} elseif ($this->timestamp !== null) {
			return call_user_func($this->timestamp);
		} else {
			return time();
		}
	}
}
