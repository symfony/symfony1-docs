Aumente a sua produtividade
=========================

*por Fabien potencier*

Usar o symfony já é uma grande maneira de aumentar sua produtividade 
como desenvolvedor. Claro, todo mundo já sabe o quanto as exceções detalhadas e a 
Barra de Ferramentas para Debug Web (*web debug toolbar*) podem aumentar sua produtividade. Este capítulo
vai lhe ensinar algumas dicas para aumentar sua produtividade ainda mais 
usando algumas funcionalidades novas ou não tão conhecidas.

Comece Mais Rápido: Personalize o Processo de Criação de Projeto
----------------------------------------------------

Graças à ferramenta CLI do symfony, criar um novo projeto é rápido e
simples:

    $ php /path/to/symfony generate:project foo --ORM=Doctrine

A tarefa `generate:project` gera a estrutura padrão de diretórios para seu 
novo projeto e cria os arquivos de configuração com dados padrão Você pode
então usar outras tarefas do symfony para criar aplicações, instalar plugins,
configurar seu modelo e muito mais.

Mas os primeiros passos para se criar um novo projeto geralmente são os
mesmos: você cria uma aplicação principal, instala vários plugins, ajusta
algumas configurações padrão ao seu gosto, e assim por diante.

A partir do symfony 1.3, o processo de criação de projetos pode ser personalizado e
automatizado.

>**NOTE**
>Como todas as tarefas do symfony são classes, é muito fácil personalizá-las e estendê-las, com algumas exceções.
>A tarefa `generate:project`, entretanto, não é facilmente customizável
>porque não existe um projeto quando a tarefa é executada.

A tarefa `generate:project` tem a opção `--installer`, que é um script PHP que
será executado durante o processo de criação do projeto:

    $ php /path/to/symfony generate:project --installer=/algumlugar/meu_instalador.php

O script `/algumlugar/meu_instalador.php` será executado no contexto da instância 
de `sfGenerateProjecttarefa`, então, ele tem acesso à todos os métodos da tarefa usando o 
objeto `$this`. As próximas sessões descrevem todos os métodos disponíveis que você
pode utilizar na personalização de seu processo de criação de um projeto.

>**TIP**
>Se você habilitar uma URL de acesso para a função `include()` no seu
>`php.ini`, você pode até mesmo passar uma URL como um instalador (claro que você precisa
>ser muito cuidadoso quando fizer isso com um script que você desconhece):
>
> $ symfony generate:project
> --installer=http://example.com/sf_installer.php

### `InstallDir()`

O método `installDir()` replica a estrutura de diretório (composta de
sub-diretórios e arquivos) no projeto recém-criado:

    [php]
    $this->installDir(dirname(__FILE__).'/skeleton');

### `RunTask()`

O método `runTask()` executa uma tarefa. Leva o nome da tarefa, e uma string
representando os argumentos e as opções que você deseja passar para ele como
argumentos:

    [php]
    $this->runTask('configure:author', "'Fabien Potencier'");

Argumentos e opções também podem ser passados como arrays:

    [php]
    $this->runTask('configure:author', array('author' => 'Fabien Potencier'));

>**TIP**
>Os nomes dos atalhos das tarefas também funcionam como esperado:
>
> [php]
> $this->runTask('cc');

Este método pode, naturalmente, ser usado para instalar plugins:

    [php]
    $this->runTask('plugin:install', 'sfDoctrineGuardPlugin');

Para instalar uma versão específica de um plugin, apenas passe as opções necessárias:

    [php]
    $ this->runTask plugin ( ': install', 'sfDoctrineGuardPlugin', array estabilidade ( 'libertação' => '10 .0.0 ',' => beta '));

>**TIP**
>Para executar uma tarefa de um plugin instalado recentemente, as tarefas precisam ser
>recarregadas primeiro:
>
> [php]
> $this->reloadtarefas();

Se você criar uma nova aplicação e quiser usar as tarefas que dependem de uma
aplicação específica, como `generate:module`, deve alterar a configuração
contextual você mesmo

    [php]
    $this->setConfiguration($this->createConfiguration('frontend', 'dev'));

###Loggers

Para dar um feedback ao desenvolvedor quando um script de instalação é executado, você pode facilmente
gravar um log:

    [php]
    // um simples log
    $this->log('alguma mensagem de instalação');

    // log de um bloco
    $this->logBlock('Fabien\'s Crazy Installer', 'ERROR_LARGE');

    // log em uma seção
    $this->logSection('install', 'instala alguns arquivos loucos');

### Interação com o Usuário

