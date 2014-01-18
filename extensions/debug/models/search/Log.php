<?php

namespace yii\debug\models\search;

use yii\data\ArrayDataProvider;
use yii\debug\components\search\Filter;

/**
 * Log represents the model behind the search form about current request log.
 */
class Log extends Base
{

	/**
	 * @var string ip attribute input search value
	 */
	public $level;

	/**
	 * @var string method attribute input search value
	 */
	public $category;

	/**
	 * @var integer message attribute input search value
	 */
	public $message;

	public function rules()
	{
		return [
			[['level', 'message', 'category'], 'safe'],
		];
	}

	/**
	 * @inheritdoc
	 */
	public function attributeLabels()
	{
		return [
			'level' => 'Level',
			'category' => 'Category',
			'message' => 'Message',
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
			'pagination' => false,
			'sort' => [
				'attributes' => ['time', 'level', 'category', 'message'],
			],
		]);

		if (!($this->load($params) && $this->validate())) {
			return $dataProvider;
		}

		$filter = new Filter();
		$this->addCondition($filter, 'level');
		$this->addCondition($filter, 'category', true);
		$this->addCondition($filter, 'message', true);
		$dataProvider->allModels = $filter->filter($models);

		return $dataProvider;
	}

}
