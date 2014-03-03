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
use yii\web\Request;
use yii\web\Response;

/**
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class Serializer extends Component
{
	public $fieldsParam = 'fields';
	public $expandParam = 'expand';
	public $totalCountHeader = 'X-Pagination-Total-Count';
	public $pageCountHeader = 'X-Pagination-Page-Count';
	public $currentPageHeader = 'X-Pagination-Current-Page';
	public $perPageHeader = 'X-Pagination-Per-Page';
	/**
	 * @var Request
	 */
	public $request;
	/**
	 * @var Response
	 */
	public $response;

	public function init()
	{
		if ($this->request === null) {
			$this->request = Yii::$app->getRequest();
		}
		if ($this->response === null) {
			$this->response = Yii::$app->getResponse();
		}
	}

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
	 * @param DataProviderInterface $dataProvider
	 * @return array
	 */
	protected function serializeDataProvider($dataProvider)
	{
		$models = $dataProvider->getModels();

		if (($pagination = $dataProvider->getPagination()) !== false) {
			$this->addPaginationHeaders($pagination);
		}

		if ($this->request->getIsHead()) {
			return null;
		} else {
			return $this->serializeModels($models);
		}
	}

	/**
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
	 * @param Model $model
	 * @return array
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
	 * @param Model $model
	 * @return array
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
