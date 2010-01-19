Mejora tu productividad
=======================

*por Fabien Potencier*

Utilizar Symfony es el primer paso para mejorar tu productividad como
programador web. Obviamente todo el mundo sabe que las excepciones que muestra
Symfony en el entorno de desarrollo y la barra de depuración web ayudan a
mejorar tu productividad, pero en este capítulo se muestran algunos trucos y
consejos que mejoran todavía más la productividad y hacen uso de características
de Symfony nuevas o poco conocidas.

Empieza más rápido: personalizando la creación de proyectos
-----------------------------------------------------------

Gracias a la línea de comandos de Symfony, crear un proyecto nuevo es tan
sencillo como lo siguiente:

    $ php /ruta/hasta/symfony generate:project mi_proyecto --orm=Doctrine

La tarea `generate:project` genera la estructura de directorios por defecto
del nuevo proyecto y crea todos los archivos de configuración con los valores
iniciales adecuados. Después se puede hacer uso de otras tareas de Symfony para
crear aplicaciones, instalar plugins, configurar el modelo, etc.

No obstante, lo primero que haces después de crear cada proyecto seguramente
siempre es lo mismo: creas la aplicación principal, instalas varios plugins,
modificas el valor de algunas opciones de configuración a tu gusto, etc.

Desde la versión 1.3 de Symfony es posible personalizar y automatizar el
proceso de creación de un nuevo proyecto.

>**NOTE**
>Como todas las tareas de Symfony son clases, resulta muy sencillo personalizar
>su comportamiento mediante la herencia, salvo en el caso de la tarea
>`generate:project`. El motivo es que cuando se ejecuta esa tarea todavía no
>existe ningún proyecto, por lo que no existe una forma sencilla de personalizar
>su comportamiento.

La tarea `generate:project` permite el uso de una opción llamada `--installer`
y que indica el nombre del archivo PHP que se ejecuta durante el proceso de
creación del proyecto:

    $ php /ruta/hasta/symfony generate:project --installer=/donde_sea/mi_instalador.php

El script `/donde_sea/mi_instalador.php` se ejecuta dentro del contexto de la
instancia de `sfGenerateProjectTask`, por lo que tiene acceso a todos sus
métodos (invocándolos a través del objeto `$this`). Las siguientes secciones
muestran todos los métodos disponibles que puedes utilizar para personalizar el
proceso de creación de proyectos.

>**TIP**
>Si en tu archivo de configuración `php.ini` activas la inclusión de archivos
>mediante URL en la función `include()`, puedes incluso utilizar una URL como
>instalador (obviamente debes tener mucho cuidado en este caso, ya que estás
>haciendo uso de un script remoto del que puede que no sepas nada):
>
>      $ symfony generate:project
>      --installer=http://ejemplo.com/instalador_symfony.php

### `installDir()`

El método `installDir()` copia una estructura de directorios (compuesta de
archivos y subdirectorios) en el nuevo proyecto:

    [php]
    $this->installDir(dirname(__FILE__).'/skeleton');

### `runTask()`

El método `runTask()` ejecuta una tarea. Como argumento se le pasa el nombre
de la tarea y una cadena de texto con todos los argumentos y opciones de esa
tarea:

    [php]
    $this->runTask('configure:author', "'Fabien Potencier'");

Las opciones y argumentos de la tarea también se pueden pasar como array:

    [php]
    $this->runTask('configure:author', array('author' => 'Fabien Potencier'));

>**TIP**
>También se pueden utilizar los nombres cortos de las tareas:
>
>    [php]
>    $this->runTask('cc');

Este método se puede utilizar también para instalar plugins:

    [php]
    $this->runTask('plugin:install', 'sfDoctrineGuardPlugin');

