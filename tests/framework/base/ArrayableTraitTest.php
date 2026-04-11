<?php

declare(strict_types=1);

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\base;

use JsonSerializable;
use yii\base\Arrayable;
use yii\base\ArrayableTrait;
use yii\base\Model;
use yii\web\Link;
use yii\web\Linkable;
use yiiunit\TestCase;

class ArrayableTraitTest extends TestCase
{
    public function testFieldsReturnsPublicProperties(): void
    {
        $model = new ArrayableStub();
        $model->name = 'John';
        $model->email = 'john@example.com';

        $fields = $model->fields();
        $this->assertArrayHasKey('name', $fields);
        $this->assertArrayHasKey('email', $fields);
        $this->assertSame('name', $fields['name']);
        $this->assertSame('email', $fields['email']);
    }

    public function testExtraFieldsReturnsEmptyArray(): void
    {
        $model = new ArrayableStub();
        $this->assertSame([], $model->extraFields());
    }

    public function testToArrayReturnsAllFields(): void
    {
        $model = new ArrayableStub();
        $model->name = 'John';
        $model->email = 'john@example.com';

        $result = $model->toArray();
        $this->assertSame('John', $result['name']);
        $this->assertSame('john@example.com', $result['email']);
    }

    public function testToArrayWithSpecificFields(): void
    {
        $model = new ArrayableStub();
        $model->name = 'John';
        $model->email = 'john@example.com';

        $result = $model->toArray(['name']);
        $this->assertArrayHasKey('name', $result);
        $this->assertArrayNotHasKey('email', $result);
    }

    public function testToArrayWithWildcardReturnsAll(): void
    {
        $model = new ArrayableStub();
        $model->name = 'John';
        $model->email = 'john@example.com';

        $result = $model->toArray(['*']);
        $this->assertArrayHasKey('name', $result);
        $this->assertArrayHasKey('email', $result);
    }

    public function testToArrayWithCallableDefinition(): void
    {
        $model = new ArrayableCallableFieldsStub();
        $model->firstName = 'John';
        $model->lastName = 'Doe';

        $result = $model->toArray();
        $this->assertSame('John Doe', $result['fullName']);
    }

    public function testToArrayWithExtraFields(): void
    {
        $model = new ArrayableExtraFieldsStub();
        $model->name = 'John';
        $model->email = 'john@example.com';
        $model->password = 'secret';

        $result = $model->toArray([], ['password']);
        $this->assertSame('John', $result['name']);
        $this->assertSame('john@example.com', $result['email']);
        $this->assertSame('secret', $result['password']);
    }

    public function testToArrayExtraFieldsNotIncludedByDefault(): void
    {
        $model = new ArrayableExtraFieldsStub();
        $model->name = 'John';
        $model->email = 'john@example.com';
        $model->password = 'secret';

        $result = $model->toArray();
        $this->assertArrayNotHasKey('password', $result);
    }

    public function testToArrayWithNestedArrayable(): void
    {
        $inner = new ArrayableStub();
        $inner->name = 'inner';
        $inner->email = 'inner@test.com';

        $outer = new ArrayableNestedStub();
        $outer->title = 'outer';
        $outer->child = $inner;

        $result = $outer->toArray();
        $this->assertSame('outer', $result['title']);
        $this->assertIsArray($result['child']);
        $this->assertSame('inner', $result['child']['name']);
    }

    public function testToArrayWithNestedJsonSerializable(): void
    {
        $json = new JsonSerializableStub(['key' => 'value']);

        $model = new ArrayableNestedStub();
        $model->title = 'test';
        $model->child = $json;

        $result = $model->toArray();
        $this->assertSame(['key' => 'value'], $result['child']);
    }

    public function testToArrayWithArrayOfArrayables(): void
    {
        $item1 = new ArrayableStub();
        $item1->name = 'a';
        $item1->email = 'a@test.com';

        $item2 = new ArrayableStub();
        $item2->name = 'b';
        $item2->email = 'b@test.com';

        $model = new ArrayableNestedStub();
        $model->title = 'list';
        $model->child = [$item1, $item2];

        $result = $model->toArray();
        $this->assertCount(2, $result['child']);
        $this->assertSame('a', $result['child'][0]['name']);
        $this->assertSame('b', $result['child'][1]['name']);
    }

