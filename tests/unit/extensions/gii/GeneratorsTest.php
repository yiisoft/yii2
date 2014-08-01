<?php
namespace yiiunit\extensions\gii;

use yii\gii\CodeFile;
use yii\gii\generators\controller\Generator as ControllerGenerator;
use yii\gii\generators\crud\Generator as CRUDGenerator;
use yii\gii\generators\extension\Generator as ExtensionGenerator;
use yii\gii\generators\form\Generator as FormGenerator;
use yii\gii\generators\model\Generator as ModelGenerator;
use yii\gii\generators\module\Generator as ModuleGenerator;

/**
 * GeneratorsTest checks that Gii generators aren't throwing any errors during generation
 */
class GeneratorsTest extends GiiTestCase
{
    public function testControllerGenerator()
    {
        $generator = new ControllerGenerator();
        $generator->template = 'default';
        $generator->controller = 'test';

        if ($generator->validate()) {
            $generator->generate();
        } else {
            print_r($generator->getErrors());
        }
    }

    public function testExtensionGenerator()
    {
        $generator = new ExtensionGenerator();
        $generator->template = 'default';
        $generator->vendorName = 'samdark';
        $generator->namespace = 'samdark\\';
        $generator->license = 'BSD';
        $generator->title = 'Sample extension';
        $generator->description = 'This is sample description.';
        $generator->authorName = 'Alexander Makarov';
        $generator->authorEmail = 'sam@rmcreative.ru';

        if ($generator->validate()) {
            $generator->generate();
        } else {
            print_r($generator->getErrors());
        }
    }

    public function testModelGenerator()
    {
        $generator = new ModelGenerator();
        $generator->template = 'default';
        $generator->tableName = 'profile';
        $generator->modelClass = 'Profile';

        if ($generator->validate()) {
            $files = $generator->generate();
            $modelCode = $files[0]->content;

            $this->assertTrue(strpos($modelCode, "'id' => 'ID'") !== false, "ID label should be there:\n" . $modelCode);
            $this->assertTrue(strpos($modelCode, "'description' => 'Description',") !== false, "Description label should be there:\n" . $modelCode);
        } else {
            print_r($generator->getErrors());
        }
    }

    public function testModuleGenerator()
    {
        $generator = new ModuleGenerator();
        $generator->template = 'default';
        $generator->moduleID = 'test';
        $generator->moduleClass = 'app\modules\test\Module';

        if ($generator->validate()) {
            $generator->generate();
        } else {
            print_r($generator->getErrors());
        }
    }


    public function testFormGenerator()
    {
        $generator = new FormGenerator();
        $generator->template = 'default';
        $generator->modelClass = 'yiiunit\extensions\gii\Profile';
        $generator->viewName = 'profile';
        $generator->viewPath = '@yiiunit/runtime';

        if ($generator->validate()) {
            $generator->generate();
        } else {
            print_r($generator->getErrors());
        }
    }

    public function testCRUDGenerator()
    {
        $generator = new CRUDGenerator();
        $generator->template = 'default';
        $generator->modelClass = 'yiiunit\extensions\gii\Profile';
        $generator->controllerClass = 'app\TestController';

        if ($generator->validate()) {
            $generator->generate();
        } else {
            print_r($generator->getErrors());
        }
    }
}