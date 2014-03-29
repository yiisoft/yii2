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
use yii\debug\models\search\Db;

/**
 * Debugger panel that collects and displays database queries performed.
 *
 * @property array $profileLogs This property is read-only.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class DbPanel extends Panel
{
    /**
     * @var integer the threshold for determining whether the request has involved
     * critical number of DB queries. If the number of queries exceeds this number,
     * the execution is considered taking critical number of DB queries.
     */
    public $criticalQueryThreshold;
    /**
     * @var array db queries info extracted to array as models, to use with data provider.
     */
    private $_models;

    /**
     * @var array current database request timings
     */
    private $_timings;

    /**
     * @inheritdoc
     */
    public function getName()
    {
        return 'Database';
    }

    /**
     * @inheritdoc
     */
    public function getSummary()
    {
        $timings = $this->calculateTimings();
        $queryCount = count($timings);
        $queryTime = number_format($this->getTotalQueryTime($timings) * 1000) . ' ms';

        return Yii::$app->view->render('panels/db/summary', [
            'timings' => $this->calculateTimings(),
            'panel' => $this,
            'queryCount' => $queryCount,
            'queryTime' => $queryTime,
        ]);
    }

    /**
     * @inheritdoc
     */
    public function getDetail()
    {
        $searchModel = new Db();
        $dataProvider = $searchModel->search(Yii::$app->request->getQueryParams(), $this->getModels());

        return Yii::$app->view->render('panels/db/detail', [
            'panel' => $this,
            'dataProvider' => $dataProvider,
            'searchModel' => $searchModel,
        ]);
    }

    /**
     * Calculates given request profile timings.
     *
     * @return array timings [token, category, timestamp, traces, nesting level, elapsed time]
     */
    protected function calculateTimings()
    {
        if ($this->_timings === null) {
            $this->_timings = Yii::getLogger()->calculateTimings($this->data['messages']);
        }

        return $this->_timings;
    }

    /**
     * @inheritdoc
     */
    public function save()
    {
        return ['messages' => $this->getProfileLogs()];
    }

    /**
     * Returns all profile logs of the current request for this panel. It includes categories such as:
     * 'yii\db\Command::query', 'yii\db\Command::execute'.
     * @return array
     */
    public function getProfileLogs()
    {
        $target = $this->module->logTarget;

        return $target->filterMessages($target->messages, Logger::LEVEL_PROFILE, ['yii\db\Command::query', 'yii\db\Command::execute']);
    }

    /**
     * Returns total query time.
     *
     * @param  array   $timings
     * @return integer total time
     */
    protected function getTotalQueryTime($timings)
    {
        $queryTime = 0;

        foreach ($timings as $timing) {
            $queryTime += $timing['duration'];
        }

        return $queryTime;
    }

    /**
     * Returns an  array of models that represents logs of the current request.
     * Can be used with data providers such as \yii\data\ArrayDataProvider.
     * @return array models
     */
    protected function getModels()
    {
        if ($this->_models === null) {
            $this->_models = [];
            $timings = $this->calculateTimings();

            foreach ($timings as $seq => $dbTiming) {
                $this->_models[] = 	[
                    'type' => $this->getQueryType($dbTiming['info']),
                    'query' => $dbTiming['info'],
                    'duration' => ($dbTiming['duration'] * 1000), // in milliseconds
                    'trace' => $dbTiming['trace'],
                    'timestamp' => ($dbTiming['timestamp'] * 1000), // in milliseconds
                    'seq' => $seq,
                ];
            }
        }

        return $this->_models;
    }

    /**
     * Returns databse query type.
     *
     * @param  string $timing timing procedure string
     * @return string query type such as select, insert, delete, etc.
     */
    protected function getQueryType($timing)
    {
        $timing = ltrim($timing);
        preg_match('/^([a-zA-z]*)/', $timing, $matches);

        return count($matches) ? $matches[0] : '';
    }

    /**
     * Check if given queries count is critical according settings.
     *
     * @param  integer $count queries count
     * @return boolean
     */
    public function isQueryCountCritical($count)
    {
        return (($this->criticalQueryThreshold !== null) && ($count > $this->criticalQueryThreshold));
    }
}
