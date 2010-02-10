Emails
======

*por Fabien Potencier*

Enviar ~emails~ con symfony es sencillo a la vez que potente, gracias al uso
de la librería [Swift Mailer](http://www.swiftmailer.org/). Aunque enviar
emails con ~Swift Mailer~ es muy sencillo, symfony añade una capa adicional
por encima para hacer que el envío de emails sea todavía más flexible y
potente. Este capítulo explica todas las opciones a tu disposición.

>**NOTE**
>symfony 1.3 incluye la versión 4.1 de Swift Mailer.

Introducción
------------

Symfony gestiona la creación y envío de emails a través de un objeto de tipo
mailer. Como la mayoría de objetos del núcleo de symfony, el objeto mailer
también es una factoría. Su comportamiento se configura mediante el archivo de
configuración `factories.yml` y siempre está disponible a través del objeto
que almacena el contexto:

    [php]
    $mailer = sfContext::getInstance()->getMailer();

>**TIP**
>Al contrario que el resto de factorías, el objeto mailer se carga e
inicializa bajo demanda. Por tanto, si no lo utilizas no se penaliza el
rendimiento de la aplicación.

Este tutorial explica la integración de Swift Mailer con symfony. Si quieres
conocer todos los detalles de la librería Swift Mailer, puedes leer su propia
[documentación](http://www.swiftmailer.org/docs).

Enviando emails desde una acción
--------------------------------

Obtener la instancia del objeto mailer en una acción es muy sencillo gracias
al atajo `getMailer()`:

    [php]
    $mailer = $this->getMailer();

### La forma más rápida

Enviar un email es tan sencillo como utilizar el método
~`sfAction::composeAndSend()`~:

    [php]
    $this->getMailer()->composeAndSend(
      'remitente@ejemplo.com',
      'fabien@ejemplo.com',
      'Asunto',
      'Cuerpo'
    );

El método `composeAndSend()` utiliza cuatro argumentos:

 * La dirección desde la que se envía el email (campo `from`)
 * La dirección o direcciones a las que se envía el email (campo `to`)
 * El asunto del mensaje
 * El cuerpo o contenido del mensaje

Siempre que un método utilice una dirección de email como argumento, se puede
indicar como cadena de texto o como array:

    [php]
    $direccion = 'fabien@ejemplo.com';
    $direccion = array('fabien@ejemplo.com' => 'Fabien Potencier');

Obviamente puedes enviar un mismo email a varios destinatarios pasando como
segundo argumento del método un array con todas las direcciones de email:

    [php]
    $para = array(
      'destinatario1@ejemplo.com',
      'destinatario2@ejemplo.com',
    );
    $this->getMailer()->composeAndSend('remitente@ejemplo.com', $para, 'Asunto', 'Cuerpo');

    $para = array(
      'destinatario1@ejemplo.com' => 'Sr. Destinatario',
      'destinatario2@ejemplo.com' => 'Sra. Destinataria',
    );
    $this->getMailer()->composeAndSend('remitente@ejemplo.com', $para, 'Asunto', 'Cuerpo');

### La forma flexible

Si necesitas más flexibilidad, puedes hacer uso del método 
~`sfAction::compose()`~ para crear un mensaje, personalizarlo de la forma que
quieras y enviarlo después. Esta forma es útil por ejemplo cuando quieres 
añadir un ~adjunto|adjunto en el email~ como se muestra a continuación:

    [php]
    // crear un objeto de tipo mensaje
    $mensaje = $this->getMailer()
      ->compose('remitente@ejemplo.com', 'fabien@ejemplo.com', 'Asunto', 'Cuerpo')
      ->attach(Swift_Attachment::fromPath('/ruta/hasta/el/archivo.zip'))
    ;

    // enviar el mensaje
    $this->getMailer()->send($mensaje);

### La forma más completa

Si necesitas aún más flexibilidad, puedes crear directamente el objeto del 
mensaje:

    [php]
    $mensaje = Swift_Message::newInstance()
      ->setFrom('remitente@ejemplo.com')
      ->setTo('destinatario@ejemplo.com')
      ->setSubject('Asunto')
      ->setBody('Cuerpo')
      ->attach(Swift_Attachment::fromPath('/ruta/hasta/el/archivo.zip'))
    ;

    $this->getMailer()->send($mensaje);

>**TIP**
>Si quieres saberlo todo sobre cómo crear mensajes, puedes leer las
>secciones ["Creando mensajes"](http://swiftmailer.org/docs/messages) y
>["Cabeceras de los mensajes"](http://swiftmailer.org/docs/headers) de la
>documentación oficial de Swift Mailer.

Utilizando la vista de symfony
------------------------------

Enviar los emails desde las acciones permite aprovechar fácilmente todas las 
características de los componentes y de los elementos parciales.

    [php]
    $mensaje->setBody($this->getPartial('nombre_del_parcial', $argumentos));


Configuración
-------------

Como el objeto mailer también es una factoría, se puede modificar su 
comportamiento mediante el archivo de configuración `factories.yml`. Su 
configuración por defecto es la siguiente:

    [yml]
    mailer:
      class: sfMailer
      param:
        logging:           %SF_LOGGING_ENABLED%
        charset:           %SF_CHARSET%
        delivery_strategy: realtime
        transport:
          class: Swift_SmtpTransport
          param:
            host:       localhost
            port:       25
            encryption: ~
            username:   ~
            password:   ~

Cuando se crea una nueva aplicación, el archivo `factories.yml` local redefine
la configuración anterior para establecer unos valores más apropiados en los 
entornos `prod`, `env` y `test`:

    [yml]
    test:
      mailer:
        param:
          delivery_strategy: none

    dev:
      mailer:
        param:
          delivery_strategy: none

La estrategia de envío
----------------------

La estrategia o mecanismo de envío es una de las características más útiles de 
la integración de Swift Mailer con symfony. La estrategia de envío permite 
indicar a symfony la forma en la que se envían los mensajes y se puede 
configurar mediante la opción ~`delivery_strategy`~ del archivo de 
configuración `factories.yml`. La estrategia modifica el comportamiento del 
método ~`send()`|`sfMailer::send()`~. Por defecto se dispone de cuatro 
estrategias diferentes, que cubren todas las necesidades habituales:

 * `realtime`:       los mensajes se envían en tiempo real.
 * `single_address`: los mensajes se envían a la dirección de correo electrónico indicada.
 * `spool`:          los mensajes se guardan en una cola de envío.
 * `none`:           los mensajes no se envían y simplemente se ignoran.

### La estrategia ~`realtime`~

La estrategia `realtime` es la que se utiliza por defecto y la más sencilla de 
configurar porque no se debe hacer nada especial.

Los emails se envían mediante el transporte configurado en la sección 
`transport` del archivo de configuración `factories.yml` (la siguiente sección 
explica cómo configurar un transporte para email).

### La estrategia ~`single_address`~

Utilizando la estrategia `single_address`, todos los mensajes se envían a una 
dirección de correo electrónico configurada en la opción `delivery_address`.

Esta estrategia es muy útil en el entorno de desarrollo para no enviar los 
emails a los usuarios reales pero al mismo tiempo permitir que el programador 
pueda comprobar con su lector de correo el aspecto y contenido de los emails 
enviados.

>**TIP**
>Si quieres comprobar las direcciones `para`, `cc` y `bcc` originales, están 
>disponibles en las cabeceras `X-Swift-To`, `X-Swift-Cc` y `X-Swift-Bcc` 
>respectivamente.

Los emails se envían mediante el mismo transporte que utiliza la estrategia 
`realtime`.

### La estrategia ~`spool`~

La estrategia `spool` hace que todos los mensajes se almacenen en una cola.

Esta es la mejor estrategia para el entorno de producción, ya que las 
peticiones web no tienen que esperar a que se envíen los emails.

La clase `spool` se configura mediante la opción ~`spool_class`~. Symfony 
incluye por defecto tres clases de este tipo:

 * ~`Swift_FileSpool`~: los mensajes se guardan en el sistema de archivos.
 * ~`Swift_DoctrineSpool`~: los mensajes se guardan en un modelo de Doctrine.
 * ~`Swift_PropelSpool`~: los mensajes se guardan en un modelo de Propel.

Cuando se instancia la clase, se pasa como argumento de su constructor la 
opción ~`spool_arguments`~. A continuación se indican las opciones disponibles 
para los tipos de colas incluidos por defecto:

 * `Swift_FileSpool`:

    * La ruta absoluta del directorio de la cola (los mensajes se guardan en 
      este directorio)

 * `Swift_DoctrineSpool`:

    * El modelo de Doctrine en el que se guardan los mensajes (por defecto es 
      `MailMessage`)

    * El nombre de la columna utilizada para guardar el mensaje (por defecto 
      es `message`)

    * El método que se invoca para obtener los mensajes a enviar (opcional). 
      Como argumento de este método se le pasan las opciones de la cola.

 * `Swift_PropelSpool`:

    * El modelo de Propel en el que se guardan los mensajes (por defecto es 
     `MailMessage`)

    * El nombre de la columna utilizada para guardar el mensaje (por defecto 
      es `message`)

    * El método que se invoca para obtener los mensajes a enviar (opcional). 
      Como argumento de este método se le pasan las opciones de la cola.

Seguidamente se muestra la configuración típica de una cola de Doctrine:

    [yml]
    # Configuración del esquema en schema.yml
    MailMessage:
     actAs: { Timestampable: ~ }
     columns:
       message: { type: clob, notnull: true }

-

    [yml]
    # configuración en factories.yml
    mailer:
      class: sfMailer
      param:
        delivery_strategy: spool
        spool_class:       Swift_DoctrineSpool
        spool_arguments:   [ MailMessage, message, getSpooledMessages ]

Y la misma configuración de antes para una cola de Propel:

    [yml]
    # Configuración del esquema en schema.yml
    mail_message:
      message:    { type: clob, required: true }
      created_at: ~

-

    [yml]
    # configuración en factories.yml
    dev:
      mailer:
        param:
          delivery_strategy: spool
          spool_class:       Swift_PropelSpool
          spool_arguments:   [ MailMessage, message, getSpooledMessages ]

Para enviar todos los mensajes almacenados en la cola, puedes emplear la tarea 
~`project:send-emails`~ (esta tarea es completamente independiente del tipo de 
cola y de sus opciones):

    $ php symfony project:send-emails

>**NOTE**
>La tarea `project:send-emails` requiere como argumentos el nombre de una 
aplicación y un entorno.

Cuando se utiliza la tarea `project:send-emails`, los mensajes se envían con 
el mismo transporte utilizado por la estrategia `realtime`.

>**TIP**
>La tarea `project:send-emails` se puede ejecutar en cualquier máquina, no 
>necesariamente en la misma en la que se creó el mensaje. Esto es así porque 
>todo se guarda en el objeto del mensaje, incluso los archivos adjuntos.

-

>**NOTE**
>El funcionamiento interno de las colas es muy sencillo. Los emails se envían 
>sin controlar los errores que se pueden producir, es decir, como si hubieran 
>sido enviados con la estrategia `realtime`. Obviamente puedes extender las 
>clases de las colas para incluir tu propia lógica de gestión de errores.

La tarea `project:send-emails` admite opcionalmente las siguientes dos opciones:

 * `message-limit`: limita el número de mensajes que se envían.

 * `time-limit`: limita (en segundos) el tiempo empleado en enviar los mensajes.

Las dos opciones también se pueden combinar:

  $ php symfony project:send-emails --message-limit=10 --time-limit=20

El comando anterior deja de enviar mensajes cuando se envían 10 mensajes o
cuando transcurren 20 segundos.

Aun cuando hagas uso de la estrategia `spool`, puede que tengas que enviar un 
mensaje de forma inmediata sin almacenarlo en la cola de mensajes. Para ello, 
puedes hacer uso de un método especial del mailer llamado 
`sendNextImmediately()`:

    [php]
    $this->getMailer()->sendNextImmediately()->send($mensaje);

En el ejemplo anterior, el `$mensaje` no se guardará en la cola y se enviará 
inmediatamente. Como su propio nombre indica, el método 
`sendNextImmediately()` solamente afecta al siguiente mensaje que se envía.

>**NOTE**
>El método `sendNextImmediately()` no produce ningún efecto especial cuando la 
>estrategia de envío no es `spool`.

### La estrategia ~`none`~

Esta estrategia es muy útil en el entorno de desarrollo para no enviar emails 
a ningún usuario real. Los mensajes están disponibles en la barra de 
depuración web (más adelante se explica la sección del mailer en la barra de 
depuración web).

También se trata de la mejor estrategia para el entorno de pruebas, donde el 
objeto `sfTesterMailer` permite la introspección de los mensajes sin tener que 
enviarlos realmente (como se explica más adelante en la sección de pruebas).

El transporte de email
----------------------

Los mensajes de correo electrónico realmente se envían a través de un 
transporte configurado en el archivo de configuración `factories.yml`. La 
configuración por defecto hace uso del servidor SMTP de la máquina local:

    [yml]
    transport:
      class: Swift_SmtpTransport
      param:
        host:       localhost
        port:       25
        encryption: ~
        username:   ~
        password:   ~

Swift Mailer incluye tres tipos diferentes de clases de transporte:

  * ~`Swift_SmtpTransport`~: hace uso de un servidor SMTP para enviar los 
    mensajes.

  * ~`Swift_SendmailTransport`~: emplea sendmail para enviar los mensajes.

  * ~`Swift_MailTransport`~: utiliza la función `mail()` de PHP para enviar 
    los mensajes.

>**TIP**
> La sección ["Tipos de 
transporte"](http://swiftmailer.org/docs/transport-types) de la documentación 
oficial de Swift Mailer describe todo lo necesitas saber sobre las clases 
anteriores y sobre sus parámetros.

Enviando un email desde una tarea
---------------------------------

Enviar un email desde una tarea es muy similar a enviar un email desde una
acción, ya que el sistema de tareas también proporciona un método `getMailer()`.

Cuando se crea el *mailer*, la tarea utiliza la configuración actual, por lo que
si quieres hacer uso de la configuración de una aplicación específica, debes
incluir la opción `--application` (el capítulo dedicado a las tareas tiene más
información sobre esta opción).

La tarea utiliza la misma configuración que los controladores, por lo que si
quieres forzar el envío de los mensajes cuando se utiliza la estrategia `spool`
puedes emplear el método `sendNextImmediately()`:

    [php]
    $this->getMailer()->sendNextImmediately()->send($mensaje);

Depurando
---------

Depurar el envío de emails siempre ha sido una pesadilla. Con symfony la 
depuración es muy sencilla gracias a la ~barra de depuración web~.

Directamente desde el navegador se pueden ver fácilmente cuántos mensajes ha 
enviado la acción que se ha ejecutado:

![Emails en la barra de depuración web](http://www.symfony-project.org/images/more-with-symfony/emails_wdt.png "Emails en la barra de depuración web")

Si pulsas sobre el icono del email, puedes visualizar todos los detalles de 
los mensajes enviados, como se muestra en la siguiente imagen.

![Emails en la barra de depuración web - detalles](http://www.symfony-project.org/images/more-with-symfony/emails_wdt_details.png "Emails en la barra de depuración web - detalles")

>**NOTE**
>Symfony también añade un mensaje en el archivo de log cada vez que se envía 
un email.

Pruebas
-------

La integración de la librería no hubiera sido completa sin una forma sencilla 
de realizar pruebas con mensajes de correo electrónico. Para facilitar las 
pruebas con emails, symfony dispone de un tester denominado `mailer` (clase 
~`sfMailerTester`~)

El método ~`hasSent()`~ prueba el número de mensajes enviados durante la 
petición actual:

    [php]
    $browser->
      get('/foo')->
      with('mailer')->
        hasSent(1)
    ;

El código anterior comprueba que la URL `/foo` solamente envía un email.

Se pueden realizar pruebas más detalladas con cada email enviado gracias a los 
métodos ~`checkHeader()`~ y ~`checkBody()`~:

    [php]
    $browser->
      get('/foo')->
      with('mailer')->begin()->
        hasSent(1)->
        checkHeader('Asunto', '/Asunto/')->
        checkBody('/Cuerpo/')->
      end()
    ;

El segundo argumento de `checkHeader()` y el primero de `checkBody()` pueden 
ser cualquiera de los siguientes elementos:

 * Una cadena de texto, para realizar una comprobación exacta
 * Una expresión regular que debe cumplir el valor comprobado
 * Una expresión regular negativa (es decir, una expresión regular que empieza 
   por el carácter `!`) que no debe cumplir el valor comprobado

Las comprobaciones siempre se realizan sobre el primer mensaje enviado. Si se 
han enviado varios mensajes, puedes elegir cual quieres comprobar mediante el 
método ~`withMessage()`~.

    [php]
    $browser->
      get('/foo')->
      with('mailer')->begin()->
        hasSent(2)->
        withMessage('destinatario@ejemplo.com')->
        checkHeader('Asunto', '/Asunto/')->
        checkBody('/Cuerpo/')->
      end()
    ;

El método `withMessage()` toma como primer argumento un destinatario. Si se 
han enviado varios mensajes al mismo destinatario, también toma como segundo 
argumento el mensaje que se quiere probar.

Por último, el método ~`debug()`~ muestra toda la información sobre los 
mensajes enviados para detectar fácilmente la causa de los problemas:

    [php]
    $browser->
      get('/foo')->
      with('mailer')->
      debug()
    ;

Mensajes de correo electrónico como clases
------------------------------------------

En la introducción de este capítulo se ha mostrado cómo enviar emails desde 
una acción. Esta es probablemente la forma más sencilla de enviar emails en 
una aplicación de symfony y la forma que debes utilizar cuando sólo tienes que 
enviar unos pocos mensajes.

No obstante, si tu aplicación maneja muchos tipos diferentes de mensajes de 
correo electrónico, probablemente debas utilizar una estrategia diferente.

>**NOTE**
>Además, el uso de clases para los mensajes de correo electrónico significa que
>puedes reutilizar el mismo mensaje en diferentes aplicaciones, como por ejemplo
>en el frontend y en el backend.

Como los mensajes son objetos PHP normales, la forma más obvia de organizar 
tus mensajes consiste en crear una clase para cada uno de ellos:

    [php]
    // lib/email/ProjectConfirmationMessage.class.php
    class ProjectConfirmationMessage extends Swift_Message
    {
      public function __construct()
      {
        parent::__construct('Asunto', 'Cuerpo');

        $this
          ->setFrom(array('aplicacion@ejemplo.com' => 'Robot de la Aplicación'))
          ->attach('...')
        ;
      }
    }

A continuación, enviar un mensaje desde una acción o desde cualquier otro 
punto de la aplicación es algo tan sencillo como instanciar la clase de 
mensaje adecuada:

    [php]
    $this->getMailer()->send(new ProjectConfirmationMessage());

Obviamente, puede ser muy útil crear una clase base que centralice todas las 
cabeceras comunes, como por ejemplo la cabecera `From`, o la inclusión de la 
misma firma en todos los mensajes:

    [php]
    // lib/email/ProjectConfirmationMessage.class.php
    class ProjectConfirmationMessage extends ProjectBaseMessage
    {
      public function __construct()
      {
        parent::__construct('Asunto', 'Cuerpo');

        // cabeceras específicas, adjuntos, ...
        $this->attach('...');
      }
    }

    // lib/email/ProjectBaseMessage.class.php
    class ProjectBaseMessage extends Swift_Message
    {
      public function __construct($asunto, $cuerpo)
      {
        $body .= <<<EOF
    --

    Email enviado por el Robot de mi Aplicación
    EOF
        ;
        parent::__construct($asunto, $cuerpo);

        // añadir todas las cabeceras comunes
        $this->setFrom(array('aplicacion@ejemplo.com' => 'Robot de la Aplicación'));
      }
    }

Si un mensaje depende de algunos objetos del modelo, también puedes pasarlos 
como argumentos de su constructor:

    [php]
    // lib/email/ProjectConfirmationMessage.class.php
    class ProjectConfirmationMessage extends ProjectBaseMessage
    {
      public function __construct($usuario)
      {
        parent::__construct('Confirmación para '.$usuario->getNombre(), 'Cuerpo');
      }
    }


Recetas
-------

### Enviando emails mediante ~Gmail~

Si no dispones de un servidor SMTP pero tienes una cuenta de correo 
electrónico de Gmail, utiliza la siguiente configuración para enviar los 
mensajes a través de los servidores de Google:

    [yml]
    transport:
      class: Swift_SmtpTransport
      param:
        host:       smtp.gmail.com
        port:       465
        encryption: ssl
        username:   tu_nombre_de_usuario_de_gmail
        password:   tu_contrasena_de_gmail

Sustituye el valor de las opciones `username` y `password` por tus 
credenciales de Gmail y ya puedes empezar a enviar emails.

### Personalizando el objeto mailer

Si necesitas una configuración mayor que la proporcionada por el archivo de 
configuración `factories.yml`, puedes utilizar el evento ~`mailer.configure`~ 
para personalizar todavía más el objeto mailer.

Puedes conectar tu aplicación con este evento directamente en la clase 
`ProjectConfiguration`, tal y como se muestra a continuación:

    [php]
    class ProjectConfiguration extends sfProjectConfiguration
    {
      public function setup()
      {
        // ...

        $this->dispatcher->connect(
          'mailer.configure',
          array($this, 'configurarMailer')
        );
      }

      public function configurarMailer(sfEvent $event)
      {
        $mailer = $event->getSubject();

        // hacer algo con el objeto mailer
      }
    }

La siguiente sección muestra un caso práctico de esta técnica.

### Utilizando los ~plugins de Swift Mailer~

Los plugins de Swift Mailer plugins requieren el uso del evento 
`mailer.configure` explicado en la sección anterior:

    [php]
    public function configurarMailer(sfEvent $event)
    {
      $mailer = $event->getSubject();

      $plugin = new Swift_Plugins_ThrottlerPlugin(
        100, Swift_Plugins_ThrottlerPlugin::MESSAGES_PER_MINUTE
      );

      $mailer->registerPlugin($plugin);
    }

>**TIP**
>La sección ["Plugins"](http://swiftmailer.org/docs/plugins) de la 
>documentación oficial de Swift Mailer describe todas las características de 
>los plugins incluidos en la librería.

### Personalizando el comportamiento de la cola

El comportamiento por defecto de las colas es muy simple. Se seleccionan todos 
los emails de la cola de forma aleatoria y se envían.

Puedes configurar una cola para que se limite el tiempo (en segundos) dedicado
al envío de los mensajes o para que se limite el número de mensajes a enviar:

    [php]
    $spool = $mailer->getSpool();

    $spool->setMessageLimit(10);
    $spool->setTimeLimit(10);

En esta sección se explica cómo crear un mecanismo de prioridad para la cola y
se muestra todo lo necesario para desarrollar nuestra propia lógica.

En primer lugar se añade en el esquema una columna de prioridad:

    [yml]
    # para Propel
    mail_message:
      message:    { type: clob, required: true }
      created_at: ~
      priority:   { type: integer, default: 3 }

    # para Doctrine
    MailMessage:
      actAs: { Timestampable: ~ }
      columns:
        message:  { type: clob, notnull: true }
        priority: { type: integer }

Cuando se envía un email, se establece la cabecera de prioridad (siendo `1` 
la máxima prioridad):

    [php]
    $mensaje = $this->getMailer()
      ->compose('remitente@ejemplo.com', 'destinatario@ejemplo.com', 'Asunto', 'Cuerpo')
      ->setPriority(1)
    ;
    $this->getMailer()->send($mensaje);

A continuación, redefine el método `setMessage()` por defecto para modificar 
la prioridad del propio objeto `MailMessage`:

    [php]
    // para Propel
    class MailMessage extends BaseMailMessage
    {
      public function setMessage($mensaje)
      {
        $msg = unserialize($mensaje);
        $this->setPriority($msg->getPriority());

        return parent::setMessage($mensaje);
      }
    }

    // para Doctrine
    class MailMessage extends BaseMailMessage
    {
      public function setMessage($mensaje)
      {
        $msg = unserialize($mensaje);
        $this->priority = $msg->getPriority();

        return $this->_set('message', $mensaje);
      }
    }

Ten en cuenta que la cola serializa los mensajes, así que es necesario 
deserializar el mensaje antes de obtener su prioridad. A continuación es 
necesario crear un método que ordene los mensajes por prioridad:

    [php]
    // para Propel
    class MailMessagePeer extends BaseMailMessagePeer
    {
      static public function getSpooledMessages(Criteria $criteria)
      {
        $criteria->addAscendingOrderByColumn(self::PRIORITY);

        return self::doSelect($criteria);
      }

      // ...
    }

    // para Doctrine
    class MailMessageTable extends Doctrine_Table
    {
      public function getSpooledMessages()
      {
        return $this->createQuery('m')
          ->orderBy('m.priority')
        ;
      }

      // ...
    }

El último paso consiste en definir en el archivo de configuración 
`factories.yml` el método que se invoca para obtener los mensajes de la cola:

    [yml]
    spool_arguments: [ MailMessage, message, getSpooledMessages ]

Y eso es todo lo que hay que hacer. Ahora, cuando ejecutes la tarea 
`project:send-emails`, los mensajes se enviarán de acuerdo a su prioridad.

>**SIDEBAR**
>Personalizando la cola con cualquier criterio
>
>El ejemplo anterior emplea una cabecera estándar de los emails, la prioridad.
>Pero si quieres utilizar cualquier otro criterio, o si no quieres modificar
>el mensaje enviado, puedes guardar el criterio en una cabecera personalizada
>y borrarla antes de enviar el mensaje.
>
>En primer lugar añade la cabecera personalizada al email que se va a enviar:
>
>     [php]
>     public function executeIndex()
>     {
>       $mensaje = $this->getMailer()
>         ->compose('remitente@ejemplo.com', 'destinatario@ejemplo.com', 'Asunto', 'Cuerpo')
>       ;
>     
>       $mensaje->getHeaders()->addTextHeader('X-Queue-Criteria', 'valor');
>     
>       $this->getMailer()->send($mensaje);
>     }
>
>A continuación, cuando se guarda el mensaje se obtiene el valor de esta 
>cabecera y se eliminar inmediatamente:
>
>     [php]
>     public function setMessage($mensaje)
>     {
>       $msg = unserialize($mensaje);
>     
>       $cabeceras = $msg->getHeaders();
>       $criteria = $cabeceras->get('X-Queue-Criteria')->getFieldBody();
>       $this->setCriteria($criteria);
>       $cabeceras->remove('X-Queue-Criteria');
>     
>       return parent::_set('message', serialize($msg));
>     }
