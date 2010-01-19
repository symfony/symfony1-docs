
Aproveitando o Poder da Linha de Comando
========================================

*por Geoffrey Bachelet*

O symfony 1.1 introduziu um sistema de linha de comando moderno, poderoso e flexível
em substituição ao antigo sistema de tarefas baseado no pake. De versão em versão,
o sistema de tarefas foi melhorado para tornar-se o que é hoje.

Muitos desenvolvedores web não consideram as tarefas importantes, por desconhecerem as 
suas possibilidades. Neste capítulo, vamos
mergulhar nas tarefas, desde o início até o seu uso mais avançado, vendo como elas
podem ajudar no seu trabalho diário, e como você pode obter o melhor delas.

Breve olhar nas Tarefas 
-----------------

Uma tarefa é um pedaço de código que é executado na linha de comando usando o script php `symfony`
na raiz do seu projeto. Você já pode ter executado tarefas
através da tarefa bem conhecida `cache:clear` (também conhecida como `cc`), executando-a em
um shell:

    $ php symfony cc

O symfony fornece um conjunto de tarefas incorporadas, de propósito geral, para uma variedade de usos.
Você pode obter uma lista das tarefas disponíveis executando o script `symfony` 
sem quaisquer argumentos ou opções:

    $ php symfony

A saída será parecida com a seguinte (conteúdo truncado):

    Uso:
      symfony [opções] nome_tarefa [argumentos]

    Opções:
      --help -H Exibe esta mensagem de ajuda.
      --quiet -q Não faz log de mensagens para a saída padrão.
      --trace -t Liga invocar/executar tracing, habilita backtrace completo.
      --version -V Mostra a versão do programa.
      --color Força saída de cores ANSI.
      --xml Para obter a saída da ajuda no formato XML

    Tarefas disponíveis:
      :help Apresenta a ajuda para uma tarefa (h)
      :list Lista as tarefas
    app
      :routes Apresenta as rotas atuais para uma aplicação
    cache
      :clear Limpar o cache (cc, clear-cache)

Você já deve ter notado que as tarefas são agrupadas. Grupos de tarefas são chamados
namespaces, e os nomes de tarefas são geralmente compostos por um namespace e o nome de tarefa
(exceto para as tarefas `help` e `list` que não tem um namespace). Esse
esquema de nomenclatura permite a categorização fácil das tarefas, e você deve escolher um
namespace significativo para cada uma das suas tarefas.


Escrevendo suas próprias tarefas
----------------------

Começar a escrever tarefas com o symfony leva apenas alguns minutos. Tudo
o que você precisa fazer é criar a sua tarefa, nomeá-la, colocar alguma lógica nela e voilà,
você está pronto para executar sua primeira tarefa personalizada. Vamos criar uma tarefa simples *Olá,
Mundo!*, por exemplo, em `lib/task/sayHelloTask.class.php`:

    [php]
    class sayHelloTask extends sfBaseTask
    {
      public function configure()
      {
        $this->namespace = 'dizer';
        $this->name = 'ola';
      }

      public function execute($arguments = array(), $options = array())
      {
        echo "Olá, Mundo!";
      }
    }

Agora execute ela com o seguinte comando:

    $ php symfony dizer:ola

Essa tarefa exibirá somente a saída *Olá, Mundo!*, mas isso é apenas o começo! As tarefas não 
são realmente para a saída de conteúdo diretamente através das declarações `echo` ou `print`
. Ao estender a classe `sfBaseTask` temos à nossa disposição vários métodos úteis, 
incluindo o método `log()`, que serve exatamente para o que nós queremos fazer, a saída 
de conteúdo:

    [php]
    public function execute($arguments = array(), $options = array())
    {
      $this->log('Olá, Mundo!');
    }

Uma vez que a chamada de uma única tarefa pode resultar na saída de conteúdo de múltiplas tarefas, você
pode desejar usar o método `logSection()`:

    [php]
    public function execute($arguments = array(), $options = array())
    {
      $this->logSection('dizer', 'Olá, Mundo!');
    }

Agora, você já deve ter notado os dois argumentos passados ao método `execute()`,
`$arguments` e `$options`. Eles destinam-se à conter todos os argumentos e
opções passados para a sua tarefa em tempo de execução. Nós vamos falar sobre argumentos e opções
extensivamente mais tarde. Agora, vamos adicionar um pouco de interatividade à nossa tarefa
permitindo ao usuário especificar à quem queremos dizer Olá:

    [php]
    public function configure()
    {
      $this->addArgument('quem', sfCommandArgument::OPTIONAL, 'Quem deseja saudar?', 'Mundo');
    }

    public function execute($arguments = array(), $options = array())
    {
      $this->logSection('dizer', 'Olá, '.$arguments['quem'].'!');
    }

