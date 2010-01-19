Uso avanzado de Doctrine
========================

*por Jonathan H. Wage*

Creando un comportamiento de Doctrine
-------------------------------------

En esta sección se explica cómo crear un nuevo comportamiento haciendo uso de 
Doctrine 1.2. El ejemplo utilizado permitirá mantener una cache del número
de relaciones de un registro para no tener que hacer esa consulta todo el rato.

La funcionalidad es realmente simple: en todas las relaciones en las que quieras
controlar su número, el comportamiento añade una columna a su modelo para almacenar
un contador.

### El esquema

Inicialmente se va a utilizar el siguiente esquema. Más adelante se modifica
para añadir la definición `actAs` del comportamiento que se va a crear:

    [yml]
    # config/doctrine/schema.yml
    Thread:
      columns:
        title:
          type: string(255)
          notnull: true

    Post:
      columns:
        thread_id:
          type: integer
          notnull: true
        body:
          type: clob
          notnull: true
      relations:
        Thread:
          onDelete: CASCADE
          foreignAlias: Posts

Seguidamente se construyen todas las clases del esquema:

    $ php symfony doctrine:build --all

### La plantilla

En primer lugar se crea la clase básica de tipo `Doctrine_Template` que será
la responsable de añadir las columnas al modelo que guardará los contadores.

Añade la siguiente clase dentro de cualquier directorio `lib/` del proyecto 
para que symfony pueda cargarla de forma automática:

    [php]
    // lib/count_cache/CountCache.class.php
    class CountCache extends Doctrine_Template
    {
      public function setTableDefinition()
      {
      }

      public function setUp()
      {
      }
    }

A continuación se modifica el modelo `Post` para añadir el comportamiento
`CountCache` mediante `actAs`:

    [yml]
    # config/doctrine/schema.yml
    Post:
      actAs:
        CountCache: ~
      # ...

Ahora que el modelo `Post` hace uso del comportamiento `CountCache`, su
funcionamiento es el siguiente: cuando se instancia la información de mapeo de
un modelo, se invocan los métodos `setTableDefinition()` y `setUp()` de todos
sus comportamientos asociados. Esto es lo mismo que sucede con la clase 
`BasePost` en `lib/model/doctrine/base/BasePost.class.php`. Esta característica
permite añadir elementos de todo tipo a un modelo, como columnas, relaciones,
eventos, etc.

Ahora que está más claro su funcionamiento interno, se añade toda la lógica
interna del comportamiento `CountCache`:

    [php]
    class CountCache extends Doctrine_Template
    {
      protected $_options = array(
        'relations' => array()
      );

      public function setTableDefinition()
      {
        foreach ($this->_options['relations'] as $relation => $options)
        {
          // si no se dispone del nombre de la columna, se crea
          if (!isset($options['columnName']))
          {
            $this->_options['relations'][$relation]['columnName'] = 'num_'.Doctrine_Inflector::tableize($relation);
          }

          // añadir la columna al modelo relacionado
          $columnName = $this->_options['relations'][$relation]['columnName'];
          $relatedTable = $this->_table->getRelation($relation)->getTable();
          $this->_options['relations'][$relation]['className'] = $relatedTable->getOption('name');
          $relatedTable->setColumn($columnName, 'integer', null, array('default' => 0));
        }
      }
    }

El código superior añade columnas para mantener los contadores de los modelos
relacionados. Por tanto, en este caso se añade el comportamiento en el modelo
`Post` para su relación `Thread`. De esta forma, el número de posts de cualquier 
`Thread` se almacena en una columna llamada `num_posts`. A continuación, 
modifica el esquema YAML para definir las opciones adicionales del comportamiento:

    [yml]
    # ...

    Post:
      actAs:
        CountCache:
          relations:
            Thread:
              columnName: num_posts
              foreignAlias: Posts
      # ...

Ahora el modelo `Thread` dispone de una columna llamada `num_posts` y que 
guardará de forma actualizada el número de posts que tiene cada hilo de discusión.

### El event listener

El siguiente paso consiste en crear un *event listener* de registro que será el
que se encargue de mantener actualizado el contador cuando se creen nuevos
registros y cuando se borren registros de forma individual o en bloque.

    [php]
    class CountCache extends Doctrine_Template
    {
      // ...

      public function setTableDefinition()
      {
        // ...

        $this->addListener(new CountCacheListener($this->_options));
      }
    }

