<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\debug;

use Yii;
use yii\base\Component;

/**
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class Panel extends Component
{
	public $id;
	public $tag;
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

	public function getUrl()
	{
		return Yii::$app->getUrlManager()->createUrl($this->module->id . '/default/view', array(
			'panel' => $this->id,
			'tag' => $this->tag,
		));
	}
}
