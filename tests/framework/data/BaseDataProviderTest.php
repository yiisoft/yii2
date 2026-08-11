<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\data;

use ReflectionClass;
use yii\base\InvalidArgumentException;
use yii\data\BaseDataProvider;
use yii\data\Pagination;
use yii\data\Sort;
use yiiunit\TestCase;

/**
 * @group data
 */
class BaseDataProviderTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->mockApplication();
    }

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

    public function testPrepareAndGetModels(): void
    {
        $provider = new ConcreteDataProvider();
        $this->assertSame([], $provider->getModels());
        $this->assertSame([], $provider->getKeys());
    }

    public function testSetModels(): void
    {
        $provider = new ConcreteDataProvider();
        $provider->setModels(['a', 'b']);
        $this->assertSame(['a', 'b'], $provider->getModels());
    }

    public function testSetKeys(): void
    {
        $provider = new ConcreteDataProvider();
        $provider->setKeys([1, 2]);
        $this->assertSame([1, 2], $provider->getKeys());
    }

    public function testGetCount(): void
    {
        $provider = new ConcreteDataProvider();
        $provider->setModels(['a', 'b', 'c']);
        $this->assertSame(3, $provider->getCount());
    }

    public function testGetTotalCountWithoutPagination(): void
    {
        $provider = new ConcreteDataProvider(['pagination' => false]);
        $provider->setModels(['a', 'b']);
        $this->assertSame(2, $provider->getTotalCount());
    }

    public function testGetTotalCountWithPagination(): void
    {
        $provider = new CountableDataProvider();
        $this->assertSame(42, $provider->getTotalCount());
    }

    public function testSetTotalCount(): void
    {
        $provider = new ConcreteDataProvider();
        $provider->setTotalCount(42);
        $this->assertSame(42, $provider->getTotalCount());
    }

    public function testGetPaginationDefault(): void
    {
        $provider = new ConcreteDataProvider();
        $this->assertInstanceOf(Pagination::class, $provider->getPagination());
    }

    public function testSetPaginationWithId(): void
    {
        $provider = new ConcreteDataProvider(['id' => 'test']);
        $pagination = $provider->getPagination();
        $this->assertSame('test-page', $pagination->pageParam);
        $this->assertSame('test-per-page', $pagination->pageSizeParam);
    }

    public function testSetPaginationInstance(): void
    {
        $pagination = new Pagination();
        $provider = new ConcreteDataProvider();
        $provider->setPagination($pagination);
        $this->assertSame($pagination, $provider->getPagination());
    }

    public function testSetPaginationFalse(): void
    {
        $provider = new ConcreteDataProvider();
        $provider->setPagination(false);
        $this->assertFalse($provider->getPagination());
    }

    public function testSetPaginationInvalid(): void
    {
        $provider = new ConcreteDataProvider();
        $this->expectException(InvalidArgumentException::class);
        $provider->setPagination('invalid');
    }

    public function testGetSortDefault(): void
    {
        $provider = new ConcreteDataProvider();
        $this->assertInstanceOf(Sort::class, $provider->getSort());
    }

    public function testSetSortWithId(): void
    {
        $provider = new ConcreteDataProvider(['id' => 'test']);
        $sort = $provider->getSort();
        $this->assertSame('test-sort', $sort->sortParam);
    }

    public function testSetSortInstance(): void
    {
        $sort = new Sort();
        $provider = new ConcreteDataProvider();
        $provider->setSort($sort);
        $this->assertSame($sort, $provider->getSort());
    }

    public function testSetSortFalse(): void
    {
        $provider = new ConcreteDataProvider();
        $provider->setSort(false);
        $this->assertFalse($provider->getSort());
    }

    public function testSetSortInvalid(): void
    {
        $provider = new ConcreteDataProvider();
        $this->expectException(InvalidArgumentException::class);
        $provider->setSort('invalid');
    }

    public function testRefresh(): void
    {
        $provider = new ConcreteDataProvider();
        $provider->getModels();
        $provider->setTotalCount(42);
        $provider->refresh();
        $this->assertSame(0, $provider->getTotalCount());
    }

    public function testForcePrepare(): void
    {
        $provider = new ConcreteDataProvider();
        $provider->prepare();
        $provider->setModels(['overridden']);
        $provider->prepare(true);
        $this->assertSame([], $provider->getModels());
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

class CountableDataProvider extends BaseDataProvider
{
    protected function prepareModels()
    {
        return ['prepared-model'];
    }

    protected function prepareKeys($models)
    {
        return array_keys($models);
    }

    protected function prepareTotalCount()
    {
        return 42;
    }
}
