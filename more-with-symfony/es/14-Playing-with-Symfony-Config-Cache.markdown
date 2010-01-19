Jugando con la cache de configuración de Symfony
================================================

*por Kris Wallsmith*

Uno de mis objetivos personales como programador de Symfony consiste en racionalizar
lo más posible el flujo de trabajo de mis compañeros de proyecto. Aunque yo
conozco bien el código de Symfony por dentro y por fuera, no es un requisito que
se pueda exigir al resto del equipo de trabajo. Afortunadamente, Symfony incluye
mecanismos para aislar o centralizar algunas funcionalidades de los proyectos,
facilitando su modificación a los demás programadores.

Cadenas de texto de los formularios
-----------------------------------

Un buen ejemplo de lo comentado anteriormente es el framework de formularios.
Se trata de uno de los componentes más poderosos de Symfony y te proporciona un
gran control sobre los formularios gracias al uso de objetos PHP tanto para
mostrarlos como para validarlos. Lo mejor de los formularios de Symfony es que
el programador puede encapsular una gran cantidad de lógica compleja dentro de
una clase de formulario para reutilizarla y extenderla después en muchos otros
proyectos.

No obstante, desde el punto de vista del diseñador de las plantillas de la
aplicación puede resultar muy complicado tratar de entender esta abstracción
encargada de mostrar los formularios. Si se considera el siguiente formulario:

