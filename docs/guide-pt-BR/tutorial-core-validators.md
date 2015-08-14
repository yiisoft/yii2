Validadores do núcleo
==================

Yii fornece um conjunto de validadores do núcleo comumente usados, encontrados principalmente sob o namespace `yii\validators`. Em vez de usar nomes de classe de validador longos, você pode usar *aliases* para especificar o uso desses validadores. Por exemplo, você pode usar o alias `required` para referenciar a classe [[yii\validators\RequiredValidator]]:

```php
public function rules()
{
   return [
       [['email', 'password'], 'required'],
   ];
}
```

A propriedade [[yii\validators\Validator::builtInValidators]] declara todos os aliases de validação suportados.

A seguir, descreveremos o uso principal e as propriedades de cada um desses validadores.


## [[yii\validators\BooleanValidator|boolean]] <span id="boolean"></span>

```php
[
   // Verifica se "selected" é 0 ou 1, independentemente do tipo de dados
   ['selected', 'boolean'],

   // verifica se "deleted" é um tipo boolean, e se é verdadeiro ou Falso
   ['deleted', 'boolean', 'trueValue' => true, 'falseValue' => false, 'strict' => true],
]
```

Este validador verifica se o valor de entrada é um booleano.

- `trueValue`: o valor representando *true*. O padrão é `'1'`.
- `falseValue`: o valor representando *false*. O padrão é `'0'`.
- `strict`: se o tipo do valor de entrada deve corresponder ao `trueValue` e `falseValue`. O padrão é `false`.


> Observação: Porque a entrada de dados enviados através de formulários HTML são todos strings, você normalmente deve deixar a propriedade [[yii\validators\BooleanValidator::strict|strict]] como falso.


## [[yii\captcha\CaptchaValidator|captcha]] <span id="captcha"></span>

```php
[
   ['verificationCode', 'captcha'],
]
```

Este validador é geralmente usado junto com [[yii\captcha\CaptchaAction]] e [[yii\captcha\Captcha]] para se certificar de que a entrada de dados é igual ao código de verificação exibido pelo widget [[yii\captcha\Captcha|CAPTCHA]].

