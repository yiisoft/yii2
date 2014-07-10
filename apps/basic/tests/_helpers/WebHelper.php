<?php
namespace Codeception\Module;

class WebHelper extends \Codeception\Module
{
    public function _beforeSuite($settings = [])
    {
        include __DIR__.'/../acceptance/_bootstrap.php';
    }
}
