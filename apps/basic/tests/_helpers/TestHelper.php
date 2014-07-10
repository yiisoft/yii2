<?php
namespace Codeception\Module;

class TestHelper extends \Codeception\Module
{
    public function _beforeSuite($settings = [])
    {
        include __DIR__.'/../functional/_bootstrap.php';
    }
}
