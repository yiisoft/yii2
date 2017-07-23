<?php

namespace yii\db\fake;

interface FakeConnectionLoggerInterface
{
    public function getExecutedCommands();
}
