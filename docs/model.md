Model
=====

Validation rules and mass assignment
------------------------------------

In Yii2 unlike Yii 1.x validation rules are separated from mass assignment. Validation
rules are described in `rules()` method of the model while what's safe for mass
assignment is described in `scenarios` method:

```php

function rules() {
 return array(
  // rule applied when corresponding field is "safe"
  array('username', 'length', 'min' => 2),
  array('first_name', 'length', 'min' => 2),
  array('password', 'required'),

  // rule applied when scenario is "signup" no matter if field is "safe" or not
  array('hashcode', 'check', 'on' => 'signup'),
 );
}

function scenarios() {
 return array(
  // on signup allow mass assignment of username
  'signup' => array('username', 'password'),
  'update' => array('username', 'first_name'),
 );
}

```

Note that everything is unsafe by default and you can't make field "safe"
without specifying scenario.