Электронная почта
=================

*автор: Fabien Potencier; перевод на русский — BRIGADA*

Благодаря использованию библиотеки [Swift Mailer](http://www.swiftmailer.org/), отправка электронных писем в symfony выполняется очень просто.
Хотя всю работу делает ~Swift Mailer~, symfony выступает в роли "обёртки", которая позволяет сделать процесс отправки писем более гибким и мощным.
В этой главе вы научитесь использовать всю эту мощь.

>**NOTE**
>symfony 1.3 включает Swift Mailer версии 4.1.

Введение
--------

Управление электронной почтой в symfony сконцентрировано в специальном почтовом объекте (мейлере).
Как и многие другие symfony-объекты, этот объект является фабрикой. Он настраивается через конфигурационный файл `factories.yml`, и всегда доступен через экземпляр контекста:

    [php]
    $mailer = sfContext::getInstance()->getMailer();

>**TIP**
>В отличие от других фабрик, почтовый объект загружается и инициализируется по необходимости.
>Если вы его не используете, то и никакого воздействия на общую производительность не происходит.

Эта глава объясняет интеграцию Swift Mailer в symfony.
Если вы хотите узнать больше о самой библиотеке Swift Mailer, обратитесь к её [документации](http://www.swiftmailer.org/docs).

Отправка электронной почты из действия
--------------------------------------

В действии получение экземпляра почтового класса реализовано через метод `getMailer()`:

    [php]
    $mailer = $this->getMailer();

### Быстрый способ

Отправка электронной почты столь же проста, как и использование метода ~`sfMailer::composeAndSend()`~:

    [php]
    $this->getMailer()->composeAndSend(
      'from@example.com',
      'fabien@example.com',
      'Subject',
      'Body'
    );

Метод `composeAndSend()` принимает четыре аргумента:

 * адрес электронной почты отправителя (`from`);
 * один или несколько адресов электронной почты получателя (`to`);
 * тема письма;
 * тело письма.

Адреса можно передавать в виде массивов или строк:

    [php]
    $address = 'fabien@example.com';
    $address = array('fabien@example.com' => 'Fabien Potencier');

Естественно, вы можете отправить письмо нескольким получателям, передав их адреса массивом во втором параметре метода:

    [php]
    $to = array(
      'foo@example.com',
      'bar@example.com',
    );
    $this->getMailer()->composeAndSend('from@example.com', $to, 'Subject', 'Body');

    $to = array(
      'foo@example.com' => 'Mr Foo',
      'bar@example.com' => 'Miss Bar',
    );
    $this->getMailer()->composeAndSend('from@example.com', $to, 'Subject', 'Body');

### Гибкий способ

Если вам требуется большая гибкость, для создания сообщения вы можете использовать метод ~`sfMailer::compose()`~, это позволит вам настроить его необходимым образом, а затем отправить.
Это может быть полезно, когда, например, вам требуется добавить к письму вложение:

    [php]
    // создание объекта сообщения
    $message = $this->getMailer()
      ->compose('from@example.com', 'fabien@example.com', 'Subject', 'Body')
      ->attach(Swift_Attachment::fromPath('/path/to/a/file.zip'))
    ;

    // отправка сообщения
    $this->getMailer()->send($message);

### Мощный способ

Также вы можете напрямую создать объект сообщения, что даст ещё большую гибкость:

    [php]
    $message = Swift_Message::newInstance()
      ->setFrom('from@example.com')
      ->setTo('to@example.com')
      ->setSubject('Subject')
      ->setBody('Body')
      ->attach(Swift_Attachment::fromPath('/path/to/a/file.zip'))
    ;

    $this->getMailer()->send($message);

>**TIP**
>Разделы официальной документации по Swift Mailer ["Creating Messages"](http://swiftmailer.org/docs/messages) (создание сообщений) и ["Message Headers"](http://swiftmailer.org/docs/headers) (заголовки сообщений) описывают всё, что вам необходимо знать о процессе создания сообщений.

### Использование Вида

Отправка электронных сообщений из действий позволяет вам легко использовать всю мощь компонентов.

    [php]
    $message->setBody($this->getPartial('partial_name', $arguments));

Конфигурация
------------

Как и другие фабрики в symfony, почта может быть сконфигурирована через файл `factories.yml`. По умолчанию, конфигурация выглядит следующим образом:

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

При создании нового приложения, локальный файл `factories.yml` приложения переопределяет некоторые заданные по умолчанию значения для окружений `prod`, `env` и `test`:

    [yml]
    test:
      mailer:
        param:
          delivery_strategy: none

    dev:
      mailer:
        param:
          delivery_strategy: none

Стратегия доставки
------------------

Одна из самых часто используемых опций почтового сервиса Swift Mailer в symfony - управление стратегией доставки.
Стратегия указывает symfony, как именно следует доставлять сообщения, и её можно задать через настройку ~`delivery_strategy`~ в `factories.yml`.
Стратегия изменяет поведение метода ~`sfMailer::send()`~. По умолчанию доступно четыре стратегии, которые подходят для всех общих случаев:

 * `realtime` - сообщения отправляются в реальном времени;
 * `single_address` - сообщения отправляются на один адрес;
 * `spool` - сообщения помещаются в очередь;
 * `none` - сообщения просто игнорируются.

### Стратегия ~`realtime`~

Стратегия `realtime` является стратегией по умолчанию, и не требует никаких специальных настроек.

Сообщения электронной почты отправляются через транспорт, указанный в разделе `transport` конфигурационного файла `factories.yml` (см. следующий раздел, для получения информации о конфигурировании почтового транспорта).

### Стратегия ~`single_address`~

При указании стратегии `single_address`, все сообщения посылаются на один адрес, заданный параметром `delivery_address`.

Эта стратегия очень полезна в отладочном окружении, она исключает отправку сообщений реальным пользователям, но всё ещё  позволяет разработчику проверять корректность вывода сообщений.

>**TIP**
>Если вам потребуется проверить оригинальных получателей `to`, `cc` и `bcc`, то они доступны через значения следующих заголовков: `X-Swift-To`, `X-Swift-Cc` и `X-Swift-Bcc`.

Сообщения электронной почты отправляются через тот же самый транспорт, который используется для стратегии `realtime`.

### Стратегия `spool`~

При указании стратегии `spool`, сообщения помещаются в очередь.

Это наилучшая стратегия для рабочего окружения, так как обработчик вэб-запросов не ждёт реальной отправки сообщений.

Класс для очереди задаётся параметром ~`spool_class`~. По умолчанию, symfony поставляется с тремя такими классами:

 * ~`Swift_FileSpool`~ - сообщения сохраняются в файловой системе.

 * ~`Swift_DoctrineSpool`~ - сообщения сохраняются в Doctrine-модели.

 * ~`Swift_PropelSpool`~ - сообщения сохранятся в Propel-модели.

Когда создаётся экземпляр очереди, опция ~`spool_arguments`~ используется для передачи аргументов в конструктор. Вот опции, которые используются для встроенных классов:

 * `Swift_FileSpool`:

    * Абсолютный путь к каталогу очереди (сообщения сохраняются в эту директорию)

 * `Swift_DoctrineSpool`:

    * Doctrine-модель, используемая для хранения сообщений (по умолчанию `MailMessage`)

    * Имя столбца для сохранения сообщения (по умолчанию `message`)

    * Метод, который будет использоваться для получения сообщения из очереди и его отправки (опционально). В качестве аргумента он получает опции очереди.

 * `Swift_PropelSpool`:

    * Propel-модель, используемая для хранения сообщений (по умолчанию `MailMessage`)

    * Имя столбца для сохранения сообщения (по умолчанию `message`)

    * Метод, который будет использоваться для получения сообщения из очереди и его отправки (опционально). В качестве аргумента он получает опции очереди.

Это классическая конфигурация для Doctrine-очереди:

    [yml]
    # схема в schema.yml
    MailMessage:
     actAs: { Timestampable: ~ }
     columns:
       message: { type: blob, notnull: true }

-

    [yml]
    # конфигурация в factories.yml
    mailer:
      class: sfMailer
      param:
        delivery_strategy: spool
        spool_class:       Swift_DoctrineSpool
        spool_arguments:   [ MailMessage, message, getSpooledMessages ]

И такая же конфигурация для Propel-очереди:

    [yml]
    # схема в schema.yml
    mail_message:
      message:    { type: blob, required: true }
      created_at: ~

-

    [yml]
    # конфигурация в factories.yml
    dev:
      mailer:
        param:
          delivery_strategy: spool
          spool_class:       Swift_PropelSpool
          spool_arguments:   [ MailMessage, message, getSpooledMessages ]

Для отправки сохранённых в очереди сообщений, вы можете использовать задачу ~`project:send-emails`~ (заметьте, эта задача полностью независима от реализации очереди и её опций):

    $ php symfony project:send-emails

>**NOTE**
>Для задачи `project:send-emails` можно указывать опции `application` и `env`.

Когда вызывается задача `project:send-emails`, сообщения электронной почты посылаются через тот же транспорт, который используется для стратегии `realtime`.

>**TIP**
>Заметьте, задача `project:send-emails` может быть запущена на любой машине, а не обязательно на той, на которой было создано сообщение.
>Это происходит из-за того, что все связанные с сообщением данные сохраняется в объекте сообщения (даже файловые вложения).

-

>**NOTE**
>Встроенные реализации очереди очень просты.
>Они посылают электронную почту без какой-либо обработки ошибок, также как если бы вы использовали стратегию `realtime`.
>Конечно, вы можете расширить классы очередей, чтобы реализовать свою собственную логику обработки ошибок.

Задача `project:send-emails` поддерживает две необязательные опции:

 * `message-limit` - ограничение на количество посылаемых сообщений.

 * `time-limit` - ограничение на время, затрачиваемое на отправку сообщений (в секундах).

Опции можно комбинировать:

  $ php symfony project:send-emails --message-limit=10 --time-limit=20

Эта команда прекратит посылать сообщения либо после отправки 10-го, либо через 20 секунд.

Даже если вы используете стратегию `spool`, вам может потребоваться отправить сообщение немедленно без его помещения в очередь.
Это можно реализовать через специальный метод `sendNextImmediately()`:

    [php]
    $this->getMailer()->sendNextImmediately()->send($message);

В данном примере, `$message` не запоминается в очереди и отправляется немедленно.
Как можно догадаться по имени метода, `sendNextImmediately()` воздействует только на следующее отправляемое сообщение.

>**NOTE**
>The `sendNextImmediately()` method has no special effect when the delivery strategy is not `spool`.

### Стратегия ~`none`~

Эта стратегия полезна в окружении разработки (отладочном), она позволяет отключить отправку сообщений реальным пользователям.
Сообщения всё ещё доступны через отладочную вэб-панель (больше информации об этом свойстве приведено в разделе, посвящённом взаимодействию с отладочной панелью).

Также эта стратегия является лучшей для тестового окружения, где объект `sfTesterMailer` позволяет вам исследовать сообщения без их отправки (больше информации об этом свойстве приведено в разделе, посвящённом тестированию).

Почтовый транспорт
------------------

Почтовые сообщения фактически посылает транспорт.
Транспорт настраивается через конфигурационный файл `factories.yml`, и конфигурация по умолчанию использует SMTP-сервер локальной машины:

    [yml]
    transport:
      class: Swift_SmtpTransport
      param:
        host:       localhost
        port:       25
        encryption: ~
        username:   ~
        password:   ~

Swift Mailer имеет три встроенных класса транспорта:

  * ~`Swift_SmtpTransport`~ - для отправки сообщений используется SMTP-сервер.

  * ~`Swift_SendmailTransport`~: для отправки сообщений используется `sendmail`.

  * ~`Swift_MailTransport`~ - для отправки сообщений используется PHP-функция `mail()`.

>**TIP**
>Глава ["Transport Types (Типы Транспортов)"](http://swiftmailer.org/docs/transport-types) официальной документации Swift Mailer описывает всё, что вам необходимо знать о встроенных классах транспорта и их параметрах.

Отправка электронной почты из задачи
------------------------------------

Отправка электронной почты из задачи подобна отправке из действия, так как система управления задачами поддерживает метод `getMailer()`.

При создании почтового объекта система управления задачами полагается на текущую конфигурацию.
Таким образом, если вы хотите использовать конфигурацию определённого приложения, то вам необходимо указывать опцию `--application` (см. главу про задачи для получения дополнительной информации по этой теме).

Заметьте, что задача использует ту же самую конфигурацию, что и контроллеры.
Так, если вы хотите форсировать отправку при использовании стратегии `spool`, используйте метод `sendNextImmediately()`:

    [php]
    $this->getMailer()->sendNextImmediately()->send($message);

Отладка
-------

Традиционно, отладка электронных писем была ночным кошмаром разработчика.
С symfony ситуация сильно упростилась, благодаря ~отладочной вэб-панели~.

В своём браузере вы быстро можете увидеть количество отправленных текущим действием сообщений:

![Электронная почта и отладочная вэб-панель](http://www.symfony-project.org/images/more-with-symfony/emails_wdt.png "Электронная почта в отладочной вэб-панели")

Если вы кликните по иконке с конвертом, отправленные сообщения отобразятся в панели.

![Электронная почта и отладочная вэб-панель - детали](http://www.symfony-project.org/images/more-with-symfony/emails_wdt_details.png "Детализация электронной почты в отладочной вэб-панели")

>**NOTE**
>Каждый раз при оправке сообщения, symfony также добавляет сообщение в лог.

Тестирование
------------

Конечно, интеграция была бы неполной без способа проверки почтовых сообщений. По умолчанию, для упрощения проверки почтовых сообщений в функциональных тестах symfony регистрирует тестер `mailer` (~`sfMailerTester`~).

Метод ~`hasSent()`~ проверяет количество сообщений, отправленных в ходе текущего запроса:

    [php]
    $browser->
      get('/foo')->
      with('mailer')->
        hasSent(1)
    ;

Этот код проверяет, что для URL `/foo` отправлено только одно сообщение.

Каждое отправленное электронное письмо может быть последовательно проверено с помощью методов ~`checkHeader()`~ и ~`checkBody()`~:

    [php]
    $browser->
      get('/foo')->
      with('mailer')->begin()->
        hasSent(1)->
        checkHeader('Subject', '/Subject/')->
        checkBody('/Body/')->
      end()
    ;

Вторым аргументом в `checkHeader()` и первым в `checkBody()` может быть одно из следующего:

 * строка для проверки полного соответствия;

 * регулярное выражение для проверки конкретных значений;

 * отрицательное регулярное выражение (регулярное выражение, которое начинается с символа `!`) для проверки значения на несоответствие.

По умолчанию, проверяется успешность отправки первого сообщения.
Если посылается несколько сообщений, вы можете выбрать конкретное сообщение с помощью метода ~`withMessage()`~:

    [php]
    $browser->
      get('/foo')->
      with('mailer')->begin()->
        hasSent(2)->
        withMessage('foo@example.com')->
        checkHeader('Subject', '/Subject/')->
        checkBody('/Body/')->
      end()
    ;

Метод `withMessage()` в качестве первого аргумента принимает адрес получателя.
Также этот метод принимает второй аргумент, указывающий номер сообщения в случае отправки нескольких сообщений одному и тому же получателю.

Последний, но не менее важный метод, ~`debug()`~, выводит сообщения для изучения при провале тестирования:

    [php]
    $browser->
      get('/foo')->
      with('mailer')->
      debug()
    ;

Сообщения электронной почты как классы
--------------------------------------

Во введении к этой главе вы узнали, как послать электронное письмо из действия.
Вероятно, это самый простой и лучший путь для отправки письма в symfony-приложении.

Но когда приложению необходимо управлять большим числом разных сообщений, вам потребуется использовать несколько иную стратегию.

>**NOTE**
>Как дополнительный бонус, использование классов для почтовых сообщений позволяет использовать одно и то же сообщение в разных приложениях, например во frontend и backend.

Поскольку сообщения - простые PHP-объекты, очевидный способ организовать их состоит в создании для них отдельных классов:

    [php]
    // lib/email/ProjectConfirmationMessage.class.php
    class ProjectConfirmationMessage extends Swift_Message
    {
      public function __construct()
      {
        parent::__construct('Subject', 'Body');

        $this
          ->setFrom(array('app@example.com' => 'My App Bot'))
          ->attach('...')
        ;
      }
    }

Отправка сообщения из действия или откуда-либо ещё, заключается в выборе правильного класса сообщения:

    [php]
    $this->getMailer()->send(new ProjectConfirmationMessage());

Естественно, удобно использовать некий базовый класс для управления общими заголовками (например, `From`) или добавления общей для всех сообщений подписи:

    [php]
    // lib/email/ProjectConfirmationMessage.class.php
    class ProjectConfirmationMessage extends ProjectBaseMessage
    {
      public function __construct()
      {
        parent::__construct('Subject', 'Body');

        // specific headers, attachments, ...
        $this->attach('...');
      }
    }

    // lib/email/ProjectBaseMessage.class.php
    class ProjectBaseMessage extends Swift_Message
    {
      public function __construct($subject, $body)
      {
        $body .= <<<EOF
    --

    Сообщение отправлено ботом My App
    EOF
        ;
        parent::__construct($subject, $body);

        // set all shared headers
        $this->setFrom(array('app@example.com' => 'Бот My App'));
      }
    }

Если сообщение зависит от объектов некоторой модели, вы можете передать их в качестве аргументов конструктора:

    [php]
    // lib/email/ProjectConfirmationMessage.class.php
    class ProjectConfirmationMessage extends ProjectBaseMessage
    {
      public function __construct($user)
      {
        parent::__construct('Подтверждение регистрации пользователя '.$user->getName(), 'Body');
      }
    }

Рецепты
-------

### Отправка почты через ~Gmail~

Если у вас нет собственного SMTP-сервера, но есть аккаунт на Gmail, использование следующей конфигурации позволит вам отправлять и архивировать почту через сервера Google:

    [yml]
    transport:
      class: Swift_SmtpTransport
      param:
        host:       smtp.gmail.com
        port:       465
        encryption: ssl
        username:   ваш_логин_на_gmail
        password:   ваш_пароль_на_gmail

Замените значения для `username` и `password` на ваши учётные данные Gmail.

### Настройка почтового объекта

Если вам недостаточны возможности конфигурирования почтового объекта через `factories.yml`, вы можете установить обработчик на событие ~`mailer.configure`~, и далее настроить почтовый объект в соответствии со своими требованиями.

Это событие можно соединить в своём классе `ProjectConfiguration` следующим образом:

    [php]
    class ProjectConfiguration extends sfProjectConfiguration
    {
      public function setup()
      {
        // ...

        $this->dispatcher->connect(
          'mailer.configure',
          array($this, 'configureMailer')
        );
      }

      public function configureMailer(sfEvent $event)
      {
        $mailer = $event->getSubject();

        // do something with the mailer
      }
    }

Следующий раздел демонстрирует всю мощь использования этой техники.

### Использование плагинов Swift Mailer

Для использования плагинов Swift Mailer, установите обработчик события `mailer.configure` (см. раздел выше):

    [php]
    public function configureMailer(sfEvent $event)
    {
      $mailer = $event->getSubject();

      $plugin = new Swift_Plugins_ThrottlerPlugin(
        100, Swift_Plugins_ThrottlerPlugin::MESSAGES_PER_MINUTE
      );

      $mailer->registerPlugin($plugin);
    }

>**TIP**
>Раздел ["Plugins (Плагины)"](http://swiftmailer.org/docs/plugins) официальной документации Swift Mailer описывает всё, что вам необходимо знать для использования встроенных плагинов.

### Настройка поведения очереди

Встроенные реализации очереди отправляемых сообщений предельно просты. Они получают все сообщения в произвольном порядке и отправляют их.

Вы можете настроить очередь отправляемых сообщений, ограничив затрачиваемое на отправку время, или ограничив общее число отправляемых сообщений:

    [php]
    $spool = $mailer->getSpool();

    $spool->setMessageLimit(10);
    $spool->setTimeLimit(10);

В этом разделе, вы научитесь реализовывать систему приоритетов для очереди.
Это даст вам необходимую информацию для реализации своей собственной логики.

Во-первых, добавим к схеме столбец `priority`:

    [yml]
    # для Propel
    mail_message:
      message:    { type: blob, required: true }
      created_at: ~
      priority:   { type: integer, default: 3 }

    # для Doctrine
    MailMessage:
      actAs: { Timestampable: ~ }
      columns:
        message:  { type: blob, notnull: true }
        priority: { type: integer }

Во-вторых, отправляя сообщение, установим заголовок приоритета (1 означает "важное"):

    [php]
    $message = $this->getMailer()
      ->compose('john@doe.com', 'foo@example.com', 'Subject', 'Body')
      ->setPriority(1)
    ;
    $this->getMailer()->send($message);

В-третьих, перепишем используемый по умолчанию метод `setMessage()` для изменения приоритета в самом объекте `MailMessage`:

    [php]
    // для Propel
    class MailMessage extends BaseMailMessage
    {
      public function setMessage($message)
      {
        $msg = unserialize($message);
        $this->setPriority($msg->getPriority());

        return parent::setMessage($message);
      }
    }

    // для Doctrine
    class MailMessage extends BaseMailMessage
    {
      public function setMessage($message)
      {
        $msg = unserialize($message);
        $this->priority = $msg->getPriority();

        return $this->_set('message', $message);
      }
    }

Заметьте, сообщение сериализуется очередью, поэтому перед получением значения приоритета необходимо выполнить десериализацию.
Теперь, создадим метод, который упорядочит сообщения по приоритетам:

    [php]
    // для Propel
    class MailMessagePeer extends BaseMailMessagePeer
    {
      static public function getSpooledMessages(Criteria $criteria)
      {
        $criteria->addAscendingOrderByColumn(self::PRIORITY);

        return self::doSelect($criteria);
      }

      // ...
    }

    // для Doctrine
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

Последним шагом станет определение метода получения сообщений в `factories.yml`, для изменения заданного по умолчанию пути извлечения сообщений из очереди:

    [yml]
    spool_arguments: [ MailMessage, message, getSpooledMessages ]

Вот и всё.
Теперь, при каждом запуске задачи `project:send-emails` сообщения будут посылаться в соответствии с их приоритетами.

>**SIDEBAR**
>Настройка очереди сообщений с помощью любого критерия
>
>В предыдущем примере использовался стандартный заголовок сообщения - приоритет.
>Однако если вы хотите использовать любой критерий, или если вы не хотите изменять исходное сообщение, вы можете сохранять свой критерий как свой собственный заголовок, который перед отправкой будете удалять.
>
>Добавьте свой собственный заголовок к отправляемому сообщению:
>
>     [php]
>     public function executeIndex()
>     {
>       $message = $this->getMailer()
>         ->compose('john@doe.com', 'foo@example.com', 'Subject', 'Body')
>       ;
>     
>       $message->getHeaders()->addTextHeader('X-Queue-Criteria', 'foo');
>     
>       $this->getMailer()->send($message);
>     }
>
>Затем при сортировке очереди получите значение этого заголовка и удалите его:
>
>     [php]
>     public function setMessage($message)
>     {
>       $msg = unserialize($message);
>     
>       $headers = $msg->getHeaders();
>       $criteria = $headers->get('X-Queue-Criteria')->getFieldBody();
>       $this->setCriteria($criteria);
>       $headers->remove('X-Queue-Criteria');
>     
>       return parent::_set('message', serialize($msg));
>     }
