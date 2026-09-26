Query Builder (Construtor de Consulta)
=============

Desenvolvido à partir do [Database Access Objects](db-dao.md), o query builder permite que você construa uma instrução SQL em um programático e independente de banco de dados. Comparado a escrever instruções SQL à mão, usar query builder lhe ajudará a escrever um código SQL relacional mais legível e gerar declarações  SQL mais seguras.  

Usar query builder geralmente envolve dois passos:

1. Criar um objeto  [[yii\db\Query]] para representar diferentes partes de uma instrução SQL (ex. `SELECT`, `FROM`).
2. Executar um método (ex. `all()`) do objeto [[yii\db\Query]] para recuperar dados do banco de dados.

O código a seguir mostra uma forma habitual de utilizar query builder:

```php
$rows = (new \yii\db\Query())
   ->select(['id', 'email'])
   ->from('user')
   ->where(['last_name' => 'Smith'])
   ->limit(10)
   ->all();
```

O código acima gera e executa a seguinte instrução SQL, onde o parâmetro `:last_name` está ligado a string `'Smith'`.

```sql
SELECT `id`, `email` 
FROM `user`
WHERE `last_name` = :last_name
LIMIT 10
```

> Observação: Geralmente, você trabalhará mais com o [[yii\db\Query]] do que com o [[yii\db\QueryBuilder]]. Este último é chamado pelo primeiro implicitamente quando você chama um dos métodos da query. O [[yii\db\QueryBuilder]] é a classe responsável por gerar instruções SGDBs dependentes (ex. colocar aspas em nomes de tabela/coluna) a partir de objetos de query independentemente do SGDB.


## Construindo Queries <span id="building-queries"></span>

Para construir um objeto [[yii\db\Query]], você pode chamar diferentes métodos de construção de query para especificar diferentes partes de uma instrução SQL. Os nomes destes métodos assemelha-se as palavras-chave SQL utilizados nas partes correspondentes da instrução SQL. Por exemplo, para especificar a parte da instrução SQL `FROM`, você deve chamar o método `from()`. Todos os métodos de construção de query retornam o próprio objeto query, que permite você encadear várias chamadas em conjunto. A seguir, descreveremos o uso de cada método de construção de query.


### [[yii\db\Query::select()|select()]] <span id="select"></span>

O método [[yii\db\Query::select()|select()]] especifica o fragmento de uma instrução SQL `SELECT`. Você pode especificar colunas para ser selecionado em um array ou uma string, como mostrado abaixo. Os nomes das colunas que estão sendo selecionadas serão automaticamente envolvidas entre aspas quando a instrução SQL está sendo gerada a partir do objeto query.

```php
$query->select(['id', 'email']);

// equivalente a:

$query->select('id, email');
```

Os nomes das colunas que estão sendo selecionadas podem incluir prefixos de tabela e/ou aliases de colunas, como você faz ao escrever instruções SQL manualmente. 
Por exemplo:

```php
$query->select(['user.id AS user_id', 'email']);

// é equivalente a:

$query->select('user.id AS user_id, email');
```

Se você estiver usando um array para especificar as colunas, você também pode usar as chaves do array para especificar os aliases das colunas. Por exemplo, o código acima pode ser reescrito da seguinte forma,

```php
$query->select(['user_id' => 'user.id', 'email']);
```

Se você não chamar o método [[yii\db\Query::select()|select()]] na criação da query, o `*` será selecionado, o que significa selecionar *todas* as colunas.

Além dos nomes de colunas, você também pode selecionar expressões DB. Você deve usar o formato array quando utilizar uma expressão DB que contenha vírgula para evitar que sejam gerados nomes de colunas de forma equivocada. Por exemplo:

```php
$query->select(["CONCAT(first_name, ' ', last_name) AS full_name", 'email']); 
```

A partir da versão 2.0.1, você também pode selecionar sub-queries. Você deve especificar cada sub-query na forma de um objeto [[yii\db\Query]]. Por exemplo:

