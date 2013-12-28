<?php

namespace yii\debug\components\search;

use yii\base\Component;

class Filter extends Component
{

	/**
	 * @var array rules for matching filters in the way: [:fieldName => [rule1, rule2,..]]
	 */
	protected $rules = [];

	/**
	 * Adds rules for filtering data. Match can be partial or exactly.
	 * @param string $name attribute name
	 * @param \yii\debug\components\search\matches\Base $rule
	 */
	public function addMatch($name, $rule)
	{
		if (empty($rule->value) && $rule->value !== 0) {
			return;
		}

		$this->rules[$name][] = $rule;
	}

	/**
	 * Applies filter on given array and returns filtered data.
	 * @param array $data data to filter
	 * @return array filtered data
	 */
	public function filter(array $data)
	{
		$filtered = [];

		foreach($data as $row)
		{
			if ($this->checkFilter($row)) {
				$filtered[] = $row;
			}
		}

		return $filtered;
	}

	/**
	 * Check if the given data satisfies filters.
	 * @param array $row
	 */
	public function checkFilter(array $row)
	{
		$matched = true;

		foreach ($row as $name=>$value)
		{
			if (isset($this->rules[$name])) {

				#check all rules for given attribute

				foreach($this->rules[$name] as $rule)
				{
					if (!$rule->check($value)) {
						$matched = false;
					}
				}

			}
		}

		return $matched;
	}

}
