
Usando Doctrine em modo Avançado
=======================

* Por Jonathan H. Wage *

Criando um comportamento para Doctrine
---------------------------

Nesta seção vamos demonstrar como você pode escrever um comportamento usando Doctrine 1.2.
Iremos criar um exemplo que lhe permitirá manter facilmente um contador em cache dos relacionamentos
de modo que você não terá que consultar a contagem a todo momento.

A funcionalidade é bastante simples. Para todos os relacionamentos que você deseja manter
um contador, o comportamento irá adicionar uma coluna ao modelo para armazenar a contagem atual.

### O Schema

Aqui está o Schema que você irá utilizar para começar. Mais tarde vamos modificá-lo e adicionar a
definição do `actAs` para o comportamento que estamos prestes a escrever:

    [yml]
    # config/doctrine/schema.yml
    Thread:
      columns:
        title:
          type: string (255)
          notnull: true

    Post:
      columns:
        thread_id:
          type: integer
          notnull: true
        body:
          type: clob
          notnull: true
      relations:
        Thread:
          onDelete: CASCADE
          foreignAlias: Posts

Agora podemos criar tudo para este schema:

    $ php symfony doctrine:build all

### O Template

Primeiro, precisamos escrever uma classe de template (modelo) `Doctrine_Template` filha que será
responsável por adicionar as colunas ao modelo que irá armazenar a contagem.

Você pode simplesmente colocá-lo em qualquer diretório `lib/` do projeto que o symfony
será capaz de carregá-lo automaticamente para você:

    [php]
    // lib/count_cache/CountCache.class.php
    class CountCache extends Doctrine_Template
    {
      public function setTableDefinition()
      {
      }

      public function setUp ()
      {
      }
    }

Agora vamos modificar o modelo `Post` para `actAs` como comportamento `CountCache`:

    [yml]
    # config/doctrine/schema.yml
    Post:
      actAs:
        CountCache: ~
      # ...

Agora que temos o modelo `Post` usando o comportamento `CountCache` deixe-me explicar
um pouco sobre o que acontece com ele.

Quando a informação de mapeamento de um modelo é instanciada, quaisquer comportamentos ligados
terão os métodos `setTableDefinition()` e `setUp()` invocados. Da mesma forma que você têm
na classe `BasePost` em `lib/model/doctrine/base/BasePost.class.php`. Isto
lhe permite adicionar coisas para qualquer modelo de modo plug n' play. Isto pode estar
nas colunas, relacionamentos, ouvintes de eventos, etc

