<?php

namespace yii\debug\models\search;

use yii\base\Model;
use yii\debug\components\search\Filter;
use yii\debug\components\search\matches;

class Base extends Model
{

	/**
	 * @param Filter $filter
	 * @param string $attribute
	 * @param boolean $partial
	 */
	public function addCondition($filter, $attribute, $partial = false)
	{
		$value = $this->$attribute;

		if (mb_strpos($value, '>') !== false) {

			$value = intval(str_replace('>', '', $value));
			$filter->addMatch($attribute, new matches\Greater(['value' => $value]));

		} elseif (mb_strpos($value, '<') !== false) {

			$value = intval(str_replace('<', '', $value));
			$filter->addMatch($attribute, new matches\Lower(['value' => $value]));

		} else {
			$filter->addMatch($attribute, new matches\Exact(['value' => $value, 'partial' => $partial]));
		}

	}

}
