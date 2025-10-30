<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yii\test;

use yii\base\ArrayAccessTrait;
use yii\base\InvalidConfigException;

/**
 * ArrayFixture represents arbitrary fixture that can be loaded from PHP files.
 *
 * For more details and usage information on ArrayFixture, see the [guide article on fixtures](guide:test-fixtures).
 *
 * @author Mark Jebri <mark.github@yandex.ru>
 * @since 2.0
 *
 * @implements \IteratorAggregate<string, array<string, mixed>>
 * @implements \ArrayAccess<string, array<string, mixed>|null>
 */
class ArrayFixture extends Fixture implements \IteratorAggregate, \ArrayAccess, \Countable
{
    use ArrayAccessTrait;
    use FileFixtureTrait;

    /**
     * @var array the data rows. Each array element represents one row of data (column name => column value).
     *
     * @phpstan-var array<string, array<string, mixed>>
     * @psalm-var array<string, array<string, mixed>>
     */
    public $data = [];


    /**
     * Loads the fixture.
     *
     * The default implementation simply stores the data returned by [[getData()]] in [[data]].
     * You should usually override this method by putting the data into the underlying database.
     */
    public function load()
    {
        $this->data = $this->getData();
    }

    /**
     * Returns the fixture data.
     *
     * The default implementation will try to return the fixture data by including the external file specified by [[dataFile]].
     * The file should return the data array that will be stored in [[data]] after inserting into the database.
     *
     * @return array the data to be put into the database
     * @throws InvalidConfigException if the specified data file does not exist.
     *
     * @phpstan-return array<string, array<string, mixed>>
     * @psalm-return array<string, array<string, mixed>>
     */
    protected function getData()
    {
        return $this->loadData($this->dataFile);
    }

    /**
     * {@inheritdoc}
     */
    public function unload()
    {
        parent::unload();
        $this->data = [];
    }
}