Antes de continuar es necesario definir la clase `CountCacheListener` que 
extiende la clase `Doctrine_Record_Listener` y que acepta un array de opciones
que simplemente se pasan al *listener* de la plantilla:

    [php]
    // lib/model/count_cache/CountCacheListener.class.php

    class CountCacheListener extends Doctrine_Record_Listener
    {
      protected $_options;

      public function __construct(array $options)
      {
        $this->_options = $options;
      }
    }

Para mantener los contadores actualizados es preciso utilizar los siguientes
eventos:

 * **postInsert()**: incrementa el contador cuando se inserta un nuevo objeto

 * **postDelete()**: decrementa el contador cuando se borra un objeto

 * **preDqlDelete()**: decrementa el contador cuando se borrar varios 
   objetos mediante un borrado DQL.

En primer lugar se define el método `postInsert()`:

    [php]
    class CountCacheListener extends Doctrine_Record_Listener
    {
      // ...

      public function postInsert(Doctrine_Event $event)
      {
        $invoker = $event->getInvoker();
        foreach ($this->_options['relations'] as $relation => $options)
        {
          $table = Doctrine::getTable($options['className']);
          $relation = $table->getRelation($options['foreignAlias']);

          $table
            ->createQuery()
            ->update()
            ->set($options['columnName'], $options['columnName'].' + 1')
            ->where($relation['local'].' = ?', $invoker->$relation['foreign'])
            ->execute();
        }
      }
    }

El código anterior incrementa en una unidad, mediante una consulta de tipo 
DQL UPDATE, los contadores de todas las relaciones configuradas cada vez que 
se inserta un nuevo objeto, como por ejemplo el siguiente:

    [php]
    $post = new Post();
    $post->thread_id = 1;
    $post->body = 'contenido del post';
    $post->save();

El `Thread` cuyo `id` valga `1` incrementará en una unidad el valor de su
columna `num_posts`.

Ahora que los contadores ya se incrementan al insertar nuevos objetos, es necesario
decrementarlos cuando se borre algún objeto. Para ello se define el siguiente
método `postDelete()`:

    [php]
    class CountCacheListener extends Doctrine_Record_Listener
    {
      // ...

      public function postDelete(Doctrine_Event $event)
      {
        $invoker = $event->getInvoker();
        foreach ($this->_options['relations'] as $relation => $options)
        {
          $table = Doctrine::getTable($options['className']);
          $relation = $table->getRelation($options['foreignAlias']);

          $table
            ->createQuery()
            ->update()
            ->set($options['columnName'], $options['columnName'].' - 1')
            ->where($relation['local'].' = ?', $invoker->$relation['foreign'])
            ->execute();
        }
      }
    }

El método `postDelete()` superior es casi idéntico al método `postInsert()`,
siendo la única diferencia que en este caso el valor de la columna `num_posts`
se decrementa en una unidad. Si ahora se borra el registro creado anteriormente,
el contador se actualiza correctamente:

    [php]
    $post->delete();

La última parte del comportamiento debe encargarse de los borrados masivos
realizados con una consulta de tipo DQL. La solución consiste en crear un 
método `preDqlDelete()`:

    [php]
    class CountCacheListener extends Doctrine_Record_Listener
    {
      // ...

      public function preDqlDelete(Doctrine_Event $event)
      {
        foreach ($this->_options['relations'] as $relation => $options)
        {
          $table = Doctrine::getTable($options['className']);
          $relation = $table->getRelation($options['foreignAlias']);

          $q = clone $event->getQuery();
          $q->select($relation['foreign']);
          $ids = $q->execute(array(), Doctrine::HYDRATE_NONE);

          foreach ($ids as $id)
          {
            $id = $id[0];

            $table
              ->createQuery()
              ->update()
              ->set($options['columnName'], $options['columnName'].' - 1')
              ->where($relation['local'].' = ?', $id)
              ->execute();
          }
        }
      }
    }

El código anterior clona la consulta de tipo `DQL DELETE` y la transforma en
una consulta `SELECT` que permite obtener los `ID` de los registros que se 
van a borrar, de forma que se pueda actualizar correctamente el contador.

