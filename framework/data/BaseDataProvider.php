<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\data;

use Yii;
use yii\base\Component;
use yii\base\InvalidArgumentException;

/**
 * BaseDataProvider 是一个实现了 [[DataProviderInterface]] 的基类。
 *
 * 有关 BaseDataProvider 的详细信息和使用信息，请参阅 [guide article on data providers](guide:output-data-providers)。
 *
 * @property int $count 当前页中的数据模型数。此属性为只读。
 * @property array $keys 与 [[models]] 对应的键值列表。[[models]] 中的每个数据模型
 * 都由该数组中相应的键值唯一标识。
 * @property array $models 当前页中的数据模型列表。
 * @property Pagination|false $pagination 分页对象。如果为 false，则表示禁用分页。
 * 注意，此属性的类型在 getter 和 setter 中有所不同。有关详细信息，请参见 [[getPagination()]] 和
 * [[setPagination()]]。
 * @property Sort|bool $sort 排序对象。如果为 false，则表示排序被禁用。注意
 * 此属性的类型在 getter 和 setter 中有所不同。有关详细信息，请参见 [[getSort()]] 和 [[setSort()]]。
 * @property int $totalCount 数据模型总数。
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
abstract class BaseDataProvider extends Component implements DataProviderInterface
{
    /**
     * @var int 当前页上的数据提供器数。用于生成唯一 IDs。
     */
    private static $counter = 0;
    /**
     * @var string 在所有数据提供器中唯一标识该数据提供器的 ID，如果未设置该 ID，
     * 则按以下方式自动生成：
     *
     * - 第一个数据提供器 ID 为空。
     * - 第二个和所有后续的数据提供者 IDs 是："dp-1"，"dp-2" 等。
     */
    public $id;

    private $_sort;
    private $_pagination;
    private $_keys;
    private $_models;
    private $_totalCount;


    /**
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();
        if ($this->id === null) {
            if (self::$counter > 0) {
                $this->id = 'dp-' . self::$counter;
            }
            self::$counter++;
        }
    }

    /**
     * 准备将在当前页中可用的数据模型。
     * @return array 可用的数据模型
     */
    abstract protected function prepareModels();

    /**
     * 准备与当前可用数据模型关联的键。
     * @param array $models 可用的数据模型
     * @return array 键列表
     */
    abstract protected function prepareKeys($models);

    /**
     * 返回一个值，该值指示此数据提供器中的数据模型总数。
     * @return int 此数据提供器中的数据模型总数。
     */
    abstract protected function prepareTotalCount();

    /**
     * 准备数据模型和键。
     *
     * 此方法将准备可通过 [[getModels()]] 和 [[getKeys()]] 检索的
     * 数据模型和键。
     *
     * 如果没有调用此方法，则它将由 [[getModels()]] 和 [[getKeys()]] 隐式调用。
     *
     * @param bool $forcePrepare 是否强制进行数据准备，即使之前已经进行过。
     */
    public function prepare($forcePrepare = false)
    {
        if ($forcePrepare || $this->_models === null) {
            $this->_models = $this->prepareModels();
        }
        if ($forcePrepare || $this->_keys === null) {
            $this->_keys = $this->prepareKeys($this->_models);
        }
    }

    /**
     * 返回当前页中的数据模型。
     * @return array 当前页中的数据模型列表。
     */
    public function getModels()
    {
        $this->prepare();

        return $this->_models;
    }

    /**
     * 设置当前页中的数据模型。
     * @param array $models 当前页面中的模型
     */
    public function setModels($models)
    {
        $this->_models = $models;
    }

    /**
     * 返回与数据模型关联的键值。
     * @return array 与 [[getModels|models]] 对应的键值列表。[[getModels|models]] 中的每个数据模型
     * 都由该数组中相应的键值唯一标识。
     */
    public function getKeys()
    {
        $this->prepare();

        return $this->_keys;
    }

    /**
     * 设置与数据模型关联的键值。
     * @param array $keys 与 [[models]] 对应的键值列表
     */
    public function setKeys($keys)
    {
        $this->_keys = $keys;
    }

    /**
     * 返回当前页中的数据模型数。
     * @return int 当前页中的数据模型数。
     */
    public function getCount()
    {
        return count($this->getModels());
    }

    /**
     * 返回数据模型总数。
     * 当 [[pagination]] 为 false，值与 [[count]] 相同。
     * 否则，将调用 [[prepareTotalCount()]] 获取数量。
     * @return int 数据模型总数。
     */
    public function getTotalCount()
    {
        if ($this->getPagination() === false) {
            return $this->getCount();
        } elseif ($this->_totalCount === null) {
            $this->_totalCount = $this->prepareTotalCount();
        }

        return $this->_totalCount;
    }

    /**
     * 设置数据模型总数。
     * @param int $value 数据模型总数
     */
    public function setTotalCount($value)
    {
        $this->_totalCount = $value;
    }

    /**
     * 返回此数据提供器使用的分页对象。
     * 注意，我们应该先调用 [[prepare()]] 或者 [[getModels()]] 以获取
     * [[Pagination::totalCount]] 和 [[Pagination::pageCount]] 的正确的值。
     * @return Pagination|false 分页对象。如果为 false，则表示禁用分页。
     */
    public function getPagination()
    {
        if ($this->_pagination === null) {
            $this->setPagination([]);
        }

        return $this->_pagination;
    }

    /**
     * 为数据提供器设置分页组件。
     * @param array|Pagination|bool $value 被此数据提供器使用的分页件。
     * 可以是下列之一：
     *
     * - 一个用于创建分页对象的配置数组。“class” 元素默认
     *   为 'yii\data\Pagination'
     * - [[Pagination]] 或其子类的实例
     * - false，禁用分页
     *
     * @throws InvalidArgumentException
     */
    public function setPagination($value)
    {
        if (is_array($value)) {
            $config = ['class' => Pagination::className()];
            if ($this->id !== null) {
                $config['pageParam'] = $this->id . '-page';
                $config['pageSizeParam'] = $this->id . '-per-page';
            }
            $this->_pagination = Yii::createObject(array_merge($config, $value));
        } elseif ($value instanceof Pagination || $value === false) {
            $this->_pagination = $value;
        } else {
            throw new InvalidArgumentException('Only Pagination instance, configuration array or false is allowed.');
        }
    }

    /**
     * 返回此数据提供器使用的排序对象。
     * @return Sort|bool 排序对象。如果为 false，则表示排序被禁用。
     */
    public function getSort()
    {
        if ($this->_sort === null) {
            $this->setSort([]);
        }

        return $this->_sort;
    }

    /**
     * 设置此数据提供器的排序定义。
     * @param array|Sort|bool $value 被此数据提供器使用的排序定义。
     * 可以是下列之一：
     *
     * - 一个用于创建排序定义的配置数组。"class" 元素默认
     *   为 'yii\data\Sort'
     * - [[Sort]] 或其子类的实例。
     * - false，禁用排序
     *
     * @throws InvalidArgumentException
     */
    public function setSort($value)
    {
        if (is_array($value)) {
            $config = ['class' => Sort::className()];
            if ($this->id !== null) {
                $config['sortParam'] = $this->id . '-sort';
            }
            $this->_sort = Yii::createObject(array_merge($config, $value));
        } elseif ($value instanceof Sort || $value === false) {
            $this->_sort = $value;
        } else {
            throw new InvalidArgumentException('Only Sort instance, configuration array or false is allowed.');
        }
    }

    /**
     * 刷新数据提供器
     * 调用此方法后，如果再次调用 [[getModels()]]，[[getKeys()]] 或者 [[getTotalCount()]]，
     * 它们将重新执行查询并返回可用的最新数据。
     */
    public function refresh()
    {
        $this->_totalCount = null;
        $this->_models = null;
        $this->_keys = null;
    }
}
