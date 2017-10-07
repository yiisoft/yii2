Yii 2 コアフレームワークコードスタイル
=====================================

下記のコードスタイルが Yii 2.x コアと公式エクステンションの開発に用いられています。
コアに対してコードをプルリクエストをしたいときは、これを使用することを考慮してください。
私たちは、あなたが自分のアプリケーションにこのコードスタイルを使うことを強制するものではありません。
あなたにとってより良いコードスタイルを自由に選んでください。

なお、CodeSniffer のための設定をここで入手できます: https://github.com/yiisoft/yii2-coding-standards

> Note: 以下では、説明のために、サンプル・コードのドキュメントやコメントを日本語に翻訳しています。
  しかし、コアコードや公式エクステンションに対して実際に寄稿する場合には、それらを英語で書く必要があります。


## 1. 概要

全体として、私たちは [PSR-2](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-2-coding-style-guide.md) 互換のスタイルを使っていますので、
[PSR-2](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-2-coding-style-guide.md) に適用されることは、すべて私たちのコードスタイルにも適用されます。

- ファイルは `<?php` または `<?=` のタグを使わなければならない。
- ファイルの末尾には一個の改行があるべきである。
- ファイルは PHP コードに対して BOM 無しの UTF-8 だけを使わなければならない。
- コードはインデントに、タブではなく、4個の空白を使わなければならない。
- クラス名は `StudlyCaps` で宣言されなくてはならない。
- クラス内の定数はアンダースコアで区切られた大文字だけで宣言されなければならない。
- メソッド名は `camelCase` で宣言されなければならない。
- プロパティ名は `camelCase` で宣言されなければならない。
- プロパティ名は private である場合はアンダースコアで始まらなければならない。
- `else if` ではなく常に `elseif` を使用すること。

## 2. ファイル

### 2.1. PHP タグ

- PHP コードは `<?php ?>` または `<?=` タグを使わなければなりません。他のタグの変種、例えば `<?` を使ってはなりません。
- ファイルが PHP コードのみを含む場合は、末尾の `?>` を含むべきではありません。
- 各行の末尾に空白を追加してはいけません。
- PHP コードを含む全てのファイルの名前は `.php` という拡張子で終るべきです。

### 2.2. 文字エンコーディング

PHP コードは BOM 無しの UTF-8 のみを使わなければなりません。

## 3. クラス名

クラス名は `StudlyCaps` で宣言されなければなりません。例えば、`Controller`、`Model`。

## 4. クラス

ここで "クラス" という用語はあらゆるクラスとインタフェイスを指すものとします。

- クラスは `CamelCase` で命名されなければなりません。
- 中括弧は常にクラス名の下の行に書かれるべきです。
- 全てのクラスは PHPDoc に従ったドキュメントブロックを持たなければなりません。
- クラス内のすべてのコードは4個の空白によってインデントされなければなりません。
- 一つの PHP ファイルにはクラスが一つだけあるべきです。
- 全てのクラスは名前空間に属すべきです。
- クラス名はファイル名と合致すべきです。クラスの名前空間はディレクトリ構造と合致すべきです。

```php
/**
 * ドキュメント
 */
class MyClass extends \yii\base\BaseObject implements MyInterface
{
    // コード
}
```

### 4.1. 定数

クラスの定数はアンダースコアで区切られた大文字だけで宣言されなければなりません。
例えば、

```php
<?php
class Foo
{
    const VERSION = '1.0';
    const DATE_APPROVED = '2012-06-01';
}
```
### 4.2. プロパティ

- Public なクラスメンバを宣言するときは `public` キーワードを明示的に指定します。
- Public および protected な変数はクラスの冒頭で、すべてのメソッドの宣言に先立って宣言されるべきです。
  Private な変数もまたクラスの冒頭で宣言されるべきですが、
  変数がクラスのメソッドのごく一部分にのみ関係する場合は、変数を扱う一群のメソッドの直前に追加しても構いません。
