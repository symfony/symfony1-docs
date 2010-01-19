Descubriendo el poder de la línea de comandos
=============================================

*por Geoffrey Bachelet*

Symfony 1.1 introdujo una herramienta de línea de comandos moderna, muy potente y
mucho más flexible que el anterior sistema de tareas basado en la librería Pake.
Con cada nueva versión se han seguido introduciendo mejoras hasta llegar al
sistema de tareas actual

Muchos programadores web no consideran importantes a las tareas, ya que desconocen
todas sus posibilidades. En este capítulo se repasan las tareas desde el principio
del todo hasta los conceptos avanzados, mostrando cómo pueden ayudarte en el
trabajo diario y cómo puedes aprovecharlas al máximo.

Las tareas en pocas palabras
----------------------------

Una tarea es un *trozo* de código que se ejecuta mediante la línea de comandos
utilizando el script `symfony` de PHP que se encuentra en la raíz del proyecto.
Seguramente ya has ejecutado varias tareas, sobre todo la famosa tarea
`cache:clear` (también conocida como `cc`) escribiendo lo siguiente en una
consola de comandos:

    $ php symfony cc

Symfony incluye varias tareas propias de propósito general y para usos muy
diferentes. Si ejecutas el comando `symfony` sin ninguna opción ni argumento
puedes ver la lista completa de tareas:

    $ php symfony

La salida será algo similar a lo siguiente (se muestra sólo una parte):

    Usage:
      symfony [options] task_name [arguments]

    Options:
      --help        -H  Display this help message.
      --quiet       -q  Do not log messages to standard output.
      --trace       -t  Turn on invoke/execute tracing, enable full backtrace.
      --version     -V  Display the program version.
      --color           Forces ANSI color output.
      --xml             To output help as XML

    Available tasks:
      :help                        Displays help for a task (h)
      :list                        Lists tasks
    app
      :routes                      Displays current routes for an application
    cache
      :clear                       Clears the cache (cc, clear-cache)

Probablemente ya te has dado cuenta de que las tareas están agrupadas. Los grupos
de tareas se llaman *namespaces* y el nombre de las tareas normalmente está
compuesto de un *namespace* seguido del nombre particular de la tarea (salvo
las tareas `help` y `list` que no tienen *namespace*). Este esquema de nombrado
permite una categorización sencilla de las tareas, por lo que es aconsejable
elegir un *namespace* significativo para todas tus tareas.

Creando tus propias tareas
--------------------------

Empezar a crear tareas de Symfony cuesta muy poco tiempo. Lo único que debes
hacer es crear una tarea, darle un nombre, añadir algo de código y ya puedes
ejecutar tu propia tarea. Vamos a crear una tarea muy sencilla de tipo *¡Hola
Mundo!* en un archivo llamado `lib/task/sayHelloTask.class.php`:

    [php]
    class sayHelloTask extends sfBaseTask
    {
      public function configure()
      {
        $this->namespace = 'say';
        $this->name      = 'hello';
      }

      public function execute($arguments = array(), $options = array())
      {
        echo '¡Hola Mundo!';
      }
    }

Ahora ya puedes ejecutar la tarea con el siguiente comando:

    $ php symfony say:hello

Esta tarea simplemente muestra *¡Hola Mundo!*, pero recuerda que acabamos de
empezar. Las tareas no están pensadas para mostrar sus mensajes mediante
instrucciones `echo` o `print` sencillas. Las tareas heredan de la clase
`sfBaseTask`, por lo que tienen a sus disposición un montón de métodos útiles,
incluyendo el método `log()` encargado de mostrar mensajes e información:

    [php]
    public function execute($arguments = array(), $options = array())
    {
      $this->log('¡Hola Mundo!');
    }

Como la ejecución de una tarea puede derivar en la ejecución de varias tareas
diferentes que a su vez muestran mensajes, es recomendable utilizar el método
`logSection()` al mostrar mensajes:

    [php]
    public function execute($arguments = array(), $options = array())
    {
      $this->logSection('say', '¡Hola Mundo!');
    }

Al método `execute()` de las tareas siempre se le pasan dos parámetros llamados
`$arguments` y `$options`. Se  de las variables que guardan todos los parámetros
y todas las opciones que se pasan al ejecutar una tarea. Más adelante se explican
con detalle los argumentos y las opciones, pero ahora vamos a hacer la tarea un
poco más interactiva permitiendo que el usuario especifique la persona a la que
queremos saludar:

    [php]
    public function configure()
    {
      $this->addArgument('who', sfCommandArgument::OPTIONAL, '¿A quién quieres saludar?', 'Mundo');
    }

    public function execute($arguments = array(), $options = array())
    {
      $this->logSection('say', '¡Hola '.$arguments['who'].'!');
    }

