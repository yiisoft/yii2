# <div dir="rtl">تثبيت ال Yii</div>

<p dir="rtl">يمكنك تثبيت ال Yii بطريقتين ، الأولى باستخدام مدير الحزم <a href="https://getcomposer.org">Composer</a> أو عن طريق تنزيل Archive File. الطريقة الأولى هي الطريقة المفضلة للعمل، ، لأنها تتيح لك تثبيت [<a href="../guide/structure-extensions.md">extensions</a> - ملحقات أو اضافات] جديدة، أو تحديث إطار العمل Yii ببساطة عن طريق تشغيل أمر واحد فقط.
</p>

<p dir="rtl">
    التثبيت الإفتراضي لل Yii ينتج عنه بنية تركيبة منظمة ومرتبة للمجلدات والملفات التي بداخلها، ويوفر هذا الكلام بعض المميزات التي يتم إضافتها وإنشائها بشكل تلقائي مثل صفحة تسجيل الدخول، ونموذج اتصل بنا...الخ، هذا الأمر سيشكل نقطة إنطلاق جيدة لبدء العمل على أي مشروع.
</p>
    
<p dir="rtl">
    في هذه الصفحة من التوثيق سنقوم بشرح ووصف كيف يمكن تثبيت إطار العمل Yii وبالتحديد Yii2 Basic Project Template.
    هناك Template آخر موجود بإطار العمل Yii وهو <a href="https://www.yiiframework.com/extension/yiisoft/yii2-app-advanced/doc/guide">Yii2 Advanced Project Template</a>، وهو الأفضل للعمل وإنشاء المشاريع لفريق عمل برمجي، ولتطوير المشاريع متعددة الطبقات(multiple tires). 
</p>

<blockquote class="info"><p dir="rtl">
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
</p>

<p dir="rtl">
إذا كان ال composer مثبتًا من قبل، فتأكد من استخدام إصدار حديث. يمكنك تحديث ال composer عن طريق تنفيذ الأمر التالي <code>composer self-update</code>
</p>

<blockquote class="note"><p dir="rtl">
  ملاحظة مهمة: أثناء تثبيت ال Yii ، سيحتاج ال composer إلى طلب(request) الكثير من المعلومات من ال Github Api. يعتمد عدد الطلبات على عدد dependencies التي يمتلكها التطبيق الخاص بك، وقد يكون هذا العدد أكبر من الحد المسموح به من قبل ال Github Api <b>(Github API rate limit)</b>. إذا وصلت الى الحد الأعلى المسموح به من الطلبات، فقد يطلب منك ال composer بيانات تسجيل الدخول إلى Github، وذلك للحصول على رمز (token) للدخول إلى Github Api. اذا كانت عمليات الإتصال سريعة، فقد تصل إلى هذا الحد(limit) قبل أن يتمكن ال composer من التعامل معه ، لذالك نوصي بتكوين رمز الدخول(access token) قبل تثبيت ال Yii. يرجى الرجوع إلى التوثيق الخاص بال Composer والإطلاع على التعليمات الخاصة <a href="https://getcomposer.org/doc/articles/troubleshooting.md#api-rate-limit-and-oauth-tokens">Github API tokens</a> للحصول على الإرشادات اللازمة للقيام بذلك. 
</p></blockquote>

### <div dir="rtl">تثبيت ال Yii</div> <span id="installing-from-composer"></span>

<p dir="rtl">
    من خلال ال Composer، يمكنك الآن تثبيت ال Yii من خلال تنفيذ سطر الأوامر التالي داخل أي مسار يمكن الوصول اليه من قبل الويب
</p>

```bash
composer create-project --prefer-dist yiisoft/yii2-app-basic basic
```
<p dir="rtl">
      سطر الأوامر السابق سيقوم بتثبيت أحدث نسخة مستقرة(stable) من إطار العمل Yii داخل مسار جديد اسمه basic، ويمكنك التعديل على سطر الأوامر السابق لتغيير اسم المشروع لأي اسم ترغب فيه.  
</p>

<blockquote class="info"><p dir="rtl">
معلومة: اذا واجهتك أي مشكلة عند تنفيذ السطر `composer create-project` فيمكنك الذهاب إلى <a href="https://getcomposer.org/doc/articles/troubleshooting.md">قسم استكشاف الأخطاء في ال composer</a>.
في معظم الأخطاء الشائعة، وعند حل المشكلة أو الخطأ، يمكنك إكمال التثبيت من خلال الدخول الى المسار `basic` ومن ثم تنفيذ الأمر التالي: `composer update`.
</p></blockquote>

