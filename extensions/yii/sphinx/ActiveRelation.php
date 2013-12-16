<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\sphinx;

use yii\db\ActiveRelationInterface;
use yii\db\ActiveRelationTrait;

/**
 * ActiveRelation represents a relation to Sphinx Active Record class.
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 2.0
 */
class ActiveRelation extends ActiveQuery implements ActiveRelationInterface
{
	use ActiveRelationTrait;

	/**
	 * @inheritdoc
	 */
	public function createCommand($db = null)
	{
		if ($this->primaryModel !== null) {
			// lazy loading
			if ($this->via instanceof self) {
				// via pivot index
				$viaModels = $this->via->findPivotRows([$this->primaryModel]);
				$this->filterByModels($viaModels);
			} elseif (is_array($this->via)) {
				// via relation
				/** @var ActiveRelation $viaQuery */
				list($viaName, $viaQuery) = $this->via;
				if ($viaQuery->multiple) {
					$viaModels = $viaQuery->all();
					$this->primaryModel->populateRelation($viaName, $viaModels);
				} else {
					$model = $viaQuery->one();
					$this->primaryModel->populateRelation($viaName, $model);
					$viaModels = $model === null ? [] : [$model];
				}
				$this->filterByModels($viaModels);
			} else {
				$this->filterByModels([$this->primaryModel]);
			}
		}
		return parent::createCommand($db);
	}
}