Os métodos `askConfirmation()`, `askAndValidate()`, e `ask()` permitem que você faça
perguntas e torne seu processo de instalação dinamicamente configurável.

Se você só precisa de uma confirmação, use o método `askConfirmation()`:

    [php]
    if (!$this->askConfirmation('Tem certeza de que deseja executar este instalador louco?'))
    (
      $this->logSection('install', 'Você fez a escolha certa!');

      return;
    )

Você também pode fazer qualquer pergunta e obter resposta do usuário como uma string usando
o método `ask()`:

    [php]
    $secret = $this->venda('Dê uma string exclusiva para o segredo CSRF:');

E se você quer validar a resposta, use o método `askAndValidate()`:

    [php]
    $validator = new sfValidatorEmail(array(), array('invalid' => 'hmmm, isso não se parece com um email!'));;
    $email = $this->askAndValidate('Por favor, me de seu email:', $validator);

### Operações no Filesystem

Se você quer fazer alteraçoes no filesystem, você pode acessar o objeto de filesystem do
symfony:

    [php]
    $this->getFilesystem()->...();;

>**SIDEBAR**
>O Processo de Criação do Sandbox
>
> O sandbox do symfony é um projeto pré-empacotado com uma aplicação simples
> e um banco de dados SQLite pré-configurado. Qualquer um pode criar um sandbox
> usando o script de instalação:
>
>$ php symfony generate:project --installer=/caminho/do/symfony/data/bin/sandbox_installer.ph
>
>Dê uma olhada no script `symfony/data/bin/sandbox_installer.php` para ter um
> exemplo de um script instalador funcional.

O script do instalador é apenas outro arquivo PHP. Assim, você pode fazer qualquer coisa
que queira. Em vez de executar as mesmas tarefas de novo e de novo cada vez que
criar um novo projeto do symfony, você pode criar seu próprio script de instalação e
ajustar a sua instalação do projeto da maneira que quiser. Criar novos
projetos com um instalador é muito mais rápido e previne que se esqueça
etapas. Você pode compartilhar o seu script de instalação com os outros!

