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
 * Panel is a base class for debugger panel classes. It defines how data should be collected,
 * what should be displayed at debug toolbar and on debugger details view.
 *
 * @property string $detail Content that is displayed in debugger detail view. This property is read-only.
 * @property string $name Name of the panel. This property is read-only.
 * @property string $summary Content that is displayed at debug toolbar. This property is read-only.
 * @property string $url URL pointing to panel detail view. This property is read-only.
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
	 * @var array array of actions to add to the debug modules default controller.
	 * This array will be merged with all other panels actions property.
	 * See [[\yii\base\Controller::actions()]] for the format.
	 */
	public $actions = [];

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
		return Yii::$app->getUrlManager()->createUrl($this->module->id . '/default/view', [
			'panel' => $this->id,
			'tag' => $this->tag,
		]);
	}
}
