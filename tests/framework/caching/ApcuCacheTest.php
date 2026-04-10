<?php

declare(strict_types=1);

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\caching;

use PHPUnit\Framework\Attributes\Group;
use Xepozz\InternalMocker\MockerState;
use yii\base\InvalidConfigException;
use yii\caching\ApcuCache;

/**
 * Unit tests for {@see ApcuCache}.
 *
 * Requires `apc.enable_cli=1` in `php.ini` for CLI execution.
 *
 * @author Wilmer Arambula <terabytesoftw@gmail.com>
 * @since 2.2
 */
#[Group('caching')]
#[Group('apcu')]
final class ApcuCacheTest extends CacheTestCase
{
    private ApcuCache|null $_cacheInstance = null;

    /**
     * @return ApcuCache
     */
    protected function getCacheInstance(): ApcuCache
    {
        if ($this->_cacheInstance === null) {
            $this->_cacheInstance = new ApcuCache();
        }

        return $this->_cacheInstance;
    }

    public function testInitThrowsExceptionWhenApcuExtensionNotLoaded(): void
    {
        MockerState::addCondition('yii\caching', 'extension_loaded', ['apcu'], false);

        $this->expectException(InvalidConfigException::class);
        $this->expectExceptionMessage('ApcuCache requires PHP apcu extension to be loaded.');

        new ApcuCache();
    }
}
