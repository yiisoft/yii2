<?php
namespace Codeception\Module;

class CodeHelper extends \Codeception\Module
{
    public function _beforeSuite($settings = [])
    {
        include __DIR__.'/../unit/_bootstrap.php';
    }
}
