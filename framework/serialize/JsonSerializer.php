<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\serialize;

use yii\base\BaseObject;
use yii\helpers\Json;

/**
 * JsonSerializer serializes data in JSON format.
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 3.0.0
 */
class JsonSerializer extends BaseObject implements SerializerInterface
{
    /**
     * @var integer the encoding options. For more details please refer to
     * <http://www.php.net/manual/en/function.json-encode.php>.
     * Default is `JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE`.
     */
    public $options = 320;


    /**
     * {@inheritdoc}
     */
    public function serialize($value)
    {
        return Json::encode($value, $this->options);
    }

    /**
     * {@inheritdoc}
     */
    public function unserialize($value)
    {
        return Json::decode($value);
    }
}