- `caseSensitive`: se a comparação da verificação de código for case sensitivo. O padrão é falso.
- `captchaAction`: a [rota](structure-controllers.md#routes) correspondente ao [[yii\captcha\CaptchaAction|CAPTCHA action]] que renderiza as imagens. O padrão é `'site/captcha'`.
- `skipOnEmpty`: se a validação pode ser ignorada se a entrada estiver vazia. O padrão é Falso,
 o que significa que a entrada é obrigatória.


## [[yii\validators\CompareValidator|compare]] <span id="compare"></span>

```php
[
   // valida se o valor do atributo "password"  é igual a "password_repeat"
   ['password', 'compare'],

   // valida se a idade é maior do que ou igual a 30
   ['age', 'compare', 'compareValue' => 30, 'operator' => '>='],
]
```

Este validador compara o valor de entrada especificado com um outro e certificar-se se a sua relação está como especificado pela propriedade `operator`.

- `compareAttribute`: o nome do atributo cujo valor deve ser comparado. Quando o validador está sendo usado para validar um atributo, o valor padrão dessa propriedade seria o nome do atributo com o sufixo `_repeat`. Por exemplo, se o atributo que está sendo validado é `password`, Estão esta propriedade será por padrão `password_repeat`.
- `compareValue`: um valor constante com o qual o valor de entrada deve ser comparado. When esta property e `compareAttribute` são especificadas, `compareValue` terá precedência.
- `operator`: o operador de comparação. O padrão é `==`, ou seja, verificar se o valor de entrada é igual ao do `compareAttribute` ou `compareValue`. Os seguintes operadores são suportados:
    * `==`: verifica se dois valores são iguais. A comparação é feita no modo non-strict.
    * `===`: verifica se dois valores são iguais. A comparação é feita no modo strict.
    * `!=`: verifica se dois valores NÃO são iguais. A comparação é feita no modo non-strict.
    * `!==`: verifica se dois valores NÃO são iguais. A comparação é feita no modo strict.
    * `>`: verifica se o valor que está sendo validado é maior do que o valor que está sendo comparado.
    * `>=`: verifica se o valor que está sendo validado é maior ou igual ao valor que está sendo comparado.
    * `<`: verifica se o valor que está sendo validado é menor do que o valor que está sendo comparado.
    * `<=`: verifica se o valor que está sendo validado menor ou igual ao valor que está sendo comparado.


## [[yii\validators\DateValidator|date]] <span id="date"></span>

```php
[
   [['from_date', 'to_date'], 'date'],
]
```

Este validador verifica se o valor de entrada é uma data, tempo ou data e hora em um formato adequado. Opcionalmente, pode converter o valor de entrada para um UNIX timestamp ou outro formato legível e armazená-lo em um atributo especificado via [[yii\validators\DateValidator::timestampAttribute|timestampAttribute]].

- `format`: o formato date/time que o valor que está sendo validado deve ter. Este pode ser um padrão de data e hora conforme descrito no [ICU manual] (http://userguide.icu-project.org/formatparse/datetime#TOC-Date-Time-Format-Syntax). Alternativamente esta pode ser uma string com o prefixo `php:` representando um formato que pode ser reconhecido pela classe PHP `Datetime`. Por favor, consulte <http://php.net/manual/en/datetime.createfromformat.php> para formatos suportados. Se isso não for definido, ele terá o valor de `Yii::$app->formatter->dateFormat`. Consulte a [[yii\validators\DateValidator::$format|API documentation]] para mais detalhes.

- `timestampAttribute`: o nome do atributo para que este validador possa atribuir o UNIX  timestamp convertido a partir da entrada de data / hora. Este pode ser o mesmo atributo que está sendo validado. Se este for o caso,  valor original será substituído pelo valor timestamp após a validação. Veja ["Handling date input with the DatePicker"] (https://github.com/yiisoft/yii2-jui/blob/master/docs/guide/topics-date-picker.md) Para exemplos de uso.

Desde a versão 2.0.4, um formato e fuso horário pode ser especificado para esse atributo utilizando [[yii\validators\DateValidator::$timestampAttributeFormat|$timestampAttributeFormat]] e [[yii\validators\DateValidator::$timestampAttributeTimeZone|$timestampAttributeTimeZone]].

- Desde a versão 2.0.4 também é possível definir um timestamp [[yii\validators\DateValidator::$min|minimum]] ou [[yii\validators\DateValidator::$max|maximum]].

Caso a entrada seja opcional, você também pode querer adicionar um [default value filter](#default) para o validador de data para garantir que entradas vazias sejam armazenadas com `NULL`. De outra forma você pode terminar com datas como `0000-00-00` no seu banco de dados ou `1970-01-01` no campo de entrada de um date picker.

```php
[
   [['from_date', 'to_date'], 'default', 'value' => null],
   [['from_date', 'to_date'], 'date'],
],
```

## [[yii\validators\DefaultValueValidator|default]] <span id="default"></span>

```php
[
   // configura "age" para ser null se este for vazio
   ['age', 'default', 'value' => null],

   // configura "country" para ser "USA" se este for vazio
   ['country', 'default', 'value' => 'USA'],

   // atribui "from" e "to" com uma data de 3 dias e 6 dias a partir de hoje, se estiverem vazias
   [['from', 'to'], 'default', 'value' => function ($model, $attribute) {
       return date('Y-m-d', strtotime($attribute === 'to' ? '+3 days' : '+6 days'));
   }],
]
```

Este validador não valida dados. Em vez disso, atribui um valor padrão para os atributos que estão sendo validados caso estejam vazios.

- `value`: o valor padrão ou um PHP callable que retorna o valor padrão que irá ser atribuído aos atributos que estão sendo validados caso estejam vazios. A assinatura do PHP callable deve ser como a seguir,

```php
function foo($model, $attribute) {
   // ... computar $value ...
   return $value;
}
```

> Observação: Como determinar se um valor está vazio ou não é um tópico separado descrito na seção [Empty Values](input-validation.md#handling-empty-inputs).


## [[yii\validators\NumberValidator|double]] <span id="double"></span>

```php
[
   // checks if "salary" is a double number
   ['salary', 'double'],
]
```

Este validados verifica se o valor de entrada é um double. É equivalente ao validador [number](#number).

- `max`: o limite superior do valor (inclusive). Se não configurado, significa que o validador não verifica o limite superior.
- `min`: o limite inferior do valor (inclusive). Se não configurado, significa que o validador não verifica o limite inderior.


## [[yii\validators\EachValidator|each]] <span id="each"></span>

> Observação: Este validador está disponível desde a versão 2.0.4.

```php
[
   // verifica se todos category 'categoryIDs' são 'integer'
   ['categoryIDs', 'each', 'rule' => ['integer']],
]
```

Este validador só funciona com um atributo array. Ele valida *todos* os elementos do array com uma regra de validação especificada. No exemplo acima, o atributo `categoryIDs` deve ter um array e cada elemento do array será validado pela regra de validação  `integer`.

- `rule`: um array especificando uma regra de validação. O primeiro elemento do array determina o nome da classe ou o alias do validador. O resto dos pares nome-valor no array são usados para configurar o objeto validador.
- `allowMessageFromRule`: se pretende usar a mensagem de erro retornada pela regra de validação incorporada. Padrão é true. Se for false, ele usará `message` como a mensagem de erro.

> Observação: Se o valor do atributo não for um array, a validação será considerada como falha e a `mensagem` será retornada como erro.


## [[yii\validators\EmailValidator|email]] <span id="email"></span>

```php
[
   // verifica se o "email" é um endereço de email válido
   ['email', 'email'],
]
```

Este validador verifica se o valor de entrada é um endereço de email válido.

- `allowName`: permitir nome no endereço de email (ex. `John Smith <john.smith@example.com>`). O padrão é false.
- `checkDNS`, para verificar se o domínio do e-mail existe e tem tanto um A ou registro MX. Esteja ciente de que esta verificação pode falhar devido a problemas de DNS temporários, mesmo se o endereço de e-mail for realmente válido. O padrão é false
- `enableIDN`, se o processo de validação deve verificar uma conta IDN (internationalized domain names). O padrão é false. Note que para usar a validação IDN você deve instalar e habilitar a extensão PHP `intl`, ou uma exceção será lançada.


## [[yii\validators\ExistValidator|exist]] <span id="exist"></span>

```php
[
   // a1 precisa existir na coluna representada pelo atributo "a1"
   ['a1', 'exist'],

   // a1 precisa existir, mas seu valor irá usar a2 para verificar a existência
   ['a1', 'exist', 'targetAttribute' => 'a2'],

   // a1 e a2 precisam existir juntos, e ambos receberão mensagem de erro
   [['a1', 'a2'], 'exist', 'targetAttribute' => ['a1', 'a2']],

   // a1 e a2 precisam existir juntos, somente a1 receberá mensagem de erro
   ['a1', 'exist', 'targetAttribute' => ['a1', 'a2']],

   // a1 precisa existir, verificando a existência de ambos A2 e A3 (usando o valor de a1)
   ['a1', 'exist', 'targetAttribute' => ['a2', 'a1' => 'a3']],

   // a1 precisa existir. Se a1 for um array, então todos os seus elementos devem existir.
   ['a1', 'exist', 'allowArray' => true],
]
```

Este validador verifica se o valor de entrada pode ser encontrado em uma coluna representada por um atributo [Active Record](db-active-record.md). Você pode usar `targetAttribute` para especificar o atributo [Active Record](db-active-record.md) e `targetClass` a classe [Active Record](db-active-record.md) correspondente. Se você não especificá-los, eles vão receber os valores do atributo e a classe modelo que está sendo validada.

Você pode usar este validador para validar uma ou várias colunas (ex., a combinação de múltiplos valores de atributos devem existir).

- `targetClass`: o nome da classe [Active Record](db-active-record.md) que deve ser usada para procurar o valor de entrada que está sendo validado. Se não for configurada, a atual classe do modelo que está sendo validado será usada.
- `targetAttribute`: o nome do atributo em `targetClass` que deve ser utilizado para validar a existência do valor de entrada. Se não for configurado, será usado o nome do atual atributo que está sendo validado. Você pode utilizar um array para validar a existência de múltiplas colunas ao mesmo tempo. Os valores do array são os atributos que serão utilizados para validar a existência, enquanto as chaves são os atributos cujos valores devem ser validados. Se a chave e o valor forem os mesmos, você pode especificar apenas o valor.
- `filter`: filtro adicional para ser aplicado na consulta DB utilizada para verificar a existência do valor de entrada. Pode ser uma string ou um array representando a condição de consulta adicional (consulte [[yii\db\Query::where()]] formato de condição de consulta), ou uma função anônima com a assinatura `function ($query)`, onde `$query` é o objeto  [[yii\db\Query|Query]] que você pode modificar na função.
- `allowArray`: se permitir que o valor de entrada seja um array. Padrão é false. Se esta propriedade for true e a entrada for um array, então, cada elemento do array deve existir na coluna alvo. Observe que essa propriedade não pode ser definida como true se você estiver validando várias colunas configurando `targetAttribute` como um array.

## [[yii\validators\FileValidator|file]] <span id="file"></span>

```php
[
   // verifica se "primaryImage" é um arquivo de imagem carregado no formato PNG, JPG ou GIF.
   // o tamanho do arquivo deve ser inferior a 1MB
   ['primaryImage', 'file', 'extensions' => ['png', 'jpg', 'gif'], 'maxSize' => 1024*1024],
]
```

Este validador verifica se a entrada é um arquivo enviado válido.

- `extensions`: uma lista de extensões de nome de arquivo que são permitidos no upload. Pode ser um array ou uma string que consiste em nomes de extensão de arquivos separados por espaço ou vírgula (Ex. "gif, jpg"). Nomes de extensões são case-insensitive. O padrão é null, significa que todas as extensões são permitidas.
- `mimeTypes`: uma lista de tipos de arquivos MIME que são permitidos no upload. Pode ser tanto um array como uma string consiste de tipos de MIME separados per espaço ou virgula (ex. "image/jpeg, image/png"). Nomes de tipos de Mime são case-insensitivo. O padrão é null, significa que todos os tipos de MIME são permitidos. Para mais detalhes, consulte [common media types](http://en.wikipedia.org/wiki/Internet_media_type#List_of_common_media_types).
- `minSize`: o número mínimo de bytes exigido para o arquivo carregado. O padrão é null, significa não ter limite mínimo.
- `maxSize`: o número máximo de bytes exigido para o arquivo carregado. O padrão é null, significa não ter limite máximo.
- `maxFiles`: o número máximo de arquivos que o atributo pode receber. O padrão é 1, ou seja, a entrada deve ser de um único arquivo. Se for maior que 1, então a entrada deve ser um array que consiste em, no máximo `maxFiles` números de arquivos.
- `checkExtensionByMimeType`: verificação da extensão do arquivo por tipo MIME do arquivo. Se a extensão produzido pela verificação do tipo MIME difere da extensão do arquivo carregado, o arquivo será considerado inválido. O padrão é true, o que significa realizar tal verificação.

`FileValidator` é usado junto com [[yii\web\UploadedFile]]. consulte a seção  [Uploading Files](input-file-upload.md) para mais informações sobre o upload de arquivos e de uma validação sobre os arquivos carregados.


## [[yii\validators\FilterValidator|filter]] <span id="filter"></span>

```php
[
   // trima as entradas "username" e "email"
   [['username', 'email'], 'filter', 'filter' => 'trim', 'skipOnArray' => true],

   // normaliza a entrada "phone"
   ['phone', 'filter', 'filter' => function ($value) {
       // normaliza a entrada phone aqui
       return $value;
   }],
]
```

Este validador não valida dados. Em vez disso, aplica um filtro no valor de entrada e retorna para o atributo que está sendo validado.

- `filter`: um PHP callback que define um filtro. Pode ser um nome de função global, uma função anônima, etc. A assinatura da function deve ser `function ($value) { return $newValue; }`. Esta propriedade deve ser definida.
- `skipOnArray`: para ignorar o filtro se o valor de entrada for um array. O padrão é false. Observe que se o filtro não puder manipular a entrada de array, você deve configurar esta propriedade como true. De outra forma algum PHP erro deve ocorrer.

> Dica: Se você quiser trimar valores de entrada, você deve utilizar o validador [trim](#trim).

> Dica: Existem várias funções PHP que tem a assinatura esperada para o `filter` callback.
> Por exemplo, para aplicar a conversão de tipos (usando por exemplo [intval](http://php.net/manual/en/function.intval.php),
> [boolval](http://php.net/manual/en/function.boolval.php), ...) para garantir um tipo específico para um atributo,
> você pode simplesmente especificar os nomes das funções do filtro sem a necessidade de envolvê-los em um closure:
>
> ```php
> ['property', 'filter', 'filter' => 'boolval'],
> ['property', 'filter', 'filter' => 'intval'],
> ```


## [[yii\validators\ImageValidator|image]] <span id="image"></span>

```php
[
   // verifica se "primaryImage" é ima imagem válida com as proporções adequadas
   ['primaryImage', 'image', 'extensions' => 'png, jpg',
       'minWidth' => 100, 'maxWidth' => 1000,
       'minHeight' => 100, 'maxHeight' => 1000,
   ],
]
```

Este validador verifica se o valor de entrada representa um arquivo de imagem válido. Ele se estende do validador [file](#file) herdando todas as suas propriedades. Além Disso, suporta as seguintes propriedades adicionais específicas para fins de validação de imagem:

- `minWidth`: a largura mínima da imagem. O padrão é null, significa não ter limite mínimo.
- `maxWidth`: a largura máxima da imagem. O padrão é null, significa não ter limite máximo.
- `minHeight`: a altura mínima da imagem. O padrão é null, significa não ter limite mínimo.
- `maxHeight`: a altura máxima da imagem. O padrão é null, significa não ter limite máximo.


## [[yii\validators\RangeValidator|in]] <span id="in"></span>

```php
[
   // checks if "level" is 1, 2 or 3
   ['level', 'in', 'range' => [1, 2, 3]],
]
```

Este validador verifica se o valor de entrada pode ser encontrado entre os valores da lista fornecida.

- `range`: uma lista de determinados valores dentro da qual o valor de entrada deve ser procurado.
- `strict`: se a comparação entre o valor de entrada e os valores dados devem ser strict
 (o tipo e o valor devem ser identicos). O padrão é false.
- `not`: se o resultado de validação deve ser invertido. O padrão é false. Quando esta propriedade é definida como true, o validador verifica se o valor de entrada NÃO está entre os valores da lista fornecida.
- `allowArray`: para permitir que o valor de entrada seja um array. quando esta propriedade é marcada como true e o valor de entrada é um array, todos os elementos neste array devem ser encontrados na lista de valores fornecida, ou a validação irá falhar.


## [[yii\validators\NumberValidator|integer]] <span id="integer"></span>

```php
[
   // verifica se "age" é um inteiro
   ['age', 'integer'],
]
```

Este validador verifica se o valor de entrada é um inteiro.

- `max`: limite máximo (inclusive) do valor. Se não for configurado, significa que não tem verificação de limite máximo.
- `min`: o limite mínimo (inclusive) do valor. Se não for configurado, significa que não tem verificação de limite mínimo.


## [[yii\validators\RegularExpressionValidator|match]] <span id="match"></span>

```php
[
   // verifica se "username" começa com uma letra e contem somente caracteres
   ['username', 'match', 'pattern' => '/^[a-z]\w*$/i']
]
```

este validador verifica se o valor de entrada atende a expressão regular especificada.

- `pattern`: a expressão regular que o valor de entrada deve corresponder. Esta propriedade deve ser configurada, ou uma exceção será lançada.
- `not`: para inverter o resultado da validação. O padrão é false, significa que a validação terá sucesso apenas se o valor de entrada corresponder ao padrão definido. Se for configurado como true a validação terá sucesso apenas se o valor de entrada NÃO corresponder ao padrão definido.

## [[yii\validators\NumberValidator|number]] <span id="number"></span>

```php
[
   // verifica se "salary" é um number
   ['salary', 'number'],
]
```

este validador verifica se o valor de entrada é um number. É equivalente ao validador [double](#double).

- `max`: limite máximo (inclusive) do valor. Se não for configurado, significa que não tem verificação de limite máximo.
- `min`: o limite mínimo (inclusive) do valor. Se não for configurado, significa que não tem verificação de limite mínimo.

## [[yii\validators\RequiredValidator|required]] <span id="required"></span>

```php
[
   // verifica se ambos "username" e "password" não estão vazios
   [['username', 'password'], 'required'],
]
```

Este validador verifica de o valor de entrada foi fornecido e não está vazio.

- `requiredValue`: o valor desejado que a entrada deve ser. Se não configurado, isso significa que o valor de entrada apenas não deve estar vazio.
- `strict`: para verificar os tipos de dados ao validar um valor. O padrão é false. Quando `requiredValue` não é configurado, se esta propriedade for true, o validador verificará se o valor de entrada não é estritamente nulo; Se esta propriedade for false, o validador irá usar uma regra solta para determinar se o valor está vazio ou não. Quando `requiredValue` está configurado, a comparação entre o valor de entrada e `requiredValue` também irá verificar os tipos de dados se esta propriedade for true.

> Observação: Como determinar se um valor está vazio ou não é um tópico separado descrito em [Empty Values](input-validation.md#handling-empty-inputs) section.


## [[yii\validators\SafeValidator|safe]] <span id="safe"></span>

```php
[
   // marks "description" to be a safe attribute
   ['description', 'safe'],
]
```

Este validador não executa validação de dados. Em vez disso, ele é usado para marcar um atributo para ser um [atributo seguro](structure-models.md#safe-attributes).


## [[yii\validators\StringValidator|string]] <span id="string"></span>

```php
[
   // verifica se "username" é uma string cujo tamanho está entre 4 e 24
   ['username', 'string', 'length' => [4, 24]],
]
```

Este validador verifica se o valor de entrada é uma string válida com um determinado tamanho.

- `length`: especifica o limite de comprimento da string de entrada que está sendo validada. Este pode ser especificado em uma das seguintes formas:
    * um inteiro: o comprimento exato que a string deverá ter;
    * um array de um elemento: o comprimento mínimo da string de entrada (ex. `[8]`). Isso substituirá `min`.
    * um array de dois elementos: o comprimento mínimo e máximo da string de entrada (ex. `[8, 128]`). Isso substituirá ambos `min` e `max`.
- `min`: o comprimento mínimo da string de entrada. se não configurado, significa não ter limite para o comprimento mínimo.
- `max`: o comprimento máximo da string de entrada. se não configurado, significa não ter limite para o comprimento máximo.
- `encoding`: a codificação da string de entrada a ser validada. se não configurado, será usado o valor de [[yii\base\Application::charset|charset]] da aplicação que por padrão é  `UTF-8`.


## [[yii\validators\FilterValidator|trim]] <span id="trim"></span>

```php
[
   // trima os espaçõs em branco ao redor de "username" e "email"
   [['username', 'email'], 'trim'],
]
```

Este validador não executa validação de dados. Em vez disso, ele vai retirar os espaços em branco ao redor do valor de entrada. Note que se o valor de entrada for um array, ele será ignorado pelo validador.


## [[yii\validators\UniqueValidator|unique]] <span id="unique"></span>

```php
[
  // a1 precisa ser único na coluna representada pelo atributo  "a1"
  ['a1', 'unique'],

  // a1 precisa ser único, mas a coluna a2 será usada para verificar a singularidade do valor de a1
  ['a1', 'unique', 'targetAttribute' => 'a2'],

  // a1 e a2 precisam ser únicos, e ambos receberão mensagem de erro
  [['a1', 'a2'], 'unique', 'targetAttribute' => ['a1', 'a2']],

   // a1 e a2 precisam ser únicos, mas somente ‘a1’ receberá mensagem de erro
  ['a1', 'unique', 'targetAttribute' => ['a1', 'a2']],

  // a1 precisa ser único verificando a singularidade de ambos a2 e a3 (usando o valor de a1)
  ['a1', 'unique', 'targetAttribute' => ['a2', 'a1' => 'a3']],
]
```

Este validador verifica se o valor de entrada é único na coluna da tabela. Ele só trabalha com atributos do modelo [Active Record](db-active-record.md). Suporta a validação de uma única coluna ou de várias.

- `targetClass`: o nome da classe [Active Record](db-active-record.md) que deve ser usada para procurar o valor de imput que está sendo validado. Se não for configurado, a classe do modelo corrente que está sendo validado será usada.
- `targetAttribute`: o nome do atributo em `targetClass` que deve ser usado para validar a singularidade do valor de entrada. Se não for configurado, este usará o nome do atributo corrente que está sendo validado. Você pode usar um array ta validar a sibgularidáde de várias colunas ao mesmo tempo. Os valores do array são os atributos que serão usados para validar a singularidade, enquanto as cheves do array são os atributos cujos valores serão validados. Se a chave e o valor forem os mesmos, você pode apenas especificar o valor.
- `filter`: filtro adicional para ser aplicado na query DB usada para validar a singularidade do valor de entrada. Este pode ser uma string ou um array representando a condição adicional da query (consulte [[yii\db\Query::where()]] no formato de condição de consulta), ou uma função anônima com a assinatura `function ($query)`, onde `$query` é o objeto [[yii\db\Query|Query]] que você pode modificar na função.


## [[yii\validators\UrlValidator|url]] <span id="url"></span>

```php
[
   // verifica se "website" é uma URL válida. Coloca "http://" no atributo "website"
   // e ele não tiver um esquema de URI
   ['website', 'url', 'defaultScheme' => 'http'],
]
```

|Este validador verifica se o valor de entrada é uma URL válida.

- `validSchemes`: um array especificando o esquema de URI que deve ser considerada válida. O padrão é `['http', 'https']`, significa que ambos `http` e `https` URLs são considerados como válidos.
- `defaultScheme`: o esquema de URI padrão para ser anexado à entrada, se a parte do esquema não for informada na entrada. O padrão é null, significa que o valor de entrada não será modificado.
- `enableIDN`: se o validador deve ter uma conta IDN (internationalized domain names). O padrão é false. Observe que para usar a validação IDN você tem que instalar e ativar a extenção PHP `intl`, caso contrário uma exceção será lançada.