Ahora, cuando se vuelve a ejecutar la tarea:

    $ php symfony say:hello Geoffrey

Se muestra un mensaje diferente:

    >> say       ¡Hola Geoffrey!

Como añadir un argumento ha sido muy sencillo, vamos a incluir en la tarea algo
de metainformación, como por ejemplo la descripción de su propósito. Para ello,
utiliza las propiedades `briefDescription` y `description`:

    [php]
    public function configure()
    {
      $this->namespace           = 'say';
      $this->name                = 'hello';
      $this->briefDescription    = 'Hola Mundo sencillo';

      $this->detailedDescription = <<<EOF
    La tarea [say:hello|INFO] es una implementación del clásico ejemplo
    Hola Mundo utilizando el sistema de tareas de Symfony.

      [./symfony say:hello|INFO]

    Puedes utilizar esta misma tarea para saludar a alguien mediante el
    argumento [--who|COMMENT].
    EOF;

      $this->addArgument('who', sfCommandArgument::OPTIONAL, '¿A quién quieres saludar?', 'Mundo');
    }

Como puedes ver, la descripción de la tarea se puede decorar un poco mediante
una sintaxis especial. Comprueba el resultado haciendo uso de la ayuda de las
tareas de Symfony:

    $ php symfony help say:hello

El sistema de opciones
----------------------

Las opciones de las tareas de Symfony están organizadas en dos grupos diferentes,
las opciones y los argumentos.

### Opciones

Las opciones son las que se pasan mediante guiones medios. Puedes añadirlas en
la línea de comandos en cualquier orden. Pueden ir acompañadas de un valor o no,
en cuyo caso actúan como un valor *booleano*. Normalmente las opciones disponen
tanto de una versión larga como de una versión corta. La forma larga normalmente
se indica mediante dos guiones medios, mientras que la opción corta sólo requiere
de un guión medio.

Algunos ejemplos de opciones habituales son la de ayuda (`--help` o `-h`), la
opción de *verbosidad* (`--quiet` o `-q`) y la de versión (`--version` o `-V`).

>**NOTE**
>Las opciones de definen mediante la clase `sfCommandOption` y se guardan en
>una clase de tipo `sfCommandOptionSet`.

### Argumentos

Los argumentos permiten proporcionar información en la línea de comandos. Se
deben especificar en el mismo orden en que se han definido y si incluyen algún
espacio en blanco en su interior, se deben encerrar mediante comillas (también
puedes aplicar el mecanismo de escape en los espacios). Los argumentos pueden
ser opcionales u obligatorios, en cuyo caso se debe especificar un valor por
defecto al definir el argumento.

>**NOTE**
>Los argumentos se definen mediante la clase `sfCommandArgument` y se guardan
>en una clase de tipo `sfCommandArgumentSet`.

### Argumentos y opciones por defecto

Las tareas de Symfony incluyen por defecto varios argumentos y opciones:

  * `--help` (-`H`): muestra este mensaje de ayuda.
  * `--quiet` (`-q`): no muestra mensajes por pantalla.
  * `--trace` `(-t`): activa la traza de ejecución, permitiendo accede a la traza completa.
  * `--version` (`-V`): muestra la versión del programa.
  * `--color`: fuerza que los mensajes muestren colores ANSI.

### Opciones especiales

El sistema de tareas de Symfony también incluye dos opciones especiales llamadas
`application` y `env`.

La opción `application` es necesaria cuando quieres acceder a una instancia de
`sfApplicationConfiguration` en lugar de una instancia de `sfProjectConfiguration`.
Este es por ejemplo el caso cuando la tarea va a generar URL, ya que el enrutamiento
normalmente está asociado con la aplicación.

Cuando a una tarea se le pasa una opción llamada `application`, Symfony la
detecta automáticamente y crea el correspondiente objeto `sfApplicationConfiguration`
en vez del objeto `sfProjectConfiguration`. Como a las opciones se les puede
establecer un valor por defecto, para aprovechar esta característica no es necesario
indicar el nombre de la aplicación cada vez que se ejecuta la tarea.

Por su parte, la opción `env` controla el entorno en el que se ejecuta la
tarea. Cuando no se indica esta opción, se utiliza el entorno `test`. Al igual
que con la opción `application`, puedes establecer un valor por defecto para
la opción `env` y Symfony lo utilizará automáticamente.

Como `application` y `env` no están incluidas dentro de la lista de opciones por
defecto, es necesario incluirlas a mano en la tarea:

    [php]
    public function configure()
    {
      $this->addOptions(array(
        new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name', 'frontend'),
        new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
      ));
    }

