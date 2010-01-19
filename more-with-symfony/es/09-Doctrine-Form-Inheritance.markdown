Utilizando la herencia de tablas de Doctrine
============================================

*por Hugo Hamon*

~Doctrine~ se ha convertido oficialmente en el ORM por defecto de Symfony 1.3,
mientras que el desarrollo de Propel ha decaído en los últimos meses. Aún así,
el proyecto ~Propel~ todavía está activo y sigue mejorando gracias al esfuerzo
de varios miembros de la comunidad Symfony.

La versión 1.2 de Doctrine se ha convertido en el ORM por defecto de Symfony
porque es más fácil de utilizar que Propel y porque incluye un montón de
utilidades como comportamientos, consultas DQL sencillas, migraciones y
herencia de tablas.

Este capítulo describe lo que es la ~herencia de tablas~ y cómo se integra con
Symfony 1.3. Haciendo uso de un proyecto real, este capítulo muestra cómo
aprovechar la herencia de tablas de Doctrine para hacer que el código sea más
flexible y esté mejor organizado.

Herencia de tablas de Doctrine
------------------------------

Aunque la herencia de tablas no es muy conocida ni utilizada por la mayoría de
programadores, se trata de una de las características más interesantes de
Doctrine. La herencia de tablas permite al programador crear tablas de base de
datos que heredan de otras tablas de la misma forma que las clases pueden
heredar de otras clases en los lenguajes orientados a objetos. La herencia de
tablas es una forma sencilla de que dos o más tablas compartan información en
una única tabla padre. Observa el siguiente diagrama para comprender mejor el
funcionamiento de la herencia de tablas.

