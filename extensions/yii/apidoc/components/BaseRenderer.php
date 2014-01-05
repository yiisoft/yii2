<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\apidoc\components;

use Yii;
use yii\base\Component;
use yii\console\Controller;
use yii\apidoc\models\Context;
use yii\web\View;

abstract class BaseRenderer extends Component
{

	private $_view;


	public function getView()
	{
		if ($this->_view === null) {
			$this->_view = new View();
		}
		return $this->_view;
	}

	/**
	 * @param Context $context
	 * @param Controller $controller
	 * @return mixed
	 */
	public abstract function render($context, $controller);

} 