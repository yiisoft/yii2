<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\widgets\grid;
use yii\base\InvalidConfigException;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use yii\db\ActiveQuery;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Inflector;

/**
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class DataColumn extends Column
{
	public $attribute;
	public $value;
	public $type;
	/**
	 * @var boolean whether to allow sorting by this column. If true and [[attribute]] is found in
	 * the sort definition of [[GridView::dataProvider]], then the header cell of this column
	 * will contain a link that may trigger the sorting when being clicked.
	 */
	public $enableSorting = true;
	/**
	 * @var string|array|boolean the HTML code representing a filter input (eg a text field, a dropdown list)
	 * that is used for this data column. This property is effective only when
	 * {@link CGridView::filter} is set.
	 * If this property is not set, a text field will be generated as the filter input;
	 * If this property is an array, a dropdown list will be generated that uses this property value as
	 * the list options.
	 * If you don't want a filter for this data column, set this value to false.
	 * @since 1.1.1
	 */
	public $filter;


	protected function renderHeaderCellContent()
	{
		if ($this->attribute !== null && $this->header === null) {
			$provider = $this->grid->dataProvider;
			if ($this->enableSorting && ($sort = $provider->getSort()) !== false && $sort->hasAttribute($this->attribute)) {
				return $sort->link($this->attribute);
			}
			$items = $provider->getItems();
			if (($item = reset($items)) instanceof Model) {
				/** @var Model $item */
				return $item->getAttributeLabel($this->attribute);
			} elseif ($provider instanceof ActiveDataProvider) {
				if ($provider->query instanceof ActiveQuery) {
					/** @var Model $model */
					$model = new $provider->query->modelClass;
					return $model->getAttributeLabel($this->attribute);
				}
			}
			return Inflector::camel2words($this->attribute);
		} else {
			return parent::renderHeaderCellContent();
		}
	}

	protected function renderFilterCellContent()
	{
		if (is_string($this->filter)) {
			return $this->filter;
		} elseif ($this->filter !== false && $this->grid->filterModel instanceof Model && $this->attribute !== null) {
			if (is_array($this->filter)) {
				return Html::activeDropDownList($this->grid->filterModel, $this->attribute, $this->filter, array('prompt' => ''));
			} else {
				return Html::activeTextInput($this->grid->filterModel, $this->attribute);
			}
		} else {
			return parent::renderFilterCellContent();
		}
	}

	protected function renderDataCellContent($item, $index)
	{
		if ($this->value !== null) {
			$value = call_user_func($this->value, $item, $index, $this);
		} elseif ($this->content === null && $this->attribute !== null) {
			$value = ArrayHelper::getValue($item, $this->attribute);
		} else {
			return parent::renderDataCellContent($item, $index);
		}
		return $this->grid->formatter->format($value, $this->type);
	}
}
