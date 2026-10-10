Yii e Docker
==============

Para o desenvolvimento e implantação de aplicativos Yii, eles podem ser executados como contêineres Docker. Um contêiner é como uma máquina virtual isolada e leve que mapeia seus serviços para as portas do host, ou seja, um servidor da web em um contêiner na porta 80 está disponível na porta 8888 do seu (local) host.

Os contêineres podem resolver muitos problemas, como ter versões idênticas de software no computador do desenvolvedor e no servidor, implantações rápidas ou simulação de arquitetura multi-servidor durante o desenvolvimento.

Você pode ler mais sobre contêineres Docker em [docker.com](https://www.docker.com/why-docker).

## Requisitos

- `docker`
- `docker-compose`

Visite a [página de download](https://www.docker.com/products/container-runtime) para obter as ferramentas do Docker.

## Instalação

Após a instalação, você deve ser capaz de executar o comando docker ps e ver uma saída semelhante a esta:

```
CONTAINER ID   IMAGE   COMMAND   CREATED   STATUS   PORTS
```

Isso significa que o seu daemon Docker está em execução.

Além disso, execute o comando docker-compose version, a saída deve ser semelhante a esta:

```
docker-compose version 1.20.0, build unknown
docker-py version: 3.1.3
CPython version: 3.6.4
OpenSSL version: OpenSSL 1.1.0g  2 Nov 2017
```

Com o Compose, você pode configurar e gerenciar todos os serviços necessários para a sua aplicação, como bancos de dados e cache.

## Recursos

- As imagens base do PHP para Yii podem ser encontradas em [yii2-docker](https://github.com/yiisoft/yii2-docker)
- Suporte do Docker para [yii2-app-basic](https://github.com/yiisoft/yii2-app-basic#install-with-docker)
- Suporte do Docker para [yii2-app-advanced](https://github.com/yiisoft/yii2-app-advanced/pull/347) está em desenvolvimento

## Uso

Os comandos básicos do Docker são

    docker-compose up -d
    
para iniciar todos os serviços em sua pilha, em segundo plano

    docker-compose ps
    
para listar os serviços em execução

    docker-compose logs -f
    
para visualizar os logs de todos os serviços continuamente

    docker-compose stop
    
para interromper todos os serviços em sua pilha de forma elegante

    docker-compose kill
    
para interromper todos os serviços em sua pilha imediatamente

    docker-compose down -v

para parar e remover todos os serviços, **atenção à perda de dados ao não usar volumes do host**

Para executar comandos em um contêiner:

    docker-compose run --rm php composer install
    
executa a instalação do Composer em um novo contêiner

    docker-compose exec php bash
    
executa um shell bash em um serviço php que está em *execução*.


## Tópicos avançados

### Testes do framework Yii

Você pode executar os testes do framework Yii em um contêiner Docker, conforme descrito [aqui](https://github.com/yiisoft/yii2/blob/master/tests/README.md#dockerized-testing).

### Database administration tools

Ao executar o MySQL como (`mysql`), você pode adicionar um contêiner do phpMyAdmin à sua pilha, como mostrado abaixo:

```
    phpmyadmin:
        image: phpmyadmin/phpmyadmin
        ports:
            - '8888:80'
        environment:
            - PMA_ARBITRARY=1
            - PMA_HOST=mysql
        depends_on:
            - mysql
```
