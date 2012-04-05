<?php
/**
 * View class file.
 *
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2012 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\base;

/**
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class View extends Component
{
	public function render($context, $_file_, $_data_ = array())
	{
		// we use special variable names here to avoid conflict with extracted variables
		extract($_data_, EXTR_PREFIX_SAME, 'data');
		ob_start();
		ob_implicit_flush(false);
		require($_file_);
		return ob_get_clean();
	}
}