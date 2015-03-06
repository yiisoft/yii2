Ресурсы (Черновой вариант)
======

<!--
An asset in Yii is a file that may be referenced in a Web page. It can be a CSS file, a JavaScript file, an image
or video file, etc. Assets are located in Web-accessible directories and are directly served by Web servers.
-->
Ресурс в Yii это файл который может быть задан в Web странице. Это может быть CSS файл, JavaScript файл, изображение или видео файл и т.д. Ресурсы располагаются в Web доступных директориях и обслуживаются непосредственно Web серверами.

<!--
It is often preferable to manage assets programmatically. For example, when you use the [[yii\jui\DatePicker]] widget
in a page, it will automatically include the required CSS and JavaScript files, instead of asking you to manually
find these files and include them. And when you upgrade the widget to a new version, it will automatically use
the new version of the asset files. In this tutorial, we will describe the powerful asset management capability
provided in Yii.
-->
Желательно, управлять ресурсами программно. Например, при использовании виджета [[yii\jui\DatePicker]] в странице, автоматически включаются необходимые CSS и JavaScript файлы, вместо того чтобы просить Вас в ручную найти эти файлы и включить их. И когда Вы обновляете виджет до новой версии, будут автоматически использованны новые версии файлов-ресурсов. В этом руководстве будет описана мощная возможность управления ресурсами представленная в Yii.


## Комплекты ресурсов <span id="asset-bundles"></span>
<!--Asset Bundles -->
<!--
Yii manages assets in the unit of *asset bundle*. An asset bundle is simply a collection of assets located
in a directory. When you register an asset bundle in a [view](structure-views.md), it will include the CSS and
JavaScript files in the bundle in the rendered Web page.
-->
Yii управляет ресурсами как единицей *комплекта ресурсов*. Комплект ресурсов - это простой набор ресурсов расположенных в директории. Когда Вы регистрируете комплект ресурсов в [представлении](structure-views.md), в отображаемой Web странице включается набор CSS и JavaScript файлов.

