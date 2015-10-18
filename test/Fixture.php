<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\test;

use yii\base\Component;

/**
 * Fixture represents a fixed state of a test environment.
 *
 * Each fixture instance represents a particular aspect of a test environment. For example,
 * you may use `UserFixture` to initialize the user database table with a set of known data. You may
 * load the fixture when running every test method so that the user table always contains the fixed data
 * and thus allows your test predictable and repeatable.
 *
 * A fixture may depend on other fixtures, specified via the [[depends]] property. When a fixture is being loaded,
 * its dependent fixtures will be automatically loaded BEFORE the fixture; and when the fixture is being unloaded,
 * its dependent fixtures will be unloaded AFTER the fixture.
 *
 * You should normally override [[load()]] to specify how to set up a fixture; and override [[unload()]]
 * for clearing up a fixture.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class Fixture extends Component
{
    /**
     * @var array the fixtures that this fixture depends on. This must be a list of the dependent
     * fixture class names.
     */
    public $depends = [];


    /**
     * Loads the fixture.
     * This method is called before performing every test method.
     * You should override this method with concrete implementation about how to set up the fixture.
     */
    public function load()
    {
    }

    /**
     * This method is called BEFORE any fixture data is loaded for the current test.
     */
    public function beforeLoad()
    {
    }

    /**
     * This method is called AFTER all fixture data have been loaded for the current test.
     */
    public function afterLoad()
    {
    }

    /**
     * Unloads the fixture.
     * This method is called after every test method finishes.
     * You may override this method to perform necessary cleanup work for the fixture.
     */
    public function unload()
    {
    }

    /**
     * This method is called BEFORE any fixture data is unloaded for the current test.
     */
    public function beforeUnload()
    {
    }

    /**
     * This method is called AFTER all fixture data have been unloaded for the current test.
     */
    public function afterUnload()
    {
    }
}
