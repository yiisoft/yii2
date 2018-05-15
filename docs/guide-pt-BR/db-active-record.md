Active Record
=============

O [Active Record](http://en.wikipedia.org/wiki/Active_record_pattern) fornece uma interface orientada a objetos para acessar e manipular dados armazenados em bancos de dados. Uma classe Active Record está associado a uma tabela da base de dados, uma instância do Active Record corresponde a uma linha desta tabela, e um *atributo* desta instância representa o valor de uma coluna desta linha. Em vez de escrever instruções SQL a mão, você pode acessar os atributos do Active Record e chamar os métodos do Active Record para acessar e manipular  os dados armazenados nas tabelas do banco de dados.

Por exemplo, assumindo que `Customer` é uma classe Active Record que está associada com a tabela `customer` e `name` é uma coluna desta tabela. Você pode escrever o seguinte código para inserir uma nova linha na tabela `customer`:

```php
$customer = new Customer();
$customer->name = 'Qiang';
$customer->save();
```

O código acima é equivalente a seguinte instrução SQL escrita à mão para MySQL, que é menos intuitiva, mais propenso a erros, e pode até ter problemas de compatibilidade se você estiver usando um tipo diferente de banco de dados:

```php
$db->createCommand('INSERT INTO `customer` (`name`) VALUES (:name)', [
   ':name' => 'Qiang',
])->execute();
```

O Yii fornece suporte Active Record para os seguintes bancos de dados relacionais:

* MySQL 4.1 ou superior: via [[yii\db\ActiveRecord]]
* PostgreSQL 8.4 ou superior: via [[yii\db\ActiveRecord]]
* SQLite 2 e 3: via [[yii\db\ActiveRecord]]
* Microsoft SQL Server 2008 ou superior: via [[yii\db\ActiveRecord]]
* Oracle: via [[yii\db\ActiveRecord]]
* Sphinx: via [[yii\sphinx\ActiveRecord]], requer a extensão `yii2-sphinx`
* ElasticSearch: via [[yii\elasticsearch\ActiveRecord]], requer a extensão `yii2-elasticsearch`. Adicionalmente, o Yii também suporta o uso de Active Record com os seguintes bancos de dados NoSQL:

* Redis 2.6.12 ou superior: via [[yii\redis\ActiveRecord]], requer a extensão `yii2-redis`
* MongoDB 1.3.0 ou superior: via [[yii\mongodb\ActiveRecord]], requer a extensão `yii2-mongodb`

Neste tutorial, vamos principalmente descrever o uso do Active Record para banco de dados relacionais. Todavia, maior parte do conteúdo descrito aqui também são aplicáveis a Active Record para bancos de dados NoSQL.


## Declarando Classes Active Record <span id="declaring-ar-classes"></span>

Para começar, declare uma classe Active Record estendendo [[yii\db\ActiveRecord]]. Porque cada classe Active Record é associada a uma tabela do banco de dados, nesta classe você deve sobrescrever o método [[yii\db\ActiveRecord::tableName()|tableName()]] para especificar a tabela que a classe está associada.

No exemplo abaixo, declaramos uma classe Active Record chamada `Customer` para a tabela do banco de dados `customer`.

```php
namespace app\models;

use yii\db\ActiveRecord;

class Customer extends ActiveRecord
{
   const STATUS_INACTIVE = 0;
   const STATUS_ACTIVE = 1;
   
   /**
    * @return string the name of the table associated with this ActiveRecord class.
    */
   public static function tableName()
   {
       return 'customer';
   }
}
```

Instâncias de Active Record são consideradas como [models (modelos)](structure-models.md). Por esta razão, geralmente colocamos as classes Active Record debaixo do namespace  `app\models` (ou outros namespaces destinados a classes model). 

Porque [[yii\db\ActiveRecord]] estende a partir de [[yii\base\Model]], ele herda *todas* as características de [model](structure-models.md), tal como atributos, regras de validação, serialização de dados, etc.


## Conectando ao Banco de Dados <span id="db-connection"></span>

Por padrão, o Active Record usa o [componente de aplicação](structure-application-components.md) `db` com a [[yii\db\Connection|DB connection]] para acessar e manipular os dados da base de dados. Como explicado em [Database Access Objects](db-dao.md), você pode configurar o componente `db` na configuração da aplicação como mostrado abaixo,

```php
return [
   'components' => [
       'db' => [
           'class' => 'yii\db\Connection',
           'dsn' => 'mysql:host=localhost;dbname=testdb',
           'username' => 'demo',
           'password' => 'demo',
       ],
   ],
];
```

Se você quiser usar uma conexão de banco de dados diferente do que o componente `db`, você deve sobrescrever o método [[yii\db\ActiveRecord::getDb()|getDb()]]:

```php
class Customer extends ActiveRecord
{
   // ...

   public static function getDb()
   {
       // use the "db2" application component
       return \Yii::$app->db2;  
   }
}
```


## Consultando Dados <span id="querying-data"></span>

Depois de declarar uma classe Active Record, você pode usá-lo para consultar dados da tabela de banco de dados correspondente. Este processo geralmente leva os seguintes três passos:

1. Crie um novo objeto query chamando o método [[yii\db\ActiveRecord::find()]];
2. Construa o objeto query chamando os [métodos de query building](db-query-builder.md#building-queries);
3. Chame um [método de query](db-query-builder.md#query-methods) para recuperar dados em uma instância do Active Record.

Como você pode ver, isso é muito semelhante ao procedimento com [query builder](db-query-builder.md). A única diferença é que em vez de usar o operador `new` para criar um objeto query, você chama [[yii\db\ActiveRecord::find()]] para retornar um novo objeto query que é da classe [[yii\db\ActiveQuery]].

A seguir, estão alguns exemplos que mostram como usar Active Query para pesquisar dados:

```php
// retorna um único customer cujo ID é 123
// SELECT * FROM `customer` WHERE `id` = 123
$customer = Customer::find()
   ->where(['id' => 123])
   ->one();

// retorna todos customers ativos e os ordena por seus IDs
// SELECT * FROM `customer` WHERE `status` = 1 ORDER BY `id`
$customers = Customer::find()
   ->where(['status' => Customer::STATUS_ACTIVE])
   ->orderBy('id')
   ->all();

// retorna a quantidade de customers ativos
// SELECT COUNT(*) FROM `customer` WHERE `status` = 1
$count = Customer::find()
   ->where(['status' => Customer::STATUS_ACTIVE])
   ->count();

// retorna todos customers em um array indexado pelos seus IDs
// SELECT * FROM `customer`
$customers = Customer::find()
   ->indexBy('id')
   ->all();
```

No exemplo acima, `$customer` é um objeto `Customer` enquanto `$customers` é um array de objetos `Customer`. Todos são preenchidos com os dados recuperados da tabela `customer`.

> Observação: Uma vez que o [[yii\db\ActiveQuery]] estende de [[yii\db\Query]], você pode usar *todos* os métodos do query building e da query tal como descrito na seção [Query Builder](db-query-builder.md).

Já que consultar por valores de chave primária ou um conjunto de valores de coluna é uma tarefa comum, o Yii fornece dois métodos de atalho para este propósito:

- [[yii\db\ActiveRecord::findOne()]]: retorna uma única instância de Active Record populado com a primeira linha do resultado da query.
- [[yii\db\ActiveRecord::findAll()]]: retorna um array de instâncias de Active Record populados com *todo* o resultado da query.

Ambos os métodos pode ter um dos seguintes formatos de parâmetro:

- Um valor escalar: o valor é tratado como uma chave primária que se deseja procurar. O Yii irá determinar automaticamente que coluna é a chave primária lendo o schema da base de dados.
- Um array de valores escalar: o array como uma chaves primárias que se deseja procurar.
- Um array associativo: as chaves são nomes de colunas e os valores são os valores correspondentes as colunas que se deseja procurar. Por favor, consulte o [Hash Format](db-query-builder.md#hash-format) para mais detalhes.
 
O código a seguir mostra como estes métodos podem ser usados:

```php
// retorna um único customer cujo ID é 123
// SELECT * FROM `customer` WHERE `id` = 123
$customer = Customer::findOne(123);

// retorna customers cujo ID é 100, 101, 123 or 124
// SELECT * FROM `customer` WHERE `id` IN (100, 101, 123, 124)
$customers = Customer::findAll([100, 101, 123, 124]);

// retorna um customer ativo cujo ID é 123
// SELECT * FROM `customer` WHERE `id` = 123 AND `status` = 1
$customer = Customer::findOne([
   'id' => 123,
   'status' => Customer::STATUS_ACTIVE,
]);

// retorna todos os customers inativos
// SELECT * FROM `customer` WHERE `status` = 0
$customers = Customer::findAll([
   'status' => Customer::STATUS_INACTIVE,
]);
```

> Observação: Nem o [[yii\db\ActiveRecord::findOne()]] ou o [[yii\db\ActiveQuery::one()]] irão adicionar  `LIMIT 1` para a instrução SQL gerada. Se a sua query retornar muitas linhas de dados, você deve chamar `limit(1)` explicitamente para melhorar o desempenho, ex., `Customer::find()->limit(1)->one()`.

Além de usar os métodos do query building, você também pode escrever SQLs `puros` para pesquisar dados e preencher os objetos Active Record com o resultado. Você pode fazer isso chamando o método [[yii\db\ActiveRecord::findBySql()]]:

```php
// retorna todos os customers inatrivos
$sql = 'SELECT * FROM customer WHERE status=:status';
$customers = Customer::findBySql($sql, [':status' => Customer::STATUS_INACTIVE])->all();
```

Não chame outros métodos do query building depois de chamar [[yii\db\ActiveRecord::findBySql()|findBySql()]] pois eles serão ignorados.


## Acessando Dados <span id="accessing-data"></span>

Como já mencionado, os dados retornados da base de dados são populados em uma instância do Active Record, e cada linha do resultado da query  corresponde a uma única instância do Active Record. Você pode acessar os valores das colunas acessando os atributos da instância do Active Record, Por exemplo,

```php
// "id" and "email" são os nomes das colunas na tabela "customer"
$customer = Customer::findOne(123);
$id = $customer->id;
$email = $customer->email;
```

> Observação: Os atributos Active Record são nomeados após a associação com as colunas da tabela de uma forma case-sensitive. O Yii automaticamente define um atributo no Active Record para todas as colunas da tabela associada. Você NÃO deve declarar novamente qualquer um dos atributos. 

Uma vez que os atributos do Active Record são nomeados de acordo com as colunas das tabelas, você pode achar que está escrevendo código PHP como `$customer->first_name`, que usa sublinhados para separar palavras em nomes de atributos se as colunas da tabela forem nomeadas desta maneira. Se você está preocupado com um estilo de código consistente, você deve renomear suas colunas da tabela em conformidade (usar camelCase, por exemplo).


### Transformação de Dados (Data Transformation) <span id="data-transformation"></span>

Acontece frequentemente que os dados que estão sendo inseridos e/ou exibidos estão em um formato que é diferente do utilizado no momento da gravação na base de dados. Por exemplo, em uma base de dados você está gravando a data de aniversário do `customer` como UNIX timestamps (que não é muito amigável), embora, na maioria das vezes você gostaria de manipular aniversários como strings no formato de `'YYYY/MM/DD'`. Para atingir este objetivo, você pode definir métodos de *transformação de dados* na classe Active Record  `Customer` como a seguir:

```php
class Customer extends ActiveRecord
{
   // ...

   public function getBirthdayText()
   {
       return date('Y/m/d', $this->birthday);
   }
   
   public function setBirthdayText($value)
   {
       $this->birthday = strtotime($value);
   }
}
```

Agora no seu código PHP, em vez de acessar `$customer->birthday`, você acessaria `$customer->birthdayText`, que lhe permitirá inserir e exibir data de aniversário dos `customers` no formato `'YYYY/MM/DD'`.

> Dica: O exemplo acima mostra uma forma genérica de transformação de dados em diferentes formatos. Se você estiver trabalhando com valores de data, você pode usar o [DateValidator](tutorial-core-validators.md#date) e o [[yii\jui\DatePicker|DatePicker]], que é mais fácil e mais poderoso.


### Recuperando Dados em Arrays <span id="data-in-arrays"></span>

Embora a recuperação de dados através de objetos Active Record seja conveniente e flexível, pode não ser a melhor opção caso você tenha que retornar uma grande quantidade de dados devido ao grande consumo de memória. Neste caso, você pode recuperar usando arrays do PHP chamando [[yii\db\ActiveQuery::asArray()|asArray()]] antes de executar um método query:

```php
// retorna todos os `customers`
// cada `customer` retornado é associado a um array
$customers = Customer::find()
   ->asArray()
   ->all();
```

> Observação: Enquanto este método economiza memória e melhora o desempenho, ele é muito próximo a camada de abstração do DB e você vai perder a maioria dos recursos do Active Record. Uma distinção muito importante reside no tipo dos valores de coluna de dados. Quando você retorna dados em uma instância de Active Record, valores de colunas serão automaticamente convertidos de acordo com os tipos de coluna reais; de outra forma quando você retorna dados em arrays, valores de colunas serão strings (uma vez que são o resultado do PDO sem nenhum processamento), independentemente seus tipos de coluna reais.


### Recuperando Dados em Lote <span id="data-in-batches"></span>

No [Query Builder](db-query-builder.md), explicamos que você pode usar *batch query* para minimizar o uso de memória quando pesquisar uma grande quantidade de dados do banco de dados. Você pode utilizar a mesma técnica no Active Record. Por exemplo,

```php
// descarrega 10 `customers` a cada vez
foreach (Customer::find()->batch(10) as $customers) {
   // $customers é um array de 10 ou memos objetos Customer
}

// descarrega 10 `customers` por vez e faz a iteração deles um por um
foreach (Customer::find()->each(10) as $customer) {
   // $customer é um objeto Customer
}

// batch query com carga antecipada
foreach (Customer::find()->with('orders')->each() as $customer) {
   // $customer é um objeto Customer
}
```


## Salvando Dados <span id="inserting-updating-data"></span>

Usando Active Record, você pode facilmente salvar dados em um banco de dados realizando as seguintes etapas:

1. Preparar uma instância de Active Record
2. Atribuir novos valores aos atributos do Active Record
3. Chamar o método [[yii\db\ActiveRecord::save()]] para salvar os informações no banco de dados.

Por exemplo,

```php
// insere uma nova linha de dados
$customer = new Customer();
$customer->name = 'James';
$customer->email = 'james@example.com';
$customer->save();

// atualiza uma linha de dados existente
$customer = Customer::findOne(123);
$customer->email = 'james@newexample.com';
$customer->save();
```

O método [[yii\db\ActiveRecord::save()|save()]] pode tanto inserir ou atualizar dados, dependendo do estado da instância do Active Record. Se a instância tiver sido recém criada através do operador `new`, ao chamar [[yii\db\ActiveRecord::save()|save()]] será realizado a inserção de uma nova linha; se a instância for o resultado de um método da query, ao chamar [[yii\db\ActiveRecord::save()|save()]] será realizado a atualização dos dados associados a instância. 

Você pode diferenciar os dois estados de uma instância de Active Record verificando o valor da sua propriedade [[yii\db\ActiveRecord::isNewRecord|isNewRecord]]. Esta propriedade também é usada internamente pelo [[yii\db\ActiveRecord::save()|save()]] como mostrado abaixo:

```php
public function save($runValidation = true, $attributeNames = null)
{
   if ($this->getIsNewRecord()) {
       return $this->insert($runValidation, $attributeNames);
   } else {
       return $this->update($runValidation, $attributeNames) !== false;
   }
}
```

> Dica: Você pode chamar [[yii\db\ActiveRecord::insert()|insert()]] ou [[yii\db\ActiveRecord::update()|update()]] diretamente para inserir ou atualizar dados.
 

### Validação de Dados<span id="data-validation"></span>

Já que o [[yii\db\ActiveRecord]] estende de [[yii\base\Model]], ele compartilha os mesmos recursos de [validação de dados](input-validation.md). Você pode declarar regras de validação sobrescrevendo o método [[yii\db\ActiveRecord::rules()|rules()]] e realizar a validação de dados chamando o método [[yii\db\ActiveRecord::validate()|validate()]].

Quando você chama [[yii\db\ActiveRecord::save()|save()]], por padrão chamará [[yii\db\ActiveRecord::validate()|validate()]] automaticamente. somente quando a validação passa, os dados são de fato salvos; do contrário, simplesmente retorna falso, e você pode verificar a propriedade [[yii\db\ActiveRecord::errors|errors]] para recuperar a mensagem de erro de validação.  

> Dica: Se você tiver certeza que os seus dados não precisam de validação (ex., os dados tem uma origem confiável),  você pode chamar `save(false)` para pular a validação.


### Atribuição Maciça <span id="massive-assignment"></span>

Como um [models](structure-models.md) normal, instância de Active Record também oferece o [recurso de atribuição maciça](structure-models.md#massive-assignment). Usando este recurso, você pode atribuir valores para vários atributos de uma instância de Active Record em uma única declaração PHP, como mostrado a seguir. Lembre-se que somente [atributos de segurança](structure-models.md#safe-attributes) pode ser massivamente atribuídos.

```php
$values = [
   'name' => 'James',
   'email' => 'james@example.com',
];

$customer = new Customer();

$customer->attributes = $values;
$customer->save();
```


### Atualizando Contadores <span id="updating-counters"></span>

Isto é uma tarefa comum para incrementar ou decrementar uma coluna em uma tabela do banco de dados. Chamamos essas colunas como colunas de contador. Você pode usar [[yii\db\ActiveRecord::updateCounters()|updateCounters()]] para atualizar uma ou mais colunas de contadores. Por exemplo,

```php
$post = Post::findOne(100);

// UPDATE `post` SET `view_count` = `view_count` + 1 WHERE `id` = 100
$post->updateCounters(['view_count' => 1]);
```

> Observação: Se você usar [[yii\db\ActiveRecord::save()]] para atualizar uma coluna de contador, você pode acabar com um resultado impreciso, porque é provável que o mesmo contador esteja sendo salvo por várias solicitações que lêem e escrevem o mesmo valor do contador.


### Atributos Sujos <span id="dirty-attributes"></span>

Quando você chama [[yii\db\ActiveRecord::save()|save()]] para salvar uma instância de Active Record, somente *atributos sujos* serão salvos. Um atributo é considerado *sujo* se o seu valor foi modificado desde que foi carregado a partir de DB ou salvos em DB, mais recentemente. Note que a validação de dados será realizada, independentemente se a instância do Active Record tiver ou não atributos sujos.

O Active Record mantém automaticamente a lista de atributos sujas. Isto é feito mantendo uma versão antiga dos valores de atributos e comparando-as com as últimas informações. Você pode chamar [[yii\db\ActiveRecord::getDirtyAttributes()]] para pegar os atributos sujos correntes. Você também pode chamar [[yii\db\ActiveRecord::markAttributeDirty()]] para marcar explicitamente um atributo como sujo.

Se você estiver interessado nos valores de atributos antes da sua modificação mais recente, você pode chamar [[yii\db\ActiveRecord::getOldAttributes()|getOldAttributes()]] ou [[yii\db\ActiveRecord::getOldAttribute()|getOldAttribute()]].

> Observação: A comparação dos valores antigos e novos será feito usando o operador `===` portanto, um valor será considerado sujo mesmo se ele tiver o mesmo valor, mas um tipo diferente. Isto é comum quando o modelo recebe a entrada de dados do usuário a partir de um formulário HTML onde todos os valores são representados como string. Para garantir o tipo correto, por exemplo, valores inteiros você pode aplicar um [filtro de validação](input-validation.md#data-filtering): `['attributeName', 'filter', 'filter' => 'intval']`.


### Valores Padrões de Atributos <span id="default-attribute-values"></span>

Algumas de suas colunas de tabelas podem ter valores padrões definidos em um banco de dados. Algumas vezes, você pode querer preencher previamente o formulário Web para uma instância de Active Record com os seus valores padrões. Para evitar escrever os mesmos valores padrão novamente, você pode chamar [[yii\db\ActiveRecord::loadDefaultValues()|loadDefaultValues()]] para popular os valores padrões definidos pelo DB para os atributos correspondentes do Active Record:

```php
$customer = new Customer();
$customer->loadDefaultValues();
// $customer->xyz será atribuído o valor padrão declarado na definição da coluna "xyz"
```


### Atualizando Múltiplas Linhas <span id="updating-multiple-rows"></span>

Os métodos descritos acima fazem todo o trabalho em uma instância individual de Active Record, causando inserção ou atualização de linhas de tabela individuais. Para atualizar múltiplas linhas individualmente, você deve chamar o método estático [[yii\db\ActiveRecord::updateAll()|updateAll()]].

```php
// UPDATE `customer` SET `status` = 1 WHERE `email` LIKE `%@example.com%`
Customer::updateAll(['status' => Customer::STATUS_ACTIVE], ['like', 'email', '@example.com']);
```

Da mesma forma você pode chamar [[yii\db\ActiveRecord::updateAllCounters()|updateAllCounters()]] para atualizar colunas de contador de várias linhas ao mesmo tempo.

```php
// UPDATE `customer` SET `age` = `age` + 1
Customer::updateAllCounters(['age' => 1]);
```


## Deletando Dados <span id="deleting-data"></span>

Para deletar uma única linha de dados, primeiro recupere a instância de Active Record correspondente a linha e depois chame o método [[yii\db\ActiveRecord::delete()]].

```php
$customer = Customer::findOne(123);
$customer->delete();
```

Você pode chamar [[yii\db\ActiveRecord::deleteAll()]] para deletar múltiplas ou todas a linhas de dados. Por exemplo,

```php
Customer::deleteAll(['status' => Customer::STATUS_INACTIVE]);
```

> Observação: Tenha muito cuidado quando chamar [[yii\db\ActiveRecord::deleteAll()|deleteAll()]] porque pode apagar todos os dados de sua tabela se você cometer um erro na especificação da condição.


## Ciclo de Vida de um Active Record <span id="ar-life-cycles"></span>

É importante entender o ciclo de vida de um Active Record quando ele é usado para diferentes propósitos. Durante cada ciclo de vida, uma determinada sequência de métodos será invocada, e você pode substituir esses métodos para ter a chance de personalizar o ciclo de vida. Você também pode responder certos eventos disparados pelo Active Record durante o ciclo de vida para injetar o seu código personalizado. Estes eventos são especialmente úteis quando você está desenvolvendo [behaviors](concept-behaviors.md)que precisam personalizar o ciclo de vida do Active Record.

A seguir, vamos resumir diversos ciclos de vida de um Active Record e os métodos/eventos que fazem parte do ciclo de vida.


### Ciclo de Vida de uma Nova Instância <span id="new-instance-life-cycle"></span>

Quando se cria uma nova instância de Active Record através do operador `new`, acontece o seguinte ciclo de vida:

1. Construção da classe;
2. [[yii\db\ActiveRecord::init()|init()]]: dispara um evento [[yii\db\ActiveRecord::EVENT_INIT|EVENT_INIT]].


### Ciclo de Vida de uma Pesquisa de Dados <span id="querying-data-life-cycle"></span>

Quando se pesquisa dados através de um dos [métodos de consulta](#querying-data), cada novo Active Record populado sofrerá o seguinte ciclo de vida:

1. Contrução da classe.
2. [[yii\db\ActiveRecord::init()|init()]]: dispara um evento [[yii\db\ActiveRecord::EVENT_INIT|EVENT_INIT]].
3. [[yii\db\ActiveRecord::afterFind()|afterFind()]]: dispara um evento [[yii\db\ActiveRecord::EVENT_AFTER_FIND|EVENT_AFTER_FIND]].


### Ciclo de Vida da Persistência de Dados <span id="saving-data-life-cycle"></span>

Quando se chama [[yii\db\ActiveRecord::save()|save()]] para inserir ou atualizar uma instância de Active Record, acontece o seguinte ciclo de vida:

1. [[yii\db\ActiveRecord::beforeValidate()|beforeValidate()]]: dispara um evento [[yii\db\ActiveRecord::EVENT_BEFORE_VALIDATE|EVENT_BEFORE_VALIDATE]]. se o método retornar falso ou [[yii\base\ModelEvent::isValid]] for falso, o restante das etapas serão ignoradas.
2. Executa a validação de dados. Se a validação de dados falhar, Os passos após o passo 3 serão ignorados. 
3. [[yii\db\ActiveRecord::afterValidate()|afterValidate()]]: dispara um evento [[yii\db\ActiveRecord::EVENT_AFTER_VALIDATE|EVENT_AFTER_VALIDATE]].
4. [[yii\db\ActiveRecord::beforeSave()|beforeSave()]]: dispara um evento [[yii\db\ActiveRecord::EVENT_BEFORE_INSERT|EVENT_BEFORE_INSERT]] ou evento [[yii\db\ActiveRecord::EVENT_BEFORE_UPDATE|EVENT_BEFORE_UPDATE]]. Se o método retornar falso ou [[yii\base\ModelEvent::isValid]] for falso, o restante dos passos serão ignorados.
5. Realiza a atual inserção ou atualização de dados;
6. [[yii\db\ActiveRecord::afterSave()|afterSave()]]: dispara um evento [[yii\db\ActiveRecord::EVENT_AFTER_INSERT|EVENT_AFTER_INSERT]] ou evento [[yii\db\ActiveRecord::EVENT_AFTER_UPDATE|EVENT_AFTER_UPDATE]].
  

### Ciclo de Vida da Deleção de Dados <span id="deleting-data-life-cycle"></span>

Quando se chama [[yii\db\ActiveRecord::delete()|delete()]] para deletar uma instância de Active Record, acontece o seguinte ciclo de vida:

1. [[yii\db\ActiveRecord::beforeDelete()|beforeDelete()]]: dispara um evento [[yii\db\ActiveRecord::EVENT_BEFORE_DELETE|EVENT_BEFORE_DELETE]]. Se o método retornar falso ou [[yii\base\ModelEvent::isValid]] for falso, o restante dos passos serão ignorados.
2. Executa a atual deleção de dados.
3. [[yii\db\ActiveRecord::afterDelete()|afterDelete()]]: dispara um evento [[yii\db\ActiveRecord::EVENT_AFTER_DELETE|EVENT_AFTER_DELETE]].


> Observação: Chamar qualquer um dos seguintes métodos não iniciará qualquer um dos ciclos de vida listados acima:
>
> - [[yii\db\ActiveRecord::updateAll()]] 
> - [[yii\db\ActiveRecord::deleteAll()]]
> - [[yii\db\ActiveRecord::updateCounters()]] 
> - [[yii\db\ActiveRecord::updateAllCounters()]] 


## Trabalhando com Transações <span id="transactional-operations"></span>

Existem duas formas de usar [transações](db-dao.md#performing-transactions) quando se trabalha com Active Record. A primeira maneira é anexar explicitamente chamadas de método Active Record em um bloco transacional, como mostrado abaixo,

```php
$customer = Customer::findOne(123);

Customer::getDb()->transaction(function($db) use ($customer) {
   $customer->id = 200;
   $customer->save();
   // ...outras operações DB...
});

// ou como alternativa

$transaction = Customer::getDb()->beginTransaction();
try {
   $customer->id = 200;
   $customer->save();
   // ...outras operações DB...
   $transaction->commit();
} catch(\Exception $e) {
   $transaction->rollBack();
   throw $e;
}
```

A segunda maneira é listar as operações de banco de dados que exigem suporte transacional no método [[yii\db\ActiveRecord::transactions()]]. Por exemplo,

```php
class Customer extends ActiveRecord
{
   public function transactions()
   {
       return [
           'admin' => self::OP_INSERT,
           'api' => self::OP_INSERT | self::OP_UPDATE | self::OP_DELETE,
           // o código acima é equivalente ao código abaixo:
           // 'api' => self::OP_ALL,
       ];
   }
}
```

O método [[yii\db\ActiveRecord::transactions()]] deve retornar um array cujas chaves são [cenário](structure-models.md#scenarios) nomes e valores das operações correspondentes que devem ser embutidas dentro de transações. Você deve usar as seguintes constantes para fazer referência a diferentes operações de banco de dados:

* [[yii\db\ActiveRecord::OP_INSERT|OP_INSERT]]: operação de inserção realizada pelo [[yii\db\ActiveRecord::insert()|insert()]];
* [[yii\db\ActiveRecord::OP_UPDATE|OP_UPDATE]]: operação de atualização realizada pelo [[yii\db\ActiveRecord::update()|update()]];
* [[yii\db\ActiveRecord::OP_DELETE|OP_DELETE]]: operação de deleção realizada pelo [[yii\db\ActiveRecord::delete()|delete()]].

Utilize o operador `|` concatenar as constantes acima para indicar várias operações. Você também pode usar a constante de atalho [[yii\db\ActiveRecord::OP_ALL|OP_ALL]] para referenciar todas as três constantes acima.


## Bloqueios Otimistas <span id="optimistic-locks"></span>

O bloqueio otimista é uma forma de evitar conflitos que podem ocorrer quando uma única linha de dados está sendo atualizado por vários usuários. Por exemplo, se ambos os usuário A e B estiverem editando o mesmo artigo de Wiki ao mesmo tempo. Após o usuário A salvar suas alterações, o usuário B pressiona o botão  "Save" em uma tentativa de salvar suas edições também. Uma vez que o usuário B está trabalhando em uma versão desatualizada do artigo, seria desejável ter uma maneira de impedi-lo de salvar o artigo e mostrar-lhe alguma mensagem.

Bloqueio otimista resolve o problema acima usando uma coluna para gravar o número de versão de cada registro. Quando um registro está sendo salvo com um número de versão desatualizado, uma exceção [[yii\db\StaleObjectException]] será lançada, que impede que o registro seja salvo. Bloqueio otimista só é suportado quando você atualiza ou exclui um registro de dados existente usando [[yii\db\ActiveRecord::update()]] ou [[yii\db\ActiveRecord::delete()]] respectivamente.

Para usar bloqueio otimista:

1. Crie uma coluna na tabela do banco de dados associada com a classe Active Record para armazenar o número da versão de cada registro. A coluna deve ser do tipo  biginteger (no MySQL ela deve ser `BIGINT DEFAULT 0`).
2. Sobrescreva o método [[yii\db\ActiveRecord::optimisticLock()]] para retornar o nome desta coluna.
3. No formulário Web que recebe as entradas dos usuários, adicione um campo hidden para armazenar o número da versão corrente do registro que está sendo atualizado. Certifique-se que seu atributo versão possui regras de validação de entrada validadas com êxito.
4. Na ação do controller que atualiza o registro usando Active Record, mostre uma estrutura *try catch* da [[yii\db\StaleObjectException]] exceção. Implementar lógica de negócios necessária (ex. mesclar as mudanças, mostrar dados obsoletos) para resolver o conflito.
  
Por exemplo, digamos que a coluna de versão se chama `version`. Você pode implementar bloqueio otimista conforme o código abaixo.

```php
// ------ view code -------

use yii\helpers\Html;

// ...Outros campos de entrada de dados
echo Html::activeHiddenInput($model, 'version');


// ------ controller code -------

use yii\db\StaleObjectException;

public function actionUpdate($id)
{
   $model = $this->findModel($id);

   try {
       if ($model->load(Yii::$app->request->post()) && $model->save()) {
           return $this->redirect(['view', 'id' => $model->id]);
       } else {
           return $this->render('update', [
               'model' => $model,
           ]);
       }
   } catch (StaleObjectException $e) {
       // lógica para resolver o conflito
   }
}
```


## Trabalhando com Dados Relacionais <span id="relational-data"></span>

Além de trabalhar com tabelas individuais, o Active Record também é capaz de trazer dados de várias tabelas relacionadas, tornando-os prontamente acessíveis através dos dados primários. Por exemplo, a tabela de clientes está relacionada a tabela de pedidos porque um cliente pode ter múltiplos pedidos. Com uma declaração adequada desta relação, você pode ser capaz de acessar informações do pedido de um cliente utilizando a expressão `$customer->orders` que devolve informações sobre o pedido do cliente em um array de instâncias de Active Record de `Pedidos`.


### Declarando Relações <span id="declaring-relations"></span>

Para trabalhar com dados relacionais usando Active Record, você primeiro precisa declarar as relações nas classes Active Record. A tarefa é tão simples como declarar um *método de relação* para cada relação desejada. Segue exemplos:

```php
class Customer extends ActiveRecord
{
   public function getOrders()
   {
       return $this->hasMany(Order::class, ['customer_id' => 'id']);
   }
}

class Order extends ActiveRecord
{
   public function getCustomer()
   {
       return $this->hasOne(Customer::class, ['id' => 'customer_id']);
   }
}
```

No código acima, nós declaramos uma relação `orders` para a classe `Customer`, e uma relação `customer` para a classe `Order`. 

Cada método de relação deve ser nomeado como `getXyz`. Nós chamamos de `xyz` (a primeira letra é em letras minúsculas) o *nome da relação*. Note que os nomes de relações são *case sensitive*.

Ao declarar uma relação, você deve especificar as seguintes informações:

- A multiplicidade da relação: especificada chamando tanto o método [[yii\db\ActiveRecord::hasMany()|hasMany()]] quanto o método [[yii\db\ActiveRecord::hasOne()|hasOne()]]. No exemplo acima você pode facilmente ler nas declarações de relação que um `customer` tem vários `orders` enquanto uma `order` só tem um `customer`.
- O nome da classe Active Record relacionada: especificada no primeiro parâmetro dos métodos [[yii\db\ActiveRecord::hasMany()|hasMany()]] e [[yii\db\ActiveRecord::hasOne()|hasOne()]]. Uma prática recomendada é chamar `Xyz::class` para obter o nome da classe para que você possa receber suporte do preenchimento automático de IDEs bem como detecção de erros. 
- A ligação entre os dois tipos de dados: especifica a(s) coluna(s) por meio do qual os dois tipos de dados se relacionam. Os valores do array são as colunas da tabela primária (representada pela classe Active Record que você declarou as relações), enquanto as chaves do array são as colunas da tabela relacionada.

Uma regra fácil de lembrar é, como você pode ver no exemplo acima, você escreve a coluna que pertence ao Active Record relacionado diretamente ao lado dele. Você pode ver que `customer_id` é uma propriedade de `Order` e `id` é uma propriedade de  `Customer`.
 

### Acessando Dados Relacionais <span id="accessing-relational-data"></span>

Após declarar as relações, você pode acessar os dados relacionados através dos nomes das relações. Isto é como acessar uma [propriedade](concept-properties.md) de um objeto definida por um método de relação. Por esta razão, nós podemos chamá-la de *propriedade de relação*. Por exemplo,

```php
// SELECT * FROM `customer` WHERE `id` = 123
$customer = Customer::findOne(123);

// SELECT * FROM `order` WHERE `customer_id` = 123
// $orders is an array of Order objects
$orders = $customer->orders;
```

> Observação: quando você declara uma relação chamada `xyz` através de um método getter `getXyz()`, você terá acesso a `xyz` como uma [propriedade de objeto](concept-properties.md). Note que o nome é case-sensitive.
 
Se a relação for declarada com [[yii\db\ActiveRecord::hasMany()|hasMany()]], acessar esta propriedade irá retornar um array de instâncias de Active Record relacionais; Se a relação for declarada com [[yii\db\ActiveRecord::hasOne()|hasOne()]], acessar esta propriedade irá retornar a instância de Active Record relacional ou `null` se não encontrar dados relacionais.

Quando você acessa uma propriedade de relação pela primeira vez, uma instrução SQL será executada, como mostrado no exemplo acima. Se a mesma propriedade for acessada novamente, o resultado anterior será devolvido sem executar novamente a instrução SQL. Para forçar a execução da instrução SQL, você deve primeiramente remover a configuração da propriedade de relação: `unset($customer->orders)`.

> Observação: Embora este conceito é semelhante ao recurso  de [propriedade de objeto](concept-properties.md), existe uma diferença importante. Em uma propriedade de objeto normal o valor da propriedade é do mesmo tipo que o método getter definindo. Já o método de relação retorna uma instância de  [[yii\db\ActiveQuery]], embora o acesso a uma propriedade de relação retorne uma instância de [[yii\db\ActiveRecord]] ou um array deste tipo.
>
> ```php
> $customer->orders; // é um array de objetos `Order`
> $customer->getOrders(); // returna uma instância de ActiveQuery
> ```
> 
> Isso é útil para a criação de consultas personalizadas, que está descrito na próxima seção.


### Consulta Relacional Dinâmica <span id="dynamic-relational-query"></span>

Uma vez que um método de relação retorna uma instância de [[yii\db\ActiveQuery]], você pode construir uma query usando os métodos de `query building` antes de executá-la. Por exemplo,

```php
$customer = Customer::findOne(123);

// SELECT * FROM `order` WHERE `subtotal` > 200 ORDER BY `id`
$orders = $customer->getOrders()
   ->where(['>', 'subtotal', 200])
   ->orderBy('id')
   ->all();
```

Diferente de acessar uma propriedade de relação, cada vez que você executar uma consulta relacional dinâmica através de um método de relação, uma instrução SQL será executada, mesmo que a mesma consulta relacional dinâmica tenha sido executada anteriormente.

Algumas vezes você pode querer parametrizar uma relação para que possa executar mais facilmente uma consulta relacional dinâmica. Por exemplo, você pode declarar uma relação  `bigOrders` da seguinte forma, 

```php
class Customer extends ActiveRecord
{
   public function getBigOrders($threshold = 100)
   {
       return $this->hasMany(Order::class, ['customer_id' => 'id'])
           ->where('subtotal > :threshold', [':threshold' => $threshold])
           ->orderBy('id');
   }
}
```

Em seguida, você será capaz de executar as seguintes consultas relacionais:

```php
// SELECT * FROM `order` WHERE `subtotal` > 200 ORDER BY `id`
$orders = $customer->getBigOrders(200)->all();

// SELECT * FROM `order` WHERE `subtotal` > 100 ORDER BY `id`
$orders = $customer->bigOrders;
```


### Relações Através de Tabela de Junção <span id="junction-table"></span>

Em uma modelagem de banco de dados, quando a multiplicidade entre duas tabelas relacionadas é `many-to-many`, geralmente é criada uma [tabela de junção](https://en.wikipedia.org/wiki/Junction_table). Por exemplo, a tabela `order` e a tabela `item` podem se relacionar através da tabela de junção chamada `order_item`. Um `order`, então, corresponderá a múltiplos `order items`, enquanto um `product item` também corresponderá a múltiplos `order items`.

Ao declarar tais relações, você chamaria [[yii\db\ActiveQuery::via()|via()]] ou [[yii\db\ActiveQuery::viaTable()|viaTable()]] para especificar a tabela de junção. A diferença entre [[yii\db\ActiveQuery::via()|via()]] e [[yii\db\ActiveQuery::viaTable()|viaTable()]] é que o primeiro especifica a tabela de junção em função a uma relação existente enquanto o último faz referência diretamente a tabela de junção. Por exemplo,

```php
class Order extends ActiveRecord
{
   public function getItems()
   {
       return $this->hasMany(Item::class, ['id' => 'item_id'])
           ->viaTable('order_item', ['order_id' => 'id']);
   }
}
```

ou alternativamente,

```php
class Order extends ActiveRecord
{
   public function getOrderItems()
   {
       return $this->hasMany(OrderItem::class, ['order_id' => 'id']);
   }

   public function getItems()
   {
       return $this->hasMany(Item::class, ['id' => 'item_id'])
           ->via('orderItems');
   }
}
```

A utilização das relações declaradas com uma tabela de junção é a mesma que a das relações normais. Por exemplo,

```php
// SELECT * FROM `order` WHERE `id` = 100
$order = Order::findOne(100);

// SELECT * FROM `order_item` WHERE `order_id` = 100
// SELECT * FROM `item` WHERE `item_id` IN (...)
// returns an array of Item objects
$items = $order->items;
```


### Lazy e Eager Loading <span id="lazy-eager-loading"></span>

Na seção [Acessando Dados Relacionais](#accessing-relational-data), nós explicamos que você pode acessar uma propriedade de relação de uma instância de Active Record como se acessa uma propriedade de objeto normal. Uma instrução SQL será executada somente quando você acessar a propriedade de relação pela primeira vez. Chamamos esta forma de acessar estes dados de *lazy loading*. Por exemplo,

```php
// SELECT * FROM `customer` WHERE `id` = 123
$customer = Customer::findOne(123);

// SELECT * FROM `order` WHERE `customer_id` = 123
$orders = $customer->orders;

// nenhum SQL é executado
$orders2 = $customer->orders;
```

O uso de lazy loading é muito conveniente. Entretanto, pode haver um problema de desempenho caso você precise acessar a mesma propriedade de relação a partir de múltiplas instâncias de Active Record. Considere o exemplo a seguir. Quantas instruções SQL serão executadas?

```php
// SELECT * FROM `customer` LIMIT 100
$customers = Customer::find()->limit(100)->all();

foreach ($customers as $customer) {
   // SELECT * FROM `order` WHERE `customer_id` = ...
   $orders = $customer->orders;
}
```

Como você pode ver no comentário do código acima, há 101 instruções SQL sendo executadas! Isto porque cada vez que você acessar a propriedade de relação  `orders` com um objeto `Customer` diferente no bloco `foreach`, uma instrução SQL será executada.

Para resolver este problema de performance, você pode usar o *eager loading*, como mostrado abaixo,

```php
// SELECT * FROM `customer` LIMIT 100;
// SELECT * FROM `orders` WHERE `customer_id` IN (...)
$customers = Customer::find()
   ->with('orders')
   ->limit(100)
   ->all();

foreach ($customers as $customer) {
   // nenhum SQL é executado
   $orders = $customer->orders;
}
```

Ao chamar [[yii\db\ActiveQuery::with()]], você instrui ao Active Record trazer os `orders` dos primeiros 100 `customers` em uma única instrução SQL. Como resultado, você reduz o número de instruções SQL executadas de 101 para 2.

Você pode utilizar esta abordagem com uma ou várias relações. Você pode até mesmo utilizar *eager loading* com *relações aninhadas*. Um relação aninhada é uma relação que é declarada dentro de uma classe Active Record de relação. Por exemplo, `Customer` se relaciona com `Order` através da relação `orders`, e `Order` está relacionado com `Item` através da relação `items`. Quando pesquisar por `Customer`, você pode fazer o *eager loading* de `items` usando a notação de relação aninhada `orders.items`. 

O código a seguir mostra diferentes formas de utilizar [[yii\db\ActiveQuery::with()|with()]]. Assumimos que a classe `Customer` tem duas relações `orders` e `country`, enquanto a classe `Order` tem uma relação `items`.

```php
// carga antecipada de "orders" e "country"
$customers = Customer::find()->with('orders', 'country')->all();
// equivalente à sintaxe de array abaixo
$customers = Customer::find()->with(['orders', 'country'])->all();
// nenhum SQL executado
$orders= $customers[0]->orders;
// nenhum SQL executado
$country = $customers[0]->country;

// carga antecipada de "orders" e a relação aninhada "orders.items"
$customers = Customer::find()->with('orders.items')->all();
// acessa os "items" do primeiro "order" e do primeiro "customer"
// nenhum SQL executado
$items = $customers[0]->orders[0]->items;
```

Você pode carregar antecipadamente relações profundamente aninhadas, tais como `a.b.c.d`. Todas as relações serão carregadas antecipadamente. Isto é, quando você chamar [[yii\db\ActiveQuery::with()|with()]] usando `a.b.c.d`, você fará uma carga antecipada de `a`, `a.b`, `a.b.c` e `a.b.c.d`.  

> Observação: Em geral, ao fazer uma carga antecipada de `N` relações em que `M` relações são definidas com uma [tabela de junção](#junction-table), um total de instruções SQL `N+M+1` serão executadas. Note que uma relação aninhada `a.b.c.d` conta como 4 relações.

Ao carregar antecipadamente uma relação, você pode personalizar a consulta relacional correspondente utilizando uma função anônima. Por exemplo,

```php
// procura “customers” trazendo junto “country” e “orders” ativas
// SELECT * FROM `customer`
// SELECT * FROM `country` WHERE `id` IN (...)
// SELECT * FROM `order` WHERE `customer_id` IN (...) AND `status` = 1
$customers = Customer::find()->with([
   'country',
   'orders' => function ($query) {
       $query->andWhere(['status' => Order::STATUS_ACTIVE]);
   },
])->all();
```

Ao personalizar a consulta relacional para uma relação, você deve especificar o nome da relação como uma chave de array e usar uma função anônima como o valor de array correspondente. A função anônima receberá um parâmetro `$query` que representa o objeto [[yii\db\ActiveQuery]] utilizado para realizar a consulta relacional para a relação. No exemplo acima, modificamos a consulta relacional, acrescentando uma condição adicional sobre o status de 'orders'.

> Observação: Se você chamar [[yii\db\Query::select()|select()]] ao carregar antecipadamente as relações, você tem que certificar-se de que as colunas referenciadas na relação estão sendo selecionadas. Caso contrário, os modelos relacionados podem não ser carregados corretamente. Por exemplo,
>
> ```php
> $orders = Order::find()->select(['id', 'amount'])->with('customer')->all();
> // $orders[0]->customer é sempre nulo. Para corrigir o problema, você deve fazer o seguinte:
> $orders = Order::find()->select(['id', 'amount', 'customer_id'])->with('customer')->all();
> ```


### Relações utilizando JOIN <span id="joining-with-relations"></span>

> Observação: O conteúdo descrito nesta subsecção é aplicável apenas aos bancos de dados relacionais, tais como MySQL, PostgreSQL, etc.

As consultas relacionais que temos descrito até agora só fizeram referência as chave primária ao consultar os dados. Na realidade, muitas vezes precisamos referenciar colunas nas tabelas relacionadas. Por exemplo, podemos querer trazer os 'customers' que tenham no mínimo um 'order' ativo. Para solucionar este problema, podemos fazer uma query com join como a seguir:

```php
// SELECT `customer`.* FROM `customer`
// LEFT JOIN `order` ON `order`.`customer_id` = `customer`.`id`
// WHERE `order`.`status` = 1
// 
// SELECT * FROM `order` WHERE `customer_id` IN (...)
$customers = Customer::find()
   ->select('customer.*')
   ->leftJoin('order', '`order`.`customer_id` = `customer`.`id`')
   ->where(['order.status' => Order::STATUS_ACTIVE])
   ->with('orders')
   ->all();
```

> Observação: É importante evitar ambiguidade de nomes de colunas ao criar queries com JOIN. Uma prática comum é prefixar os nomes das colunas com os nomes de tabela correspondente. Entretanto, uma melhor abordagem é a de explorar as declarações de relação existente chamando [[yii\db\ActiveQuery::joinWith()]]:

```php
$customers = Customer::find()
   ->joinWith('orders')
   ->where(['order.status' => Order::STATUS_ACTIVE])
   ->all();
```

Ambas as formas executam o mesmo conjunto de instruções SQL. Embora a última abordagem seja mais elegante. 

Por padrão , [[yii\db\ActiveQuery::joinWith()|joinWith()]] usará `LEFT JOIN` para juntar a tabela primária com a tabela relacionada. Você pode especificar um tipo de join diferente (ex. `RIGHT JOIN`) através do seu terceiro parâmetro `$joinType`. Se o tipo de join que você quer for `INNER JOIN`, você pode apenas chamar [[yii\db\ActiveQuery::innerJoinWith()|innerJoinWith()]].

Chamando [[yii\db\ActiveQuery::joinWith()|joinWith()]] os dados relacionais serão  [carregados antecipadamente](#lazy-eager-loading) por padrão. Se você não quiser trazer o dados relacionados, você pode especificar o segundo parâmetro `$eagerLoading` como falso. 

Assim como [[yii\db\ActiveQuery::with()|with()]], você pode juntar uma ou várias relações; você pode personalizar as queries relacionais dinamicamente; você pode utilizar join com relações aninhadas; e pode misturar o uso de [[yii\db\ActiveQuery::with()|with()]] e [[yii\db\ActiveQuery::joinWith()|joinWith()]]. Por exemplo,

```php
$customers = Customer::find()->joinWith([
   'orders' => function ($query) {
       $query->andWhere(['>', 'subtotal', 100]);
   },
])->with('country')
   ->all();
```

Algumas vezes ao juntar duas tabelas, você pode precisar especificar alguma condição extra na estrutura SQL `ON` do JOIN. Isto pode ser feito chamando o método [[yii\db\ActiveQuery::onCondition()]] como a seguir:

```php
// SELECT `customer`.* FROM `customer`
// LEFT JOIN `order` ON `order`.`customer_id` = `customer`.`id` AND `order`.`status` = 1 
// 
// SELECT * FROM `order` WHERE `customer_id` IN (...)
$customers = Customer::find()->joinWith([
   'orders' => function ($query) {
       $query->onCondition(['order.status' => Order::STATUS_ACTIVE]);
   },
])->all();
```

A query acima retorna *todos* `customers`, e para cada `customer` retorna todos `orders` ativos. Note que isto difere do nosso primeiro exemplo onde retornávamos apenas `customers` que tinham no mínimo um `order` ativo.

> Observação: Quando [[yii\db\ActiveQuery]] é especificada com uma condição usando [[yii\db\ActiveQuery::onCondition()|onCondition()]], a condição será colocada no `ON` se a query tiver um JOIN. Se a query não utilizar JOIN, a condição será colocada no `WHERE` da query.


### Relações Inversas <span id="inverse-relations"></span>

Declarações de relação são geralmente recíprocas entre duas classes de Active Record. Por exemplo, `Customer` se relaciona com `Order` através da relação `orders`, e `Order` se relaciona com `Customer` através da relação `customer`.

```php
class Customer extends ActiveRecord
{
   public function getOrders()
   {
       return $this->hasMany(Order::class, ['customer_id' => 'id']);
   }
}

class Order extends ActiveRecord
{
   public function getCustomer()
   {
       return $this->hasOne(Customer::class, ['id' => 'customer_id']);
   }
}
```

Agora considere a seguinte parte do código:

```php
// SELECT * FROM `customer` WHERE `id` = 123
$customer = Customer::findOne(123);

// SELECT * FROM `order` WHERE `customer_id` = 123
$order = $customer->orders[0];

// SELECT * FROM `customer` WHERE `id` = 123
$customer2 = $order->customer;

// displays "not the same"
echo $customer2 === $customer ? 'same' : 'not the same';
```

Podemos pensar que `$customer` e `$customer2` são a mesma coisa, mas não são! Na verdade eles contêm a mesma informação de `customer`, mas são objetos diferentes. Ao acessar `$order->customer`, uma instrução SQL extra é executada para popular um novo objeto `$customer2`.

Para evitar esta redundância de execução de SQL no exemplo acima, devemos dizer para o Yii que `customer` é uma *relação inversa* de `orders` chamando o método [[yii\db\ActiveQuery::inverseOf()|inverseOf()]] como mostrado abaixo:

```php
class Customer extends ActiveRecord
{
   public function getOrders()
   {
       return $this->hasMany(Order::class, ['customer_id' => 'id'])->inverseOf('customer');
   }
}
```

Com esta modificação na declaração de relação, podemos ter:

```php
// SELECT * FROM `customer` WHERE `id` = 123
$customer = Customer::findOne(123);

// SELECT * FROM `order` WHERE `customer_id` = 123
$order = $customer->orders[0];

// Nenhum SQL será executado
$customer2 = $order->customer;

// exibe "same"
echo $customer2 === $customer ? 'same' : 'not the same';
```

> Observação: relações inversas não podem ser definidas por relações que envolvam uma  [tabela de junção](#junction-table). Isto é, se uma relação for definida com [[yii\db\ActiveQuery::via()|via()]] ou [[yii\db\ActiveQuery::viaTable()|viaTable()]], você não deve chamar [[yii\db\ActiveQuery::inverseOf()|inverseOf()]].


## Salvando Relações <span id="saving-relations"></span>

Ao trabalhar com dados relacionais, muitas vezes você precisa estabelecer relações entre dados diferentes ou destruir relações existentes. Isso requer a definição de valores apropriados para as colunas que definem as relações. Usando Active Record, você pode acabar escrevendo o seguinte código:

```php
$customer = Customer::findOne(123);
$order = new Order();
$order->subtotal = 100;
// ...

// definindo o atributo que define a relação "customer" em "Order"
$order->customer_id = $customer->id;
$order->save();
```

Active Record fornece o método [[yii\db\ActiveRecord::link()|link()]] que lhe permite realizar essa tarefa de uma forma mais elegante:

```php
$customer = Customer::findOne(123);
$order = new Order();
$order->subtotal = 100;
// ...

$order->link('customer', $customer);
```

O método [[yii\db\ActiveRecord::link()|link()]] requer que você especifique o nome da relação e a instância de Active Record alvo cuja relação deve ser estabelecida. O método irá modificar os valores dos atributos que apontam para duas instâncias de Active Record e salvá-los no banco de dados. No exemplo acima, o atributo `customer_id` da instância `Order` será definido para ser o valor do atributo `id` da instância `Customer` e depois salvá-lo no banco de dados.

> Observação: Você não pode conectar duas instâncias de Active Record recém-criadas.

Os benefícios de usar [[yii\db\ActiveRecord::link()|link()]] é ainda mais óbvio quando uma relação é definida por meio de uma [tabela de junção](#junction-table). Por exemplo, você pode usar o código a seguir para ligar uma instância de `Order` com uma instância de `Item`:

```php
$order->link('items', $item);
```

O código acima irá inserir automaticamente uma linha na tabela de junção `order_item` para relacionar a `order` com o `item`.

> Observação: O método [[yii\db\ActiveRecord::link()|link()]] não realizará nenhuma validação de dados ao salvar a instância de Active Record afetada. É de sua responsabilidade validar todos os dados de entrada antes de chamar esse método.

A operação inversa para [[yii\db\ActiveRecord::link()|link()]] é [[yii\db\ActiveRecord::unlink()|unlink()]] que quebra uma relação existente entre duas instâncias de Active Record. Por exemplo,

```php
$customer = Customer::find()->with('orders')->where(['id' => 123])->one();
$customer->unlink('orders', $customer->orders[0]);
```


Por padrão, o método [[yii\db\ActiveRecord::unlink()|unlink()]]  irá definir o(s) valor(es) da(s) chave(s) estrangeira(s)  que especificam a relação existente para `null`. Você pode, entretanto, optar por excluir a linha da tabela que contém a chave estrangeira passando o parâmetro `$delete` como `true` para o método.

Quando uma tabela de junção está envolvida numa relação, chamar o método [[yii\db\ActiveRecord::unlink()|unlink()]] fará com que as chaves estrangeiras na tabela de junção sejam apagadas, ou a deleção da linha correspondente na tabela de junção se `$delete` for `true`.


## Relações Entre Banco de Dados Diferentes <span id="cross-database-relations"></span> 

O Active Record lhe permite declarar relações entre classes Active Record que são alimentados por diferentes bancos de dados. As bases de dados podem ser de diferentes tipos (ex. MySQL e PostgreSQL, ou MS SQL e MongoDB) e podem rodar em diferentes servidores. Você pode usar a mesma sintaxe para executar consultas relacionais. Por exemplo,

```php
// Customer está associado a tabela "customer" em um banco de dados relacional (ex. MySQL)
class Customer extends \yii\db\ActiveRecord
{
   public static function tableName()
   {
       return 'customer';
   }

   public function getComments()
   {
       // a customer tem muitos comments
       return $this->hasMany(Comment::class, ['customer_id' => 'id']);
   }
}

// Comment está associado com a coleção "comment" em um banco de dados MongoDB
class Comment extends \yii\mongodb\ActiveRecord
{
   public static function collectionName()
   {
       return 'comment';
   }

   public function getCustomer()
   {
       // um comment tem um customer
       return $this->hasOne(Customer::class, ['id' => 'customer_id']);
   }
}

$customers = Customer::find()->with('comments')->all();
```

Você pode usar a maioria dos recursos de consulta relacional que foram descritos nesta seção. 

> Observação: O uso de [[yii\db\ActiveQuery::joinWith()|joinWith()]] está limitado às bases de dados que permitem JOIN entre diferentes bancos de dados (cross-database). Por esta razão, você não pode usar este método no exemplo acima porque MongoDB não suporta JOIN.


## Personalizando Classes de Consulta <span id="customizing-query-classes"></span>

Por padrão, todas as consultas do Active Record são suportadas por [[yii\db\ActiveQuery]]. Para usar uma classe de consulta customizada em uma classe Active Record, você deve sobrescrever o método [[yii\db\ActiveRecord::find()]] e retornar uma instância da sua classe de consulta customizada. Por exemplo,

```php
namespace app\models;

use yii\db\ActiveRecord;
use yii\db\ActiveQuery;

class Comment extends ActiveRecord
{
   public static function find()
   {
       return new CommentQuery(get_called_class());
   }
}

class CommentQuery extends ActiveQuery
{
   // ...
}
```

Agora sempre que você realizar uma consulta (ex. `find()`, `findOne()`) ou definir uma relação (ex. `hasOne()`) com `Comment`, você estará trabalhando com a instância de `CommentQuery` em vez de `ActiveQuery`.

> Dica: Em grandes projetos, é recomendável que você use classes de consulta personalizadas para manter a maioria dos códigos de consultas relacionadas de modo que a classe Active Record possa se manter limpa.

Você pode personalizar uma classe de consulta  de várias formas criativas afim de melhorar a sua experiência na construção de consultas. Por exemplo, você pode definir um novo método query building em uma classe de consulta customizada: 

```php
class CommentQuery extends ActiveQuery
{
   public function active($state = true)
   {
       return $this->andWhere(['active' => $state]);
   }
}
```

> Observação: Em vez de chamar [[yii\db\ActiveQuery::where()|where()]], você geralmente deve chamar [[yii\db\ActiveQuery::andWhere()|andWhere()]] ou [[yii\db\ActiveQuery::orWhere()|orWhere()]] para anexar condições adicionais ao definir um novo método de query building de modo que as condições existentes não serão sobrescritas.

Isto permite que você escreva códigos de query building conforme o exemplo abaixo:

```php
$comments = Comment::find()->active()->all();
$inactiveComments = Comment::find()->active(false)->all();
```

Você também pode usar um novo método de query building  ao definir relações sobre `Comment` ou executar uma consulta relacional:

```php
class Customer extends \yii\db\ActiveRecord
{
   public function getActiveComments()
   {
       return $this->hasMany(Comment::class, ['customer_id' => 'id'])->active();
   }
}

$customers = Customer::find()->with('activeComments')->all();

// ou alternativamente

$customers = Customer::find()->with([
   'comments' => function($q) {
       $q->active();
   }
])->all();
```

> Observação: No Yii 1.1, existe um conceito chamado *scope*. Scope já não é suportado no Yii 2.0, e você deve usar classes de consulta personalizadas e métodos de consulta para atingir o mesmo objetivo.


## Selecionando Campos Extras

Quando uma instância de Active Record é populada através do resultado de uma consulta, seus atributos são preenchidos pelos valores correspondentes das colunas do conjunto de dados recebidos.

Você é capaz de buscar colunas ou valores adicionais da consulta e armazená-lo dentro do Active Record. Por exemplo, suponha que tenhamos uma tabela chamada 'room', que contém informações sobre quartos disponíveis em um hotel. Cada 'room' grava informações sobre seu tamanho geométrico usando os campos 'length', 'width', 'height'. Imagine que precisemos de uma lista de todos os quartos disponíveis com o seu volume em ordem decrescente. Então você não pode calcular o volume usando PHP, porque precisamos ordenar os registros pelo seu valor, mas você também quer que o 'volume' seja mostrado na lista. Para alcançar este objetivo, você precisa declarar um campo extra na sua classe Active Record 'Room', que vai armazenar o valor do campo  'volume':

```php
class Room extends \yii\db\ActiveRecord
{
   public $volume;

   // ...
}
```

Então você precisa criar uma consulta, que calcule o volume da sala e realize a ordenação:

```php
$rooms = Room::find()
   ->select([
       '{{room}}.*', // select all columns
       '([[length]] * [[width]].* [[height]]) AS volume', // calculate a volume
   ])
   ->orderBy('volume DESC') // apply sort
   ->all();

foreach ($rooms as $room) {
   echo $room->volume; // contains value calculated by SQL
}
```

Capacidade de selecionar campos extras pode ser extremamente útil para consultas de agregação. Suponha que você precise exibir uma lista de clientes com a contagem de seus pedidos. Em primeiro lugar, você precisa declarar uma classe `Customer` com uma relação com 'orders' e um campo extra para armazenamento da contagem:

```php
class Customer extends \yii\db\ActiveRecord
{
   public $ordersCount;

   // ...

   public function getOrders()
   {
       return $this->hasMany(Order::class, ['customer_id' => 'id']);
   }
}
```

Então você pode criar uma consulta que faça um JOIN com 'orders' e calcule a quantidade:

```php
$customers = Customer::find()
   ->select([
       '{{customer}}.*', // select all customer fields
       'COUNT({{order}}.id) AS ordersCount' // calculate orders count
   ])
   ->joinWith('orders') // ensure table junction
   ->groupBy('{{customer}}.id') // group the result to ensure aggregation function works
   ->all();
```
