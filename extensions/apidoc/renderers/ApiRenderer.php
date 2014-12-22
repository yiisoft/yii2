<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\apidoc\renderers;

use Yii;
use yii\apidoc\models\Context;

/**
 * Base class for all API documentation renderers
 *
 * @author Carsten Brandt <mail@cebe.cc>
 * @since 2.0
 */
abstract class ApiRenderer extends BaseRenderer
{
    /**
     * Renders a given [[Context]].
     *
     * @param Context $context the api documentation context to render.
     * @param $targetDir
     */
    abstract public function render($context, $targetDir);
}
