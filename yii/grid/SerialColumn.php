<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\grid;

/**
 * SerialColumn displays a column of row numbers (1-based).
 * 
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class SerialColumn extends Column
{
	/**
	 * Renders the data cell content.
	 * @param mixed $model the data model
	 * @param integer $index the zero-based index of the data model among the models array returned by [[dataProvider]].
	 * @return string the rendering result
	 */
	protected function renderDataCellContent($model, $index)
	{
		$pagination = $this->grid->dataProvider->getPagination();
		if ($pagination !== false) {
			return $pagination->getOffset() + $index + 1;
		} else {
			return $index + 1;
		}
	}
}
