<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\test;

use Yii;

/**
 * DbTestTrait implements the commonly used methods for setting up and accessing fixture data.
 *
 * To use DbTestTrait, call the [[loadFixtures()]] method in the setup method in a test case class.
 * The specified fixtures will be loaded and accessible through [[getFixtureData()]] and [[getFixtureModel()]].
 *
 * For example,
 *
 * ~~~
 * use yii\test\DbTestTrait;
 * use yii\codeception\TestCase;
 * use app\models\Post;
 * use app\models\User;
 *
 * class PostTestCase extends TestCase
 * {
 *     use DbTestTrait;
 *
 *     protected function setUp()
 *     {
 *         parent::setUp();
 *
 *         $this->loadFixtures([
 *             'posts' => Post::className(),
 *             'users' => User::className(),
 *         ]);
 *     }
 * }
 * ~~~
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
trait DbTestTrait
{
	/**
	 * Loads the specified fixtures.
	 *
	 * This method should typically be called in the setup method of test cases so that
	 * the fixtures are loaded before running each test method.
	 *
	 * This method does the following things:
	 *
	 * - Run [[DbFixtureManager::initScript]] if it is found under [[DbFixtureManager::basePath]].
	 * - Clean up data and models loaded in memory previously.
	 * - Load each specified fixture:
	 *      * Truncate the corresponding table.
	 *      * If a fixture file named `TableName.php` is found under [[DbFixtureManager::basePath]],
	 *        the file will be executed, and the return value will be treated as rows which will
	 *        then be inserted into the table.
	 *
	 * @param array $fixtures a list of fixtures (fixture name => table name or AR class name) to be loaded.
	 * Each array element can be either a table name (with schema prefix if needed), or a fully-qualified
	 * ActiveRecord class name (e.g. `app\models\Post`). An element can be optionally associated with a key
	 * which will be treated as the fixture name. For example,
	 *
	 * ~~~
	 * [
	 *     'tbl_comment',
	 *     'users' => 'tbl_user',   // 'users' is the fixture name, 'tbl_user' is a table name
	 *     'posts' => 'app\models\Post,  // 'app\models\Post' is a model class name
	 * ]
	 * ~~~
	 *
	 * @return array the loaded fixture data (fixture name => table rows)
	 */
	public function loadFixtures(array $fixtures = [])
	{
		return $this->getFixtureManager()->load($fixtures);
	}

	/**
	 * Returns the DB fixture manager.
	 * @return DbFixtureManager the DB fixture manager
	 */
	public function getFixtureManager()
	{
		return Yii::$app->getComponent('fixture');
	}

	/**
	 * Returns the table rows of the named fixture.
	 * @param string $fixtureName the fixture name.
	 * @return array the named fixture table rows. False is returned if there is no such fixture data.
	 */
	public function getFixtureRows($fixtureName)
	{
		return $this->getFixtureManager()->getRows($fixtureName);
	}

	/**
	 * Returns the named AR instance corresponding to the named fixture.
	 * @param string $fixtureName the fixture name.
	 * @param string $modelName the name of the fixture data row
	 * @return \yii\db\ActiveRecord the named AR instance corresponding to the named fixture.
	 * Null is returned if there is no such fixture or the record cannot be found.
	 */
	public function getFixtureModel($fixtureName, $modelName)
	{
		return $this->getFixtureManager()->getModel($fixtureName, $modelName);
	}
}
