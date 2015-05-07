<?php

namespace yiiunit\framework\helpers;

use yii\base\Object;
use yii\helpers\ArrayHelper;
use yiiunit\TestCase;
use yii\data\Sort;

class Post1
{
    public $id = 23;
    public $title = 'tt';
}

class Post2 extends Object
{
    public $id = 123;
    public $content = 'test';
    private $secret = 's';
    public function getSecret()
    {
        return $this->secret;
    }
}

class Post3 extends Object
{
    public $id = 33;
    public $subObject;

    public function init()
    {
        $this->subObject = new Post2();
    }
}

/**
 * @group helpers
 */
class ArrayHelperTest extends TestCase
{
    protected function setUp()
    {
        parent::setUp();
        $this->mockApplication();
    }

    public function testToArray()
    {
        $object = new Post1;
        $this->assertEquals(get_object_vars($object), ArrayHelper::toArray($object));
        $object = new Post2;
        $this->assertEquals(get_object_vars($object), ArrayHelper::toArray($object));

        $object1 = new Post1;
        $object2 = new Post2;
        $this->assertEquals([
            get_object_vars($object1),
            get_object_vars($object2),
        ], ArrayHelper::toArray([
            $object1,
            $object2,
        ]));

        $object = new Post2;
        $this->assertEquals([
            'id' => 123,
            'secret' => 's',
            '_content' => 'test',
            'length' => 4,
        ], ArrayHelper::toArray($object, [
            $object->className() => [
                'id', 'secret',
                '_content' => 'content',
                'length' => function ($post) {
                    return strlen($post->content);
                }
            ]
        ]));

        $object = new Post3();
        $this->assertEquals(get_object_vars($object), ArrayHelper::toArray($object, [], false));
        $this->assertEquals([
            'id' => 33,
            'subObject' => [
                'id' => 123,
                'content' => 'test',
            ],
        ], ArrayHelper::toArray($object));
    }

    public function testRemove()
    {
        $array = ['name' => 'b', 'age' => 3];
        $name = ArrayHelper::remove($array, 'name');

        $this->assertEquals($name, 'b');
        $this->assertEquals($array, ['age' => 3]);

        $default = ArrayHelper::remove($array, 'nonExisting', 'defaultValue');
        $this->assertEquals('defaultValue', $default);
    }

    public function testMultisort()
    {
        // single key
        $array = [
            ['name' => 'b', 'age' => 3],
            ['name' => 'a', 'age' => 1],
            ['name' => 'c', 'age' => 2],
        ];
        ArrayHelper::multisort($array, 'name');
        $this->assertEquals(['name' => 'a', 'age' => 1], $array[0]);
        $this->assertEquals(['name' => 'b', 'age' => 3], $array[1]);
        $this->assertEquals(['name' => 'c', 'age' => 2], $array[2]);

        // multiple keys
        $array = [
            ['name' => 'b', 'age' => 3],
            ['name' => 'a', 'age' => 2],
            ['name' => 'a', 'age' => 1],
        ];
        ArrayHelper::multisort($array, ['name', 'age']);
        $this->assertEquals(['name' => 'a', 'age' => 1], $array[0]);
        $this->assertEquals(['name' => 'a', 'age' => 2], $array[1]);
        $this->assertEquals(['name' => 'b', 'age' => 3], $array[2]);

        // case-insensitive
        $array = [
            ['name' => 'a', 'age' => 3],
            ['name' => 'b', 'age' => 2],
            ['name' => 'B', 'age' => 4],
            ['name' => 'A', 'age' => 1],
        ];

        ArrayHelper::multisort($array, ['name', 'age'], SORT_ASC, [SORT_STRING, SORT_REGULAR]);
        $this->assertEquals(['name' => 'A', 'age' => 1], $array[0]);
        $this->assertEquals(['name' => 'B', 'age' => 4], $array[1]);
        $this->assertEquals(['name' => 'a', 'age' => 3], $array[2]);
        $this->assertEquals(['name' => 'b', 'age' => 2], $array[3]);

        ArrayHelper::multisort($array, ['name', 'age'], SORT_ASC, [SORT_STRING | SORT_FLAG_CASE, SORT_REGULAR]);
        $this->assertEquals(['name' => 'A', 'age' => 1], $array[0]);
        $this->assertEquals(['name' => 'a', 'age' => 3], $array[1]);
        $this->assertEquals(['name' => 'b', 'age' => 2], $array[2]);
        $this->assertEquals(['name' => 'B', 'age' => 4], $array[3]);
    }

