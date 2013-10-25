ActiveRecord
============

Scenarios
---------

Possible scenario formats supported by ActiveRecord:

```php
public function scenarios()
{
    return [
        // attributes array, all operations won't be wrapped with transaction
        'scenario1' => ['attribute1', 'attribute2'],

        // insert and update operations will be wrapped with transaction, delete won't be wrapped
        'scenario2' => [
            'attributes' => ['attribute1', 'attribute2'],
            'atomic' => [self::OP_INSERT, self::OP_UPDATE],
        ],
    ];
}
```

Query
-----

### Basic Queries

### Relational Queries

### Scopes
