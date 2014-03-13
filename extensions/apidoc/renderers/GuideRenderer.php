<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\apidoc\renderers;

use Yii;

/**
 * Base class for all Guide documentation renderers
 *
 * @author Carsten Brandt <mail@cebe.cc>
 * @since 2.0
 */
abstract class GuideRenderer extends BaseRenderer
{
    /**
     * Render markdown files
     *
     * @param array $files list of markdown files to render
     * @param $targetDir
     */
    abstract public function render($files, $targetDir);

}
