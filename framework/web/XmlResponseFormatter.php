<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\web;

use DOMDocument;
use DOMElement;
use DOMText;
use yii\base\Arrayable;
use yii\base\Component;
use yii\helpers\StringHelper;

/**
 * XmlResponseFormatter formats the given data into an XML response content.
 *
 * It is used by [[Response]] to format response data.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class XmlResponseFormatter extends Component implements ResponseFormatterInterface
{
    /**
     * @var string the Content-Type header for the response
     */
    public $contentType = 'application/xml';
    /**
     * @var string the XML version
     */
    public $version = '1.0';
    /**
     * @var string the XML encoding. If not set, it will use the value of [[Response::charset]].
     */
    public $encoding;
    /**
     * @var string the name of the root element.
     */
    public $rootTag = 'response';
    /**
     * @var string the name of the elements that represent the array elements with numeric keys.
     */
    public $itemTag = 'item';

    /**
     * Formats the specified response.
     * @param Response $response the response to be formatted.
     */
    public function format($response)
    {
        $charset = $this->encoding === null ? $response->charset : $this->encoding;
        if (stripos($this->contentType, 'charset') === false) {
            $this->contentType .= '; charset=' . $charset;
        }
        $response->getHeaders()->set('Content-Type', $this->contentType);
        $dom = new DOMDocument($this->version, $charset);
        $root = new DOMElement($this->rootTag);
        $dom->appendChild($root);
        $this->buildXml($root, $response->data);
        $response->content = $dom->saveXML();
    }

    /**
     * @param DOMElement $element
     * @param mixed $data
     */
    protected function buildXml($element, $data)
    {
        if (is_object($data)) {
            $child = new DOMElement(StringHelper::basename(get_class($data)));
            $element->appendChild($child);
            if ($data instanceof Arrayable) {
                $this->buildXml($child, $data->toArray());
            } else {
                $array = [];
                foreach ($data as $name => $value) {
                    $array[$name] = $value;
                }
                $this->buildXml($child, $array);
            }
        } elseif (is_array($data)) {
            foreach ($data as $name => $value) {
                if (is_int($name) && is_object($value)) {
                    $this->buildXml($element, $value);
                } elseif (is_array($value) || is_object($value)) {
                    $child = new DOMElement(is_int($name) ? $this->itemTag : $name);
                    $element->appendChild($child);
                    $this->buildXml($child, $value);
                } else {
                    $child = new DOMElement(is_int($name) ? $this->itemTag : $name);
                    $element->appendChild($child);
                    $child->appendChild(new DOMText((string) $value));
                }
            }
        } else {
            $element->appendChild(new DOMText((string) $data));
        }
    }
}
