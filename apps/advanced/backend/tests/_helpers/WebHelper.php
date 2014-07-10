<?php
namespace Codeception\Module;

// here you can define custom functions for WebGuy

class WebHelper extends \Codeception\Module
{
    public function _beforeSuite($settings = [])
    {
        include __DIR__.'/../acceptance/_bootstrap.php';
    }
}
