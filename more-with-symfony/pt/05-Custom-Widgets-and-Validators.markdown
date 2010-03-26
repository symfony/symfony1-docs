Widgets e Validadores Personalizados
====================================

*por Thomas Rabaix*

Este capítulo explica como criar *widgets* e validadores personalizados para uso
no framework de formulário. Ele irá explicar os detalhes internos de `sfWidgetForm` e
`sfValidator`, bem como a forma de construir tanto um *widget* simples quanto um complexo.

Por Dentro do Widget e Validator
------------------------------

### Por Dentro do `SfWidgetForm`

Um objeto da classe ~`sfWidgetForm`~ representa a implementação visual de como
dados relacionados serão editados. Um valor "string", por exemplo, pode ser editado
com uma caixa de texto simples ou um editor WYSIWYG avançado. Para ser totalmente configurável,
a classe `sfWidgetForm` possui duas propriedades importantes: `options` (opções) e `attributes` (atributos).

* `options`: utilizado para configurar o widget (por exemplo, a consulta de dados a ser
   utilizada para a criação de uma lista a ser utilizada em uma caixa de seleção)

* `attributes`: atributos HTML adicionados ao elemento após o processamento

Além disso, a classe `sfWidgetForm` implementa dois métodos importantes:

* `configure()`: define quais opções são *opcionais* ou *obrigatórias*.
   Embora não seja uma boa prática para substituir o construtor, o método `configure()`
   pode ser facilmente substituído.

* `render()`: saídas de HTML para o widget. O método tem um primeiro argumento obrigatório,
   o nome do elemento HTML, e um segundo argumento opcional,
   o valor.

>**NOTE**
>Um objeto `sfWidgetForm` não sabe nada sobre o seu nome ou o seu valor.
>O componente é responsável apenas pela prestação do widget. O nome e
>o valor são geridos por um objeto `sfFormFieldSchema`, que é o link
>entre os dados e os widgets.

### Por Dentro do sfValidatorBase

A classe ~`sfValidatorBase`~ é a classe base de cada validador. O
~`sfValidatorBase::clean()`~ é o método mais importante desta classe
pois ele verifica se o valor é válido, dependendo das opções fornecidas.

Internamente, o método `clean()` realiza várias ações diferentes:

* Retira os espaços no início e no final do valor de entrada para valores string (se especificado através da opção `trim`)
* Verifica se o valor é vazio
* Chama o método `doClean()` do validador.

O método `doClean()` é o método que implementa a lógica principal de validação.
Não é uma boa prática sobrescrever o método `clean()`. Em vez disso,
customize a lógica do método `doClean()`.

O validador também pode ser usado como um componente independente para verificar a integridade de entrada.
Por exemplo, a validador sfValidatorEmail irá verificar se o e-mail é válido:

    [php]
    $v = new sfValidatorEmail();

    try
    {
      $v->clean ($request->getParameter("email"));
    }
    catch (sfValidatorError $e)
    {
      $this->forward404();
    }

>**NOTE**
>Quando um formulário está vinculado aos valores de solicitação, o objeto `sfForm` mantém
>referências aos valores originais (*dirty*, "sujos") e os valores validados (*clean*, "limpos").
>Os valores originais são usados quando o formulário é redesenhado, enquanto 
>os valores *cleaned* são utilizados pela aplicação (por exemplo, para salvar o objeto).

### O atributo `options`

Tanto o objeto `sfWidgetForm` quanto o `sfValidatorBase` têm uma variedade de opções:
algumas opcionais, outras obrigatórias. Estas opções são definidas
dentro do método `configure()` de cada classe através de:

* `AddOption($name, $value)`: define uma opção com um nome e um valor padrão
* `AddRequiredOption($name)`: define uma opção obrigatória

Estes dois métodos são muito convenientes pois garantem que os valores de dependência
são passados corretamente para o validador ou o widget.

Construindo um Widget e Validator Simples
--------------------------------------

