<?php

namespace yiiunit\framework\di\stubs;

use yii\base\BaseObject;

class Zeta extends BaseObject
{
    public $beta = false;
    public $betaNull = false;
    public $color = false;
    public $colorNull = false;
    public $qux = false;
    public $quxNull = false;
    public $unknown = false;
    public $unknownNull = false;

    public function __construct(
        ?Beta $beta,
        ?AbstractColor $color,
        ?QuxInterface $qux,
        ?Unknown $unknown,
        ?Beta $betaNull = null,
        ?AbstractColor $colorNull = null,
        ?QuxInterface $quxNull = null,
        ?Unknown $unknownNull = null
    ) {
        $this->beta = $beta;
        $this->betaNull = $betaNull;
        $this->color = $color;
        $this->colorNull = $colorNull;
        $this->qux = $qux;
        $this->quxNull = $quxNull;
        $this->unknown = $unknown;
        $this->unknownNull = $unknownNull;
    }
}
