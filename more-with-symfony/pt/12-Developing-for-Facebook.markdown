Desenvolvendo Aplicações Facebook
=================================

*por Fabrice Bernhard*

O Facebook, com seus quase 300 milhões de membros, tornou-se o padrão em 
sites de redes sociais na Internet. Uma de suas características mais interessantes é a
"A plataforma Facebook", uma API que permite aos desenvolvedores criar aplicações
dentro do site Facebook, bem como conectar outros sites com o
o sistema de autenticação do Facebook e o *social graph*.

Já que o frontend do Facebook é em PHP, não é de admirar que a biblioteca cliente oficial
desta API é uma biblioteca PHP. Isto, de fato, faz do symfony uma solução lógica
para o desenvolvimento rápido e limpo de aplicações para o Facebook ou
sites *Facebook Connect*. Mas, mais do que isso, desenvolver para o Facebook mostra realmente como
você pode aproveitar as funcionalidades do symfony para ganhar tempo precioso, enquanto mantêm 
elevados padrões de qualidade. Isto será abordado aqui em profundidade: após um 
breve resumo do que é a API do Facebook e como ela pode ser utilizada, vamos 
ver como utilizar o melhor do symfony no desenvolvimento de aplicações para o Facebook,
como se beneficiar dos esforços da comunidade e do plugin `sfFacebookConnectPlugin`,
demonstrando através de uma aplicação simples "Olá você!" e, finalmente, dar dicas
e truques para resolver os problemas mais comuns.

Desenvolvendo para o Facebook
-----------------------------

Embora a API seja basicamente a mesma em ambas, existem duas formas muito
diferentes de desenvolver as aplicações: criar uma aplicação dentro do Facebook e implementar o 
*Facebook Connect* em um site externo.

### Aplicações Facebook

As aplicações Facebook são aplicações web dentro do Facebook. Sua principal qualidade
é a de ser incorporada diretamente em uma rede social forte com 300 milhões de usuários,
portanto, permitindo que qualquer aplicação viral cresça a uma velocidade incrível. Farmville é
o maior e mais recente exemplo, com mais de 60 milhões de usuários ativos mensais 
e 2 milhões de fãs em poucos meses! Isso é o equivalente a população da França 
voltar a cada mês para trabalhar em sua fazenda virtual! As aplicações
Facebook interagem com o site Facebook e seu *social graph* de 
formas diferentes. Aqui está uma visão geral dos diferentes lugares onde uma aplicação do Facebook
poderia aparecer:

#### O Canvas

O canvas será geralmente a parte principal da aplicação. É 
basicamente um pequeno site incorporado dentro do frame Facebook.

#### A aba do Perfil

A aplicação também pode estar dentro de uma aba do perfil de um usuário ou uma página de fãs.
As principais limitações são:

* apenas uma página. Não é possível definir links para sub-páginas na aba.

* sem Flash dinâmico ou JavaScript durante o carregamento. Para fornecer funcionalidades
   dinâmicas, a aplicação tem que esperar o usuário interagir
   com a página clicando em um link ou botão.

#### As Caixas do Perfil

Este é mais um recurso remanescente do Facebook antigo, que realmente, ninguém mais 
utiliza. É usado para exibir algumas informações em uma caixa que pode ser encontrada na aba 
"Caixas" do perfil.

#### Adendo na Aba de Informação

Algumas informações estáticas ligadas a um usuário específico e a aplicação podem ser
exibidas na aba Informações no perfil do usuário. Ela aparece logo
abaixo da idade do usuário, endereço e currículo.

#### Publicando avisos e informações no Stream de Notícias

A aplicação pode publicar notícias, links, fotos, vídeos no stream de notícias,
no mural de um amigo, ou diretamente modificar o status do usuário.

#### A página de Informação

Esta é a "página de perfil" da aplicação, criada automaticamente pelo
Facebook. É onde o criador da aplicação poderá
interagir com seus usuários na forma usual do Facebook. Geralmente está mais relacionado 
com a equipe de marketing do que com a equipe de desenvolvimento.

