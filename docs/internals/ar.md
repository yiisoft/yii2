ActiveRecord
============

Scenarios
---------

All possible scenario formats supported by ActiveRecord:

```php
public function scenarios()
{
    return array(
        // 1. attributes array
        'scenario1' => array('attribute1', 'attribute2'),

        // 2. insert, update and delete operations won't be wrapped with transaction (default mode)
        'scenario2' => array(
            'attributes' => array('attribute1', 'attribute2'),
            'atomic' => array(), // default value
        ),

        // 3. all three operations (insert, update and delete) will be wrapped with transaction
        'scenario3' => array(
            'attributes' => array('attribute1', 'attribute2'),
            'atomic',
        ),

        // 4. insert and update operations will be wrapped with transaction, delete won't
        'scenario4' => array(
            'attributes' => array('attribute1', 'attribute2'),
            'atomic' => array(self::INSERT, self::UPDATE),
        ),

        // 5. insert and update operations won't be wrapped with transaction, delete will
        'scenario5' => array(
            'attributes' => array('attribute1', 'attribute2'),
            'atomic' => array(self::DELETE),
        ),
    );
}
```

Query
-----

### Basic Queries

### Relational Queries

### Scopes
