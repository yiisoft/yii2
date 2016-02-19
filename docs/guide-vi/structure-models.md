Models
======

Models là phần trong kiến trúc [MVC](http://en.wikipedia.org/wiki/Model%E2%80%93view%E2%80%93controller).
Là đối tượng đại diện cho phần dữ liệu, phương thức xử lý và nghiệp vụ logic.

Bạn có thể tạo mới các lớp model bằng việc kế thừa từ lớp [[yii\base\Model]] hoặc các lớp con của nó. Lớp cơ sở
[[yii\base\Model]] hỗ trợ nhiều tính năng như:

* [Attributes](#attributes): đại diện cho các dữ liệu nghiệp vụ và có thể truy cập như các thuộc tính
  hoặc mảng các phần tử;
* [Attribute labels](#attribute-labels): tên hiển thị cho các thuộc tính;
* [Massive assignment](#massive-assignment): supports populating multiple attributes in a single step;
* [Validation rules](#validation-rules): khai báo các quy tắc và xác thực dữ liệu được nhập vào;
* [Data Exporting](#data-exporting): cho phép xuất dữ liệu dưới dạng mảng hoặc tuỳ chọn khác.

Lớp `Model` thường dựa trên lớp để thực hiện chức năng nâng cao, chẳng hạn [Active Record](db-active-record.md).
Vui lòng tham khảo thêm tài liệu để biết thêm thông tin.

> Lưu ý: Model của bạn không phải bắt buộc kế thừa từ lớp [[yii\base\Model]]. Tuy nhiên, vì Yii chứa nhiều thành phần
  dựng lên và hỗ trợ cho [[yii\base\Model]], vì thế nó là lớp cơ sở cho các lớp Model.


## Thuộc tính (Attribute) <span id="attributes"></span>

Model đại diện cho tầng xử lý nghiệp vụ và chứa các *thuộc tính*. Mỗi thuộc tính được truy cập toàn cục như phần tử của 
model. Phương thức [[yii\base\Model::attributes()]] sẽ mô tả các thuộc tính trong lớp model hiện có.

Bạn có thể truy cập vào thuộc tính như các phần tử của các đối tượng:

```php
$model = new \app\models\ContactForm;

// "name" là tên thuộc tính của ContactForm
$model->name = 'example';
echo $model->name;
```

Bạn có thể truy cập các thuộc tính như truy cập mảng các phần tử, nhờ sự hỗ trợ từ lớp
[ArrayAccess](http://php.net/manual/en/class.arrayaccess.php) và [ArrayIterator](http://php.net/manual/en/class.arrayiterator.php)
bởi [[yii\base\Model]]:

```php
$model = new \app\models\ContactForm;

// truy cập các thuộc tính như mảng các phần tử
$model['name'] = 'example';
echo $model['name'];

// iterate attributes
foreach ($model as $name => $value) {
    echo "$name: $value\n";
}
```


### Định nghĩa các thuộc tính <span id="defining-attributes"></span>

Mặc định, nếu Model của bạn được kế thừa từ lớp [[yii\base\Model]], và tất cả các biến có phạm vi *toàn cục trong lớp*
. Ví dụ, Model `ContactForm` sau có bốn thuộc tính là: `name`, `email`,
`subject` và `body`. Model `ContactForm` dùng để nhận dữ liệu từ form HTML.

```php
namespace app\models;

use yii\base\Model;

class ContactForm extends Model
{
    public $name;
    public $email;
    public $subject;
    public $body;
}
```


Bạn có thể ghi đè phương thức [[yii\base\Model::attributes()]] để định nghĩa các thuộc tính theo các cách khác. Phương thức nên được trả
tên của thuộc tính trong Model. Ví dụ, lớp [[yii\db\ActiveRecord]] trả về
danh sách tên của các cột liên quan tới các bảng trong CSDL như tên các thuộc tính. Bạn có thể ghi đè các phương thức như
`__get()`, `__set()` để có thể truy cập các thuộc tính như các đối tượng thông thường.


### Nhãn của thuộc tính <span id="attribute-labels"></span>

Mỗi khi cần hiển thị giá trị hoặc nhận dữ liệu cho thuộc tính, bạn cần hiển thị nhãn tương ứng với các thuộc tính
. Ví dụ, với thuộc tính `firstName`, bạn cần hiển thị nhãn `First Name`
nhãn này sẽ thân thiện hơn khi hiển thị tới người dùng với việc nhập dữ liệu và hiện thông báo.

Bạn có thể lấy tên nhãn các thuộc tính quan việc gọi phương thức [[yii\base\Model::getAttributeLabel()]]. Ví dụ,

```php
$model = new \app\models\ContactForm;

// hiển thị "Name"
echo $model->getAttributeLabel('name');
```

Mặc định, nhãn thuộc tính sẽ tự động tạo từ tên của thuộc tính.
Phương thức [[yii\base\Model::generateAttributeLabel()]] sẽ tạo mới các nhãn cho các thuộc tính. Nó sẽ chuyển tên các biến thành các từ mới
qua việc chuyển ký tự đầu tiên thành ký tự in hoa. Ví dụ, `username` thành `Username`,
và `firstName` thành `First Name`.

Nếu bạn không muốn việc tạo các nhản bằng cách tự động, bạn cần ghi đè phương thức [[yii\base\Model::attributeLabels()]]
để mô tả các thuộc tính. Chẳng hạn,

```php
namespace app\models;

use yii\base\Model;

class ContactForm extends Model
{
    public $name;
    public $email;
    public $subject;
    public $body;

    public function attributeLabels()
    {
        return [
            'name' => 'Your name',
            'email' => 'Your email address',
            'subject' => 'Subject',
            'body' => 'Content',
        ];
    }
}
```

Với ứng dụng cần hỗ trợ đa ngôn ngữ, bạn cần dịch lại nhãn của các thuộc tính. Xem trong phương thức
[[yii\base\Model::attributeLabels()|attributeLabels()]] , như sau:

```php
public function attributeLabels()
{
    return [
        'name' => \Yii::t('app', 'Your name'),
        'email' => \Yii::t('app', 'Your email address'),
        'subject' => \Yii::t('app', 'Subject'),
        'body' => \Yii::t('app', 'Content'),
    ];
}
```

Bạn có thể gán nhãn cho các thuộc tính. Chẳng hạn, dựa vào [scenario](#scenarios)của Model
đã được sử dụng , bạn có thể trả về các nhãn khác nhau cho các thuộc tính khác nhau.

> Lưu ý: Chính xác rằng, nhãn của thuộc tính là một phần của [views](structure-views.md). Tuy nhiên việc khai báo các nhãn
  vào Model thường rất tiện lợi, code dễ nhìn và tái sử dụng.


## Kịch bản (Scenarios) <span id="scenarios"></span>

Model thường được sử dụng ở các *kịch bản* khác nhau  . Ví dụ, Model `User` dùng để xử lý việc đăng nhập,
nhưng cũng có thể được dùng ở mục đăng ký. Ở các kịch bản khác nhau, Model có thể được dùng trong các nghiệp vụ
và xử lý logic khác nhau. Ví dụ,thuộc tính `email` có thể được yêu cầu trong mục đăng ký tài khoản mới,
nhưng không được yêu cầu khi xử lý đăng nhập.

Mỗi Model sử dụng thuộc tính [[yii\base\Model::scenario]] để xử lý tuỳ theo kịch bản cần đợc dùng.
Mặc định, Model sẽ hỗ trợ kịch bản là `default`. Xem đoạn mã sau để hiểu 2 cách thiết lập kịch bản cho Model.
setting the scenario of a model:

```php
// kịch bản được thiết lập qua thuộc tính
$model = new User;
$model->scenario = User::SCENARIO_LOGIN;

// kịch bản được thiết lập qua việc cấu hình khởi tạo
$model = new User(['scenario' => User::SCENARIO_LOGIN]);
```

Mặc định, các kịch bản được hỗ trợ bởi model được xác định qua [các nguyên tắc xác minh](#validation-rules) được
mô tả ở Model. Tuy nhiên, bạn có thê tuỳ biến bằng cách ghi đè phương thức [[yii\base\Model::scenarios()]],
như sau:

```php
namespace app\models;

use yii\db\ActiveRecord;

class User extends ActiveRecord
{
    const SCENARIO_LOGIN = 'login';
    const SCENARIO_REGISTER = 'register';

    public function scenarios()
    {
        return [
            self::SCENARIO_LOGIN => ['username', 'password'],
            self::SCENARIO_REGISTER => ['username', 'email', 'password'],
        ];
    }
}
```

> Lưu ý: Như phần trên và ví dụ vừa rồi, lớp Model được kế thừa từ lớp [[yii\db\ActiveRecord]]
  bởi vì lớp [Active Record](db-active-record.md) thường được sử dụng nhiều kịch bản.

The `scenarios()` method returns an array whose keys are the scenario names and values the corresponding
*active attributes*. An active attribute can be [massively assigned](#massive-assignment) and is subject
to [validation](#validation-rules). In the above example, the `username` and `password` attributes are active
in the `login` scenario; while in the `register` scenario, `email` is also active besides `username` and `password`.

The default implementation of `scenarios()` will return all scenarios found in the validation rule declaration
method [[yii\base\Model::rules()]]. When overriding `scenarios()`, if you want to introduce new scenarios
in addition to the default ones, you may write code like the following:

```php
namespace app\models;

use yii\db\ActiveRecord;

class User extends ActiveRecord
{
    const SCENARIO_LOGIN = 'login';
    const SCENARIO_REGISTER = 'register';

    public function scenarios()
    {
        $scenarios = parent::scenarios();
        $scenarios[self::SCENARIO_LOGIN] = ['username', 'password'];
        $scenarios[self::SCENARIO_REGISTER] = ['username', 'email', 'password'];
        return $scenarios;
    }
}
```

The scenario feature is primarily used by [validation](#validation-rules) and [massive attribute assignment](#massive-assignment).
You can, however, use it for other purposes. For example, you may declare [attribute labels](#attribute-labels)
differently based on the current scenario.


## Validation Rules <span id="validation-rules"></span>

When the data for a model is received from end users, it should be validated to make sure it satisfies
certain rules (called *validation rules*, also known as *business rules*). For example, given a `ContactForm` model,
you may want to make sure all attributes are not empty and the `email` attribute contains a valid email address.
If the values for some attributes do not satisfy the corresponding business rules, appropriate error messages
should be displayed to help the user to fix the errors.

You may call [[yii\base\Model::validate()]] to validate the received data. The method will use
the validation rules declared in [[yii\base\Model::rules()]] to validate every relevant attribute. If no error
is found, it will return true. Otherwise, it will keep the errors in the [[yii\base\Model::errors]] property
and return false. For example,

```php
$model = new \app\models\ContactForm;

// populate model attributes with user inputs
$model->attributes = \Yii::$app->request->post('ContactForm');

if ($model->validate()) {
    // all inputs are valid
} else {
    // validation failed: $errors is an array containing error messages
    $errors = $model->errors;
}
```


To declare validation rules associated with a model, override the [[yii\base\Model::rules()]] method by returning
the rules that the model attributes should satisfy. The following example shows the validation rules declared
for the `ContactForm` model:

```php
public function rules()
{
    return [
        // the name, email, subject and body attributes are required
        [['name', 'email', 'subject', 'body'], 'required'],

        // the email attribute should be a valid email address
        ['email', 'email'],
    ];
}
```

A rule can be used to validate one or multiple attributes, and an attribute may be validated by one or multiple rules.
Please refer to the [Validating Input](input-validation.md) section for more details on how to declare
validation rules.

Sometimes, you may want a rule to be applied only in certain [scenarios](#scenarios). To do so, you can
specify the `on` property of a rule, like the following:

```php
public function rules()
{
    return [
        // username, email and password are all required in "register" scenario
        [['username', 'email', 'password'], 'required', 'on' => self::SCENARIO_REGISTER],

        // username and password are required in "login" scenario
        [['username', 'password'], 'required', 'on' => self::SCENARIO_LOGIN],
    ];
}
```

If you do not specify the `on` property, the rule would be applied in all scenarios. A rule is called
an *active rule* if it can be applied in the current [[yii\base\Model::scenario|scenario]].

An attribute will be validated if and only if it is an active attribute declared in `scenarios()` and
is associated with one or multiple active rules declared in `rules()`.


## Massive Assignment <span id="massive-assignment"></span>

Massive assignment is a convenient way of populating a model with user inputs using a single line of code.
It populates the attributes of a model by assigning the input data directly to the [[yii\base\Model::$attributes]]
property. The following two pieces of code are equivalent, both trying to assign the form data submitted by end users
to the attributes of the `ContactForm` model. Clearly, the former, which uses massive assignment, is much cleaner
and less error prone than the latter:

```php
$model = new \app\models\ContactForm;
$model->attributes = \Yii::$app->request->post('ContactForm');
```

```php
$model = new \app\models\ContactForm;
$data = \Yii::$app->request->post('ContactForm', []);
$model->name = isset($data['name']) ? $data['name'] : null;
$model->email = isset($data['email']) ? $data['email'] : null;
$model->subject = isset($data['subject']) ? $data['subject'] : null;
$model->body = isset($data['body']) ? $data['body'] : null;
```


### Safe Attributes <span id="safe-attributes"></span>

Massive assignment only applies to the so-called *safe attributes* which are the attributes listed in
[[yii\base\Model::scenarios()]] for the current [[yii\base\Model::scenario|scenario]] of a model.
For example, if the `User` model has the following scenario declaration, then when the current scenario
is `login`, only the `username` and `password` can be massively assigned. Any other attributes will
be kept untouched.

```php
public function scenarios()
{
    return [
        self::SCENARIO_LOGIN => ['username', 'password'],
        self::SCENARIO_REGISTER => ['username', 'email', 'password'],
    ];
}
```

> Info: The reason that massive assignment only applies to safe attributes is because you want to
  control which attributes can be modified by end user data. For example, if the `User` model
  has a `permission` attribute which determines the permission assigned to the user, you would
  like this attribute to be modifiable by administrators through a backend interface only.

Because the default implementation of [[yii\base\Model::scenarios()]] will return all scenarios and attributes
found in [[yii\base\Model::rules()]], if you do not override this method, it means an attribute is safe as long
as it appears in one of the active validation rules.

For this reason, a special validator aliased `safe` is provided so that you can declare an attribute
to be safe without actually validating it. For example, the following rules declare that both `title`
and `description` are safe attributes.

```php
public function rules()
{
    return [
        [['title', 'description'], 'safe'],
    ];
}
```


### Unsafe Attributes <span id="unsafe-attributes"></span>

As described above, the [[yii\base\Model::scenarios()]] method serves for two purposes: determining which attributes
should be validated, and determining which attributes are safe. In some rare cases, you may want to validate
an attribute but do not want to mark it safe. You can do so by prefixing an exclamation mark `!` to the attribute
name when declaring it in `scenarios()`, like the `secret` attribute in the following:

```php
public function scenarios()
{
    return [
        self::SCENARIO_LOGIN => ['username', 'password', '!secret'],
    ];
}
```

When the model is in the `login` scenario, all three attributes will be validated. However, only the `username`
and `password` attributes can be massively assigned. To assign an input value to the `secret` attribute, you
have to do it explicitly as follows,

```php
$model->secret = $secret;
```


## Data Exporting <span id="data-exporting"></span>

Models often need to be exported in different formats. For example, you may want to convert a collection of
models into JSON or Excel format. The exporting process can be broken down into two independent steps.
In the first step, models are converted into arrays; in the second step, the arrays are converted into
target formats. You may just focus on the first step, because the second step can be achieved by generic
data formatters, such as [[yii\web\JsonResponseFormatter]].

The simplest way of converting a model into an array is to use the [[yii\base\Model::$attributes]] property.
For example,

```php
$post = \app\models\Post::findOne(100);
$array = $post->attributes;
```

By default, the [[yii\base\Model::$attributes]] property will return the values of *all* attributes
declared in [[yii\base\Model::attributes()]].

A more flexible and powerful way of converting a model into an array is to use the [[yii\base\Model::toArray()]]
method. Its default behavior is the same as that of [[yii\base\Model::$attributes]]. However, it allows you
to choose which data items, called *fields*, to be put in the resulting array and how they should be formatted.
In fact, it is the default way of exporting models in RESTful Web service development, as described in
the [Response Formatting](rest-response-formatting.md).


### Fields <span id="fields"></span>

A field is simply a named element in the array that is obtained by calling the [[yii\base\Model::toArray()]] method
of a model.

By default, field names are equivalent to attribute names. However, you can change this behavior by overriding
the [[yii\base\Model::fields()|fields()]] and/or [[yii\base\Model::extraFields()|extraFields()]] methods. Both methods
should return a list of field definitions. The fields defined by `fields()` are default fields, meaning that
`toArray()` will return these fields by default. The `extraFields()` method defines additionally available fields
which can also be returned by `toArray()` as long as you specify them via the `$expand` parameter. For example,
the following code will return all fields defined in `fields()` and the `prettyName` and `fullAddress` fields
if they are defined in `extraFields()`.

```php
$array = $model->toArray([], ['prettyName', 'fullAddress']);
```

You can override `fields()` to add, remove, rename or redefine fields. The return value of `fields()`
should be an array. The array keys are the field names, and the array values are the corresponding
field definitions which can be either property/attribute names or anonymous functions returning the
corresponding field values. In the special case when a field name is the same as its defining attribute
name, you can omit the array key. For example,

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

> Warning: Because by default all attributes of a model will be included in the exported array, you should
> examine your data to make sure they do not contain sensitive information. If there is such information,
> you should override `fields()` to filter them out. In the above example, we choose
> to filter out `auth_key`, `password_hash` and `password_reset_token`.


## Best Practices <span id="best-practices"></span>

Models are the central places to represent business data, rules and logic. They often need to be reused
in different places. In a well-designed application, models are usually much fatter than
[controllers](structure-controllers.md).

In summary, models

* may contain attributes to represent business data;
* may contain validation rules to ensure the data validity and integrity;
* may contain methods implementing business logic;
* should NOT directly access request, session, or any other environmental data. These data should be injected
  by [controllers](structure-controllers.md) into models;
* should avoid embedding HTML or other presentational code - this is better done in [views](structure-views.md);
* avoid having too many [scenarios](#scenarios) in a single model.

You may usually consider the last recommendation above when you are developing large complex systems.
In these systems, models could be very fat because they are used in many places and may thus contain many sets
of rules and business logic. This often ends up in a nightmare in maintaining the model code
because a single touch of the code could affect several different places. To make the model code more maintainable,
you may take the following strategy:

* Define a set of base model classes that are shared by different [applications](structure-applications.md) or
  [modules](structure-modules.md). These model classes should contain minimal sets of rules and logic that
  are common among all their usages.
* In each [application](structure-applications.md) or [module](structure-modules.md) that uses a model,
  define a concrete model class by extending from the corresponding base model class. The concrete model classes
  should contain rules and logic that are specific for that application or module.

For example, in the [Advanced Project Template](https://github.com/yiisoft/yii2-app-advanced/blob/master/docs/guide/README.md), you may define a base model
class `common\models\Post`. Then for the front end application, you define and use a concrete model class
`frontend\models\Post` which extends from `common\models\Post`. And similarly for the back end application,
you define `backend\models\Post`. With this strategy, you will be sure that the code in `frontend\models\Post`
is only specific to the front end application, and if you make any change to it, you do not need to worry if
the change may break the back end application.
