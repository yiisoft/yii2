<?php

namespace yiiunit\data\ar;

class Item extends ActiveRecord
{
	public function tableName()
	{
		return 'tbl_item';
	}
}