```php
$subQuery = (new Query())->select('COUNT(*)')->from('user');

// SELECT `id`, (SELECT COUNT(*) FROM `user`) AS `count` FROM `post`
$query = (new Query())->select(['id', 'count' => $subQuery])->from('post');
```

Para utilizar a cláusula `distinct`, você pode chamar [[yii\db\Query::distinct()|distinct()]], como a seguir:

```php
// SELECT DISTINCT `user_id` ...
$query->select('user_id')->distinct();
```

Você pode chamar [[yii\db\Query::addSelect()|addSelect()]] para selecionar colunas adicionais. Por exemplo:

```php
$query->select(['id', 'username'])
   ->addSelect(['email']);
```


### [[yii\db\Query::from()|from()]] <span id="from"></span>

O método [[yii\db\Query::from()|from()]] especifica o fragmento de uma instrução SQL `FROM`. Por exemplo:

```php
// SELECT * FROM `user`
$query->from('user');
```

Você pode especificar todas tabelas a serem selecionadas a partir de uma string ou um array. O nome da tabela pode conter prefixos de esquema e/ou aliases de tabela, da mesma forma quando você escreve instruções SQL manualmente. Por exemplo:

```php
$query->from(['public.user u', 'public.post p']);

// é equivalente a:

$query->from('public.user u, public.post p');
```

Se você estiver usando o formato array, você também pode usar as chaves do array para especificar os aliases de tabelas, como mostrado a seguir:

```php
$query->from(['u' => 'public.user', 'p' => 'public.post']);
```

Além de nome de tabelas, você também pode selecionar a partir de sub-queries especificando-o um objeto [[yii\db\Query]].
Por exemplo:

```php
$subQuery = (new Query())->select('id')->from('user')->where('status=1');

// SELECT * FROM (SELECT `id` FROM `user` WHERE status=1) u 
$query->from(['u' => $subQuery]);
```


### [[yii\db\Query::where()|where()]] <span id="where"></span>

O método [[yii\db\Query::where()|where()]] especifica o fragmento de uma instrução SQL `WHERE`. Você pode usar um dos três formatos para especificar uma condição `WHERE`:

- formato string, ex., `'status=1'`
- formato hash, ex. `['status' => 1, 'type' => 2]`
- formato operador, ex. `['like', 'name', 'test']`


#### Formato String <span id="string-format"></span>

Formato de string é mais usado para especificar condições `WHERE` muito simples. Esta forma é muito semelhante a condições `WHERE` escritas manualmente. Por exemplo:

```php
$query->where('status=1');

// ou usar parâmetro para vincular os valores dinamicamente 
$query->where('status=:status', [':status' => $status]);
```

NÃO incorporar variáveis diretamente na condição como exemplificado abaixo, especialmente se os valores das variáveis vêm de entradas de dados dos usuários finais, porque isso vai fazer a sua aplicação ficar sujeita a ataques de injeção de SQL.

```php
// Perigoso! NÃO faça isto a menos que você esteja muito certo que o $status deve ser um número inteiro.
$query->where("status=$status");
```

Ao usar parâmetro, você pode chamar [[yii\db\Query::params()|params()]] ou [[yii\db\Query::addParams()|addParams()]] para especificar os parâmetros separadamente.

```php
$query->where('status=:status')
   ->addParams([':status' => $status]);
```


#### Formato Hash <span id="hash-format"></span>

Formato HASH é mais usado para especificar múltiplos  `AND` - sub-condições concatenadas, sendo cada uma afirmação simples de igualdade.
É escrito como um array cujas chaves são nomes de coluna e os valores correspondem ao conteúdo destas colunas. Por exemplo:

```php
// ...WHERE (`status` = 10) AND (`type` IS NULL) AND (`id` IN (4, 8, 15))
$query->where([
   'status' => 10,
   'type' => null,
   'id' => [4, 8, 15],
]);
```

Como você pode ver, o query builder é inteligente o suficiente para lidar corretamente com valores que são nulos ou arrays. Você também pode usar sub-queries com o formato hash conforme mostrado abaixo:

