<?php

namespace yiiunit\framework\validators;


use yii\validators\FileValidator;
use yii\web\UploadedFile;
use Yii;
use yiiunit\data\validators\models\FakedValidationModel;
use yiiunit\TestCase;

class FileValidatorTest extends TestCase
{
	public function setUp()
	{
		$this->mockApplication();
	}

	public function testAssureMessagesSetOnInit()
	{
		$val = new FileValidator();
		foreach (array('message', 'uploadRequired', 'tooMany', 'wrongType', 'tooBig', 'tooSmall') as $attr) {
			$this->assertTrue(is_string($val->$attr));
		}
	}

	public function testTypeSplitOnInit()
	{
		$val = new FileValidator(array('types' => 'jpeg, jpg, gif'));
		$this->assertEquals(array('jpeg', 'jpg', 'gif'), $val->types);
		$val = new FileValidator(array('types' => 'jpeg'));
		$this->assertEquals(array('jpeg'), $val->types);
		$val = new FileValidator(array('types' => ''));
		$this->assertEquals(array(), $val->types);
		$val = new FileValidator(array('types' => array()));
		$this->assertEquals(array(), $val->types);
		$val = new FileValidator();
		$this->assertEquals(array(), $val->types);
		$val = new FileValidator(array('types' => array('jpeg', 'exe')));
		$this->assertEquals(array('jpeg', 'exe'), $val->types);
	}

