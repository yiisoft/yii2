<?php
/**
 * HelpCommand class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2012 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\console\commands;

/**
 * HelpCommand represents a console help command.
 *
 * HelpCommand displays the available command list or the help instructions
 * about a specific command.
 *
 * To use this command, enter the following on the command line:
 * <pre>
 * php path/to/entry_script.php help [command name]
 * </pre>
 * In the above, if the command name is not provided, it will display all
 * available commands.
 *
 * @property string $help The command description.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class HelpController extends \yii\console\Controller
{
	public function actionIndex($args = array())
	{

	}

	/**
	 * Execute the action.
	 * @param array $args command line parameters specific for this command
	 */
	public function run($args)
	{
		$runner=$this->getCommandRunner();
		$commands=$runner->commands;
		if(isset($args[0]))
		{
			$name=strtolower($args[0]);
		}
		if(!isset($args[0]) || !isset($commands[$name]))
		{
			if(!empty($commands))
			{
				echo "Yii command runner (based on Yii v".\Yii::getVersion().")\n";
				echo "Usage: ".$runner->getScriptName()." <command-name> [parameters...]\n";
				echo "\nThe following commands are available:\n";
				$commandNames=array_keys($commands);
				sort($commandNames);
				echo ' - '.implode("\n - ",$commandNames);
				echo "\n\nTo see individual command help, use the following:\n";
				echo "   ".$runner->getScriptName()." help <command-name>\n";
			} else
			{
				echo "No available commands.\n";
				echo "Please define them under the following directory:\n";
				echo "\t".\Yii::$app->getCommandPath()."\n";
			}
		} else
			echo $runner->createCommand($name)->getHelp();
	}

	/**
	 * Provides the command description.
	 * @return string the command description.
	 */
	public function getHelp()
	{
		return parent::getHelp().' [command-name]';
	}
}