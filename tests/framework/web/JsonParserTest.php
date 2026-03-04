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
use yiiunit\TestCase;

/**
 * @group web
 */
class JsonParserTest extends TestCase
{
    public function testParsesJsonArray(): void
    {
        $parser = new JsonParser();
        $result = $parser->parse('{"name":"John","age":30}', 'application/json');

        $this->assertSame(['name' => 'John', 'age' => 30], $result);
    }

    public function testParsesJsonAsObject(): void
    {
        $parser = new JsonParser();
        $parser->asArray = false;
        $result = $parser->parse('{"name":"John"}', 'application/json');

        $this->assertInstanceOf(\stdClass::class, $result);
        $this->assertSame('John', $result->name);
    }

    public function testEmptyBodyReturnsEmptyArray(): void
    {
        $parser = new JsonParser();
        $result = $parser->parse('', 'application/json');

        $this->assertSame([], $result);
    }

    public function testNullJsonReturnsEmptyArray(): void
    {
        $parser = new JsonParser();
        $result = $parser->parse('null', 'application/json');

        $this->assertSame([], $result);
    }

    public function testStripsJsonpCallback(): void
    {
        $parser = new JsonParser();
        $result = $parser->parse('callback({"key":"value"})', 'application/javascript');

        $this->assertSame(['key' => 'value'], $result);
    }

    public function testJsonpNotStrippedForJsonContentType(): void
    {
        $parser = new JsonParser();

        $this->expectException(BadRequestHttpException::class);
        $parser->parse('callback({"key":"value"})', 'application/json');
    }

    public function testInvalidJsonThrowsException(): void
    {
        $parser = new JsonParser();

        $this->expectException(BadRequestHttpException::class);
        $this->expectExceptionMessage('Invalid JSON data in request body: Syntax error');
        $parser->parse('{invalid}', 'application/json');
    }

    public function testInvalidJsonReturnsEmptyArrayWhenThrowDisabled(): void
    {
        $parser = new JsonParser();
        $parser->throwException = false;
        $result = $parser->parse('{invalid}', 'application/json');

        $this->assertSame([], $result);
    }

}
