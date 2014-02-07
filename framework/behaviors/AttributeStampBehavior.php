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
 * AttributeStampBehavior is used to fill an ActiveRecord's object attributes with arbitrary data when specified events occurs.
 *
 * AttributeStampBehavior is mainly used as a base class for other behaviors, like `TimestampBehavior` and `BlameableBehavior`.
 * Check `yii\db\BaseActiveRecord` for available events.
 * Note that you need to create the columns corresponding to the attributes you want to fill before using this behavior.
 * In order to attach this behavior to an AR model, you need to write the following method (with your attributes customization) 
 * in the class representing the model:
 *
 * ~~~
 * class BlogPost extends \yii\db\ActiveRecord
 * 
 *     // ...
 *     // ...
 *     public function behaviors()
 *     {
 *         return [
 *             'attributeStamp' => [
 *                 'class' => 'yii\behaviors\AttributeStampBehavior',
 *                 'attributes' => [
 *                     ActiveRecord::EVENT_BEFORE_INSERT => [
 *                         // using some value directly
 *                         'attribute1' => 'Some Value',
 *                         // using an arbitrary value or anonymous function that returns the desired value
 *                         'attribute2' => function() {
 *                             return date('d/m/Y');
 *                         }
 *                     ],
 *                     ActiveRecord::EVENT_BEFORE_UPDATE => [
 *                         'attribute1' => 'Back to square one',
 *                     ],
 *                 ],
 *             ],
 *         ];
 *     }
 * ~~~
 *
 * @author Luciano Baraglia <luciano.baraglia@gmail.com>
 * @since 2.0
 */
class AttributeStampBehavior extends Behavior
{
	/**
	 * @var array list of attributes that are to be automatically filled when specified `ActiveRecord` events occurs.
	 * The array keys are the ActiveRecord events upon which the attributes are to be filled with, and the array values 
	 * are the corresponding attribute(s) to be updated. 
	 * Each attribute value could be any arbitrary data and even a anonymous function that returns the desired data.
	 * By default, AttributeStampBehavior won't fill anything unless you specify and customize the events, attributes and the data.
	 * 
	 */
	public $attributes = [];
	
	/**
	 * @var mixed|\Closure The value that will be used to fill each attribute.
	 * This can be any value, even an anonymous function that returns the desired value.
	 * If `Closure` is use instead, the return value of the anonymouse function will be used.
	 *  
	 */
	protected $attributeValue = null;
	
	/**
	 * @var mixed The value that will be used by default to fill each attribute.
	 * Each child class should override this value, and maybe in the `init()` implementation if the vlaue need some process.
	 *  
	 */
	protected $defaultValue = null;


	/**
	 * Declares event handlers for the [[owner]]'s events.
	 * @return array events (array keys) and the corresponding event handler methods (array values).
	 */
	public function events()
	{
		$events = $this->attributes;
		foreach ($events as $i => $event) {
			$events[$i] = 'stampAttribute';
		}
		return $events;
	}

	/**
	 * Updates the model attributes using the given value.
	 * @param Event $event
	 */
	public function stampAttribute($event)
	{
		$model = $this->owner;
		$attributes = $this->attributes[$event->name];
		
		if ($attributes !== null) {
			foreach ($attributes as $key => $val) {
				if (is_numeric($key)) { // e.g. `['attribute1',]`
					$attribute = $val;
				} else { // e.g. `['attribute1' => 'val1',]`
					$attribute = $key;
				}
				// this need to be done because some child classes won't receive `$val` to process
				// that classes only will use `$defaultValue`
				$this->attributeValue = $val;
				$model->$attribute = $this->processValue();
			}
		}
	}
	
	/**
	 * Process the value for each specified  attribute, defaults to `$this->attributeValue`.
	 * If child class only allow using the default value, this method should be overriden accordingly.
	 * @param mixed $value
	 * @return mixed the value after processing
	 */
	protected function processValue()
	{
		$value = $this->attributeValue;
		if ($value instanceof \Closure) {
			return call_user_func($value);
		} elseif ($value !== null) {
			return $value;
		}
		return $this->defaultValue;
	}
}
