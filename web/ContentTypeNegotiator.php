<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\web;

use yii\base\Component;

/**
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class ContentTypeNegotiator extends Component
{
    /**
     * @var array list of supported API version numbers. If the current request does not specify a version
     * number, the first element will be used as the [[version|chosen version number]]. For this reason, you should
     * put the latest version number at the first. If this property is empty, [[version]] will not be set.
     */
    public $supportedVersions = [];
    /**
     * @var array list of supported response formats. The array keys are the requested content MIME types,
     * and the array values are the corresponding response formats. The first element will be used
     * as the response format if the current request does not specify a content type.
     */
    public $supportedFormats = [
        'application/json' => Response::FORMAT_JSON,
        'application/xml' => Response::FORMAT_XML,
    ];

}
