<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\behaviors;

use Yii;
use yii\db\ActiveRecord;

class BlameableBehavior extends AttributeStampBehavior
{

	public $attributes = [
		ActiveRecord::EVENT_BEFORE_INSERT => ['created_by'],
		ActiveRecord::EVENT_BEFORE_UPDATE => ['updated_by'],
	];

	public function init()
	{
		parent::init();
		$value = isset(\Yii::$app->user->id) ? \Yii::$app->user->id : null;
		$this->defaultValue = $value;
	}

	protected function processValue()
	{
		return $this->defaultValue;
	}
}
