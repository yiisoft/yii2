<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\i18n;

use yii\base\Component;

/**
 * GettextFile 是表示 Gettext 消息文件的基类。
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
abstract class GettextFile extends Component
{
    /**
     * 从文件加载消息。
     * @param string $filePath 文件路径
     * @param string $context 消息上下文
     * @return array 消息翻译。数组键是源消息，数组值是已翻译的消息：
     * 源消息 => 已翻译的消息。
     */
    abstract public function load($filePath, $context);

    /**
     * 将消息保存到文件。
     * @param string $filePath 文件路径
     * @param array $messages 消息翻译。 数组键是源消息，数组值是已翻译的消息：源消息 => 已翻译的消息。
     * 请注意，如果消息具有上下文，
     * 则消息 ID 必须以 chr(4) 作为分隔符的上下文前缀。
     */
    abstract public function save($filePath, $messages);
}
