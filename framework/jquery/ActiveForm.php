<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\jquery;

use yii\helpers\Json;
use yii\helpers\Url;

/**
 * ActiveForm
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @author Paul Klimov <pklimov@quartsoft.com>
 * @since 2.1
 */
class ActiveForm extends \yii\widgets\ActiveForm
{
    /**
     * @inheritdoc
     */
    public $fieldClass = ActiveField::class;


    /**
     * Runs the widget.
     * This registers the necessary JavaScript code and renders the form close tag.
     */
    public function run()
    {
        $html = parent::run();

        if ($this->enableClientScript) {
            $id = $this->options['id'];
            $options = Json::htmlEncode($this->getClientOptions());
            $attributes = Json::htmlEncode($this->attributes);
            $view = $this->getView();
            ActiveFormAsset::register($view);
            $view->registerJs("jQuery('#$id').yiiActiveForm($attributes, $options);");
        }

        return $html;
    }

    /**
     * Returns the options for the form JS widget.
     * @return array the options.
     */
    protected function getClientOptions()
    {
        $options = [
            'encodeErrorSummary' => $this->encodeErrorSummary,
            'errorSummary' => '.' . implode('.', preg_split('/\s+/', $this->errorSummaryCssClass, -1, PREG_SPLIT_NO_EMPTY)),
            'validateOnSubmit' => $this->validateOnSubmit,
            'errorCssClass' => $this->errorCssClass,
            'successCssClass' => $this->successCssClass,
            'validatingCssClass' => $this->validatingCssClass,
            'ajaxParam' => $this->ajaxParam,
            'ajaxDataType' => $this->ajaxDataType,
            'scrollToError' => $this->scrollToError,
            'scrollToErrorOffset' => $this->scrollToErrorOffset,
        ];
        if ($this->validationUrl !== null) {
            $options['validationUrl'] = Url::to($this->validationUrl);
        }

        // only get the options that are different from the default ones (set in yii.activeForm.js)
        return array_diff_assoc($options, [
            'encodeErrorSummary' => true,
            'errorSummary' => '.error-summary',
            'validateOnSubmit' => true,
            'errorCssClass' => 'has-error',
            'successCssClass' => 'has-success',
            'validatingCssClass' => 'validating',
            'ajaxParam' => 'ajax',
            'ajaxDataType' => 'json',
            'scrollToError' => true,
            'scrollToErrorOffset' => 0,
        ]);
    }
}