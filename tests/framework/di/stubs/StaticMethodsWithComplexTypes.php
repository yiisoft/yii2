<?php

namespace yiiunit\framework\di\stubs;

class StaticMethodsWithComplexTypes
{
    public static function withBetaUnion(string | Beta $beta)
    {
    }

    public static function withBetaUnionInverse(Beta | string $beta)
    {
    }

    public static function withBetaAndQuxUnion(Beta | QuxInterface $betaOrQux)
    {
    }

    public static function withQuxAndBetaUnion(QuxInterface | Beta $betaOrQux)
    {
    }

    public static function withQuxInterfaceAndQuxAnotherIntersection(QuxInterface & QuxAnother $Qux)
    {
    }

    public static function withQuxAnotherAndQuxInterfaceIntersection(QuxAnother & QuxInterface $Qux)
    {
    }
}
