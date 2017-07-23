<?php

namespace yii\db;

class IrreversibleCommandException extends \yii\base\Exception
{
    public function getName()
    {
        return 'Migration step can not be reverted automatically';
    }
}
