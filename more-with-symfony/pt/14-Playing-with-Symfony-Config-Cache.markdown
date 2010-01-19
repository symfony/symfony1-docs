
Brincando com o Cache da Configuração do symfony
===================================

*por Kris Wallsmith*

Um dos meus objetivos pessoais como desenvolvedor symfony é tornar o mais eficiente 
possível o fluxo de trabalho de cada um de meus colegas em qualquer projeto. Embora eu conheça
nosso código de cor, esta não é uma expectativa razoável para 
todos do time. Felizmente, o symfony fornece mecanismos para isolar e
centralizar funcionalidades dentro de um projeto, tornando fácil para outros fazer
mudanças com um *footprint* leve.

Strings de Formulários
------------

Um excelente exemplo disso é o *framework* de formulário do symfony. O *framework* de formulário
é um componente poderoso do symfony que lhe dá grande controle sobre seus
formulários, transferindo seu processamento de renderização e validação para objetos PHP. Esta é uma
dádiva de Deus para o desenvolvedor do aplicativo, porque significa que você pode encapsular
lógica complexa em uma classe única e estendê-la e reutilizá-la em vários
lugares.

No entanto, da perspectiva do desenvolvedor de *templates*, essa abstração de como um
um formulário se renderiza pode ser problemática. Dê uma olhada na seguinte formulário:

