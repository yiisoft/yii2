<?php

namespace yiiunit\data\ar\mongodb\file;

use yiiunit\data\ar\mongodb\CustomerQuery;

class CustomerFile extends ActiveRecord
{
	public static function collectionName()
	{
		return 'customer_fs';
	}

	public function attributes()
	{
		return array_merge(
			parent::attributes(),
			[
				'tag',
				'status',
			]
		);
	}

	public static function createQuery()
	{
		return new CustomerQuery(['modelClass' => get_called_class()]);
	}
}