Salvo que se indique lo contrario, la tarea del ejemplo anterior se ejecuta en
el entorno `dev` de la aplicación `frontend`.

Accediendo a la base de datos
-----------------------------

Acceder a la base de datos desde una tarea de Symfony es algo tan sencillo como
crear una instancia de la clase `sfDatabaseManager`:

    [php]
    public function execute($arguments = array(), $options = array())
    {
      $databaseManager = new sfDatabaseManager($this->configuration);
    }

También se puede acceder directamente al objeto de la conexión del ORM:

    [php]
    public function execute($arguments = array(), $options = array())
    {
      $databaseManager = new sfDatabaseManager($this->configuration);
      $connection = $databaseManager->getDatabase()->getConnection();
    }

¿Qué sucede si se dispone de varias conexiones definidas en el archivo `databases.yml`?
En este caso se puede añadir en la tarea una opción llamada `connection`:

    [php]
    public function configure()
    {
      $this->addOption('connection', sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'doctrine');
    }

    public function execute($arguments = array(), $options = array())
    {
      $databaseManager = new sfDatabaseManager($this->configuration);
      $connection = $databaseManager->getDatabase(isset($options['connection']) ? $options['connection'] : null)->getConnection();
    }

Como es evidente, esta opción de conexión puede tener un valor por defecto de
forma que no se obligue a indicarlo cada vez que se ejecuta la tarea. Utilizando
solamente el código anterior ya es posible manipular los modelos de datos como
si estuvieras dentro de una aplicación de Symfony.

>**NOTE**
>Si la tarea procesa muchos objetos del ORM, debes tener en cuenta que tanto
>Propel como Doctrine sufren un error de PHP muy conocido relacionado con las
>referencias cíclicas y el recolector de basura que provoca consumos masivos
>de memoria. Este error se ha corregido parcialmente en PHP 5.3.

Enviando emails
---------------

