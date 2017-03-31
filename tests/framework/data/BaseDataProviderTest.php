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

class ConcreteDataProvider extends BaseDataProvider
{
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
}
