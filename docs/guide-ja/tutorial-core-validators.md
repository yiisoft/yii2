コア・バリデータ
================

Yii は、一般的に使われる一連のコア・バリデータを提供しています。コア・バリデータは、主として、`yii\validators` 名前空間の下にあります。
長ったらしいバリデータ・クラス名を使う代りに、*エイリアス* を使って使用するコア・バリデータを指定することが出来ます。
例えば、[[yii\validators\RequiredValidator]] クラスを参照するのに `required` というエイリアスを使うことが出来ます。

```php
public function rules()
{
    return [
        [['email', 'password'], 'required'],
    ];
}
```

[[yii\validators\Validator::builtInValidators]] プロパティがサポートされている全てのコア・バリデータのエイリアスを宣言しています。

以下では、全てのコア・バリデータについて、主な使用方法とプロパティを説明します。


## [[yii\validators\BooleanValidator|boolean]] <span id="boolean"></span>

```php
[
    // データ型にかかわらず、"selected" が 0 または 1 であるかどうかチェック
    ['selected', 'boolean'],

    // "deleted" が boolean 型であり、true または false であるかどうかチェック
    ['deleted', 'boolean', 'trueValue' => true, 'falseValue' => false, 'strict' => true],
]
```

このバリデータは、入力値が真偽値であるかどうかをチェックします。

- `trueValue`: `true` を表す値。デフォルト値は `'1'`。
- `falseValue`: `false` を表す値。デフォルト値は `'0'`。
- `strict`: 入力値の型が `trueValue` と `falseValue` の型と一致しなければならないかどうか。デフォルト値は `false`。


> Note: HTML フォームで送信されたデータ入力値は全て文字列であるため、通常は、
  [[yii\validators\BooleanValidator::strict|strict]] プロパティは `false` のままにすべきです。


## [[yii\captcha\CaptchaValidator|captcha]] <span id="captcha"></span>

```php
[
    ['verificationCode', 'captcha'],
]
```

このバリデータは、通常、[[yii\captcha\CaptchaAction]] および [[yii\captcha\Captcha]] と一緒に使われ、
入力値が [[yii\captcha\Captcha|CAPTCHA]] ウィジェットによって表示された検証コードと同じであることを確認します。