Agora o seguinte comando:

    $ php symfony dizer:ola Geoffrey

Deve produzir o seguinte resultado:

    >> dizer Olá, Geoffrey!

Uau, isso foi fácil.

A propósito, você pode desejar incluir um pouco mais de metadados nas tarefas, como
o que ela faz, por exemplo. Você pode fazer isso definindo as propriedades `briefDescription` e
`detailedDescription`:

    [php]
    public function configure()
    {
      $this->namespace = 'dizer';
      $this->name = 'ola';
      $this->briefDescription = 'Simples olá mundo';

      $this->detailedDescription = <<<EOF
    A tarefa [dizer:ola|INFO] é uma implementação do clássico
    exemplo Olá Mundo utilizando o sistema de tarefas do symfony.

      [./symfony dizer:ola|INFO]

    Utilize esta tarefa para cumprimentar você, ou qualquer outra pessoa usando
    o argumento [--quem|COMMENT].
    EOF;

      $this->addArgument('quem', sfCommandArgument::OPTIONAL, 'Quem deseja saudar?', 'Mundo');
    }

Como pode-se ver, é possível utilizar um conjunto básico de marcação para decorar a sua descrição.
O processamento pode ser verificado utilizando o sistema de ajuda das tarefas do symfony:

    $ php symfony help dizer:ola

O Sistema de Opções
------------------

As opções de uma tarefa do symfony são organizadas em dois conjuntos distintos, opções e
argumentos.

### Opções

As opções são aquelas que você passa usando hífens. É possível adicioná-las à sua
linha de comando em qualquer ordem. Elas podem ter um valor ou não, 
nesse caso, atuam como um booleano. Na maioria das vezes, as opções tem tanto
uma forma longa quanto uma abreviada. A forma longa é geralmente chamada usando dois hífens enquanto
a forma abreviada requer apenas um hífen.

Exemplos das opções mais comuns são a opção de ajuda (`--help` ou `-h`), a 
verbosidade (`--quiet` ou `-q`) ou a opção de versão (`--version` ou
`-V`).

>**NOTE**
>As opções são definidas com a classe `sfCommandOption` e armazenadas na
>classe `sfCommandOptionSet`.

### Argumentos

Os argumentos são apenas um pedaço de dados que você adiciona à sua linha de comando. Eles
devem ser especificados na mesma ordem em que foram definidos, e você deve
adicioná-los entre aspas se quiser incluir um espaço neles (ou você pode
também escapar os espaços). Eles podem ser obrigatórios ou opcionais, neste caso
deve-se especificar um valor padrão na definição do argumento.

>**NOTE**
>Obviamente, os argumentos são definidos com a classe `sfCommandArgument` e armazenados na
>classe `sfCommandArgumentSet`.

### Conjuntos Padrão

Cada tarefa do symfony vem com um conjunto de opções e argumentos padrão:

  * `--help` (-`H`): Exibe esta mensagem de ajuda.
  * `--quiet` (`-q`): Não faz o log de mensagens para a saída padrão.
  * `--trace` `(-t`): Liga invocar/executar o tracing, habilita backtrace completo.
  * `--version` (`-V`): Mostra a versão do programa.
  * `--color`: Força a saída de cores ANSI.

### Opções especiais

O sistema de tarefas do symfony compreende duas opções muito especiais, `application` e
`env`.

A opção `application` é necessária quando se pretende o acesso à uma
instância do `sfApplicationConfiguration` em vez de apenas a instância do 
`sfProjectConfiguration`. Este é o caso, por exemplo, quando você deseja gerar URLs, uma vez que
o roteamento é geralmente associado a uma aplicação específica.

Quando uma opção `application` é passada à uma tarefa, o symfony irá automaticamente
detectá-la e, então, criar o objeto `sfApplicationConfiguration` correspondente em vez do
objeto padrão `sfProjectConfiguration`. Note que você pode definir um valor padrão para
esta opção, portanto, poupando-lhe o aborrecimento de ter que passar uma aplicação
manualmente toda vez que executar a tarefa.

A opção `env` controla, obviamente, o ambiente no qual a tarefa
será executada. Quando nenhum ambiente é passado, o `test` é utilizado por padrão. Assim como
para o `application`, você pode definir um valor padrão para a opção `env` que será
automaticamente utilizado pelo symfony.

Já que as opções `application` e `env` não estão incluídas no conjunto de opções padrão, você
tem que adicioná-las manualmente na sua tarefa:

    [php]
    public function configure()
    {
      $this->addOptions(array(
        new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'O nome da aplicação', 'frontend'),
        new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'O ambiente', 'dev'),
      ));
    }

