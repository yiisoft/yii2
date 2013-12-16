<?php

namespace tests\unit\models;

use yii\codeception\TestCase;
use yii\test\DbTestTrait;

class UserTest extends TestCase
{
	use DbTestTrait;

	protected function setUp()
	{
		parent::setUp();
		// uncomment the following to load fixtures for table tbl_user
		//$this->loadFixtures(['tbl_user']);
	}

	// TODO add test methods here
}