Ahora ya es posible manejar consultas como la siguiente actualizando de forma
correcta el valor de los contadores:

    [php]
    Doctrine::getTable('Post')
      ->createQuery()
      ->delete()
      ->where('id = ?', 1)
      ->execute();

El valor de los contadores se actualiza correctamente incluso cuando se borran
varios registros a la vez:

    [php]
    Doctrine::getTable('Post')
      ->createQuery()
      ->delete()
      ->where('body LIKE ?', '%cool%')
      ->execute();

>**NOTE**
>Para invocar el método `preDqlDelete()` es necesario activar un atributo.
>El motivo es que los *callbacks* de DQL están desactivados por defecto porque
>penalizan ligeramente el rendimiento. Por tanto, para utilizarlos es necesario
>activarlos:
>
>     [php]
>     $manager->setAttribute(Doctrine_Core::ATTR_USE_DQL_CALLBACKS, true);

¡Y eso es todo! El nuevo comportamiento ya está terminado. Lo último que falta 
por hacer es añadir algunas pruebas unitarias.

### Pruebas

Ahora que el código ya está completado, se va a probar con los siguientes datos
de prueba:

    [yml]
    # data/fixtures/data.yml

    Thread:
      thread1:
        title: Test Thread
        Posts:
          post1:
            body: This is the body of my test thread
          post2:
            body: This is really cool
          post3:
            body: Ya it is pretty cool

A continuación se ejecuta la siguiente tarea para volver a crear todas las 
clases y para cargar todos los datos de prueba:

    $ php symfony doctrine:build --all --and-load

Después de volver a crear y cargar todo, se realiza la siguiente prueba para
comprobar que los contadores se actualizan correctamente:

    $ php symfony doctrine:dql "FROM Thread t, t.Posts p"
    doctrine - executing: "FROM Thread t, t.Posts p" ()
    doctrine -   id: '1'
    doctrine -   title: 'Test Thread'
    doctrine -   num_posts: '3'
    doctrine -   Posts:
    doctrine -     -
    doctrine -       id: '1'
    doctrine -       thread_id: '1'
    doctrine -       body: 'This is the body of my test thread'
    doctrine -     -
    doctrine -       id: '2'
    doctrine -       thread_id: '1'
    doctrine -       body: 'This is really cool'
    doctrine -     -
    doctrine -       id: '3'
    doctrine -       thread_id: '1'
    doctrine -       body: 'Ya it is pretty cool'

El valor de la columna `num_posts` del modelo `Thread` vale tres. Si se borra
un post mediante el siguiente comando, el contador debe decrementarse:

    [php]
    $post = Doctrine_Core::getTable('Post')->find(1);
    $post->delete();

Como se puede comprobar, el registro se ha borrado y el contador se ha actualizado.

    $ php symfony doctrine:dql "FROM Thread t, t.Posts p"
    doctrine - executing: "FROM Thread t, t.Posts p" ()
    doctrine -   id: '1'
    doctrine -   title: 'Test Thread'
    doctrine -   num_posts: '2'
    doctrine -   Posts:
    doctrine -     -
    doctrine -       id: '2'
    doctrine -       thread_id: '1'
    doctrine -       body: 'This is really cool'
    doctrine -     -
    doctrine -       id: '3'
    doctrine -       thread_id: '1'
    doctrine -       body: 'Ya it is pretty cool'

También funciona correctamente cuando se borran los otros dos registros 
restantes mediante una consulta de tipo DQL.

    [php]
    Doctrine_Core::getTable('Post')
      ->createQuery()
      ->delete()
      ->where('body LIKE ?', '%cool%')
      ->execute();

Ahora que se han borrado todos los posts relacionados, el valor de la columna
`num_posts` debería ser cero.

    $ php symfony doctrine:dql "FROM Thread t, t.Posts p"
    doctrine - executing: "FROM Thread t, t.Posts p" ()
    doctrine -   id: '1'
    doctrine -   title: 'Test Thread'
    doctrine -   num_posts: '0'
    doctrine -   Posts: {  }

