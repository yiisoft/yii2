パスワードを扱う
================

> Note|注意: この節はまだ執筆中です。

十分なセキュリティは、すべてのアプリケーションの健全さと成功のために欠くことが出来ないものです。
不幸なことに、理解が不足しているためか、実装の難易度が高すぎるためか、セキュリティのことになると手を抜く開発者がたくさんいます。
Yii によって駆動されるあなたのアプリケーションを可能な限り安全にするために、Yii はいくつかの優秀な使いやすいセキュリティ機能を内蔵しています。


ハッシュとパスワードの検証
--------------------------

ほとんどの開発者はパスワードを平文テキストで保存してはいけないということを知っていますが、パスワードを `md5` や `sha1` でハッシュしてもまだ安全だと思っている開発者がたくさんいます。
かつては、前述のハッシュアルゴリズムを使えば十分であった時もありましたが、現代のハードウェアをもってすれば、そのようなハッシュはブルートフォースアタックを使って非常に簡単に復元することが可能です。

最悪のシナリオ (アプリケーションに侵入された場合) であっても、ユーザのパスワードについて強化されたセキュリティを提供することが出来るように、ブルートフォースアタックに対する耐性が強いハッシュアルゴリズムを使う必要があります。
現在、最善の選択は `bcrypt` です。
PHP では、[crypt 関数](http://php.net/manual/ja/function.crypt.php) を使って `bcrypt` ハッシュを生成することが出来ます。
Yii は `crypt` を使ってハッシュを安全に生成し検証することを容易にするために、二つのヘルパ関数を提供しています。

ユーザが初めてパスワードを提供するとき (例えば、ユーザ登録の時) には、パスワードをハッシュする必要があります。


```php
$hash = Yii::$app->getSecurity()->generatePasswordHash($password);
```

そして、ハッシュを対応するモデル属性と関連付けて、後で使用するためにデータベースに保存します。

ユーザがログインを試みたときは、送信されたパスワードは、前にハッシュされて保存されたパスワードと照合して検証されなければなりません。


```php
if (Yii::$app->getSecurity()->validatePassword($password, $hash)) {
    // よろしい、ユーザをログインさせる
} else {
    // パスワードが違う
}
```

擬似乱数データを生成する
------------------------

擬似乱数データはさまざまな状況で役に立ちます。
例えば、メール経由でパスワードをリセットするときは、トークンを生成してデータベースに保存し、それをユーザにメールで送信します。
そして、ユーザはこのトークンを自分がアカウントの所有者であることの証拠として使用します。
このトークンがユニークかつ推測困難なものであることは非常に重要なことです。
さもなくば、攻撃者がトークンの値を推測してユーザのパスワードをリセットする可能性があります。

Yii のセキュリティヘルパは擬似乱数データの生成を単純な作業にしてくれます。


```php
$key = Yii::$app->getSecurity()->generateRandomString();
```

暗号論的に安全な乱数データを生成するためには、`openssl` 拡張をインストールしている必要があることに注意してください。

暗号化と復号化
--------------

Yii は秘密鍵を使ってデータを暗号化/復号化することを可能にする便利なヘルパ関数を提供しています。
データを暗号化関数に渡して、秘密鍵を持つ者だけが復号化することが出来るようにすることが出来ます。
例えば、何らかの情報をデータベースに保存する必要があるけれども、(たとえアプリケーションのデータベースが第三者に漏洩した場合でも) 秘密鍵を持つユーザだけがそれを見ることが出来るようにする必要がある、という場合には次のようにします。

```php
// $data と $secretKey はフォームから取得する
$encryptedData = Yii::$app->getSecurity()->encryptByPassword($data, $secretKey);
// $encryptedData をデータベースに保存する
```

そして、後でユーザがデータを読みたいときは、次のようにします。

```php
// $secretKey はユーザ入力から取得、$encryptedData はデータベースから取得
$data = Yii::$app->getSecurity()->decryptByPassword($encryptedData, $secretKey);
```

データの完全性を確認する
------------------------

データが第三者によって改竄されたり、更には何らかの形で毀損されたりしていないことを確認する必要がある、という場合があります。
Yii は二つのヘルパ関数の形で、データの完全性を確認するための簡単な方法を提供しています。

秘密鍵とデータから生成されたハッシュをデータにプレフィクスします。

```php
// $secretKey はアプリケーションまたはユーザの秘密、$genuineData は信頼できるソースから取得
$data = Yii::$app->getSecurity()->hashData($genuineData, $secretKey);
```

データの完全性が毀損されていないかチェックします。

```php
// $secretKey はアプリケーションまたはユーザの秘密、$data は信頼できないソースから取得
$data = Yii::$app->getSecurity()->validateData($data, $secretKey);
```


todo: XSS prevention, CSRF prevention, cookie protection, refer to 1.1 guide

プロパティを設定することによって、CSRF バリデーションをコントローラ および/または アクション単位で無効にすることも出来ます。

```php
namespace app\controllers;

use yii\web\Controller;

class SiteController extends Controller
{
    public $enableCsrfValidation = false;

    public function actionIndex()
    {
        // CSRF バリデーションはこのアクションおよびその他のアクションに適用されない
    }

}
```

特定のアクションに対して CSRF バリデーションを無効にするためには、次のようにします。

```php
namespace app\controllers;

use yii\web\Controller;

class SiteController extends Controller
{
    public function beforeAction($action)
    {
        // ... ここで、何らかの条件に基づいて `$this->enableCsrfValidation` をセットする ...
        // 親のメソッドを呼ぶ。プロパティが true なら、その中で CSRF がチェックされる。
        return parent::beforeAction($action);
    }
}
```

クッキーを安全にする
--------------------

- validation
- httpOnly is default

参照
----

以下も参照してください。

- [ビューのセキュリティ](structure-views.md#security)

