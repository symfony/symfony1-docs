Emails
======

*por Fabien potencier*

Enviar ~e-mails~ com o symfony é simples e poderoso, graças ao uso
da biblioteca [Swift Mailer](http://www.swiftmailer.org/). Apesar da ~Swift Mailer~
tornar fácil o envio de e-mails, o symfony fornece um invólucro fino em cima dele para
fazer o envio de e-mails ainda mais flexível e poderoso. Este capítulo vai ensiná-lo
como ter todo o seu poder à sua disposição.

> **NOTE**
> symfony 1,3 embuti o Swift Mailer versão 4.1.

Introdução
------------

A gestão de e-mail no symfony é centrada em torno de um objeto *mailer*. E como muitos
outros objetos do núcleo do symfony, o *mailer* é uma fábrica (*factory*). Ela é configurada no
arquivo `factories.yml`, e sempre está disponível através do contexto
exemplo:

    [php]
    $mailer = sfContext::getInstance()->getMailer();

>**TIP**
>Ao contrário de outras fábricas (*factories*), o *mailer* é carregado e inicializado na demanda. Se
>você não usá-lo, não há qualquer impacto no desempenho.

Este tutorial explica integração a do Swift Mailer no symfony. Se você quiser
aprender os pequenos detalhes da biblioteca Swift em si, veja sua
[documentação](http://www.swiftmailer.org/docs) dedicada.

Enviar e-mails de uma action*
-----------------------------

Em uma *action*, recuperar a instância do Mailer é feita com o simples
método de atalho `getMailer()`:

    [php]
    $mailer = $this->getMailer();

### A forma mais rápida

Enviar um e-mail em seguida, é tão simples como usar o método ~`sfAction::composeAndSend()`~
:

    [php]
    $this-getMailer->()->composeAndSend(
      'from@example.com',
      'fabien@example.com',
      'Subject',
      'Body'
    );

O método `composeAndSend()` tem quatro argumentos:

 * O endereço de e-mail do remetente(`from`);
 * O endereço de e-mail do destinatário(s) (`to`);
 * O assunto da mensagem;
 * O corpo da mensagem.

Sempre que um método tem um endereço de email como um parâmetro, você pode passar uma string
ou uma matriz:

    [php]
    $address = 'fabien@example.com';
    $address = array('fabien@example.com' => 'Fabien potencier');

Claro, você pode enviar um e-mail para várias pessoas ao mesmo tempo, passando uma matriz
de e-mails como o segundo argumento do método:

    [php]
    $to = array (
      'foo@example.com',
      'bar@example.com',
    );
    $this->getMailer->()->composeAndSend('from@example.com', $to, 'Assunto', 'Corpo');

    $to = array (
      'foo@example.com' => 'Sr. Foo',
      'bar@example.com' => 'Miss Bar',
    );
    $this->getMailer->()->composeAndSend('from@example.com', $to, 'Assunto', 'Corpo');

### O Jeito Flexível

Se precisar de mais flexibilidade, você também pode usar o metodo ~`sfAction::compose()`~
para criar uma mensagem, personalizá-la do jeito que você quiser, e eventualmente enviá-la.
Isso é útil, por exemplo, quando você precisa adicionar um
~attachment|e-mail attachment~ (anexo) como mostrado abaixo:

    [php]
    // Cria um objeto de mensagem
    $message = $this->getMailer()
      ->compose('from@example.com','fabien@example.com','Assunto','Body')
      ->attach(Swift_Attachment::fromPath('/caminho/para/um/file.zip/'))
    ;

    // Enviar a mensagem
    $this->getMailer>()->send($message);

### O Jeito Poderoso

Você também pode criar um objeto mensagem diretamente para uma flexibilidade ainda maior:

    [php]
    $mensagem = Swift_Message:: newInstance()
      ->setFrom('from@example.com')
      ->setTo('to@example.com')
      ->setSubject('Assunto')
      ->setBody('Corpo')
      ->attach(Swift_Attachment::fromPath('caminho/para/um/arquivo.zip'))
    ;

    $this->getMailer()->send($message);

>**TIP**
>A ["Criando mensagens"](http://swiftmailer.org/docs/messages) e
>["Cabeçalho de mensagem"](http://swiftmailer.org/docs/headers) secções da
>documentação oficial do Swift Mailer descreve tudo o que você precisa saber sobre
>criar mensagens.

### Usando a Visualização do Symony (*Symfony View*)

Enviar e-mails de suas *actions* permite aproveitar o poder das
parciais (*partials*) e componentes (*components*) com bastante facilidade.

    [php]
    $mensagem->setBody($this->getPartial('nome_da_pa', $ arguments));

Configuração
-------------

Como qualquer outra fábrica (*factory*) do symfony, o *mailer* pode ser configurada no
arquivo de configuração `factories.yml`. A configuração padrão passa a ter o
seguinte:

    [yml]
    mailer:
      classe: sfMailer
      param:
        logging: %SF_LOGGING_ENABLED%
        charset: %SF_CHARSET%
        delivery_strategy: realtime
        transport:
          classe: Swift_SmtpTransport
          param:
            host: localhost
            port: 25
            encryption: ~
            username: ~
            password: ~

Ao criar um novo aplicativo, o arquivo de configuração `factories.yml` local
substitui a configuração padrão com alguns padrões sensíveis para os
ambientes `prod`, `env` e `test`:

    [yml]
    test:
      mailer:
        param:
          delivery_strategy: none

    dev:
      mailer:
        param:
          delivery_strategy: none

A Estratégia de Entrega
---------------------

Uma das características mais úteis da integração do Swift em symfony é
a estratégia de entrega. A estratégia de entrega permite-lhe dizer como o symfony
entregará as mensagens de e-mail e é configurado através da configuração ~`delivery_strategy`~
do `factories.yml`. A estratégia muda a forma como o
método ~`send()`|`sfMailer::send()`~ comporta. Quatro estratégias estão disponíveis por
padrão, que deverá atender todas as necessidades em comum:

 * `realtime`: As mensagens são enviadas em tempo real.
 * `single_address`: As mensagens são enviadas para um único endereço.
 * `spool`: As mensagens são armazenadas em uma fila.
 * `none`: As mensagens são simplesmente ignoradas.

### A Estratégia ~`realtime`~

A estratégia `realtime`, é a estratégia de entrega padrão, e mais fácil de
configurar, como não há nada especial a fazer.

Mensagens são enviadas através do transporte configurado na seção `transport`
do arquivo de configuração `factories.yml` (veja a próxima seção para
mais informações sobre como configurar o transporte de correio (*mail*)).

### A Estratégia ~`single_address`~

Com a estratégia `single_address`, todas as mensagens são enviadas para um único endereço,
configurado através da definição `delivery_address`.

Esta estratégia é muito útil no ambiente de desenvolvimento para evitar o envio de
mensagens para os usuários reais, mas ainda permitem que o desenvolvedor verificar a renderização da
mensagem em um leitor de e-mail.

>**TIP**
>Se você precisar verificar os destinatários `to`, `cc` e `bcc` originais, que são
>disponíveis, como valores dos cabeçalhos que se segue: `X-Swift-To`, `X-Swift-Cc`, e
>`X-Swift-BCC`, respectivamente.

As mensagens são enviadas através do mesmo transporte de e-mail utilizado para a
estratégia `realtime`.

### A Estratégia ~`spool`~

Com a estratégia ~`spool`~, as mensagens são armazenadas em uma fila.

Esta é a melhor estratégia para o ambiente de produção, fazendo com que as requisções web
não esperem os e-mails para serem executadas.

A classe `spool` está configurada com a definição ~`spool_class`~. Por padrão,
symfony já vem com três delas:

 * ~`Swift_FileSpool`~: As mensagens são armazenadas no sistema de arquivos.

 * ~`Swift_DoctrineSpool`~: As mensagens são armazenadas em um modelo do Doctrine.

 * ~`Swift_PropelSpool`~: As mensagens são armazenadas em um modelo do Propel.

Quando o *spool* é instanciado, a definição ~`spool_arguments`~ é utilizada como
argumentos do construtor. Aqui estão as opções disponíveis para a classe de filas *built-in*


 * `Swift_FileSpool`:

    * O caminho absoluto do diretório de fila (as mensagens são armazenadas
      neste diretório)

 * `Swift_DoctrineSpool`:

    * O modelo do Doctrine a ser usado para armazenar as mensagens (`MailMessage` por
      padrão)

    * O nome da coluna a ser usada para o armazenamento das mensagens (`message` por padrão)

    * O método a ser chamado para recuperar as mensagens a ser enviada (opcional). Ele
      recebe as opções da fila como um argumento.

 * `Swift_PropelSpool`:

    * O modelo do Propel a ser usado para armazenar as mensagens (`MailMessage` por padrão)

    * O nome da coluna a ser usada para armazenamento das mensagens (`message` por padrão)

    * O método a ser chamado para recuperar as mensagens a serem enviadas (opcional). Ele
      recebe as opções da fila como um argumento.

Aqui está uma configuração clássica para um spoll do Doctrine:

    [yml]
    # Configuração do esquema no schema.yml
    MailMessage:
     actAs: {Timestampable: ~}
     columns:
       Mensagem: {type: clob, notnull: true}

-

    [yml]
    # Configuração em factories.yml
    Mailer:
      class: sfMailer
      param:
        delivery_strategy: spool
        spool_class: Swift_DoctrineSpool
        spool_arguments: [MailMessage, message, getSpooledMessages]

E a mesma configuração de um spool do Propel:

    [yml]
    # Configuração do esquema no schema.yml
    mail_message:
      message {type: clob, exigidos: true}
      created_at: ~

-

    [yml]
    # Configuração em factories.yml
    dev:
      Mailer:
        param:
          delivery_strategy: spool
          spool_class: Swift_PropelSpool
          spool_arguments: [MailMessage, mensagem, getSpooledMessages]

Para enviar a mensagem armazenada em uma fila, você pode usar a tarefa (*task*) ~`project:send-emails`~
Observação (note que essa tarefa é totalmente independente da implementação da fila,
e as opções que toma):

    $ php symfony project:send-emails

>**NOTE**
>A tarefa (*task*) `project:send-emails` recebe as opções `application` e `env`

Ao chamar a tarefa (*task*) `project:send-emails`, e-mails são enviados através do
mesmo transporte que é utilizado para a estratégia `realtime`.

>**TIP**
>Note que a tarefa (*task*) `project:send-emails` pode ser executada em qualquer máquina, não
>necessariamente na mesma máquina que criou a mensagem. Ele funciona porque
>tudo é armazenado no objeto da mensagem, até mesmo os anexos de arquivo.

-

>**NOTE**
>A implementação *built-in* das filas são muito simples. Eles enviam e-mails
>sem qualquer controle de erro, como eles teriam sido enviados se você tivesse usado
>a estratégia `realtime`. Obviamente, as classes de fila padrão pode ser estendidas
>para implementar sua própria lógica e gerenciamento de erros.

A tarefa (*task*) `project:send-emails` recebe duas opções opcionais:

 *  `message-limit`: Limita o número de mensagens enviadas.

 *  `time-limit`: Limita o tempo gasto para enviar mensagens (em segundos).

Ambas as opções podem ser combinadas:

  $ php symfony project:send-emails -message-limit=10 --time-limit=20

O comando acima irá parar de enviar mensagens quando 10 mensagens forem enviadas ou
após 20 segundos.

Mesmo quando se utiliza a estratégia `spool`, talvez você precise enviar uma mensagem
imediatamente, sem armazená-lo na fila. Isso é possível usando o
método especial `sendNextImmediately()` do *mailer*:

    [php]
    $this->getMailer()->sendNextImmediately()->send($mensagem);

No exemplo anterior, a `$mensagem` não será armazenada na fila e vai
ser enviado imediatamente. Como o próprio nome sugere, o método `sendNextImmediately()`
afeta somente a mensagem muito próxima a ser enviada.

>**NOTE**
>O método `sendNextImmediately()` não tem efeito especial, quando a
>estratégia de envio não é `spool`.

### A Estratégia ~`none`~

Esta estratégia é útil no ambiente de desenvolvimento para evitar e-mails sejam
enviados para os usuários reais. Mensagens ainda estão disponíveis na ferramentas web de depuração
(mais informações na seção abaixo sobre o painel da ferramenta de depuração do
*mailer*).

É também a melhor estratégia para o ambiente de teste, onde o
objeto `sfTesterMailer permite a introspecção das mensagens sem a necessidade
realmente a enviá-las (mais informações na seção abaixo sobre tested).

O transporte de email
------------------

Mensagens de correio são realmente enviadas por um transporte. O transporte está configurado no
o arquivo de configuração `factories.yml`, e a configuração padrão utiliza
o servidor SMTP da máquina local:

    [yml]
    transport:
      class: Swift_SmtpTransport
      param:
        host: localhost
        port: 25
        encryption: ~
        username: ~
        password: ~

Swift Mailer vem com três classes de transporte diferentes:

  * ~`Swift_SmtpTransport`~: Usa um servidor SMTP para enviar mensagens.

  * ~`Swift_SendmailTransport`~: Usa `sendmail` para enviar mensagens.

  * ~`Swift_MailTransport`~: Usa a função `mail()` nativa do PHP para enviar
    as mensagens

>**TIP**
>A seção ["Tipos de Transportes"](http://swiftmailer.org/docs/transport-types) da
>documentação oficial do Swift Mailer descreve tudo o que você precisa saber
>sobre as classes built-in de transporte e seus diferentes parâmetros.

Enviando um E-mail Através de uma Tarefa (*task*)
----------------------------

Enviando um e-mail através de uma tarefa é bastante semelhante ao envio de um e-mail em uma
*action*, como o sistema de tarefas também oferece um método `getMailer()` método.

Ao criar o *mailer*, o sistema de tarefa (*task*) depende da configuração atual.
Assim, se você quiser usar uma configuração de uma aplicação específica, você deve
passar a opção `--application` (veja no capítulo sobre tarefas, mais
informações sobre esse assunto).

Observe que a tarefa usa a mesma configuração dos controladores (*controllers*). Assim, se
deseja forçar a entrega, quando a estratégia `spool` é usada, use
`sendNextImmediately()`:

    [php]
    $this->getMailer()->sendNextImmediately()->send($mensagem);

Depuração
---------

Tradicionalmente, a depuração de e-mails tem sido um pesadelo. Com o symfony, é muito
fácil, graças à ~web debug toolbar~ (Barra de ferramenta Web de depuração).

A partir do conforto do seu navegador, você pode facilmente e rapidamente ver quantas
mensagens foram enviadas pela *action* atual:

![E-mails na Web Toolbar Debug](http://www.symfony-project.org/images/more-with-symfony/emails_wdt.png "e-mails no Debug Web Toolbar")

Se você clicar no ícone e-mail, as mensagens enviadas são exibidas no painel
em sua forma bruta, conforme mostrado abaixo.

![E-mails no Debug Web Toolbar - detalhes](http://www.symfony-project.org/images/more-with-symfony/emails_wdt_details.png "e-mails no Debug Web Toolbar - Detalhes")

>**NOTE**
>Cada vez que uma mensagem é enviada, symfony também adiciona uma mensagem no log.

Testando
-------

Naturalmente, a integração não seria completa sem uma maneira de testar
as mensagens de correio eletrônico. Por padrão, o symfony registra um `mailer` de teste
(~`sfMailerTester`~) para facilitar o correio de teste em testes funcionais.

O método ~`(hasSent)`~ testa o número de mensagens enviadas durante o requisição
corrente:

    [php]
    $browser->
      get('/foo')->
      with('mailer')->
        hasSent(1)
    ;

O código anterior verifica se a URL `/foo` envia apenas um e-mail.

Cada e-mail enviado ainda pode ser testado com a ajuda dos métodos ~`(checkHeader)` ~
e ~`checkBody()`~ :

    [php]
    $browser->
      get('/foo')->
      with('mailer')->begin()->
        hasSent(1)->
        checkHeader('Subject', '/Subject/')->
        checkBody('/Body/')->
      end()
    ;

O segundo argumento do `checkHeader()` e o primeiro argumento do `checkBody()`
pode ser um dos seguintes procedimentos:

 * Uma *string* para buscar uma correspondência exata;

 * Uma expressão regular para verificar o valor contra ele;

 * Uma expressão negativa regular (uma expressão regular a partir de um `!`)) Para
   verificar que o valor não corresponde.

Por padrão, as verificações são feitas com a primeira mensagem enviada. Se várias mensagens
tenham sido enviadas, você pode escolher o que você quiser testar com o
método ~`withMessage()`~:

    [php]
    $browser->
      get('/foo')->
      with('mailer')->begin()->
        hasSent(2)->
        withMessage('foo@example.com')->
        checkHeader('Assunto', '/Assunto /')->
        checkBody('/Corpo/')->
      end()
    ;

O `withMessage()` tem um destinatário como seu primeiro argumento. Tem igualmente um
segundo argumento para indicar qual a mensagem que você deseja testar se os vários
tenham sido enviados para o mesmo destinatário.

Por último mas não menos importante, o método ~`debug()`~ descarrega as mensagens enviadas para local de
problemas quando um teste falhar:

    [php]
    $browser->
      get('/foo')->
      com('mailer')->
      debug()
    ;

Mensagens de e-mail como Classes
-------------------------

Na introdução deste capítulo, você aprendeu como enviar e-mails a partir de uma
*action*. Esta é provavelmente a maneira mais fácil de enviar e-mails em uma aplicação symfony
e, provavelmente, o melhor quando você só precisa enviar umas simples e poucas
mensagens

Mas quando seu aplicativo precisa gerir um grande número de diferentes mensagens de e-mail
, você provavelmente deve ter uma estratégia diferente.

>**NOTE**
>Como um bônus adicionado, utilizar classes para mensagens de email significa que a mesma mensagem de e-mail
>pode ser utilizada em diferentes aplicações; uma *frontend* e uma *backend* por
> exemplo.

As mensagens são objetos simples do PHP, a maneira mais óbvia para organizar suas mensagens
é criar uma classe para cada uma delas:

    [php]
    // lib/email/ProjectConfirmationMessage.class.php
    class ProjectConfirmationMessage extends Swift_Message
    {
      public function __construct()
      (
        parent::__construct('Assunto', 'Corpo');

        $this
          ->setFrom(array('app@example.com' => 'Minha aplicação Robô'))
          ->attach('...')
        ;
      }
    }

Enviar uma mensagem através de uma *action*, ou de qualquer outro lugar para essa matéria, é
uma simples questão de instanciar a classe certa da mensagem:

    [php]
    $this->getMailer()->send(new ProjectConfirmationMessage());

Claro, adicionando uma classe base para centralizar os cabeçalhos compartilhados como o
cabeçalho `From`, ou para adicionar uma assinatura comum pode ser conveniente:

    [php]
    // lib/email/ProjectConfirmationMessage.class.php
    class ProjectConfirmationMessage extend ProjectBaseMessage
    {
      public function __construct()
      {
        parent::__construct('Assunto', 'Corpo');

        // Cabeçalhos específicos, anexos ...
        $this->attach('...');
      }
    }

    // lib/email/ProjectConfirmationMessage.class.php
    class ProjectBaseMessage extends Swift_Message
    {
      public function __construct($assunto, $corpo)
      {
        $corpo.= <<<EOF
    --

    E-mail enviado pela minha aplicação Robô
    EOF
        ;
        parent::__construct($assunto, $corpo);

        // Definir todos os cabeçalhos compartilhados
        $this->setFrom(array('app@example.com' => 'Minha aplicação Robô'));
      }
    }

Se uma mensagem depende de alguns objetos de modelo, você pode, naturalmente, passá-las como
argumentos para o construtor:

    [php]
    // lib/email/ProjectConfirmationMessage.class.php
    class ProjectConfirmationMessage extends ProjectBaseMessage
    {
      public function __construct ($usuario)
      {
        parent::__construct('Confirmação para'. $usuario->getNome(), 'Corpo');
      }
    }

Receitas
-------

### Enviar e-mails através do ~Gmail~

Se você não tiver um servidor SMTP, mas tem uma conta do Gmail, use a seguinte
configuração para usar os servidores do Google para enviar e arquivar mensagens:

    [yml]
    transport:
      class: Swift_SmtpTransport
      param:
        host: smtp.gmail.com
        port: 465
        encryption: SSL
        username: seu_usuario_do_gmail_vem_aqui
        password: seu_senha_do_gmail_vem_aqui

Substituir o `username` e `passwrod` com as suas credenciais do Gmail e você está
pronto.

### Personalizando o Objeto *Mailer*

Se configurar o *mailer* através do `factories.yml` não for o suficiente, você pode
ouvir o evento ~`mailer.configure`~ evento, e personalizar ainda mais o *mailer*.

Você pode se conectar a esse evento em sua classe `ProjectConfiguration` como mostrado
abaixo:

    [php]
    class ProjectConfiguration extends sfProjectConfiguration
    {
      public function setup()
      {
        / / ...

        $this->dispatcher->connect(
          'mailer.configure',
          array($this, 'configureMailer')
        );
      }

      public function configureMailer(sfEvent $event)
      {
        $mailer = $event->getSubject();

        // Fazer algo com o mailer
      }
    }

A seção a seguir ilustra um uso desta poderosa técnica.

### Usando plugins do ~Swift Mailer~

Para usar os plugins do Swift Mailer, ouvir o evento `mailer.configure` (ver a
seção acima):

    [php]
    public function configureMailer(sfEvent $event)
    {
      $mailer = $event-> getSubject();

      $plugin = new Swift_Plugins_ThrottlerPlugin(
        100, Swift_Plugins_ThrottlerPlugin::MESSAGES_PER_MINUTE
      );

      $mailer->registerPlugin($plugin);
    }

>**TIP**
>A seção ["Plugins"](http://swiftmailer.org/docs/plugins) da
>documentação oficial do Swift Mailer descreve tudo o que você precisa saber sobre os
>plugins embutidos.

### Personalizar o Comportamento de Carretel (*Spool Behavior*)

A implementação *built-in* dos *spools* é muito simples. Cada *spool* recupera
todos os e-mails da fila em uma ordem aleatória e os enviam.

Você pode configurar um spool para limitar o tempo gasto para enviar e-mails (em segundos),
ou para limitar o número de mensagens a enviar:

    [php]
    $spool = $mailer->getSpool();

    $spool->setMessageLimit(10);
    $spool->setTimeLimit(10);

Nesta seção, você vai aprender como implementar um sistema de prioridade para a
fila. Ele lhe dará todas as informações necessárias para implementar sua própria
lógica.

Primeiro, adicione uma coluna `prioridade` para o esquema:

    [yml]
    # para Propel
    mail_message:
      mensagem: { type: clob, required: true }
      created_at: ~
      prioridade: { type: integer, default: 3 }

    # para Doctrine
    MailMessage:
      actAs: { Timestampable: ~ }
      columns:
        mensagem: { type: clob, notnull: true }
        prioridade: { type: integer }

Ao enviar um e-mail, defina o cabeçalho de prioridade (1 significa mais alto):

    [php]
    $mensagem = $this->getMailer()
      ->compose('john@doe.com', 'foo@example.com', 'Assunto', 'Body')
      ->setPriority(1)
    ;
    $this->getMailer()->send($mensagem);

Em seguida, substitua o método padrão `setMessage()` para alterar a prioridade do
objeto `MailMessage` em si:

    [php]
    // para Propel
    class MailMessage extends BaseMailMessage
    {
      public function setMensagem($mensagem)
      {
        $msg = unserialize($mensagem);
        $this->setPrioridade($msg->getPrioridade());

        parent::setMensagem($mensagem);
      }
    }

    // para a Doctrine
    class MailMessage extends BaseMailMessage
    {
      public function setMensagem($mensagem)
      {
        $msg = unserialize($mensagem);
        $this->prioridade = $msg->getPrioridade();

        $this->_set('mensagem', $mensagem);
      }
    }

Observe que a mensagem é serializada pela fila, por isso tem que ser desserializados
antes de obter o valor de prioridade. Agora, criar um método que ordena a
mensagens por prioridade:

    [php]
    // para Propel
    class MailMessagePeer extends BaseMailMessagePeer
    {
      static public function getSpooledMessages(Criteria $criteria)
      {
        $criteria->addAscendingOrderByColumn(self::PRIORIDADE);

        return self::doSelect($criteria);
      }

      // ...
    }

    // para a Doctrine
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

O último passo é definir o método de recuperação no `factories.yml`
a configuração para alterar a forma padrão no qual as mensagens são obtidas
da fila:

    [yml]
    spool_arguments: [MailMessage, message, getSpooledMessages]

E isto resume todo o processo. Agora, cada vez que você executar a tarefa (*task*) `project:send-mails`
, cada e-mail será enviado de acordo com a sua prioridade.

>**SIDEBAR**
>Personalizando o *Spool* com qualquer Criteria
>
>O exemplo anterior utiliza um cabeçalho de mensagem padrão, a prioridade. Mas se você
>deseja usar qualquer critério, ou se você não quiser alterar a mensagem enviada,
>você também pode armazenar os critérios como um cabeçalho personalizado, e removê-lo antes
>enviar o e-mail.
>
>Primeiro, adicione um cabeçalho personalizado para a mensagem a ser enviada:
>
>     [php]
>     public function executeIndex()
>     {
>       $mensagem = $this->getMailer()
>         ->compose('john@doe.com', 'foo@example.com', 'Assunto', 'Corpo')
>       ;
>     
>       $message->getHeaders()->addTextHeader('X-Queue-Criteria', 'foo');
>     
>       $this->getMailer()->send($mensagem);
>     }
>
>Em seguida, recuperar o valor deste cabeçalho ao armazenar a mensagem na
>fila, e removê-lo imediatamente:
>
>     [php]
>     public function setMensagem($mensagem)
>     {
>       $msg = unserialize($mensagem);
>     
>       $headers = $msg->getHeaders();
>       $criteria = $headers->get('X-Queue-Criteria')->getFieldBody();
>       $this->setCriteria($criteria);
>       $headers->remove('X-Queue-Criteria');
>    
>       parent::setMessage($mensagem);
>     }
