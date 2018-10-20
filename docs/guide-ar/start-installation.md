# <div dir="rtl">تثبيت ال Yii</div>

<p dir="rtl">يمكنك تثبيت ال Yii بطريقتين ، الأولى باستخدام مدير الحزم <a href="https://getcomposer.org">Composer</a> أو عن طريق تنزيل Archive File. الطريقة الأولى هي الطريقة المفضلة للعمل، ، لأنها تتيح لك تثبيت [<a href="structure-extensions.md">extensions</a> - ملحقات أو اضافات] جديدة، أو تحديث إطار العمل Yii ببساطة عن طريق تشغيل أمر واحد فقط.
</p>

<p dir="rtl">
    التثبيت الإفتراضي لل Yii ينتج عنه بنية تركيبة منظمة ومرتبة للمجلدات والملفات التي بداخلها، ويوفر هذا الكلام بعض المميزات التي يتم إضافتها وإنشائها بشكل تلقائي مثل صفحة تسجيل الدخول، ونموذج اتصل بنا...الخ، هذا الأمر سيشكل نقطة إنطلاق جيدة لبدء العمل على أي مشروع.
</p>
    
<p dir="rtl">
    في هذه الصفحة من التوثيق سنقوم بشرح ووصف كيف يمكن تثبيت إطار العمل Yii وبالتحديد Yii2 Basic Project Template.
    هناك Template آخر موجود بإطار العمل Yii وهو <a href="https://www.yiiframework.com/extension/yiisoft/yii2-app-advanced/doc/guide">Yii2 Advanced Project Template</a>، وهو الأفضل للعمل وإنشاء المشاريع لفريق عمل برمجي، ولتطوير المشاريع متعددة الطبقات(multiple tires). 
</p>

<blockquote><p dir="rtl">
معلومة: قالب المشروع الأساسي (Basic) مناسب لتطوير 90% من تطبيقات الويب. ويختلف القالب المتقدم (Advanced Template) عن القالب الأساسي في كيفية تنظيم وهيكلة الشيفرة البرمجية.
اذا كنت جديدا في عالم تطوير تطبيقات الويب باستخدام ال Yii، فإننا نوصيك بقوة بأن تستخدم القالب الأساسي في بناء المشروع الخاص بك.
</p></blockquote>


## <div dir="rtl">تثبيت ال Yii من خلال (Composer)</div> <span id="installing-via-composer"></span>

### <div dir="rtl">تثبيت ال Composer</div>

<p dir="rtl">
إن لم يكن لديك Composer مثبت مسبقا، فيمكنك السير بخطوات تثبيته من خلال الدخول الى هذا الرابط <a href="https://getcomposer.org/download/">https://getcomposer.org/download/</a>.
لتثبيت ال Composer في كل من نظامي Linux و Max OS X، يمكنك تنفيذ الأوامر التالية: 
</p>

```bash
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
```
<p dir="rtl">
    ولنظام ويندوز يمكنك تثبيت ال <a href="https://getcomposer.org/Composer-Setup.exe">Composer-Setup.exe</a> ومن ثم عمل run 
</p>

<p dir="rtl">
يرجى الدخول الى <a href="https://getcomposer.org/doc/articles/troubleshooting.md">Troubleshooting section of the Composer Documentation</a> في حال واجهتك أي مشاكل متعلقة بال composer, وإذا كنت مستخدمًا جديدًا لل composer، ننصحك أيضًا بقراءة <a href="https://getcomposer.org/doc/01-basic-usage.md">قسم الاستخدام الأساسي</a> على الأقل من التوثيف الخاص بال composer. 
</p>

<p dir="rtl">
    في هذا الدليل ، نفترض أنك قمت بتثبيت ال composer على مستوى جميع المشاريع (<a href="https://getcomposer.org/doc/00-intro.md#globally">globally</a>)  بحيث تكون أوامر ال composer متاحة لجميع المشاريع من أي مكان. أما إذا كنت تستخدم ال composer.phar لمسار محدد فقط(local directory)،  فيجب عليك ضبط  الأومر وفقًا لذلك.

إذا كان ال composer مثبتًا من قبل، فتأكد من استخدام إصدار حديث. يمكنك تحديث ال composer عن طريق تنفيذ الأمر التالي `composer self-update`
</p>