    public function testMultisortUseSort()
    {
        // single key
        $sort = new Sort([
            'attributes' => ['name', 'age'],
            'defaultOrder' => ['name' => SORT_ASC],
        ]);
        $orders = $sort->getOrders();

        $array = [
            ['name' => 'b', 'age' => 3],
            ['name' => 'a', 'age' => 1],
            ['name' => 'c', 'age' => 2],
        ];
        ArrayHelper::multisort($array, array_keys($orders), array_values($orders));
        $this->assertEquals(['name' => 'a', 'age' => 1], $array[0]);
        $this->assertEquals(['name' => 'b', 'age' => 3], $array[1]);
        $this->assertEquals(['name' => 'c', 'age' => 2], $array[2]);

        // multiple keys
        $sort = new Sort([
            'attributes' => ['name', 'age'],
            'defaultOrder' => ['name' => SORT_ASC, 'age' => SORT_DESC],
        ]);
        $orders = $sort->getOrders();

        $array = [
            ['name' => 'b', 'age' => 3],
            ['name' => 'a', 'age' => 2],
            ['name' => 'a', 'age' => 1],
        ];
        ArrayHelper::multisort($array, array_keys($orders), array_values($orders));
        $this->assertEquals(['name' => 'a', 'age' => 2], $array[0]);
        $this->assertEquals(['name' => 'a', 'age' => 1], $array[1]);
        $this->assertEquals(['name' => 'b', 'age' => 3], $array[2]);
    }

    public function testMerge()
    {
        $a = [
            'name' => 'Yii',
            'version' => '1.0',
            'options' => [
                'namespace' => false,
                'unittest' => false,
            ],
            'features' => [
                'mvc',
            ],
        ];
        $b = [
            'version' => '1.1',
            'options' => [
                'unittest' => true,
            ],
            'features' => [
                'gii',
            ],
        ];
        $c = [
            'version' => '2.0',
            'options' => [
                'namespace' => true,
            ],
            'features' => [
                'debug',
            ],
        ];

        $result = ArrayHelper::merge($a, $b, $c);
        $expected = [
            'name' => 'Yii',
            'version' => '2.0',
            'options' => [
                'namespace' => true,
                'unittest' => true,
            ],
            'features' => [
                'mvc',
                'gii',
                'debug',
            ],
        ];

        $this->assertEquals($expected, $result);
    }

    public function testIndex()
    {
        $array = [
            ['id' => '123', 'data' => 'abc'],
            ['id' => '345', 'data' => 'def'],
        ];
        $result = ArrayHelper::index($array, 'id');
        $this->assertEquals([
            '123' => ['id' => '123', 'data' => 'abc'],
            '345' => ['id' => '345', 'data' => 'def'],
        ], $result);

        $result = ArrayHelper::index($array, function ($element) {
            return $element['data'];
        });
        $this->assertEquals([
            'abc' => ['id' => '123', 'data' => 'abc'],
            'def' => ['id' => '345', 'data' => 'def'],
        ], $result);
    }

    public function testGetColumn()
    {
        $array = [
            'a' => ['id' => '123', 'data' => 'abc'],
            'b' => ['id' => '345', 'data' => 'def'],
        ];
        $result = ArrayHelper::getColumn($array, 'id');
        $this->assertEquals(['a' => '123', 'b' => '345'], $result);
        $result = ArrayHelper::getColumn($array, 'id', false);
        $this->assertEquals(['123', '345'], $result);

        $result = ArrayHelper::getColumn($array, function ($element) {
            return $element['data'];
        });
        $this->assertEquals(['a' => 'abc', 'b' => 'def'], $result);
        $result = ArrayHelper::getColumn($array, function ($element) {
            return $element['data'];
        }, false);
        $this->assertEquals(['abc', 'def'], $result);
    }

    public function testMap()
    {
        $array = [
            ['id' => '123', 'name' => 'aaa', 'class' => 'x'],
            ['id' => '124', 'name' => 'bbb', 'class' => 'x'],
            ['id' => '345', 'name' => 'ccc', 'class' => 'y'],
        ];

        $result = ArrayHelper::map($array, 'id', 'name');
        $this->assertEquals([
            '123' => 'aaa',
            '124' => 'bbb',
            '345' => 'ccc',
        ], $result);

        $result = ArrayHelper::map($array, 'id', 'name', 'class');
        $this->assertEquals([
            'x' => [
                '123' => 'aaa',
                '124' => 'bbb',
            ],
            'y' => [
                '345' => 'ccc',
            ],
        ], $result);
    }

    public function testKeyExists()
    {
        $array = [
            'a' => 1,
            'B' => 2,
        ];
        $this->assertTrue(ArrayHelper::keyExists('a', $array));
        $this->assertFalse(ArrayHelper::keyExists('b', $array));
        $this->assertTrue(ArrayHelper::keyExists('B', $array));
        $this->assertFalse(ArrayHelper::keyExists('c', $array));

        $this->assertTrue(ArrayHelper::keyExists('a', $array, false));
        $this->assertTrue(ArrayHelper::keyExists('b', $array, false));
        $this->assertTrue(ArrayHelper::keyExists('B', $array, false));
        $this->assertFalse(ArrayHelper::keyExists('c', $array, false));
    }