Neste exemplo, a aplicação `frontend` será automaticamente usada, e,
a não ser que um ambiente diferente seja especificado, a tarefa será executada no ambiente 
`dev`.

Acessando o banco de dados
----------------------

Acessar o seu banco de dados de dentro de uma tarefa symfony é apenas uma questão de
instanciar o `sfDatabaseManager`:

    [php]
    public function execute($arguments = array(), $options = array())
    {
      $databaseManager = new sfDatabaseManager($this->configuration);
    }

Você também pode acessar diretamente o objeto de conexão ORM:

    [php]
    public function execute($arguments = array(), $options = array())
    {
      $databaseManager = new sfDatabaseManager($this->configuration);
      $connection = $databaseManager->getDatabase()->getConnection();
    }

Mas, e se você tiver várias conexões definidas em seu `databases.yml`? Você
poderia, por exemplo, adicionar uma opção `connection` para a sua tarefa:

    [php]
    public function configure()
    {
      $this->addOption('connection', sfCommandOption::PARAMETER_REQUIRED, 'O nome da conexão', 'doctrine');
    }

    public function execute($arguments = array(), $options = array())
    {
      $databaseManager = new sfDatabaseManager($this->configuration);
      $connection = $databaseManager->getDatabase(isset($options['connection']) ? $options['connection'] : null)->getConnection();
    }

Como de costume, você pode definir um valor padrão para esta opção.

Voilà! Agora você pode manipular os seus modelos como se estivesse em sua aplicação 
symfony.

>**NOTE**
>Tenha cuidado ao utilizar processamento em lote usando seus objetos ORM favoritos. Ambos Propel e
>Doctrine sofrem de um bug bem conhecido do PHP relacionado à referências cíclicas e o
>garbage collector que resulta em um memory leak. Isto foi parcialmente corrigido
>no PHP 5.3.

Enviando e-mails
--------------

