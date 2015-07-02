Limitação de Taxa
=============


Para prevenir abusos, você deve
considerar a utilização de *limitação de taxa* na suas APIs. Por exemplo, você
pode quere limitar o uso da API para cada usuário para no máximo 100 chamadas
na API a cada 10 minutos. Se o número de solicitações recebidas por um usuário ultrapassar este limite, uma resposta com status 429 (significa "Muitas Requisições") deve ser retornada.

Para habilitar a limitação de taxa, a
[[yii\web\User::identityClass|user identity class]] deve implementar
[[yii\filters\RateLimitInterface]]. Esta
interface requer a implementação de três métodos:


* `getRateLimit()`: retorna o número
máximo de pedidos permitidos e o período de tempo (ex., `[100, 600]` significa
que pode haver, no máximo, 100 chamadas de API dentro de 600 segundo).
* `loadAllowance()`: retorna o número de
pedidos permitidos restantes  e a hora da última verificação .
* `saveAllowance()`: salva tanto o número de
requisições restantes e a hora atual.

Você pode usar duas colunas na tabela
de usuários para registrar estas informações. Com esses campos definidos, então
`loadAllowance()` e `saveAllowance()` podem ser implementados para ler e
guardar os valores das duas colunas correspondentes ao atual usuário autenticado.
Para melhorar o desempenho, você também pode considerar armazenar essas
informações em um cache ou armazenamento NoSQL.

Uma vez que a classe identidade
implementa a interface necessária, Yii irá automaticamente usar [[yii\filters\RateLimiter]]
configurada como um filtro da ação para o [[yii\rest\Controller]] realizar a
verificação de limitação de taxa. A limitação de taxa irá lançar uma exceção [[yii\web\TooManyRequestsHttpException]] quando o
limite for excedido.

Você pode configurar o limitador de
taxa da seguinte forma em suas classes controller REST:


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

Quando a limitação de taxa está
habilitada, por padrão a cada resposta será enviada com o seguinte cabeçalho
HTTP que contêm a informação atual de limitação de taxa:

* `X-Rate-Limit-Limit`, o número
máximo permitido de pedidos com um período de tempo
* `X-Rate-Limit-Remaining`, o número de
pedidos restantes no período de tempo atual
* `X-Rate-Limit-Reset`, o número de segundos
de espera a fim de obter o número máximo de pedidos permitidos

Você pode desativar esses cabeçalhos,
configurando [[yii\filters\RateLimiter::enableRateLimitHeaders]] para falso,
como mostrado no exemplo acima.
 

