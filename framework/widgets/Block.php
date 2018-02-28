<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\widgets;

use yii\base\Widget;

/**
 * Block records all output between [[begin()]] and [[end()]] calls and stores it in [[\yii\base\View::$blocks]].
 * for later use.
 *
 * [[\yii\base\View]] component contains two methods [[\yii\base\View::beginBlock()]] and [[\yii\base\View::endBlock()]].
 * The general idea is that you're defining block default in a view or layout:
 *
 * ```php
 * <?php $this->beginBlock('messages', true) ?>
 * Nothing.
 * <?php $this->endBlock() ?>
 * ```
 *
 * And then overriding default in sub-views:
 *
 * ```php
 * <?php $this->beginBlock('username') ?>
 * Umm... hello?
 * <?php $this->endBlock() ?>
 * ```
 *
 * Second parameter defines if block content should be outputted which is desired when rendering its content but isn't
 * desired when redefining it in subviews.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class Block extends Widget
{
    /**
     * @var bool whether to render the block content in place. Defaults to false,
     * meaning the captured block content will not be displayed.
     */
    public $renderInPlace = false;


    /**
     * Starts recording a block.
     */
    public function init()
    {
        parent::init();

        ob_start();
        ob_implicit_flush(false);
    }

    /**
     * Ends recording a block.
     * This method stops output buffering and saves the rendering result as a named block in the view.
     * @return string the result of widget execution to be outputted.
     */
    public function run()
    {
        $block = ob_get_clean();
        if ($this->renderInPlace) {
            return $block;
        }
        $this->view->blocks[$this->getId()] = $block;
        return '';
    }
}
