<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\test;

use Yii;

/**
 * InitDbFixture represents the initial state needed for DB-related tests.
 *
 * Its main task is to toggle integrity check of the database during data loading.
 * This is needed by other DB-related fixtures (e.g. [[ActiveFixture]]) so that they can populate
 * data into the database without triggering integrity check errors.
 *
 * Besides, DbFixture also attempts to load an [[initScript|initialization script]] if it exists.
 *
 * You should normally use InitDbFixture to prepare a skeleton test database.
 * Other DB fixtures will then add specific tables and data to this database.
 *
 * For more details and usage information on InitDbFixture, see the [guide article on fixtures](guide:test-fixtures).
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class InitDbFixture extends DbFixture
{
    /**
     * @var string the init script file that should be executed when loading this fixture.
     * This should be either a file path or [path alias](guide:concept-aliases). Note that if the file does not exist,
     * no error will be raised.
     */
    public $initScript = '@app/tests/fixtures/initdb.php';
    /**
     * @var array list of database schemas that the test tables may reside in. Defaults to
     * `['']`, meaning using the default schema (an empty string refers to the
     * default schema). This property is mainly used when turning on and off integrity checks
     * so that fixture data can be populated into the database without causing problem.
     */
    public $schemas = [''];


    /**
     * @inheritdoc
     */
    public function beforeLoad()
    {
        $this->checkIntegrity(false);
    }

    /**
     * @inheritdoc
     */
    public function afterLoad()
    {
        $this->checkIntegrity(true);
    }

    /**
     * @inheritdoc
     */
    public function load()
    {
        $file = Yii::getAlias($this->initScript);
        if (is_file($file)) {
            require $file;
        }
    }

    /**
     * @inheritdoc
     */
    public function beforeUnload()
    {
        $this->checkIntegrity(false);
    }

    /**
     * @inheritdoc
     */
    public function afterUnload()
    {
        $this->checkIntegrity(true);
    }

    /**
     * Toggles the DB integrity check.
     * @param bool $check whether to turn on or off the integrity check.
     */
    public function checkIntegrity($check)
    {
        foreach ($this->schemas as $schema) {
            $this->db->createCommand()->checkIntegrity($check, $schema)->execute();
        }
    }
}
