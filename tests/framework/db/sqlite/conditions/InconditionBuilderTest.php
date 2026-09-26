<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

declare(strict_types=1);

namespace yiiunit\framework\db\sqlite\conditions;

use yii\base\NotSupportedException;
use yii\db\conditions\InCondition;
use yii\db\Query;
use yii\db\sqlite\conditions\InConditionBuilder;
use yiiunit\framework\db\DatabaseTestCase;

/**
 * @group sqlite
 *
 * @author Wilmer Arambula <terabytesoftw@gmail.com>
 */
final class InconditionBuilderTest extends DatabaseTestCase
{
    public $driverName = 'sqlite';

    public function testBuildSubqueryInCondition(): void
    {
        $db = $this->getConnection();
        $query = new Query();
        $inConditionBuilder = new InConditionBuilder($db->getQueryBuilder());

        $inCondition = new InCondition(
            ['id'],
            'in',
            $query->select('id')->from('users')->where(['active' => 1]),
        );

        $this->expectException(NotSupportedException::class);
        $this->expectExceptionMessage(
            'yii\db\sqlite\conditions\InConditionBuilder::buildSubqueryInCondition is not supported by SQLite.',
        );

        $inConditionBuilder->build($inCondition);
    }
}
