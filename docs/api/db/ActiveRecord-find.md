The returned [[ActiveQuery]] instance can be further customized by calling
methods defined in [[ActiveQuery]] before returning the populated active records.

Below are some examples:

~~~
// find all customers
$customers = Customer::find()->all();
// find all active customers and order them by their age:
$customers = Customer::find()
    ->where(array('status' => 1))
    ->orderBy('age')
    ->all();
// find a single customer whose primary key value is 10
$customer = Customer::find(10);
// the above is equivalent to:
$customer = Customer::find()->where(array('id' => 10))->one();
// find a single customer whose age is 30 and whose status is 1
$customer = Customer::find(array('age' => 30, 'status' => 1));
// the above is equivalent to:
$customer = Customer::find()->where(array('age' => 30, 'status' => 1))->one();
~~~
