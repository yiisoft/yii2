<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\i18n;

use Yii;
use yii\base\InvalidConfigException;
use yii\caching\CacheInterface;
use yii\db\Connection;
use yii\db\Expression;
use yii\db\Query;
use yii\di\Instance;
use yii\helpers\ArrayHelper;

/**
 * DbMessageSource 继承自 [[MessageSource]]，
 * 并表示将翻译后的消息存储在数据库中作为消息源。
 *
 * 数据库必须包含以下两个表：source_message 和 message。
 *
 * 表 `source_message` 存储要翻译的消息，
 * 表 `message` 储存翻译后的消息。
 * 可以通过设置 [[sourceMessageTable]] 和 [[messageTable]] 来自定义这两个表的名称。
 *
 * 数据库连接由 [[db]] 指定。可以通过应用迁移来初始化数据库模式:
 *
 * ```
 * yii migrate --migrationPath=@yii/i18n/migrations/
 * ```
 *
 * 如果你不想使用迁移，而是需要 SQL，那么所有数据库的文件都在 migrations 目录中。
 *
 * @author resurtm <resurtm@gmail.com>
 * @since 2.0
 */
class DbMessageSource extends MessageSource
{
    /**
     * 在生成缓存键时将使用的前缀。
     * @deprecated 此常量从未使用过，将在 2.1.0 中删除。
     */
    const CACHE_KEY_PREFIX = 'DbMessageSource';

    /**
     * @var Connection|array|string 数据库连接对象或数据库连接的应用程序组件 ID。
     *
     * 在创建 DbMessageSource 对象之后，
     * 如果您希望更改此属性，则应该仅使用 DB 连接对象对其进行分配。
     *
     * 从 2.0.2 版本开始，它也可以是用于创建对象的配置数组。
     */
    public $db = 'db';
    /**
     * @var CacheInterface|array|string 缓存对象或缓存对象的应用程序组件 ID。
     * 消息数据将使用此缓存对象进行缓存。
     * 注意，要启用缓存，必须将 [[enableCaching]] 设置为 `true`，否则设置此属性没有效果。
     *
     * 在创建 DbMessageSource 对象之后，如果您想更改此属性，
     * 您应该只使用缓存对象分配它。
     *
     * 从 2.0.2 版本开始，它也可以是用于创建对象的配置数组。
     * @see cachingDuration
     * @see enableCaching
     */
    public $cache = 'cache';
    /**
     * @var string 源消息表的名称。
     */
    public $sourceMessageTable = '{{%source_message}}';
    /**
     * @var string 翻译后的消息表的名称。
     */
    public $messageTable = '{{%message}}';
    /**
     * @var int 消息在缓存中保持有效的时间，以秒为单位。
     * 使用 0 表示缓存的数据永远不会过期。
     * @see enableCaching
     */
    public $cachingDuration = 0;
    /**
     * @var bool 是否要缓存翻译后的消息
     */
    public $enableCaching = false;


    /**
     * 初始化 DbMessageSource 组件。
     * 这个方法将初始化 [[db]] 属性，以确保它引用一个有效的 DB 连接。
     * 配置的 [[cache]] 组件也将被初始化。
     * @throws InvalidConfigException 如果 [[db]] 无效或 [[cache]] 无效。
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
     * 加载指定语言和类别的消息翻译。
     * 如果没有找到 `en-US` 等特定语言环境代码的翻译，
     * 它会尝试更通用的 `en`。
     *
     * @param string $category 消息的分类
     * @param string $language 目标语言
     * @return array 加载消息。键是原始消息，
     * 值是经过翻译的消息。
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
     * 从数据库加载消息。
     * 您可以重写此方法来自定义数据库中的消息存储。
     * @param string $category 消息类别。
     * @param string $language 目标语言。
     * @return array 从数据库加载的消息。
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
     * 该方法为后备语言消息搜索构建 [[Query]] 对象。
     * 通常由 [[loadMessagesFromDb]] 调用。
     *
     * @param string $category 消息类别
     * @param string $language 最初请求的语言
     * @param string $fallbackLanguage 目标后备语言
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
