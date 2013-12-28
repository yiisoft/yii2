<?php

namespace yii\debug\models\search;

use yii\base\Model;
use yii\data\ArrayDataProvider;
use yii\debug\components\search\Filter;
use yii\debug\components\search\matches;

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
	public $statusCode;

	/**
	 * @var array critical codes, used to determine grid row options.
	 */
	public $criticalCodes = [400, 404, 500];

	public function rules()
	{
		return [
			[['tag', 'ip', 'method', 'ajax', 'url','statusCode'], 'safe'],
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
			'statusCode' => 'Status code',
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
				'attributes' => ['method', 'ip','tag','time','statusCode'],
			],
			'pagination' => [
				'pageSize' => 10,
			],
		]);

		if (!($this->load($params) && $this->validate())) {
			return $dataProvider;
		}

		$filter = new Filter();
		$filter->addMatch('tag', new matches\Exact(['value' => $this->tag, 'partial' => true]));
		$filter->addMatch('ip', new matches\Exact(['value' => $this->ip, 'partial' => true]));
		$filter->addMatch('method', new matches\Exact(['value' => $this->method]));
		$filter->addMatch('ajax', new matches\Exact(['value' => $this->ajax]));
		$filter->addMatch('url', new matches\Exact(['value' => $this->url]));
		$filter->addMatch('statusCode', new matches\Exact(['value' => $this->statusCode]));
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

