Продвинутая маршрутизация
=========================

*by Ryan Weaver*

Система маршрутизации, если смотреть изнутри, представляет из себя карту,
которая связывает каждый URL с некоторой локацией внутри проекта на symfony и наоборот.
Он может с легкостью создавать красивые URL, оставаясь независимым от логики приложения.
Учитывая прогресс, которого удалось достигнуть в предыдущих версиях symfony, маршрутизатор
делает следующий шаг вперед.

Эта глава иллюстрирует процесс создания простого веб-приложения, в котором каждый
клиент использует отдельный субдомен (например client1.mydomain.com и client2.mydomain.com).
Это будет совсем несложно, когда мы расширим возможности маршрутизатора.

>**NOTE**
>Для примера из этой главы мы будем использовать Doctrine в качестве ORM

Настройка проекта: CMS для многих клиентов
------------------------------------------

Для начала представим некую вымышленную компанию, Sympal Builder, которая желает создать CMS,
с помощью которой ее клиенты смогут создавать вебсайты на поддоменах сайта sympalbuilder.com.
Т.е. клиент XXX может просмотреть свой сайт по адресу `xxx.sympalbuilder.com`
и использовать админку по адресу `xxx.sympalbuilder.com/backend.php`.

>**NOTE**
>Наименование `Sympal` мы позаимствовали у одноименного проекта Jonathan Wage
>[Sympal](http://www.sympalphp.org/), CMF, созданная на symfony.

This project has two basic requirements:
Проект имеет два основных требования:

  * Пользователь должен иметь возможность создавать страницы и указывать для них
    title, content и URL.

  * Приложение должно быть создано внутри одного проекта symfony, который контролирует
    фронтэнд и бэкэнд всех клентских сайтов, определяя клиента и загружая корректные
    данные для поддомена.

>**NOTE**
>Для создания нашего приложения, вебсервер должен быть настроен направлять все
>запросы на *.sympalbuilder.com на тот же document root – web директорию нашего
>проекта на symfony.


### Схема и данные

База данных для проекта будет состоять из объектов `Client` и `Page`. Каждый `Client`
представлен на своем поддомене и может содержать много объектов `Page`.

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
>Хотя индексы для таблиц не являются необходимыми, создать их все же рекомендуется,
>поскольку приложение будет часто запрашивать данные из них.

Для того чтобы наш проект заработал, необходимо разместить следующие тестовые данные
в файле `data/fixtures/fixtures.yml`:

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

Это тестовые данные для двух вебсайтов, каждый из них имеет по одной странице.
Полный URL каждой страницы определяется колонкой `subdomain` в таблице `Client` и
колонкой `slug` в таблице `Page`.

    http://pete.sympalbuilder.com/location
    http://citypub.sympalbuilder.com/menu

### Маршрутизация

Each page of a Sympal Builder website corresponds directly to a `Page` model
object, which defines the title and content of its output. To link each URL
specifically to a `Page` object, create an object route of type
`sfDoctrineRoute` that uses the `slug` field. The following code will
automatically look for a `Page` object in the database with a `slug` field
that matches the url:

Каждая страница вебсайта Sympal Builder напрямую соответствует объекту `Page`,
который определяет ее заголовок и содержание. Для того чтобы связать каждый URL с его страницей,
создадим маршрут типа `sfDoctrineRoute`, который использует поле `slug`:

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

Этот маршрут связывает страницу `http://pete.sympalbuilder.com/location` с соответствующим объектом `Page`.
К несчастью, этот маршрут *также* соответствует URL’у `http://pete.sympalbuilder.com/menu`,
т.е. меню ресторана (это другая страница, которая относится к клиенту citypub)
также будет отбражаться и на сайте Пита!  На этом шаге маршрут не знает о важности
клиентских поддоменов.

Для того чтобы проект заработал, маршрут должен стать более умным. Он должен находить
корректную страницу основываясь на полях `slug` *и* `client_id` одновременно, что позволит
определять соответствующий хост (например `pete.sympalbuilder.com`) по колонке `subdomain`
в модели `Client`. Для того чтобы достичь этого, мы воспользуемся каркасом маршрутизации
и создадим свой собственный класс маршрута.

Но, для начала, нам необходимо немного узнать о том, как работает система маршрутизации.

Как работает система маршрутизации
----------------------------------

A "route", in symfony, is an object of type ~`sfRoute`~ that has two important
jobs:

 * Generate a URL: For example, if you pass the `page_show` method a `slug`
   parameter, it should be able to generate a real URL (e.g. `/location`).

 * Match an incoming URL: Given the URL from an incoming request, each route
   must be able to determine if the URL "matches" the requirements of the
   route.

The information for individual routes is most commonly setup inside each
application's config directory located at `app/yourappname/config/routing.yml`.
Recall that each route is *"an object of type `sfRoute`"*. So how do these
simple YAML entries become `sfRoute` objects?

### Routing Cache Config Handler

Despite the fact most routes are defined in a YAML file, each entry in this
file is transformed into an actual object at request time via a special type
of class called a cache config handler. The final result is PHP code representing
each and every route in the application. While the specifics of this process
are beyond the scope of this chapter, let's peak at the final, compiled version
of the `page_show` route. The compiled file is located at
`cache/yourappname/envname/config/config_routing.yml.php` for the specific
application and environment. Below is a shortened version of what the
`page_show` route looks like:

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
>The class name of each route is defined by the `class` key inside the `routing.yml`
>file. If no `class` key is specified, the route will default to be a class
>of `sfRoute`. Another common route class is `sfRequestRoute` which allows
>the developer to create RESTful routes. A full list of route classes and
>available options is available via
>[The symfony Reference Book](http://www.symfony-project.org/reference/1_3/en/10-Routing)

### Matching an Incoming Request to a Specific Route

One of the main jobs of the routing framework is to match each incoming URL
with the correct route object. The ~`sfPatternRouting`~ class represents the
core routing engine and is tasked with this exact task. Despite its importance,
a developer will rarely interact directly with `sfPatternRouting`.

To match the correct route, `sfPatternRouting` iterates through each `sfRoute`
and "asks" the route if it matches the incoming url. Internally, this means
that `sfPatternRouting` calls the ~`sfRoute::matchesUrl()`~ method on each
route object. This method simply returns `false` if the route doesn't match the
incoming url.

However, if the route *does* match the incoming URL, `sfRoute::matchesUrl()`
does more than simply return `true`. Instead, the route returns an array
of parameters that are merged into the request object. For example, the url
`http://pete.sympalbuilder.com/location` matches the `page_show` route,
whose `matchesUrl()` method would return the following array:

    [php]
    array('slug' => 'location')

This information is then merged into the request object, which is why it's
possible to access route variables (e.g. `slug`) from the actions file and
other places.

    [php]
    $this->slug = $request->getParameter('slug');

As you may have guessed, overriding the `sfRoute::matchesUrl()` method is
a great way to extend and customize a route to do almost anything.

Creating a Custom Route Class
-----------------------------

In order to extend the `page_show` route to match based on the subdomain of
the `Client` objects, we will create a new custom route class. Create a file
named `acClientObjectRoute.class.php` and place it in the project's `lib/routing`
directory (you'll need to create this directory):

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

The only other step is to instruct the `page_show` route to use this route
class. In `routing.yml`, update the `class` key on the route:

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

So far, `acClientObjectRoute` adds no additional functionality, but all
the pieces are in place. The `matchesUrl()` method has two specific jobs.

### Adding Logic to the Custom Route

To give the custom route the needed functionality, replace the contents of the
`acClientObjectRoute.class.php` file with the following.

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

        // return false if the baseHost isn't found
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

The initial call to `parent::matchesUrl()` is important as it runs through the
normal route-matching process. In this example, since the URL `/location` matches
the `page_show` route, `parent::matchesUrl()` would return an array containing
the matched `slug` parameter.

    [php]
    array('slug' => 'location')

In other words, all the hard-work of route matching is done for us, which allows
the remainder of the method to focus on matching based on the correct `Client` subdomain.

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

By performing a simple string replace, we can isolate the subdomain portion
of the host and then query the database to see if any of the `Client` objects
have this subdomain. If no `Client` objects match the subdomain, then we
return `false` indicating that the incoming request does not match the route.
However, if there is a `Client` object with the current subdomain, we merge an
extra parameter, `client_id` into the returned array.

>**TIP**
>The `$context` array passed to `matchesUrl()` is prepopulated with lot's of
>useful information about the current request, including the `host`, an
>`is_secure` boolean, the `request_uri`, the HTTP `method` and more.

But, what has the custom route really accomplished? The `acClientObjectRoute`
class now does the following:

 * The incoming `$url` will only match if the `host` contains a subdomain
   belonging to one of the `Client` objects.

 * If the route matches, an additional `client_id` parameter for the matched
   `Client` object is returned and ultimately merged into the request parameters.

### Leveraging the Custom Route

Now that the correct `client_id` parameter is being returned by `acClientObjectRoute`,
we have access to it via the request object. For example, the `page/show` action
could use the `client_id` to find the correct `Page` object:

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
>The `findOneBySlugAndClientId()` method is a type of
>[magic finder](http://www.doctrine-project.org/upgrade/1_2#Expanded%20Magic%20Finders%20to%20Multiple%20Fields)
>new in Doctrine 1.2 that queries for objects based on multiple fields.

As nice as this is, the routing framework allows for an even more elegant solution.
First, add the following method to the `acClientObjectRoute` class:

    [php]
    protected function getRealVariables()
    {
      return array_merge(array('client_id'), parent::getRealVariables());
    }

With this final piece, the action can rely completely on the route to return
the correct `Page` object. The `page/show` action can be reduced to a single
line.

    [php]
    public function executeShow(sfWebRequest $request)
    {
      $this->page = $this->getRoute()->getObject();
    }

Without any additional work, the above code will query for a `Page` object
based on both the `slug` *and* `client_id` columns. Additionally, like all
object routes, the action will automatically forward to a 404 page if no
corresponding object is found.

But how does this work? Object routes, like `sfDoctrineRoute`, which the
`acClientObjectRoute` class extends, automatically query for the related
object based on the variables in the `url` key of the route. For example, the
`page_show` route, which contains the `:slug` variable in its `url`, queries
for the `Page` object via the `slug` column.

In this application, however, the `page_show` route must also query for `Page`
objects based on the `client_id` column. To do this, we've overridden the
~`sfObjectRoute::getRealVariables()`~, which is called internally to determine
which columns to use for the object query. By adding the `client_id` field
to this array, the `acClientObjectRoute` will query based on both the `slug`
and `client_id` columns.

>**NOTE**
>Objects routes automatically ignore any variables that don't correspond
>to a real column. For example, if the URL key contains a `:page` variable,
>but no `page` column exists on the relevant table, the variable will be ignored.

At this point, the custom route class accomplishes everything needed with
very little effort. In the next sections, we'll reuse the new route to create
a client-specific admin area.

### Generating the Correct Route

One small problem remains with how the route is generated. Suppose create
a link to a page with the following code:

    [php]
    <?php echo link_to('Locations', 'page_show', $page) ?>

-

    Generated url: /location?client_id=1

As you can see, the `client_id` was automatically appended to the url. This
occurs because the route tries to use all its available variables to generate
the url. Since the route is aware of both a `slug` parameter and a `client_id`
parameter, it uses both when generating the route.

To fix this, add the following method to the `acClientObjectRoute` class:

    [php]
    protected function doConvertObjectToArray($object)
    {
      $parameters = parent::doConvertObjectToArray($object);

      unset($parameters['client_id']);

      return $parameters;
    }

When an object route is generated, it attempts to retrieve all of the necessary
information by calling `doConvertObjectToArray()`. By default, the `client_id`
is returned in the `$parameters` array. By unsetting it, however, we prevent
it from being included in the generated url. Remember that we have this luxury
since the `Client` information is held in the subdomain itself.

>**TIP**
>You can override the `doConvertObjectToArray()` process entirely and handle
>it yourself by adding a `toParams()` method to the model class. This method
>should return an array of the parameters that you want to be used during
>route generation.

Route Collections
-----------------

To finish the Sympal Builder application, we need to create an admin area
where each individual `Client` can manage its `Pages`. To do this, we will need
a set of actions that allows us to list, create, update, and delete the `Page` objects.
As these types of modules are fairly common, symfony can generate the module
automatically. Execute the following task from the command line to generate
a `pageAdmin` module inside an application called `backend`:

    $ php symfony doctrine:generate-module backend pageAdmin Page --with-doctrine-route --with-show

The above task generates a module with an actions file and related templates
capable of making all the modifications necessary to any `Page` object.
Lot's of customizations could be made to this generated CRUD, but that
falls outside the scope of this chapter.

While the above task prepares the module for us, we still need to create a
route for each action. By passing the `--with-doctrine-route` option to the
task, each action was generated to work with an object route. This decreases
the amount of code in each action. For example, the `edit` action contains
one simple line:

    [php]
    public function executeEdit(sfWebRequest $request)
    {
      $this->form = new PageForm($this->getRoute()->getObject());
    }

In total, we need routes for the `index`, `new`, `create`, `edit`, `update`,
and `delete` actions. Normally, creating these routes in a
[RESTful](http://en.wikipedia.org/wiki/Representational_State_Transfer)
manner would require significant setup in `routing.yml`.

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

To visualize these routes, use the `app:routes` task, which displays a summary
of every route for a specific application:

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

### Replacing the Routes with a Route Collection

Fortunately, symfony provides a much easier way to specify all of the routes
that belong to a traditional CRUD. Replace the entire content of `routing.yml` with one simple route.

    [yml]
    pageAdmin:
      class:   sfDoctrineRouteCollection
      options:
        model:        Page
        prefix_path:  /pages
        module:       pageAdmin

Once again, execute the `app:routes` task to visualize all of the routes.
As you'll see, all seven of the previous routes still exist.

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

Route collections are a special type of route object that internally represent
more than one route. The ~`sfDoctrineRouteCollection`~ route, for example
automatically generates the seven most common routes needed for a CRUD. Behind
the scenes, `sfDoctrineRouteCollection` is doing nothing more than creating
the same seven routes previously specified in `routing.yml`. Route collections
basically exist as a shortcut to creating a common group of routes.

Creating a Custom Route Collection
----------------------------------

At this point, each `Client` will be able to modify its `Page` objects inside
a functioning crud via the URL `/pages`. Unfortunately, each `Client` can
currently see and modify *all* `Page` objects - those both belonging and
not belonging to the `Client`. For example,
`http://pete.sympalbuilder.com/backend.php/pages` will render a list of *both*
pages in the fixtures - the `location` page from Pete's Pet Shop and the `menu`
page from City Pub.

To fix this, we'll reuse the `acClientObjectRoute` that was created for the
frontend. The `sfDoctrineRouteCollection` class generates a group of `sfDoctrineRoute` objects. In this application, we'll need to generate a group of `acClientObjectRoute`
objects instead.

To accomplish this, we'll need to use a custom route collection class. Create
a new file named `acClientObjectRouteCollection.class.php` and place it in
the `lib/routing` directory. Its content is incredibly straightforward:

    [php]
    // lib/routing/acClientObjectRouteCollection.class.php
    class acClientObjectRouteCollection extends sfObjectRouteCollection
    {
      protected
        $routeClass = 'acClientObjectRoute';
    }

The `$routeClass` property defines the class that will be used when creating
each underlying route. Now that each underlying routing is an `acClientObjectRoute`
route, the job is actually done. For example,
`http://pete.sympalbuilder.com/backend.php/pages` will now list only *one*
page: the `location` page from Pete's Pet Shop. Thanks to the custom route
class, the index action returns only `Page` objects related to the correct
`Client`, based on the subdomain of the request. With just a few lines of
code, we've created an entire backend module that can be safely used by
multiple clients.

### Missing Piece: Creating New Pages

Currently, a `Client` select box displays on the backend when creating or editing
`Page` objects. Instead of allowing users to choose the `Client` (which would be
a security risk), let's set the `Client` automatically based on the current subdomain of
the request.

First, update the `PageForm` object in `lib/form/PageForm.class.php`.

    [php]
    public function configure()
    {
      $this->useFields(array(
        'title',
        'content',
      ));
    }

The select box is now missing from the `Page` forms as needed. However, when new
`Page` objects are created, the `client_id` is never set. To fix this, manually
set the related `Client` in both the `new` and `create` actions.

    [php]
    public function executeNew(sfWebRequest $request)
    {
      $page = new Page();
      $page->Client = $this->getRoute()->getClient();
      $this->form = new PageForm($page);
    }

This introduces a new function, `getClient()` which doesn't currently exist
in the `acClientObjectRoute` class. Let's add it to the class by making a few
simple modifications:

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

By adding a `$client` class property and setting it in the `matchesUrl()` function,
we can easily make the `Client` object available via the route. The `client_id`
column of new `Page` objects will now be automatically and correctly set based
on the subdomain of the current host.

Customizing an Object Route Collection
--------------------------------------

By using the routing framework, we have now easily solved the problems posed by
creating the Sympal Builder application. As the application grows, the developer
will be able to reuse the custom routes for other modules in the backend area
(e.g., so each `Client` can manage their photo galleries).

Another common reason to create a custom route collection is to add additional,
commonly used routes. For example, suppose a project employs many models, each
with an `is_active` column. In the admin area, there needs to be an easy way
to toggle the `is_active` value for any particular object. First, modify
`acClientObjectRouteCollection` and instruct it to add a new route to the collection:

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

The ~`sfObjectRouteCollection::generateRoutes()`~ method is called when the
collection object is instantiated and is responsible for creating all the
needed routes and adding them to the `$routes` class property array. In this
case, we offload the actual creation of the route to a new protected method
called `getRouteForToggleActive()`:

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

The only remaining step is to setup the route collection in `routing.yml`.
Notice that `generateRoutes()` looks for an option named `with_is_active`
before adding the new route. Adding this logic gives us more control in case
we want to use the `acClientObjectRouteCollection` somewhere later that doesn't
need the `toggleActive` route:

    [yml]
    # apps/frontend/config/routing.yml
    pageAdmin:
      class:   acClientObjectRouteCollection
      options:
        model:          Page
        prefix_path:    /pages
        module:         pageAdmin
        with_is_active: true

Check the `app:routes` task and verify that the new `toggleActive` route
is present. The only remaining piece is to create the action that will do
that actual work. Since you may want to use this route collection and
corresponding action across several modules, create a new
`backendActions.class.php` file in the `apps/backend/lib/action`
directory (you'll need to create this directory):

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

Finally, change the base class of the `pageAdminActions` class to extend this
new `backendActions` class.

    [php]
    class pageAdminActions extends backendActions
    {
      // ...
    }

What have we just accomplished? By adding a route to the route collection and
an associated action in a base actions file, any new module can automatically
use this functionality simply by using the `acClientObjectRouteCollection` and
extending the `backendActions` class. In this way, common functionality can
be easily shared across many modules.

Options on a Route Collection
-----------------------------

Object route collections contain a series of options that allow it to be highly
customized. In many cases, a developer can use these options to configure
the collection without needing to create a new custom route collection class.
A detailed list of route collection options is available via
[The symfony Reference Book](http://www.symfony-project.org/reference/1_3/en/10-Routing#chapter_10_sfobjectroutecollection).

### Action Routes

Each object route collection accepts three different options which determine
the exact routes generated in the collection. Without going into great detail,
the following collection would generate all seven of the default routes along
with an additional collection route and object route:

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

### Column

By default, the primary key of the model is used in all of the generated urls
and is used to query for the objects. This, of course, can easily be changed.
For example, the following code would use the `slug` column instead of the
primary key:

    [yml]
    pageAdmin:
      class:   acClientObjectRouteCollection
      options:
        # ...
        column: slug

### Model Methods

By default, the route retrieves all related objects for a collection route
and queries on the specified `column` for object routes. If you
need to override this, add the `model_methods` option to the route. In this
example, the `fetchAll()` and `findForRoute()` methods would need to be added
to the `PageTable` class. Both methods will receive an array of request
parameters as an argument:

    [yml]
    pageAdmin:
      class:   acClientObjectRouteCollection
      options:
        # ...
        model_methods:
          list:       fetchAll
          object:     findForRoute

### Default Parameters

Finally, suppose that you need to make a specific request parameter available
in the request for each route in the collection. This is easily done with
the `default_params` option:

    [yml]
    pageAdmin:
      class:   acClientObjectRouteCollection
      options:
        # ...
        default_params:
          foo:   bar

Final Thoughts
--------------

The traditional job of the routing framework - to match and generate urls -
has evolved into a fully customizable system capable of catering to the most
complex URL requirements of a project. By taking control of the route objects,
the special URL structure can be abstracted away from the business logic and
kept entirely inside the route where it belongs. The end result is more control,
more flexibility and more manageable code.
