<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\filters;

use Yii;
use yii\base\ActionFilter;

/**
 * PregReplace uses Regular Expressions to replace the content of a page
 *
 * To use PregReplace, declare it in the `behaviors()` method of your
 * controller class.
 *
 * In the following example the filter will be applied to the `index`, delete
 * Html comments and replace multiple white spaces (including linebreaks)
 * with a single space
 *
 * ~~~
 * public function behaviors()
 * {
 *     return [
 *         'pregReplace' => [
 *             'class' => 'yii\filters\PregReplace',
 *             'rules' => [
 *                 '/<\!--.*-->/Us' => '',
 *                 '/\w+/s' => ' ',
 *             ],
 *         ],
 *     ];
 * }
 * ~~~
 *
 * @author Angel Guevara <angeldelcaos@gmail.com>
 * @since 2.0
 */
class PregReplace extends ActionFilter
{
    /**
     * @var array set of rules in the form 'pattern' => 'replacemen'.
     */
    public $rules = [];

    /**
     * @inheritdoc
     */
    public function afterAction($action, $result)
    {
        foreach ($this->rules as $pattern => $replacement) {
            $result = preg_replace($pattern, $replacement, $result);
        }

        return $result;
    }
}