    public function valueProvider()
    {
        return [
            ['name', 'test'],
            ['noname', null],
            ['noname', 'test', 'test'],
            ['post.id', 5],
            ['post.id', 5, 'test'],
            ['nopost.id', null],
            ['nopost.id', 'test', 'test'],
            ['post.author.name', 'cebe'],
            ['post.author.noname', null],
            ['post.author.noname', 'test', 'test'],
            ['post.author.profile.title', '1337'],
            ['admin.firstname', 'Qiang'],
            ['admin.firstname', 'Qiang', 'test'],
            ['admin.lastname', 'Xue'],
            [
                function ($array, $defaultValue) {
                    return $array['date'] . $defaultValue;
                },
                '31-12-2113test',
                'test'
            ],
            [['version', '1.0', 'status'], 'released'],
            [['version', '1.0', 'date'], 'defaultValue', 'defaultValue'],
        ];
    }

    /**
     * @dataProvider valueProvider
     *
     * @param $key
     * @param $expected
     * @param null $default
     */
    public function testGetValue($key, $expected, $default = null)
    {
        $array = [
            'name' => 'test',
            'date' => '31-12-2113',
            'post' => [
                'id' => 5,
                'author' => [
                    'name' => 'cebe',
                    'profile' => [
                        'title' => '1337',
                    ],
                ],
            ],
            'admin.firstname' => 'Qiang',
            'admin.lastname' => 'Xue',
            'admin' => [
                'lastname' => 'cebe',
            ],
            'version' => [
                '1.0' => [
                    'status' => 'released'
                ]
            ],
        ];

        $this->assertEquals($expected, ArrayHelper::getValue($array, $key, $default));
    }

    public function testIsAssociative()
    {
        $this->assertFalse(ArrayHelper::isAssociative('test'));
        $this->assertFalse(ArrayHelper::isAssociative([]));
        $this->assertFalse(ArrayHelper::isAssociative([1, 2, 3]));
        $this->assertTrue(ArrayHelper::isAssociative(['name' => 1, 'value' => 'test']));
        $this->assertFalse(ArrayHelper::isAssociative(['name' => 1, 'value' => 'test', 3]));
        $this->assertTrue(ArrayHelper::isAssociative(['name' => 1, 'value' => 'test', 3], false));
    }

    public function testIsIndexed()
    {
        $this->assertFalse(ArrayHelper::isIndexed('test'));
        $this->assertTrue(ArrayHelper::isIndexed([]));
        $this->assertTrue(ArrayHelper::isIndexed([1, 2, 3]));
        $this->assertTrue(ArrayHelper::isIndexed([2 => 'a', 3 => 'b']));
        $this->assertFalse(ArrayHelper::isIndexed([2 => 'a', 3 => 'b'], true));
    }

    public function testHtmlEncode()
    {
        $array = [
            'abc' => '123',
            '<' => '>',
            'cde' => false,
            3 => 'blank',
            [
                '<>' => 'a<>b',
                '23' => true,
            ]
        ];
        $this->assertEquals([
            'abc' => '123',
            '<' => '&gt;',
            'cde' => false,
            3 => 'blank',
            [
                '<>' => 'a&lt;&gt;b',
                '23' => true,
            ]
        ], ArrayHelper::htmlEncode($array));
        $this->assertEquals([
            'abc' => '123',
            '&lt;' => '&gt;',
            'cde' => false,
            3 => 'blank',
            [
                '&lt;&gt;' => 'a&lt;&gt;b',
                '23' => true,
            ]
        ], ArrayHelper::htmlEncode($array, false));
    }

    public function testHtmlDecode()
    {
        $array = [
            'abc' => '123',
            '&lt;' => '&gt;',
            'cde' => false,
            3 => 'blank',
            [
                '<>' => 'a&lt;&gt;b',
                '23' => true,
            ]
        ];
        $this->assertEquals([
            'abc' => '123',
            '&lt;' => '>',
            'cde' => false,
            3 => 'blank',
            [
                '<>' => 'a<>b',
                '23' => true,
            ]
        ], ArrayHelper::htmlDecode($array));
        $this->assertEquals([
            'abc' => '123',
            '<' => '>',
            'cde' => false,
            3 => 'blank',
            [
                '<>' => 'a<>b',
                '23' => true,
            ]
        ], ArrayHelper::htmlDecode($array, false));
    }
}