- クラスにおけるプロパティの宣言の順序は、その可視性に基づいて、 public から始まり、protected、private と続くべきです。
- 同じ可視性を持つプロパティの順序については、厳格な規則はありません。
- より読みやすいように、プロパティの宣言は空行を挟まずに続け、プロパティ宣言とメソッド宣言のブロック間には2行の空行を挟むべきです。
  また、異なる可視性のグループの間に、1行の空行を追加するべきです。
- Private 変数は `$_varName` のように名付けるべきです。
- Public なクラスメンバとスタンドアロンな変数は、先頭を小文字にした `$camelCase` で名付けるべきです。
- 説明的な名前を使うこと。`$i` や `$j` のような変数は使わないようにしましょう。

例えば、

```php
<?php
class Foo
{
    public $publicProp1;
    public $publicProp2;

    protected $protectedProp;

    private $_privateProp;


    public function someMethod()
    {
        // ...
    }
}
```

### 4.3. メソッド

- 関数およびメソッドは、先頭を小文字にした `camelCase` で名付けるべきです。
- 名前は、関数の目的を示す自己説明的なものであるべきです。
- クラスのメソッドは常に修飾子 `private`、`protected` または `public` を使って、可視性を宣言すべきです。`var` は許可されません。
- 関数の開始の中括弧は関数宣言の次の行に置くべきです。

```php
/**
 * ドキュメント
 */
class Foo
{
    /**
     * ドキュメント
     */
    public function bar()
    {
        // コード
        return $value;
    }
}
```

### 4.4 PHPDoc ブロック

 - `@param`、`@var`、`@property` および `@return` は `bool`、`int`、`string`、`array` または `null` として型を宣言しなければなりません。
   `Model` または `ActiveRecord` のようなクラス名を使うことも出来ます。
 - 型付きの配列に対しては `ClassName[]` を使います。
 - PHPDoc の最初の行には、メソッドの目的を記述しなければなりません。
 - メソッドが何かをチェックする (たとえば、`isActive`, `hasClass` など) ものである場合は、
   最初の行は `Checks whether` で始まらなければなりません。
 - `@return` は、厳密に何が返されるのかを明示的に記述しなければなりません。

```php
/**
 * Checks whether the IP is in subnet range
 *
 * @param string $ip an IPv4 or IPv6 address
 * @param int $cidr the CIDR lendth
 * @param string $range subnet in CIDR format e.g. `10.0.0.0/8` or `2001:af::/64`
 * @return bool whether the IP is in subnet range
 */
 private function inRange($ip, $cidr, $range)
 {
   // ...
 }
```


### 4.5 コンストラクタ

- PHP 4 スタイルのコンストラクタの代りに、`__construct` が使われるべきです。

## 5 PHP

### 5.1 型

- PHP の全ての型と値には小文字を使うべきです。このことは、`true`、`false`、`null` および `array` にも当てはまります。

既存の変数の型を変えることは悪いプラクティスであると見なされています。本当に必要でない限り、そのようなコードを書かないように努めましょう。


```php
public function save(Transaction $transaction, $argument2 = 100)
{
    $transaction = new Connection; // 駄目
    $argument2 = 200; // 良い
}
```

### 5.2 文字列

- 文字列が変数および一重引用符を含まない場合は、一重引用符を使います。

```php
$str = 'こんな具合に。';
```

- 文字列が一重引用符を含む場合は、余計なエスケープを避けるために二重引用符を使ってもかまいません。

#### 変数置換

```php
$str1 = "こんにちは $username さん";
$str2 = "こんにちは {$username} さん";
```

下記は許可されません。

```php
$str3 = "こんにちは ${username} さん";
```

#### 連結

文字列を連結するときは、ドットの前後に空白を追加します。

```php
$name = 'Yii' . ' Framework';
```

文字列が長い場合、書式は以下のようにします。

```php
$sql = "SELECT *"
    . "FROM `post` "
    . "WHERE `id` = 121 ";
```

### 5.3 配列

配列には、私たちは PHP 5.4 の短縮構文を使用しています。