    public function testToArrayWithArrayOfJsonSerializables(): void
    {
        $model = new ArrayableNestedStub();
        $model->title = 'list';
        $model->child = [
            new JsonSerializableStub(['x' => 1]),
            new JsonSerializableStub(['y' => 2]),
        ];

        $result = $model->toArray();
        $this->assertSame(['x' => 1], $result['child'][0]);
        $this->assertSame(['y' => 2], $result['child'][1]);
    }

    public function testToArrayWithArrayOfScalars(): void
    {
        $model = new ArrayableNestedStub();
        $model->title = 'tags';
        $model->child = ['php', 'yii'];

        $result = $model->toArray();
        $this->assertSame(['php', 'yii'], $result['child']);
    }

    public function testToArrayNonRecursive(): void
    {
        $inner = new ArrayableStub();
        $inner->name = 'inner';
        $inner->email = 'inner@test.com';

        $outer = new ArrayableNestedStub();
        $outer->title = 'outer';
        $outer->child = $inner;

        $result = $outer->toArray([], [], false);
        $this->assertSame('outer', $result['title']);
        $this->assertInstanceOf(ArrayableStub::class, $result['child']);
    }

    public function testToArrayWithNestedFieldSelection(): void
    {
        $inner = new ArrayableStub();
        $inner->name = 'inner';
        $inner->email = 'inner@test.com';

        $outer = new ArrayableNestedStub();
        $outer->title = 'outer';
        $outer->child = $inner;

        $result = $outer->toArray(['title', 'child.name']);
        $this->assertSame('outer', $result['title']);
        $this->assertArrayHasKey('name', $result['child']);
        $this->assertArrayNotHasKey('email', $result['child']);
    }

    public function testToArrayWithNestedExpand(): void
    {
        $inner = new ArrayableExtraFieldsStub();
        $inner->name = 'inner';
        $inner->email = 'inner@test.com';
        $inner->password = 'secret';

        $outer = new ArrayableNestedStub();
        $outer->title = 'outer';
        $outer->child = $inner;

        $result = $outer->toArray([], ['child.password']);
        $this->assertSame('secret', $result['child']['password']);
    }

    public function testExtractRootFieldsSimple(): void
    {
        $model = new ArrayableStub();
        $result = $model->callExtractRootFields(['name', 'email']);
        $this->assertSame(['name', 'email'], $result);
    }

    public function testExtractRootFieldsNested(): void
    {
        $model = new ArrayableStub();
        $result = $model->callExtractRootFields(['item.id', 'item.name', 'other']);
        $this->assertSame(['item', 'other'], array_values($result));
    }

    public function testExtractRootFieldsWildcard(): void
    {
        $model = new ArrayableStub();
        $result = $model->callExtractRootFields(['*']);
        $this->assertSame([], $result);
    }

    public function testExtractFieldsForSimple(): void
    {
        $model = new ArrayableStub();
        $result = $model->callExtractFieldsFor(['item.id', 'item.name', 'other'], 'item');
        $this->assertSame(['id', 'name'], $result);
    }

    public function testExtractFieldsForNoMatch(): void
    {
        $model = new ArrayableStub();
        $result = $model->callExtractFieldsFor(['item.id'], 'other');
        $this->assertSame([], $result);
    }

    public function testExtractFieldsForDeepNesting(): void
    {
        $model = new ArrayableStub();
        $result = $model->callExtractFieldsFor(['item.sub.deep'], 'item');
        $this->assertSame(['sub.deep'], $result);
    }

    public function testResolveFieldsWithIntegerKeys(): void
    {
        $model = new ArrayableIntegerKeyFieldsStub();
        $model->name = 'John';
        $model->email = 'john@test.com';

        $result = $model->toArray();
        $this->assertSame('John', $result['name']);
        $this->assertSame('john@test.com', $result['email']);
    }

    public function testResolveFieldsExtraWithIntegerKeys(): void
    {
        $model = new ArrayableIntegerKeyExtraStub();
        $model->name = 'John';
        $model->secret = 'hidden';

        $result = $model->toArray([], ['secret']);
        $this->assertSame('John', $result['name']);
        $this->assertSame('hidden', $result['secret']);
    }

