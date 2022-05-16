<?php

namespace yiiunit\framework\di\stubs;

// Syntax valid only for PHP 8.1+
class StaticMethodsWithIntersectionTypes
{
    public static function withQuxInterfaceAndQuxAnotherIntersection(QuxInterface & QuxAnother $Qux)
    {
    }

    public static function withQuxAnotherAndQuxInterfaceIntersection(QuxAnother & QuxInterface $Qux)
    {
    }
}
