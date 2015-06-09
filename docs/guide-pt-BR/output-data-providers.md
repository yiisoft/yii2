Data Providers (Provedores de Dados)
==============
 
Nas seções [Paginação](output-pagination.md) e [ordenação](output-sorting.md), descrevemos como os usuários finais podem escolher uma determinada página de dados para exibir e ordená-los por algumas colunas. Uma vez que esta tarefa de paginação e ordenação de dados é muito comum, Yii fornece um conjunto de classes *data provider* para encapsular estes recursos.
 
Um data provider é uma classe que implementa
[[yii\data\DataProviderInterface]]. Ele suporta principalmente a recuperação de dados paginados e ordenados. Geralmente é usado para trabalhar com [widgets de dados](output-data-widgets.md) de modo que os usuários finais possam interativamente paginar e ordenar dados.
 
O Yii fornece as seguintes classes de data provider:
 
* [[yii\data\ActiveDataProvider]]: Utilize [[yii\db\Query]] ou [[yii\db\ActiveQuery]] para consultar dados de um database e retorná-los na forma de array ou uma instância de [Active Record](db-active-record.md).
* [[yii\data\SqlDataProvider]]: executa uma instrução SQL e retorna os dados do banco de dados como array.
* [[yii\data\ArrayDataProvider]]: é preciso um grande array e retorna uma parte deste baseado na paginação e ordenação especificada.
 
O uso de todos estes data providers compartilham o seguinte padrão comum:
 
```php
// cria o data provider configurando suas propriedades de paginação e ordenação
$provider = new XyzDataProvider([
   'pagination' => [...],
   'sort' => [...],
]);
 
// recupera dados paginados e ordenados
$models = $provider->getModels();
 
// obtem o número de itens de dados na página atual
$count = $provider->getCount();
 
// obtem o número total de itens de dados de todas as páginas
$totalCount = $provider->getTotalCount();
```
 
Você define o comportamento da paginação e ordenação do data provider configurando suas propriedades [[yii\data\BaseDataProvider::pagination|pagination]] e [[yii\data\BaseDataProvider::sort|sort]] que correspondem às configurações [[yii\data\Pagination]] and [[yii\data\Sort]]  respectivamente. Você também pode configurá-los como false para desativar os recursos de paginação e/ou ordenação.
 
[widgets de dados](output-data-widgets.md), assim como [[yii\grid\GridView]], tem uma propriedade chamada `dataProvider` que pode receber uma instância de data provider e exibir os dados que ele fornece. Por exemplo,
 
```php
echo yii\grid\GridView::widget([
   'dataProvider' => $dataProvider,
]);
```
 
Estes data providers variam principalmente conforme a fonte de dados é  especificada. Nas subseções seguintes, vamos explicar o uso detalhado de cada um dos data providers.
 
## Active Data Provider (Provedor de Dados) <span id="active-data-provider"></span>
 
Para usar [[yii\data\ActiveDataProvider]], você deve configurar sua propriedade [[yii\data\ActiveDataProvider::query|query]].
Ele pode receber qualquer um dos objetos [[yii\db\Query]] ou [[yii\db\ActiveQuery]]. Se for o primeiro, os dados serão retornados em array; se for o último, os dados podem ser retornados em array ou uma instância de [Active Record](db-active-record.md).
Por Exemplo,
 
```php
use yii\data\ActiveDataProvider;
 
$query = Post::find()->where(['status' => 1]);
 
$provider = new ActiveDataProvider([
   'query' => $query,
   'pagination' => [
       'pageSize' => 10,
   ],
   'sort' => [
       'defaultOrder' => [
           'created_at' => SORT_DESC,
           'title' => SORT_ASC,
       ]
   ],
]);
 
// retorna um array de objetos Post
$posts = $provider->getModels();
```
 
Se `$query` no exemplo acima fosse criada usando o código a seguir, então o data provider retornaria um array.
 
```php
use yii\db\Query;
 
$query = (new Query())->from('post')->where(['status' => 1]);
```
 