![Esquema de la herencia de tablas de Doctrine](http://www.symfony-project.org/images/more-with-symfony/01_table_inheritance.png "Esquema de la herencia de tablas de Doctrine")

Doctrine incluye tres estrategias diferentes para gestionar la herencia de tablas
en función de las necesidades de la aplicación (rendimiento, atomicidad,
simplicidad...): `__simple__`, `__column aggregation__` y `__concrete__`. Aunque
todas estas estrategias se describen en el
[libro de Doctrine](http://www.doctrine-project.org/documentation/1_2/en), las
siguientes secciones explican cada una de ellas y las circunstancias en las que
son útiles.

### La estrategia simple de herencia de tablas

La ~estrategia simple de herencia de tablas~ es la más básica ya que guarda todas
las columnas, incluso las de las tablas hijo, dentro de la tabla padre. Si el
esquema del modelo es como el siguiente código YAML, Doctrine genera una tabla
llamada `Person` que incluye tanto las columnas de la tabla `Professor` como
las de la tabla `Student`.

    [yml]
    Person:
      columns:
        first_name:
          type:           string(50)
          notnull:        true
        last_name:
          type:           string(50)
          notnull:        true

    Professor:
      inheritance:
        type:             simple
        extends:          Person
      columns:
        specialty:
          type:           string(50)
          notnull:        true

    Student:
      inheritance:
        type:             simple
        extends:          Person
      columns:
        graduation:
          type:           string(20)
          notnull:        true
        promotion:
          type:           integer(4)
          notnull:        true

En la estrategia simple de herencia de tablas, las columnas `specialty`, `graduation`
y `promotion` se añaden automáticamente en el modelo `Person` aunque Doctrine
genera una clase de modelo tanto para `Student` como para `Professor`.

![Esquema de la herencia simple de tablas](http://www.symfony-project.org/images/more-with-symfony/02_simple_tables_inheritance.png "Esquema de la herencia simple de Doctrine")

El inconveniente de esta estrategia es que la tabla padre `Person` no incluye
ninguna columna que identifique el tipo de cada registro. En otras palabras,
no es posible obtener solamente los objetos de tipo `Professor` o `Student`.
La siguiente instrucción de Doctrine devuelve un objeto `Doctrine_Collection`
con todos los registros de la tabla (registros `Student` y `Professor`).

    [php]
    $professors = Doctrine_Core::getTable('Professor')->findAll();

La estrategia estrategia simple de herencia de tablas no suele ser muy útil en
los ejemplos del mundo real, ya que normalmente es necesario seleccionar objetos
de un determinado tipo. Así que no se usará más esta estrategia en este capítulo.

### La estrategia de agregación de columnas en la herencia de tablas

La ~estrategia de agregación de columnas~ en la herencia de tablas es similar a
la estrategia simple excepto por el hecho de que añade una columna llamada `type`
que identifica los diferentes tipos de registros. De esta forma, cuando se
guarda un objeto en la base de datos, se añade automáticamente un valor a la
columna `type` que indica el tipo de clase del objeto.

    [yml]
    Person:
      columns:
        first_name:
          type:           string(50)
          notnull:        true
        last_name:
          type:           string(50)
          notnull:        true

    Professor:
      inheritance:
        type:             column_aggregation
        extends:          Person
        keyField:         type
        keyValue:         1
      columns:
        specialty:
          type:           string(50)
          notnull:        true

    Student:
      inheritance:
        type:             column_aggregation
        extends:          Person
        keyField:         type
        keyValue:         2
      columns:
        graduation:
          type:           string(20)
          notnull:        true
        promotion:
          type:           integer(4)
          notnull:        true

En el esquema YAML anterior se ha modificado el tipo de herencia al valor
~`column_aggregation`~ y se han añadido dos nuevos atributos. El primer
atributo se denomina `keyField` y especifica la columna que se crea para guardar
el tipo de información del registro. El atributo `keyField` es una columna de
de texto llamada `type`, que es el nombre por defecto cuando no se
especifica el atributo `keyField`. El segundo atributo (`keyValue`) define el
valor del tipo de cada registro que pertenece a la clase `Professor` o `Student`.

![Esquema de la herencia de tablas basada en la agregación de columnas](http://www.symfony-project.org/images/more-with-symfony/03_columns_aggregation_tables_inheritance.png "Esquema de la herencia de tablas basada en la agregación de columnas")

La estrategia de agregación de columnas es una forma de herencia de tablas muy
interesante porque crea una única tabla (`Person`) que contiene todos los campos
definidos además del campo `type` adicional. De esta forma, no es necesario crear
varias tablas para unirlas después mediante una consulta SQL. A continuación
se muestran algunos ejemplos de cómo realizar consultas en las tablas y el tipo
de resultados devueltos:

    [php]
    // Devuelve un Doctrine_Collection de objetos Professor
    $professors = Doctrine_Core::getTable('Professor')->findAll();

    // Devuelve un Doctrine_Collection de objetos Student
    $students = Doctrine_Core::getTable('Student')->findAll();

    // Devuelve un objeto Professor
    $professor = Doctrine_Core::getTable('Professor')->findOneBySpeciality('physics');

    // Devuelve un objeto Student
    $student = Doctrine_Core::getTable('Student')->find(42);

    // Devuelve un onjeto Student
    $student = Doctrine_Core::getTable('Person')->findOneByIdAndType(array(42, 2));

Cuando se obtienen datos de una subclase (`Professor`, `Student`), Doctrine
añade automáticamente la cláusula `WHERE` necesaria de SQL para realizar la
consulta con el valor correspondiente de la columna `type`.

No obstante, en algunos casos la agregación de columnas presenta inconvenientes.
En primer lugar, la agregación de columnas impide que los campos de cada sub-tabla
puedan ser configurados como `required`. Dependiendo de cuantos campos haya, la
 tabla `Person` puede contener registros con varios campos vacíos.

El segundo inconveniente está relacionado con el número de sub-tablas y campos.
Si el esquema declara muchas sub-tablas y cada una declara a su vez muchos
campos, la tabla padre final contendrá un gran número de columnas. Por tanto,
esta tabla padre puede ser difícil de mantener.

### La estrategia concreta de herencia de tablas

La ~estrategia concreta~ de herencia de tablas es un compromiso entre las ventajas
de la agregación de columnas, el rendimiento de la aplicación y su facilidad de
mantenimiento. Efectivamente, esta estrategia crea una tabla independiente por
cada subclase conteniendo todas las columnas: tanto las columnas compartidas
como las columnas exclusivas de cada modelo.

    [yml]
    Person:
      columns:
        first_name:
          type:           string(50)
          notnull:        true
        last_name:
          type:           string(50)
          notnull:        true

    Professor:
      inheritance:
        type:             concrete
        extends:          Person
      columns:
        specialty:
          type:           string(50)
          notnull:        true

    Student:
      inheritance:
        type:             concrete
        extends:          Person
      columns:
        graduation:
          type:           string(20)
          notnull:        true
        promotion:
          type:           integer(4)
          notnull:        true

El esquema anterior genera una tabla `Professor` con los siguientes campos:
`id`, `first_name`, `last_name` y `specialty`.

![Esquema de la herencia concreta de tablas](http://www.symfony-project.org/images/more-with-symfony/04_concrete_tables_inheritance.png "Esquema de la herencia concreta de tablas")

Esta estrategia tiene varias ventajas respecto de las anteriores. La primera es
que cada tabla permanece aislada y es independiente de las otras tablas. Además,
ya no se guardan columnas vacías y tampoco se incluye la columna adicional `type`.
El resultado es que cada tabla es mucho más pequeña y está aislada del resto.

>**NOTE**
>El hecho de que las columnas comunes estén duplicadas en las sub-tablas es
>una mejora del rendimiento y de la escalabilidad, ya que Doctrine no tiene que
>hacer uniones SQL automáticas con la tabla padre para obtener los datos
>compartidos que pertenecen a un registro de una sub-tabla.

Las únicas dos desventajas de la herencia concreta de tabla son la duplicación
de las columnas compartidas (aunque la duplicación es buena para mejorar el
rendimiento) y el hecho de que la tabla padre generada siempre estará vacía.
Efectivamente, Doctrine genera una tabla `Person` aunque nunca guarde información
en ella ni la utilice en las consultas. Como toda la información se guarda en
las sub-tablas, esta tabla padre no se utilizará en ninguna consulta.

Hasta ahora sólo se han presentado las tres estrategias de herencias de tablas
de Doctrine, pero todavía no se han utilizado en ningún proyecto real desarrollado
con Symfony. La siguiente parte de este capítulo explica cómo aprovechar la
~herencia de tablas~ de Doctrine en Symfony 1.3, sobre todo en el modelo y en
el framework de formularios.

Integración de la herencia de tablas en Symfony
-----------------------------------------------

Antes de Symfony 1.3, la ~herencia de tablas~ de Doctrine no estaba integrada
en el framework, ya que los formularios y los filtros no heredaban correctamente
de la clase base. Por tanto, los programadores que querían utilizar la herencia
de tablas debían modificar los formularios y filtros, además de redefinir muchos
métodos.

Gracias al apoyo de la comunidad de usuarios, los desarrolladores de Symfony
han mejorado los formularios y los filtros para que Symfony 1.3 soporte la
herencia de tablas de Doctrine de forma sencilla pero completa.

El resto de este capítulo explica cómo usar la herencia de tablas de Doctrine y
cómo aprovecharla en varios ejemplos adoptándola en los modelos, formularios,
filtros y generadores de la parte de administración. Los casos de uso reales
que se presentan permiten entender mejor cómo funciona la herencia de tablas
en Symfony de forma que puedas hacer uso de ella en tus propias aplicaciones.

### Introduciendo casos de estudio reales

A lo largo de este capítulo se van a presentar varios casos de uso reales que
muestran las grandes ventajas de usar la herencia de tablas de Doctrine en
varios elementos: modelos, formularios, filtros y el generador de la parte de
administración.

El primer ejemplo fue utilizado en una aplicación desarrollada por Sensio para
una empresa francesa muy conocida. Este ejemplo muestra cómo la herencia de
tablas es una buena solución para manejar una docena de conjuntos de datos
idénticos de forma que se puedan reaprovechar métodos y propiedades para evitar
la duplicación de código.

El segundo ejemplo muestra cómo hacer uso de la ~herencia concreta de tablas~ 
en los formularios creando un modelo simple que gestione archivos numéricos.

Por último, el tercer ejemplo muestra cómo utilizar la herencia de tablas con
el generador de la parte de administración para hacerlo más flexible.

### Herencia de tablas en el modelo

Al igual que la programación orientada a objetos, la ~herencia de tablas~
fomenta que se comparta la información. Por tanto, es posible compartir métodos
y propiedades cuando se trabaja con las clases generadas por el modelo. La
herencia de tablas de Doctrine es una buena forma de compartir y redefinir las
acciones de los objetos heredados. A continuación se explica este uso con un
ejemplo real.

#### El problema ####

Muchas aplicaciones web requieren el uso de datos "~referenciales~" para funcionar.
Por referencial se entiende un conjunto normalmente pequeño de datos que se
representan mediante una tabla sencilla que contiene al menos dos campos (por
ejemplo `id` y `label`). No obstante, en algunos casos los datos referenciales
contienen información adicional como los campos `is_active` o `is_default`.
Este fue el caso al que se enfrentó recientemente la empresa Sensio al desarrollar
una aplicación para un cliente.

El cliente quería gestionar un gran conjunto de datos a través de los formularios
y plantillas de la aplicación. Todas las tablas referenciales presentaban la
misma estructura básica: `id`, `label`, `position` y `is_default`. El campo
`position` se emplea para ordenar los registros mediante una funcionalidad
tipo *drag & drop* construida con AJAX. El campo `is_default` indica si el
registro se debe mostrar o no seleccionado por defecto cuando se muestra en
una lista desplegable de HTML.

#### La solución ####

Gestionar dos o más tablas idénticas es uno de los problemas que más fácilmente
se resuelven con la ~herencia de tablas~. En este caso, se eligió la
~herencia concreta de tablas~ como mejor estrategia para que los métodos de
cada objeto se encontraran en una única clase. Veamos un esquema de datos
simplificado para ilustrar el problema.

    [yml]
    sfReferential:
      columns:
        id:
          type:        integer(2)
          notnull:     true
        label:
          type:        string(45)
          notnull:     true
        position:
          type:        integer(2)
          notnull:     true
        is_default:
          type:        boolean
          notnull:     true
          default:     false

    sfReferentialContractType:
      inheritance:
        type:          concrete
        extends:       sfReferential

    sfReferentialProductType:
      inheritance:
        type:          concrete
        extends:       sfReferential

La herencia concreta de tablas es la mejor en este caso porque crea tablas
aisladas e independientes y porque el campo `position` se debe gestionar para
los registros que sean del mismo tipo.

Construye el modelo y verás que Doctrine y Symfony generan tres tablas SQL y
seis clases del modelo en el directorio `lib/model/doctrine`:

  * `sfReferential`: gestiona los registros de tipo the `sf_referential`
  * `sfReferentialTable`: gestiona la tabla `sf_referential`
  * `sfReferentialContractType`: gestiona los registros de tipo `sf_referential_contract_type`
  * `sfReferentialContractTypeTable`: gestiona la tabla `sf_referential_contract_type`
  * `sfReferentialProductType`: gestiona los registros de tipo `sf_referential_product_type`
  * `sfReferentialProductTypeTable`: gestiona la tabla `sf_referential_product_type`

Si exploras las clases generadas, verás que las clases base de
`sfReferentialContractType` y `sfReferentialProductType` heredan de la clase
`sfReferential`. Por tanto, todos los métodos y propiedades de tipo `public` o
`protected` de la clase `sfReferential` se comparten entre las dos sub-clases
y pueden ser redefinidos fácilmente si es necesario. Esto es justamente lo que
necesitamos.

La clase `sfReferential` ahora puede contener métodos que gestionen cualquier
tipo de dato referencial, como por ejemplo:

    [php]
    // lib/model/doctrine/sfReferential.class.php
    class sfReferential extends BasesfReferential
    {
      public function promote()
      {
        // sube un elemento dentro de la lista
      }

      public function demote()
      {
        // baja un elemento dentro de la lista
      }

      public function moveToFirstPosition()
      {
        // posiciona el elemento como el primero de la lista
      }

      public function moveToLastPosition()
      {
        // posiciona el elemento como el último de la lista
      }

      public function moveToPosition($position)
      {
        // coloca el elemento en la posición indicada
      }

      public function makeDefault($forceSave = true, $conn = null)
      {
        $this->setIsDefault(true);

        if ($forceSave)
        {
          $this->save($conn);
        }
      }
    }

Gracias a la ~herencia concreta de tablas~ de Doctrine, todo el código se
encuentra centralizado en un único sitio. Por tanto, el código es más sencillo
de depurar, mantener, mejorar y probar con pruebas unitarias.

La anterior es la primera gran ventaja de trabajar con la herencia de tablas.
Además, gracias a esta estrategia, los objetos del modelo se pueden utilizar
para centralizar el código de las acciones, tal y como se muestra a continuación.
La clase `sfBaseReferentialActions` es un tipo especial de clase de acciones
que gestiona el modelo referencial y que heredan cada una de las clases de acciones.

    [php]
    // lib/actions/sfBaseReferentialActions.class.php
    class sfBaseReferentialActions extends sfActions
    {
      /**
       * Acción AJAX que guarda la nueva posición resultante después de que
       * el usuario reordene los elementos de la lista.
       *
       * Esta acción está relacionada gracias a una ruta ~sfDoctrineRoute~ que
       * facilita la búsqueda de los objetos referenciales.
       *
       * @param sfWebRequest $request
       */
      public function executeMoveToPosition(sfWebRequest $request)
      {
        $this->forward404Unless($request->isXmlHttpRequest());

        $referential = $this->getRoute()->getObject();

        $referential->moveToPosition($request->getParameter('position', 1));

        return sfView::NONE;
      }
    }

¿Qué hubiera sucedido si el esquema no utiliza herencia de tablas? El código
debería haberse duplicado en cada una de las sub-clases referenciales. Esta
estrategia no sería muy DRY (*Don't Repeat Yourself*) sobre todo en una aplicación
que dispone de una docena de tablas referenciales.

### Herencia de tablas en los formularios ###

Sigamos con el recorrido de todas las ventajas de la herencia de tablas de
Doctrine. En la sección anterior se ha mostrado lo útil que es la herencia para
compartir métodos y propiedades entre varios modelos. A continuación se muestra
su utilidad en los formularios generados automáticamente por Symfony.

#### El modelo de ejemplo ####

El siguiente esquema YAML describe un modelo para gestionar documentos numéricos.
El objetivo es guardar información genérica en la tabla `File` e información
específica en las sub-tablas `Video` y `PDF`.

    [yml]
    File:
      columns:
        filename:
          type:            string(50)
          notnull:         true
        mime_type:
          type:            string(50)
          notnull:         true
        description:
          type:            clob
          notnull:         true
        size:
          type:            integer(8)
          notnull:         true
          default:         0

    Video:
      inheritance:
        type:              concrete
        extends:           File
      columns:
        format:
          type:            string(30)
          notnull:         true
        duration:
          type:            integer(8)
          notnull:         true
          default:         0
        encoding:
          type:            string(50)

    PDF:
      tableName:           pdf
      inheritance:
        type:              concrete
        extends:           File
      columns:
        pages:
          type:            integer(8)
          notnull:         true
          default:         0
        paper_size:
          type:            string(30)
        orientation:
          type:            enum
          default:         portrait
          values:          [portrait, landscape]
        is_encrypted:
          type:            boolean
          default:         false
          notnull:         true

Las tablas `PDF` y `Video` comparten la misma tabla `File`, que contiene información
general sobre archivos numéricos. El modelo `Video` encapsula la información
relativa a los objetos de tipo vídeo como su formato (columna `format`) (4/3,
16/9, ...) o su duración (columna `duration`), mientras que el modelo `PDF`
contiene información como el número de páginas (columna `pages`) y la orientación
del documento (columna `orientation`). Ejecuta la siguiente tarea para generar
el modelo y sus correspondientes formularios.

    $ php symfony doctrine:build --all

La siguiente sección describe cómo aprovechar la herencia de tablas en las clases
de los formularios gracias al nuevo método ~`setupInheritance()`~.

#### Descubre el método ~setupInheritance()~ ###

Como era de esperar, Doctrine ha generado seis clases de formulario en los
directorios `lib/form/doctrine` y `lib/form/doctrine/base`:

  * `BaseFileForm`
  * `BaseVideoForm`
  * `BasePDFForm`

  * `FileForm`
  * `VideoForm`
  * `PDFForm`

Si abres las tres clases `Base` de los formularios, verás algo nuevo en el
método ~`setup()`~. Symfony 1.3 añade un nuevo método llamado ~`setupInheritance()`~.
Inicialmente este método está vacío.

Lo más importante es que la herencia de formularios se mantiene porque tanto
`BaseVideoForm` como `BasePDFForm` heredan de las clases `FileForm` y `BaseFileForm`.
Por tanto, cada una de ellas hereda de la clase `File` y pueden compartir sus
métodos.

El siguiente código redefine el método `setupInheritance()` y configura la clase
`FileForm` para que pueda ser utilizar en cualquier sub-formulario de forma
más efectiva.

    [php]
    // lib/form/doctrine/FileForm.class.php
    class FileForm extends BaseFileForm
    {
      protected function setupInheritance()
      {
        parent::setupInheritance();

        $this->useFields(array('filename', 'description'));

        $this->widgetSchema['filename']    = new sfWidgetFormInputFile();
        $this->validatorSchema['filename'] = new sfValidatorFile(array(
          'path' => sfConfig::get('sf_upload_dir')
        ));
      }
    }

El método `setupInheritance()`, invocado por las sub-clases `VideoForm` y `PDFForm`,
elimina todos los campos salvo `filename` y `description`. El widget del campo
`filename` se ha transformado en un widget de archivo y su validador asociado
se ha cambiado a ~`sfValidatorFile`~. De esta forma, el usuario podrá subir un
archivo y guardarlo en el servidor.

![Personalizando los formularios heredados mediante el método setupInheritance()](http://www.symfony-project.org/images/more-with-symfony/05_table_inheritance_forms.png "Personalizando los formularios heredados mediante el método setupInheritance()")

#### Estableciendo el tamaño y tipo MIME del archivo

Aunque los formularios ya están preparados, todavía falta configurar una cosa
más antes de poder utilizarlos. Como los campos `mime_type` y `size` se han
eliminado del objeto `FileForm`, es preciso añadirlos en la aplicación. El
mejor lugar para añadirlos es el método `generateFilenameFilename()` de la
clase `File`.

    [php]
    // lib/model/doctrine/File.class.php
    class File extends BaseFile
    {
      /**
       * Generates a filename for the current file object.
       *
       * @param sfValidatedFile $file
       * @return string
       */
      public function generateFilenameFilename(sfValidatedFile $file)
      {
        $this->setMimeType($file->getType());
        $this->setSize($file->getSize());

        return $file->generateFilename();
      }
    }

Este nuevo método se encarga de generar un nombre de archivo propio para guardar
el archivo en el sistema de archivos. Aunque el método `generateFilenameFilename()`
devuelve por defecto un nombre de archivo generado automáticamente, también
establece las propiedades `mime_type` y `size` gracias al objeto de tipo
~`sfValidatedFile`~ que se pasa como primer argumento.

Como Symfony 1.3 ya soporta la herencia de tablas de Doctrine, los formularios
ahora pueden guardar un objeto y todos sus valores heredados. Gracias al soporte
nativo de la herencia de tablas, es posible crear formularios muy potentes y
funcionales añadiendo una pequeña cantidad de código propio.

El código anterior puede mejorarse mucho de forma sencilla gracias a la herencia
de clases. Por ejemplo las clases `VideoForm` y `PDFForm` podrían redefinir el
validador de `filename` para que utilizara un validador propio más específico
como `sfValidatorVideo` o `sfValidatorPDF`.

### Herencia de tablas en los filtros ###

Como los filtros en realidad son formularios, también heredan los métodos y
propiedades de los filtros padre. De esta forma, los objetos `VideoFormFilter`
y `PDFFormFilter` heredan de la clase `FileFormFilter` y se pueden personalizar
utilizando el método ~`setupInheritance()`~.

De la misma forma, tanto `VideoFormFilter` como `PDFFormFilter` pueden compartir
los mismos métodos propios de la clase `FileFormFilter`.

### Herencia de tablas en el generador de la parte de administración ###

A continuación se muestra cómo aprovechar la herencia de tablas de Doctrine
junto con una de las nuevas características del generador de la parte de
administración: la definición de una __clase base de las acciones__. El generador
de la parte de administración es una de las características que más ha mejorado
Symfony desde su versión 1.0.

En noviembre de 2008 Symfony introdujo el nuevo generador de administración como
parte de Symfony 1.2. Esta herramienta incluye muchas funcionalidades listas
para usar, como las operaciones CRUD básicas, paginación y filtrado de listados,
borrado múltiple, etc. El generador de administraciones es una herramienta muy
poderosa que facilita y acelera el desarrollo y personalización del *backend*
de las aplicaciones.

#### Introducción al ejemplo práctico

El objetivo de esta última parte del capítulo consiste en ilustrar el uso de la
herencia de tablas de Doctrine junto con el generador de la parte de administración.
Para ello, se va a crear un backend sencillo que gestione dos tablas que contienen
información que se puede ordenar y priorizar.

Como el lema de Symfony es *"no reinventes la rueda"*, el modelo de Doctrine
va a utilizar el plugin [csDoctrineActAsSortablePlugin](http://www.symfony-project.org/plugins/csDoctrineActAsSortablePlugin "página del plugin csDoctrineActAsSortablePlugin")
para que se encargue de todo lo relacionado con la ordenación de objetos. El
plugin ~`csDoctrineActAsSortablePlugin`~ lo desarrolla y mantiene una empresa
llamada *CentreSource* y que es una de las empresas más activas dentro del
ecosistema de Symfony.

El modelo de datos es muy sencillo, ya que está formado por tres clases llamadas
`sfItem`, `sfTodoItem` y `sfShoppingItem`, que permiten gestionar una lista de
tareas y una lista de la compra. Cada elemento de las dos listas es ordenable
de forma que se pueda asignar la prioridad de los elementos en base a su
posición.

    [yml]
    sfItem:
      actAs:             [Timestampable]
      columns:
        name:
          type:          string(50)
          notnull:       true

    sfTodoItem:
      actAs:             [Sortable]
      inheritance:
        type:            concrete
        extends:         sfItem
      columns:
        priority:
          type:          string(20)
          notnull:       true
          default:       minor
        assigned_to:
          type:          string(30)
          notnull:       true
          default:       me

    sfShoppingItem:
      actAs:             [Sortable]
      inheritance:
        type:            concrete
        extends:         sfItem
      columns:
        quantity:
          type:          integer(3)
          notnull:       true
          default:       1

El esquema anterior describe el modelo de datos basado en tres clases diferentes.
Las dos sub-clases (`sfTodoItem` y `sfShoppingItem`) utilizan los comportamientos
`Sortable` y `Timestampable`. El comportamiento `Sortable` está disponible gracias
al plugin `csDoctrineActAsSortablePlugin` y añade en cada tabla una columna de
tipo entero llamada `position`. Las dos clases heredan de la clase base `sfItem`.
Esta clase contiene dos columnas llamadas `id` y `name`.

Para poder probar el backend se crea el siguiente archivo de datos. Como es
habitual, este archivo de datos se guarda en el archivo `data/fixtures.yml` del
proyecto Symfony.

    [yml]
    sfTodoItem:
      sfTodoItem_1:
        name:           "Write a new symfony book"
        priority:       "medium"
        assigned_to:    "Fabien Potencier"
      sfTodoItem_2:
        name:           "Release Doctrine 2.0"
        priority:       "minor"
        assigned_to:    "Jonathan Wage"
      sfTodoItem_3:
        name:           "Release symfony 1.4"
        priority:       "major"
        assigned_to:    "Kris Wallsmith"
      sfTodoItem_4:
        name:           "Document Lime 2 Core API"
        priority:       "medium"
        assigned_to:    "Bernard Schussek"

    sfShoppingItem:
      sfShoppingItem_1:
        name:           "Apple MacBook Pro 15.4 inches"
        quantity:       3
      sfShoppingItem_2:
        name:           "External Hard Drive 320 GB"
        quantity:       5
      sfShoppingItem_3:
        name:           "USB Keyboards"
        quantity:       2
      sfShoppingItem_4:
        name:           "Laser Printer"
        quantity:       1

Después de instalar el plugin `csDoctrineActAsSortablePlugin` y después de crear
el modelo de datos, es necesario activar el nuevo plugin en la clase
~`ProjectConfiguration`~ del archivo `config/ProjectConfiguration.class.php`:

    [php]
    class ProjectConfiguration extends sfProjectConfiguration
    {
      public function setup()
      {
        $this->enablePlugins(array(
          'sfDoctrinePlugin',
          'csDoctrineActAsSortablePlugin'
        ));
      }
    }

A continuación se puede generar la base de datos, el modelo, los formularios y
los filtros, además de cargar los datos de prueba en las tablas de la recién
creada base de datos. Todo esto se puede realizar con una única tarea llamada
~`doctrine:build`~:

    $ php symfony doctrine:build --all --no-confirmation

Para completar el proceso es necesario borrar la cache de Symfony y también se
deben enlazar los recursos del plugin desde el directorio `web/` del proyecto:

    $ php symfony cache:clear
    $ php symfony plugin:publish-assets

La siguiente sección explica cómo crear los módulos del backend con las herramientas
de generación de administraciones y también explica cómo aprovechar la nueva
clase base de las acciones.

#### Creando el backend

Esta sección describe los pasos necesarios para crear una nueva aplicación de
backend que contenga dos módulos generados automáticamente para gestionar las
listas de tareas y las listas de la compra. Por tanto, el primer paso consiste
en generar una nueva aplicación llamada `backend`:

    $ php symfony generate:app backend

Aunque el generador de administraciones es una herramienta muy completa, antes
de Symfony 1.3 el programador debía duplicar todo el código común de los
diferentes módulos. Ahora en cambio, la tarea ~`doctrine:generate-admin`~
incluye una nueva opción llamada ~`--actions-base-class`~ que permite al
programador definir la clase base de las acciones del módulo.

Como los dos módulos son muy parecidos, es seguro que compartirán el código de
las acciones genéricas. Este código compartido se puede incluir en una clase
base de las acciones que se encuentra en el directorio `lib/actions`, tal y como
se muestra a continuación:

    [php]
    // lib/actions/sfSortableModuleActions.class.php
    class sfSortableModuleActions extends sfActions
    {

    }

Una vez que se ha creado la nueva clase `sfSortableModuleActions` y después de
borrar la cache, ya es posible generar los dos módulos de la aplicación backend:

    $ php symfony doctrine:generate-admin --module=shopping --actions-base-class=sfSortableModuleActions backend sfShoppingItem

-

    $ php symfony doctrine:generate-admin --module=todo --actions-base-class=sfSortableModuleActions backend sfTodoItem

El generador de la parte de administración genera los módulos en dos directorios
diferentes. El primer directorio obviamente es `apps/backend/modules`. No
obstante, la mayoría de los archivos generados se encuentran en el directorio
`cache/backend/dev/modules`. Los archivos que se encuentran en ese directorio
se regeneran cada vez que se borra la cache o cuando se modifica la cofiguración
del módulo.

>**Note**
>Investigar los archivos generados en la cache es una de las mejores formas de
>aprender cómo funciona internamente el generador de administraciones de Symfony.
>Las nuevas sub-clases de `sfSortableModuleActions` las puedes encontrar en
>`cache/backend/dev/modules/autoShopping/actions/actions.class.php` y
>`cache/backend/dev/modules/autoTodo/actions/actions.class.php` respectivamente.
>Symfony genera por defecto estas clases para que hereden directamente de ~`sfActions`~.

![Gestión por defecto de la lista de tareas](http://www.symfony-project.org/images/more-with-symfony/06_table_inheritance_backoffice_todo_1.png "Gestión por defecto de la lista de tareas")

![Gestión por defecto de la lista de la compra](http://www.symfony-project.org/images/more-with-symfony/07_table_inheritance_backoffice_shopping_1.png "Gestión por defecto de la lista de la compra")

Los dos módulos del backend ya están listos para utilizarlos y personalizar su
comportamiento. No obstante, en este capítulo no se trata la configuración de los
módulos generados automáticamente. Afortunadamente existe mucha documentación
sobre este aspecto, como por ejemplo la 
[Referencia de Symfony](http://www.symfony-project.org/reference/1_3/en/06-Admin-Generator).

#### Modificando la posición de un elemento

La sección anterior describe cómo crear dos módulos de administración completamente
funcionales, cada uno de ellos heredando de la misma clase base de acciones. El
siguiente paso consiste en crear una acción compartida que permita reordenar los
elementos de una lista. Este requerimiento es bastante sencillo ya que el plugin
que acabamos de instalar incluye una completa API para reordenar los objetos.

En primer lugar se crean dos nuevas rutas preparadas para mover un registro
hacia arriba o hacia abajo. Como el generador de administraciones utiliza la
ruta ~`sfDoctrineRouteCollection`~, se pueden añadir fácilmente nuevas rutas a
la colección mediante el archivo de configuración `config/generator.yml` de
cada módulo:

    [yml]
    # apps/backend/modules/shopping/config/generator.yml
    generator:
      class: sfDoctrineGenerator
      param:
        model_class:           sfShoppingItem
        theme:                 admin
        non_verbose_templates: true
        with_show:             false
        singular:              ~
        plural:                ~
        route_prefix:          sf_shopping_item
        with_doctrine_route:   true
        actions_base_class:    sfSortableModuleActions

        config:
          actions: ~
          fields:  ~
          list:
            max_per_page:      100
            sort:              [position, asc]
            display:           [position, name, quantity]
            object_actions:
              moveUp:          { label: "move up", action: "moveUp" }
              moveDown:        { label: "move down", action: "moveDown" }
              _edit:      ~
              _delete:    ~
          filter:  ~
          form:    ~
          edit:    ~
          new:     ~

Los cambios anteriores también hay que incluirlos en el módulo `todo`:

    [yml]
    # apps/backend/modules/todo/config/generator.yml
    generator:
      class: sfDoctrineGenerator
      param:
        model_class:           sfTodoItem
        theme:                 admin
        non_verbose_templates: true
        with_show:             false
        singular:              ~
        plural:                ~
        route_prefix:          sf_todo_item
        with_doctrine_route:   true
        actions_base_class:    sfSortableModuleActions

        config:
          actions: ~
          fields:  ~
          list:
            max_per_page:      100
            sort:              [position, asc]
            display:           [position, name, priority, assigned_to]
            object_actions:
              moveUp:          { label: "move up", action: "moveUp" }
              moveDown:        { label: "move down", action: "moveDown" }
              _edit:      ~
              _delete:    ~
          filter:  ~
          form:    ~
          edit:    ~
          new:     ~

Los dos archivos YAML describen la configuración de los módulos `shopping` y
`todo`. Cada uno ha sido configurado para que se adapte a las necesidades del
usuario. En primer lugar, los listados se ordenan de forma ascendente según la
columna `position`. Además, el máximo número de elementos por página se ha
incrementado hasta 100 para evitar en lo posible la paginación de elementos.

Por último, el número de columnas que se muestran se han reducido a `position`,
`name`, `priority`, `assigned_to` y `quantity`. Además, cada módulo dispone de
dos nuevas acciones: `moveUp` y `moveDown`. El aspecto final de los listados
debe ser idéntico al de las siguientes imágenes:

![Administración personalizada de la lista de tareas](http://www.symfony-project.org/images/more-with-symfony/09_table_inheritance_backoffice_todo_2.png "Administración personalizada de la lista de tareas")

![Administración personalizada de la lista de la compra](http://www.symfony-project.org/images/more-with-symfony/08_table_inheritance_backoffice_shopping_2.png "Administración personalizada de la lista de tareas")

Estas dos nuevas acciones se han declarado pero todavía no hacen nada. Las dos
se deben crear en la clase base de las acciones `sfSortableModuleActions`, tal
y como se describe a continuación.  El plugin ~`csDoctrineActAsSortablePlugin`~
incluye dos métodos muy útiles en la clase de cada modelo: `promote()` y `demote()`.
Los dos se utilizan para crear las acciones `moveUp` y `moveDown`.

    [php]
    // lib/actions/sfSortableModuleActions.class.php
    class sfSortableModuleActions extends sfActions
    {
      /**
       * Moves an item up in the list.
       *
       * @param sfWebRequest $request
       */
      public function executeMoveUp(sfWebRequest $request)
      {
        $this->item = $this->getRoute()->getObject();

        $this->item->promote();

        $this->redirect($this->getModuleName());
      }

      /**
       * Moves an item down in the list.
       *
       * @param sfWebRequest $request
       */
      public function executeMoveDown(sfWebRequest $request)
      {
        $this->item = $this->getRoute()->getObject();

        $this->item->demote();

        $this->redirect($this->getModuleName());
      }
    }

Gracias a estas dos nuevas acciones compartidas, tanto la lista de tareas como
la lista de la compra permiten reordenar sus elementos. Además, estas acciones
son fáciles de mantener y de probar con las pruebas funcionales. Si quieres
puedes mejorar el aspecto de los dos módulos redefiniendo la plantilla de las
acciones del objeto para eliminar el primer enlace `move up` y el último enlace
`move down`.

#### Mejorando la experiencia de usuario

Antes de concluir este capítulo se van a retocar las dos listas para mejorar la
experiencia de usuario. Casi todo el mundo está de acuerdo en que mover un
elemento pinchando un enlace no es una forma muy intuitiva para la mayoría de
usuarios. Una forma mucho mejor de hacerlo consiste en utilizar comportamientos
de AJAX y JavaScript. En este último caso, todas las filas de la tabla HTML son
reordenables simplemente arrastrando y soltando (*drag & drop*) gracias al plugin
*~`Table Drag and Drop`~* de jQuery. Cada vez que el usuario mueva una fila de
la tabla HTML, se realizará una llamada mediante AJAX.

En primer lugar, descarga el framework jQuery e instálalo en el directorio `web/js`.
Repite este proceso para el plugin *`Table Drag and Drop`* cuyo código puedes
encontrar en un [repositorio de Google Code](http://code.google.com/p/tablednd/).

Para que esta nueva característica funcione, los listados de cada módulo deben
en primer lugar incluir cierto código JavaScript y las dos tablas necesitan un
atributo `id`. Como todas las plantillas y elementos parciales del generador de
administraciones se pueden redefinir, localiza el archivo `_list.php` de la
cache y copialo en los dos módulos.

Sin embargo, copiar el mismo archivo `_list.php` en el directorio `templates/`
de cada módulo no es una práctica muy DRY (*Don't Repeat Yourself*). Por tanto,
copiar el archivo `cache/backend/dev/modules/autoShopping/templates/_list.php`
en el directorio `apps/backend/templates/` y cambia su nombre a `_table.php`.
Reemplaza su contenido actual por el siguiente código:

    [php]
    <div class="sf_admin_list">
      <?php if (!$pager->getNbResults()): ?>
        <p><?php echo __('No result', array(), 'sf_admin') ?></p>
      <?php else: ?>
        <table cellspacing="0" id="sf_item_table">
          <thead>
            <tr>
              <th id="sf_admin_list_batch_actions"><input id="sf_admin_list_batch_checkbox" type="checkbox" onclick="checkAll();" /></th>
              <?php include_partial(
                $sf_request->getParameter('module').'/list_th_tabular',
                array('sort' => $sort)
              ) ?>
              <th id="sf_admin_list_th_actions">
                <?php echo __('Actions', array(), 'sf_admin') ?>
              </th>
            </tr>
          </thead>
          <tfoot>
            <tr>
              <th colspan="<?php echo $colspan ?>">
                <?php if ($pager->haveToPaginate()): ?>
                  <?php include_partial(
                    $sf_request->getParameter('module').'/pagination',
                    array('pager' => $pager)
                  ) ?>
                <?php endif; ?>
                <?php echo format_number_choice(
                  '[0] no result|[1] 1 result|(1,+Inf] %1% results', 
                  array('%1%' => $pager->getNbResults()),
                  $pager->getNbResults(), 'sf_admin'
                ) ?>
                <?php if ($pager->haveToPaginate()): ?>
                  <?php echo __('(page %%page%%/%%nb_pages%%)', array(
                    '%%page%%' => $pager->getPage(), 
                    '%%nb_pages%%' => $pager->getLastPage()), 
                    'sf_admin'
                  ) ?>
                <?php endif; ?>
              </th>
            </tr>
          </tfoot>
          <tbody>
          <?php foreach ($pager->getResults() as $i => $item): ?>
            <?php $odd = fmod(++$i, 2) ? 'odd' : 'even' ?>
            <tr class="sf_admin_row <?php echo $odd ?>">
              <?php include_partial(
                $sf_request->getParameter('module').'/list_td_batch_actions',
                array(
                  'sf_'. $sf_request->getParameter('module') .'_item' => $item,
                  'helper' => $helper
              )) ?>
              <?php include_partial(
                $sf_request->getParameter('module').'/list_td_tabular', 
                array(
                  'sf_'. $sf_request->getParameter('module') .'_item' => $item
              )) ?>
                <?php include_partial(
                  $sf_request->getParameter('module').'/list_td_actions',
                  array(
                    'sf_'. $sf_request->getParameter('module') .'_item' => $item, 
                    'helper' => $helper
                )) ?>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      <?php endif; ?>
      </div>
      <script type="text/javascript">
        /* <![CDATA[ */
        function checkAll() {
          var boxes = document.getElementsByTagName('input'); 
          for (var index = 0; index < boxes.length; index++) { 
            box = boxes[index]; 
            if (
              box.type == 'checkbox' 
              && 
              box.className == 'sf_admin_batch_checkbox'
            ) 
            box.checked = document.getElementById('sf_admin_list_batch_checkbox').checked 
          }
          return true;
        }
        /* ]]> */
      </script>

Por último, crea un archivo `_list.php` en el directorio `templates/` de cada
módulo y coloca el siguiente código en cada uno:

    [php]
    // apps/backend/modules/shopping/templates/_list.php
    <?php include_partial('global/table', array(
      'pager' => $pager,
      'helper' => $helper,
      'sort' => $sort,
      'colspan' => 5
    )) ?>
    
-

    // apps/backend/modules/shopping/templates/_list.php
    <?php include_partial('global/table', array(
      'pager' => $pager,
      'helper' => $helper,
      'sort' => $sort,
      'colspan' => 8
    )) ?>

Para modificar la posición de una fila, los dos módulos deben implementar una
nueva acción que procese la petición AJAX entrante. Como se ha explicado anteriormente,
la nueva acción compartida `executeMove()` debe añadirse a la clase base de acciones
`sfSortableModuleActions`:

    [php]
    // lib/actions/sfSortableModuleActions.class.php
    class sfSortableModuleActions extends sfActions
    {
      /**
       * Performs the Ajax request, moves an item to a new position.
       *
       * @param sfWebRequest $request
       */
      public function executeMove(sfWebRequest $request)
      {
        $this->forward404Unless($request->isXmlHttpRequest());
        $this->forward404Unless($item = Doctrine_Core::getTable($this->configuration->getModel())->find($request->getParameter('id')));

        $item->moveToPosition((int) $request->getParameter('rank', 1));

        return sfView::NONE;
      }
    }

La acción `executeMove()` requiere un método llamado `getModel()` en el objeto
de la configuración. Implementa este nuevo método en las clases `todoGeneratorConfiguration`
y `shoppingGeneratorConfiguration` tal y como se muestra a continuación:

    [php]
    // apps/backend/modules/shopping/lib/shoppingGeneratorConfiguration.class.php
    class shoppingGeneratorConfiguration extends BaseShoppingGeneratorConfiguration
    {
      public function getModel()
      {
        return 'sfShoppingItem';
      }
    }

-

    // apps/backend/modules/todo/lib/todoGeneratorConfiguration.class.php
    class todoGeneratorConfiguration extends BaseTodoGeneratorConfiguration
    {
      public function getModel()
      {
        return 'sfTodoItem';
      }
    }

Todavía queda una última tarea que hacer. Por el momento, las filas de las tablas
no se pueden arrastrar y no se realiza ninguna llamada AJAX cuando se recoloca
una fila. Para implementar estas características, los dos módulos necesitan una
ruta específica para acceder a su correspondiente acción `move`. Por tanto,
añade en el archivo `apps/backend/config/routing.yml` las dos siguientes rutas:

    [php]
    <?php foreach (array('shopping', 'todo') as $module) : ?>

    <?php echo $module ?>_move:
      class: sfRequestRoute
      url: /<?php echo $module ?>/move
      param:
        module: "<?php echo $module ?>"
        action: move
      requirements:
        sf_method: [get]

    <?php endforeach ?>

Para evitar el código duplicado, las dos nuevas rutas se generan mediante una
instrucción `foreach` y hacen uso del nombre del módulo para utilizarlas
fácilmente desde la vista. Por último, el archivo `apps/backend/templates/_table.php`
debe incluir el siguiente código JavaScript para añadir el comportamiento de
"arrastrar y soltar" en las filas de las tablas y para realizar la correspondiente
llamada AJAX:

    [php]
    <script type="text/javascript" charset="utf-8">
      $().ready(function() {
        $("#sf_item_table").tableDnD({
          onDrop: function(table, row) {
            var rows = table.tBodies[0].rows;

            // Get the moved item's id
            var movedId = $(row).find('td input:checkbox').val();

            // Calculate the new row's position
            var pos = 1;
            for (var i = 0; i<rows.length; i++) {
              var cells = rows[i].childNodes;
              // Perform the ajax request for the new position
              if (movedId == $(cells[1]).find('input:checkbox').val()) {
                $.ajax({
                  url:"<?php echo url_for('@'. $sf_request->getParameter('module').'_move') ?>?id="+ movedId +"&rank="+ pos,
                  type:"GET"
                });
                break;
              }
              pos++;
            }
          },
        });
      });
    </script>

La tabla HTML ya es completamente funcional. Sus filas se pueden arrastrar y
soltar y la nueva posición de los elementos se guarda automáticamente gracias
a las llamadas AJAX. Añadiendo un poco de código en las acciones y plantillas,
la usabilidad del backend ha mejorado enormemente, mejorando también la
experiencia de usuario. El generador de la parte de administración es suficientemente
flexible como para extenderlo y personalizarlo, además de soportar todas las
características de la herencia de tablas de Doctrine.

Si quieres ahora puedes mejorar los dos módulos eliminando las acciones `moveUp`
y `moveDown` obsoletas y añadiendo cualquier otro cambio que se ajuste a tus
necesidades.

Conclusión
----------

Este capítulo ha mostrado cómo la ~herencia de tablas~ de Doctrine es una utilidad
muy poderosa que permite al programador crear código más rápidamente y mejorar
la organización del código. Esta característica de Doctrine se encuentra
completamente integrada en varios niveles de Symfony, por lo que animamos a
todos los programadores a que la utilicen para aumentar su eficiencia y mejoren
la organización de su código.