<blockquote class="tip"><p dir="rtl">
    تلميح: اذا كنت ترغب بتثبيت أحدث نسخة خاصة بالمطورين من ال Yii، فيمكنك ذلك من خلال إضافة الخيار <a href="https://getcomposer.org/doc/04-schema.md#minimum-stability">stability</a> وذلك من خلال سطر الأوامر التالي:
</p></blockquote>  

```bash
    composer create-project --prefer-dist --stability=dev yiisoft/yii2-app-basic basic
```
<blockquote class="note"><p dir="rtl">
    ملاحظة: نسخة المطورين من ال Yii يجب أن يتم إستخدامها للمواقع الإلكترونية التي لن تصدر كنسخة نهائية للمستخدم(Not for production) لأن ذلك يمكن أن يسبب بإيقاف المشروع أو الشيفرة البرمجية الخاصة بك. 
</p></blockquote>

### <div dir="rtl">تثبيت ال Yii من خلال ال Archive File</div> <span id="installing-from-archive-file"></span>
--------------------------

<p dir="rtl">
يتضمن تثبيت Yii من ملف أرشيف ثلاث خطوات وهي:
</p>
<ol dir="rtl">
<li> تثبت الملف من خلال الموقع الرسمي <a href="https://www.yiiframework.com/download/">yiiframework.com</a>.</li>
<li> قم بفك ضغط الملف الذي تم تنزيله إلى مجلد يمكن الوصول إليه عبر الويب.</li>
<li> قم بتعديل ملف <code>config/web.php</code> عن طريق إدخال secret key ل<code> cookieValidationKey</code>
(يتم ذلك تلقائيًا إذا قمت بتثبيت ال Yii باستخدام Composer): </li>
</ol>

   ```php
   // !!! insert a secret key in the following (if it is empty) - this is required by cookie validation
   'cookieValidationKey' => 'enter your secret key here',
   ```


### <div dir="rtl">خيارات تثبيت أخرى</div> <span id="other-installation-options"></span>
--------------------------

<p dir="rtl">
توضح تعليمات التثبيت أعلاه كيفية تثبيت ال Yii ، والذي يقوم أيضًا بإنشاء تطبيق ويب أساسي(basic).
هذا النهج هو نقطة انطلاق جيدة لمعظم المشاريع، صغيرة كانت أو كبيرة. خصوصا اذا كنت قد بدأت تعلم ال Yii من وقت قريب.
<br /><br />
لكن، هناك خيارات أخرى متاحة لتثبيت ال Yii وهي: 
</p>

<ul dir="rtl">
<li> إذا كنت ترغب فقط في تثبيت ال core لإطار العمل Yii، وترغب ببناء المكونات الخاصة بإطار العمل كما ترغب أنت وبطريقتك أنت، يمكنك اتباع التعليمات كما هو موضح في هذه الصفحة <a href="../guide/tutorial-start-from-scratch.md"> Building Application from Scratch</a>.</li>
<li> إذا كنت تريد البدء بتطبيق أكثر تعقيدًا وأكثر إحترافية، ويتناسب بشكل أفضل مع وجود فريق عمل تقني،
فأنت اذا سترغب بتثبيت ال <a href="https://github.com/yiisoft/yii2-app-advanced/blob/master/docs/guide/README.md">Advanced Project Template</a>
</li>
</ul>


### <div dir="rtl">تثبيت ال Assets</div> <span id="installing-assets"></span>
--------------------------

<p dir="rtl">
    تعتمد ال Yii على حزم <a href="https://bower.io/">Bower</a> و/أو <a href="https://www.npmjs.com/">NPM</a> لتثبيت مكتبات ال (CSS و JavaScript). ويستخدم ال composer للحصول على هذه المكتبات ، مما يسمح بالحصول على إصدارات ال PHP و CSS/JavaScript في نفس الوقت. ويمكن تحقيق ذلك إما عن طريق استخدام <a href="https://asset-packagist.org/">asset-packagist.org</a> أو من خلال ال <a href="https://github.com/fxpio/composer-asset-plugin">composer asset plugin</a>، يرجى الرجوع إلى <a href="../guide/structure-assets.md">Assets documentation</a> لمزيد من التفاصيل.
<br /><br />
قد ترغب في إدارة ال assets عبر  ال native Bower/NPM أو استخدام ال CDN أو تجنب تثبيت ال assets بالكامل من حلال ال Composer ، ويمكن ذلك من خلال إضافة الأسطر التالية إلى "composer.json":
</p>

