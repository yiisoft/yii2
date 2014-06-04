<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\debug\models\search;

use yii\data\ArrayDataProvider;
use yii\debug\components\search\Filter;

/**
 * Search model for current request log.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @author Mark Jebri <mark.github@yandex.ru>
 * @since 2.0
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

    /**
     * @inheritdoc
     */
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
     *
     * @param array $params an array of parameter values indexed by parameter names
     * @param array $models data to return provider for
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
