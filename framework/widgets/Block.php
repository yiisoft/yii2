<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\widgets;

use yii\base\Widget;

/**
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class Block extends Widget
{
    /**
     * @var boolean whether to render the block content in place. Defaults to false,
     * meaning the captured block content will not be displayed.
     */
    public $renderInPlace = false;


    /**
     * Starts recording a block.
     */
    public function init()
    {
        ob_start();
        ob_implicit_flush(false);
    }

    /**
     * Ends recording a block.
     * This method stops output buffering and saves the rendering result as a named block in the view.
     */
    public function run()
    {
        $block = ob_get_clean();
        if ($this->renderInPlace) {
            echo $block;
        }
        $this->view->blocks[$this->getId()] = $block;
    }
}
