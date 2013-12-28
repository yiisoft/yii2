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
	 *
	 * @var integer sql count attribute input search value
	 */
	public $sqlCount;

	/**
	 * @var array critical codes, used to determine grid row options.
	 */
	public $criticalCodes = [400, 404, 500];

	public function rules()
	{
		return [
			[['tag', 'ip', 'method', 'ajax', 'url', 'statusCode', 'sqlCount'], 'safe'],
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
			'sqlCount' => 'Total queries count',
		];
	}

	/**
	 * Returns data provider with filled models. Filter applied if needed.
	 * @param array $params
	 * @param array $models
	 * @return \yii\data\ArrayDataProvider
	 */
	public function search($params, $models)
	{
		$dataProvider = new ArrayDataProvider([
			'allModels' => $models,
			'sort' => [
				'attributes' => ['method', 'ip', 'tag', 'time', 'statusCode', 'sqlCount'],
			],
			'pagination' => [
				'pageSize' => 10,
			],
		]);

		if (!($this->load($params) && $this->validate())) {
			return $dataProvider;
		}

		$filter = new Filter();
		$this->addCondition($filter, 'tag', true);
		$this->addCondition($filter, 'ip', true);
		$this->addCondition($filter, 'method');
		$this->addCondition($filter, 'ajax');
		$this->addCondition($filter, 'url', true);
		$this->addCondition($filter, 'statusCode');
		$this->addCondition($filter, 'sqlCount');
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

	/**
	 * @param Filter $filter
	 * @param string $attribute
	 * @param boolean $partial
	 */
	public function addCondition($filter, $attribute, $partial = false)
	{
		$value = $this->$attribute;

		if (mb_strpos($value, '>') !== false) {

			$value = intval(str_replace('>', '', $value));
			$filter->addMatch($attribute, new matches\Greater(['value' => $value]));

		} elseif (mb_strpos($value, '<') !== false) {

			$value = intval(str_replace('<', '', $value));
			$filter->addMatch($attribute, new matches\Lower(['value' => $value]));

		} else {
			$filter->addMatch($attribute, new matches\Exact(['value' => $value, 'partial' => $partial]));
		}

	}

}
