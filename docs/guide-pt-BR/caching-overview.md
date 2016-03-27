Cache
=======

Cache é uma maneira simples e eficiente de melhorar o desempenho de uma aplicação Web. Ao gravar dados relativamente
estáticos em cache e servindo os do cache quando requisitados, a aplicação economiza o tempo que seria necessário para renderizar as informações do zero todas as vezes.

Cache pode ocorrer em diferentes níveis e locais em uma aplicação Web. No servidor, no baixo nível,
cache pode ser usado para armazenar dados básicos, como a informação de uma lista de artigos mais recentes trazidos
do banco de dados; e no alto nível, cache pode ser usado para armazenar fragmentos ou páginas Web inteiras, como o
resultado da renderização dos artigos mais recentes. No cliente, cache HTTP pode ser usado para manter o conteúdo da última página acessada no cache do navegador.

Yii suporta todos os quatro métodos de cache:
* [Cache de Dados](caching-data.md)
* [Cache de Fragmento](caching-fragment.md)
* [Cache de Página](caching-page.md)
* [Cache de HTTP](caching-http.md)