- `caseSensitive`: 検証コードの比較で大文字と小文字を区別するか否か。デフォルト値は `false`。
- `captchaAction`: CAPTCHA 画像を表示する [[yii\captcha\CaptchaAction|CAPTCHA アクション]] に対応する
  [ルート](structure-controllers.md#routes)。デフォルト値は `'site/captcha'`。
- `skipOnEmpty`: 入力値が空のときに検証をスキップできるかどうか。デフォルト値は `false` で、
  入力が必須であることを意味します。


## [[yii\validators\CompareValidator|compare]] <span id="compare"></span>

```php
[
    // "password" 属性の値が "password_repeat" 属性の値と同じであるかどうか検証する
    ['password', 'compare'],

    // 上記と同じだが、比較する属性を明示的に指定
    ['password', 'compare', 'compareAttribute' => 'password_repeat'],

    // "age" が 30 以上であるかどうか検証する
    ['age', 'compare', 'compareValue' => 30, 'operator' => '>=', 'type' => 'number'],
]
```

このバリデータは指定された入力値を他の値と比較し、両者の関係が
`operator` プロパティで指定されたものであることを確認します。

- `compareAttribute`: その値が比較対象となる属性の名前。
  このバリデータが属性を検証するのに使用されるとき、このプロパティのデフォルト値はその属性の名前に接尾辞
  `_repeat` を付けた名前になります。
  例えば、検証される属性が `password` であれば、このプロパティのデフォルト値は `password_repeat` となります。
- `compareValue`: 入力値が比較される定数値（または値を返すクロージャ）。
  このプロパティと `compareAttribute` の両方が指定された場合は、このプロパティが優先されます。
- `operator`: 比較演算子。デフォルト値は `==` で、入力値が `compareAttribute` の値または `compareValue` と等しいことを検証することを意味します。
  次の演算子がサポートされています。
     * `==`: 二つの値が等しいことを検証。厳密でない比較を行う。
     * `===`: 二つの値が等しいことを検証。厳密な比較を行う。
     * `!=`: 二つの値が等しくないことを検証。厳密でない比較を行う。
     * `!==`: 二つの値が等しくないことを検証。厳密な比較を行う。
     * `>`: 検証される値が比較される値よりも大きいことを検証する。
     * `>=`: 検証される値が比較される値よりも大きいか等しいことを検証する。
     * `<`: 検証される値が比較される値よりも小さいことを検証する。
     * `<=`: 検証される値が比較される値よりも小さいか等しいことを検証する。
- `type`: デフォルトの比較タイプは '[[yii\validators\CompareValidator::TYPE_STRING|string]]' (文字列) であり、その場合、値は 1 バイトごとに比較されます。
  数値を比較する場合は、必ず [[yii\validators\CompareValidator::$type|$type]] を '[[yii\validators\CompareValidator::TYPE_NUMBER|number]]' に設定して、
  数値としての比較を有効にして下さい。

### 日付の値を比較する

compare バリデータは、文字列や数値を比較するためにしか使えません。
日付のような値を比較する必要がある場合は、二つの選択肢があります。
日付をある固定値と比較するときは、単に [[yii\validators\DateValidator|date]] バリデータを使って、
その [[yii\validators\DateValidator::$min|$min]] や [[yii\validators\DateValidator::$max|$max]] のプロパティを指定すれば良いでしょう。
フォームに入力された二つの日付、例えば、`fromDate` と `toDate` のフィールドを比較する必要がある場合は、
次のように、compare バリデータと date バリデータを組み合わせて使うことが出来ます。

```php
['fromDate', 'date', 'timestampAttribute' => 'fromDate'],
['toDate', 'date', 'timestampAttribute' => 'toDate'],
['fromDate', 'compare', 'compareAttribute' => 'toDate', 'operator' => '<', 'enableClientValidation' => false],
```

バリデータは指定された順序に従って実行されますので、まず最初に、`fromDate` と `toDate` に入力された値が
有効な日付であることが確認され、有効な日付であった場合は、機械が読める形式に変換されます。
その後に、これらの二つの値が compare バリデータによって比較されます。
現在、date バリデータはクライアント・サイドのバリデーションを提供していませんので、これはサーバ・サイドでのみ動作します。
そのため、compare バリデータについても、[[yii\validators\CompareValidator::$enableClientValidation|$enableClientValidation]] は
`false` に設定されています。


## [[yii\validators\DateValidator|date]] <span id="date"></span>

[[yii\validators\DateValidator|date]] バリデータには三つの異なる
ショートカットがあります。

```php
[
    [['from_date', 'to_date'], 'date'],
    [['from_datetime', 'to_datetime'], 'datetime'],
    [['some_time'], 'time'],
]
```

このバリデータは、入力値が正しい書式の date、time、または datetime であるかどうかをチェックします。
オプションとして、入力値を UNIX タイムスタンプ (または、その他、機械による読み取りが可能な形式) に変換して、
[[yii\validators\DateValidator::timestampAttribute|timestampAttribute]] によって指定された属性に保存することも出来ます。

- `format`: 検証される値が従っているべき日付/時刻の書式。
  これには [ICU manual](https://unicode-org.github.io/icu/userguide/format_parse/datetime/#datetime-format-syntax)
  で記述されている日付/時刻のパターンを使うことが出来ます。
  あるいは、PHP の `Datetime` クラスによって認識される書式に接頭辞 `php:` を付けた文字列でも構いません。
  サポートされている書式については、<https://www.php.net/manual/ja/datetime.createfromformat.php> を参照してください。
  このプロパティが設定されていないときは、`Yii::$app->formatter->dateFormat` の値を取ります。

- `timestampAttribute`: このバリデータが、入力された日付/時刻から変換した UNIX タイムスタンプを代入することが出来る属性の名前。
  これは、検証される属性と同じ属性であってもかまいません。
  その場合は、元の値は検証実行後にタイムスタンプで上書きされます。
  [DatePicker で日付の入力を扱う](https://github.com/yiisoft/yii2-jui/blob/master/docs/guide-ja/topics-date-picker.md) に使用例がありますので、参照してください。

  バージョン 2.0.4 以降では、[[yii\validators\DateValidator::$timestampAttributeFormat|$timestampAttributeFormat]] と
[[yii\validators\DateValidator::$timestampAttributeTimeZone|$timestampAttributeTimeZone]] を使って、
この属性に対するフォーマットとタイム・ゾーンを指定することが出来ます。

`timestampAttribute` を使う場合、入力値が UNIX タイムスタンプに変換されること、そして、UNIX タイムスタンプは定義により UTC であることに注意して下さい。
すなわち、[[yii\validators\DateValidator::timeZone|入力のタイム・ゾーン]] から UTC への変換が実行されます。
（この動作は、2.0.39 以降では、[[yii\validators\DateValidator::$defaultTimeZone|$defaultTimeZone]] を設定して変更することが出来ます）

- バージョン 2.0.4 以降では、タイムスタンプの [[yii\validators\DateValidator::$min|最小値]] または
  [[yii\validators\DateValidator::$max|最大値]] を指定することも出来ます。

入力が必須でない場合には、date バリデータに加えて、default バリデータ (デフォルト値フィルタ) を追加すれば、
空の入力値が `null` として保存されることを保証することが出来ます。そうしないと、データベースに `0000-00-00` という日付が保存されたり、
デート・ピッカーの入力フィールドが `1970-01-01` になったりしてしまいます。

```php
[
    [['from_date', 'to_date'], 'default', 'value' => null],
    [['from_date', 'to_date'], 'date'],
]
```

## [[yii\validators\DefaultValueValidator|default]] <span id="default"></span>

```php
[
    // 空のときは "age" を null にする
    ['age', 'default', 'value' => null],

    // 空のときは "country" を "USA" にする
    ['country', 'default', 'value' => 'USA'],

    // 空のときは "from" と "to" に今日から三日後・六日後の日付を入れる
    [['from', 'to'], 'default', 'value' => function ($model, $attribute) {
        return date('Y-m-d', strtotime($attribute === 'to' ? '+3 days' : '+6 days'));
    }],
]
```

このバリデータはデータを検証しません。
その代りに、検証される属性が空のときに、その属性にデフォルト値を割り当てます。

- `value`: デフォルト値、または、デフォルト値をコールバックとして返すクロージャ。
  検証される属性が空のときにこの値が割り当てられます。クロージャのシグニチャは、次のものでなければなりません。

```php
function foo($model, $attribute) {
    // ... $value を計算 ...
    return $value;
}
```

> Info: 値が空であるか否かを決定する方法については、独立したトピックとして、[空の入力値を扱う](input-validation.md#handling-empty-inputs) のセクションでカバーされています。
  データベース・スキーマによるデフォルト値は、モデルの [loadDefaultValues()](db-active-record.md#default-attribute-values)
  によってロードすることが出来ます。


## [[yii\validators\NumberValidator|double]] <span id="double"></span>

```php
[
    // "salary" が実数であるかどうかチェック
    ['salary', 'double'],
]
```

このバリデータは、入力値が実数値であるかどうかをチェックします。[number](#number) バリデータと等価です。

- `max`: 上限値 (その値を含む)。設定されていない場合は、バリデータが上限値をチェックしないことを意味します。
- `min`: 下限値 (その値を含む)。設定されていない場合は、バリデータが下限値をチェックしないことを意味します。


## [[yii\validators\EachValidator|each]] <span id="each"></span>

> Info: このバリデータは、バージョン 2.0.4 以降で利用できます。

```php
[
    // 全てのカテゴリ ID が整数であるかどうかチェックする
    ['categoryIDs', 'each', 'rule' => ['integer']],
]
```

このバリデータは配列の属性に対してのみ働きます。
これは、配列の *全ての* 要素が指定された検証規則による検証に成功するかどうかを調べるものです。
上の例では、`categoryIDs` 属性は配列を値として取らなければならず、配列の各要素は `integer` の検証規則によって検証されることになります。

- `rule`: 検証規則を指定する配列。配列の最初の要素がバリデータのクラス名かエイリアスを指定します。
  配列の残りの「名前・値」のペアが、バリデータ・オブジェクトを構成するのに使われます。
- `allowMessageFromRule`: 埋め込まれた検証規則によって返されるエラー・メッセージを使うかどうか。
  デフォルト値は `true` です。これが `false` の場合は、`message` をエラー・メッセージとして使います。

> Note: 属性が配列でない場合は、検証が失敗したと見なされ、
  `message` がエラー・メッセージとして返されます。


## [[yii\validators\EmailValidator|email]] <span id="email"></span>

```php
[
    // "email" が有効なメール・アドレスであるかどうかチェック
    ['email', 'email'],
]
```

このバリデータは、入力値が有効なメール・アドレスであるかどうかをチェックします。

- `allowName`: メール・アドレスに表示名 (例えば、`John Smith <john.smith@example.com>`) を許容するか否か。デフォルト値は `false`。
- `checkDNS`: メールのドメインが存在して A または MX レコードを持っているかどうかをチェックするか否か。
  このチェックは、メール・アドレスが実際には有効なものでも、一時的な DNS の問題によって失敗する場合があることに注意してください。
  デフォルト値は `false`。
- `enableIDN`: 検証のプロセスが IDN (国際化ドメイン名) を考慮に入れるか否か。
  デフォルト値は `false`。
  IDN の検証を使用するためには、`intl` PHP 拡張をインストールして有効化する必要があることに注意してください。そうしないと、例外が投げられます。


## [[yii\validators\ExistValidator|exist]] <span id="exist"></span>

```php
[
    // a1 の入力値が a1 のカラムに存在する必要がある
    // すなわち、a1 = 1 は、"a1" カラムに 1 の値が存在する場合に有効
    ['a1', 'exist'],
    // 以下と同義
    ['a1', 'exist', 'targetAttribute' => 'a1'],
    ['a1', 'exist', 'targetAttribute' => ['a1' => 'a1']],

    // a1 の入力値が a2 のカラムに存在する必要がある
    // すなわち、a1 = 2 は、"a2" カラムに 2 の値が存在する場合に有効
    ['a1', 'exist', 'targetAttribute' => 'a2'],
    // 以下と同義
    ['a1', 'exist', 'targetAttribute' => ['a1' => 'a2']],

    // a2 の入力値が a2 のカラムに存在する必要がある。a1 がエラー・メッセージを受ける
    // すなわち、a2 = 2 は、"a2" カラムに 2 の値が存在する場合に有効
    ['a1', 'exist', 'targetAttribute' => ['a2']],
    // 以下と同義
    ['a1', 'exist', 'targetAttribute' => ['a2' => 'a2']],

    // a1 と a2 の両方が存在する必要がある。エラーの無い最初の属性がエラー・メッセージを受け取る
    // すなわち、a1 = 3, a2 = 4 は、"a1" カラムに 3, "a2" カラムに 4 が存在する場合に有効
    [['a1', 'a2'], 'exist', 'targetAttribute' => ['a1', 'a2']],
    // 以下と同義
    [['a1', 'a2'], 'exist', 'targetAttribute' => ['a1' => 'a1', 'a2' => 'a2']],

    // a1 と a2 の両方が存在する必要がある。a1 のみがエラー・メッセージを受け取る
    // すなわち、a1 = 5, a2 = 6 は、"a1" カラムに 5, "a2" カラムに 6 が存在する場合に有効
    ['a1', 'exist', 'targetAttribute' => ['a1', 'a2']],
    // 以下と同義
    ['a1', 'exist', 'targetAttribute' => ['a1' => 'a1', 'a2' => 'a2']],

    // a2 の値が a2 のカラム、a1 の値が a3 のカラムに存在する必要がある。a1 がエラー・メッセージを受け取る
    // すなわち、a1 = 7, a2 = 8 は、"a3" カラムに 7, "a2" カラムに 8 が存在する場合に有効
    ['a1', 'exist', 'targetAttribute' => ['a2', 'a1' => 'a3']],
    // 以下と同義
    ['a1', 'exist', 'targetAttribute' => ['a2' => 'a2', 'a1' => 'a3']],

    // a1 が存在する必要がある。a1 が配列である場合は、その全ての要素が存在する必要がある
    ['a1', 'exist', 'allowArray' => true],
    // すなわち、a1 = 9 は、"a1" カラムに 9 が存在する場合に有効
    //           a1 = [9, 10] は、"a1" カラムに 9 と 10 が存在する場合に有効

    // type_id が ProductType クラスで定義されているテーブルの id カラムに存在する必要がある
    // すなわち、type_id = 1 は ProductType のテーブルの "id" カラムに 1 が存在する場合に有効
    ['type_id', 'exist', 'targetClass' => ProductType::class, 'targetAttribute' => ['type_id' => 'id']],    
    
    // 同上。定義済みの "type" リレーションを使用。
    ['type_id', 'exist', 'targetRelation' => 'type'],
]
```

このバリデータは、入力値が [アクティブ・レコード](db-active-record.md) の属性によって表されるテーブルのカラムに存在するかどうかをチェックします。
`targetAttribute` を使って [アクティブ・レコード](db-active-record.md) の属性を指定し、
`targetClass` によって対応するクラスを指定することが出来ます。
これらを指定しない場合は、検証されるモデルの属性とクラスが使用されます。

このバリデータは、一つまたは複数のカラムに対する検証に使用することが出来ます
(複数のカラムに対する検証の場合は、それらの属性の組み合せが存在しなければならないことを意味します)。
同時に複数のカラムをチェックして（例えば `['a1', 'a2']`）バリデーションが失敗したときに、`skipOnError` が `true` に設定されている場合は、
先行するエラーが無い最初の属性だけが新しいエラー・メッセージを受け取ります。

- `targetClass`: 検証される入力値を探すために使用される [アクティブ・レコード](db-active-record.md) クラスの名前。
  設定されていない場合は、現在検証されているモデルのクラスが使用されます。
- `targetAttribute`: `targetClass` において、入力値の存在を検証するために使用される属性の名前。
  設定されていない場合は、現在検証されている属性の名前が使用されます。
  複数のカラムの存在を同時に検証するために配列を使うことが出来ます。
  配列の値は存在を検証するのに使用される属性であり、配列のキーは入力値が検証される属性です。
  キーと値が同じ場合は、値だけを指定することが出来ます。
  検証されるモデルが ModelA であり、検証に使用されるモデルが ModelB であるとすると、
  下記のように `targetAttribute` を構成することが出来ます。
    - `null` => ModelA の現在検証されている属性の値が ModelB の同名の属性の保存されている値に対してチェックされる
    - `'a'` => ModelA の現在検証されている属性の値が ModelB の属性 "a" の保存されている値に対してチェックされる
    - `['a']` => ModelA の属性 "a" の値が ModelB の属性 "a" の保存されている値に対してチェックされる
    - `['a' => 'a']` => 同上
    - `['a', 'b']` => ModelA の属性 "a" の値が ModelB の属性 "a" の保存されている値に対してチェックされ、
    同時に、ModelA の属性 "b" の値が ModelB の属性 "b" の保存されている値に対してチェックされる
    - `['a' => 'b']` => ModelA の属性 "a" の値が ModelB の属性 "b" の保存されている値に対してチェックされる
- `targetRelation`: バージョン 2.0.14 以降は簡便な `targetRelation` 属性が使えます。これは指定されたリレーションの定義によって `targetClass` と `targetAttribute` の属性をオーバーライドするものです。
- `filter`: 入力値の存在をチェックするのに使用される DB クエリに適用される追加のフィルタ。
  これには、追加のクエリ条件を表現する文字列または配列を使うことが出来ます (クエリ条件の書式については、[[yii\db\Query::where()]] を参照してください)。
  または、`function ($query)` というシグニチャを持つ無名関数でも構いません。
  `$query` は関数の中で修正できる [[yii\db\Query|Query]] オブジェクトです。
- `allowArray`: 入力値が配列であることを許容するか否か。デフォルト値は `false`。
  このプロパティが `true` で入力値が配列であった場合は、配列の全ての要素がターゲットのカラムに存在しなければなりません。
  `targetAttribute` を配列で指定して複数のカラムに対して検証しようとしている場合は、このプロパティを `true` に設定することが出来ないことに注意してください。


## [[yii\validators\FileValidator|file]] <span id="file"></span>

```php
[
    // "primaryImage" が PNG、JPG、または GIF 形式のアップロードされた
    // 画像ファイルであり、ファイルサイズが 1MB 以下であるかどうかチェック
    ['primaryImage', 'file', 'extensions' => ['png', 'jpg', 'gif'], 'maxSize' => 1024*1024],
]
```

このバリデータは、入力値がアップロードされた有効なファイルであるかどうかをチェックします。

- `extensions`: アップロードを許可されるファイル名拡張子のリスト。
  リストは、配列、または、空白かカンマで区切られたファイル名拡張子からなる文字列 (例えば、"gif, jpg") で指定することが出来ます。
  拡張子名は大文字と小文字を区別しません。
  デフォルト値は `null` であり、すべてのファイル名拡張子が許可されることを意味します。
- `mimeTypes`: アップロードを許可されるファイルの MIME タイプのリスト。
  リストは、配列、または、空白かカンマで区切られたファイルの MIME タイプからなる文字列 (例えば、"image/jpeg, image/png") で指定することが出来ます。
  特殊文字 `*` によるワイルドカードのマスクを使って、一群の MIME タイプに一致させることも出来ます。
  例えば `image/*` は、`image/` で始まる全ての MIME タイプ (`image/jpeg`, `image/png` など) を通します。
  MIME タイプ名は大文字と小文字を区別しません。デフォルト値は `null` であり、すべての MIME タイプが許可されることを意味します。
  MIME タイプの詳細については、[一般的なメディア・タイプ](https://en.wikipedia.org/wiki/Media_type) を参照してください。
- `minSize`: アップロードされるファイルに要求される最小限のバイト数。デフォルト値は `null` であり、下限値が無いことを意味します。
- `maxSize`: アップロードされるファイルに許可される最大限のバイト数。デフォルト値は `null` であり、上限値が無いことを意味します。
- `maxFiles`: 指定された属性が保持しうる最大限のファイル数。
  デフォルト値は 1 であり、入力値がアップロードされた一つだけのファイルでなければならないことを意味します。
  この値が 2 以上である場合は、入力値は最大で `maxFiles` 数のアップロードされたファイルからなる配列でなければなりません。
- `checkExtensionByMimeType`: ファイルの MIME タイプでファイル拡張子をチェックするか否か。
  MIME タイプのチェックから導かれる拡張子がアップロードされたファイルの拡張子と違う場合に、そのファイルは無効であると見なされます。
  デフォルト値は `true` であり、そのようなチェックが行われることを意味します。

`FileValidator` は [[yii\web\UploadedFile]] と一緒に使用されます。
ファイルのアップロードおよびアップロードされたファイルの検証の実行に関する完全な説明は、[ファイルをアップロードする](input-file-upload.md) のセクションを参照してください。


## [[yii\validators\FilterValidator|filter]] <span id="filter"></span>

```php
[
    // "username" と "email" の入力値をトリムする
    [['username', 'email'], 'filter', 'filter' => 'trim', 'skipOnArray' => true],

    // "phone" の入力値を正規化する
    ['phone', 'filter', 'filter' => function ($value) {
        // 電話番号の入力値をここで正規化する
        return $value;
    }],
    
    // 関数 "normalizePhone" を使って "phone" の入力値を正規化する
    ['phone', 'filter', 'filter' => [$this, 'normalizePhone']],
    
    public function normalizePhone($value) {
        return $value;
    }
]
```

このバリデータはデータを検証しません。
代りに、入力値にフィルタを適用して、それを検証される属性に書き戻します。

- `filter`: フィルタを定義する PHP コールバック。これには、グローバル関数の名前、無名関数などを指定することが出来ます。
  関数のシグニチャは ``function ($value) { return $newValue; }` でなければなりません。このプロパティは必須項目です。
- `skipOnArray`: 入力値が配列である場合にフィルタをスキップするか否か。デフォルト値は `false`。
  フィルタが配列の入力を処理できない場合は、このプロパティを `true` に設定しなければなりません。そうしないと、
  何らかの PHP エラーが生じ得ます。

> Tip: 入力値をトリムしたい場合は、[trim](#trim) バリデータを直接使うことが出来ます。

> Tip: `filter` のコールバックに期待されるシグニチャを持つ PHP 関数が多数存在します。
> 例えば、([intval](https://www.php.net/manual/ja/function.intval.php) や [boolval](https://www.php.net/manual/ja/function.boolval.php) 
> などを使って) 型キャストを適用し、属性が特定の型になるように保証したい場合は、
> それらの関数をクロージャで包む必要はなく、単にフィルタの関数名を指定するだけで十分です。
>
> ```php
> ['property', 'filter', 'filter' => 'boolval'],
> ['property', 'filter', 'filter' => 'intval'],
> ```


## [[yii\validators\ImageValidator|image]] <span id="image"></span>

```php
[
    // "primaryImage" が適切なサイズの有効な画像であることを検証
    ['primaryImage', 'image', 'extensions' => 'png, jpg',
        'minWidth' => 100, 'maxWidth' => 1000,
        'minHeight' => 100, 'maxHeight' => 1000,
    ],
]
```

このバリデータは、入力値が有効な画像ファイルであるかどうかをチェックします。
これは [file](#file) バリデータを拡張するものであり、従って、そのプロパティの全てを継承しています。
それに加えて、画像の検証の目的に特化した次のプロパティをサポートしています。

- `minWidth`: 画像の幅の最小値。デフォルト値は `null` であり、下限値がないことを意味します。
- `maxWidth`: 画像の幅の最大値。デフォルト値は `null` であり、上限値がないことを意味します。
- `minHeight`: 画像の高さの最小値。デフォルト値は `null` であり、下限値がないことを意味します。
- `maxHeight`: 画像の高さの最大値。デフォルト値は `null` であり、上限値がないことを意味します。

## [[yii\validators\IpValidator|ip]] <span id="ip"></span>
```php
[
    // "ip_address" が有効な IPv4 または IPv6 アドレスであることを検証
    ['ip_address', 'ip'],

    // "ip_address" が有効な IPv6 アドレスまたはサブネットであることを検証
    // 値は完全な IPv6 記法に展開される
    ['ip_address', 'ip', 'ipv4' => false, 'subnet' => null, 'expandIPv6' => true],

    // "ip_address" が有効な IPv4 または IPv6 アドレスであることを検証
    // 先頭に否定文字 `!` を置くことを許可
    ['ip_address', 'ip', 'negation' => true],
]
```

このバリデータは属性の値が有効な IPv4/IPv6 アドレスまたはサブネットであることを検証します。
正規化または IPv6 展開が有効にされた場合は、属性の値を変更することも出来ます。

バリデータは以下の構成オプションを持っています。

- `ipv4`: 検証の対象となる値が IPv4 アドレスであってよいか否か。デフォルト値は `true`。
- `ipv6`: 検証の対象となる値が IPv6 アドレスであってよいか否か。デフォルト値は `true`。
- `subnet`: アドレスが `192.168.10.0/24` のような CIDR サブネットを持つ IP であってよいか否か。
     * `true` - サブネットが必要。CIDR の無いアドレスは却下されます
     * `false` - アドレスは CIDR を伴ってはいけません
     * `null` - CIDR は有っても無くても構いません

    デフォルト値は `false`。
- `normalize`: CIDR を持たないアドレスに、最も短い (IPv4 では 32、IPv6 では 128) CIDR プレフィクスを追加するか否か。
  `subnet` が `false` 以外の場合にのみ動作します。例えば、
    * `10.0.1.5` は `10.0.1.5/32` に正規化され、
    * `2008:db0::1` は `2008:db0::1/128` に正規化されます

    デフォルト値は `false`。
- `negation`: 検証の対象となるアドレスが先頭に否定文字 `!` を持つことが出来るか否か。デフォルト値は `false`。
- `expandIPv6`: IPv6 アドレスを完全な記法に展開するか否か。
  例えば、`2008:db0::1` は `2008:0db0:0000:0000:0000:0000:0000:0001` に展開されます。デフォルト値は `false`。
- `ranges`: 許容または禁止される IPv4 または IPv6 の範囲の配列。

    配列が空の場合、またはこのオプションが設定されていない場合は、全ての IP アドレスが許容されます。
    そうでない場合は、最初に合致するものが見つかるまで、規則が順番にチェックされます。
    どの規則にも合致しなかった場合、その IP アドレスは禁止されます。
    
    例えば、
    ```php
    [
         'client_ip', 'ip', 'ranges' => [
             '192.168.10.128'
             '!192.168.10.0/24',
             'any' // 他の IP アドレスは全て許容
         ]
    ]
    ```
この例では、`192.168.10.0/24` のサブネットを除いて、全ての IPv4 および IPv6 アドレスが許容されます。
IPv4 アドレス `192.168.10.128` も、制約の前にリストされているため、同様に許容されます。
- `networks`: `ranges` で使用する事が出来るネットワークのエイリアスの配列。配列の形式は、
    * キー - エイリアス名
    * 値 - 文字列の配列。文字列は、範囲、IP アドレス、または、他のエイリアスとすることが出来ます。
      また、文字列は (`negation` オプションとは独立に) `!` によって否定することが出来ます。

    デフォルトで、次のエイリアスが定義されています。
    
    * `*`: `any`
    * `any`: `0.0.0.0/0, ::/0`
    * `private`: `10.0.0.0/8, 172.16.0.0/12, 192.168.0.0/16, fd00::/8`
    * `multicast`: `224.0.0.0/4, ff00::/8`
    * `linklocal`: `169.254.0.0/16, fe80::/10`
    * `localhost`: `127.0.0.0/8', ::1`
    * `documentation`: `192.0.2.0/24, 198.51.100.0/24, 203.0.113.0/24, 2001:db8::/32`
    * `system`: `multicast, linklocal, localhost, documentation`

> Info: このバリデータは、バージョン 2.0.7 以降で利用することが出来ます。

## [[yii\validators\RangeValidator|in]] <span id="in"></span>

```php
[
    // "level" が 1, 2 または 3 であるかどうかチェック
    ['level', 'in', 'range' => [1, 2, 3]],
]
```

このバリデータは、入力値が所与の値のリストにあるかどうかをチェックします。

- `range`: 与えられた値のリスト。この中に、入力値がなければならない。
- `strict`: 入力値と所与の値の比較が厳密でなければならない (型と値の両方が同じでなければならない) かどうか。
  デフォルト値は `false`。
- `not`: 検証結果を反転すべきか否か。デフォルト値は `false`。
  このプロパティが `true` に設定されているときは、入力値が所与の値のリストにない場合に検証が成功したとされます。
- `allowArray`: 入力値が配列であることを許可するかどうか。
  このプロパティが `true` であるときに、入力値が配列である場合は、配列の全ての要素が所与の値のリストにある必要があり、そうでなければ検証は失敗します。


## [[yii\validators\NumberValidator|integer]] <span id="integer"></span>

```php
[
    // "age" が整数であるかどうかチェック
    ['age', 'integer'],
]
```

このバリデータは入力値が整数であるかどうかをチェックします。

- `max`: 上限値 (その値を含む)。設定されていないときは、バリデータは上限をチェックしません。
- `min`: 下限値 (その値を含む)。設定されていないときは、バリデータは下限をチェックしません。


## [[yii\validators\RegularExpressionValidator|match]] <span id="match"></span>

```php
[
    // "username" が英字から始まり、英字、数字、アンダーバーだけで構成されているかどうかチェック
    ['username', 'match', 'pattern' => '/^[a-z]\w*$/i']
]
```

このバリデータは、入力値が指定された正規表現に一致するかどうかをチェックします。

- `pattern`: 入力値が一致すべき正規表現。このプロパティを設定することは必須です。
  そうしないと、例外が投げられます。
- `not`: 検証結果を反転すべきかどうか。
  デフォルト値は false で、入力値がパターンに一致したときにだけ検証が成功することを意味します。
  このプロパティが true に設定されているときは、入力値がパターンに一致しない場合にだけ検証が成功したと見なされます。


## [[yii\validators\NumberValidator|number]] <span id="number"></span>

```php
[
    // "salary" が数値であるかどうかチェック
    ['salary', 'number'],
]
```

このバリデータは、入力値が数値であるかどうかをチェックします。[double](#double) バリデータと等価です。

- `max`: 上限値 (その値を含む)。設定されていないときは、バリデータは上限をチェックしません。
- `min`: 下限値 (その値を含む)。設定されていないときは、バリデータは下限をチェックしません。


## [[yii\validators\RequiredValidator|required]] <span id="required"></span>

```php
[
    // "username" と "password" がともに空ではないことをチェックする
    [['username', 'password'], 'required'],
]
```

このバリデータは、入力値が提供されており、空ではないことをチェックします。

- `requiredValue`: 入力値として要求される値。このプロパティが設定されていない場合は、入力値が空ではいけないことを意味します。
- `strict`: 値を検証するときに、データ型をチェックするかどうか。デフォルト値は `false`。
  `requiredValue` が設定されていない場合、このプロパティが `true` であるときは、バリデータは入力値が厳密な意味で `null` であるかどうかをチェックします。
  一方、このプロパティが `false` であるときは、値が空か否かの判断に緩い規則を使います。
  `requiredValue` が設定されている場合、このプロパティが `true` であるときは、
入力値と `requiredValue` を比較するときに型のチェックを行います。

> Info: 値が空であるか否かを決定する方法については、独立したトピックとして、
> [空の入力値を扱う](input-validation.md#handling-empty-inputs) のセクションでカバーされています。


## [[yii\validators\SafeValidator|safe]] <span id="safe"></span>

```php
[
    // "description" を安全な属性としてマーク
    ['description', 'safe'],
]
```

このバリデータは検証を実行しません。
その代りに、このバリデータは、属性を [安全な属性](structure-models.md#safe-attributes) としてマークするために使われます。


## [[yii\validators\StringValidator|string]] <span id="string"></span>

```php
[
    // "username" が、長さが 4 以上 24 以下の文字列であるかどうかチェック
    ['username', 'string', 'length' => [4, 24]],
]
```

このバリデータは、入力値が一定の長さを持つ有効な文字列であるかどうかをチェックします。

- `length`: 検証される入力文字列の長さの制限を指定します。
  これは、次のいずれかの形式で指定することが出来ます。
     * 一つの整数: 文字列がちょうどその長さでなければならない、その長さ。
     * 一つの要素を持つ配列: 入力文字列の長さの最小値 (例えば、`[8]`)。これは `min` を上書きします。
     * 二つの要素を持つ配列: 入力文字列の長さの最小値と最大値 (例えば、`[8, 128]`)。
       これは `min` と `max` の両方を上書きします。
- `min`: 入力文字列の長さの最小値。設定されていない時は、長さの下限値がないことを意味します。
- `max`: 入力文字列の長さの最大値。設定されていない時は、長さの上限値がないことを意味します。
- `encoding`: 検証される入力文字列の文字エンコーディング。設定されていない時は、
  アプリケーションの [[yii\base\Application::charset|charset]] の値が使われ、デフォルトでは `UTF-8` となります。


## [[yii\validators\FilterValidator|trim]] <span id="trim"></span>

```php
[
    // "username" と "email" の前後にあるホワイトスペースをトリムする
    [['username', 'email'], 'trim'],
]
```

このバリデータはデータの検証を実行しません。その代りに、入力値の前後にあるホワイト・スペースをトリムします。
入力値が配列であるときは、このバリデータによって無視されることに注意してください。


## [[yii\validators\UniqueValidator|unique]] <span id="unique"></span>

```php
[
    // a1 の入力値が a1 のカラムにおいてユニークである必要がある
    // すなわち、a1 = 1 は、"a1" カラムに 1 の値が存在しない場合に有効
    ['a1', 'unique'],
    // 以下と同義
    ['a1', 'unique', 'targetAttribute' => 'a1'],
    ['a1', 'unique', 'targetAttribute' => ['a1' => 'a1']],

    // a1 の入力値がユニークである必要がある。ただし a2 のカラムが a1 の入力値のユニークネスのチェックに用いられる
    // すなわち、a1 = 2 は、"a2" カラムに 2 の値が存在しない場合に有効
    ['a1', 'unique', 'targetAttribute' => 'a2'],
    // 以下と同義
    ['a1', 'unique', 'targetAttribute' => ['a1' => 'a2']],

    // a1 と a2 の両方がユニークである必要がある。両者がともにエラー・メッセージを受け取る
    // すなわち、a1 = 3, a2 = 4 は、"a1" カラムに 3 の値が存在せず、同時に、"a2" カラムに 4 の値が存在しない場合に有効
    [['a1', 'a2'], 'unique', 'targetAttribute' => ['a1', 'a2']],
    // 以下と同義
    [['a1', 'a2'], 'unique', 'targetAttribute' => ['a1' => 'a1', 'a2' => 'a2']],

    // a1 と a2 の両方がユニークである必要がある。a1 のみがエラー・メッセージを受け取る
    ['a1', 'unique', 'targetAttribute' => ['a1', 'a2']],

    // a2 の値が a2 のカラム、a1 の値が a3 のカラムにおいてユニークである必要がある。a1 がエラー・メッセージを受け取る
    // すなわち、a1 = 5, a2 = 6 は、"a3" カラムに 5 の値が存在せず、同時に、"a2" カラムに 6 の値が存在しない場合に有効
    ['a1', 'unique', 'targetAttribute' => ['a2', 'a1' => 'a3']],
    
    // type_id が ProductType クラスで定義されているテーブルの "id" カラムにおいてユニークである必要がある
    // すなわち、type_id = 1 は ProductType のテーブルの "id" カラムに 1 が存在しない場合に有効
    ['type_id', 'unique', 'targetClass' => ProductType::class, 'targetAttribute' => 'id'],
]
```

このバリデータは、入力値がテーブルのカラムにおいてユニークであるかどうかをチェックします。このバリデータは [アクティブ・レコード](db-active-record.md) モデルの属性に対してのみ働きます。
一つのカラムに対する検証か、複数のカラムに対する検証か、どちらかをサポートします。
同時に複数のカラムをチェックするバリデーション（例えば上記の `['a1', 'a2']` ）が失敗したときに、
`skipOnError` が `true` に設定されている場合は、先行するエラーが無い最初の属性のみが新しいエラー・メッセージを受け取ります。

- `targetClass`: 検証される入力値を探すために使用される [アクティブ・レコード](db-active-record.md) クラスの名前。
  設定されていない場合は、現在検証されているモデルのクラスが使用されます。
- `targetAttribute`: `targetClass` において、入力値がユニークであることを検証するために使用される属性の名前。
  設定されていない場合は、現在検証されている属性の名前が使用されます。
  複数のカラムのユニーク性を同時に検証するために配列を使うことが出来ます。
  配列の値はユニーク性を検証するのに使用される属性であり、配列のキーはその入力値が検証される属性です。
  キーと値が同じ場合は、値だけを指定することが出来ます。
  検証されるモデルが ModelA であり、検証に使用されるモデルが ModelB であるとすると、
  下記のように `targetAttribute` を構成することが出来ます。
    - `null` => ModelA の現在検証されている属性の値が ModelB の同名の属性の保存されている値に対してチェックされる
    - `'a'` => ModelA の現在検証されている属性の値が ModelB の属性 "a" の保存されている値に対してチェックされる
    - `['a']` => ModelA の属性 "a" の値が ModelB の属性 "a" の保存されている値に対してチェックされる
    - `['a' => 'a']` => 同上
    - `['a', 'b']` => ModelA の属性 "a" の値が ModelB の属性 "a" の保存されている値に対してチェックされ、
    同時に、ModelA の属性 "b" の値が ModelB の属性 "b" の保存されている値に対してチェックされる
    - `['a' => 'b']` => ModelA の属性 "a" の値が ModelB の属性 "b" の保存されている値に対してチェックされる
- `filter`: 入力値がユニークであることをチェックするのに使用される DB クエリに適用される追加のフィルタ。
  これには、追加のクエリ条件を表現する文字列または配列を使うことが出来ます (クエリ条件の書式については、[[yii\db\Query::where()]] を参照してください)。
  これは、または、`function ($query)` というシグニチャを持つ無名関数でも構いません。
  `$query` は関数の中で修正できる [[yii\db\Query|Query]] オブジェクトです。


## [[yii\validators\UrlValidator|url]] <span id="url"></span>

```php
[
    // "website" が有効な URL であるかどうかをチェック。
    // URI スキームを持たない場合は、"website" 属性に "http://" を前置する
    ['website', 'url', 'defaultScheme' => 'http'],
]
```

このバリデータは、入力値が有効な URL であるかどうかをチェックします。

- `validSchemes`: 有効と見なされるべき URI スキームを指定する配列。
  デフォルト値は  `['http', 'https']` であり、`http` と `https` の URL がともに有効と見なされることを意味します。
- `defaultScheme`: 入力値がスキームの部分を持たないときに前置されるデフォルトの URI スキーム。
  デフォルト値は `null` であり、入力値を修正しないことを意味します。
- `enableIDN`: バリデータが IDN (国際化ドメイン名) を考慮すべきか否か。デフォルト値は `false`。
  IDN の検証を使用するためには、`intl` PHP 拡張をインストールして有効化する必要があることに注意してください。
  そうしないと、例外が投げられます。

> Note: このバリデータは URL スキームとホスト部分が正しいものであることを検証します。
  URL の残りの部分はチェックしません。また、XSS や他の攻撃に対して防御するように設計されてもいません。
  アプリケーション開発における脅威に対する防御について更に学習するために[セキュリティのベスト・プラクティス](security-best-practices.md) を参照して下さい。