El envío de emails es uno de los usos más comunes de las tareas. Hasta Symfony
1.3 el envío de emails no era tan sencillo como debía ser. Afortunadamente la
situación ha cambiado y ahora Symfony está completamente integrado con
[Swift Mailer](http://swiftmailer.org/), una librería muy completa para el
envío de emails.

El objeto *mailer* está disponible en las tareas de Symfony mediante el método
`sfCommandApplicationTask::getMailer()`. Por tanto, acceder al *mailer* y enviar
emails es tan sencillo como se muestra en el siguiente ejemplo:

    [php]
    public function execute($arguments = array(), $options = array())
    {
      $mailer = $this->getMailer();
      $mailer->composeAndSend($remitente, $destinatario, $asunto, $contenido);
    }

>**NOTE**
>Como la configuración del *mailer* se obtiene a partir de la configuración de la
>aplicación, para utilizar el *mailer* es obligatorio que la tarea utilice una
>opción llamada `application`.

-

>**NOTE**
>Si utilizas la estrategia de envío `spool`, los emails sólo se envían cuando
>se ejecuta la tarea `project:send-emails`.

Normalmente el contenido del email no se encuentra en una variable mágica
llamada `$contenido` esperando a que lo envíes, sino que debes generarlo de alguna
forma antes de enviarlo. En Symfony no existe una forma estándar de generar el
contenido de los emails, pero puedes seguir los consejos mostrados en las
próximas secciones para facilitar tu trabajo.

### Delegar la generación del contenido

Se puede crear en la tarea un método protegido que devuelva el contenido del
email que se va a enviar:

    [php]
    public function execute($arguments = array(), $options = array())
    {
      $this->getMailer()->composeAndsend($remitente, $destinatario, $asunto, $this->getContenido());
    }

    protected function getContenido()
    {
      return 'Hola Mundo';
    }

### Utilizar el plugin decorator del Swift Mailer

Swift Mailer incluye un plugin llamado
[`Decorator`](http://swiftmailer.org/docs/decorator-plugin) que básicamente es
un sistema de plantillas muy simple pero eficiente. Este plugin recibe los datos
que dependen del destinatario y los sustituye en el contenido del email preparado.

Puedes leer la [documentación de Swift Mailer](http://swiftmailer.org/docs/)
para obtener más información.

### Utilizar un sistema de plantillas externo

Integrar una librería externa de plantillas es muy sencillo. Se puede utilizar
por ejemplo el nuevo componente de plantillas que forma parte del proyecto
*Symfony Components*. Descarga el código del componente, copialo por ejemplo en
el directorio `lib/vendor/templating/` y utiliza el siguiente código en la
plantilla:

    [php]
    protected function getContenido($template, $vars = array())
    {
      $engine = $this->getTemplateEngine();
      return $engine->render($template, $vars);
    }

    protected function getTemplateEngine()
    {
      if (is_null($this->templateEngine))
      {
        $loader = new sfTemplateLoaderFilesystem(sfConfig::get('sf_app_dir').'/templates/emails/%s.php');
        $this->templateEngine = new sfTemplateEngine($loader);
      }

      return $this->templateEngine;
    }

### Combinando lo mejor de cada uno

Todavía existe otra forma de hacerlo. El plugin `Decorator` de Swift Mailer es
genial porque se encarga de realizar las sustituciones que dependen del
destinatario del mensaje. Esto significa que en el contenido del mensaje se
definen los elementos que dependen del destinatario y Swift Mailer se encarga
de reemplazar los tokens con el valor correcto en función del destinatario del
email que se envía. El siguiente código muestra cómo integrarlo con el componente
de las plantillas:

    [php]
    public function execute($arguments = array(), $options = array())
    {
      $message = Swift_Message::newInstance();

      // obtiene la lista de usuarios
      foreach($users as $user)
      {
        $replacements[$user->getEmail()] = array(
          '{username}'      => $user->getEmail(),
          '{specific_data}' => $user->getSomeUserSpecificData(),
        );

        $message->addTo($user->getEmail());
      }

      $this->registerDecorator($replacements);

      $message
        ->setSubject('User specific data for {username}!')
        ->setBody($this->getMessageBody('user_specific_data'));

      $this->getMailer()->send($message);
    }

    protected function registerDecorator($replacements)
    {
      $this->getMailer()->registerPlugin(new Swift_Plugins_DecoratorPlugin($replacements));
    }

    protected function getMessageBody($template, $vars = array())
    {
      $engine = $this->getTemplateEngine();
      return $engine->render($template, $vars);
    }

    protected function getTemplateEngine($replacements = array())
    {
      if (is_null($this->templateEngine))
      {
        $loader = new sfTemplateLoaderFilesystem(sfConfig::get('sf_app_template_dir').'/emails/%s.php');
        $this->templateEngine = new sfTemplateEngine($loader);
      }

      return $this->templateEngine;
    }

El archivo `apps/frontend/templates/emails/user_specific_data.php` contiene el
siguiente código:

    Hi {username}!

    We just wanted to let you know your specific data:

    {specific_data}

¡Y eso es todo! Ahora ya dispones un completo sistema de plantillas para crear
el contenido de tus emails.

Generando URL
-------------

Cuando se crean emails es habitual generar URL que dependen de la configuración
del enrutamiento. Afortunadamente la generación de URL también se ha simplificado
al máximo en Symfony 1.3 porque dentro de una tarea puedes acceder directamente
al enrutamiento de la aplicación actual con el método `sfCommandApplicationTask::getRouting()`:

    [php]
    public function execute($arguments = array(), $options = array())
    {
      $routing = $this->getRouting();
    }

>**NOTE**
>Como el enrutamiento depende de la aplicación, debes asegurarte de que la
>tarea tenga acceso a la configuración de la aplicación, ya que de otra forma
>no es posible generar URL con el sistema de enrutamiento.
>
>Puedes leer la sección *Opciones especiales* para aprender cómo obtener dentro
>de una tarea la configuración de la aplicación automáticamente.

Ahora que se ha obtenido la instancia del enrutamiento, es muy sencillo generar
cualquier URL con el método `generate()`:

    [php]
    public function execute($arguments = array(), $options = array())
    {
      $url = $this->getRouting()->generate('default', array('module' => 'foo', 'action' => 'bar'));
    }

El primer argumento es el nombre de la ruta y el segundo argumento es un array
con los parámetros de la ruta. El código anterior genera una URL relativa, que
seguramente no es lo que queremos hacer. Desafortunadamente no es posible generar
URL absolutas dentro de una tarea porque no se dispone del objeto `sfWebRequest`
con el que obtener el *host*.

Una forma sencilla de evitar este problema consiste en definir el *host* dentro
del archivo de configuración `factories.yml`:

    [yml]
    all:
      routing:
        class: sfPatternRouting
        param:
          generate_shortest_url:            true
          extra_parameters_as_query_string: true
          context:
            host: example.org

El *host* se ha añadido como una opción llamada `context_host`. Esta es la opción
que utiliza el enrutamiento al generar la URL absoluta:

    [php]
    public function execute($arguments = array(), $options = array())
    {
      $url = $this->getRouting()->generate('my_route', array(), true);
    }

Accediendo al sistema de internacionalización
---------------------------------------------

No todas las factorías son tan fáciles de acceder como el *mailer* y el
enrutamiento. Si se quiere por ejemplo internacionalizar las tareas, es preciso
acceder al sistema de internacionalización o i18n de Symfony. Para ello, se
emplea la clase `sfFactoryConfigHandler`:

    [php]
    protected function getI18N($culture = 'en')
    {
      if (!$this->i18n)
      {
        $config = sfFactoryConfigHandler::getConfiguration($this->configuration->getConfigPaths('config/factories.yml'));
        $class  = $config['i18n']['class'];

        $this->i18n = new $class($this->configuration, null, $config['i18n']['param']);
      }

      $this->i18n->setCulture($culture);

      return $this->i18n;
    }

En primer lugar se utiliza una estrategia sencilla de cache para no tener que
reconstruir el componente i18n cada vez que se utilice. A continuación, haciendo
uso de `sfFactoryConfigHandler`, se obtiene la configuración del componente para
instanciarlo. Por último, se establece la configuración de la cultura y la tarea
ya tiene acceso a la internacionalización:

    [php]
    public function execute($arguments = array(), $options = array())
    {
      $this->log($this->getI18N('fr')->__('some translated text!'));
    }

Obviamente no es muy cómodo pasar la cultura en cada llamada, sobre todo cuando
no se modifica mucho en una misma tarea. La próxima sección explica precisamente
cómo conseguirlo.

Refactorizando las tareas
-------------------------

Como enviar emails (y crear su contenido) y generar URL son dos actividades muy
comunes, puede ser una buena idea crear una tarea base que proporcione estas
dos características a cualquier tarea que las necesite. Para conseguirlo, crea
una clase base en tu proyecto en el archivo `lib/task/sfBaseEmailTask.class.php`.

    [php]
    class sfBaseEmailTask extends sfBaseTask
    {
      protected function registerDecorator($replacements)
      {
        $this->getMailer()->registerPlugin(new Swift_Plugins_DecoratorPlugin($replacements));
      }

      protected function getMessageBody($template, $vars = array())
      {
        $engine = $this->getTemplateEngine();
        return $engine->render($template, $vars);
      }

      protected function getTemplateEngine($replacements = array())
      {
        if (is_null($this->templateEngine))
        {
          $loader = new sfTemplateLoaderFilesystem(sfConfig::get('sf_app_template_dir').'/templates/emails/%s.php');
          $this->templateEngine = new sfTemplateEngine($loader);
        }

        return $this->templateEngine;
      }
    }

Ya que estamos en ello, vamos a automatizar también la creación de las opciones
de la tarea. Para ello, añade los siguientes métodos en la clase `sfBaseEmailTask`:

    [php]
    public function configure()
    {
      $this->addOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application', 'frontend');
    }

    protected function generateUrl($route, $params = array())
    {
      return $this->getRouting()->generate($route, $params, true);
    }

Utilizando el método `configure()` se pueden añadir opciones comunes a todas
las tareas que hereden de esta clase. El único inconveniente es que cualquier
clase que herede de `sfBaseEmailTask` obligatoriamente tendrá que invocar el
método `parent::configure` dentro de su propio método `configure()`. De todas
formas, se trata de un inconveniente menor en comparación de todo lo que se
gana a cambio.

Vamos a refactorizar a continuación el código de acceso a la internacionalización
que se utilizó en la sección anterior:

    [php]
    public function configure()
    {
      $this->addOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application', 'frontend');
      $this->addOption('culture', null, sfCommandOption::PARAMETER_REQUIRED, 'The culture', 'en');
    }

    protected function getI18N()
    {
      if (!$this->i18n)
      {
        $config = sfFactoryConfigHandler::getConfiguration($this->configuration->getConfigPaths('config/factories.yml'));
        $class  = $config['i18n']['class'];

        $this->i18n = new $class($this->configuration, null, $config['i18n']['param']);

        $this->i18n->setCulture($this->commandManager->getOptionValue('culture'));
      }

      return $this->i18n;
    }

    protected function changeCulture($culture)
    {
      $this->getI18N()->setCulture($culture);
    }

    protected function process(sfCommandManager $commandManager, $options)
    {
      parent::process($commandManager, $options);
      $this->commandManager = $commandManager;
    }

El problema que debemos resolver es que no se pueden acceder a las opciones y
argumentos fuera del contexto del método `execute()`. Por tanto, se redefine el
método `process()` para incluir el gestor de opciones dentro de la clase. El
gestor de opciones se encarga, como su propio nombre indica, de gestionar los
argumentos y opciones de la tarea. Para obtener por ejemplo el valor de una
opción, se utiliza el método `getOptionValue()`.

Ejecutando una tarea dentro de otra tarea
-----------------------------------------

Otra forma alternativa de refactorizar las tareas consiste en incluir una tarea
dentro de otra. Esta técnica es muy sencilla gracias a los métodos
`sfCommandApplicationTask::createTask()` y `sfCommandApplicationTask::runTask()`.

El método `createTask()` crea una instancia de la tarea indicada. Si se pasa el
nombre de una tarea como si se ejecutara en la línea de comandos, este método
devuelve una instancia de esa tarea lista para ejecutarla:

    [php]
    $task = $this->createTask('cache:clear');
    $task->run();

Si no tienes muchas ganas de trabajar, el método `runTask()` puede hacerlo todo
a la vez:

    [php]
    $this->runTask('cache:clear');

También es posible pasar argumentos y opciones (en este orden):

    [php]
    $this->runTask('plugin:install', array('sfGuardPlugin'), array('install_deps' => true));

Embeber unas tareas dentro de otras es muy útil para crear tareas complejas a
partir de tareas más sencillas. Se pueden combinar por ejemplo varias tareas
en una gran tarea llamada `project:clean` y que se ejecute después de cada
instalación:

    [php]
    $tasks = array(
      'cache:clear',
      'project:permissions',
      'log:rotate',
      'plugin:publish-assets',
      'doctrine:build-model',
      'doctrine:build-forms',
      'doctrine:build-filters',
      'project:optimize',
      'project:enable',
    );

    foreach($tasks as $task)
    {
      $this->run($task);
    }

Manipulando el sistema de archivos
----------------------------------

Symfony incluye una abstracción sencilla del sistema de archivos llamada
`sfFilesystem` y que permite la ejecución de operaciones sencillas sobre archivos
y directorios. Dentro de una tarea se puede acceder mediante `$this->getFilesystem()`
e incluye los siguientes métodos:

* `sfFilesystem::copy()`, copia un archivo
* `sfFilesystem::mkdirs()`, crea directorios de forma recursiva
* `sfFilesystem::touch()`, crea un archivo
* `sfFilesystem::remove()`, borra un archivo o un directorio
* `sfFilesystem::chmod()`, modifica los permisos de un archivo o directorio
* `sfFilesystem::rename()`, renombra un archivo o directorio
* `sfFilesystem::symlink()`, crea un enlace simbólico a un directorio
* `sfFilesystem::relativeSymlink()`, crea un enlace simbólico relativo con un directorio
* `sfFilesystem::mirror()`, realiza una copia de una estructura de directorios
* `sfFilesystem::execute()`, ejecuta cualquier comando de la shell

Además, `sfFilesystem` incluye un método muy útil llamado `replaceTokens()` y
que se va a presentar en la siguiente sección.

Generando archivos mediante *esqueletos*
----------------------------------------

Otro de los usos comunes de las tareas es la generación de archivos, que se
realiza mediante *esqueletos* y el método `sfFilesystem::replaceTokens()`. Como
su propio nombre sugiere, este método reemplaza *tokens* dentro de un conjunto
de archivos. Si se le pasa un array con los archivos y una lista de *tokens*,
reemplaza todas las apariciones de cada *token* en todos los archivos por su
correspondiente valor.

Para comprender mejor la utilidad de este método, vamos a reescribir de forma
parcial una tarea existente llamada `generate:module`. Para simplificar el
ejemplo, sólo nos vamos a fijar en el método `execute()` de esta tarea, por lo
que suponemos que se ha configurado correctamente con todas las opciones necesarias.
También nos vamos a olvidar de la validación.

Antes de crear la tarea, es necesario crear el *esqueleto* de los archivos y
directorios que se van a crear, definiéndolos por ejemplo dentro del directorio
`data/skeleton/`:

    data/skeleton/
      module/
        actions/
          actions.class.php
        templates/

El *esqueleto* de `actions.class.php` debería tener el siguiente aspecto:

    [php]
    class %moduleName%Actions extends %baseActionsClass%
    {
    }

El primer paso de nuestra tarea consiste en copiar toda la estructura de archivos
y directorios en el lugar que corresponda:

    [php]
    $moduleDir = sfConfig::get('sf_app_module_dir').$options['module'];
    $finder    = sfFinder::type('any');
    $this->getFilesystem()->mirror(sfConfig::get('sf_data_dir').'/skeleton/module', $moduleDir, $finder);

Ahora ya es posible reemplazar los *tokens* del archivo `actions.class.php`:

    [php]
    $tokens = array(
      'moduleName'       => $options['module'],
      'baseActionsClass' => $options['base-class'],
    );

    $finder = sfFinder::type('file');
    $this->getFilesystem()->replaceTokens($finder->in($moduleDir), '%', '%', $tokens);

¡Y esto es todo! Ya hemos creado nuestro nuevo módulo y lo hemos personalizado
mediante el reemplazo de tokens.

>**NOTE**
>La tarea `generate:module` de Symfony busca realmente en el directorio
>`data/skeleton/` por si el programador ha definido algún *esqueleto* propio,
>así que cuidado con los cambios que realices.

Utilizando la opción dry-run
----------------------------

En ocasiones quieres ver el resultado de una tarea sin ejecutarla realmente. A
continuación se explican un par de trucos para conseguirlo.

En primer lugar, te recomendamos que utilices para esta opción un nombre estándar
que todo el mundo conoce y que por tanto, todo el mundo es capaz de interpretar
correctamente. Este nombre es `dry-run` y hasta la versión Symfony 1.3 la
clase `sfCommandApplication` añadía por defecto una opción llamada `dry-run`,
pero ahora debes añadirla manualmente (idealmente la añadirías en una clase base
como la mostrada en las secciones anteriores):

    [php]
    $this->addOption(new sfCommandOption('dry-run', null, sfCommandOption::PARAMETER_NONE, 'Executes a dry run');

Ahora ya puedes invocar la tarea de la siguiente forma:

    ./symfony my:task --dry-run

La opción `dry-run` indica que la tarea no debe realizar ningún cambio. Estas
últimas palabras son realmente las importantes: *"no debe realizar ningún cambio"*.
Cuando se ejecuta en el modo `dry-run`, la tarea debe dejar todo tal y como se
encontraba antes de la ejecución, incluyendo entre otros:

* La base de datos: no insertes, actualices o borres ningún registro de la base
  de datos. Esto se puede conseguir mediante una transacción.
* El sistema de archivos: no crees, modifiques ni borres ningún archivo o directorio
  del sistema de archivos.
* Envío de emails: no envíes emails o envíalos a una dirección de prueba.

A continuación se muestra un ejemplo sencillo del uso de la opción `dry-run`:

    [php]
    $connection->beginTransaction();

    // modifica la información de la base de datos

    if ($options['dry-run'])
    {
      $connection->rollBack();
    }
    else
    {
      $connection->commit();
    }

Creando pruebas unitarias
-------------------------

Como las tareas pueden hacer tantas cosas diferentes, no es sencillo crear
pruebas unitarias. Por consiguiente, no existe una única forma de probar las
tareas, sino que existen unas recomendaciones que puedes seguir para que tus
tareas sean más sencillas de probar.

En primer lugar, piensa que tu tarea es un controlador. ¿Recuerdas la recomendación
básica de los controladores? *Los controladores deben tener poco código y las
clases del modelo mucho código*. Así que traslada toda la lógica de negocio a
las clases de tus modelos de forma que puedas probar los modelos en vez de tu
tarea, que es algo mucho más fácil.

Cuando ya no puedas trasladar más código a los modelos, divide el método
`execute()` en pequeños trozos de código que sean fáciles de probar. Cada
*trozo* debería residir en su propio método sencillo y accesible (métodos de
tipo *public*). Dividir el código tiene muchas ventajas:

  1. hace que el método `execute()` de la tarea sea más fácil de leer
  1. facilita las pruebas de la tarea
  1. hace que la tarea sea mas fácil de extender

Finalmente, si no encuentras ninguna forma de probar esa tarea tan espectacular
que acabas de crear, existen dos posibilidades: que la tarea esté mal escrita
o que tengas que pedir a alguien su opinión. También puedes investigar el código
de otros programadores para aprender a probar las tareas (el propio código de
Symfony incluye pruebas para todas sus tareas, incluso para los generadores).

Métodos útiles para mostrar mensajes
------------------------------------

El sistema de tareas de Symfony hace todo lo posible para facilitar el trabajo
de los programadores, por lo que incluye métodos muy útiles para algunas de las
operaciones más comunes, como mostrar mensajes de log y solicitar información
al usuario.

Para mostrar mensajes de log en `STDOUT`, se pueden emplear los métodos de la
*familia* `log`:

  * `log`, acepta un array de mensajes
  * `logSection`, formatea el mensaje con un prefijo (primer argumento del método)
    y un tipo de mensaje (cuarto argumento). Si muestras un mensje muy largo,
    como por ejemplo la ruta de un archivo, `logSection` trunca el mensaje, lo
    que puede llegar a ser molesto. Utiliza el tercer argumento para especificar
    el tamaño máximo permitido en los mensajes.
  * `logBlock`, se trata del estilo de log utilizado en las excepciones. En este
    caso también puedes pasar el estilo con el que se formatea el mensaje.

Los tipos de formatos de log disponibles son `ERROR`, `INFO`, `COMMENT` y `QUESTION`.
No dudes en probar todos ellos para ver cuál es su aspecto.

Ejemplo de uso:

    [php]
    $this->logSection('file+', $aVeryLongFileName, $this->strlen($aVeryLongFileName));

    $this->logBlock('¡Felicidades! ¡La tarea se ha ejecutado correctamente!', 'INFO');

Métodos útiles para interactuar con el usuario
----------------------------------------------

Para facilitar la interacción con el usuario se han definido otros métodos
útiles:

  * `ask()`, muestra una pregunta y devuelve lo que haya escrito el usuario

  * `askConfirmation()`, se solicita la confirmación del usuario permitiendo
    solamente `y` (yes / si) y `n` (no) como respuesta

  * `askAndValidate()`, un método muy útil que muestra una pregunta y valida la
    respuesta del usuario mediante un validador de tipo `sfValidator` pasado
    como segundo argumento. El tercer argumento es un array de opciones en el
    que puedes pasar un valor por defecto (`value`), un número máximo de intentos
    (`attempts`) y el estilo con el que se formatea el mensaje (`style`).

Puedes por ejemplo preguntar al usuario su email y validarlo en ese mismo momento:

    [php]
    $email = $this->askAndValidate('¿Cuál es tu email?', new sfValidatorEmail());

Utilizando las tareas con un Crontab
------------------------------------

La mayoría de sistemas UNIX y GNU/Linux permiten planificar tareas mediante un
mecanismo denominado *cron*. El *cron* dispone de un archivo de configuración
(un *crontab*) en el que busca los comandos que se deben ejecutar en cada
momento. Las tareas de Symfony se pueden integrar fácilmente en un *crontab*
y la tarea `project:send-emails` es un candidato perfecto para un ejemplo de
este tipo:

    MAILTO="you@example.org"
    0 3 * * *       /usr/bin/php /var/www/yourproject/symfony project:send-emails

La configuración anterior le indica a *cron* que debe ejecutar la tarea
`project:send-emails` todos los días a las 3 de la mañana y que envíe cualquier
mensaje que se produzca (avisos, errores, etc.) a la dirección *you@example.org*.

>**NOTE**
>Si quieres más información sobre el formato del archivo de configuración de
>*crontab*, ejecuta el comando `man 5 crontab` es una consola de comandos.

También es posible pasar opciones y argumentos a la tarea programada:

    MAILTO="you@example.org"
    0 3 * * *       /usr/bin/php /var/www/yourproject/symfony project:send-emails --env=prod --application=frontend

>**NOTE**
>Debes reemplazar `/usr/bin/php` por la localización del archivo binario de PHP
>ejecutable desde la línea de comandos. Si desconoces su localización, puedes
>ejecutar `which php` en los sistemas Linux y `whereis php` en la mayoría de
>sistemas UNIX.

Utilizando STDIN
----------------

Como las tareas se ejecutan en una consola de comandos, también tienes acceso
a la entrada estándar STDIN. En UNIX, las aplicaciones pueden interactuar entre
si de muchas formas diferentes, una de las cuales se denomina *pipe* o *tubería*
y se simbolizan mediante el carácter *|*. El *pipe* permite conectar la salida
de una aplicación (conocida como *STDOUT*) con la entrada estándar de otra
aplicación (conocida como *STDIN*). Tanto la entrada como la salida estándar
son accesibles desde las tareas de Symfony mediante unas constantes especiales
de PHP denominadas `STDIN` y `STDOUT`. Existe otra constante especial llamada
`STDERR` preparada para mostrar los mensajes de error de las aplicaciones.

¿Qué se puede hacer con la entrada estándar? Imagina que dispones en tu servidor
de una aplicación que se quiere comunicar con Symfony. Por supuesto podría
comunicarse mediante HTTP, pero una forma mucho más eficiente de hacerlo sería
mediante un *pipe* de su salida a la entrada de la tarea de Symfony. Supongamos
que la aplicación puede generar datos estructurados (por ejemplo un array PHP
serializado) que describe los objetos que se deben insertar en la base de datos.
En este caso, se podría escribir una tarea como la siguiente:

    [php]
    while ($content = trim(fgets(STDIN)))
    {
      if ($data = unserialize($content) !== false)
      {
        $object = new Object();
        $object->fromArray($data);
        $object->save();
      }
    }

Ahora ya podrías conectar las dos mediante el siguiente comando:

    /usr/bin/data_provider | ./symfony data:import

`data_provider` es la aplicación que genera los objetos y `data:import` es la
tarea que se acaba de crear.

Conclusión
----------

Las posibilidades de las tareas sólo están limitadas por tu imaginación. El
sistema de tareas de Symfony es suficientemente poderoso y flexible como para
que puedas hacer cualquier cosa que imagines. Si a todo esto añades el poder
de la shell de UNIX, las tareas te van a encantar.
