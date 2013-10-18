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
use yii\helpers\Inflector;
use yii\helpers\StringHelper;

/**
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class ActionColumn extends Column
{
	public $template = '{view} {update} {delete}';
	public $buttons = array();
	public $urlCreator;

	public function init()
	{
		parent::init();
		$this->initDefaultButtons();
	}

	protected function initDefaultButtons()
	{
		if (!isset($this->buttons['view'])) {
			$this->buttons['view'] = function ($model, $column) {
				/** @var ActionColumn $column */
				$url = $column->createUrl($model, 'view');
				return Html::a('<span class="glyphicon glyphicon-eye-open"></span>', $url, array(
					'title' => Yii::t('yii', 'View'),
				));
			};
		}
		if (!isset($this->buttons['update'])) {
			$this->buttons['update'] = function ($model, $column) {
				/** @var ActionColumn $column */
				$url = $column->createUrl($model, 'update');
				return Html::a('<span class="glyphicon glyphicon-pencil"></span>', $url, array(
					'title' => Yii::t('yii', 'Update'),
				));
			};
		}
		if (!isset($this->buttons['delete'])) {
			$this->buttons['delete'] = function ($model, $column) {
				/** @var ActionColumn $column */
				$url = $column->createUrl($model, 'delete');
				return Html::a('<span class="glyphicon glyphicon-trash"></span>', $url, array(
					'title' => Yii::t('yii', 'Delete'),
					'data-confirm' => Yii::t('yii', 'Are you sure to delete this item?'),
					'data-method' => 'post',
				));
			};
		}
	}

	/**
	 * @param \yii\db\ActiveRecord $model
	 * @param string $action
	 * @return string
	 */
	public function createUrl($model, $action)
	{
		if ($this->urlCreator instanceof Closure) {
			return call_user_func($this->urlCreator, $model, $action);
		} else {
			$params = $model->getPrimaryKey(true);
			if (count($params) === 1) {
				$params = array('id' => reset($params));
			}
			return Yii::$app->controller->createUrl($action, $params);
		}
	}

	/**
	 * Renders the data cell content.
	 * @param mixed $model the data model
	 * @param integer $index the zero-based index of the data model among the models array returned by [[dataProvider]].
	 * @return string the rendering result
	 */
	protected function renderDataCellContent($model, $index)
	{
		return preg_replace_callback('/\\{(\w+)\\}/', function ($matches) use ($this, $model) {
			$name = $matches[1];
			if (isset($this->buttons[$name])) {
				return call_user_func($this->buttons[$name], $model, $this);
			} else {
				return '';
			}
		}, $this->template);
	}
}
