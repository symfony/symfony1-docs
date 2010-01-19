Desarrollando aplicaciones Facebook
===================================

*por Fabrice Bernhard*

Facebook se ha convertido en la red social *estándar* de Internet gracias a sus
más de 350 millones de usuarios. Una de sus características más interesantes es
la *"Plataforma Facebook"*, una API que permite a los programadores crear
aplicaciones que se ejecutan dentro del sitio web de Facebook, así como conectar
otros sitios web externos con la autenticación de Facebook.

Como el frontal de Facebook se ha desarrollado con PHP, no es de extrañar que la
librería cliente oficial de esta API sea una librería PHP. Este hecho hace que
Symfony sea una buena solución para crear aplicaciones Facebook rápidamente o
para incorporar el *Facebook Connect* a los sitios web. De hecho, crear una
aplicación Facebook con Symfony es uno de los mejores ejemplos de cómo aprovechar
las funcionalidades de este framework para ahorrar mucho tiempo pero manteniendo
un alto nivel de calidad en el trabajo desarrollado.

A lo largo de este capítulo se va a explicar brevemente qué es la API de Facebook
y cómo se puede utilizar, se va a detallar cómo aprovechar lo mejor de Symfony
para crear aplicaciones Facebook, cómo utilizar el trabajo de la comunidad de
usuarios y el plugin `sfFacebookConnectPlugin`, se va a desarrollar una aplicación
sencilla de ejemplo y por último se van a explicar algunos trucos y consejos
para resolver la mayoría de problemas comunes.

Desarrollando aplicaciones Facebook
-----------------------------------

Aunque la API es básicamente la misma, existen dos casos muy diferentes al
desarrollar aplicaciones Facebook: 1) crear una aplicación que se va a ejecutar
dentro del sitio web de Facebook; 2) añadir la funcionalidad Facebook Connect
en un sitio web externo.

### Aplicaciones Facebook

Las aplicaciones Facebook son aplicaciones web que se ejecutan dentro de Facebook.
Su principal ventaja es que están disponibles dentro de una red social utilizada
por más de 300 millones de usuarios, por lo que cualquier aplicación viral puede
extenderse a una velocidad increíble. La aplicación *Farmville* es el mejor
ejemplo de todo ello, ya que ha alcanzado en pocos meses más de 60 millones de
usuarios activos y más de dos millones de fans. Para poner los datos anteriores
en perspectiva, es como si toda la población de un país como Francia entrara
cada mes a trabajar en su granja virtual. Las aplicaciones Facebook interactúan
con el sitio web de Facebook y sus características sociales de muchas formas
diferentes. A continuación se explican brevemente los diferentes lugares en los
que puede aparecer una aplicación Facebook:

#### El *canvas*

El *canvas* normalmente es la parte principal de tu aplicación. Básicamente se
trata de un pequeño sitio web embebido dentro de Facebook.

#### La pestaña del perfil

La aplicación también se puede mostrar dentro de una pestaña del perfil de un
usuario o de una página de fans. Las principales limitaciones son:

 * solamente una página. No se pueden crear dentro de la pestaña enlaces a
   sub-páginas.

 * durante la carga no se puede utilizar ni flash ni JavaScript. Para incluir
   características dinámicas, la aplicación debe esperar a que el usuario
   interactúe con la página pinchando algún enlace o botón.

#### La casilla del perfil

Se trata de un remanente de la versión anterior de Facebook, por lo que ya no
lo utiliza nadie. Se emplea para mostrar información en una casilla que se encuentra
dentro de la pestaña *"Casillas"* del perfil.

#### La pestaña de información

La pestaña *Información* de la página del perfil puede mostrar cierta información
estática relacionada con la aplicación. La información se muestra debajo de
la edad, dirección y curriculum del usuario.

#### Publicando avisos y noticias

La aplicación también puede publicar noticias, enlaces, fotos y vídeos dentro
de la sección de *Últimas noticias*. Además, puede escribir en el muro de un
amigo del usuario y también puede modificar la información de estado del usuario.

#### La página de información

