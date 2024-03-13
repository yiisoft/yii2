<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\validators;

use Yii;
use yii\helpers\FileHelper;
use yii\validators\FileValidator;
use yii\web\UploadedFile;
use yiiunit\data\validators\models\FakedValidationModel;
use yiiunit\TestCase;

/**
 * @group validators
 */
class FileValidatorTest extends TestCase
{
    protected function setUp()
    {
        parent::setUp();
        $this->mockApplication();
    }

    public function testAssureMessagesSetOnInit()
    {
        $val = new FileValidator();
        foreach (['message', 'uploadRequired', 'tooMany', 'wrongExtension', 'tooBig', 'tooSmall', 'wrongMimeType'] as $attr) {
            $this->assertInternalType('string', $val->$attr);
        }
    }

    public function testTypeSplitOnInit()
    {
        $val = new FileValidator(['extensions' => 'jpeg, jpg, gif']);
        $this->assertEquals(['jpeg', 'jpg', 'gif'], $val->extensions);

        $val = new FileValidator(['extensions' => 'jpeg']);
        $this->assertEquals(['jpeg'], $val->extensions);

        $val = new FileValidator(['extensions' => '']);
        $this->assertEquals([], $val->extensions);

        $val = new FileValidator(['extensions' => []]);
        $this->assertEquals([], $val->extensions);

        $val = new FileValidator();
        $this->assertEquals([], $val->extensions);

        $val = new FileValidator(['extensions' => ['jpeg', 'exe']]);
        $this->assertEquals(['jpeg', 'exe'], $val->extensions);
    }

    public function testMimeTypeSplitOnInit()
    {
        $val = new FileValidator(['mimeTypes' => 'text/plain, image/png']);
        $this->assertEquals(['text/plain', 'image/png'], $val->mimeTypes);

        $val = new FileValidator(['mimeTypes' => 'text/plain']);
        $this->assertEquals(['text/plain'], $val->mimeTypes);

        $val = new FileValidator(['mimeTypes' => '']);
        $this->assertEquals([], $val->mimeTypes);

        $val = new FileValidator(['mimeTypes' => []]);
        $this->assertEquals([], $val->mimeTypes);

        $val = new FileValidator();
        $this->assertEquals([], $val->mimeTypes);

        $val = new FileValidator(['mimeTypes' => ['text/plain', 'image/png']]);
        $this->assertEquals(['text/plain', 'image/png'], $val->mimeTypes);
    }

    public function testGetSizeLimit()
    {
        $size = min($this->sizeToBytes(ini_get('upload_max_filesize')), $this->sizeToBytes(ini_get('post_max_size')));
        $val = new FileValidator();
        $this->assertEquals($size, $val->getSizeLimit());
        $val->maxSize = $size + 1; // set and test if value is overridden
        $this->assertEquals($size, $val->getSizeLimit());
        $val->maxSize = abs($size - 1);
        $this->assertEquals($size - 1, $val->getSizeLimit());
        $_POST['MAX_FILE_SIZE'] = $size + 1;
        $this->assertEquals($size - 1, $val->getSizeLimit());
        $_POST['MAX_FILE_SIZE'] = abs($size - 2);
        $this->assertSame($_POST['MAX_FILE_SIZE'], $val->getSizeLimit());
    }

    protected function sizeToBytes($sizeStr)
    {
        switch (substr($sizeStr, -1)) {
            case 'M':
            case 'm':
                return (int) $sizeStr * 1048576;
            case 'K':
            case 'k':
                return (int) $sizeStr * 1024;
            case 'G':
            case 'g':
                return (int) $sizeStr * 1073741824;
            default:
                return (int) $sizeStr;
        }
    }

