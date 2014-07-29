<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\debug\panels;

use Yii;
use yii\debug\Panel;
use yii\log\Logger;
use yii\debug\models\search\Log;

/**
 * Debugger panel that collects and displays logs.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class LogPanel extends Panel
{
    /**
     * @var array log messages extracted to array as models, to use with data provider.
     */
    private $_models;

    /**
     * @inheritdoc
     */
    public function getName()
    {
        return 'Logs';
    }

    /**
     * @inheritdoc
     */
    public function getSummary()
    {
        return Yii::$app->view->render('panels/log/summary', ['data' => $this->data, 'panel' => $this]);
    }

    /**
     * @inheritdoc
     */
    public function getDetail()
    {
        $searchModel = new Log();
        $dataProvider = $searchModel->search(Yii::$app->request->getQueryParams(), $this->getModels());

        return Yii::$app->view->render('panels/log/detail', [
            'dataProvider' => $dataProvider,
            'panel' => $this,
            'searchModel' => $searchModel,
        ]);
    }

    /**
     * @inheritdoc
     */
    public function save()
    {
        $target = $this->module->logTarget;
        $messages = $target->filterMessages($target->messages, Logger::LEVEL_ERROR | Logger::LEVEL_INFO | Logger::LEVEL_WARNING | Logger::LEVEL_TRACE);
        return ['messages' => $messages];
    }

    /**
     * Returns an array of models that represents logs of the current request.
     * Can be used with data providers, such as \yii\data\ArrayDataProvider.
     *
     * @param boolean $refresh if need to build models from log messages and refresh them.
     * @return array models
     */
    protected function getModels($refresh = false)
    {
        if ($this->_models === null || $refresh) {
            $this->_models = [];

            foreach ($this->data['messages'] as $message) {
                $this->_models[] = 	[
                    'message' => $message[0],
                    'level' => $message[1],
                    'category' => $message[2],
                    'time' => ($message[3] * 1000), // time in milliseconds
                    'trace' => $message[4]
                ];
            }
        }

        return $this->_models;
    }
}
