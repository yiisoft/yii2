<?php
namespace yiiunit\framework\db;
 
use yiiunit\data\ar\TestAR;
use yiiunit\TestCase;

/**
 * @group db
 */
class BaseActiveRecordTest extends TestCase
{
    /**
     * @var \yii\db\BaseActiveRecord
     */
    protected $ar;

    protected function setUp()
    {
        parent::setUp();
        $this->mockApplication();
        $this->ar = new TestAR();
    }

    /**
     * Tests that attributes are getting dirty properly
     *
     * @dataProvider dirtyProvider
     * @param mixed $new
     * @param mixed $old
     */
    public function testDirtyAttributes($new, $old)
    {
        $this->ar->setAttribute('v', $new);
        $this->ar->setOldAttributes(['v' => $old]);
        $this->assertNotEmpty($this->ar->getDirtyAttributes(['v']), var_export($new, true)." and " . var_export($old, true) . " should not be equal.");
    }

    /**
     * Tests that attributes aren't getting dirty when they should not
     *
     * @dataProvider nonDirtyProvider
     * @param mixed $new
     * @param mixed $old
     */
    public function testNotDirtyAttributes($new, $old)
    {
        $this->ar->setAttribute('v', $new);
        $this->ar->setOldAttributes(['v' => $old]);
        $this->assertEmpty($this->ar->getDirtyAttributes(['v']), var_export($new, true) . " and " . var_export($old, true) . " should be equal.");
    }

    /**
     * @return array values that should be equal i.e. attribute should not be considered dirty
     */
    public function nonDirtyProvider() {
        return [
            [1, 1],
            ['a', 'a'],
            [true, true],
            
            [1, '1'],
            [1, '1.0'],
            [1.0, '1.0'],
            ['1', '1.0'],
            [0, '0'],
            
            ['1', 1],
            ['1.0', 1],
            ['1.0', 1.0],
            ['1.0', '1'],
            ['0', 0],
            
        ];
    }

    /**
     * @return array values that should not be equal i.e. attribute should be considered dirty
     */
    public function dirtyProvider()
    {
        $values = ['', 0,  null, false, []];

        $falseData = [];
        foreach ($values as $val1) {
            foreach ($values as $val2) {
                if ($val1 !== $val2) {
                    $falseData[] = [$val1, $val2];
                }
            }
        }
        
        $trueData = [
            [1, 'a'],
            [1, '1a'],
            [1, true],
            [1, -1],
            
            ['a', 1],
            ['1a', 1],
            [true, 1],
            [-1, 1],
        ];
        
        return array_merge($falseData, $trueData);
    }
}