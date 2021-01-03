<?php

use yii\base\Controller;

class TestController extends Controller
{

    public function actionParams(
        $mixed,
        int $int,
        float $float,
        bool $bool,
        DateTime $dateTime,
        DateTimeImmutable $dateTimeImmutable
    )
    {
    }

    public function actionNoType($value)
    {
    }

    public function actionBuiltin(int $int, float $float, bool $bool)
    {
    }

    public function actionBuiltinNullable(?int $int, ?float $float, ?bool $bool)
    {
    }

    public function actionDateTime(DateTime $dateTime, DateTimeImmutable $dateTimeImmutable)
    {
    }

    public function actionDateTimeNullable(?DateTime $dateTime, ?DateTimeImmutable $dateTimeImmutable)
    {
    }
}
