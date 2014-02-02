<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\behaviors;

use Yii;
use yii\base\Behavior;
use yii\base\Event;
use yii\db\ActiveRecord;

/**
 * Blame is used to fill automatically the attributes representing the user creating and/or updating the record.
 *
 * Blame fills these attributes when the associated AR model is being inserted or updated.
 * Note that you need to create the create and update columns by hand before using this behavior.
 * In order to attach this behavior to an AR model, write the following method in that class:
 *
 * ~~~
 * public function behaviors()
 * {
 *     return [
 *         'blame' => [
 *             'class' => 'yii\behaviors\Blame',
 *         ],
 *     ];
 * }
 * ~~~
 * 
 * And here a more complete example customizing several properties:
 * 
 * ~~~
 * public function behaviors()
 * {
 *     return [
 *         'blame' => [
 *             'class' => 'yii\behaviors\Blame',
 *             // This results in using a custom column name and not saving update information
 *             'attributes' => [
 *                  \Yii\db\ActiveRecord::EVENT_BEFORE_INSERT => 'creation_information',
 *                  \Yii\db\ActiveRecord::EVENT_BEFORE_UPDATE => null,
 *              ],
 *              // Using an arbitrary value or anonymous function that returns the desired value
 *              'attributeValue' => function() {
 *                  return 'Created by: ' . \Yii::$app->user->getIdentity()->username . ' at ' . date('Y-m-d H:i:s');
 *              }
 *          ],
 *     ];
 * }
 * ~~~
 *
 * By default, Blame will fill the `created_by` attribute with the currently logged in user's ID
 * when the associated AR object is being inserted; and the `updated_by` attribute when the AR object is being updated.
 *
 * @author Luciano Baraglia <luciano.baraglia@gmail.com>
 * @since 2.0
 */
class Blame extends Behavior
{
	/**
	 * @var array list of attributes that are to be automatically filled with the logged in user's ID.
	 * The array keys are the ActiveRecord events upon which the attributes are to be filled,
	 * and the array values are the corresponding attribute to be updated.
	 * The default setting is to update the `created_by` attribute upon AR insertion,
	 * and update the `updated_by` attribute upon AR update.
	 * Also `null` could be specified for any of the attribute values, so it won't be filled in (e.g. you
	 * want to log only information about who creates the record).
	 */
	public $attributes = [
		ActiveRecord::EVENT_BEFORE_INSERT => 'created_by',
		ActiveRecord::EVENT_BEFORE_UPDATE => 'updated_by',
	];
	/**
	 * @var mixed|\Closure The value that will be used to fill each attribute.
	 * This can be any value, even an anonymous function that returns the desired value.
	 * If not set, `Yii::$app->user->id` will be used.
	 * If `Closure` is use instead, any arbitrary value can be set, for example:
	 *
	 * ~~~
	 * public function behaviors()
	 * {
	 *     return [
	 *         'blame' => [
	 *             'class' => 'yii\behaviors\Blame',
	 *             'attributeValue' => function() {
	 *                 return 'By: ' . \Yii::$app->user->getIdentity()->username . ' at ' . date('Y-m-d H:i:s');
	 *             }
	 *         ],
	 *     ];
	 * }
	 * ~~~
	 *  
	 */
	public $attributeValue;


	/**
	 * Declares event handlers for the [[owner]]'s events.
	 * @return array events (array keys) and the corresponding event handler methods (array values).
	 */
	public function events()
	{
		$events = $this->attributes;
		foreach ($events as $i => $event) {
			$events[$i] = 'blameUser';
		}
		return $events;
	}

	/**
	 * Updates the attributes using the given value, defaults to logged in user's ID.
	 * @param Event $event
	 */
	public function blameUser($event)
	{
		$model = $this->owner;
		$attribute = $this->attributes[$event->name];
		$value = Yii::$app->user->id;
		
		if ($attribute !== null) {
			if ($this->attributeValue instanceof \Closure) {
				$value = call_user_func($this->attributeValue);
			} elseif ($this->attributeValue != $value) {
				$value = $this->attributeValue;
			}
			$model->$attribute = $value;
		}
	}
}
