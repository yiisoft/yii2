<?php

namespace yiiunit\framework\filters\stubs;

use yii\base\Object;
use yii\filters\RateLimitInterface;

class RateLimit extends Object implements RateLimitInterface
{
    private $_rateLimit;

    private $_allowance;

    public function getRateLimit($request, $action)
    {
        return $this->_rateLimit;
    }

    public function setRateLimit($rateLimit)
    {
        $this->_rateLimit = $rateLimit;
        
        return $this;
    }

    public function loadAllowance($request, $action)
    {
        return $this->_allowance;
    }

    public function setAllowance($allowance)
    {
        $this->_allowance = $allowance;

        return $this;
    }


    public function saveAllowance($request, $action, $allowance, $timestamp)
    {
        return [$action, $allowance, $timestamp];
    }

}
