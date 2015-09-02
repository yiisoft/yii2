Limitador de Acesso
=============

Para prevenir abusos, você pode considerar a utilização de um *limitador de acesso* nas suas APIs. Por exemplo, você pode querer limitar o uso da API para cada usuário em no máximo 100 chamadas a cada 10 minutos. Se o número de solicitações recebidas por usuário ultrapassar este limite, uma resposta com status 429 (significa "Muitas Requisições") deve ser retornada.

Para habilitar o limitador de acesso, a [[yii\web\User::identityClass|classe de identidade do usuário]] deve implementar [[yii\filters\RateLimitInterface]]. Esta interface requer a implementação de três métodos:

* `getRateLimit()`: retorna o número máximo de pedidos permitidos e o período de tempo (ex., `[100, 600]` significa que pode haver, no máximo, 100 chamadas de API dentro de 600 segundo);
* `loadAllowance()`: retorna o número restante de pedidos permitidos e a hora da última verificação;
* `saveAllowance()`: salva tanto o número restante de requisições e a hora atual.

Você pode usar duas colunas na tabela de usuários para registrar estas informações. Com esses campos definidos, então `loadAllowance()` e `saveAllowance()` podem ser implementados para ler e guardar os valores das duas colunas correspondentes ao atual usuário autenticado.
Para melhorar o desempenho, você também pode considerar armazenar essas informações em um cache ou armazenamento NoSQL.

Uma vez que a classe de identidade do usuário estiver com a interface necessária implementada, o Yii automaticamente usará a classe [[yii\filters\RateLimiter]] configurada como um filtro da ação para o [[yii\rest\Controller]] realizar a verificação da limitação do acesso. O limitador de acesso lançará uma exceção [[yii\web\TooManyRequestsHttpException]] quando o limite for excedido.

Você pode configurar o limitador de acesso da seguinte forma em suas classes controller REST:

```php
public function behaviors()
{
  $behaviors =
parent::behaviors();
  $behaviors['rateLimiter']['enableRateLimitHeaders']
= false;
  return $behaviors;
}
```

Quando o limitador de acesso está habilitado, por padrão a cada resposta será enviada com o seguinte cabeçalho HTTP contendo a informação da atual taxa de limitação:

* `X-Rate-Limit-Limit`, o número máximo permitido de pedidos em um período de tempo;
* `X-Rate-Limit-Remaining`, o número de pedidos restantes no período de tempo atual;
* `X-Rate-Limit-Reset`, o número de segundos de espera a fim de obter o número máximo de pedidos permitidos.

Você pode desativar esses cabeçalhos, configurando [[yii\filters\RateLimiter::enableRateLimitHeaders]] para `false`, como mostrado no exemplo acima.


