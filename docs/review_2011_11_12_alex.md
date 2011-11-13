Alex's Code Review, 2011.11.12
==============================

Overall hierarchy
------------------

Generally is OK. Like that `Object` and `Component` are now separated.
I've generated 2 diagrams under `docs/` to see it better as a whole.

> The purpose of separating `Object` from `Component` is to make `Object`
> a super-light base class that supports properties defined by getter/setters.
> Note that `Component` is a bit of heavy because it uses two extra member
> variables to support events and behaviors.


Object
------

### property feature

Is it OK that `canGetProperty` and `canSetProperty` will return `false` for real
class members?

> Added $checkVar parameter

### callbacks and expressions

We're using 5.3. What's the reason to support `eval()` in `evaluateExpression` if
we have anonymous functions? Is that for storing code as string inside of DB (RBAC)?

If we're going to get rid of `eval()`, cosider remaning method to something about callback.
If not then we definitely need to use anonymous functions in API docs and the guide
where possible.

> The purpose of evaluateExpression() is to provide a way of evaluating a PHP expression
> in the context of an object. Will remove it before release if we find no use of it.

>> mdomba:
>> As eval() is controversial, and anonymous functions can replace all Yii 1 usage of eval()
>> how about removing it from the beginning and add it only if we find it necessary.
>> This way we would not be tempted to stick with eval() and will be forced to first try to find alternatives

### Object::create()

#### `__construct` issue

Often a class doesn't have `__construct` implementation and `stdClass` doesn't have
default one either but Object::create() always expects constructor to be
defined. See `ObjectTest`. Either `method_exists` call or `Object::__construct` needed.

> Added Object::__construct.

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

> The key issue here is about how to process the config file. Clearly, we cannot
> do this for every type of component because it would mean an extra file access
> for every component type

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

> I don't think we need this. Foo::$prop cannot be an object unless it needs it to be.
> In that case, it can be defined with a setter in which it can handle the object creation
> based on a configuration array. This is a bit inconvenient, but I think such usage is
> not very common.

### Why `Event` is `Object`?

There's no need to extend from `Object`. Is there a plan to use `Object` features
later?

> To use properties defined via getter/setter.


Behaviors
---------

Overall I wasn't able to use behaviors. See `BehaviorTest`.

### Should behaviors be able to define events for owner components?

Why not? Should be a very good feature in order to make behaviors customizable.

> It's a bit hard to implement it efficiently. I tend not to support it for now
> unless enough people are requesting for it.

### Multiple behaviors can be attached to the same component

What if we'll have multiple methods / properties / events with the same name?

> The first one takes precedence. This is the same as we do in 1.1.

### How to use Behavior::attach?

Looks like it is used by `Component::attachBehavior` but can't be used without it.
Why it's public then? Can we move it to `Component?`

> It's public because it is called by Component. It is in Behavior such that
> it can be overridden by behavior classes to customize the attach process.

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

> I think not. We used to do this in early versions of 1.0. We found sometimes
> very mysterious things would happen which makes error fixing harder rather than
> easier.

Coding style
------------

See `docs/code_style.md`.