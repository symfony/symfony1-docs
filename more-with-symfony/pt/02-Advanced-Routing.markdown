Roteamento Avançado
================

*por Ryan Weaver*

Em sua essência, o framework de roteamento é um mapa que liga cada URL a uma localização
específica dentro de um projeto symfony e vice-versa. Ele pode facilmente criar belas
URLs mesmo permanecendo completamente independente da lógica de aplicação. Com
melhorias realizadas em versões recentes do symfony, o framework de roteamento agora vai muito
além.

Este capítulo ilustra como criar uma simples aplicação web, onde
cada cliente utiliza um subdomínio separado (por exemplo `client1.mydomain.com` e
`client2.mydomain.com`). Estendendo o framework de roteamento, isto torna-se uma tarefa
bem simples.

>**NOTE**
>Este capítulo requer que você utilize o Doctrine como *ORM* para o seu projeto.

Configuração do Projeto: Um CMS para Muitos Clientes
-------------------------------------

Neste projeto, uma empresa fictícia - *Sympal Builder* - quer criar um
*CMS* para que seus clientes possam construir sites como subdomínios de `sympalbuilder.com`.
Especificamente, o cliente XXX pode visualizar seu site em `xxx.sympalbuilder.com` e utilizar
a área administrativa em `xxx.sympalbuilder.com/backend.php`.

