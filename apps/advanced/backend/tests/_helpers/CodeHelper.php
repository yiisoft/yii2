<?php
namespace Codeception\Module;

// here you can define custom functions for CodeGuy

class CodeHelper extends \Codeception\Module
{
    public function _beforeSuite($settings = [])
    {
        include __DIR__.'/../unit/_bootstrap.php';
    }
}
