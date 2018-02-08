<?php

namespace yii\db\sqlite\conditions;

/**
 * {@inheritdoc}
 */
class LikeConditionBuilder extends \yii\db\conditions\LikeConditionBuilder
{
    /**
     * @inheritdoc
     */
    protected $escapeCharacter = '\\';
}