    public function testValidateAttributeMultiple()
    {
        $val = new FileValidator([
            'maxFiles' => 2,
        ]);
        $m = FakedValidationModel::createWithAttributes(['attr_files' => 'path']);
        $val->validateAttribute($m, 'attr_files');
        $this->assertTrue($m->hasErrors('attr_files'));
        $m = FakedValidationModel::createWithAttributes(['attr_files' => []]);
        $val->validateAttribute($m, 'attr_files');
        $this->assertTrue($m->hasErrors('attr_files'));
        $this->assertSame($val->uploadRequired, current($m->getErrors('attr_files')));
        $m = FakedValidationModel::createWithAttributes(
            [
                'attr_files' => $this->createTestFiles(
                    [
                        [
                            'name' => 'test_up_1.txt',
                            'size' => 1024,
                        ],
                        [
                            'error' => UPLOAD_ERR_NO_FILE,
                        ],
                    ]
                ),
            ]
        );
        $val->validateAttribute($m, 'attr_files');
        $this->assertFalse($m->hasErrors('attr_files'));
        $m = FakedValidationModel::createWithAttributes([
            'attr_files' => $this->createTestFiles([
                [''], [''], [''],
            ]),
        ]);
        $val->validateAttribute($m, 'attr_files');
        $this->assertTrue($m->hasErrors());
        $this->assertNotFalse(stripos(current($m->getErrors('attr_files')), 'you can upload at most'));

        $files = [
            'file_1' => [
                'name' => 'test_up_1.txt',
                'size' => 1024,
            ],
            'file_2' => [
                'name' => 'test_up_2.txt',
                'size' => 1024,
            ]
        ];
        $m = FakedValidationModel::createWithAttributes(
            [
                'attr_files' => $this->createTestFiles(
                    $files
                ),
            ]
        );
        $val->validateAttribute($m, 'attr_files');
        $this->assertFalse($m->hasErrors());
        $this->assertEquals(array_keys($m->attr_files), array_keys($files));

        $val->maxFiles = 0;
        $m->clearErrors();
        $val->validateAttribute($m, 'attr_files');
        $this->assertFalse($m->hasErrors());

        $m = FakedValidationModel::createWithAttributes(
            [
                'attr_images' => $this->createTestFiles(
                    [
                        [
                            'name' => 'image.png',
                            'size' => 1024,
                            'type' => 'image/png',
                        ],
                        [
                            'name' => 'image.png',
                            'size' => 1024,
                            'type' => 'image/png',
                        ],
                        [
                            'name' => 'text.txt',
                            'size' => 1024,
                        ],
                    ]
                ),
            ]
        );
        $m->setScenario('validateMultipleFiles');
        $this->assertFalse($m->validate());
        $this->assertNotFalse(stripos(current($m->getErrors('attr_images')),
            'Only files with these extensions are allowed'));

        $m = FakedValidationModel::createWithAttributes(
            [
                'attr_images' => $this->createTestFiles(
                    [
                        [
                            'name' => 'image.png',
                            'size' => 1024,
                            'type' => 'image/png',
                        ],
                        [
                            'name' => 'image.png',
                            'size' => 1024,
                            'type' => 'image/png',
                        ],
                    ]
                ),
            ]
        );
        $m->setScenario('validateMultipleFiles');
        $this->assertTrue($m->validate());

        $m = FakedValidationModel::createWithAttributes(
            [
                'attr_image' => $this->createTestFiles(
                    [
                        [
                            'name' => 'text.txt',
                            'size' => 1024,
                        ],
                    ]
                ),
            ]
        );
        $m->setScenario('validateFile');
        $this->assertFalse($m->validate());
    }

    public function testValidateAttribute_minFilesGreaterThanOneMaxFilesUnlimited_notError()
    {
        $validator = new FileValidator(['minFiles' => 2, 'maxFiles' => 0]);
        $model = FakedValidationModel::createWithAttributes(
            [
                'attr_images' => $this->createTestFiles(
                    [
                        [
                            'name' => 'image.png',
                            'size' => 1024,
                            'type' => 'image/png',
                        ],
                        [
                            'name' => 'image.png',
                            'size' => 1024,
                            'type' => 'image/png',
                        ],
                    ]
                )
            ]
        );

        $validator->validateAttribute($model, 'attr_images');

        $this->assertFalse($model->hasErrors('attr_images'));
    }

