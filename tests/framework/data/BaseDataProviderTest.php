<?php

namespace yiiunit\framework\data;

use yiiunit\TestCase;
use yii\data\BaseDataProvider;

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

class ConcreteDataProvider extends BaseDataProvider {

    /**
     * Prepares the data models that will be made available in the current page.
     * @return array the available data models
     */
    protected function prepareModels()
    {
        return [];
    }

    /**
     * Prepares the keys associated with the currently available data models.
     * @param array $models the available data models
     * @return array the keys
     */
    protected function prepareKeys($models)
    {
        return [];
    }

    /**
     * Returns a value indicating the total number of data models in this data provider.
     * @return int total number of data models in this data provider.
     */
    protected function prepareTotalCount()
    {
        return 0;
    }
}