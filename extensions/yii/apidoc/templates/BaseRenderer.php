<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\apidoc\templates;

use Yii;
use yii\base\Component;
use yii\console\Controller;
use yii\apidoc\models\Context;
use yii\web\AssetManager;
use yii\web\View;

/**
 * Base class for all API documentation renderers
 *
 * @author Carsten Brandt <mail@cebe.cc>
 * @since 2.0
 */
abstract class BaseRenderer extends Component
{
	/**
	 * Renders a given [[Context]].
	 *
	 * @param Context $context the api documentation context to render.
	 * @param Controller $controller the apidoc controller instance. Can be used to control output.
	 */
	public abstract function render($context, $controller);
}