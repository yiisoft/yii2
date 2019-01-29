<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\test;

use Yii;

/**
 * InitDbFixture 用于数据库相关的测试状态初始化相关的需求。
 *
 * 它的主要任务是在数据加载时，关闭数据库完整性校验。类似这种功能通常被其他数据库相关的夹具所需要（例如： [[ActiveFixture]] ），
 * 这样其他夹具在填入数据到数据库时，不会触发完整性校验错误。
 *
 * 另外，DbFixture 会尝试加载 [[initScript| 初始化脚本 ]]，如果这个属性有被设置的话。
 *
 * 您通常应该使用 InitDbFixture 来准备一个骨架测试数据库。然后，其他 DB fixture 将向该数据库添加特定的表和数据。
 *
 * 有关 InitDbFixture 的更多细节和使用信息，请参阅 [guide article on fixtures](guide:test-fixtures)
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class InitDbFixture extends DbFixture
{
    /**
     * @var string 加载夹具时需要执行的初始化脚本文件。
     * 这个属性可以是一个文件路径或者 [path alias](guide:concept-aliases)。注意，如果文件不存在，不会产生任何错误。
     */
    public $initScript = '@app/tests/fixtures/initdb.php';
    /**
     * @var array 测试表所依赖的数据库模式列表。默认是 `['']` ，意味着使用默认的模式。（一个空字符串指代默认模式）。
     * 这个属性的作用在于开关完整性校验，这样夹具数据填入数据库时就不会触发错误。
     */
    public $schemas = [''];


    /**
     * {@inheritdoc}
     */
    public function beforeLoad()
    {
        $this->checkIntegrity(false);
    }

    /**
     * {@inheritdoc}
     */
    public function afterLoad()
    {
        $this->checkIntegrity(true);
    }

    /**
     * {@inheritdoc}
     */
    public function load()
    {
        $file = Yii::getAlias($this->initScript);
        if (is_file($file)) {
            require $file;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function beforeUnload()
    {
        $this->checkIntegrity(false);
    }

    /**
     * {@inheritdoc}
     */
    public function afterUnload()
    {
        $this->checkIntegrity(true);
    }

    /**
     * 开关数据库完整性校验。
     * @param bool $check 是否开启关闭完整性校验。
     */
    public function checkIntegrity($check)
    {
        if (!$this->db instanceof \yii\db\Connection) {
            return;
        }
        foreach ($this->schemas as $schema) {
            $this->db->createCommand()->checkIntegrity($check, $schema)->execute();
        }
    }
}
