<?php

namespace yiiunit\framework\data;

use yiiunit\TestCase;
use yii\data\BaseDataProvider;

/**
 * @group data
 */
class BaseDataProviderTest extends TestCase
{
    public function testGenerateId()
    {
        $rc = new \ReflectionClass(BaseDataProvider::className());
        $rp = $rc->getProperty('counter');
        $rp->setAccessible(true);
        $rp->setValue(null);

        $this->assertNull((new ConcreteDataProvider())->id);
        $this->assertNotNull((new ConcreteDataProvider())->id);
    }

}

/**
 * ConcreteDataProvider
 */
class ConcreteDataProvider extends BaseDataProvider
{
    /**
     * @inheritdoc
     */
    protected function prepareModels()
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    protected function prepareKeys($models)
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    protected function prepareTotalCount()
    {
        return 0;
    }
}
