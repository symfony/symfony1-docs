Funcionamiento interno de Symfony
=================================

*por Geoffrey Bachelet*

¿Alguna vez te has preguntado qué le sucede a una petición HTTP cuando llega a
una aplicación Symfony? Si la respuesta es afirmativa, estás en el lugar adecuado.
Este capítulo explica detalladamente cómo procesa Symfony cada petición para
crear y enviar la respuesta correspondiente. Obviamente no sólo se va a describir
el proceso completo sino que se van a explicar muchas otras cosas interesantes
incluyendo los lugares en los que puedes interactuar con el proceso.

El inicio
---------

Todo empieza en el controlador de tu aplicación. Supongamos que tienes un
controlador llamado `frontend` y un entorno llamado `dev` (algo muy común al
empezar a desarrollar un proyecto Symfony). En este caso, tu controlador frontal
se encuentra en el archivo [`web/frontend_dev.php`](http://trac.symfony-project.org/browser/branches/1.3/lib/task/generator/skeleton/app/web/index.php).
¿Qué es exactamente lo que sucede en este archivo? En unas pocas líneas de código
Symfony obtiene la configuración de la aplicación y crea una instancia de
`sfContext`, que es el encargado de despachar la petición. La configuración de
la aplicación es necesaria cuando se crea el objeto `sfContext`, que es el *motor*
que hace funcionar a Symfony y que depende de la aplicación.

>**TIP**
>Symfony concede al programador cierto control en este punto ya que permite pasar
>como cuarto argumento de ~`ProjectConfiguration::getApplicationConfiguration()`~
>un directorio raíz propio para la aplicación, así como una clase de contexto
>propia como tercer (y último) argumento de
>[`sfContext::createInstance()`](http://www.symfony-project.org/api/1_3/sfContext#method_createinstance)
>(pero recuerda que debe heredar de `sfContext`).

Obtener la configuración de la aplicación es un paso muy importante. En primer
lugar, `sfProjectConfiguration` se encarga de adivinar la clase de configuración
de la aplicación, normalmente llamada `${application}Configuration` y guardada
en el archivo `apps/${application}/config/${application}Configuration.class.php`.

La clase `sfApplicationConfiguration` hereda de `ProjectConfiguration`, por lo
que cualquier método de `ProjectConfiguration` está disponible en todas las
aplicaciones del proyecto. Esto también significa que `sfApplicationConfiguration`
comparte su constructor tanto con `ProjectConfiguration` como con `sfProjectConfiguration`.
Esto es una gran ventaja porque muchos aspectos del proyecto se configuran en
el constructor de `sfProjectConfiguration`. Su primera tarea es calcular y
guardar varios valores útiles, como el directorio raíz del proyecto y el directorio
de la librería de Symfony. `sfProjectConfiguration` también crea un despachador
de eventos (*event dispatcher*) de tipo `sfEventDispatcher`, a menos que se le
pase un despachador propio como quinto argumento de `ProjectConfiguration::getApplicationConfiguration()`
en el controlador frontal.

Justo después de esto, tienes la primera oportunidad para interactuar con el
proceso de configuración redefiniendo el método `setup()` de `ProjectConfiguration`.
Este es normalmente el mejor lugar para activar o desactivar plugins mediante
[`sfProjectConfiguration::setPlugins()`](http://www.symfony-project.org/api/1_3/sfProjectConfiguration#method_setplugins),
[`sfProjectConfiguration::enablePlugins()`](http://www.symfony-project.org/api/1_3/sfProjectConfiguration#method_enableplugins),
[`sfProjectConfiguration::disablePlugins()`](http://www.symfony-project.org/api/1_3/sfProjectConfiguration#method_disableplugins) o
[`sfProjectConfiguration::enableAllPluginsExcept()`](http://www.symfony-project.org/api/1_3/sfProjectConfiguration#method_enableallpluginsexcept)).

A continuación se cargan los plugins mediante [`sfProjectConfiguration::loadPlugins()`](http://www.symfony-project.org/api/1_3/sfProjectConfiguration#method_loadplugins)
y el programador puede interactuar con este proceso redefiniendo el método
[`sfProjectConfiguration::setupPlugins()`](http://www.symfony-project.org/api/1_3/sfProjectConfiguration#method_setupplugins).

La inicialización de los plugins es muy sencilla. Symfony busca para cada plugin
una clase llamada `${plugin}Configuration` (por ejemplo, `sfGuardPluginConfiguration`)
y si la encuentra, crea una instancia. Si no la encuentra, utiliza la clase
genérica `sfPluginConfigurationGeneric`. Los siguientes dos métodos permiten
modificar la configuración de un plugin:

 * `${plugin}Configuration::configure()`, antes de que se realice la carga
   automática de clases
 * `${plugin}Configuration::initialize()`, después de la carga automática de
   clases

Seguidamente `sfApplicationConfiguration` ejecuta su método `configure()`, que
también se puede utilizar para personalizar la configuración de cada aplicación
antes de que comience el proceso de inicialización de la configuración
interna en [`sfApplicationConfiguration::initConfiguration()`](http://www.symfony-project.org/api/1_3/sfApplicationConfiguration#method_initconfiguration).

Esta parte del proceso de configuración de Symfony se encarga de muchas tareas
y tiene varios puntos preparados para que puedas interactuar. Si quieres
interactuar por ejemplo con la configuración del cargador automático de clases,
puedes conectarte al evento `autoload.filter_config`. A continuación se cargan
varios archivos de configuración muy importantes, incluyendo `settings.yml` y
`app.yml`. Por último, se ejecuta la última parte de la configuración de los
plugins mediante el archivo `config/config.php` de cada plugin o con el método
`initialize()` de la clase de configuración.

Si la opción `sf_check_lock` está activada, Symfony busca un archivo de *lock*
(por ejemplo el que crea la tarea `project:disable`). Si se encuentra el archivo
de *lock*, se comprueban los siguientes archivos y se incluye el primero que
se encuentra, además de terminar de forma inmediata el script:

 1. `apps/${application}/config/unavailable.php`,
 1. `config/unavailable.php`,
 1. `web/errors/unavailable.php`,
 1. `lib/vendor/symfony/lib/exception/data/unavailable.php`,

Finalmente, el programador tiene una última oportunidad para personalizar la
inicialización de la aplicación a través del método ~`sfApplicationConfiguration::initialize()`~.

### Resumen del inicio y su configuración

 * Se obtiene la configuración de la aplicación
  * `ProjectConfiguration::setup()` (aquí se definen los plugins)
  * Se cargan los plugins
   * `${plugin}Configuration::configure()`
   * `${plugin}Configuration::initialize()`
  * `ProjectConfiguration::setupPlugins()` (aquí se configuran los plugins)
  * `${application}Configuration::configure()`
  * Se notifica el evento `autoload.filter_config`
  * Se cargan `settings.yml` y `app.yml`
  * `${application}Configuration::initialize()`
 * Se crea la instancia de `sfContext`

`sfContext` y las factorías
---------------------------

Antes de adentrarnos más profundamente en este proceso, vamos a hablar de una
de las partes más importantes del flujo de trabajo de Symfony: las factorías.

Las factorías en Symfony son una serie de clases o componentes de los que depende
tu aplicación, como por ejemplo `logger` o `i18n`. Cada factoría se configura
en el archivo `factories.yml`, que después se compila mediante un gestor de
configuración (como se explicará más adelante) para convertirlo en el código PHP
que realmente instancia los objetos factoría (puedes ver el resultado de este
proceso en el archivo `cache/frontend/dev/config/config_factories.yml.php` de
tu cache).

>**NOTE**
>La carga de las factoría se realiza durante la inicialización de `sfContext`.
>Puedes ver el código de [`sfContext::initialize()`](http://www.symfony-project.org/api/1_3/sfContext#method_initialize)
>y [`sfContext::loadFactories()`](http://www.symfony-project.org/api/1_3/sfContext#method_loadfactories)
>para obtener más información.

En este punto ya puedes personalizar gran parte del comportamiento de Symfony
simplemente editando la configuración del archivo `factories.yml`. Incluso es
posible reemplazar algunas clases internas de Symfony por tus propias clases de
tipo factoría.

>**NOTE**
>Si quieres conocer más detalles de las factorías, lo mejor es que leas la
>[Referencia de Symfony](http://www.symfony-project.org/reference/1_3/en/05-Factories)
>así como el contenido del propio archivo
>[`factories.yml`](http://trac.symfony-project.org/browser/branches/1.3/lib/config/config/factories.yml).

Si has echado un vistazo al archivo `config_factories.yml.php`, habrás observado
que las factorías se instancia en un determinado orden. Este orden es muy
importante porque algunas factorías dependen de otras (el componente `routing`
por ejemplo requiere el componente `request` para obtener la información que
necesita).

Seguidamente se va a detallar el funcionamiento de la petición (`request`). Por
defecto, las peticiones se representan mediante clases de tipo `sfWebRequest`.
Al crear su instancia, se invoca el método
[`sfWebRequest::initialize()`](http://www.symfony-project.org/api/1_3/sfWebRequest#method_initialize),
que obtiene información relevante como el método HTTP empleado y los parámetros
GET y POST enviados. Si quieres modificar el procesamiento de las peticiones,
puedes hacer uso del evento `request.filter_parameters`.

### Utilizando el evento `request.filter_parameter`

Imagina que tu sitio web dispone de una API pública accesible mediante HTTP.
Para que la aplicación valide la petición, cada usuario de la API debe proporcionar
una clave a través de una cabecera de la petición (llamada por ejemplo `X_API_KEY`).
Todo esto se puede conseguir fácilmente mediante el evento `request.filter_parameter`:

    [php]
    class apiConfiguration extends sfApplicationConfiguration
    {
      public function configure()
      {
        // ...

        $this->dispatcher->connect('request.filter_parameters', array(
          $this, 'requestFilterParameters'
        ));
      }

      public function requestFilterParameters(sfEvent $event, $parameters)
      {
        $request = $event->getSubject();

        $api_key = $request->getHttpHeader('X_API_KEY');

        if (null === $api_key || false === $api_user = Doctrine_Core::getTable('ApiUser')->findOneByToken($api_key))
        {
          throw new RuntimeException(sprintf('Invalid api key "%s"', $api_key));
        }

        $request->setParameter('api_user', $api_user);

        return $parameters;
      }
    }

Después de ejecutar el código anterior, se puede obtener el usuario de la API
directamente desde la petición:

    [php]
    public function executeFoobar(sfWebRequest $request)
    {
      $api_user = $request->getParameter('api_user');
    }

Esta técnica se puede utilizar por ejemplo para validar las llamadas a los
servicios web de tu aplicación.

>**NOTE**
>El evento `request.filter_parameters` incluye mucha información sobre la
>petición, tal y como se puede ver en la documentación de la API sobre el
>[método `sfWebRequest::getRequestContext()`](http://www.symfony-project.org/api/1_3/sfWebRequest#method_getrequestcontext).

La siguiente factoría importante es el enrutamiento. La inicialización del
enrutamiento es muy sencilla, ya que sólo consiste en obtener y establecer una
serie de opciones específicas. Si quieres también puedes modificar este proceso
aprovechando el evento `routing.load_configuration`.

>**NOTE**
>El evento `routing.load_configuration` te da acceso a la instancia del objeto
>con el enrutamiento actual (por defecto es un objeto de tipo
>[`sfPatternRouting`](http://trac.symfony-project.org/browser/branches/1.3/lib/routing/sfPatternRouting.class.php)).
>Las rutas registradas se pueden manipular mediante diferentes métodos.

### Ejemplo de uso del evento `routing.load_configuration`

Gracias a este evento es muy fácil añadir una nueva ruta en la aplicación:

    [php]
    public function setup()
    {
      // ...

      $this->dispatcher->connect('routing.load_configuration', array(
        $this, 'listenToRoutingLoadConfiguration'
      ));
    }

    public function listenToRoutingLoadConfiguration(sfEvent $event)
    {
      $routing = $event->getSubject();

      if (!$routing->hasRouteName('my_route'))
      {
        $routing->prependRoute('my_route', new sfRoute(
          '/my_route', array('module' => 'default', 'action' => 'foo')
        ));
      }
    }

El procesamiento de las URL ocurre justo después de la inicialización mediante
el método [`sfPatternRouting::parse()`](http://www.symfony-project.org/api/1_3/sfPatternRouting#method_parse).
Aunque durante este proceso intervienen varios métodos, lo único que hay que
saber es que para cuando se llega al final del método `parse()`, se ha encontrado
la ruta correcta, se ha instanciado y se le han asociado los parámetros adecuados.

>**NOTE**
>Si quieres conocer más sobre el enrutamiento, puedes leer el capítulo
>*"Enrutamiento avanzado"* de este mismo libro.

Una vez que se han cargado y configurado correctamente todas las factorías, se
notifica el evento `context.load_factories`. Este evento es importante porque
es el primer evento del framework en el que el programador tiene acceso a todos
los objetos de las factorías de Symfony (petición, respuesta, usuario, log,
bases de datos, etc.)

Este es también el punto en el que puedes conectarte a otro evento muy útil
llamado `template.filter_parameters`. Este evento se notifica cada vez que
[`sfPHPView`](http://trac.symfony-project.org/browser/branches/1.3/lib/view/sfPHPView.class.php)
procesa y muestra un archivo, por lo que permite al programador controlar los
parámetros que se le pasan a la plantilla. `sfContext` utiliza este evento para
añadir algunos parámetros útiles a cada plantilla (en concreto, `$sf_context`,
`$sf_request`, `$sf_params`, `$sf_response` y `$sf_user`).

Por tanto, puedes hacer uso del evento `template.filter_parameters` para añadir
parámetros globales adicionales en todas las plantillas.

### Utilizando el evento `template.filter_parameters`

Imagina que todas las plantillas de tu aplicación deben tener acceso a un
objeto determinado, por ejemplo un *helper*. En este caso, puedes añadir el
siguiente código en `ProjectConfiguration`:

    [php]
    public function setup()
    {
      // ...

      $this->dispatcher->connect('template.filter_parameters', array(
        $this, 'templateFilterParameters'
      ));
    }

    public function templateFilterParameters(sfEvent $event, $parameters)
    {
      $parameters['my_helper_object'] = new MyHelperObject();

      return $parameters;
    }

Ahora todas las plantillas tienen acceso a una instancia de `MyHelperObject`
mediante la variable `$my_helper_object`.

### Resumen de `sfContext`

1. Inicialización de `sfContext`
1. Se cargan las factorías
1. Se notifican los siguientes eventos:
 1. [request.filter_parameters](http://www.symfony-project.org/reference/1_3/en/15-Events#chapter_15_sub_request_filter_parameters)
 1. [routing.load_configuration](http://www.symfony-project.org/reference/1_3/en/15-Events#chapter_15_sub_routing_load_configuration)
 1. [context.load_factories](http://www.symfony-project.org/reference/1_3/en/15-Events#chapter_15_sub_context_load_factories)
1. Se añaden los parámetros globales en las plantillas

Gestores de configuración o *Config Handlers*
---------------------------------------------

Los gestores de configuración son la parte esencial del sistema de configuración
de Symfony. La tarea de los gestores de configuración es *entender* el significado
de los archivos de configuración. Cada gestor de configuración es simplemente
una clase que se emplea para transformar la configuración YAML en el código
PHP que realmente se ejecuta cuando es necesario. Cada archivo de configuración
dispone de su propio gestor de configuración que se configura en el
[archivo `config_handlers.yml`](http://trac.symfony-project.org/browser/branches/1.3/lib/config/config/config_handlers.yml).

Los gestores de configuración no procesan los archivos YAML, ya que eso es tarea
de `sfYaml`. En realidad, lo único que hacen los gestores de configuración es
crear una serie de instrucciones PHP en base a la información YAML y guardar
esas instrucciones en un archivo PHP que se incluye después durante la ejecución
de la aplicación. La versión *compilada* de cada archivo de configuración YAML
se encuentra en el directorio de la cache.

El gestor de configuración más utilizado es
[`sfDefineEnvironmentConfigHandler`](http://trac.symfony-project.org/browser/branches/1.3/lib/config/sfDefineEnvironmentConfigHandler.class.php),
que permite definir opciones de configuración diferentes en función del entorno
en el que se ejecute la aplicación. Este gestor se encarga por tanto de obtener
solamente las opciones de configuración que se aplican al entorno actual.

¿No te parece suficiente? Pues echa un vistazo al gestor de configuración
[`sfFactoryConfigHandler`](http://trac.symfony-project.org/browser/branches/1.3/lib/config/sfFactoryConfigHandler.class.php).
Este gestor se encarga de compilar el archivo `factories.yml`, que es uno de los
archivos de configuración más importantes de Symfony. Este gestor de configuración
es muy especial porque convierte un archivo de configuración YAML en el código
PHP que finalmente instanciará los objetos de tipo factoría (todos los componentes
importantes que se han presentado en las secciones anteriores). Este gestor de
configuración es mucho más avanzado que los otros gestores, ¿verdad?

Despachando y ejecutando la petición
------------------------------------

Una vez presentadas las factorías, volvemos a la explicación del proceso que
despacha las peticiones. Tras la inicialización de `sfContext`, se invoca el
método `dispatch()` del controlador,
[`sfFrontWebController::dispatch()`](http://www.symfony-project.org/api/1_3/sfFrontWebController#method_dispatch).

En Symfony el proceso que despacha las peticiones es muy sencillo. De hecho,
`sfFrontWebController::dispatch()` simplemente obtiene el nombre del módulo y
de la acción a partir de los parámetros de la petición y redirige la aplicación
mediante [`sfController::forward()`](http://www.symfony-project.org/api/1_3/sfController#method_forward).

>**NOTE**
>En este punto, si el enrutamiento no puede encontrar ningún módulo o acción
>a partir de la URL, se lanza una excepción de tipo
>[`sfError404Exception`](http://trac.symfony-project.org/browser/branches/1.3/lib/exception/sfError404Exception.class.php),
>que redirige la petición al módulo encargado de procesar la respuesta del
>error 404 (ver información sobre [`sf_error_404_module` y
>`sf_error_404_action`](http://www.symfony-project.org/reference/1_3/en/04-Settings#chapter_04_sub_error_404)).
>Si quieres mostrar un error de tipo 404 en cualquier punto de la aplicación,
>tu también puedes lanzar esta excepción.

El método `forward` realiza muchas comprobaciones previas a la ejecución, además
de preparar la configuración y los datos para la acción se que va a ejecutar.

En primer lugar el controlador comprueba si existe un archivo llamado
[`generator.yml`](http://trac.symfony-project.org/browser/branches/1.3/lib/config/config/generator.yml)
en el módulo actual. Esta comprobación es la primera que se realiza (después de
una limpieza básica del nombre del módulo y de la acción) porque el archivo de
configuración `generator.yml` (si existe) se encarga de generar la clase base
de las acciones del módulo (mediante su
[gestor de configuración, `sfGeneratorConfigHandler`](http://trac.symfony-project.org/browser/branches/1.3/lib/config/sfGeneratorConfigHandler.class.php)).
Esta comprobación es necesaria para el siguiente paso, que comprueba si existen
el módulo y la acción. La comprobación se delega en el controlador, a través
de su método
[`sfController::actionExists()`](http://www.symfony-project.org/api/1_3/sfController#method_actionexists),
que después invoca el método
[`sfController::controllerExists()`](http://www.symfony-project.org/api/1_3/sfController#method_controllerexists).
Una vez más, si el método `actionExists()` falla, se lanza una excepción de tipo
`sfError404Exception`.

>**NOTE**
>El gestor de configuración
>[`sfGeneratorConfigHandler`](http://trac.symfony-project.org/browser/branches/1.3/lib/config/sfGeneratorConfigHandler.class.php)
>es un tipo especial de gestor que se encarga de instanciar la clase generadora
>correcta para tu módulo y la ejecuta. Si quieres conocer más detalladamente los
>gestores de configuración, puedes leer la sección anterior de este mismo
>capítulo y también el
>[capítulo 6 de la Referencia de Symfony](http://www.symfony-project.org/reference/1_3/en/06-Admin-Generator).

En este punto no hay mucho que hacer salvo redefinir el método
[`sfApplicationConfiguration::getControllerDirs()`](http://www.symfony-project.org/api/1_3/sfApplicationConfiguration#method_getcontrollerdirs)
en la clase de configuración de la aplicación. Este método devuelve un array
con los directorios en los que residen los controladores, además de un parámetro
adicional que le indica a Symfony si debe comprobar que los controladores estén
activados mediante la opción de configuración `sf_enabled_modules` del archivo
`settings.yml`. El método `getControllerDirs()` podría tener por ejemplo el
siguiente aspecto:

    [php]
    /**
     * Controllers in /tmp/myControllers won't need to be enabled
     * to be detected
     */
    public function getControllerDirs($moduleName)
    {
      return array_merge(parent::getControllerDirs($moduleName), array(
        '/tmp/myControllers/'.$moduleName => false
      ));
    }

>**NOTE**
>Si la acción no existe, se lanza una excepción de tipo `sfError404Exception`.

El siguiente paso consiste en obtener una instancia del controlador que contiene
la acción. De ello se encarga el método
[`sfController::getAction()`](http://www.symfony-project.org/api/1_3/sfController#method_getaction),
que al igual que `actionExists()` es un intermediario del método
[`sfController::getController()`](http://www.symfony-project.org/api/1_3/sfController#method_getcontroller).
Finalmente, la instancia del controlador se añade al conjunto de acciones o
*action stack*.

>**NOTE**
>El conjunto de acciones es una pila de tipo FIFO (*First In First Out*) que
>contiene todas las acciones que se ejecutan durante la petición. Cada elemento
>de la pila se representa con un objeto de tipo `sfActionStackEntry`. La pila
>siempre está accesible mediante `sfContext::getInstance()->getActionStack()`
>o a través de `$this->getController()->getActionStack()` dentro de una acción.

Después de cargar algo más de configuración, ya será posible ejecutar la acción.
De hecho, es necesario cargar la configuración específica del módulo, que se
puede encontrar en dos lugares diferentes. Primero Symfony busca el archivo
`module.yml` (que se encuentra normalmente en `apps/frontend/modules/miModulo/config/module.yml`),
que como es un archivo de configuración YAML, utiliza la cache de configuración.
Además, este archivo de configuración puede declarar el módulo como *interno*,
utilizando la opción `mod_miModulo_is_internal` que hace que la petición produzca
in error, ya que los módulos internos no se pueden acceder públicamente.

>**NOTE**
>Los módulos internos se utilizaban antes para generar el contenido de los emails
>(por ejemplo mediante `getPresentationFor()`). Ahora se utilizan otras técnicas,
>como los elementos parciales (`$this->renderPartial()`).

Después de cargar el archivo `module.yml`, se comprueba por segunda vez que el
módulo actual está activado. Efectivamente, puedes establecer la opción
`mod_$moduleName_enabled` a `false` si quieres deshabilitar el módulo en este
punto.

>**NOTE**
>Como se ha mencionado, existen dos formas diferentes de activar o desactivar
>un módulo. La diferencia reside en lo que sucede cuando se deshabilita el
>módulo. En el primer caso, cuando está activada la opción `sf_enabled_modules`,
>un módulo desactivado provoca que se lance una excepción de tipo
>[`sfConfigurationException`](http://trac.symfony-project.org/browser/branches/1.3/lib/exception/sfConfigurationException.class.php).
>Esta opción debería utilizarse cuando se deshabilita un módulo permanentemente.
>En el segundo caso, mediante la opción `mod_$moduleName_enabled`, el módulo
>deshabilitado provocará que la aplicación se redirija al módulo deshabilitado
>(ver las opciones [`sf_module_disabled_module` y
>`sf_module_disabled_action`](http://www.symfony-project.org/reference/1_3/en/04-Settings#chapter_04_sub_module_disabled)).
>Esta es la opción que debes utilizar cuando se deshabilita temporalmente un
>módulo.

La última oportunidad para configurar un módulo se encuentra en el archivo
`config.php` (`apps/frontend/modules/yourModule/config/config.php`) donde puedes
incluir cualquier código PHP que quieras que se ejecute dentro del contexto del
método [`sfController::forward()`](http://www.symfony-project.org/api/1_3/sfController#method_forward)
(es decir, que tienes acceso a la instancia de `sfController` mediante la
variable `$this`, ya que el código se ejecuta literalmente dentro de la clase
`sfController`).

### Resumen de cómo se despacha la petición

1. Se invoca [`sfFrontWebController::dispatch()`](http://www.symfony-project.org/api/1_3/sfFrontWebController#method_dispatch)
1. Se invoca [`sfController::forward()`](http://www.symfony-project.org/api/1_3/sfController#method_forward)
1. Se busca un archivo `generator.yml`
1. Se comprueba que el módulo y la acción existen
1. Se obtiene la lista de directorios de los controladores
1. Se obtiene una instancia de la acción
1. Se carga la configuración del módulo mediante `module.yml` y/o `config.php`

La cadena de filtros
--------------------

Ahora que ya se ha realizado toda la configuración, es momento de empezar con
*el trabajo de verdad*, que en este caso consiste en la ejecución de la cadena
de filtros.

>**NOTE**
>La cadena de filtros de Symfony implementa un patrón de diseño llamado
[cadena de responsabilidad](http://es.wikipedia.org/wiki/Chain_of_Responsibility_%28patr%C3%B3n_de_dise%C3%B1o%29).
>Se trata de un patrón de diseño sencillo pero muy potente que permite encadenar
>varias acciones. Cada una de las partes de la cadena puede decidir si la cadena
>debe seguir ejecutándose o no. Cada parte de la cadena también puede ejecutarse
>antes o después del resto de partes de la cadena.

La configuración de la cadena de filtros se obtiene del archivo
[`filters.yml`](http://trac.symfony-project.org/browser/branches/1.3/lib/config/config/filters.yml)
del módulo actual, que es la razón por la que se necesita la instancia de la
acción. En este punto es donde se puede modificar el conjunto de filtros
ejecutados por la cadena. Simplemente se debe tener en cuenta que el filtro
`rendering` siempre debe ser el primero de la lista (como se explicará más
adelante). La configuración de los filtros por defecto es la siguiente:

* [`rendering`](http://trac.symfony-project.org/browser/branches/1.3/lib/filter/sfRenderingFilter.class.php)
* [`security`](http://trac.symfony-project.org/browser/branches/1.3/lib/filter/sfSecurityFilter.class.php)
* [`cache`](http://trac.symfony-project.org/browser/branches/1.3/lib/filter/sfCacheFilter.class.php)
* [`execution`](http://trac.symfony-project.org/browser/branches/1.3/lib/filter/sfExecutionFilter.class.php)

>**NOTE**
>Recomendamos encarecidamente que añadas tus propios filtros entre el filtro
>`security` y el filtro `cache`.

### El filtro `security`

Como el filtro `rendering` espera a que todos los demás filtros acaben antes de
realizar su trabajo, el primer filtro que realmente se ejecuta es el filtro
`security`. Este filtro asegura que se cumpla la configuración del archivo
[`security.yml`](http://trac.symfony-project.org/browser/branches/1.3/lib/config/config/security.yml).
En concreto, este filtro redirige a los usuarios sin autenticar al módulo y
acción `login` y a los usuarios sin permisos suficientes al módulo y acción
`secure`. Este filtro sólo se ejecuta si la seguridad está activada para esta
acción.

### El filtro `cache`

A continuación se ejecuta el filtro `cache`, que aprovecha su capacidad de evitar
la ejecución del resto de filtros de la cadena. Efectivamente, si la cache está
activada y si la página solicitada por el usuario está guardada en la cache, la
acción ya no se ejecuta. Obviamente lo anterior sólo es válido para las páginas
que se pueden guardar enteras en la cache, que no suele ser el caso para la
mayoría de páginas.

Este filtro contiene además otra parte que se ejecuta después del filtro
`execution` y justo antes del filtro `rendering`. Este código es responsable de
establecer correctamente las cabeceras HTTP relacionadas con la cache, además
de guardar si es necesario la página en la cache gracias al método
[`sfViewCacheManager::setPageCache()`](http://www.symfony-project.org/api/1_3/sfViewCacheManager#method_setpagecache).

### El filtro `execution`

El último filtro, pero no por eso menos importante, se denomina `execution` y
se encarga de ejecutar la lógica de negocio y su vista asociada.

Todo comienza cuando el filtro comprueba la cache para la acción actual. Si se
encuentra en la cache, la acción no se ejecuta y se pasa directamente a ejecutar
la vista denominada `Success`.

Si no se encuentra la acción en la cache, se ejecuta el método `preExecute()`
del controlador y después se ejecuta la propia acción. La ejecución se realiza
invocando el método
[`sfActions::execute()`](http://www.symfony-project.org/api/1_3/sfActions#method_execute)
de la instancia de la acción. Este método simplemente comprueba que la acción
se puede ejecutar y en caso afirmativo la ejecuta. Al volver al filtro, se
ejecuta el método `postExecute()` de la acción.

>**NOTE**
>El valor devuelto por tu acción es muy importante, ya que determina la vista
>que se ejecuta. Por defecto, cuando no se devuelve ningún valor, se sobreentiende
>que el valor devuelto es `sfView::SUCCESS` (lo que se traduce en el valor `Success`
>y de ahí el nombre de las plantillas: `indexSuccess.php`).

Siguiendo con el proceso, ahora le toca el turno a la vista. El filtro comprueba
si la acción ha devuelto alguno de los dos siguientes valores especiales:
`sfView::HEADER_ONLY` y `sfView::NONE`. Cada uno de ellos hace lo que implica
su nombre: devuelve solamente las cabeceras HTTP (mediante el método interno
[`sfWebResponse::setHeaderOnly()`](http://www.symfony-project.org/api/1_3/sfWebResponse#method_setheaderonly))
o no muestra ninguna página.

>**NOTE**
>Los nombres internos disponibles paras las vistas son: `ALERT`, `ERROR`, `INPUT`,
>`NONE` y `SUCCESS`. Además de estos valores preconfigurados, puedes utilizar
>cualquier otro valor propio.

Una vez que ya se ha determinado que la acción va a mostrar una página, se puede
ejecutar el último paso del filtro: la ejecución de la vista seleccionada.

En primer lugar se obtiene un objeto de tipo [`sfView`](http://trac.symfony-project.org/browser/branches/1.3/lib/view/sfView.class.php)
a través del método [`sfController::getView()`](http://www.symfony-project.org/api/1_3/sfController#method_getview).
Este objeto se puede obtener de dos maneras diferentes. La primera es mediante
un objeto propio de vista para esta acción específica, llamado `actionSuccessView`
o `module_actionSuccessView` y disponible en el archivo
`apps/frontend/modules/module/view/actionSuccessView.class.php` (suponiendo que
la acción se llame `action` y el módulo se llame `module`). En cualquier otro
caso se emplea la clase definida en la opción de configuración `mod_module_view_class`.
El valor inicial de esta opción es [`sfPHPView`](http://trac.symfony-project.org/browser/branches/1.3/lib/view/sfPHPView.class.php).

>**TIP**
>Si utilizas un clase propia para la vista es posible ejecutar lógica específica
>de la vista mediante el método [`sfView::execute()`](http://www.symfony-project.org/api/1_3/sfView#method_execute).
>De esta forma podrías por ejemplo instanciar tu propio motor de plantillas.

Existen tres modos diferentes de generar la vista:

1. `sfView::RENDER_NONE`": equivalente a `sfView::NONE`, por lo que no se genera
   ninguna vista.
1. `sfView::RENDER_VAR`: prepara la presentación de la acción, que después se
   puede acceder a través del método [`sfActionStackEntry::getPresentation()`](http://www.symfony-project.org/api/1_3/sfActionStackEntry#method_getpresentation).
1. `sfView::RENDER_CLIENT`, genera la vista e incluye el contenido de la
   respuesta. Se trata del modo por defecto.

>**NOTE**
>En la práctica, el modo de generación de la vista sólo se utiliza a través del
>método [`sfController::getPresentationFor()`](http://www.symfony-project.org/api/1_3/sfController#method_getpresentationfor)
>que devuelve la vista generada para el módulo y acción indicados.

### El filtro `rendering`

Aunque el proceso está prácticamente finalizado, todavía queda un último paso.
La cadena de filtros se ha ejecutado en su totalidad pero el filtro `rendering`
sigue esperando a que el resto de filtros terminen para poder hacer su trabajo.
En concreto, el filtro `rendering` envía el contenido de la respuesta al navegador
utilizando [`sfWebResponse::send()`](http://www.symfony-project.org/api/1_3/sfWebResponse#method_send).

### Resumen de la ejecución de la cadena de filtros

1. La cadena de filtros se instancia utilizando el archivo de configuración `filters.yml`
1. El filtro `security` comprueba las credenciales y las autorizaciones
1. El filtro `cache` gestiona la cache de la página actual
1. El filtro `execution` ejecuta la acción
1. El filtro `rendering` envía la respuesta mediante `sfWebResponse`

Resumen general
---------------

1. Se obtiene la configuración de la aplicación
1. Se crea la instancia de `sfContext`
1. Se inicializa `sfContext`
1. Se cargan las factorías
1. Se notifican los siguientes eventos
 1. ~`request.filter_parameters`~
 1. ~`routing.load_configuration`~
 1. ~`context.load_factories`~
1. Se añaden los parámetros globales en las plantillas
1. Se ejecuta [`sfFrontWebController::dispatch()`](http://www.symfony-project.org/api/1_3/sfFrontWebController#method_dispatch)
1. Se ejecuta [`sfController::forward()`](http://www.symfony-project.org/api/1_3/sfController#method_forward)
1. Se busca el archivo `generator.yml`
1. Se comprueba si existen el módulo y acción
1. Se obtienen los directorios de los controladores
1. Se obtiene una instancia de la acción
1. Se carga la configuración del módulo mediante `module.yml` y/o `config.php`
1. Se instancia la cadena de filtros con la configuración del archivo `filters.yml`
1. El filtro `security` comprueba las credenciales y las autorizaciones
1. El filtro `cache` gestiona la cache de la página actual
1. El filtro `execution` ejecuta la acción
1. El filtro `rendering` envía la respuesta mediante `sfWebResponse`

Conclusión
----------

¡Y eso es todo! La petición ya se ha procesado y la aplicación está lista para
atender a la siguiente. Como puedes imaginar, se podría escribir un libro entero
sobre los procesos internos de Symfony, así que este capítulo sólo ha sido una
breve introducción. Te invitamos a que explores el código fuente de Symfony, ya
que se trata de la mejor forma de aprender el funcionamiento interno de cualquier
framework o librería.
