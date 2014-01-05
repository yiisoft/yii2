<?php
/**
 * 
 * 
 * @author Carsten Brandt <mail@cebe.cc>
 */

namespace yii\phpdoc\components;


use Yii;
use yii\base\Component;
use yii\console\Controller;
use yii\phpdoc\models\Context;
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