### Facebook Connect

O *Facebook Connect* permite a qualquer website disponibilizar algumas das grandes 
funcionalidades do Facebook para os seus próprios usuários. Sites já "conectados" podem
ser reconhecidos pela presença de um grande botão azul "Conectar com o Facebook" (*Connect with Facebook*).
Os mais famosos incluem: digg.com, cnet.com, netvibes.com, yelp.com, etc. Logo a seguir 
está a lista das quatro razões principais para fazer o Facebook se conectar a um site 
existente.

#### Sistema de Autenticação *One-click*

Assim como o OpenID, o *Facebook Connect* concede aos sites a oportunidade de fornecer
login automático usando sua sessão do Facebook. Assim que a "conexão" entre
o site e o Facebook foi aprovada pelo usuário, a sessão do Facebook
é fornecida automaticamente para o site, evitando com que o usuário tenha que fazer mais uma
inscrição e a criação de uma nova senha para lembrar.

#### Obtenha mais Informações sobre o Usuário

Uma outra característica fundamental do *Facebook Connect* é a quantidade de informações
fornecidas. Normalmente os usuários fornecem poucas informações pessoais ao se inscreverem em um
novo site, o *Facebook Connect* oferece a oportunidade de obter rapidamente informações adicionais 
interessantes como nome, idade, sexo, localização, sua imagem de perfil, etc, 
enriquecendo o site. Os termos de uso do *Facebook Connect* lembram claramente 
que não se deve armazenar qualquer informação pessoal sobre o usuário, sem a
autorização explícita do mesmo, mas, as informações podem ser usadas para
preencher formulários e pedir a confirmação através de um clique. Além disso, o site pode utilizar 
informação pública, como nome e foto do perfil sem a necessidade de armazená-las
.

#### Comunicação Viral usando o Feed de Notícias

A habilidade de interagir com o feed de notícias do usuário, convidar amigos ou publicar no 
mural do amigo, permite ao site usar o potencial viral completo do Facebook para
comunicação. Qualquer site com algum componente social pode realmente se beneficiar
deste recurso, contanto que as informações publicadas no feed do Facebook tenham
algum valor social que possa interessar aos amigos e aos amigos dos amigos.

#### Obtenha Benefício do *Social Graph* existente

Para um site onde o serviço se baseia em um *social graph* (como uma rede de
amigos ou conhecidos), o custo para construir uma primeira comunidade, com bastante
ligações entre os usuários para interagir e se beneficiar do serviço, é realmente elevado.
Ao fornecer fácil acesso à lista de amigos de um usuário, o *Facebook Connect*
reduz drasticamente esse custo, eliminando a necessidade de procurar "amigos já
registrados".

Criando o primeiro projeto usando o `sfFacebookConnectPlugin`
----------------------------------------------------------

### Criando uma aplicação Facebook 