```php
$userQuery = (new Query())->select('id')->from('user');

// ...WHERE `id` IN (SELECT `id` FROM `user`)
$query->where(['id' => $userQuery]);
```


#### Formato Operador <span id="operator-format"></span>

Formato operador lhe permite especificar arbitrariamente condições de uma forma programática. Ele tem o seguinte formato:

```php
[operator, operand1, operand2, ...]
```

onde cada um dos operandos pode ser especificado no formato string, formato hash ou formato operador recursivamente, enquanto o operador pode ser um dos seguintes procedimentos:

- `and`: os operandos devem ser concatenados juntos usando `AND`. Por exemplo,
 `['and', 'id=1', 'id=2']` irá gerar `id=1 AND id=2`. Se um operando for um array,
 ele será convertido para string usando as regras descritas aqui. Por exemplo,
 `['and', 'type=1', ['or', 'id=1', 'id=2']]` irá gerar `type=1 AND (id=1 OR id=2)`.
 O método NÃO vai fazer qualquer tratamento de escapar caracteres ou colocar aspas.

- `or`: similar ao operador `and` exceto pelo fato de que os operandos são concatenados usando `OR`.

- `between`: o operando 1 deve ser um nome de coluna, e os operandos 2 e 3 devem ser os valores de início e fim. Por exemplo, `['between', 'id', 1, 10]` irá gerar `id BETWEEN 1 AND 10`.

- `not between`: similar ao `between` exceto pelo fato de que `BETWEEN` é substituído por `NOT BETWEEN` na geração da condição.

- `in`: o operando 1 deve ser um nome de coluna ou uma expressão DB. O operando 2 pode ser tanto um array ou um objeto `Query`. Será gerado uma condição `IN`. Se o operando 2 for um array, representará o intervalo dos valores que a coluna ou expressão DB devem ser; se o operando 2 for um objeto `Query`, uma sub-query será gerada e usada como intervalo da coluna ou expressão DB. Por exemplo, `['in', 'id', [1, 2, 3]]` irá gerar `id IN (1, 2, 3)`. O método fará o tratamento apropriado de aspas e escape de valores para o intervalo. O operador `in` também suporta colunas compostas. Neste caso, o operando 1 deve ser um array de colunas, enquanto o operando 2 deve ser um array de arrays ou um objeto `Query` representando o intervalo das colunas.

- `not in`: similar ao operador `in` exceto pelo fato de que o `IN` é substituído por `NOT IN` na geração da condição.

