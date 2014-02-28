<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\apidoc\templates;

use Yii;
use yii\apidoc\models\ClassDoc;
use yii\apidoc\models\ConstDoc;
use yii\apidoc\models\Context;
use yii\apidoc\models\EventDoc;
use yii\apidoc\models\InterfaceDoc;
use yii\apidoc\models\MethodDoc;
use yii\apidoc\models\PropertyDoc;
use yii\apidoc\models\TraitDoc;
use yii\base\Component;
use yii\console\Controller;

/**
 * Base class for all API documentation renderers
 *
 * @author Carsten Brandt <mail@cebe.cc>
 * @since 2.0
 */
abstract class BaseRenderer extends Component
{
	/**
	 * @var Context the [[Context]] currently being rendered.
	 */
	public $context;
	/**
	 * @var array files for guide pages
	 */
	public $markDownFiles = [];

	/**
	 * Renders a given [[Context]].
	 *
	 * @param Context $context the api documentation context to render.
	 * @param Controller $controller the apidoc controller instance. Can be used to control output.
	 */
	public abstract function renderApi($context, $controller);

	/**
	 * Renders a given [[Context]].
	 *
	 * @param array $files list of markdown files to render
	 * @param Context $context the api documentation context to render.
	 * @param Controller $controller the apidoc controller instance. Can be used to control output.
	 */
	public abstract function renderMarkdownFiles($controller);

	/**
	 * creates a link to a type (class, interface or trait)
	 * @param ClassDoc|InterfaceDoc|TraitDoc $types
	 * @param string $title
	 * @return string
	 */
	public abstract function typeLink($types, $title = null);

	/**
	 * creates a link to a subject
	 * @param PropertyDoc|MethodDoc|ConstDoc|EventDoc $subject
	 * @param string $title
	 * @return string
	 */
	public abstract function subjectLink($subject, $title = null);
}