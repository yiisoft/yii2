<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\httpclient;

use yii\base\Object;
use yii\helpers\Json;

/**
 * Class FormatterJson
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 2.0
 */
class FormatterJson extends Object implements FormatterInterface
{
    /**
     * @var integer the encoding options.For more details please refer to
     * <http://www.php.net/manual/en/function.json-encode.php>.
     */
    public $encodeOptions = 0;

    /**
     * @inheritdoc
     */
    public function format(DocumentInterface $httpDocument)
    {
        $httpDocument->getHeaders()->set('Content-Type', 'application/json; charset=UTF-8');
        $data = $httpDocument->getData();
        return Json::encode($data, $this->encodeOptions);
    }
} 