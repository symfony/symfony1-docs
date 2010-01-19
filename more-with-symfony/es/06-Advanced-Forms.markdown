Formularios avanzados
=====================

*por Ryan Weaver, Fabien Potencier*

El framework de formularios de Symfony proporciona al programador todas las
herramientas necesarias para mostrar y validar fácilmente datos en un formulario
de forma similar a los objetos. Gracias a las clases ~`sfFormDoctrine`~ y
~`sfFormPropel`~ ofrecidas por cada ORM, el framework de formularios puede
mostrar y guardar fácilmente formularios que están íntimamente relacionados
con la capa de datos.

No obstante, en los proyectos reales suele ser habitual que el programador
tenga que personalizar o extender los formularios. En este capítulo se
presentan varios problemas comunes de los formularios pero que son difíciles de
solucionar. También se diseccionará el objeto ~`sfForm`~ para descubrir algunos
de sus misterios.

Mini-proyecto: productos y fotos
--------------------------------

El primer problema está relacionado con la edición de un producto que puede
contener un número ilimitado de fotos. El usuario debe poder editar el producto
y todas sus fotos en el mismo formulario. Además el usuario puede subir hasta
dos nuevas fotos del producto simultáneamente. A continuación se muestra uno
de los posibles esquemas de este proyecto:

    [yml]
    Product:
      columns:
        name:           { type: string(255), notnull: true }
        price:          { type: decimal, notnull: true }

    ProductPhoto:
      columns:
        product_id:     { type: integer }
        filename:       { type: string(255) }
        caption:        { type: string(255), notnull: true }
      relations:
        Product:
          alias:        Product
          foreignType:  many
          foreignAlias: Photos
          onDelete:     cascade

Cuando esté terminado, el formulario tendrá el siguiente aspecto:

