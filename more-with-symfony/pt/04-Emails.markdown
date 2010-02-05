Emails
======

*por Fabien Potencier*

O envio de ~e-mails~ com o symfony é simples e poderoso, graças ao uso
da biblioteca [Swift Mailer](http://www.swiftmailer.org/). Apesar do ~Swift Mailer~
tornar fácil o envio de e-mails, o symfony fornece uma camada adicional acima dele para
tornar o envio de e-mails ainda mais flexível e poderoso. Este capítulo vai ensiná-lo
como ter todo o seu poder à sua disposição.

>**NOTE**
>O symfony 1.3 vem com o Swift Mailer versão 4.1.

Introdução
------------

A gestão de e-mails no symfony é centrada no objeto *mailer*. E, como muitos
outros objetos do núcleo do symfony, o *mailer* é uma *factory*. Ele é configurado no
arquivo `factories.yml`, e está sempre disponível através da instância do contexto (*context*):

    [php]
    $mailer = sfContext::getInstance()->getMailer();

>**TIP**
>Ao contrário de outras *factories*, o *mailer* é carregado e inicializado por demanda. Se
>você não utilizá-lo, não há qualquer impacto no desempenho.

Este tutorial explica a integração do *Swift Mailer* no symfony. Se você quiser
aprender os pequenos detalhes da biblioteca *Swift Mailer*, veja a
[documentação](http://www.swiftmailer.org/docs) dedicada.

Enviando e-mails a partir de uma ação
-----------------------------

Em uma ação, é simples recuperar a instância do *mailer* 
utilizando o método atalho `getMailer()`:

    [php]
    $mailer = $this->getMailer();

### A forma mais rápida

Enviar um e-mail é tão simples como usar o método ~`sfAction::composeAndSend()`~:

    [php]
    $this-getMailer->()->composeAndSend(
      'from@example.com',
      'fabien@example.com',
      'Subject',
      'Body'
    );

O método `composeAndSend()` possui quatro argumentos:

 * O endereço de e-mail do remetente(`from`);
 * O endereço de e-mail do destinatário(s) (`to`);
 * O assunto da mensagem;
 * O corpo da mensagem.

Sempre que um método tem um endereço de email como parâmetro, você pode passar uma string
ou um array:

    [php]
    $address = 'fabien@example.com';
    $address = array('fabien@example.com' => 'Fabien potencier');

Claro, você pode enviar um e-mail para várias pessoas ao mesmo tempo, passando um array
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

### A forma Flexível

Se precisar de mais flexibilidade, você também pode usar o método ~`sfAction::compose()`~
para criar uma mensagem, personalizá-la do jeito que você quiser, e eventualmente enviá-la.
Isso é útil, por exemplo, quando você precisa adicionar um
anexo (*~attachment|e-mail attachment~*) como mostrado abaixo:

    [php]
    // Cria um objeto de mensagem
    $mensagem = $this->getMailer()
      ->compose('from@example.com','fabien@example.com','Assunto','Corpo')
      ->attach(Swift_Attachment::fromPath('/caminho/para/um/arquivo.zip/'))
    ;

    // Envia a mensagem
    $this->getMailer()->send($mensagem);

### A Forma Poderosa

Você também pode criar um objeto de mensagem diretamente, para uma flexibilidade ainda maior:

    [php]
    $mensagem = Swift_Message:: newInstance()
      ->setFrom('from@example.com')
      ->setTo('to@example.com')
      ->setSubject('Assunto')
      ->setBody('Corpo')
      ->attach(Swift_Attachment::fromPath('caminho/para/um/arquivo.zip'))
    ;

    $this->getMailer()->send($mensagem);

>**TIP**
>As sessões ["Criando mensagens"](http://swiftmailer.org/docs/messages) e
>["Cabeçalhos das mensagens"](http://swiftmailer.org/docs/headers) da
>documentação oficial do *Swift Mailer* descrevem tudo o que você precisa saber sobre
>a criação de mensagens.

### Usando a camada de Visão do Symfony (*Symfony View*)

Enviar e-mails de suas ações permite aproveitar o poder das
parciais (*partials*) e componentes (*components*) com bastante facilidade.

    [php]
    $mensagem->setBody($this->getPartial('partial_name', $arguments));

Configuração
-------------

Como qualquer outra *factory* do symfony, o *mailer* pode ser configurado no
arquivo de configuração `factories.yml`. A configuração padrão é a 
seguinte:

    [yml]
    mailer:
      class: sfMailer
      param:
        logging: %SF_LOGGING_ENABLED%
        charset: %SF_CHARSET%
        delivery_strategy: realtime
        transport:
          class: Swift_SmtpTransport
          param:
            host: localhost
            port: 25
            encryption: ~
            username: ~
            password: ~

Ao criar uma nova aplicação, o arquivo local de configuração `factories.yml` 
substitui a configuração padrão com alguns padrões sensíveis aos
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

Uma das características mais úteis da integração do *Swift Mailer* no symfony é
a estratégia de entrega. A estratégia de entrega permite-lhe dizer como o symfony
entregará as mensagens de e-mail e, é configurado através da configuração ~`delivery_strategy`~
no `factories.yml`. A estratégia muda a forma do comportamento do 
método ~`send()`|`sfMailer::send()`~. Por padrão, quatro estratégias estão disponíveis,
que deverá atender todas as necessidades comuns:

 * `realtime`: As mensagens são enviadas em tempo real.
 * `single_address`: As mensagens são enviadas para um único endereço.
 * `spool`: As mensagens são armazenadas em uma fila (*queue*).
 * `none`: As mensagens são simplesmente ignoradas.

### A Estratégia ~`realtime`~

A estratégia `realtime` é a estratégia de entrega padrão, e mais fácil de
configurar, pois não há nada de especial a fazer.

As mensagens são enviadas através do transporte configurado na seção `transport`
do arquivo de configuração `factories.yml` (veja a próxima seção para
mais informações sobre como configurar o transporte de correio (*mail*)).

### A Estratégia ~`single_address`~

Com a estratégia `single_address`, todas as mensagens são enviadas para um único endereço,
configurado através da definição `delivery_address`.

Esta estratégia é muito útil no ambiente de desenvolvimento para evitar o envio de
mensagens para os usuários reais, mas ainda permite ao desenvolvedor verificar a renderização da
mensagem em um leitor de e-mail.

>**TIP**
>Se você precisar verificar os destinatários `to`, `cc` e `bcc` originais, eles estão
>disponíveis como valores dos seguintes cabeçalhos (*headers*): `X-Swift-To`, `X-Swift-Cc` e
>`X-Swift-BCC`, respectivamente.

As mensagens são enviadas através do mesmo transporte de e-mail utilizado para a
estratégia `realtime`.

### A Estratégia ~`spool`~

Com a estratégia ~`spool`~, as mensagens são armazenadas em uma fila (*queue*).

Esta é a melhor estratégia para o ambiente de produção, pois as requisções web
não precisam esperar os e-mails serem enviados.

A classe `spool` é configurada com a configuração ~`spool_class`~. Por padrão,
o symfony já vem com três delas:

 * ~`Swift_FileSpool`~: As mensagens são armazenadas no sistema de arquivos.

 * ~`Swift_DoctrineSpool`~: As mensagens são armazenadas em um modelo do Doctrine.

 * ~`Swift_PropelSpool`~: As mensagens são armazenadas em um modelo do Propel.

Quando o *spool* é instanciado, a configuração ~`spool_arguments`~ é utilizada com
os argumentos do construtor. Aqui estão as opções disponíveis para a classe de filas integradas:


 * `Swift_FileSpool`:

    * O caminho absoluto do diretório de fila (as mensagens são armazenadas
      neste diretório)

 * `Swift_DoctrineSpool`:

    * O modelo do Doctrine que será usado para armazenar as mensagens (`MailMessage` por
      padrão)

    * O nome da coluna que será usada para o armazenamento das mensagens (`message` por padrão)

    * O método a ser chamado para recuperar as mensagens a serem enviadas (opcional). Ele
      recebe as opções da fila como argumento.

 * `Swift_PropelSpool`:

    * O modelo do Propel que será usado para armazenar as mensagens (`MailMessage` por padrão)

    * O nome da coluna que será usada para armazenamento das mensagens (`message` por padrão)

    * O método a ser chamado para recuperar as mensagens a serem enviadas (opcional). Ele
      recebe as opções da fila como argumento.

Aqui está uma configuração clássica para um *spool* do Doctrine:

    [yml]
    # Configuração do esquema no schema.yml
    MailMessage:
     actAs: {Timestampable: ~}
     columns:
       message: {type: clob, notnull: true}

-

    [yml]
    # Configuração no factories.yml
    mailer:
      class: sfMailer
      param:
        delivery_strategy: spool
        spool_class: Swift_DoctrineSpool
        spool_arguments: [MailMessage, message, getSpooledMessages]

E a mesma configuração para um *spool* do Propel:

    [yml]
    # Configuração do esquema no schema.yml
    mail_message:
      message {type: clob, required: true}
      created_at: ~

-

    [yml]
    # Configuração no factories.yml
    dev:
      mailer:
        param:
          delivery_strategy: spool
          spool_class: Swift_PropelSpool
          spool_arguments: [MailMessage, message, getSpooledMessages]

Para enviar a mensagem armazenada em uma fila (*queue*), você pode usar a tarefa ~`project:send-emails`~
(note que essa tarefa é totalmente independente da implementação da fila (*queue*),
e as suas opções):

    $ php symfony project:send-emails

>**NOTE**
>A tarefa `project:send-emails` recebe as opções `application` e `env`

Ao chamar a tarefa `project:send-emails`,as mensagens de e-mail são enviadas através do
mesmo transporte que é utilizado para a estratégia `realtime`.

>**TIP**
>Note que a tarefa `project:send-emails` pode ser executada em qualquer máquina, não
>necessariamente na mesma máquina que criou a mensagem. Ela funciona porque
>tudo é armazenado no objeto da mensagem, até mesmo os anexos de arquivo.

-

>**NOTE**
>A implementação integrada das filas é muito simples. Elas enviam e-mails
>sem qualquer controle de erro, exatamente como elas teriam sido enviadas se você tivesse usado
>a estratégia `realtime`. Obviamente, as classes de fila (*queue*) padrão podem ser estendidas
>para implementar sua própria lógica e gerenciamento de erros.

A tarefa `project:send-emails` recebe duas opções opcionais:

 *  `message-limit`: Limita o número de mensagens enviadas.

 *  `time-limit`: Limita o tempo gasto para enviar as mensagens (em segundos).

Ambas as opções podem ser combinadas:

  $ php symfony project:send-emails -message-limit=10 --time-limit=20

O comando acima irá parar de enviar mensagens quando 10 mensagens forem enviadas ou
após 20 segundos.

Mesmo quando se utiliza a estratégia `spool`, talvez você precise enviar uma mensagem
imediatamente, sem armazená-la na fila. Isso é possível usando o
método especial `sendNextImmediately()` do *mailer*:

    [php]
    $this->getMailer()->sendNextImmediately()->send($mensagem);

No exemplo anterior, a `$mensagem` não será armazenada na fila e 
será enviada imediatamente. Como o próprio nome sugere, o método `sendNextImmediately()`
afeta somente a próxima mensagem a ser enviada.

>**NOTE**
>O método `sendNextImmediately()` não tem efeito especial, quando a
>estratégia de envio não é `spool`.

### A Estratégia ~`none`~

Esta estratégia é útil no ambiente de desenvolvimento para evitar que os e-mails sejam
enviados para os usuários reais. As mensagens ainda estão disponíveis na barra de ferramentas para debug web (*web debug toolbar*)
(mais informações na seção abaixo sobre o painel *mailer* da barra de ferramentas para debug web).

É também a melhor estratégia para o ambiente de teste, onde o
objeto `sfTesterMailer` permite a introspecção das mensagens sem a necessidade
de realmente enviá-las (mais informações na seção abaixo sobre teste).

O transporte de e-mail
------------------

As mensagens de e-mail são realmente enviadas por um transporte. O transporte está configurado no
arquivo de configuração `factories.yml`, e a configuração padrão utiliza
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

O *Swift Mailer* vem com três classes de transporte diferentes:

  * ~`Swift_SmtpTransport`~: Usa um servidor SMTP para enviar mensagens.

  * ~`Swift_SendmailTransport`~: Usa o `sendmail` para enviar mensagens.

  * ~`Swift_MailTransport`~: Usa a função nativa do PHP `mail()` para enviar
    as mensagens

>**TIP**
>A seção ["Tipos de Transportes"](http://swiftmailer.org/docs/transport-types) da
>documentação oficial do *Swift Mailer* descreve tudo o que você precisa saber
>sobre as classes integradas de transporte e seus diferentes parâmetros.

Enviando um E-mail Através de uma Tarefa
----------------------------

O envio de e-mail através de uma tarefa é bastante semelhante ao envio de um e-mail em uma
ação, pois o sistema de tarefas também oferece o método `getMailer()`.

Ao criar o *mailer*, o sistema de tarefas depende da configuração atual.
Assim, se você quiser usar uma configuração de uma aplicação específica, você deve
passar a opção `--application` (veja no capítulo sobre tarefas para mais
informações sobre esse assunto).

Observe que a tarefa usa a mesma configuração dos controladores (*controllers*). Assim, se
deseja forçar a entrega quando a estratégia `spool` é usada, utilize o 
`sendNextImmediately()`:

    [php]
    $this->getMailer()->sendNextImmediately()->send($mensagem);

Depuração
---------

Tradicionalmente, a depuração de e-mails tem sido um pesadelo. Com o symfony, é muito
fácil, graças à ~web debug toolbar~ (barra de ferramentas para debug web).

A partir do conforto do seu navegador, você pode fácil e rapidamente ver quantas
mensagens foram enviadas pela ação atual:

![E-mails na Barra de Ferramentas para Debug Web](http://www.symfony-project.org/images/more-with-symfony/emails_wdt.png "E-mails na Barra de Ferramentas para Debug Web")

Se você clicar no ícone e-mail, as mensagens enviadas são exibidas no painel
em sua forma bruta, conforme mostrado abaixo.

![E-mails na Barra de Ferramentas para Debug Web - detalhes](http://www.symfony-project.org/images/more-with-symfony/emails_wdt_details.png "E-mails na Barra de Ferramentas para Debug Web - Detalhes")

>**NOTE**
>Cada vez que uma mensagem é enviada, o symfony também adiciona uma mensagem no log.

Testando
-------

Naturalmente, a integração não seria completa sem uma forma de testar
as mensagens de e-mail. Por padrão, o symfony registra um `mailer` de teste
(~`sfMailerTester`~) para facilitar o teste de e-mails com testes funcionais.

O método ~`hasSent()`~ testa o número de mensagens enviadas durante o pedido (*request*) atual:

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
pode ser um dos seguintes:

 * Uma *string* para buscar uma correspondência exata;

 * Uma expressão regular para verificar o valor;

 * Uma expressão regular negativa (uma expressão regular iniciando com um `!`)) para
   verificar se o valor não corresponde.

Por padrão, as verificações são feitas com a primeira mensagem enviada. Se várias mensagens
foram enviadas, você pode escolher qual deseja testar com o
método ~`withMessage()`~:

    [php]
    $browser->
      get('/foo')->
      with('mailer')->begin()->
        hasSent(2)->
        withMessage('foo@example.com')->
        checkHeader('Assunto', '/Assunto/')->
        checkBody('/Corpo/')->
      end()
    ;

O `withMessage()` tem um destinatário como seu primeiro argumento. Ele também possui um
segundo argumento para indicar qual a mensagem que você deseja testar se várias
foram enviadas para o mesmo destinatário.

Por último, mas não menos importante, o método ~`debug()`~ descarrega as mensagens enviadas para verificar 
os problemas quando um teste falhar:

    [php]
    $browser->
      get('/foo')->
      with('mailer')->
      debug()
    ;

Mensagens de E-mail como Classes
-------------------------

Na introdução deste capítulo, você aprendeu como enviar e-mails a partir de uma
ação. Esta é provavelmente a maneira mais fácil de enviar e-mails em uma aplicação symfony
e, provavelmente, a melhor quando você só precisa enviar poucas
mensagens simples.

Mas quando sua aplicação precisa gerenciar um grande número de diferentes mensagens de e-mail, 
você provavelmente utilizará uma estratégia diferente.

>**NOTE**
>Como um bônus, utilizar classes para mensagens de email significa que a mesma mensagem de e-mail
>pode ser utilizada em diferentes aplicações; uma *frontend* e uma *backend* por
>exemplo.

As mensagens são objetos simples do PHP, a forma mais óbvia para organizar suas mensagens
é criar uma classe para cada uma delas:

    [php]
    // lib/email/ProjectConfirmationMessage.class.php
    class ProjectConfirmationMessage extends Swift_Message
    {
      public function __construct()
      {
        parent::__construct('Assunto', 'Corpo');

        $this
          ->setFrom(array('app@example.com' => 'Minha Aplicação Robô'))
          ->attach('...')
        ;
      }
    }

Enviar uma mensagem através de uma ação, ou de qualquer outro lugar, é
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

    E-mail enviado pela Minha Aplicação Robô
    EOF
        ;
        parent::__construct($assunto, $corpo);

        // Definir todos os cabeçalhos compartilhados
        $this->setFrom(array('app@example.com' => 'Minha Aplicação Robô'));
      }
    }

Se uma mensagem depende de alguns objetos do modelo, você pode, naturalmente, passá-las como
argumentos para o construtor:

    [php]
    // lib/email/ProjectConfirmationMessage.class.php
    class ProjectConfirmationMessage extends ProjectBaseMessage
    {
      public function __construct($usuario)
      {
        parent::__construct('Confirmação para'. $usuario->getNome(), 'Corpo');
      }
    }

Receitas
-------

### Enviar e-mails através do ~Gmail~

Se você não tem um servidor SMTP, mas possui uma conta do Gmail, use a seguinte
configuração para utilizar os servidores do Google para enviar e arquivar mensagens:

    [yml]
    transport:
      class: Swift_SmtpTransport
      param:
        host: smtp.gmail.com
        port: 465
        encryption: SSL
        username: seu_usuario_do_gmail_vem_aqui
        password: sua_senha_do_gmail_vem_aqui

Substitua o `username` e `password` com as suas credenciais do Gmail e você está
pronto.

### Personalizando o Objeto *Mailer*

Se configurar o *mailer* através do `factories.yml` não for o suficiente, você pode
escutar (*listen*) o evento ~`mailer.configure`~, e personalizar ainda mais o *mailer*.

Você pode se conectar à esse evento em sua classe `ProjectConfiguration` como mostrado
abaixo:

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

        // Fazer algo com o mailer
      }
    }

A seção a seguir ilustra um uso desta poderosa técnica.

### Usando ~Plugins do Swift Mailer~

Para usar os plugins do *Swift Mailer*, escute (*listen*) o evento `mailer.configure` (ver a
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
>plugins integrados.

### Personalizar o Comportamento do *Spool*

A implementação integrada dos *spools* é muito simples. Cada *spool* recupera
todos os e-mails da fila em uma ordem aleatória e os envia.

Você pode configurar um spool para limitar o tempo gasto no envio de e-mails (em segundos),
ou para limitar o número de mensagens a enviar:

    [php]
    $spool = $mailer->getSpool();

    $spool->setMessageLimit(10);
    $spool->setTimeLimit(10);

Nesta seção, você vai aprender como implementar um sistema de prioridade para a
fila (*queue*). Ele lhe dará todas as informações necessárias para implementar sua própria
lógica.

Primeiro, adicione uma coluna `priority` no esquema:

    [yml]
    # para o Propel
    mail_message:
      message: { type: clob, required: true }
      created_at: ~
      priority: { type: integer, default: 3 }

    # para o Doctrine
    MailMessage:
      actAs: { Timestampable: ~ }
      columns:
        message: { type: clob, notnull: true }
        priority: { type: integer }

Ao enviar um e-mail, defina o cabeçalho da prioridade (1 significa mais alto):

    [php]
    $mensagem = $this->getMailer()
      ->compose('john@doe.com', 'foo@example.com', 'Assunto', 'Body')
      ->setPriority(1)
    ;
    $this->getMailer()->send($mensagem);

Em seguida, sobrescreva o método padrão `setMessage()` para alterar a prioridade do
objeto `MailMessage`:

    [php]
    // para o Propel
    class MailMessage extends BaseMailMessage
    {
      public function setMessage($mensagem)
      {
        $msg = unserialize($mensagem);
        $this->setPriority($msg->getPriority());

        return parent::setMessage($mensagem);
      }
    }

    // para o Doctrine
    class MailMessage extends BaseMailMessage
    {
      public function setMessage($mensagem)
      {
        $msg = unserialize($mensagem);
        $this->priority = $msg->getPriority();

        return $this->_set('message', $mensagem);
      }
    }

Observe que a mensagem é serializada pela fila (*queue*), por isso tem que ser desserializada
antes de obter o valor da prioridade. Agora, crie um método que ordena as
mensagens por prioridade:

    [php]
    // para o Propel
    class MailMessagePeer extends BaseMailMessagePeer
    {
      static public function getSpooledMessages(Criteria $criteria)
      {
        $criteria->addAscendingOrderByColumn(self::PRIORITY);

        return self::doSelect($criteria);
      }

      // ...
    }

    // para o Doctrine
    class MailMessageTable extends Doctrine_Table
    {
      public function getSpooledMessages()
      {
        return $this->createQuery('m')
          ->orderBy('m.priority')
          ->execute()
        ;
      }

      // ...
    }

O último passo é definir o método de recuperação no arquivo de configuração `factories.yml`
para alterar a forma padrão na qual as mensagens são obtidas
da fila (*queue*):

    [yml]
    spool_arguments: [MailMessage, message, getSpooledMessages]

E isto resume todo o processo. Agora, cada vez que você executar a tarefa `project:send-mails`, 
cada e-mail será enviado de acordo com a sua prioridade.

>**SIDEBAR**
>Personalizando o *Spool* com qualquer Critério
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
>       $mensagem->getHeaders()->addTextHeader('X-Queue-Criteria', 'foo');
>     
>       $this->getMailer()->send($mensagem);
>     }
>
>Em seguida, recuperar o valor deste cabeçalho ao armazenar a mensagem na
>fila, e, remova-o imediatamente:
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
>       return parent::_set('message', serialize($msg));
>     }
