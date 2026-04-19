<?php

namespace yiiunit\framework\di\stubs;

class StaticMethodsWithIntersectionTypes
{
    public static function withQuxInterfaceAndQuxAnotherIntersection(QuxInterface & QuxAnother $Qux): void
    {
    }

    public static function withQuxAnotherAndQuxInterfaceIntersection(QuxAnother & QuxInterface $Qux): void
    {
    }
}