#### 添え字配列

- 負の数を配列のインデックスに使わないこと。

配列を宣言するときは、下記の書式を使います。

```php
$arr = [3, 14, 15, 'Yii', 'Framework'];
```

一つの行には多過ぎるほど要素がたくさんある場合は、

```php
$arr = [
    3, 14, 15,
    92, 6, $test,
    'Yii', 'Framework',
];
```

#### 連想配列

連想配列には下記の書式を使います。

```php
$config = [
    'name' => 'Yii',
    'options' => ['usePHP' => true],
];
```

### 5.4 制御文

- 制御文の条件は括弧の前と後に一つの空白を持たなければなりません。
- 括弧の中の演算子は空白によって区切られるべきです。
- 開始の中括弧は同じ行に置きます。
- 終了の中括弧は新しい行に置きます。
- 単一行の文に対しても、常に中括弧を使用します。

```php
if ($event === null) {
    return new Event();
}
if ($event instanceof CoolEvent) {
    return $event->instance();
}
return null;


// 下記は許容されません
if (!$model && null === $event)
    throw new Exception('test');
```

そうしても意味が通じる場合は、`return` の後の `else` は避けてください。
[ガード条件](http://refactoring.com/catalog/replaceNestedConditionalWithGuardClauses.html) を使用しましょう。

```php
$result = $this->getResult();
if (empty($result)) {
    return true;
} else {
    // $result を処理
}
```

これは、次の方が良いです。

```php
$result = $this->getResult();
if (empty($result)) {
    return true;
}

// $result を処理
```

#### switch

switch には下記の書式を使用します。

```php
switch ($this->phpType) {
    case 'string':
        $a = (string) $value;
        break;
    case 'integer':
    case 'int':
        $a = (int) $value;
        break;
    case 'boolean':
        $a = (bool) $value;
        break;
    default:
        $a = null;
}
```

### 5.5 関数呼び出し

```php
doIt(2, 3);

doIt(['a' => 'b']);

doIt('a', [
    'a' => 'b',
    'c' => 'd',
]);
```

### 5.6 無名関数 (lambda) の宣言

`function`/`use` トークンと開始括弧の間の空白に注意してください。

```php
// 良い
$n = 100;
$sum = array_reduce($numbers, function ($r, $x) use ($n) {
    $this->doMagic();
    $r += $x * $n;
    return $r;
});

// 悪い
$n = 100;
$mul = array_reduce($numbers, function($r, $x) use($n) {
    $this->doMagic();
    $r *= $x * $n;
    return $r;
});
```

ドキュメント
------------

- ドキュメントの文法については [phpDoc](http://phpdoc.org/) を参照してください。
- ドキュメントの無いコードは許容されません。
- 全てのクラスファイルは、ファイルレベルの doc ブロックを各ファイルの先頭に持ち、クラスレベルの doc ブロックを各クラスの直前に持たなければなりません。
- メソッドが実際に何も返さないときは `@return` を使う必要はありません。
- `yii\base\BaseObject` から派生するクラスのすべての仮想プロパティは、クラスの doc ブロックで `@property` タグでドキュメントされます。
  これらの注釈は、`build` ディレクトリで `./build php-doc` コマンドを走らせることにより、対応する getter や setter の `@return` や `@param` タグから自動的に生成されます。
  getter や setter に `@property` タグを追加することによって、これらのメソッドによって導入されるプロパティに対してドキュメントのメッセージを明示的に与えることが出来ます。
  これは `@return` で記述されているのとは違う説明を与えたい場合に有用です。
  下記が一例です。

  ```php
    <?php
    /**
     * 全ての属性または一つの属性についてエラーを返す。
     * @param string $attribute 属性の名前。全ての属性についてエラーを取得するためには null を使う。
     * @property array 全ての属性に対するエラーの配列。エラーが無い場合は空の配列が返される。
     * 結果は二次元の配列である。詳細な説明は [[getErrors()]] を参照。
     * @return array 全ての属性または特定の属性に対するエラー。エラーが無い場合は空の配列が返される。
     * 全ての属性に対するエラーを返す場合、結果は、下記のような二次元の配列になる。
     * ...
     */
    public function getErrors($attribute = null)
  ```

#### ファイル

```php
<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */
```

#### クラス

```php
/**
 * Component は *property*、*event* および *behavior* の機能を提供する基底クラスである。
 *
 * @include @yii/docs/base-Component.md
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class Component extends \yii\base\BaseObject
```


#### 関数 / メソッド

```php
/**
 * イベントに対してアタッチされたイベントハンドラのリストを返す。
 * 返された [[Vector]] オブジェクトを操作して、ハンドラを追加したり削除したり出来る。
 * 例えば、
 *
 * ```
 * $component->getEventHandlers($eventName)->insertAt(0, $eventHandler);
 * ```
 *
 * @param string $name イベントの名前
 * @return Vector イベントにアタッチされたハンドラのリスト
 * @throws Exception イベントが定義されていない場合
 */
public function getEventHandlers($name)
{
    if (!isset($this->_e[$name])) {
        $this->_e[$name] = new Vector;
    }
    $this->ensureBehaviors();
    return $this->_e[$name];
}
```

#### Markdown

上記の例に見られるように、phpDoc コメントの書式設定には markdown を使います。

ドキュメントの中でクラス、メソッド、プロパティをクロスリンクするために使える追加の文法があります。

- `[[canSetProperty]]` は、同じクラス内の `canSetProperty` メソッドまたはプロパティへのクロスリンクを生成します。
- `[[Component::canSetProperty]]` は、同じ名前空間内の `Component` クラスの `canSetProperty` メソッドへのクロスリンクを生成します。
- `[[yii\base\Component::canSetProperty]]` は、`yii\base` 名前空間の`Component` クラスの `canSetProperty` メソッドへのクロスリンクを生成します。
- `[[Component]]` は、同じ名前空間内の `Component` クラスへのクロスリンクを生成します。ここでも、クラス名に名前空間を追加することが可能です。

上記のリンクにクラス名やメソッド名以外のラベルを付けるためには、次の例で示されている文法を使うことが出来ます。

```
... [[header|ヘッダセル]] に表示されているように
```

`|` の前の部分がメソッド、プロパティ、クラスへの参照であり、`|` の後ろの部分がリンクのラベルです。

下記の文法を使ってガイドにリンクすることも可能です。

```markdown
[ガイドへのリンク](guide:file-name.md)
[ガイドへのリンク](guide:file-name.md#subsection)
```


#### コメント

- 一行コメントは `//` で開始されるべきです。`#` は使いません。
- 一行コメントはそれ自体で一行を占めるべきです。

追加の規則
----------

### `=== []` 対 `empty()`

可能な場合は `empty()` を使います。

### 複数の return ポイント

条件の入れ子が込み入ってきた場合は、早期の return を使います。メソッドが短いものである場合は、特に問題にしません。

### `self` 対 `static`

以下の場合を除いて、常に `static` を使います。

- 定数へのアクセスには `self` を使わなければなりません: `self::MY_CONSTANT`
- private な静的プロパティへのアクセスには `self` を使わなければなりません: `self::$_events`
- 再帰呼出しにおいて、拡張クラスの実装ではなく、現在のクラスの実装を再び呼び出したいときなど、合理的な理由がある場合には、`self` を使うことが許可されます。

### 「何かをするな」を示す値

コンポーネントに対して「何かをしない」という設定を許可するプロパティは `false` の値を受け入れるべきです。
`null`、`''`、または `[]` がそういう値であると見なされるべきではありません。

### ディレクトリ/名前空間の名前

- 小文字を使います。
- オブジェクトを表すものには複数形の名詞を使います (例えば、validators)。
- 機能や特徴を表す名前には単数形を使います (例えば、web)。
- 出来れば単一の語の名前空間にします。
- 単一の語が適切でない場合は、camelCase を使います。
