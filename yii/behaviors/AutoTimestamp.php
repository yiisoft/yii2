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
 * By default, the attribute for keeping the creation time is named as "create_time", and the attribute
 * for updating time is "update_time". You may customize the names via [[createAttribute]] and [[updateAttribute]].
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class AutoTimestamp extends Behavior
{
	/**
	 * @var string The name of the attribute to store the creation time.  Set to null to not
	 * use a timestamp for the creation attribute.  Defaults to 'create_time'
	 */
	public $createAttribute = 'create_time';
	/**
	 * @var string The name of the attribute to store the modification time.  Set to null to not
	 * use a timestamp for the update attribute.  Defaults to 'update_time'
	 */
	public $updateAttribute = 'update_time';
	/**
	 * @var string|Expression The expression that will be used for generating the timestamp.
	 * This can be either a string representing a PHP expression (e.g. 'time()'),
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
		return array(
			ActiveRecord::EVENT_BEFORE_INSERT => 'beforeInsert',
			ActiveRecord::EVENT_BEFORE_UPDATE => 'beforeUpdate',
		);
	}

	/**
	 * This is the event handler for the "beforeInsert" event of the associated AR object.
	 */
	public function beforeInsert()
	{
		if ($this->createAttribute !== null) {
			$this->owner->{$this->createAttribute} = $this->evaluateTimestamp($this->createAttribute);
		}
	}

	/**
	 * This is the event handler for the "beforeUpdate" event of the associated AR object.
	 */
	public function beforeUpdate()
	{
		if ($this->updateAttribute !== null) {
			$this->owner->{$this->updateAttribute} = $this->evaluateTimestamp($this->updateAttribute);
		}
	}

	/**
	 * Gets the appropriate timestamp depending on the column type $attribute is
	 * @param string $attribute attribute name
	 * @return mixed the timestamp value
	 */
	protected function evaluateTimestamp($attribute)
	{
		if ($this->timestamp instanceof Expression) {
			return $this->timestamp;
		} elseif ($this->timestamp !== null) {
			return eval('return ' . $this->timestamp . ';');
		} else {
			return time();
		}
	}
}
