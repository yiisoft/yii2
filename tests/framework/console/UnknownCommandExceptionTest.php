<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\console;

use Yii;
use yii\console\UnknownCommandException;
use yiiunit\TestCase;

/**
 * @group console
 */
class UnknownCommandExceptionTest extends TestCase
{
    protected function setUp(): void
    {
        $this->mockApplication([
            'enableCoreCommands' => false,
            'controllerMap' => [
                'cache' => 'yii\console\controllers\CacheController',
                'migrate' => 'yii\console\controllers\MigrateController',
                'message' => 'yii\console\controllers\MessageController',
                'whatever' => 'yiiunit\data\console\controllers\FakeController',
                'whatever-empty' => 'yiiunit\data\console\controllers\FakeEmptyController',
                'whatever-no-default' => 'yiiunit\data\console\controllers\FakeNoDefaultController',
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
            ['ca', ['cache', 'cache/flush', 'cache/flush-all', 'cache/flush-schema', 'cache/index']],
            ['cach', ['cache', 'cache/flush', 'cache/flush-all', 'cache/flush-schema', 'cache/index']],
            ['cach/fush', ['cache/flush']],
            ['cach/fushall', ['cache/flush-all']],
            ['what?', []],
            ['', []],
            // test UTF 8 chars
            ['ёлка', []],
            // this crashes levenshtein because string is longer than 255 chars
            [str_repeat('asdw1234', 31), []],
            [str_repeat('asdw1234', 32), []],
            [str_repeat('asdw1234', 33), []],
            ['what', ['whatever', 'whatever/default', 'whatever/second', 'whatever-no-default/index']],
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
