<?php

namespace yii\db\ar;

class Relation extends ActiveQuery
{
	public $parentClass;

	public function findWith(&$parentRecords)
	{
		$this->andWhere(array('in', $links, $keys));
		$records = $this->find();
		foreach ($records as $record) {
			// find the matching parent record(s)
			// insert into the parent records(s)
		}
	}
}
