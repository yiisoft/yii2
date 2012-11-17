<?php

namespace yii\db\ar;

class ManyManyRelation extends Relation
{
	public $joinTable;
	public $leftLink;
	public $rightLink;
}
