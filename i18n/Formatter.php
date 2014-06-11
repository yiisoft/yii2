<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\i18n;


/**
 * Dummy Formatter class in 'yii\i18n' for compatibility reason.
 * Formatter class in 'yii\base' provides all functionality with localized format also
 * independent if php extension "intl" is loaded or not.
 * @see yii\base\Formatter.php
 *
 * If php extension "intl" want to be used or can't be loaded then localized formats patterns 
 * could be entered in class 'yii\i18n\FormatDefs.php'
 * ```
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @author Enrica Ruedin <e.ruedin@guggach.com>
 * @since 2.0
 */
class Formatter extends \yii\base\Formatter
{

}
