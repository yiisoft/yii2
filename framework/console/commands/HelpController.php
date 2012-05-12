<?php
/**
 * HelpController class file.
 *
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
 *
 * ~~~
 * yiic help [command name]
 * ~~~
 *
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
		echo "Yii console command helper (based on Yii v" . \Yii::getVersion() . ").\n";
		$commands = $this->getCommands();
		if ($commands !== array()) {
			echo "\n    Usage: yiic <command-name> [...options...]\n\n";
			echo "The following commands are available:\n";
			foreach ($commands as $command) {
				echo " - $command\n";
			}
			echo "\nTo see individual command help, enter:\n";
			echo "\n    yiic help <command-name>\n";
		} else {
			echo "\nNo commands are found.\n";
		}
	}

	protected function getCommands()
	{
		$commands = $this->getModuleCommands(\Yii::$application);
		sort($commands);
		return array_unique($commands);
	}

	/**
	 * @param \yii\base\Module $module
	 * @return array
	 */
	protected function getModuleCommands($module)
	{
		if ($module === null) {
			return array();
		}

		$commands = array_keys($module->controllers);

		foreach ($module->getModules() as $id => $module) {
			foreach ($this->getModuleCommands($module->getModule($id)) as $command) {
				$commands[] = $command;
			}
		}

		$files = scandir($module->getControllerPath());
		foreach ($files as $file) {
			if(strcmp(substr($file,-14),'Controller.php') === 0 && is_file($file)) {
				$commands[] = lcfirst(substr(basename($file), 0, -14));
			}
		}

		return $commands;
	}
}