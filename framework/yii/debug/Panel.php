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
 * Panel is a base class for debugger panel. It defines how data should be collected,
 * what should be dispalyed at debug toolbar and on debugger details view.
 *
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

	/**
	 * @return string name of the panel
	 */
	public function getName()
	{
		return '';
	}

	/**
	 * @return string content that is displayed at debug toolbar
	 */
	public function getSummary()
	{
		return '';
	}

	/**
	 * @return string content that is displayed in debugger detail view
	 */
	public function getDetail()
	{
		return '';
	}

	/**
	 * Saves data to be later used in debugger detail view.
	 * This method is called on every page where debugger is enabled.
	 *
	 * @return mixed data to be saved
	 */
	public function save()
	{
		return null;
	}

	public function load($data)
	{
		$this->data = $data;
	}

	/**
	 * @return string URL pointing to panel detail view
	 */
	public function getUrl()
	{
		return Yii::$app->getUrlManager()->createUrl($this->module->id . '/default/view', array(
			'panel' => $this->id,
			'tag' => $this->tag,
		));
	}
}
