Widgets y validadores propios
=============================

*por Thomas Rabaix*

Este capítulo explica cómo crear un widget y un validador propios para el
framework de formularios. Además de explicar el funcionamiento interno de
`sfWidgetForm` y `sfValidator` se van a crear widgets simples y avanzados.

Cómo funciona un widget y un validador
--------------------------------------

### Funcionamiento interno de `sfWidgetForm`

Un objeto de la clase ~`sfWidgetForm`~ representa la implementación visual
necesaria para modificar los datos relacionados. Una cadena de texto por
ejemplo se puede editar con un cuadro de texto simple o con un editor WYSIWYG.
Para permitir una mejor configuración, la clase `sfWidgetForm` tiene dos propiedades
importantes: `options` y `attributes`.

 * `options`: se emplean para configurar el widget (por ejemplo la consulta a
   la base de datos cuando se crea una lista de elementos para mostrar en una
   lista desplegable)

 * `attributes`: atributos HTML que se añaden al elemento antes de mostrarlo

Además, la clase `sfWidgetForm` implementa dos métodos importantes:

 * `configure()`: define qué opciones son *opcionales* y cuales *obligatorias*.
   Aunque redefinir el constructor no es una buena práctica, el método `configure()`
   se puede redefinir sin problemas.

 * `render()`: genera el código HTML del widget. El primer argumento del método
   es obligatorio e indica el nombre del widget HTML. El segundo argumento es
   opcional e indica el valor.

>**NOTE**
>Un objeto `sfWidgetForm` no sabe nada acerca de su nombre o su valor. El
>componente sólo se encarga de mostrar el widget. El nombre y el valor los
>gestiona un objeto de tipo `sfFormFieldSchema`, que es lo que une los datos
>con los widgets.

### Funcionamiento interno de `sfValidatorBase`

La clase ~`sfValidatorBase`~ es la clase de la que heredan todos los validadores.
El método ~`sfValidatorBase::clean()`~ es el más importante de esta clase, ya
que comprueba si el valor es válido en función de las opciones indicadas.

Internamente el método `clean()` realiza diferentes acciones:

 * en las cadenas de texto elimina el posible espacio en blanco inicial y final
   (si se especifica la opción `trim`)
 * comprueba si el valor es vacío
 * invoca el método `doClean()` del validador

El método `doClean()` es el método que incluye toda la lógica de validación. No
es una buena práctica redefinir el método `clean()`, ya que es preferible
incluir toda la lógica propia en el método `doClean()`.

Un validador también se puede utilizar como un componente independiente para
comprobar la integridad de los datos introducidos. El validador `sfValidatorEmail`
comprueba por ejemplo si el email es válido:

    [php]
    $v = new sfValidatorEmail();

    try
    {
      $v->clean($request->getParameter("email"));
    }
    catch(sfValidatorError $e)
    {
      $this->forward404();
    }

>**NOTE**
>Cuando se asocia el formulario con los valores de la petición, el objeto `sfForm`
>guarda tanto los valores originales (*sucios*) como los validados (*limpios*).
>Los valores originales se utilizan al volver a mostrar el formulario, mientras
>que los valores validados se utilizan en la aplicación (por ejemplo para guardar
>el objeto).

### El atributo `options`

Los objetos `sfWidgetForm` y `sfValidatorBase` también tienen otras opciones:
algunas son opcionales y otras son obligatorias. Estas opciones se definen en
el método `configure()` de cada clase mediante:

 * `addOption($nombre, $valor)`: define una opción con un nombre y un valor inicial
 * `addRequiredOption($nombre)`: define una opción obligatoria

Estos dos métodos son muy útiles porque aseguran que se pasan correctamente los
valores de los que dependen los validadores y los widgets.

Creando un widget simple y un validador
---------------------------------------

Esta sección explica cómo crear un widget simple. Llamaremos a este widget
*"Trilean"*, ya que se trata de un valor *booleano* con tres opciones, que se
muestran en una lista desplegable: `No`, `Yes` y `Null`. 

    [php]
    class sfWidgetFormTrilean extends sfWidgetForm
    {
      public function configure($options = array(), $attributes = array())
      {

        $this->addOption('choices', array(
          0 => 'No',
          1 => 'Yes',
          'null' => 'Null'
        ));
      }

      public function render($name, $value = null, $attributes = array(), $errors = array())
      {
        $value = $value === null ? 'null' : $value;

        $options = array();
        foreach ($this->getOption('choices') as $key => $option)
        {
          $attributes = array('value' => self::escapeOnce($key));
          if ($key == $value)
          {
            $attributes['selected'] = 'selected';
          }

          $options[] = $this->renderContentTag(
            'option',
            self::escapeOnce($option),
            $attributes
          );
        }

        return $this->renderContentTag(
          'select',
          "\n".implode("\n", $options)."\n",
          array_merge(array('name' => $name), $attributes
        ));
      }
    }

