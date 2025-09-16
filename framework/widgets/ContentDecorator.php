<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yii\widgets;

use yii\base\InvalidConfigException;
use yii\base\Widget;

/**
 * ContentDecorator records all output between [[begin()]] and [[end()]] calls, passes it to the given view file
 * as `$content` and then echoes rendering result.
 *
 * ```
 * <?php ContentDecorator::begin([
 *     'viewFile' => '@app/views/layouts/base.php',
 *     'params' => [],
 *     'view' => $this,
 * ]) ?>
 *
 * some content here
 *
 * <?php ContentDecorator::end() ?>
 * ```
 *
 * There are [[\yii\base\View::beginContent()]] and [[\yii\base\View::endContent()]] wrapper methods in the
 * [[\yii\base\View]] component to make syntax more friendly. In the view these could be used as follows:
 *
 * ```
 * <?php $this->beginContent('@app/views/layouts/base.php') ?>
 *
 * some content here
 *
 * <?php $this->endContent() ?>
 * ```
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class ContentDecorator extends Widget
{
    /**
     * @var string the view file that will be used to decorate the content enclosed by this widget.
     * This can be specified as either the view file path or [path alias](guide:concept-aliases).
     */
    public $viewFile;
    /**
     * @var array the parameters (name => value) to be extracted and made available in the decorative view.
     */
    public $params = [];


    /**
     * Starts recording a clip.
     */
    public function init()
    {
        parent::init();

        if ($this->viewFile === null) {
            throw new InvalidConfigException('ContentDecorator::viewFile must be set.');
        }
        ob_start();
        ob_implicit_flush(false);
    }

    /**
     * Ends recording a clip.
     * This method stops output buffering and saves the rendering result as a named clip in the controller.
     */
    public function run()
    {
        $params = $this->params;
        $params['content'] = ob_get_clean();
        // render under the existing context
        echo $this->view->renderFile($this->viewFile, $params);
    }
}
