<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\behaviors;

use yii\base\Behavior;
use yii\base\Event;
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
 *     return [
 *         'timestamp' => ['class' => 'yii\behaviors\AutoTimestamp'],
 *     ];
 * }
 * ~~~
 *
 * By default, AutoTimestamp will fill the `created_at` attribute with the current timestamp
 * when the associated AR object is being inserted; it will fill the `updated_at` attribute
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
	 * and the array values are the corresponding attribute(s) to be updated. You can use a string to represent
	 * a single attribute, or an array to represent a list of attributes.
	 * The default setting is to update the `created_at` attribute upon AR insertion,
	 * and update the `updated_at` attribute upon AR updating.
	 */
	public $attributes = [
		ActiveRecord::EVENT_BEFORE_INSERT => 'created_at',
		ActiveRecord::EVENT_BEFORE_UPDATE => 'updated_at',
	];
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
		$events = $this->attributes;
		foreach ($events as $i => $event) {
			$events[$i] = 'updateTimestamp';
		}
		return $events;
	}

	/**
	 * Updates the attributes with the current timestamp.
	 * @param Event $event
	 */
	public function updateTimestamp($event)
	{
		$attributes = isset($this->attributes[$event->name]) ? (array)$this->attributes[$event->name] : [];
		if (!empty($attributes)) {
			$timestamp = $this->evaluateTimestamp();
			foreach ($attributes as $attribute) {
				$this->owner->$attribute = $timestamp;
			}
		}
	}

	/**
	 * Gets the current timestamp.
	 * @return mixed the timestamp value
	 */
	protected function evaluateTimestamp()
	{
		if ($this->timestamp instanceof Expression) {
			return $this->timestamp;
		} elseif ($this->timestamp !== null) {
			return call_user_func($this->timestamp);
		} else {
			return time();
		}
	}

	/**
	 * Updates a timestamp attribute to the current timestamp.
	 *
	 * ```php
	 * $model->touch('lastVisit');
	 * ```
	 * @param string $attribute the name of the attribute to update.
	 */
	public function touch($attribute)
	{
		$timestamp = $this->evaluateTimestamp();
		$this->owner->updateAttributes([$attribute => $timestamp]);
	}
}