¡Y eso es todo! Confiamos que este artículo te haya sido útil tanto por haber
aprendido a crear comportamientos como por el propio comportamiento creado.

Utilizando la cache de resultados de Doctrine
---------------------------------------------

En los sitios web con mucho tráfico es necesario guardar la información en
caches para aliviar algunos recursos de la CPU. En la última versión de 
doctrine 1.2 se han añadido muchas mejoras a la cache de resultados para tener
un mejor control sobre el borrado de las entradas de la cache. Antes no se podía
especificar la clave asociada con cada entrada de la cache, por lo que no era
posible identificar correctamente la entrada que se quería borrar.

En esta sección se muestra un ejemplo sencillo de cómo utilizar la cache de 
resultados para guardar en ella todas las consultas relacionadas con los
usuarios, así como el uso de eventos para borrar todas las entradas cuya
información haya sido modificada.

### El esquema

El siguiente esquema es el que se va a utilizar en este ejemplo:

    [yml]
    # config/doctrine/schema.yml
    User:
      columns:
        username:
          type: string(255)
          notnull: true
          unique: true
        password:
          type: string(255)
          notnull: true

A continuación se crean todas las clases con el siguiente comando:

    $ php symfony doctrine:build --all

Después de ejecutarla, se habrá generado la siguiente clase llamada `User`:

    [php]
    // lib/model/doctrine/User.class.php
    /**
     * User
     *
     * This class has been auto-generated by the Doctrine ORM Framework
     *
     * @package    ##PACKAGE##
     * @subpackage ##SUBPACKAGE##
     * @author     ##NAME## <##EMAIL##>
     * @version    SVN: $Id: Builder.php 6508 2009-10-14 06:28:49Z jwage $
     */
    class User extends BaseUser
    {
    }

Más adelante se añadirá el código correspondiente en esta clase, así que no la
pierdas de vista.

### Configurando la cache de resultados

Antes de utilizar la cache de resultados es necesario configurar el driver de
la cache que utilizarán las consultas. Esta configuración se realiza mediante
el atributo `ATTR_RESULT_CACHE`. En este ejemplo se hace uso del driver APC
porque es la mejor elección para los entornos de producción. Si no dispones de
APC, puedes utilizar los drivers `Doctrine_Cache_Db` o `Doctrine_Cache_Array`
para hacer las pruebas.

Este atributo se puede definir en la clase `ProjectConfiguration`, añadiendo
un método llamado `configureDoctrine()`:

    [php]
    // config/ProjectConfiguration.class.php

    // ...
    class ProjectConfiguration extends sfProjectConfiguration
    {
      // ...

      public function configureDoctrine(Doctrine_Manager $manager)
      {
        $manager->setAttribute(Doctrine_Core::ATTR_RESULT_CACHE, new Doctrine_Cache_Apc());
      }
    }

Una vez configurado el driver de la cache, ya se puede hacer uso de este driver
para almacenar en la cache el resultado de las búsquedas.

### Consultas de prueba

Imagina que tu aplicación tiene varias consultas relacionadas con los usuarios
y que quieres borrarlas de la cache cada vez que se modifica alguna información 
del usuario.

La siguiente consulta se puede utilizar para mostrar una lista completa de 
todos los usuarios ordenados alfabéticamente:

    [php]
    $q = Doctrine_Core::getTable('User')
        ->createQuery('u')
        ->orderBy('u.username ASC');

Para guardar el resultado de esa consulta en la cache, se utiliza el método
`useResultCache()`:

    [php]
    $q->useResultCache(true, 3600, 'users_index');

>**NOTE**
>El tercer argumento del método es muy importante, ya que es la clave con la que
>se asociarán los resultados en el driver de la cache. De esta forma es posible
>identificar fácilmente a esa consulta para borrarla más adelante.

Cuando se ejecuta el código anterior, se realiza la consulta a la base de datos
y los resultados se guardan en el driver de la cache bajo la clave `users_index`.
Cuando se vuelve a ejecutar el código anterior, los resultados se obtienen
directamente de la cache en vez de realizar la consulta en la base de datos:

    [php]
    $usuarios = $q->execute();

>**NOTE**
>La cache no sólo ahorra recursos en el servidor de base de datos, sino que 
>también evita todo el procesamiento de los registros, llamado *hidratación*.
>Doctrine guarda en la cache los registros ya procesados, por lo que también
>se liberan recursos del servidor web.

