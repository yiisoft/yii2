<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yiiunit\framework\db\mssql;

use yii\db\BatchQueryResult;

/**
 * @group db
 * @group mssql
 */
class BatchQueryResultTest extends \yiiunit\framework\db\BatchQueryResultTest
{
    public $driverName = 'sqlsrv';
    private $noMoreRowsErrorMessage = 'SQLSTATE[IMSSP]: There are no more rows in the active result set.  Since this result set is not scrollable, no more data may be retrieved.';

    protected function getAllRowsFromBach(BatchQueryResult $batch)
    {
        $allRows = [];
        try {
            foreach ($batch as $rows) {
                $allRows = array_merge($allRows, $rows);
            }
        } catch (\PDOException $e) {
            if ($e->getMessage() !== $this->noMoreRowsErrorMessage) {
                throw $e;
            }
        }

        return $allRows;
    }

    protected function getAllRowsFromEach(BatchQueryResult $each)
    {
        $allRows = [];
        try {
            foreach ($each as $index => $row) {
                $allRows[$index] = $row;
            }
        } catch (\PDOException $e) {
            if ($e->getMessage() !== $this->noMoreRowsErrorMessage) {
                throw $e;
            }
        }

        return $allRows;
    }
}
