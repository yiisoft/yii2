<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\web;

use yii\base\BaseObject;

/**
 * Link represents a link object as defined in [JSON Hypermedia API Language](https://tools.ietf.org/html/draft-kelly-json-hal-03).
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class Link extends BaseObject
{
    /**
     * The self link.
     */
    const REL_SELF = 'self';

    /**
     * @var string a URI [RFC3986](https://tools.ietf.org/html/rfc3986) or
     * URI template [RFC6570](https://tools.ietf.org/html/rfc6570). This property is required.
     */
    public $href;
    /**
     * @var string a secondary key for selecting Link Objects which share the same relation type
     */
    public $name;
    /**
     * @var string a hint to indicate the media type expected when dereferencing the target resource
     */
    public $type;
    /**
     * @var bool a value indicating whether [[href]] refers to a URI or URI template.
     */
    public $templated = false;
    /**
     * @var string a URI that hints about the profile of the target resource.
     */
    public $profile;
    /**
     * @var string a label describing the link
     */
    public $title;
    /**
     * @var string the language of the target resource
     */
    public $hreflang;


    /**
     * Serializes a list of links into proper array format.
     * @param array $links the links to be serialized
     * @return array the proper array representation of the links.
     */
    public static function serialize(array $links)
    {
        foreach ($links as $rel => $link) {
            if (is_array($link)) {
                foreach ($link as $i => $l) {
                    $link[$i] = $l instanceof self ? array_filter((array) $l) : ['href' => $l];
                }
                $links[$rel] = $link;
            } elseif (!$link instanceof self) {
                $links[$rel] = ['href' => $link];
            }
        }

        return $links;
    }
}