Um dos usos mais comuns para as tarefas é o envio de e-mails. Até o symfony 1.3,
enviar um e-mail não era realmente simples. Mas os tempos mudaram: o symfony
agora possui integração total com o [Swift Mailer](http://swiftmailer.org/), uma
biblioteca de e-mail PHP feature-rich, então, vamos usá-la!

O sistema de tarefas do symfony expõe o objeto mailer através do
método `sfCommandApplicationTask::getMailer()`. Dessa forma, você ganha acesso
ao mailer e pode facilmente enviar e-mails:

    [php]
    public function execute($arguments = array(), $options = array())
    {
      $mailer = $this->getMailer();
      $mailer->composeAndSend($from, $recipient, $subject, $messageBody);
    }

>**NOTE**
>Uma vez que a configuração do mailer é lida a partir da configuração da aplicação,
>sua tarefa deve aceitar uma opção de aplicação, para poder utilizar o
>mailer.

-

>**NOTE**
>Se você estiver usando a estratégia de spool, os e-mails são enviados apenas quando você chamar
>a tarefa `project:send-emails`.

Na maioria dos casos, você não terá o conteúdo da sua mensagem em uma variável mágica
`$messageBody` esperando para ser enviado, você vai querer, de alguma forma,
gerá-lo. Não há nenhuma maneira preferida, no symfony, para gerar conteúdo para os seus
e-mails, mas existem algumas dicas que você pode seguir para tornar sua vida mais fácil:

### Delegar a Geração de Conteúdo

Por exemplo, criar um método protegido para a sua tarefa que retorna o conteúdo para o
e-mail que você está enviando:

    [php]
    public function execute($arguments = array(), $options = array())
    {
      $this->getMailer()->composeAndsend($from, $recipient, $subject, $this->getMessageBody());
    }

    protected function getMessageBody()
    {
      return 'Olá, Mundo';
    }

### Utilizar o plugin Decorator do Swift Mailer

O Swift Mailer possui um plugin conhecido como
[`Decorator`](http://swiftmailer.org/docs/decorator-plugin) que é basicamente um
mecanismo de template muito simples, mas eficaz, que pega valores de pares para substituição em destinatário específicos 
e aplica-os em todos os e-mails enviados.

Veja a [documentação do Swift Mailer](http://swiftmailer.org/docs/) para mais informações.

### Usar uma biblioteca de template externa

A integração com uma biblioteca de template externa é fácil. Por exemplo, você poderia
usar o novo componente de template lançado como parte do projeto de componentes 
do symfony. Basta soltar o código do componente em algum lugar no seu projeto
(`lib/vendor/templating/` seria um bom lugar), e colocar o seguinte 
código na sua tarefa:

    [php]
    protected function getMessageBody($template, $vars = array())
    {
      $engine = $this->getTemplateEngine();
      return $engine->render($template, $vars);
    }

    protected function getTemplateEngine()
    {
      if (is_null($this->templateEngine))
      {
        $loader = new sfTemplateLoaderFilesystem(sfConfig::get('sf_app_dir').'/templates/emails/%s.php');
        $this->templateEngine = new sfTemplateEngine($loader);
      }

      return $this->templateEngine;
    }

### Obtendo o melhor dos dois mundos

Você pode fazer ainda mais. O plugin `Decorator` do Swift Mailer é muito útil
uma vez que pode gerenciar as substituições com base em destinatários específicos. Isso significa que
você define um conjunto de substituições para cada um de seus destinatários, e o Swift Mailer
cuida da substituição de tokens pelo valor correto com base no destinatário
do e-mail a ser enviado. Vamos ver como podemos integrar isso com o componente de
template:

    [php]
    public function execute($arguments = array(), $options = array())
    {
      $message = Swift_Message::newInstance();

      // Obtém uma lista de usuários
      foreach($users as $user)
      {
        $replacements[$user->getEmail()] = array(
          '{username}' => $user->getEmail(),
          '{specific_data}' => $user->getSomeUserSpecificData(),
        );

        $message->addTo($user->getEmail());
      }

      $this->registerDecorator($replacements);

      $message
        ->setSubject('User specific data for {username}!')
        ->setBody($this->getMessageBody('user_specific_data'));

      $this->getMailer()->send($message);
    }

    protected function registerDecorator($replacements)
    {
      $this->getMailer()->registerPlugin(new Swift_Plugins_DecoratorPlugin($replacements));
    }

    protected function getMessageBody($template, $vars = array())
    {
      $engine = $this->getTemplateEngine();
      return $engine->render($template, $vars);
    }

    protected function getTemplateEngine($replacements = array())
    {
      if (is_null($this->templateEngine))
      {
        $loader = new sfTemplateLoaderFilesystem(sfConfig::get('sf_app_template_dir').'/emails/%s.php');
        $this->templateEngine = new sfTemplateEngine($loader);
      }

      return $this->templateEngine;
    }

Com o `apps/frontend/templates/emails/user_specific_data.php` contendo o
seguinte código:

    Hi {username}!

    We just wanted to let you know your specific data:

    (specific_data)

E isso é tudo! Você dispõe agora de um sistema de template completo para construir o 
conteúdo de seus e-mails.

Gerando URLs
---------------

Escrever e-mails geralmente requer a geração de URLs com base na sua configuração de 
roteamento. Felizmente, a geração de URLs foi simplificada no 
symfony 1.3, pois, você poderá acessar diretamente o roteamento da aplicação
atual dentro de uma tarefa usando o método `sfCommandApplicationTask::getRouting()`
:

    [php]
    public function execute($arguments = array(), $options = array())
    {
      $routing = $this->getRouting();
    }

>**NOTE**
>Como o roteamento é dependente da aplicação, você precisa ter certeza que o seu
>aplicativo tem uma configuração de aplicação disponível, caso contrário, você não vai
>conseguir gerar URLs usando o roteamento.
>
>Veja a seção *Opções Especiais* para aprender como obter automaticamente uma
>configuração de aplicação na sua tarefa.

Agora que temos uma instância de roteamento, é muito simples gerar uma
URL usando o método `generate()`:

    [php]
    public function execute($arguments = array(), $options = array())
    {
      $url = $this->getRouting()->generate('default', array('module' => 'foo', 'action' => 'bar'));
    }

O primeiro argumento é o nome da rota e o segundo é um array com os parâmetros da
rota. Neste ponto, temos uma URL relativa gerada, que certamente
não é o que queremos. Infelizmente, não é possível gerar URLs absolutas dentro de uma tarefa
uma vez que não temos um objeto `sfWebRequest` com o qual obtemos o host HTTP.

Uma maneira simples de resolver isso é definir o host HTTP no seu
arquivo de configuração `factories.yml`:

    [yml]
    all:
      routing:
        class: sfPatternRouting
        param:
          generate_shortest_url: true
          extra_parameters_as_query_string: true
          context:
            host: example.org

Viu a definição `context_host`? O roteamento utilizará o seu contéudo quando solicitada
uma URL absoluta:

    [php]
    public function execute($arguments = array(), $options = array())
    {
      $url = $this->getRouting()->generate('my_route', array(), true);
    }

Acessando o Sistema I18N
-------------------------

Nem todas as factories são tão facilmente acessíveis como o mailer e o roteamento.
Se você precisar de acesso a um deles, é extremamente fácil criar uma instância
deles. Por exemplo, se você quer internacionalizar as suas tarefas, então,
desejará acessar o subsistema i18n do symfony. Isso pode ser feito facilmente usando o
`sfFactoryConfigHandler`:

    [php]
    protected function getI18N($culture = 'en')
    {
      if(!$this->i18n)
      {
        $config = sfFactoryConfigHandler::getConfiguration($this->configuration->getConfigPaths('config/factories.yml'));
        $class = $config['i18n']['class'];

        $this->i18n = new $class($this->configuration, null, $config['i18n']['param']);
      }

      $this->i18n->setCulture($culture);

      return $this->i18n;
    }

Vamos ver o que está acontecendo aqui. Em primeiro lugar, estamos usando uma técnica de cache simples
para evitar a re-construção do componente i18n em cada chamada. Então, usando o
`sfFactoryConfigHandler`, nós recuperamos a configuração do componente, a fim de
instanciá-lo. Finalizamos definindo a configuração da cultura. A tarefa agora
tem acesso a internacionalização:

    [php]
    public function execute($arguments = array(), $options = array())
    {
      $this->log($this->getI18N('fr')->__('some translated text!'));
    }

Com certeza, não é muito conveniente ter que passar sempre a cultura, especialmente se você
não precisa alterar a cultura frequentemente em sua tarefa. Veremos como
melhorar isso na próxima seção.

Refatoração das suas tarefas 
----------------------

Uma vez que, o envio de e-mails (e criação de conteúdo para eles) e a geração de URLs são
duas tarefas muito comuns, é uma boa idéia criar uma tarefa base que
fornece estas duas funcionalidades automaticamente para cada tarefa. Isto é bastante fácil de
fazer. Crie uma classe base dentro de seu projeto, por exemplo, em
`lib/task/sfBaseEmailTask.class.php`.

    [php]
    class sfBaseEmailTask extends sfBaseTask
    {
      protected function registerDecorator($replacements)
      {
        $this->getMailer()->registerPlugin(new Swift_Plugins_DecoratorPlugin($replacements));
      }

      protected function getMessageBody($template, $vars = array())
      {
        $engine = $this->getTemplateEngine();
        return $engine->render($template, $vars);
      }

      protected function getTemplateEngine($replacements = array())
      {
        if (is_null($this->templateEngine))
        {
          $loader = new sfTemplateLoaderFilesystem(sfConfig::get('sf_app_template_dir').'/templates/emails/%s.php');
          $this->templateEngine = new sfTemplateEngine($loader);
        }

        return $this->templateEngine;
      }
    }

Já que estamos nela, vamos automatizar também a configuração de opções da tarefa. Adicione estes
métodos na classe `sfBaseEmailTask`:

    [php]
    public function configure()
    {
      $this->addOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application', 'frontend');
    }

    protected function generateUrl($route, $params = array())
    {
      return $this->getRouting()->generate($route, $params, true);
    }

Usamos o método `configure()` para adicionar opções comuns à todas as tarefas estendidas.
Infelizmente, qualquer classe que estender o `sfBaseEmailTask` terá agora que chamar
`parent::configure` em seu próprio método `configure()`, mas, este é apenas um pequeno inconveniente 
em relação ao valor acrescentado.

Agora vamos refatorar o código de acesso I18N da seção anterior:

    [php]
    public function configure()
    {
      $this->addOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application', 'frontend');
      $this->addOption('culture', null, sfCommandOption::PARAMETER_REQUIRED, 'The culture', 'en');
    }

    protected function getI18N()
    {
      if(!$this->i18n)
      {
        $config = sfFactoryConfigHandler::getConfiguration($this->configuration->getConfigPaths('config/factories.yml'));
        $class = $config['i18n']['class'];

        $this->i18n = new $class($this->configuration, null, $config['i18n']['param']);

        $this->i18n->setCulture($this->commandManager->getOptionValue('culture'));
      }

      return $this->i18n;
    }

    protected function changeCulture($culture)
    {
      $this->getI18N()->setCulture($culture);
    }

    protected function process(sfCommandManager $commandManager, $options)
    {
      parent::process($commandManager, $options);
      $this->commandManager = $commandManager;
    }

Temos um problema para resolver aqui: não é possível acessar valores de argumentos e
opções fora do escopo `execute()`.  Para corrigir isso, vamos simplesmente
sobrecarregar o método `process()` para anexar o gerenciador de opções para à classe.
O gerenciador de opções é, como seu nome indica, o gerenciador dos argumentos e opções para
a tarefa atual. Por exemplo, você pode acessar os valores de opções através do
método `getOptionValue()`.

Executando uma Tarefa dentro de outra Tarefa
------------------------------

Uma forma alternativa para refatorar as suas tarefas consiste em inserir uma tarefa dentro de outra
tarefa. Isto torna-se particularmente fácil através dos métodos
`sfCommandApplicationTask::createTask()` e
`sfCommandApplicationTask::runTask()`.

O método `createTask()` irá criar uma instância de uma tarefa para você. Basta passar à ele
o nome da tarefa, como se estivesse na linha de comando, e ele irá retornar uma
instância da tarefa desejada, pronta para ser executada:

    [php]
    $task = $this->createTask('cache:clear');
    $task->run();

Mas, uma vez que temos muito trabalho para realizar, o `runTask` nos ajuda, fazendo tudo para nós:

    [php]
    $this->runTask('cache:clear');

Claro, você pode passar argumentos e opções (nesta ordem):

    [php]
    $this->runTask('plugin:install', array('sfGuardPlugin'), array('install_deps' => true));

A incorporação de tarefas é útil para compor tarefas poderosas a partir de tarefas mais simples.
Por exemplo, você pode combinar várias tarefas em uma tarefa `project:clean` que você
poderia executar após cada implantação:

    [php]
    $tasks = array(
      'cache:clear',
      'project:permissions',
      'log:rotate',
      'plugin:publish-assets',
      'doctrine:build-model',
      'doctrine:build-forms',
      'doctrine:build-filters',
      'project:optimize',
      'project:enable',
    );

    foreach($tasks as $task)
    {
      $this->run($task);
    }

Manipulando o sistema de arquivos
---------------------------

Symfony inclui uma abstração simples incorporada do sistema de arquivos (`sfFilesystem`)
que permite a execução de operações simples em arquivos e diretórios. É 
acessível dentro de uma tarefa com `$this->getFilesystem()`. Essa abstração
inclui os seguintes métodos:

* `sfFilesystem::copy()`, copia um arquivo
* `sfFilesystem::mkdirs()`, cria diretórios recursivos
* `sfFilesystem::touch()`, cria um arquivo
* `sfFilesystem::remove()`, apaga um arquivo ou diretório
* `sfFilesystem::chmod()`, altera as permissões em um arquivo ou diretório
* `sfFilesystem::rename()`, renomeia um arquivo ou diretório
* `sfFilesystem::symlink()`, cria um link para um diretório
* `sfFilesystem::relativeSymlink()`, cria um link relativo para um diretório
* `sfFilesystem::mirror()`, espelha uma árvore de arquivo completa
* `sfFilesystem::execute()`, executa um comando shell arbitrário

Ele também inclui um método muito útil chamado `replaceTokens()` que nós veremos na próxima
seção.

Usando Esqueletos para gerar Arquivos
---------------------------------

Outro uso comum para as tarefas é a geração de arquivos. A geração de arquivos pode ser feita
facilmente usando esqueletos e o método `sfFilesystem::replaceTokens()` acima 
mencionado. Como o próprio nome sugere, este método substitui os tokens 
dentro de um conjunto de arquivos. Ou seja, você passa um array de arquivos, uma lista de
tokens e ele substitui todas as ocorrências de cada token com o seu
valor atribuído, para cada arquivo no array.

Para entender melhor como isso é útil, vamos reescrever uma parte
da tarefa existente: `generate:module`. Por razões de clareza e brevidade, nós
veremos somente a parte `execute` desta tarefa, assumindo que ele tenha sido configurado
adequadamente com todas as opções necessárias. Também vamos pular a validação.

Mesmo antes de começar a escrever a tarefa, precisamos criar um esqueleto para os
diretórios e arquivos que vamos criar, e armazená-lo em algum lugar como
`data/skeleton/`:

    data/skeleton/
      module/
        actions/
          actions.class.php
        templates/

O esqueleto `actions.class.php` deve ser semelhante ao seguinte:

    [php]
    class %moduleName%Actions extends %baseActionsClass%
    {
    }

A primeira etapa da nossa tarefa será a de espelhar a árvore de arquivos no lugar certo:

    [php]
    $moduleDir = sfConfig::get('sf_app_module_dir').$options['module'];
    $finder = sfFinder::type('any');
    $this->getFilesystem()->mirror(sfConfig::get('sf_data_dir').'/skeleton/module', $moduleDir, $finder);

Agora vamos substituir os tokens no `actions.class.php`:

    [php]
    $tokens = array(
      'moduleName' => $options['module'],
      'baseActionsClass' => $options['base-class'],
    );

    $finder = sfFinder::type('file');
    $this->getFilesystem()->replaceTokens($finder->in($moduleDir), '%', '%', $tokens);

E isso é tudo, geramos o nosso novo módulo, usando o token de substituição para personalizá-lo
.

>**NOTE**
>A tarefa integrada `generate: module` procura em `data/skeleton/` por
>um esqueleto alternativo para usar em vez do padrão, por isso tome cuidado!

Usando uma opção dry-run
----------------------

Muitas vezes, você deseja visualizar o resultado de uma tarefa antes de realmente
executá-la. Aqui estão algumas dicas de como fazer isso.

Primeiro, você deve usar um nome padrão, assim como `dry-run`. Assim, todos irão
reconhecer a finalidade dela. Até o symfony 1.3, o `sfCommandApplication` *fazia*
a adição de uma opção padrão `dry-run`, mas agora ele deve ser adicionado manualmente (possivelmente em
uma classe base, como demonstrado acima):

    [php]
    $this->addOption(new sfCommandOption('dry-run', null, sfCommandOption::PARAMETER_NONE, 'Executes a dry run');

Você deve, então, invocar a sua tarefa como a seguir:

    ./symfony my:task --dry-run

A opção `dry-run` indica que a tarefa não deve fazer qualquer alteração.

*Não deve fazer qualquer alteração*, lembre-se disso, são as palavras-chave. Quando
executando em modo `dry-run`, a sua tarefa deve deixar o ambiente exatamente como ele
era antes, incluindo (mas não limitado a):

* O banco de dados: não inserir, atualizar ou excluir registros de suas tabelas. Você
  pode usar uma transação para conseguir isso.
* O sistema de arquivos: não criar, modificar ou apagar arquivos de seu sistema.
* Envio de E-mail: não envia e-mails, ou envia eles somente para um endereço de debug.

Aqui está um exemplo simples do uso da opção `dry-run`:

    [php]
    $connection->beginTransaction();

    // Alterar o seu banco de dados

    if ($options['dry-run'])
    {
      $connection->rollBack();
    }
    else
    {
      $connection->commit();
    }

Escrevendo testes de unidade
------------------

Sendo que, as tarefas podem ser utilizadas para uma variedade de finalidades, realizar testes unitários não é algo 
fácil. Como tal, não há uma maneira de testar as tarefas, mas existem alguns
princípios a seguir que podem nos ajudar a tornar as tarefas mais testáveis.

Primeiro, pense em sua tarefa como um controlador. Lembra da regra sobre controlador?
*Controladores magros, modelos obesos*. Ou seja, mover toda a lógica de negócio para dentro
dos seus modelos, dessa forma, você pode testar seus modelos em vez da tarefa, que é a 
maneira mais fácil.

Quando não conseguir adicionar mais lógica em seus modelos, divida o seu método `execute()`
em pedaços de código facilmente testáveis, cada um residente em seu próprio e facilmente 
acessível (leia-se: public) método. Dividir seu código em pedaços tem várias vantagens:

  1. torna o `execute` da sua tarefa mais legível
  1. faz sua tarefa mais testável
  1. faz sua tarefa mais estensível

Seja criativo, não hesite em construir um pequeno ambiente específico para as suas
necessidades de testes. E se você não encontrar nenhuma maneira de testar essa tarefa impressionante que
você acabou de escrever, há duas possibilidades: ou você escreveu ela mal ou
você deve pedir a opinião de alguém. Além disso, você pode sempre escavar no
código de outra pessoa para ver como eles testam as coisas (as tarefas do symfony estão bem testadas, 
por exemplo, mesmo os geradores).

Métodos de Ajuda: Logging
-----------------------

O sistema de tarefas do symfony se esforça para fazer o dia do desenvolvedor mais fácil, fornecendo
um método de ajuda útil para operações comuns, como logging e interação do usuário.

Pode-se facilmente fazer log de mensagens para o `STDOUT` usando a família de métodos `log`:

  * `log`, aceita um array de mensagens
  * `logSection`, um pouco mais elaborado, formata a sua mensagem com um prefixo 
    (primeiro argumento) e um tipo de mensagem (quarto argumento). Quando você faz log de alguma coisa
    muito longa, como um caminho de arquivo, o `logSection` normalmente irá diminuir a sua mensagem,
    o que pode ser irritante. Use o terceiro argumento para especificar um tamanho máximo da sua
    mensagem
  * `logBlock`, é o estilo de log usado para registro de exceções. Aqui, novamente, você pode passar
    um estilo de formatação

Os formatos disponíveis de log são `ERROR`, `INFO`, `COMMENT` e `QUESTION`. Não
hesite em testá-los para ver como eles se parecem.

Exemplo de uso:

    [php]
    $this->logSection('file+', $aVeryLongFileName, $this->strlen($aVeryLongFileName));

    $this->logBlock('Congratulations! You ran the task successfuly!', 'INFO');

Métodos do helper: Interação com o Usuário
--------------------------------

Mais três helpers são disponibilizados para facilitar a interação com o usuário:

  * `ask()`, basicamente imprime uma pergunta e retorna qualquer entrada do usuário

  * `askConfirmation()`, pedimos ao usuário uma confirmação, permitindo `y` (sim) e
    `n` (não) como entrada do usuário

  * `askAndValidate()`, um método muito útil que imprime uma pergunta e valida
    a entrada do usuário através de um `sfValidator` passado como o segundo argumento. O terceiro
    argumento é um array de opções em que você pode passar um valor padrão
    (`value`), um número máximo de tentativas (`attempts`) e um estilo de formatação
    (`style`).

Por exemplo, você pode pedir a um usuário o seu endereço de e-mail e validá-lo *on the fly*:

    [php]
    $email = $this->askAndValidate('What is your email address?', new sfValidatorEmail());

Rodada Bônus: Usando Tarefas com um Crontab
---------------------------------------

A maioria dos sistemas UNIX e GNU/Linux permite o planejamento de tarefas através de um
mecanismo conhecido como *cron*. O *cron* verifica um arquivo de configuração (um *crontab*)
para os comandos que devem ser executados em um determinado período de tempo. As tarefas do symfony podem ser facilmente integradas
em um crontab, e a tarefa `project:send-emails` é uma candidata perfeita para
um exemplo:

    MAILTO="you@example.org"
    0 3 * * * /usr/bin/php /var/www/yourproject/symfony project:send-emails

Esta configuração diz ao *cron* para executar o `project:send-emails` todos os dias às
3h da madrugada e enviar todas as saídas possíveis (ou seja, logs, erros, etc) para o endereço
*you@example.org*.

>**NOTE**
>Para mais informações sobre o formato do arquivo de configuração do crontab, digite `man 5
>crontab` em um terminal.

Você pode, e deve na verdade, passar argumentos e opções:

    MAILTO="you@example.org"
    0 3 * * * /usr/bin/php /var/www/yourproject/symfony project:send-emails --env=prod --application=frontend

>**NOTE**
>Você deve trocar o caminho `/usr/bin/php` pela localização do seu binário do CLI do PHP.
>Se você não tem esta informação, você pode executar `which php` em sistemas linux 
>ou `whereis php` na maioria dos outros sistemas UNIX.

Rodada Bônus: Usando STDIN
------------------------

Uma vez que as tarefas são executadas em um ambiente de linha de comando, você pode acessar o stream
de entrada padrão (STDIN). A linha de comando do UNIX permite que os aplicativos interajam
entre si por uma variedade de meios, um dos quais é o *pipe*,
simbolizado pelo caractere *|*. O *pipe* permite que você passe a saída de uma aplicação
(conhecido como *STDOUT*) para a entrada padrão de outra aplicação (conhecido como
*STDIN*). Estas são disponibilizadas em suas tarefas através das constantes PHP especiais 
`STDIN` e `STDOUT`. Há também um terceiro stream padrão, *STDERR*,
acessivel através do `STDERR`, destinado a mostrar as mensagens de erro das aplicações.

Então, o que podemos fazer exatamente com a entrada padrão? Bem, imagine que você tenha uma
aplicação em execução no seu servidor que gostaria que se comunicasse com a sua 
aplicação symfony. Você poderia, naturalmente, fazer ela se comunicar através de HTTP, mas 
uma forma mais eficaz seria utilizar o *pipe* da sua saída para uma tarefa symfony. Digamos que a 
aplicação possa enviar dados estruturados (por exemplo um array serializado PHP)
descrevendo objetos de domínio que você deseja incluir em seu banco de dados. Você
poderia escrever a seguinte tarefa:

    [php]
    while ($content = trim(fgets(STDIN)))
    {
      if ($data = unserialize($content) !== false)
      {
        $object = new Object();
        $object->fromArray($data);
        $object->save();
      }
    }

Você poderia usá-lo como a seguir:

    /usr/bin/data_provider | ./symfony data:import

`data_provider` é a aplicação que gera os novos objetos de domínio, e
`data:import`, é a tarefa que acabamos de escrever.

Reflexões finais
--------------

As possibilidades de uso das tarefas estão limitadas apenas à sua imaginação. O sistema de tarefas do
symfony é poderoso e flexível o suficiente para que você possa fazer simplesmente qualquer coisa que 
imaginar. Adicione à isso o poder de um shell UNIX, e você irá realmente
amar as tarefas.