Si ahora se busca en el driver de la cache, se obtiene una entrada llamada
`users_index`:

    [php]
    if ($cacheDriver->contains('users_index'))
    {
      echo 'existe la cache';
    }
    else
    {
      echo 'no existe la cache';
    }

### Borrando la cache

Ahora que la consulta ya se ha guardado en la cache, el siguiente paso consiste
en aprender a borrar esa cache. El borrado se puede realizar manualmente con
la API del driver de la cache o se pueden utilizar los eventos para borrar la
cache automáticamente cuando se inserta o modifica un usuario.

#### La API del driver de la cache

Antes de utilizarla en un evento, se va a mostrar el uso manual de la API del
driver de la cache.

>**TIP**
>La instancia del driver de la cache se puede obtener mediante la instancia de
>la clase `Doctrine_Manager`.
>
>     [php]
>     $cacheDriver = $manager->getAttribute(Doctrine_Core::ATTR_RESULT_CACHE);
>
>Si no está definida la variable `$manager`, puedes obtener la instancia
>correspondiente con el siguiente código.
>
>     [php]
>     $manager = Doctrine_Manager::getInstance();

Ahora ya se puede hacer uso de la API para borrar las entradas de la cache:

    [php]
    $cacheDriver->delete('users_index');

Seguramente la cache contendrá más de una consulta relacionada con los
usuarios y todas ellas harán uso del mismo prefijo `users_` así que el método
`delete()` no es muy útil en este caso. En su lugar se puede utilizar el método
`deleteByPrefix()` para borrar la cache de todas las consultas que contengan el
prefijo indicado:

    [php]
    $cacheDriver->deleteByPrefix('users_');

Si el método `deleteByPrefix()` no es suficiente, existen otros métodos muy
útiles para borrar entradas de la cache:

 * `deleteBySuffix($sufijo)`: borra las entradas de la cache que contengan el
   sufijo indicado.

 * `deleteByRegularExpression($regex)`: borra las entradas de la cache cuya clave
   cumpla con la expresión regular indicada.

 * `deleteAll()`: borra todas las entradas de la cache.

### Borrando con eventos

La forma ideal de borrar la cache consiste en que se borre automáticamente
cada vez que se modifica algún dato del usuario. Para ello, sólo es necesario
configurar un evento en el método `postSave()` de la clase del modelo `User`.

¿Recuerdas la clase `User` creada anteriormente? Abre la clase con tu editor
favorito y añade el código del siguiente método `postSave()`:

    [php]
    // lib/model/doctrine/User.class.php

    class User extends BaseUser
    {
      // ...

      public function postSave($event)
      {
        $cacheDriver = $this->getTable()->getAttribute(Doctrine_Core::ATTR_RESULT_CACHE);
        $cacheDriver->deleteByPrefix('users_');
      }
    }

Ahora, cada vez que se actualiza un usuario y cada vez que se inserta un nuevo
usuario, se borran de la cache todas las consultas relacionadas con los usuarios:

    [php]
    $user = new User();
    $user->username = 'jwage';
    $user->password = 'changeme';
    $user->save();

Después de ejecutar el código anterior, la próxima vez que se realicen las consultas
de los usuarios no existirá una cache con los resultados, por lo que se volverán
a realizar las consultas en la base de datos. En las siguientes consultas,
volverán a utilizarse las entradas guardadas en la cache.

Aunque el ejemplo mostrado es muy sencillo, es útil para hacerse una idea de 
cómo se puede utilizar esta característica de Doctrine para tener un control
muy preciso de la forma en la que se guardan las consultas en la cache.

Creando un hydrator de Doctrine
-------------------------------

Una de las principales características de Doctrine en su habilidad para
transformar un objeto de tipo `Doctrine_Query` en resultados con diferentes
estructuras. Esta tarea la realizan los *hydrators* de Doctrine y hasta la
versión 1.2 de Doctrine los programadores no podían crear sus propios *hydrators*.
Ahora que ya es posible hacerlo, se puede desarrollar un *hydrator* propio para
crear cualquier tipo de estructura a partir de los resultados obtenidos
mediante `Doctrine_Query`.

