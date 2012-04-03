<?php
/**
 * StatePersister class file.
 *
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2012 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\base;

/**
 * CStatePersister implements a file-based persistent data storage.
 *
 * It can be used to keep data available through multiple requests and sessions.
 *
 * By default, CStatePersister stores data in a file named 'state.bin' that is located
 * under the application {@link CApplication::getRuntimePath runtime path}.
 * You may change the location by setting the {@link stateFile} property.
 *
 * To retrieve the data from CStatePersister, call {@link load()}. To save the data,
 * call {@link save()}.
 *
 * Comparison among state persister, session and cache is as follows:
 * <ul>
 * <li>session: data persisting within a single user session.</li>
 * <li>state persister: data persisting through all requests/sessions (e.g. hit counter).</li>
 * <li>cache: volatile and fast storage. It may be used as storage medium for session or state persister.</li>
 * </ul>
 *
 * Since server resource is often limited, be cautious if you plan to use CStatePersister
 * to store large amount of data. You should also consider using database-based persister
 * to improve the throughput.
 *
 * CStatePersister is a core application component used to store global application state.
 * It may be accessed via {@link CApplication::getStatePersister()}.
 * page state persistent method based on cache.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class StatePersister extends ApplicationComponent
{
	/**
	 * @var string the file path for keeping the state data. Make sure the directory containing
	 * the file exists and is writable by the Web server process. If using relative path, also
	 * make sure the path is correct. You may use a path alias here. If not set, it defaults
	 * to the `state.bin` file under the application's runtime directory.
	 */
	public $dataFile;

	/**
	 * Loads state data from persistent storage.
	 * @return mixed state data. Null if no state data available.
	 */
	public function load()
	{
		$dataFile = \Yii::getAlias($this->dataFile);
		if (is_file($dataFile) && ($data = file_get_contents($dataFile)) !== false) {
			return unserialize($data);
		} else {
			return null;
		}
	}

	/**
	 * Saves application state in persistent storage.
	 * @param mixed $state state data (must be serializable).
	 */
	public function save($state)
	{
		file_put_contents(\Yii::getAlias($this->dataFile), serialize($state), LOCK_EX);
	}
}
