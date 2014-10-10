<?php
namespace yiiunit\framework\validators;

use yii\validators\ColorValidator;
use yiiunit\data\validators\models\FakedValidationModel;
use yiiunit\TestCase;

/**
 * @group validators
 */
class ColorValidatorTest extends TestCase
{
    protected function setUp()
    {
        parent::setUp();
        $this->mockApplication();
    }

    public function testValidateValue()
    {
        $validator = new ColorValidator();
        // hex 3 chars
        $this->assertTrue($validator->validate('#fF0'));
        $this->assertFalse($validator->validate('#12j'));
        // hex 6 chars
        $this->assertTrue($validator->validate('#abCDef'));
        $this->assertFalse($validator->validate('#ggasd1'));
        // rgb
        $this->assertTrue($validator->validate('rgB(0, 123, 255)'));
        $this->assertFalse($validator->validate('rgb(-1, 123, 256)'));
        // rgba
        $this->assertTrue($validator->validate('rgBa(0,15, 245,.4)'));
        $this->assertTrue($validator->validate('rgba(0,11, 111,     0)'));
        $this->assertTrue($validator->validate('rgBa(0, 234, 235, .990)'));
        $this->assertTrue($validator->validate('rgBa(0, 234, 235, .990)'));
        $this->assertFalse($validator->validate('rgba(0, 91, 255, 1.1)'));
        $this->assertFalse($validator->validate('rgba(0, 91,256,1)'));
        // hsl
        $this->assertTrue($validator->validate('hsl(120,100%,50%)'));
        $this->assertTrue($validator->validate('hsl(359,20%,0%)'));
        $this->assertFalse($validator->validate('hsl(369,20%,0%)'));
        // hsla
        $this->assertTrue($validator->validate('hsla(120,100%,50%,0.44)'));
        $this->assertTrue($validator->validate('hsla(359,20%,0%,   .91)'));
        $this->assertFalse($validator->validate('hsla(349,91%,0%, 3)'));
        $this->assertFalse($validator->validate('hsla(369,91%,0%, 1)'));
        // names
        $this->assertTrue($validator->validate('black'));
        $this->assertTrue($validator->validate('lightgreen'));
        $this->assertTrue($validator->validate('whitesmoke'));
        $this->assertTrue($validator->validate('DeepSkyBlue'));
        $this->assertFalse($validator->validate('darksnow'));
        $this->assertFalse($validator->validate('lightBlack'));
//        // common
        $this->assertFalse($validator->validate(''));
        $this->assertFalse($validator->validate('notVeryLongString'));
        $this->assertFalse($validator->validate('-01923='));
    }

    public function testValidateValueByMethods()
    {
        $validator = new ColorValidator();
        $validator->methods = ['hex', 'rgb', 'names'];

        // rgb
        $this->assertTrue($validator->validate('rgB(0, 123, 255)'));
        // hex 6 chars
        $this->assertTrue($validator->validate('#abCDef'));
        // hsla
        $this->assertFalse($validator->validate('hsla(120,100%,50%,0.44)'));
        // hsl
        $this->assertFalse($validator->validate('hsl(120,100%,50%)'));
        // rgba
        $this->assertFalse($validator->validate('rgBa(0,15, 245,.4)'));
        // names
        $this->assertTrue($validator->validate('black'));
    }

    public function testValidateAttribute()
    {
        $validator = new ColorValidator();
        $model = new FakedValidationModel();

        $model->attr_color = '#ddd333';
        $validator->validateAttribute($model, 'attr_color');
        $this->assertFalse($model->hasErrors('attr_color'));

        $model->attr_color = '#09134g';
        $validator->validateAttribute($model, 'attr_color');
        $this->assertTrue($model->hasErrors('attr_color'));
    }

    public function testNotSupportedMethods()
    {
        $this->setExpectedException('yii\base\InvalidConfigException');

        $validator = new ColorValidator();
        $validator->methods = ['test', 'rgb'];
        $validator->init();
    }

}
