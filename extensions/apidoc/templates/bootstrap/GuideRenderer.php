<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\apidoc\templates\bootstrap;

use Yii;
use yii\helpers\Html;

/**
 *
 * @author Carsten Brandt <mail@cebe.cc>
 * @since 2.0
 */
class GuideRenderer extends \yii\apidoc\templates\html\GuideRenderer
{
    use RendererTrait;

    public $layout = '@yii/apidoc/templates/bootstrap/layouts/guide.php';

    /**
     * @inheritDoc
     */
    public function render($files, $targetDir)
    {
        $types = array_merge($this->apiContext->classes, $this->apiContext->interfaces, $this->apiContext->traits);

        $extTypes = [];
        foreach ($this->extensions as $k => $ext) {
            $extType = $this->filterTypes($types, $ext);
            if (empty($extType)) {
                unset($this->extensions[$k]);
                continue;
            }
            $extTypes[$ext] = $extType;
        }

        parent::render($files, $targetDir);
    }
}
