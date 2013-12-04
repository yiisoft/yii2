<?php

namespace yiiunit\data\ar\mongo\file;

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

	public static function activeOnly($query)
	{
		$query->andWhere(['status' => 2]);
	}
}