- `like`: o operando 1 deve ser uma coluna ou uma expressão DB, e o operando 2 deve ser uma string ou um array representando o valor que a coluna ou expressão DB devem atender. Por exemplo, `['like', 'name', 'tester']` irá gerar `name LIKE '%tester%'`. Quando a faixa de valor é dado como um array, múltiplos predicados `LIKE` serão gerados e concatenadas utilizando `AND`. Por exemplo, `['like', 'name', ['test', 'sample']]` irá gerar `name LIKE '%test%' AND name LIKE '%sample%'`. Você também pode fornecer um terceiro operando opcional para especificar como escapar caracteres especiais nos valores. O operando deve ser um array de mapeamentos de caracteres especiais. Se este operando não for fornecido, um mapeamento de escape padrão será usado. Você pode usar `false` ou um array vazio para indicar que os valores já estão escapados e nenhum escape deve ser aplicado. Note-se que ao usar um mapeamento de escape (ou o terceiro operando não é fornecido), os valores serão automaticamente fechado dentro de um par de caracteres percentuais.

 > Observação: Ao utilizar o SGDB PostgreSQL você também pode usar [`ilike`](https://www.postgresql.org/docs/8.3/functions-matching.html#FUNCTIONS-LIKE)
 > em vez de `like` para diferenciar maiúsculas de minúsculas.

- `or like`: similar ao operador `like` exceto pelo fato de que `OR` é usado para concatenar os predicados `LIKE` quando o operando 2 é um array.

- `not like`: similar ao operador `like` exceto pelo fato de que `LIKE` é substituído por `NOT LIKE`.

- `or not like`: similar ao operador `not like` exceto pelo fato de que `OR` é usado para concatenar os predicados `NOT LIKE`.

- `exists`: requer um operador que deve ser uma instância de [[yii\db\Query]] representando a sub-query. Isto criará uma expressão `EXISTS (sub-query)`.

- `not exists`: similar ao operador `exists` e cria uma expressão `NOT EXISTS (sub-query)`.

- `>`, `<=`, ou qualquer outro operador válido que leva dois operandos: o primeiro operando deve ser um nome de coluna enquanto o segundo um valor. Ex., `['>', 'age', 10]` vai gerar `age>10`.


#### Acrescentando Condições <span id="appending-conditions"></span>

Você pode usar [[yii\db\Query::andWhere()|andWhere()]] ou [[yii\db\Query::orWhere()|orWhere()]] para acrescentar condições adicionais a uma condição já existente. Você pode chamá-los várias vezes para acrescentar várias condições separadamente. Por exemplo:

```php
$status = 10;
$search = 'yii';

$query->where(['status' => $status]);

if (!empty($search)) {
   $query->andWhere(['like', 'title', $search]);
}
```

Se o `$search` não estiver vazio, a seguinte instrução SQL será gerada:

```sql
... WHERE (`status` = 10) AND (`title` LIKE '%yii%')
```


#### Filtrar Condições <span id="filter-conditions"></span>

Ao construir condições `WHERE` a partir de entradas de usuários finais, você geralmente deseja ignorar os valores vazios. Por exemplo, em um formulário de busca que lhe permite pesquisar por nome ou e-mail, você poderia ignorar as condições nome/e-mail se não houver entradas destes valores. Para atingir este objetivo utilize o método [[yii\db\Query::filterWhere()|filterWhere()]]:

```php
// $username and $email são inputs dos usuário finais 
$query->filterWhere([
   'username' => $username,
   'email' => $email,
]);
```

A única diferença entre  [[yii\db\Query::filterWhere()|filterWhere()]] e [[yii\db\Query::where()|where()]] é que o primeiro irá ignorar valores vazios fornecidos na condição no [formato hash](#hash-format). Então se `$email` for vazio e `$username` não, o código acima resultará um SQL como: `...WHERE username=:username`.

> Observação: Um valor é considerado vazio se ele for `null`, um array vazio, uma string vazia ou uma string que consiste em apenas espaços em branco. Assim como [[yii\db\Query::andWhere()|andWhere()]] e [[yii\db\Query::orWhere()|orWhere()]], você pode usar [[yii\db\Query::andFilterWhere()|andFilterWhere()]] e [[yii\db\Query::orFilterWhere()|orFilterWhere()]] para inserir condições de filtro adicionais.


### [[yii\db\Query::orderBy()|orderBy()]] <span id="order-by"></span>

O método  [[yii\db\Query::orderBy()|orderBy()]] especifica o fragmento de uma instrução SQL `ORDER BY`. Por exemplo:

```php
// ... ORDER BY `id` ASC, `name` DESC
$query->orderBy([
   'id' => SORT_ASC,
   'name' => SORT_DESC,
]);
```

No código acima, as chaves do array são nomes de colunas e os valores são a direção da ordenação. A constante PHP `SORT_ASC` indica ordem crescente e `SORT_DESC` ordem decrescente. Se `ORDER BY` envolver apenas nomes simples de colunas, você pode especificá-lo usando string, da mesma forma como faria escrevendo SQL manualmente. Por exemplo:

```php
$query->orderBy('id ASC, name DESC');
```

> Observação: Você deve usar o formato array se `ORDER BY` envolver alguma expressão DB. 

Você pode chamar [[yii\db\Query::addOrderBy()|addOrderBy()]] para incluir colunas adicionais para o fragmento `ORDER BY`. Por exemplo:

```php
$query->orderBy('id ASC')
   ->addOrderBy('name DESC');
```


### [[yii\db\Query::groupBy()|groupBy()]] <span id="group-by"></span>

O método [[yii\db\Query::groupBy()|groupBy()]] especifica o fragmento de uma instrução SQL `GROUP BY`. Por exemplo:

```php
// ... GROUP BY `id`, `status`
$query->groupBy(['id', 'status']);
```

Se o `GROUP BY` envolver apenas nomes de colunas simples, você pode especificá-lo usando uma string, da mesma forma como faria escrevendo SQL manualmente. Por exemplo:

```php
$query->groupBy('id, status');
```

> Observação: Você deve usar o formato array se `GROUP BY` envolver alguma expressão DB.

Você pode chamar [[yii\db\Query::addGroupBy()|addGroupBy()]] para incluir colunas adicionais ao fragmento `GROUP BY`. Por exemplo:

```php
$query->groupBy(['id', 'status'])
   ->addGroupBy('age');
```


### [[yii\db\Query::having()|having()]] <span id="having"></span>

O método [[yii\db\Query::having()|having()]] especifica o fragmento de uma instrução SQL `HAVING`. Este método recebe uma condição que pode ser especificada da mesma forma como é feito para o [where()](#where). Por exemplo:

```php
// ... HAVING `status` = 1
$query->having(['status' => 1]);
```

Por favor, consulte a documentação do [where()](#where) para mais detalhes de como especificar uma condição.

Você pode chamar [[yii\db\Query::andHaving()|andHaving()]] ou [[yii\db\Query::orHaving()|orHaving()]] para incluir uma condição adicional para o fragmento `HAVING`. Por exemplo:

```php
// ... HAVING (`status` = 1) AND (`age` > 30)
$query->having(['status' => 1])
   ->andHaving(['>', 'age', 30]);
```


### [[yii\db\Query::limit()|limit()]] e [[yii\db\Query::offset()|offset()]] <span id="limit-offset"></span>

Os métodos  [[yii\db\Query::limit()|limit()]] e [[yii\db\Query::offset()|offset()]] especificam os fragmentos de uma instrução SQL `LIMIT` e `OFFSET`. Por exemplo:

```php
// ... LIMIT 10 OFFSET 20
$query->limit(10)->offset(20);
```

Se você especificar um limit ou offset inválido (Ex. um valor negativo), ele será ignorado.

> Observação: Para SGDBs que não suportam `LIMIT` e `OFFSET` (ex. MSSQL), query builder irá gerar uma instrução SQL que emula o comportamento `LIMIT`/`OFFSET`.


### [[yii\db\Query::join()|join()]] <span id="join"></span>

O método [[yii\db\Query::join()|join()]] especifica o fragmento de uma instrução SQL `JOIN`. Por exemplo:

```php
// ... LEFT JOIN `post` ON `post`.`user_id` = `user`.`id`
$query->join('LEFT JOIN', 'post', 'post.user_id = user.id');
```

O método [[yii\db\Query::join()|join()]] recebe quatro parâmetros:

- `$type`: tipo do join, ex., `'INNER JOIN'`, `'LEFT JOIN'`.
- `$table`: o nome da tabela a ser unida.
- `$on`: opcional, a condição do join, isto é, o fragmento `ON`. Por favor, consulte [where()](#where) para detalhes sobre como especificar uma condição.
- `$params`: opcional, os parâmetros a serem vinculados à condição do join.

Você pode usar os seguintes métodos de atalho para especificar `INNER JOIN`, `LEFT JOIN` e `RIGHT JOIN`, respectivamente.

- [[yii\db\Query::innerJoin()|innerJoin()]]
- [[yii\db\Query::leftJoin()|leftJoin()]]
- [[yii\db\Query::rightJoin()|rightJoin()]]

Por exemplo:

```php
$query->leftJoin('post', 'post.user_id = user.id');
```

Para unir múltiplas tabelas, chame os métodos join acima multiplas vezes, uma para cada tabela. Além de unir tabelas, você também pode unir sub-queries. Para fazê-lo, especifique a sub-queries a ser unida como um objeto [[yii\db\Query]]. Por exemplo:

```php
$subQuery = (new \yii\db\Query())->from('post');
$query->leftJoin(['u' => $subQuery], 'u.id = author_id');
```

Neste caso, você deve colocar a sub-query em um array e usar as chaves do array para especificar o alias.


### [[yii\db\Query::union()|union()]] <span id="union"></span>

O método [[yii\db\Query::union()|union()]] especifica o fragmento de uma instrução SQL `UNION`. Por exemplo:

```php
$query1 = (new \yii\db\Query())
   ->select("id, category_id AS type, name")
   ->from('post')
   ->limit(10);

$query2 = (new \yii\db\Query())
   ->select('id, type, name')
   ->from('user')
   ->limit(10);

$query1->union($query2);
```

Você  pode chamar [[yii\db\Query::union()|union()]] múltiplas vezes para acrescentar mais fragmentos `UNION`. 


## Métodos Query <span id="query-methods"></span>

[[yii\db\Query]] fornece um conjunto de métodos para diferentes propósitos da consulta:

- [[yii\db\Query::all()|all()]]: retorna um array de linhas sendo cada linha um array de pares nome-valor.
- [[yii\db\Query::one()|one()]]: retorna a primeira linha do resultado.
- [[yii\db\Query::column()|column()]]: retorna a primeira coluna do resultado.
- [[yii\db\Query::scalar()|scalar()]]: retorna um valor escalar localizado na primeira linha e coluna do primeiro resultado.
- [[yii\db\Query::exists()|exists()]]: retorna um valor que indica se a consulta contém qualquer resultado.
- [[yii\db\Query::count()|count()]]: retorna a quantidade de resultados da query.
- Outros métodos de agregação da query, incluindo [[yii\db\Query::sum()|sum($q)]], [[yii\db\Query::average()|average($q)]], [[yii\db\Query::max()|max($q)]], [[yii\db\Query::min()|min($q)]]. O parâmetro `$q` é obrigatório para estes métodos e pode ser um nome de uma coluna ou expressão DB. Por exemplo:

```php
// SELECT `id`, `email` FROM `user`
$rows = (new \yii\db\Query())
   ->select(['id', 'email'])
   ->from('user')
   ->all();
   
// SELECT * FROM `user` WHERE `username` LIKE `%test%`
$row = (new \yii\db\Query())
   ->from('user')
   ->where(['like', 'username', 'test'])
   ->one();
```

> Observação: O método [[yii\db\Query::one()|one()]] retorna apenas a primeira linha do resultado da query. Ele não adiciona `LIMIT 1` para a geração da sentença SQL. Isso é bom e preferível se você souber que a query retornará apenas uma ou algumas linhas de dados (Ex. se você estiver consultando com algumas chaves primárias). Entretanto, se a query pode retornar muitas linha de dados, você deve chamar `limit(1)` explicitamente para melhorar a performance. Ex., `(new \yii\db\Query())->from('user')->limit(1)->one()`.

Todos estes métodos query recebem um parâmetro opcional `$db` que representa a [[yii\db\Connection|conexão do DB]] que deve ser usada para realizar uma consulta no DB. Se você omitir este parâmetro, o [componente da aplicação](structure-application-components.md) `db` será usado como a conexão do DB. Abaixo está um outro exemplo do método  `count()`:

```php
// executes SQL: SELECT COUNT(*) FROM `user` WHERE `last_name`=:last_name
$count = (new \yii\db\Query())
   ->from('user')
   ->where(['last_name' => 'Smith'])
   ->count();
```

Quando você chamar um método de [[yii\db\Query]], ele na verdade faz o seguinte trabalho por baixo dos panos:

* Chama [[yii\db\QueryBuilder]] para gerar uma instrução SQL com base na atual construção de [[yii\db\Query]];
* Cria um objeto [[yii\db\Command]] com a instrução SQL gerada;
* Chama um método query (ex. `queryAll()`) do [[yii\db\Command]] para executar a instrução SQL e retornar os dados.

Algumas vezes, você pode querer examinar ou usar a instrução SQL construído a partir de um objeto [[yii\db\Query]]. Você pode atingir este objetivo com o seguinte código: 

```php
$command = (new \yii\db\Query())
   ->select(['id', 'email'])
   ->from('user')
   ->where(['last_name' => 'Smith'])
   ->limit(10)
   ->createCommand();
   
// mostra a instrução SQL 
echo $command->sql;

// Mostra os parâmetros que serão ligados
print_r($command->params);

// retorna todas as linhas do resultado da query
$rows = $command->queryAll();
```


### Indexando os Resultados da Query <span id="indexing-query-results"></span>

Quando você chama [[yii\db\Query::all()|all()]], será retornado um array de linhas que são indexadas por inteiros consecutivos. Algumas vezes você pode querer indexa-los de forma diferente, tal como indexar por uma coluna ou valor de expressão em particular. Você pode atingir este objetivo chamando [[yii\db\Query::indexBy()|indexBy()]] antes de [[yii\db\Query::all()|all()]]. Por exemplo:

```php
// retorna [100 => ['id' => 100, 'username' => '...', ...], 101 => [...], 103 => [...], ...]
$query = (new \yii\db\Query())
   ->from('user')
   ->limit(10)
   ->indexBy('id')
   ->all();
```

Para indexar através de valores de expressão, passe uma função anônima para o método [[yii\db\Query::indexBy()|indexBy()]]:

```php
$query = (new \yii\db\Query())
   ->from('user')
   ->indexBy(function ($row) {
       return $row['id'] . $row['username'];
   })->all();
```

A função anônima recebe um parâmetro `$row` que contém os dados da linha atual e deve devolver um valor escalar que irá ser utilizada como índice para o valor da linha atual.


### Batch Query (Consultas em Lote) <span id="batch-query"></span>

Ao trabalhar com grandes quantidades de dados, métodos tais como [[yii\db\Query::all()]] não são adequados porque eles exigem carregar todos os dados na memória. Para manter o requisito de memória baixa, Yii fornece o chamado suporte batch query. Um batch query faz uso do cursor de dados e obtém dados em lotes. Batch query pode ser usado como a seguir:

```php
use yii\db\Query;

$query = (new Query())
   ->from('user')
   ->orderBy('id');

foreach ($query->batch() as $users) {
   // $users é um array de 100 ou menos linha da tabela user
}

// ou se você quiser fazer uma iteração da linha uma por uma
foreach ($query->each() as $user) {
   // $user representa uma linha de dados a partir da tabela user
}
```

O método [[yii\db\Query::batch()]] and [[yii\db\Query::each()]] retorna um objeto [[yii\db\BatchQueryResult]] que implementa a interface `Iterator` e, assim, pode ser utilizado na construção do `foreach`. Durante a primeira iteração, uma consulta SQL é feita à base de dados. Os dados são, então, baixados em lotes nas iterações restantes. Por padrão, o tamanho do batch é 100, significando 100 linhas de dados que serão baixados a cada batch. Você pode mudar o tamanho do batch passando o primeiro parâmetro para os métodos `batch()` ou `each()`.

Em comparação com o [[yii\db\Query::all()]], o batch query somente carrega 100 linhas de dados na memória a cada vez. Se você processar os dados e, em seguida, descartá-lo imediatamente, o batch query pode ajudar a reduzir o uso de memória. Se você especificar o resultado da query a ser indexado por alguma coluna via [[yii\db\Query::indexBy()]], o batch query ainda vai manter o índice adequado. Por exemplo:

```php
$query = (new \yii\db\Query())
   ->from('user')
   ->indexBy('username');

foreach ($query->batch() as $users) {
   // $users é indexado pela coluna  "username"
}

foreach ($query->each() as $username => $user) {
}
```
