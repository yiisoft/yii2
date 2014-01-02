<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\debug\panels;

/**
 * PanelInterface is the interface that should be implemented by all debug panels
 * that will be used in debug module.
 * 
 * @author Mark Jebri <mark.github@yandex.ru>
 * @since 2.0
 */
interface PanelInterface
{

	/**
	 * @return string name of the panel
	 */
	public function getName();

	/**
	 * @return string content that is displayed at debug toolbar
	 */
	public function getSummary();

	/**
	 * @return string content that is displayed in debugger detail view
	 */
	public function getDetail();

	/**
	 * Saves data to be later used in debugger detail view.
	 * This method is called on every page where debugger is enabled.
	 *
	 * @return mixed data to be saved
	 */
	public function save();

	/**
	 * Loads current request data that was saved during request execution.
	 * 
	 * @param mixed $data saved request data
	 */
	public function load($data);

}