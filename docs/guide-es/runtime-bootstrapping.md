Bootstrapping
=============

El Bootstrapping hace referencia al proceso de preparar el entorno antes de que una aplicación se inicie para resolver y procesar una petición entrante. El se ejecuta en dos lugares: el [script de entrada](structure-entry-scripts.md) y la [aplicación](structure-applications.md).

En el [script de entrada](structure-entry-scripts.md), se registran los cargadores automáticos de clase para diferentes librerías. Esto incluye el cargador automático de Composer a través de su fichero ‘autoload.php’ y del cargador automático de Yii a través del fichero de clase ‘Yii’. El script de entrada después carga la [configuración](concept-configurations.md) de la aplicación y crea una instancia de la [aplicación](structure-applications.md).

El constructor de la aplicación, ejecuta el siguiente trabajo de bootstrapping:

Llama a [[yii\base\Application::preInit()|preInit()]], que configura algunas propiedades de alta prioridad de la aplicación, como [[yii\base\Application::basePath|basePath]].
Registra el [[yii\base\Application::errorHandler|error handler]].
Inicializa las propiedades de aplicación usando la configuración de la aplicación dada.
Llama a [[yii\base\Application::init()|init()]] que a su vez llama a [[yii\base\Application::bootstrap()|bootstrap()]] para ejecutar componentes de bootstrapping.
Incluye el archivo de manifiesto de extensiones ‘vendor/yiisoft/extensions.php’
Crea y ejecuta [componentes de bootstrap](structure-extensions.md#bootstrapping-classes) declarados por las extensiones. 
Crea y ejecuta [componentes de aplicación](structure-application-components.md) y/o [módulos](structure-modules.md) que se declaran en la [propiedad bootstrap](structure-applications.md#bootstrap) de la aplicación.

Debido a que el trabajo de bootstrapping se tiene que ejecutar antes de gestionar *todas* las peticiones, es muy importante mantener este proceso ligero y optimizado lo máximo que sea posible.

Intenta no registrar demasiados componentes de bootstrapping. Un componente de bootstrapping sólo es necesario si tiene que interaccionar en todo el ciclo de vida de la gestión de la petición. Por ejemplo, si un modulo necesita registrar reglas de análisis de URL adicionales, se debe incluirse en la [propiedad bootstrap](structure-applications.md#bootstrap) para que la nueva regla de URL tenga efecto antes de que sea utilizada para resolver peticiones.

En modo de producción, hay que habilitar la cache bytecode, así como [APC](http://php.net/manual/es/book.apc.php), para minimizar el tiempo necesario para incluir y analizar archivos PHP.

Algunas grandes aplicaciones tienen [configuraciones](concept-configurations.md) de aplicación muy complejas que están dividida en muchos archivos de configuración más pequeños.