Agora que você entende um pouco sobre o que está acontecendo, vamos fazer o
comportamento `CountCache` realmente fazer alguma coisa:

    [php]
    class CountCache extends Doctrine_Template
    {
      protected $_options = array(
        'relations' => array()
      };

      public function setTableDefinition()
      {
        foreach ($this->_options['relations'] as $relation => $options)
        {
          // Cria um nome de coluna se um não for dado
          if(!isset($options['columnName']))
          {
            $this->_options['relations'][$relation]['columnName'] = 'num_'.Doctrine_Inflector::tableize($relation);
          }

          // Adiciona a coluna ao modelo relacionado
          $columnName = $this->_options['relations'][$relation]['columnName'];
          $relatedTable = $this->_table->getRelation($relation)->getTable();
          $ this-> _OPTIONS [ 'Relações'] [$ relation] [ 'className'] = $ RelatedTable-> GetOption ( 'nome');
          $relatedTable->setColumn($columnName, 'integer', null, array('default' => 0));
        }
      }
    

O código acima irá agora adicionar colunas para manter a contagem do modelo relacionados.
Portanto, no nosso caso, estamos adicionando o comportamento ao modelo `Post` para o relacionamento 
com `Thread` Nós queremos manter o número de posts qualquer instancia de `Thread`
tem em uma coluna chamada `num_posts`. Então agora modifique o YAML schema para 
definir as opções adicionais para o comportamento:

    [yml]
    # ...

    Post:
      actAs:
        CountCache:
          relations:
            Thread:
              columnName: num_posts
              foreignAlias: Posts
      # ...

Agora, o modelo `Thread` tem uma coluna `num_posts`que irá manter-se atualizada
com o número de posts que cada thread tem.

### O Ouvinte de Eventos

O próximo passo para a construção do comportamento é escrever um ouvinte de evento registrador 
que será responsável por manter a contagem atualizada quando inserirmos novos registros,
excluir um registro ou executar DQL de exclusão de registros em lote:

    [php]
    class CountCache extends Doctrine_Template
    {
      // ...

      public function setTableDefinition()
      {
        // ...

        $this->addListener(new CountCacheListener($this->_options));
      }
    }

Antes de prosseguirmos, precisamos definir a `classe` CountCacheListener que
extende `Doctrine_Record_Listener`. Ele aceita uma variedade de opções que são simplesmente
transmitidos ao ouvinte a partir do modelo:

    [php]
    // lib/model/count_cache/CountCacheListener.class.php 

    class CountCacheListener extends Doctrine_Record_Listener
    {
      protected $_options;

      public function __construct(array $options)
      {
        $this->_options = $options;
      }
    }

Agora, devemos utilizar os seguintes eventos afim de manter o nosso contador atualizado:

* **postInsert()**: Incrementa o contador quando um novo objeto é inserido;

* **postDelete()**: Diminui o contador quando um objeto é excluído;

* **preDqlDelete()**: Diminui o contador quando os registros são eliminados através de
   um DQL Delete.

Primeiro vamos definir o método `postInsert()`:

    [php]
    class CountCacheListener extends Doctrine_Record_Listener
    {
      // ...

      public function postInsert(Doctrine_Event $event)
      {
        $invoker = $event->getInvoker();
        foreach ($this->_options['relations'] as $relation => $options)
        {
          $table = Doctrine::getTable($options['className']);
          $relation = $table->getRelation($options['foreignAlias']);

          $table
            ->createQuery()
            ->Update()
            ->set($options['columnName'], $options['columnName'].' +1)
            ->where($relation['local'].' = ?', $invoker->$relation['foreign'])
            ->execute();
        }
      }
    }

O código acima irá incrementar a contagem em um para todas os relacionamentos configurados
mediante a emissão de uma instrução DQL UPDATE quando um novo objeto como é inserido abaixo:

    [php]
    $post = new Post();
    $ post-> thread_id = 1;
    $post->body = 'corpo da mensagem';
    $post->save();

O `Thread` com o `id` `1` terá a coluna `num_posts` coluna incrementado em `1`.

Agora que o contador está sendo incrementado quando novos objetos são inseridos, nós
precisamos manipular quando objetos são excluídos e diminuir o contador. Faremos isso
implementando o método `postDelete()`:

    [php]
    class CountCacheListener extends Doctrine_Record_Listener
    {
      // ...

      public function postDelete(Doctrine_Event $event)
      {
        $invoker = $event->getInvoker();
        foreach ($this->_options['relations'] as $relation => $options)
        {
          $table = Doctrine::getTable($options['className']);
          $relation = $table->getRelation($options['foreignAlias']);

          $table
            ->createQuery()
            ->Update()
            ->set($options['columnName'], $options['columnName'].' - 1')
            ->where($relation['local'].' = ?', $invoker->$relation['foreign'])
            ->execute();
        }
      }
    }

O método `postDelete()` acima é quase idêntico ao `postInsert`. A
única diferença é que nós diminuiremos a coluna `num_posts` em 1 ao invés de
incrementá-lo. Ele manipularia o seguinte código se fôssemos remover o registro `$post`
salvo previamente:

    [php]
    $post->delete();

A última peça do quebra-cabeça é manipular quando os registros são excluídos usando uma DQL
de Update. Podemos resolver isso usando o método `preDqlDelete()`:

    [php]
    class CountCacheListener extends Doctrine_Record_Listener
    {
      // ...

      public function preDqlDelete(Doctrine_Event $event)
      {
        foreach ($this->_options['relations'] as $relation => $options)
        {
          $table = Doctrine::getTable($options['className']);
          $relation = $table->getRelation($options['foreignAlias']);

          $q = clone $event->getQuery();
          $q->select($relation['foreign']);
          $ids = $q->execute(array(), Doctrine::HYDRATE_NONE);

          foreach ($ids as $id)
          {
            $id = $id[0];

            $table
              ->createQuery()
              ->update()
              ->set($options['columnName'], $options['columnName'].' - 1')
              ->where($relation['local'].' = ?', $id)
              ->execute();
          }
        }
      }
    }

O código acima clona a instrução `DQL DELETE` e transformá-lo em um `SELECT` que
nos permite recuperar os `ID`s que serão excluídos, para que possamos atualizar o contador
desses registros que foram excluídos.

Agora, temos o seguinte cenário cuidando de que o contador será decrementado
se tivéssemos de fazer o seguinte:

    [php]
    Doctrine::getTable('Post')
      ->createQuery()
      ->delete()
      ->where('id = ?', 1)
      ->execute();

Ou mesmo se quiséssemos excluir vários registros o contador ainda seria diminuído
corretamente:

    [php]
    Doctrine::getTable('Post')
      ->createQuery()
      ->delete()
      ->where('body LIKE ?', '%cool%')
      ->execute();

>**NOTA**
>Para que o método `preDqlDelete()` seja invocado você deve habilitar
>um atributo. Os retornos DQL estão desligados por padrão devido a eles terem um custo
>um pouco maior. Então se você quiser usá-los, você deve habilitá-los.
>
> [php]
> $manager->setAttribute(Doctrine_Core::ATTR_USE_DQL_CALLBACKS, true);

E é Isso! O comportamento está terminado. A última coisa que nós vamos fazer é testá-lo um pouco.

### Testando

Agora que temos o código implementado, vamos executar um teste com uma
massa de dados de exemplo:

    [yml]
    # data/fixtures/data.yml

    Thread:
      thread1:
        title: Thread de Teste
        Posts:
          post1:
            body: Este é o corpo do meu thread de teste
          post2:
            body: Isso é muito legal
          post3:
            body: Ya é muito legal

Agora, dê um build para criar tudo de novo e carregar a massa de dados:

    $ php symfony doctrine:build --all --and-load

Agora tudo está criado e a massa de dados está carregada, por isso vamos executar um teste para ver
Se os contadores foram mantidas atualizados:

    $ php symfony doctrine:dql "FROM Thread t, t.Posts p"
    doctrine - executing: "FROM Thread t, t.Posts p" ()
    doctrine - id: '1'
    doctrine - title: 'Thread de Teste'
    doctrine - num_posts: '3'
    doctrine - Posts:
    doctrine - -
    doctrine - id: '1'
    doutrina - thread_id: '1 '
    doctrine - body: 'Este é o corpo do meu thread de teste'
    doctrine - -
    doctrine - id: '2'
    doctrine - thread_id: '1'
    doctrine - body: 'Isto é realmente legal'
    doctrine - -
    doctrine - id: '3'
    doctrine - thread_id: '1'
    doctrine - body: 'Ya é muito legal'

Você verá que o modelo `Thread` tem uma coluna `num_posts`, cujo valor é três.
Se tivéssemos de excluir uma das mensagens com o seguinte código ele irá diminuir
o contador para você:

    [php]
    $post = Doctrine_Core::getTable('Post')->find(1);
    $post->delete();

Você verá que o registro é excluído e o contador é atualizado:

    $ php symfony doctrine:dql "FROM Thread t, t.Posts p"
    doctrine - executing: "FROM Thread t, t.Posts p" ()
    doctrine - id: '1'
    doctrine - title: 'Thread de Teste'
    doctrine - num_posts: '2'
    doctrine - Posts:
    doctrine - -
    doctrine - id: '2'
    doctrine - thread_id: '1'
    doctrine - body: 'Isto é realmente legal'
    doctrine - -
    doctrine - id: '3'
    doctrine - thread_id: '1'
    doctrine - body: "Ya é muito legal'

Isso funciona mesmo se tivéssemos de fazer uma exclusão em lote para os dois registros restantes com uma instrução DQL
Delete:

    [php]
    Doctrine_Core::getTable('Post')
      ->createQuery()
      ->delete()
      ->where('body LIKE ?', '%legal%')
      ->execute();

Agora nós excluimos todos as mensagens relatadas e o valor de `num_posts` deverá ser zero:

    $ php symfony doctrine:dql "FROM Thread t, t.Posts p"
    doctrine - executing: "FROM Thread t, t.Posts p" ()
    doctrine - id: '1'
    doctrine - title: 'Thread de Teste'
    doctrine - num_posts: '0'
    doctrine - Posts: { }

E é isso! Espero que este artigo seja útil tanto no sentido de que você aprenda
algo sobre os comportamentos e os comportamentos em si também sejam úteis à você!

Usando o Cache de Resultados do Doctrine
-----------------------------

Em aplicações web de tráfego pesado é comum necessitar de um cache de informações
para salvar recursos de CPU. Com a última versão do Doctrine 1.2 fizemos um monte de
melhorias no cache de conjunto de resultados que lhe dará muito mais controle sobre
a remoção de entradas no cache a partir dos controladore de cache. Anteriormente não era possível
especificar a chave de cache usado para armazenar a entrada no cache, então você não podia realmente
identificar a entrada de cache a fim de excluí-lo.

Nesta seção, mostraremos um exemplo simples de como você pode utilizar o cacheamento de conjunto de resultados
para cachear todas as consultas relacionadas a seu usuário, bem como o uso de eventos para se certificar
de que eles sejam devidamente apurados quando alguns dado for alterado.

### Nosso Schema

Para este exemplo, vamos usar o seguinte schema:

    [yml]
    # config/doctrine/schema.yml
    User
      columns:
        username:
          type: string(255)
          notnull: true
          unique: true
        password:
          type: string(255)
          notnull: true

Agora vamos criar tudo a partir do esquema com o seguinte comando:

    $ php symfony doctrine:build --all

Uma ves que tenha feito, você deverá ter a seguinte classe `User` gerada:

    [php]
    // lib/model/doctrine/User.class.php
    /**
     * User
     *
     * This class has been auto-generated by the Doctrine ORM Framework
     *
     * @package ##PACKAGE##
     * @subpackage ##SUBPACKAGE##
     * @author ##NAME## <##EMAIL##>
     * @version SVN: $Id: Builder.php 6508 2009-10-14 06:28:49Z jwage $
     */
    class User extends BaseUser
    {
    }

Mais tarde no artigo você vai precisar adicionar algum código para esta classe para fazer a anotação da mesma.

### Configurando o Cache de Resultado

A fim de usar o cache de resultado precisamos configurar um controlador de cache a ser usado para as
consultas. Isto pode ser feito configurando o atributo `ATTR_RESULT_CACHE`.
Iremos usar o controlador de cache APC, pois é a melhor escolha para ambiente de produção. Se você
não tiver APC disponível, você pode usar o controlador `Doctrine_Cache_Db` ou 
`Doctrine_Cache_Array` para fins de teste.

Podemos definir este atributo na nossa classe `ProjectConfiguration`. Defina um método `configureDoctrine()`:

    [php]
    // config/ProjectConfiguration.class.php

    // ...
    class ProjectConfiguration extends sfProjectConfiguration
    {
      // ...

      public function configureDoctrine(Doctrine_Manager $manager)
      {
        $manager->setAttribute(Doctrine_Core::ATTR_RESULT_CACHE, new Doctrine_Cache_Apc());
      }
    }

Agora que temos o controlador de cache de resultado configurado, podemos começar a realmente
utilizar este controlador de cache para os conjuntos de resultados das consultas.

### Consultas de exemplo

Agora imagine que em sua aplicação você tem um grande número de consultas realcioniona ao usuário
e quer apurá-los sempre que algum dado do usuário for alterado.

Aqui está uma consulta simples que podemos usar para processar uma lista de usuários ordenados
alfabeticamente:

    [php]
    $q = Doctrine_Core::getTable('User')
        ->createQuery('u')
        ->orderBy('u.username ASC');

Agora, nós podemos ligar o cache para essa consulta usando o método `useResultCache()`:

    [php]
    $q->useResultCache(true, 3600, 'users_index');

>**NOTA**
>Observe o terceiro argumento. Esta é a chave que será usada para armazenar a entrada
> cacheada para os resultados no controlador de cache. Isso nos permite identificar facilmente
>esta consulta e excluí-la do controlador de cache.

Agora, quando executarmos a consulta quirá irá consultar o banco de dados para buscar os resultados e armazená-
los no controlador de cache na chave chamada `users_index` e todas as requisições posteriores
obterão as informações do controlador de cache em vez de pedir ao banco de dados:

    [php]
    $users = $q->execute();

>**NOTA**
>Isso não somente economiza o processamento no servidor de banco de dados, ele também ignora
> o processo inteiro de hidratação que é como o Doctrine armazena os dados hidratado. Isso significa
> que irá também aliviar um pouco o processamento do seu servidor web.

Agora, se verificar no controlador de cache, você vai ver que existe uma entrada chamada
`users_index`:

    [php]
    if ($cacheDriver->contains('users_index'))
    {
      echo 'cache existe';
    }
    else
    {
      echo 'cache não existe';
    }

### Removendo Cache

Agora que a consulta está em cache, precisamos aprender um pouco sobre como podemos remover
o cache. Nós podemos eliminá-lo manualmente utilizando a API do controlador de cache ou podemos utilizar
alguns eventos para limpar automaticamente a entrada de cache quando um usuário for inserido ou modificado.

#### API do Controlador de Cache

Primeiro vamos apenas demonstrar a API crua do controlador de cache antes de implementá
-lo em um evento.

>**Dica**
> Para ter acesso à instância do controlador de cache de resultado você pode recuperá-lo a partir da
> instância da classe `Doctrine_Manager`.
>
> [php]
> $cacheDriver = $manager->getAttribute(Doctrine_Core::ATTR_RESULT_CACHE)
>
> Se você não tiver acesso imediato à variável `$manager` você pode
> recuperar a instância com o seguinte código.
>
> [php]
> $manager = Doctrine_Manager::getInstance();

Agora podemos começar a usar a API para excluir nossas entradas do cache:

    [php]
    $cacheDriver->delete('users_index');

Você provavelmente terá mais do que uma consulta prefixada com `users_` e poderia fazer
sentido excluir o cache de resultado para todos eles. Neste caso, o método 
`delete()`, por si só não vai funcionar. Para isso temos um método chamado
`deleteByPrefix()` que nos permite apagar qualquer entrada de cache que contiver o
prefixo passado. Aqui está um exemplo:

    [php]
    $cacheDriver->deleteByPrefix('users_');

Temos alguns outros métodos convenientes que podemos usar para eliminar as entradas de cache se o
metódo `deleteByPrefix()` não for suficiente para você:

* `deleteBySuffix($suffix)`: Exclui as entradas de cache que combinem com o sufixo
   passado;

* `deleteByRegularExpression($regex)`: Exclui as entradas de cache que correspondam à
   expressão regular passada;

* `deleteAll()`: Exclui todas as entradas de cache.

### Removendo com eventos

A maneira ideal para limpar o cache seria limpá-lo automaticamente sempre que algum
dado do usuário for modificado. Podemos fazer isso implementando um evento `postSave()` na definição
da classe modelo `User`.

Lembre-se da classe `User` de que falamos anteriormente? Agora precisamos adicionar algum código a ela
portanto abra a classe no seu editor favorito e adicione o seguinte método `postSave()` método:

    [php]
    // lib/model/doctrine/User.class.php

    class User extends BaseUser
    {
      // ...

      public function postSave($event)
      {
        $cacheDriver = $this->getTable()->getAttribute(Doctrine_Core::ATTR_RESULT_CACHE);
        $cacheDriver->deleteByPrefix('users_');
      }
    }

Agora, se fôssemos atualizar um usuário ou inserir um novo ele iria limpar o cache para
todas as consultas relarionadas ao usuário:

    [php]
    $user = new User();
    $user->username = 'jorge';
    user->password = 'mudeme';
    $user->save();

A próxima vez que as consultas forem invocadas ela verá que o cache não existe e buscará
os novos dados do banco de dados para cacheá-los novamente para solicitações subseqüentes.

Embora esse exemplo seja muito simples, ele demonstra muito bem como você pode
usar esses recursos para implementar um cacheamento refinado em suas consultas Doctrine.

Criando Hydratador Doctrine
---------------------------

Uma das principais características do Doctrine é a capacidade de transformar um objeto `Doctrine_Query`
em várias estruturas de conjunto de resultado. Este é o trabalho dos hidratadores do
Doctrine e até a versão 1.2, o hidratadores eram todos codificados rigidamente e não
eram abertos aos desenvolvedores para serem personalizados. Agora que isto mudou, é possível
escrever um hidratador personalizado e criar qualquer estrutura de dados que for desejado
a partir dos dados devolvidos do banco de dados ao executar uma instância `Doctrine_Query`.

Neste exemplo, vamos construir um hidratador que vai ser extremamente simples e de fácil
compreesão, mas muito útil. Ele permitirá que você selecione duas colunas e hidrate
os dados em uma matriz simples onde a primeira coluna selecionada é a chave e a segundo
coluna selecionada é o valor.

### O Schema e a massa de dados

Para começar primeiro precisamos de um schema simples para executar com nossos testes. Vamos usar apenas
um simples modelo `Usuário`:

    [yml]
    # config/doctrine/schema.yml
    User:
      columns:
        username: string(255)
        is_active: string(255)

Precisaremos também de alguns dados para o teste, então copie a massa de dados abaixo:

    [yml]
    # data/fixtures/data.yml
    User:
      user1:
        username: jorge
        password: mudeme
        is_active: 1
      user2:
        username: jorjao
        password: mudeme
        is_active: 0

Agora crie tudo com o seguinte comando:

    $ php symfony doctrine:build --all --and-load

### Escrevendo o Hydratador

Para escrever um hidratador tudo o que precisamos fazer é escrever uma nova classe que se estende `Doctrine_Hydrator_Abstract`
e devemos implementar um método `hydrateResultSet($stmt)`. Ele recebe a instância do `PDOStatement`
usado para executar a instrução. Podemos então utilizar essa declaração para obter os resultados
crus da consulta do PDO e em então transformá-lo para nossa própria estrutura.

Vamos criar uma nova classe denominada `KeyValuePairHydrator` e colocá-la no diretório
`lib/` de modo que o symfony possa carregá-la automaticamente:

    [php]
    // lib/KeyValuePairHydrator.class.php
    class KeyValuePairHydrator extends Doctrine_Hydrator_Abstract
    {
      public function hydrateResultSet($stmt)
      {
        return $stmt->fetchAll(Doctrine_Core::FETCH_NUM);
      }
    }

O código acima como está agora irá apenas devolver os dados exatamente como ele vem do PDO.
Isto não é exatamente o que queremos. Queremos transformar esses dados para a nossa própria estrutura de pares
chave => valor. Então vamos modificar um pouco o método `hydrateResultSet()` para que ela faça
o que queremos:

    [php]
    // lib/KeyValuePairHydrator.class.php
    class KeyValuePairHydrator extends Doctrine_Hydrator_Abstract
    {
      public function hydrateResultSet($stmt)
      {
        $results = $stmt->fetchAll(Doctrine_Core::FETCH_NUM);
        $array = array();
        foreach ($results as $result)
        {
          $array[$result[0]] = $result[1];
        }

        return $array;
      }
    }

Bem, isso foi fácil! O código hidratadr está terminado e ele faz exatamente o que queremos
Portanto, vamos testá-lo!

### Usando o Hidratador

Para usar e testar o hidratador primeiro precisamos registrá-lo com o Doctrina de forma que
quando nós executarmos algumas instruções, Doctrina esteja ciente da classe hidratador que escrevemos.

Para fazer isso, registre-o na instância do `Doctrine_Manager` em `ProjectConfiguration`:

    [php]
    // config/ProjectConfiguration.class.php

    // ...
    class ProjectConfiguration extends sfProjectConfiguration
    {
      // ...

      public function configureDoctrine(Doctrine_Manager $manager)
      {
        $manager->registerHydrator('key_value_pair', 'KeyValuePairHydrator');
      }
    }

Agora que temos o hidratador registrado, podemos fazer uso dele com as instâncias de
`Doctrine_Query`. Aqui está um exemplo:

    [php]
    $q = Doctrine_Core::getTable('User')
      ->createQuery('u')
      ->select('u.username, u.is_active');

    $results = $q->execute(array(), 'key_value_pair');
    print_r($results);

Executando a consulta acima com a massa de dados definida mais acima resultaria
no seguinte:

    Array
    (
        [jorge] => 1
        [jorjao] => 0
    )

Bem, é isso! Simplesmente lindo não? Espero que isso seja útil a você e como resultado
a comunidade terá alguns novos e impressivos hidratadores contribuídos.