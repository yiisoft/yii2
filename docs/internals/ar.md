ActiveRecord
============

Scenarios
---------

All possible scenario formats supported by ActiveRecord:

```php
public function scenarios()
{
    return array(
        // attributes array, all operations won't be wrapped with transaction
        'scenario1' => array('attribute1', 'attribute2'),

        // insert and update operations will be wrapped with transaction, delete won't be wrapped
        'scenario2' => array(
            'attributes' => array('attribute1', 'attribute2'),
            'atomic' => array(self::OP_INSERT, self::OP_UPDATE),
        ),
    );
}
```

Query
-----

### Basic Queries

### Relational Queries

### Scopes
