<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\rest;

use Yii;
use yii\base\Component;
use yii\base\Model;
use yii\data\DataProviderInterface;
use yii\data\Pagination;
use yii\helpers\ArrayHelper;
use yii\web\Link;
use yii\web\Request;
use yii\web\Response;

/**
 * Serializer converts resource objects and collections into array representation.
 *
 * Serializer is mainly used by REST controllers to convert different objects into array representation
 * so that they can be further turned into different formats, such as JSON, XML, by response formatters.
 *
 * The default implementation handles resources as [[Model]] objects and collections as objects
 * implementing [[DataProviderInterface]]. You may override [[serialize()]] to handle more types.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class Serializer extends Component
{
	/**
	 * @var string the name of the query parameter containing the information about which fields should be returned
	 * for a [[Model]] object. If the parameter is not provided or empty, the default set of fields as defined
	 * by [[Model::fields()]] will be returned.
	 */
	public $fieldsParam = 'fields';
	/**
	 * @var string the name of the query parameter containing the information about which fields should be returned
	 * in addition to those listed in [[fieldsParam]] for a resource object.
	 */
	public $expandParam = 'expand';
	/**
	 * @var string the name of the HTTP header containing the information about total number of data items.
	 * This is used when serving a resource collection with pagination.
	 */
	public $totalCountHeader = 'X-Pagination-Total-Count';
	/**
	 * @var string the name of the HTTP header containing the information about total number of pages of data.
	 * This is used when serving a resource collection with pagination.
	 */
	public $pageCountHeader = 'X-Pagination-Page-Count';
	/**
	 * @var string the name of the HTTP header containing the information about the current page number (1-based).
	 * This is used when serving a resource collection with pagination.
	 */
	public $currentPageHeader = 'X-Pagination-Current-Page';
	/**
	 * @var string the name of the HTTP header containing the information about the number of data items in each page.
	 * This is used when serving a resource collection with pagination.
	 */
	public $perPageHeader = 'X-Pagination-Per-Page';
	/**
	 * @var string the name of the envelope (e.g. `items`) for returning the resource objects in a collection.
	 * This is used when serving a resource collection. When this is set and pagination is enabled, the serializer
	 * will return a collection in the following format:
	 *
	 * ```php
	 * [
	 *     'items' => [...],  // assuming collectionEnvelope is "items"
	 *     '_links' => {  // pagination links as returned by Pagination::getLinks()
	 *         'self' => '...',
	 *         'next' => '...',
	 *         'last' => '...',
	 *     },
	 *     '_meta' => {  // meta information as returned by Pagination::toArray()
	 *         'totalCount' => 100,
	 *         'pageCount' => 5,
	 *         'currentPage' => 1,
	 *         'perPage' => 20,
	 *     },
	 * ]
	 * ```
	 *
	 * If this property is not set, the resource arrays will be directly returned without using envelope.
	 * The pagination information as shown in `_links` and `_meta` can be accessed from the response HTTP headers.
	 */
	public $collectionEnvelope;
	/**
	 * @var Request the current request. If not set, the `request` application component will be used.
	 */
	public $request;
	/**
	 * @var Response the response to be sent. If not set, the `response` application component will be used.
	 */
	public $response;

	/**
	 * @inheritdoc
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
	 * Serializes the given data into a format that can be easily turned into other formats.
	 * This method mainly converts the objects of recognized types into array representation.
	 * It will not do conversion for unknown object types or non-object data.
	 * The default implementation will handle [[Model]] and [[DataProviderInterface]].
	 * You may override this method to support more object types.
	 * @param mixed $data the data to be serialized.
	 * @return mixed the converted data.
	 */
	public function serialize($data)
	{
		if ($data instanceof Model) {
			return $data->hasErrors() ? $this->serializeModelErrors($data) : $this->serializeModel($data);
		} elseif ($data instanceof DataProviderInterface) {
			return $this->serializeDataProvider($data);
		} else {
			return $data;
		}
	}

	/**
	 * @return array the names of the requested fields. The first element is an array
	 * representing the list of default fields requested, while the second element is
	 * an array of the extra fields requested in addition to the default fields.
	 * @see Model::fields()
	 * @see Model::extraFields()
	 */
	protected function getRequestedFields()
	{
		$fields = $this->request->get($this->fieldsParam);
		$expand = $this->request->get($this->expandParam);
		return [
			preg_split('/\s*,\s*/', $fields, -1, PREG_SPLIT_NO_EMPTY),
			preg_split('/\s*,\s*/', $expand, -1, PREG_SPLIT_NO_EMPTY),
		];
	}

	/**
	 * Serializes a data provider.
	 * @param DataProviderInterface $dataProvider
	 * @return array the array representation of the data provider.
	 */
	protected function serializeDataProvider($dataProvider)
	{
		$models = $this->serializeModels($dataProvider->getModels());

		if (($pagination = $dataProvider->getPagination()) !== false) {
			$this->addPaginationHeaders($pagination);
		}

		if ($this->request->getIsHead()) {
			return null;
		} elseif ($this->collectionEnvelope === null) {
			return $models;
		} else {
			$result = [
				$this->collectionEnvelope => $models,
			];
			if ($pagination !== false) {
				return array_merge($result, $this->serializePagination($pagination));
			} else {
				return $result;
			}
		}
	}

	/**
	 * Serializes a pagination into an array.
	 * @param Pagination $pagination
	 * @return array the array representation of the pagination
	 * @see addPaginationHeader()
	 */
	protected function serializePagination($pagination)
	{
		return [
			'_links' => Link::serialize($pagination->getLinks(true)),
			'_meta' => [
				'totalCount' => $pagination->totalCount,
				'pageCount' => $pagination->getPageCount(),
				'currentPage' => $pagination->getPage(),
				'perPage' => $pagination->getPageSize(),
			],
		];
	}

	/**
	 * Adds HTTP headers about the pagination to the response.
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
	 * Serializes a model object.
	 * @param Model $model
	 * @return array the array representation of the model
	 */
	protected function serializeModel($model)
	{
		if ($this->request->getIsHead()) {
			return null;
		} else {
			list ($fields, $expand) = $this->getRequestedFields();
			return $model->toArray($fields, $expand);
		}
	}

	/**
	 * Serializes the validation errors in a model.
	 * @param Model $model
	 * @return array the array representation of the errors
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
	 * Serializes a set of models.
	 * @param array $models
	 * @return array the array representation of the models
	 */
	protected function serializeModels(array $models)
	{
		list ($fields, $expand) = $this->getRequestedFields();
		foreach ($models as $i => $model) {
			if ($model instanceof Model) {
				$models[$i] = $model->toArray($fields, $expand);
			} elseif (is_array($model)) {
				$models[$i] = ArrayHelper::toArray($model);
			}
		}
		return $models;
	}
}