Para começar, é necessário uma conta no Facebook com a 
aplicação ["Developer"](http://www.facebook.com/developers) instalada. Para 
criar a aplicação, a única informação necessária é um nome. Uma vez que isto é 
feito, nenhuma configuração adicional é necessária.

### Instalar e configurar o `sfFacebookConnectPlugin`

O próximo passo é relacionar os usuários do Facebook com os usuários do `sfGuard`. Esta é a
principal característica do `sfFacebookConnectPlugin`, que eu iniciei e que
outros desenvolvedores symfony têm contribuído rapidamente. Com o plugin
instalado, existe uma etapa de configuração fácil, mas necessária. A chave da API,
o segredo da aplicação e o ID da aplicação precisam ser configurados no arquivo `app.yml`:

    [yml]
    # Valores padrão
    all:
      facebook:
        api_key: xxx
        api_secret: xxx
        api_id: xxx
        redirect_after_connect: false
        redirect_after_connect_url:''
        connect_signin_url: 'sfFacebookConnectAuth/signin'
        app_url: '/my-app'
        guard_adapter: ~
        js_framework: none # none, jQuery or prototype.

      sf_guard_plugin:
        profile_class: sfGuardUserProfile
        profile_field_name: user_id
        profile_facebook_uid_name: facebook_uid # ATENÇÃO esta coluna deve ser do tipo varchar! 100000398093902 é um uid válido, por exemplo!
        profile_email_name: email
        profile_email_hash_name: email_hash

      facebook_connect:
        load_routing: true
        user_permissions: []

>**TIP**
>Nas versões mais antigas do symfony, lembre-se de definir a opção "load_routing"
>como false, uma vez que ele utiliza o sistema de roteamento novo.

### Configurar uma aplicação Facebook

Se o projeto é uma aplicação Facebook, o único parâmetro importante 
será o `app_url` que aponta para o caminho relativo da aplicação no 
Facebook. Por exemplo, para a aplicação `http://apps.facebook.com/my-app`
o valor do parâmetro `app_url` será `/my-app`.

### Configurar um site *Facebook Connect*

Se o projeto é um site *Facebook Connect*, na maioria das vezes, podem ser mantidos os valores padrão 
dos parâmetros de configuração:

* `redirect_after_connect` permite modificar o comportamento após clicar no 
   botão "Conectar com o Facebook". Por padrão, o plugin reproduz o
   comportamento do `sfGuardPlugin` após o registo.

* `js_framework` pode ser utilizado para especificar um framework JS. É 
   altamente recomendável usar um framework JS como o jQuery nos sites *Facebook Connect*
   já que o JavaScript do *Facebook Connect* é bastante pesado e pode causar 
   erros fatais (!) no IE6 se não carregado no momento certo.

* `user_permissions` é o array de permissões que serão concedidas aos novos
   usuários do *Facebook Connect*.

### Conectando o sfGuard com o Facebook

A ligação entre um usuário do Facebook e o sistema do `sfGuardPlugin` é feita 
usando a coluna `facebook_uid` na tabela `Profile`. O plugin
assume que a ligação entre o `sfGuardUser` e seu perfil é feita usando
o método `getProfile()`. Este é o comportamento padrão com
o `sfPropelGuardPlugin` mas precisa ser configurado manualmente se for utilizado o 
`sfDoctrineGuardPlugin`. Aqui está um possível arquivo `schema.yml`:

Para o Propel:

    [yml]
    sf_guard_user_profile:
      _attributes: { phpName: UserProfile }
      id:
      user_id: { type: integer, foreignTable: sf_guard_user, foreignReference: id, onDelete: cascade }
      first_name: { type: varchar, size: 30 }
      last_name: { type: varchar, size: 30 }
      facebook_uid: { type: varchar, size: 20 }
      email: { type: varchar, size: 255 }
      email_hash: { type: varchar, size: 255 }
      _uniques:
        facebook_uid_index: [facebook_uid]
        email_index: [email]
        email_hash_index: [email_hash]

Para o Doctrine:

    [yml]
    sfGuardUserProfile:
      tableName: sf_guard_user_profile
      columns:
        user_id: { type: integer(4), notnull: true }
        first_name: { type: string(30) }
        last_name: { type: string(30) }
        facebook_uid: { type: string(20) }
        email: { type: string(255) }
        email_hash: { type: string(255) }
      indexes:
        facebook_uid_index:
          fields: [facebook_uid]
          unique: true
        email_index:
          fields: [email]
          unique: true
        email_hash_index:
          fields: [email_hash]
          unique: true
      relations:
        sfGuardUser:
          type: one
          foreignType: one
          class: sfGuardUser
          local: user_id
          foreign: id
          onDelete: cascade
          foreignAlias: Profile


>**TIP**
>O que acontece se o projeto utiliza o Doctrine e a opção `foreignAlias` não é o `Profile`? Nesse 
>caso, o plugin não irá funcionar. Mas um simples método `getProfile()` no 
>`sfGuardUser.class.php` que aponta para a tabela `Profile` vai resolver o problema!

Por favor, note que a coluna `facebook_uid` deve ser do tipo `varchar`, porque os novos
perfis no Facebook tem `uids` acima de `10^15`. Melhor trabalhar com segurança, utilizando uma
coluna indexada `varchar` do que tentar fazer uma coluna `bigint` trabalhar com diferentes ORMs.

As outras duas colunas são menos importantes: `email` e `email_hash` são necessárias 
apenas no caso de um site *Facebook Connect* com usuários existentes. Neste 
caso, o Facebook oferece um processo complicado para tentar associar contas existentes
com as novas contas do *Facebook Connect* usando um hash de e-mail. Certamente 
que o processo tornou-se simples graças a uma tarefa fornecida pelo 
`sfFacebookConnectPlugin`, que é descrita na última parte deste 
capítulo.

### Escolher entre FBML e XFBML: Problema resolvido pelo symfony

Agora que tudo está configurado, podemos começar a codificar a aplicação.
O Facebook oferece muitas tags especiais que podem processar funcionalidades completas, como
um formulário para "convidar amigos" ou um sistema de comentário totalmente funcional. Essas tags são
chamadas FBML ou XFBML. As tags FBML e XFBML são muito semelhantes, mas a
escolha depende se a aplicação é exibida dentro do Facebook ou não.
Se o projeto é um site *Facebook Connect*, há apenas uma escolha: XFBML.
Se for uma aplicação Facebook, há duas opções:

* Incorporar a aplicação como um IFrame real dentro da página da aplicação Facebook
   e usar XFBML dentro deste IFrame;

* Deixar o Facebook incorporá-lo de forma transparente dentro da página, e usar FBML.

O Facebook incentiva os desenvolvedores à utilizar a sua "incorporação transparente" ou 
como é chamada: "aplicação FBML". Na verdade, ela tem algumas características interessantes:

* Não é utilizado Iframe, que é sempre complicado de gerenciar uma vez que você precisa preocupar-se 
   se os seus links apontam para o iframe ou a janela principal;

* Tags especiais chamadas tags FBML são interpretadas automaticamente pelo servidor do Facebook
   e permitem que você exiba informações particulares sobre o usuário
   sem ter de realizar uma comunicação prévia com o servidor Facebook;

* Não há necessidade de passar a sessão do Facebook manualmente de página em página.

Mas, o FBML possui alguns inconvenientes:

* Todo o JavaScript está inserido dentro de um sandbox, impossibilitando o uso
   de bibliotecas externas como o Google Maps, jQuery ou qualquer outro sistema de estatísticas que não seja 
   o Google Analytics, oficialmente adotado pelo Facebook;

* Afirma ser mais rápido uma vez que algumas chamadas de API podem ser substituídas por tags FBML.
   No entanto, se a aplicação é leve, hospedá-la em seu próprio servidor será
   muito mais rápido;

* É mais difícil para depurar, especialmente os erros 500, que são capturados pelo Facebook 
   e substituídos por um erro padrão.

Então, qual é a opção recomendada? A boa notícia é que, com o symfony e o
`sfFacebookConnectPlugin`, não há escolha a fazer! É possível escrever
aplicações agnósticas e alternar indiferentemente de um IFrame para uma aplicação incorporada
para um site *Facebook Connect* com o mesmo código. Isto é possível
porque, tecnicamente, a principal diferença é realmente no layout ... que é
muito fácil de mudar no symfony. Aqui estão os exemplos dos dois layouts 
diferentes:

O layout para uma aplicação FBML:

    [html]
    <?php sfConfig::set('sf_web_debug', false); ?>
    <fb:title><?php echo sfContext::getInstance()->getResponse()->getTitle() ?></fb:title>
    <?php echo $sf_content ?>

O layout para uma aplicação XFBML ou um site *Facebook Connect*:

    [html]
    <?php use_helper('sfFacebookConnect')?>
    <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
    <html xmlns="http://www.w3.org/1999/xhtml" xmlns:fb="http://www.facebook.com/2008/fbml">
      <head>
        <?php include_http_metas() ?>
        <?php include_metas() ?>
        <?php include_title() ?>
        <script type="text/javascript" src="/sfFacebookConnectPlugin/js/animation/animation.js"></script>
      </head>
      <body>
        <?php echo $sf_content ?>
        <?php echo include_facebook_connect_script() ?>
      </body>
    </html>

Para alternar automaticamente entre os dois, basta adicionar o seguinte no seu 
arquivo `actions.class.php`:

    [php]
    public function preExecute()
    {
      if (sfFacebook::isInsideFacebook())
      {
        $this->setLayout('layout_fbml');
      }
      else
      {
        $this->setLayout('layout_connect');
      }
    }

>**NOTE**
>Existe uma pequena diferença entre FBML e XFBML que não está localizada
>no layout: tags FBML podem ser fechadas, mas as XFBML não. Então, basta substituir
>as tags a seguir:
>
> [html]
> <fb:profile-pic uid="12345" size="normal" width="400" />
>
>por:
>
> [html]
> <fb:profile-pic uid="12345" size="normal" width="400"></fb:profile-pic>

Claro que, para fazer isto, a aplicação precisa ser configurada também como uma 
aplicação *Facebook Connect* nas configurações do desenvolvedor, mesmo se a aplicação for
apenas para fins FBML. Mas, a grande vantagem de fazer isso é
a possibilidade de testar a aplicação localmente. Se você estiver criando uma
aplicação Facebook e planeja usar tags FBML, o que é quase inevitável,
a única forma de visualizar o resultado é colocar o código online e ver o 
resultado diretamente no Facebook! Felizmente, graças ao *Facebook Connect*,
as tags XFBML podem ser utilizadas fora do facebook.com. E, como foi acima descrito,
a única diferença entre FBML e tags XFBML é o layout.
Portanto, esta solução permite mostrar as tags FBML localmente, enquanto houver
uma conexão de Internet. Além disso, com um ambiente de desenvolvimento visível
na Internet, tal como um servidor ou um computador simples com a porta 80 aberta, até mesmo as partes 
que dependem do sistema de autenticação do Facebook irão funcionar fora do domínio facebook.com 
graças ao *Facebook Connect*. Isso permite testar a aplicação completa antes de carregá-la no Facebook.

### A aplicação simples *Olá Você*

Com o seguinte código no template home, a aplicação *Olá Você* estará 
finalizada:

    [php]
    <?php $sfGuardUser = sfFacebook::getSfGuardUserByFacebookSession(); ?>
    Olá <fb:name uid="<?php echo $sfGuardUser?$sfGuardUser->getProfile()->getFacebookUid():'' ?>"></fb:name>

O `sfFacebookConnectPlugin` converte automaticamente o usuário que está visitando o Facebook
em um usuário `sfGuard`. Isso permite uma integração muito fácil com o código symfony 
existente que depende do `sfGuardPlugin`.

Facebook Connect
----------------

### Como funciona o *Facebook Connect* e as diferentes Estratégias de Integração

O *Facebook Connect*, basicamente, compartilha a sua sessão com a sessão do site. Isto é
feito copiando os cookies de autenticação do Facebook para o site, através da abertura
de um IFrame no site que aponta para uma página no Facebook, e que, por sua vez, abre um 
IFrame para o site. Para fazer isso, o *Facebook Connect* precisa ter acesso ao
site, o que torna impossível usar ou testar o *Facebook Connect* em um 
servidor local ou em uma Intranet. O ponto de entrada é o arquivo `xd_receiver.htm`,
que o `sfFacebookConnectPlugin` disponibiliza. Lembre-se de usar a 
a tarefa `plugin:publish-assets` para tornar esse arquivo acessível.

Uma vez que isso for feito, a biblioteca oficial do Facebook está pronta para usar a sessão do 
Facebook. Além disso, o `sfFacebookConnectPlugin` cria um usuário `sfGuard` 
ligado à sessão do Facebook, que se integra perfeitamente com 
o site do symfony existente. É por isso que o plugin redireciona, por padrão, para a 
ação `sfFacebookConnectAuth/signIn` após ser clicado no botão *Facebook Connect* e 
ser validada a sessão do *Facebook Connect*. O plugin primeiro 
procura por um usuário existente com o mesmo UID do Facebook ou o mesmo hash de e-mail (ver
"Conectando os usuários existentes com a sua conta do Facebook" no final do
artigo. Se nada for encontrado, um novo usuário é criado.

Outra estratégia comum é, não criar o usuário diretamente, mas primeiro redirecioná-lo
para um formulário de registro específico. Lá, pode-se usar a sessão do Facebook
para o pré-preenchimento de informações comuns, por exemplo, adicionando o seguinte código
para o método de configuração do formulário de inscrição:

    [php]
    public function setDefaultsFromFacebookSession()
    {
      if ($fb_uid = sfFacebook::getAnyFacebookUid())
      {
        $ret = sfFacebook::getFacebookApi()->users_getInfo(
          array(
            $fb_uid
          ),
          array(
            'first_name',
            'last_name',
          )
        );

        if ($ret && count($ret)>0)
        {
          if (array_key_exists('first_name', $ret[0]))
          {
            $this->setDefault('first_name',$ret[0]['first_name']);
          }
          if (array_key_exists('last_name', $ret[0]))
          {
            $this->setDefault('last_name',$ret[0]['last_name']);
          }
        }
      }

Para utilizar a segunda estratégia, basta especificar no arquivo `app.yml` para redirecionar
após o *Facebook Connect* e informar a rota que será usada para o redirecionamento:

    [yml]
    # Valores padrão
    all:
      facebook:
        redirect_after_connect: true
        redirect_after_connect_url: '@register_with_facebook'

### O Filtro do *Facebook Connect*

Outra característica importante do *Facebook Connect* é a de que os usuários do Facebook, 
muito freqüentemente, estão logados no Facebook quando navegam na Internet. Aqui é 
onde o `sfFacebookConnectRememberMeFilter` é muito útil. Se um usuário
visita o site e já está conectado no Facebook, o 
`sfFacebookConnectRememberMeFilter` irá automaticamente fazer o login deles no
site exatamente como o filtro "Remember me" faz.

    [php]
    $sfGuardUser = sfFacebook::getSfGuardUserByFacebookSession();
    if ($sfGuardUser)
    {
      $this->getContext()->getUser()->signIn($sfGuardUser, true);
    }

No entanto, isto tem uma séria desvantagem: os usuários não podem mais desconectar do
site, já que, enquanto estiverem conectados no Facebook, serão conectados automaticamente no 
site. Use esse recurso com cautela.

### Implementação Limpa para evitar Erros Fatais de JavaScript no IE

Um dos bugs mais terríveis que você pode ter em um site é o erro "Operação abortada" no IE
que simplesmente trava o processamento do site...
no cliente! Isto acontece devido à má qualidade da renderização do IE6 e 
IE7 que podem falhar se você acrescentar elementos DOM ao elemento `body` à partir de um 
script que não é diretamente um filho do elemento `body`. Infelizmente, este é
normalmente o caso se você carregar o JavaScript do *Facebook Connect* sem ter
cuidado de carregá-lo somente direto do elemento `body` e, no final do 
seu documento. Mas, isto pode ser facilmente resolvido com o symfony usando `slots`. Utilize 
um `slot` para incluir o script do *Facebook Connect* sempre que necessário no 
seu template o processá-lo no layout no final do documento, antes da 
tag `</body>`:

    [php]
    // Em um template que usa uma tag XFBML ou um botão Facebook Connect
    slot('fb_connect');
    include_facebook_connect_script();
    end_slot();

    // adicionado antes do </body> no layout para evitar problemas no IE
    if (has_slot('fb_connect'))
    {
      include_slot('fb_connect');
    }

Melhores Práticas para Aplicações Facebook
----------------------------------------

Graças ao `sfFacebookConnectPlugin`, a integração com o `sfGuardPlugin` 
é realizada sem problemas e a escolha se a aplicação será FBML, IFrame 
ou um site *Facebook Connect* pode esperar até o último minuto. Para ir além 
e criar uma aplicação real com muito mais recursos do Facebook, aqui estão algumas 
dicas importantes que se beneficiam das funcionalidades do symfony.

### Usando os ambientes do symfony para criar vários servidores de teste *Facebook Connect*

Um aspecto muito importante da filosofia do symfony é o da depuração rápida e 
testes de qualidade da aplicação. Usando o Facebook isso pode tornar-se realmente 
difícil, pois muitos recursos precisam de uma conexão com a Internet para se comunicar com 
o servidor do Facebook, e uma porta 80 aberta para a troca dos cookies de 
autenticação. Além disso, há outra restrição: uma aplicação *Facebook Connect* pode 
ser conectada somente a um host. Este é um problema real se a aplicação for
desenvolvida em uma máquina, testada em outra, colocada em pré-produção em um terceiro
servidor e, finalmente, utilizada em um quarto. Nesse caso, a solução mais simples
é realmente criar uma aplicação para cada servidor e criar um 
ambiente symfony para cada um deles. Isto é muito simples no symfony: basta fazer um 
simples copiar e colar do arquivo `frontend_dev.php` ou seu equivalente em 
`frontend_preprod.php` e editar a linha no arquivo recém-criado para mudar 
o ambiente `dev` para o novo ambiente `preprod`:

    [php]
    $configuration = ProjectConfiguration::getApplicationConfiguration('frontend', 'preprod', true);

Em seguida, edite o seu arquivo `app.yml` para configurar as diferentes aplicações Facebook
correspondentes a cada ambiente:

    [yml]
    prod:
      facebook:
        api_key: xxx
        api_secret: xxx
        api_id: xxx

    dev:
      facebook:
        api_key: xxx
        api_secret: xxx
        api_id: xxx

    preprod:
      facebook:
        api_key: xxx
        api_secret: xxx
        api_id: xxx

Agora, a aplicação será testada em todos os diferentes servidores usando o
ponto de entrada correspondente ao `frontend_xxx.php`.

### Usando o sistema de log do symfony para depuração FBML

A solução de troca de layout permite o desenvolvimento e teste de mais de uma 
aplicação FBML fora do site Facebook. No entanto, o teste final no interior
do Facebook pode algumas vezes resultar, no entanto, nas mensagens de erro mais obscuras.
Na verdade, o principal problema do processamento do FBML diretamente no Facebook é o fato de que 
os erros 500 são capturados e substituídos por uma não-muito-útil mensagem de erro
padrão. Além disso, a barra de ferramentas de debug, que os desenvolvedores 
rapidamente ficaram viciados, não é exibida no frame do Facebook. Felizmente, o 
excelente sistema de log do symfony está lá para nos salvar. O 
`sfFacebookConnectPlugin` faz log automaticamente de muitas ações importantes e é fácil
adicionar linhas no arquivo de log em qualquer ponto da aplicação:

    [php]
    if (sfConfig::get('sf_logging_enabled'))
    {
      sfContext::getInstance()->getLogger()->info($message);
    }

### Usando um proxy para evitar redirecionamentos incorretos no Facebook

Um bug estranho do Facebook é que, uma vez que o *Facebook Connect* é configurado na 
aplicação, o servidor do *Facebook Connect* é considerado como home da 
aplicação. Embora a home possa ser configurada, tem que ser no domínio 
do host do *Facebook Connect*. Portanto, não existe outra solução a não ser render-se e 
configurar a sua home para uma ação simples do symfony redirecionando para onde for necessário.
O seguinte código fará um redirecionamento para a aplicação Facebook:

    [php]
    public function executeRedirect(sfWebRequest $request)
    {

      return $this->redirect('http://apps.facebook.com'.sfConfig::get('app_facebook_app_url'));
    }

### Usando o helper `fb_url_for()` nas aplicações do Facebook

Para manter uma aplicação agnóstica, que pode ser usada como FBML no Facebook ou XFBML
em um IFrame até o último minuto, um detalhe importante a ser considerado é o roteamento:

* Para uma aplicação FBML, os links dentro da aplicação precisam apontar para 
   `/app-name/symfony-route`;

* Para uma aplicação IFrame, é importante passar as informações da sessão do Facebook 
   de página em página.

O `sfFacebookConnectPlugin` fornece um helper especial que pode fazer 
ambos, o helper `fb_url_for()`.

### Redirecionando dentro de uma aplicação FBML

Os desenvolvedores symfony rapidamente se acostumam a utilizar um redirecionamento após um post bem-sucedido, que é uma 
boa prática em desenvolvimento web para evitar um post duplo. O redirecionamento em uma 
aplicação FBML, no entanto, não funciona como esperado. Em vez disso, a tag FBML especial `<fb:redirect>`
é necessária para dizer ao Facebook para fazer o redirecionamento. Para ficar
agnóstico, dependendo do contexto (a tag FBML ou o redirecionamento normal do symfony 
) existe uma função especial de redirecionamento na classe `sfFacebook`, que 
pode ser usada, por exemplo, em uma ação onde o formulário é salvo:

    [php]
    if ($form->isValid())
    {
      $form->save();

      return sfFacebook::redirect($url);
    }

### Conectando usuários existentes com as suas contas do Facebook

Um dos objetivos do *Facebook Connect* é facilitar o processo de registro dos 
novos usuários. No entanto, um outro uso interessante é também conectar os usuários 
existentes com sua conta do Facebook, tanto para obter mais informações sobre eles 
quanto para comunicar-se em seu feed. Pode-se conseguir isso de duas formas:

* Obrigar os usuários existentes do sfGuard a clicar no botão "Conectar com o Facebook".
   A ação `sfFacebookConnectAuth/signIn` não vai criar um novo usuário sfGuard 
   se ela detectar um usuário atualmente conectado, mas irá simplesmente salvar o 
   novo usuário *Facebook Connect* no usuário sfGuard atual. Assim fácil.

* Use o sistema de reconhecimento de e-mail do Facebook. Quando um usuário utiliza o *Facebook 
   Connect* em um site, o Facebook pode fornecer um hash especial dos seus e-mails,
   que pode ser comparado com os hashes de e-mail no banco de dados existente para 
   reconhecer uma conta pertencente ao usuário que foi criada anteriormente.
   No entanto, provavelmente por razões de segurança, o Facebook só fornece esses 
   hashes de e-mail se o usuário já tenha se registrado anteriormente usando sua API!
   Portanto, é importante registrar todos os e-mails dos novos usuários regularmente 
   para poder reconhecê-los mais tarde. Isto é o que a tarefa `registerUsers`, 
   que foi portada para o 1.2 por Damien Alexandre, faz. Esta tarefa deve ser executada 
   pelo menos a cada noite, para registrar os usuários recém-criados, ou após um novo usuário ser 
   criado, utilizando o método `registerUsers` do `sfFacebookConnect`:

    [php]
    sfFacebookConnect::registerUsers(array($sfGuardUser));

Indo mais longe
-------------

Espero que este capítulo conseguiu cumprir seu objetivo: ajudar você a começar a desenvolver
uma aplicação Facebook usando o symfony e explicar como beneficiar-se do symfony para o 
seu desenvolvimento Facebook. No entanto, o `sfFacebookConnectPlugin` não 
substituirá a API do Facebook, e, para aprender sobre como usar o poder da 
plataforma de desenvolvimento do Facebook, você terá que visitar o seu
[website](http://developers.facebook.com/).

Para concluir, quero agradecer à comunidade symfony pela sua qualidade e generosidade,
especialmente àqueles que já contribuíram para o `sfFacebookConnectPlugin` através de 
seus comentários e patches: Damien Alexandre, Thomas Parisot, Maxime Picaud,
Alban Creton e desculpe aos outros que eu possa ter esquecido. E, claro, se você sente
que há algo faltando no plugin, não hesite em contribuir você mesmo!