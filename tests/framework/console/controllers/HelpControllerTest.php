<?php
namespace yiiunit\framework\console\controllers;

use yii\console\controllers\HelpController;
use yiiunit\TestCase;

/**
 * Unit test for [[\yii\console\controllers\HelpController]].
 * @see HelpController
 * @group console
 */
class HelpControllerTest extends TestCase
{
    /**
     * @inheritdoc
     */
    public function setUp()
    {
        $this->mockApplication();
    }

    /**
     * Creates controller instance.
     * @return BufferedHelpController
     */
    protected function createController()
    {
        $module = $this->getMock('yii\\base\\Module', ['fake'], ['console']);
        return new BufferedHelpController('help', $module);
    }

    /**
     * Emulates running controller action.
     * @param  string $actionID id of action to be run.
     * @param  array $args action arguments.
     * @return string command output.
     */
    protected function runControllerAction($actionID, $actionParams = [])
    {
        $controller = $this->createController();
        $action = $controller->createAction($actionID);
        $action->runWithParams($actionParams);
        return $controller->flushStdOutBuffer();
    }

    public function testActionIndex()
    {
        $result = $this->runControllerAction('index');
        $this->assertContains('This is Yii version ', $result);
        $this->assertContains('The following commands are available:', $result);
        $this->assertContains('To see the help of each command, enter:', $result);
        $this->assertContains('bootstrap.php help <command-name>', $result);
    }

    public function testActionIndexWithHelpCommand()
    {
        $out = $this->runControllerAction('index', ['command' => 'help']);
        $this->assertEqualsWithoutLE(<<<EOF

DESCRIPTION

Displays available commands or the detailed information
about a particular command.


USAGE

bootstrap.php help [command] [...options...]

- command: string
  The name of the command to show help about.
  If not provided, all available commands will be displayed.


OPTIONS

--appconfig: string
  custom application configuration file path.
  If not set, default application configuration is used.

--color: boolean, 0 or 1
  whether to enable ANSI color in the output.
  If not set, ANSI color will only be enabled for terminals that support it.

--help, -h: boolean, 0 or 1
  whether to display help information about current command.

--interactive: boolean, 0 or 1 (defaults to 1)
  whether to run the command interactively.


EOF
            , $out);
    }

    public function testActionIndexWithServeCommand()
    {
        $out = $this->runControllerAction('index', ['command' => 'serve']);
        $this->assertEqualsWithoutLE(<<<EOF

DESCRIPTION

Runs PHP built-in web server


USAGE

bootstrap.php serve [address] [...options...]

- address: string (defaults to 'localhost')
  address to serve on. Either "host" or "host:port".


OPTIONS

--appconfig: string
  custom application configuration file path.
  If not set, default application configuration is used.

--color: boolean, 0 or 1
  whether to enable ANSI color in the output.
  If not set, ANSI color will only be enabled for terminals that support it.

--docroot, -t: string (defaults to '@app/web')
  path or path alias to directory to serve

--help, -h: boolean, 0 or 1
  whether to display help information about current command.

--interactive: boolean, 0 or 1 (defaults to 1)
  whether to run the command interactively.

--port, -p: int (defaults to 8080)
  port to serve on.

--router, -r: string
  path to router script.
  See https://secure.php.net/manual/en/features.commandline.webserver.php


EOF
            , $out);
    }

}


class BufferedHelpController extends HelpController
{
    use StdOutBufferControllerTrait;
}