    public function testToArrayWithLinkable(): void
    {
        $model = new ArrayableLinkableStub();
        $model->name = 'John';

        $result = $model->toArray();
        $this->assertSame('John', $result['name']);
        $this->assertArrayHasKey('_links', $result);
    }

    public function testToArrayEmptyExpand(): void
    {
        $model = new ArrayableExtraFieldsStub();
        $model->name = 'John';
        $model->email = 'john@test.com';
        $model->password = 'secret';

        $result = $model->toArray([], []);
        $this->assertArrayNotHasKey('password', $result);
    }

    public function testToArrayNullProperty(): void
    {
        $model = new ArrayableStub();
        $model->name = null;
        $model->email = 'test@test.com';

        $result = $model->toArray();
        $this->assertNull($result['name']);
        $this->assertSame('test@test.com', $result['email']);
    }

    public function testArrayOfArrayablesReturnsArrays(): void
    {
        $item = new ArrayableStub();
        $item->name = 'test';
        $item->email = 'test@test.com';

        $model = new ArrayableNestedStub();
        $model->title = 'list';
        $model->child = [$item];

        $result = $model->toArray();
        $this->assertIsArray($result['child'][0]);
        $this->assertSame('test', $result['child'][0]['name']);
    }

    public function testExtractFieldsForWithRegexChars(): void
    {
        $model = new ArrayableStub();
        $result = $model->callExtractFieldsFor(['item[0].id', 'item[0].name'], 'item[0]');
        $this->assertSame(['id', 'name'], $result);
    }

    public function testExtractFieldsForDeduplicates(): void
    {
        $model = new ArrayableStub();
        $result = $model->callExtractFieldsFor(['item.id', 'item.id'], 'item');
        $this->assertSame(['id'], $result);
    }

    public function testResolveFieldsEarlyReturnOnEmptyExpand(): void
    {
        $model = new ArrayableExtraFieldsStub();
        $model->name = 'John';
        $model->email = 'john@test.com';
        $model->password = 'secret';

        $result = $model->toArray(['name'], []);
        $this->assertSame(['name' => 'John'], $result);
    }
}

class ArrayableStub extends Model implements Arrayable
{
    use ArrayableTrait;

    public $name;
    public $email;

    public function callExtractRootFields(array $fields): array
    {
        return $this->extractRootFields($fields);
    }

    public function callExtractFieldsFor(array $fields, string $rootField): array
    {
        return $this->extractFieldsFor($fields, $rootField);
    }
}

class ArrayableCallableFieldsStub extends Model implements Arrayable
{
    use ArrayableTrait;

    public $firstName;
    public $lastName;

    public function fields()
    {
        return [
            'firstName',
            'lastName',
            'fullName' => function ($model, $field) {
                return $model->firstName . ' ' . $model->lastName;
            },
        ];
    }
}

class ArrayableExtraFieldsStub extends Model implements Arrayable
{
    use ArrayableTrait;

    public $name;
    public $email;
    public $password;

    public function fields()
    {
        return [
            'name' => 'name',
            'email' => 'email',
        ];
    }

    public function extraFields()
    {
        return [
            'password' => 'password',
        ];
    }
}

class ArrayableNestedStub extends Model implements Arrayable
{
    use ArrayableTrait;

    public $title;
    public $child;
}

class JsonSerializableStub implements JsonSerializable
{
    private $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function jsonSerialize(): array
    {
        return $this->data;
    }
}

class ArrayableIntegerKeyFieldsStub extends Model implements Arrayable
{
    use ArrayableTrait;

    public $name;
    public $email;

    public function fields()
    {
        return ['name', 'email'];
    }
}

class ArrayableIntegerKeyExtraStub extends Model implements Arrayable
{
    use ArrayableTrait;

    public $name;
    public $secret;

    public function fields()
    {
        return ['name' => 'name'];
    }

    public function extraFields()
    {
        return ['secret'];
    }
}

class ArrayableLinkableStub extends Model implements Arrayable, Linkable
{
    use ArrayableTrait;

    public $name;

    public function getLinks()
    {
        return [
            Link::REL_SELF => '/api/test/1',
        ];
    }
}
