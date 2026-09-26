Models (Modelos)
================

Os models (modelos) fazem parte da arquitetura [MVC](https://pt.wikipedia.org/wiki/MVC).
Eles representam os dados, as regras e a lógica de negócio.

Você pode criar uma classe model estendendo de [[yii\base\Model]] ou de seus filhos.
A classe base [[yii\base\Model]] suporta muitos recursos úteis:

* [Atributos](#attributes): representa os dados de negócio e podem ser acessados 
  normalmente como uma propriedade de objeto ou como um elemento de array;
* [Labels dos atributos](#attribute-labels): especifica os labels de exibição dos 
  atributos;
* [Atribuição em massa](#massive-assignment): suporta popular vários atributos em 
  uma única etapa;
* [Regras de validação](#validation-rules): garante que os dados de entrada sejam 
  baseadas nas regras de validação que foram declaradas;
* [Data Exporting](#data-exporting): permite que os dados de model a serem exportados 
  em array possuam formatos personalizados.

A classe `Model` também é a classe base para models mais avançados, como o [Active Record](db-active-record.md).
Por favor, consulte a documentação relevante para mais detalhes sobre estes models mais avançados.

> Informação: Você não é obrigado basear suas classe model em [[yii\base\Model]]. 
> No entanto, por existir muitos componentes do Yii construídos para suportar o 
> [[yii\base\Model]], normalmente é a classe base preferível para um model.


## Atributos <span id="attributes"></span>

Os models representam dados de negócio por meio de *atributos*. Cada atributo é 
uma propriedade publicamente acessível de um model. O método [[yii\base\Model::attributes()]] 
especifica quais atributos de uma classe model possuirá.

Você pode acessar um atributo como fosse uma propriedade normal de um objeto:

```php
$model = new \app\models\ContactForm;

// "name" é um atributo de ContactForm
$model->name = 'example';
echo $model->name;
```

Você também pode acessar os atributos como elementos de um array, graças ao suporte 
de [ArrayAccess](https://www.php.net/manual/en/class.arrayaccess.php) e 
[ArrayIterator](https://www.php.net/manual/en/class.arrayiterator.php) pelo 
[[yii\base\Model]]:

```php
$model = new \app\models\ContactForm;

// acessando atributos como elementos de array
$model['name'] = 'example';
echo $model['name'];

// iterando sobre os atributos
foreach ($model as $name => $value) {
    echo "$name: $value\n";
}
```


### Definindo Atributos <span id="defining-attributes"></span>

Por padrão, se a classe model estender diretamente de [[yii\base\Model]], todas 
as suas variáveis públicas e não estáticas serão atributos. Por exemplo, a classe 
model `ContactForm` a seguir possui quatro atributos: `name`, `email`, `subject` 
e `body`. O model `ContactForm` é usado para representar os dados de entrada obtidos 
a partir de um formulário HTML.

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


Você pode sobrescrever o método [[yii\base\Model::attributes()]] para definir 
atributos de uma forma diferente. Este método deve retornar os nomes dos atributos 
em um model. Por exemplo, o [[yii\db\ActiveRecord]] faz com que o método retorne 
os nomes das colunas da tabela do banco de dados como nomes de atributos.
Observe que também poderá sobrescrever os métodos mágicos tais como `__get()` e 
`__set()`, para que os atributos poderem ser acessados como propriedades normais 
de objetos.


### Labels dos Atributos <span id="attribute-labels"></span>

Ao exibir valores ou obter dados de entrada dos atributos, muitas vezes é necessário 
exibir alguns labels associados aos atributos. Por exemplo, dado um atributo chamado 
`firstName`, você pode querer exibir um label `First Name` que é mais amigável 
quando exibido aos usuários finais como em formulários e mensagens de erro.

Você pode obter o label de um atributo chamando o método [[yii\base\Model::getAttributeLabel()]]. 
Por exemplo,

```php
$model = new \app\models\ContactForm;

// displays "Name"
echo $model->getAttributeLabel('name');
```

Por padrão, os labels dos atributos automaticamente serão gerados com os nomes dos 
atributos. Isto é feito pelo método [[yii\base\Model::generateAttributeLabel()]]. 
Ele transforma os nomes camel-case das variáveis em várias palavras, colocando em 
caixa alta a primeira letra de cada palavra. Por exemplo, `username` torna-se 
`Username`, enquanto `firstName` torna-se `First Name`.

Se você não quiser usar esta geração automática do labels, poderá sobrescrever o 
método [[yii\base\Model::attributeLabels()]] declarando explicitamente os labels 
dos atributos. Por exemplo,

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

Para aplicações que suportam vários idiomas, você pode querer traduzir os labels 
dos atributos. Isto também é feito no método [[yii\base\Model::attributeLabels()|attributeLabels()]], 
conforme o exemplo a seguir:

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

Você pode até definir condicionalmente os labels dos atributos. Por exemplo, baseado 
no [cenário](#scenarios) que o model estiver utilizando, você pode retornar diferentes 
labels para o mesmo atributo.

> Informação: Estritamente falando, os labels dos atributos fazem parte das 
[views](structure-views.md) (visões). Mas ao declarar os labels em models (modelos), 
frequentemente tornam-se mais convenientes e podem resultar um código mais limpo 
e reutilizável.


## Cenários <span id="scenarios"></span>

Um model (modelo) pode ser usado em diferentes *cenários*. Por exemplo, um model 
`User` pode ser usado para obter dados de entrada de login, mas também pode ser 
usado com a finalidade de registrar o usuário. Em diferentes cenários, um model 
pode usar diferentes regras e lógicas de negócio. Por exemplo, um atributo `email` 
pode ser obrigatório durante o cadastro do usuário, mas não durante ao login.

Um model (modelo) usa a propriedade [[yii\base\Model::scenario]] para identificar 
o cenário que está sendo usado.
Por padrão, um model (modelo) suporta apenas um único cenário chamado `default`. 
O código a seguir mostra duas formas de definir o cenário de um model (modelo):

```php
// o cenário é definido pela propriedade
$model = new User;
$model->scenario = 'login';

// o cenário é definido por meio de configuração
$model = new User(['scenario' => 'login']);
```

Por padrão, os cenários suportados por um model (modelo) são determinados pelas 
[regras de validação](#validation-rules) declaradas no próprio model (modelo).
No entanto, você pode personalizar este comportamento sobrescrevendo o método 
[[yii\base\Model::scenarios()]], conforme o exemplo a seguir:

```php
namespace app\models;

use yii\db\ActiveRecord;

class User extends ActiveRecord
{
    public function scenarios()
    {
        return [
            'login' => ['username', 'password'],
            'register' => ['username', 'email', 'password'],
        ];
    }
}
```

> Informação: Nos exemplos anteriores, as classes model (model) são estendidas de 
[[yii\db\ActiveRecord]] por usarem diversos cenários para auxiliarem as classes 
[Active Record](db-active-record.md) classes.

O método `scenarios()` retorna um array cujas chaves são os nomes dos cenários e 
os valores que correspondem aos *active attributes* (atributo ativo). Um atributo 
ativo podem ser [atribuídos em massa](#massive-assignment) e é sujeito a 
[validação](#validation-rules). No  exemplo anterior, os atributos `username` e 
`password` são ativos no cenário `login`; enquanto no cenário `register`, além 
dos atribitos `username` e `password`, o atributo `email` passará a ser ativo.

A implementação padrão do método `scenarios()` retornará todos os cenários encontrados 
nas regras de validação declaradas no método [[yii\base\Model::rules()]]. Ao 
sobrescrever o método `scenarios()`, se quiser introduzir novos cenários, além 
dos cenários padrão, poderá escrever um código conforme o exemplo a seguir:
 
```php
namespace app\models;

use yii\db\ActiveRecord;

class User extends ActiveRecord
{
    public function scenarios()
    {
        $scenarios = parent::scenarios();
        $scenarios['login'] = ['username', 'password'];
        $scenarios['register'] = ['username', 'email', 'password'];
        return $scenarios;
    }
}
```

O recurso de cenários são usados principalmente para [validação](#validation-rules) 
e para [atribuição em massa](#massive-assignment).
Você pode, no entanto, usá-lo para outros fins. Por exemplo, você pode declarar 
diferentes [labels para os atributos](#attribute-labels) baseados no cenário atual.


## Regras de Validação <span id="validation-rules"></span>

Quando os dados para um model (modelo) são recebidos de usuários finais, devem ser 
validados para garantir que satisfazem as regras (*regras de validação*, também 
conhecidos como *regras de negócio*). Por exemplo, considerando um model (modelo) 
`ContactForm`, você pode querer garantir que todos os atributos não sejam vazios e 
que o atributo `email` contenha um e-mail válido.
Se o valor de algum atributo não satisfizer a regra de negócio correspondente, 
mensagens apropriadas de erros serão exibidas para ajudar o usuário a corrigi-los.

Você pode chamar o método [[yii\base\Model::validate()]] para validar os dados 
recebidos. O método usará as regras de validação declaradas em [[yii\base\Model::rules()]] 
para validar todos os atributos relevantes. Se nenhum erro for encontrado, o método 
retornará `true`. Caso contrário, o método irá manter os erros na propriedade 
[[yii\base\Model::errors]] e retornará `false`. Por exemplo,

```php
$model = new \app\models\ContactForm;

// os atributos do model serão populados pelos dados fornecidos pelo usuário
$model->attributes = \Yii::$app->request->post('ContactForm');

if ($model->validate()) {
    // todos os dados estão válidos 
} else {
    // a validação falhou: $errors é um array contendo as mensagens de erro
    $errors = $model->errors;
}
```


Para declarar as regras de validação em um model (modelo), sobrescreva o método 
[[yii\base\Model::rules()]] retornando as regras que os atributos do model (modelo) 
devem satisfazer. O exemplo a seguir mostra as regras de validação sendo declaradas 
no model (modelo) `ContactForm`:

```php
public function rules()
{
    return [
        // os atributos name, email, subject e body são obrigatórios
        [['name', 'email', 'subject', 'body'], 'required'],

        // o atributo email deve ter um e-mail válido
        ['email', 'email'],
    ];
}
```

Uma regra pode ser usada para validar um ou vários atributos e, um atributo pode 
ser validado por uma ou várias regras.
Por favor, consulte a seção [Validação de Dados](input-validation.md) para mais 
detalhes sobre como declarar regras de validação.

Às vezes, você pode querer que uma regra se aplique apenas em determinados 
[cenários](#scenarios). Para fazer isso, você pode especificar a propriedade 
`on` de uma regra, como o seguinte:

```php
public function rules()
{
    return [
        // os atributos username, email e password são obrigatórios no cenario "register"
        [['username', 'email', 'password'], 'required', 'on' => 'register'],

        // os atributos username e password são obrigatórios no cenario "login"
        [['username', 'password'], 'required', 'on' => 'login'],
    ];
}
```

Se você não especificar a propriedade `on`, a regra será aplicada em todos os 
cenários. Uma regra é chamada de *active rule* (regra ativa), se ela puder ser 
aplicada no [[yii\base\Model::scenario|cenário]] atual.

Um atributo será validado, se e somente se, for um atributo ativo declarado no 
método `scenarios()` e estiver associado a uma ou várias regras declaradas no método `rules()`.


## Atribuição em Massa <span id="massive-assignment"></span>

Atribuição em massa é a forma conveniente para popular um model (modelo) com os 
dados de entrada do usuário usando uma única linha de código.
Ele popula os atributos de um model (modelo) atribuindo os dados de entrada diretamente 
na propriedade [[yii\base\Model::$attributes]]. Os dois códigos a seguir são 
equivalentes, ambos tentam atribuir os dados do formulário enviados pelos usuários 
finais para os atributos do model (modelo) `ContactForm`.  Evidentemente, a 
primeira forma, que utiliza a atribuição em massa, é a mais limpa e o menos 
propenso a erros do que a segunda forma:

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


### Atributos Seguros <span id="safe-attributes"></span>

A atribuição em massa só se aplica aos chamados *safe attributes* (atributos seguros), 
que são os atributos listados no [[yii\base\Model::scenarios()]] para o 
[[yii\base\Model::scenario|cenário]] atual de um model (modelo).
Por exemplo, se o model (modelo) `User` declarar o cenário como o código a seguir, 
quando o cenário atual for `login`, apenas os atributos `username` e `password` 
podem ser atribuídos em massa. Todos os outros atributos permanecerão inalterados.

```php
public function scenarios()
{
    return [
        'login' => ['username', 'password'],
        'register' => ['username', 'email', 'password'],
    ];
}
```

> Informação: A razão da atribuição em massa só se aplicar para os atributos seguros 
é para que você tenha o controle de quais atributos podem ser modificados pelos 
dados dos usuário finais. Por exemplo, se o model (modelo) tiver um atributo 
`permission` que determina a permissão atribuída ao usuário, você gostará que 
apenas os administradores possam modificar este atributo através de uma interface backend.

Como a implementação do método [[yii\base\Model::scenarios()]] retornará todos os 
cenários e atributos encontrados em [[yii\base\Model::rules()]], se não quiser 
sobrescrever este método, isto significa que um atributo é seguro desde que esteja 
mencionado em uma regra de validação ativa.

Por esta razão, uma alias especial de validação chamada `safe`, será fornecida 
para que você possa declarar um atributo seguro, sem ser validado. Por exemplo, 
a declaração da regra a seguir faz com que tanto o atributo `title` quanto o
`description` sejam seguros.

```php
public function rules()
{
    return [
        [['title', 'description'], 'safe'],
    ];
}
```


### Atributos não Seguros <span id="unsafe-attributes"></span>

Como descrito anteriormente, o método [[yii\base\Model::scenarios()]] serve para 
dois propósitos: determinar quais atributos devem ser validados e quais atributos 
são seguros. Em alguns casos raros, você pode quer validar um atributo sem marca-lo 
como seguro. Para fazer isto, acrescente um ponto de exclamação `!` como prefixo 
do nome do atributo ao declarar no método `scenarios()`, como o que foi feito no 
atributo `secret` no exemplo a seguir:

```php
public function scenarios()
{
    return [
        'login' => ['username', 'password', '!secret'],
    ];
}
```

Quando o model (modelo) estiver no cenário `login`, todos os três atributos serão 
validados. No entanto, apenas os atributos `username` e `password` poderão ser 
atribuídos em massa. Para atribuir um valor de entrada no atributo `secret`, terá 
que fazer isto explicitamente da seguinte forma:

```php
$model->secret = $secret;
```


## Exportação de Dados <span id="data-exporting"></span>

Muitas vezes os models (modelos) precisam ser exportados em diferentes tipos de 
formatos. Por exemplo, você pode querer converter um conjunto de models (modelos) 
no formato JSON ou Excel. O processo de exportação pode ser divido em duas etapas independentes.
Na primeira etapa, os models (modelos) serão convertidos em arrays; na segunda 
etapa, os arrays serão convertidos em um determinado formato. Se concentre apenas 
na primeira etapa, uma vez que a segunda etapa pode ser alcançada por formatadores 
de dados genéricos, tais como o [[yii\web\JsonResponseFormatter]].

A maneira mais simples de converter um model (modelo) em um array consiste no uso 
da propriedade [[yii\base\Model::$attributes]].
Por exemplo,

```php
$post = \app\models\Post::findOne(100);
$array = $post->attributes;
```

Por padrão, a propriedade [[yii\base\Model::$attributes]] retornará os valores de 
todos os atributos declarados no método [[yii\base\Model::attributes()]].

Uma maneira mais flexível e poderosa de converter um model (modelo) em um array é 
através do método [[yii\base\Model::toArray()]]. O seu comportamento padrão é o 
mesmo do [[yii\base\Model::$attributes]]. No entanto, ele permite que você escolha 
quais itens de dados, chamados de *fields* (campos), devem ser mostrados no array 
resultante e como eles devem vir formatados.
Na verdade, é a maneira padrão de exportação de models (modelos) no desenvolvimento 
de Web services RESTful, como descrito na seção [Formatando Respostas](rest-response-formatting.md).


### Campos <span id="fields"></span>

Um campo é simplesmente um elemento nomeado no array obtido pela chamada do método 
[[yii\base\Model::toArray()]] de um model (modelo).

Por padrão, os nomes dos campos são iguais aos nomes dos atributos. No entanto, 
você pode alterar este comportamento sobrescrevendo os métodos 
[[yii\base\Model::fields()|fields()]] e/ou [[yii\base\Model::extraFields()|extraFields()]]. 
Ambos os métodos devem retornar uma lista dos campos definidos. Os campos definidos 
pelo método `fields()` são os campos padrão, o que significa que o `toArray()` 
retornará estes campos por padrão. O método `extraFields()` define, de forma adicional, 
os campos disponíveis que também podem ser retornados pelo `toArray()`, contanto 
que sejam especificados através do parâmetro `$expand`. Por exemplo, o código a 
seguir retornará todos os campos definidos em `fields()` incluindo os campos 
`prettyName` e `fullAddress`, a menos que estejam definidos no `extraFields()`.

```php
$array = $model->toArray([], ['prettyName', 'fullAddress']);
```

Você poderá sobrescrever o método `fields()` para adicionar, remover, renomear ou 
redefinir os campos. O valor de retorno do `fields()` deve ser um array. As chaves 
do array não os nomes dos campos e os valores correspondem ao nome do atributo 
definido, na qual, podem ser tanto os nomes de propriedades/atributos quanto funções 
anônimas que retornam o valor dos campos correspondentes. Em um caso especial, 
quando o nome do campo for igual ao nome do atributo definido, você poderá omitir 
a chave do array. Por exemplo,

```php
// usar uma lista explicita de todos os campos lhe garante que qualquer mudança 
// em sua tabela do banco de dados ou atributos do model (modelo) não altere os 
// nomes de seus campos (para manter compatibilidade com versões anterior da API).
public function fields()
{
    return [
        // o nome do campos é igual ao nome do atributo
        'id',

        // o nome do campo é "email", o nome do atributo correspondente é "email_address"
        'email' => 'email_address',

        // o nome do campo é "name", o seu valor é definido por uma função call-back do PHP
        'name' => function () {
            return $this->first_name . ' ' . $this->last_name;
        },
    ];
}

// filtra alguns campos, é bem usado quando você quiser herdar a implementação 
// da classe pai e remover alguns campos delicados.
public function fields()
{
    $fields = parent::fields();

    // remove os campos que contém informações delicadas
    unset($fields['auth_key'], $fields['password_hash'], $fields['password_reset_token']);

    return $fields;
}
```

> Atenção: Como, por padrão, todos os atributos de um model (modelo) serão 
>incluídos no array exportado, você deve examinar seus dados para ter certeza 
>que não possuem informações delicadas. Se existir, deverá sobrescrever o método 
>`fields()` para remove-los. No exemplo anterior, nós decidimos remover os 
>campos `auth_key`, `password_hash` e `password_reset_token`.


## Boas Práticas <span id="best-practices"></span>

A representação dos dados, regras e lógicas de negócios estão centralizados nos 
models (modelos). Muitas vezes precisam ser reutilizadas em lugares diferentes. 
Em um aplicativo bem projetado, models (modelos) geralmente são muitos maiores 
que os [controllers](structure-controllers.md)

Em resumo, os models (modelos):

* podem conter atributos para representar os dados de negócio;
* podem conter regras de validação para garantir a validade e integridade dos dados;
* podem conter métodos para implementar lógicas de negócio;
* NÃO devem acessar diretamente as requisições, sessões ou quaisquer dados do 
ambiente do usuário. Os models (modelos) devem receber estes dados a partir dos 
[controllers (controladores)](structure-controllers.md);
* devem evitar inserir HTML ou outros códigos de apresentação – isto deve ser 
feito nas [views (visões)](structure-views.md);
* devem evitar ter muitos [cenários](#scenarios) em um único model (modelo).

Você deve considerar em utilizar com mais frequência a última recomendação acima 
quando desenvolver sistemas grandes e complexos.
Nestes sistemas, os models (modelos) podem ser bem grandes, pois são usados em 
muitos lugares e podendo, assim, conter muitas regras e lógicas de negócio. 
Nestes casos, a manutenção do código de um model (modelo) pode se transformar 
em um pesadelo, na qual uma simples mudança no código pode afetar vários lugares 
diferentes. Para desenvolver um model (modelo) manutenível, você pode seguir a 
seguinte estratégia: 

* Definir um conjunto de classes model (modelo) base que são compartilhados por 
diferentes [aplicações](structure-applications.md) ou [módulos](structure-modules.md). 
Estas classes model (modelo) base deve contem um conjunto mínimo de regras e lógicas 
de negocio que são comuns entre os locais que as utilizem.
* Em cada [aplicação](structure-applications.md) ou [módulo](structure-modules.md) 
que usa um model (modelo), deve definir uma classe model (modelo) concreta que 
estenderá a classe model (modelo) base que a corresponde. A classe model (modelo) 
concreta irá conter apenas as regras e lógicas que são específicas de uma aplicação 
ou módulo.

Por exemplo, no [Template Avançado de Projetos](tutorial-advanced-app.md), você 
pode definir uma classe model (modelo) base `common\models\Post`. Em seguida, 
para a aplicação front-end, você define uma classe model (modelo) concreta 
`frontend\models\Post` que estende de `common\models\Post`. E de forma similar 
para a aplicação back-end, você define a `backend\models\Post`. Com essa estratégia, 
você garantirá que o `frontend\models\Post` terá apenas códigos específicos da 
aplicação front-end e, se você fizer qualquer mudança nele, não precisará se 
preocupar se esta mudança causará erros na aplicação back-end.