    public function testValidateAttribute_minFilesTwoMaxFilesFour_notError()
    {
        $validator = new FileValidator(['minFiles' => 2, 'maxFiles' => 4]);
        $model = FakedValidationModel::createWithAttributes(
            [
                'attr_images' => $this->createTestFiles(
                    [
                        [
                            'name' => 'image.png',
                            'size' => 1024,
                            'type' => 'image/png',
                        ],
                        [
                            'name' => 'image.png',
                            'size' => 1024,
                            'type' => 'image/png',
                        ],
                    ]
                )
            ]
        );

        $validator->validateAttribute($model, 'attr_images');

        $this->assertFalse($model->hasErrors('attr_images'));
    }

    public function testValidateAttribute_minFilesTwoMaxFilesUnlimited_hasError()
    {
        $validator = new FileValidator(['minFiles' => 2, 'maxFiles' => 0]);
        $model = FakedValidationModel::createWithAttributes(
            [
                'attr_images' => $this->createTestFiles(
                    [
                        [
                            'name' => 'image.png',
                            'size' => 1024,
                            'type' => 'image/png',
                        ],
                        [
                            'error' => UPLOAD_ERR_NO_FILE,
                        ],
                    ]
                )
            ]
        );

        $validator->validateAttribute($model, 'attr_images');

        $this->assertTrue($model->hasErrors('attr_images'));
    }

    /**
     * @param  array          $params
     * @return UploadedFile[]
     */
    protected function createTestFiles($params = [])
    {
        $rndString = function ($len = 10) {
            $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
            $randomString = '';
            for ($i = 0; $i < $len; $i++) {
                $randomString .= $characters[random_int(0, strlen($characters) - 1)];
            }

            return $randomString;
        };
        $files = [];
        foreach ($params as $key => $param) {
            if (empty($param) && count($params) != 1) {
                $files[$key] = ['no instance of UploadedFile'];
                continue;
            }
            $name = isset($param['name']) ? $param['name'] : $rndString();
            $tempName = \Yii::getAlias('@yiiunit/runtime/validators/file/tmp/') . $name;
            if (is_readable($tempName)) {
                $size = filesize($tempName);
            } else {
                $size = isset($param['size']) ? $param['size'] : random_int(
                    1,
                    $this->sizeToBytes(ini_get('upload_max_filesize'))
                );
            }
            $type = isset($param['type']) ? $param['type'] : 'text/plain';
            $error = isset($param['error']) ? $param['error'] : UPLOAD_ERR_OK;
            if (count($params) == 1) {
                $error = empty($param) ? UPLOAD_ERR_NO_FILE : $error;

                return new UploadedFile([
                    'name' => $name,
                    'tempName' => $tempName,
                    'type' => $type,
                    'size' => $size,
                    'error' => $error,
                ]);
            }
            $files[$key] = new UploadedFile([
                'name' => $name,
                'tempName' => $tempName,
                'type' => $type,
                'size' => $size,
                'error' => $error,
            ]);
        }

        return $files;
    }

    /**
     * @param $fileName
     * @return UploadedFile
     */
    protected function getRealTestFile($fileName)
    {
        $filePath = \Yii::getAlias('@yiiunit/framework/validators/data/mimeType/') . $fileName;

        return new UploadedFile([
            'name' => $fileName,
            'tempName' => $filePath,
            'type' => FileHelper::getMimeType($filePath),
            'size' => filesize($filePath),
            'error' => UPLOAD_ERR_OK,
        ]);
    }