Esta seção irá explicar como criar um widget simples. Este elemento particular
será chamado de "Trilean" widget. O widget exibirá uma caixa de seleção com três opções:
`Não`, `Sim` e `Nulo`.

    [php]
    class sfWidgetFormTrilean extends sfWidgetForm
    {
      public function configure ($options = array(), $attributes = array())
      {

        $this-addOption>('choices', array(
          0 => 'Não',
          1 => 'Sim',
          'null' => 'Nulo'
        ));
      }

      public function render($name, $value = null, $atributos = array(), $errors = array ())
      {
        $valor = $ valor === null? 'null': $valor;

        $options = array();
        foreach($this->getOption('choices') as $key => $opção)
        {
          $attributes = array ('value' = self::escapeOnce($key));
          if($ key == $value)
          {
            $attributes ['selected'] = 'selected';
          }

          $options [] = $this->renderContentTag(
            'option',
            self::escapeOnce($option),
            $attributes
          );
        }

        return $this->renderContentTag(
          'select',
          "\n". implode ("\n", $options). "\n",
          array_merge(array('name' => $name), $attributes
        ));
      }
    }

O método `configure()` define a lista de valores da opção através da opção `choices`.
Este array pode ser redefinido (ou seja, para alterar o rótulo associado a cada valor).
Não há limite para o número de opções que um widget pode definir. 
A classe base widget, no entanto, declara algumas opções padrões, que funcionam *de-facto*
como opções reservadas:

* `id_format`: o formato de id, o padrão é '%s'

* `is_hidden`: valor boolean para definir se o elemento é um campo oculto (usado
   por `sfForm::renderHiddenFields()` para processar todos os campos ocultos de uma vez)

* `needs_multipart`; valor boolean para definir se tag form deverá incluir
   a opção de multipart (por exemplo, para o upload de arquivos)

* `default`: o valor padrão que deve ser usado para processar o widget
   se nenhum valor é fornecido

* `label`: o rótulo padrão widget

O método `render()` gera o código HTML correspondente para uma caixa de seleção. O
método chama a função embutida `renderContentTag()` para ajudar a processar tags HTML.

O widget está agora concluído. Vamos criar o validador correspondente:

    [php]
    class sfValidatorTrilean extends sfValidatorBase
    {
      class sfValidatorTrilean extends sfValidatorBase
      {
        $ this-addOption> ( 'matriz true_values', ( 'true', 't', 'yes', 'y', 'on', '1 '));
        $this->addOption('false_values', array('false', 'f', 'no', 'n', 'off', '0'));
        $this->addOption('null_values', array('null', null));
      }

      protected function doClean($value)
      {
        if (in_array($value, $this->getOption('true_values')))
        {
          return true;
        }

        if (in_array($value, $this->getOption('true_values')))
        {
          return false;
        }

        if (in_array($value, $this->getOption('null_values')))
        {
          return null;
        }

        throw new sfValidatorError($this, 'invalid', array('value' => $value));
      }

      public function isEmpty($value)
      {
        return false;
      }
    }

O validator `sfValidatorTrilean` define três opções no método `configure()`
Cada opção é um conjunto de valores válidos. Como estas são definidas como opções,
o desenvolvedor pode personalizar os valores, dependendo da especificação.

O método `doClean()` verifica se o valor corresponde a um conjunto de valores válidos e 
retorna o valor limpo. Se nenhum valor é correspondido, o método irá gerar um
`sfValidatorError` qual é o erro padrão de validação no quadro do formulário.

