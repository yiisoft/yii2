<?php

namespace yiiunit\framework\di\stubs;

// Syntax valid only for PHP 8.0+
class StaticMethodsWithUnionTypes
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
}
