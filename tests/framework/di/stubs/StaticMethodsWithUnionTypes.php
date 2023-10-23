<?php

namespace yiiunit\framework\di\stubs;

// Syntax valid only for PHP 8.0+
class StaticMethodsWithUnionTypes
{
    public static function withBetaUnion(string | Beta $beta): void
    {
    }

    public static function withBetaUnionInverse(Beta | string $beta): void
    {
    }

    public static function withBetaAndQuxUnion(Beta | QuxInterface $betaOrQux): void
    {
    }

    public static function withQuxAndBetaUnion(QuxInterface | Beta $betaOrQux): void
    {
    }
}