    public function testValidateAttribute()
    {
        // single File
        $val = new FileValidator();
        $m = $this->createModelForAttributeTest();
        $val->validateAttribute($m, 'attr_files');
        $this->assertFalse($m->hasErrors());
        $val->validateAttribute($m, 'attr_files_empty');
        $this->assertTrue($m->hasErrors('attr_files_empty'));
        $this->assertSame($val->uploadRequired, current($m->getErrors('attr_files_empty')));

        // single File with skipOnEmpty = false
        $val = new FileValidator(['skipOnEmpty' => false]);
        $m = $this->createModelForAttributeTest();
        $val->validateAttribute($m, 'attr_files');
        $this->assertFalse($m->hasErrors());
        $val->validateAttribute($m, 'attr_files_empty');
        $this->assertTrue($m->hasErrors('attr_files_empty'));
        $this->assertSame($val->uploadRequired, current($m->getErrors('attr_files_empty')));
        $m = $this->createModelForAttributeTest();

        // too big
        $val = new FileValidator(['maxSize' => 128]);
        $val->validateAttribute($m, 'attr_files');
        $this->assertTrue($m->hasErrors('attr_files'));
        $this->assertNotFalse(stripos(current($m->getErrors('attr_files')), 'too big'));
        // to Small
        $m = $this->createModelForAttributeTest();
        $val = new FileValidator(['minSize' => 2048]);
        $val->validateAttribute($m, 'attr_files');
        $this->assertTrue($m->hasErrors('attr_files'));
        $this->assertNotFalse(stripos(current($m->getErrors('attr_files')), 'too small'));
        // UPLOAD_ERR_INI_SIZE/UPLOAD_ERR_FORM_SIZE
        $m = $this->createModelForAttributeTest();
        $val = new FileValidator();
        $val->validateAttribute($m, 'attr_err_ini');
        $this->assertTrue($m->hasErrors('attr_err_ini'));
        $this->assertNotFalse(stripos(current($m->getErrors('attr_err_ini')), 'too big'));
        // UPLOAD_ERR_PARTIAL
        $m = $this->createModelForAttributeTest();
        $val = new FileValidator();
        $val->validateAttribute($m, 'attr_err_part');
        $this->assertTrue($m->hasErrors('attr_err_part'));
        $this->assertSame(Yii::t('yii', 'File upload failed.'), current($m->getErrors('attr_err_part')));
    }

    public function testValidateAttributeType()
    {
        $val = new FileValidator([
            'extensions' => 'jpeg, jpg',
            'checkExtensionByMimeType' => false,
        ]);
        $m = FakedValidationModel::createWithAttributes(
            [
                'attr_jpg' => $this->createTestFiles([['name' => 'one.jpeg']]),
                'attr_exe' => $this->createTestFiles([['name' => 'bad.exe']]),
            ]
        );
        $val->validateAttribute($m, 'attr_jpg');
        $this->assertFalse($m->hasErrors('attr_jpg'));
        $val->validateAttribute($m, 'attr_exe');
        $this->assertTrue($m->hasErrors('attr_exe'));
        $this->assertNotFalse(stripos(current($m->getErrors('attr_exe')), 'Only files with these extensions '));
    }

    public function testValidateEmptyExtension()
    {
        $val = new FileValidator([
            'extensions' => ['txt', ''],
            'checkExtensionByMimeType' => false,
        ]);
        $m = FakedValidationModel::createWithAttributes(
            [
                'attr_txt' => $this->createTestFiles([['name' => 'one.txt']]),
                'attr_empty' => $this->createTestFiles([['name' => 'bad.']]),
                'attr_empty2' => $this->createTestFiles([['name' => 'bad']]),
            ]
        );
        $val->validateAttribute($m, 'attr_txt');
        $this->assertFalse($m->hasErrors('attr_txt'));
        $val->validateAttribute($m, 'attr_empty');
        $this->assertFalse($m->hasErrors('attr_empty'));
        $val->validateAttribute($m, 'attr_empty2');
        $this->assertFalse($m->hasErrors('attr_empty2'));
    }

