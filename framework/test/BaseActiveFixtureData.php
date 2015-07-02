<?php

namespace yii\test;

use Yii;
use yii\base\Object;

/**
 * ActiveFixtureData represents a fixture data file.
 *
 * @author Andrey Kolomenskiy <link@onedev.ru>
 * @since 2.0
 */
abstract class BaseActiveFixtureData extends Object
{
    /**
     * Returns fixture data array
     * @return array
     */
    abstract public function getData();
}
