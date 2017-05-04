<?php
/**
 * Created by PhpStorm.
 * User: ivan
 * Date: 2/6/15
 * Time: 10:00 PM
 */

namespace yii\db;


use Yii;
use yii\base\Component;

class SubJoin extends Query{

    /**
     * Creates a DB command that can be used to execute this query.
     * @param Connection $db the database connection used to generate the SQL statement.
     * If this parameter is not given, the `db` application component will be used.
     * @return Command the created DB command instance.
     */
    public function createCommand($db = null)
    {
        if ($db === null) {
            $db = Yii::$app->getDb();
        }
        list ($sql, $params) = $db->getQueryBuilder()->buildSubJoin($this);

        return $db->createCommand($sql, $params);
    }


    public function subJoin($table, $params = [])
    {
        if(is_array($this->join) && count($this->join)>0){
            throw new Exception('SubJoin could be only in first position');
        }
        $this->join[] = ['', $table];
        return $this->addParams($params);
    }
}
