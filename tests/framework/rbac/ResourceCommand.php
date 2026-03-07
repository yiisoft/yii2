<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

declare(strict_types=1);

namespace yiiunit\framework\rbac;

use yii\db\Command;

class ResourceCommand extends Command
{
    public static $resourceRuleNames = [];
    public static $emptyRuleNames = [];
    public static $anonymousRuleData;

    public function queryAll($fetchMode = null)
    {
        return $this->convertRows(parent::queryAll($fetchMode));
    }

    public function queryOne($fetchMode = null)
    {
        return $this->convertRow(parent::queryOne($fetchMode));
    }

    private function convertRows(array $rows): array
    {
        foreach ($rows as $index => $row) {
            $rows[$index] = $this->convertRow($row);
        }

        return $rows;
    }

    private function convertRow($row)
    {
        if ($row === false || strpos($this->sql, 'auth_rule') === false || !array_key_exists('data', $row)) {
            return $row;
        }

        if (!isset($row['name']) && self::$anonymousRuleData !== null) {
            $row['data'] = $this->createStream(self::$anonymousRuleData);
            return $row;
        }

        if (in_array($row['name'], self::$emptyRuleNames, true)) {
            $row['data'] = $this->createStream('');
            return $row;
        }

        if (in_array($row['name'], self::$resourceRuleNames, true)) {
            $row['data'] = $this->createStream($row['data']);
        }

        return $row;
    }

    private function createStream(string $data)
    {
        $stream = fopen('php://temp', 'rb+');
        fwrite($stream, $data);
        rewind($stream);

        return $stream;
    }
}
