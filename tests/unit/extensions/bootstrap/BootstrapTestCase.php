<?php
namespace yiiunit\extensions\bootstrap;

use yiiunit\TestCase;

/**
 * BootstrapTestCase is the base class for all bootstrap extension test cases
 */
abstract class BootstrapTestCase extends TestCase
{
    public function setUp()
    {
        $this->mockWebApplication();
    }
}