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
use yii\debug\models\search\Profile;

/**
 * Debugger panel that collects and displays performance profiling info.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class ProfilingPanel extends Panel
{
    /**
     * @var array current request profile timings
     */
    private $_models;

    /**
     * @inheritdoc
     */
    public function getName()
    {
        return 'Profiling';
    }

    /**
     * @inheritdoc
     */
    public function getSummary()
    {
        return Yii::$app->view->render('panels/profile/summary', [
            'memory' => sprintf('%.1f MB', $this->data['memory'] / 1048576),
            'time' => number_format($this->data['time'] * 1000) . ' ms',
            'panel' => $this
        ]);
    }

    /**
     * @inheritdoc
     */
    public function getDetail()
    {
        $searchModel = new Profile();
        $dataProvider = $searchModel->search(Yii::$app->request->getQueryParams(), $this->getModels());

        return Yii::$app->view->render('panels/profile/detail', [
            'panel' => $this,
            'dataProvider' => $dataProvider,
            'searchModel' => $searchModel,
            'memory' => sprintf('%.1f MB', $this->data['memory'] / 1048576),
            'time' => number_format($this->data['time'] * 1000) . ' ms',
        ]);
    }

    /**
     * @inheritdoc
     */
    public function save()
    {
        $target = $this->module->logTarget;
        $messages = $target->filterMessages($target->messages, Logger::LEVEL_PROFILE);
        return [
            'memory' => memory_get_peak_usage(),
            'time' => microtime(true) - YII_BEGIN_TIME,
            'messages' => $messages,
        ];
    }

    /**
     * Returns array of profiling models that can be used in a data provider.
     * @return array models
     */
    protected function getModels()
    {
        if ($this->_models === null) {
            $this->_models = [];
            $timings = Yii::getLogger()->calculateTimings($this->data['messages']);

            foreach ($timings as $seq => $profileTiming) {
                $this->_models[] = 	[
                    'duration' => $profileTiming['duration'] * 1000, // in milliseconds
                    'category' => $profileTiming['category'],
                    'info' => $profileTiming['info'],
                    'level' => $profileTiming['level'],
                    'timestamp' => $profileTiming['timestamp'] * 1000, //in milliseconds
                    'seq' => $seq,
                ];
            }
        }

        return $this->_models;
    }
}