    public function testValidateAttributeDoubleType()
    {
        $val = new FileValidator([
            'extensions' => 'tar.gz, tar.xz',
            'checkExtensionByMimeType' => false,
        ]);

        $m = FakedValidationModel::createWithAttributes(
            [
                'attr_tar' => $this->createTestFiles([['name' => 'one.tar.gz']]),
                'attr_bar' => $this->createTestFiles([['name' => 'bad.bar.xz']]),
                'attr_badtar' => $this->createTestFiles([['name' => 'badtar.xz']]),
            ]
        );
        $val->validateAttribute($m, 'attr_tar');
        $this->assertFalse($m->hasErrors('attr_tar'));

        $val->validateAttribute($m, 'attr_bar');
        $this->assertTrue($m->hasErrors('attr_bar'));
        $this->assertNotFalse(stripos(current($m->getErrors('attr_bar')), 'Only files with these extensions '));

        $val->validateAttribute($m, 'attr_badtar');
        $this->assertTrue($m->hasErrors('attr_badtar'));
        $this->assertNotFalse(stripos(current($m->getErrors('attr_badtar')), 'Only files with these extensions '));
    }

    public function testIssue11012()
    {
        $baseName = '飛兒樂團光茫';
        /** @var UploadedFile $file */
        $file = $this->createTestFiles([
            ['name' => $baseName . '.txt'],
        ]);
        $this->assertEquals($baseName, $file->getBaseName());
    }

    /**
     * @param string $fileName
     * @param string $mask
     * @dataProvider validMimeTypes
     */
    public function testValidateMimeTypeMaskValid($fileName, $mask)
    {
        $validator = new FileValidator(['mimeTypes' => $mask]);
        $file = $this->getRealTestFile($fileName);
        $this->assertTrue($validator->validate($file));
    }

    /**
     * @param string $fileName
     * @param string $mask
     * @dataProvider invalidMimeTypes
     */
    public function testValidateMimeTypeMaskInvalid($fileName, $mask)
    {
        $validator = new FileValidator(['mimeTypes' => $mask]);
        $file = $this->getRealTestFile($fileName);
        $this->assertFalse($validator->validate($file));
    }

    public function validMimeTypes()
    {
        $validMimeTypes = array_filter([
            ['test.svg', 'image/*', 'svg'],
            ['test.jpg', 'image/*', 'jpg'],
            ['test.png', 'image/*', 'png'],
            ['test.png', 'IMAGE/*', 'png'],
            ['test.txt', 'text/*', 'txt'],
            ['test.xml', '*/xml', 'xml'],
            ['test.odt', 'application/vnd*', 'odt'],
            ['test.tar.xz', 'application/x-xz', 'tar.xz'],
        ]);

        # fix for bundled libmagic bug, see also https://github.com/yiisoft/yii2/issues/19925
        if ((PHP_VERSION_ID >= 80100 && PHP_VERSION_ID < 80122) || (PHP_VERSION_ID >= 80200 && PHP_VERSION_ID < 80209)) {
            $v81_zx = ['test.tar.xz', 'application/octet-stream', 'tar.xz'];
            array_pop($validMimeTypes);
            $validMimeTypes[] = $v81_zx;
        }

        return $validMimeTypes;
    }

    public function invalidMimeTypes()
    {
        return [
            ['test.txt', 'image/*', 'png, jpg'],
            ['test.odt', 'text/*', 'txt'],
            ['test.xml', '*/svg+xml', 'svg'],
            ['test.png', 'image/x-iso9660-image', 'bmp'],
            ['test.svg', 'application/*', 'jpg'],
        ];
    }

    /**
     * @param string $fileName
     * @param mixed $_
     * @param string|array $allowedExtensions
     * @dataProvider validMimeTypes
     */
    public function testValidateFileByExtensionUsingMimeType($fileName, $_, $allowedExtensions)
    {
        $validator = new FileValidator(['extensions' => (array) $allowedExtensions]);
        $file = $this->getRealTestFile($fileName);
        $detectedMimeType = FileHelper::getMimeType($file->tempName, null, false);
        $this->assertTrue($validator->validate($file), "Mime type detected was \"$detectedMimeType\". Consider adding it to MimeTypeController::\$aliases.");
    }

