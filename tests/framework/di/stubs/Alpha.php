<?php

namespace yiiunit\framework\di\stubs;

use yii\base\BaseObject;

class Alpha extends BaseObject
{
    public $beta;
    public $omega;
    public $unknown = true;
    public $color = true;

    public function __construct(
        Beta $beta = null,
        QuxInterface $omega = null,
        Unknown $unknown = null,
        AbstractColor $color = null
    ) {
        $this->beta = $beta;
        $this->omega = $omega;
        $this->unknown = $unknown;
        $this->color = $color;
    }
}
