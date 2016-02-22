<?php
/**
 * @author Bennet Klarhoelter <boehsermoe@me.com>
 */
namespace yiiunit\framework\widgets;
use yii\base\Arrayable;
use yii\base\ArrayableTrait;
use yii\base\DynamicModel;
use yii\base\Object;
use yii\widgets\ActiveForm;
use yii\widgets\DetailView;

/**
 * @group widgets
 */
class DetailViewTest extends \yiiunit\TestCase
{
    /** @var DetailView */
    public $detailView;

    protected function setUp()
    {
        parent::setUp();

        $this->mockWebApplication();
    }

    public function testRelationAttribute()
    {
        $model = new ObjectMock();
        $model->id = 'model';
        $model->related = new ObjectMock();
        $model->related->id = 'related';

        $this->detailView = new PublicDetailView([
            'model' => $model,
            'template' => '{label}:{value}',
            'attributes' => [
                'id',
                'related.id',
            ],
        ]);

        $this->assertEquals('Id:model', $this->detailView->renderAttribute($this->detailView->attributes[0], 0));
        $this->assertEquals('Related Id:related', $this->detailView->renderAttribute($this->detailView->attributes[1], 1));

        // test null relation
        $model->related = null;

        $this->detailView = new PublicDetailView([
            'model' => $model,
            'template' => '{label}:{value}',
            'attributes' => [
                'id',
                'related.id',
            ],
        ]);

        $this->assertEquals('Id:model', $this->detailView->renderAttribute($this->detailView->attributes[0], 0));
        $this->assertEquals('Related Id:<span class="not-set">(not set)</span>', $this->detailView->renderAttribute($this->detailView->attributes[1], 1));
    }

    public function testArrayableModel()
    {
        $expectedValue = [
            [
                'attribute' => 'id',
                'format' => 'text',
                'label' => 'Id',
                'value' => 1
            ],
            [
                'attribute' => 'text',
                'format' => 'text',
                'label' => 'Text',
                'value' => 'I`m arrayable'
            ],
        ];

        $model = new ArrayableMock();
        $model->id = 1;
        $model->text = 'I`m arrayable';

        $this->detailView = new DetailView([
            'model' => $model,
        ]);

        $this->assertEquals($expectedValue, $this->detailView->attributes);
    }

    public function testObjectModel()
    {
        $expectedValue = [
            [
                'attribute' => 'id',
                'format' => 'text',
                'label' => 'Id',
                'value' => 1
            ],
            [
                'attribute' => 'text',
                'format' => 'text',
                'label' => 'Text',
                'value' => 'I`m an object'
            ],
        ];

        $model = new ObjectMock();
        $model->id = 1;
        $model->text = 'I`m an object';

        $this->detailView = new DetailView([
            'model' => $model,
        ]);

        $this->assertEquals($expectedValue, $this->detailView->attributes);
    }

    public function testArrayModel()
    {
        $expectedValue = [
            [
                'attribute' => 'id',
                'format' => 'text',
                'label' => 'Id',
                'value' => 1
            ],
            [
                'attribute' => 'text',
                'format' => 'text',
                'label' => 'Text',
                'value' => 'I`m an array'
            ],
        ];

        $model = [
            'id' => 1,
            'text' => 'I`m an array'
        ];

        $this->detailView = new DetailView([
            'model' => $model,
        ]);

        $this->assertEquals($expectedValue, $this->detailView->attributes);
    }
}

/**
 * Helper Class
 */
class ArrayableMock implements Arrayable
{
    use ArrayableTrait;

    public $id;

    public $text;
}

/**
 * Helper Class
 */
class ObjectMock extends Object
{
    public $id;
    public $text;

    private $_related;

    public function getRelated()
    {
        return $this->_related;
    }

    public function setRelated($related)
    {
        $this->_related = $related;
    }
}

class PublicDetailView extends DetailView
{
    public function renderAttribute($attribute, $index)
    {
        return parent::renderAttribute($attribute, $index);
    }
}