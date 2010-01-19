
Estendendo a barra de ferramenta de depuração Web
==================================

* por Ryan Weaver*

Por padrão, a barra de ferramentas de depuração web do symfony contém uma variedade de ferramentas que auxiliam
com a depuração, melhoria de desempenho e muito mais. A ferramenta de depuração Web
consiste de várias ferramentas, chamada *painéis de debug web*, que se relacionam com a memória cache,
config, logs, uso de memória, versão do symfony e tempo de processamento. Além disso, o
symfony 1,3 introduz dois novos *painéis de debug web* para informações da `view`
e depuração da `mail`.

![Ferramenta de depuração Web](http://www.symfony-project.org/images/more-with-symfony/web_debug_01.png "A ferramentas de depuração web com widgets padrão do 1,3 symfony")

A partir do symfony 1.2, os desenvolvedores podem criar facilmente seus próprios *painéis de depuração web* e 
adicioná-los à barra de feramenta de depuração web. Neste capítulo, configuraremos um novo *painel de depuração web*
e depois o rodaremos com todas as diferentes ferramentas e personalizações disponíveis.
Além disso, o [ac2009WebDebugPlugin](http://www.symfony-project.org/plugins/ac2009WebDebugPlugin)
contém vários painéis úteis e interessante que empregam algumas das 
técnicas utilizadas neste capítulo.

Criando um novo painel de depuração Web
------------------------------

Os componentes individuais da ferramenta de depuração web são conhecidos como os *painéis de depuração web*
e são classes especiais que estendem a classe `~sfWebDebugPanel~`. Criar um novo 
painel é realmente muito fácil. Crie um arquivo chamado `sfWebDebugPanelDocumentation.class.php`
em seu diretorio `lib/debug/` (você precisa criar este diretório):

    [php]
    //lib/debug/ sfWebDebugPanelDocumentation.class.php
    class acWebDebugPanelDocumentation extends sfWebDebugPanel
    (
      function getTitle()
      (
        return '<img src="/images/documentation.png" alt="Documentation Shortcuts" height="16" width="16" /> docs';
      )

      public function getPanelTitle()
      (
        return 'Documentation';
      )
      
      public function getPanelContent()
      (
        $content = 'Placeholder Painel de Conteúdo';
        
        return $content;
      )
    )

No mínimo, todos os painéis de depuração devem implementar os métodos `getTitle()`, `getPanelTitle()`
e `getPanelContent()`.

* ~`SfWebDebugPanel::getTitle()`~: Determina como o painel irá aparecer na
   barra de ferramentas. Como a maioria dos painéis, o nosso painel personalizado inclui um pequeno ícone
   e um nome curto para o painel.

* ~`SfWebDebugPanel::getPanelTitle()`~: Usado como o texto para a tag `h1`
   aparecerá no topo do conteúdo do painel. Este também é usado como atributo `title`
   da tag link que envolve o ícone na barra de ferramentas e, como tal,
   *não* deve incluir qualquer código HTML.

* ~`SfWebDebugPanel::getPanelContent()`~: gera o conteúdo HTML que
   será exibido quando você clicar no ícone do painel.

A única etapa restante é para notificar o aplicativo que você deseja incluir
o novo painel na sua barra de ferramentas. Para fazer isso, adicione um ouvinte ao
evento `debug.web.load_panels`, que é notificado quando a ferramenta de depuração web
está recolhendo os painéis potencial. Primeiramente, modificar o arquivvo
`config/ProjectConfiguration.class.php` para ouvir o evento:

    [php]
    //Config/ProjectConfiguration.class.php
    function initialize()
    (
      //...

      $this->dispatcher->connect('debug.web.load_panels', array(
        'acWebDebugPanelDocumentation',
        'listenToLoadDebugWebPanelEvent'
      ));
    )

Agora, vamos adicionar a funçao ouvinte `listenToLoadDebugWebPanelEvent()` ao
`acWebDebugPanelDocumentation.class.php` para adicionar o painel para a barra de ferramentas:

    [php]
    //Lib/debug/sfWebDebugPanelDocumentation.class.php
    public static function listenToLoadDebugWebPanelEvent(sfEvent $event)
    (
      $event->getSubject()->setPanel(
        'documentation',
        new self($event->getSubject())
      );
    )

É isto! Atualize seu navegador e verá imediatamente o resultado.

![Barra de ferramenta de depuração Web](http://www.symfony-project.org/images/more-with-symfony/web_debug_02.png "A barra de ferramenta web com um novo painel personalizado")

>**TIP**
> A partir do symfony 1.3, um url parâmetro para o `sfWebDebugPanel pode ser usado para automaticamente
> abrir um determinado painel de depuração web no carregamento da página. Por exemplo, adicionando
> `?sfWebDebugPanel=documentation` ao final da URL seria automaticamente
> aberto o painel de documentação que acabou de ser adicionado. sto pode ser bastante útil
> ao construir de painéis personalizados.

Os Três Tipos de painéis depuração Web
-----------------------------------

Nos bastidores, existem apenas três tipos diferentes de painéis de depuração web.

### O painel do tipo *Icon-Only*

O tipo mais básico de painel é um que mostra um ícone e texto na barra de ferramentas
e nada mais. O exemplo clássico é o painel `memory`, que exibe
o uso de memória, mas não faz nada quando clicado. Para criar um painel *icon-only*,
basta definir o `getPanelContent()` para retornar uma seqüência vazia. A única saída
do painel vem do método `getTitle()`:

    [php]
    public function getTitle()
    (
      $totalMemory = sprintf('% .1f',(memory_get_peak_usage(true)/ 1024));

      return '<img src="'.$this->webDebug->getOption('image_root_path').'/memory.png" alt="Memory" /> '.$totalMemory.' KB';
    )

    public function getPanelContent()
    (
      return;
    )

### O painel do tipo *Link*

Como o painel *icon-only*, um painel *link* constituí de um painel sem conteúdo. Ao contrário do
painel *only-icon*, no entanto, ao clicar em um painel *link* sobre a barra de ferramentas
levá-lo-a para um URL especificado através do método `getTitleUrl()` painel. Para criar
um painel *link*, configure o `getPanelContent()` para retornar uma seqüência vazia e adicione
um método `getTitleUrl()` método para a classe.

    [php]
    public function getTitleUrl()
    (
      //Ligação para um uri externo
      return 'http://www.symfony-project.org/api/1_3/';

      // Ou link para uma rota em seu aplicativo
      rerurn url_for( 'homepage');
    )

    public function getPanelContent()
    (
      return;
    )

### O painel do tipo *Content*

De longe, o tipo mais comum de painel é um painel de *content*. Estes painéis têm
um corpo cheio de conteúdo HTML que é exibida quando você clica no painel
na barra de ferramentas de depuração. Para criar esse tipo de painel, simplesmente certifique-se que
o `getPanelContent()` retorna mais de uma seqüência vazia.

Personalizando o Painel Content
-------------------------

Agora que você criou e adicionou seu painel personalizado de depuração à barra de ferramentas,
adicionar conteúdo pode ser feito facilmente através do método `getPanelContent()`.
Symfony fornece vários métodos para ajudá-lo a tornar este conteúdo rico
e utilizável.

### ~`SfWebDebugPanel::setStatus ()`~

Por padrão, cada painel é exibido na barra de ferramenta de depuração web usando o fundo padrão cinza.
Isso pode ser alterado, porém, um fundo laranja ou vermelho quando se
requer atenção especial algum conteúdo dentro do painel.

![Barra de ferramentas de depuração Web com o erro](http://www.symfony-project.org/images/more-with-symfony/web_debug_05.png "A web debug toolbar mostrando um estado de erro no logs")

Para alterar a cor de fundo do painel, basta empregar o método `setStatus()`.
Este método aceita qualquer `prioridade` constante da
da classe [sfLogger](http://www.symfony-project.org/api/1_3/sfLogger).
Em particular, há três níveis de status diferentes, que correspondem
para as três diferentes cores de fundo de um painel (cinza, laranja e vermelho).
Mais comumente, o método `setStatus()` será chamado de dentro do
método `getPanelContent()`, quando tenha ocorrido alguma condição que precisa
atenção especial.

    [php]
    public function getPanelContent()
    (
      // ...

      // Definir o fundo para cinza (padrão)
      $this->setStatus(sfLogger:: INFO);

      // Definir o fundo de laranja
      $this->setStatus(sfLogger:: WARNING);

      // Definir o fundo para vermelho
      $this->setStatus(sfLogger:: ERR);
    )

### ~`SfWebDebugPanel::getToggler()`~

Uma das características mais comum em todas as ferramentas de depuração web existentes é o toggler:
um elemento visual que esconde/mostra um recipiente de conteúdo quando clicado.

![Web Debug Toggler](http://www.symfony-project.org/images/more-with-symfony/web_debug_03.png "A web debug toggler em ação")

Esta funçao pode ser facilmente usada no painel personalizado de depuração com a função
`getToggler()`. Por exemplo, suponha que queremos mudar uma lista de
conteúdo em um painel:

    [php]
    public function getPanelContent()
    (
      $listContent = '<ul id="debug_documentation_list" style="display: none;">
        <li> List Item 1 </ li>
        <li> List Item 2 </ li>
      </ ul> ';

      $toggler = $this->getToggler('debug_documentation_list', 'Toggle list');

      return sprintf('<h3>List Items %s</h3>%s', $toggler, $listContent);
    )

O `getToggler` leva dois argumentos: o DOM `id` do elemento para mudar e
um `título` para definir como o atributo `título` do link toggler. A escolha é sua.
para criar o elemento DOM com o dado `id` atributo, bem como qualquer etiqueta descritiva
(por exemplo "Os itens da lista ") para o toggler.

### ~`SfWebDebugPanel::getToggleableDebugStack()`~

Similar ao `getToggler()`, `getToggleableDebugStack ()` processa uma seta clicável
que alterna a exibição de um conjunto de conteúdos. Neste caso, o conjunto de conteúdo é
uma depuração stack trace. Esta função é útil se você precisar exibir resultados de log
para uma classe personalizada. Por exemplo, suponha que realizamos alguns logs personalizado em
uma classe chamada `myCustomClass`:

    [php]
    MyCustomClass classe
    {
      public function doSomething()
      {
        $dispatcher = sfApplicationConfiguration::getActive()
          ->getEventDispatcher();

        $ dispatcher-> notify (sfEvent novo ($ this, 'application.log', array (
          'priority' => sfLogger::INFO,
          'Beginning execution of myCustomClass::doSomething()',
        )));
      }
    }

Como exemplo, vamos exibir uma lista das mensagens de log relacionados à
`MyCustomClass` completo com depuração stack trace para cada um.

    [php]
    public function getPanelContent()
    {
      // Recupera todas as mensagens de log para a solicitação atual
      $logs = $this->webDebug->getLogger()->getLogs();

      $logList ='';
      foreach ($logs as $log)
      {
        if ($log['type'] == 'myCustomClass')
        {
          $logList .= sprintf('<li>%s %s</li>',
            $log['message'],
            $this->getToggleableDebugStack($log['debug_backtrace'])
          };
        }
      }

      return sprintf('<ul>%s</ul>', $logList);
    }

![Web Debug Toggleable Debug](http://www.symfony-project.org/images/more-with-symfony/web_debug_04.png "Um web debug cambiavel depurando pilha em acção")

>**NOTE**
>Mesmo sem a criação de um painel personalizado, as mensagens de log para `myCustomClass`
>seria exibida no painel de registros. A vantagem aqui é simplesmente
>recolher este subconjunto de mensagens de log em um local e controle de sua saída.

### ~`SfWebDebugPanel::formatFileLink()`~

Novo no symfony 1,3 é a possibilidade de clicar em arquivos na barra de ferramentas de depuração web e
mandar abrir no seu editor de texto preferido. Para obter mais informações, consulte o
artigo ["What's new"]http://www.symfony-project.org/tutorial/1_3/en/whats-new)
para o symfony 1.3.

Para ativar esse recurso para qualquer caminho de arquivo particular, o `formatFileLink()` deve
ser utilizado. Para além do arquivo em si, uma linha exata opcionalmente pode ser alvejado.
Por exemplo, o código seguinte link para a linha 15 do `config/ProjectConfiguration.class.php`:

    [php]
    public function getPanelContent()
    {
      $content='';

      //...

      $path = sfConfig::get('sf_config_dir') . '/ ProjectConfiguration.class.php';
      $content .= $this->formatFileLink($path, 15, 'Project Configuration');

      return $content;
    }

Tanto o segundo argumento (número da linha) quanto o terceiro argumento (o link de texto) são
opcionais. Se nenhum argumento "texto link" é especificado, o caminho do arquivo será mostrado
como o texto do link.

>**NOTE**
>Antes de testar, verifique se você configurou o arquivo de novo recurso. Este
> recurso pode ser configurado através da chave `sf_file_link_format em settings.yml ou
> através da configuração `file_link_format` em
>[xdebug](http://xdebug.org/docs/stack_trace#file_link_format). O último
>método garante que o projeto não está vinculado a uma IDE específica.

Outros truques com a barra de depuração da Web
---------------------------------------

Em grande parte, a magia de seu painel de depuração web personalizado será formada pelo
conteúdo e informações que você escolher para mostrar. Há, no entanto,
alguns truques mais a explorar.

### Removendo Default Painéis

Por padrão, o symfony automaticamente carrega vários painéis de depuração web em sua
Barra de ferramentas de web. Ao utilizar o evento `debug.web.load_panels` , estes painéis padrões 
também podem ser facilmente removidos. Use a função de ouvinte declarada
anteriormente, mas substitua o corpo com a função `removePanel()`. O seguinte
código irá remover o painel `memória` da barra de ferramentas:

    [php]
    public static function listenToLoadDebugWebPanelEvent(sfEvent $event)
    {
      $event->getSubject()->removePanel('memory');
    }

###Acessando os parâmetros de solicitação de um Painel

Uma das coisas mais comum necessária dentro de um painel de debug é o pedido de
parâmetros. Digamos, por exemplo, que você deseja exibir informações de
um banco de dados sobre um objeto `evento` no banco de dados baseado fora de um `event_id`
parâmetro de solicitação:

    [php]
    $parameters = $this->webDebug->getOption('request_parameters');
    if (isset($parameters['event_id']))
    {
      $event = Doctrine::getTable('Event')->find($parameters['event_id']);
    }

###Ocultar um Painel Condicionalmente

Às vezes, o painel pode não ter qualquer informação útil para mostrar para a
solicitação atual. Nessas situações, você pode optar por esconder o seu painel
completamente. Vamos supor que, no exemplo anterior, que o painel personalizado
não apresente nenhuma informação a não ser o parâmetro `event_id` do pedito.
Para ocultar o painel, basta não retornar conteúdo para o método `getTitle()`:

    [php]
    public function getTitle()
    {
      $parameters = $this->webDebug->getOption('request_parameters');
      if(!isset($parameters[´event_id´]))
      {
        return;
      }

      return '<img src="/acWebDebugPlugin/images/documentation.png" alt="Documentation Shortcuts" height="16" width="16" /> docs';
    }

Reflexões finais
--------------

A ferramenta de depuração web existe para tornar a vida do desenvolvedor mais simples, mas é mais
do que uma exposição passiva da informação. Ao adicionar painéis personalizado de depuração, o
potencial da web ferramentas de depuração é limitado apenas pela imaginação do
desenvolvedor. O [ac2009WebDebugPlugin](http://www.symfony-project.org/plugins/ac2009WebDebugPlugin)
inclui apenas alguns dos painéis que poderiam ser criados. Sinta-se livre para criar
o seu próprio.