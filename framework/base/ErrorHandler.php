<?php
/**
 * This file contains the error handler application component.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2011 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

Yii::import('CHtml',true);

/**
 * CErrorHandler handles uncaught PHP errors and exceptions.
 *
 * It displays these errors using appropriate views based on the
 * nature of the error and the mode the application runs at.
 * It also chooses the most preferred language for displaying the error.
 *
 * CErrorHandler uses two sets of views:
 * <ul>
 * <li>development views, named as <code>exception.php</code>;
 * <li>production views, named as <code>error&lt;StatusCode&gt;.php</code>;
 * </ul>
 * where &lt;StatusCode&gt; stands for the HTTP error code (e.g. error500.php).
 * Localized views are named similarly but located under a subdirectory
 * whose name is the language code (e.g. zh_cn/error500.php).
 *
 * Development views are displayed when the application is in debug mode
 * (i.e. YII_DEBUG is defined as true). Detailed error information with source code
 * are displayed in these views. Production views are meant to be shown
 * to end-users and are used when the application is in production mode.
 * For security reasons, they only display the error message without any
 * sensitive information.
 *
 * CErrorHandler looks for the view templates from the following locations in order:
 * <ol>
 * <li><code>themes/ThemeName/views/system</code>: when a theme is active.</li>
 * <li><code>protected/views/system</code></li>
 * <li><code>framework/views</code></li>
 * </ol>
 * If the view is not found in a directory, it will be looked for in the next directory.
 *
 * The property {@link maxSourceLines} can be changed to specify the number
 * of source code lines to be displayed in development views.
 *
 * CErrorHandler is a core application component that can be accessed via
 * {@link CApplication::getErrorHandler()}.
 *
 * @property array $error The error details. Null if there is no error.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @version $Id$
 * @package system.base
 * @since 1.0
 */
class CErrorHandler extends CApplicationComponent
{
	/**
	 * @var integer maximum number of source code lines to be displayed. Defaults to 25.
	 */
	public $maxSourceLines=25;

	/**
	 * @var integer maximum number of trace source code lines to be displayed. Defaults to 10.
	 * @since 1.1.6
	 */
	public $maxTraceSourceLines = 10;

	/**
	 * @var string the application administrator information (could be a name or email link). It is displayed in error pages to end users. Defaults to 'the webmaster'.
	 */
	public $adminInfo='the webmaster';
	/**
	 * @var boolean whether to discard any existing page output before error display. Defaults to true.
	 */
	public $discardOutput=true;
	/**
	 * @var string the route (eg 'site/error') to the controller action that will be used to display external errors.
	 * Inside the action, it can retrieve the error information by Yii::app()->errorHandler->error.
	 * This property defaults to null, meaning CErrorHandler will handle the error display.
	 */
	public $errorAction;

	private $_error;

	/**
	 * Handles the exception/error event.
	 * This method is invoked by the application whenever it captures
	 * an exception or PHP error.
	 * @param CEvent $event the event containing the exception/error information
	 */
	public function handle($event)
	{
		// set event as handled to prevent it from being handled by other event handlers
		$event->handled=true;

		if($this->discardOutput)
		{
			// the following manual level counting is to deal with zlib.output_compression set to On
			for($level=ob_get_level();$level>0;--$level)
			{
				@ob_end_clean();
			}
		}

		if($event instanceof CExceptionEvent)
			$this->handleException($event->exception);
		else // CErrorEvent
			$this->handleError($event);
	}

	/**
	 * Returns the details about the error that is currently being handled.
	 * The error is returned in terms of an array, with the following information:
	 * <ul>
	 * <li>code - the HTTP status code (e.g. 403, 500)</li>
	 * <li>type - the error type (e.g. 'CHttpException', 'PHP Error')</li>
	 * <li>message - the error message</li>
	 * <li>file - the name of the PHP script file where the error occurs</li>
	 * <li>line - the line number of the code where the error occurs</li>
	 * <li>trace - the call stack of the error</li>
	 * <li>source - the context source code where the error occurs</li>
	 * </ul>
	 * @return array the error details. Null if there is no error.
	 */
	public function getError()
	{
		return $this->_error;
	}

