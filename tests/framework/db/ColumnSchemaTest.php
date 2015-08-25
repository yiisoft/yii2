<?php

namespace yiiunit\framework\db;

use yiiunit\framework\db\DatabaseTestCase;
use yii\db\ColumnSchema;

/**
 * @group db
 * @group mysql
 */
class ColumnSchemaTest extends DatabaseTestCase
{

    public function testDbTypecastNoCommas()
    {
        $locale = setlocale(LC_NUMERIC, 0);
        if (false === $locale) {
            $this->markTestSkipped('Your platform does not support locales.');
        }

        try {
            // This one sets decimal mark to comma sign
            setlocale(LC_NUMERIC, 'ru_RU.utf8');
            $cschemaDouble = new ColumnSchema([
                'type' => 'double',
                'dbType' => 'double',
                'phpType' => 'double',
            ]);
            $this->assertEquals('1.23', (string) $cschemaDouble->dbTypecast('1.23'));
            $this->assertEquals('1.23', (string) $cschemaDouble->dbTypecast(1.23));
            $this->assertEquals('2.0E+30', (string) $cschemaDouble->dbTypecast(2e+30));

            $cschemaFloat = new ColumnSchema([
                'type' => 'float',
                'phpType' => 'double',
                'dbType' => 'float'
            ]);
            $this->assertEquals('1.23', (string) $cschemaFloat->dbTypecast('1.23'));
            $this->assertEquals('1.23', (string) $cschemaFloat->dbTypecast(1.23));
            $this->assertEquals('2.0E+30', (string) $cschemaDouble->dbTypecast(2e+30));
            
            setlocale(LC_NUMERIC, $locale);
        } catch (\Exception $e) {
            setlocale(LC_NUMERIC, $locale);
            throw $e;
        }
    }

}
