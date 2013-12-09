<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\grid;

use Yii;
use Closure;
use yii\helpers\Html;

/**
 * ActionColumn is a column for the [[GridView]] widget that displays buttons for viewing and manipulating the items.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @author Kartik Visweswaran <kartikv2@gmail.com>
 * @since 2.0
 */
class ActionColumn extends Column
{
	public $template = '{view} {update} {delete}';
	public $buttons = [];
	public $urlCreator;
	
	/* Options for view button */
	public $viewOptions = [];

	/* Options for update button */
	public $updateOptions = [];

	/* Options for delete button */
	public $deleteOptions = [];
	
	/* Default view button options */
	private static $_defaultView = [
		'label' => '<span class="glyphicon glyphicon-eye-open"></span>',
		'title' => 'View'
	];

	/* Default update button options */
	private static $_defaultUpdate = [
		'label' => '<span class="glyphicon glyphicon-pencil"></span>',
		'title' => 'Update'
	];

	/* Default delete button options */
	private static $_defaultDelete = [
		'label' => '<span class="glyphicon glyphicon-trash"></span>',
		'title' => 'Delete',
		'data-confirm' => 'Are you sure to delete this item?',
		'data-method' => 'post'
	];
	
	public function init()
	{
		parent::init();
		$this->initDefaultButtons();
	}
	
	protected function renderButton($options, $default, $url) {
		$options = array_replace($default, $options);
		$label = $options['label'];
		unset($options['label']);
		return Html::a($label, $url, $options);
	}

	protected function initDefaultButtons()
	{
		if (!isset($this->buttons['view'])) {
			$this->buttons['view'] = function ($model, $key, $index, $column) {
				/** @var ActionColumn $column */
				$url = $column->createUrl($model, $key, $index, 'view');
				return $this->renderButton($this->viewOptions, self::$_defaultView, $url);
			};
		}
		if (!isset($this->buttons['update'])) {
			$this->buttons['update'] = function ($model, $key, $index, $column) {
				/** @var ActionColumn $column */
				$url = $column->createUrl($model, $key, $index, 'update');
				return $this->renderButton($this->updateOptions, self::$_defaultUpdate, $url);
			};
		}
		if (!isset($this->buttons['delete'])) {
			$this->buttons['delete'] = function ($model, $key, $index, $column) {
				/** @var ActionColumn $column */
				$url = $column->createUrl($model, $key, $index, 'delete');
				return $this->renderButton($this->deleteOptions, self::$_defaultDelete, $url);
			};
		}
	}

	/**
	 * @param \yii\db\ActiveRecord $model
	 * @param mixed $key the key associated with the data model
	 * @param integer $index
	 * @param string $action
	 * @return string
	 */
	public function createUrl($model, $key, $index, $action)
	{
		if ($this->urlCreator instanceof Closure) {
			return call_user_func($this->urlCreator, $model, $key, $index, $action);
		} else {
			$params = is_array($key) ? $key : ['id' => $key];
			return Yii::$app->controller->createUrl($action, $params);
		}
	}

	/**
	 * @inheritdoc
	 */
	protected function renderDataCellContent($model, $key, $index)
	{
		return preg_replace_callback('/\\{(\w+)\\}/', function ($matches) use ($model, $key, $index) {
			$name = $matches[1];
			if (isset($this->buttons[$name])) {
				return call_user_func($this->buttons[$name], $model, $key, $index, $this);
			} else {
				return '';
			}
		}, $this->template);
	}
}