El método `configure()` define la lista de valores mediante la opción `choices`.
Este array se puede redefinir por ejemplo para modificar el título asociado a
cada valor. No existe un límite en el número de opciones que puede definir un
widget. No obstante, la clase base de los widgets declara una serie de opciones
estándar que funcionan como opciones reservadas *de facto*:

 * `id_format`: el formato del identificador, cuyo valor por defecto es '%s'

 * `is_hidden`: valor *booleano* que indica si el widget es un campo oculto
   (utilizado por `sfForm::renderHiddenFields()` para mostrar a la vez todos
   los campos ocultos)

 * `needs_multipart`: valor *booleano* que indica si la etiqueta del formulario
   debe incluir la opción `multipart` (necesaria cuando se suben archivos)

 * `default`: el valor por defecto que se utiliza al mostrar el widget a menos
   que no se proporcione otro valor

 * `label`: el título por defecto del widget

El método `render()` genera el código HTML de la lista desplegable. En realidad,
el método invoca la función interna `renderContentTag()` para facilitar la
creación de las etiquetas HTML.

El widget ya está terminado, por lo que a continuación se crea su validador:

    [php]
    class sfValidatorTrilean extends sfValidatorBase
    {
      protected function configure($options = array(), $messages = array())
      {
        $this->addOption('true_values', array('true', 't', 'yes', 'y', 'on', '1'));
        $this->addOption('false_values', array('false', 'f', 'no', 'n', 'off', '0'));
        $this->addOption('null_values', array('null', null));
      }

      protected function doClean($value)
      {
        if (in_array($value, $this->getOption('true_values')))
        {
          return true;
        }

        if (in_array($value, $this->getOption('false_values')))
        {
          return false;
        }

        if (in_array($value, $this->getOption('null_values')))
        {
          return null;
        }

        throw new sfValidatorError($this, 'invalid', array('value' => $value));
      }

      public function isEmpty($value)
      {
        return false;
      }
    }

El validador `sfValidatorTrilean` define tres opciones en su método `configure()`.
Cada opción es un conjunto de valores válidos. Como se definen mediante opciones,
el programador puede personalizar los valores de acuerdo a sus necesidades.

El método `doClean()` comprueba si el valor se encuentra dentro de los valores
definidos como válidos y devuelve el valor *limpio*. Si el valor no coincide con
ninguno de los permitidos, se lanza una excepción de tipo `sfValidatorError`,
que es el error de validació estándar en el framework de formularios.

El último método llamado `isEmpty()` se redefine porque por defecto devuelve
`true` si el valor es `null`. Como el widget creado permite el uso de `null`
como valor válido, el método siempre debe devolver `false`.

>**Note**:
> Si el método `isEmpty()` devuelve `true`, no se ejecuta el método `doClean()`.

Aunque este widget era muy sencillo, ha servido para introducir algunas
características básicas muy importantes que se necesitarán más adelante. La
siguiente sección crea un widget más complejo formado por varios campos y que
también hace uso de JavaScript.

El widget de direcciones con Google Maps
----------------------------------------

En esta sección se va a crear un widget avanzado, por lo que se utilizarán
nuevos métodos e incluso se integrará código JavaScript. El widget se llamará
"GMAW, acrónimo de *"Google Map Address Widget"*.

¿Cuál es el propósito de este widget? Su objetivo principal es permitir que el
usuario pueda introducir una dirección. Para ello se va a hacer uso de un cuadro
de texto sencillo y del servicio de mapas de Google.

