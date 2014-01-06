<?php

namespace yii\debug\models\search;

use yii\data\ArrayDataProvider;
use yii\debug\components\search\Filter;

/**
 * Profile represents the model behind the search form about current request profiling log.
 */
class Mail extends Base
{

    /**
     * @var string from attribute input search value
     */
    public $from;

    /**
     * @var string to attribute input search value
     */
    public $to;

    /**
     * @var string replyTo attribute input search value
     */
    public $replyTo;

    /**
     * @var string cc attribute input search value
     */
    public $cc;

    /**
     * @var string bcc attribute input search value
     */
    public $bcc;

    /**
     * @var string subject attribute input search value
     */
    public $subject;

    /**
     * @var string file attribute input search value
     */
    public $file;

	public function rules()
	{
		return [
			[['from', 'to', 'replyTo', 'cc', 'bcc', 'subject', 'file'], 'safe'],
		];
	}

	/**
	 * @inheritdoc
	 */
	public function attributeLabels()
	{
		return [
            'from' => 'From',
            'to' => 'To',
            'replyTo' => 'Reply to',
            'cc' => 'Copy receiver',
            'bcc' => 'Hidden copy receiver',
            'subject' => 'Subject',
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
				'attributes' => ['from', 'to', 'replyTo', 'cc', 'bcc'],
			],
		]);

		if (!($this->load($params) && $this->validate())) {
			return $dataProvider;
		}

		$filter = new Filter();
		$this->addCondition($filter, 'from', true);
		$this->addCondition($filter, 'to', true);
        $this->addCondition($filter, 'replyTo', true);
        $this->addCondition($filter, 'cc', true);
        $this->addCondition($filter, 'bcc', true);
		$dataProvider->allModels = $filter->filter($models);

		return $dataProvider;
	}

}
