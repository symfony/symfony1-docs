Estendendo a Barra de Ferramenta para Debug Web
===============================================

*por Ryan Weaver*

Por padrão, a barra de ferramentas para debug web do symfony contém uma variedade de ferramentas que auxiliam
na depuração, melhoria de desempenho e muito mais. A *Barra de Ferramenta para Debug Web*
consiste de várias ferramentas, chamada *painéis de debug web*, que está relacionada com a memória cache,
config, logs, uso de memória, versão do symfony e tempo de processamento. Além disso, o
symfony 1.3 introduz dois novos *painéis de debug web* para exibir informações da `visão` (*view*)
e depuração de `e-mail` (*mail*).

![*Barra de Ferramenta para Debug Web*](http://www.symfony-project.org/images/more-with-symfony/web_debug_01.png "A *Barra de Ferramenta para Debug Web* com widgets padrão do symfony 1.3")

Desde a versão 1.2 do symfony, os desenvolvedores podem criar facilmente seus próprios *painéis de debug web* e 
adicioná-los à *Barra de Ferramenta para Debug Web*. Neste capítulo, configuraremos um novo *painel de debug web*
e, depois, utilizaremos todas as diferentes ferramentas e personalizações disponíveis.
Além disso, o [ac2009WebDebugPlugin](http://www.symfony-project.org/plugins/ac2009WebDebugPlugin)
contém vários painéis úteis e interessantes que empregam algumas das 
técnicas utilizadas neste capítulo.

Criando um novo *Painel de Debug Web*
-------------------------------------

Os componentes individuais da *Barra de Ferramentas para Debug Web* são conhecidos como *painéis de debug web*
e são classes especiais que estendem a classe ~`sfWebDebugPanel`~. Criar um novo 
painel é realmente muito fácil. Crie um arquivo chamado `sfWebDebugPanelDocumentation.class.php`
em seu diretorio `lib/debug/` (você precisa criar este diretório):

    [php]
    // lib/debug/sfWebDebugPanelDocumentation.class.php
    class acWebDebugPanelDocumentation extends sfWebDebugPanel
    {
      public function getTitle()
      {
        return '<img src="/images/documentation.png" alt="Documentation Shortcuts" height="16" width="16" /> docs';
      }

      public function getPanelTitle()
      {
        return 'Documentation';
      }
      
      public function getPanelContent()
      {
        $content = 'Placeholder Panel Content';
        
        return $content;
      }
    }

No mínimo, todos os painéis de debug devem implementar os métodos `getTitle()`, `getPanelTitle()`
e `getPanelContent()`.

* ~`SfWebDebugPanel::getTitle()`~: Determina como o painel irá aparecer na
   barra de ferramenta. Como a maioria dos painéis, o nosso painel personalizado inclui um pequeno ícone
   e um nome curto no painel.

* ~`SfWebDebugPanel::getPanelTitle()`~: Usado como o texto para a *tag* `h1`
   que aparecerá no topo do conteúdo do painel. Também é usado como atributo `title`
   da *tag link* que envolve o ícone na barra de ferramentas e, como tal,
   *não* deve incluir qualquer código HTML.

* ~`SfWebDebugPanel::getPanelContent()`~: gera o conteúdo HTML que
   será exibido quando você clicar no ícone do painel.

A única etapa restante é para notificar a aplicação que você deseja incluir
o novo painel na sua barra de ferramentas. Para fazer isso, adicione um *listener* ao
evento `debug.web.load_panels`, que é notificado quando a *Barra de Ferramenta para Debug Web*
está coletando os potenciais painéis. Primeiramente, modificar o arquivvo
`config/ProjectConfiguration.class.php` para ouvir (*listen*) o evento:

    [php]
    // config/ProjectConfiguration.class.php
    public function initialize()
    {
      // ...

      $this->dispatcher->connect('debug.web.load_panels', array(
        'acWebDebugPanelDocumentation',
        'listenToLoadDebugWebPanelEvent'
      ));
    }

Agora, vamos adicionar a função *listener* `listenToLoadDebugWebPanelEvent()` ao
`acWebDebugPanelDocumentation.class.php` para adicionar o painel na barra de ferramentas:

    [php]
    // lib/debug/sfWebDebugPanelDocumentation.class.php
    public static function listenToLoadDebugWebPanelEvent(sfEvent $event)
    {
      $event->getSubject()->setPanel(
        'documentation',
        new self($event->getSubject())
      );
    }

É isto! Atualize seu navegador e verá imediatamente o resultado.

![*Barra de ferramenta para Debug Web*](http://www.symfony-project.org/images/more-with-symfony/web_debug_02.png "A *Barra de ferramenta para Debug Web* com um novo painel personalizado")

>**TIP**
>A partir do symfony 1.3, um parâmetro url `sfWebDebugPanel pode ser usado para automaticamente
>abrir um determinado painel de debug web no carregamento da página. Por exemplo, adicionando
>`?sfWebDebugPanel=documentation` ao final da URL será automaticamente
>aberto o painel de documentação que acabamos de adicionar. Isto pode ser muito útil
>ao construir painéis personalizados.

Os Três Tipos de *Painéis de Debug Web*
---------------------------------------

Nos bastidores, existem apenas três tipos diferentes de *Painéis de Debug Web*.

### O painel do tipo *Icon-Only*

O tipo mais básico de painel é o que mostra um ícone e texto na barra de ferramentas
e nada mais. O exemplo clássico é o painel `memory`, que exibe
o uso de memória, mas não faz nada quando clicado. Para criar um painel *icon-only*,
basta definir o `getPanelContent()` para retornar uma string vazia. A única saída
do painel vem do método `getTitle()`:

    [php]
    public function getTitle()
    {
      $totalMemory = sprintf('%.1f', (memory_get_peak_usage(true) / 1024));

      return '<img src="'.$this->webDebug->getOption('image_root_path').'/memory.png" alt="Memory" /> '.$totalMemory.' KB';
    }

    public function getPanelContent()
    {
      return;
    }

### O painel do tipo *Link*

Como o painel *icon-only*, um painel *link* consiste de um painel sem conteúdo. No entanto, 
ao contrário do painel *only-icon*, ao clicar em um painel *link* na barra de ferramentas
você será direcionado à URL especificada através do método `getTitleUrl()` do painel. Para criar
um painel *link*, configure o `getPanelContent()` para retornar uma seqüência vazia e adicione
um método `getTitleUrl()` na classe.

    [php]
    public function getTitleUrl()
    {
      // link to an external uri
      return 'http://www.symfony-project.org/api/1_3/';

      // or link to a route in your application
      return url_for('homepage');
    }

    public function getPanelContent()
    {
      return;
    }

### O painel do tipo *Content*

De longe, o tipo mais comum de painel é um painel *content*. Estes painéis têm
um corpo cheio de conteúdo HTML que é exibido quando você clica no painel
na *Barra de Ferramentas para Debug Web*. Para criar esse tipo de painel, simplesmente 
certifique-se que o `getPanelContent()` retorna mais do que uma string vazia.

Personalizando o Painel *Content*
-------------------------

Agora que você criou e adicionou seu painel personalizado de debug à barra de ferramentas,
a adição de conteúdo poderá ser realizada facilmente através do método `getPanelContent()`.
O symfony fornece vários métodos para ajudá-lo a tornar este conteúdo rico
e utilizável.

### ~`SfWebDebugPanel::setStatus()`~

Por padrão, cada painel é exibido na *Barra de Ferramentas para Debug Web* usando um 
fundo padrão cinza. Mas você pode alterar para um fundo laranja ou vermelho quando se
requer atenção especial algum conteúdo dentro do painel.

![*Barra de Ferramentas para Debug Web* com o erro](http://www.symfony-project.org/images/more-with-symfony/web_debug_05.png "A *Barra de Ferramentas para Debug Web* mostrando um estado de erro nos logs")

Para alterar a cor de fundo do painel, basta utilizar o método `setStatus()`.
Este método aceita qualquer constante `priority` da classe 
[sfLogger](http://www.symfony-project.org/api/1_3/sfLogger).
Em particular, há três níveis de status diferentes, que correspondem
as três diferentes cores de fundo de um painel (cinza, laranja e vermelho).
Mais comumente, o método `setStatus()` será chamado de dentro do
método `getPanelContent()`, quando ocorreu alguma condição que precisa
de atenção especial.

    [php]
    public function getPanelContent()
    {
      // ...

      // set the background to gray (the default)
      $this->setStatus(sfLogger::INFO);

      // set the background to orange
      $this->setStatus(sfLogger::WARNING);

      // set the background to red
      $this->setStatus(sfLogger::ERR);
    }

### ~`SfWebDebugPanel::getToggler()`~

Uma das características mais comuns em todas as *Barra de Ferramentas para Debug Web* existentes é o *toggler*:
um elemento visual em forma de uma seta que esconde/exibe conteúdo quando clicado.

![Web Debug Toggler](http://www.symfony-project.org/images/more-with-symfony/web_debug_03.png "O *web debug toggler* em ação")

Esta função pode ser facilmente usada no painel personalizado de debug com a função
`getToggler()`. Por exemplo, suponha que queremos mudar uma lista de
conteúdo em um painel:

    [php]
    public function getPanelContent()
    {
      $listContent = '<ul id="debug_documentation_list" style="display: none;">
        <li>List Item 1</li>
        <li>List Item 2</li>
      </ul>';

      $toggler = $this->getToggler('debug_documentation_list', 'Toggle list');

      return sprintf('<h3>List Items %s</h3>%s',  $toggler, $listContent);
    }

O `getToggler` possui dois argumentos: o `id` do elemento e
um `título` para definir como o atributo `title` do link *toggler*. Você é que deverá  
criar o elemento DOM com o atributo `id`, bem como qualquer *label* descritiva
(por exemplo "Os itens da lista") para o *toggler*.

### ~`SfWebDebugPanel::getToggleableDebugStack()`~

Similar ao `getToggler()`, o `getToggleableDebugStack()` processa uma seta clicável
que alterna a exibição de um conjunto de conteúdos. Neste caso, o conjunto de conteúdo é
um *debug stack trace*. Esta função é útil se você precisar exibir resultados de log
para uma classe personalizada. Por exemplo, suponha que realizamos alguns logs personalizado em
uma classe chamada `myCustomClass`:

    [php]
    class myCustomClass
    {
      public function doSomething()
      {
        $dispatcher = sfApplicationConfiguration::getActive()
          ->getEventDispatcher();

        $dispatcher->notify(new sfEvent($this, 'application.log', array(
          'priority' => sfLogger::INFO,
          'Beginning execution of myCustomClass::doSomething()',
        )));
      }
    }

Como exemplo, vamos exibir uma lista das mensagens de log relacionados à
`MyCustomClass` completo com *debug stack trace* para cada um.

    [php]
    public function getPanelContent()
    {
      // retrieves all of the log messages for the current request
      $logs = $this->webDebug->getLogger()->getLogs();

      $logList = '';
      foreach ($logs as $log)
      {
        if ($log['type'] == 'myCustomClass')
        {
          $logList .= sprintf('<li>%s %s</li>',
            $log['message'],
            $this->getToggleableDebugStack($log['debug_backtrace'])
          );
        }
      }

      return sprintf('<ul>%s</ul>', $logList);
    }

![Web Debug Toggleable Debug](http://www.symfony-project.org/images/more-with-symfony/web_debug_04.png "Exibindo um debug web alternável")

>**NOTE**
>Mesmo sem a criação de um painel personalizado, as mensagens de log para `myCustomClass`
>seriam exibidas no painel de logs. A vantagem aqui é simplesmente
>reunir este subconjunto de mensagens de log em um local e controlar a sua saída.

### ~`SfWebDebugPanel::formatFileLink()`~

A possibilidade de clicar em arquivos na *Barra de Ferramentas para Debug Web* e
abrir no seu editor de texto preferido é outra novidade no symfony 1.3. Para obter mais informações, consulte o
artigo ["What's new"](http://www.symfony-project.org/tutorial/1_3/en/whats-new)
para o symfony 1.3.

Para ativar esse recurso para qualquer caminho de arquivo em particular, o `formatFileLink()` deve
ser utilizado. Além do arquivo em si, poderá ser, opcionalmente, direcionado para uma linha exata.
Por exemplo, o seguinte código deverá linkar para a linha 15 do `config/ProjectConfiguration.class.php`:

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
opcionais. Se nenhum argumento de "texto do link" for especificado, o caminho do arquivo será mostrado
como o texto do link.

>**NOTE**
>Antes de testar, verifique se você configurou o novo recurso de link de arquivo. Este
>recurso pode ser configurado através da chave `sf_file_link_format` no settings.yml ou
>através da configuração `file_link_format` no 
>[xdebug](http://xdebug.org/docs/stack_trace#file_link_format). O último
>método garante que o projeto não está vinculado à uma IDE específica.

Outros truques com a *Barra de Ferramentas para Debug Web*
---------------------------------------

Em grande parte, a magia de seu painel de debug web personalizado será formada pelo
conteúdo e informações que você decidir mostrar. Há, no entanto,
alguns truques mais a explorar.

### Removendo os Painéis Padrão

Por padrão, o symfony automaticamente carrega vários painéis de debug web em sua
*Barra de ferramentas para Debug Web*. Ao utilizar o evento `debug.web.load_panels`, estes painéis padrões 
também podem ser facilmente removidos. Use a mesma função *listener* declarada
anteriormente, mas substitua o corpo com a função `removePanel()`. O seguinte
código irá remover o painel `memory` da barra de ferramentas:

    [php]
    public static function listenToLoadDebugWebPanelEvent(sfEvent $event)
    {
      $event->getSubject()->removePanel('memory');
    }

### Acessando os Parâmetros do Pedido (*Request*) a partir de um Painel

Uma das coisas mais comumente necessárias dentro de um painel de debug são os parâmetros
do pedido. Digamos, por exemplo, que você deseja exibir informações de
um banco de dados sobre um objeto `Event` no banco de dados com base no parâmetro 
do pedido `event_id`:

    [php]
    $parameters = $this->webDebug->getOption('request_parameters');
    if(isset($parameters['event_id']))
    {
      $event = Doctrine::getTable('Event')->find($parameters['event_id']);
    }

### Ocultar um Painel Condicionalmente

Às vezes, o painel pode não possuir informação útil para mostrar para a
solicitação atual. Nessas situações, você pode optar por esconder o seu painel
completamente. Vamos supor que, no exemplo anterior, que o painel personalizado
não apresente nenhuma informação a não ser o parâmetro `event_id` do pedito.
Para ocultar o painel, basta não retornar conteúdo no método `getTitle()`:

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

A *Barra de ferramentas para Debug Web* existe para tornar a vida do desenvolvedor mais simples, porém é mais
do que uma exposição passiva da informação. Ao adicionar painéis personalizados de debug, o
potencial da *Barra de ferramentas para Debug Web* é limitado apenas pela imaginação do
desenvolvedor. O [ac2009WebDebugPlugin](http://www.symfony-project.org/plugins/ac2009WebDebugPlugin)
inclui apenas alguns dos painéis que poderiam ser criados. Sinta-se livre para criar
os seus próprios.