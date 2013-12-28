<?php

namespace yii\debug\models\search;

use yii\base\Model;
use yii\data\ArrayDataProvider;
use yii\debug\components\search\Filter;

/**
 * Debug represents the model behind the search form about requests manifest data.
 */
class Debug extends Model
{
	/**
	 * @var string tag attribute input search value
	 */
	public $tag;

	/**
	 * @var string ip attribute input search value
	 */
	public $ip;

	/**
	 * @var string method attribute input search value
	 */
	public $method;

	/**
	 * @var integer ajax attribute input search value
	 */
	public $ajax;

	/**
	 * @var string url attribute input search value
	 */
	public $url;

	/**
	 * @var string status code attribute input search value 
	 */
	public $status_code;

	/**
	 * @var array critical codes, used to determine grid row options.
	 */
	protected $criticalCodes = [400,404,500];

	public function rules()
	{
		return [
			[['tag', 'ip', 'method', 'ajax', 'url','status_code'], 'safe'],
		];
	}

	/**
	 * @inheritdoc
	 */
	public function attributeLabels()
	{
		return [
			'tag' => 'Tag',
			'ip' => 'Ip',
			'method' => 'Method',
			'ajax' => 'Ajax',
			'url' => 'url',
			'status_code' => 'Status code',
		];
	}

	/**
	 * Returns data provider with filled models. Filter applied if needed.
	 * @param type $params
	 * @param type $models
	 * @return \yii\data\ArrayDataProvider
	 */
	public function search($params, $models)
	{
		$dataProvider = new ArrayDataProvider([
			'allModels' => $models,
			'sort' => [
				'attributes' => ['method', 'ip','tag','time','status_code'],
			],
			'pagination' => [
				'pageSize' => 10,
			],
		]);

		if (!($this->load($params) && $this->validate())) {
			return $dataProvider;
		}

		$filter = new Filter();
		$filter->addMatch('tag', $this->tag, true);
		$filter->addMatch('ip', $this->ip);
		$filter->addMatch('method', $this->method);
		$filter->addMatch('ajax', $this->ajax);
		$filter->addMatch('url', $this->url, true);
		$filter->addMatch('status_code', $this->status_code);
		$dataProvider->allModels = $filter->filter($models);

		return $dataProvider;
	}

	/**
	 * Checks if the code is critical: 400 or greater, 500 or greater.
	 * @param integer $code
	 * @return bool
	 */
	public function isCodeCritical($code)
	{
		return in_array($code, $this->criticalCodes);
	}

}

