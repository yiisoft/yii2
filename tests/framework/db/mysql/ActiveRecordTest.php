<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yiiunit\framework\db\mysql;

use yiiunit\data\ar\Document;

/**
 * @group db
 * @group mysql
 */
class ActiveRecordTest extends \yiiunit\framework\db\ActiveRecordTest
{
    public $driverName = 'mysql';

    public function testJsonColumn()
    {
        $props = [
            'obj' => ['a' => ['b' => ['c' => 2.7418]]],
            'array' => [1,2,null,3],
            'null_field' => null,
            'boolean_field' => true,
            'last_update_time' => '2018-02-21',
        ];

        $document = new Document([
            'title' => 'Doc with JSON props',
            'properties' => $props,
        ]);
        $this->assertTrue($document->save(), 'Document can be saved');
        $this->assertNotNull($document->id);

        $retrievedDocument = Document::findOne($document->id);
        $this->assertSame($props, $retrievedDocument->properties, 'Properties are restored from JSON to array without changes');

        $retrievedDocument->properties = ['updatedProps' => $props];
        $this->assertSame(1, $retrievedDocument->update(), 'Document can be updated');

        $retrievedDocument->refresh();
        $this->assertSame(['updatedProps' => $props], $retrievedDocument->properties, 'Properties have been changed during update');
    }
}
