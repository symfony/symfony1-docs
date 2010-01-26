Funcionamento Interno do symfony
================================

*por Geoffrey Bachelet*

Alguma vez você já se perguntou o que acontece com uma requisição HTTP quando ela chega a uma 
aplicação symfony? Caso positivo, então você está no lugar certo. Este capítulo
irá explicar em detalhes como o symfony processa cada requisição, a fim de criar e
retornar a resposta. Claro que, apenas descrever o processo faltaria um
pouco de diversão, por isso vamos dar uma olhada em algumas coisas interessantes que você pode fazer e
onde você pode interagir neste processo.

A Inicialização
-------------

Tudo começa no controlador de sua aplicação. Digamos que você tenha um controlador `frontend`
com um ambiente `dev` (um início clássico para qualquer projeto symfony)
. Neste caso, você terá um *front controller* localizado em
[`web/frontend_dev.php`](http://trac.symfony-project.org/browser/branches/1.3/lib/task/generator/skeleton/app/web/index.php).
O que acontece exatamente nesse arquivo? Em apenas algumas linhas de código, o symfony
obtêm a configuração da aplicação e cria uma instância do 
`sfContext`, que é responsável por despachar a requisição. A configuração da 
aplicação é necessária para criar o objeto `sfContext`, que é
o motor por trás do symfony e que depende da aplicação.

>**TIP**
>O symfony já lhe concede um pouco de controle no que acontece aqui permitindo
>passar um diretório raiz personalizado para sua aplicação como o quarto
>argumento de ~`ProjectConfiguration::getApplicationConfiguration()`~, bem como
>uma classe de contexto personalizada como o terceiro (e último) argumento de
>[`sfContext::createInstance()`](http://www.symfony-project.org/api/1_3/sfContext#method_createinstance)
>(mas lembre-se que ela tem de estender `sfContext`).

Obter a configuração da aplicação é um passo muito importante. Primeiro,
o `sfProjectConfiguration` é responsável por descobrir
a classe de configuração da aplicação, geralmente `${application}Configuration`, localizada em
`apps/${application}/config/${application}Configuration.class.php`.

`sfApplicationConfiguration` na verdade estende `ProjectConfiguration`, significando
que qualquer método em `ProjectConfiguration` pode ser compartilhado entre todas as aplicações.
Isto significa também que `sfApplicationConfiguration` compartilha seu construtor
com ambos `ProjectConfiguration` e `sfProjectConfiguration`. Isto é
ótimo, já que grande parte da configuração do projeto é realizada dentro
do construtor de `sfProjectConfiguration`. Primeiro, vários valores úteis são
calculados e armazenados, como, o diretório raiz do projeto e o diretório da biblioteca do 
symfony. O `sfProjectConfiguration` também cria um novo distribuidor de eventos (*event dispatcher*) 
do tipo `sfEventDispatcher`, a menos que um tenha sido passado como o quinto
argumento de `ProjectConfiguration::getApplicationConfiguration()` no
*front controller*.

Só depois disso, você terá a oportunidade de interagir com o processo de configuração
sobrescrevendo o método `setup()` do `ProjectConfiguration`. Este
geralmente é o melhor local para ativar / desativar plugins (usando 
[`sfProjectConfiguration::setPlugins()`](http://www.symfony-project.org/api/1_3/sfProjectConfiguration#method_setplugins),
[`sfProjectConfiguration::enablePlugins()`](http://www.symfony-project.org/api/1_3/sfProjectConfiguration#method_enableplugins),
[`sfProjectConfiguration::disablePlugins()`](http://www.symfony-project.org/api/1_3/sfProjectConfiguration#method_disableplugins) ou
[`sfProjectConfiguration::enableAllPluginsExcept()`](http://www.symfony-project.org/api/1_3/sfProjectConfiguration#method_enableallpluginsexcept)).

Em seguida, os plugins são carregados por [`sfProjectConfiguration::loadPlugins()`](http://www.symfony-project.org/api/1_3/sfProjectConfiguration#method_loadplugins)
e o desenvolvedor tem uma chance de interagir com este processo através do
[`sfProjectConfiguration::setupPlugins()`](http://www.symfony-project.org/api/1_3/sfProjectConfiguration#method_setupplugins) que pode ser sobrescrito.

A inicialização de um plugin é bastante simples. Para cada plugin,
o symfony procura por uma classe `${plugin}Configuration` (por exemplo: `sfGuardPluginConfiguration`)
e, se for encontrado, cria uma instância dela. Caso contrário, a classe `sfPluginConfigurationGeneric` é
utilizada. Os seguintes métodos permitem modificar a configuração de um plugin:

* `${plugin}Configuration::configure()`, antes do carregamento automático (*autoloading*)
* `${plugin}Configuration::initialize()`, após o carregamento automático

Em seguida, `sfApplicationConfiguration` executa seu método `configure()`,
que pode ser usado para personalizar a configuração de cada aplicação antes 
que comece o processo de inicialização da configuração interna em 
[`sfApplicationConfiguration::initConfiguration()`](http://www.symfony-project.org/api/1_3/sfApplicationConfiguration#method_initconfiguration).

Esta parte do processo de configuração do symfony é responsável por muitas coisas
e existem diversos pontos de entrada caso você queira interagir nesse processo.
Por exemplo, você pode interagir com a configuração do carregamento automático (*autoloader*)
conectando-se ao evento `autoload.filter_config`. Em seguida, vários
arquivos de configuração importantes são carregados, incluindo o `settings.yml` e o
`app.yml`. Finalmente, um último pedaço da configuração do plugin está disponível através do
arquivo `config/config.php` de cada plugin ou do método da classe de configuração `initialize()`.


Se o `sf_check_lock` estiver ativado, o symfony irá buscar por um arquivo de bloqueio (
criado pela tarefa `project:disable`, por exemplo). Se o bloqueio for encontrado,
os seguintes arquivos são verificados e o primeiro disponível é incluído, seguido
imediatamente pela finalização do script:

1. `apps/${application}/config/unavailable.php`,
1. `config/unavailable.php`,
1. `web/errors/unavailable.php`,
1. `vendor/lib/symfony/lib/exception/data/unavailable.php`,

Por último, o desenvolvedor tem uma última chance de personalizar a inicialização da 
aplicação através do método ~`sfApplicationConfiguration::initialize()`~.

### Resumo da inicialização e configuração

* Recuperação da configuração da aplicação
  * `ProjectConfiguration::setup()` (definir seus plugins aqui)
  * Plugins são carregados
   * `${plugin}Configuration::configure()`
   * `${plugin}Configuration::initialize()`
  * `ProjectConfiguration::setupPlugins()` (configurar seus plugins aqui)
  * `${application}Configuration::configure()`
  * `autoload.filter_config` é notificado
  * Carregamento do `settings.yml` e `app.yml`
  * `${application}Configuration::initialize()`
* Criação de uma instância `sfContext`

`sfContext` e *Factories*
-------------------------

Antes de mergulhar no processo de execução, vamos falar sobre uma parte vital do
workflow do symfony: as *factories*.

No symfony, as *factories* são um conjunto de componentes ou classes que a sua 
aplicação depende. Exemplos de *factories* são `logger`, `i18n`, etc.
Cada *factory* é configurada via `factories.yml`, que é compilada por um
manipulador de configuração (*config handler*) (veremos sobre manipuladores de configuração mais tarde) e convertidos em código PHP
que realmente instancia os objetos *factory* (você pode ver este 
código em seu cache no
arquivo `cache/frontend/dev/config/config_factories.yml.php`).

>**NOTE**
>O Carregamento da *Factory* acontece após a inicialização do `sfContext`. Veja:
>[`sfContext::initialize()`](http://www.symfony-project.org/api/1_3/sfContext#method_initialize)
>e [`sfContext::loadFactories()`](http://www.symfony-project.org/api/1_3/sfContext#method_loadfactories)
>para mais informações.

Neste ponto, você já pode personalizar uma grande parte do comportamento do symfony
apenas editando a configuração no `factories.yml`. Você pode até substituir as classes
*factory* internas do symfony pelas suas próprias!

>**NOTE**
>Se você estiver interessado em saber mais sobre as *factories*,
>[O Livro de Referência do symfony](http://www.symfony-project.org/reference/1_3/en/05-Factories)
>bem como o arquivo 
>[`factories.yml`](http://trac.symfony-project.org/browser/branches/1.3/lib/config/config/factories.yml)
>são recursos inestimáveis.

Se você olhou o arquivo `config_factories.yml.php` gerado, você pode ter
notado que as *factories* são instanciadas em uma determinada ordem. Essa ordem é
importante, pois algumas *factories* são dependentes de outras (por exemplo, o
componente `routing` necessita, obviamente, do `request` para recuperar as informações que 
ele precisa).

Vamos falar em maiores detalhes sobre o `request`. Por padrão, a 
classe `sfWebRequest` representa o `request`. Após a criação da instância,
[`sfWebRequest::initialize()`](http://www.symfony-project.org/api/1_3/sfWebRequest#method_initialize) 
é chamado, que reúne informações relevantes como os parâmetros GET / POST
bem como o método HTTP. Você então tem a oportunidade de adicionar o seu próprio 
processamento do pedido através do evento `request.filter_parameters`.

### Usando o evento `request.filter_parameter` 

Imagine que o seu website disponibiliza uma API pública aos seus usuários. A API 
está disponível através de HTTP, e cada usuário que deseja utilizá-la deve fornecer uma chave de API 
válida através de um cabeçalho de solicitação (por exemplo `X_API_KEY`) que será validada pela 
sua aplicação. Isso pode ser facilmente obtido utilizando o 
evento `request.filter_parameter`:

    [php]
    class apiConfiguration extends sfApplicationConfiguration
    {
      public function configure()
      {
        // ...

        $this->dispatcher->connect('request.filter_parameters', array(
          $this, 'requestFilterParameters'
        ));
      }

      public function requestFilterParameters(sfEvent $event, $parameters)
      {
        $request = $event->getSubject();

        $api_key = $request->getHttpHeader('X_API_KEY');

        if (null === $api_key || false === $api_user = Doctrine_Core::getTable('ApiUser')->findOneByToken($api_key))
        {
          throw new RuntimeException(sprintf('Invalid api key "%s"', $api_key));
        }

        $request->setParameter('api_user', $api_user);

        return $parameters;
      }
    }

Você poderá então acessar seu usuário da API a partir do pedido (*request*): 

    [php]
    public function executeFoobar(sfWebRequest $request)
    {
      $api_user = $request->getParameter('api_user');
    }

Esta técnica pode ser usada, por exemplo, para validar chamadas à webservices.

>**NOTE**
>O evento `request.filter_parameters` vem com muitas informações sobre 
>o pedido (*request*), consulte o método 
>[`sfWebRequest::getRequestContext()`](http://www.symfony-project.org/api/1_3/sfWebRequest#method_getrequestcontext)
>para obter mais informações.

A próxima *factory* muito importante é o roteamento (*routing*). A inicialização de roteamento é 
bastante simples e consiste principalmente de coletar e configurar opções específicas
. Você pode, no entanto, interagir neste processo através do 
evento `routing.load_configuration`.

>**NOTE**
>O evento `routing.load_configuration` lhe fornece acesso a instância do 
>objeto de roteamento atual (por padrão,
>[`sfPatternRouting`](http://trac.symfony-project.org/browser/branches/1.3/lib/routing/sfPatternRouting.class.php)).
>Você pode então manipular rotas registradas através de uma variedade de métodos.

### Exemplo de uso do evento `routing.load_configuration`

Por exemplo, você pode facilmente adicionar uma rota:

    [php]
    public function setup()
    {
      // ...

      $this->dispatcher->connect('routing.load_configuration', array(
        $this, 'listenToRoutingLoadConfiguration'
      ));
    }

    public function listenToRoutingLoadConfiguration(sfEvent $event)
    {
      $routing = $event->getSubject();

      if (!$routing->hasRouteName('my_route'))
      {
        $routing->prependRoute('my_route', new sfRoute(
          '/my_route', array('module' => 'default', 'action' => 'foo')
        ));
      }
    }

O processamento da URL ocorre logo após a inicialização, através do método 
[`sfPatternRouting::parse()`](http://www.symfony-project.org/api/1_3/sfPatternRouting#method_parse).
Existem alguns métodos envolvidos, mas tudo o que você precisa saber 
é que, quando chegamos ao final do método `parse()`, a rota correta foi encontrada,
instanciada e vinculada aos parâmetros relevantes. 

>**NOTE**
>Para mais informações sobre roteamento, consulte o capítulo `Roteamento Avançado` deste 
>livro.

Uma vez que todas as *factories* tenham sido carregadas e configuradas corretamente, o
evento `context.load_factories` é disparado. Este evento é importante, pois
é o primeiro evento no *framework* onde o desenvolvedor tem acesso a todos
os objetos *factory* do núcleo do symfony (request, response, user, logging, database,
etc.).

Este é também o momento para se conectar a um outro evento muito útil:
`template.filter_parameters`. Este evento ocorre sempre que um arquivo é processado pelo
[`sfPHPView`](http://trac.symfony-project.org/browser/branches/1.3/lib/view/sfPHPView.class.php)
e permite ao desenvolvedor controlar os parâmetros atualmente passados ao *template*.
`sfContext` aproveita este evento para adicionar alguns parâmetros úteis para cada
*template* (ou seja, `$sf_context`, `$sf_request`, `$sf_params`, `$sf_response`
e `$sf_user`).

Você pode se conectar ao evento `template.filter_parameters` a fim de acrescentar
parâmetros globais personalizados para todos os *templates*. 

### Aproveitando o evento `template.filter_parameters`

Digamos que você decidiu que cada *template* utilizado deve ter acesso à um 
determinado objeto, digamos, um objeto *helper*. Neste caso, você pode adicionar o seguinte
código no `ProjectConfiguration`:

    [php]
    public function setup()
    {
      // ...

      $this->dispatcher->connect('template.filter_parameters', array(
        $this, 'templateFilterParameters'
      ));
    }

    public function templateFilterParameters(sfEvent $event, $parameters)
    {
      $parameters['my_helper_object'] = new MyHelperObject();

      return $parameters;
    }

Agora, cada *template* tem acesso a uma instância do `MyHelperObject` através da variável 
`$my_helper_object`.

### Resumo do `sfContext`

1. Inicialização do `sfContext`
1. Carregamento da *Factory*
1. Eventos notificados:
1. [request.filter_parameters](http://www.symfony-project.org/reference/1_3/en/15-Events#chapter_15_sub_request_filter_parameters)
1. [routing.load_configuration](http://www.symfony-project.org/reference/1_3/en/15-Events#chapter_15_sub_routing_load_configuration)
1. [context.load_factories](http://www.symfony-project.org/reference/1_3/en/15-Events#chapter_15_sub_context_load_factories)
1. Parâmetros globais dos *templates* adicionados

Uma palavra sobre Manipuladores de Configuração (*Config Handlers*)
-------------------------

Os manipuladores de configuração são o coração do sistema de configuração do symfony. Um manipulador 
de configuração é encarregado de *entender* o significado por trás de um arquivo de 
configuração. Cada manipulador de configuração é simplesmente uma classe que é usada para traduzir um conjunto de
arquivos de configuração yaml em um bloco de código PHP que pode ser executado quando 
necessário. Cada arquivo de configuração é atribuído à um manipulador de configuração específico através do 
[arquivo `config_handlers.yml`](http://trac.symfony-project.org/browser/branches/1.3/lib/config/config/config_handlers.yml).

Para deixar claro, o trabalho de um manipulador de configuração *não* é de fazer o processamento (*parse*) dos arquivos YAML
(isto é realizado pelo `sfYaml`). Na verdade, o que os manipuladores de configuração fazem é criar um conjunto
de instruções PHP com base nas informações YAML e salvar as instruções
em um arquivo PHP, que poderá ser eficientemente incluído posteriormente. A versão *compilada* 
de cada arquivo de configuração YAML pode ser encontrada no diretório cache.

O manipulador de configuração mais comumente utilizado é certamente o 
[`sfDefineEnvironmentConfigHandler`](http://trac.symfony-project.org/browser/branches/1.3/lib/config/sfDefineEnvironmentConfigHandler.class.php),
que permite configurações específicas por ambiente.
Esse manipulador de configuração tem o cuidado de buscar apenas as definições de configuração 
do ambiente atual.

Ainda não está convencido? Vamos explorar o 
[`sfFactoryConfigHandler`](http://trac.symfony-project.org/browser/branches/1.3/lib/config/sfFactoryConfigHandler.class.php).
Este manipulador de configuração é usado para compilar o `factories.yml`, que é um dos
mais importantes arquivos de configuração do symfony. Esse manipulador de configuração é muito
especial, pois converte um arquivo de configuração YAML em código PHP que
finalmente instancia as *factories* (todos os componentes importantes que falamos 
anterioremente). Este manipulador é muito mais avançado que os outros, não é?

Despachando e Executando o Pedido (*Request*)
--------------------------------------------

Já falamos o suficiente sobre *factories*, vamos voltar a explicação do processo de despacho (*dispatch*) do pedido.
Após o `sfContext` terminar a inicialização, o último passo é chamar o 
método `dispatch()` do controlador,
[`sfFrontWebController::dispatch()`](http://www.symfony-project.org/api/1_3/sfFrontWebController#method_dispatch).

O processo de despacho (*dispatch*) no symfony é muito simples. Na verdade,
o `sfFrontWebController::dispatch()` simplesmente obtêm os nomes do módulo e da ação 
dos parâmetros do pedido (*request*) e encaminha a aplicação através do 
[`sfController::forward()`](http://www.symfony-project.org/api/1_3/sfController#method_forward).

>**NOTE**
>Neste ponto, se o roteamento não pôde processar qualquer nome de módulo ou ação
>a partir da URL atual, um
>[`sfError404Exception`](http://trac.symfony-project.org/browser/branches/1.3/lib/exception/sfError404Exception.class.php) é
>originado, que irá encaminhar o pedido para o módulo de manipulação do erro 404 (ver
>[`sf_error_404_module` e 
>`sf_error_404_action`](http://www.symfony-project.org/reference/1_3/en/04-Settings#chapter_04_sub_error_404)).
>Note que você pode originar uma exceção em qualquer lugar na sua aplicação para
>atingir esse efeito.

O método `forward` é responsável por uma série de verificações prévias à execução 
bem como preparar a configuração e dados para a ação a ser executada.

Primeiro, o controlador verifica a presença de um arquivo 
[`generator.yml`](http://trac.symfony-project.org/browser/branches/1.3/lib/config/config/generator.yml)
para o módulo atual. Esta verificação é realizada primeiro (depois de limpeza básica no 
nome do módulo e da ação), pois o arquivo de configuração `generator.yml` (se
existir) é responsável por gerar a classe base das ações para o módulo 
(através da seu 
[manipulador de configuração, `sfGeneratorConfigHandler`](http://trac.symfony-project.org/browser/branches/1.3/lib/config/sfGeneratorConfigHandler.class.php)).
Isso é necessário para a próxima etapa, que verifica se o módulo e a ação
existem. Isto é delegado ao controlador, através de
[`sfController::actionExists()`](http://www.symfony-project.org/api/1_3/sfController#method_actionexists),
que, por sua vez, chama o método 
[`sfController::controllerExists()`](http://www.symfony-project.org/api/1_3/sfController#method_controllerexists)
. Aqui, novamente, se o método `actionExists()` falhar, um `sfError404Exception`
é gerado.

>**NOTE**
>O 
>[`sfGeneratorConfigHandler`](http://trac.symfony-project.org/browser/branches/1.3/lib/config/sfGeneratorConfigHandler.class.php) é
>um manipulador de configuração especial que cuida de instanciar a classe correta do gerador (*generator*)
>para o seu módulo e executá-lo. Para mais informações sobre manipuladores de configuração
, ver *Uma palavra sobre manipuladores de configuração* neste capítulo.
>Além disso, para obter mais informações sobre o `generator.yml`, veja o
>[capítulo 6 do Livro de Referência do symfony](http://www.symfony-project.org/reference/1_3/en/06-Admin-Generator).

Não há muito que você pode fazer aqui além de sobrescrever o método 
[`sfApplicationConfiguration::getControllerDirs()`](http://www.symfony-project.org/api/1_3/sfApplicationConfiguration#method_getcontrollerdirs)
na classe de configuração da aplicação. Esse método retorna um array
de diretórios onde os arquivos dos controladores residem, com um parâmetro adicional
para dizer ao symfony se ele deve verificar se os controladores em cada diretório estão 
ativados através da opção de configuração `sf_enabled_modules` no `settings.yml`. 
Por exemplo, `getControllerDirs()` poderia parecer com o seguinte código:

    [php]
    /**
     * Controladores em /tmp/myControllers não precisam ser ativados 
     *para serem detectados
     */
    public function getControllerDirs($moduleName)
    {
      return array_merge(parent::getControllerDirs($moduleName), array(
        '/tmp/myControllers/'.$moduleName => false
      ));
    }

>**NOTE**
>Se a ação não existe, um `sfError404Exception` é acionado.

O próximo passo é recuperar uma instância do controlador que contém a ação.
Isso é tratado através do método 
[`sfController::getAction()`](http://www.symfony-project.org/api/1_3/sfController#method_getaction)
que, como o `actionExists()`, é uma fachada para o método 
[`sfController::getController()`](http://www.symfony-project.org/api/1_3/sfController#method_getcontroller).
Finalmente, a instância do controlador é adicionada na *action stack*.

>**NOTE**
>A *action stack* é uma pilha do estilo FIFO (*First In First Out*) que contém todas as
>ações executadas durante o pedido. Cada item dentro da pilha é representado com 
> um objeto `sfActionStackEntry`. Você sempre pode acessar a pilha com
>`sfContext::getInstance()->getActionStack()` ou
>`$this->getController()->getActionStack()` a partir de uma ação.

Após carregar mais um pouco de configuração, nós estaremos prontos para executar a nossa ação.
A configuração específica para o módulo ainda precisa ser carregada, ela pode ser 
encontrada em dois locais distintos. Primeiro, o symfony procura o arquivo `module.yml`
(normalmente localizado em `apps/frontend/modules/yourModule/config/module.yml`)
que, por ser um arquivo de configuração YAML, usa o cache de configuração. Além disso,
este arquivo de configuração pode declarar o módulo como *interno*, utilizando a 
configuração `mod_yourModule_is_internal` que fará o pedido falhar neste 
ponto já que um módulo interno não pode ser chamado publicamente.

>**NOTE**
>Módulos internos são utilizados para gerar conteúdo de e-mail (através do 
>`getPresentationFor()`, por exemplo). Agora você deve usar outras técnicas, 
>tais como o processamento parcial (`$this->renderPartial()`) em seu lugar.

Agora que o `module.yml` foi carregado, é hora de verificar uma segunda vez se o
módulo atual está habilitado. Na verdade, você pode definir a configuração `mod_$moduleName_enabled`
para `false`, se você deseja desativar o módulo neste momento.

>**NOTE**
>Como mencionado, existem duas maneiras de ativar ou desativar um módulo.
>A diferença é o que acontece quando o módulo é desativado. No primeiro caso,
>quando a configuração `sf_enabled_modules` é verificada, um módulo desabilitado fará 
>uma
>[`sfConfigurationException`](http://trac.symfony-project.org/browser/branches/1.3/lib/exception/sfConfigurationException.class.php)
>ser acionada. Deve ser usado quando deseja-se desativar um módulo de forma permanente. No
>segundo caso, através da configuração `mod_$moduleName_enabled`, um módulo desativado 
>irá fazer a aplicação redirecionar para o módulo desabilitado (ver as configurações [
>`sf_module_disabled_module` e
>`sf_module_disabled_action`](http://www.symfony-project.org/reference/1_3/en/04-Settings#chapter_04_sub_module_disabled)
>). Você deve usar este quando deseja desativar um módulo temporariamente.

A última oportunidade para configurar um módulo se encontra no arquivo `config.php`
(`apps/frontend/modules/yourModule/config/config.php`) onde você pode adicionar 
código PHP arbitrário que será executado no contexto do método 
[`sfController::forward()`](http://www.symfony-project.org/api/1_3/sfController#method_forward)
(ou seja, você tem acesso à instância `sfController` através da variável `$this`, 
pois o código é literalmente executado dentro da classe `sfController`).

### Resumo do Processo de Despacho do Pedido 

1. [`sfFrontWebController::dispatch()`](http://www.symfony-project.org/api/1_3/sfFrontWebController#method_dispatch) é chamado
1. [`sfController::forward()`](http://www.symfony-project.org/api/1_3/sfController#method_forward) é chamado
1. Verifica se há um 'generator.yml'
1. Verifica se o módulo / ação existe
1. Recupera uma lista dos diretórios dos controladores
1. Obtêm uma instância da ação
1. Carrega a configuração do módulo através do `module.yml` e/ou `config.php`

A Cadeia de Filtros
----------------

Agora que toda a configuração foi realizada, é hora de iniciar o trabalho real.
Trabalho real, neste caso específico, é a execução da cadeia de filtros.

>**NOTE**
>A cadeia de filtros do symfony implementa um padrão de *design* conhecido como
[*chain of responsibility*] (http://en.wikipedia.org/wiki/Chain-of-responsibility_pattern).
>É um padrão simples, mas poderoso, que permite ações encadeadas, onde cada
>parte da cadeia é capaz de decidir se deve ou não continuar a
>execução.
>Cada parte da cadeia também é capaz de executar tanto antes como depois do 
>resto das partes da cadeia.

A configuração da cadeia de filtro é obtida a partir do arquivo 
[`filters.yml`](http://trac.symfony-project.org/browser/branches/1.3/lib/config/config/filters.yml) do módulo atual,
razão pela qual a instância da ação é necessária. Esta é sua chance de modificar o
conjunto de filtros executados pela cadeia. Basta lembrar que o filtro `rendering` 
deve ser sempre o primeiro na lista (veremos porque mais tarde). A configuração padrão
dos filtros é a seguinte:

* [`rendering`](http://trac.symfony-project.org/browser/branches/1.3/lib/filter/sfRenderingFilter.class.php)
* [`security`](http://trac.symfony-project.org/browser/branches/1.3/lib/filter/sfSecurityFilter.class.php)
* [`cache`](http://trac.symfony-project.org/browser/branches/1.3/lib/filter/sfCacheFilter.class.php)
* [`execution`](http://trac.symfony-project.org/browser/branches/1.3/lib/filter/sfExecutionFilter.class.php)

>**NOTE**
>É altamente recomendável adicionar seus próprios filtros entre o filtro 
>`security` e o `cache`

### O Filtro `security` 

Uma vez que o filtro `rendering` espera por todos os outros filtros terminarem antes de fazer qualquer coisa, o 
primeiro filtro que realmente executa é o filtro `security`. Este filtro
garante que tudo está certo de acordo com o arquivo de configuração
[`security.yml`](http://trac.symfony-project.org/browser/branches/1.3/lib/config/config/security.yml)
. Especificamente, o filtro encaminha um usuário não autenticado
ao módulo e ação `login` e um usuário com credenciais insuficientes para
o módulo / ação `secure`. Note que este filtro só é executado se
a segurança está habilitada para a determinada ação.

### O Filtro `cache`

Em seguida vem o filtro `cache`. Este filtro tira vantagem da sua capacidade de
evitar que filtros adicionais sejam executados. Na verdade, se o cache estiver ativado,
e se a página solicitada pelo usuário estiver no cache, a ação não é executada. Evidentemente, isso
irá funcionar apenas para uma página totalmente em cache, que não é o caso da maioria
das páginas.

Mas esse filtro tem um segundo pedaço de lógica que é executado após o
filtro `execution`, e pouco antes do filtro `rendering`. Este código é
responsável pela criação dos cabeçalhos HTTP de cache corretos, e de adicionar a página
no cache, se necessário, graças ao método 
[`sfViewCacheManager::setPageCache()`](http://www.symfony-project.org/api/1_3/sfViewCacheManager#method_setpagecache).


### O Filtro `execution`

O último filtro, mas não menos importante, é o filtro `execution` que irá, finalmente, cuidar de
executar a sua lógica de negócio e a manipulação da visão associada.

Tudo começa quando o filtro verifica o cache para a ação atual. 
Naturalmente, se temos algo no cache, a execução da ação atual é
ignorada e a visão `Success` é então executada.

Se a ação não for encontrada no cache, então é hora de executar a 
lógica do controlador `preExecute()`, e, finalmente, executar a ação
. Isto é realizado pela instância da ação através da chamada do
[`sfActions::execute()`](http://www.symfony-project.org/api/1_3/sfActions#method_execute).
Este método não faz muito: ele simplesmente verifica se é possível executar a ação, e, então, 
a executa. Voltando ao filtro, a lógica da ação `postExecute()` é agora executada.

>**NOTE**
>O valor de retorno de sua ação é muito importante, uma vez que irá determinar
>que visão será executada. Por padrão, se nenhum valor de retorno é encontrado,
>é assumido o `sfView::SUCCESS` (que se traduz, você adivinhou, em `Success`, como em 
>`indexSuccess.php`).

Seguindo com o processo, agora está na hora da visão. O filtro verifica por dois valores de retorno especiais 
que a sua ação pode ter retornado, `sfView::HEADER_ONLY`
e `sfView::NONE`. Cada um faz exatamente o que o seu nome sugere: envia apenas cabeçalhos HTTP
(internamente tratado através do 
[`sfWebResponse::setHeaderOnly()`](http://www.symfony-project.org/api/1_3/sfWebResponse#method_setheaderonly))
e não exibe nenhuma página.

>**NOTE**
>Os nomes internos disponíveis para a visão são: `ALERT`, `ERROR`, `INPUT`, `NONE` e `SUCCESS`. Mas você pode
>basicamente retornar o que quiser.

Uma vez que sabemos que *vamos* exibir alguma coisa, nós estamos prontos para entrar na 
etapa final do filtro: a execução de visão atual.

A primeira coisa que fazemos é recuperar um objeto [`sfView`](http://trac.symfony-project.org/browser/branches/1.3/lib/view/sfView.class.php)
através do método [`sfController::getView()`](http://www.symfony-project.org/api/1_3/sfController#method_getview). Este objeto pode vir de
dois lugares diferentes. Primeiro, você poderia ter um objeto de exibição personalizado para esta ação específica
(assumindo que o módulo/ação atual é, vamos manter simples,
módulo/ação) `actionSuccessView` ou `module_actionSuccessView` em um arquivo
chamado `apps/frontend/modules/module/view/actionSuccessView.class.php`.
Caso contrário, a classe definida na configuração `mod_module_view_class`
será usada. O valor padrão desta opção é [`sfPHPView`](http://trac.symfony-project.org/browser/branches/1.3/lib/view/sfPHPView.class.php).

>**TIP**
>Usando a sua própria classe de visão você tem a oportunidade de executar alguma lógica de visão específica, 
>através do método [`sfView::execute()`](http://www.symfony-project.org/api/1_3/sfView#method_execute)
>. Por exemplo, você pode instanciar a sua própria *template engine*. 

Existem três modos de renderização possível para executar a visão:

1. `sfView::RENDER_NONE`: equivalente a `sfView::NONE`, anula qualquer renderização de ser executada.
1. `sfView::RENDER_VAR`: preenche a apresentação da ação, que depois pode ser acessada através do método [`sfActionStackEntry::getPresentation()`](http://www.symfony-project.org/api/1_3/sfActionStackEntry#method_getpresentation). 
1. `sfView::RENDER_CLIENT`, o modo padrão, irá processar a visão e incluir o conteúdo da resposta.

>**NOTE**
>Na verdade, o modo de processamento é utilizado apenas através do método 
>[`sfController::getPresentationFor()`](http://www.symfony-project.org/api/1_3/sfController#method_getpresentationfor) que retorna o processamento para um 
>determinado módulo/ação.

### O Filtro `rendering`

Estamos quase terminando agora, apenas um último passo. A cadeia de filtro quase
terminou a execução, mas você se lembra do filtro `rendering`? Ele está aguardando 
desde o início da cadeia, que todos os filtros concluam o seu trabalho para que ele possa 
fazer o seu próprio. Ou seja, o filtro `rendering` envia o conteúdo da resposta para o navegador, usando
[`sfWebResponse::send()`](http://www.symfony-project.org/api/1_3/sfWebResponse#method_send).

### Resumo da execução da cadeida de filtros

1. A cadeia de filtros é instanciada com a configuração do arquivo `filters.yml`
1. O filtro `security` verifica as autorizações e credenciais
1. O filtro `cache` manipula o cache para a página atual
1. O filtro `execution` executa a ação
1. O filtro `rendering` envia a resposta através do `sfWebResponse`

Resumo Global
--------------

1. Obtenção da configuração da aplicação
1. Criação de uma instância do `sfContext`
1. Inicialização do `sfContext`
1. Carregamento das *Factories*
1. Eventos notificados:
1. ~`request.filter_parameters`~
1. ~`routing.load_configuration`~
1. ~`context.load_factories`~
1. Adicionados os parâmetros dos *templates* globais
1. [`sfFrontWebController::dispatch()`](http://www.symfony-project.org/api/1_3/sfFrontWebController#method_dispatch) é chamado
1. [`sfController::forward()`](http://www.symfony-project.org/api/1_3/sfController#method_forward) é chamado
1. Verifica se existe um `generator.yml`
1. Verifique se o módulo/ação existe
1. Recupera uma lista de diretórios dos controladores
1. Obtêm uma instância da ação
1. Carrega a configuração do módulo através do `module.yml` e/ou `config.php`
1. A cadeia de filtros é instanciada com a configuração do arquivo `filters.yml`
1. O filtro `security` verifica as autorizações e credenciais
1. O filtro `cache` manipula o cache para a página atual
1. O filtro `execution` executa a ação
1. O filtro `rendering` envia a resposta através do `sfWebResponse`

Reflexões finais
--------------

É isto! A requisição (*request*) foi tratada e agora estamos prontos para processar a próxima. 
Naturalmente, poderíamos escrever um livro inteiro sobre os processos internos do symfony, então
este capítulo serve apenas como uma visão geral. Você é mais do que bem-vindo para explorar
os códigos-fonte você mesmo - que é, e sempre será, a melhor maneira de aprender o 
funcionamento interno de qualquer *framework* ou biblioteca.