Enrutamiento avanzado
=====================

*por Ryan Weaver*

El framework de enrutamiento es básicamente un mapa que enlaza cada URL con un
lugar específico dentro de un proyecto Symfony y viceversa. Gracias al
enrutamiento se pueden crear URL *bonitas* que además son completamente
independientes de la lógica de la aplicación. Con los avances incorporados en
las versiones más recientes de Symfony, el framework de enrutamiento es todavía
más completo.

En este capítulo se muestra cómo crear una aplicación web sencilla en la que
cada usuario utiliza un subdominio diferente (`cliente1.midominio.com` y
`cliente2.midominio.com`). Esto se puede conseguir fácilmente extendiendo el
framework de enrutamiento.

>**NOTE**
>Este capítulo requiere utilizar Doctrine como ORM del proyecto.

Preparación del proyecto: un CMS para muchos clientes
-----------------------------------------------------

En este proyecto, una empresa imaginaria llamada *Sympal Builder* quiere crear
un CMS para que sus clientes puedan construir sitios web como subdominios de
`sympalbuilder.com`. En concreto, el cliente XXX puede ver su sitio web en
`xxx.sympalbuilder.com` y hacer uso del área de administración en
`xxx.sympalbuilder.com/backend.php`.

>**NOTE**
>El nombre `Sympal` se ha tomado prestado del proyecto [Sympal](http://www.sympalphp.org/)
>creado por Jonathan Wage, que es un framework de gestores de contenidos (CMF)
>desarrollado con Symfony.

Los dos requerimientos básicos del proyecto son:

  * Los usuarios pueden crear páginas y especificar el título, contenido y URL
    de esas páginas.

  * Toda la aplicación debe construirse dentro de un único proyecto de Symfony
    que gestione el *frontend* y *backend* de todos los sitios de los clientes
    y que obtenga los datos adecuados en función del subdominio utilizado por
    cada cliente.

>**NOTE**
>Para crear esta aplicación, el servidor debe configurarse para redirigir todos
>los subdominios `*.sympalbuilder.com` al mismo directorio raíz, que es el
>directorio web del proyecto Symfony.

### El esquema y los datos

La base de datos del proyecto está formada por clientes (objeto `Client`) y
páginas (objeto `Page`). Cada cliente representa un sitio web accesible mediante
un subdominio y puede contener varias páginas.

    [yml]
    # config/doctrine/schema.yml
    Client:
      columns:
        name:       string(255)
        subdomain:  string(50)
      indexes:
        subdomain_index:
          fields:   [subdomain]
          type:     unique

    Page:
      columns:
        title:      string(255)
        slug:       string(255)
        content:    clob
        client_id:  integer
      relations:
        Client:
          alias:        Client
          foreignAlias: Pages
          onDelete:     CASCADE
      indexes:
        slug_index:
          fields:   [slug, client_id]
          type:     unique

>**NOTE**
>Aunque los índices de cada tabla no son obligatorios, es mejor añadirlos porque
>la aplicación va a realizar muchas consultas que utilizan estas columnas.

Para poder probar el funcionamiento del proyecto, añade los siguientes datos de
prueba en el archivo `data/fixtures/fixtures.yml`:

    [yml]
    # data/fixtures/fixtures.yml
    Client:
      client_pete:
        name:      Pete's Pet Shop
        subdomain: pete
      client_pub:
        name:      City Pub and Grill
        subdomain: citypub

    Page:
      page_pete_location_hours:
        title:     Location and Hours | Pete's Pet Shop
        content:   We're open Mon - Sat, 8 am - 7pm
        slug:      location
        Client:    client_pete
      page_pub_menu:
        title:     City Pub And Grill | Menu
        content:   Our menu consists of fish, Steak, salads, and more.
        slug:      menu
        Client:    client_pub

Los datos de prueba crean dos sitios web, cada uno de ellos con una página. La
URL completa de cada página está formada por el valor de la columa `subdomain`
de la tabla `Client` y por el valor de la columna `slug` del objeto `Page`.

    http://pete.sympalbuilder.com/location
    http://citypub.sympalbuilder.com/menu

### El enrutamiento

Todas las páginas de los sitios web creados en *Sympal Builder* se corresponden
de forma directa con un objeto de tipo `Page` del modelo, que define el título
y el contenido que se muestran. Para asociar cada URL con su correspondiente
objeto `Page`, se crea una nueva ruta de objetos de tipo `sfDoctrineRoute` y
que hace uso del campo `slug`. El siguiente código busca automáticamente en la
base de datos un objeto de tipo `Page` cuyo campo `slug` coincida con el que
incluye la URL:

    [yml]
    # apps/frontend/config/routing.yml
    page_show:
      url:        /:slug
      class:      sfDoctrineRoute
      options:
        model:    Page
        type:     object
      params:
        module:   page
        action:   show

La ruta anterior asocia correctamente la página `http://pete.sympalbuilder.com/location`
con su objeto `Page`. Desafortunadamente, la ruta anterior también funciona con
la URL `http://pete.sympalbuilder.com/menu`, por lo que el menú del restaurante
se mostraría en el sitio web de Peter. Por el momento, el enrutamiento no es
consciente de la importancia de los subdominios de los clientes.

Para que la aplicación funcione correctamente, el enrutamiento debe ser más
avanzado. El objeto `Page` se debe buscar tanto por el campo `slug` como por el
campo `client_id`. Este último campo se puede determinar comparando el *host*
(por ejemplo `pete.sympalbuilder.com`) con el valor de la columna `subdomain`
del modelo `Client`. Para ello, se va a mejorar el framework de enrutamiento
creando una clase propia de enrutamiento. No obstante, antes de crear esta clase
será necesario repasar cómo funciona el sistema de enrutamiento.

Cómo funciona el sistema de enrutamiento
----------------------------------------

Las rutas de Symfony son objetos de tipo ~`sfRoute`~ que tienen dos importantes
tareas:

 * Generar URL: si por ejemplo se pasa al método `page_show` un parámetro
   llamado `slug`, debería ser capaz de generar una URL real (por ejemplo,
   `/location`).

 * Procesar las URL entrantes: a partir de la URL de una petición, cada ruta
   debe ser capaz de determinar si la URL cumple los requisitos de la ruta.

La información de cada ruta individual normalmente se configura en el archivo 
`app/mi_aplicacion/config/routing.yml` que se encuentra en el directorio de configuración
de cada aplicación. Si una ruta es *"un objeto de tipo `sfRoute`"*, ¿cómo se transforma
la configuración YAML en objetos `sfRoute`?

###  Gestor de configuración de la cache del enrutamiento

Aunque las rutas se definen en un archivo YAML, cada entrada de ese archivo se
transforma en un objeto durante la petición mediante un tipo especial de clase
llamada *gestor de configuración de la cache*. El resultado es código PHP que representa a todas
y cada una de las rutas de la aplicación. Aunque los detalles de funcionamiento
de este proceso están fuera del alcance de este capítulo, se muestra a continuación
parte de la versión compilada final de la ruta `page_show`. El archivo compilado
se encuentra en `cache/mi_aplicacion/mi_entorno/config/config_routing.yml.php`
y depende de la aplicación y del entorno. A continuación se muestra un pequeño
extracto de la ruta `page_show` completa:

    [php]
    new sfDoctrineRoute('/:slug', array (
      'module' => 'page',
      'action' => 'show',
    ), array (
      'slug' => '[^/\\.]+',
    ), array (
      'model' => 'Page',
      'type' => 'object',
    ));

>**TIP**
>El nombre de la clase de cada ruta se define en la clave `class` del archivo
>`routing.yml`. Si no se especifica una clave `class`, por defecto se considera
>que es una clase de tipo `sfRoute`. Otra clase de ruta común es `sfRequestRoute`
>que permite al programador crear rutas RESTful. El libro 
>*[The symfony Reference Book](http://www.symfony-project.org/reference/1_3/en/10-Routing)*
>incluye la lista completa de clases de ruta y todas sus opciones.

### Asociando una petición con una ruta específica

Una de las tareas principales del framework de enrutamiento consiste en asociar
cada URL entrante con su objeto de ruta correcto. La clase ~`sfPatternRouting`~
es el núcleo central del enrutamiento y se encarga de realizar este proceso. A
pesar de su importancia, los programadores no interactúan casi nunca de forma
directa con `sfPatternRouting`.

Para asociar la ruta correcta, `sfPatternRouting` itera por cada clase `sfRoute`
preguntando si el patrón de la ruta coincide con la URL entrante. Internamente
`sfPatternRouting` ejecuta el método ~`sfRoute::matchesUrl()`~ sobre cada
objeto de ruta. Este método simplemente devuelve `false` si el patrón de la ruta
no coincide con la URL entrante.

Cuando el patrón de la ruta coincide, el método `sfRoute::matchesUrl()` hace
mucho más que devolver `true`. En este caso, la ruta devuelve un array de
parámetros que se incluyen en el objeto de la petición. La URL 
`http://pete.sympalbuilder.com/location` por ejemplo está asociada con la ruta
`page_show`, cuyo método `matchesUrl()` devolvería el siguiente array:

    [php]
    array('slug' => 'location')

Esta información se incluye después en el objeto de la petición, por lo que es
posible acceder a las variables de la ruta (por ejemplo `slug`) desde las acciones
y otros lugares del proyecto.

    [php]
    $this->slug = $request->getParameter('slug');

Como puede que ya hayas adivinado, redefinir el método `sfRoute::matchesUrl()`
es la mejor forma de personalizar las rutas para que hagan cualquier cosa.

Creando una clase de ruta personalizada
---------------------------------------

A continuación se crea una nueva clase de ruta para extender la ruta `page_show`
de forma que tenga en cuenta el subdominio de los objetos `Client`. Para ello,
crea un archivo llamado `acClientObjectRoute.class.php` en el directorio
`lib/routing` del proyecto (debes crear este directorio manualmente):

    [php]
    // lib/routing/acClientObjectRoute.class.php
    class acClientObjectRoute extends sfDoctrineRoute
    {
      public function matchesUrl($url, $context = array())
      {
        if (false === $parameters = parent::matchesUrl($url, $context))
        {
          return false;
        }

        return $parameters;
      }
    }

El único paso que falta es indicar a la ruta `page_show` que utilice esta nueva
clase de ruta. Actualiza el valor de la clave `class` de la ruta en el archivo
`routing.yml`:

    [yml]
    # apps/fo/config/routing.yml
    page_show:
      url:        /:slug
      class:      acClientObjectRoute
      options:
        model:    Page
        type:     object
      params:
        module:   page
        action:   show

Aunque el uso de la clase `acClientObjectRoute` todavía no añade ninguna
funcionalidad, la aplicación ya está preparada para funcionar como se espera.
El método `matchesUrl()` se encarga principalmente de dos tareas.

### Añadiendo la lógica en la ruta personalizada

Para que la ruta propia incluya la funcionalidad requerida, reemplaza los
contenidos del archivo `acClientObjectRoute.class.php` por lo siguiente.

    [php]
    class acClientObjectRoute extends sfDoctrineRoute
    {
      protected $baseHost = '.sympalbuilder.com';

      public function matchesUrl($url, $context = array())
      {
        if (false === $parameters = parent::matchesUrl($url, $context))
        {
          return false;
        }

        // devuelve false si no se encuentra el valor de "baseHost"
        if (strpos($context['host'], $this->baseHost) === false)
        {
          return false;
        }

        $subdomain = str_replace($this->baseHost, '', $context['host']);

        $client = Doctrine_Core::getTable('Client')
          ->findOneBySubdomain($subdomain)
        ;

        if (!$client)
        {
          return false;
        }

        return array_merge(array('client_id' => $client->id), $parameters);
      }
    }

La llamada inicial al método `parent::matchesUrl()` es importante porque ejecuta
el proceso normal de comprobación de las rutas. En este ejemplo, como la URL
`/location` cumple con el patrón de la ruta `page_show`, el método
`parent::matchesUrl()` devolvería un array que contiene el parámetro `slug`.

    [php]
    array('slug' => 'location')

En otras palabras, el trabajo duro del enrutamiento se realiza de forma
automática, por lo que el resto del método se puede dedicar a obtener el objeto
`Client` correcto para ese subdominio.

    [php]
    public function matchesUrl($url, $context = array())
    {
      // ...

      $subdomain = str_replace($this->baseHost, '', $context['host']);

      $client = Doctrine_Core::getTable('Client')
        ->findOneBySubdomain($subdomain)
      ;

      if (!$client)
      {
        return false;
      }

      return array_merge(array('client_id' => $client->id), $parameters);
    }

Realizando una sustitución en la cadena de texto se puede obtener la parte del
subdominio del host y después realizar una consulta en la base de datos para
determinar si algún objeto `Client` tiene este subdominio. Si no existen objetos
`Client` con ese subdominio, se devuelve el valor `false` para indicar que la
petición entrante no cumple con el patrón de esta ruta. Si por el contrario
existe un objeto `Client` con ese subdominio, se añade un nuevo parámetro
llamado `client_id` en el array que se devuelve.

>**TIP**
>El array `$context` que se pasa a `matchesUrl()` incluye mucha información
>útil sobre la petición actual, incluyendo el `host`, un valor booleano que
>indica si la petición es segura (`is_secure`), la URI de la petición (`request_uri`),
>el método de HTTP (`method`) y mucho más.

¿Qué es lo que se ha conseguido con esta ruta personalizada? Básicamente la
clase `acClientObjectRoute` ahora realiza lo siguiente:

 * La `$url` entrante sólo cumplirá el patrón de la ruta si el `host` contiene
   un subdominio que pertenezca a alguno de los objetos `Client`.

 * Si se cumple el patrón de la ruta, se devuelve un parámetro adicional llamado
   `client_id`, obtenido del objeto `Client` y que se añade al resto de
   parámetros de la petición.

### Haciendo uso de la ruta propia

Una vez que `acClientObjectRoute` devuelve el parámetro `client_id` correcto,
la acción puede obtenerlo a través del objeto de la petición. La acción
`page/show` podría utilizar por ejemplo el parámetro `client_id` para encontrar
el objeto `Page` correcto:

    [php]
    public function executeShow(sfWebRequest $request)
    {
      $this->page = Doctrine_Core::getTable('Page')->findOneBySlugAndClientId(
        $request->getParameter('slug'),
        $request->getParameter('client_id')
      );

      $this->forward404Unless($this->page);
    }

>**NOTE**
>El método `findOneBySlugAndClientId()` es un nuevo tipo de
>*[buscador mágico](http://www.doctrine-project.org/upgrade/1_2#Expanded%20Magic%20Finders%20to%20Multiple%20Fields)*
>de Doctrine 1.2 que busca objetos en función de varios campos.

El framework de enrutamiento permite aplicar una solución todavía más elegante.
En primer lugar, añade el siguiente método a la clase `acClientObjectRoute`:

    [php]
    protected function getRealVariables()
    {
      return array_merge(array('client_id'), parent::getRealVariables());
    }

Gracias a este último método, la acción puede obtener el objeto `Page` correcto
directamente desde la ruta. Por tanto, la acción `page/show` se puede reducir
a una única línea de código.

    [php]
    public function executeShow(sfWebRequest $request)
    {
      $this->page = $this->getRoute()->getObject();
    }

Sin necesidad de añadir más código, la instrucción anterior busca un objeto de
tipo `Page` en función de las columnas `slug` *y* `client_id`. Además, al igual
que el resto de rutas de objetos, la acción redirige de forma automática a la
página del error 404 si no se encuentra ningún objeto.

¿Cómo funciona? Las rutas de objetos, como `sfDoctrineRoute`, utilizada por la
clase `acClientObjectRoute`, busca automáticamente el objeto relacionado en
función de las variables de la clave `url` de la ruta. La ruta `page_show` por
ejemplo contiene la variable `:slug` en su `url`, por lo que busca el objeto
`Page` mediante el valor de la columna `slug`.

No obstante, en esta aplicación la ruta `page_show` también debe buscar los
objetos `Page` en función de la columna `client_id`. Para ello, se ha redefinido
el método ~`sfObjectRoute::getRealVariables()`~, que se invoca internamente
para obtener las columnas con las que se realiza la consulta. Añadiendo el campo
`client_id` en este array, `acClientObjectRoute` buscará los objetos haciendo
uso de las columnas `slug` y `client_id`.

>**NOTE**
>Las rutas de objetos ignoran automáticamente cualquier variable que no se
>corresponda a una columna real. Si por ejemplo la URL contiene una variable
>llamada `:page` pero la tabla no contiene una columna `page`, esta variable se
>ignora.

A estas alturas, ya hemos conseguido que la clase de ruta propia realice todo
lo necesario. En las próximas secciones se reutiliza esta nueva ruta para crear
un área de administración específico para cada cliente.

### Generando la ruta correcta

Aún existe un pequeño problema sobre cómo se genera la ruta. Imagina que se
crea un enlace a una página utilizando el siguiente código:

    [php]
    <?php echo link_to('Locations', 'page_show', $page) ?>

-

    URL generada: /location?client_id=1

Como puedes observar, el valor de `client_id` se ha añadido automáticamente al
final de la URL. Esto sucede porque la ruta trata de utilizar todas sus variables
para generar la URL. Como la ruta dispone de un parámetro llamado `slug` y de
otro parámetro llamado `client_id`, hace uso de los dos al generar la ruta.

Para solucionarlo, añade el siguiente método a la clase `acClientObjectRoute`:

    [php]
    protected function doConvertObjectToArray($object)
    {
      $parameters = parent::doConvertObjectToArray($object);

      unset($parameters['client_id']);

      return $parameters;
    }

Cuando se genera una ruta de objetos, se obtiene toda la información necesaria
invocando el método `doConvertObjectToArray()`. Por defecto se devuelve `client_id`
en el array `$parameters`. Al eliminar esa variable, se evita que se incluya
en la URL generada. Recuerda que esto es posible porque la información del
objeto `Client` se guarda en el propio subdominio.

>**TIP**
>Puedes redefinir completamente el proceso de `doConvertObjectToArray()` y
>gestionarlo tu mismo añadiendo un método llamado `toParams()` en la clase del
>modelo. Este método debe devolver un array con los parámetros que quieres que
>se utilicen al generar la ruta.

Colecciones de rutas
--------------------

Para finalizar la aplicación *Sympal Builder*, es preciso crear un área de
administración individual para que cada cliente (`Client`) pueda gestionar sus
páginas (`Pages`). Para ello, se necesitan varias acciones que permitan listar,
crear, actualizar y borrar los objetos de tipo `Page`. Como este tipo de acciones
son muy comunes, Symfony puede generar automáticamente el módulo completo.
Ejecuta la siguiente tarea en la línea de comandos para generar un módulo
llamado `pageAdmin` dentro de la aplicación llamada `backend`:

    $ php symfony doctrine:generate-module backend pageAdmin Page --with-doctrine-route --with-show

La tarea anterior genera un módulo con un archivo de acciones y todas las
plantillas necesarias para realizar cualquier modificación sobre los objetos
`Page`. Aunque se pueden realizar muchas modificaciones sobre estas acciones y
plantillas generadas, es algo que está fuera del alcance de este capítulo.

Aunque la tarea anterior genera un módulo completo, todavía es necesario crear
una ruta para cada acción. La opción `--with-doctrine-route` que se ha pasado
a la tarea hace que todas las acciones generadas funcionen con una ruta de
objeto. De esta forma se reduce el código de cada acción. La siguiente acción
`edit` contiene por ejemplo una única línea:

    [php]
    public function executeEdit(sfWebRequest $request)
    {
      $this->form = new PageForm($this->getRoute()->getObject());
    }

Todas las rutas necesarias son `index`, `new`, `create`, `edit`, `update`,
y `delete`. Normalmente crear estas rutas de tipo
[RESTful](http://es.wikipedia.org/wiki/Representational_State_Transfer)
requeriría añadir lo siguiente en el archivo `routing.yml`.

    [yml]
    pageAdmin:
      url:         /pages
      class:       sfDoctrineRoute
      options:     { model: Page, type: list }
      params:      { module: page, action: index }
      requirements:
        sf_method: [get]
    pageAdmin_new:
      url:        /pages/new
      class:      sfDoctrineRoute
      options:    { model: Page, type: object }
      params:     { module: page, action: new }
      requirements:
        sf_method: [get]
    pageAdmin_create:
      url:        /pages
      class:      sfDoctrineRoute
      options:    { model: Page, type: object }
      params:     { module: page, action: create }
      requirements:
        sf_method: [post]
    pageAdmin_edit:
      url:        /pages/:id/edit
      class:      sfDoctrineRoute
      options:    { model: Page, type: object }
      params:     { module: page, action: edit }
      requirements:
        sf_method: [get]
    pageAdmin_update:
      url:        /pages/:id
      class:      sfDoctrineRoute
      options:    { model: Page, type: object }
      params:     { module: page, action: update }
      requirements:
        sf_method: [put]
    pageAdmin_delete:
      url:        /pages/:id
      class:      sfDoctrineRoute
      options:    { model: Page, type: object }
      params:     { module: page, action: delete }
      requirements:
        sf_method: [delete]
    pageAdmin_show:
      url:        /pages/:id
      class:      sfDoctrineRoute
      options:    { model: Page, type: object }
      params:     { module: page, action: show }
      requirements:
        sf_method: [get]

Para ver estas rutas, ejecuta la tarea `app:routes`, que muestra un resumen de
cada ruta de la aplicación indicada:

    $ php symfony app:routes backend

    >> app       Current routes for application "backend"
    Name             Method Pattern
    pageAdmin        GET    /pages
    pageAdmin_new    GET    /pages/new
    pageAdmin_create POST   /pages
    pageAdmin_edit   GET    /pages/:id/edit
    pageAdmin_update PUT    /pages/:id
    pageAdmin_delete DELETE /pages/:id
    pageAdmin_show   GET    /pages/:id

### Sustituyendo las rutas por una colección de rutas

Afortunadamente Symfony permite añadir todas las rutas relacionadas con el CRUD
de forma mucho más concisa. Reemplaza el contenido del archivo `routing.yml`
por la siguiente ruta.

    [yml]
    pageAdmin:
      class:   sfDoctrineRouteCollection
      options:
        model:        Page
        prefix_path:  /pages
        module:       pageAdmin

Ejecuta de nuevo la tarea `app:routes` para visualizar todas las rutas. Como
puedes ver, todavía se muestran las siete rutas anteriores.

    $ php symfony app:routes backend

    >> app       Current routes for application "backend"
    Name             Method Pattern
    pageAdmin        GET    /pages.:sf_format
    pageAdmin_new    GET    /pages/new.:sf_format
    pageAdmin_create POST   /pages.:sf_format
    pageAdmin_edit   GET    /pages/:id/edit.:sf_format
    pageAdmin_update PUT    /pages/:id.:sf_format
    pageAdmin_delete DELETE /pages/:id.:sf_format
    pageAdmin_show   GET    /pages/:id.:sf_format

Las colecciones de rutas son un tipo especial de objeto que internamente
representan más de una ruta. La ruta ~`sfDoctrineRouteCollection`~ por ejemplo
genera automáticamente las siete rutas habitualmente necesarias para el CRUD.
En realidad, la ruta `sfDoctrineRouteCollection` crea internamente las mismas
siete rutas que se incluyeron antes en el archivo `routing.yml`. Las colecciones
de rutas básicamente existen como atajo para crear grupos comunes de rutas.

Creando una colección de rutas propia
-------------------------------------

En estos momentos, cada cliente (`Client`) puede modificar sus objetos página
(`Page`) mediante un CRUD accedido a través de la URL `/pages`. Desafortunadamente,
cada cliente puede ver y modificar *todos* los objetos `Page`, incluso los que
no le pertenecen. La URL `http://pete.sympalbuilder.com/backend.php/pages` por
ejemplo mostrará una lista de *todas* las páginas creadas mediante el archivo
de datos - la página `location` de la tienda de animales de Pete y la página
`menu` del City Pub.

Para solucionar este problema, se va a reutilizar la ruta propia `acClientObjectRoute`
que se creo para el frontend. La clase `sfDoctrineRouteCollection` genera un
grupo de objetos `sfDoctrineRoute`. No obstante, en esta aplicación es necesario
generar un grupo de objetos `acClientObjectRoute`.

Para ello, se va a utilizar una colección de rutas propia. Crea un nuevo archivo
llamado `acClientObjectRouteCollection.class.php` dentro del directorio
`lib/routing`. El contenido del archivo es realmente sencillo:

    [php]
    // lib/routing/acClientObjectRouteCollection.class.php
    class acClientObjectRouteCollection extends sfObjectRouteCollection
    {
      protected
        $routeClass = 'acClientObjectRoute';
    }

La propiedad `$routeClass` define la clase que se utiliza al crear cada una de
las rutas de la colección. Por tanto, ahora cada ruta individual será de tipo
`acClientObjectRoute`. Si se accede ahora a la URL
`http://pete.sympalbuilder.com/backend.php/pages` solamente se muestra una página:
la página `location` de la tienda de animales de Pete. Gracias a la clase de
ruta propia, la acción `index` sólo devuelve los objetos `Page` relacionados
con el `Client` correcto en función del subdominio de la petición. Como se
acaba de demostrar, es posible crear con unas pocas líneas de código, un módulo
completo del backend que pueden utilizar varios clientes diferentes.

### Creando nuevas páginas

Actualmente las páginas de creación y modificación de objetos `Page` muestran
una lista desplegable con todos los `Client`. En lugar de permitir que los
usuarios puedan elegir el `Client`, que además podría comprometer la seguridad,
se va a fijar el `Client` automáticamente en función del subdominio de la petición.

En primer lugar, actualiza el objeto `PageForm` en `lib/form/PageForm.class.php`.

    [php]
    public function configure()
    {
      $this->useFields(array(
        'title',
        'content',
      ));
    }

La lista desplegable ya no se muestra en los formularios de tipo `Page`. El
problema es que al quitar la lista de clientes, cuando se crean objetos `Page`
ya no se incluye el valor `client_id`. La solución consiste en añadir a mano
el objeto `Client` relacionado en las acciones `new` y `create`.

    [php]
    public function executeNew(sfWebRequest $request)
    {
      $page = new Page();
      $page->Client = $this->getRoute()->getClient();
      $this->form = new PageForm($page);
    }

El código anterior utiliza un método llamado `getClient()` que todavía no existe
en la clase `acClientObjectRoute`. A continuación se muestran las modificaciones
necesarias para añadirlo:

    [php]
    // lib/routing/acClientObjectRoute.class.php
    class acClientObjectRoute extends sfDoctrineRoute
    {
      // ...

      protected $client = null;

      public function matchesUrl($url, $context = array())
      {
        // ...

        $this->client = $client;

        return array_merge(array('client_id' => $client->id), $parameters);
      }

      public function getClient()
      {
        return $this->client;
      }
    }

Para hacer que el objeto `Client` está disponible a través de la ruta, se añade
una propiedad `$client` en la clase y se establece su valor en el método
`matchesUrl()`. Ahora los nuevos objetos `Page` ya incluirán correctamente el
valor de la columna `client_id` en función del subdominio de la petición.

Personalizando una colección de rutas de objeto
-----------------------------------------------

Haciendo uso del framework de enrutamiento, hemos solucionado fácilmente los
retos planteados al crear una aplicación como *Sympal Builder*. A medida que la
aplicación crezca, podremos reutilizar las rutas propias en otros módulos del
área de administración (para que los clientes puedan por ejemplo gestionar sus
galerías de fotos).

Otra razón para crear una colección de rutas propia es la posibilidad de añadir
rutas adicionales usadas habitualmente. Imagina que por ejemplo un proyecto
utiliza muchos modelos, cada uno de los cuales dispone de una columna `is_active`.
El área de administración debe incluir una forma sencilla de activar o desactivar
el valor `is_active` de un objeto. Para ello, en primer lugar modifica la clase
`acClientObjectRouteCollection` para incluir una nueva ruta a la colección:

    [php]
    // lib/routing/acClientObjectRouteCollection.class.php
    protected function generateRoutes()
    {
      parent::generateRoutes();

      if (isset($this->options['with_is_active']) && $this->options['with_is_active'])
      {
        $routeName = $this->options['name'].'_toggleActive';

        $this->routes[$routeName] = $this->getRouteForToggleActive();
      }
    }

El método ~`sfObjectRouteCollection::generateRoutes()`~ se invoca al instanciar
el objeto de la colección y se encarga de crear todas las rutas necesarias y de
incluirlas en la propiedad `$routes` de la clase. En este caso, derivamos la
creación de la ruta a un nuevo método protegido llamado `getRouteForToggleActive()`:

    [php]
    protected function getRouteForToggleActive()
    {
      $url = sprintf(
        '%s/:%s/toggleActive.:sf_format',
        $this->options['prefix_path'],
        $this->options['column']
      );

      $params = array(
        'module' => $this->options['module'],
        'action' => 'toggleActive',
        'sf_format' => 'html'
      );

      $requirements = array('sf_method' => 'put');

      $options = array(
        'model' => $this->options['model'],
        'type' => 'object',
        'method' => $this->options['model_methods']['object']
      );

      return new $this->routeClass(
        $url,
        $params,
        $requirements,
        $options
      );
    }

El único paso que falta es configurar la colección de rutas en el archivo
`routing.yml`. Como has podido observar, `generateRoutes()` busca una opción
llamada `with_is_active` antes de añadir la nueva ruta. Incluir esta comprobación
permite un mayor control en caso de que se quiera reutilizar la colección
`acClientObjectRouteCollection` más adelante en algún lugar que no necesite
la ruta `toggleActive`:

    [yml]
    # apps/frontend/config/routing.yml
    pageAdmin:
      class:   acClientObjectRouteCollection
      options:
        model:          Page
        prefix_path:    /pages
        module:         pageAdmin
        with_is_active: true

Ejecuta la tarea `app:routes` y verifica que existe una nueva ruta llamada
`toggleActive`. Lo único que falta es crear la acción encargada de realizar
todo el trabajo. Como es posible que se reutilice en varios módulos esta colección
de rutas y su acción asociada, crea un nuevo archivo `backendActions.class.php`
en el directorio `apps/backend/lib/action` (debes crear a mano este directorio):

    [php]
    # apps/backend/lib/action/backendActions.class.php
    class backendActions extends sfActions
    {
      public function executeToggleActive(sfWebRequest $request)
      {
        $obj = $this->getRoute()->getObject();

        $obj->is_active = !$obj->is_active;

        $obj->save();

        $this->redirect($this->getModuleName().'/index');
      }
    }

Por último, modifica la clase base de `pageAdminActions` para que herede de
esta nueva clase `backendActions`.

    [php]
    class pageAdminActions extends backendActions
    {
      // ...
    }

¿Qué es lo que hemos conseguido? Añadir una ruta a la colección de rutas y una
acción asociada permite que cualquier módulo pueda incluir automáticamente esta
funcionalidad simplemente utilizando la colección `acClientObjectRouteCollection`
y extendiendo la clase `backendActions`. De esta forma, es posible reutilizar
las funcionalidades comunes entre varios módulos diferentes.

Opciones de las colecciones de rutas
------------------------------------

Las colecciones de rutas de objetos incluyen varias opciones para personalizar
completamente su funcionamiento. En muchos casos estas opciones son suficientes
para configurar la colección sin necesidad de crear una nueva colección propia.
El libro *[The symfony Reference Book](http://www.symfony-project.org/reference/1_3/en/10-Routing#chapter_10_sfobjectroutecollection)*
contiene la lista completa de todas las opciones de las colecciones de rutas.

### Rutas de la acción

Cada colección de rutas admite tres opciones diferentes que determinan exactamente
las rutas que se generan en la colección. Sin entrar en muchos detalles, la
siguiente colección generaría las siete rutas por defecto junto con una
colección de rutas adicional y una ruta de objeto:

    [yml]
    pageAdmin:
      class:   acClientObjectRouteCollection
      options:
        # ...
        actions:      [list, new, create, edit, update, delete, show]
        collection_actions:
          indexAlt:   [get]
        object_actions:
          toggle:     [put]

### Columna

Por defecto todas las URL generadas emplean la clave primaria del modelo, que
también se utiliza para buscar los objetos. Obviamente este comportamiento se
puede modificar con facilidad. El siguiente ejemplo utiliza la columna `slug`
en vez de la clave primaria:

    [yml]
    pageAdmin:
      class:   acClientObjectRouteCollection
      options:
        # ...
        column: slug

### Métodos del modelo

Por defecto la ruta obtiene todos los objetos relacionados con la colección
y utiliza la columna especificada en `column` para las rutas de los objetos.
Si necesitas redefinir ese comportamiento, añade la opción `model_methods` en
la ruta. En este ejemplo se deben añadir los métodos `fetchAll()` y `findForRoute()`
a la clase `PageTable`. A los dos métodos se les pasa como argumento un array
con los parámetros de la petición:

    [yml]
    pageAdmin:
      class:   acClientObjectRouteCollection
      options:
        # ...
        model_methods:
          list:       fetchAll
          object:     findForRoute

### Parámetros por defecto

Por último, imagina que todas las rutas de la colección necesitan un determinado
parámetro en la petición. Esto se puede conseguir fácilmente con la opción
`default_params`:

    [yml]
    pageAdmin:
      class:   acClientObjectRouteCollection
      options:
        # ...
        default_params:
          foo:   bar

Conclusión
----------

Las tareas habituales del framework de enrutamiento - generar URL y comprobar
que cumplen el patrón de las rutas - ha evolucionado hacia un sistema completamente
configurable capaz de encargarse de los requerimientos más complejos para las
URL de un proyecto. Además, la estructura de las URL se abstrae de la lógica de
negocio de la aplicación y se traslada al enrutamiento, que es su lugar natural
y donde se controlan todos los objetos de las rutas. El resultado final es un
mayor control, más flexibilidad y código más manejable.
