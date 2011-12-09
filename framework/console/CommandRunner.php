<?php
/**
 * CConsoleCommandRunner class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2011 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\console;

/**
 * CConsoleCommandRunner manages commands and executes the requested command.
 *
 * @property string $scriptName The entry script name.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class CommandRunner extends \yii\base\Component
{
	/**
	 * @var array list of all available commands (command name=>command configuration).
	 * Each command configuration can be either a string or an array.
	 * If the former, the string should be the class name or
	 * {@link YiiBase::getPathOfAlias class path alias} of the command.
	 * If the latter, the array must contain a 'class' element which specifies
	 * the command's class name or {@link YiiBase::getPathOfAlias class path alias}.
	 * The rest name-value pairs in the array are used to initialize
	 * the corresponding command properties. For example,
	 * <pre>
	 * array(
	 *   'email'=>array(
	 *      'class'=>'path.to.Mailer',
	 *      'interval'=>3600,
	 *   ),
	 *   'log'=>'path.to.LoggerCommand',
	 * )
	 * </pre>
	 */
	public $commands=array();

	private $_scriptName;

	/**
	 * Executes the requested command.
	 * @param array $args list of user supplied parameters (including the entry script name and the command name).
	 */
	public function run($args)
	{
		$this->_scriptName=$args[0];
		array_shift($args);
		if(isset($args[0]))
		{
			$name=$args[0];
			array_shift($args);
		}
		else
			$name='help';

		if(($command=$this->createCommand($name))===null)
			$command=$this->createCommand('help');
		$command->init();
		$command->run($args);
	}

	/**
	 * @return string the entry script name
	 */
	public function getScriptName()
	{
		return $this->_scriptName;
	}

	/**
	 * Searches for commands under the specified directory.
	 * @param string $path the directory containing the command class files.
	 * @return array list of commands (command name=>command class file)
	 */
	public function findCommands($path)
	{
		if(($dir=@opendir($path))===false)
			return array();
		$commands=array();
		while(($name=readdir($dir))!==false)
		{
			$file=$path.DIRECTORY_SEPARATOR.$name;
			if(!strcasecmp(substr($name,-11),'Command.php') && is_file($file))
				$commands[strtolower(substr($name,0,-11))]=$file;
		}
		closedir($dir);
		return $commands;
	}

	/**
	 * Adds commands from the specified command path.
	 * If a command already exists, the new one will be ignored.
	 * @param string $path the alias of the directory containing the command class files.
	 */
	public function addCommands($path)
	{
		if(($commands=$this->findCommands($path))!==array())
		{
			foreach($commands as $name=>$file)
			{
				if(!isset($this->commands[$name]))
					$this->commands[$name]=$file;
			}
		}
	}

	/**
	 * @param string $name command name (case-insensitive)
	 * @return \yii\console\Command the command object. Null if the name is invalid.
	 */
	public function createCommand($name)
	{
		$name=strtolower($name);
		if(isset($this->commands[$name]))
		{
			if(is_string($this->commands[$name]))  // class file path or alias
			{
				if(strpos($this->commands[$name],'/')!==false || strpos($this->commands[$name],'\\')!==false)
				{
					$className=substr(basename($this->commands[$name]),0,-4);
					if(!class_exists($className,false))
						require_once($this->commands[$name]);
				}
				else // an alias
					$className=\Yii::import($this->commands[$name]);
				return new $className($name,$this);
			}
			else // an array configuration
				return \Yii::create($this->commands[$name],$name,$this);
		}
		else if($name==='help')
			return new HelpCommand('help',$this);
		else
			return null;
	}
}