<blockquote><p dir="rtl">
  ملاحظة مهمة: أثناء تثبيت ال Yii ، سيحتاج ال composer إلى طلب(request) الكثير من المعلومات من ال Github Api. يعتمد عدد الطلبات على عدد dependencies التي يمتلكها التطبيق الخاص بك، وقد يكون هذا العدد أكبر من الحد المسموح به من قبل ال Github Api <b>(Github API rate limit)</b>. إذا وصلت الى الحد الأعلى المسموح به من الطلبات، فقد يطلب منك ال composer بيانات تسجيل الدخول إلى Github، وذلك للحصول على رمز (token) للدخول إلى Github Api. اذا كانت عمليات الإتصال سريعة، فقد تصل إلى هذا الحد(limit) قبل أن يتمكن ال composer من التعامل معه ، لذالك نوصي بتكوين رمز الدخول(access token) قبل تثبيت ال Yii. يرجى الرجوع إلى التوثيق الخاص بال Composer والإطلاع على التعليمات الخاصة <a href="https://getcomposer.org/doc/articles/troubleshooting.md#api-rate-limit-and-oauth-tokens">Github API tokens</a> للحصول على الإرشادات اللازمة للقيام بذلك. 
</p></blockquote>

### Installing Yii <span id="installing-from-composer"></span>

With Composer installed, you can install Yii application template by running the following command
under a Web-accessible folder:

```bash
composer create-project --prefer-dist yiisoft/yii2-app-basic basic
```

This will install the latest stable version of Yii application template in a directory named `basic`.
You can choose a different directory name if you want.

