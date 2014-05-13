Resources
=========

RESTful APIs are mostly about accessing and manipulating *resources*, also known as [models](structure-models.md)
in the MVC paradigm. You can represent a resource as an object of an arbitrary class.

By default, Yii will return all public property values of a resource object through RESTful APIs.
However, if the resource implements the [[yii\base\Arrayable]] interface, Yii will return the result
of the [[yii\base\Arrayable::toArray()]] method, instead.

In most cases, your resource classes should extend from [[yii\base\Model]] or its child class
(e.g. [[yii\db\ActiveRecord]] for DB-related resources) for the following reasons:

* [[yii\base\Model]] implements [[yii\base\Arrayable]], and you can specify what data should be returned
  by your resource class;
* [[yii\base\Model]] supports [input validation](input-validation.md), which is useful if your RESTful APIs
  need to support data input.

In this section, we will mainly describe how to specify the data to be returned by your resource classes,
assuming they extend from [[yii\base\Model]].



By default, all public member variables of a resource

When developing a resource class, besides the normal business logic that you should put in the class,
a major development effort lies in declaring what information about the resource can be returned by the RESTful APIs.




For classes extending from [[yii\base\Model]] or [[yii\db\ActiveRecord]], besides directly overriding `toArray()`,
you may also override the `fields()` method and/or the `extraFields()` method to customize the data being returned.

The method [[yii\base\Model::fields()]] declares a set of *fields* that should be included in the result.
A field is simply a named data item. In a result array, the array keys are the field names, and the array values
are the corresponding field values. The default implementation of [[yii\base\Model::fields()]] is to return
all attributes of a model as the output fields; for [[yii\db\ActiveRecord::fields()]], by default it will return
the names of the attributes whose values have been populated into the object.

You can override the `fields()` method to add, remove, rename or redefine fields. For example,

```php
// explicitly list every field, best used when you want to make sure the changes
// in your DB table or model attributes do not cause your field changes (to keep API backward compatibility).
public function fields()
{
    return [
        // field name is the same as the attribute name
        'id',
        // field name is "email", the corresponding attribute name is "email_address"
        'email' => 'email_address',
        // field name is "name", its value is defined by a PHP callback
        'name' => function () {
            return $this->first_name . ' ' . $this->last_name;
        },
    ];
}

// filter out some fields, best used when you want to inherit the parent implementation
// and blacklist some sensitive fields.
public function fields()
{
    $fields = parent::fields();

    // remove fields that contain sensitive information
    unset($fields['auth_key'], $fields['password_hash'], $fields['password_reset_token']);

    return $fields;
}
```

The return value of `fields()` should be an array. The array keys are the field names, and the array values
are the corresponding field definitions which can be either property/attribute names or anonymous functions
returning the corresponding field values.

> Warning: Because by default all attributes of a model will be included in the API result, you should
> examine your data to make sure they do not contain sensitive information. If there is such information,
> you should override `fields()` or `toArray()` to filter them out. In the above example, we choose
> to filter out `auth_key`, `password_hash` and `password_reset_token`.

You may use the `fields` query parameter to specify which fields in `fields()` should be included in the result.
If this parameter is not specified, all fields returned by `fields()` will be returned.

The method [[yii\base\Model::extraFields()]] is very similar to [[yii\base\Model::fields()]].
The difference between these methods is that the latter declares the fields that should be returned by default,
while the former declares the fields that should only be returned when the user specifies them in the `expand` query parameter.

For example, `http://localhost/users?fields=id,email&expand=profile` may return the following JSON data:

```php
[
    {
        "id": 100,
        "email": "100@example.com",
        "profile": {
            "id": 100,
            "age": 30,
        }
    },
    ...
]
```
