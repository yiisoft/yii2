<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\rest;

use Yii;
use yii\base\Arrayable;
use yii\base\Component;
use yii\base\Model;
use yii\data\DataProviderInterface;
use yii\data\Pagination;
use yii\helpers\ArrayHelper;
use yii\web\Link;
use yii\web\Request;
use yii\web\Response;

/**
 * Serializer 将资源对象和集合转换为数组表示。
 *
 * Serializer 主要由 REST  控制器用于将不同的对象转换为数组表示
 * 这样它们可以通过响应格式化程序进一步转换为不同的格式，例如JSON，XML。
 *
 * 默认的实现：将 [[Model]] 对象作为资源处理，将 [[DataProviderInterface]] 对象作为集合处理。
 * 你可以覆盖 [[serialize()]] 来处理更多类型。
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class Serializer extends Component
{
    /**
     * @var string 查询参数的名称，包含有关应返回哪些字段的信息
     * 对于 [[Model]] 对象，如果未提供参数或为空，则 [[Model::fields()]] 定义的默认字段集
     * 将被返回。
     */
    public $fieldsParam = 'fields';
    /**
     * @var string 查询参数的名称，包含有关应返回哪些字段的信息
     * 除了资源对象的 [[fieldsParam]] 中列出的之外。
     */
    public $expandParam = 'expand';
    /**
     * @var string HTTP 标头名称，包含有关数据项总数的信息。
     * 在使用分页提供资源集合时使用此选项。
     */
    public $totalCountHeader = 'X-Pagination-Total-Count';
    /**
     * @var string HTTP 标头名称，包含有关数据项总页数的信息。
     * 在使用分页提供资源集合时使用此选项。
     */
    public $pageCountHeader = 'X-Pagination-Page-Count';
    /**
     * @var string HTTP 标头名称，包含有关数据项当前页数的信息。
     * 在使用分页提供资源集合时使用此选项。
     */
    public $currentPageHeader = 'X-Pagination-Current-Page';
    /**
     * @var string HTTP 标头名称，包含有关数据项每页的数据个数的信息
     * 在使用分页提供资源集合时使用此选项。
     */
    public $perPageHeader = 'X-Pagination-Per-Page';
    /**
     * @var string 用于返回集合中资源对象的索引名称（例如 `items`）。
     * 在提供资源集合时使用。设置此选项并启用分页后
     * 将以以下格式返回集合：
     *
     * ```php
     * [
     *     'items' => [...],  // 假如设的索引是 "items"
     *     '_links' => {  // 分页链接集，此数据通过 Pagination::getLinks() 处理返回
     *         'self' => '...',
     *         'next' => '...',
     *         'last' => '...',
     *     },
     *     '_meta' => {  // Pagination::toArray() 返回的 meta 信息。
     *         'totalCount' => 100,
     *         'pageCount' => 5,
     *         'currentPage' => 1,
     *         'perPage' => 20,
     *     },
     * ]
     * ```
     *
     * 如果未设置此属性，则将直接返回资源数组，而不使用索引。
     * `_links` 和 `_meta` 中显示的分页信息被放置到 HTTP 头部以供访问。
     */
    public $collectionEnvelope;
    /**
     * @var string 用于返回链接对象的索引名称（例如 `_links`）。
     * `collectionEnvelope` 设置后它才生效。
     * @since 2.0.4
     */
    public $linksEnvelope = '_links';
    /**
     * @var string 用于返回链接对象的索引名称（例如 `_meta`）。
     * `collectionEnvelope` 设置后它才生效。
     * @since 2.0.4
     */
    public $metaEnvelope = '_meta';
    /**
     * @var Request 当前请求，未设置则默认应用中的 `request` 组件。
     */
    public $request;
    /**
     * @var Response 当前响应，未设置则默认应用中的 `request` 组件。
     */
    public $response;
    /**
     * @var bool 在序列化集合数据时是否保留数组键。
     * 将其设置为 `true` 以允许将集合序列化为 JSON 对象时，数组键用于索引对应的模型对象。
     * 无论如何，默认是将所有集合序列化为数组
     * 如何索引数组。
     * @see serializeDataProvider()
     * @since 2.0.10
     */
    public $preserveKeys = false;


    /**
     * {@inheritdoc}
     */
    public function init()
    {
        if ($this->request === null) {
            $this->request = Yii::$app->getRequest();
        }
        if ($this->response === null) {
            $this->response = Yii::$app->getResponse();
        }
    }

    /**
     * 将给定数据序列化为可轻松转换为其他格式的格式。
     * 此方法主要将识别类型的对象转换为数组表示。
     * 它不会对未知对象类型或非对象数据进行转换。
     * 默认的实现是仅仅处理 [[Model]] 和 [[DataProviderInterface]] 。
     * 你可以覆盖此方法以支持更多对象类型。
     * @param mixed $data 要被序列化的数据。
     * @return mixed the 转换后的数据。
     */
    public function serialize($data)
    {
        if ($data instanceof Model && $data->hasErrors()) {
            return $this->serializeModelErrors($data);
        } elseif ($data instanceof Arrayable) {
            return $this->serializeModel($data);
        } elseif ($data instanceof DataProviderInterface) {
            return $this->serializeDataProvider($data);
        }

        return $data;
    }

    /**
     * @return array 所请求字段的名称。 The first element is an array
     * 第一个元素是一个数组，表示请求的默认字段列表，
     * 而第二个元素是除默认字段外，还请求的一系列额外字段列表。
     * @see Model::fields()
     * @see Model::extraFields()
     */
    protected function getRequestedFields()
    {
        $fields = $this->request->get($this->fieldsParam);
        $expand = $this->request->get($this->expandParam);

        return [
            is_string($fields) ? preg_split('/\s*,\s*/', $fields, -1, PREG_SPLIT_NO_EMPTY) : [],
            is_string($expand) ? preg_split('/\s*,\s*/', $expand, -1, PREG_SPLIT_NO_EMPTY) : [],
        ];
    }

    /**
     * 序列化一个数据提供器。
     * @param DataProviderInterface $dataProvider
     * @return array 数据提供器的数组表示。
     */
    protected function serializeDataProvider($dataProvider)
    {
        if ($this->preserveKeys) {
            $models = $dataProvider->getModels();
        } else {
            $models = array_values($dataProvider->getModels());
        }
        $models = $this->serializeModels($models);

        if (($pagination = $dataProvider->getPagination()) !== false) {
            $this->addPaginationHeaders($pagination);
        }

        if ($this->request->getIsHead()) {
            return null;
        } elseif ($this->collectionEnvelope === null) {
            return $models;
        }

        $result = [
            $this->collectionEnvelope => $models,
        ];
        if ($pagination !== false) {
            return array_merge($result, $this->serializePagination($pagination));
        }

        return $result;
    }

    /**
     * 序列化分页器为数组格式。
     * @param Pagination $pagination
     * @return array 分页器的数组表示
     * @see addPaginationHeaders()
     */
    protected function serializePagination($pagination)
    {
        return [
            $this->linksEnvelope => Link::serialize($pagination->getLinks(true)),
            $this->metaEnvelope => [
                'totalCount' => $pagination->totalCount,
                'pageCount' => $pagination->getPageCount(),
                'currentPage' => $pagination->getPage() + 1,
                'perPage' => $pagination->getPageSize(),
            ],
        ];
    }

    /**
     * 将分页信息加到 HTTP 响应头部。
     * @param Pagination $pagination
     */
    protected function addPaginationHeaders($pagination)
    {
        $links = [];
        foreach ($pagination->getLinks(true) as $rel => $url) {
            $links[] = "<$url>; rel=$rel";
        }

        $this->response->getHeaders()
            ->set($this->totalCountHeader, $pagination->totalCount)
            ->set($this->pageCountHeader, $pagination->getPageCount())
            ->set($this->currentPageHeader, $pagination->getPage() + 1)
            ->set($this->perPageHeader, $pagination->pageSize)
            ->set('Link', implode(', ', $links));
    }

    /**
     * 序列化模型对象。
     * @param Arrayable $model
     * @return array 模型对象的数组表示
     */
    protected function serializeModel($model)
    {
        if ($this->request->getIsHead()) {
            return null;
        }

        list($fields, $expand) = $this->getRequestedFields();
        return $model->toArray($fields, $expand);
    }

    /**
     * 序列化模型中的验证错误信息。
     * @param Model $model
     * @return array 验证错误的数组表示
     */
    protected function serializeModelErrors($model)
    {
        $this->response->setStatusCode(422, 'Data Validation Failed.');
        $result = [];
        foreach ($model->getFirstErrors() as $name => $message) {
            $result[] = [
                'field' => $name,
                'message' => $message,
            ];
        }

        return $result;
    }

    /**
     * 序列化一组模型
     * @param array $models
     * @return array 一组模型的数组表示
     */
    protected function serializeModels(array $models)
    {
        list($fields, $expand) = $this->getRequestedFields();
        foreach ($models as $i => $model) {
            if ($model instanceof Arrayable) {
                $models[$i] = $model->toArray($fields, $expand);
            } elseif (is_array($model)) {
                $models[$i] = ArrayHelper::toArray($model);
            }
        }

        return $models;
    }
}
