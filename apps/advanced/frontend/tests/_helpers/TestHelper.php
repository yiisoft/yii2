<?php
namespace Codeception\Module;

// here you can define custom functions for TestGuy

class TestHelper extends \Codeception\Module
{
    public function _beforeSuite($settings = [])
    {
        include __DIR__.'/../functional/_bootstrap.php';
    }
}