	/**
	 * Handles the exception.
	 * @param Exception $exception the exception captured
	 */
	protected function handleException($exception)
	{
		$app=Yii::app();
		if($app instanceof CWebApplication)
		{
			if(($trace=$this->getExactTrace($exception))===null)
			{
				$fileName=$exception->getFile();
				$errorLine=$exception->getLine();
			}
			else
			{
				$fileName=$trace['file'];
				$errorLine=$trace['line'];
			}

			$trace = $exception->getTrace();

			foreach($trace as $i=>$t)
			{
				if(!isset($t['file']))
					$trace[$i]['file']='unknown';

				if(!isset($t['line']))
					$trace[$i]['line']=0;

				if(!isset($t['function']))
					$trace[$i]['function']='unknown';

				unset($trace[$i]['object']);
			}

			$this->_error=$data=array(
				'code'=>($exception instanceof CHttpException)?$exception->statusCode:500,
				'type'=>get_class($exception),
				'errorCode'=>$exception->getCode(),
				'message'=>$exception->getMessage(),
				'file'=>$fileName,
				'line'=>$errorLine,
				'trace'=>$exception->getTraceAsString(),
				'traces'=>$trace,
			);

			if(!headers_sent())
				header("HTTP/1.0 {$data['code']} ".get_class($exception));

			if($exception instanceof CHttpException || !YII_DEBUG)
				$this->render('error',$data);
			else
			{
				if($this->isAjaxRequest())
					$app->displayException($exception);
				else
					$this->render('exception',$data);
			}
		}
		else
			$app->displayException($exception);
	}

	/**
	 * Handles the PHP error.
	 * @param CErrorEvent $event the PHP error event
	 */
	protected function handleError($event)
	{
		$trace=debug_backtrace();
		// skip the first 3 stacks as they do not tell the error position
		if(count($trace)>3)
			$trace=array_slice($trace,3);
		$traceString='';
		foreach($trace as $i=>$t)
		{
			if(!isset($t['file']))
				$trace[$i]['file']='unknown';

			if(!isset($t['line']))
				$trace[$i]['line']=0;

			if(!isset($t['function']))
				$trace[$i]['function']='unknown';

			$traceString.="#$i {$trace[$i]['file']}({$trace[$i]['line']}): ";
			if(isset($t['object']) && is_object($t['object']))
				$traceString.=get_class($t['object']).'->';
			$traceString.="{$trace[$i]['function']}()\n";

			unset($trace[$i]['object']);
		}

		$app=Yii::app();
		if($app instanceof CWebApplication)
		{
			switch($event->code)
			{
				case E_WARNING:
					$type = 'PHP warning';
					break;
				case E_NOTICE:
					$type = 'PHP notice';
					break;
				case E_USER_ERROR:
					$type = 'User error';
					break;
				case E_USER_WARNING:
					$type = 'User warning';
					break;
				case E_USER_NOTICE:
					$type = 'User notice';
					break;
				case E_RECOVERABLE_ERROR:
					$type = 'Recoverable error';
					break;
				default:
					$type = 'PHP error';
			}
			$this->_error=$data=array(
				'code'=>500,
				'type'=>$type,
				'message'=>$event->message,
				'file'=>$event->file,
				'line'=>$event->line,
				'trace'=>$traceString,
				'traces'=>$trace,
			);
			if(!headers_sent())
				header("HTTP/1.0 500 PHP Error");
			if($this->isAjaxRequest())
				$app->displayError($event->code,$event->message,$event->file,$event->line);
			else if(YII_DEBUG)
				$this->render('exception',$data);
			else
				$this->render('error',$data);
		}
		else
			$app->displayError($event->code,$event->message,$event->file,$event->line);
	}

	/**
	 * whether the current request is an AJAX (XMLHttpRequest) request.
	 * @return boolean whether the current request is an AJAX request.
	 */
	protected function isAjaxRequest()
	{
		return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH']==='XMLHttpRequest';
	}

	/**
	 * Returns the exact trace where the problem occurs.
	 * @param Exception $exception the uncaught exception
	 * @return array the exact trace where the problem occurs
	 */
	protected function getExactTrace($exception)
	{
		$traces=$exception->getTrace();

		foreach($traces as $trace)
		{
			// property access exception
			if(isset($trace['function']) && ($trace['function']==='__get' || $trace['function']==='__set'))
				return $trace;
		}
		return null;
	}

	/**
	 * Renders the view.
	 * @param string $view the view name (file name without extension).
	 * See {@link getViewFile} for how a view file is located given its name.
	 * @param array $data data to be passed to the view
	 */
	protected function render($view,$data)
	{
		if($view==='error' && $this->errorAction!==null)
			Yii::app()->runController($this->errorAction);
		else
		{
			// additional information to be passed to view
			$data['version']=$this->getVersionInfo();
			$data['time']=time();
			$data['admin']=$this->adminInfo;
			include($this->getViewFile($view,$data['code']));
		}
	}

	/**
	 * Determines which view file should be used.
	 * @param string $view view name (either 'exception' or 'error')
	 * @param integer $code HTTP status code
	 * @return string view file path
	 */
	protected function getViewFile($view,$code)
	{
		$viewPaths=array(
			Yii::app()->getTheme()===null ? null :  Yii::app()->getTheme()->getSystemViewPath(),
			Yii::app() instanceof CWebApplication ? Yii::app()->getSystemViewPath() : null,
			YII_PATH.DIRECTORY_SEPARATOR.'views',
		);

		foreach($viewPaths as $i=>$viewPath)
		{
			if($viewPath!==null)
			{
				 $viewFile=$this->getViewFileInternal($viewPath,$view,$code,$i===2?'en_us':null);
				 if(is_file($viewFile))
				 	 return $viewFile;
			}
		}
	}