!["Google Map Address Widget"](http://www.symfony-project.org/images/more-with-symfony/widgets-figure-01.png "Google Map Address Widget")

Caso de uso 1:

 * El usuario introduce una dirección.
 * El usuario pulsa el botón *"lookup"*.
 * Se crea un nuevo marcador en el mapa y se actualizan los campos ocultos que
   guardan la longitud y la latitud. El marcador del mapa se coloca en la
   localización de la dirección indicada. Si el servicio de geocodificación de
   Google no puede encontrar la dirección, se muestra un mensaje de error.

Caso de uso 2:

 * El usuario pincha en el mapa.
 * Se actualizan los campos ocultos de longitud y latitud.
 * Se realiza una búsqueda inversa para determinar la dirección que corresponde
   a ese punto del mapa.

*El formulario debe incluir y gestionar los siguientes campos:*

 * `latitude`: valor decimal entre 90 y -90
 * `longitude`: valor decimal entre 180 y -180
 * `address`: cadena de texto

Una vez definidas las especificaciones funcionales del widget, se definen a
continuación las herramientas técnicas y sus responsabilidades:

 * Google maps y los servicios de geocodificación: muestra el mapa y obtiene
   la información de la dirección
 * jQuery: añade las interacciones JavaScript entre el formulario y el campo
 * sfForm: muestra el widget y valida los datos introducidos

### Widget `sfWidgetFormGMapAddress`

Como un widget es la representación visual de los datos, el método `configure()`
del widget debe incluir diferentes opciones para configurar el mapa de Google
o para modificar los estilos de cada elemento. Una de las opciones más importantes
es `template.html`, que define cómo se ordenan todos los elementos. Cuando se
crea un widget es muy importante pensar en su extensibilidad y en cómo reutilizarlo.

Otro aspecto importante es la definición de los archivos externos. Las clases
`sfWidgetForm` pueden implementar dos métodos específicos:

 * `getJavascripts()` devuelve un array de archivos JavaScript

 * `getStylesheets()` devuelve un array de archivos CSS (donde la clave es la
   ruta del archivo y su valor es el atributo `media`).

El widget que se está creando sólo hace uso de JavaScript, por lo que no se
necesita ningún archivo CSS. En este caso, el widget no se encargará de
inicializar el código JavaScript de Google, a pesar de que luego hará uso de
los mapas y la geocodificación de Google. En su lugar, es responsabilidad del
programador incluir los archivos necesarios en la página. El motivo es que otros
elementos de la página diferentes al widget también podrían hacer uso de los
servicios de Google.

A continuación se muestra el código necesario:

    [php]
    class sfWidgetFormGMapAddress extends sfWidgetForm
    {
      public function configure($options = array(), $attributes = array())
      {
        $this->addOption('address.options', array('style' => 'width:400px'));

        $this->setOption('default', array(
          'address' => '',
          'longitude' => '2.294359',
          'latitude' => '48.858205'
        ));

        $this->addOption('div.class', 'sf-gmap-widget');
        $this->addOption('map.height', '300px');
        $this->addOption('map.width', '500px');
        $this->addOption('map.style', "");
        $this->addOption('lookup.name', "Lookup");

        $this->addOption('template.html', '
          <div id="{div.id}" class="{div.class}">
            {input.search} <input type="submit" value="{input.lookup.name}"  id="{input.lookup.id}" /> <br />
            {input.longitude}
            {input.latitude}
            <div id="{map.id}" style="width:{map.width};height:{map.height};{map.style}"></div>
          </div>
        ');

         $this->addOption('template.javascript', '
          <script type="text/javascript">
            jQuery(window).bind("load", function() {
              new sfGmapWidgetWidget({
                longitude: "{input.longitude.id}",
                latitude: "{input.latitude.id}",
                address: "{input.address.id}",
                lookup: "{input.lookup.id}",
                map: "{map.id}"
              });
            })
          </script>
        ');
      }

      public function getJavascripts()
      {
        return array(
          '/sfFormExtraPlugin/js/sf_widget_gmap_address.js'
        );
      }

      public function render($name, $value = null, $attributes = array(), $errors = array())
      {
        // definir las variables principales de la plantilla
        $template_vars = array(
          '{div.id}'             => $this->generateId($name),
          '{div.class}'          => $this->getOption('div.class'),
          '{map.id}'             => $this->generateId($name.'[map]'),
          '{map.style}'          => $this->getOption('map.style'),
          '{map.height}'         => $this->getOption('map.height'),
          '{map.width}'          => $this->getOption('map.width'),
          '{input.lookup.id}'    => $this->generateId($name.'[lookup]'),
          '{input.lookup.name}'  => $this->getOption('lookup.name'),
          '{input.address.id}'   => $this->generateId($name.'[address]'),
          '{input.latitude.id}'  => $this->generateId($name.'[latitude]'),
          '{input.longitude.id}' => $this->generateId($name.'[longitude]'),
        );

        // evitar cualquier error o aviso sobre formatos no válidos de $value
        $value = !is_array($value) ? array() : $value;
        $value['address']   = isset($value['address'])   ? $value['address'] : '';
        $value['longitude'] = isset($value['longitude']) ? $value['longitude'] : '';
        $value['latitude']  = isset($value['latitude'])  ? $value['latitude'] : '';

        // definir el widget de la dirección
        $address = new sfWidgetFormInputText(array(), $this->getOption('address.options'));
        $template_vars['{input.search}'] = $address->render($name.'[address]', $value['address']);

        // definir los campos de longitud y latitud
        $hidden = new sfWidgetFormInputHidden;
        $template_vars['{input.longitude}'] = $hidden->render($name.'[longitude]', $value['longitude']);
        $template_vars['{input.latitude}']  = $hidden->render($name.'[latitude]', $value['latitude']);

        // combinar las plantillas y las variables
        return strtr(
          $this->getOption('template.html').$this->getOption('template.javascript'),
          $template_vars
        );
      }
    }

Este widget utiliza el método `generateId()` para generar el `id` de cada elemento.
La variable `$name` se define en `sfFormFieldSchema`, por lo que `$name` está
compuesta por el nombre del formulario, cualquier otro nombre de un esquema de
widgets y el propio nombre del widget definido en el método `configure()`.

>**NOTE**
>Si por ejemplo el nombre del formulario es `user`, el nombre del esquema de
>widgets es `location` y el nombre del widget es `address`, el valor completo
>de `name` será `user[location][address]` y el `id` será `user_location_address`.
>En otras palabras, `$this->generateId($name.'[latitude]')` generará un valor
>`id` válido y único para el campo `latitude`.

Los diferentes atributos `id` de los elementos son muy importantes porque se
pasan al bloque de código JavaScript (mediante la variable `template.js`), de
forma que los elementos se puedan manipular correctamente desde JavaScript.

El método `render()` también instancia dos widgets internos: el widget
`sfWidgetFormInputText`, que se emplea para mostrar el campo de la dirección
y un widget `sfWidgetFormInputHidden` que es el que se emplea para incluir los
campos ocultos.

El widget se puede probar fácilmente con el siguiente trozo de código:

    [php]
    $widget = new sfWidgetFormGMapAddress();
    echo $widget->render('user[location][address]', array(
      'address' => '151 Rue montmartre, 75002 Paris',
      'longitude' => '2.294359',
      'latitude' => '48.858205'
    ));

El código HTML y JavaScript resultante es el siguiente:

    [html]
    <div id="user_location_address" class="sf-gmap-widget">
      <input style="width:400px" type="text" name="user[location][address][address]" value="151 Rue montmartre, 75002 Paris" id="user_location_address_address" />
      <input type="submit" value="Lookup"  id="user_location_address_lookup" /> <br />
      <input type="hidden" name="user[location][address][longitude]" value="2.294359" id="user_location_address_longitude" />
      <input type="hidden" name="user[location][address][latitude]" value="48.858205" id="user_location_address_latitude" />
      <div id="user_location_address_map" style="width:500px;height:300px;"></div>
    </div>

    <script type="text/javascript">
      jQuery(window).bind("load", function() {
        new sfGmapWidgetWidget({
          longitude: "user_location_address_longitude",
          latitude: "user_location_address_latitude",
          address: "user_location_address_address",
          lookup: "user_location_address_lookup",
          map: "user_location_address_map"
        });
      })
    </script>

La parte JavaScript del widget toma los diferentes atributos `id` y les asocia
*listeners* de jQuery para ejecutar cierto código JavaScript como respuesta a
las acciones del usuario. El código JavaScript actualiza los campos ocultos con
la longitud y latitud proporcionadas por el servicio de geocodificación de
Google.

El objeto JavaScript dispone de varios métodos interesantes:

 * `init()`: es el método en el que se inicializan todas las variables y se
   asocian los diferentes eventos

 * `lookupCallback()`: método *static* que utiliza el geocodificador para buscar
   la dirección indicada por el usuario

 * `reverseLookupCallback()`: otro método *static* utilizado por el geocodificador
   para convertir la longitud y latitud en una dirección válida.

El código JavaScript completo se puede ver en el Apéndice A.

Si quieres conocer detalladamente todas las funcionalidades de los mapas de
Google, por favor consulta la documentación de su [API](http://code.google.com/apis/maps/).

### Validador `sfValidatorGMapAddress`

La clase `sfValidatorGMapAddress` hereda de `sfValidatorBase`, que ya realiza
una validación: si el valor del campo se establece como es debido, su valor no
puede ser `null`. Por tanto, `sfValidatorGMapAddress` sólo necesita validar tres
valores diferentes: `latitude`, `longitude` y `address`. La variable `$value`
debería ser un array, pero como no podemos confiar en lo que introduzca el
usuario, el validador comprueba que todas las claves están presentes, de forma
que se pasen valores válidos a cada validador.

    [php]
    class sfValidatorGMapAddress extends sfValidatorBase
    {
      protected function doClean($value)
      {
        if (!is_array($value))
        {
          throw new sfValidatorError($this, 'invalid');
        }

        try
        {
          $latitude = new sfValidatorNumber(array( 'min' => -90, 'max' => 90, 'required' => true ));
          $value['latitude'] = $latitude->clean(isset($value['latitude']) ? $value['latitude'] : null);

          $longitude = new sfValidatorNumber(array( 'min' => -180, 'max' => 180, 'required' => true ));
          $value['longitude'] = $longitude->clean(isset($value['longitude']) ? $value['longitude'] : null);

          $address = new sfValidatorString(array( 'min_length' => 10, 'max_length' => 255, 'required' => true ));
          $value['address'] = $address->clean(isset($value['address']) ? $value['address'] : null);
        }
        catch(sfValidatorError $e)
        {
          throw new sfValidatorError($this, 'invalid');
        }

        return $value;
      }
    }

>**NOTE**
>Un validador siempre lanza una excepción de tipo `sfValidatorError` cuando un
>valor no es válido. Ese es el motivo por el que se encierra la validación en
>un bloque `try/catch`. En este caso, el validador lanza una nueva excepción de
>tipo `invalid`, que equivale a un error de validación de tipo `invalid` en el
>validador `sfValidatorGMapAddress`.

### Pruebas

¿Por qué son importantes las pruebas? El validador es lo que une la aplicación
con los datos introducidos por el usuario. Si el validador contiene errores, la
aplicación será vulnerable. Afortunadamente, Symfony incluye la librería de
pruebas llamada `lime`, que facilita la creación de pruebas unitarias y funcionales.

¿Cómo se puede probar el validador? Como se comentó anteriormente, un validador
lanza una excepción cada vez que se produce un error de validación. Por tanto,
para probarlo se le pasan valores válidos y erróneos y se comprueba si se lanza
una excepción en los valores erróneos.

    [php]
    $t = new lime_test(7, new lime_output_color());

    $tests = array(
      array(false, '', 'empty value'),
      array(false, 'string value', 'string value'),
      array(false, array(), 'empty array'),
      array(false, array('address' => 'my awesome address'), 'incomplete address'),
      array(false, array('address' => 'my awesome address', 'latitude' => 'String', 'longitude' => 23), 'invalid values'),
      array(false, array('address' => 'my awesome address', 'latitude' => 200, 'longitude' => 23), 'invalid values'),
      array(true, array('address' => 'my awesome address', 'latitude' => '2.294359', 'longitude' => '48.858205'), 'valid value')
    );

    $v = new sfValidatorGMapAddress;

    $t->diag("Testing sfValidatorGMapAddress");

    foreach($tests as $test)
    {
      list($validity, $value, $message) = $test;

      try
      {
        $v->clean($value);
        $catched = false;
      }
      catch(sfValidatorError $e)
      {
        $catched = true;
      }

      $t->ok($validity != $catched, '::clean() '.$message);
    }

Cuando se invoca el método `sfForm::bind()`, el formulario ejecuta el método
`clean()` de cada validador. La prueba anterior reproduce ese comportamiento
instanciando directamente el validador `sfValidatorGMapAddress` y probando los
diferentes valores.

Conclusiones
------------

El error más común cuando se crea un widget consiste en centrarse demasiado
en cómo almacenar la información en la base de datos. El framework de formularios
es simplemente un framework para validar y guardar valores. Por tanto, un widget
sólo debe gestionar su propia información. Si la información es correcta, los
diferentes valores validados pueden ser utilizados en el modelo o en el controlador.
