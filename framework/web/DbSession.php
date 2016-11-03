<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\web;

use Yii;
use yii\db\Connection;
use yii\db\Query;
use yii\base\InvalidConfigException;
use yii\di\Instance;

/**
 * DbSession extends [[Session]] by using database as session data storage.
 *
 * By default, DbSession stores session data in a DB table named 'session'. This table
 * must be pre-created. The table name can be changed by setting [[sessionTable]].
 *
 * The following example shows how you can configure the application to use DbSession:
 * Add the following to your application config under `components`:
 *
 * ```php
 * 'session' => [
 *     'class' => 'yii\web\DbSession',
 *     // 'db' => 'mydb',
 *     // 'sessionTable' => 'my_session',
 * ]
 * ```
 *
 * DbSession extends [[MultiFieldSession]], thus it allows saving extra fields into the [[sessionTable]].
 * Refer to [[MultiFieldSession]] for more details.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class DbSession extends MultiFieldSession
{
    /**
     * @var Connection|array|string the DB connection object or the application component ID of the DB connection.
     * After the DbSession object is created, if you want to change this property, you should only assign it
     * with a DB connection object.
     * Starting from version 2.0.2, this can also be a configuration array for creating the object.
     */
    public $db = 'db';
    /**
     * @var string the name of the DB table that stores the session data.
     * The table should be pre-created as follows:
     *
     * ```sql
     * CREATE TABLE session
     * (
     *     id CHAR(40) NOT NULL PRIMARY KEY,
     *     expire INTEGER,
     *     data BLOB
     * )
     * ```
     *
     * where 'BLOB' refers to the BLOB-type of your preferred DBMS. Below are the BLOB type
     * that can be used for some popular DBMS:
     *
     * - MySQL: LONGBLOB
     * - PostgreSQL: BYTEA
     * - MSSQL: BLOB
     *
     * When using DbSession in a production server, we recommend you create a DB index for the 'expire'
     * column in the session table to improve the performance.
     *
     * Note that according to the php.ini setting of `session.hash_function`, you may need to adjust
     * the length of the `id` column. For example, if `session.hash_function=sha256`, you should use
     * length 64 instead of 40.
     */
    public $sessionTable = '{{%session}}';


    /**
     * Initializes the DbSession component.
     * This method will initialize the [[db]] property to make sure it refers to a valid DB connection.
     * @throws InvalidConfigException if [[db]] is invalid.
     */
    public function init()
    {
        parent::init();
        $this->db = Instance::ensure($this->db, Connection::className());
    }

    /**
     * Updates the current session ID with a newly generated one .
     * Please refer to <http://php.net/session_regenerate_id> for more details.
     * @param boolean $deleteOldSession Whether to delete the old associated session file or not.
     */
    public function regenerateID($deleteOldSession = false)
    {
        $oldID = session_id();

        // if no session is started, there is nothing to regenerate
        if (empty($oldID)) {
            return;
        }

        parent::regenerateID(false);
        $newID = session_id();

        $query = new Query();
        $row = $query->from($this->sessionTable)
            ->where(['id' => $oldID])
            ->createCommand($this->db)
            ->queryOne();
        if ($row !== false) {
            if ($deleteOldSession) {
                $this->db->createCommand()
                    ->update($this->sessionTable, ['id' => $newID], ['id' => $oldID])
                    ->execute();
            } else {
                $row['id'] = $newID;
                $this->db->createCommand()
                    ->insert($this->sessionTable, $row)
                    ->execute();
            }
        } else {
            // shouldn't reach here normally
            $this->db->createCommand()
                ->insert($this->sessionTable, $this->composeFields($newID, ''))
                ->execute();
        }
    }

    /**
     * Session read handler.
     * Do not call this method directly.
     * @param string $id session ID
     * @return string the session data
     */
    public function readSession($id)
    {
        $query = new Query();
        $query->from($this->sessionTable)
            ->where('[[expire]]>:expire AND [[id]]=:id', [':expire' => time(), ':id' => $id]);

        if ($this->readCallback !== null) {
            $fields = $query->one($this->db);
            return $fields === false ? '' : $this->extractData($fields);
        }

        $data = $query->select(['data'])->scalar($this->db);
        return $data === false ? '' : $data;
    }

    /**
     * Session write handler.
     * Do not call this method directly.
     * @param string $id session ID
     * @param string $data session data
     * @return boolean whether session write is successful
     */
    public function writeSession($id, $data)
    {
        // exception must be caught in session write handler
        // http://us.php.net/manual/en/function.session-set-save-handler.php#refsect1-function.session-set-save-handler-notes
        try {
            $query = new Query;
            $exists = $query->select(['id'])
                ->from($this->sessionTable)
                ->where(['id' => $id])
                ->createCommand($this->db)
                ->queryScalar();
            $fields = $this->composeFields($id, $data);
            if ($exists === false) {
                $this->db->createCommand()
                    ->insert($this->sessionTable, $fields)
                    ->execute();
            } else {
                unset($fields['id']);
                $this->db->createCommand()
                    ->update($this->sessionTable, $fields, ['id' => $id])
                    ->execute();
            }
        } catch (\Exception $e) {
            $exception = ErrorHandler::convertExceptionToString($e);
            // its too late to use Yii logging here
            error_log($exception);
            if (YII_DEBUG) {
                echo $exception;
            }
            return false;
        }

        return true;
    }

    /**
     * Session destroy handler.
     * Do not call this method directly.
     * @param string $id session ID
     * @return boolean whether session is destroyed successfully
     */
    public function destroySession($id)
    {
        $this->db->createCommand()
            ->delete($this->sessionTable, ['id' => $id])
            ->execute();

        return true;
    }

    /**
     * Session GC (garbage collection) handler.
     * Do not call this method directly.
     * @param integer $maxLifetime the number of seconds after which data will be seen as 'garbage' and cleaned up.
     * @return boolean whether session is GCed successfully
     */
    public function gcSession($maxLifetime)
    {
        $this->db->createCommand()
            ->delete($this->sessionTable, '[[expire]]<:expire', [':expire' => time()])
            ->execute();

        return true;
    }
}
