<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\debug;

use yii\base\Component;

/**
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class Panel extends Component
{
	/**
	 * @var Module
	 */
	public $module;
	public $data;

	public function getName()
	{
		return '';
	}

	public function getSummary()
	{
		return '';
	}

	public function getDetail()
	{
		return '';
	}

	public function save()
	{
		return null;
	}

	public function load($data)
	{
		$this->data = $data;
	}
}