    /**
     * @param string $fileName
     * @param mixed $_
     * @param string|array $allowedExtensions
     * @dataProvider invalidMimeTypes
     */
    public function testValidateFileByExtensionUsingMimeTypeInvalid($fileName, $_, $allowedExtensions)
    {
        $validator = new FileValidator(['extensions' => (array) $allowedExtensions]);
        $file = $this->getRealTestFile($fileName);
        $this->assertFalse($validator->validate($file));
    }

    protected function createModelForAttributeTest()
    {
        return FakedValidationModel::createWithAttributes(
            [
                'attr_files' => $this->createTestFiles([
                    ['name' => 'abc.jpg', 'size' => 1024, 'type' => 'image/jpeg'],
                ]),
                'attr_files_empty' => $this->createTestFiles([[]]),
                'attr_err_ini' => $this->createTestFiles([['error' => UPLOAD_ERR_INI_SIZE]]),
                'attr_err_part' => $this->createTestFiles([['error' => UPLOAD_ERR_PARTIAL]]),
                'attr_err_tmp' => $this->createTestFiles([['error' => UPLOAD_ERR_NO_TMP_DIR]]),
                'attr_err_write' => $this->createTestFiles([['error' => UPLOAD_ERR_CANT_WRITE]]),
                'attr_err_ext' => $this->createTestFiles([['error' => UPLOAD_ERR_EXTENSION]]),
            ]
        );
    }

    public function testValidateAttributeErrPartial()
    {
        $m = $this->createModelForAttributeTest();
        $val = new FileValidator();
        $val->validateAttribute($m, 'attr_err_part');
        $this->assertTrue($m->hasErrors('attr_err_part'));
        $this->assertSame(Yii::t('yii', 'File upload failed.'), current($m->getErrors('attr_err_part')));
    }

    public function testValidateAttributeErrCantWrite()
    {
        $m = $this->createModelForAttributeTest();
        $val = new FileValidator();
        $val->validateAttribute($m, 'attr_err_write');
        $this->assertTrue($m->hasErrors('attr_err_write'));
        $this->assertSame(Yii::t('yii', 'File upload failed.'), current($m->getErrors('attr_err_write')));
    }

    public function testValidateAttributeErrExtension()
    {
        $m = $this->createModelForAttributeTest();
        $val = new FileValidator();
        $val->validateAttribute($m, 'attr_err_ext');
        $this->assertTrue($m->hasErrors('attr_err_ext'));
        $this->assertSame(Yii::t('yii', 'File upload failed.'), current($m->getErrors('attr_err_ext')));
    }

    public function testValidateAttributeErrNoTmpDir()
    {
        $m = $this->createModelForAttributeTest();
        $val = new FileValidator();
        $val->validateAttribute($m, 'attr_err_tmp');
        $this->assertTrue($m->hasErrors('attr_err_tmp'));
        $this->assertSame(Yii::t('yii', 'File upload failed.'), current($m->getErrors('attr_err_tmp')));
    }

    /**
     * @param string $mask
     * @param string $fileMimeType
     * @param bool   $expected
     * @dataProvider mimeTypeCaseInsensitive
     */
    public function testValidateMimeTypeCaseInsensitive($mask, $fileMimeType, $expected) {
        $validator = $this->getMock('\yii\validators\FileValidator', ['getMimeTypeByFile']);
        $validator->method('getMimeTypeByFile')->willReturn($fileMimeType);
        $validator->mimeTypes = [$mask];

        $file = $this->getRealTestFile('test.txt');
        $this->assertEquals($expected, $validator->validate($file), sprintf('Mime type validate fail: "%s" / "%s"', $mask, $fileMimeType));
    }

    public function mimeTypeCaseInsensitive() {
        return [
            ['Image/*', 'image/jp2', true],
            ['image/*', 'Image/jp2', true],
            ['application/vnd.ms-word.document.macroEnabled.12', 'application/vnd.ms-word.document.macroenabled.12', true],
            ['image/jxra', 'image/jxrA', true],
        ];
    }
}