```json
"replace": {
    "bower-asset/jquery": ">=1.11.0",
    "bower-asset/inputmask": ">=3.2.0",
    "bower-asset/punycode": ">=1.3.0",
    "bower-asset/yii2-pjax": ">=2.0.0"
},
```

<blockquote class="note"><p dir="rtl">
ملاحظة: في حالة تجاوز تثبيت ال assets عبر ال Composer، فأنت المسؤول عن تثبيت ال assets وحل مشكلات التعارض بين الإصدارات والمكتبات المختلفة. وكن مستعدًا لعدم تناسق محتمل بين ملفات ال asstes والإضافات المختلفة.
</p></blockquote>

### <div dir="rtl">التحقق من التثبيت</div> <span id="verifying-installation"></span>
--------------------------

<p dir="rtl">
    بعد الانتهاء من التثبيت، ستحتاج الى القيام بإعداد خادم الويب الخاص بك(your web server) (انظر القسم التالي) أو قم باستخدام <a href="https://www.php.net/manual/en/features.commandline.webserver.php">built-in PHP web server</a> عن طريق تنفيذ الأمر التالي داخل المسار web في المشروع الخاص بك: 
</p>
 
```bash
php yii serve
```

<blockquote class="note"><p dir="rtl">
ملاحظة: افتراضيًا ال HTTP-server يعمل على البورت 8080. ومع ذلك ، إذا كان هذا البورت قيد الاستخدام بالفعل أو كنت ترغب في تشغيل أكثر من تطبيق بهذه الطريقة، حينها سيلزمك تحديد البورت الذي يجب استخدامه. ما عليك سوى إضافة --port:
</p></blockquote>

```bash
php yii serve --port=8888
```
<p dir="rtl">
    يمكنك استخدام الرابط الموجود في الأسفل للوصول الى تطبيق ال Yii الذي قمت بتثبيته وتنفيذ الأوامر السابقة عليه.
</p>

```
http://localhost:8080/
```

![Successful Installation of Yii](../guide/images/start-app-installed.png)

<p dir="rtl">
    اذا كانت كل الإعدادات السابقة تعمل بشكل صحيح، فيجب أن ترى الصورة الموجودة بالأعلى "Congratulations!" على المتصفح. إذا لم يكن كذلك، يرجى التحقق مما إذا كان تثبيت الPHP الخاص بك متوافق مع متطلبات ال Yii. يمكنك التحقق من ذلك باستخدام أحد الأساليب التالية:
</p>

<ul dir="rtl">
    <li>قم بنسخ الملف <code>/requirements.php</code> الى المسار <code>/web/requirements.php</code> بحيث يمكنك الوصول الى الصفحة من خلال الرابط التالي: <code>http://localhost/requirements.php</code></li>
    <li>قم بتنفيذ الأوامر التالية: <br /><code>
        cd basic
        </code><br /><code>php requirements.php</code></li>
</ul>

<p dir="rtl">
    يجب عليك أن تقوم بتثبيت وإعداد ال PHP الخاص بك بحيث تلبي الحد الأدنى من متطلبات ال Yii. الأهم من ذلك يجب أن يكون الإصدار الخاص بال PHP أعلى أو يساوي 5.4. من الناحية المثالية أحدث إصدار  يعمل مع ال Yii هو ال PHP 7. يجب عليك أيضًا تثبيت ال <a href="https://www.php.net/manual/en/pdo.installation.php">PDO PHP Extension</a>.
</p>


### <div dir="rtl">إعداد ال Web Servers</div> <span id="configuring-web-servers"></span>
-----------------------

<blockquote class="info"><p dir="rtl">
معلومة: يمكنك تخطي هذا الجزء الآن إذا كنت تختبر فقط إطار العمل Yii دون أي نية لنشر هذا التطبيق على الويب(بدون رفع التطبيق على production server).
</p></blockquote>

<p dir="rtl">
    يجب أن يعمل التطبيق الذي تم تثبيته وفقًا للتعليمات المذكورة أعلاه مع أي من الخوادم ال <a href="https://httpd.apache.org/">Apache HTTP</a> أو ال <a href="https://nginx.org/">Nginx HTTP</a> في كل من أنظمة التشغيل Windows, Mac OS X أو Linux ممن لديها إصدار أعلى أو يساوي PHP 5.4، كما أن ال Yii 2.0 متوافق مع ال Facebook <a href="https://hhvm.com/">HHVM</a>، لكن، يجب أن تأخذ بعين الإعتبار أن ال HHVM يسلك في بعض الأحيان بطريقة مختلفة عن ال Native PHP، لذلك يجب أن تأخذ عناية إضافية عندما تعمل على ال HHVM.
