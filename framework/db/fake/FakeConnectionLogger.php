<?php

namespace yii\db\fake;

use yii\db\ConnectionLoggerInterface;

/**
 * Class FakeConnectionLogger is designed to log events from fake connection
 * // TODO better docs
 *
 * @author Dmytro Naumenko <d.naumenko.a@gmail.com>
 * @since 2.0.13
 */
class FakeConnectionLogger implements ConnectionLoggerInterface, FakeConnectionLoggerInterface
{
    protected $items = [];

    /**
     * @return array commands description that where executed
     */
    public function getExecutedCommands()
    {
        $result = [];
        foreach ($this->items as list($command, $category)) {
            if ($category === FakeCommand::EXECUTED_COMMAND_LOG) {
                $result[] = $command;
            }
        }

        return $result;
    }

    public function log($message, $category)
    {
        $this->items['log'][] = [$message, $category];
    }

    public function error($message, $category)
    {
        $this->items['error'][] = [$message, $category];
    }

    public function trace($message, $category)
    {
        $this->items['trace'][] = [$message, $category];
    }

    public function warning($message, $category)
    {
        $this->items['warning'][] = [$message, $category];
    }
}
