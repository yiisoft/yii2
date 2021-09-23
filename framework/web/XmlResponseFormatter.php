<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\web;

use DOMDocument;
use DOMElement;
use DOMException;
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
     * @var string|string[]|false the name of the root element. If set to false, null or is empty then no root tag
     * should be added.
     *
     * Since 2.0.44 URI namespace could be specified by passing `[namespace, tag name]` array.
     */
    public $rootTag = 'response';
    /**
     * @var string the name of the elements that represent the array elements with numeric keys.
     */
    public $itemTag = 'item';
    /**
     * @var bool whether to interpret objects implementing the [[\Traversable]] interface as arrays.
     * Defaults to `true`.
     * @since 2.0.7
     */
    public $useTraversableAsArray = true;
    /**
     * @var bool if object class names should be used as tag names
     * @since 2.0.11
     */
    public $useObjectTags = true;
    /**
     * @var bool if true, converts object tags to lowercase, `$useObjectTags` must be enabled
     * @since 2.0.43
     */
    public $objectTagToLowercase = false;

    /**
     * @var DOMDocument the XML document, serves as the root of the document tree
     * @since 2.0.43
     */
    protected $dom;


    /**
     * Formats the specified response.
     *
     * @param Response $response the response to be formatted.
     */
    public function format($response)
    {
        $charset = $this->encoding === null ? $response->charset : $this->encoding;
        if (stripos($this->contentType, 'charset') === false) {
            $this->contentType .= '; charset=' . $charset;
        }
        $response->getHeaders()->set('Content-Type', $this->contentType);
        if ($response->data !== null) {
            $this->dom = new DOMDocument($this->version, $charset);
            if (!empty($this->rootTag)) {
                if (is_array($this->rootTag)) {
                    $root = $this->dom->createElementNS($this->rootTag[0], $this->rootTag[1]);
                } else {
                    $root = $this->dom->createElement($this->rootTag);
                }
                $this->dom->appendChild($root);
                $this->buildXml($root, $response->data);
            } else {
                $this->buildXml($this->dom, $response->data);
            }
            $response->content = $this->dom->saveXML();
        }
    }

    /**
     * Recursively adds data to XML document.
     *
     * @param DOMElement|DOMDocument $element current element
     * @param mixed $data content of the current element
     */
    protected function buildXml($element, $data)
    {
        if (is_array($data) ||
            ($data instanceof \Traversable && $this->useTraversableAsArray && !$data instanceof Arrayable)
        ) {
            foreach ($data as $name => $value) {
                if (is_int($name) && is_object($value)) {
                    $this->buildXml($element, $value);
                } elseif (is_array($value) || is_object($value)) {
                    $child = $this->dom->createElement($this->getValidXmlElementName($name));
                    $element->appendChild($child);
                    $this->buildXml($child, $value);
                } else {
                    $child = $this->dom->createElement($this->getValidXmlElementName($name));
                    $child->appendChild($this->dom->createTextNode($this->formatScalarValue($value)));
                    $element->appendChild($child);
                }
            }
        } elseif (is_object($data)) {
            if ($this->useObjectTags) {
                $name = StringHelper::basename(get_class($data));
                if ($this->objectTagToLowercase) {
                    $name = strtolower($name);
                }
                $child = $this->dom->createElement($name);
                $element->appendChild($child);
            } else {
                $child = $element;
            }
            if ($data instanceof Arrayable) {
                $this->buildXml($child, $data->toArray());
            } else {
                $array = [];
                foreach ($data as $name => $value) {
                    $array[$name] = $value;
                }
                $this->buildXml($child, $array);
            }
        } else {
            $element->appendChild($this->dom->createTextNode($this->formatScalarValue($data)));
        }
    }

    /**
     * Formats scalar value to use in XML text node.
     *
     * @param int|string|bool|float $value a scalar value.
     * @return string string representation of the value.
     * @since 2.0.11
     */
    protected function formatScalarValue($value)
    {
        if ($value === true) {
            return 'true';
        }
        if ($value === false) {
            return 'false';
        }
        if (is_float($value)) {
            return StringHelper::floatToString($value);
        }
        return (string) $value;
    }

    /**
     * Returns element name ready to be used in DOMElement if
     * name is not empty, is not int and is valid.
     *
     * Falls back to [[itemTag]] otherwise.
     *
     * @param mixed $name the original name
     * @return string
     * @since 2.0.12
     */
    protected function getValidXmlElementName($name)
    {
        if (empty($name) || is_int($name) || !$this->isValidXmlName($name)) {
            return $this->itemTag;
        }

        return $name;
    }

    /**
     * Checks if name is valid to be used in XML.
     *
     * @param mixed $name the name to test
     * @return bool
     * @see http://stackoverflow.com/questions/2519845/how-to-check-if-string-is-a-valid-xml-element-name/2519943#2519943
     * @since 2.0.12
     */
    protected function isValidXmlName($name)
    {
        try {
            return $this->dom->createElement($name) !== false;
        } catch (DOMException $e) {
            return false;
        }
    }
}