>**TIP**
>Em [Capítulo 06](#chapter_06), vamos usar um instalador personalizado. O código para ele
> pode ser encontrada no [Apêndice B](#chapter_b).

Desenvolver Mais Rápido
--------------

Do código PHP para tarefas CLI, a programação significa um monte de digitação. Vamos ver como
reduzir isto ao mínimo.

### Escolhendo a sua IDE

Usar uma IDE ajuda o desenvolvedor a ser mais produtivo de mais de uma maneira.

Primeiro, IDEs mais modernas fornecem autocompletar para PHP por padrão. Isso significa
que você só precisa digitar os primeiros caracteres do nome de um método. Isso
também significa que, mesmo se você não lembrar o nome do método, você não é forçado
a olhar a API, pois o IDE irá sugerir todos os métodos disponíveis do
objeto atual.

Além disso, algumas IDEs, como PHPEdit ou Netbeans, sabem ainda mais sobre symfony
e proporcionam integração específica com projetos do symfony.

>**SIDEBAR**
>Editores de Texto
>
>Alguns usuários preferem usar um editor de texto para o seu trabalho de programação, principalmente
>porque os editores de texto são mais rápidos do que qualquer outra IDE. Claro, editores de texto fornecem
>menos recursos que IDE específica. A maioria dos editores populares, no entanto, oferecem
>plugins/extensões que podem ser usadas para melhorar a sua experiência de usuário e
> fazer o editor funcionar de forma mais eficiente com PHP e projetos do symfony.
>
>Por exemplo, uma grande quantidade de usuários Linux tende a usar o VIM para todos os seus trabalhos.
>Para esses desenvolvedores, a extensão [vim-symfony] http://github.com/geoffrey/vim-symfony ()
>está disponível. VIM-symfony é um conjunto de scripts que integra o VIM
>ao symfony no seu editor favorito. Usando o vim-symfony, você pode facilmente criar macros do 
>vim e comandos para agilizar o seu desenvolvimento com o symfony. Também
>adiciona um conjunto de comandos padrão que facilitam o acesso vários 
>arquivos na ponta dos dedos (schema, routing etc) e permitem que você
>mude facilmente de actions para models.
>
> Alguns usuários do MacOS X usam o TextMate. Estes desenvolvedores podem instalar o symfony
>[pacote](http://github.com/denderello/symfony-tmbundle), que acrescenta vários
>macros e atalhos para economizar tempo nas suas atividades diárias

#### Usando uma IDE que de suporte ao symfony

Algumas IDEs, como o [PHPEdit 3,4](http://www.phpedit.com/en/presentation/extensions/symfony)
e [NetBeans 6.8](http://www.netbeans.org/community/releases/68/), têm
suporte nativo para symfony, e assim proporcionam uma ótima integração
com o framework. Dê uma olhada em sua documentação para saber mais sobre
seu suporte específico ao symfony e como ele pode ajudá-lo a desenvolver mais rápido.

#### Ajudando a IDE

O autocompletar do PHP em IDEs só funciona para os métodos que são explicitamente definidos
no código PHP. Mas se o seu código usa os métodos "mágicos" `__call()` ou `__get()` 
, as IDEs não tem como adivinhar os métodos disponíveis ou propriedades. A
boa notícia é que você pode ajudar a maioria das IDEs, fornecendo os métodos e/ou
propriedades em um bloco PHPDoc (usando as anotações `@method` e `@property` 
, respectivamente).

Digamos que você tem uma classe de `Mensagem`, com uma propriedade dinâmica (`mensagem`) e um
método dinâmico (`getMessage()`). O código a seguir mostra como uma IDE pode
conhecê-los, sem qualquer definição explícita no código PHP:

    [php]
    /**
     * @property clob $message
     *
     * @method clob getMessage() Returns the current message value
     */
    class Message
    (
      public function __get()
      (
        // ...
      )

      public function __call()
      (
        // ...
      )
    )

Mesmo se o método `getMessage()` não existir, ele será reconhecido pela
IDE, graças a anotação `@method`. O mesmo vale para a propriedade `message`
que nós adicionamos a anotação `@property`.

Esta técnica é utilizada pela tarefa `doctrine:build-model`. Por exemplo, uma
classe Doctrine `MailMessage' com duas colunas (`mensagem` e `priority`) ficaria
da seguinte forma;

    [php]
    /**
     * BaseMailMessage
     *
     * This class has been auto-generated by the Doctrine ORM Framework
     *
     * @property clob $message
     * @property integer $priority
     *
     * @method clob getMessage() Returns the current record's "message" value
     * @method integer getPriority() Returns the current record's "priority" value
     * @method MailMessage setMessage() Sets the current record's "message" value
     * @method MailMessage setPriority() Sets the current record's "priority" value
     *
     * @package ##PACKAGE##
     * @subpackage ##SUBPACKAGE##
     * @author ##NAME## <##EMAIL##>
     * @version SVN: $Id: Builder.php 6508 2009-10-14 06:28:49Z jwage $
     */
    abstract class BaseMailMessage extends sfDoctrineRecord
    (
        public function setTableDefinition()
        (
            $this->setTableName('mail_message');
            $this->hasColumn('message', 'clob', null, array(
                 'type' => 'clob',
                 'notnull' => true,
                 ));
            $this->hasColumn('priority', 'integer', null, array(
                 'type' => 'integer',
                 ));
        )

        public function setUp()
        (
            parent::setUp();
            $timestampable0 = new Doctrine_Template_Timestampable();
            $this->actAs($timestampable0);
        )
    )

Encontrar a Documentação Rapidamente 
-------------------------

Como o symfony é um grande framework, com muitos recursos, nem sempre é fácil
lembrar todas as possibilidades de configuração, ou todas as classes e métodos
à sua disposição. Como vimos antes, usar uma IDE pode facilitar
proporcionando autocompletar. Vamos explorar como as ferramentas existentes podem ser aproveitados para
encontrar respostas o mais rápido possível.

### API Online

A forma mais rápida para encontrar documentação sobre uma classe ou um método é navegar
pela [API](http://www.symfony-project.org/api/1_3/).

Ainda mais interessante é o mecanismo de busca embutido da API. A pesquisa permite
encontrar rapidamente uma classe ou um método com apenas algumas tecladas. Após
inserir algumas letras na caixa de pesquisa da página da API, uma caixa de busca rápida
aparecerá em tempo real com sugestões úteis.

Você pode pesquisar, digitando o início de um nome de classe:

![API de pesquisa](http://www.symfony-project.org/images/more-with-symfony/api_search_1.png "API de pesquisa")

ou de um nome de método:

![API de pesquisa](http://www.symfony-project.org/images/more-with-symfony/api_search_2.png "API de pesquisa")

ou um nome da classe seguido de `::` a lista de todos os métodos disponíveis:

![API de pesquisa](http://www.symfony-project.org/images/more-with-symfony/api_search_3.png "API de pesquisa")

ou acessar o início de um nome de método para refinar ainda mais as possibilidades:

![API de pesquisa](http://www.symfony-project.org/images/more-with-symfony/api_search_4.png "API de pesquisa")

Se você quer listar todas as classes de um pacote, basta digitar o nome do pacote e
fazer a busca.

Você pode até mesmo integrar a busca da API do symfony ao seu navegador. Dessa forma, você
nem sequer precisa navegar até o site do symfony para procurar algo. Isto é 
possível, porque nós fornecemos suporte nativo ao [OpenSearch](http://www.opensearch.org/)
para a API do symfony.

Se você usa Firefox, os motores de busca do symfony API aparecerão automaticamente
no menu do mecanismo de busca. Você também pode clicar no link "API OpenSearch"
da seção de documentação da API para adicionar um deles a o campo de busca do seu 
navegador.

>**NOTE**
>Você pode dar uma olhada em um vídeo que mostra como o mecanismo de busca da API symfony
>se integra bem com o Firefox no 
>[blog](http://www.symfony-project.org/blog/2009/02/24/opensearch-support-for-the-symfony-api) do symfony.

### Cheat Sheets

Se você deseja acessar rapidamente informações sobre as principais partes da estrutura,
estão disponíveis uma grande coleção de [Cheat Sheets](http://trac.symfony-project.org/wiki/CheatSheets):


* [Estrutura de diretório e CLI](http://andreiabohner.files.wordpress.com/2007/03/cheatsheetsymfony001_enus.pdf)
* [View](http://andreiabohner.files.wordpress.com/2007/08/sfviewfirstpartrefcard.pdf)
* [View: Partials, Components, Slots e Component Slots](http://andreiabohner.files.wordpress.com/2007/08/sfviewsecondpartrefcard.pdf)
* [Lime Unit & Functional Testing](http://trac.symfony-project.com/attachment/wiki/LimeTestingFramework/lime-cheat.pdf?format=raw)
* [ORM](http://andreiabohner.files.wordpress.com/2007/08/sform_enus.pdf)
* [Propel](http://andreiabohner.files.wordpress.com/2007/08/sfmodelfirstpartrefcard.pdf)
* [Propel Schema](http://andreiabohner.files.wordpress.com/2007/09/sfmodelsecondpartrefcard.pdf)
* [Doctrine](http://www.phpdoctrine.org/Doctrine-Cheat-Sheet.pdf)

>**NOTE**
>Algumas dessas cheat sheets ainda não foram atualizadas para o symfony 1.3.

### Documentação Off-line

Perguntas sobre a configuração são melhores respondidas pela guia de referência do
symfony. Este é um livro que você deve ter a mão sempre que você desenvolver com
symfony. O livro é o caminho mais rápido para encontrar todas as configurações disponíveis,
graças a um índice remissivo detalhado, um índice de termos,
referências cruzadas dentro dos capítulos, tabelas, e muito mais.

Você pode navegar pelo livro
[online](http://www.symfony-project.org/reference/1_3/en/), comprar uma cópia
[impressa](http://books.sensiolabs.com/book/the-symfony-1-3-reference-guide)
dele, ou até mesmo baixar o
[PDF](http://www.symfony-project.org/get/pdf/reference-1.3-en.pdf versão).

### Ferramentas

Como vimos no início deste capítulo, o symfony fornece um bom conjunto de ferramentas
para ajudar você a começar rapidamente. Eventualmente, você vai terminar o seu
projeto, e será a hora de colocá-lo em produção.

Para verificar se seu projeto está pronto para ser colocado em produção, você pode usar a
[checklist](http://symfony-check.org/) online de implantação. Este site abrange os
principais pontos que são necessários verificar antes de ir para produção.

Depurando Rapidamente
------------

Quando ocorre um erro no ambiente de desenvolvimento, symfony exibe uma bela
página de exceção cheia de informações úteis. Você pode, por exemplo, dar uma olhada
no stack trace e os arquivos que foram executados. Se você configurar
o ~`sf_file_link_format`~ no arquivo de configuração `settings.yml` (veja
abaixo), você ainda pode clicar sobre os nomes e os arquivos relacionados serão
abertos na linha certa em seu editor de texto favorito ou IDE. Este é um
grande exemplo de um recurso muito pequeno que pode poupar muito tempo quando estiver
depurando um problema.

>**NOTE**
> O log e painéis de exibição na web debug toolbar também exibem nomes de arquivos
> (especialmente quando XDebug está habilitado), que se tornam clicáveis quando você definir a
> configuração `sf_file_link_format`.

Por padrão, `sf_file_link_format` está vazia e o symfony usa o
valor da configuração do PHP
[`xdebug.file_link_format`](http://xdebug.org/docs/all_settings#file_link_format)
, se ele existe (atribuir valor ao `xdebug.file_link_format` no 
`php.ini` permite que versões mais recentes do XDebug adicionem links a todos os nomes de arquivos 
no stack trace)

O valor para `sf_file_link_format` depende do seu IDE e sistema operacional.
Por exemplo, se você quiser abrir arquivos em ~TextMate^, adicione o seguinte
ao seu `settings.yml`:

    [yml]
    dev:
      .settings:
        file_link_format: txmt://open?url=file://%f&line=%l

O placeholder `%f` é substituído pelo symfony com o caminho absoluto do arquivo
e o placeholder `%l` é substituído pelo número da linha.

Se você usa o VIM, a configuração é mais complexa e é explicada online para o
[symfony](http://geekblog.over-blog.com/article-symfony-open-exceptions-files-in-remote-vim-sessions-37895120.html)
e [XDebug](http://www.koch.ro/blog/index.php?/archives/77-Firefox,-VIM,-Xdebug-Jumping-to-the-error-line.html).

>**NOTE**
>Use o seu buscador favorito para aprender como configurar sua IDE. Você pode
>procurar pela configuração do `sf_file_link_format` ou `xdebug.file_link_format`
>que ambos funcionam da mesma maneira.

Teste Rapidamente
-----------

### Grave seus Testes Funcionais

Testes funcionais simulam a interação do usuário para testar exaustivamente a
integração de todas as peças de sua aplicação. Escrever testes funcionais é
fácil, mas demorado. Mas como cada arquivo de teste funcional é um cenário que
simula um usuário navegando pelo seu site, e navegar por uma aplicação é
mais rápido do que escrever código PHP, porque você não poderia gravar uma sessão de navegação e
tê-la automaticamente convertida em código PHP? Felizmente, o symfony tem um
plugin para isso. É chamado
[swFunctionalTestGenerationPlugin](http://www.symfony-project.org/plugins/swFunctionalTestGenerationPlugin),
e lhe permite gerar testes personalizados prontos para serem personalizados em uma questão
de minutos. Claro, você ainda precisa adicionar as chamadas apropriadas ao tester para
torná-lo útil, mas isso não deixa de ser um grande poupador de tempo.

O plugin funciona registrando um filtro do symfony que irá interceptar todas as
requisições, e convertê-los em código de teste funcional. Após a instalação do
plugin da forma padrão, você precisa habilitá-lo. Abra o `filters.yml` da sua
aplicação e adicione as seguintes linhas após a linha de comentário:

    [php]
    functional_test:
      class: swFilterFunctionalTest

Em seguida, ative o plugin na sua class`ProjectConfiguration`:

    [php]
    // config/ProjectConfiguration.class.php
    class ProjectConfiguration extends sfProjectConfiguration
    (
      public function setup()
      (
        // ...

        $this->enablePlugin('swFunctionalTestGenerationPlugin');
      )
    )

Como o plugin utiliza as web debug toolbar como sua interface principal, certifique-se
de tê-la ativada (que é o caso do ambiente de desenvolvimento por padrão).
Quando ativado, um novo menu chamado "Functional Test" ficará disponível. Neste
painel, você pode iniciar a gravação de uma sessão clicando no link "Activate",
e apagar a sessão atual, clicando em "Reset". Quando estiver pronto, copie
e cole o código da textarea para um arquivo de teste e comece a personalizá-lo.

### Executar sua Suite de Testes Rapidademnte

Quando você tem um grande conjunto de testes, pode ser muito demorado para lançar
todos os testes, cada vez que você fizer uma alteração, especialmente se alguns testes falharem. Cada 
vez que corrigir um teste, você deve executar toda a suíte de testes novamente para garantir
que você não tenha quebrado os outros testes. Mas enquanto os testes não estiverem corrigidos
não há nenhum problema em re-executar todos os outros testes. Para acelerar este processo,
a tarefa `test:all` tem uma opção `--only-failed` (com o atalho `-f`) que força
a tarefa a apenas re-executar os testes que falharam durante a execução anterior:

    $ php symfony test:all --only-failed

Na primeira execução, todos os testes são executados como de costume. Mas, para as próximas execuções execuções, somente os testes que falharam serão executados. Conforme você corrige seu código, alguns testes
vão passar, e serão removidos das execuções posteriores. Quando todos os testes passarem
novamente, o pacote de teste completo é executado ... Você pode em seguida, refatorar e repetir.