![Formulário em seu estado padrão](http://www.symfony-project.org/images/more-with-symfony/config_cache_form_default.png)

A classe que configura este formulário se parece com isto:

    [php]
    // lib/form/CommentForm.class.php
    class CommentForm extends BaseForm
    {
      public function configure ()
      {
        $this->setWidget('body', new sfWidgetFormTextarea());
        $this->setValidator('body', new sfValidatorString(array(
          'min_length' => 12,
        )));
      }
    }

O formulário é então renderizado em um *template* PHP como este:

    <!-- apps/frontend/modules/principal/templates/indexSuccess.php ->
    <form action="#" method="post">
      <ul>
        <li>
          <?php echo $form['body']->renderLabel() ?>
          <?php echo $form['body'] ?>
          <?php echo $form['body']->renderError() ?>
        </li>
      </ul>
      <p><button type="submit">Envie seu comentário agora</button></p>
    </form>

O desenvolvedor de *template* tem um pouco de controle sobre como este formulário é
renderizado. Ele pode mudar os *labels* padrões para serem um pouco mais amigáveis:

    <?php echo $form['body']->renderLabel('Por favor, escreva o seu comentário') ?>

Ele pode adicionar uma classe para os campos de entrada:

    <?php echo $form['body']->render(array('class' => 'comment')) ?>

Essas modificações são intuitivas e fáceis. Mas e se ele precisar modificar uma
mensagem de erro?

![Formulário em seu estado de erro](http://www.symfony-project.org/images/more-with-symfony/config_cache_form_error.png)

O método `->renderError()` não aceita nenhum argumento, de modo que o único recurso do
desenvolvedor de *template* é abrir o arquivo de classe do formulário, localizar o código que
cria o validador em questão, e modificar seu construtor para que as novas mensagens
de erro estejam associadas com os códigos de erro apropriados.

No nosso exemplo, o desenvolvedor de *template* teria que fazer a seguinte
mudança:

    [php]
    // antes
    $this->setValidator('body', new sfValidatorString(array(
      'min_length' => 12,
    )));

    // depois
    $this->setValidator('body', new sfValidatorString(array(
      'min_length' => 12,
    ), array(
      'min_length' => 'Você não 'escreveu' o suficiente',
    )));

Viu o problema? Ops! Eu usei um apóstrofo simples dentro de uma string com apóstrofo simples. 
Claro que você ou eu nunca cometeríamos um erro bobo desses, mas o que um
um desenvolvedor de *template* fuçando dentro de uma classe de formulário não faria?

Com toda seriedade, podemos esperar que os desenvolvedores de *template* saibam se virar
no *framework* de formulário do symfony bem o suficiente para localizar exatamente onde uma mensagem
de erro está definida? É esperado de alguém que está trabalhando na camada de visão saber
a assinatura de um construtor de um validador?

Tenho certeza de que todos podemos concordar que a resposta a estas perguntas é não.
Desenvolvedores de *template* fazem um monte de trabalho valioso, mas é simplesmente absurdo
esperar que alguém que não está escrevendo o código do aplicativo compreenda o funcionamento interno 
do *framework* de formulário do symfony.

YAML: Uma Solução
----------------

Para simplificar o processo de edição de strings do formulário, nós iremos adicionar uma camada de
configuração YAML que realça cada objeto de formulário que é passado para a visão.
Este arquivo de configuração será parecido com isto:

    [yml]
    # config/forms.yml
    CommentForm:
      body:
        label: Por favor, escreva seu comentário
        attributes: { class: comment }
        errors:
          min_length: Você não 'escreveu' o suficiente

Isto é muito mais fácil, certo? A configuração explica-se, além do que
o problema com o apostrofo que passamos anteriormente agora é discutível. Então vamos construí-lo!

Filtrando Variáveis de Template
----------------------------

O primeiro desafio é encontrar um gancho no symfony que nos permitirá filtrar
todas as variáveis de formulário passadas a um *template* com essa configuração. Para
isso, usamos o evento `template.filter_parameters`, que é disparado a partir do
núcleo do symfony pouco antes de renderizar um *template* ou um *template partial*.

    [php]
    // lib/form/sfFormYamlEnhancer.class.php
    class sfFormYamlEnhancer
    {
      public function connect(sfEventDispatcher $dispatcher)
      {
        $dispatcher->connect('template.filter_parameters',
          array($this, 'filterParameters'));
      }

      public function filterParameters(sfEvent $event, $parameters)
      {
        foreach ($parameters as $name => $param)
        {
          if ($param instanceof sfForm && !$param->getOption('is_enhanced'))
          {
            $this->enhance($param);
            $param->setOption('is_enhanced', true);
          }
        }

        return $parameters;
      }

      public function enhance(sfForm $form)
      {
        // ...
      }
    }

>**NOTE**
>Perceba que este código verifica uma opção `is_enhanced` em cada objeto de formulário antes
>de realçá-lo (método `enhance`). Isto é para prevenir que formulários passados de *templates* para *partials* sejam
>realçados duas vezes.

Esta classe realçadora - `sfFormYamlEnhancer` - precisa ser conectada a partir de sua configuração do aplicativo:

    [php]
    // apps/frontend/config/frontendConfiguration.class.php
    class frontendConfiguration extends sfApplicationConfiguration
    {
      public function initialize()
      {
        $enhancer = new sfFormYamlEnhancer($this->getConfigCache());
        $enhancer->connect($this->dispatcher);
      }
    }

Agora que somos capazes de isolar variáveis de formulários pouco antes de eles serem passados para um
*template* ou *partial*, temos tudo que precisamos para fazer isto funcionar. A última
tarefa é aplicar o que foi configurado no YAML.

Aplicando o YAML
-----------------

A maneira mais fácil de aplicar esta configuração YAML para cada formulário é para carregá-lo
em um array e passar através de cada configuração:

    [php]
    public function enhance(sfForm $form)
    {
      $config = sfYaml::load(sfConfig::get('sf_config_dir').'/forms.yml');

      foreach ($config as $class => $fieldConfigs)
      {
        if ($form instanceof $class)
        {
          foreach ($fieldConfigs as $fieldName => $fieldConfig)
          {
            if (isset($form[$fieldName]))
            {
              if (isset($fieldConfig['label']))
              {
                $form->getWidget($fieldName)->setLabel($fieldConfig['label']);
              }

              if (isset($fieldConfig['attributes']))
              {
                $form->getWidget($fieldName)->setAttributes(array_merge(
                  $form->getWidget($fieldName)->getAttributes(),
                  $fieldConfig['attributes']
                ));
              }

              if (isset($fieldConfig['errors']))
              {
                foreach ($fieldConfig['errors'] as $code => $msg)
                {
                  $form->getValidator($fieldName)->setMessage($code, $msg);
                }
              }
            }
          }
        }
      }
    }

Há uma série de problemas com essa implementação. Primeiro, o arquivo YAML
é lido a partir do sistema de arquivos e carregados em `sfYaml` cada vez que um formulário é
realçado. Leitura do sistema de arquivos desta forma deve ser evitada.
Segundo, existem vários níveis de loops aninhados e uma série de condicionais
que só vai atrasar a sua aplicação para baixo. A solução para ambos os
problemas reside no cache de configuração do symfony.

O Cache de Configuração
----------------

O cache de configuração é composto por uma coleção de classes que otimizam o uso
dos arquivos de configuração YAML, automatizando sua tradução para código PHP e
escrevendo este código para o diretório de cache para execução. Este mecanismo irá
eliminar a sobrecarga necessária para carregar o conteúdo de nosso arquivo de
configuração para o `sfYaml` antes de aplicar os seus valores.

Vamos implementar um cache de configuração para o nosso realçador de formulário (`sfFormYamlEnhancer`). Em vez de carregar
`forms.yml` para o `sfYaml`, vamos pedir ao atual cache de configuração do aplicativo
por uma versão pré-processada.

Para fazer isso, a classe `sfFormYamlEnhancer` precisará de acesso ao atual
cache de configuração do aplicativo, por isso vamos acrescentar isto ao construtor.

    [php]
    class sfFormYamlEnhancer
    {
      protected
        $configCache = null;

      public function __construct(sfConfigCache $configCache)
      {
        $this->configCache = $configCache;
        $this->configCache->registerConfigHandler->('config/forms.yml',
          'sfSimpleYamlConfigHandler');
      }

      // ...
    }

O cache de configuração precisa ser avisado sobre o que fazer quando um certo arquivo de configuração
é solicitada pelo aplicativo. Por hora, instruimos o cache de configuração para
usar `sfSimpleYamlConfigHandler` para processar `forms.yml`. Esse manipulador de configuração
simplesmente faz o *parsing* do YAML para um array e armazena-o como código PHP.

Com o cache de configuração no lugar e um manipulador de configuração registrado para `forms.yml`,
agora podemos chamá-lo ao invés de `sfYaml`:

    [php]
    public function enhance(sfForm $form)
    {
      $config = include $this->configCache->checkConfig('config/forms.yml');

      // ...
    }

Isto é muito melhor. Não só eliminamos a sobrecarga de *parsing* do YAML
em todas as requisições menos a primeira, como nós também passamos a usar `include`, que
expõe esta leitura para as bênçãos do cache do op-code.

>**SIDEBAR**
>Ambiente de Desenvolvimento vs Ambiente de Produção
>
>Os detalhes do `->checkConfig()` diferem dependendo se seu
>modo de debug do aplicativo está ligado ou desligado. Em seu ambiente `prod`, quando o modo de debug
>estiver desligado, este método funciona como descrito aqui:
>
> * Verifique se há uma versão em cache do arquivo solicitado
> * Se ele existir, retornar o caminho para o arquivo em cache
> * Se ele não existir:
> * Processe o arquivo de configuração
> * Salve o código resultante para o cache
> * Retorna o caminho para o novo arquivo em cache
>
>Este método funciona de forma diferente quando está com o modo debug ligado. Como os arquivos de configuração
>são editados durante o curso do desenvolvimento, `->checkConfig()` irá comparar
>quando os arquivos originais e os armazenados em cache foram modificados pela última vez para se certificar de que obtenha
>versão mais recente. Isso adiciona mais alguns passos ao
>funcionamento do modo de debug desligado:
>
> * Verifique se há uma versão em cache do arquivo solicitado
> * Se ele não existir:
> * Processe o arquivo de configuração
> * Salve o código resultante para o cache
> * Se ele existir:
> * Compare quando os arquivos de configuração e os de cache foram modificados pela última vez
> * Se o arquivo de configuração foi modificado mais recentemente:
> * Processe o arquivo de configuração
> * Salve o código resultante para o cache
> * Retorna o caminho para o arquivo em cache

Cubra-me, eu vou entrar!
-----------------------

Vamos escrever alguns testes antes de ir adiante. Podemos começar com este script
básico:

    [php]
    // test/unit/form/sfFormYamlEnhancerTest.php
    include dirname(__FILE__).'/../../bootstrap/unit.php';

    $t = new lime_test(3);

    $configuration = $configuration->getApplicationConfiguration(
      'frontend', 'test', true, null, $configuration->getEventDispatcher());
    sfToolkit::clearDirectory(sfConfig::get('sf_app_cache_dir'));

    $enhancer = new sfFormYamlEnhancer($configuration->getConfigCache());

    // ->enhance()
    $t->diag('->enhance()');

    $form = new CommentForm();
    $form->bind(array('body' => '+1'));

    $enhancer->enhance($form);

    $t->like($form['body']->renderLabel(), '/Por favor, escreva o seu comentário/',
      '->enhance() realça os labels');
    $t->like($form['body']->render(), '/class="comment"/',
      '->enhance() realça os widgets');
    $t->like($form['body']->renderError(), '/Você não 'escreveu' o suficiente/',
      '->enhance() realça as mensagens de erro');

A execução deste teste contra o atual `sfFormYamlEnhancer` verifica se ele está
funcionando corretamente:

![Testes passando](http://www.symfony-project.org/images/more-with-symfony/config_cache_tests_3_ok.png)

Agora nós podemos tratar da refatoração com a confiança que nossos testes irão acusar
algo se quebrarmos alguma coisa.

Manipuladores de Configuração Customizados
----------------------

No código do realçador (*enhancer*) acima, cada variável de formulário passada a um *template* irá passar
por cada classe de formulário configurado em `forms.yml`. Isto realiza o trabalho,
mas se você passar vários objetos de formulário a um *template*, ou tem uma longa lista de
formulários configurados no YAML, você poderá começar a ver um impacto na performance.
Esta é uma boa oportunidade para escrever um manipulador de configuração customizado que otimiza
este processo.

>**SIDEBAR**
>Por que customizar?
>
>Escrever um manipulador de configuração customizado não é para os fracos de coração. Assim como qualquer
>gerador de código, os manipuladores de configuração podem ser sujeitos a erros e difíceis de testar,
>mas os benefícios podem ser muitos. Criando lógica complexa *on-the-fly*
>atinge um ponto ótimo que lhe dá a vantagem da flexibilidade do YAML e a
>baixa sobrecarga de código PHP nativo. Com um cache de op-code adicionado à mistura (como
>[APC](http://pecl.php.net/apc) ou [XCache](http://xcache.lighttpd.net/))
>manipuladores de configuração são difíceis de bater pela facilidade de uso e desempenho.

A maior parte da magia dos manipuladores de configuração acontece nos bastidores. O cache de
configuração cuida da lógica de cache antes de executar qualquer manipulador de configuração
especial para que possamos nos concentrar apenas em gerar o código necessário para aplicar a
configuração YAML.

Cada manipulador de configuração deve implementar os dois métodos a seguir:

* `static public function getConfiguration(array $configFiles)`
* `public function execute($configFiles)`

Ao primeiro método, `::getConfiguration()`, é passado um array de caminhos de arquivo,
faz o *parsing* deles e funde seu conteúdo em um único valor. Na
class `sfSimpleYamlConfigHandler` que usamos anteriormente, este método inclui apenas uma
linha:

    [php]
    static public function getConfiguration(array $configFiles)
    {
      return self::parseYamls($configfiles);
    }

A classe `sfSimpleYamlConfigHandler` estende a classe abstrata
`sfYamlConfigHandler` que inclui uma série de métodos auxiliares para o processamento
de arquivos de configuração YAML:

* `::parseYamls($configFiles)`
* `::parseYaml($configFile)`
* `::parseYaml($configFile)`
* `::parseYaml($configFile)`

Os dois primeiros métodos de implementam a
[configuração em cascata](http://www.symfony-project.org/reference/1_2/en/03-Configuration-Files-Principles#chapter_03_configuration_cascade) do symfony (*configuration cascade*).
Os outros implementam a
[questão do ambiente](http://www.symfony-project.org/reference/1_2/en/03-Configuration-Files-Principles#chapter_03_environment_awareness) do symfony (*environment-awareness*).

O método `::getConfiguration()` em nosso manipulador de configuração vai precisar de um método
customizado para mesclar a configuração com base na herança de classe. Crie um método
`::applyInheritance()` que encapsula esta lógica:

    [php]
    // lib/config/sfFormYamlEnhancementsConfigHander.class.php
    class sfFormYamlEnhancementsConfigHander extends sfYamlConfigHandler
    {
      public function execute($configFiles)
      {
        $config = self::getConfiguration($configFiles);

        // Compilação de dados
        $retval = "<?php\n".
                  "// auto-generated by %s\n".
                  "// date: %s\nreturn %s;\n";
        $retval = sprintf($retval, __CLASS__, date('Y/m/d H:i:s'),
          var_export($config, true));

        return $retval;
      }

      static public function getConfiguration(array $configFiles)
      {
        return self::applyInheritance(self::parseYamls($configFiles));
      }

      static public function applyInheritance($config)
      {
        $classes = array_keys($config);

        $merged = array();
        foreach ($classes as $class)
        {
          if (class_exists($class))
          {
            $merged[$class] = $config[$class];
            foreach (array_intersect(class_parents($class), $classes) as $parent)
            {
              $merged[$class] = sfToolkit::arrayDeepMerge(
                $config[$parent],
                $merged[$class]
              );
            }
          }
        }

        return $merged;
      }
    }

Agora temos um array cujos valores foram agrupados por classe de herança. Isto
elimina a necessidade de filtrar toda a configuração através do `instanceof`
para cada objeto de formulário. Além do mais, esta fusão é feita no manipulador de configuração, então
só vai acontecer uma vez e depois ser armazenados em cache.

Agora podemos aplicar esse array fundido para formar um objeto com uma simples
lógica de pesquisa:

    [php]
    class sfFormYamlEnhancer
    {
      protected
        $configCache = null;

      public function __construct(sfConfigCache $configCache)
      {
        $this->configCache = $configCache;
        $this->configCache->registerConfigHandler('config/forms.yml',
          'sfFormYamlEnhancementsConfigHander');
      }

      // ...

      public function enhance(sfForm $form)
      {
        $config = include $this->configCache->checkConfig('config/forms.yml');

        $class = get_class($form);
        if (isset($config[$class]))
        {
          $fieldConfigs = $config[$class];
        }
        else if ($overlap = array_intersect(class_parents($class),
          array_keys($config)))
        {
          $fieldConfigs = $config[current($overlap)];
        }
        else
        {
          return;
        }

        foreach ($fieldConfigs as $fieldName => $fieldConfig)
        {
          // ...
        }
      }
    }

Antes de executar o script de teste novamente, vamos adicionar uma asserção para a nova lógica de
herança de classe.

    [yml]
    # config/forms.yml

    # ...

    BaseForm:
      body:
        errors:
          min_length: Uma mensagem base para min_length
          required: Uma mensagem base para required

Podemos verificar que a nova mensagem para `required` está sendo aplicada no script
de teste, e confirma que formulários filhos receberão as melhorias de seus pais,
mesmo se não houver nenhum configuraçãl para a classe filha.

    [php]
    $t = new lime_test(5);

    // ...

    $form = new CommentForm();
    $ form->bind();
    $enhancer->enhance($form);
    $t->like($form['body']->renderError(), '/Uma mensagem base para required/',
      '->enhance() considera herança');

    class SpecialCommentForm extends CommentForm { }
    $form = new SpecialCommentForm();
    $form->bind();
    $enhancer->enhance($form);
    $t->like($form['body']->renderLabel(), '/Por favor, escreva seu comentário/',
      '->enhance() aplica configuração da classe pai');

Execute este script de teste atualizado para verificar se o realçador de formulário está funcionando como
esperado.

![Testes passando](http://www.symfony-project.org/images/more-with-symfony/config_cache_tests_5_ok.png)

Caprichando com Formulários Embarcados (*Embedded Forms*)
---------------------------------

Existe uma característica importante do *framework* de formulário do symfony que ainda
não consideramos: formulários embarcados. Se uma instância de `CommentForm` é embarcado em
outro formulário, os realces que fizemos em `forms.yml` não serão aplicados.
Isso é fácil de demonstrar, em nosso script de teste:

    [php]
    $t = new lime_test(6);

    // ...

    $form = new BaseForm();
    $form->embedForm('comment', new CommentForm());
    $form->bind();
    $enhancer->enhance($form);
    $t->like($form['comment']['body']->renderLabel(),
      '/Por favor, escreva seu comentário/',
      '->enhance() realça formulários embarcados');

Esta nova asserção demonstra que formulários embarcados não estão sendo realçados:

![Testes falhando](http://www.symfony-project.org/images/more-with-symfony/config_cache_tests_6_not_ok.png)

Consertar este teste envolverá um manipulador de configuração mais avançado. Temos de ser
capazes de aplicar os realces configurados em `forms.yml` de uma forma modular para
contar com os formulários embarcados, assim nós vamos gerar um método realçador
especialmente feito para cada classe de formulário configurada. Estes métodos serão gerados pelo nosso
manipulador personalizado de configuração em uma nova classe "trabalhadora".

    [php]
    class sfFormYamlEnhancementsConfigHander extends sfYamlConfigHandler
    {
      // ...

      protected function getEnhancerCode($fields)
      {
        $code = array();
        foreach ($fields as $field => $config)
        {
          $code[] = sprintf('if (isset($fields[%s]))', var_export($field, true));
          $code[] = '{';

          if (isset($config['label']))
          {
            $code[] = sprintf(' $fields[%s]->getWidget()->setLabel(%s);',
              var_export($config['label'], true));
          }

          if (isset($config['attributes']))
          {
            $code[] = ' $fields[%s]->getWidget()->setAttributes(array_merge(';
            $code[] = ' $fields[%s]->getWidget()->getAttributes(),';
            $code[] = ' '.var_export($config['attributes'], true);
            $code[] = ' ));';
          }

          if (isset($config['errors']))
          {
            $code[] = sprintf(' if ($error = $fields[%s]->getError())',
              var_export($field, true));
            $code[] = ' {';
            $code[] = ' $error->getValidator()->getMessages(),';
            $ code [] = '$ error-> getValidator () -> getMessages (),';
            $code[] = ' '.var_export($config['errors'], true);
            $code[] = ' ));';
            $code[] = ' }';
          }

          $code[] = '}';
        }

        return implode(PHP_EOL.' ', $code);
      }
    }

Observe como o array de configuração está marcado para certas chaves quando o código é
gerado, em vez de em tempo de execução. Isto proporcionará uma pequena melhora de
desempenho.

>**TIP**
>Como regra geral, a lógica que verifica as condições da configuração deve
>ser executada no manipulador de configuração, não no código gerado. Lógica que verifica
>condições de tempo de execução, como a natureza do objeto de formulário a ser realçado,
>deve ser executado em tempo de execução.

Este código gerado é colocado dentro de uma definição de classe, que é então salvo
no diretório de cache.

    [php]
    class sfFormYamlEnhancementsConfigHander extends sfYamlConfigHandler
    {
      public function execute($configFiles)
      {
        $forms = self::getConfiguration($configFiles);

        $code = array();
        $code[] = '<?php';
        $code[] = '// auto-generated by '.__CLASS__;
        code[] = '// date: '.date('Y/m/d H:is');
        $code[] = 'class sfFormYamlEnhancementsWorker';
        $code[] = '{';
        $code[] = ' static public $enhancable = '.var_export(array_keys($forms), true).';';

        foreach ($forms as $class => $fields)
        {
          $code[] = ' static public function enhance'.$class.'(sfFormFieldSchema $fields)';
          $code[] = ' {';
          $code[] = ' '.$this->getEnhancerCode($fields);
          $code[] = ' }';
        }

        $code[] = '}';

        return implode(PHP_EOL, $code);
      }

      // ...
    }

A classe `sfFormYamlEnhancer` vai agora submeter-se a classe trabalhadora gerada para
lidar com a manipulação de objetos de formulários, mas agora deve levar em conta para a recursividade
através de formulários embarcados. Para fazer isso devemos processar o esquema de campo do formulário
(que pode ser iterado recursivamente) e o objeto do formulário (que
inclui os formulários embarcados) em paralelo.

    [php]
    class sfFormYamlEnhancer
    {
      // ...

      public function enhance(sfForm $form)
      {
        require_once $this->configCache->checkConfig('config/forms.yml');
        $this->doEnhance($form->getFormFieldSchema(), $form);
      }

      protected function doEnhance(sfFormFieldSchema $fieldSchema, sfForm $form)
      {
        if ($enhancer = $this->getEnhancer(get_class($form)))
        {
          call_user_func($enhancer, $fieldSchema);
        }

        foreach ($form->getEmbeddedForms() as $name => $form)
        {
          if (isset($fieldSchema[$name]))
          {
            $this->doEnhance($fieldSchema[$name], $form);
          }
        }
      }

      public function getEnhancer($class)
      {
        if (in_array($class, sfFormYamlEnhancementsWorker::$enhancable))
        {
          return array('sfFormYamlEnhancementsWorker', 'enhance'.$class);
        }
        else if ($overlap = array_intersect(class_parents($class),
          sfFormYamlEnhancementsWorker::$enhancable))
        {
          return array('sfFormYamlEnhancementsWorker', 'enhance'.current($overlap));
        }
      }
    }

>**NOTE**
>Os campos em objetos de formulários embarcados não devem ser modificados depois de terem sido
>embarcados. Formulários embarcados são armazenados no formulário pai para efeitos de 
>processamento, mas não têm nenhum efeito sobre a forma como o formulário pai é renderizado.

Com suporte para formulários embarcados pronto, nossos testes agora devem passar. Execute
o script para descobrir:

![Testes passando](http://www.symfony-project.org/images/more-with-symfony/config_cache_tests_6_ok.png)

Como nós faríamos?
------------

Vamos executar alguns *benchmarks* apenas para ter certeza de que nós não perdemos o nosso tempo. Para deixar
os resultados mais interessantes, adicione mais algumas classes de formulário para `forms.yml` usando um
loop do PHP.

    [yml]
    # <?php for ($i = 0; $i < 100; $i++): ?> #
    Forma <?php echo $i ?>: ~
    # <?php endfor; ?> #

Crie todas estas classes, executando o seguinte trecho de código:

    [php]
    mkdir($dir = sfConfig::get('sf_lib_dir').'/form/test_fixtures');
    for ($i = 0; $i < 100; $i++)
    {
      file_put_contents($dir.'/Form'.$i.'.class.php',
        '<?php class Form'.$i.' extends BaseForm { }');
    }

Agora estamos prontos para executar alguns *benchmarks*. Para os resultados abaixo, eu rodei o
seguinte comando [Apache](http://httpd.apache.org/docs/2.0/programs/ab.html)
em meu MacBook várias vezes até que eu tenho um desvio padrão inferior a
2ms.

    $ ab -t 60 -n 20 http://localhost/config_cache/web/index.php

Comece com um *benchmark* de base para executar o aplicativo sem o
realçador totalmente não conectado. Comente o `sfFormYamlEnhancer` no
`frontendConfiguration` e rode o *benchmark*:

    Connection Times (ms)
                  min mean[+/-sd] median max
    Connect: 0 0 0.0 0 0
    Processing: 62 63 1.5 63 69
    Waiting: 62 63 1.5 63 69
    Total: 62 63 1.5 63 69

Em seguida, cole a primeira versão de `sfFormYamlEnhancer::enhance()` que chamou
`sfYaml` diretamente da classe e execute o *benchmark*:

    Connection Times (ms)
                  min mean[+/-sd] median max
    Connect: 0 0 0.0 0 0
    Processing: 87 88 1.6 88 93
    Waiting: 87 88 1.6 88 93
    Total: 87 88 1.7 88 94

Você pode ver que nós adicionamos uma média de 25ms para cada pedido, um aumento de
quase 40%. Em seguida, desfaça a mudança que você acabou de fazer ao `->enhance ()` para que nosso manipulador
de configuração customizado seja restaurado e execute o *benchmark* novamente:

    Connection Times (ms)
                  min mean[+/-sd] median max
    Connect: 0 0 0.0 0 0
    Processing: 62 63 1.6 63 70
    Waiting: 62 63 1.6 63 70
    Total: 62 64 1.6 63 70

Como você pode ver, nós reduzimos o tempo de processamento de volta para a base, criando
um manipulador de configuração customizado.

Apenas por diversão: Empacotando um Plugin
-------------------------------

Agora que temos este grande sistema pronto para realçar objetos de formulário com um
simples arquivo de configuração YAML, porque não empacotá-lo como um plugin e compartilhá-lo
com a comunidade. Isto pode parecer intimidante para aqueles que ainda não publicaram
um plugin no passado; com sorte, poderemos acabar com parte deste medo agora.

Este plugin terá a seguinte estrutura de arquivos:

    sfFormYamlEnhancementsPlugin/
      config/
        sfFormYamlEnhancementsPluginConfiguration.class.php
      lib/
        config/
          sfFormYamlEnhancementsConfigHander.class.php
        form/
          sfFormYamlEnhancer.class.php
      test/
        unit/
          form/
            sfFormYamlEnhancerTest.php

Nós precisamos fazer algumas modificações para facilitar o processo de instalação do plugin.
Criação e conexão do objeto realçador (*enhancer*) pode ser encapsulado na
classe de configuração do plugin:

    [php]
    class sfFormYamlEnhancementsPluginConfiguration extends sfPluginConfiguration
    {
      public function initialize()
      {
        if ($this->configuration instanceof sfApplicationConfiguration)
        {
          $enhancer = new sfFormYamlEnhancer($this->configuration->getConfigCache());
          $enhancer->connect($this->dispatcher);
        }
      }
    }

O script de teste deverá ser atualizado para fazer referência ao script de *bootstrap* do
projeto:

    [php]
    include dirname(__FILE__).'/../../../../../test/bootstrap/unit.php';

    // ...

Finalmente, ative o plugin no `ProjectConfiguration`:

    [php]
    class ProjectConfiguration extends sfProjectConfiguration
    {
      public function setup()
      {
        $this->enablePlugins('sfFormYamlEnhancementsPlugin');
      }
    }

Se você deseja executar testes do plugin, conecte-os em
`ProjectConfiguration` agora:

    [php]
    class ProjectConfiguration extends sfProjectConfiguration
    {
      // ...

      public function setupPlugins()
      {
        $this->pluginConfigurations['sfFormYamlEnhancementsPlugin']->connectTests();
      }
    }

Agora, os testes do plugin irão executar quando você chamar qualquer uma das tarefas
`test:*`

![Testes do Plugin](http://www.symfony-project.org/images/more-with-symfony/config_cache_plugin_tests.png)

Todas as nossas classes agora estão localizadas no novo diretório do plugin, mas há
um problema: o script de teste se baseia em arquivos que ainda estão localizados no
projeto. Isto significa que qualquer pessoa que queira executar estes testes, não
será capaz, a não ser que tenham os mesmos arquivos em seu projeto.

Para corrigir isso, vamos precisar isolar o código na classe realçadora que chama
o cache de configuração, de modo que podemos sobrecarregá-lo em nosso script de teste e usar uma *fixture* para o
`forms,yml` no lugar.

    [php]
    lass sfFormYamlEnhancer
    {
      // ...

      public function enhance(sfForm $form)
      {
        $this->loadWorker();
        $this->doEnhance($form->getFormFieldSchema(), $form);
      }

      public function loadWorker()
      {
        require_once $this->configCache->checkConfig('config/forms.yml');
      }

      // ...
    }

Podemos, então, sobrecarregar o método `->loadWorker()` em nosso script de teste para chamar
o manipulador de configuração customizado diretamente. A classe `CommentForm` também deve ser
transferida para o script de teste e o arquivo `forms.yml` transferido para o diretório
`test/fixtures` do plugin.

    [php]
    include dirname(__FILE__).'/../../../../../test/bootstrap/unit.php';

    $t = new lime_test(6);

    class sfFormYamlEnhancerTest extends sfFormYamlEnhancer
    {
      public function loadWorker()
      {
        if (!class_exists('sfFormYamlEnhancementsWorker', false))
        {
          $configHandler = new sfFormYamlEnhancementsConfigHander();
          $code = $configHandler->execute(array(dirname(__FILE__).'/../../fixtures/forms.yml'));

          $file = tempnam(sys_get_temp_dir(), 'sfFormYamlEnhancementsWorker');
          file_put_contents($file, $code);

          require $file;
        }
      }
    }

    class CommentForm extends BaseForm
    {
      public function configure()
      {
        $this->setWidget('body', new sfWidgetFormTextarea());
        $this->setValidator('body', new sfValidatorString(array('min_length' => 12)));
      }
    }

    $configuration = $configuration->getApplicationConfiguration(
      'frontend', 'test', true, null, $configuration->getEventDispatcher());

    $enhancer = new sfFormYamlEnhancerTest($configuration->getConfigCache());

    // ...

Finalmente, o empacotamento do plugin é fácil com o `sfTaskExtraPlugin` instalado. Apenas
execute a tarefa `plugin:package` e um pacote será criado após alguns
*prompts* interativos.

    $ php symfony plugin:package sfFormYamlEnhancementsPlugin

>**NOTE**
>O código deste artigo foi publicado como um plugin e está disponível para
>download no site de plugins do symfony:
>
> http://symfony-project.org/plugins/sfFormYamlEnhancementsPlugin
>
>Este plugin inclui o que tratamos aqui e muito mais, incluindo suporte
>para arquivos `widgets.yml` e `validators.yml`, bem como a integração com a
>tarefa `i18n:extract` para fácil internacionalização dos seus formulários.

Reflexões finais
--------------

Como você pode ver pelos *benchmarks* feitos aqui, o cache de configuração do symfony torna
possível utilizar a simplicidade dos arquivos de configuração YAML com praticamente
nenhum impacto no desempenho.