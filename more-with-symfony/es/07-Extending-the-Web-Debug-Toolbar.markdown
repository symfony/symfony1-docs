Mejorando la barra de depuración web
====================================

*por Ryan Weaver*

La barra de depuración web de Symfony incluye por defecto una gran variedad de
utilidades que facilitan la depuración y la mejora de rendimiento de las aplicaciones.
La barra de depuración web está formada por varias herramientas llamadas *paneles
de depuración web* y que están relacionadas con la cache, la configuración, los
archivos de log, el uso de memoria, la versión de Symfony y el tiempo de procesamiento.
Además, Symfony 1.3 ha introducido dos nuevos *paneles de depuración web* con
información sobre la vista y los emails enviados.

![Barra de depuración web](http://www.symfony-project.org/images/more-with-symfony/web_debug_01.png "La barra de depuración web con los widgets por defecto de Symfony 1.3")

Desde Symfony 1.2 los programadores pueden crear sus propios *paneles de
depuración web* y añadirlos a la barra de depuración web. Este capítulo muestra
cómo crear un nuevo *panel de depuración web* y después se explican todas sus
opciones y posibles configuraciones.  Además, el plugin 
[ac2009WebDebugPlugin](http://www.symfony-project.org/plugins/ac2009WebDebugPlugin)
contiene diversos paneles de depuración muy útiles y que utilizan algunas de
las técnicas mostradas en este capítulo.

Creando un nuevo panel de depuración web
----------------------------------------

Los elementos que forman la barra de depuración web se denominan *paneles de
depuración web* y son unas clases especiales que heredan de la clase
~`sfWebDebugPanel`~ class. En realidad, crear un nuevo panel es muy sencillo.
Crea un nuevo archivo llamado `sfWebDebugPanelDocumentation.class.php` en el
directorio `lib/debug/` de tu proyecto (el directorio no existe y por tanto
tienes que crearlo):

    [php]
    // lib/debug/sfWebDebugPanelDocumentation.class.php
    class acWebDebugPanelDocumentation extends sfWebDebugPanel
    {
      public function getTitle()
      {
        return '<img src="/images/documentation.png" alt="Documentation Shortcuts" height="16" width="16" /> docs';
      }

      public function getPanelTitle()
      {
        return 'Documentation';
      }
      
      public function getPanelContent()
      {
        $content = 'Placeholder Panel Content';
        
        return $content;
      }
    }

Como mínimo todos los paneles deben implementar los métodos `getTitle()`,
`getPanelTitle()` y `getPanelContent()`.

 * ~`sfWebDebugPanel::getTitle()`~: determina cómo se muestra inicialmente el
   panel en la barra de depuración web. Al igual que la mayoría de paneles,
   el panel que estamos creando se va a mostrar mediante un pequeño icono y
   un nombre corto.

 * ~`sfWebDebugPanel::getPanelTitle()`~: su valor se muestra en la parte
   superior del contenido del panel mediante una etiqueta `<h1>`. También se
   utiliza como valor del atributo `title` del enlace que encierra el icono
   de la barra, por lo que no puede incluir ningún tipo de código HTML.

 * ~`sfWebDebugPanel::getPanelContent()`~: genera el contenido HTML que se
   muestra al pulsar sobre el icono del panel.

El último paso que falta consiste en notificar a la aplicación que se quiere
incluir un nuevo panel en la barra. Para ello, añade un nuevo *listener* para
el evento `debug.web.load_panels`, que se notifica cuando la barra de depuración
obtiene los paneles que se van a mostrar. En primer lugar, modificar el archivo
`config/ProjectConfiguration.class.php` para escuchar ese evento:

    [php]
    // config/ProjectConfiguration.class.php
    public function initialize()
    {
      // ...

      $this->dispatcher->connect('debug.web.load_panels', array(
        'acWebDebugPanelDocumentation',
        'listenToLoadDebugWebPanelEvent'
      ));
    }

A continuación, añade la función `listenToLoadDebugWebPanelEvent()` del
*listener* a la clase `acWebDebugPanelDocumentation.class.php` para incluir el
panel en la barra:

    [php]
    // lib/debug/sfWebDebugPanelDocumentation.class.php
    public static function listenToLoadDebugWebPanelEvent(sfEvent $event)
    {
      $event->getSubject()->setPanel(
        'documentation',
        new self($event->getSubject())
      );
    }

¡Y eso es todo! Refresca la página para ver el resultado de forma inmediata.

![Barra de depuración web](http://www.symfony-project.org/images/more-with-symfony/web_debug_02.png "La barra de depuración web con un panel propio")

>**TIP**
>Desde Symfony 1.3, es posible utilizar en la URL un parámetro llamado
>`sfWebDebugPanel` para mostrar desplegado un determinado panel web al 
>cargar la página. Si por ejemplo se añade `?sfWebDebugPanel=documentation`
>al final de la URL, se abrirá automáticamente el panel que se acaba de crear.
>Esta característica puede ser muy útil cuando se crean paneles propios.

Los tres tipos de paneles de depuración web
-------------------------------------------

Internamente existen tres tipos diferentes de paneles de depuración web.

### El tipo *sólo icono*

El tipo más básico de panel es aquel que solamente muestra un icono y un
texto en la barra. El ejemplo clásico es el del panel `memory`, que muestra la
cantidad de memoria utilizada para generar la página y que no hace nada cuando
se pincha sobre el. Para crear un panel de tipo *sólo icono*, simplemente haz
que el método `getPanelContent()` devuelva una cadena vacía. La única
información del panel la genera el método `getTitle()`:

    [php]
    public function getTitle()
    {
      $totalMemory = sprintf('%.1f', (memory_get_peak_usage(true) / 1024));

      return '<img src="'.$this->webDebug->getOption('image_root_path').'/memory.png" alt="Memory" /> '.$totalMemory.' KB';
    }

    public function getPanelContent()
    {
      return;
    }

### El tipo *enlace*

Al igual que el panel *sólo icono*, un panel de tipo *enlace* consiste en un
panel sin contenido. A diferencia del panel *sólo icono*, cuando se pincha sobre
un panel de tipo *enlace*, el navegador carga la URL especificada por el método
`getTitleUrl()` del panel. Para crear un panel de tipo *enlace*, haz que el
método `getPanelContent()` devuelva una cadena vacía y añade en la clase un
método llamado `getTitleUrl()`.

    [php]
    public function getTitleUrl()
    {
      // enlace a una URI externa
      return 'http://www.symfony-project.org/api/1_3/';

      // enlace a una ruta en tu aplicación
      return url_for('homepage');
    }

    public function getPanelContent()
    {
      return;
    }

### El tipo *contenido*

Se trata del tipo más popular con mucha diferencia. Estos paneles tienen un
contenido HTML que se muestra cuando se pincha sobre el nombre del panel en
la barra de depuración web. Para crear este tipo de panel, simplemente haz que
el método `getPanelContent()` no devuelva una cadena vacía.

Personalizando el contenido del panel
-------------------------------------

Una vez creado e incluido el panel de depuración web a la barra, su contenido
se puede añadir fácilmente mediante el método `getPanelContent()`. Symfony
proporciona varios métodos para facilitar la creación de contenido avanzado y
usable.

### ~`sfWebDebugPanel::setStatus()`~

Por defecto todos los paneles de la barra de depuración web muestran un color
de fondo gris. Si se quiere llamar la atención sobre algún contenido del panel,
su fondo se puede mostrar de color naranja o rojo.

![Barra de depuración con error](http://www.symfony-project.org/images/more-with-symfony/web_debug_05.png "La barra de depuración web mostrando un error en los logs")

Para modificar el color de fondo del panel se emplea el método `setStatus()`.
Este método acepta cualquier constante de prioridad definida por la clase
[sfLogger](http://www.symfony-project.org/api/1_3/sfLogger). En concreto se han
definido tres niveles de estado diferentes que se corresponden con los tres
colores de fondo de los paneles (gris, naranja y rojo). Normalmente el método
`setStatus()` se invoca desde el método `getPanelContent()` cuando se cumple
alguna condición que merece una atención especial.

    [php]
    public function getPanelContent()
    {
      // ...

      // mostrar el fondo gris (valor por defecto)
      $this->setStatus(sfLogger::INFO);

      // mostrar el fondo naranja
      $this->setStatus(sfLogger::WARNING);

      // mostrar el fondo rojo
      $this->setStatus(sfLogger::ERR);
    }

### ~`sfWebDebugPanel::getToggler()`~

Uno de los elementos más comunes de los paneles de depuración web existentes
es el *toggler* o alternador, un pequeño elemento con forma de flecha que muestra
u oculta alternativamente cierto contenido cuando se pincha sobre él.

![Alternador](http://www.symfony-project.org/images/more-with-symfony/web_debug_03.png "El toggler o alternador del panel de depuración")

Esta funcionalidad se puede incluir fácilmente en un panel propio mediante
la función `getToggler()`. Si por ejemplo se quiere alternar en el panel el
contenido de una lista:

    [php]
    public function getPanelContent()
    {
      $contenidoLista = '<ul id="debug_documentation_list" style="display: none;">
        <li>Elemento 1</li>
        <li>Elemento 2</li>
      </ul>';

      $toggler = $this->getToggler('debug_documentation_list', 'Muestra/oculta lista');

      return sprintf('<h3>Elementos de la lista %s</h3>%s',  $toggler, $contenidoLista);
    }

El método `getToggler` requiere dos argumentos: el valor del atributo `id` del
elemento DOM que se va a alternar y el título que se va a incluir como valor
del atributo `title` del enlace asociado con el *toggler*. Obviamente debes
crear el elemento DOM con ese atributo `id` y también tienes que crear todo
el contenido que se va a mostrar/ocultar con el alternador.

### ~`sfWebDebugPanel::getToggleableDebugStack()`~

Este método es similar a `getToggler()`, ya que muestra una pequeña flecha que
muestra u oculta alternativamente cierto contenido. En este caso, el contenido
es el de una traza de depuración. Una de sus principales utilidades es la de
mostrar los mensajes de log de una clase propia. Si suponemos que una clase
llamada `myCustomClass` genera mensajes de log propios:

    [php]
    class myCustomClass
    {
      public function doSomething()
      {
        $dispatcher = sfApplicationConfiguration::getActive()
          ->getEventDispatcher();

        $dispatcher->notify(new sfEvent($this, 'application.log', array(
          'priority' => sfLogger::INFO,
          'Begin execution of myCustomClass::doSomething()',
        )));
      }
    }

El siguiente ejemplo muestra la lista de todos los mensajes de log de la clase
`myCustomClass` junto con la traza de depuración de cada uno.

    [php]
    public function getPanelContent()
    {
      // obtiene todos los mensajes de log de la petición actual
      $logs = $this->webDebug->getLogger()->getLogs();

      $listadoLogs = '';
      foreach ($logs as $log)
      {
        if ($log['type'] == 'myCustomClass')
        {
          $listadoLogs .= sprintf('<li>%s %s</li>',
            $log['message'],
            $this->getToggleableDebugStack($log['debug_backtrace'])
          );
        }
      }

      return sprintf('<ul>%s</ul>', $listadoLogs);
    }

![Información de depuración en la barra](http://www.symfony-project.org/images/more-with-symfony/web_debug_04.png "Mostrando una traza de depuración en la barra")

>**NOTE**
>Aunque no se cree un panel propio, los mensajes de log de la clase `myCustomClass`
>se pueden ver en el panel de mensajes de log. La ventaja de esta técnica es que
>se puede mostrar solamente un subconjunto pequeño de los mensajes de log y
>además controlar la forma en la que se muestran.

### ~`sfWebDebugPanel::formatFileLink()`~

Una de las novedades de Symfony 1.3 es que se puede pinchar sobre el nombre de
un archivo en la barra de depuración web para abrirlo con nuestro editor de
texto favorito. Puedes obtener más información en el artículo 
*["What's new"](http://www.symfony-project.org/tutorial/1_3/en/whats-new)* de
Symfony 1.3.

Para hacer uso de esta característica para cualquier archivo, se emplea el
método `formatFileLink()`. Además del propio archivo, se puede enlazar a una
línea concreta. El código del siguiente ejemplo crea un enlace que abre el
archivo `config/ProjectConfiguration.class.php` y posiciona el editor de
texto en la línea 15:

    [php]
    public function getPanelContent()
    {
      $contenido = '';

      // ...

      $ruta = sfConfig::get('sf_config_dir') . '/ProjectConfiguration.class.php';
      $contenido .= $this->formatFileLink($path, 15, 'Configuración del proyecto');

      return $contenido;
    }

Tanto el segundo argumento (número de línea) como el tercero (el texto del enlace)
son opcionales. Si no se indica el tercer argumento, el texto del enlace
es la propia ruta del archivo.

>**NOTE**
>Antes de probar el ejemplo anterior, asegúrate de haber configurado la nueva
>opción de enlazar archivos. Esta opción se configura mediante la clave
>`sf_file_link_format` del archivo de configuración `settings.yml` o mediante
>la opción `file_link_format` de [xdebug](http://xdebug.org/docs/stack_trace#file_link_format).
>La última forma asegura que el proyecto no sea dependiente de un IDE específico.

Otros trucos de la barra de depuración web
------------------------------------------

Normalmente lo mejor de tu panel de depuración web será el contenido que
muestres y la forma en la que lo hagas. No obstante, existen algunos otros
trucos interesantes.

### Eliminar los paneles por defecto

Por defecto Symfony muestra varios paneles en la barra de depuración web. Si
no quieres mostrar alguno de ellos, debes hacer uso del evento
`debug.web.load_panels`. Utiliza la misma función *listener* de las secciones
anteriores, elimina todo su contenido y añade la siguiente función `removePanel()`.
El código del siguiente ejemplo hace que no se muestre el panel con la
información sobre la memoria:

    [php]
    public static function listenToLoadDebugWebPanelEvent(sfEvent $event)
    {
      $event->getSubject()->removePanel('memory');
    }

### Obteniendo parámetros de la petición en un panel

Una de las cosas que más utilizan los paneles de depuración web son los
parámetros de la petición. El siguiente código muestra cómo obtener el parámetro
`event_id` para realizar una consulta a la base de datos y así poder mostrar
la información de un determinado objeto:

    [php]
    $parametros = $this->webDebug->getOption('request_parameters');
    if (isset($parametros['event_id']))
    {
      $objeto = Doctrine::getTable('Event')->find($parametros['event_id']);
    }

### Ocultar un panel cuando no sea necesario

En determinadas ocasiones, los paneles no tienen ningún tipo de información
relevante que mostrar para la petición actual. En estos casos es mejor ocultar
directamente el panel. Supongamos que en el ejemplo anterior el panel no
muestra información útil a menos que exista un parámetro de la petición
llamado `event_id`. Para ocultar ese panel cuando no exista ese parámetro,
simplemente no se devuelve ningún contenido en el método `getTitle()`:

    [php]
    public function getTitle()
    {
      $parametros = $this->webDebug->getOption('request_parameters');
      if (!isset($parametros['event_id']))
      {
        return;
      }

      return '<img src="/acWebDebugPlugin/images/documentation.png" alt="Documentation Shortcuts" height="16" width="16" /> docs';
    }

Últimas reflexiones
-------------------

La barra de depuración web facilita enormemente el trabajo de los programadores,
pero es mucho más que una herramienta para mostrar información. La posibilidad
de añadir paneles de depuración propios a la barra hace que su potencial sólo
esté limitado por la imaginación de los programadores. El plugin
[ac2009WebDebugPlugin](http://www.symfony-project.org/plugins/ac2009WebDebugPlugin)
incluye solamente algunos de los muchos paneles que se pueden crear, así que no
dudes en crear tus propios paneles.