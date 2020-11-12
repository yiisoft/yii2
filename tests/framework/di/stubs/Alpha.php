<?php

namespace yiiunit\framework\di\stubs;

use yii\base\BaseObject;

class Alpha extends BaseObject
{
    public $beta;
    public $omega;
    public $unknown = true;
    public $abstract = true;

    public function __construct(
        Beta $beta = null,
        QuxInterface $omega = null,
        Unknown $unknown = null,
        AbstractObject $abstract = null
    ) {
        $this->beta = $beta;
        $this->omega = $omega;
        $this->unknown = $unknown;
        $this->abstract = $abstract;
    }
}
