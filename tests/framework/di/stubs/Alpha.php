<?php

namespace yiiunit\framework\di\stubs;

use yii\base\BaseObject;

class Alpha extends BaseObject
{
    public $beta;
    public $omega;

    public function __construct(Beta $beta = null, QuxInterface $omega = null)
    {
        $this->beta = $beta;
        $this->omega = $omega;
    }
}