O último método, `isEmpty`()`, é sobrescrito pois o comportamento padrão desse
método é retornar `true` se `null` é fornecido. Como o widget atual permite
`null` como um valor válido, o método deve voltar sempre `falso`.

**NOTE**
>Se `isEmpty()` retorna true, o método `doClean()` método nunca será chamado.

Embora este widget seja bastante simples, ele apresenta algumas características de base importantes
que serão necessárias mais adiante. A próxima seção irá criar um widget mais
complexo com vários campos e interação JavaScript.

O Widget Google Address Map
-----------------------------

Nesta seção, vamos construir um widget complexo. Novos métodos serão
introduzidos e o widget terá alguma interação JavaScript também.
O widget será chamado de "GMAW": "*Google Map Address Widget*".

O que queremos alcançar? O widget deve fornecer uma maneira fácil para o
usuário final para adicionar um endereço. Ao utilizar um campo de texto e com os 
serviços de mapas do Google podemos alcançar este objetivo.

!["Google Map Address Widget" mashup](http://www.symfony-project.org/images/more-with-symfony/widgets-figure-01.png ""Google Map Address Widget" mashup")

Caso de Uso 1:

* O usuário digita um endereço.
* O usuário clica no botão "pesquisa".
* Os campos ocultos de latitude e a longitude são atualizados e um novo marcador
   é criado no mapa. O marcador está posicionado no local do
   endereço. Se o serviço do Google Geocoding não pode encontrar o endereço uma mensagem de erro 
   irá aparecer.

Caso de Uso 2:

* O usuário clica sobre o mapa.
* Os campos ocultos de latitude e a longitude são atualizados.
* A pesquisa reversa é usada para localizar o endereço.

*Os seguintes campos precisam ser enviados e tratados pelo formulário:*

* `latitude`: float, entre 90 e -90
* `longitude`: float, entre 180 e -180
* `endereço`: string, apenas texto simples

As especificações funcionais do Widget foram definidas, agora vamos
definir os instrumentos técnicos e os seus escopos:

* Google Map e os serviços de Georreferenciamento: mostra o mapa e recupera as informações de endereço
* JQuery: adiciona interações JavaScript entre o form e o campo
* sfForm: desenha o widget e valida as entradas

### `sfWidgetFormGMapAddress` Widget

Como um widget é a representação visual dos dados, o método `configure()`
do widget deve ter várias opções para ajustar o mapa do Google ou modificar
os estilos de cada elemento. Uma das opções mais importantes é a
opção `template.html`, que define como todos os elementos estão ordenados.
Quando a construção de um widget que é muito importante pensar sobre a reutilização e
extensibilidade.

Outra coisa importante é a definição de assets externos. Uma classe `sfWidgetForm` 
pode implementar dois métodos específicos:

* `getJavascripts()` deve retornar um array de arquivos JavaScript;

* `getStylesheets()` deve retornar um array de folhas de estilo
   (onde a chave é o caminho e o valor o nome da mídia).

O Widget atual exige apenas um pouco de JavaScript para funcionar então nenhuma folha de estilo é necessária.
Neste caso, porém, o widget não vai lidar com a inicialização do
JavaScript do Google, embora o widget utilize da geocodificação Google
e serviços de mapa. Em vez disso, será responsabilidade do desenvolvedor
incluí-lo na página. A razão por trás disso é que os serviços do Google
podem ser utilizados por outros elementos na página, e não apenas pelo widget.

Vamos pular para o código:

      [php]
      class sfWidgetFormGMapAddress extends sfWidgetForm
      {
        public function configure($options = array(), $attributes = array())
        (
          $this->addOption('address.options', array('style' => 'width:400px'));

          $this->setOption('default', array(
            'address' => '',
            'longitude' => '2.294359',
            'latitude' => '48.858205'
          ));

          $this->addOption('div.class', 'sf-gmap-widget');
          $this->addOption('map.height', '300px');
          $this->addOption('map.width', '500px');
          $this->addOption('map.style', "");
          $this->addOption('lookup.name', "Lookup");

          $this->addOption('template.html', '
            <div id="{div.id}" class="{div.class}">
              {input.search} <input type="submit" value="{input.lookup.name}" id="{input.lookup.id}" /> <br />
              {input.longitude}
              {input.latitude}
              <div id="{map.id}" style="width:{map.width};height:{map.height};{map.style}"></div>
            </div>
          ');

           $this->addOption('template.javascript', '
            <script type="text/javascript">
              jQuery(window).bind("load", function() {
                new sfGmapWidgetWidget({
                  longitude: "{input.longitude.id}",
                  latitude: "{input.latitude.id}",
                  address: "{input.address.id}",
                  lookup: "{input.lookup.id}",
                  map: "{map.id}"
                });
              })
            </script>
          ');
        }

        public function getJavascripts()
        {
          return array(
            '/sfFormExtraPlugin/js/sf_widget_gmap_address.js'
          );
        }

        public function render($name, $value = null, $attributes = array(), $errors = array())
        {
          // define as variáveis do template principal
          $template_vars = array(
            '{div.id}' => $this->generateId($name),
            '{div.class}' => $this->getOption('div.class'),
            '{map.id}' => $this->generateId($name.'[map]'),
            '{map.style}' => $this->getOption('map.style'),
            '{map.height}' => $this->getOption('map.height'),
            '{map.width}' => $this->getOption('map.width'),
            '{input.lookup.id}' => $this->generateId($name.'[lookup]'),
            '{input.lookup.name}' => $this->getOption('lookup.name'),
            '{input.address.id}' => $this->generateId($name.'[address]'),
            '{input.latitude.id}' => $this->generateId($name.'[latitude]'),
            '{input.longitude.id}' => $this->generateId($name.'[longitude]'),
          );

          // evita que qualquer mensagem de erro para o formato $value
          $value = !is_array($value) ? array() : $value;
          $value['address'] = isset($value['address']) ? $value['address'] : ''; $ value [ 'address']:'';
          $value['longitude'] = isset($value['longitude']) ? $value['longitude'] : ''; $ value [ 'longitude']:'';
          $value['latitude'] = isset($value['latitude']) ? $value['latitude'] : ''; $ value [ 'latitude']:'';

          // Define o widget address
          $address = new sfWidgetFormInputText(array(), $this->getOption('address.options'));
          $template_vars['{input.search}'] = $address->render($name.'[address]', $value['address']);

          / / Define os campos longitude e latitude
          $hidden = new sfWidgetFormInputHidden;
          $template_vars['{input.longitude}'] = $hidden->render($name.'[longitude]', $value['longitude']);
          $template_vars['{input.latitude}'] = $hidden->render($name.'[latitude]', $value['latitude']);

          // Mescla modelos e variáveis
          return strtr(
            $this->getOption('template.html').$this->getOption('template.javascript'),
            $template_vars
          );
        }
      }

O Widget usa o método `generateId()` para gerar o `id` de cada elemento.
A variável `$name` é definida pelo `sfFormFieldSchema`, de modo que a variável `$name`
é composta do nome do formulário, qualquer nome de esquema aninhados de widgets e
o nome do widget, tal como definido no `configure()` do form.

>**NOTE**
>Por exemplo, se o nome do formulário é `user`, o nome do esquema aninhado é `location`
>e o nome do widget é o `endereço`, o `name` final de usuário será `user[location][address]` 
>e o `id` será `user_location_address`. Em outras palavras,
>`$this->generateId($nome.'[latitude]')` irá gerar um `id` válido e único
>para o campo `latitude`.

Os atributos `id` diferentes para cada elemento são muito importantes, pois são passados
para o bloco de JavaScript (através da variável `template.js`), pelo qual o JavaScript pode
tratar adequadamente os diferentes elementos.

O método `render()` também instancia dois widgets internos: um widget `sfWidgetFormInputText`, 
que é usado para processar o campo `address` e um widget `sfWidgetFormInputHidden`, 
que é usado para processar os campos ocultos.

O widget pode ser rapidamente testado com este pequeno trecho de código:

    [php]
    $widget = new sfWidgetFormGMapAddress();
    echo $widget->render('user[location][address]', array(
      'address' => '151 Rue montmartre, 75002 Paris',
      'longitude' => '2.294359',
      'latitude' => '48.858205'
    ));

O resultado de saída é:

    [html]
    <div id="user_location_address" class="sf-gmap-widget">
      <input style="width:400px" type="text" name="user[location][address][address]" value="151 Rue montmartre, 75002 Paris" id="user_location_address_address" />
      <input type="submit" value="Lookup" id="user_location_address_lookup" /> <br />
      <input type="hidden" name="user[location][address][longitude]" value="2.294359" id="user_location_address_longitude" />
      <input type="hidden" name="user[location][address][latitude]" value="48.858205" id="user_location_address_latitude" />
      <div id="user_location_address_map" style="width:500px;height:300px;"></div>
    </div>

    <script type="text/javascript">
      jQuery(window).bind("load", function() {
        new sfGmapWidgetWidget({
          longitude: "user_location_address_longitude",
          latitude: "user_location_address_latitude",
          address: "user_location_address_address",
          lookup: "user_location_address_lookup",
          map: "user_location_address_map"
        });
      })
    </script>

A parte JavaScript do widget leva a diferentes atributos `id` e
liga observadores jQuery a eles, para que o JavaScript adequado seja acionado 
quando as ações são executadas. O JavaScript atualiza os campos ocultos 
com a latitude e longitude fornecidas pelo serviço google geocoding.

O objeto JavaScript tem alguns métodos interessantes:

* `init()`: o método em que todas as variáveis são inicializadas e eventos
   para diferentes inputs

* `lookupCallback()`: um método *estático* usado pelo método de geocoder
   pesquisar o endereço fornecido pelo usuário

* `reverseLookupCallback ()`: é outro método *estático* usado pelo geocoder
   para converter os dados de longitude e latitude em um endereço válido.

O código JavaScript final pode ser visto no Apêndice A.

Por favor, consulte a documentação do Google map para obter mais detalhes sobre a funcionalidade
do Google maps [API](http://code.google.com/apis/maps/).

### Validador `sfValidatorGMapAddress`

A classe `sfValidatorGMapAddress` estende `sfValidatorBase` que já
executa uma validação: especificamente, se o campo está definido como obrigatório então
o valor não pode ser `null`. Assim, `sfValidatorGMapAddress` só precisa validar
os diferentes valores: `latitude`, `longitude` e `address`. A variável `$value` 
deve ser um array, mas como não se deve confiar na entrada do usuário, o validador 
verifica a presença de todas as chaves para que os validadores internos passam
valores válidos.

    [php]
    class sfValidatorGMapAddress extends sfValidatorBase
    {
      protected function doClean($value)
      {
        if (! if(!is_array($value))
        {
          throw new sfValidatorError($this, 'invalid');
        }

        try
        {
          $latitude = new sfValidatorNumber(array( 'min' => -90, 'max' => 90, 'required' => true ));
          $value['latitude'] = $latitude->clean(isset($value['latitude']) ? $value['latitude'] : null); $ value [ 'latitude']: null);

          $longitude = new sfValidatorNumber(array( 'min' => -180, 'max' => 180, 'required' => true ));
          $value['longitude'] = $longitude->clean(isset($value['longitude']) ? $value['longitude'] : null); $ value [ 'longitude']: null);

          $address = new sfValidatorString(array( 'min_length' => 10, 'max_length' => 255, 'required' => true ));
          $value['address'] = $address->clean(isset($value['address']) ? $value['address'] : null); $ value [ 'address']: null);
        }
        catch(sfValidatorError $e)
        {
          throw new sfValidatorError($this, 'invalid');
        }

        return $value;
      }
    }

>**NOTE**
>Um validador sempre lança uma `excepção` `sfValidatorError` quando um valor não é
>válido. Por isso, a validação é cercada por um bloco `try/catch`.
>Neste validador, a validação re-lança uma nova excepção `invalid`, que
>equivale a um erro de validação `invalid` no validador 
>`sfValidatorGMapAddress`.

### Testando

Por que o teste é importante? O validador é a ponte entre a entrada do usuário
e a aplicação. Se o validador é falho, a aplicação esta vulnerável.
Felizmente, o symfony vem com o `lime` que é uma biblioteca de testes 
muito fácil de usar.

Como podemos testar o validador? Como afirmado anteriormente, um validador lança uma exceção
em um erro de validação. O teste pode enviar valores válidos e inválidos para o validador
e verificar que a exceção é lançada nas circunstâncias corretas.

    [php]
    $t = new lime_test(7, new lime_output_color());

    $tests = array(
      array(false, '', 'empty value'),
      array(false, 'string value', 'string value'),
      array(false, array(), 'empty array'),
      array(false, array('address' => 'my awesome address'), 'incomplete address'),
      array(false, array('address' => 'my awesome address', 'latitude' => 'String', 'longitude' => 23), 'invalid values'),
      array(false, array('address' => 'my awesome address', 'latitude' => 200, 'longitude' => 23), 'invalid values'),
      array(true, array('address' => 'my awesome address', 'latitude' => '2.294359', 'longitude' => '48.858205'), 'valid value')
    );

    $v = new sfValidatorGMapAddress;

    $t->diag("Testing sfValidatorGMapAddress");

    foreach($tests as $test)
    {
      list($validity, $value, $message) = $test;

      try
      {
        $v->clean($value);
        $catched = false;
      }
      catch(sfValidatorError $e)
      {
        $catched = true;
      }

      $t->ok($validity != $catched, '::clean() '.$message);
    }

Quando o método `sfForm::bind()` é chamado, o formulário executa o método `clean()` 
de cada validador. Este teste reproduz este comportamento instanciando
o validador `sfValidatorGMapAddress` diretamente e testa diferentes valores.

Considerações Finais
--------------

O erro mais comum durante a criação de um *widget* é focar excessivamente em
como as informações serão armazenadas no banco de dados. O framework de formulário é
simplesmente um contêiner de dados e um framework de validação. Portanto, um widget deve
gerenciar somente a sua informação relacionada. Se os dados forem válidos, em seguida, os diferentes
valores limpos poderão então ser utilizados no modelo ou no controlador.
