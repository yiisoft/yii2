<?php

declare(strict_types=1);

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\db\pgsql\providers;

use yii\db\Constraint;

/**
 * Data provider for {@see \yiiunit\framework\db\pgsql\SchemaConstraintsTest} test cases.
 */
final class ConstraintsProvider extends \yiiunit\base\db\providers\ConstraintsProvider
{
    /**
     * @return array<string, array{string, string, Constraint|bool|array<array-key, mixed>|null}>
     */
    public static function constraints(): array
    {
        $result = parent::constraints();

        $result['1: check'][2][0]->expression = 'CHECK ((("C_check")::text <> \'\'::text))';
        $result['3: foreign key'][2][0]->foreignSchemaName = 'public';
        $result['3: index'][2] = [];

        return $result;
    }
}