> Info: If the `composer create-project` command fails you may also refer to the 
> [Troubleshooting section of the Composer Documentation](https://getcomposer.org/doc/articles/troubleshooting.md)
> for common errors. When you have fixed the error, you can resume the aborted installation
> by running `composer update` inside of the `basic` directory.

> Tip: If you want to install the latest development version of Yii, you may use the following command instead,
> which adds a [stability option](https://getcomposer.org/doc/04-schema.md#minimum-stability):
>
> ```bash
> composer create-project --prefer-dist --stability=dev yiisoft/yii2-app-basic basic
> ```
>
> Note that the development version of Yii should not be used for production as it may break your running code.


Installing from an Archive File <span id="installing-from-archive-file"></span>
-------------------------------

Installing Yii from an archive file involves three steps:

1. Download the archive file from [yiiframework.com](http://www.yiiframework.com/download/).
2. Unpack the downloaded file to a Web-accessible folder.
3. Modify the `config/web.php` file by entering a secret key for the `cookieValidationKey` configuration item
   (this is done automatically if you are installing Yii using Composer):

   ```php
   // !!! insert a secret key in the following (if it is empty) - this is required by cookie validation
   'cookieValidationKey' => 'enter your secret key here',
   ```


Other Installation Options <span id="other-installation-options"></span>
--------------------------

The above installation instructions show how to install Yii, which also creates a basic Web application that works out of the box.
This approach is a good starting point for most projects, either small or big. It is especially suitable if you just
start learning Yii.

But there are other installation options available:

* If you only want to install the core framework and would like to build an entire  application from scratch,
  you may follow the instructions as explained in [Building Application from Scratch](tutorial-start-from-scratch.md).
* If you want to start with a more sophisticated application, better suited to team development environments,
  you may consider installing the [Advanced Project Template](https://github.com/yiisoft/yii2-app-advanced/blob/master/docs/guide/README.md).


Installing Assets <span id="installing-assets"></span>
-----------------

Yii relies on [Bower](http://bower.io/) and/or [NPM](https://www.npmjs.org/) packages for the asset (CSS and JavaScript) libraries installation.
It uses Composer to obtain these libraries, allowing PHP and CSS/JavaScript package versions to resolve at the same time.
This can be achieved either by usage of [asset-packagist.org](https://asset-packagist.org) or [composer asset plugin](https://github.com/francoispluchino/composer-asset-plugin/).
Please refer to [Assets documentation](structure-assets.md) for more details.

You may want to either manage your assets via native Bower/NPM client, use CDN or avoid assets installation entirely.
In order to prevent assets installation via Composer, add the following lines to your 'composer.json':

```json
"replace": {
    "bower-asset/jquery": ">=1.11.0",
    "bower-asset/inputmask": ">=3.2.0",
    "bower-asset/punycode": ">=1.3.0",
    "bower-asset/yii2-pjax": ">=2.0.0"
},
```

> Note: in case of bypassing asset installation via Composer, you are responsible for the assets installation and resolving
> version collisions. Be prepared for possible inconsistencies among asset files from different extensions.


Verifying the Installation <span id="verifying-installation"></span>
--------------------------

After installation is done, either configure your web server (see next section) or use the
[built-in PHP web server](https://secure.php.net/manual/en/features.commandline.webserver.php) by running the following
console command while in the project `web` directory:
 
```bash
php yii serve
```

> Note: By default the HTTP-server will listen to port 8080. However if that port is already in use or you wish to 
serve multiple applications this way, you might want to specify what port to use. Just add the --port argument:

```bash
php yii serve --port=8888
```

You can use your browser to access the installed Yii application with the following URL:

```
http://localhost:8080/
```

![Successful Installation of Yii](images/start-app-installed.png)

You should see the above "Congratulations!" page in your browser. If not, please check if your PHP installation satisfies
Yii's requirements. You can check if the minimum requirements are met using one of the following approaches:

* Copy `/requirements.php` to `/web/requirements.php` and then use a browser to access it via `http://localhost/requirements.php`
* Run the following commands:

  ```bash
  cd basic
  php requirements.php
  ```

You should configure your PHP installation so that it meets the minimum requirements of Yii. Most importantly, you
should have PHP 5.4 or above. Ideally latest PHP 7. You should also install the [PDO PHP Extension](http://www.php.net/manual/en/pdo.installation.php)
and a corresponding database driver (such as `pdo_mysql` for MySQL databases), if your application needs a database.


Configuring Web Servers <span id="configuring-web-servers"></span>
-----------------------

> Info: You may skip this subsection for now if you are just test driving Yii with no intention
  of deploying it to a production server.

The application installed according to the above instructions should work out of box with either
an [Apache HTTP server](http://httpd.apache.org/) or an [Nginx HTTP server](http://nginx.org/), on
Windows, Mac OS X, or Linux running PHP 5.4 or higher. Yii 2.0 is also compatible with facebook's
[HHVM](http://hhvm.com/). However, there are some edge cases where HHVM behaves different than native
PHP, so you have to take some extra care when using HHVM.

On a production server, you may want to configure your Web server so that the application can be accessed
via the URL `http://www.example.com/index.php` instead of `http://www.example.com/basic/web/index.php`. Such configuration
requires pointing the document root of your Web server to the `basic/web` folder. You may also
want to hide `index.php` from the URL, as described in the [Routing and URL Creation](runtime-routing.md) section.
In this subsection, you'll learn how to configure your Apache or Nginx server to achieve these goals.

> Info: By setting `basic/web` as the document root, you also prevent end users from accessing
your private application code and sensitive data files that are stored in the sibling directories
of `basic/web`. Denying access to those other folders is a security improvement.

> Info: If your application will run in a shared hosting environment where you do not have permission
to modify its Web server configuration, you may still adjust the structure of your application for better security. Please refer to
the [Shared Hosting Environment](tutorial-shared-hosting.md) section for more details.

> Info: If you are running your Yii application behind a reverse proxy, you might need to configure
> [Trusted proxies and headers](runtime-requests.md#trusted-proxies) in the request component.

### Recommended Apache Configuration <span id="recommended-apache-configuration"></span>

Use the following configuration in Apache's `httpd.conf` file or within a virtual host configuration. Note that you
should replace `path/to/basic/web` with the actual path for `basic/web`.

```apache
# Set document root to be "basic/web"
DocumentRoot "path/to/basic/web"

<Directory "path/to/basic/web">
    # use mod_rewrite for pretty URL support
    RewriteEngine on
    # If a directory or a file exists, use the request directly
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    # Otherwise forward the request to index.php
    RewriteRule . index.php

    # if $showScriptName is false in UrlManager, do not allow accessing URLs with script name
    RewriteRule ^index.php/ - [L,R=404]

    # ...other settings...
</Directory>
```


### Recommended Nginx Configuration <span id="recommended-nginx-configuration"></span>

To use [Nginx](http://wiki.nginx.org/), you should install PHP as an [FPM SAPI](http://php.net/install.fpm).
You may use the following Nginx configuration, replacing `path/to/basic/web` with the actual path for 
`basic/web` and `mysite.test` with the actual hostname to serve.

```nginx
server {
    charset utf-8;
    client_max_body_size 128M;

    listen 80; ## listen for ipv4
    #listen [::]:80 default_server ipv6only=on; ## listen for ipv6

    server_name mysite.test;
    root        /path/to/basic/web;
    index       index.php;

    access_log  /path/to/basic/log/access.log;
    error_log   /path/to/basic/log/error.log;

    location / {
        # Redirect everything that isn't a real file to index.php
        try_files $uri $uri/ /index.php$is_args$args;
    }

    # uncomment to avoid processing of calls to non-existing static files by Yii
    #location ~ \.(js|css|png|jpg|gif|swf|ico|pdf|mov|fla|zip|rar)$ {
    #    try_files $uri =404;
    #}
    #error_page 404 /404.html;

    # deny accessing php files for the /assets directory
    location ~ ^/assets/.*\.php$ {
        deny all;
    }

    location ~ \.php$ {
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        fastcgi_pass 127.0.0.1:9000;
        #fastcgi_pass unix:/var/run/php5-fpm.sock;
        try_files $uri =404;
    }

    location ~* /\. {
        deny all;
    }
}
```

When using this configuration, you should also set `cgi.fix_pathinfo=0` in the `php.ini` file
in order to avoid many unnecessary system `stat()` calls.

Also note that when running an HTTPS server, you need to add `fastcgi_param HTTPS on;` so that Yii
can properly detect if a connection is secure.
