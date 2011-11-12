Alex's Code Review, 2011.11.12
==============================

Overall hierarchy
------------------

Generally is OK. Like that `Object` and `Component` are now separated.
I've generated 2 diagrams under `docs/` to see it better as a whole.

Object
------

### property feature

Why returning anything when setting a value?

~~~
if (method_exists($this, $setter)) {
	// ???
	return $this->$setter($value);
}
~~~

Is it OK that `canGetProperty` and `canSetProperty` will return `false` for real
class members?

### callbacks and expressions

We're using 5.3. What's the reason to support `eval()` in `evaluateExpression` if
we have anonymous functions? Is that for storing code as string inside of DB (RBAC)?

If we're going to get rid of `eval()`, cosider remaning method to something about callback.
If not then we definitely need to use anonymous functions in API docs and the guide
where possible.

### Object::create()

#### `__construct` issue

Often a class doesn't have `__construct` implementation and `stdClass` doesn't have
default one either but Object::create() always expects constructor to be
defined. See `ObjectTest`. Either `method_exists` call or `Object::__construct` needed.

#### How to support object factory like we do with CWidgetFactory?

~~~
class ObjectConfig
{
	public function configure($class)
	{
		$config = $this->load($class);
		// apply config to $class
	}

	private function load($class)
	{
		// get class properties from a config file
		// in this method we need to walk all the
		// inheritance hierarchy down to Object itself
		return array(
			'property' => 'value',
			// …
		);
	}
}
~~~

Then we need to add `__construct` to `Object` (or implement `Initalbe`):

~~~
class Object
{
	public function __construct()
	{
		$conf = new ObjectConfig();
		$conf->configure($this);
	}
}
~~~

This way we'll be able to set defaults for any object.

#### Do we need to support lazy class injection?

Currently there's no way to lazy-inject class into another class property via
config. Do we need it? If yes then we can probably extend component config to support
the following:

~~~
class Foo extends Object
{
	public $prop;
}

class Bar extends Object
{
	public $prop;
}

$config = array(
	'prop' => array(
		'class' => 'Bar',
		'prop' => 'Hello!',
	),
);

$foo = Foo::create($config);
echo $foo->bar->prop;
// will output Hello!
~~~

Should it support infinite nesting level?

### Why `Event` is `Object`?

There's no need to extend from `Object`. Is there a plan to use `Object` features
later?

Initable
--------

Interface itself looks OK. Its usage is OK too.

`Initable::preinit` mentioned in `Yii::create()` docs but neither defined in
the interface nor called in the code.

Behaviors
---------

Overall I wasn't able to use behaviors. See `BehaviorTest`.

### Wrong API docs at Behavior

Docs mention properties and events but not methods.

### Should behaviors be able to define events for owner components?

Why not? Should be a very good feature in order to make behaviors customizable.

### Multiple behaviors can be attached to the same component

What if we'll have multiple methods / properties / events with the same name?

### How to use Behavior::attach?

Looks like it is used by `Component::attachBehavior` but can't be used without it.
Why it's public then? Can we move it to `Component?`

Events
------

Class itself looks OK. Component part is OK as well but I've not tested
it carefully. Overall it seems concept is the same as in Yii1.

### Event declaration: the on-method is mostly repetitive for every event. Should we choose a different way of declaring events?

Maybe. People complained previously about too many code for event declaration.

### Should we implement some additional event mechanism, such as global events?

Why use two different implementations in a single application?

Exceptions
----------

- Should we convert all errors, warnings and notices to exceptions?

Coding style
------------

See `docs/code_style.md`.