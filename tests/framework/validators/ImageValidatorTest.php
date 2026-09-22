<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

declare(strict_types=1);

namespace yiiunit\framework\validators;

use Yii;
use yii\helpers\FileHelper;
use yii\validators\ImageValidator;
use yii\web\UploadedFile;
use yiiunit\data\validators\models\FakedValidationModel;
use yiiunit\framework\validators\stubs\ViewStub;
use yiiunit\TestCase;

use function basename;
use function filesize;

/**
 * Unit tests for {@see \yii\validators\ImageValidator} dimension and image type checks.
 *
 * @author Wilmer Arambula <terabytesoftw@gmail.com>
 * @since 2.0.56
 *
 * @group validators
 */
class ImageValidatorTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->mockApplication();
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->destroyApplication();
    }

    public function testInitSetsDefaultMessages(): void
    {
        $validator = new ImageValidator();

        $this->assertSame(
            'The file "{file}" is not an image.',
            $validator->notImage,
            'Default template must announce a file that is not an image.',
        );
        $this->assertSame(
            'The image "{file}" is too small. The width cannot be smaller than {limit, number} {limit, plural, one{pixel} other{pixels}}.',
            $validator->underWidth,
            'Default template must announce a width below the limit.',
        );
        $this->assertSame(
            'The image "{file}" is too small. The height cannot be smaller than {limit, number} {limit, plural, one{pixel} other{pixels}}.',
            $validator->underHeight,
            'Default template must announce a height below the limit.',
        );
        $this->assertSame(
            'The image "{file}" is too large. The width cannot be larger than {limit, number} {limit, plural, one{pixel} other{pixels}}.',
            $validator->overWidth,
            'Default template must announce a width above the limit.',
        );
        $this->assertSame(
            'The image "{file}" is too large. The height cannot be larger than {limit, number} {limit, plural, one{pixel} other{pixels}}.',
            $validator->overHeight,
            'Default template must announce a height above the limit.',
        );
    }

    public function testValidateValueAcceptsImage(): void
    {
        $validator = new ImageValidator();

        $file = $this->createUploadedFile(Yii::getAlias('@yiiunit/framework/validators/data/mimeType/test.jpg'));

        $error = null;

        $this->assertTrue(
            $validator->validate($file, $error),
            'A real image without limits must pass.',
        );
        $this->assertNull(
            $error,
            'No error must be produced.',
        );
    }

    public function testValidateValueRejectsNonImageFile(): void
    {
        $validator = new ImageValidator();

        $file = $this->createUploadedFile(Yii::getAlias('@yiiunit/framework/validators/data/mimeType/test.svg'));

        $error = null;

        $this->assertFalse(
            $validator->validate($file, $error),
            'A file without readable dimensions must be rejected.',
        );
        $this->assertSame(
            'The file "test.svg" is not an image.',
            $error,
            'Error must be the not-an-image message.',
        );
    }

    public function testValidateValueRejectsZeroWidthImage(): void
    {
        $validator = new ImageValidator();

        $file = $this->createUploadedFile(Yii::getAlias('@yiiunit/framework/validators/data/image/zero-width.png'));

        $error = null;

        $this->assertFalse(
            $validator->validate($file, $error),
            'A degenerate dimension must not pass the guard.',
        );
        $this->assertSame(
            'The file "zero-width.png" is not an image.',
            $error,
            'Error must be the not-an-image message.',
        );
    }

    public function testValidateValueRejectsZeroHeightImage(): void
    {
        $validator = new ImageValidator();

        $file = $this->createUploadedFile(Yii::getAlias('@yiiunit/framework/validators/data/image/zero-height.png'));

        $error = null;

        $this->assertFalse(
            $validator->validate($file, $error),
            'A degenerate dimension must not pass the guard.',
        );
        $this->assertSame(
            'The file "zero-height.png" is not an image.',
            $error,
            'Error must be the not-an-image message.',
        );
    }

    /**
     * @param array $config
     * @param bool $expectedResult
     * @param string|null $expectedError
     *
     * @dataProvider dimensionLimitsProvider
     */
    public function testValidateValueChecksDimensionLimits($config, $expectedResult, $expectedError): void
    {
        $validator = new ImageValidator($config);

        $file = $this->createUploadedFile(Yii::getAlias('@yiiunit/framework/validators/data/image/rectangle.png'));

        $error = null;

        $this->assertSame(
            $expectedResult,
            $validator->validate($file, $error),
            'Outcome must follow the limit that the 3x2 image violates.',
        );
        $this->assertSame(
            $expectedError,
            $error,
            'Error must name the violated limit.',
        );
    }

    public static function dimensionLimitsProvider(): array
    {
        return [
            'width below minWidth' => [
                ['minWidth' => 4],
                false,
                'The image "rectangle.png" is too small. The width cannot be smaller than 4 pixels.',
            ],
            'width above maxWidth' => [
                ['maxWidth' => 2],
                false,
                'The image "rectangle.png" is too large. The width cannot be larger than 2 pixels.',
            ],
            'height below minHeight' => [
                ['minHeight' => 3],
                false,
                'The image "rectangle.png" is too small. The height cannot be smaller than 3 pixels.',
            ],
            'height above maxHeight' => [
                ['maxHeight' => 1],
                false,
                'The image "rectangle.png" is too large. The height cannot be larger than 1 pixel.',
            ],
            'dimensions on the exact bounds' => [
                ['minWidth' => 3, 'maxWidth' => 3, 'minHeight' => 2, 'maxHeight' => 2],
                true,
                null,
            ],
        ];
    }

    public function testValidateAttributeAddsNotImageError(): void
    {
        $validator = new ImageValidator();

        $model = FakedValidationModel::createWithAttributes([
            'attr_image' => $this->createUploadedFile(
                Yii::getAlias('@yiiunit/framework/validators/data/mimeType/test.svg')
            ),
        ]);

        $validator->validateAttribute($model, 'attr_image');

        $this->assertTrue(
            $model->hasErrors('attr_image'),
            'Model must carry an error on the attribute.',
        );
        $this->assertSame(
            'The file "test.svg" is not an image.',
            $model->getFirstError('attr_image'),
            'Error must be the not-an-image message.',
        );
    }

    /**
     * Legacy client-side contract; not applicable to 22.0.
     */
    public function testClientValidateAttribute(): void
    {
        $validator = new ImageValidator([
            'minWidth' => 10,
            'maxWidth' => 100,
            'minHeight' => 10,
            'maxHeight' => 100,
        ]);

        $model = FakedValidationModel::createWithAttributes(['attr_image' => null]);

        $this->assertSame(
            'yii.validation.image(attribute, messages, {"message":"File upload failed.","skipOnEmpty":true,"mimeTypes":[],"wrongMimeType":"Only files with these MIME types are allowed: .","extensions":[],"wrongExtension":"Only files with these extensions are allowed: .","maxFiles":1,"tooMany":"You can upload at most 1 file.","notImage":"The file \\u0022{file}\\u0022 is not an image.","minWidth":10,"underWidth":"The image \\u0022{file}\\u0022 is too small. The width cannot be smaller than 10 pixels.","maxWidth":100,"overWidth":"The image \\u0022{file}\\u0022 is too large. The width cannot be larger than 100 pixels.","minHeight":10,"underHeight":"The image \\u0022{file}\\u0022 is too small. The height cannot be smaller than 10 pixels.","maxHeight":100,"overHeight":"The image \\u0022{file}\\u0022 is too large. The height cannot be larger than 100 pixels."}, deferred);',
            $validator->clientValidateAttribute($model, 'attr_image', new ViewStub()),
            'Generated call must match the legacy JavaScript contract.',
        );
    }

    /**
     * Legacy client-side contract; not applicable to 22.0.
     */
    public function testGetClientOptions(): void
    {
        $validator = new ImageValidator([
            'minWidth' => 10,
            'maxWidth' => 100,
            'minHeight' => 10,
            'maxHeight' => 100,
        ]);

        $model = FakedValidationModel::createWithAttributes(['attr_image' => null]);

        $this->assertSame(
            [
                'message' => 'File upload failed.',
                'skipOnEmpty' => true,
                'mimeTypes' => [],
                'wrongMimeType' => 'Only files with these MIME types are allowed: .',
                'extensions' => [],
                'wrongExtension' => 'Only files with these extensions are allowed: .',
                'maxFiles' => 1,
                'tooMany' => 'You can upload at most 1 file.',
                'notImage' => 'The file "{file}" is not an image.',
                'minWidth' => 10,
                'underWidth' => 'The image "{file}" is too small. The width cannot be smaller than 10 pixels.',
                'maxWidth' => 100,
                'overWidth' => 'The image "{file}" is too large. The width cannot be larger than 100 pixels.',
                'minHeight' => 10,
                'underHeight' => 'The image "{file}" is too small. The height cannot be smaller than 10 pixels.',
                'maxHeight' => 100,
                'overHeight' => 'The image "{file}" is too large. The height cannot be larger than 100 pixels.',
            ],
            $validator->getClientOptions($model, 'attr_image'),
            'Client options must match the legacy JavaScript contract.',
        );
    }

    private function createUploadedFile(string $path): UploadedFile
    {
        return new UploadedFile([
            'name' => basename($path),
            'tempName' => $path,
            'type' => (string) FileHelper::getMimeType($path),
            'size' => filesize($path),
            'error' => UPLOAD_ERR_OK,
        ]);
    }
}
