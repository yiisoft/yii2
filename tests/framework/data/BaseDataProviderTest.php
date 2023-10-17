<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\data;

use yii\data\BaseDataProvider;
use yiiunit\TestCase;

/**
 * @group data
 */
class BaseDataProviderTest extends TestCase
{
    public function testGenerateId()
    {
        $baseDataProvider = new class extends BaseDataProvider {
            protected function prepareModels()
            {
                return [];
            }

            protected function prepareKeys($models)
            {
                return [];
            }

            protected function prepareTotalCount()
            {
                return 0;
            }
        };

        $this->setInaccessibleProperty($baseDataProvider, 'counter', null);

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
