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
	public $header = '#';

	/**
	 * @inheritdoc
	 */
	protected function renderDataCellContent($model, $key, $index)
	{
		$pagination = $this->grid->dataProvider->getPagination();
		if ($pagination !== false) {
			return $pagination->getOffset() + $index + 1;
		} else {
			return $index + 1;
		}
	}
}
