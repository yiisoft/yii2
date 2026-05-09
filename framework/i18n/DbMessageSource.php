<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yii\i18n;

use yii\base\InvalidConfigException;
use yii\caching\CacheInterface;
use yii\db\Connection;
use yii\db\Expression;
use yii\db\Query;
use yii\di\Instance;
use yii\helpers\ArrayHelper;

/**
 * DbMessageSource extends [[MessageSource]] and represents a message source that stores translated
 * messages in database.
 *
 * The database must contain the following two tables: source_message and message.
 *
 * The `source_message` table stores the messages to be translated, and the `message` table stores
 * the translated messages. The name of these two tables can be customized by setting [[sourceMessageTable]]
 * and [[messageTable]], respectively.
 *
 * The database connection is specified by [[db]]. Database schema could be initialized by applying migration:
 *
 * ```
 * yii migrate --migrationPath=@yii/i18n/migrations/
 * ```
 *
 * If you don't want to use migration and need SQL instead, files for all databases are in migrations directory.
 *
 * @author resurtm <resurtm@gmail.com>
 * @since 2.0
 */
class DbMessageSource extends MessageSource
{
    /**
     * Prefix which would be used when generating cache key.
     * @deprecated This constant has never been used and will be removed in 2.1.0.
     */
    public const CACHE_KEY_PREFIX = 'DbMessageSource';
    /**
     * @var Connection|array|string the DB connection object or the application component ID of the DB connection.
     *
     * After the DbMessageSource object is created, if you want to change this property, you should only assign
     * it with a DB connection object.
     *
     * Starting from version 2.0.2, this can also be a configuration array for creating the object.
     */
    public $db = 'db';
    /**
     * @var CacheInterface|array|string the cache object or the application component ID of the cache object.
     * The messages data will be cached using this cache object.
     * Note, that to enable caching you have to set [[enableCaching]] to `true`, otherwise setting this property has no effect.
     *
     * After the DbMessageSource object is created, if you want to change this property, you should only assign
     * it with a cache object.
     *
     * Starting from version 2.0.2, this can also be a configuration array for creating the object.
     * @see cachingDuration
     * @see enableCaching
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
     * @var int the time in seconds that the messages can remain valid in cache.
     * Use 0 to indicate that the cached data will never expire.
     * @see enableCaching
     */
    public $cachingDuration = 0;
    /**
     * @var bool whether to enable caching translated messages
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
        $this->db = Instance::ensure($this->db, Connection::className());
        if ($this->enableCaching) {
            $this->cache = Instance::ensure($this->cache, 'yii\caching\CacheInterface');
        }
    }

    /**
     * Loads the message translation for the specified language and category.
     * If translation for specific locale code such as `en-US` isn't found it
     * tries more generic `en`.
     *
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
        }

        return $this->loadMessagesFromDb($category, $language);
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
        $mainQuery = (new Query())->select(['message' => 't1.message', 'translation' => 't2.translation'])
            ->from(['t1' => $this->sourceMessageTable, 't2' => $this->messageTable])
            ->where([
                't1.id' => new Expression('[[t2.id]]'),
                't1.category' => $category,
                't2.language' => $language,
            ]);

        $fallbackLanguage = substr($language, 0, 2);
        $fallbackSourceLanguage = substr($this->sourceLanguage, 0, 2);

        if ($fallbackLanguage !== $language) {
            $mainQuery->union($this->createFallbackQuery($category, $language, $fallbackLanguage), true);
        } elseif ($language === $fallbackSourceLanguage) {
            $mainQuery->union($this->createFallbackQuery($category, $language, $fallbackSourceLanguage), true);
        }

        $messages = $mainQuery->createCommand($this->db)->queryAll();

        return ArrayHelper::map($messages, 'message', 'translation');
    }

    /**
     * The method builds the [[Query]] object for the fallback language messages search.
     * Normally is called from [[loadMessagesFromDb]].
     *
     * @param string $category the message category
     * @param string $language the originally requested language
     * @param string $fallbackLanguage the target fallback language
     * @return Query
     * @see loadMessagesFromDb
     * @since 2.0.7
     */
    protected function createFallbackQuery($category, $language, $fallbackLanguage)
    {
        return (new Query())->select(['message' => 't1.message', 'translation' => 't2.translation'])
            ->from(['t1' => $this->sourceMessageTable, 't2' => $this->messageTable])
            ->where([
                't1.id' => new Expression('[[t2.id]]'),
                't1.category' => $category,
                't2.language' => $fallbackLanguage,
            ])->andWhere([
                'NOT IN', 't2.id', (new Query())->select('[[id]]')->from($this->messageTable)->where(['language' => $language]),
            ]);
    }
}
