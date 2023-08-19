Yii 和 Docker
=============

对于开发和部署 Yii 应用程序可以使用 Docker 容器运行。容器就像一个轻量级的独立虚拟机，它将其服务映射到主机的端口，即在端口 80 上的容器中的Web服务器在您的（本地）主机上的端口 8888 上可用。

容器可以解决许多问题，例如在开发人员计算机和服务器上具有相同的软件版本，在开发时快速部署或模拟多服务器体系结构。

您可以在 [docker.com](https://www.docker.com/why-docker) 上阅读有关Docker容器的更多信息。

## 要求

- `docker`
- `docker-compose`

访问[下载页面](https://www.docker.com/products/container-runtime)获取 Docker 工具。

## 安装

安装后，你应该可以运行 `docker ps` 并看到类似的输出：

```
CONTAINER ID   IMAGE   COMMAND   CREATED   STATUS   PORTS
```

这意味着您的Docker守护进程已启动并正在运行。

另外运行 `docker-compose version`，你的输出应该是这样的

```
docker-compose version 1.20.0, build unknown
docker-py version: 3.1.3
CPython version: 3.6.4
OpenSSL version: OpenSSL 1.1.0g  2 Nov 2017
```

使用 Compose，您可以配置管理您的应用程序所需的所有服务，例如数据库和缓存。

## 资源

- 基于 PHP 的 Yii 镜像可以在这里找到 [yii2-docker](https://github.com/yiisoft/yii2-docker)
- Docker 支持的 [yii2-app-basic](https://github.com/yiisoft/yii2-app-basic#install-with-docker)
- Docker 支持的 [yii2-app-advanced](https://github.com/yiisoft/yii2-app-advanced/pull/347) 正在开发中

## 用法

Docker的基本命令是：

    docker-compose up -d
    
在后台启动堆栈中的所有服务

    docker-compose ps
    
列出正在运行的服务

    docker-compose logs -f
    
持续查看所有服务的日志

    docker-compose stop
    
优雅地停止堆栈中的所有服务

    docker-compose kill
    
立即停止堆栈中的所有服务

    docker-compose down -v
    
停止并删除所有服务，**在不使用 host-volumes 时注意数据丢失**

在容器中运行命令

    docker-compose run --rm php composer install
    
在新的容器中运行 composer 安装

    docker-compose exec php bash
    
在 *运行中的* `php` 服务中执行 bash


## 高级主题

### Yii 框架测试

你可以按照[这里](https://github.com/yiisoft/yii2/blob/master/tests/README.md#dockerized-testing)描述的方式为 Yii 本身运行 dockerized 框架测试。

### 数据库管理工具

以 MySQL（`mysql`）的形式运行MySQL时，可以将 phpMyAdmin 容器添加到您的堆栈中，如下所示：

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
