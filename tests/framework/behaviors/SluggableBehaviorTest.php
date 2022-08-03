<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\behaviors;

use Yii;
use yii\behaviors\SluggableBehavior;
use yii\db\ActiveRecord;
use yii\db\Connection;
use yiiunit\TestCase;

/**
 * Unit test for [[\yii\behaviors\SluggableBehavior]].
 * @see SluggableBehavior
 *
 * @group behaviors
 */
class SluggableBehaviorTest extends TestCase
{
    /**
     * @var Connection test db connection
     */
    protected $dbConnection;

    public static function setUpBeforeClass()
    {
        if (!extension_loaded('pdo') || !extension_loaded('pdo_sqlite')) {
            static::markTestSkipped('PDO and SQLite extensions are required.');
        }
    }

    public function setUp()
    {
        $this->mockApplication([
            'components' => [
                'db' => [
                    'class' => '\yii\db\Connection',
                    'dsn' => 'sqlite::memory:',
                ],
            ],
        ]);

        $columns = [
            'id' => 'pk',
            'name' => 'string',
            'slug' => 'string',
            'category_id' => 'integer',
            'belongs_to_id' => 'integer',
        ];
        Yii::$app->getDb()->createCommand()->createTable('test_slug', $columns)->execute();

        $columns = [
            'id' => 'pk',
            'name' => 'string',
        ];
        Yii::$app->getDb()->createCommand()->createTable('test_slug_related', $columns)->execute();
    }

    public function tearDown()
    {
        Yii::$app->getDb()->close();
        parent::tearDown();
        gc_enable();
        gc_collect_cycles();
    }

    // Tests :

    public function testSlug()
    {
        $model = new ActiveRecordSluggable();
        $model->name = 'test name';
        $model->validate();

        $this->assertEquals('test-name', $model->slug);
    }

    /**
     * @depends testSlug
     */
    public function testSlugSeveralAttributes()
    {
        $model = new ActiveRecordSluggable();
        $model->getBehavior('sluggable')->attribute = ['name', 'category_id'];

        $model->name = 'test';
        $model->category_id = 10;

        $model->validate();
        $this->assertEquals('test-10', $model->slug);
    }

    /**
     * @depends testSlug
     */
    public function testSlugRelatedAttribute()
    {
        $model = new ActiveRecordSluggable();
        $model->getBehavior('sluggable')->attribute = 'related.name';

        $relatedmodel = new ActiveRecordRelated();
        $relatedmodel->name = 'I am an value inside an related activerecord model';
        $relatedmodel->save(false);

        $model->belongs_to_id = $relatedmodel->id;

        $model->validate();

        $this->assertEquals('i-am-an-value-inside-an-related-activerecord-model', $model->slug);
    }

    /**
     * @depends testSlug
     */
    public function testUniqueByIncrement()
    {
        $name = 'test name';

        $model = new ActiveRecordSluggableUnique();
        $model->name = $name;
        $model->save();

        $model = new ActiveRecordSluggableUnique();
        $model->sluggable->uniqueSlugGenerator = 'increment';
        $model->name = $name;
        $model->save();

        $this->assertEquals('test-name-2', $model->slug);
    }

    /**
     * @depends testUniqueByIncrement
     */
    public function testUniqueByCallback()
    {
        $name = 'test name';

        $model = new ActiveRecordSluggableUnique();
        $model->name = $name;
        $model->save();

        $model = new ActiveRecordSluggableUnique();
        $model->sluggable->uniqueSlugGenerator = function ($baseSlug, $iteration) {return $baseSlug . '-callback';};
        $model->name = $name;
        $model->save();

        $this->assertEquals('test-name-callback', $model->slug);
    }

    /**
     * @depends testSlug
     */
    public function testUpdateUnique()
    {
        $name = 'test name';

        $model = new ActiveRecordSluggableUnique();
        $model->name = $name;
        $model->save();

        $model->save();
        $this->assertEquals('test-name', $model->slug);

        $model = ActiveRecordSluggableUnique::find()->one();
        $model->save();
        $this->assertEquals('test-name', $model->slug);

        $model->name = 'test-name';
        $model->save();
        $this->assertEquals('test-name', $model->slug);
    }

    public function testSkipOnEmpty()
    {
        $model = new SkipOnEmptySluggableActiveRecord();
        $model->name = 'test name';
        $model->save();
        $this->assertEquals('test-name', $model->slug);

        $model->name = null;
        $model->save();
        $this->assertEquals('test-name', $model->slug);

        $model->name = 'test name 2';
        $model->save();
        $this->assertEquals('test-name-2', $model->slug);
    }

    /**
     * @depends testSlug
     */
    public function testImmutableByAttribute()
    {
        $model = new ActiveRecordSluggable();
        $model->getSluggable()->immutable = true;

        $model->name = 'test name';
        $model->validate();
        $this->assertEquals('test-name', $model->slug);

        $model->name = 'another name';
        $model->validate();
        $this->assertEquals('test-name', $model->slug);
    }

    /**
     * @depends testSlug
     */
    public function testImmutableByCallback()
    {
        $model = new ActiveRecordSluggable();
        $model->getSluggable()->immutable = true;
        $model->getSluggable()->attribute = null;
        $model->getSluggable()->value = function () use ($model) {
            return $model->name;
        };

        $model->name = 'test name';
        $model->validate();
        $this->assertEquals('test name', $model->slug);

        $model->name = 'another name';
        $model->validate();
        $this->assertEquals('test name', $model->slug);
    }
}

/**
 * Test Active Record class with [[SluggableBehavior]] behavior attached.
 *
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property int $category_id
 *
 * @property SluggableBehavior $sluggable
 */
class ActiveRecordSluggable extends ActiveRecord
{
    public function behaviors()
    {
        return [
            'sluggable' => [
                'class' => SluggableBehavior::className(),
                'attribute' => 'name',
            ],
        ];
    }

    public static function tableName()
    {
        return 'test_slug';
    }

    /**
     * @return SluggableBehavior
     */
    public function getSluggable()
    {
        return $this->getBehavior('sluggable');
    }

    public function getRelated()
    {
        return $this->hasOne(ActiveRecordRelated::className(), ['id' => 'belongs_to_id']);
    }
}

class ActiveRecordRelated extends ActiveRecord
{
    public static function tableName()
    {
        return 'test_slug_related';
    }
}

class ActiveRecordSluggableUnique extends ActiveRecordSluggable
{
    public function behaviors()
    {
        return [
            'sluggable' => [
                'class' => SluggableBehavior::className(),
                'attribute' => 'name',
                'ensureUnique' => true,
            ],
        ];
    }
}

class SkipOnEmptySluggableActiveRecord extends ActiveRecordSluggable
{
    public function behaviors()
    {
        return [
            'sluggable' => [
                'class' => SluggableBehavior::className(),
                'attribute' => 'name',
                'slugAttribute' => 'slug',
                'ensureUnique' => true,
                'skipOnEmpty' => true,
            ],
        ];
    }
}
