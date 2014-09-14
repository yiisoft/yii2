<?php

namespace yiiunit\extensions\faker;

use Yii;
use yiiunit\TestCase;
use yii\faker\FixtureController;

/**
 * Unit test for [[\yii\faker\FixtureController]].
 * @see FixtureController
 *
 * @group console
 */
class FixtureControllerTest extends TestCase
{

    /**
     * @var \yiiunit\extensions\faker\FixtureConsoledController
     */
    private $_fixtureController;

    protected function setUp()
    {
        parent::setUp();

        if (defined('HHVM_VERSION')) {
            // https://github.com/facebook/hhvm/issues/1447
            $this->markTestSkipped('Can not test on HHVM because require is cached.');
        }

        $this->mockApplication();

        $this->_fixtureController = Yii::createObject([
            'class' => 'yiiunit\extensions\faker\FixtureConsoledController',
            'interactive' => false,
            'fixtureDataPath' => '@runtime/faker',
            'templatePath' => '@yiiunit/data/extensions/faker/templates'
        ],['fixture-faker', Yii::$app]);
    }

    public function tearDown()
    {
        @unlink(Yii::getAlias('@runtime/faker/user.php'));
        @unlink(Yii::getAlias('@runtime/faker/profile.php'));
        @unlink(Yii::getAlias('@runtime/faker/book.php'));
        parent::tearDown();
    }

    public function testGenerateOne()
    {
        $filename = Yii::getAlias('@runtime/faker/user.php');
        $this->assertFileNotExists($filename, 'file to be generated should not exist before');

        $this->_fixtureController->actionGenerate('user');
        $this->assertFileExists($filename, 'fixture template file should be generated');

        $generatedData = require Yii::getAlias('@runtime/faker/user.php');
        $this->assertCount(2, $generatedData, 'by default only 2 fixtures should be generated');
        
        foreach ($generatedData as $fixtureData) {
            $this->assertNotNull($fixtureData['username'],  'generated "username" should not be empty');
            $this->assertNotNull($fixtureData['email'],     'generated "email" should not be empty');
            $this->assertNotNull($fixtureData['auth_key'],  'generated "auth_key" should not be empty');
            $this->assertNotNull($fixtureData['created_at'],'generated "created_at" should not be empty');
            $this->assertNotNull($fixtureData['updated_at'],'generated "updated_at" should not be empty');
        }
    }

    public function testGenerateBoth()
    {
        $userFilename = Yii::getAlias('@runtime/faker/user.php');
        $this->assertFileNotExists($userFilename, 'file to be generated should not exist before');

        $profileFilename = Yii::getAlias('@runtime/faker/profile.php');
        $this->assertFileNotExists($profileFilename, 'file to be generated should not exist before');

        $this->_fixtureController->actionGenerate('user', 'profile');
        $this->assertFileExists($userFilename, 'fixture template file should be generated');
        $this->assertFileExists($profileFilename, 'fixture template file should be generated');
    }

    public function testGenerateNotFound()
    {
        $fileName = Yii::getAlias('@runtime/faker/not_existing_template.php');
        $this->_fixtureController->actionGenerate('not_existing_template');
        $this->assertFileNotExists($fileName, 'not existing template should not be generated');
    }

    public function testGenerateProvider()
    {
        $bookFilename = Yii::getAlias('@runtime/faker/book.php');
        $this->assertFileNotExists($bookFilename, 'file to be generated should not exist before');

        $this->_fixtureController->providers[] = 'yiiunit\data\extensions\faker\providers\Book';
        $this->_fixtureController->run('generate',['book']);
        $this->assertFileExists($bookFilename, 'fixture template file should be generated');
    }

    /**
     * @expectedException yii\console\Exception
     */
    public function testNothingToGenerateException()
    {
        $this->_fixtureController->actionGenerate();
    }

    /**
     * @expectedException yii\console\Exception
     */
    public function testWrongTemplatePathException()
    {
        $this->_fixtureController->templatePath = '@not/existing/fixtures/templates/path';
        $this->_fixtureController->run('generate',['user']);
    }

    public function testGenerateParticularTimes()
    {
        $filename = Yii::getAlias('@runtime/faker/user.php');
        $this->assertFileNotExists($filename, 'file to be generated should not exist before');

        $this->_fixtureController->count = 5;
        $this->_fixtureController->actionGenerate('user');
        $this->assertFileExists($filename, 'fixture template file should be generated');

        $generatedData = require Yii::getAlias('@runtime/faker/user.php');
        $this->assertCount(5, $generatedData, 'exactly 5 fixtures should be generated for the given template');
    }

    public function testGenerateParticlularLanguage()
    {
        $filename = Yii::getAlias('@runtime/faker/profile.php');
        $this->assertFileNotExists($filename, 'file to be generated should not exist before');

        $this->_fixtureController->language = 'ru_RU';
        $this->_fixtureController->actionGenerate('profile');
        $this->assertFileExists($filename, 'fixture template file should be generated');

        $generatedData = require Yii::getAlias('@runtime/faker/profile.php');
        $this->assertEquals(1, preg_match('/^[а-я]*$/iu', $generatedData[0]['first_name']), 'generated value should be in ru-RU language');
    }

    public function testGenerateAll()
    {
        $userFilename = Yii::getAlias('@runtime/faker/user.php');
        $this->assertFileNotExists($userFilename, 'file to be generated should not exist before');

        $profileFilename = Yii::getAlias('@runtime/faker/profile.php');
        $this->assertFileNotExists($profileFilename, 'file to be generated should not exist before');

        $bookFilename = Yii::getAlias('@runtime/faker/book.php');
        $this->assertFileNotExists($bookFilename, 'file to be generated should not exist before');

        $this->_fixtureController->providers[] = 'yiiunit\data\extensions\faker\providers\Book';
        $this->_fixtureController->run('generate-all');

        $this->assertFileExists($userFilename, 'fixture template file should be generated');
        $this->assertFileExists($profileFilename, 'fixture template file should be generated');
        $this->assertFileExists($bookFilename, 'fixture template file should be generated');
    }

}

class FixtureConsoledController extends FixtureController
{

    public function stdout($string)
    {
    }

}
