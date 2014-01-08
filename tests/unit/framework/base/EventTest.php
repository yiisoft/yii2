<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yiiunit\framework\base;

use yii\base\Component;
use yii\base\Event;
use yiiunit\TestCase;

/**
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class EventTest extends TestCase
{
	public $counter;

	public function setUp()
	{
		$this->counter = 0;
		Event::off(ActiveRecord::className(), 'save');
		Event::off(Post::className(), 'save');
		Event::off(User::className(), 'save');
	}

	public function testOn()
	{
		Event::on(Post::className(), 'save', function ($event) {
			$this->counter += 1;
		});
		Event::on(ActiveRecord::className(), 'save', function ($event) {
			$this->counter += 3;
		});
		$this->assertEquals(0, $this->counter);
		$post = new Post;
		$post->save();
		$this->assertEquals(4, $this->counter);
		$user = new User;
		$user->save();
		$this->assertEquals(7, $this->counter);
	}

	public function testOff()
	{
		$handler = function ($event) {
			$this->counter ++;
		};
		$this->assertFalse(Event::hasHandlers(Post::className(), 'save'));
		Event::on(Post::className(), 'save', $handler);
		$this->assertTrue(Event::hasHandlers(Post::className(), 'save'));
		Event::off(Post::className(), 'save', $handler);
		$this->assertFalse(Event::hasHandlers(Post::className(), 'save'));
	}

	public function testHasHandlers()
	{
		$this->assertFalse(Event::hasHandlers(Post::className(), 'save'));
		$this->assertFalse(Event::hasHandlers(ActiveRecord::className(), 'save'));
		Event::on(Post::className(), 'save', function ($event) {
			$this->counter += 1;
		});
		$this->assertTrue(Event::hasHandlers(Post::className(), 'save'));
		$this->assertFalse(Event::hasHandlers(ActiveRecord::className(), 'save'));

		$this->assertFalse(Event::hasHandlers(User::className(), 'save'));
		Event::on(ActiveRecord::className(), 'save', function ($event) {
			$this->counter += 1;
		});
		$this->assertTrue(Event::hasHandlers(User::className(), 'save'));
		$this->assertTrue(Event::hasHandlers(ActiveRecord::className(), 'save'));
	}
}

class ActiveRecord extends Component
{
	public function save()
	{
		$this->trigger('save');
	}
}

class Post extends ActiveRecord
{
}

class User extends ActiveRecord
{
}