	public function testGetSizeLimit()
	{
		$size = $this->sizeToBytes(ini_get('upload_max_filesize'));
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
				return (int)$sizeStr * 1048576;
			case 'K':
			case 'k':
				return (int)$sizeStr * 1024;
			case 'G':
			case 'g':
				return (int)$sizeStr * 1073741824;
			default:
				return (int)$sizeStr;
		}
	}

	public function testValidateAttributeMultiple()
	{
		$val = new FileValidator(array('maxFiles' => 2));
		$m = FakedValidationModel::createWithAttributes(array('attr_files' => 'path'));
		$val->validateAttribute($m, 'attr_files');
		$this->assertTrue($m->hasErrors('attr_files'));
		$m = FakedValidationModel::createWithAttributes(array('attr_files' => array()));
		$val->validateAttribute($m, 'attr_files');
		$this->assertTrue($m->hasErrors('attr_files'));
		$this->assertSame($val->uploadRequired, current($m->getErrors('attr_files')));
		$m = FakedValidationModel::createWithAttributes(
			array(
				'attr_files' => $this->createTestFiles(
					array(
						array(
							'name' => 'test_up_1.txt',
							'size' => 1024,
						),
						array(
							'error' => UPLOAD_ERR_NO_FILE,
						),
					)
				)
			)
		);
		$val->validateAttribute($m, 'attr_files');
		$this->assertFalse($m->hasErrors('attr_files'));
		$m = FakedValidationModel::createWithAttributes(
			array('attr_files' => $this->createTestFiles(array(array(''), array(''), array(''),)))
		);
		$val->validateAttribute($m, 'attr_files');
		$this->assertTrue($m->hasErrors());
		$this->assertTrue(stripos(current($m->getErrors('attr_files')), 'you can upload at most') !== false);
	}

	/**
	 * @param array $params
	 * @return UploadedFile[]
	 */
	protected function createTestFiles($params = array())
	{
		$rndString = function ($len = 10) {
			$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
			$randomString = '';
			for ($i = 0; $i < $len; $i++) {
				$randomString .= $characters[rand(0, strlen($characters) - 1)];
			}
			return $randomString;
		};
		$files = array();
		foreach ($params as $param) {
			if (empty($param) && count($params) != 1) {
				$files[] = array('no instance of UploadedFile');
				continue;
			}
			$name = isset($param['name']) ? $param['name'] : $rndString();
			$tempName = \Yii::getAlias('@yiiunit/runtime/validators/file/tmp') . $name;
			if (is_readable($tempName)) {
				$size = filesize($tempName);
			} else {
				$size = isset($param['size']) ? $param['size'] : rand(
					1,
					$this->sizeToBytes(ini_get('upload_max_filesize'))
				);
			}
			$type = isset($param['type']) ? $param['type'] : 'text/plain';
			$error = isset($param['error']) ? $param['error'] : UPLOAD_ERR_OK;
			if (count($params) == 1) {
				$error = empty($param) ? UPLOAD_ERR_NO_FILE : $error;
				return new UploadedFile($name, $tempName, $type, $size, $error);
			}
			$files[] = new UploadedFile($name, $tempName, $type, $size, $error);
		}
		return $files;
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
		$m = $this->createModelForAttributeTest();
		// too big
		$val = new FileValidator(array('maxSize' => 128));
		$val->validateAttribute($m, 'attr_files');
		$this->assertTrue($m->hasErrors('attr_files'));
		$this->assertTrue(
			stripos(
				current($m->getErrors('attr_files')),
				str_ireplace(array('{file}', '{limit}'), array($m->attr_files->getName(), 128), $val->tooBig)
			) !== false
		);
		// to Small
		$m = $this->createModelForAttributeTest();
		$val = new FileValidator(array('minSize' => 2048));
		$val->validateAttribute($m, 'attr_files');
		$this->assertTrue($m->hasErrors('attr_files'));
		$this->assertTrue(
			stripos(
				current($m->getErrors('attr_files')),
				str_ireplace(array('{file}', '{limit}'), array($m->attr_files->getName(), 2048), $val->tooSmall)
			) !== false
		);
		// UPLOAD_ERR_INI_SIZE/UPLOAD_ERR_FORM_SIZE
		$m = $this->createModelForAttributeTest();
		$val = new FileValidator();
		$val->validateAttribute($m, 'attr_err_ini');
		$this->assertTrue($m->hasErrors('attr_err_ini'));
		$this->assertTrue(
			stripos(
				current($m->getErrors('attr_err_ini')),
				str_ireplace(
					array('{file}', '{limit}'),
					array($m->attr_err_ini->getName(), $val->getSizeLimit()),
					$val->tooBig
				)
			) !== false
		);
		// UPLOAD_ERR_PARTIAL
		$m = $this->createModelForAttributeTest();
		$val = new FileValidator();
		$val->validateAttribute($m, 'attr_err_part');
		$this->assertTrue($m->hasErrors('attr_err_part'));
		$this->assertSame(Yii::t('yii', 'File upload failed.'), current($m->getErrors('attr_err_part')));
		$log = Yii::$app->getLog()->toArray();
		$this->assertSame(FileValidator::className() . '::validateFile', $log['messages'][0][2]);
	}

	public function testValidateAttributeType()
	{
		$val = new FileValidator(array('types' => 'jpeg, jpg'));
		$m = FakedValidationModel::createWithAttributes(
			array(
				'attr_jpg' => $this->createTestFiles(array(array('name' => 'one.jpeg'))),
				'attr_exe' => $this->createTestFiles(array(array('name' => 'bad.exe'))),
			)
		);
		$val->validateAttribute($m, 'attr_jpg');
		$this->assertFalse($m->hasErrors('attr_jpg'));
		$val->validateAttribute($m, 'attr_exe');
		$this->assertTrue($m->hasErrors('attr_exe'));
		$this->assertTrue(stripos(current($m->getErrors('attr_exe')), 'Only files with these extensions ') !== false);
	}


	protected function createModelForAttributeTest()
	{
		return FakedValidationModel::createWithAttributes(
			array(
				'attr_files' => $this->createTestFiles(
					array(
						array('name' => 'abc.jpg', 'size' => 1024, 'type' => 'image/jpeg'),
					)
				),
				'attr_files_empty' => $this->createTestFiles(array(array())),
				'attr_err_ini' => $this->createTestFiles(array(array('error' => UPLOAD_ERR_INI_SIZE))),
				'attr_err_part' => $this->createTestFiles(array(array('error' => UPLOAD_ERR_PARTIAL))),
				'attr_err_tmp' => $this->createTestFiles(array(array('error' => UPLOAD_ERR_NO_TMP_DIR))),
				'attr_err_write' => $this->createTestFiles(array(array('error' => UPLOAD_ERR_CANT_WRITE))),
				'attr_err_ext' => $this->createTestFiles(array(array('error' => UPLOAD_ERR_EXTENSION))),

			)
		);
	}

	public function testValidateAttributeErrPartial()
	{
		$m = $this->createModelForAttributeTest();
		$val = new FileValidator();
		$val->validateAttribute($m, 'attr_err_part');
		$this->assertTrue($m->hasErrors('attr_err_part'));
		$this->assertSame(Yii::t('yii', 'File upload failed.'), current($m->getErrors('attr_err_part')));
		$log = Yii::$app->getLog()->toArray();
		$this->assertSame(FileValidator::className() . '::validateFile', $log['messages'][0][2]);
		$this->assertContains('File was only', $log['messages'][0][0]);
	}

	public function testValidateAttributeErrCantWrite()
	{
		$m = $this->createModelForAttributeTest();
		$val = new FileValidator();
		$val->validateAttribute($m, 'attr_err_write');
		$this->assertTrue($m->hasErrors('attr_err_write'));
		$this->assertSame(Yii::t('yii', 'File upload failed.'), current($m->getErrors('attr_err_write')));
		$log = Yii::$app->getLog()->toArray();
		$this->assertSame(FileValidator::className() . '::validateFile', $log['messages'][0][2]);
		$this->assertContains('Failed to write', $log['messages'][0][0]);
	}

	public function testValidateAttributeErrExtension()
	{
		$m = $this->createModelForAttributeTest();
		$val = new FileValidator();
		$val->validateAttribute($m, 'attr_err_ext');
		$this->assertTrue($m->hasErrors('attr_err_ext'));
		$this->assertSame(Yii::t('yii', 'File upload failed.'), current($m->getErrors('attr_err_ext')));
		$log = Yii::$app->getLog()->toArray();
		$this->assertSame(FileValidator::className() . '::validateFile', $log['messages'][0][2]);
		$this->assertContains('PHP extension', $log['messages'][0][0]);
	}

	public function testValidateAttributeErrNoTmpDir()
	{
		$m = $this->createModelForAttributeTest();
		$val = new FileValidator();
		$val->validateAttribute($m, 'attr_err_tmp');
		$this->assertTrue($m->hasErrors('attr_err_tmp'));
		$this->assertSame(Yii::t('yii', 'File upload failed.'), current($m->getErrors('attr_err_tmp')));
		$log = Yii::$app->getLog()->toArray();
		$this->assertSame(FileValidator::className() . '::validateFile', $log['messages'][0][2]);
		$this->assertContains('Missing the temporary folder', $log['messages'][0][0]);
	}
}