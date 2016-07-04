共享托管环境
==========================

共享的托管环境常常会对目录结构以及配置文件有较多的限制。然而，在大多数情况下，你仍可以通过少量的修改以在共享托管环境下运行 Yii 2.0。

部署一个基础应用模板
---------------------------

由于共享托管环境往往只有一个 webroot，如果可能，请优先使用基础项目模板（ basic project template )构建你的应用程序。参考 [安装 Yii 章节](start-installation.md)在本地安装基础项目模板。当你让应用程序在本地正常运行后，我们将要做少量的修改以让它可以在共享托管服务器运行。

### 重命名 webroot <span id="renaming-webroot"></span>

用FTP或者其他的工具连接到你的托管服务器，你可能看到类似如下的目录结构：

```
config
logs
www
```

在以上，`www` 是你的 web 服务器的 webroot 目录。不同的托管环境下名称可能各不相同，通常是类似： `www`, `htdocs`, 和 `public_html` 之类的名称。

对于我们的基础项目模板而言，其 webroot 名为 `web` 。 在你上传你的应用程序到 web 服务器上去之前，将你的本地 webroot 重命名以匹配服务器。 即： 从 `web` 改为 `www`, `public_html` 或者其他你的托管环境的 webroot 名称。

### FTP 根目录可写

如果你有 FTP 根目录的写权限，即，有 `config`, `logs` 和 `www` 的根目录，那么，如本地根目录相同的结构上传 `assets`, `commands` 等目录。

### 增加 web 服务器的额外配置 <span id="add-extras-for-webserver"></span>

如果你的 web 服务器是 Apache，你需要增加一个包含如下内容的 `.htaccess` 文件到你的 `web` 目录(或者 `public_html` 根据实际情况而定，是你的 `index.php` 文件所在的目录)。

```
Options +FollowSymLinks
IndexIgnore */*

RewriteEngine on

# if a directory or a file exists, use it directly
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d

# otherwise forward it to index.php
RewriteRule . index.php
```

对于nginx而言，你不需要额外的配置文件。

### 检查环境要求

为了运行 Yii ，你的 web 服务器必须匹配它的环境要求。最低的要求必须是 PHP 5.4。为了检查环境配置，将 `requirements.php` 从你的根目录拷贝到 webroot 目录，
并通过浏览器输入 URL `http://example.com/requirements.php` 运行它。最后，检查结束后别忘了删除这个文件哦！

部署一个高级应用程序模板
---------------------------------

部署一个高级应用程序到共享的托管环境比基础应用要麻烦的原因在于它包含有两个 webroot 目录，而共享的托管环境不支持两个。对于这种情况，我们需要调整目录结构。

### 将入口文件移动到同一个 webroot

首先我们需要一个 webroot 目录，如[重命名 webroot](#renaming-webroot)一节所述，创建一个新的跟你的托管环境 webroot  同名的目录，如类似 `www` 或者 `public_html` 的名字。创建如下的目录结构，其中 `www` 目录指代你刚刚创建的 webroot 目录。

```
www
    admin
backend
common
console
environments
frontend
...
```

`www` 目录是我们的前台目录，所以将 `frontend/web` 里面的内容移到这个目录。 将 `backend/web` 里面的内容移到 `www/admin` 目录。对于每种情况下，你需要调整｀index.php` 和 `index-test.php` 里面引用的目录结构。

### 分离 Session 和 Cookie

通常情况下，backend 和 frontend 运行在不同的域下，当我们将其都移到同一个域时， frontend 和 backend 将会共享相同的 cookie，这样会造成冲突。为了修复这个问题，
如下调整 backend 的应用程序配置文件 `backend/config/main.php`：

```php
'components' => [
    'request' => [
        'csrfParam' => '_backendCSRF',
        'csrfCookie' => [
            'httpOnly' => true,
            'path' => '/admin',
        ],
    ],
    'user' => [
        'identityCookie' => [
            'name' => '_backendIdentity',
            'path' => '/admin',
            'httpOnly' => true,
        ],
    ],
    'session' => [
        'name' => 'BACKENDSESSID',
        'cookieParams' => [
            'path' => '/admin',
        ],
    ],
],
```
