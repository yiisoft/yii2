<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

declare(strict_types=1);

namespace yiiunit\framework\web;

use yii\web\BadRequestHttpException;
use yii\web\JsonParser;
use yii\web\RequestParserInterface;
use yiiunit\TestCase;

/**
 * @group web
 */
class JsonParserTest extends TestCase
{
    public function testImplementsRequestParserInterface(): void
    {
        $parser = new JsonParser();

        $this->assertInstanceOf(RequestParserInterface::class, $parser);
    }

    public function testDefaultPropertyValues(): void
    {
        $parser = new JsonParser();

        $this->assertTrue($parser->asArray);
        $this->assertTrue($parser->throwException);
    }

    /**
     * @dataProvider provideValidJsonData
     */
    public function testParseValidJson(string $body, array $expected): void
    {
        $parser = new JsonParser();
        $result = $parser->parse($body, 'application/json');

        $this->assertSame($expected, $result);
    }

    public static function provideValidJsonData(): array
    {
        return [
            'object' => ['{"foo":"bar","baz":1}', ['foo' => 'bar', 'baz' => 1]],
            'array' => ['[1,2,3]', [1, 2, 3]],
            'nested' => ['{"a":{"b":"c"}}', ['a' => ['b' => 'c']]],
            'empty object' => ['{}', []],
            'empty array' => ['[]', []],
        ];
    }

    public function testParseEmptyBodyReturnsEmptyArray(): void
    {
        $parser = new JsonParser();
        $parser->throwException = false;

        $this->assertSame([], $parser->parse('', 'application/json'));
    }

    public function testParseNullJsonReturnsEmptyArray(): void
    {
        $parser = new JsonParser();

        $this->assertSame([], $parser->parse('null', 'application/json'));
    }

    /**
     * @dataProvider provideJsonpData
     */
    public function testParseJsonp(string $body, array $expected): void
    {
        $parser = new JsonParser();
        $result = $parser->parse($body, 'application/javascript');

        $this->assertSame($expected, $result);
    }

    public static function provideJsonpData(): array
    {
        return [
            'simple callback' => [
                'callback({"foo":"bar"})',
                ['foo' => 'bar'],
            ],
            'callback with semicolon' => [
                'parseResponse({"foo":"bar","baz":1});',
                ['foo' => 'bar', 'baz' => 1],
            ],
        ];
    }

    public function testParseJsonpNotTriggeredForJsonContentType(): void
    {
        $parser = new JsonParser();
        $parser->throwException = false;

        $result = $parser->parse('callback({"foo":"bar"})', 'application/json');

        $this->assertSame([], $result);
    }

    public function testInvalidJsonThrowsException(): void
    {
        $parser = new JsonParser();

        $this->expectException(BadRequestHttpException::class);
        $this->expectExceptionMessageMatches('/^Invalid JSON data in request body: .+/');

        $parser->parse('{invalid}', 'application/json');
    }

    public function testInvalidJsonWithThrowExceptionDisabled(): void
    {
        $parser = new JsonParser();
        $parser->throwException = false;

        $result = $parser->parse('{invalid}', 'application/json');

        $this->assertSame([], $result);
    }

    public function testAsArrayFalseReturnsObject(): void
    {
        $parser = new JsonParser();
        $parser->asArray = false;

        $result = $parser->parse('{"foo":"bar"}', 'application/json');

        $this->assertInstanceOf(\stdClass::class, $result);
        $this->assertSame('bar', $result->foo);
    }
}
