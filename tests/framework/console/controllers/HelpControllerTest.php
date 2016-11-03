<?php
namespace yiiunit\framework\console\controllers;

use yii\helpers\Console;
use yiiunit\framework\console\controllers\TestTrait;
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
        $result = Console::stripAnsiFormat($this->runControllerAction('index'));
        $this->assertContains('This is Yii version ', $result);
        $this->assertContains('The following commands are available:', $result);
        $this->assertContains('To see the help of each command, enter:', $result);
        $this->assertContains('bootstrap.php help', $result);
    }

    public function testActionIndexWithHelpCommand()
    {
        $result = Console::stripAnsiFormat($this->runControllerAction('index', ['command' => 'help']));
        $this->assertContains('Displays available commands or the detailed information', $result);
        $this->assertContains('bootstrap.php help [command] [...options...]', $result);
        $this->assertContains('--appconfig: string', $result);
        $this->assertContains('- command: string', $result);
        $this->assertContains('--color: boolean, 0 or 1', $result);
        $this->assertContains('--help, -h: boolean, 0 or 1', $result);
        $this->assertContains('--interactive: boolean, 0 or 1 (defaults to 1)', $result);
    }

    public function testActionIndexWithServeCommand()
    {
        $result = Console::stripAnsiFormat($this->runControllerAction('index', ['command' => 'serve']));
        $this->assertContains('Runs PHP built-in web server', $result);
        $this->assertContains('bootstrap.php serve [address] [...options...]', $result);
        $this->assertContains('- address: string (defaults to \'localhost\')', $result);
        $this->assertContains('--appconfig: string', $result);
        $this->assertContains('--color: boolean, 0 or 1', $result);
        $this->assertContains('--docroot, -t: string (defaults to \'@app/web\')', $result);
        $this->assertContains('--help, -h: boolean, 0 or 1', $result);
        $this->assertContains('--interactive: boolean, 0 or 1 (defaults to 1)', $result);
        $this->assertContains('--port, -p: int (defaults to 8080)', $result);
        $this->assertContains('--router, -r: string', $result);
    }


}


class BufferedHelpController extends HelpController
{
    use StdOutBufferControllerTrait;
}