Para instalar una versión específica del plugin, simplemente se pasan las
opciones adecuadas:

    [php]
    $this->runTask('plugin:install', 'sfDoctrineGuardPlugin', array('release' => '10.0.0', 'stability' => beta'));

>**TIP**
>Para ejecutar tareas de un plugin recién instalado, es necesario volver a
>cargar las tareas ejecutando el siguiente código:
>
>     [php]
>     $this->reloadTasks();

Si creas una nueva aplicación y quieres utilizar tareas como `generate:module`
que dependen de una aplicación específica, debes modificar el contexto de la
configuración manualmente:

    [php]
    $this->setConfiguration($this->createConfiguration('frontend', 'dev'));

### Mensajes de log

El script de instalación también puede mostrar fácilmente mensajes de log que
indiquen al programador la instalación que se está realizando:

    [php]
    // mensaje de log sencillo
    $this->log('mensaje creado por el instalador');

    // bloque de mensajes de log
    $this->logBlock('Instalador de Fabien', 'ERROR_LARGE');

    // sección de mensajes de log
    $this->logSection('install', 'instalación de varios archivos');

### Interacción con el usuario

Los métodos `askConfirmation()`, `askAndValidate()` y `ask()` permiten realizar
preguntas y por tanto convierten la instalación en un proceso configurable de
forma dinámica.

Si sólo necesitas una confirmación, utiliza el método `askConfirmation()`:

    [php]
    if (!$this->askConfirmation('¿Seguro que quieres utilizar este instalador?'))
    {
      $this->logSection('install', '¡Buena elección!');

      return;
    }

También puedes hacer uso del método `ask()` para preguntar al usuario y obtener
su respuesta en forma de cadena de texto:

    [php]
    $secreto = $this->ask('Escribe una cadena de texto única para el secreto de CSRF:');

Y si quieres validar la respuesta, puedes emplear el método `askAndValidate()`:

    [php]
    $validador = new sfValidatorEmail(array(), array('invalid' => '¡Vaya, parece que no es un email!'));
    $email = $this->askAndValidate('Por favor, introduce tu email:', $validador);

### Operaciones con el sistema de archivos

Si quieres realizar cambios en el sistema de archivos, puedes acceder al
objeto de Symfony encargado de estas operaciones:

    [php]
    $this->getFilesystem()->...();

>**SIDEBAR**
>El proceso de creación del *sandbox*
>
>El *sandbox* de Symfony no es más que un proyecto con una aplicación creada y
>con una base de datos SQLite preconfigurada. Cualquiera puede crearse su propio
>sandbox haciendo uso de su script de instalación:
>
>     $ php symfony generate:project --installer=/ruta/hasta/symfony/data/bin/sandbox_installer.php
>
>Echa un vistazo al script `symfony/data/bin/sandbox_installer.php` para ver un
>ejemplo real de un script de instalación.

El script de instalación no deja de ser un archivo PHP normal, por lo que puedes
hacer cualquier otra cosa que quieras. En lugar de ejecutar las mismas tareas
una y otra vez al crear un nuevo proyecto Symfony, puedes utilizar tu propio
script para adaptar como quieras la instalación de los proyectos Symfony. Crear
un nuevo proyecto con un instalador es mucho más rápido y evita que te olvides
de ejecutar algún paso. Además, puedes compartir tu script de instalación con
otros programadores.

>**TIP**
>El el [capítulo 6](#chapter_06) usaremos un instalador propio. Su código fuente
se puede encontrar en el [apéndice B](#chapter_b).

Programa más rápido
-------------------

Programar aplicaciones y crear nuevas tareas obliga a teclear muchos caracteres.
A continuación se explica cómo reducir al mínimo imprescindible los caracteres
que se teclean.

### Eligiendo el IDE

Utilizar un buen IDE ayuda al programador a ser más productivo de muchas formas
diferentes.

En primer lugar, los IDE más modernos incluyen autocompletado de código PHP.
Esto significa que sólo tienes que escribir las primeras letras del nombre de
un método. Además, esto también significa que si no te acuerdas del nombre del
método, no hace falta que busques en la API del framework, ya que el IDE muestra
la lista de todos los métodos disponibles en ese objeto.

En segundo lugar, algunos IDE como PHPEdit y Netbeans conocen más sobre Symfony
y por eso ofrecen una integración mucho más específica con los proyectos
Symfony.

>**SIDEBAR**
>Editores de texto
>
>Algunos programadores prefieren los editores de texto cuando están programando,
>sobre todo porque los editores de texto son mucho más rápidos que los IDE.
>Obviamente los editores de texto no incluyen muchas de las características
>de los IDE. No obstante, los editores más populares disponen de plugins y
>extensiones que mejoran la experiencia de usuario y hacen que el trabajo con
>PHP y con los proyectos Symfony sea más eficiente.
>
>Muchos usuarios de Linux utilizan por ejemplo el editor VIM para todo su
>trabajo. Estos usuarios pueden hacer uso de la extensión
>[vim-symfony](http://github.com/geoffrey/vim-symfony), que es un conjunto de
>scripts de VIM para integrar Symfony. Utilizando vim-symfony, es posible crear
>macros y comandos de vim para simplificar tus desarrollos con Symfony. Además
>incluye varios comandos prediseñados para facilitar el acceso a los archivos
>de configuración importantes (esquema de datos, enrutamiento, etc.) y para
>alternar entre las acciones y sus plantillas asociadas.
>
>Algunos usuarios de MacOS X utilizan el editor TextMate. En este caso pueden
>instalar el [bundle de Symfony](http://github.com/denderello/symfony-tmbundle),
>que añade un montón de macros y atajos para ahorrar tiempo en las tareas más
>habituales.

#### Utilizando un IDE que soporte Symfony

Algunos IDE como [PHPEdit 3.4](http://www.phpedit.com/en/presentation/extensions/symfony)
y [NetBeans 6.8](http://www.netbeans.org/community/releases/68/) disponen de
soporte nativo de Symfony, por lo que ofrecen una integración muy completa
con el framework. No te olvides repasar su documentación para aprender más
sobre su soporte de Symfony y sobre cómo pueden ayudarte a programar más rápido.

#### Ayudando al IDE

El autocompletado de código PHP en los IDE sólo funciona con los métodos que
han sido definidos explícitamente en el código. Por tanto, si tu código incluye
métodos *mágicos* como `__call()` o `__get()` los IDE no son capaces de
adivinar los métodos y propiedades disponibles para ese objeto. La buena noticia
es que en esos casos puedes ayudar a los IDE definiendo los métodos y/o propiedades
disponibles mediante un bloque de PHPDoc (utilizando las anotaciones `@method`
y `@property` respectivamente).

Imagina que tienes una clase `Message` con una propiedad dinámica (`message`)
y un método dinámico (`getMessage()`). El siguiente código muestra cómo pueden
los IDE saber lo anterior sin ninguna definición explícita en el código PHP:

    [php]
    /**
     * @property clob $message
     *
     * @method clob getMessage() Devuelve el valor del mensaje actual
     */
    class Message
    {
      public function __get()
      {
        // ...
      }

      public function __call()
      {
        // ...
      }
    }

Aunque el método `getMessage()` no existe, el IDE puede reconocerlo gracias a
la anotación `@method`. Lo mismo sucede con la propiedad `message` debido a la
anotación `@property`.

Esta técnica es la que utiliza por ejemplo la tarea `doctrine:build-model`.
Si una clase se llama `MailMessage`, tiene dos columnas (`message` y `priority`)
y usa Doctrine, su código PHP es el siguiente:

    [php]
    /**
     * BaseMailMessage
     *
     * This class has been auto-generated by the Doctrine ORM Framework
     *
     * @property clob $message
     * @property integer $priority
     * 
     * @method clob        getMessage()  Returns the current record's "message" value
     * @method integer     getPriority() Returns the current record's "priority" value
     * @method MailMessage setMessage()  Sets the current record's "message" value
     * @method MailMessage setPriority() Sets the current record's "priority" value
     * 
     * @package    ##PACKAGE##
     * @subpackage ##SUBPACKAGE##
     * @author     ##NAME## <##EMAIL##>
     * @version    SVN: $Id: Builder.php 6508 2009-10-14 06:28:49Z jwage $
     */
    abstract class BaseMailMessage extends sfDoctrineRecord
    {
        public function setTableDefinition()
        {
            $this->setTableName('mail_message');
            $this->hasColumn('message', 'clob', null, array(
                 'type' => 'clob',
                 'notnull' => true,
                 ));
            $this->hasColumn('priority', 'integer', null, array(
                 'type' => 'integer',
                 ));
        }

        public function setUp()
        {
            parent::setUp();
            $timestampable0 = new Doctrine_Template_Timestampable();
            $this->actAs($timestampable0);
        }
    }

Encuentra la documentación más rápido
-------------------------------------

Como Symfony es un framework grande y con muchas características, no siempre
resulta fácil acordarse de todas sus opciones de configuración o de todas las
clases y métodos disponibles. Como se explicó anteriormente, utilizar un IDE
puede facilitar tu trabajo mediante el autocompletado. A continuación se explica
cómo sacar partido a las herramientas existentes para encontrar las respuestas
lo más rápido posible.

### API online

La forma más rápida de encontrar documentación sobre una clase o método consiste
en navegar la [API](http://www.symfony-project.org/api/1_3/) online de Symfony.

Más interesante todavía es el buscador que incluye la propia API, ya que permite
encontrar rápidamente cualquier clase o método escribiendo sólo unos pocos
caracteres. En la página de la API, introduce algunas letras en el cuadro de
búsqueda para que aparezca una caja con sugerencias útiles actualizadas en
tiempo real.

Se puede buscar escribiendo el principio del nombre de una clase:

![Búsqueda de la API](http://www.symfony-project.org/images/more-with-symfony/api_search_1.png "Búsqueda de la API")

o el principio del nombre de un método:

![Búsqueda de la API](http://www.symfony-project.org/images/more-with-symfony/api_search_2.png "Búsqueda de la API")

o el nombre de una clase seguido por `::` para mostrar el listado de todos sus
métodos:

![Búsqueda de la API](http://www.symfony-project.org/images/more-with-symfony/api_search_3.png "Búsqueda de la API")

o el nobre de un método para refinar aún más las posibilidades:

![Búsqueda de la API](http://www.symfony-project.org/images/more-with-symfony/api_search_4.png "Búsqueda de la API")

Si quieres ver todas las clases de un paquete, escribe el nombre del paquete
y envía tu petición.

Incluso puedes integrar el buscador de la API de Symfony en tu propio navegador.
De esta forma, no es necesario que visites el sitio web de Symfony cuando quieras
hacer una búsqueda. Esto es posible porque el sitio dispone de soporte nativo
de [OpenSearch](http://www.opensearch.org/) para la API de Symfony.

Si utilizas Firefox, los buscadores de la API de Symfony aparecen automáticamente
en el menú de búsqueda del navegador. También puedes pinchar sobre el enlace
*"API OpenSearch"* incluido en la documentación de la API para añadir ese
buscador en tu navegador de forma permanente.

>**NOTE**
>El blog oficial publicó hace tiempo un un *screencast* que muestra cómo se
>integra el buscador de la API de Symfony con Firefox:
>[ver screencast](http://www.symfony-project.org/blog/2009/02/24/opensearch-support-for-the-symfony-api).

### *Cheat Sheets* o chuletas

Si quieres resúmenes sobre las partes más importantes del framework, puedes
descargar un montón de *[cheat sheets](http://trac.symfony-project.org/wiki/CheatSheets)*:

 * [Estructura de directorios y CLI](http://andreiabohner.files.wordpress.com/2007/03/cheatsheetsymfony001_enus.pdf)
 * [Vista](http://andreiabohner.files.wordpress.com/2007/08/sfviewfirstpartrefcard.pdf)
 * [Vista: elementos parciales, componentes, slots y slots de componentes](http://andreiabohner.files.wordpress.com/2007/08/sfviewsecondpartrefcard.pdf)
 * [Pruebas unitarias y funcionales con Lime](http://trac.symfony-project.com/attachment/wiki/LimeTestingFramework/lime-cheat.pdf?format=raw)
 * [ORM](http://andreiabohner.files.wordpress.com/2007/08/sform_enus.pdf)
 * [Propel](http://andreiabohner.files.wordpress.com/2007/08/sfmodelfirstpartrefcard.pdf)
 * [Esquemas de Propel](http://andreiabohner.files.wordpress.com/2007/09/sfmodelsecondpartrefcard.pdf)
 * [Doctrine](http://www.phpdoctrine.org/Doctrine-Cheat-Sheet.pdf)

>**NOTE**
>Algunas de estas *cheat sheets* no han sido actualizadas para Symfony 1.3.

### Documentación *offline*

La guía de referencia de Symfony es la mejor forma de responder a las preguntas
relativas a las opciones de configuración. Se trata de un libro imprescindible
cuando desarrollas aplicaciones Symfony. Este libro es la forma más rápida de
encontrar cualquier opción de configuración disponible gracias a su detallado
índice de contenidos, su índice alfabético, las referencias cruzadas en cada
capítulo, las tablas, etc.

Puedes [leer esta guía](http://www.symfony-project.org/reference/1_3/en/) en
Internet, puedes [comprar una versión impresa](http://books.sensiolabs.com/book/the-symfony-1-3-reference-guide)
e incluso descargar su [versión en PDF](http://www.symfony-project.org/get/pdf/reference-1.3-en.pdf).

### Herramientas *online*

Como se ha mostrado al inicio de este capítulo, Symfony incluye muchas
herramientas útiles para que empieces a crear tu aplicación lo antes posible.
Por eso, en poco tiempo habrás acabado tu proyecto y será el momento de
pasarlo a producción.

Para comprobar si tu sitio está listo para pasarlo a producción, puedes hacer
uso de las siguiente [lista de comprobación](http://symfony-check.org/).
Este sitio web muestra las comprobaciones esenciales que debes realizar antes
de pasar el sitio a producción.

Depura más rápido
-----------------

Cuando se produce un error en el entorno de desarrollo, Symfony muestra una
página de excepción que incluye un montón de información útil. Entre otras cosas
es posible ver la traza de ejecución y todos los archivos que se han ejecutado.
Si configuras la opción ~`sf_file_link_format`~ en el archivo `settings.yml`
(ver más adelante), puedes incluso pinchar sobre el nombre de cada archivo y
se abrirá en tu editor de textos o IDE favorito posicionándose en la línea
exacta en la que se ha producido el error. Esta característica es un buen
ejemplo de cómo una pequeña mejora puede hacer que ahorres mucho tiempo
cuando estás depurando una aplicación.

>**NOTE**
>Los paneles de la vista y de los mensajes de log también muestran nombres de
>archivo (sobre todo cuando Xdebug está activado). Estos nombres de archivo
>también se pueden pinchar cuando se configura la opción `sf_file_link_format`.

Por defecto la opción `sf_file_link_format` no tiene ningún valor, por lo que
se hace uso, si está disponible, del valor de la opción de configuración 
[`xdebug.file_link_format`](http://xdebug.org/docs/all_settings#file_link_format)
de PHP. La opción `xdebug.file_link_format` en el archivo `php.ini` permite a
las versiones más recientes de XDebug añadir enlaces a todos los nombres de
archivos mostrados en la traza de ejecución.

El valor adecuado para la opción `sf_file_link_format` depende tanto de tu IDE
como de tu sistema operativo. Si quieres por ejemplo abrir los archivos con el
editor ~TextMate~, debes añadir lo siguiente en el archivo `settings.yml`:

    [yml]
    dev:
      .settings:
        file_link_format: txmt://open?url=file://%f&line=%l

Symfony reemplaza la variable `%f` por el valor de la ruta absoluta del archivo
y la variable `%l` se reemplaza por el número de línea específico.

Si utilizas el editor VIM la configuración no es tan sencilla, pero existen
recursos web que lo explican para [symfony](http://geekblog.over-blog.com/article-symfony-open-exceptions-files-in-remote-vim-sessions-37895120.html)
y para [XDebug](http://www.koch.ro/blog/index.php?/archives/77-Firefox,-VIM,-Xdebug-Jumping-to-the-error-line.html).

>**NOTE**
>Puedes utilizar tu buscador favorito para aprender a configurar tu IDE. Además,
>puedes buscar la configuración tanto de `sf_file_link_format` como de
>`xdebug.file_link_format`, ya que las dos funcionan igual.

Crea pruebas más rápido
-----------------------

### Graba tus pruebas funcionales

Las pruebas funcionales simulan las interacciones que realizan los usuarios
para poder probar la integración de todas las partes que forman tu aplicación.
Escribir las pruebas funcionales es bastante sencillo pero requiere mucho tiempo.
Sin embargo, como cada prueba funcional consiste en un escenario que simula la
navegación realizada por un usuario y como navegar por el sitio web es mucho
más rápido que escribir código PHP, sería genial poder grabar una sesión de
navegación y que se convierta automáticamente en código PHP.

Afortunadamente Symfony dispone de un plugin llamado [swFunctionalTestGenerationPlugin](http://www.symfony-project.org/plugins/swFunctionalTestGenerationPlugin)
y que permite generar en pocos minutos esqueletos de pruebas listos para ser
personalizados. Obviamente para hacer estas pruebas completamente funcionales
es necesario añadir las llamadas apropiadas a los *testers*, pero en cualquier
caso proporciona un gran ahorro de tiempo.

Este plugin funciona registrando un filtro de Symfony que intercepta todas las
peticiones y las convierte en el código de la prueba funcional. Tras realizar
la instalación del plugin, es necesario habilitarlo. Abre el archivo `filters.yml`
de la aplicación y añade las siguientes líneas después de la línea de comentario:

    [php]
    functional_test:
      class: swFilterFunctionalTest

Después, activa el plugin en la clase `ProjectConfiguration`:

    [php]
    // config/ProjectConfiguration.class.php
    class ProjectConfiguration extends sfProjectConfiguration
    {
      public function setup()
      {
        // ...

        $this->enablePlugin('swFunctionalTestGenerationPlugin');
      }
    }

Como el plugin utiliza la barra de depuración web como su interfaz principal,
asegúrate de que esté activada (que es lo habitual en el entorno de desarrollo).
Cuando se activa el plugin, la barra de depuración web muestra un nuevo elemento
de menú llamado *"Functional Test"*. Con este nuevo panel se puede empezar a
grabar una sesión pinchando el enlace *"Activate"* y se puede resetear la
sesión actual pinchando sobre el enlace *"Reset"*. Cuando hayas terminado de
navegar, copia y pega el código del textarea en un archivo de pruebas y
modificalo a tu gusto.

### Ejecuta el conjunto de pruebas más rápido

Si dispones de una gran cantidad de pruebas, resulta muy lento ejecutar todas
ellas cada vez que haces un cambio en la aplicación, sobre todo cuando fallan
algunas pruebas. El motivo es que cada vez que arregles una prueba que había
fallado, tienes que volver a ejecutar todas las pruebas para asegurarte de que
no has roto nada nuevo. No obstante, mientras no se arreglen las pruebas que
fallan, no tiene sentido volver a ejecutar todas las demás pruebas. Por ello,
la tarea `test:all` dispone de una opción llamada `--only-failed` (`-f` es el
atajo) que obliga a ejecutar solamente las pruebas que fallaron la última vez:

    $ php symfony test:all --only-failed

La primera vez se ejecutan todas las pruebas, pero en las siguientes veces sólo
se ejecutan las pruebas que fallaron la última vez. A medida que arregles el
código de la aplicación, algunas pruebas se corregirán y por tanto ya no se
volverán a ejecutar. Cuando se ejecuten correctamente todas las pruebas que
fallaban, ya puedes volver a ejecutar de nuevo todo el conjunto de pruebas
unitarias y funcionales.
