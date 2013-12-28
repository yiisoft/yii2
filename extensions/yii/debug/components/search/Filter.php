<?php

namespace yii\debug\components\search;

use yii\base\Component;

class Filter extends Component
{

	/**
	 * @var array attributes and its values that should be partially
	 */
	protected $partialMatch = [];

	/**
	 * @var array attributes and its values that should match exactly
	 */
	protected $fullMatch = [];

	/**
	 * Adds rules for filtering data. Match can be partial or exactly.
	 * @param type $name
	 * @param type $value
	 * @param type $partialMatch
	 */
	public function addMatch($name, $value, $partialMatch=false)
	{
		if ($value == '')
				return;

		if ($partialMatch) {
			$this->partialMatch[$name] = $value;
		}
		else {
			$this->fullMatch[$name] = $value;
		}
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
			if ($this->checkFilter($row))
				$filtered[] = $row;
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
			if (isset($this->partialMatch[$name]) && (mb_strpos($value, $this->partialMatch[$name]) === false)) {
				$matched = false;
			}
			if (isset($this->fullMatch[$name]) && (mb_strtolower($this->fullMatch[$name],'utf8') != mb_strtolower($value,'utf8'))) {
				$matched = false;
			}
		}
		return $matched;
	}

}
