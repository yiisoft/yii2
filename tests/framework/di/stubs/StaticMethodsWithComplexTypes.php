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

    public static function withBetaAndQuxUnion(Beta | Qux $betaOrQux)
    {
    }

    public static function withQuxAndBetaUnion(Qux | Beta $betaOrQux)
    {
    }
}