Se trata de la *página del perfil* de la aplicación, que Facebook crea de forma
automática. El creador de la aplicación puede utilizar esta página para interactuar
con sus usuarios de la forma habitual en Facebook. Normalmente es algo más
relacionado con el equipo de marketing que con el equipo de desarrollo.

### Facebook Connect

Facebook Connect permite que cualquier sitio web pueda ofrecer a sus usuarios
algunas de las mejores funcionalidades de Facebook. Los sitios que hacen uso
de esta característica se reconocen fácilmente porque muestran un gran botón
azul llamado *"Connect with Facebook"*. Algunos de los sitios más grandes como
digg.com, cnet.com, netvibes.com o yelp.com ya incluyen este botón. A continuación
se presentan las cuatro razones principales por las que un sitio web debería
incluir *Facebook Connect*.

#### Sistema de autenticación de 1-click

Al igual que OpenID, el servicio *Facebook Connect* ofrece a los sitios web la
oportunidad de incluir el login automático de los usuarios mediante su sesión
de Facebook. Una vez que el usuario aprueba la *conexión* entre el sitio web y
Facebook, el sitio web tiene acceso automáticamente a la sesión de Facebook,
evitando el proceso de registro en este sitio y la creación de una nueva
contraseña que recordar.

#### Obtener más información sobre el usuario

Otra de las principales ventajas de *Facebook Connect* es la cantidad de información
que proporciona. Normalmente los usuarios añaden poca información personal cuando
se registran en los sitios web, pero *Facebook Connect* proporciona información
tan interesante como el nombre, edad, sexo, localización, fotografía, etc.
Las condiciones de uso de *Facebook Connect* recuerdan explícitamente que los
sitios web no pueden guardar esa información sin el consentimiento expreso
del usuario, pero la información si que se puede utilizar para llenar los datos
de un formulario y pedir que el usuario los confirme con un solo *click*. Además,
el sitio web puede utilizar información pública como el nombre y la foto sin
necesidad de guardarlas.

#### Comunicación viral mediante el canal de noticias

Los sitios web pueden aprovechar todo el potencial viral de Facebook gracias a
la posibilidad de interactuar con el canal de noticias del usuario, la opción
de enviar invitaciones a sus amigos y la publicación de información en el muro
del usuario o de sus amigos. Cualquier sitio web con cierto componente social
puede hacer uso de esta característica, siempre que la información publicada en
Facebook tenga algún valor social de interés para los amigos o para los amigos
de los amigos del usuario.

#### Aprovechar la red de contactos existente

El coste que supone para cualquier sitio web nuevo la creación de una red social
de usuarios amplia y con muchas conexiones entre ellos es prohibitivo. *Facebook
Connect* reduce el coste al mínimo porque proporciona acceso a la lista de amigos
del usuario, haciendo innecesaria la típica opción *"buscar conocidos que ya
estén registrados en este sitio web"*.

Creando un primer proyecto con `sfFacebookConnectPlugin`
--------------------------------------------------------

### Crear la aplicación Facebook

