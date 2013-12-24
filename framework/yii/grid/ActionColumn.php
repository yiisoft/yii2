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
use yii\helpers\ArrayHelper;

/**
 * ActionColumn is a column for the [[GridView]] widget that displays buttons for viewing and manipulating the items.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @author Kartik Visweswaran <kartikv2@gmail.com>
 * @since 2.0
 */
class ActionColumn extends Column
{
	/**
	 * @var string the ID of the controller that should handle the actions specified here.
	 * If not set, it will use the currently active controller. This property is mainly used by
	 * [[urlCreator]] to create URLs for different actions. The value of this property will be prefixed
	 * to each action name to form the route of the action.
	 */
	public $controller;
	public $template = '{view} {update} {delete}';
	public $buttons = [];
	public $urlCreator;
	
	/* Options for view button */
	public $viewOptions = [];

	/* Options for update button */
	public $updateOptions = [];

	/* Options for delete button */
	public $deleteOptions = [];
	
	public function init()
	{
		parent::init();
		$this->initDefaultButtons();
	}

	protected function initDefaultButtons()
	{
		if (!isset($this->buttons['view'])) {
			$this->buttons['view'] = function ($model, $key, $index, $column) {
				/** @var ActionColumn $column */
				$url = $column->createUrl($model, $key, $index, 'view');
				$label = ArrayHelper::remove($this->viewOptions, 'label', '<span class="glyphicon glyphicon-eye-open"></span>'); 
				$options = array_replace(['title' => Yii::t('yii', 'View')], $this->viewOptions); 
				return Html::a($label, $url, $options);
			};
		}
		if (!isset($this->buttons['update'])) {
			$this->buttons['update'] = function ($model, $key, $index, $column) {
				/** @var ActionColumn $column */
				$url = $column->createUrl($model, $key, $index, 'update');
				$label = ArrayHelper::remove($this->updateOptions, 'label', '<span class="glyphicon glyphicon-pencil"></span>'); 
				$options = array_replace(['title' => Yii::t('yii', 'Update')], $this->updateOptions); 
				return Html::a($label, $url, $options);
			};
		}
		if (!isset($this->buttons['delete'])) {
			$this->buttons['delete'] = function ($model, $key, $index, $column) {
				/** @var ActionColumn $column */
				$url = $column->createUrl($model, $key, $index, 'delete');
				$label = ArrayHelper::remove($this->deleteOptions, 'label', '<span class="glyphicon glyphicon-trash"></span>'); 
				$options = array_replace([
					'title' => Yii::t('yii', 'Delete'),
					'data-confirm' => Yii::t('yii', 'Are you sure to delete this item?'),
					'data-method' => 'post'
				], $this->deleteOptions); 
				return Html::a($label, $url, $options);
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
			$route = $this->controller ? $this->controller . '/' . $action : $action;
			return Yii::$app->controller->createUrl($route, $params);
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
