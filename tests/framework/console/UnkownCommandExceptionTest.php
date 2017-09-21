<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yiiunit\framework\console;

use Yii;
use yii\console\Application;
use yii\console\UnknownCommandException;
use yiiunit\TestCase;

/**
 * @group console
 */
class UnkownCommandExceptionTest extends TestCase
{
    public function setUp()
    {
        $this->mockApplication([
            'enableCoreCommands' => false,
            'controllerMap' => [
                'cache' => 'yii\console\controllers\CacheController',
                'migrate' => 'yii\console\controllers\MigrateController',
                'message' => 'yii\console\controllers\MessageController',
            ],
        ]);
    }

    public function suggestedCommandsProvider()
    {
        return [
            ['migate', ['migrate']],
            ['mihate/u', ['migrate/up']],
            ['mirgte/u', ['migrate/up']],
            ['mirgte/up', ['migrate/up']],
            ['mirgte', ['migrate']],
            ['hlp', ['help']],
            ['ca', ['cache', 'cache/clear', 'cache/clear-all', 'cache/clear-schema', 'cache/index']],
            ['cach', ['cache', 'cache/clear', 'cache/clear-all', 'cache/clear-schema', 'cache/index']],
            ['cach/clear', ['cache/clear']],
            ['cach/clearall', ['cache/clear-all']],
            ['what?', []],
            // test UTF 8 chars
            ['ёлка', []],
            // this crashes levenshtein because string is longer than 255 chars
            [str_repeat('asdw1234', 31), []],
            [str_repeat('asdw1234', 32), []],
            [str_repeat('asdw1234', 33), []],
        ];
    }

    /**
     * @dataProvider suggestedCommandsProvider
     * @param string $command
     * @param array $expectedSuggestion
     */
    public function testSuggestCommand($command, $expectedSuggestion)
    {
        $exception = new UnknownCommandException($command, Yii::$app);
        $this->assertEquals($expectedSuggestion, $exception->getSuggestedAlternatives());
    }

    public function testNameAndConstructor()
    {
        $exception = new UnknownCommandException('test', Yii::$app);
        $this->assertEquals('Unknown command', $exception->getName());
        $this->assertEquals('test', $exception->command);
    }
}