>**NOTE**
>O nome *`Sympal`* foi pego emprestado de Jonathan Wage e seu
>[Sympal](http://www.sympalphp.org/), um framework de gerenciamento de conteúdo (CMF)
>construído com o symfony.

Este projeto possui dois requisitos básicos:

  * Os usuários devem ser capazes de criar páginas e especificar o título, conteúdo,
    e URL para essas páginas.

  * Toda a aplicação deve ser construída dentro de um projeto symfony que
    gerencia a frontend e backend de todos os sites de clientes, determinando
    o cliente e carregando os dados corretos de acordo com o subdomínio.

>**NOTE**
>Para criar esta aplicação, o servidor terá de ser configurado para encaminhar todos os
>subdomínios `*.sympalbuilder.com` para o mesmo documento raíz - o diretório web
>do projeto symfony.

### O Esquema e os Dados

O banco de dados para o projeto consiste de objetos *`Client`* (Cliente) e *`Page`* (Página). Cada 
`Client` representa um subdomínio que é constituído por muitos objetos `Page`.

    [yml]
    # config/doctrine/schema.yml
    Client:
      columns:
        name: string(255)
        subdomain: string(50)
      indexes:
        subdomain_index:
          fields: [subdomain]
          type: unique

    Page:
      columns:
        title: string(255)
        slug: string(255)
        content: clob
        client_id: integer
      relations:
        Client:
          alias: Client
          foreignAlias: Pages
          onDelete: CASCADE
      índexes:
        slug_index:
          fields: [slug, client_id]
          type: unique

>**NOTE**
>Embora os índices de cada tabela não sejam necessários, eles são uma boa idéia
>pois a aplicação realizará consultas freqüentes com base nestas colunas.

Para trazer o projeto à vida, coloque os dados de teste a seguir no arquivo 
`data/fixtures/fixtures.yml`:

    [yml]
    # data/fixtures/fixtures.yml
    Client:
      client_pete:
        name: Pete's Pet Shop
        subdomain: pete
      client_pub:
        name: City Pub and Grill
        subdomain: citypub

    Page:
      page_pete_location_hours:
        title: Location and Hours | Pete's Pet Shop
        content: We're open Mon - Sat, 8 am - 7pm
        slug: location
        Client: client_pete
      page_pub_menu:
        title: City Pub And Grill | Menu
        content: Our menu consists of fish, Steak, salads, and more.
        slug: menu
        Client: client_pub

Os dados de teste introduzem inicialmente dois websites, cada um com uma página.
A URL completa de cada página é definida por ambas as colunas *`subdomain`* do
`Client` e a coluna `slug` do objeto `Page`.

    http://pete.sympalbuilder.com/location
    http://citypub.sympalbuilder.com/menu

### O Roteamento

Cada página de um website Sympal Builder corresponde diretamente a um objeto de modelo `Page`,
que define o título e o conteúdo de sua saída. Para fazer a ligação de cada URL
a um objeto `Page` específico, crie um objeto de rotas do tipo
`sfDoctrineRoute` que utiliza o campo `slug`. O código a seguir
irá procurar automaticamente um objeto `Page` no banco de dados com um campo `slug`
que corresponda à url:

    [yml]
    # apps/frontend/config/routing.yml
    page_show:
      url: /:slug
      class: sfDoctrineRoute
      options:
        model: Page
        type: object
      params:
        module: page
        action: show

A rota acima irá casar corretamente a página
`http://pete.sympalbuilder.com/location` com o objeto `Page` correto. Infelizmente, a rota acima casaria
*também* a URL `http://pete.sympalbuilder.com/menu`, significando que o
menu do restaurante será exibido no site Pete! Neste momento, a
rota desconhece a importância dos subdomínios de clientes.

Para trazer a aplicação à vida, a rota precisa ser mais esperta. Ela deve
casar o objeto `Page` correto baseando-se tanto no `slug` *e* no `client_id`,
que pode ser determinado ligando o host (por exemplo, `pete.sympalbuilder.com`)
com a coluna `subdomain` do modelo `Client`. Para conseguir isso, vamos
melhorar o framework de roteamento através da criação de uma classe de roteamento personalizada.

Antes, porém, precisamos de algumas informações sobre como funciona o sistema de roteamento.

Como o Sistema de Roteamento Funciona
----------------------------

Uma "rota", no symfony, é um objeto do tipo ~`sfRoute`~ que possui duas funções
importantes:

* Gerar uma URL: Por exemplo, se você passar ao método `page_show` um parâmetro
   `slug`, ele deve ser capaz de gerar uma URL real (ex. `/location`).

* "Casar" uma URL recebida: Dada a URL de uma requisição de entrada, cada rota
   deve ser capaz de determinar se a URL "corresponde" às exigências da
   rota.

As informações para rotas individuais são normalmente configuradas dentro de cada
diretório de configurações das aplicações localizado em `app/nomedoseuapp/config/routing.yml`.
Lembre-se de que cada rota é *"um objeto do tipo `sfRoute`"*. Então como é que estas
entradas simples em arquivos YAML se tornam objetos `sfRoute`?

### Manipulação da Configuração do Cache de Roteamento

Apesar do fato de a maioria das rotas serem definidas em um arquivo YAML, cada entrada neste
arquivo é transformada em um objeto real no momento da requisição através de um tipo especial
de classe chamada de manipulador de configuração de cache. O resultado final é um código PHP que representa
cada rota na aplicação. Embora as especificidades deste processo
estão fora do escopo deste capítulo, vamos dar uma olhada na versão final, compilada
da rota `page_show`. O arquivo compilado está localizado em
`cache/nomedoseuapp/nomeambiente/config/config_routing.yml.php` para o ambiente
e aplicação específicos. Abaixo está uma versão simplificada de como
a rota `page_show` parece:

    [php]
    new sfDoctrineRoute('/:slug', array (
      'module' => 'page',
      'action' => 'show',
    ), array (
      'slug' => '[^/\\.]+',
    ), array (
      'model' => 'Page',
      'type' => 'object',
    ));

>**TIP**
>O nome da classe de cada rota é definido pela chave `class` dentro do arquivo `routing.yml`.
> Se nenhuma chave `class` for especificada, a rota utilizará uma classe do tipo
>`sfRoute`. outra classe de roteamento comum é a `sfRequestRoute` que permite que
>o desenvolvedor crie rotas RESTful. Uma lista completa de classes de roteamento e suas
>opções está disponível em
>[The symfony Reference Book](http://www.symfony-project.org/reference/1_3/en/10-Routing)

### Casando uma Requisição de Entrada com uma Rota Específica

Uma das principais tarefas do framework de roteamento é casar cada URL de entrada
com o objeto de roteamento correto. A classe ~`sfPatternRouting`~ representa o
núcleo do motor de roteamento e é encarregada desta exata tarefa. Apesar de sua importância,
um desenvolvedor raramente irá interagir com a `sfPatternRouting`.

Para casar com a rota correta, `sfPatternRouting` itera sobre cada `sfRoute` e
e "pede" à rota se ela coincide com a URL de entrada. Internamente, isso significa
que a `sfPatternRouting` invoca o método ~`sfRoute::matchesUrl()`~ para cada
objeto de roteamento. Este método simplesmente retorna `falso` se a rota não coincide com a
URL de entrada.

No entanto, se a rota *corresponder* à URL de entrada, `sfRoute::matchesUrl()`
faz mais do que simplesmente retornar `verdadeiro`. Em vez disso, a rota retorna uma matriz
de parâmetros que são agrupados no objeto da requisição. Por exemplo, a URL
`http://pete.sympalbuilder.com/location` coincide com a rota `page_show`
cujo método `matchesUrl()` retornaria a seguinte matriz:

    [php]
    array('slug' => 'location')

Esta informação é então incorporado ao objeto da requisição, e é por isso que é
possível acessar as variáveis de roteamento (por exemplo `slug`), a partir do arquivo de actions e
outros lugares.

    [php]
    $this->slug = $request->getParameter('slug');

Como você deve ter percebido, sobrescrever o método `sfRoute::matchesUrl()` é
uma ótima maneira de estender e personalizar uma rota para fazer praticamente tudo.

Criando uma Classe Personalizada de Roteamento
-----------------------------

A fim de estender a rota `page_show` para casar de acordo com o subdomínio dos
objetos `Client`, vamos criar uma nova classe personalizada de roteamento. Crie um arquivo
chamado `acClientObjectRoute.class.php` e coloque-o no diretório `lib/routing` do
projeto (você precisará criar este diretório):

    [php]
    // lib/routing/acClientObjectRoute.class.php
    class acClientObjectRoute extends sfDoctrineRoute
    {
      public function matchesUrl($url, $context = array())
      {
        if (false === $parameters = parent::matchesUrl($url, $context))
        {
          return false;
        }

        return $parameters;
      }
    }

O último passo é instruir a rota `page_show` para utilizar esta classe de
roteamento. Em `routing.yml`, atualize a chave `class` para a rota:

    [yml]
    # apps/fo/config/routing.yml
    page_show:
      url: /:slug
      class: acClientObjectRout
      options:
        model: Page
        type: object
      params:
        module: page
        action: show

Até agora, `acClientObjectRoute` não adiciona qualquer funcionalidade, mas todas as peças
estão presentes. O método `matchesUrl()` possui duas funções específicas.

### Adicionando Lógica à Rota Personalizada

Para dar à rota personalizada a funcionalidade necessária, substitua o conteúdo do
arquivo `acClientObjectRoute.class.php` com o conteúdo a seguir.

    [php]
    class acClientObjectRoute extends sfDoctrineRoute
    {
      protected $baseHost = '.sympalbuilder.com';

      public function matchesUrl($url, $context = array())
      {
        if (false === $parameters = parent::matchesUrl($url, $context))
        {
          return false;
        }

        // returna falso se o baseHost não é encontrado
        if (strpos($context['host'], $this->baseHost) === false)
        {
          return false;
        }

        $subdomain = str_replace($this->baseHost, '', $context['host']);

        $client = Doctrine_Core::getTable('Client')
          ->findOneBySubdomain($subdomain)
        ;

        if (!$client)
        {
          return false;
        }

        return array_merge(array('client_id' => $client->id), $parameters);
      }
    }

A chamada inicial para `parent::matchesUrl()` é importante pois ela é executada
através do processo de roteamento normal. Neste exemplo, uma vez que a URL `/location` case
a rota `page_show`, `parent::matchesUrl()` retornaria uma matriz contendo
o parâmetro `slug` correspondente.

    [php]
    array('slug' => 'location')

Em outras palavras, todo o trabalho duro de "casamento" de rotas é feito para nós, o que permite
que o restante do método se concentre em realizar o casamento baseado no subdomínio de um objeto `Client`.

    [php]
    public function matchesUrl($url, $context = array())
    {
      // ...

      $subdomain = str_replace($this->baseHost, '', $context['host']);

      $client = Doctrine_Core::getTable('Client')
        ->findOneBySubdomain($subdomain)
      ;

      if (!$client)
      {
        return false;
      }

      return array_merge(array('client_id' => $client->id), $parameters);
    }

Realizando uma simples substituição de string, podemos isolar a parte do subdomínio
do host e, em seguida, consultar o banco de dados para ver se algum dos objetos `Client`
possui esse subdomínio. Se nenhum objeto `Client` corresponder ao subdomínio, então
retornamos falso, indicando que a requisição de entrada não coincide com a rota.
No entanto, se há um objeto `Client` com o subdomínio atual, nós agrupamos
um parâmetro extra, `client_id` no array retornado.

>**TIP**
>O array `$context` passado à `matchesUrl()` é preenchido com diversas
>informações úteis sobre a requisição atual, incluindo o `host`, um
>booleano `is_secure`, `request_uri`, o `método` HTTP e muito mais.

Mas, o que a rota personalizada realmente conseguiu? A classe `acClientObjectRoute`
agora faz o seguinte:

* A `$url` de entrada só casa se o `host` contém um subdomínio
   pertencente a um dos objetos `Client`.

* Se a rota casar com um parâmetro `client_id` adicional para o objeto `Client`
   correspondente, ele será retornado e por fim adicionado aos parâmetros da requisição.

### Aproveitando-se do Roteamento Personalizado

Agora que o parâmetro `client_id` correto está sendo retornado por `acClientObjectRoute`,
temos acesso a ele através do objeto da requisição. por exemplo, a ação `page/show`
poderia utilizar o `client_id` para encontrar objeto `Page` correto:

    [php]
    public function executeShow(sfWebRequest $request)
    {
      $this->page = Doctrine_Core::getTable('Page')->findOneBySlugAndClientId(
        $request->getParameter('slug'),
        $request->getParameter('client_id')
      );

      $this->forward404Unless($this->page);
    }

>**NOTE**
>O método `findOneBySlugAndClientId()` é um tipo de
>[Localizador mágico](http://www.doctrine-project.org/upgrade/1_2#Expanded%20Magic%20Finders%20to%20Multiple%20Fields)
>novo no Doctrine 1.2 que realiza consultas por objetos utilizando-se de múltiplos campos.

Tão agradável como seja, o framework de roteamento permite uma solução ainda mais elegante.
Primeiro, adicione o seguinte método à classe `acClientObjectRoute`:

    [php]
    protected function getRealVariables()
    {
      return array_merge(array('client_id'), parent::getRealVariables());
    }

Com esta parte final, a ação pode confiar totalmente na rota para retornar
o objeto `Page` correto. A ação `page/show` pode ser reduzida a uma única
linha.

    [php]
    public function executeShow(sfWebRequest $request)
    {
      $this->page = $this->getRoute()->getObject();
    }

Sem qualquer trabalho adicional, o código acima irá consultar por um objeto `Page`
baseando-se nas colunas `slug` *e* `client_id`. Além disso, como todos os
objetos de rota, a ação irá automaticamente redirecionar à uma página 404 caso nenhum
objeto correspondente seja encontrado.

Mas como isso funciona? Objetos de rota, como `sfDoctrineRoute`, que a
classe `acClientObjectRoute` estende, busca automaticamente pelo objeto
relacionado com base nas variáveis na chave `url` da rota. Por exemplo, a
rota `page_show`, que contém a variável `:slug` em sua `url`, busca
pelo objeto `Page` através da coluna `slug`.

Nesta aplicação, no entanto, a rota `page_show` deve também buscar por objetos
`Page` com base na coluna `client_id`. Para fazer isso, nós sobreescrevemos o
~`sfObjectRoute::getRealVariables()`~, que é chamado internamente, para determinar
que colunas usar para a busca do objeto. Ao adicionar o campo `client_id`
a este array, `acClientObjectRoute` irá consultar com base em ambas as colunas
`slug` e `client_id`.

>**NOTE**
>Rotas de objetos ignoram automaticamente quaisquer variáveis que não correspondam
>à uma coluna real. Por exemplo, se a chave URL contiver uma variável `:page`,
>mas não existir uma coluna `page` na tabela relevante, a variável será ignorada.

Neste ponto, a classe de roteamento personalizada realiza o necessário com
pouquíssimo esforço. Nas próximas seções, iremos reutilizar a nova rota para criar
área administrativa especifica a cada cliente.

### Gerando a Rota Correta

Um pequeno problema persiste em como a rota é gerada. Imagine a criação
de um link para uma página com o seguinte código:

    [php]
    <?php echo link_to('Locations', 'page_show', $page) ?>

-

    Generated url: /location?client_id = 1

Como você pode ver, `client_id` foi automaticamente adicionada à url. Isto
ocorre porque a rota tenta usar todas suas variáveis disponíveis para gerar
a url. Devido ao fato da rota ter conhecimento dos parâmetros `slug` e
`client_id`, ela utiliza ambos ao gerar a rota.

Para corrigir isto, adicione o seguinte método à classe `acClientObjectRoute`:

    [php]
    protected function doConvertObjectToArray($object)
    {
      $parameters = parent::doConvertObjectToArray($object);

      unset($parameters['client_id']);

      return $parameters;
    }

Quando um objeto rota é gerado, ele tenta recuperar todas informações
necessárias chamando `doConvertObjectToArray()`. Por padrão, `client_id`
é retornado na array `$parâmetros`. Anulando sua atribuição, no entanto, evitamos
que ele seja incluído na URL gerada. Lembre-se de que temos esse luxo
pois a informação sobre `Client` é armazenada no próprio subdomínio.

>**TIP**
>Você pode sobrescrever o processo `doConvertObjectToArray()` completamente e tratá-lo
>você mesmo, adicionando um método `toParams()` à classe de modelo. Este método
>deve retornar um array de parâmetros que você quer que seja utilizado durante a
>geração da rota.

Coleções (*Collections*) de Rota
-----------------

Para terminar a aplicação Sympal Builder, precisamos criar uma área administrativa
onde cada `Client` possa administrar suas `Pages`. Para fazer isso, precisaremos
de um conjunto de ações que nos permita listar, criar, atualizar e excluir objetos `Page`.
Como estes tipos de módulos são bastante comuns, symfony pode gerar o módulo
automaticamente. Executar a seguinte tarefa na linha de comando para gerar
o módulo `pageAdmin` dentro da aplicação chamada `backend`:

    $ php symfony doctrine:generate-module backend pageAdmin Page --with-doctrine-route --with-show

A tarefa acima gera um módulo com um arquivo de ações e templates relacionados
capaz de fazer todas as modificações necessárias para qualquer objeto `Page`.
Muitas personalizações podem ser feitas a este CRUD gerado, mas isso
está fora do escopo deste capítulo.

Enquanto a tarefa acima prepara o módulo para nós, precisamos ainda criar uma
rota para cada ação. Ao passar a opção `--with-doctrine-route` para a
tarefa, cada ação foi gerada para trabalhar com um objeto de rota. Isso diminui
a quantidade de código em cada ação. Por exemplo, a ação `edit` contém
uma simples linha:

    [php]
    public function executeEdit(sfWebRequest $request)
    {
      $this->form = new PageForm($this->getRoute()->getObject());
    }

No total, precisamos de rotas para as ações `index`, `new`, `create`, `edit`,
`update` e `delete`. Normalmente, a criação destas rotas de uma maneira
[RESTful](http://en.wikipedia.org/wiki/Representational_State_Transfer)
exigiria uma configuraçào significativa em `routing.yml`.

    [yml]
    pageAdmin:
      url: /pages
      class: sfDoctrineRoute
      options: { model: Page, type: list }
      params: { module: page, action: index }
      requirements:
        sf_method: [get]
    pageAdmin_new:
      url: /pages/new
      class: sfDoctrineRoute
      options: { model: Page, type: object }
      params: { module: page, action: new }
      requirements:
        sf_method: [get]
    pageAdmin_create:
      url: /pages
      class: sfDoctrineRoute
      options: { model: Page, type: object }
      params: { module: page, action: create }
      requirements:
        sf_method: [post]
    pageAdmin_edit:
      url: /pages/:id/edit
      class: sfDoctrineRoute
      options: { model: Page, type: object }
      params: { module: page, action: edit }
      requirements:
        sf_method: [get]
    pageAdmin_update:
      url: /pages/:id
      class: sfDoctrineRoute
      options: { model: Page, type: object }
      params: { module: page, action: update }
      requirements:
        sf_method: [put]
    pageAdmin_delete:
      url: /pages/:id
      class: sfDoctrineRoute
      options: { model: Page, type: object }
      params: { module: page, action: delete }
      requirements:
        sf_method: [delete]
    pageAdmin_show:
      url: /pages/:id
      class: sfDoctrineRoute
      options: { model: Page, type: object }
      params: { module: page, action: show }
      requirements:
        sf_method: [get]

Para visualizar essas rotas, use a tarefa `app:routes`, que exibe um resumo
de cada rota para uma aplicação específica:

    $ php symfony app:routes backend

    >> app Current routes for application "backend"
    Name Method Pattern
    pageAdmin GET /pages
    pageAdmin_new GET /pages/new
    pageAdmin_create POST /pages
    pageAdmin_edit GET /pages/:id/edit
    pageAdmin_update PUT /pages/:id
    pageAdmin_delete DELETE /pages/:id
    pageAdmin_show GET /pages/:id

### Substituindo as Rotas com uma Coleção de Rota

Felizmente, o symfony fornece uma maneira muito mais fácil para especificar todas as rotas
que pertencem a um CRUD tradicional. Substitua todo o conteúdo em `routing.yml` com uma simples rota.

    [yml]
    pageAdmin:
      class: sfDoctrineRouteCollection
      options:
        model: Page
        prefix_path: /pages
        module: pageAdmin

Mais uma vez, execute a tarefa `app:routes` para visualizar todas as rotas.
Como você vai ver, todos as sete rotas anteriores continuam existindo.

    $ php symfony app:routes backend

    >> app Current routes for application "backend"
    Name Method Pattern
    pageAdmin GET /pages.:sf_format
    pageAdmin_new GET /pages/new.:sf_format
    pageAdmin_create POST /pages.:sf_format
    pageAdmin_edit GET /pages/:id/edit.:sf_format
    pageAdmin_update PUT /pages/:id.:sf_format
    pageAdmin_delete DELETE /pages/:id.:sf_format
    pageAdmin_show GET /pages/:id.:sf_format

Coleções de Rotas são um tipo especial de objeto de rota que representa internamente
mais de uma rota. A rota ~`sfDoctrineRouteCollection`~, por exemplo
gera automaticamente as sete rotas mais comuns necessárias para um CRUD. Debaixo
dos panos, `sfDoctrineRouteCollection` não está fazendo nada mais do que criar
as mesmas sete rotas previamente especificadas em `routing.yml`. Coleções de rota
existem basicamente como um atalho para a criação de um grupo comum de rotas.

Criando uma Coleção de Rota Personalizada
----------------------------------

Neste ponto, cada `Client`, será capaz de modificar seus objetos `Page` dentro de
um CRUD funcional através da URL `/pages`. Infelizmente, cada `Client` pode
atualmente ver e modificar *todos* objetos `Page` - tanto aqueles que pertencem e
não pertencentes ao `Client`. Por exemplo:
`http://pete.sympalbuilder.com/backend.php/pages` irá renderizar uma lisa de *ambas*
as páginas nas fixtures - a página `location` do Pet Shop do Pete e a página
`menu` do *City Pub*.

Para corrigir isso, vamos reutilizar `acClientObjectRoute` que foi criada para a
*frontend*. A classe `sfDoctrineRouteCollection` gera um grupo de objetos `sfDoctrineRoute`. Nesta aplicação, precisaremos gerar um grupo de objetos `acClientObjectRoute`
em seu lugar.

Para conseguir isso, vamos precisar usar uma coleção de classes de roteamento personalizadas. Crie
um novo arquivo chamado `acClientObjectRouteCollection.class.php` e coloque-o no
diretório `lib/routing`. Seu conteúdo é extremamente simples:

    [php]
    // lib/routing/acClientObjectRouteCollection.class.php
    class acClientObjectRouteCollection extends sfObjectRouteCollection
    {
      protected
        $routeClass = 'acClientObjectRoute';
    }

A propriedade `$routeClass` define a classe que será utilizado para a criação
de cada rota subjacente. Agora que cada rota subjacente é uma rota `acClientObjectRoute`,
o trabalho está na verdade finalizado. Por exemplo:
`http://pete.sympalbuilder.com/backend.php/pages` agora irá listar apenas *uma*
página: a página `location` do Pet Shop do Pete. Graças à classe de roteamento
personalizada, a ação index retornam apenas objetos `Page` que tenham relação com o `Client`
correto, baseado no subdomínio da requisição. Com apenas algumas linhas de
código, criamos um módulo de backend inteiro que pode tranquilamento ser utilizado por
vários clientes.

### A Peça que Faltava: Criando Novas Páginas

Atualmente, uma caixa de seleção de `Client` é exibida na backend ao criar ou editar
objetos `Page`. Em vez de permitir que os usuários escolham `Client` (o que seria
um risco de segurança), vamos defini-lo automaticamente baseando-se no subdomínio atual da
requisição.

Primeiro, atualize o objeto `PageForm` em `lib/form/PageForm.class.php`.

    [php]
    public function configure()
    {
      $this->useFields(array(
        'title',
        'content',
      ));
    }

A caixa de seleção está agora faltando nos formulários de `Page`, como necessário. No entanto, quando
novos objetos `Page` são criados, `client_id` nunca é definido. Para resolver isso, defina
manualmente o `Client` relacionado em ambas as ações `new` e `create`.

    [php]
    public function executeNew(sfWebRequest $request)
    {
      $page = new Page();
      $page->Client = $this->getRoute()->getClient();
      $this->form = new PageForm($page);
    }

Aqui é introduzido um novo método, `getClient()`, que não existe atualmente na
classe `acClientObjectRoute`. Vamos adicioná-lo à classe fazendo algumas
simples modificações:

    [php]
    // lib/routing/acClientObjectRoute.class.php
    class acClientObjectRoute extends sfDoctrineRoute
    {
      // ...

      protected $client = null;

      public function matchesUrl($url, $context = array())
      {
        // ...

        $this->client = $client;

        return array_merge(array('client_id' => $client->id), $parameters);
      }

      public function getClient()
      {
        return $this->client;
      }
    }

Ao adicionar a propriedade `$client` e defini-la no método `matchesUrl()`,
podemos facilmente fazer o objeto `Client` disponível através da rota. A coluna
`client_id` dos novos objetos `Page` será automatica e corretamente definida
baseando-se no subdomínio do host atual.

Personalizando um Objeto de Coleção de Roteamento
--------------------------------------

Ao utilizar o framework de roteamento, resolvemos agora facilmente os problemas que surgiram
ao criar a aplicação Sympal Builder. Conforme a aplicação cresce, o desenvolvedor
será capaz de reutilizar as rotas personalizadas para os outros módulos na área de back-end
(por exemplo, para que cada `Client` possa gerenciar suas galerias de foto).

Outro motivo comum para criar uma coleção de roteamento personalizada é para acrescentar rotas,
de uso comum adicionaiss. Por exemplo, imagine que um projeto utilize muitos modelos, cada um
com uma coluna `is_active`. Na área administrativa, é preciso haver uma maneira fácil
para alternar o valor de `is_active` para qualquer objeto em particular. Primeiramente, modifique
`acClientObjectRouteCollection` e instrua-o à adicionar uma nova rota para a coleção:

    [php]
    // lib/routing/acClientObjectRouteCollection.class.php
    protected function generateRoutes()
    {
      parent::generateRoutes();

      if (isset($this->options['with_is_active']) && $this->options['with_is_active'])
      {
        $routeName = $this->options['name'].'_toggleActive';

        $this->routes[$routeName] = $this->getRouteForToggleActive();
      }
    }

O método ~`sfObjectRouteCollection::generateRoutes()`~ é chamado quando o
objeto de coleção é instanciado e é responsável pela criação de todas as
rotas necessárias e de adicioná-los à propriedade da classe `$routes`. Neste
caso, passamos a criação efetiva da rota para um novo método protegido
chamado `getRouteForToggleActive()`:

    [php]
    protected function getRouteForToggleActive()
    {
      $url = sprintf(
        '%s/:%s/toggleActive.:sf_format',
        $this->options['prefix_path'],
        $this->options['column']
      );

      $params = array(
        'module' => $this->options['module'],
        'action' => 'toggleActive',
        'sf_format' => 'html'
      );

      $requirements = array('sf_method' => 'put');

      $options = array(
        'model' => $this->options['model'],
        'type' => 'object',
        'method' => $this->options['model_methods']['object']
      );

      return new $this->routeClass(
        $url,
        $params,
        $requirements,
        $options
      );
    }

O único passo restante é configurar a coleção de roteamento em `routing.yml`.
Observe que `generateRoutes()` procura por uma opção chamada `with_is_active`
antes de adicionar a nova rota. Adicionar esta lógica nos dá mais controle em caso
de querermos utilizar `acClientObjectRouteCollection` em algum lugar depois que não
precise da rota `toggleActive`:

    [yml]
    # apps/frontend/config/routing.yml
    pageAdmin:
      class: acClientObjectRouteCollection
      options:
        model: Page
        prefix_path: /pages
        module: pageAdmin
        with_is_active: true

Verifique a tarefa `app:routes` e assegure-se de que a nova rota `toggleActive`
está presente. A única parte restante é criar a ação que irá fazer
o trabalho de verdade. Devido ao fato de que que você pode querer usar esta coleção de rota e
ação correspondente em vários módulos, crie um novo
arquivo `backendActions.class.php` dentro do diretório
`apps/backend/lib/action` (você precisará criar esse diretório):

    [php]
    # apps/backend/lib/action/backendActions.class.php
    class backendActions extends sfActions
    {
      public function executeToggleActive(sfWebRequest $request)
      {
        $obj = $this->getRoute()->getObject();

        $obj->is_active = !$obj->is_active;

        $obj->save();

        $this->redirect($this->getModuleName().'/index');
      }
    }

Finalmente, altere a classe base da classe `pageAdminActions` para estender essa
nova classe `backendActions`.

    [php]
    class pageAdminActions extends backendActions
    {
      // ...
    }

O que nós acabamos de fazer? Ao adicionar uma rota para a coleção de rotas e
uma ação associada a um arquivo de ações base, qualquer novo módulo pode automaticamente
utilizar esta funcionalidade simplesmente usando o `acClientObjectRouteCollection` e
estendendo a classe `backendActions`. Desta forma, a funcionalidade comum pode
ser facilmente compartilhados entre muitos módulos.

Opções sobre uma Coleção de Rotas
-----------------------------

O objeto de coleções de rota contém uma série de opções que lhe permitam ser altamente
personalizado. Em muitos casos, um desenvolvedor pode usar essas opções para configurar
a coleção sem precisar criar uma nova classe de coleção de rota personalizada.
Uma lista detalhada das opções da coleção de rotas está disponível através de
[The symfony Reference Book](http://www.symfony-project.org/reference/1_3/en/10-Routing chapter_10_sfobjectroutecollection #).

### Rotas de Ação (*Action Routes*)

Cada objeto de coleção de rota aceita três opções diferentes que determinam
as rotas exatas geradas na coleção. Sem entrar em grandes detalhes,
a seguinte coleção geraria todas as sete rotas padrão juntamente
com uma coleção de rotas e objeto de rota adicionais:

    [yml]
    pageAdmin:
      class: acClientObjectRouteCollection
      options:
        # ...
        actions: [list, new, create, edit, update, delete, show]
        collection_actions:
          indexAlt: [get]
        object_actions:
          toggle: [put]

### Coluna

Por padrão, a chave primária do modelo é utilizada em todas as urls geradas
e é usado para consultar os objetos. Isto, naturalmente, pode ser facilmente alterado.
Por exemplo, o código a seguir usar a coluna `slug` em vez do
chave primária:

    [yml]
    pageAdmin:
      class: acClientObjectRouteCollection
      options:
        # ...
        column: slug

### Métodos de Modelo

Por padrão, a rota recupera todos os objetos relacionados para uma coleção de rotas
e faz uma consulta sobre a 'coluna' determinada para as rotas de objeto. Se você
precisar sobrescrever isso, adicione a opção `model_methods` para a rota. Neste
exemplo, os métodos `fetchAll()` e `findForRoute()` que devem ser adicionados
a classe `PageTable`. Ambos os métodos receberão uma matriz de
parâmetros de pedido (*request*) como um argumento:

    [yml]
    pageAdmin:
      class: acClientObjectRouteCollection
      options:
        # ...
        model_methods:
          list: fetchAll
          object: findForRoute

### Parâmetros Padrões

Finalmente, suponha que você precisa tornar um parâmetro de solicitação específico disponível
na requisição (*request*) de cada rota na coleção. Isso é facilmente feito com
a opção `default_params`:

    [yml]
    pageAdmin:
      class: acClientObjectRouteCollection
      options:
        # ...
        default_params:
          foo: bar

Reflexões finais
--------------

O emprego tradicional do framework de roteamento - para combinar e gerar URLs -
evoluiu para um sistema totalmente personalizável capaz de atender a maioria das
complexas URL exigidas em um projeto. Ao assumir o controle dos objetos de rota,
a estrutura de URL especial pode ser captada longe da lógica de negócios e
mantido inteiramente dentro da rota em que ele pertence. O resultado final é mais controle,
mais flexibilidade e mais código gerenciável.