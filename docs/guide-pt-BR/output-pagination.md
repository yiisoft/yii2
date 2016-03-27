Paginação
==========

Quando existem muitos dados para serem exibidos em uma única página, uma estratégia comum é mostrá-los em várias páginas e em cada página exibir uma porção pequena dos dados. Esta estratégia é conhecida como *paginação*. O Yii usa o objeto [[yii\data\Pagination]] para representar as informações sobre um esquema de paginação. Em particular, 

* [[yii\data\Pagination::$totalCount|contagem total]] especifica o número total de itens de dados. Note que este é geralmente muito maior do que o número de itens de dados necessários para exibir em uma única página.
* [[yii\data\Pagination::$pageSize|quantidade por página]] especifica quantos itens cada página contém. O padrão é 20.
* [[yii\data\Pagination::$page|página atual]] retorna a página corrente (baseada em zero). O valor padrão é 0, ou seja, a primeira página.

Com o objeto [[yii\data\Pagination]] totalmente especificado, você pode recuperar e exibir dados parcialmente. Por exemplo, se você está buscando dados a partir de um banco de dados, você pode especificar as cláusulas `OFFSET` e `LIMIT` da query com os valores correspondentes fornecidos pela paginação. Abaixo está um exemplo, 

```php

use yii\data\Pagination;

// Cria uma query para pegar todos os artigos com status = 1

$query = Article::find()->where(['status' => 1]);

// pega o total de artigos (mas não baixa os dados ainda)

$count = $query->count();

// cria um objeto pagination com o total em $count

$pagination = new Pagination(['totalCount' => $count]);

// Lima a query usando a paginação e recupera os artigos

$articles = $query->offset($pagination->offset)

    ->limit($pagination->limit)

    ->all();

```

Qual página de artigos será devolvido no exemplo acima? Depende se um parâmetro da query chamado `page` for fornecido. Por padrão, a paginação 
tentará definir a [[yii\data\Pagination::$page|página atual]] com o  valor do parâmetro `page`. Se o parâmetro não for fornecido, então o padrão será 0.
Para facilitar a construção de um elemento UI que suporta a paginação, Yii fornece o widget [[yii\widgets\LinkPager]] que exibe uma lista de botões de página na qual os usuários podem clicar para indicar qual a página de dados deve ser exibido. O widget recebe um objeto de paginação para que ele saiba qual é a sua página corrente e quantas botões de páginas devem ser exibido. Por exemplo,

```php

use yii\widgets\LinkPager;

echo LinkPager::widget([

    'pagination' => $pagination,

]);

```

Se você quer construir elemento UI manualmente, você pode utilizar [[yii\data\Pagination::createUrl()]] para criar URLs que conduziria a diferentes páginas. O método requer um parâmetro página e criará um formatado apropriado de URL Contendo o parâmetro página. Por exemplo,

```php

// especifica a rota que o URL a ser criada deve usar

// Se você não a especificar, a atual rota requerida será usado

$pagination->route = 'article/index';

// exibe: /index.php?r=article/index&page=100

echo $pagination->createUrl(100);

// exibe: /index.php?r=article/index&page=101

echo $pagination->createUrl(101);

```

> Dica: Você pode personalizar o nome do parâmetro de consulta `page` configurando a propriedade [[yii\data\Pagination::pageParam|pageParam]] ao criar o objeto de paginação.