![Formulario de producto y fotografías](http://www.symfony-project.org/images/more-with-symfony/advanced_forms_01.png "Formulario de producto con formularios ProductPhoto embebidos")

Aprendiendo más haciendo los ejemplos
-------------------------------------

La mejor forma de aprender las técnicas avanzadas consiste en seguir paso a paso
las instrucciones probando todos los ejemplos. Gracias a la opción `--installer`
de [symfony](#chapter_03), ha sido muy sencillo crear un proyecto completo con
una base de datos SQLite lista para funcionar, el esquema de datos de Doctrine,
algunos archivos de datos, una aplicación `frontend` y un módulo `product`.
Descarga el
[script](http://www.symfony-project.org/images/more-with-symfony/advanced_form_installer.php.src)
del instalador y ejecuta el siguiente comando para crear el proyecto Symfony:

    $ php symfony generate:project advanced_form --installer=/ruta/hasta/advanced_form_installer.php

El comando anterio crear un proyecto completamente funcional con el esquema de
datos mostrado en la sección anterior.

>**NOTE**
>En este capítulo, las rutas de los archivos corresponden a un proyecto Symfony
>que hace uso de Doctrine, tal y como genera la tarea anterior.

Configuración básica del formulario
-----------------------------------

Los requerimientos de la aplicación obligan a modificar dos modelos diferentes
(`Product` y `ProductPhoto`), por lo que la solución debe hacer uso de dos
formularios de Symfony (`ProductForm` y `ProductPhotoForm`). Afortunadamente,
el framework de formularios puede combinar fácilmente varios formularios en
uno solo mediante el método ~`sfForm::embedForm()`~. En primer lugar, configura
`ProductPhotoForm` de forma independiente. En este ejemplo, se va a utilizar
el campo `filename` como campo para subir archivos:

    [php]
    // lib/form/doctrine/ProductPhotoForm.class.php
    public function configure()
    {
      $this->useFields(array('filename', 'caption'));

      $this->setWidget('filename', new sfWidgetFormInputFile());
      $this->setValidator('filename', new sfValidatorFile(array(
        'mime_types' => 'web_images',
        'path' => sfConfig::get('sf_upload_dir').'/products',
      )));
    }

Este formulario requiere, automáticamente pero por diferentes razones, tanto un
campo llamado `caption` como un campo llamado `filename`. El campo `caption` es
obligatorio porque su columna relacionada en el esquema de datos ha definido la
propiedad `notnull` con un valor `true`. El campo `filename` es obligatorio
porque un objeto validador establece por defecto el valor `true` en la opción
`required`.

>**NOTE**
>~`sfForm::useFields()`~ es una nueva función de Symfony 1.3 que permite
>especificar exactamente qué campos del formulario se utilizan y en qué orden
>deben visualizarse. Todos los demás campos que no sean vacíos se eliminan del
>formulario.

Hasta ahora no hemos hecho más que la configuración habitual de los formularios.
A continuación se combinan varios formularios en uno solo.

Embebiendo formularios
----------------------

Haciendo uso de ~`sfForm::embedForm()`~, es posible combinar fácilmente los
formularios independientes `ProductForm` y `ProductPhotoForms`. Esta combinación
siempre se realiza en el formulario *principal*, que en este caso es `ProductForm`.
Los requerimientos de la aplicación exigen que se puedan subir hasta dos fotos
de producto a la vez. Para conseguirlo, se embeben dos objetos `ProductPhotoForm`
dentro de `ProductForm`:

    [php]
    // lib/form/doctrine/ProductForm.class.php
    public function configure()
    {
      $subForm = new sfForm();
      for ($i = 0; $i < 2; $i++)
      {
        $productPhoto = new ProductPhoto();
        $productPhoto->Product = $this->getObject();

        $form = new ProductPhotoForm($productPhoto);

        $subForm->embedForm($i, $form);
      }
      $this->embedForm('newPhotos', $subForm);
    }

Si accedes con tu navegador al módulo `product`, verás que ya es posible subir
dos objetos `ProductPhoto`, así como modificar el propio objeto `Product`.
Symfony guarda automáticamente los nuevos objetos `ProductPhoto` y los relaciona
con su correspondiente objeto `Product`. Incluso la subida de los archivos,
definida en `ProductPhotoForm`, funciona correctamente.

Ejecuta las siguientes tareas para comprobar que los registros se han guardado
correctamente en la base de datos:

    $ php symfony doctrine:dql --table "FROM Product"
    $ php symfony doctrine:dql --table "FROM ProductPhoto"

En la tabla `ProductPhoto` puedes ver los nombres de archivo de las fotos. Todo
funciona correctamente siempre que en el directorio `web/uploads/products/`
existan archivos con el mismo nombre que el guardado en la base de datos.

>**NOTE**
>Como los campos `filename` y `caption` son obligatorios en `ProductPhotoForm`,
>la validación del formulario principal siempre falla a menos que el usuario
>suba dos nuevas fotos. Sigue leyendo para aprender cómo solucionar este problema.

Refactorizando
--------------

Aunque el formulario anterior funciona como se esperaba, es mejor refactorizar
un poco su código para facilitar la creación de pruebas unitarias y para que
el código sea fácilmente reutilizable.

En primer lugar se aprovecha el código anterior para crear un nuevo formulario
que represente a una colección de `ProductPhotoForm`:

    [php]
    // lib/form/doctrine/ProductPhotoCollectionForm.class.php
    class ProductPhotoCollectionForm extends sfForm
    {
      public function configure()
      {
        if (!$product = $this->getOption('product'))
        {
          throw new InvalidArgumentException('You must provide a product object.');
        }

        for ($i = 0; $i < $this->getOption('size', 2); $i++)
        {
          $productPhoto = new ProductPhoto();
          $productPhoto->Product = $product;

          $form = new ProductPhotoForm($productPhoto);

          $this->embedForm($i, $form);
        }
      }
    }

Este formulario require dos opciones:

 * `product`: el producto para el que se crea una colección de `ProductPhotoForm`

 * `size`: el número de `ProductPhotoForm` que se crean (por defecto so dos)

Ahora se puede modificar el método `configure()` de `ProductForm` de la siguiente
forma:

    [php]
    // lib/form/doctrine/ProductForm.class.php
    public function configure()
    {
      $form = new ProductPhotoCollectionForm(null, array(
        'product' => $this->getObject(),
        'size'    => 2,
      ));

      $this->embedForm('newPhotos', $form);
    }

Diseccionando el objeto sfForm
------------------------------

En esencia, un formulario web es una colección de campos que se muestran y se
vuelven a enviar al servidor. Igualmente, el objeto ~`sfForm`~ es básicamente
un array de *campos* de formulario. ~`sfForm`~ gestiona el proceso completo,
pero los campos individuales son los responsables de mostrarse y validarse.

En Symfony, cada *campo* de formulario se define mediante dos objetos diferentes:

  * Un *widget* que muestra el código XHTML del campo de formulario

  * Un *validador* que *limpia* y valida los datos enviados en ese campo

>**TIP**
>En Symfony, un *widget* se define como cualquier objeto cuya única tarea consiste
>en generar código XHTML. Aunque normalmente se utilizan en los formularios,
>se puede crear un objeto de tipo widget para generar cualquier tipo de código.

### Un formulario es un array

Recuerda que un objeto ~`sfForm`~ es básicamente un array de *campos* de
formulario. De forma más precisa, `sfForm` incluye un array de widgets y un
array de validadores para todos los campos del formularios. Estos dos arrays,
llamados `widgetSchema` y `validatorSchema` son propiedades de la clase `sfForm`.
Para añadir un nuevo campo al formulario, simplemente se añade el widget del
campo en el array `widgetSchema` y el validador del campo en el array
`validatorSchema`. El siguiente código por ejemplo añade un campo `email` en
el formulario:

    [php]
    public function configure()
    {
      $this->widgetSchema['email'] = new sfWidgetFormInputText();
      $this->validatorSchema['email'] = new sfValidatorEmail();
    }

>**NOTE**
>Los arrays `widgetSchema` y `validatorSchema` en realidad son clases llamadas
>~`sfWidgetFormSchema`~ y ~`sfValidatorSchema`~ que implementan la interfaz
>`ArrayAccess`.

### Diseccionando el formulario `ProductForm`

Como la clase `ProductForm` hereda de `sfForm`, también incluye todos sus
widgets y validadores en los arrays `widgetSchema` y `validatorSchema`. A
continuación se muestra cómo se organiza cada array en el objeto `ProductForm`
final.

    [php]
    widgetSchema    => array
    (
      [id]          => sfWidgetFormInputHidden,
      [name]        => sfWidgetFormInputText,
      [price]       => sfWidgetFormInputText,
      [newPhotos]   => array(
        [0]           => array(
          [id]          => sfWidgetFormInputHidden,
          [filename]    => sfWidgetFormInputFile,
          [caption]     => sfWidgetFormInputText,
        ),
        [1]           => array(
          [id]          => sfWidgetFormInputHidden,
          [filename]    => sfWidgetFormInputFile,
          [caption]     => sfWidgetFormInputText,
        ),
      ),
    )

    validatorSchema => array
    (
      [id]          => sfValidatorDoctrineChoice,
      [name]        => sfValidatorString,
      [price]       => sfValidatorNumber,
      [newPhotos]   => array(
        [0]           => array(
          [id]          => sfValidatorDoctrineChoice,
          [filename]    => sfValidatorFile,
          [caption]     => sfValidatorString,
        ),
        [1]           => array(
          [id]          => sfValidatorDoctrineChoice,
          [filename]    => sfValidatorFile,
          [caption]     => sfValidatorString,
        ),
      ),
    )

>**TIP**
>Al igual que `widgetSchema` y `validatorSchema` son realmente objetos que se
>comportan como arrays, los arrays anteriores definidos mediante las claves
>`newPhotos`, `0` y `1` son objetos `sfWidgetSchema` y `sfValidatorSchema`.

Como era de esperar, los campos básicos (`id`, `name` y `price`) se representan
en el primer nivel de cada array. En los formularios que no embeben otros
formularios, tanto `widgetSchema` como `validatorSchema` solamente tienen un
nivel, que representa los campos básicos del formulario. Los widgets y validadores
de cualquier formulario embebido se representan como subarrays de `widgetSchema`
y `validatorSchema`. A continuación se explica el método que se encarga de este
proceso.

### Entendiendo el método ~`sfForm::embedForm()`~

Como un formulario está compuesto por un array de widgets y otro de validadores,
embeber un formulario en otro consiste fundamentalmente en añadir los arrays de
widgets y validadores de un formulario dentro de los arrays de widgets y
validadores del formulario principal. Este proceso lo realiza completamente el
método `sfForm::embedForm()`. El resultado siempre es la creación de unos arrays
`widgetSchema` y `validatorSchema` multidimensionales, tal y como se mostró
anteriormente.

A continuación se explica la configuración de `ProductPhotoCollectionForm`, que
asocia objetos `ProductPhotoForm` individuales consigo mismo. Este formulario
intermedio actúa como un *formulario contenedor* y facilita la organización de
todos los formularios. En primer lugar, veamos el siguiente código extraído de
`ProductPhotoCollectionForm::configure()`:

    [php]
    $form = new ProductPhotoForm($productPhoto);
    $this->embedForm($i, $form);

El propio formulario `ProductPhotoCollectionForm` comienza como un nuevo objeto
de tipo `sfForm`. Por tanto, sus arrays `widgetSchema` y `validatorSchema` están
vacíos.

    [php]
    widgetSchema    => array()
    validatorSchema => array()

Por su parte, cada formulario `ProductPhotoForm` ya contiene tres campos
(`id`, `filename` y `caption`) y sus correspondientes tres elementos en los
arrays `widgetSchema` y `validatorSchema`.

    [php]
    widgetSchema    => array
    (
      [id]            => sfWidgetFormInputHidden,
      [filename]      => sfWidgetFormInputFile,
      [caption]       => sfWidgetFormInputText,
    )

    validatorSchema => array
    (
      [id]            => sfValidatorDoctrineChoice,
      [filename]      => sfValidatorFile,
      [caption]       => sfValidatorString,
    )

El método ~`sfForm::embedForm()`~ simplemente añade los arrays `widgetSchema`
y `validatorSchema` de cada `ProductPhotoForm` dentro de los arrays `widgetSchema`
y `validatorSchema` del objeto `ProductPhotoCollectionForm` vacío.

Al finalizar, los arrays `widgetSchema` y `validatorSchema` del formulario
contenedor (`ProductPhotoCollectionForm`) son arrays multidimensionales que
contienen los widgets y validadores de los dos formularios `ProductPhotoForm`.

    [php]
    widgetSchema    => array
    (
      [0]             => array
      (
        [id]            => sfWidgetFormInputHidden,
        [filename]      => sfWidgetFormInputFile,
        [caption]       => sfWidgetFormInputText,
      ),
      [1]             => array
      (
        [id]            => sfWidgetFormInputHidden,
        [filename]      => sfWidgetFormInputFile,
        [caption]       => sfWidgetFormInputText,
      ),
    )

    validatorSchema => array
    (
      [0]             => array
      (
        [id]            => sfValidatorDoctrineChoice,
        [filename]      => sfValidatorFile,
        [caption]       => sfValidatorString,
      ),
      [1]             => array
      (
        [id]            => sfValidatorDoctrineChoice,
        [filename]      => sfValidatorFile,
        [caption]       => sfValidatorString,
      ),
    )

En el último paso de este proceso, el formulario contenedor resultante
(`ProductPhotoCollectionForm`) se embebe directamente en `ProductForm`. Esto
se realiza dentro del método `ProductForm::configure()`, que aprovecha todo el
trabajo realizado dentro de `ProductPhotoCollectionForm`:

    [php]
    $form = new ProductPhotoCollectionForm(null, array(
      'product' => $this->getObject(),
      'size'    => 2,
    ));

    $this->embedForm('newPhotos', $form);

El código anterior produce la estructura definitiva de los arrays `widgetSchema`
y `validatorSchema` que se mostró anteriormente. En realidad, el método `embedForm()`
es similar a combinar manualmente los arrays `widgetSchema` y `validatorSchema`:

    [php]
    $this->widgetSchema['newPhotos'] = $form->getWidgetSchema();
    $this->validatorSchema['newPhotos'] = $form->getValidatorSchema();

Mostrando los formularios embebidos
-----------------------------------

La plantilla `_form.php` actual del módulo `product` contiene el siguiente
código:

    [php]
    // apps/frontend/module/product/templates/_form.php
    <!-- ... -->

    <tbody>
      <?php echo $form ?>
    </tbody>

    <!-- ... -->

La instrucción `<?php echo $form ?>` es la forma más sencilla de mostrar un
formulario, incluso para los formularios más complejos. Aunque se trata de algo
muy útil cuando se prototipa una aplicación, se debe sustituir por tu propio
código si quieres modificar la forma en la que se muestra el formulario. Borra
esa línea de código porque se va a reemplazar en esta misma sección.

Lo más importante que hay que saber al mostrar formularios embebidos en la
vista es la organización multidimensional del array `widgetSchema` que se
explicó en la sección anterior. En este ejemplo se empieza mostrando los campos
básicos `name` y `price` del formulario `ProductForm`:

    [php]
    // apps/frontend/module/product/templates/_form.php
    <?php echo $form['name']->renderRow() ?>

    <?php echo $form['price']->renderRow() ?>

    <?php echo $form->renderHiddenFields() ?>

Como su propio nombre indica, `renderHiddenFields()` incluye todos los campos
ocultos del formulario.

>**NOTE**
>El código de las acciones se ha omitido a propósito porque no requiere de
>ninguna atención especial. Puedes echar un vistazo al código del archivo
>`apps/frontend/modules/product/actions/actions.class.php`. Su aspecto es el de
>cualquier CRUD normal y se puede generar automáticamente mediante la tarea
>`doctrine:generate-module`.

Como se acaba de explicar, la clase `sfForm` incluye los arrays `widgetSchema`
y `validatorSchema` que definen nuestros campos. Además, la clase `sfForm`
implementa la interfaz nativa de PHP 5 `ArrayAccess`, lo que significa que se
puede acceder directamente a los campos de un formulario utilizando la sintaxis
de los arrays mostrada anteriormente.

Para mostrar los campos en la vista, se pueden acceder directamente invocando
el método `renderRow()`. ¿Qué tipo de objeto es `$form['name']`? Aunque puede
que pienses que la respuesta es el widget `sfWidgetFormInputText` del campo
`name`, la respuesta correcta es ligeramente diferente.

### Mostrando cada campo de formulario con ~`sfFormField`~

La clase `sfForm` genera automáticamente un tercer array llamado `sfFormFieldSchema`
utilizando los arrays `widgetSchema` y `validatorSchema` definidos en cada clase
de formulario. Este array contiene un objeto especial para cada campo que actúa
como una clase de tipo *helper* encargada de mostrar cada campo. El objeto, de
tipo ~`sfFormField`~, es una combinación del widget y el validador de cada campo
y se crea de forma automática.

    [php]
    <?php echo $form['name']->renderRow() ?>

En el código anterior, `$form['name']` es un objeto de tipo `sfFormField`, que
incluye el método `renderRow()` junto con muchas otras funciones útiles para
mostrar los campos.

### Métodos de sfFormField

Cada objeto `sfFormField` se puede emplear para mostrar fácilmente cada aspecto
del campo al que representa (por ejemplo el propio campo, su título, los
mensajes de error, etc.) A continuación se muestran algunos de los métodos más
útiles de `sfFormField`. El resto de métodos los puedes consultar en la
[API de Symfony 1.3](http://www.symfony-project.org/api/1_3/sfFormField).

 * `sfFormField->render()`: muestra el campo del formulario (es decir, la etiqueta
   `<input>`, `<select>`, etc.) con su valor correcto de acuerdo al objeto del
   widget del campo.

 * `sfFormField->renderError()`: muestra cualquier error de validación del campo
   utilizando el objeto del validador del campo.

 * `sfFormField->renderRow()`: *todo en uno* que muestra el título, el campo de
   formulario, los errores y los mensajes de ayuda dentro de un contenedor de
   código XHTML.

>**NOTE**
>En realidad, cada función de visualización de la clase `sfFormField` también
>utiliza información de la propiedad `widgetSchema` del formulario (el objeto
>`sfWidgetFormSchema` que incluye todos los widgets del formulario). Esta clase
>ayuda en la generación de los atributos `name` e `id` de cada campo, genera el
>título de cada campo y define el código XHTML utilizado por `renderRow()`.

Otro aspecto importante es que el array `formFieldSchema` siempre es idéntico
a la estructura de los arrays `widgetSchema` y `validatorSchema` del formulario.
El array `formFieldSchema` por ejemplo del formulario `ProductForm` completo
tendría la siguiente estructura, que es imprescindible para mostrar cada campo
del formulario:

    [php]
    formFieldSchema    => array
    (
      [id]          => sfFormField
      [name]        => sfFormField,
      [price]       => sfFormField,
      [newPhotos]   => array(
        [0]           => array(
          [id]          => sfFormField,
          [filename]    => sfFormField,
          [caption]     => sfFormField,
        ),
        [1]           => array(
          [id]          => sfFormField,
          [filename]    => sfFormField,
          [caption]     => sfFormField,
        ),
      ),
    )

### Mostrando el nuevo ProductForm

Utilizando el array superior como una especie de *mapa*, se pueden mostrar
fácilmente los campos del formulario `ProductPhotoForm` embebido localizando
y mostrando los objetos `sfFormField` adecuados:

    [php]
    // apps/frontend/module/product/templates/_form.php
    <?php foreach ($form['newPhotos'] as $photo): ?>
      <?php echo $photo['caption']->renderRow() ?>
      <?php echo $photo['filename']->renderRow() ?>
    <?php endforeach; ?>

El bloque de código anterior se ejecuta dos veces, una para el array de campos
de formulario `0` y otra para el array de campos de formulario `1`. Como se
observa en el diagrama anterior, los objetos asociados con cada array son
objetos de tipo `sfFormField`, que se pueden mostrar como cualquier otro campo.

Guardando formularios de objetos
--------------------------------

Normalmente los formularios están relacionados directamente con una o más tablas
de la base de datos y modifican la información en función de los datos enviados
en el formulario. Symfony genera automáticamente un objeto de formulario por
cada modelo del esquema de datos, que hereda de `sfFormDoctrine` o de `sfFormPropel`
dependiendo del ORM utilizado. Las clases de formulario son similares entre si
y permiten guardar fácilmente en la base de datos lo valores enviados por el usuario.

>**NOTE**
>~`sfFormObject`~ es una nueva clase añadida en Symfony 1.3 para gestionar todas
>las tareas comunes de `sfFormDoctrine` y `sfFormPropel`. Cada clase hereda de
>`sfFormObject`, que ahora se encarga de parte del proceso de guardado del
>formulario que se acaba de describir.

### El proceso de guardado de los formularios

En el ejemplo anterior, Symfony guarda tanto la información de `Product` como la
de los nuevos objetos `ProductPhoto` sin ningún esfuerzo por parte del programador.
El método que hace posible esta magia, ~`sfFormObject::save()`~, ejecuta
internamente una serie de métodos. Entender este proceso es vital para poder
modificarlo en escenarios más avanzados.

El proceso de guardado de un formulario consiste en una serie de métodos
ejecutados internamente tras invocar el método ~`sfFormObject::save()`~. La mayor
parte del trabajo se ejecuta en el método ~`sfFormObject::updateObject()`~, que
se invoca recursivamente para todos los formularios embebidos.

![Proceso de guardado del formulario](http://www.symfony-project.org/images/more-with-symfony/advanced_forms_06.png "Detalle del proceso de guardado de un formulario")

>**NOTE**
>La mayor parte del proceso de guardado tiene lugar en el método
>~`sfFormObject::doSave()`~, que se invoca desde `sfFormObject::save()` y se
>ejecuta dentro de una transacción de bases de datos. Si quieres modificar el
>propio proceso de guardado, `sfFormObject::doSave()` es normalmente el mejor
>sitio para hacerlo.

Ignorando los formularios embebidos
-----------------------------------

La implementación actual de `ProductForm` sufre una carencia importante. Como
los campos `filename` y `caption` son obligatorios en `ProductPhotoForm`, la
validación del formulario principal siempre falla a menos que el usuario suba
dos nuevas fotos. En otras palabras, el usuario no puede modificar solamente
el precio de `Product` sin tener que subir dos nuevas fotos del producto.

![Error de validación de las fotos en el formulario del producto](http://www.symfony-project.org/images/more-with-symfony/advanced_forms_04.png "Error de validación de las fotos en el formulario del producto")

Por este motivo se van a redefinir los requisitos de la aplicación para que si
el usuario deja vacíos todos los campos de tipo `ProductPhotoForm`, el formulario
principal los ignore. No obstante, si al menos un campo de este tipo tiene
información (sea el `caption` o el `filename`), el formulario realiza la
validación tradicional y se guarda normalmente. Para conseguirlo, se va a
utilizar una técnica avanzada que hace uso de un post-validador propio.

El primer paso consiste en modificar el formulario `ProductPhotoForm` para hacer
que los campos `caption` y `filename` sean opcionales:

    [php]
    // lib/form/doctrine/ProductPhotoForm.class.php
    public function configure()
    {
      $this->setValidator('filename', new sfValidatorFile(array(
        'mime_types' => 'web_images',
        'path' => sfConfig::get('sf_upload_dir').'/products',
        'required' => false,
      )));

      $this->validatorSchema['caption']->setOption('required', false);
    }

El código anterior establece la opción `required` a `false` al redefinir el
validador por defecto del campo `filename`. Además, también se ha establecido
de forma explícita la opción `required` del campo `caption` a `false`.

Seguidamente se añade el post-validador en el formulario `ProductPhotoCollectionForm`:

    [php]
    // lib/form/doctrine/ProductPhotoCollectionForm.class.php
    public function configure()
    {
      // ...

      $this->mergePostValidator(new ProductPhotoValidatorSchema());
    }

Un post-validador es un tipo especial de validador que tiene acceso a todos los
valores enviados, a diferencia de un validador que sólo pude acceder a un único
campo del formulario. Uno de los post-validadores más utilizados es
`sfValidatorSchemaCompare` que comprueba por ejemplo que el valor de un campo
sea inferior al del otro campo.

### Creando un validador propio

Afortunadamente es muy sencillo crear un validador propio. Crea un nuevo archivo
llamado `ProductPhotoValidatorSchema.class.php` y guárdalo en el directorio
`lib/validator/` (debes crear este directorio a mano):

    [php]
    // lib/validator/ProductPhotoValidatorSchema.class.php
    class ProductPhotoValidatorSchema extends sfValidatorSchema
    {
      protected function configure($options = array(), $messages = array())
      {
        $this->addMessage('caption', 'The caption is required.');
        $this->addMessage('filename', 'The filename is required.');
      }

      protected function doClean($values)
      {
        $errorSchema = new sfValidatorErrorSchema($this);

        foreach($values as $key => $value)
        {
          $errorSchemaLocal = new sfValidatorErrorSchema($this);

          // se ha rellenado el campo filename pero no el campo caption
          if ($value['filename'] && !$value['caption'])
          {
            $errorSchemaLocal->addError(new sfValidatorError($this, 'required'), 'caption');
          }

          // se ha rellenado el campo caption pero no el campo filename
          if ($value['caption'] && !$value['filename'])
          {
            $errorSchemaLocal->addError(new sfValidatorError($this, 'required'), 'filename');
          }

          // no se ha rellenado ni caption ni filename, se eliminan los valores vacíos
          if (!$value['filename'] && !$value['caption'])
          {
            unset($values[$key]);
          }

          // algun error para este formulario embebido
          if (count($errorSchemaLocal))
          {
            $errorSchema->addError($errorSchemaLocal, (string) $key);
          }
        }

        // lanza un error para el formulario principal
        if (count($errorSchema))
        {
          throw new sfValidatorErrorSchema($this, $errorSchema);
        }

        return $values;
      }
    }

>**TIP**
>Todos los validadores heredan de `sfValidatorBase` y solamente requieren el
>método `doClean()`. También se puede emplear el método `configure()` para
>añadir opciones o mensajes al validador. En este caso, se han añadido dos
>mensajes al validador. Igualmente, se pueden añadir opciones adicionales
>mediante el método `addOption()`.

El método `doClean()` se encarga de *limpiar* y validar los datos enviados. La
propia lógica del validador es muy simple:

 * Si la foto enviada solamente tiene un nombre de archivo o un título, se lanza
   un error (`sfValidatorErrorSchema`) con el mensaje apropiado

 * Si la foto enviada no tiene ni nombre de archivo ni título, se eliminan los
   dos valores para evitar guardar una foto vacía

 * Si no se producen errores de validación, el método devuelve un array con
   los datos *limpios*

>**TIP**
>Como en este caso el validador propio se va a utilizar como post-validador,
>su método `doClean()` espera un array con los valores enviados por el usuario
>y devuelve un array con los valores *limpios*. Los validadores propios para
>campos individuales también se pueden crear igual de fácil. En este caso, el
>método `doClean()` solamente espera un valor (el valor enviado por el usuario)
>y devuelve un único valor.

El último paso consiste en redefinir el método `saveEmbeddedForms()` de `ProductForm`
para eliminar los formularios de fotos vacíos de forma que no se guarde una
foto vacía en la base de datos (ya que se lanzaría una excepción porque la
columna `caption` es obligatoria):

    [php]
    public function saveEmbeddedForms($con = null, $forms = null)
    {
      if (null === $forms)
      {
        $photos = $this->getValue('newPhotos');
        $forms = $this->embeddedForms;
        foreach ($this->embeddedForms['newPhotos'] as $name => $form)
        {
          if (!isset($photos[$name]))
          {
            unset($forms['newPhotos'][$name]);
          }
        }
      }

      return parent::saveEmbeddedForms($con, $forms);
    }

Embebiendo fácilmente formularios relacionados con Doctrine
-----------------------------------------------------------

Otra de las novedades de Symfony 1.3 es la función ~`sfFormDoctrine::embedRelation()`~,
que permite al programador embeber automáticamente una relación uno-a-muchos en
un formulario. Imagina que además de permitir al usuario subir dos nuevas fotos,
se le permite modificar cualquier objeto `ProductPhoto` relacionado con este
producto.

El siguiente código utiliza el método `embedRelation()` para añadir un objeto
`ProductPhotoForm` adicional por cada objeto `ProductPhoto` existente:

    [php]
    // lib/form/doctrine/ProductForm.class.php
    public function configure()
    {
      // ...

      $this->embedRelation('Photos');
    }

Internamente el método ~`sfFormDoctrine::embedRelation()`~ hace casi lo mismo
que añadimos anteriormente para embeber los dos nuevos objetos `ProductPhotoForm`.
Si ya existen dos `ProductPhoto` relacionadas, los arrays `widgetSchema` y
`validatorSchema` resultantes del formulario tendrían el siguiente aspecto:

    [php]
    widgetSchema    => array
    (
      [id]          => sfWidgetFormInputHidden,
      [name]        => sfWidgetFormInputText,
      [price]       => sfWidgetFormInputText,
      [newPhotos]   => array(...)
      [Photos]      => array(
        [0]           => array(
          [id]          => sfWidgetFormInputHidden,
          [caption]     => sfWidgetFormInputText,
        ),
        [1]           => array(
          [id]          => sfWidgetFormInputHidden,
          [caption]     => sfWidgetFormInputText,
        ),
      ),
    )

    validatorSchema => array
    (
      [id]          => sfValidatorDoctrineChoice,
      [name]        => sfValidatorString,
      [price]       => sfValidatorNumber,
      [newPhotos]   => array(...)
      [Photos]      => array(
        [0]           => array(
          [id]          => sfValidatorDoctrineChoice,
          [caption]     => sfValidatorString,
        ),
        [1]           => array(
          [id]          => sfValidatorDoctrineChoice,
          [caption]     => sfValidatorString,
        ),
      ),
    )

![Formulario de producto con dos fotos existentes](http://www.symfony-project.org/images/more-with-symfony/advanced_forms_03.png "Formulario de producto con dos fotos existentes")

El siguiente paso consiste en añadir en la vista el código necesario para mostrar
los nuevos formularios de tipo *Photo* embebidos:

    [php]
    // apps/frontend/module/product/templates/_form.php
    <?php foreach ($form['Photos'] as $photo): ?>
      <?php echo $photo['caption']->renderRow() ?>
      <?php echo $photo['filename']->renderRow(array('width' => 100)) ?>
    <?php endforeach; ?>

El código anterior es exactamente el mismo que se utilizó anteriormente para
embeber los nuevos formularios de fotos.

El último paso consiste en convertir el campo para subir archivos en un campo
que permita al usuario ver la foto actual y modificarla por una nueva
(`sfWidgetFormInputFileEditable`):

    [php]
    public function configure()
    {
      $this->useFields(array('filename', 'caption'));

      $this->setValidator('filename', new sfValidatorFile(array(
        'mime_types' => 'web_images',
        'path' => sfConfig::get('sf_upload_dir').'/products',
        'required' => false,
      )));

      $this->setWidget('filename', new sfWidgetFormInputFileEditable(array(
        'file_src'    => '/uploads/products/'.$this->getObject()->filename,
        'edit_mode'   => !$this->isNew(),
        'is_image'    => true,
        'with_delete' => false,
      )));

      $this->validatorSchema['caption']->setOption('required', false);
    }

Eventos de formulario
---------------------

Los eventos de formulario son una novedad de Symfony 1.3 que permiten extender
cualquier objeto de formulario desde cualquier punto del proyecto. Symfony
incluye los siguientes cuatro eventos de formulario:

 * `form.post_configure`: este evento se notifica después de configurar cada formulario
 * `form.filter_values`: este evento se notifica cuando se combinan los valores y los
    arrays de archivos enviados por los usuarios justo antes de asociar los datos con el formulario
 * `form.validation_error`: este evento se notifica siempre que falla la validación del formulario
 * `form.method_not_found`: este evento se notifica siempre que se invoca un método desconocido

### Mensajes de log propios mediante `form.validation_error`

Haciendo uso de los eventos de los formularios es posible añadir mensajes de log
propios para los errores de validación de cualquier formulario del proyecto. Esto
puede ser útil si quieres controlar los formularios y/o campos de formulario
que están creando confusión entre los usuarios.

En primer lugar se registra un *listener* para el evento `form.validation_error`.
Añade el siguiente método `setup()` en la clase `ProjectConfiguration` que se
encuentra en el directorio `config`:

    [php]
    public function setup()
    {
      // ...

      $this->getEventDispatcher()->connect(
        'form.validation_error',
        array('BaseForm', 'listenToValidationError')
      );
    }

`BaseForm`, que se encuentra en `lib/form`, es una clase de formulario especial
de la que heredan todas las clases de formulario. En esencia, `BaseForm` es una
clase en la que se puede incluir código accesible por todos los formularios del
proyecto. Para generar los mensajes de log de los errores de validación, simplemente
añade el siguiente código en la clase `BaseForm`:

    [php]
    public static function listenToValidationError($event)
    {
      foreach ($event['error'] as $key => $error)
      {
        self::getEventDispatcher()->notify(new sfEvent(
          $event->getSubject(),
          'application.log',
          array (
            'priority' => sfLogger::NOTICE,
            sprintf('Validation Error: %s: %s', $key, (string) $error)
          )
        ));
      }
    }

![Mensajes de log de los errores de validación](http://www.symfony-project.org/images/more-with-symfony/advanced_forms_05.png "Barra de depuración web con errores de validación")

Aplicando estilos diferentes para los elementos con errores
-----------------------------------------------------------

Como práctica final, se muestra una utilidad muy sencilla relacionada con el
estilo de los elementos de formulario. Imagina que el diseño de la página `Product`
incluye estilos diferentes para los campos que tienen algún error de validación.

![Formulario de producto con errores](http://www.symfony-project.org/images/more-with-symfony/advanced_forms_02.png "Formulario de producto con estilos en los campos con errores")

Se supone que el diseñador ya ha creado la hoja de estilos que aplica estilos
diferentes a los campos de tipo `<input>` que se encuentren dentro de cualquier
elemento `<div>` con la clase `form_error_row`. ¿Cómo se pueden añadir fácilmente
clases `form_row_error` en los campos que tengan errores?

Para conseguirlo hay que hacer uso de un objeto especial llamado *formateador
del esquema de formulario* (*"form schema formatter"*). Todos los formularios
de Symfony utilizan este formateador para determinar el código HTML que se debe
utilizar cuando se muestran los elementos de formulario. Por defecto Symfony
emplea un formateador que hace uso de tablas HTML.

En primer lugar se crea una nueva clase de tipo formateador de formulario que
emplea una código HTML más simple al mostrar el formulario. Crea un nuevo
archivo llamado `sfWidgetFormSchemaFormatterAc2009.class.php` y guárdalo en el
directorio `lib/widget/` (debes crear este directorio a mano):

    [php]
    class sfWidgetFormSchemaFormatterAc2009 extends sfWidgetFormSchemaFormatter
    {
      protected
        $rowFormat       = "<div class=\"form_row\">
                            %label% \n %error% <br/> %field%
                            %help% %hidden_fields%\n</div>\n",
        $errorRowFormat  = "<div>%errors%</div>",
        $helpFormat      = '<div class="form_help">%help%</div>',
        $decoratorFormat = "<div>\n  %content%</div>";
    }

Aunque el formato de esta clase es un poco raro, la idea básica es que el método
`renderRow()` utiliza el código de `$rowFormat` para mostrar su información.
Las clases de tipo formateador tienen muchas otras opciones que se pueden
consultar en la 
[API de symfony 1.3](http://www.symfony-project.org/api/1_3/sfWidgetFormSchemaFormatter).

Para hacer uso del nuevo formateador en todos los formularios del proyecto, se
puede añadir lo siguiente en la clase `ProjectConfiguration`:

    [php]
    class ProjectConfiguration extends sfProjectConfiguration
    {
      public function setup()
      {
        // ...

        sfWidgetFormSchema::setDefaultFormFormatterName('ac2009');
      }
    }

El objetivo consiste en añadir una clase `form_row_error` dentro del elemento
`form_row` solamente si ese campo tiene algún error de validación. Añade una
variable `%row_class%` en la propiedad `$rowFormat` y redefine el método
~`sfWidgetFormSchemaFormatter::formatRow()`~ de la siguiente manera:

    [php]
    class sfWidgetFormSchemaFormatterAc2009 extends sfWidgetFormSchemaFormatter
    {
      protected
        $rowFormat       = "<div class=\"form_row%row_class%\">
                            %label% \n %error% <br/> %field%
                            %help% %hidden_fields%\n</div>\n",
        // ...

      public function formatRow($label, $field, $errors = array(), $help = '', $hiddenFields = null)
      {
        $row = parent::formatRow(
          $label,
          $field,
          $errors,
          $help,
          $hiddenFields
        );

        return strtr($row, array(
          '%row_class%' => (count($errors) > 0) ? ' form_row_error' : '',
        ));
      }
    }

Con este último cambio, cada elemento que se muestre con el método `renderRow()`
será encerrado por un elemento `<div>` con la clase `form_row_error` si ese
elemento tiene algún error de validación.

Conclusión
----------

El framework de formularios de Symfony es uno de sus componentes más poderosos
y a la vez más complejos. Disfrutar de una validación muy estricta, protección
CSRF y objetos de formulario tiene el inconveniente de que extender el
framework de formularios puede convertirse rápidamente en una tarea muy compleja.
No obstante, conocer los detalles del funcionamiento de los formularios es la
clave para aprovechar todo su potencial. Confiamos en que este capítulo te haya
ayudado en esa tarea.

El futuro del framework de formularios se centrará en mantener todo su poder
mientras se reduce su complejidad y se ofrece más flexibilidad al programador.
El framework de formularios no ha hecho más que dar sus primeros pasos.