Para crear la aplicación es necesario disponer en primer lugar de una cuenta de
Facebook con la [aplicación "Developer"](http://www.facebook.com/developers)
instalada. La única información necesaria para crear la aplicación es su nombre.
Una vez creada, ya no es necesario configurar nada más.

### Instalar y configurar `sfFacebookConnectPlugin`

El siguiente paso consiste en relacionar los usuarios de Facebook con los usuarios
de `sfGuard`. Esta es la principal finalidad del plugin `sfFacebookConnectPlugin`
creado por Fabrice Bernhard y que incluye las contribuciones de muchos otros
programadores de Symfony. Después de instalar el plugin, es obligatorio configurarlo
correctamente para poder usarlo. Para ello, añade en el archivo de configuración
`app.yml` la clave de la API y el `ID` y el secreto de la aplicación:

    [yml]
    # default values
    all:
      facebook:
        api_key: xxx
        api_secret: xxx
        api_id: xxx
        redirect_after_connect: false
        redirect_after_connect_url: ''
        connect_signin_url: 'sfFacebookConnectAuth/signin'
        app_url: '/my-app'
        guard_adapter: ~
        js_framework: none # none, jQuery or prototype.

      sf_guard_plugin:
        profile_class: sfGuardUserProfile
        profile_field_name: user_id
        profile_facebook_uid_name: facebook_uid # WARNING this column must be of type varchar! 100000398093902 is a valid uid for example!
        profile_email_name: email
        profile_email_hash_name: email_hash

      facebook_connect:
        load_routing:     true
        user_permissions: []

>**TIP**
>Si utilizas una versión antigua de Symfony, no olvides establecer la opción
>`load_routing` a `false`, ya que utiliza el nuevo sistema de enrutamiento.

### Configurar una aplicación Facebook

Si el proyecto es una aplicación Facebook, el otro parámetro importante que se
debe configurar es `app_url` que apunta a la ruta relativa de la aplicación en
Facebook. Si por ejemplo la aplicación se puede acceder en `http://apps.facebook.com/my-app`
el valor del parámetro `app_url` debería ser `/my-app`.

### Configurar un sitio web con Facebook Connect

Si el proyecto es un sitio web con Facebook Connect, es habitual dejar los valores
por defecto en el resto de las opciones:

 * `redirect_after_connect` permite modificar el comportamiento que se produce
   después de pulsar el botón *"Connect with Facebook"*. Por defecto el plugin
   reproduce el comportamiento de `sfGuardPlugin` tras el registro de un usuario.

 * `js_framework` se emplea para indicar el framework JavaScript utilizado. Se
   recomienda utilizar un framework como jQuery en los sitios web con *Facebook
   Connect* porque el código JavaScript de Facebook es muy grande y puede causar
   errores en Internet Explorer 6 si no se carga bien.

 * `user_permissions` es un array con los permisos que se concederán a los
   nuevos usuarios de *Facebook Connect*.

### Relacionando sfGuard con Facebook

La relación entre los usuarios de Facebook y los del plugin `sfGuardPlugin` se
realiza mediante una columna llamada `facebook_uid` en la tabla `Profile`. El
plugin supone que la relación entre `sfGuardUser` y su perfil se realiza mediante
el método `getProfile()`. Aunque este es el comportamiento por defecto de
`sfPropelGuardPlugin`, en el caso de `sfDoctrineGuardPlugin` es preciso configurarlo
a mano. A continuación se muestra un posible archivo `schema.yml`:

Para Propel:

    [yml]
    sf_guard_user_profile:
      _attributes: { phpName: UserProfile }
      id:
      user_id:            { type: integer, foreignTable: sf_guard_user, foreignReference: id, onDelete: cascade }
      first_name:         { type: varchar, size: 30 }
      last_name:          { type: varchar, size: 30 }
      facebook_uid:       { type: varchar, size: 20 }
      email:              { type: varchar, size: 255 }
      email_hash:         { type: varchar, size: 255 }
      _uniques:
        facebook_uid_index: [facebook_uid]
        email_index:        [email]
        email_hash_index:   [email_hash]

Para Doctrine:

    [yml]
    sfGuardUserProfile:
      tableName:     sf_guard_user_profile
      columns:
        user_id:          { type: integer(4), notnull: true }
        first_name:       { type: string(30) }
        last_name:        { type: string(30) }
        facebook_uid:     { type: string(20) }
        email:            { type: string(255) }
        email_hash:       { type: string(255) }
      indexes:
        facebook_uid_index:
          fields: [facebook_uid]
          unique: true
        email_index:
          fields: [email]
          unique: true
        email_hash_index:
          fields: [email_hash]
          unique: true
      relations:
        sfGuardUser:
          type: one
          foreignType: one
          class: sfGuardUser
          local: user_id
          foreign: id
          onDelete: cascade
          foreignAlias: Profile


>**TIP**
>¿Qué sucede si el proyecto utiliza Doctrine y el valor de la opción `foreignAlias`
>no es `Profile`. En ese caso, el plugin simplemente no funciona. Afortunadamente,
>el problema se puede resolver añadiendo un método `getProfile()` sencillo en
>la clase `sfGuardUser.class.php` y que apunte a la tabla `Profile`.

También es importante que la columna `facebook_uid` sea de tipo `varchar`, ya
que los nuevos perfiles de Facebook tienen `uids` con valores superiores a `10^15`.
Resulta más sencillo utilizar una columna de tipo `varchar` con un índice asociado
en vez de intentar hacer funcionar las columnas de tipo `bigint` con los
diferentes ORM.

Las otras dos columnas (`email` y `email_hash`) son menos importantes y sólo son
necesarias en el caso de los sitios web con *Facebook Connect* que ya tenían
usuarios registrados previamente. En ese caso, Facebook realia un proceso un
poco complicado para tratar de asociar las cuentas existentes con las nuevas
cuentas de Facebook mediante el *hash* de un email. El plugin `sfFacebookConnectPlugin`
facilita este proceso con una de las tareas que incluye, tal y como se describe
al final de este capítulo.

### Symfony evita el problema de elegir entre FBML y XFBML

Ahora que ya está todo preparado, es posible empezar a programar la aplicación.
Facebook ofrece muchas etiquetas especiales que permiten mostrar funcionalidades
completas, como un formulario para invitar a amigos o un sistema completo de
comentarios. Estas etiquetas se denominan FBML o XFBML. Los dos tipos de etiquetas
son muy similares y la elección depende de si la aplicación se muestra dentro
de Facebook o no. Si el proyecto es un sitio web de tipo *Facebook Connect*, sólo
se pueden utilizar las etiquetas XFBML. Si se trata de una aplicación Facebook
se pueden seleccionar cualquiera de las dos opciones:

 * Si se embebe la aplicación dentro de un `<iframe>` de la página de la aplicación
   de Facebook, se emplea XFBML.

 * Si dejamos que Facebook embeba la aplicación de forma transparente, se utiliza
   FBML.

Facebook aconseja a los programadores que embeban sus aplicaciones de forma
transparente, por lo que fomenta el uso de las *aplicaciones FBML*. En efecto,
esta estrategia tiene algunas características muy interesantes:

 * No se utiliza ningún `<iframe>`, que siempre es más complicado de gestionar
   porque tienes que tener en cuenta si los enlaces de la aplicación deben
   apuntar al `<iframe>` o a la ventana contenedora.

 * El servidor de FBML interpreta las etiquetas especiales FBML, por lo que es
   posible mostrar información privada del usuario sin tener que realizar una
   comunicación previa con el servidor de Facebook.

 * No es necesario pasar la sesión de Facebook de una página a otra manualmente.

FBML también tiene algunas desventajas importantes:

 * Todos los archivos JavaScript se incluyen desde un *sandbox*, por lo que no
   pueden utilizar librerías externas como las de Google Maps, jQuery o cualquier
   otro sistema de estadísticas que no sea Google Analytics (soportado oficialmente
   por Facebook).

 * Facebook asegura que es mucho más rápido porque alguna de las peticiones de
   la API se pueden sustituir por etiquetas FBML. No obstante, si la aplicación
   es sencilla, resulta mucho más rápido que disponga de su propio sitio web.

 * Resulta más difícil depurar las aplicaciones, sobre todo los errores de tipo
   500, que son interceptados por Facebook y se reemplazan por errores estándar.

¿Cuál es entonces la opción recomendada? La buena noticia es que gracias a
Symfony y al plugin `sfFacebookConnectPlugin`, no debes tomar ninguna decisión.
Se pueden crear aplicaciones *agnósticas* cuyo código sirva tanto para las
aplicaciones ejecutadas en un `<iframe>`, como para las aplicaciones embebidas
de forma transparente y también para los sitios con *Facebook Connect*. Esto es
posible ya que técnicamente, la principal diferencia entre ellas reside en el
layout, que en Symfony se puede modificar fácilmente. Estos son los ejemplos de
los dos tipos de layouts:

El layout de una aplicación FBML:

    [html]
    <?php sfConfig::set('sf_web_debug', false); ?>
    <fb:title><?php echo sfContext::getInstance()->getResponse()->getTitle() ?></fb:title>
    <?php echo $sf_content ?>

El layout de una aplicación XFBML o de tipo *Facebook Connect*:

    [html]
    <?php use_helper('sfFacebookConnect')?>
    <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
    <html xmlns="http://www.w3.org/1999/xhtml" xmlns:fb="http://www.facebook.com/2008/fbml">
      <head>
        <?php include_http_metas() ?>
        <?php include_metas() ?>
        <?php include_title() ?>
        <script type="text/javascript" src="/sfFacebookConnectPlugin/js/animation/animation.js"></script>
      </head>
      <body>
        <?php echo $sf_content ?>
        <?php echo include_facebook_connect_script() ?>
      </body>
    </html>

Para alternar entre uno y otro layout, simplemente añade el siguiente código
dentro del archivo `actions.class.php`:

    [php]
    public function preExecute()
    {
      if (sfFacebook::isInsideFacebook())
      {
        $this->setLayout('layout_fbml');
      }
      else
      {
        $this->setLayout('layout_connect');
      }
    }

>**NOTE**
>Existe una pequeña diferencia entre FBML y XFBML que no se encuentra en el
>layout: las etiquetas FBML se pueden cerrar y las etiquetas XFBML no se pueden
>cerrar. Así que sustituye este tipo de etiquetas:
>
>      [html]
>      <fb:profile-pic uid="12345" size="normal" width="400" />
>
>por esta otra etiqueta equivalente:
>
>      [html]
>      <fb:profile-pic uid="12345" size="normal" width="400"></fb:profile-pic>

Para que este cambio sea efectivo, la aplicación también debe ser configurada
como de tipo *Facebook Connect* dentro de las opciones de la aplicación de
Facebook, incluso aunque la aplicación sólo esté pensada para FBML. No obstante,
la enorme ventaja de hacerlo es que se puede probar la aplicación de forma local.

Si vas a crear una aplicación de Facebook que utilice etiquetas FBML, algo casi
inevitable, la única forma de ver el resultado final es publicar el código de
la aplicación y ver cómo muestra el resultado Facebook. El uso de *Facebook Connect*
permite utilizar las etiquetas XFBML fuera del sitio web facebook.com y como se
ha explicado anteriormente, la única diferencia entre FBML y XFBML es el layout.

Por tanto, la solución presentada permite mostrar las etiquetas FBML de forma
local, siempre que dispongas de conexión a Internet. Además, con un entorno de
desarrollo accesible desde Internet (como por ejemplo un servidor o un simple
ordenador con el puerto 80 abierto) incluso las partes que dependen de la
autenticación de Facebook funcionan fuera del dominio facebook.com, gracias al
sistema *Facebook Connect*. De esta forma, puedes probar la aplicación completa
antes de subirla a Facebook.

### La aplicación sencilla *Hola, tu*

Si añades el siguiente código en la plantilla de la portada del sitio, ya dispones
de una aplicación que muestra el mensaje *Hola, [tu nombre]*:

    [php]
    <?php $sfGuardUser = sfFacebook::getSfGuardUserByFacebookSession(); ?>
    Hello <fb:name uid="<?php echo $sfGuardUser?$sfGuardUser->getProfile()->getFacebookUid():'' ?>"></fb:name>

El plugin `sfFacebookConnectPlugin` convierte automáticamente a cualquier
usuario que sea miembro de Facebook en un usuario de tipo `sfGuard`. Así es muy
sencillo integrar Facebook con el código Symfony existente que haga uso del
plugin `sfGuardPlugin`.

Facebook Connect
----------------

### Cómo funciona Facebook Connect y cómo se puede integrar

El funcionamiento de Facebook Connect se basa en compartir su sesión con la
sesión del sitio web. Para ello se copian las cookies de autenticación de
Facebook en el sitio web mediante un `<iframe>` que se crea en el sitio web que
apunta a una página de Facebook, que a su vez crea un `<iframe>` al sitio web.
Como para realizar este proceso Facebook Connect debe tener acceso al sitio web,
no es posible utilizar o probar Facebook Connect en un sitio web local o en una
intranet. El punto de entrada es un archivo llamado `xd_receiver.htm` y que
también proporciona el plugin `sfFacebookConnectPlugin`. Para que este archivo
sea accesible, recuerda ejecutar la tarea `plugin:publish-assets` después de
instalar el plugin.

Una vez hecho lo anterior, la librería oficial de Facebook ya puede utilizar la
sesión de Facebook. Además, el plugin `sfFacebookConnectPlugin` crea un usuario
de tipo `sfGuard` asociado con la sesión de Facebook, por lo que la integración
con el sitio web de Symfony es completa. Este es el motivo por el que el plugin
redirige por defecto a la acción `sfFacebookConnectAuth/signIn` después de pulsar
el botón de *Facebook Connect* y una vez que la sesión de Facebook ha sido
validada. El plugin primero busca algún usuario existente con ese UID de Facebook
o con el mismo hash de email (como se explica más adelante). Si no se encuentra
ningún usuario, se crea uno nuevo.

Otra estrategia común consiste en no crear directamente el usuario sino redirigirle
a un formulario de registro propio. En ese formulario se pueden rellenar de
forma automática todos los datos proporcionados por la sesión de Facebook, tal
y como muestra el código del siguiente ejemplo:

    [php]
    public function setDefaultsFromFacebookSession()
    {
      if ($fb_uid = sfFacebook::getAnyFacebookUid())
      {
        $ret = sfFacebook::getFacebookApi()->users_getInfo(
          array(
            $fb_uid
          ),
          array(
            'first_name',
            'last_name',
          )
        );

        if ($ret && count($ret)>0)
        {
          if (array_key_exists('first_name', $ret[0]))
          {
            $this->setDefault('first_name',$ret[0]['first_name']);
          }
          if (array_key_exists('last_name', $ret[0]))
          {
            $this->setDefault('last_name',$ret[0]['last_name']);
          }
        }
      }

Si se quiere utilizar esta segunda estrategia, simplemente se debe indicar en
el archivo `app.yml` que después de conectar con *Facebook Connect* se redirige
al usuario a la ruta indicada:

    [yml]
    # default values
    all:
      facebook:
        redirect_after_connect: true
        redirect_after_connect_url: '@register_with_facebook'

### El filtro de Facebook Connect

Otra de las características importantes de *Facebook Connect* es que muchos de
los usuarios que visitan tu sitio web ya se han conectado previamente en Facebook.
Esta característica la aprovecha el plugin `sfFacebookConnectRememberMeFilter`
de forma muy útil. Si un usuario que visita tu sitio web ya está conectado a
Facebook, el plugin `sfFacebookConnectRememberMeFilter` le conecta de forma
automática al sitio web de la misma forma en la que lo hace el filtro *"Remember
me"*.

    [php]
    $sfGuardUser = sfFacebook::getSfGuardUserByFacebookSession();
    if ($sfGuardUser)
    {
      $this->getContext()->getUser()->signIn($sfGuardUser, true);
    }

Sin embargo, esta funcionalidad tiene una desventaja que puede ser importante:
los usuarios no se pueden desconectar del sitio web mientras permanezcan
conectados en Facebook. Por tanto, emplea esta característica con cautela.

### Cómo evitar los errores de JavaScript en Internet Explorer

Uno de los errores más graves que pueden suceder en una aplicación web es el
famoso error *"Operation aborted"* de Internet Explorer que impide que se muestre
la aplicación web. El motivo de este error es el motor de renderizado de IE 6
y IE 7, que produce un error grave cuando se añaden elementos DOM al `<body>`
desde un script que no sea hijo directo del `<body>`

Desafortunadamente esta práctica es habitual cuando cargas el código JavaScript
de Facebook Connect sin preocuparte de cargarlo solamente desde el elemento
`<body>` y desde el final de la página. Symfony puede solucionar fácilmente
este problema mediante el uso de los `slots`. Siempre que se vaya a incluir el
script de Facebook Connect, se utiliza un `slot` en la plantilla y se muestra
al final del layout, justo antes de la etiqueta `</body>`:

    [php]
    // en una plantilla que utilice XFBML o un botón de Facebook Connect
    slot('fb_connect');
    include_facebook_connect_script();
    end_slot();

    // justo antes de la etiqueta </body> del layout
    if (has_slot('fb_connect'))
    {
      include_slot('fb_connect');
    }

Buenas prácticas en las aplicaciones Facebook
---------------------------------------------

Las secciones anteriores han explicado cómo el plugin `sfFacebookConnectPlugin`
se integra perfectamente con `sfGuardPlugin` y cómo permite crear aplicaciones
*agnósticas* que pueden convertirse en aplicaciones FBML, de tipo `<iframe>` o
de tipo *Facebook Connect*. No obstante, para crear una aplicación real más
compleja y que utilice características avanzadas de Facebook es necesario seguir
algunos de los siguientes consejos para aprovechar también las funcionalidades
de Symfony.

### Utilizando los entornos de Symfony para probar diferentes servidores Facebook Connect

Depurar rápidamente las aplicaciones y facilitar sus pruebas es uno de los
principios de Symfony. Desarrollar aplicaciones Facebook puede dificultar
seriamente estas opciones, ya que muchas características requieren una conexión
a Internet para comunicarse con el servidor de Facebook y un puerto 80 abierto
para intercambiar las cookies de autenticación. Además, existe la limitación de
que las aplicaciones que utilizan Facebook Connect sólo pueden conectarse a un
servidor.

Esta última limitación es un gran problema si la aplicación se desarrolla en una
máquina, se prueba en otra, se pone en pre-producción en una tercer máquina y
por último se sube a producción en otra máquina. Con Symfony la forma habitual
de proceder en este caso sería crear una aplicación para cada servidor y crear
un entorno para cada máquina. Realizar todo esto en Symfony es tan sencillo como
hacer una copia del archivo `frontend_dev.php` en otro archivo llamado
`frontend_preprod.php` y modificarlo para indicar que el nuevo entorno se llama
`preprod` (de *pre-producción*) y no `dev`:

    [php]
    $configuration = ProjectConfiguration::getApplicationConfiguration('frontend', 'preprod', true);

A continuación, modifica el archivo `app.yml` para configurar aplicaciones
Facebook diferentes en función de cada entorno:

    [yml]
    prod:
      facebook:
        api_key: xxx
        api_secret: xxx
        api_id: xxx

    dev:
      facebook:
        api_key: xxx
        api_secret: xxx
        api_id: xxx

    preprod:
      facebook:
        api_key: xxx
        api_secret: xxx
        api_id: xxx

Ahora la aplicación ya se puede probar en cada uno de os diferentes servidores
utilizando como punto de entrada el correspondiente archivo `frontend_xxx.php`.

### Depurando FBML con los mensajes de log de Symfony

La estrategia de cambiar el layout permite desarrollar y probar aplicaciones
FBML fuera del sitio web de Facebook. Sin embargo, las pruebas finales sobre
Facebook pueden mostrar mensajes de error inesperados y difíciles de comprender.
En efecto, el principal problema de mostrar FBML directamente en Facebook es
que los errores de tipo 500 se interceptan y reemplazan por unos mensajes de
error estándar poco útiles. Además, la barra de depuración web de Symfony
tampoco se muestra dentro del *frame* de Facebook. Afortunadamente, todos estos
problemas se pueden solucionar gracias a los mensajes de log de Symfony. El
plugin `sfFacebookConnectPlugin` crea mensajes de log para todas las acciones
importantes, y es muy sencillo añadir nuevos mensajes de log desde cualquier
lugar de la aplicación:

    [php]
    if (sfConfig::get('sf_logging_enabled'))
    {
      sfContext::getInstance()->getLogger()->info($message);
    }

### Utilizar un proxy para evitar las redirecciones erróneas de Facebook

Uno de los errores más peculiares de Facebook es que una vez que *Facebook
Connect* se ha configurado en la aplicación, el servidor de *Facebook Connect*
se considera la página de inicio de la aplicación. Aunque esta página de inicio
se puede configurar, siempre debe pertenecer al mismo dominio del host de
*Facebook Connect*. Así que no queda otro remedio que configurar la portada de
la aplicación a una acción simple de Symfony que redirija a la URL adecuada.
El siguiente ejemplo muestra el código para redirigir a una aplicación de
Facebook:

    [php]
    public function executeRedirect(sfWebRequest $request)
    {

      return $this->redirect('http://apps.facebook.com'.sfConfig::get('app_facebook_app_url'));
    }

### Utilizar el helper `fb_url_for()` en las aplicaciones Facebook

El enrutamiento es uno de los mayores problemas de que las aplicaciones sean
*agnósticas* y puedan funcionar como FBML dentro de Facebook o como XFBML
dentro de un `<iframe>`:

 * Si se trata de una aplicación FBML, los enlaces internos de la aplicación
   tienen que apuntar a `/app-name/ruta-symfony`

 * Si se trata de una aplicación con `<iframe>`, es importante pasar la
   información de la sesión de Facebook de una página a otra.

El plugin `sfFacebookConnectPlugin` incluye un *helper* especial llamado
`fb_url_for()` y que se encarga de los dos casos.

### Redirigiendo dentro de una aplicación FBML

Los programadores de Symfony están acostumbrados a redirigir al usuario a una
nueva página tras una operación exitosa, de forma que se evite repetir esa misma
operación. Sin embargo, en las aplicaciones FBML las redirecciones no funcionan
tal y como se espera. En su lugar, es necesario utilizar una etiqueta especial
llamada `<fb:redirect>` y que ordena a Facebook realizar la redirección. Para
que la aplicación funcione correctamente en cualquier caso (etiqueta FBML o
redirección normal de Symfony) la clase `sfFacebook` incluye una función especial
para redirecciones que se puede utilizar por ejemplo al guardar una acción:

    [php]
    if ($form->isValid())
    {
      $form->save();

      return sfFacebook::redirect($url);
    }

### Relacionando los usuarios existentes con sus cuentas de Facebook

Uno de los objetivos de *Facebook Connect* es facilitar el proceso de registro
de los nuevos usuarios. De todos modos, otro de sus usos más interesantes es
el de relacionar los usuarios ya existentes en el sitio web con sus respectivas
cuentas de Facebook, ya sea para obtener más información de los usuarios o
para comunicarse a través de su Facebook. Esto se puede conseguir de dos formas:

 * Obligar a los usuarios de tipo `sfGuard` existentes a que pulsen el botón
   *"Connect with Facebook"*. La acción `sfFacebookConnectAuth/signIn` no crea
   nuevos usuarios de tipo `sfGuard` si detecta que ese usuario ya existe, pero
   si que crea cualquier nuevo usuario conectado a través de *Facebook Connect*.
   Tan simple como eso.

 * Utilizar el sistema de reconocimiento de emails de Facebook. Cuando un usuario
   utiliza *Facebook Connect* en un sitio web, Facebook puede proporcionar un
   hash especial de sus emails, que se puede comparar con los hash de emails
   existentes en la base de datos, de forma que se puedan reconocer las cuentas
   de Facebook pertenecientes a los usuarios que ya estaban creados. No obstante,
   por razones de seguridad, Facebook sólo proporciona estos *hashes* si el
   usuario se ha registrado previamente utilizando su API. Por tanto, resulta
   imprescindible registrar de forma habitual los emails de todos los nuevos
   usuarios de forma que puedan ser reconocidos más adelante. Esto es precisamente
   lo que hace la tarea `registerUsers`, que ha sido portada a Symfony 1.2 por
   Damien Alexandre. Esta tarea se debería ejecutar cada noche para registrar
   todos los nuevos usuarios. También se puede realizar el registro justo
   después de crear un usuario, mediante el método `registerUsers()` de
   `sfFacebookConnect`:

      [php]
      sfFacebookConnect::registerUsers(array($sfGuardUser));

Próximos pasos
--------------

Confiamos en que este capítulo haya cumplido con lo prometido: ayudarte a empezar
a desarrollar una aplicación Facebook con Symfony y explicar cómo aprovechar
las características de Symfony durante el desarrollo de la aplicación. No obstante,
el plugin `sfFacebookConnectPlugin` no reemplaza la API de Facebook, por lo que
debes visitar el [sitio web de Facebook para programadores](http://developers.facebook.com/)
si quieres aprenderlo todo sobre cómo desarrollar para la plataforma de Facebook.

Por último, me gustaría agradecer de forma sincera la calidad y generosidad de
la comunidad de usuarios de Symfony, especialmente aquellos que han ayudado al
desarrollo de `sfFacebookConnectPlugin` con sus comentarios y aportaciones:
Damien Alexandre, Thomas Parisot, Maxime Picaud, Alban Creton y cualquier otro
usuario que haya olvidado. Por supuesto, si crees que le falta algo importante
al plugin, tu mismo puedes ayudar en su desarrollo.