</p>

<p dir="rtl">
    على ال production server، قد ترغب في إعداد خادم الويب الخاص بك بحيث يمكن الوصول إلى التطبيق
الخاص بك عبر ال URL التالي <code>https://www.example.com/index.php</code> بدلاً من <code>https://www.example.com/basic/web/index.php</code>. هذا الكلام يتطلب إنشاء إعداد يقوم بتوجيه ال document root الموجود على ال web server الى مجلد ال basic/web، كما قد ترغب أيضا بإخفاء ال <code>index.php</code> من ال URL كما هو موضح في ال <a href="../guide/runtime-routing.md">Routing and URL Creation</a>. في هذا الموضوع ستتعلم كيف يمكنك إعداد ال Apache أو ال Nginx server لتحقيق هذه الأهداف. 
</p>

<blockquote class="info"><p dir="rtl">
    معلومة: من خلال تعيين ال <code>basic/web</code> ك <code>document root</code>، فإنك بذلك تمنع أيضًا المستخدمين النهائيين من الوصول الى الشيفرة البرمجية الخاصة بالتطبيق الخاص بك، وتمنعهم من الوصول الى الملفات الحساسة والمهمة والمخزنة في sibling directories من <code>basic/web</code>، ويعبر رفض الوصول الى المجلدات الأخرى تحسينا أمنيا مهما، يساعد في الحفاظ على مستوى أعلى من الحماية.
</p></blockquote>

<blockquote class="info"><p dir="rtl">
معلومة: إذا كان سيتم تشغيل التطبيق الخاص بك في بيئة استضافة مشتركة(shared hosting) حيث ليس لديك الصلاحية لتعديل الإعدادات الخاصة بال web server، ستحتاج حينها الى تعديل في البنية الخاصة بالمشروع للحصول على أفضل أمان ممكن. يرجى الرجوع إلى <a href="../guide/tutorial-shared-hosting.md">Shared Hosting Environment</a> لمزيد من المعلومات. 
</p></blockquote>

<blockquote class="info"><p dir="rtl">
    معلومة: إذا كنت تقوم بتشغيل تطبيق ال Yii بوجود ال proxy، فقد تحتاج إلى إعداد التطبيق ليكون ضمن ال <a href="../guide/runtime-requests.md#trusted-proxies">trusted proxies and header</a>.
</p></blockquote>

### <div dir="rtl">الإعدادات الموصى بها لل Apache</div> <span id="recommended-apache-configuration"></span>
-----------------------

<p dir="rtl">
    استخدم الإعدادات التالية في ملف ال <code>httpd.conf</code> في Apache أو ضمن إعدادات ال virtual host.
    ملاحظة:  يجب عليك استبدال المسار التالي <code>path/to/basic/web</code> بالمسار الفعلي للتطبيق الخاص بك وصولا الى ال <code>basic/web</code>.
</p>

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


### <div dir="rtl">الإعدادات الموصى بها لل Nginx</div> <span id="recommended-nginx-configuration"></span>
-----------------------

<p dir="rtl">
    لاستخدام <a href="https://wiki.nginx.org/">Nginx</a>، يجب تثبيت PHP على أنه <a href="https://www.php.net/install.fpm">FPM SAPI</a>، ويمكنك استخدام إعدادات ال Nginx التالية، مع الإنتباه على استبدال المسار من  <code>path/to/basic/web</code> الى المسار الفعلي  وصولا إلى <code>basic/web</code> بالإضافة الى إستبدال <code>mysite.test</code> إلى ال hostname الخاص بالتطبيق.
</p>


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
<p dir="rtl">
    عند استخدامك لهذا الإعداد، يجب عليك أيضًا تعيين <code>cgi.fix_pathinfo = 0</code> في ملف php.ini
    من أجل تجنب العديد من طلبات ال <code>stat()</code>  الغير الضرورية للنظام.
</p>

<p dir="rtl">
    لاحظ أيضًا أنه عند تشغيل خادم HTTPS، تحتاج إلى إضافة  <code>fastcgi_param HTTPS on;</code>
بحيث يمكنك إكتشاف إذا ما كان الاتصال آمنًا أم لا.
</p>
