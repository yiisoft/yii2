<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

declare(strict_types=1);

namespace yiiunit\framework\validators;

use Yii;
use yii\helpers\Json;
use yii\validators\ImageValidator;
use yii\web\UploadedFile;
use yii\web\View;
use yiiunit\data\validators\models\FakedValidationModel;
use yiiunit\TestCase;

/**
 * @group validators
 */
class ImageValidatorTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->mockApplication();
    }

    public function testInit(): void
    {
        $val = new ImageValidator();
        $this->assertIsString($val->notImage);
        $this->assertIsString($val->underWidth);
        $this->assertIsString($val->underHeight);
        $this->assertIsString($val->overWidth);
        $this->assertIsString($val->overHeight);
    }

    public function testValidateValue(): void
    {
        $val = new ImageValidator();

        $image = $this->createTestFile('test.jpg');
        $this->assertTrue($val->validate($image));

        $image = $this->createTestFile('test.png');
        $this->assertTrue($val->validate($image));

        $notImage = $this->createTestFile('test.txt');
        $error = '';
        $this->assertFalse($val->validate($notImage, $error));
        $this->assertStringContainsString('is not an image', $error);
    }

    public function testValidateImageDimensions(): void
    {
        $image = $this->createTestFile('test.jpg');

        $val = new ImageValidator(['minWidth' => 2]);
        $error = '';
        $this->assertFalse($val->validate($image, $error));
        $this->assertStringContainsString('width cannot be smaller than 2', $error);

        $val = new ImageValidator(['minHeight' => 2]);
        $error = '';
        $this->assertFalse($val->validate($image, $error));
        $this->assertStringContainsString('height cannot be smaller than 2', $error);

        $val = new ImageValidator(['maxWidth' => 0]);
        $error = '';
        $this->assertFalse($val->validate($image, $error));
        $this->assertStringContainsString('width cannot be larger than 0', $error);

        $val = new ImageValidator(['maxHeight' => 0]);
        $error = '';
        $this->assertFalse($val->validate($image, $error));
        $this->assertStringContainsString('height cannot be larger than 0', $error);

        $val = new ImageValidator(['minWidth' => 1, 'minHeight' => 1, 'maxWidth' => 1, 'maxHeight' => 1]);
        $this->assertTrue($val->validate($image));
    }

    public function testClientValidateAttribute(): void
    {
        $val = new ImageValidator([
            'minWidth' => 10,
            'maxWidth' => 100,
            'minHeight' => 10,
            'maxHeight' => 100,
        ]);
        $model = new FakedValidationModel();
        $view = new ImageValidatorViewStub();

        $js = $val->clientValidateAttribute($model, 'attr', $view);
        $this->assertStringContainsString('yii.validation.image', $js);
        $this->assertStringContainsString('"minWidth":10', $js);
        $this->assertStringContainsString('"maxWidth":100', $js);
        $this->assertStringContainsString('"minHeight":10', $js);
        $this->assertStringContainsString('"maxHeight":100', $js);
    }

    public function testGetClientOptions(): void
    {
        $val = new ImageValidator([
            'minWidth' => 10,
            'maxWidth' => 100,
            'minHeight' => 10,
            'maxHeight' => 100,
        ]);
        $model = new FakedValidationModel();
        $options = $val->getClientOptions($model, 'attr');

        $this->assertEquals(10, $options['minWidth']);
        $this->assertEquals(100, $options['maxWidth']);
        $this->assertEquals(10, $options['minHeight']);
        $this->assertEquals(100, $options['maxHeight']);
        $this->assertArrayHasKey('notImage', $options);
        $this->assertArrayHasKey('underWidth', $options);
        $this->assertArrayHasKey('overWidth', $options);
        $this->assertArrayHasKey('underHeight', $options);
        $this->assertArrayHasKey('overHeight', $options);
    }

    public function testValidateImageZeroDimensions(): void
    {
        $validator = new ImageValidator();
        $file = new UploadedFile([
            'tempName' => Yii::getAlias('@yiiunit/framework/validators/data/mimeType/test.jpg'),
            'name' => 'test.jpg'
        ]);

        $this->assertNull($this->invokeMethod($validator, 'validateImage', [$file]));
    }

    protected function createTestFile($fileName)
    {
        $filePath = Yii::getAlias('@yiiunit/framework/validators/data/mimeType/') . $fileName;
        return new UploadedFile([
            'name' => $fileName,
            'tempName' => $filePath,
            'type' => 'image/jpeg',
            'size' => filesize($filePath),
            'error' => UPLOAD_ERR_OK,
        ]);
    }
}

class ImageValidatorViewStub extends View
{
    public function registerAssetBundle($name, $position = null) {}
}