> Observação: Se uma query já especificou a cláusula `orderBy, as novas instruções de ordenação dadas por usuários finais
 (através da configuração `sort`) será acrescentada a cláusula `orderBy` existente. Existindo qualquer uma das cláusulas `limit` e `offset` será substituído pelo request de paginação dos usuários finais (através da configuração `pagination`).
 
Por padrão, [[yii\data\ActiveDataProvider]] utiliza o componente `db` da aplicação como a conexão de banco de dados. Você pode usar uma conexão de banco de dados diferente, configurando a propriedade [[yii\data\ActiveDataProvider::db]].
 
## SQL Data Provider <span id="sql-data-provider"></span>
 
[[yii\data\SqlDataProvider]] trabalha com uma instrução SQL, que é usado para obter os dados necessários. Com base nas especificações de [[yii\data\SqlDataProvider::sort|sort]] e
[[yii\data\SqlDataProvider::pagination|pagination]], o provider ajustará as cláusulas `ORDER BY` e `LIMIT` da instrução SQL em conformidade para buscar somente a página de dados solicitada na ordem desejada.
 
Para usar [[yii\data\SqlDataProvider]], você deve especificar a propriedade [[yii\data\SqlDataProvider::sql|sql]] bem como a propriedade [[yii\data\SqlDataProvider::totalCount|totalCount]. Por exemplo,
 
```php
use yii\data\SqlDataProvider;
 
