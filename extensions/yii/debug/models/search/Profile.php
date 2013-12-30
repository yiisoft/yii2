<?php

namespace yii\debug\models\search;

use yii\data\ArrayDataProvider;
use yii\debug\components\search\Filter;

/**
 * Profile represents the model behind the search form about current request profiling log.
 */
class Profile extends Base
{

	/**
	 * @var string method attribute input search value
	 */
	public $category;

	/**
	 * @var integer info attribute input search value
	 */
	public $info;

	public function rules()
	{
		return [
			[['category', 'info'], 'safe'],
		];
	}

	/**
	 * @inheritdoc
	 */
	public function attributeLabels()
	{
		return [
			'category' => 'Category',
			'info' => 'Info',
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
				'attributes' => ['category','info','duration'],
				'defaultOrder' => [
					'duration' => SORT_DESC,
				],
			],
		]);

		if (!($this->load($params) && $this->validate())) {
			return $dataProvider;
		}

		$filter = new Filter();
		$this->addCondition($filter, 'category', true);
		$this->addCondition($filter, 'info', true);
		$dataProvider->allModels = $filter->filter($models);

		return $dataProvider;
	}

}
