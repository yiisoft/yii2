<?php

declare(strict_types=1);

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\web\session\oci;

use PHPUnit\Framework\Attributes\Group;
use yiiunit\base\web\session\BaseDbSession;

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
}