	/**
	 * Looks for the view under the specified directory.
	 * @param string $viewPath the directory containing the views
	 * @param string $view view name (either 'exception' or 'error')
	 * @param integer $code HTTP status code
	 * @param string $srcLanguage the language that the view file is in
	 * @return string view file path
	 */
	protected function getViewFileInternal($viewPath,$view,$code,$srcLanguage=null)
	{
		$app=Yii::app();
		if($view==='error')
		{
			$viewFile=$app->findLocalizedFile($viewPath.DIRECTORY_SEPARATOR."error{$code}.php",$srcLanguage);
			if(!is_file($viewFile))
				$viewFile=$app->findLocalizedFile($viewPath.DIRECTORY_SEPARATOR.'error.php',$srcLanguage);
		}
		else
			$viewFile=$viewPath.DIRECTORY_SEPARATOR."exception.php";
		return $viewFile;
	}

	/**
	 * Returns server version information.
	 * If the application is in production mode, empty string is returned.
	 * @return string server version information. Empty if in production mode.
	 */
	protected function getVersionInfo()
	{
		if(YII_DEBUG)
		{
			$version='<a href="http://www.yiiframework.com/">Yii Framework</a>/'.Yii::getVersion();
			if(isset($_SERVER['SERVER_SOFTWARE']))
				$version=$_SERVER['SERVER_SOFTWARE'].' '.$version;
		}
		else
			$version='';
		return $version;
	}

	/**
	 * Converts arguments array to its string representation
	 *
	 * @param array $args arguments array to be converted
	 * @return string string representation of the arguments array
	 */
	protected function argumentsToString($args)
	{
		$count=0;

		$isAssoc=$args!==array_values($args);

		foreach($args as $key => $value)
		{
			$count++;
			if($count>=5)
			{
				if($count>5)
					unset($args[$key]);
				else
					$args[$key]='...';
				continue;
			}

			if(is_object($value))
				$args[$key] = get_class($value);
			else if(is_bool($value))
				$args[$key] = $value ? 'true' : 'false';
			else if(is_string($value))
			{
				if(strlen($value)>64)
					$args[$key] = '"'.substr($value,0,64).'..."';
				else
					$args[$key] = '"'.$value.'"';
			}
			else if(is_array($value))
				$args[$key] = 'array('.$this->argumentsToString($value).')';
			else if($value===null)
				$args[$key] = 'null';
			else if(is_resource($value))
				$args[$key] = 'resource';

			if(is_string($key))
			{
				$args[$key] = '"'.$key.'" => '.$args[$key];
			}
			else if($isAssoc)
			{
				$args[$key] = $key.' => '.$args[$key];
			}
		}
		$out = implode(", ", $args);

		return $out;
	}

	/**
	 * Returns a value indicating whether the call stack is from application code.
	 * @param array $trace the trace data
	 * @return boolean whether the call stack is from application code.
	 */
	protected function isCoreCode($trace)
	{
		if(isset($trace['file']))
		{
			$systemPath=realpath(dirname(__FILE__).'/..');
			return $trace['file']==='unknown' || strpos(realpath($trace['file']),$systemPath.DIRECTORY_SEPARATOR)===0;
		}
		return false;
	}

	/**
	 * Renders the source code around the error line.
	 * @param string $file source file path
	 * @param integer $errorLine the error line number
	 * @param integer $maxLines maximum number of lines to display
	 * @return string the rendering result
	 */
	protected function renderSourceCode($file,$errorLine,$maxLines)
	{
		$errorLine--;	// adjust line number to 0-based from 1-based
		if($errorLine<0 || ($lines=@file($file))===false || ($lineCount=count($lines))<=$errorLine)
			return '';

		$halfLines=(int)($maxLines/2);
		$beginLine=$errorLine-$halfLines>0 ? $errorLine-$halfLines:0;
		$endLine=$errorLine+$halfLines<$lineCount?$errorLine+$halfLines:$lineCount-1;
		$lineNumberWidth=strlen($endLine+1);

		$output='';
		for($i=$beginLine;$i<=$endLine;++$i)
		{
			$isErrorLine = $i===$errorLine;
			$code=sprintf("<span class=\"ln".($isErrorLine?' error-ln':'')."\">%0{$lineNumberWidth}d</span> %s",$i+1,CHtml::encode(str_replace("\t",'    ',$lines[$i])));
			if(!$isErrorLine)
				$output.=$code;
			else
				$output.='<span class="error">'.$code.'</span>';
		}
		return '<div class="code"><pre>'.$output.'</pre></div>';
	}
}
