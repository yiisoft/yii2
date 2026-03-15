<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\data;

use ReflectionClass;
use yii\data\BaseDataProvider;
use yiiunit\TestCase;

/**
 * @group data
 */
class BaseDataProviderTest extends TestCase
{
    public function testGenerateId(): void
    {
        $rc = new ReflectionClass(BaseDataProvider::class);
        $rp = $rc->getProperty('counter');

        // @link https://wiki.php.net/rfc/deprecations_php_8_5#deprecate_reflectionsetaccessible
        // @link https://wiki.php.net/rfc/make-reflection-setaccessible-no-op
        if (PHP_VERSION_ID < 80100) {
            $rp->setAccessible(true);
        }

        $rp->setValue(new ConcreteDataProvider(), null);

        $this->assertNull((new ConcreteDataProvider())->id);
        $this->assertNotNull((new ConcreteDataProvider())->id);
    }
}

/**
 * ConcreteDataProvider.
 */
class ConcreteDataProvider extends BaseDataProvider
{
    /**
     * {@inheritdoc}
     */
    protected function prepareModels()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    protected function prepareKeys($models)
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    protected function prepareTotalCount()
    {
        return 0;
    }
}