## Задание Комплекта Ресурсов<span id="defining-asset-bundles"></span>
<!-- Defining Asset Bundles -->
<!--
Asset bundles are specified as PHP classes extending from [[yii\web\AssetBundle]]. The name of a bundle is simply
its corresponding fully qualified PHP class name (without the leading backslash). An asset bundle class should
be [autoloadable](concept-autoloading.md). It usually specifies where the assets are located, what CSS and 
JavaScript files the bundle contains, and how the bundle depends on other bundles.
-->
Комплект ресурсов определяется как PHP класс расширяющийся от [[yii\web\AssetBundle]]. Имя комплекта соответствует полному имени PHP класса (без ведущей обратной косой черты - backslash "\"). Класс комплекта ресурсов должен быть в состоянии [возможности автозагрузки](concept-autoloading.md). При задании комплекта ресурсов обычно указывается где ресурсы находятся, какие CSS и JavaScript файлы содержит комплект, и как комплект зависит от других комплектов.
<!--
The following code defines the main asset bundle used by [the basic application template](start-installation.md):
-->
Следующий код задаёт основной комплект ресурсов используемый в [шаблоне базового приложения](start-installation.md):

```php
<?php

namespace app\assets;

use yii\web\AssetBundle;

class AppAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $css = [
        'css/site.css',
    ];
    public $js = [
    ];
    public $depends = [
        'yii\web\YiiAsset',
        'yii\bootstrap\BootstrapAsset',
    ];
}
```
<!--
The above `AppAsset` class specifies that the asset files are located under the `@webroot` directory which
corresponds to the URL `@web`; the bundle contains a single CSS file `css/site.css` and no JavaScript file;
the bundle depends on two other bundles: [[yii\web\YiiAsset]] and [[yii\bootstrap\BootstrapAsset]]. More detailed
explanation about the properties of [[yii\web\AssetBundle]] can be found in the following:
-->
В коде выше класс `AppAsset` указывает, что файлы ресурса находятся в директории `@webroot`, которой соответствует URL `@web`; комплект содержит единственный CSS файл `css/site.css` и не содержит JavaScript файлов; комплект зависит от двух других комплектов: [[yii\web\YiiAsset]] и [[yii\bootstrap\BootstrapAsset]]. Более детальное объяснение о свойствах [[yii\web\AssetBundle]] может быть найдено ниже:
<!--
* [[yii\web\AssetBundle::sourcePath|sourcePath]]: specifies the root directory that contains the asset files in
  this bundle. This property should be set if the root directory is not Web accessible. Otherwise, you should
  set the [[yii\web\AssetBundle::basePath|basePath]] property and [[yii\web\AssetBundle::baseUrl|baseUrl]], instead.
  [Path aliases](concept-aliases.md) can be used here.
-->
* [[yii\web\AssetBundle::sourcePath|sourcePath]]: задаёт корневую директорию содержащую файлы ресурса в этом комплекте. Это свойство должно быть установлено если корневая директория не доступна из Web. В противном случае, Вы должны установить [[yii\web\AssetBundle::basePath|basePath]] свойство и [[yii\web\AssetBundle::baseUrl|baseUrl]] свойство вместо текущего. Здесь могут быть использованы [псевдонимы путей](concept-aliases.md).

<!--
* [[yii\web\AssetBundle::basePath|basePath]]: specifies a Web-accessible directory that contains the asset files in
  this bundle. When you specify the [[yii\web\AssetBundle::sourcePath|sourcePath]] property,
  the [asset manager](#asset-manager) will publish the assets in this bundle to a Web-accessible directory
  and overwrite this property accordingly. You should set this property if your asset files are already in
  a Web-accessible directory and do not need asset publishing. [Path aliases](concept-aliases.md) can be used here.
-->
* [[yii\web\AssetBundle::basePath|basePath]]: задаёт Web доступную директорию, которая содержит файлы ресурсов текущего комплекта. Когда Вы задаёте свойство [[yii\web\AssetBundle::sourcePath|sourcePath]] [Менеджер ресурсов](#asset-manager) опубликует ресурсы текущего комплекта в Web доступную директорию и перезапишет соответственно данное свойство. Вы должны задать данное свойство если Ваши файлы ресурсов уже в Web доступной директории и не нужно опубликовывать ресурсы. Здесь могут быть использованы [псевдонимы путей](concept-aliases.md).

<!--
* [[yii\web\AssetBundle::baseUrl|baseUrl]]: specifies the URL corresponding to the directory
  [[yii\web\AssetBundle::basePath|basePath]]. Like [[yii\web\AssetBundle::basePath|basePath]],
  if you specify the [[yii\web\AssetBundle::sourcePath|sourcePath]] property, the [asset manager](#asset-manager)
  will publish the assets and overwrite this property accordingly. [Path aliases](concept-aliases.md) can be used here.
-->
* [[yii\web\AssetBundle::baseUrl|baseUrl]]: задаёт URL соответствующий директории [[yii\web\AssetBundle::basePath|basePath]]. Также как и для [[yii\web\AssetBundle::basePath|basePath]], если Вы задаёте свойство [[yii\web\AssetBundle::sourcePath|sourcePath]] [Менеджер ресурсов](#asset-manager) опубликует ресурсы и перезапишет это свойство соответственно. Здесь могут быть использованы [псевдонимы путей](concept-aliases.md).

<!--
* [[yii\web\AssetBundle::js|js]]: an array listing the JavaScript files contained in this bundle. Note that only
  forward slash "/" should be used as directory separators. Each JavaScript file can be specified in one of the
  following two formats:
  - a relative path representing a local JavaScript file (e.g. `js/main.js`). The actual path of the file
    can be determined by prepending [[yii\web\AssetManager::basePath]] to the relative path, and the actual URL
    of the file can be determined by prepending [[yii\web\AssetManager::baseUrl]] to the relative path.
  - an absolute URL representing an external JavaScript file. For example,
    `http://ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js` or
    `//ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js`.
-->
* [[yii\web\AssetBundle::js|js]]: массив, перечисляющий JavaScript файлы, содержащиеся в данном комплекте. Заметьте, что только прямая косая черта (forward slash - "/") может быть использована, как разделитель директорий. Каждый JavaScript файл может быть задан в одном из следующих форматов:
- относительный путь, представленный локальным JavaScript файлом (например `js/main.js`). Актуальный путь файла может быть определён путём добавления [[yii\web\AssetManager::basePath]] к относительному пути, и актуальный URL файла может быть определён путём добавления [[yii\web\AssetManager::baseUrl]] к относительному пути.
- абсолютный URL, представленный внешним JavaScript файлом. Например,
    `http://ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js` или
    `//ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js`.

<!--
* [[yii\web\AssetBundle::css|css]]: an array listing the CSS files contained in this bundle. The format of this array
  is the same as that of [[yii\web\AssetBundle::js|js]].
-->
* [[yii\web\AssetBundle::css|css]]: массив, перечисляющий CSS файлы, содержащиеся в данном комплекте. Формат этого массива такой же, как и у [[yii\web\AssetBundle::js|js]].

<!--
* [[yii\web\AssetBundle::depends|depends]]: an array listing the names of the asset bundles that this bundle depends on
  (to be explained shortly).
-->
* [[yii\web\AssetBundle::depends|depends]]: массив, перечисляющий имена комплектов ресурсов, от которых зависит данный комплект.

<!--
* [[yii\web\AssetBundle::jsOptions|jsOptions]]: specifies the options that will be passed to the
  [[yii\web\View::registerJsFile()]] method when it is called to register *every* JavaScript file in this bundle.
-->
* [[yii\web\AssetBundle::jsOptions|jsOptions]]: задаёт параметры, которые будут относится к методу [[yii\web\View::registerJsFile()]], когда он вызывается для регистрации *каждого* JavaScript файла данного комплекта.

<!--
* [[yii\web\AssetBundle::cssOptions|cssOptions]]: specifies the options that will be passed to the
  [[yii\web\View::registerCssFile()]] method when it is called to register *every* CSS file in this bundle.
-->
* [[yii\web\AssetBundle::cssOptions|cssOptions]]: задаёт параметры, которые будут приняты методом [[yii\web\View::registerCssFile()]], когда он вызывается для регистрации *каждого* CSS файла данного комплекта.

<!--
* [[yii\web\AssetBundle::publishOptions|publishOptions]]: specifies the options that will be passed to the
  [[yii\web\AssetManager::publish()]] method when it is called to publish source asset files to a Web directory.
  This is only used if you specify the [[yii\web\AssetBundle::sourcePath|sourcePath]] property.
-->
* [[yii\web\AssetBundle::publishOptions|publishOptions]]: задаёт параметры, которые будут приняты методом [[yii\web\AssetManager::publish()]], когда метод будет вызван, опубликуются исходные файлы ресурсов в Web директории. Этот параметр используется только в том случае, если задаётся свойство [[yii\web\AssetBundle::sourcePath|sourcePath]].


### Расположение ресурсов<span id="asset-locations"></span>

<!-- Asset Locations  -->
<!--
Assets, based on their location, can be classified as:
-->
Ресурсы, в зависимости от их расположения, могут быть классифицированы как:

<!--
* source assets: the asset files are located together with PHP source code which cannot be directly accessed via Web.
  In order to use source assets in a page, they should be copied to a Web directory and turned into the so-called
  published assets. This process is called *asset publishing* which will be described in detail shortly.
-->
* исходные ресурсы: файлы ресурсов, расположенные вместе с исходным кодом PHP, которые не могут быть непосредственно доступны через Web. Для того, чтобы использовать исходные ресурсы на странице, они должны быть скопированы в Web директорию и превратиться в так называемые опубликованные ресурсы. Этот процесс называется *публикацией ресурсов*, который более подробно будет описан в ближайшее время.

<!--
* published assets: the asset files are located in a Web directory and can thus be directly accessed via Web.
-->
* опубликованные ресурсы: файлы ресурсов, расположенные в Web директории и, таким образом, могут быть напрямую доступны через Web.

<!--
* external assets: the asset files are located on a Web server that is different from the one hosting your Web
  application.
-->
* внешние ресурсы: файлы ресурсов, расположенные на другом Web сервере, отличного от веб-хостинга вашего приложения.

<!--
When defining an asset bundle class, if you specify the [[yii\web\AssetBundle::sourcePath|sourcePath]] property,
it means any assets listed using relative paths will be considered as source assets. If you do not specify this property,
it means those assets are published assets (you should therefore specify [[yii\web\AssetBundle::basePath|basePath]] and
[[yii\web\AssetBundle::baseUrl|baseUrl]] to let Yii know where they are located).
-->
При определении класса комплекта ресурсов, если Вы задаёте свойство [[yii\web\AssetBundle::sourcePath|sourcePath]], это означает, что любые перечисленные ресурсы, используя относительные пути, будут рассматриваться как исходные ресурсы. Если Вы не задаёте данное свойство, это означает, что эти ресурсы - это опубликованные ресурсы (в этом случае Вам следует указать [[yii\web\AssetBundle::basePath|basePath]] и [[yii\web\AssetBundle::baseUrl|baseUrl]], чтобы дать знать Yii где ресурсы располагаются).

<!--
It is recommended that you place assets belonging to an application in a Web directory to avoid the unnecessary asset
publishing process. This is why `AppAsset` in the prior example specifies [[yii\web\AssetBundle::basePath|basePath]]
instead of [[yii\web\AssetBundle::sourcePath|sourcePath]].
-->
Рекомендуется размещать ресурсы, принадлежащие приложению, в Web директорию, для того, чтобы избежать не нужного процесса публикации ресурсов. Вот почему `AppAsset` в предыдущем примере задаёт [[yii\web\AssetBundle::basePath|basePath]] вместо [[yii\web\AssetBundle::sourcePath|sourcePath]].

<!--
For [extensions](structure-extensions.md), because their assets are located together with their source code
in directories that are not Web accessible, you have to specify the [[yii\web\AssetBundle::sourcePath|sourcePath]]
property when defining asset bundle classes for them.
-->
Для [расширений](structure-extensions.md), в связи с тем, что их ресурсы располагаются вместе с их исходным кодом в директориях, которые не являются веб-доступными, необходимо указать свойство [[yii\web\AssetBundle::sourcePath|sourcePath]] при задании класса комплекта ресурсов для них.

<!--
> Note: Do not use `@webroot/assets` as the [[yii\web\AssetBundle::sourcePath|source path]].
  This directory is used by default by the [[yii\web\AssetManager|asset manager]] to save the asset files
  published from their source location. Any content in this directory is considered temporarily and may be subject
  to removal.
-->
> Примечание: Не используйте `@webroot/assets` как [[yii\web\AssetBundle::sourcePath|source path]]. Эта директория по умолчанию используется менеджером ресурсов [[yii\web\AssetManager|asset manager]] для сохранения файлов ресурсов, опубликованных из их исходного месторасположения. Любое содержимое этой директории расценивается как временное и может быть удалено.
  

### Зависимости ресурсов <span id="asset-dependencies"></span>
<!-- Asset Dependencies  -->
<!--
When you include multiple CSS or JavaScript files in a Web page, they have to follow a certain order to avoid
overriding issues. For example, if you are using a jQuery UI widget in a Web page, you have to make sure
the jQuery JavaScript file is included before the jQuery UI JavaScript file. We call such ordering the dependencies
among assets.
-->

Когда Вы включаете несколько CSS или JavaScript файлов в Web страницу, они должны следовать в определенном порядке, <b> чтобы избежать переопределения при выдаче</b>. Например, если Вы используете виджет jQuery UI в Web странице, вы должны убедиться, что jQuery JavaScript файл был включен до jQuery UI JavaScript файла. Мы называем такой порядок зависимостью между ресурсами.
<!--
Asset dependencies are mainly specified through the [[yii\web\AssetBundle::depends]] property.
In the `AppAsset` example, the asset bundle depends on two other asset bundles: [[yii\web\YiiAsset]] and
[[yii\bootstrap\BootstrapAsset]], which means the CSS and JavaScript files in `AppAsset` will be included *after*
those files in the two dependent bundles.
-->
Зависимости ресурсов в основном указываются через свойство [[yii\web\AssetBundle::depends]]. Например в `AppAsset`, комплект ресурсов зависит от двух других комплектов ресурсов: [[yii\web\YiiAsset]] и [[yii\bootstrap\BootstrapAsset]], что обозначает, что CSS и JavaScript файлы `AppAsset` будут включены *после* файлов этих двух комплектов зависимостей.

<!--
Asset dependencies are transitive. This means if bundle A depends on B which depends on C, A will depend on C, too.
-->
Зависимости ресурсов являются также зависимыми. Это значит, что если комплект А зависит от В, который зависит от С, то А тоже зависит от С.

### Параметры ресурсов <span id="asset-options"></span>
<!-- Asset Options  -->

<!--
You can specify the [[yii\web\AssetBundle::cssOptions|cssOptions]] and [[yii\web\AssetBundle::jsOptions|jsOptions]]
properties to customize the way that CSS and JavaScript files are included in a page. The values of these properties
will be passed to the [[yii\web\View::registerCssFile()]] and [[yii\web\View::registerJsFile()]] methods, respectively, when
they are called by the [view](structure-views.md) to include CSS and JavaScript files.
-->
Вы можете задать свойства [[yii\web\AssetBundle::cssOptions|cssOptions]] и [[yii\web\AssetBundle::jsOptions|jsOptions]], чтобы настроить путь для включения CSS и JavaScript файлов в страницу. Значения этих свойств будут приняты методами [[yii\web\View::registerCssFile()]] и [[yii\web\View::registerJsFile()]] соответственно, когда они (методы) вызываются [представлением](structure-views.md) происходит включение CSS и JavaScript файлов.

<!--
> Note: The options you set in a bundle class apply to *every* CSS/JavaScript file in the bundle. If you want to
  use different options for different files, you should create separate asset bundles, and use one set of options
  in each bundle.
-->
> Примечание: Параметры, заданные в комплекте класса применяются для *каждого* CSS/JavaScript-файла в комплекте. Если Вы хотите использовать различные параметры для разных файлов, Вы должны создать раздельные комплекты ресурсов, и использовать одну установку параметров для каждого комплекта.

<!--
For example, to conditionally include a CSS file for browsers that are IE9 or below, you can use the following option:
-->
Например, условно включим CSS файл для браузера IE9 или ниже. Для этого Вы можете использовать следующий параметр:

```php
public $cssOptions = ['condition' => 'lte IE9'];
```
<!--
This will cause a CSS file in the bundle to be included using the following HTML tags:
-->
Это вызовет CSS файл из комплекта, который будет включен в страницу, используя следующие HTML теги:

```html
<!--[if lte IE9]>
<link rel="stylesheet" href="path/to/foo.css">
<![endif]-->
```

<!--
To wrap the generated CSS link tags within `<noscript>`, you can configure `cssOptions` as follows,
-->
Для того чтобы обернуть созданную CSS ссылку в тег `<noscript>`, Вы можете настроить `cssOptions` следующим образом:

```php
public $cssOptions = ['noscript' => true];
```

<!--
To include a JavaScript file in the head section of a page (by default, JavaScript files are included at the end
of the body section), use the following option:
-->
Для включения JavaScript файла в head раздел страницы (по умолчанию, JavaScript файлы включаются в конец раздела body) используйте следующий параметр:

```php
public $jsOptions = ['position' => \yii\web\View::POS_HEAD];
```

<!--
By default, when an asset bundle is being published, all contents in the directory specified by [[yii\web\AssetBundle::sourcePath]]
will be published. You can customize this behavior by configuring the [[yii\web\AssetBundle::publishOptions|publishOptions]] 
property. For example, to publish only one or a few subdirectories of [[yii\web\AssetBundle::sourcePath]], 
you can do the following in the asset bundle class:
-->
По умолчанию, когда комплект ресурсов публикуется, всё содержимое в заданной директории [[yii\web\AssetBundle::sourcePath]] будет опубликовано. Вы можете настроить это поведение, сконфигурировав свойство [[yii\web\AssetBundle::publishOptions|publishOptions]]. Например, опубликовать одну или несколько поддиректорий [[yii\web\AssetBundle::sourcePath]] в классе комплекта ресурсов Вы можете в следующим образом:

```php
<?php
namespace app\assets;

use yii\web\AssetBundle;

class FontAwesomeAsset extends AssetBundle 
{
    public $sourcePath = '@bower/font-awesome'; 
    public $css = [ 
        'css/font-awesome.min.css', 
    ]; 
    
    public function init()
    {
        parent::init();
        $this->publishOptions['beforeCopy'] = function ($from, $to) {
            $dirname = basename(dirname($from));
            return $dirname === 'fonts' || $dirname === 'css';
        };
    }
}  
```

<!--
The above example defines an asset bundle for the ["fontawesome" package](http://fontawesome.io/). By specifying 
the `beforeCopy` publishing option, only the `fonts` and `css` subdirectories will be published.
-->
В выше указанном примере определён комплект ресурсов для [пакета "fontawesome"](http://fontawesome.io/). Задан параметр публикации `beforeCopy`, здесь только `fonts` и `css` поддиректории будут опубликованы.


### Bower и NPM Ресурсы<span id="bower-npm-assets"></span>
<!-- Bower and NPM Assets -->

<!--
Most JavaScript/CSS packages are managed by [Bower](http://bower.io/) and/or [NPM](https://www.npmjs.org/).
If your application or extension is using such a package, it is recommended that you follow these steps to manage
the assets in the library:
-->
Большинство JavaScript/CSS пакетов управляются [Bower](http://bower.io/) и/или [NPM](https://www.npmjs.org/).
Если Вашим приложением или расширением используется такой пакет, то рекомендуется следовать следующим этапам для управления ресурсами библиотеки:

<!--
1. Modify the `composer.json` file of your application or extension and list the package in the `require` entry.
   You should use `bower-asset/PackageName` (for Bower packages) or `npm-asset/PackageName` (for NPM packages)
   to refer to the library.
-->
1. Исправить файл `composer.json` Вашего приложения или расширения и включить пакет в список в раздел `require`. Следует использовать `bower-asset/PackageName` (для Bower пакетов) или `npm-asset/PackageName` (для NPM пакетов) для обращения к соответствующей библиотеке.

<!--
2. Create an asset bundle class and list the JavaScript/CSS files that you plan to use in your application or extension.
   You should specify the [[yii\web\AssetBundle::sourcePath|sourcePath]] property as `@bower/PackageName` or `@npm/PackageName`.
-->
2. Создать класс комплекта ресурсов и перечислить JavaScript/CSS файлы, которые Вы планируете использовать в Вашем приложении или расширении. Вы должны задать свойство [[yii\web\AssetBundle::sourcePath|sourcePath]] как `@bower/PackageName` или `@npm/PackageName`.

<!--
   This is because Composer will install the Bower or NPM package in the directory corresponding to this alias.
-->
   Это происходит потому, что Composer устанавливает Bower или NPM пакет в директорию, соответствующую этим псевдонимам.
 
<!--
> Note: Some packages may put all their distributed files in a subdirectory. If this is the case, you should specify
  the subdirectory as the value of [[yii\web\AssetBundle::sourcePath|sourcePath]]. For example, [[yii\web\JqueryAsset]]
  uses `@bower/jquery/dist` instead of `@bower/jquery`.
-->
> Примечание: В некоторых пакетах файлы дистрибутива могут находиться в поддиректории. В этом случае, Вы должны задать поддиреторию как значение [[yii\web\AssetBundle::sourcePath|sourcePath]]. Например, [[yii\web\JqueryAsset]] использует `@bower/jquery/dist` вместо `@bower/jquery`.


## Использование Комплекта Ресурсов<span id="using-asset-bundles"></span>
<!-- Using Asset Bundles -->

<!--
To use an asset bundle, register it with a [view](structure-views.md) by calling the [[yii\web\AssetBundle::register()]]
method. For example, in a view template you can register an asset bundle like the following:
-->
Для использования комплекта ресурсов, зарегистрируйте его в [представлении](structure-views.md) вызвав метод [[yii\web\AssetBundle::register()]]. Например, комплект ресурсов в представлении может быть зарегистрирован следующим образом:

```php
use app\assets\AppAsset;
AppAsset::register($this);  // $this - представляет собой объект представления
```

<!--
> Info: The [[yii\web\AssetBundle::register()]] method returns an asset bundle object containing the information
  about the published assets, such as [[yii\web\AssetBundle::basePath|basePath]] or [[yii\web\AssetBundle::baseUrl|baseUrl]].
-->
> Для справки: Метод [[yii\web\AssetBundle::register()]] возвращает объект комплекта ресурсов, содержащий информацию о публикуемых ресурсах, таких как [[yii\web\AssetBundle::basePath|basePath]] или [[yii\web\AssetBundle::baseUrl|baseUrl]].

<!--
If you are registering an asset bundle in other places, you should provide the needed view object. For example,
to register an asset bundle in a [widget](structure-widgets.md) class, you can get the view object by `$this->view`.
-->
Если Вы регистрируете комплект ресурсов в других местах (т.е. не в представлении), Вы должны обеспечить необходимый объект представления. Например, при регистрации комплекта ресурсов в классе [widget](structure-widgets.md), Вы можете взять за объект представления `$this->view`.

<!--
When an asset bundle is registered with a view, behind the scenes Yii will register all its dependent asset bundles.
And if an asset bundle is located in a directory inaccessible through the Web, it will be published to a Web directory.
Later, when the view renders a page, it will generate `<link>` and `<script>` tags for the CSS and JavaScript files
listed in the registered bundles. The order of these tags is determined by the dependencies among
the registered bundles and the order of the assets listed in the [[yii\web\AssetBundle::css]] and [[yii\web\AssetBundle::js]]
properties.
-->
Когда комплект ресурсов регистрируется в представлении, Yii регистрирует все зависимые от него комплекты ресурсов. И, если комплект ресурсов расположен в директории не доступной из Web, то он будет опубликован в Web директории. Затем, когда представление отображает страницу, сгенерируются теги `<link>` и `<script>` для CSS и JavaScript файлов, перечисленных в регистрируемых комплектах. Порядок этих тегов определён зависимостью среди регистрируемых комплектов, и последовательность ресурсов перечислена в [[yii\web\AssetBundle::css]] и [[yii\web\AssetBundle::js]] свойствах.

### Настройка Комплектов Ресурсов <span id="customizing-asset-bundles"></span>
<!-- Customizing Asset Bundles -->

<!--
Yii manages asset bundles through an application component named `assetManager` which is implemented by [[yii\web\AssetManager]].
By configuring the [[yii\web\AssetManager::bundles]] property, it is possible to customize the behavior of an asset bundle.
For example, the default [[yii\web\JqueryAsset]] asset bundle uses the `jquery.js` file from the installed
jquery Bower package. To improve the availability and performance, you may want to use a version hosted by Google.
This can be achieved by configuring `assetManager` in the application configuration like the following:
-->
Yii управляет комплектами ресурсов через компонент приложения называемый `assetManager`, который реализован в [[yii\web\AssetManager]]. Путём настройки свойства [[yii\web\AssetManager::bundles]], возможно настроить поведение комплекта ресурсов. Например, комплект ресурсов [[yii\web\JqueryAsset]] по умолчанию использует `jquery.js` файл из установленного jquery Bower пакета. Для повышения доступности и производительности, можно использовать версию jquery на Google хостинге.
Это может быть достигнуто, настроив `assetManager` в конфигурации приложения следующим образом:

```php
return [
    // ...
    'components' => [
        'assetManager' => [
            'bundles' => [
                'yii\web\JqueryAsset' => [
                    'sourcePath' => null,   // не опубликовывать комплект
                    'js' => [
                        '//ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js',
                    ]
                ],
            ],
        ],
    ],
];
```

<!--
You can configure multiple asset bundles similarly through [[yii\web\AssetManager::bundles]]. The array keys
should be the class names (without the leading backslash) of the asset bundles, and the array values should
be the corresponding [configuration arrays](concept-configurations.md).
-->
Можно сконфигурировать несколько комплектов ресурсов аналогично через [[yii\web\AssetManager::bundles]]. Ключи массива должны быть именами класса (без впереди стоящей обратной косой черты) комплектов ресурсов, а значения массивов должны соответствовать [конфигурации массивов](concept-configurations.md).

<!--
> Tip: You can conditionally choose which assets to use in an asset bundle. The following example shows how
> to use `jquery.js` in the development environment and `jquery.min.js` otherwise:
-->
> Совет: Можно условно выбрать, какой из ресурсов будет использован в комплекте ресурсов. Следующий пример показывает, как можно использовать в разработке окружения `jquery.js` или `jquery.min.js` в противном случае:

> ```php
> 'yii\web\JqueryAsset' => [
>     'js' => [
>         YII_ENV_DEV ? 'jquery.js' : 'jquery.min.js'
>     ]
> ],
> ```

<!--
You can disable one or multiple asset bundles by associating `false` with the names of the asset bundles
that you want to disable. When you register a disabled asset bundle with a view, none of its dependent bundles
will be registered, and the view also will not include any of the assets in the bundle in the page it renders.
For example, to disable [[yii\web\JqueryAsset]], you can use the following configuration:
-->
Можно запретить один или несколько комплектов ресурсов, связав `false` с именами комплектов ресурсов, которые Вы хотите сделать недоступными. Когда Вы регистрируете недоступный комплект ресурсов в представлении, обратите внимание, что зависимость комплектов будет зарегистрирована, и представление также не включит ни один из ресурсов комплекта в отображаемую страницу. Например, для запрета [[yii\web\JqueryAsset]] можно использовать следующую конфигурацию: 


```php
return [
    // ...
    'components' => [
        'assetManager' => [
            'bundles' => [
                'yii\web\JqueryAsset' => false,
            ],
        ],
    ],
];
```

<!--
You can also disable *all* asset bundles by setting [[yii\web\AssetManager::bundles]] as `false`.
-->
Можно также запретить *все* комплекты ресурсов, установив [[yii\web\AssetManager::bundles]] как `false`.


### Привязка ресурсов<span id="asset-mapping"></span>
<!-- Asset Mapping -->
<!--
Sometimes you may want to "fix" incorrect/incompatible asset file paths used in multiple asset bundles. For example,
bundle A uses `jquery.min.js` version 1.11.1, and bundle B uses `jquery.js` version 2.1.1. While you can
fix the problem by customizing each bundle, an easier way is to use the *asset map* feature to map incorrect assets
to the desired ones. To do so, configure the [[yii\web\AssetManager::assetMap]] property like the following:
-->
Иногда необходимо исправить пути до файлов ресурсов, в нескольких комплектах ресурсов. Например, комплект А использует `jquery.min.js` версии 1.11.1, а комплект В использует `jquery.js` версии 2.1.1. Раньше Вы могли решить данную проблему, настраивая каждый комплект ресурсов по отдельности, но более простой способ - использовать *asset map* возможность, чтобы найти неверные ресурсы и исправить их. Сделать это можно, сконфигурировав свойство [[yii\web\AssetManager::assetMap]] следующим образом:

```php
return [
    // ...
    'components' => [
        'assetManager' => [
            'assetMap' => [
                'jquery.js' => '//ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js',
            ],
        ],
    ],
];
```
<!--
The keys of [[yii\web\AssetManager::assetMap|assetMap]] are the asset names that you want to fix, and the values
are the desired asset paths. When you register an asset bundle with a view, each relative asset file in its
[[yii\web\AssetBundle::css|css]] and [[yii\web\AssetBundle::js|js]] arrays will be examined against this map.
If any of the keys are found to be the last part of an asset file (which is prefixed with [[yii\web\AssetBundle::sourcePath]]
if available), the corresponding value will replace the asset and be registered with the view.
For example, the asset file `my/path/to/jquery.js` matches the key `jquery.js`.
-->
Ключи [[yii\web\AssetManager::assetMap|assetMap]] - это имена ресурсов, которые Вы хотите исправить, а значения - это требуемые пути для ресурсов. Когда регистрируется комплект ресурсов в представлении, каждый соответствующий файл ресурса в [[yii\web\AssetBundle::css|css]] или [[yii\web\AssetBundle::js|js]] массивах будет рассмотрен в соответствии с этой привязкой. И, если какой-либо из ключей найден, как последняя часть пути до файла ресурса (путь на который начинается с [[yii\web\AssetBundle::sourcePath]] по возможности), то соответствующее значение заменит ресурс и будет зарегистрировано в представлении. Например, путь до файла ресурса `my/path/to/jquery.js` - это соответствует ключу `jquery.js`.

<!--
> Note: Only assets specified using relative paths are subject to asset mapping. The target asset paths
  should be either absolute URLs or paths relative to [[yii\web\AssetManager::basePath]].
--> 
> Примечание: Ресурсы заданные только с использованием относительного пути могут использоваться в привязке ресурсов. Пути ресурсов должны быть абсолютные URLs или путь относительно [[yii\web\AssetManager::basePath]].


### Публикация Ресурсов<span id="asset-publishing"></span>
<!-- Asset Publishing -->

<!--
As aforementioned, if an asset bundle is located in a directory that is not Web accessible, its assets will be copied
to a Web directory when the bundle is being registered with a view. This process is called *asset publishing*, and is done
automatically by the [[yii\web\AssetManager|asset manager]].
--> 
Как уже было сказано выше, если комплект ресурсов располагается в директории которая не доступна из Web, эти ресурсы будут скопированы в Web директорию, когда комплект будет зарегистрирован в представлении. Этот процесс называется *публикацией ресурсов*, его автоматически выполняет [[yii\web\AssetManager|asset manager]].

<!--
By default, assets are published to the directory `@webroot/assets` which corresponds to the URL `@web/assets`.
You may customize this location by configuring the [[yii\web\AssetManager::basePath|basePath]] and
[[yii\web\AssetManager::baseUrl|baseUrl]] properties.
--> 
По умолчанию, ресурсы публикуются в директорию `@webroot/assets` которая соответствует URL `@web/assets`. Можно настроить это местоположение сконфигурировав свойства [[yii\web\AssetManager::basePath|basePath]] и [[yii\web\AssetManager::baseUrl|baseUrl]].

<!--
Instead of publishing assets by file copying, you may consider using symbolic links, if your OS and Web server allow.
This feature can be enabled by setting [[yii\web\AssetManager::linkAssets|linkAssets]] to be true.
-->
Вместо публикации ресурсов путём копирования файлов, можно рассмотреть использование символических ссылок, если Ваша операционная система или Web сервер это разрешают. Эта функция может быть включена путем установки [[yii\web\AssetManager::linkAssets|linkAssets]] в true.

```php
return [
    // ...
    'components' => [
        'assetManager' => [
            'linkAssets' => true,
        ],
    ],
];
```

<!--
With the above configuration, the asset manager will create a symbolic link to the source path of an asset bundle
when it is being published. This is faster than file copying and can also ensure that the published assets are
always up-to-date.
-->
С конфигурацией, установленной выше, менеджер ресурсов будет создавать символические ссылки на исходные пути комплекта ресурсов когда он будет публиковаться. Это быстрее, чем копирование файлов, а также может гарантировать, что опубликованные ресурсы всегда up-to-date(обновлённые/свежие).

### Перебор Кэша<span id="cache-busting"></span>
<!-- Cache Busting -->

<!--
For Web application running in production mode, it is a common practice to enable HTTP caching for assets and other
static resources. A drawback of this practice is that whenever you modify an asset and deploy it to production, a user
client may still use the old version due to the HTTP caching. To overcome this drawback, you may use the cache busting
feature, which was introduced in version 2.0.3, by configuring [[yii\web\AssetManager]] like the following:
-->
Для Web приложения запущенного в режиме продакшена, считается нормальной практикой разрешить HTTP кэширование для ресурсов и других статичных источников. Недостаток такой практики в том, что всякий раз, когда изменяется ресурс и разворачивается продакшен, пользователь может по-прежнему использовать старую версию ресурса вследствие HTTP кэширования. Чтобы избежать этого, можно использовать возможность перебора кэша, которая была добавлена в версии 2.0.3, для этого можно настроить [[yii\web\AssetManager]] следующим образом:
  
```php
return [
    // ...
    'components' => [
        'assetManager' => [
            'appendTimestamp' => true,
        ],
    ],
];
```

<!--
By doing so, the URL of every published asset will be appended with its last modification timestamp. For example,
the URL to `yii.js` may look like `/assets/5515a87c/yii.js?v=1423448645"`, where the parameter `v` represents the
last modification timestamp of the `yii.js` file. Now if you modify an asset, its URL will be changed, too, which causes
the client to fetch the latest version of the asset.
-->
Делая таким образом, к URL каждого опубликованного ресурса будет добавляться временная метка его последней модификации. Например, URL для `yii.js` может выглядеть как `/assets/5515a87c/yii.js?v=1423448645"`, где параметр `v` представляет собой временную метку последней модификации файла `yii.js`. Теперь если изменить ресурс, его URL тоже будет изменен, это означает что клиент получит последнюю версию ресурса.


## Обычное Использование Комплекта Ресурсов<span id="common-asset-bundles"></span>
<!-- Commonly Used Asset Bundles -->

<!--
The core Yii code has defined many asset bundles. Among them, the following bundles are commonly used and may
be referenced in your application or extension code.
-->
Код ядра Yii содержит большое количество комплектов ресурсов. Среди них, следующие комплекты широко используются и могут упоминаться в Вашем приложении или коде расширения:
<!--
- [[yii\web\YiiAsset]]: It mainly includes the `yii.js` file which implements a mechanism of organizing JavaScript code
  in modules. It also provides special support for `data-method` and `data-confirm` attributes and other useful features.
-->
- [[yii\web\YiiAsset]]: Включает основной `yii.js` файл который реализует механизм организации JavaScript кода в модулях. Также обеспечивает специальную поддержку для `data-method` и `data-confirm` атрибутов и содержит другие полезные функции.

<!--
- [[yii\web\JqueryAsset]]: It includes the `jquery.js` file from the jQuery Bower package.
-->
- [[yii\web\JqueryAsset]]: Включает `jquery.js` файл из jQuery Bower пакета.

<!--
- [[yii\bootstrap\BootstrapAsset]]: It includes the CSS file from the Twitter Bootstrap framework.
-->
- [[yii\bootstrap\BootstrapAsset]]: Включает CSS файл из Twitter Bootstrap фреймворка.

<!--
- [[yii\bootstrap\BootstrapPluginAsset]]: It includes the JavaScript file from the Twitter Bootstrap framework for
  supporting Bootstrap JavaScript plugins.
-->
- [[yii\bootstrap\BootstrapPluginAsset]]: Включает JavaScript файл из Twitter Bootstrap фреймворка для поддержки Bootstrap JavaScript плагинов.

<!--
- [[yii\jui\JuiAsset]]: It includes the CSS and JavaScript files from the jQuery UI library.
-->
- [[yii\jui\JuiAsset]]: Включает CSS и JavaScript файлы из jQuery UI библиотеки.

<!--
If your code depends on jQuery, jQuery UI or Bootstrap, you should use these predefined asset bundles rather than
creating your own versions. If the default setting of these bundles do not satisfy your needs, you may customize them 
as described in the [Customizing Asset Bundle](#customizing-asset-bundles) subsection. 
-->
Если Ваш код зависит от jQuery, jQuery UI или Bootstrap, Вам необходимо использовать эти предопределенные комплекты ресурсов, а не создавать свои собственные варианты. Если параметры по умолчанию этих комплектов не удовлетворяют Вашим нуждам, Вы можете настроить их как описано в подразделе [Настройка Комплектов Ресурсов](#customizing-asset-bundles).


## Преобразование Ресурсов<span id="asset-conversion"></span>
<!-- Asset Conversion -->

<!--
Instead of directly writing CSS and/or JavaScript code, developers often write them in some extended syntax and
use special tools to convert it into CSS/JavaScript. For example, for CSS code you may use [LESS](http://lesscss.org/)
or [SCSS](http://sass-lang.com/); and for JavaScript you may use [TypeScript](http://www.typescriptlang.org/).
-->
Вместо того, чтобы напрямую писать CSS и/или JavaScript код, разработчики часто пишут его в некотором <b>расширенном синтаксисе</b> и используют специальные инструменты конвертации в CSS/JavaScript. Например, для CSS кода можно использовать [LESS](http://lesscss.org/) или [SCSS](http://sass-lang.com/); а для JavaScript можно использовать [TypeScript](http://www.typescriptlang.org/).

<!--
You can list the asset files in extended syntax in the [[yii\web\AssetBundle::css|css]] and [[yii\web\AssetBundle::js|js]] properties of an asset bundle. For example,
-->
Можно перечислить файлы ресурсов в <b>расширенном синтаксисе</b> в [[yii\web\AssetBundle::css|css]] и [[yii\web\AssetBundle::js|js]] свойствах из комплекта ресурсов. Например,

```php
class AppAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $css = [
        'css/site.less',
    ];
    public $js = [
        'js/site.ts',
    ];
    public $depends = [
        'yii\web\YiiAsset',
        'yii\bootstrap\BootstrapAsset',
    ];
}
```

<!--
When you register such an asset bundle with a view, the [[yii\web\AssetManager|asset manager]] will automatically
run the pre-processor tools to convert assets in recognized extended syntax into CSS/JavaScript. When the view
finally renders a page, it will include the CSS/JavaScript files in the page, instead of the original assets
in extended syntax.
-->
Когда Вы регистрируете такой комплект ресурсов в представлении, [[yii\web\AssetManager|asset manager]] автоматически запустит нужные инструменты препроцессора и конвертирует ресурсы в CSS/JavaScript, если их расширенный синтаксис распознан. Когда представление окончательно отобразит страницу, в неё будут включены файлы CSS/JavaScript, вместо оригинальных ресурсов в расширенном синтаксисе.

<!--
Yii uses the file name extensions to identify which extended syntax an asset is in. By default it recognizes
the following syntax and file name extensions:
-->
Yii использует имена расширений файлов для идентификации расширенного синтаксиса внутри ресурса. По умолчанию признаны следующие синтаксисы и имена расширений файлов:

- [LESS](http://lesscss.org/): `.less`
- [SCSS](http://sass-lang.com/): `.scss`
- [Stylus](http://learnboost.github.io/stylus/): `.styl`
- [CoffeeScript](http://coffeescript.org/): `.coffee`
- [TypeScript](http://www.typescriptlang.org/): `.ts`

<!--
Yii relies on the installed pre-processor tools to convert assets. For example, to use [LESS](http://lesscss.org/)
you should install the `lessc` pre-processor command.
-->
Yii ориентируется на установленные инструменты конвертации ресурсов препроцессора. Например, используя [LESS](http://lesscss.org/), Вы должны установить команду `lessc` препроцессора.

You can customize the pre-processor commands and the supported extended syntax by configuring
[[yii\web\AssetManager::converter]] like the following:

Вы можете настроить команды препроцессора и поддерживать расширенный сиснтаксис сконфигурировав [[yii\web\AssetManager::converter]] следующим образом:

```php
return [
    'components' => [
        'assetManager' => [
            'converter' => [
                'class' => 'yii\web\AssetConverter',
                'commands' => [
                    'less' => ['css', 'lessc {from} {to} --no-color'],
                    'ts' => ['js', 'tsc --out {to} {from}'],
                ],
            ],
        ],
    ],
];
```

In the above, we specify the supported extended syntax via the [[yii\web\AssetConverter::commands]] property.
The array keys are the file extension names (without leading dot), and the array values are the resulting
asset file extension names and the commands for performing the asset conversion. The tokens `{from}` and `{to}`
in the commands will be replaced with the source asset file paths and the target asset file paths.

В примере выше, Вы задали поддержку расширенного синтаксиса через [[yii\web\AssetConverter::commands]] свойство.
Ключи массива это имена файлов расширений (без ведущей точки), а значения массива это образующийся файл ресурса имён расширений и команд для выполнения конвертации ресурса. Маркеры `{from}` и `{to}` в командах будут заменены исходным путём файла ресурсов и соответственно путём назначения файла ресурсов.



> Info: There are other ways of working with assets in extended syntax, besides the one described above.
  For example, you can use build tools such as [grunt](http://gruntjs.com/) to monitor and automatically
  convert assets in extended syntax. In this case, you should list the resulting CSS/JavaScript files in
  asset bundles rather than the original files.
  
> Примечание: Существуют другие способы работы с ресурсами расширенного синтаксиса, кроме того, который указан выше.
Например, Вы можете использовать инструменты построения, такие как [grunt](http://gruntjs.com/) для отслеживания и автоматической конвертации ресурсов расширенного синтаксиса. В этом случае, Вы должны перечислить конечные CSS/JavaScript файлы в комплекте ресурсов вместо исходных файлов.


## Combining and Compressing Assets - Объединение и Сжатие Ресурсов<span id="combining-compressing-assets"></span>

A Web page can include many CSS and/or JavaScript files. To reduce the number of HTTP requests and the overall
download size of these files, a common practice is to combine and compress multiple CSS/JavaScript files into 
one or very few files, and then include these compressed files instead of the original ones in the Web pages.

Web страница может включать много CSS и/или JavaScript файлов. Чтобы сократить количество HTTP запросов и общий размер загрузки этих файлов, общепринятой практикой является объединение и сжатие нескольких CSS/JavaScript файлов в один или в меньшее количество, а затем включение этих сжатых файлов вместо исходных в Web страницы.
 
> Info: Combining and compressing assets is usually needed when an application is in production mode. 
  In development mode, using the original CSS/JavaScript files is often more convenient for debugging purposes.
  
> Примечание: Комбинирование и сжатие ресурсов обычно необходимо, когда приложение находится в режиме продакшена.
В режиме разработки, использование исходных CSS/JavaScript файлов часто более удобно для целей отладки.

In the following, we introduce an approach to combine and compress asset files without the need to modify
your existing application code.

Далее, Мы представим подход комбинирования и сжатия файлов ресурса без необходимости изменения Вашего существующего кода приложения.

1. Find all the asset bundles in your application that you plan to combine and compress.
Найдите все комплекты ресурсов в Вашем приложении, которые Вы планируете скомбинировать и сжать.

2. Divide these bundles into one or a few groups. Note that each bundle can only belong to a single group.
Распределите эти комплекты в одну или несколько групп. Обратите внимание, что каждый комплект может принадлежать только одной группе.

3. Combine/compress the CSS files in each group into a single file. Do this similarly for the JavaScript files.
Скомбинируйте/сожмите CSS файлы в каждой группе в один файл. Сделайте тоже самое для JavaScript файлов.

4. Define a new asset bundle for each group:
Определите новый комплект ресурсов для каждой группы:

   * Set the [[yii\web\AssetBundle::css|css]] and [[yii\web\AssetBundle::js|js]] properties to be
     the combined CSS and JavaScript files, respectively.

* Установите [[yii\web\AssetBundle::css|css]] и [[yii\web\AssetBundle::js|js]] свойства. Соответствующие CSS и JavaScript файлы будут скомбинированы.

   * Customize the asset bundles in each group by setting their [[yii\web\AssetBundle::css|css]] and 
     [[yii\web\AssetBundle::js|js]] properties to be empty, and setting their [[yii\web\AssetBundle::depends|depends]]
     property to be the new asset bundle created for the group.

* Настройте комплекты ресурсов в каждой группе, установив их [[yii\web\AssetBundle::css|css]] и 
     [[yii\web\AssetBundle::js|js]] свойства как пустые, и установите их [[yii\web\AssetBundle::depends|depends]] свойство как новый комплект ресурсов, созданный для группы.

Using this approach, when you register an asset bundle in a view, it causes the automatic registration of
the new asset bundle for the group that the original bundle belongs to. And as a result, the combined/compressed 
asset files are included in the page, instead of the original ones.

Используя этот подход, при регистрации комплекта ресурсов в представлении, автоматически регистрируется новый комплект ресурсов для группы, к которому исходный комплект принадлежит. В результате скомбинированные/сжатые файлы ресурсов включаются в страницу вместо исходных.


### An Example - Пример <span id="example"></span>

Let's use an example to further explain the above approach. 

Давайте рассмотрим пример, чтобы объяснить вышеуказанный подход.

Assume your application has two pages, X and Y. Page X uses asset bundles A, B and C, while Page Y uses asset bundles B, C and D.

Предположим, ваше приложение имеет две страницы, X и Y. Страница X использует комплект ресурсов A, B и C, в то время как страница Y используеткомплект ресурсов, B, C и D.

You have two ways to divide these asset bundles. One is to use a single group to include all asset bundles, the
other is to put A in Group X, D in Group Y, and (B, C) in Group S. Which one is better? It depends. The first way
has the advantage that both pages share the same combined CSS and JavaScript files, which makes HTTP caching
more effective. On the other hand, because the single group contains all bundles, the size of the combined CSS and 
JavaScript files will be bigger and thus increase the initial file transmission time. For simplicity in this example, 
we will use the first way, i.e., use a single group to contain all bundles.

У Вас есть два пути чтобы разделить эти комплекты ресурсов. Первый - использовать одну группу включающую в себя все комплекты ресурсов. Другой путь - положить комплект А в группу Х, D в группу Y, а (B, C) в группу S. Какой из этих вариантов лучше? Это зависит. Первый способ имеет то преимущество, что в обоих страницах одинаково скомбинированы файлы CSS и JavaScript, что делает HTTP кэширование более эффективным. С другой стороны, поскольку одна группа содержит все комплекты, размер в скомбинированных CSS и JavaScript файлов будет больше, и таким образом увеличится время отдачи файла <i><b>(загрузки страницы)</b></i>. Для простоты в этом примере, мы будем использовать первый способ, то есть, использовать единую группу, содержащую все пакеты.

> Info: Dividing asset bundles into groups is not trivial task. It usually requires analysis about the real world
  traffic data of various assets on different pages. At the beginning, you may start with a single group for simplicity. 
  
> Примечание: Разделение комплекта ресурсов на группы это не тривиальная задача. Это, как правило, требует анализа о реальном мире трафика данных различных ресурсов на разных страницах. В начале, вы можете начать с одной группы, для простоты.

Use existing tools (e.g. [Closure Compiler](https://developers.google.com/closure/compiler/), 
[YUI Compressor](https://github.com/yui/yuicompressor/)) to combine and compress CSS and JavaScript files in 
all the bundles. Note that the files should be combined in the order that satisfies the dependencies among the bundles. 
For example, if Bundle A depends on B which depends on both C and D, then you should list the asset files starting 
from C and D, followed by B and finally A. 

Используйте существующие инструменты (например [Closure Compiler](https://developers.google.com/closure/compiler/), 
[YUI Compressor](https://github.com/yui/yuicompressor/)) для комбинирования и сжатия CSS и JavaScript файлов во всех комплектах. Обратите внимание, что файлы должны быть объединены в том порядке, который удовлетворяет зависимости между комплектами. Например, если комплект A зависит от В который зависит от С и D, то Вы должны перечислить файлы ресурсов начиная с С и D, затем B и только после того А.

After combining and compressing, we get one CSS file and one JavaScript file. Assume they are named as 
`all-xyz.css` and `all-xyz.js`, where `xyz` stands for a timestamp or a hash that is used to make the file name unique
to avoid HTTP caching problems.

После объединения и сжатия, Вы получите один CSS файл и один JavaScript файл. Предположим, они названы как `all-xyz.css` и `all-xyz.js`, где `xyz` это временная метка или хэш, используется, чтобы создать уникальное имя файла чтобы избежать проблем с кэшированием HTTP.
 
We are at the last step now. Configure the [[yii\web\AssetManager|asset manager]] as follows in the application
configuration:
 	
Сейчас мы находимся на последнем шаге. Настройте [[yii\web\AssetManager|asset manager]] как показано ниже в конфигурации вашего приложения:

```php
return [
    'components' => [
        'assetManager' => [
            'bundles' => [
                'all' => [
                    'class' => 'yii\web\AssetBundle',
                    'basePath' => '@webroot/assets',
                    'baseUrl' => '@web/assets',
                    'css' => ['all-xyz.css'],
                    'js' => ['all-xyz.js'],
                ],
                'A' => ['css' => [], 'js' => [], 'depends' => ['all']],
                'B' => ['css' => [], 'js' => [], 'depends' => ['all']],
                'C' => ['css' => [], 'js' => [], 'depends' => ['all']],
                'D' => ['css' => [], 'js' => [], 'depends' => ['all']],
            ],
        ],
    ],
];
```

As explained in the [Customizing Asset Bundles](#customizing-asset-bundles) subsection, the above configuration
changes the default behavior of each bundle. In particular, Bundle A, B, C and D no longer have any asset files.
They now all depend on the `all` bundle which contains the combined `all-xyz.css` and `all-xyz.js` files.
Consequently, for Page X, instead of including the original source files from Bundle A, B and C, only these
two combined files will be included; the same thing happens to Page Y.

Как объяснено в подразделе [Настройка Комплектов Ресурсов](#customizing-asset-bundles), приведенная выше конфигурация
изменяет поведение по умолчанию каждого комплекта. В частности, комплект A, B, C и D не имеют больше никаких файлов ресурсов. Теперь они все зависят от `all` комплекта который содержит скомбинированные `all-xyz.css` и `all-xyz.js` файлы.

There is one final trick to make the above approach work more smoothly. Instead of directly modifying the
application configuration file, you may put the bundle customization array in a separate file and conditionally
include this file in the application configuration. For example,

Есть еще один трюк, чтобы сделать работу вышеуказанного подхода более отлаженной.



```php
return [
    'components' => [
        'assetManager' => [
            'bundles' => require(__DIR__ . '/' . (YII_ENV_PROD ? 'assets-prod.php' : 'assets-dev.php')),  
        ],
    ],
];
```

That is, the asset bundle configuration array is saved in `assets-prod.php` for production mode, and
`assets-dev.php` for non-production mode.

То есть, массив конфигурации комплекта ресурсов сохраняется в `assets-prod.php` для режима продакшена, и в `assets-dev.php` для режима не продакшена.


### Using the `asset` Command - Использование команды `asset`<span id="using-asset-command"></span>

Yii provides a console command named `asset` to automate the approach that we just described.

Yii предоставляет консольную команду с именем `asset` для автоматизации подхода, который мы только что описали.

To use this command, you should first create a configuration file to describe what asset bundles should
be combined and how they should be grouped. You can use the `asset/template` sub-command to generate
a template first and then modify it to fit for your needs.

Чтобы использовать эту команду, Вы должны сначала создать файл конфигурации для описания того, как комплекты ресурсов должны быть скомбинированны и как они должны быть сгруппированны. Затем Вы можете использовать подкомманду `asset/template`, чтобы сгенерировать первый шаблон и затем отредактировать его под свои нужды.

```
yii asset/template assets.php
```

The command generates a file named `assets.php` in the current directory. The content of this file looks like the following:

Данная команда сгенерирует файл с именем `assets.php` в текущей директории. Содержание этого файла можно увидеть ниже:

```php
<?php
/**
 * Configuration file for the "yii asset" console command.
 * Файл конфигурации команды консоли "yii asset".
 * Note that in the console environment, some path aliases like '@webroot' and '@web' may not exist.
 * Обратите внимание, что в консольной среде, некоторые псевдонимы путей такие как "@webroot' и '@web " не могут существовать/быть использованы.
 * Please define these missing path aliases.
 * Пожалуйста, определите эти отсутствующие псевдонимы путей.
 */
return [
    // Adjust command/callback for JavaScript files compressing:
    // Настроить команду/обратный вызов для сжатия файлов JavaScript:
    'jsCompressor' => 'java -jar compiler.jar --js {from} --js_output_file {to}',
    // Adjust command/callback for CSS files compressing:
    // Настроить команду/обратный вызов для сжатия файлов CSS:
    'cssCompressor' => 'java -jar yuicompressor.jar --type css {from} -o {to}',
    // The list of asset bundles to compress:
    // Список комплектов ресурсов для сжатия:
    'bundles' => [
        // 'yii\web\YiiAsset',
        // 'yii\web\JqueryAsset',
    ],
    // Asset bundle for compression output:
    // Комплект ресурса после сжатия:
    'targets' => [
        'all' => [
            'class' => 'yii\web\AssetBundle',
            'basePath' => '@webroot/assets',
            'baseUrl' => '@web/assets',
            'js' => 'js/all-{hash}.js',
            'css' => 'css/all-{hash}.css',
        ],
    ],
    // Asset manager configuration:
    // Настройка менеджера ресурсов:
    'assetManager' => [
    ],
];
```

You should modify this file and specify which bundles you plan to combine in the `bundles` option. In the `targets` 
option you should specify how the bundles should be divided into groups. You can specify one or multiple groups, 
as aforementioned.

Вы должны изменить этот файл и указать какие комплекты вы планируете совместить в `bundles` параметре. В параметре `targets` вы должны указать как комплекты должны быть поделены в группы. Вы можете указать одну или несколько групп, как уже было сказано выше.

> Note: Because the alias `@webroot` and `@web` are not available in the console application, you should
  explicitly define them in the configuration.
  
> Примечание: Так как псевдонимы путей `@webroot` и `@web` не могут быть использованны в консольном приложении, Вы должны явно задать их в файле конфигурации.

JavaScript files are combined, compressed and written to `js/all-{hash}.js` where {hash} is replaced with the hash of
the resulting file.

JavaScript файлы объеденены, сжаты и записаны в `js/all-{hash}.js`, где {hash} перенесён из хэша результирующего файла.

The `jsCompressor` and `cssCompressor` options specify the console commands or PHP callbacks for performing
JavaScript and CSS combining/compressing. By default, Yii uses [Closure Compiler](https://developers.google.com/closure/compiler/) 
for combining JavaScript files and [YUI Compressor](https://github.com/yui/yuicompressor/) for combining CSS files. 
You should install those tools manually or adjust these options to use your favorite tools.

Параметры `jsCompressor` и `cssCompressor` указывают на консольные команды или обратный вызов PHP, выполняющие JavaScript и CSS объединение/сжатие. По умолчанию, Yii использует [Closure Compiler](https://developers.google.com/closure/compiler/) для объединения JavaScript файлов и [YUI Compressor](https://github.com/yui/yuicompressor/) для объединения CSS файлов. Вы должны установить эти инструменты вручную или настроить данные параметры, чтобы использовать ваши любимые инструменты.

With the configuration file, you can run the `asset` command to combine and compress the asset files
and then generate a new asset bundle configuration file `assets-prod.php`:

Вы можете запустить команду `asset`, с файлом конфигурации, для объединения и сжатия файлов ресурса и затем создать новый файл конфигурации комплекта ресурса `assets-prod.php`:
 
```
yii asset assets.php config/assets-prod.php
```

The generated configuration file can be included in the application configuration, like described in
the last subsection.

Сгенерированный файл конфигурации может быть включен в конфигурацию приложения как описано в последнем подразделе.


> Info: Using the `asset` command is not the only option to automate the asset combining and compressing process.
  You can use the excellent task runner tool [grunt](http://gruntjs.com/) to achieve the same goal.
  
> Для справки: Использовать команду `asset` можно не только в целях автоматизации процесса объединения и сжатия.  	
Вы можете использовать отличный инструмент запуска приложений [grunt](http://gruntjs.com/) для достижения той же цели.


### Grouping Asset Bundles - Группировка Комплектов Ресурсов <span id="grouping-asset-bundles"></span>

In the last subsection, we have explained how to combine all asset bundles into a single one in order to minimize
the HTTP requests for asset files referenced in an application. This is not always desirable in practice. For example,
imagine your application has a "front end" as well as a "back end", each of which uses a different set of JavaScript 
and CSS files. In this case, combining all asset bundles from both ends into a single one does not make sense, 
because the asset bundles for the "front end" are not used by the "back end" and it would be a waste of network
bandwidth to send the "back end" assets when a "front end" page is requested.

В последнем подразделе, мы поясним как объединять все комплекты ресурсов в единый в целях минимизации HTTP запросов для файлов ресурсов упоминавшихся в приложении. Это не всегда желательно на практике. Например, представьте себе, что Ваше приложение содержит "front end" а также и "back end", каждый из которых использует свой набор JavaScript и CSS файлов. В этом случае, объединение всех комплектов ресурсов с обеих сторон в один не имеет смысла, потому, что комплекты ресурсов для "front end" не используются в "back end" и будет бесполезной тратой траффика отправлять "back end" ресурсы когда страница из "front end" будет запрошена.

To solve the above problem, you can divide asset bundles into groups and combine asset bundles for each group.
The following configuration shows how you can group asset bundles: 

Для решения вышеуказанной проблемы, вы можете разделить комплекты по группам и объединить комплекты ресурсов для каждой группы. Следующая конфигурация показывает, как Вы можете объединять комплекты ресурсов:

```php
return [
    ...
    // Specify output bundles with groups:
    // Укажите выходной комплект для групп:
    'targets' => [
        'allShared' => [
            'js' => 'js/all-shared-{hash}.js',
            'css' => 'css/all-shared-{hash}.css',
            'depends' => [
                // Include all assets shared between 'backend' and 'frontend'
                // Включаем все ресурсы поделённые между 'backend' и 'frontend'
                'yii\web\YiiAsset',
                'app\assets\SharedAsset',
            ],
        ],
        'allBackEnd' => [
            'js' => 'js/all-{hash}.js',
            'css' => 'css/all-{hash}.css',
            'depends' => [
                // Include only 'backend' assets:
                // Включаем только 'backend' ресурсы:
                'app\assets\AdminAsset'
            ],
        ],
        'allFrontEnd' => [
            'js' => 'js/all-{hash}.js',
            'css' => 'css/all-{hash}.css',
            'depends' => [], // Include all remaining assets - Включаем все оставшиеся ресурсы
        ],
    ],
    ...
];
```

As you can see, the asset bundles are divided into three groups: `allShared`, `allBackEnd` and `allFrontEnd`.
They each depends on an appropriate set of asset bundles. For example, `allBackEnd` depends on `app\assets\AdminAsset`.
When running `asset` command with this configuration, it will combine asset bundles according to the above specification.

Как вы можете видеть, комплекты ресурсов поделены на три группы: `allShared`, `allBackEnd` и `allFrontEnd`. Каждая из которых зависит от соответствующего набора комплектов ресурсов. Например, `allBackEnd` зависит от `app\assets\AdminAsset`. При запуске команды `asset` с данной конфигурацией, будут объединены комплекты ресурсов согласно приведенной выше спецификации.

> Info: You may leave the `depends` configuration empty for one of the target bundle. By doing so, that particular
  asset bundle will depend on all of the remaining asset bundles that other target bundles do not depend on.

> Для справки: Вы можете оставить `depends` конфигурацию пустой для одного из намеченных комплектов. Поступая таким образом, данный комплект ресурсов будет зависить от всех остальных комплектов ресурсов, от которых другие целевые комплекты не зависят.
