<?php

namespace yiiunit\framework\validators\UniqueValidatorDriverTests;

use yiiunit\framework\validators\UniqueValidatorTest;

/**
 * @group validators
 */
class UniqueValidatorPostgresTest extends UniqueValidatorTest
{
    protected $driverName = 'pgsql';
}
