<?php

namespace yii\debug\models\search;

use yii\data\ArrayDataProvider;
use yii\debug\components\search\Filter;

/**
 * Db represents the model behind the search form about current request database queries.
 */
class Db extends Base
{

	/**
	 * @var string type attribute input search value
	 */
	public $type;

	/**
	 * @var integer query attribute input search value
	 */
	public $query;

	public function rules()
	{
		return [
			[['type', 'query'], 'safe'],
		];
	}

	/**
	 * @inheritdoc
	 */
	public function attributeLabels()
	{
		return [
			'type' => 'Type',
			'query' => 'Query',
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
			'pagination' => [
				'pageSize' => 10,
			],
			'sort' => [
				'attributes' => ['duration', 'seq', 'type', 'query'],
				'defaultOrder' => [
					'duration' => SORT_DESC,
				],
			],
		]);

		if (!($this->load($params) && $this->validate())) {
			return $dataProvider;
		}

		$filter = new Filter();
		$this->addCondition($filter, 'type', true);
		$this->addCondition($filter, 'query', true);
		$dataProvider->allModels = $filter->filter($models);

		return $dataProvider;
	}

}
