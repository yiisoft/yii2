<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\rest;
use \yii\base\Widget;

class TemplateRenderer extends Widget
{
	/**
     * @var \yii\data\DataProviderInterface the data provider for the view. This property is required.
     */
	public $dataProvider;
	/**
     * @var string the name of the view for rendering the container/wrapper for data items.
     * The following variables will
     * be available in the view:
     * - `$dataProvider`: the data provider
     * - `$widget`: TemplateRenderer, this widget instance
     */
	public $parentView;
	/**
     * @var string the name of the view for rendering each data item for rendering each data item.
     * The following variables will
     * be available in the view:
     * - `$model`: mixed, the data model
     * - `$widget`: TemplateRenderer, this widget instance
     */
	public $itemView;

	/**
     * Runs the widget.
     */
	public function run(){
		$this->dataProvider->prepare(true);
		return $this->render($this->parentView);
	}

	/**
     * Renders the full content according to the template given in $parentView and $itemView
     * In $parentView, {{variable}} will be replaced by the returned result of TemplateRenderer::renderVariable()
     * By default, {{items}} will be replaced by the replaced by renderItems() which will contain
     * the collectively rendered result of $itemView for the models in the current page
     * @return string the rendering result
     */
	public function render($view, $params = []){
		return preg_replace_callback('/{{(\\w+)}}/', function ($matches){
            return method_exists($this, "render".ucfirst($matches[1]))? $this->{"render".ucfirst($matches[1])}() : $matches[0];
        }, $this->view->render($this->parentView, ['dataProvider' => $this->dataProvider, 'widget' => $this] + $params));
	}
	
	/**
     * Renders the collective result of view specified in $itemView for all the data models.
     * @return string the rendering result
     */
	public function renderItems(){
		$rows = [];
		$models = $this->dataProvider->models;
		foreach($models as $model){
			$rows[] = $this->view->render($this->itemView, ['model' => $model, 'widget' => $this]);
		}
		return implode("\n", $rows);
	}
}