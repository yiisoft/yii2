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
 * Class ActiveRelation
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 2.0
 */
class ActiveRelation extends ActiveQuery implements ActiveRelationInterface
{
	use ActiveRelationTrait;
}