El siguiente ejemplo muestra cómo crear un *hydrator* muy sencillo y fácil de 
entender, pero a la vez muy útil. El funcionamiento del *hydrator* consiste en
seleccionar un par de columnas y transformarlas en un array asociativo en el 
que la clave de cada elemento del array es el valor de la primera columna y
el valor de cada elemento del array es el valor de la segunda columna.

### El esquema y los datos de prueba

Para realizar las pruebas se va a utilizar el siguiente esquema de un modelo
sencillo llamado `User`:

    [yml]
    # config/doctrine/schema.yml
    User:
      columns:
        username: string(255)
        is_active: string(255)

Como también son necesarios algunos datos de prueba, se va a hacer uso de los
siguientes:

    [yml]
    # data/fixtures/data.yml
    User:
      user1:
        username: jwage
        password: changeme
        is_active: 1
      user2:
        username: jonwage
        password: changeme
        is_active: 0

A continuación ejecuta la siguiente tarea para crear todas las clases:

    $ php symfony doctrine:build --all --and-load

### Creando el *hydrator*

Para crear un *hydrator* sólo es necesario crear una nueva clase que herede de
`Doctrine_Hydrator_Abstract` y que implemente un método llamado `hydrateResultSet($stmt)`.
Este método recibe como argumento una instancia del `PDOStatement` utilizado para
ejecutar la consulta. Por tanto, se puede utilizar este objeto para obtener los
resultados de la consulta directamente del PDO y transformarlos en la estructura
deseada.

Se crea una nueva clase llamada `KeyValuePairHydrator` y se coloca en el directorio
`lib/` para que symfony pueda cargarla automáticamente:

    [php]
    // lib/KeyValuePairHydrator.class.php
    class KeyValuePairHydrator extends Doctrine_Hydrator_Abstract
    {
      public function hydrateResultSet($stmt)
      {
        return $stmt->fetchAll(Doctrine_Core::FETCH_NUM);
      }
    }

El código anterior por el momento sólo devuelve los datos tal y como los 
devuelve PDO. Esto no es lo que queremos, ya que queremos transformar los
datos en una estructura de tipo `clave => valor`. Modifica por tanto el método
`hydrateResultSet()` para completar su funcionalidad:

    [php]
    // lib/KeyValuePairHydrator.class.php
    class KeyValuePairHydrator extends Doctrine_Hydrator_Abstract
    {
      public function hydrateResultSet($stmt)
      {
        $results = $stmt->fetchAll(Doctrine_Core::FETCH_NUM);
        $array = array();
        foreach ($results as $result)
        {
          $array[$result[0]] = $result[1];
        }

        return $array;
      }
    }

¡Ha sido bastante fácil! El código del *hydrator* ya está terminado y hace
exactamente lo que queríamos, así que vamos a probarlo.

### Utilizando el *hydrator*

Antes de utilizar el *hydrator* es necesario registrarlo en Doctrine para que
esté disponible cuando se ejecuten las consultas. Para ello, regístralo en la 
instancia del `Doctrine_Manager` en la clase `ProjectConfiguration`:

    [php]
    // config/ProjectConfiguration.class.php

    // ...
    class ProjectConfiguration extends sfProjectConfiguration
    {
      // ...

      public function configureDoctrine(Doctrine_Manager $manager)
      {
        $manager->registerHydrator('key_value_pair', 'KeyValuePairHydrator');
      }
    }

Ahora que el *hydrator* ya está registrado, se puede utilizar en cualquier
instancia de `Doctrine_Query`, tal y como muestra el siguiente ejemplo:

    [php]
    $q = Doctrine_Core::getTable('User')
      ->createQuery('u')
      ->select('u.username, u.is_active');

    $results = $q->execute(array(), 'key_value_pair');
    print_r($results);

Si se ejecuta el código anterior con los datos de prueba mostrados anteriormente,
el resultado es el siguiente:

    Array
    (
        [jwage] => 1
        [jonwage] => 0
    )

¡Y eso es todo! Bastante fácil, ¿verdad? Esperamos que te haya sido útil y que
te animes a crear *hydrators* interesantes y los compartas con el resto de la 
comunidad.
