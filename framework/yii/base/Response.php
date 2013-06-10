<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\base;

/**
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class Response extends Component
{
	/**
	 * @event Event an event raised when the application begins to generate the response.
	 */
	const EVENT_BEGIN_RESPONSE = 'beginResponse';
	/**
	 * @event Event an event raised when the generation of the response finishes.
	 */
	const EVENT_END_RESPONSE = 'endResponse';

	/**
	 * Starts output buffering
	 */
	public function beginBuffer()
	{
		ob_start();
		ob_implicit_flush(false);
	}

	/**
	 * Returns contents of the output buffer and stops the buffer.
	 * @return string output buffer contents
	 */
	public function endBuffer()
	{
		return ob_get_clean();
	}

	/**
	 * Returns contents of the output buffer
	 * @return string output buffer contents
	 */
	public function getBuffer()
	{
		return ob_get_contents();
	}

	/**
	 * Discards the output buffer
	 * @param boolean $all if true, it will discards all output buffers.
	 */
	public function cleanBuffer($all = true)
	{
		if ($all) {
			for ($level = ob_get_level(); $level > 0; --$level) {
				if (!@ob_end_clean()) {
					ob_clean();
				}
			}
		} else {
			ob_end_clean();
		}
	}

	/**
	 * Begins generating the response.
	 * This method is called at the beginning of [[Application::run()]].
	 * The default implementation will trigger the [[EVENT_BEGIN_RESPONSE]] event.
	 * If you overwrite this method, make sure you call the parent implementation so that
	 * the event can be triggered.
	 */
	public function begin()
	{
		$this->trigger(self::EVENT_BEGIN_RESPONSE);
	}

	/**
	 * Ends generating the response.
	 * This method is called at the end of [[Application::run()]].
	 * The default implementation will trigger the [[EVENT_END_RESPONSE]] event.
	 * If you overwrite this method, make sure you call the parent implementation so that
	 * the event can be triggered.
	 */
	public function end()
	{
		$this->trigger(self::EVENT_END_RESPONSE);
	}
}
