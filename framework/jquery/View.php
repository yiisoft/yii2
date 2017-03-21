<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\jquery;

/**
 * View is an enhanced version of [[\yii\web\View]], which includes JQuery support.
 *
 * @see \yii\web\View
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @author Paul Klimov <pklimov@quartsoft.com>
 * @since 2.1
 */
class View extends \yii\web\View
{
    /**
     * @inheritdoc
     */
    public function registerJs($js, $position = self::POS_READY, $key = null)
    {
        parent::registerJs($js, $position, $key);

        if ($position === self::POS_READY || $position === self::POS_LOAD) {
            JqueryAsset::register($this);
        }
    }

    /**
     * @inheritdoc
     */
    protected function wrapOnReadyJs($js)
    {
        return "jQuery(document).ready(function () {\n" . $js . "\n});";
    }

    /**
     * @inheritdoc
     */
    protected function wrapOnLoadJs($js)
    {
        return "jQuery(window).on('load', function () {\n" . $js . "\n});";
    }
}