$count = Yii::$app->db->createCommand('
   SELECT COUNT(*) FROM post WHERE status=:status
', [':status' => 1])->queryScalar();
 
$provider = new SqlDataProvider([
   'sql' => 'SELECT * FROM post WHERE status=:status',
   'params' => [':status' => 1],
   'totalCount' => $count,
   'pagination' => [
       'pageSize' => 10,
   ],
   'sort' => [
       'attributes' => [
           'title',
           'view_count',
           'created_at',
       ],
   ],
]);
 
// retorna um array de linha de dados
$models = $provider->getModels();
```
 
> Observação: A propriedade [[yii\data\SqlDataProvider::totalCount|totalCount]] é requerida somente se você precisar paginar os dados. Isto porque a instrução SQL definida por [[yii\data\SqlDataProvider::sql|sql]] será modificada pelo provider para retornar somente a página atual de dados solicitada. O provider ainda precisa saber o número total de dados a fim de calcular correctamente o número de páginas disponíveis.
 
## Array Data Provider <span id="array-data-provider"></span>
 
[[yii\data\ArrayDataProvider]] é melhor usado quando se trabalha com um grande array. O provider permite-lhe retornar uma página dos dados do array ordenados por uma ou várias colunas. Para usar [[yii\data\ArrayDataProvider]], você precisa especificar a propriedade [[yii\data\ArrayDataProvider::allModels|allModels]] como um grande array. Elementos deste array podem ser outros arrays associados
(por exemplo, resultados de uma query do [DAO](db-dao.md)) ou objetos (por exemplo uma isntância do [Active Record](db-active-record.md)).
Por exemplo,
 
```php
use yii\data\ArrayDataProvider;
 
$data = [
   ['id' => 1, 'name' => 'name 1', ...],
   ['id' => 2, 'name' => 'name 2', ...],
   ...
   ['id' => 100, 'name' => 'name 100', ...],
];
 
$provider = new ArrayDataProvider([
   'allModels' => $data,
   'pagination' => [
       'pageSize' => 10,
   ],
   'sort' => [
       'attributes' => ['id', 'name'],
   ],
]);
 
// obter as linhas na página corrente
$rows = $provider->getModels();
```
 
> Observação: Comparado [Active Data Provider](#active-data-provider) com [SQL Data Provider](#sql-data-provider),
 array data provider é menos eficiante porque requer o carregamento de *todo* o dado na memória.
 
## Trabalhando com chave de dados <span id="working-with-keys"></span>
 
Ao usar os itens de dados retornados por um data provider, muitas vezes você precisa identificar cada item de dados com uma chave única.
Por exemplo, se os itens de dados representam as informações do cliente, você pode querer usar o ID do cliente como a chave
para cada dado do cliente. Data providers podem retornar uma lista das tais chaves correspondentes aos itens de dados retornados por [[yii\data\DataProviderInterface::getModels()]]. Por exemplo,
 
```php
use yii\data\ActiveDataProvider;
 
$query = Post::find()->where(['status' => 1]);
 
$provider = new ActiveDataProvider([
   'query' => Post::find(),
]);
 
// retorna uma array de objetos Post
$posts = $provider->getModels();
 
// retorna os valores de chave primária correspondente a $posts
$ids = $provider->getKeys();
```
 
No exemplo abaixo, como você fornecer para [[yii\data\ActiveDataProvider]] um objeto [[yii\db\ActiveQuery]],
ele é inteligente o suficiente para retornar os valores de chave primária como chaves no resultado. Você também pode especificar explicitamente como os valores de chave devem ser calculados configurando
[[yii\data\ActiveDataProvider::key]] com um nome de coluna ou com uma função calback que retorna os valores das chaves. Por exemplo,
 
```php
// use "slug" column as key values
$provider = new ActiveDataProvider([
   'query' => Post::find(),
   'key' => 'slug',
]);
 
// usa o resultados do md5(id) como valor da chave
$provider = new ActiveDataProvider([
   'query' => Post::find(),
   'key' => function ($model) {
       return md5($model->id);
   }
]);
```
 
## Criado Data Provider customizado <span id="custom-data-provider"></span>
 
Para criar sau prórpia classe de data provider customizada, você deve implementar [[yii\data\DataProviderInterface]].
Um caminho fácil é extender de [[yii\data\BaseDataProvider]] o que permite a você se concentrar na lógica principal do data provider. Em particular, você precisa principalmente implementar os seguintes métodos:
                                                  
- [[yii\data\BaseDataProvider::prepareModels()|prepareModels()]]: prepara o data models que será disponibilizado na página atual e as retorna como um array.
- [[yii\data\BaseDataProvider::prepareKeys()|prepareKeys()]]:recebe um array de data models disponíveis e retorna chaves que lhes estão associados.
- [[yii\data\BaseDataProvider::prepareTotalCount()|prepareTotalCount]]: retorna um valor que indica o número total de data models no data provider.
 
Abaixo está um exemplo de um data provider que lê dados em CSV eficientemente:
 
```php
<?php
use yii\data\BaseDataProvider;
 
class CsvDataProvider extends BaseDataProvider
{
   /**
    * @var string name of the CSV file to read
    */
   public $filename;
   
   /**
    * @var string|callable name of the key column or a callable returning it
    */
   public $key;
   
   /**
    * @var SplFileObject
    */
   protected $fileObject; // SplFileObject é muito conveniente para procurar uma linha específica em um arquivo
   
   /**
    * @inheritdoc
    */
   public function init()
   {
       parent::init();
       
       // open file
       $this->fileObject = new SplFileObject($this->filename);
   }
   /**
    * @inheritdoc
    */
   protected function prepareModels()
   {
       $models = [];
       $pagination = $this->getPagination();
       if ($pagination === false) {
           // no caso não há paginação, lê todas as linhas
           while (!$this->fileObject->eof()) {
               $models[] = $this->fileObject->fgetcsv();
               $this->fileObject->next();
           }
       } else {
           // no caso existe paginação, lê somente uma página
           $pagination->totalCount = $this->getTotalCount();
           $this->fileObject->seek($pagination->getOffset());
           $limit = $pagination->getLimit();
           for ($count = 0; $count < $limit; ++$count) {
               $models[] = $this->fileObject->fgetcsv();
               $this->fileObject->next();
           }
       }
       return $models;
   }
   /**
    * @inheritdoc
    */
   protected function prepareKeys($models)
   {
       if ($this->key !== null) {
           $keys = [];
           foreach ($models as $model) {
               if (is_string($this->key)) {
                   $keys[] = $model[$this->key];
               } else {
                   $keys[] = call_user_func($this->key, $model);
               }
           }
           return $keys;
       } else {
           return array_keys($models);
       }
   }
   /**
    * @inheritdoc
    */
   protected function prepareTotalCount()
   {
       $count = 0;
       while (!$this->fileObject->eof()) {
           $this->fileObject->next();
           ++$count;
       }
       return $count;
   }
}
```
 

