<?php
namespace yiiunit\framework\validators\ExistValidatorDriverTests;

use yiiunit\framework\validators\ExistValidatorTest;

/**
 * @group validators
 */
class ExistValidatorPostgresTest extends ExistValidatorTest
{
    protected $driverName = 'pgsql';
}
