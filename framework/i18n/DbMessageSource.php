<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\i18n;

use Yii;
use yii\base\InvalidConfigException;
use yii\helpers\ArrayHelper;
use yii\caching\Cache;
use yii\db\Connection;
use yii\db\Query;

/**
 * DbMessageSource extends [[MessageSource]] and represents a message source that stores translated
 * messages in database.
 *
 * The database must contain the following two tables:
 *
 * ~~~
 * CREATE TABLE tbl_source_message (
 *     id INTEGER PRIMARY KEY,
 *     category VARCHAR(32),
 *     message TEXT
 * );
 *
 * CREATE TABLE tbl_message (
 *     id INTEGER,
 *     language VARCHAR(16),
 *     translation TEXT,
 *     PRIMARY KEY (id, language),
 *     CONSTRAINT fk_message_source_message FOREIGN KEY (id)
 *         REFERENCES tbl_source_message (id) ON DELETE CASCADE ON UPDATE RESTRICT
 * );
 * ~~~
 *
 * The `tbl_source_message` table stores the messages to be translated, and the `tbl_message` table stores
 * the translated messages. The name of these two tables can be customized by setting [[sourceMessageTable]]
 * and [[messageTable]], respectively.
 *
 * @author resurtm <resurtm@gmail.com>
 * @since 2.0
 */
class DbMessageSource extends MessageSource
{
	/**
	 * Prefix which would be used when generating cache key.
	 */
	const CACHE_KEY_PREFIX = 'DbMessageSource';

	/**
	 * @var Connection|string the DB connection object or the application component ID of the DB connection.
	 * After the DbMessageSource object is created, if you want to change this property, you should only assign
	 * it with a DB connection object.
	 */
	public $db = 'db';
	/**
	 * @var Cache|string the cache object or the application component ID of the cache object.
	 * The messages data will be cached using this cache object. Note, this property has meaning only
	 * in case [[cachingDuration]] set to non-zero value.
	 * After the DbMessageSource object is created, if you want to change this property, you should only assign
	 * it with a cache object.
	 */
	public $cache = 'cache';
	/**
	 * @var string the name of the source message table.
	 */
	public $sourceMessageTable = '{{%source_message}}';
	/**
	 * @var string the name of the translated message table.
	 */
	public $messageTable = '{{%message}}';
	/**
	 * @var integer the time in seconds that the messages can remain valid in cache.
	 * Use 0 to indicate that the cached data will never expire.
	 * @see enableCaching
	 */
	public $cachingDuration = 0;
	/**
	 * @var boolean whether to enable caching translated messages
	 */
	public $enableCaching = false;

	/**
	 * Initializes the DbMessageSource component.
	 * This method will initialize the [[db]] property to make sure it refers to a valid DB connection.
	 * Configured [[cache]] component would also be initialized.
	 * @throws InvalidConfigException if [[db]] is invalid or [[cache]] is invalid.
	 */
	public function init()
	{
		parent::init();
		if (is_string($this->db)) {
			$this->db = Yii::$app->getComponent($this->db);
		}
		if (!$this->db instanceof Connection) {
			throw new InvalidConfigException("DbMessageSource::db must be either a DB connection instance or the application component ID of a DB connection.");
		}
		if ($this->enableCaching) {
			if (is_string($this->cache)) {
				$this->cache = Yii::$app->getComponent($this->cache);
			}
			if (!$this->cache instanceof Cache) {
				throw new InvalidConfigException("DbMessageSource::cache must be either a cache object or the application component ID of the cache object.");
			}
		}
	}

	/**
	 * Loads the message translation for the specified language and category.
	 * Child classes should override this method to return the message translations of
	 * the specified language and category.
	 * @param string $category the message category
	 * @param string $language the target language
	 * @return array the loaded messages. The keys are original messages, and the values
	 * are translated messages.
	 */
	protected function loadMessages($category, $language)
	{
		if ($this->enableCaching) {
			$key = [
				__CLASS__,
				$category,
				$language,
			];
			$messages = $this->cache->get($key);
			if ($messages === false) {
				$messages = $this->loadMessagesFromDb($category, $language);
				$this->cache->set($key, $messages, $this->cachingDuration);
			}
			return $messages;
		} else {
			return $this->loadMessagesFromDb($category, $language);
		}
	}

	/**
	 * Loads the messages from database.
	 * You may override this method to customize the message storage in the database.
	 * @param string $category the message category.
	 * @param string $language the target language.
	 * @return array the messages loaded from database.
	 */
	protected function loadMessagesFromDb($category, $language)
	{
		$query = new Query();
		$messages = $query->select(['t1.message message', 't2.translation translation'])
			->from([$this->sourceMessageTable . ' t1', $this->messageTable . ' t2'])
			->where('t1.id = t2.id AND t1.category = :category AND t2.language = :language')
			->params([':category' => $category, ':language' => $language])
			->createCommand($this->db)
			->queryAll();
		return ArrayHelper::map($messages, 'message', 'translation');
	}
}
