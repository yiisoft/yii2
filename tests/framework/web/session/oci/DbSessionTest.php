<?php

declare(strict_types=1);

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\web\session\oci;

use PHPUnit\Framework\Attributes\Group;
use stdClass;
use yiiunit\base\web\session\BaseDbSession;

use function str_repeat;

/**
 * Unit test for {@see \yii\web\DbSession} with Oracle driver.
 *
 * @see https://github.com/yiisoft/yii2/issues/15900
 * @see https://github.com/yiisoft/yii2/issues/16468
 *
 * @author Wilmer Arambula <terabytesoftw@gmail.com>
 * @since 22.0
 */
#[Group('db')]
#[Group('db-session')]
#[Group('oci')]
final class DbSessionTest extends BaseDbSession
{
    protected function getDriverNames()
    {
        return ['oci'];
    }

    protected function buildObjectForSerialization(): stdClass
    {
        $object = parent::buildObjectForSerialization();

        // Oracle `UTL_RAW.CAST_TO_RAW()` is limited by the `RAW` maximum size. With `MAX_STRING_SIZE=STANDARD`,
        // that limit is `2000` bytes, so this Oracle-specific fixture keeps both serialized writes under it.
        $object->textValue = str_repeat('QweåßƒТест', 94);

        return $object;
    }
}
