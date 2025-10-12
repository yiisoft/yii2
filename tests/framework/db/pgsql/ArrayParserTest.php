<?php

namespace yiiunit\framework\db\pgsql;

use yii\db\pgsql\ArrayParser;
use yiiunit\TestCase;

class ArrayParserTest extends TestCase
{
    /**
     * @var ArrayParser
     */
    protected $arrayParser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->arrayParser = new ArrayParser();
    }

    public static function convertProvider(): array
    {
        return [
            ['{}', []],
            ['{,}', [null, null]],
            ['{,,}', [null, null, null]],
            ['{1,2,}', ['1','2',null]],
            ['{{},,1}', [[], null, '1']],
            ['{"{\"key\":\"value\"}",NULL,"NULL","{}"}', ['{"key":"value"}', null, 'NULL', '{}']],
            ['{boo,",",,test}', ['boo', ',', null, 'test']],
            ['{"string1","str\\\\in\\"g2","str,ing3"}', ['string1','str\\in"g2','str,ing3']],
            ['{{1,2,3},{4,5,6},{7,8,9}}', [['1','2','3'], ['4','5','6'], ['7','8','9']]],
            ['{utf8€,👍}', ['utf8€', '👍']],
            ['{"","","{}",{}}', ['', '', '{}', []]]
        ];
    }

    /**
     * @dataProvider convertProvider
     *
     * @param string $string The string to convert.
     * @param array $expected The expected result.
     */
    public function testConvert(string $string, array $expected): void
    {
        $this->assertSame($expected, $this->arrayParser->parse($string));
    }
}
