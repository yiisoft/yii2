Model
======

Model là phần trong mô hình [MVC](https://vi.wikipedia.org/wiki/MVC).
Là đối tượng đại diện cho phần dữ liệu, phương thức xử lý và nghiệp vụ logic.

Bạn có thể tạo mới các lớp model bằng việc kế thừa từ lớp [[yii\base\Model]] hoặc các lớp con của nó. Lớp cơ sở
[[yii\base\Model]] hỗ trợ nhiều tính năng như:

* [Thuộc tính (Attributes)](#attributes): đại diện cho các dữ liệu nghiệp vụ và có thể truy cập như các thuộc tính
  hoặc mảng các phần tử;
* [Attribute labels](#attribute-labels): tên hiển thị cho các thuộc tính;
* [Gán nhanh (Massive assignment)](#massive-assignment): hỗ trợ nhập dữ liệu cho thuộc tính trong một bước;
* [Quy tắc xác nhận (Validation rules)](#validation-rules): khai báo các quy tắc và xác thực dữ liệu được nhập vào;
* [Xuất dữ liệu (Data Exporting)](#data-exporting): cho phép xuất dữ liệu dưới dạng mảng hoặc tuỳ chọn khác.

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
[ArrayAccess](https://www.php.net/manual/en/class.arrayaccess.php) và [ArrayIterator](https://www.php.net/manual/en/class.arrayiterator.php)
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
            'name' => 'Tên liên hệ',
            'email' => 'Địa chỉ email',
            'subject' => 'Tiêu đề',
            'body' => 'Nội dung',
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
        'name' => \Yii::t('app', 'Tên liên hệ'),
        'email' => \Yii::t('app', 'Địa chỉ email'),
        'subject' => \Yii::t('app', 'Tiêu đề'),
        'body' => \Yii::t('app', 'Nội dung'),
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

Phương thức `scenarios()` trả về một mảng có chứa các khóa là tên các kịch bản và các giá trị tương ứng là các  
danh sách *thuộc tính được chọn*. An active attribute can be [massively assigned](#massive-assignment) và là đối tượng sẽ được
dùng để [xác thực (validation)](#validation-rules). Chẳng hạn ở ví dụ trên, thuộc tính `username` và `password` sẽ được chọn
ở kịch bản `login`; còn ở kịch bản `register`, sẽ có thêm thuộc tính `email` ngoài 2 thuộc tính `username` và `password`.

Việc triển khai phương thức `scenarios()` mặc định sẻ trả về các kịch bản tìm thấy trong phương thức
[[yii\base\Model::rules()]]. Khi khi đè phương thức `scenarios()`, nếu bạn muốn khai báo các kịch bản mới, ngoài các kịch bản mặc định
in addition to the default ones, bạn có thể viết mã như sau:

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

Xây dựng các kịch bản được dùng vào việc [xác thực](#validation-rules) và [massive attribute assignment](#massive-assignment).
Tuy nhiên, bạn có thể dùng vào mục đích khác. Chẳng hạn, bạn có thể khai báo các [nhãn thuộc tính](#attribute-labels)
khác nhau được dựa trên kịch bản hiện tại.


## Các quy tắc xác nhận (Validation Rules) <span id="validation-rules"></span>

Khi dữ liệu cho model được chuyển lên từ người dùng cuối, dữ liệu này cần được xác thực để chắc chắn rằng dữ liệu này là hợp lệ
 (được gọi là *quy tắc xác nhận*, có thể gọi *business rules*). Ví dụ, cho model `ContactForm`,
bạn muốn tất cả các thuộc tính không được để trống và thuộc tính `email` phải là địa chỉ email hợp lệ.
Nếu các giá trị cho các thuộc tính không được thỏa mãn với các quy tắc xác nhận, các thông báo lỗi sẽ được
được hiển thị để giúp người dùng sửa lỗi.

Bạn có thể gọi phương thức [[yii\base\Model::validate()]] để xác thực các dữ liệu đã nhận. Phương thức sẽ dùng các quy tắc xác nhận
được khai báo ở phương thức [[yii\base\Model::rules()]] để xác thực mọi thuộc tính liên quan. Nếu không có lỗi nào tìm thấy
, sẽ trả về giá trị `true`. Nếu không thì, phương thức sẽ giữ các thông báo lỗi tại thuộc tính [[yii\base\Model::errors]]
và trả kết quả`false`. Ví dụ,

```php
$model = new \app\models\ContactForm;

// gán các thuộc tính của model từ dữ liệu người dùng
$model->attributes = \Yii::$app->request->post('ContactForm');

if ($model->validate()) {
    // tất cả các dữ liệu nhập vào hợp lệ
} else {
    // xác nhận lỗi: biến $errors chứa mảng các nội dung thông báo lỗi
    $errors = $model->errors;
}
```


Các quy tắc xác nhận được gắn vào model, việc ghi đè phương thức [[yii\base\Model::rules()]] cùng với việc trả về
có chứa các thuộc tính an toàn cần được xác thực. Ví dụ sau đây sẽ cho thấy các quy tắc xác nhận được khai báo cho model
`ContactForm`:

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

Mỗi quy tắc được dùng để xác nhận một hoặc nhiều các thuộc tính, và một thuộc tính có thể được xác nhận một hoặc nhiều quy tắc.
Vui lòng tham khảo mục [Xác nhận đầu vào](input-validation.md) để biết thêm chi tiết về cách khai báo các quy tắc xác nhận.

Đôi khi, bạn muốn các quy tắc chỉ được áp dụng chỉ trong một số [kịch bản](#scenarios). Để làm như vậy, bạn có thể
thêm thông tin thuộc tính `on` ở mỗi quy tắc, giống như sau:

```php
public function rules()
{
    return [
        // thuộc tính username, email và password cần được nhập ở kịch bản "register"
        [['username', 'email', 'password'], 'required', 'on' => self::SCENARIO_REGISTER],

        // username và password cần được nhập ở kịch bản "login"
        [['username', 'password'], 'required', 'on' => self::SCENARIO_LOGIN],
    ];
}
```

Nếu bạn không chỉ định thuộc tính `on`, quy tắc sẽ áp dụng trong tất cả các kịch bản. Một quy tắc được gọi
một *quy tắc hoạt động* nếu nó được áp dụng với kịch bản hiện tại [[yii\base\Model::scenario|scenario]].

Một thuộc tính được xác nhận nếu và chỉ nếu nó là thuộc tính được kích hoạt với khai báo tại phương thức `scenarios()` và
được liên kết với một hoặc nhiều quy tắc được khai báo ở phương thức `rules()`.


## Gán nhanh (Massive Assignment) <span id="massive-assignment"></span>

Gán nhanh là cách tiện lợi cho việc nhập dữ liệu vào model từ người dùng với một dòng mã.
Nó nhập vào các thuộc tính của model bằng việc gán dữ liệu nhập vào qua thuộc tính [[yii\base\Model::$attributes]]
. 2 đoạn mã sau hoạt động giống nhau , cả 2 đều lấy dữ liệu trong form gửi lên từ người dùng 
vào các thuộc tính của model `ContactForm`. Nhanh gọn, cách trên, sẽ dùng gán nhanh, mã của bạn trông sạch và ít lỗi hơn cách sau đó:

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


### Thuộc tính an toàn (Safe Attributes) <span id="safe-attributes"></span>

Gán nhanh chỉ gán dữ liệu cho những thuộc tính gọi là  *thuộc tính an toàn (safe attributes)* đó là các thuộc tính được liệt kê trong phương thức
[[yii\base\Model::scenarios()]] cho thuộc tính [[yii\base\Model::scenario|scenario]] của model.
Chẳng hạn, nếu model `User` có các kịch bản mô tả như sau, tiếp đến kịch bản
`login` đang được chọn, thì chỉ thuộc tính `username` và `password` có thể được gán nhanh. Bất kỳ các thuộc tính khác
sẽ được giữ nguyên.

```php
public function scenarios()
{
    return [
        self::SCENARIO_LOGIN => ['username', 'password'],
        self::SCENARIO_REGISTER => ['username', 'email', 'password'],
    ];
}
```

> Thông tin: Lý do việc gán nhanh chỉ gán dữ liệu cho các thuộc tính an toàn là bởi vì bạn muốn kiểm soát
  những thuộc tính có thể được thay đổi bởi người dùng. Chẳng hạn, nếu model `User` 
  có thuộc tính `permission` nhằm xác định các quyền hạn của người dùng, bạn chỉ muốn
  thuộc tính này chỉ được thay đổi bởi quản trị viên thông qua giao diện phụ trợ.

Bởi vì mặc định phương thức [[yii\base\Model::scenarios()]] sẽ trả về tất cả các kịch bản và thuộc tính
nằm trong phương thức [[yii\base\Model::rules()]], nếu bạn không ghi đè phương thức này, có nghĩa là một thuộc tính là an toàn
miễn là có khai báo ở một trong các quy tắc xác nhận.

Vì lý do này, bí danh `safe` được đưa ra bạn có thể khai báo các thuộc tính an toàn
mà không thực sự xác nhận nó. Chẳng hạn, các quy tắc sau đây khai báo thuộc tính `title`
và `description` là thuộc tính an toàn.

```php
public function rules()
{
    return [
        [['title', 'description'], 'safe'],
    ];
}
```


### Thuộc tính không an toàn (Unsafe Attributes) <span id="unsafe-attributes"></span>

Như mô tả trên, khai báo phương thức [[yii\base\Model::scenarios()]] có 2 mục đích: liệt kê thuộc tính cần được xác nhận
, và xác định các thuộc tính là an toàn. Trong một số trường hợp khác, bạn muốn xác nhận thuộc tính nhưng
không muốn đánh dấu là an toàn. bạn có thể thực hiện bằng việc đặt dấu chấm than `!` vào tên thuộc tính
khi khai báo tại phương thức `scenarios()`, giốn như thuộc tính `secret` như sau:

```php
public function scenarios()
{
    return [
        self::SCENARIO_LOGIN => ['username', 'password', '!secret'],
    ];
}
```

Khi model đang ở kịch bản `login`, cả 3 thuộc tính sẽ được xác nhận. Tuy nhiên, chỉ có thuộc tính `username`
và `password` được gán nhanh. Để gán giá trị cho thuộc tính `secret`, bạn
cần được gán trực tiếp như sau,

```php
$model->secret = $secret;
```

Điều tương tự có thể được thực hiện trong phương thức `rules()`:

```php
public function rules()
{
    return [
        [['username', 'password', '!secret'], 'required', 'on' => 'login']
    ];
}
```

Trong trường hợp này các thuộc tính `username`, `password` và `secret` là yêu cầu nhập, nhưng thuộc tính `secret` phải cần được gán trực tiếp.


## Xuất dữ liệu (Data Exporting) <span id="data-exporting"></span>

Các model thường được cần trích xuất ra các định dạng khác nhau. Chẳng hạn, bạn cần chuyển dữ liệu sang của
models sang định dạng JSON hoặc Excel. Quá trình xuất có thể được chia nhỏ thành hai bước độc lập:

- models cần được chuyển sang định dạng mảng;
- các mảng cần được chuyển đổi thành các định dạng cần chuyển.

Bạn chỉ cần tập trung vào bước đầu tiên, bởi vì bước thứ 2 có thể được thực hiện bởi các trình định dạng dữ liệu
, chẳng hạn như [[yii\web\JsonResponseFormatter]].

Các đơn giản nhất để chuyển đổi model sang dạng mảng là sử dụng thuộc tính [[yii\base\Model::$attributes]].
For example,

```php
$post = \app\models\Post::findOne(100);
$array = $post->attributes;
```

Bởi mặc định, thuộc tính [[yii\base\Model::$attributes]] sẽ trả về các giá trị của *tất cả* các thuộc tính
được khai báo trong phương thức [[yii\base\Model::attributes()]].

Còn một cách linh hoạt và tiện lợi hơn trong việc chuyển đổi model sang định dạng mảng là sử dụng phương thức [[yii\base\Model::toArray()]]
. Cách chuyển đổi cũng tương tự như trong cách của thuộc tính [[yii\base\Model::$attributes]]. Tuy nhiên, nó cho phép bạn chọn các dữ liệu
, được gọi là *fields*, được đặt trong mảng kết quả và chúng được định dạng thế nào.
Trong thực tế, đó là cách trích xuất mặc định của các model ở việc phát triển các dịch vụ RESTful Web, như được mô tả trong
mục [Response Formatting](rest-response-formatting.md).


### Các trường (Fields) <span id="fields"></span>

Một trường đơn giản là tên của thành phần thu được nằm trong mảng khi gọi phương thức [[yii\base\Model::toArray()]]
của model.

Mặc định, tên trường sẽ tương đương với tên thuộc tính. Tuy nhiên, bạn có thể thay đổi bằng việc ghi đè
qua phương thức [[yii\base\Model::fields()|fields()]] và/hoặc phương thức [[yii\base\Model::extraFields()|extraFields()]]. Cả 2 phương thức
trả về danh sách các khai báo trường. Các trường được định nghĩa bởi phương thức `fields()` là các trường mặc định, nghĩa là phương thức 
`toArray()` sẽ trả về những trường mặc định. Phương thức `extraFields()` sẽ khai báo thêm các trường bổ sung có thể được trả về
bởi phương thức `toArray()` miễn là bạn chỉ định chugns qua tham số `$expand`. Chẳng hạn,
đoạn mã sau sẽ trả về các trường được định nghĩa trong phương thức `fields()` và 2 trường `prettyName` và `fullAddress`
nếu chúng được định nghĩa trong phương thức `extraFields()`.

```php
$array = $model->toArray([], ['prettyName', 'fullAddress']);
```

Bạn có thể ghi đè phương thức `fields()` để thêm, xóa, cập nhật hoặc định nghĩa lại các trường. Phương thức `fields()`
sẽ trả về dữ liệu dạng mảng. Mảng này có các khóa là tên các trường, và các giá trị của mảng tương ứng
với các trường đã định nghĩa giá trị có thể là tên các thuộc tính/biến hoặc một hàm trả về các giá trị trường tương ứng
. Trong trường hợp đặc biệt khi tên trường giống với tên thuộc tính xác định của nó, bạn có thể bỏ qua khóa mảng. Ví dụ,

```php
// liệt kê rõ ràng các trường, sử dụng tốt nhất khi bạn nắm được các thay đổi
// trong bản CSDL hoặc các thuộc tính của model, không gây ra sự thay đổi của trường (để giữ tương thích với API).
public function fields()
{
    return [
        // tên trường giống với tên thuộc tính
        'id',

        // tên trường là "email", tương ứng với tên thuộc tính là "email_address"
        'email' => 'email_address',

        // tên trường là "name", giá trị được định nghĩa bởi hàm
        'name' => function () {
            return $this->first_name . ' ' . $this->last_name;
        },
    ];
}

// lọc ra một số trường, nên sử dụng khi bạn muốn kế thừa các trường
// thêm vào blacklist một số trường không cần thiết.
public function fields()
{
    $fields = parent::fields();

    // remove fields that contain sensitive information
    unset($fields['auth_key'], $fields['password_hash'], $fields['password_reset_token']);

    return $fields;
}
```

> Cảnh báo: Bởi vì theo mặc định tất cả các thuộc tính của model sẽ liệt kê trong mảng trích xuất, bạn nên
> kiểm tra dữ liệu của bạn để chắc chắn rằng chúng không chứa các thông tin không cần thiết. Nếu có thông tin như vậy,
> bạn nên ghi đè phương thức `fields()` để lọc chúng ra. Tại ví dụ trên, chúng ta chọn
> các trường để lọc ra là `auth_key`, `password_hash` và `password_reset_token`.


## Bài thực hành <span id="best-practices"></span>

Các model là phần trung tâm đại diện cho tầng dữ liệu, chứa các quy tắc và logic. Model thường được tái sử dụng
tại một số nơi khác nhau. Với một ứng dụng được thiết kế tốt, thông thường các model được chú trọng hơn 
[controllers](structure-controllers.md).

Tổng hợp mục, models

* có thể chứa các thuộc tính đại diện cho tầng dữ liệu (business data);
* có thể chứa các quy tắc xác nhận để đảm bảo tính hợp lệ và tính toàn vẹn của dữ liệu;;
* có thể chứa các phương thức tại tầng logic (business logic);
* không nên trực tiếp xử lý các yêu cầu, session, hoặc bất cứ dữ liệu môi trường. Những dữ liệu này nên được tiến hành xử lý
  bởi [controllers](structure-controllers.md) vào model;
* tránh việc nhúng mã HTML hoặc các dữ liệu hiển thị - mã này nên được đặt tại [views](structure-views.md);
* tránh có quá nhiều kịch bản [scenarios](#scenarios) trong một model.

Bạn cần có sự xem xét các đề nghị trên mỗi khi bạn triển khai hệ thống lớn và phức tạp.
Trong các hệ thống này, cácmodel cần được chú trọng bởi vì chúng được sử dụng ở nhiều nơi và có thể chứa nhiều các quy tắc
và các xử lý nghiệp vụ. Điều này có sự ảnh hưởng tại tiến trình bảo trì mỗi thay đổi mã của bạn
có thể ảnh hưởng tới nhiều vị trí khác nhau. Để mã code của bạn dễ được bảo trì hơn,
bạn có thể được thực hiện các chiến lược sau:

* Định nghĩa tập các lớp model cơ sở (base model) lớp này được chia sẻ qua các [ứng dụng](structure-applications.md) hoặc
  [modules](structure-modules.md) khác nhau. Các model này có thể chứa tập các quy tắc và logic có thể được
  dùng rộng rãi ở các lớp cần được sử dụng.
* Tại mỗi [ứng dụng](structure-applications.md) hoặc [module](structure-modules.md) có dùng model,
  ta định nghĩa lớp khung model bằng việc kế thừa từ lớp model cơ sở. Lớp khung model này
  có thể chứa các quy tắc logic được mô tả cụ thể chi ứng dụng hoặc module này.

Ví dụ, với [Mẫu dự án Advanced](https://github.com/yiisoft/yii2-app-advanced/blob/master/docs/guide/README.md), bạn có thể định nghĩa lớp cơ sở model
là `common\models\Post`. Tiếp đến tại ứng dụng front end, bạn định nghĩa lớp khung là
`frontend\models\Post` lớp này kế thừa từ lớp `common\models\Post`. Và tương tự cho ứng dụng back end,
bạn định nghĩa model `backend\models\Post`. Với cách giải quyết này, bạn sẽ chắc chắn rằng mã của bạn tại model `frontend\models\Post`
chỉ dùng cho ứng dụng front end, và nếu bạn thực hiện với bất kỳ thay đổi nào, bạn không cần lo lắng về
việc thay đổi này có ảnh hưởng tới ứng dụng back end.