![Formulario en su estado inicial](http://www.symfony-project.org/images/more-with-symfony/config_cache_form_default.png)

A continuación se muestra el código de la clase que crea ese formulario:

    [php]
    // lib/form/CommentForm.class.php
    class CommentForm extends BaseForm
    {
      public function configure()
      {
        $this->setWidget('body', new sfWidgetFormTextarea());
        $this->setValidator('body', new sfValidatorString(array(
          'min_length' => 12,
        )));
      }
    }

El formulario se muestra en la plantilla PHP de la siguiente forma:

    <!-- apps/frontend/modules/main/templates/indexSuccess.php -->
    <form action="#" method="post">
      <ul>
        <li>
          <?php echo $form['body']->renderLabel() ?>
          <?php echo $form['body'] ?>
          <?php echo $form['body']->renderError() ?>
        </li>
      </ul>
      <p><button type="submit">Post your comment now</button></p>
    </form>

El diseñador de las plantillas tiene bastante control sobre cómo se muestra el
formulario. Puede modificar por ejemplo los títulos por defecto para que sean
un poco más apropiados:

    <?php echo $form['body']->renderLabel('Please enter your comment') ?>

También puede añadir una clase CSS en los campos del formulario:

    <?php echo $form['body']->render(array('class' => 'comment')) ?>

Estas modificaciones son sencillas y bastante intuitivas. Sin embargo, ¿qué
sucede si el diseñador quiere modificar un mensaje de error?

![Formulario con errores](http://www.symfony-project.org/images/more-with-symfony/config_cache_form_error.png)

El método `->renderError()` no admite ningún argumento, así que el diseñador
debería acceder a la clase del formulario, buscar el código asociado al validador
correspondiente y modificar su constructor para asociar los nuevos mensajes de
error con los códigos de error adecuados.

En este ejemplo, el diseñador de las plantillas debería hacer el siguiente
cambio:

    [php]
    // antes
    $this->setValidator('body', new sfValidatorString(array(
      'min_length' => 12,
    )));

    // después
    $this->setValidator('body', new sfValidatorString(array(
      'min_length' => 12,
    ), array(
      'min_length' => 'You haven't written enough',
    )));

¿Has visto el error? Hemos escrito una comilla simple dentro de una cadena de
texto delimitada por esas mismas comillas. Evidentemente un programador como
tu nunca cometería ese error, pero es muy posible que un diseñador de plantillas
cometa este tipo de errores.

De hecho, ¿crees que un diseñador de plantillas es capaz de encontrar el lugar
exacto en el que se definen los mensajes de error? ¿Es realista pensar que un
diseñador va a saber los parámetros que se deben pasar al constructor de un
validador?

Obviamente la respuesta a todas las preguntas anteriores es negativa. Los
diseñadores hacen un trabajo muy valioso pero no es razonable esperar que alguien
que no es programador aprenda el funcionamiento interno del framework de
formularios de Symfony.

La solución YAML
----------------

Para simplificar el proceso de edición de las cadenas de texto de los formularios,
vamos a añadir una capa de configuración YAML para mejorar cada objeto de
formulario que se pasa a la vista. Este archivo de configuración tiene el siguiente
aspecto:

    [yml]
    # config/forms.yml
    CommentForm:
      body:
        label:        Please enter your comment
        attributes:   { class: comment }
        errors:
          min_length: You haven't written enough

Esta forma de trabajar parece mucho más sencilla. El archivo de configuración
se entiende sin tener que explicarlo y además ahora ya no es relevante el problema
de la comilla en medio del texto del mensaje. La siguiente sección explica cómo
crear todo este sistema.

Filtrando las variables de la plantilla
---------------------------------------

El primer reto consiste en encontrar el evento de Symfony adecuado para aplicar
esta nueva configuración a cualquier variable que se pase a la plantilla. En este
caso se utiliza el evento `template.filter_parameters`, que se notifica justo
antes de mostrar una plantilla o un elemento parcial.

    [php]
    // lib/form/sfFormYamlEnhancer.class.php
    class sfFormYamlEnhancer
    {
      public function connect(sfEventDispatcher $dispatcher)
      {
        $dispatcher->connect('template.filter_parameters',
          array($this, 'filterParameters'));
      }

      public function filterParameters(sfEvent $event, $parameters)
      {
        foreach ($parameters as $name => $param)
        {
          if ($param instanceof sfForm && !$param->getOption('is_enhanced'))
          {
            $this->enhance($param);
            $param->setOption('is_enhanced', true);
          }
        }

        return $parameters;
      }

      public function enhance(sfForm $form)
      {
        // ...
      }
    }

>**NOTE**
>Como puedes observar, el código anterior comprueba la existencia de una
>opción llamada `is_enhanced` antes de aplicar la configuración a cada objeto
>de formulario. De esta forma se evita aplicar dos veces la configuración a los
>objetos que se pasan de una plantilla a un elemento parcial.

Esta clase encargada de mejorar los formularios debe conectarse a los eventos
en la configuración de la aplicación:

    [php]
    // apps/frontend/config/frontendConfiguration.class.php
    class frontendConfiguration extends sfApplicationConfiguration
    {
      public function initialize()
      {
        $enhancer = new sfFormYamlEnhancer($this->getConfigCache());
        $enhancer->connect($this->dispatcher);
      }
    }

Ahora ya podemos aislar los formularios justo antes de que se pasen a la
plantilla o elemento parcial, por lo que tenemos todo listo para hacer funcionar
la nueva configuración de formularios. El último paso consiste en aplicar lo
que se ha configurado en el archivo YAML.

Aplicando la configuración YAML
-------------------------------

La forma más sencilla de aplicar esta configuración YAML a cada formulario
consiste en cargarla en un array y después recorrer cada configuración:

    [php]
    public function enhance(sfForm $form)
    {
      $config = sfYaml::load(sfConfig::get('sf_config_dir').'/forms.yml');

      foreach ($config as $class => $fieldConfigs)
      {
        if ($form instanceof $class)
        {
          foreach ($fieldConfigs as $fieldName => $fieldConfig)
          {
            if (isset($form[$fieldName]))
            {
              if (isset($fieldConfig['label']))
              {
                $form->getWidget($fieldName)->setLabel($fieldConfig['label']);
              }

              if (isset($fieldConfig['attributes']))
              {
                $form->getWidget($fieldName)->setAttributes(array_merge(
                  $form->getWidget($fieldName)->getAttributes(),
                  $fieldConfig['attributes']
                ));
              }

              if (isset($fieldConfig['errors']))
              {
                foreach ($fieldConfig['errors'] as $code => $msg)
                {
                  $form->getValidator($fieldName)->setMessage($code, $msg);
                }
              }
            }
          }
        }
      }
    }

Esta solución tiene varios problemas. En primer lugar, los archivos YAML se leen
desde el sistema de archivos y se cargan en un objeto de tipo `sfYaml` cada vez
que se aplica la configuración a un formulario. Para mejorar el rendimiento de
la aplicación deberíamos evitar tantos accesos al sistema de archivos. En segundo
lugar, el rendimiento también se verá penalizado por la cantidad de bucles y
condiciones del código. La solución de estos dos problemas consiste en utilizar
la cache de configuración de Symfony.

La cache de configuración
-------------------------

La cache de configuración está compuesta por una colección de clases que optimizan
el uso de archivos de configuración YAML automatizando su conversión en código
PHP y guardando ese código en el directorio de la cache para ejecutarlo. Este
mecanismo hace innecesario cargar los contenidos de nuestros archivos de
configuración en objetos `sfYaml` antes de aplicar sus valores.

A continuación se añade la cache de configuración en nuestra solución para
mejorar los formularios. En lugar de cargar los archivos `forms.yml` en objetos
`sfYaml`, se va a solicitar la versión pre-procesada de estos archivos a la
cache de configuración de la aplicación actual.

Para ello la clase `sfFormYamlEnhancer` necesita acceder a la cache de configuración
de la aplicación actual, así que vamos a incluirla en el constructor.

    [php]
    class sfFormYamlEnhancer
    {
      protected
        $configCache = null;

      public function __construct(sfConfigCache $configCache)
      {
        $this->configCache = $configCache;
        $this->configCache->registerConfigHandler('config/forms.yml',
          'sfSimpleYamlConfigHandler');
      }

      // ...
    }

A la cache de configuración se le debe indicar qué hacer cuando la aplicación
solicita un determinado archivo de configuración. Por el momento, el código
indica a la cache de configuración que utilice `sfSimpleYamlConfigHandler` para
procesar el archivo `forms.yml`. Este gestor de configuración simplemente procesa
el archivo YAML, lo convierte en un array y lo guarda en la cache como código
PHP.

Una vez que se ha añadido la cache de configuración y se ha registrado un gestor
de configuración para `forms.yml`, ya se puede hacer uso de ellos en lugar de
`sfYaml`:

    [php]
    public function enhance(sfForm $form)
    {
      $config = include $this->configCache->checkConfig('config/forms.yml');

      // ...
    }

Esta solución es mucho mejor. No sólo se ha eliminado la necesidad de procesar
los archivos YAML (salvo en la primera petición) sino que también se hace uso
de la instrucción `include()` de PHP para poder aprovechar los sistemas de
cache de PHP.

>**SIDEBAR**
>Entorno de desarrollo vs. entorno de producción
>
>El funcionamiento interno de `->checkConfig()` difiere dependiendo de si la
>aplicación se ejecuta con el modo `debug` activado o desactivado. En el entorno
>`prod` el modo `debug` está desactivado, por lo que el método funciona de la
>siguiente forma:
>
>  * Comprueba si en la cache existe una versión del archivo solicitado
>    * Si existe, devuelve la ruta hasta el archivo de la cache
>    * Comprueba si en la cache existe una versión del archivo solicitado:
>      * Se procesa el archivo de configuración
>      * Se guarda el resultado en la cache
>      * Se devuelve la ruta hasta el nuevo archivo de la cache
>
>Cuando el modo `debug` está activado, el funcionamiento de este método varía
>de forma significativa. Como los archivos de configuración pueden variar durante
>el desarrollo de la aplicación, `->checkConfig()` compara las fechas de modificación
>del archivo original y del archivo de la cache para asegurarse de que siempre
>se utiliza la versión más reciente. Por tanto, el comportamiento del método
>cuando está activado el modo `debug` es el siguiente:
>
>  * Comprueba si en la cache existe una versión del archivo solicitado
>    * Si no existe:
>      * Se procesa el archivo de configuración
>      * Se guarda el código resultante en la cache
>    * Si existe:
>      * Se comparan las fechas de modificación del archivo de configuración y del archivo de la cache
>      * Si el archivo de configuración se ha modificado más recientemente:
>        * Se procesa el archivo de configuración
>        * Se guarda el código resultante en la cache
>  * Se devuelve la ruta hasta el nuevo archivo de la cache

Añadiendo pruebas unitarias
---------------------------

Antes de continuar es recomendable crear algunas pruebas unitarias, como por
ejemplo la que muestra el siguiente código:

    [php]
    // test/unit/form/sfFormYamlEnhancerTest.php
    include dirname(__FILE__).'/../../bootstrap/unit.php';

    $t = new lime_test(3);

    $configuration = $configuration->getApplicationConfiguration(
      'frontend', 'test', true, null, $configuration->getEventDispatcher());
    sfToolkit::clearDirectory(sfConfig::get('sf_app_cache_dir'));

    $enhancer = new sfFormYamlEnhancer($configuration->getConfigCache());

    // ->enhance()
    $t->diag('->enhance()');

    $form = new CommentForm();
    $form->bind(array('body' => '+1'));

    $enhancer->enhance($form);

    $t->like($form['body']->renderLabel(), '/Please enter your comment/',
      '->enhance() enhances labels');
    $t->like($form['body']->render(), '/class="comment"/',
      '->enhance() enhances widgets');
    $t->like($form['body']->renderError(), '/You haven\'t written enough/',
      '->enhance() enhances error messages');

Ejecuta la prueba anterior para verificar que `sfFormYamlEnhancer` funciona
correctamente:

![Las pruebas pasan correctamente](http://www.symfony-project.org/images/more-with-symfony/config_cache_tests_3_ok.png)

Ahora ya podemos seguir modificando el código con la confianza de que las pruebas
nos avisarán si *rompemos* algo.

Gestores de configuración propios
---------------------------------

El código mostrado anteriormente recorre, para cada variable que se pasa a la
plantilla, todas las clases de formulario configuradas en `forms.yml`. Aunque
es una solución que funciona bien, penaliza mucho el rendimiento de la aplicación
cuando se pasan muchos formularios a la plantilla o si se dispone de una lista
muy larga de formularios configurados en el archivo YAML. Se trata de una buena
oportunidad para crear un gestor propio de configuración que permita optimizar
este proceso.

>**SIDEBAR**
>¿Por qué crear un gestor propio?
>
>Desarrollar un gestor propio de configuración no es nada fácil. Como sucede
>con cualquier otro generador de código, los gestores de configuración son
>propensos a los errores y muy difíciles de probar, pero tienen muchas ventajas.
>Crear código ejecutable de forma dinámica es una solución perfecta que combina
>la gran flexibilidad de YAML con la extrema rapidez de ejecución del código PHP.
>Si además se añade una cache de código (como por ejemplo [APC](http://pecl.php.net/apc)
>o [XCache](http://xcache.lighttpd.net/)) los gestores de configuración son
>prácticamente imbatibles por su facilidad de uso y su gran rendimiento.

La mayor parte de la *magia* de los gestores de configuración se produce
internamente. La cache de configuración se encarga de la lógica de la cache antes
de ejecutar el gestor de configuración apropiado, por lo que nos podemos centrar
exclusivamente en generar el código necesario para aplicar la configuración
YAML.

Todos los gestores de configuración deben implementar los siguientes dos métodos:

 * `static public function getConfiguration(array $configFiles)`
 * `public function execute($configFiles)`

Al primer método, `::getConfiguration()`, se le pasa un array de rutas de
archivos, para que los procese y junte sus contenidos en un único gran archivo.
En la `sfSimpleYamlConfigHandler` utilizada anteriormente este método sólo
utiliza una línea:

    [php]
    static public function getConfiguration(array $configFiles)
    {
      return self::parseYamls($configFiles);
    }

La clase `sfSimpleYamlConfigHandler` extiende la clase abstracta `sfYamlConfigHandler`,
que incluye varios métodos útiles para procesar los archivos de configuración
YAML:

 * `::parseYamls($configFiles)`
 * `::parseYaml($configFile)`
 * `::flattenConfiguration($config)`
 * `::flattenConfigurationWithEnvironment($config)`

Los dos primeros métodos implementan la
[configuración en cascada](http://www.symfony-project.org/reference/1_2/en/03-Configuration-Files-Principles#chapter_03_configuration_cascade).
de Symfony. El segundo implementa el mecanismo de
[configuración basada en entornos](http://www.symfony-project.org/reference/1_2/en/03-Configuration-Files-Principles#chapter_03_environment_awareness).

El método `::getConfiguration()` de nuestro gestor de configuración necesita un
método propio para unir toda la configuración en un único archivo. Crea un
método llamado `::applyInheritance()` para encapsular toda esta lógica:

    [php]
    // lib/config/sfFormYamlEnhancementsConfigHander.class.php
    class sfFormYamlEnhancementsConfigHander extends sfYamlConfigHandler
    {
      public function execute($configFiles)
      {
        $config = self::getConfiguration($configFiles);

        // compile data
        $retval = "<?php\n".
                  "// auto-generated by %s\n".
                  "// date: %s\nreturn %s;\n";
        $retval = sprintf($retval, __CLASS__, date('Y/m/d H:i:s'),
          var_export($config, true));

        return $retval;
      }

      static public function getConfiguration(array $configFiles)
      {
        return self::applyInheritance(self::parseYamls($configFiles));
      }

      static public function applyInheritance($config)
      {
        $classes = array_keys($config);

        $merged = array();
        foreach ($classes as $class)
        {
          if (class_exists($class))
          {
            $merged[$class] = $config[$class];
            foreach (array_intersect(class_parents($class), $classes) as $parent)
            {
              $merged[$class] = sfToolkit::arrayDeepMerge(
                $config[$parent],
                $merged[$class]
              );
            }
          }
        }

        return $merged;
      }
    }

Ahora ya se dispone de un array cuyos valores se han unido mediante la herencia
de clases. De esta forma se evita tener que filtrar la configuración mediante
una instrucción `instanceof` para cada objeto de formulario. Además, esta
unión de archivos se realiza en el gestor de configuración, por lo que sólo se
realiza una vez y después se guarda en la cache.

Aplicando una lógica muy sencilla, ya es posible aplicar este array a un objeto
de formulario:

    [php]
    class sfFormYamlEnhancer
    {
      protected
        $configCache = null;

      public function __construct(sfConfigCache $configCache)
      {
        $this->configCache = $configCache;
        $this->configCache->registerConfigHandler('config/forms.yml',
          'sfFormYamlEnhancementsConfigHander');
      }

      // ...

      public function enhance(sfForm $form)
      {
        $config = include $this->configCache->checkConfig('config/forms.yml');

        $class = get_class($form);
        if (isset($config[$class]))
        {
          $fieldConfigs = $config[$class];
        }
        else if ($overlap = array_intersect(class_parents($class),
          array_keys($config)))
        {
          $fieldConfigs = $config[current($overlap)];
        }
        else
        {
          return;
        }

        foreach ($fieldConfigs as $fieldName => $fieldConfig)
        {
          // ...
        }
      }
    }

Antes de volver a ejecutar el script de las pruebas, se añade una comprobación
para la nueva lógica de herencia de clases.

    [yml]
    # config/forms.yml

    # ...

    BaseForm:
      body:
        errors:
          min_length: A base min_length message
          required:   A base required message

A continuación se comprueba mediante las pruebas unitarias que se está aplicando
un mensaje de tipo `required` al formulario y que también se está aplicando ese
mensaje a todos los formularios que heredan de un formulario padre, aunque ellos
mismos no tengan configurado ese mensaje.

    [php]
    $t = new lime_test(5);

    // ...

    $form = new CommentForm();
    $form->bind();
    $enhancer->enhance($form);
    $t->like($form['body']->renderError(), '/A base required message/',
      '->enhance() considers inheritance');

    class SpecialCommentForm extends CommentForm { }
    $form = new SpecialCommentForm();
    $form->bind();
    $enhancer->enhance($form);
    $t->like($form['body']->renderLabel(), '/Please enter your comment/',
      '->enhance() applies parent config');

Ejecuta de nuevo las pruebas unitarias para comprobar que el sistema de mejora
de formularios sigue funcionando correctamente.

![Las pruebas pasan correctamente](http://www.symfony-project.org/images/more-with-symfony/config_cache_tests_5_ok.png)

Haciendo uso de formularios embebidos
-------------------------------------

Todavía no hemos tenido en cuenta una de las características más importantes:
los formularios embebidos. Si se embebe una instancia de `CommentForm` dentro
de otro formulario, no se le aplicarán las mejoras realizadas en `forms.yml`.
Esto es muy fácil de comprobar mediante las pruebas unitarias:

    [php]
    $t = new lime_test(6);

    // ...

    $form = new BaseForm();
    $form->embedForm('comment', new CommentForm());
    $form->bind();
    $enhancer->enhance($form);
    $t->like($form['comment']['body']->renderLabel(),
      '/Please enter your comment/',
      '->enhance() enhances embedded forms');

La nueva comprobación demuestra que las mejoras no se están aplicando en los
formularios embebidos:

![Las pruebas fallan](http://www.symfony-project.org/images/more-with-symfony/config_cache_tests_6_not_ok.png)

Para solucionar este problema es imprescindible crear un gestor de configuración
más avanzado. Este nuevo gestor aplica las mejoras de `forms.yml` de forma
modular para tener en cuenta los formularios embebidos. Así que se va a generar
un método específico para cada clase de formulario configurada. Estos nuevos
métodos se van a generar en una nueva clase de tipo *worker* de nuestro gestor
propio de configuración.

    [php]
    class sfFormYamlEnhancementsConfigHander extends sfYamlConfigHandler
    {
      // ...

      protected function getEnhancerCode($fields)
      {
        $code = array();
        foreach ($fields as $field => $config)
        {
          $code[] = sprintf('if (isset($fields[%s]))', var_export($field, true));
          $code[] = '{';

          if (isset($config['label']))
          {
            $code[] = sprintf('  $fields[%s]->getWidget()->setLabel(%s);',
              var_export($config['label'], true));
          }

          if (isset($config['attributes']))
          {
            $code[] = '  $fields[%s]->getWidget()->setAttributes(array_merge(';
            $code[] = '    $fields[%s]->getWidget()->getAttributes(),';
            $code[] = '    '.var_export($config['attributes'], true);
            $code[] = '  ));';
          }

          if (isset($config['errors']))
          {
            $code[] = sprintf('  if ($error = $fields[%s]->getError())',
              var_export($field, true));
            $code[] = '  {';
            $code[] = '    $error->getValidator()->setMessages(array_merge(';
            $code[] = '      $error->getValidator()->getMessages(),';
            $code[] = '      '.var_export($config['errors'], true);
            $code[] = '    ));';
            $code[] = '  }';
          }

          $code[] = '}';
        }

        return implode(PHP_EOL.'    ', $code);
      }
    }

Durante la generación del código se comprueba si existen determinadas claves
en el array de configuración, en vez de realizar la comprobación en tiempo de
ejecución. Se trata de un pequelo detalle que permite aumentar el rendimiento
de la aplicación.

>**TIP**
>Como regla general, la lógica que comprueba las condiciones de la configuración
>se debería realizar en el propio gestor de configuración y no en el código
>generado. La lógica que comprueba las condiciones en tiempo de ejecución,
>como por ejemplo el tipo de formulario que se está mejorando, deben realizarse
>en tiempo de ejecución.

Este código generado se incluye dentro de la definición de una clase y luego
se guarda en el directorio de la cache.

    [php]
    class sfFormYamlEnhancementsConfigHander extends sfYamlConfigHandler
    {
      public function execute($configFiles)
      {
        $forms = self::getConfiguration($configFiles);

        $code = array();
        $code[] = '<?php';
        $code[] = '// auto-generated by '.__CLASS__;
        $code[] = '// date: '.date('Y/m/d H:is');
        $code[] = 'class sfFormYamlEnhancementsWorker';
        $code[] = '{';
        $code[] = '  static public $enhancable = '.var_export(array_keys($forms), true).';';

        foreach ($forms as $class => $fields)
        {
          $code[] = '  static public function enhance'.$class.'(sfFormFieldSchema $fields)';
          $code[] = '  {';
          $code[] = '    '.$this->getEnhancerCode($fields);
          $code[] = '  }';
        }

        $code[] = '}';

        return implode(PHP_EOL, $code);
      }

      // ...
    }

La clase `sfFormYamlEnhancer` ahora deriva el trabajo de manipulación de los
formularios a la clase de tipo *worker* generada, pero debe controlar la
recursión de los formularios embebidos. Para ello es necesario procesar en paralelo
el esquema de los campos del formulario (iterándolo de forma recursiva) y el objeto
del formulario (que incluye los formularios embebidos).

    [php]
    class sfFormYamlEnhancer
    {
      // ...

      public function enhance(sfForm $form)
      {
        require_once $this->configCache->checkConfig('config/forms.yml');
        $this->doEnhance($form->getFormFieldSchema(), $form);
      }

      protected function doEnhance(sfFormFieldSchema $fieldSchema, sfForm $form)
      {
        if ($enhancer = $this->getEnhancer(get_class($form)))
        {
          call_user_func($enhancer, $fieldSchema);
        }

        foreach ($form->getEmbeddedForms() as $name => $form)
        {
          if (isset($fieldSchema[$name]))
          {
            $this->doEnhance($fieldSchema[$name], $form);
          }
        }
      }

      public function getEnhancer($class)
      {
        if (in_array($class, sfFormYamlEnhancementsWorker::$enhancable))
        {
          return array('sfFormYamlEnhancementsWorker', 'enhance'.$class);
        }
        else if ($overlap = array_intersect(class_parents($class),
          sfFormYamlEnhancementsWorker::$enhancable))
        {
          return array('sfFormYamlEnhancementsWorker', 'enhance'.current($overlap));
        }
      }
    }

>**NOTE**
>Los campos de los formularios embebidos no se deben modificar después de haber
>sido embebidos. Los formularios embebidos se guardan en su formulario padre
>simplemente por razones de procesamiento, ya que no afectan a la forma en la
>que se muestra el formulario padre.

Ahora que ya está disponible el soporte de los formularios embebidos, las pruebas
deberían ejecutarse correctamente. Si ejecutas las pruebas, verás el siguiente
resultado:

![Las pruebas pasan correctamente](http://www.symfony-project.org/images/more-with-symfony/config_cache_tests_6_ok.png)

Comparando el rendimiento
-------------------------

Para comprobar si todo nuestro esfuerzo ha valido la pena, se van a realizar
unas pruebas de rendimiento. Para que los resultados sean más interesantes,
se añaden muchas clases de formulario en `forms.yml` mediante un bucle de PHP.

    [yml]
    # <?php for ($i = 0; $i < 100; $i++): ?> #
    Form<?php echo $i ?>: ~
    # <?php endfor; ?> #

A continuación crea todas esas clases mediante el siguiente código:

    [php]
    mkdir($dir = sfConfig::get('sf_lib_dir').'/form/test_fixtures');
    for ($i = 0; $i < 100; $i++)
    {
      file_put_contents($dir.'/Form'.$i.'.class.php',
        '<?php class Form'.$i.' extends BaseForm { }');
    }

Ahora ya se pueden realizar las pruebas de rendimiento, para las cuales se ha
ejecutado el siguiente comando de la [herramienta ab de Apache](http://httpd.apache.org/docs/2.0/programs/ab.html)
en un ordenador tipo MacBook varias veces hasta que la desviación estándar
fuera inferior a 2ms.

    $ ab -t 60 -n 20 http://localhost/config_cache/web/index.php

El valor base para las comparaciones será el resultado de las pruebas de
rendimiento cuando la aplicación no utiliza la característica que mejora los
formularios. Comenta la línea de código de `sfFormYamlEnhancer` en
`frontendConfiguration` y ejecuta las pruebas de rendimiento:

    Connection Times (ms)
                  min  mean[+/-sd] median   max
    Connect:        0    0   0.0      0       0
    Processing:    62   63   1.5     63      69
    Waiting:       62   63   1.5     63      69
    Total:         62   63   1.5     63      69

A continuación se emplea la primera versión de `sfFormYamlEnhancer::enhance()`
que utilizaba directamente `sfYaml`:

    Connection Times (ms)
                  min  mean[+/-sd] median   max
    Connect:        0    0   0.0      0       0
    Processing:    87   88   1.6     88      93
    Waiting:       87   88   1.6     88      93
    Total:         87   88   1.7     88      94

Se han añadido unos 25 ms de media en cada petición, un aumento cercano al 40%.
Por último, se muestran las pruebas de rendimiento cuando se emplea el gestor
propio de configuración:

    Connection Times (ms)
                  min  mean[+/-sd] median   max
    Connect:        0    0   0.0      0       0
    Processing:    62   63   1.6     63      70
    Waiting:       62   63   1.6     63      70
    Total:         62   64   1.6     63      70

Como puedes ver, hemos vuelto a obtener los mismos resultados que al principio
gracias al uso del gestor propio de configuración.

Creando un plugin
-----------------

Como ya disponemos de un sistema para mejorar los formularios mediante una
configuración sencilla basada en YAML, se va a publicar en forma de plugin para
que la pueda disfrutar toda la comunidad. Si nunca has publicado un plugin, puede
que pienses que es algo complicado, pero ahora vas a ver que no es tan difícil.

La estructura de archivos del plugin es la siguiente:

    sfFormYamlEnhancementsPlugin/
      config/
        sfFormYamlEnhancementsPluginConfiguration.class.php
      lib/
        config/
          sfFormYamlEnhancementsConfigHander.class.php
        form/
          sfFormYamlEnhancer.class.php
      test/
        unit/
          form/
            sfFormYamlEnhancerTest.php

Para facilitar la instalación del plugin se van a realizar algunos pequeños
cambios. La creación y conexión del objeto que mejora los formularios se va a
encapsular en la clase de configuración del plugin:

    [php]
    class sfFormYamlEnhancementsPluginConfiguration extends sfPluginConfiguration
    {
      public function initialize()
      {
        if ($this->configuration instanceof sfApplicationConfiguration)
        {
          $enhancer = new sfFormYamlEnhancer($this->configuration->getConfigCache());
          $enhancer->connect($this->dispatcher);
        }
      }
    }

El archivo de las pruebas debe ser actualizado para que apunte al script de
inicialización del proyecto:

    [php]
    include dirname(__FILE__).'/../../../../../test/bootstrap/unit.php';

    // ...

Por último, activa el plugin en `ProjectConfiguration`:

    [php]
    class ProjectConfiguration extends sfProjectConfiguration
    {
      public function setup()
      {
        $this->enablePlugins('sfFormYamlEnhancementsPlugin');
      }
    }

Si quieres ejecutar las pruebas desde el plugin, puedes conectarlas en
`ProjectConfiguration`:

    [php]
    class ProjectConfiguration extends sfProjectConfiguration
    {
      // ...

      public function setupPlugins()
      {
        $this->pluginConfigurations['sfFormYamlEnhancementsPlugin']->connectTests();
      }
    }

Ahora las pruebas del plugin se ejecutan siempre que se ejecute una tarea de
tipo `test:*`.

![Pruebas del plugin](http://www.symfony-project.org/images/more-with-symfony/config_cache_plugin_tests.png)

Todas las clases se encuentran ahora dentro del directorio del plugin, pero
existe un problema: las pruebas unitarias dependen de archivos que todavía se
encuentran en el proyecto. Esto significa que quien quiera ejecutar las pruebas
no va a poder hacerlo a menos que disponga exactamente de los mismos archivos
en su proyecto.

Para solucionar este problema debemos asilar el código que realiza llamadas a
la cache de configuración de forma que se pueda redefinir dentro del archivo de
pruebas y en su lugar utilice un archivo de datos `forms.yml`.

    [php]
    class sfFormYamlEnhancer
    {
      // ...

      public function enhance(sfForm $form)
      {
        $this->loadWorker();
        $this->doEnhance($form->getFormFieldSchema(), $form);
      }

      public function loadWorker()
      {
        require_once $this->configCache->checkConfig('config/forms.yml');
      }

      // ...
    }

Se puede redefinir el método `->loadWorker()` en nuestro archivo de pruebas para
que llame directamente al gestor propio de configuración. La clase `CommentForm`
también se debe trasladar al archivo de pruebas y el archivo `forms.yml` se debe
guardar dentro del directorio `test/fixtures` del plugin.

    [php]
    include dirname(__FILE__).'/../../../../../test/bootstrap/unit.php';

    $t = new lime_test(6);

    class sfFormYamlEnhancerTest extends sfFormYamlEnhancer
    {
      public function loadWorker()
      {
        if (!class_exists('sfFormYamlEnhancementsWorker', false))
        {
          $configHandler = new sfFormYamlEnhancementsConfigHander();
          $code = $configHandler->execute(array(dirname(__FILE__).'/../../fixtures/forms.yml'));

          $file = tempnam(sys_get_temp_dir(), 'sfFormYamlEnhancementsWorker');
          file_put_contents($file, $code);

          require $file;
        }
      }
    }

    class CommentForm extends BaseForm
    {
      public function configure()
      {
        $this->setWidget('body', new sfWidgetFormTextarea());
        $this->setValidator('body', new sfValidatorString(array('min_length' => 12)));
      }
    }

    $configuration = $configuration->getApplicationConfiguration(
      'frontend', 'test', true, null, $configuration->getEventDispatcher());

    $enhancer = new sfFormYamlEnhancerTest($configuration->getConfigCache());

    // ...

Crear el paquete del plugin es muy sencillo si has instalado previamente el
plugin `sfTaskExtraPlugin`. Simplemente ejecuta la tarea `plugin:package` y
se creará el paquete utilizando la información proporcionada para cada pregunta
del plugin.

    $ php symfony plugin:package sfFormYamlEnhancementsPlugin

>**NOTE**
>El código de este artículo se ha publicado como plugin y está disponible para
>descargar desde el sitio de los plugins de Symfony:
>
>    http://symfony-project.org/plugins/sfFormYamlEnhancementsPlugin
>
>Este plugin incluye todo lo que se ha mostrado en este capítulo y mucho más,
>ya que incluye soporte para los archivos `widgets.yml` y `validators.yml`, así
>como integración con la tarea `i18n:extract` para internacionalizar fácilmente
>tus formularios.

Conclusión
----------

Como ha quedado demostrado con las pruebas de rendimiento, la cache de configuración
de Symfony permite utilizar la simplicidad de los archivos de configuración
YAML sin afectar al rendimiento de la aplicación.
