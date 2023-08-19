Controllers (Controladores)
===========

Depois de criar as classes de recursos e especificar como os dados de recursos devem ser formatados, a próxima coisa a fazer é criar ações do controller para expor os recursos para os usuários finais através das APIs RESTful.

O Yii fornece duas classes básicas de controller para simplificar seu trabalho de criar ações RESTful: [[yii\rest\Controller]] e [[yii\rest\ActiveController]]. A diferença entre os dois controllers é que o último fornece um conjunto padrão de ações que são especificamente concebidos para lidar com recursos do [Active Record](db-active-record.md). Então, se você estiver usando [Active Record](db-active-record.md) e está confortável com as ações fornecidas, você pode considerar estender suas classes de controller de [[yii\rest\ActiveController]], que permitirá criar poderosas APIs RESTful com um mínimo de código.

Ambas classes [[yii\rest\Controller]] e [[yii\rest\ActiveController]] fornecem os seguintes recursos, algumas das quais serão descritas em detalhes nas próximas seções:

* Validação de Método HTTP;
* [Negociação de conteúdo e formatação de dados](rest-response-formatting.md);
* [Autenticação](rest-authentication.md);
* [Limitação de taxa](rest-rate-limiting.md).

O [[yii\rest\ActiveController]] oferece também os seguintes recursos:

* Um conjunto de ações comumente necessárias: `index`, `view`, `create`, `update`, `delete`, `options`;
* Autorização do usuário em relação à ação solicitada e recursos.


## Criando Classes Controller <span id="creating-controller"></span>

Ao criar uma nova classe de controller, uma convenção na nomenclatura da classe é usar o nome do tipo de recurso no singular. Por exemplo, para disponibilizar as informações do usuário, o controlador pode ser nomeado como `UserController`. Criar uma nova ação é semelhante à criação de uma ação de uma aplicação Web. A única diferença é que em vez de renderizar o resultado usando uma view e chamando o método `render()`, para ações RESTful você retorna diretamente os dados. O [[yii\rest\Controller::serializer|serializer]] e o [[yii\web\Response|objeto response]] vão converter os dados originais para o formato solicitado. Por exemplo:

```php
public function actionView($id)
{
   return User::findOne($id);
}
```


## Filtros <span id="filters"></span>

A maioria dos recursos da API RESTful fornecidos por [[yii\rest\Controller]] são implementadas por [filtros](structure-filters.md).
Em particular, os seguintes filtros serão executados na ordem em que estão listados:

* [[yii\filters\ContentNegotiator|contentNegotiator]]: suporta a negociação de conteúdo, a ser explicado na seção [Formatação de Resposta](rest-response-formatting.md);
* [[yii\filters\VerbFilter|verbFilter]]: suporta validação de métodos HTTP;
* [[yii\filters\auth\AuthMethod|authenticator]]: suporta autenticação de usuários, que será explicado na seção [Autenticação](rest-authentication.md);
* [[yii\filters\RateLimiter|rateLimiter]]: suporta limitação de taxa, que será explicado na seção
 [Limitação de taxa](rest-rate-limiting.md).

Estes filtros são declarados no método [[yii\rest\Controller::behaviors()|behaviors()]].
Você pode sobrescrever esse método para configurar alguns filtros, desativar outros, ou adicionar seus próprios filtros. Por exemplo, se você precisar somente de autenticação básica de HTTP, poderá utilizar o seguinte código:

```php
use yii\filters\auth\HttpBasicAuth;

public function behaviors()
{
   $behaviors = parent::behaviors();
   $behaviors['authenticator'] = [
       'class' => HttpBasicAuth::class,
   ];
   return $behaviors;
}
```


## Estendendo `ActiveController` <span id="extending-active-controller"></span>

Se a sua classe controller estende de [[yii\rest\ActiveController]], você deve configurar a propriedade [[yii\rest\ActiveController::modelClass|modelClass]] para ser o nome da classe de recurso que você pretende servir através deste controller. A classe deve estender de [[yii\db\ActiveRecord]].


### Customizando Ações <span id="customizing-actions"></span>

Por padrão, o [[yii\rest\ActiveController]] fornece as seguintes ações:

* [[yii\rest\IndexAction|index]]: recursos de lista página por página;
* [[yii\rest\ViewAction|view]]: retorna os detalhes de um recurso especificado;
* [[yii\rest\CreateAction|create]]: cria um novo recurso;
* [[yii\rest\UpdateAction|update]]: atualiza um recurso existente;
* [[yii\rest\DeleteAction|delete]]: excluir o recurso especificado;
* [[yii\rest\OptionsAction|options]]: retorna os métodos HTTP suportados.

Todas essas ações são declaradas através do método [[yii\rest\ActiveController::actions()|actions()]]. Você pode configurar essas ações ou desativar algumas delas, sobrescrevendo o método `actions()`, como mostrado a seguir:

```php
public function actions()
{
   $actions = parent::actions();

   // desabilita as ações "delete" e "create"
   unset($actions['delete'], $actions['create']);

   // customiza a preparação do  data provider com o método "prepareDataProvider()"
   $actions['index']['prepareDataProvider'] = [$this, 'prepareDataProvider'];

   return $actions;
}

public function prepareDataProvider()
{
   // preparar e retornar um data provider para a ação "index"
}
```

Por favor, consulte as referências de classe para classes de ação individual para saber as opções de configuração que estão disponíveis.


### Executando Verificação de Acesso <span id="performing-access-check"></span>

Ao disponibilizar recursos por meio de APIs RESTful, muitas vezes você precisa verificar se o usuário atual tem permissão para acessar e manipular o(s) recurso(s) solicitado(s). Com o [[yii\rest\ActiveController]], isso pode ser feito sobrescrevendo o método [[yii\rest\ActiveController::checkAccess()|checkAccess()]]  conforme a seguir:

```php
/**
* Verifica os privilégios do usuário corrente.
*
* Este método deve ser sobrescrito para verificar se o usuário atual tem o privilégio
* para executar a ação especificada diante do modelo de dados especificado.
* se o usuário não tiver acesso, uma [[ForbiddenHttpException]] deve ser lançada.
*
* @param string $action o ID da ação a ser executada
* @param \yii\base\Model $model o model a ser acessado. Se `null`, isso significa que nenhum model específico está sendo acessado.
* @param array $params parâmetros adicionais
* @throws ForbiddenHttpException se o usuário não tiver acesso
*/
public function checkAccess($action, $model = null, $params = [])
{
   // verifica se o usuário pode acessar $action and $model
   // lança a ForbiddenHttpException se o acesso for negado
   if ($action === 'update' || $action === 'delete') {
        if ($model->author_id !== \Yii::$app->user->id)
            throw new \yii\web\ForbiddenHttpException(sprintf('You can only %s articles that you\'ve created.', $action));
    }
}
```

O método `checkAccess()` será chamado pelas ações padrões do [[yii\rest\ActiveController]]. Se você criar novas ações e também desejar executar a verificação de acesso, deve chamar esse método explicitamente nas novas ações.

> Dica: Você pode implementar `checkAccess()` usando o [componente de Role-Based Access Control (RBAC)](security-authorization.md).

