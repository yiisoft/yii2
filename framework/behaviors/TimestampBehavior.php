<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\behaviors;

use Yii;
use yii\db\ActiveRecord;

class TimestampBehavior extends AttributeStampBehavior
{

	public $attributes = [
		ActiveRecord::EVENT_BEFORE_INSERT => ['created_at'],
		ActiveRecord::EVENT_BEFORE_UPDATE => ['updated_at'],
	];
	
	public $format = 'Y-m-d H:i:s';

	public function init()
	{
		parent::init();
		$this->defaultValue = date($this->format);
	}

	protected function processValue()
	{
		return $this->defaultValue;
	}
}
