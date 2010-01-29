Formulários Avançados
=====================

* por Ryan Weaver, Fabien potencier *

O framework de formulários do Symfony prepara o desenvolvedor com as ferramentas 
necessárias para facilmente processar e validar os dados do formulário de uma 
formulário orientada a objeto. Graças as classes ~`sfFormDoctrine`~ and ~`sfFormPropel`~ 
o ferecidas por cada ORM, o framework de formulários pode facilmente mostrar e 
salvar formulários que estão intimamente relacionados a camada de dados.

Situações do mundo real, porém, muitas vezes exigem ao desenvolvedor personalizar
e estender os formulários. Neste capítulo iremos apresentar e resolver várias comuns 
e desafiadores problemas de formulários. Também vamos dissecar o objeto ~`sfForm`~ e
remover alguns de seus mistérios.

Mini-Projeto: Produtos & Fotos
-------------------------------

O primeiro problema gira em torno da edição de um produto individual e um
número ilimitado de fotos para esse produto. O usuário deve ser capaz de editar
tanto o produto e fotos do produto no mesmo formulário. Nós também precisamos
permiter ao usuário fazer upload de até duas fotos novas do produto de cada vez.
Aqui está um possível esquema:

    [yml]
    Product:
      columns:
        name:           { type: string(255), notnull: true }
        price:          { type: decimal, notnull: true }

                ProductPhoto:
      columns:
        product_id:     { type: integer }
        filename:       { type: string(255) }
        caption:        { type: string(255), notnull: true }
      relations:
        Product:
          alias:        Product
          foreignType:  many
          foreignAlias: Photos
          onDelete:     cascade

Quando concluído, o formulário será parecido com isto:

![Formulário de Produto e Foto](http://www.symfony-project.org/images/more-with-symfony/advanced_forms_01.png "Form de Produto com form ProductPhoto inserido")

Saiba mais fazendo o Exemplos
--------------------------------

A melhor maneira de aprender técnicas avançadas é acompanhar e testar o exemplos
passo a passo. Graças ao recurso `--installer` do [symfony](#chapter_03), nós 
fornecemos uma maneira simples para você criar um projeto funcional com um banco de 
dados SQLite, o esquema do banco Doctrine, algumas fixtures, uma aplicação `frontend`
e um módulo `produto` para trabalhar.
Baixe o instalador
[script] (http://www.symfony-project.org/images/more-with-symfony/advanced_form_installer.php.src)
e executar o seguinte comando para criar o projeto symfony:

    $ php symfony generate:project advanced_form --installer=/path/to/advanced_form_installer.php

Este comando cria um projeto totalmente funcional com o esquema de banco de dados 
que temos haviamos introduzido na seção anterior.

>**NOTA**
>Neste capítulo, os caminhos de arquivo são para um projeto symfony rodando com o
>Doctrine, como gerado na tarefa anterior.

Configuração básica de Formulário
--------------------------------

Como os requisitos envolvem mudanças de dois modelos diferentes ( `Product`
e `ProductPhoto`), a solução terá de incorporar dois diferentes formulários
symfony ( `ProductForm` e `ProductPhotoForm`). Felizmente, o framework de 
formulário pode facilmente combinar múltiplas forms em uma via ~`sfForm::embedForm()`~.
Primeiramente, a configuração do `ProductPhotoForm` de formulário independente.  
Neste exemplo, vamos usar o campo `filename` como um campo de upload de arquivo:

    [php]
    // lib/form/doctrine/ProductPhotoForm.class.php
    public function configure()
    {
      $this->useFields(array('filename', 'caption'));

      $this->setWidget('filename', new sfWidgetFormInputFile());
      $this->setValidator('filename', new sfValidatorFile(array(
        'mime_types' => 'web_images',
        'path' => sfConfig::get('sf_upload_dir').'/products',
      )));
    }

Para este formulário, tanto os campos `caption` quanto `filename` são automaticamente
requeridos, mas por razões diferentes. O campo `caption` é necessária porque
a coluna relacionada no esquema do banco de dados foi definida com um `não nulo`.
O campo `filename` é requerido por padrão porque um objeto validador padrão pede.

>**NOTA**
>~`sfForm::useFields()`~ é uma nova função no symfony 1.3 que permite que o
>desenvolvedor especifique exatamente quais os campos do formulário deve usar e em que
>ordem que deve ser exibido. Todos os outros campos não ocultos são removidos
>do formulário.

Até agora nós não fizemos nada mais do que a configuração de formulário simples. 
A seguir, vamos combinar os formulários em um.

Embutindo Formulários
---------------------

Ao utilizar ~`sfForm::embedForm()`~, o `ProductForm` e `ProductPhotoForms` 
podem ser combinados com um esforço muito pequeno. O trabalho é sempre feito
na *formulário* principal, que neste caso é `ProductForm`. O requisito é a 
capacidade de enviar até duas fotos do produto de uma vez.
Para realizar isso, inserir dois objetos `ProductPhotoForm` no `ProductForm`:

    [php]
    // lib/form/doctrine/ProductForm.class.php
    public function configure()
    {
      $subForm = new sfForm();
      for ($i = 0; $i < 2; $i++)
      {
        $productPhoto = new ProductPhoto();
        $productPhoto->Product = $this->getObject();

        $form = new ProductPhotoForm($productPhoto);

        $subForm->embedForm($i, $form);
      }
      $this->embedForm('newPhotos', $subForm);
    }

Se você apontar seu navegador para o módulo `product`, agora você pode fazer de
upload de dois `ProductPhoto`, bem como modificar o próprio objeto `Product`.

O Symfony salva automaticamente os novos objetos `ProductPhoto` relacionaeles
ao objeto correspondente `Product`. Mesmo o upload do arquivo, definido em
`ProductPhotoForm`, executa normalmente.

Verifique se os registros são salvos corretamente no banco de dados:

    $ php symfony doctrine:dql --table "FROM Product"
    $ php symfony doctrine:dql --table "FROM ProductPhoto"

Na tabela `ProductPhoto`, você vai notar os nomes das fotos.
Tudo está funcionando como esperado se você pode encontrar arquivos com os mesmos nomes
do banco de dados no diretório `web/upload/products/`.

> ** NOTA **
> Porque o nome do arquivo e campos da legenda são necessários `ProductPhotoForm»,
> validação do formulário principal será sempre a não ser que o usuário está carregando
> duas novas fotos. Continue lendo para saber como corrigir este problema.

Re-fatoração(Refactoring)
--------------------------

Mesmo o formulário anterior funcionando como esperado, seria interessante refatorar
o código para facilitar os testes e para permitir que o código seja facilmente reutilizado.

Primeiro, vamos criar um novo formulário que representa uma coleção de
`ProductPhotoForm`s, com base no código já foi escrito:

    [php]
    // lib/form/doctrine/ProductPhotoCollectionForm.class.php
    class ProductPhotoCollectionForm extends sfForm
    {
      public function configure()
      {
        if (!$product = $this->getOption('product'))
        {
          throw new InvalidArgumentException('You must provide a product object.');
        }

        for ($i = 0; $i < $this->getOption('size', 2); $i++)
        {
          $productPhoto = new ProductPhoto();
          $productPhoto->Product = $product;

          $form = new ProductPhotoForm($productPhoto);

          $this->embedForm($i, $form);
        }
      }
    }

Este formulário tem duas opções:

 * `product`: O produto qual se cria uma coleção de
   `ProductPhotoForm`s;

 * `size`: O número de `ProductPhotoForm`s a ser criado (duas por padrão).

Agora você pode alterar o método de configuração do `ProductForm` como a seguir:

    [php]
    // lib/form/doctrine/ProductForm.class.php
    public function configure()
    {
      $form = new ProductPhotoCollectionForm(null, array(
        'product' => $this->getObject(),
        'size'    => 2,
      ));

      $this->embedForm('newPhotos', $form);
    }

Dissecando o objeto sfForm
----------------------------

No sentido mais básico, um formulário da web é uma coleção de campos que são processados
e enviados de volta para o servidor. No mesmo caminho, o objeto ~`sfForm`~  é
essencialmente um array de *campos* de formulário. Enquanto ~`sfForm`~ gerencia o processo,
os campos individuais são responsáveis por definir o modo como cada um será processado
e validado.

No symfony, cada campo de formulário * * é definida por dois objetos diferentes:

  * Um *widget* que controla a marcação XHTML do campo;

  * Um *validator* que limpa e valida os dados enviados

>**Dica**
>No symfony, um *widget* é definida como qualquer objeto cujo único trabalho é saída
>em marcação XHTML. Embora mais comumente usados com formulários, um objeto Widget
>poderia ser criado para mostrar qualquer marcação.

### Um formulário é um array

Lembre-se que o objeto ~`sfForm`~ é "essencialmente um array de *campos* de formulário."
Para ser mais preciso, `sfForm` guarda tanto um array de elementos como um array
de validadores para todos os campos do formulário. Estes dois arrays, chamados
`widgetSchema` e `validatorSchema` são propriedades da classe `sfForm`.
A fim de adicionar um campo a um formulário, basta adicionar o campo do widget para o
array `widgetSchema` e o validador do campo para a array `validatorSchema`.
Por exemplo, o código a seguir adiciona um campo `email` para um formulário:

    [php]
    public function configure()
    {
      $this->widgetSchema['email'] = new sfWidgetFormInputText();
      $this->validatorSchema['email'] = new sfValidatorEmail();
    }

>**NOTA**
>Os arrays `widgetSchema` e `validatorSchema` são na verdade classes especiais
>chamadas ~`sfWidgetFormSchema`~ e ~`sfValidatorSchema`~ que implementam a
>interface `ArrayAccess`.

### Dissecando o `ProductForm`

Como em ultima estância a classe `ProductForm` estende a `sfForm`, ela também abriga todos os
seus widgets e validadores nos arrays `widgetSchema` e `validatorSchema`.
Vamos analisar como cada array é organizado no objeto final `ProductForm`.

    [php]
    widgetSchema    => array
    (
      [id]          => sfWidgetFormInputHidden,
      [name]        => sfWidgetFormInputText,
      [price]       => sfWidgetFormInputText,
      [newPhotos]   => array(
        [0]           => array(
          [id]          => sfWidgetFormInputHidden,
          [filename]    => sfWidgetFormInputFile,
          [caption]     => sfWidgetFormInputText,
        ),
        [1]           => array(
          [id]          => sfWidgetFormInputHidden,
          [filename]    => sfWidgetFormInputFile,
          [caption]     => sfWidgetFormInputText,
        ),
      ),
    )

    validatorSchema => array
    (
      [id]          => sfValidatorDoctrineChoice,
      [name]        => sfValidatorString,
      [price]       => sfValidatorNumber,
      [newPhotos]   => array(
        [0]           => array(
          [id]          => sfValidatorDoctrineChoice,
          [filename]    => sfValidatorFile,
          [caption]     => sfValidatorString,
        ),
        [1]           => array(
          [id]          => sfValidatorDoctrineChoice,
          [filename]    => sfValidatorFile,
          [caption]     => sfValidatorString,
        ),
      ),
    )

>**Dica**
>Assim como `widgetSchema` e `validatorSchema` são na verdade  objetos que se comportam
>como arrays, as arrays acima definidos pelas chaves `newPhotos`, `0` e `1`
>Também são objetos `sfWidgetSchema` e `sfValidatorSchema`.

Como esperado, os campos básicos ( `id`, `name` e `price`) estão representados no primeiro
nível de cada array. Em um formulário que não incorpora outros formulário, os arrays `widgetSchema` 
e `validatorSchema` têm apenas um nível, que representa os campos básicos no formulário.
Os widgets e validadores de qualquer formulário incorporados são representados como arrays filhos
em `widgetSchema` e `validatorSchema` como visto acima. 
O métodoque gere este processo é explicado a seguir.

### Por trás de ~`sfForm::embedForm()`~

Tenha em mente que um formulário é composto por uma variedade de widgets e um conjunto de
validadores. Incorporação de um formulário em outro, essencialmente, significa que o 
arrays de widget e validador de um formulário são adicionados aos array de widgets
e validadores do formulário principal. Isto é inteiramente realizado via
`sfForm::embedForm()`. O resultado é sempre uma adição multi-dimensional em `widgetSchema` 
e `validatorSchema` como visto acima.

A seguir, vamos discutir a configuração do `ProductPhotoCollectionForm`, que liga
objetos `ProductPhotoForm` em si. Essa formulário meio age como um "wrapper" de formulário
e contribui com a organização de forma global. Vamos começar com o seguinte código
de `ProductPhotoCollectionForm::configure()`:

    [php]
    $form = new ProductPhotoForm($productPhoto);
    $this->embedForm($i, $form);

O próprio formulário `ProductPhotoCollectionForm` começa como um novo objeto `sfForm`.
Como tal, seus arrays `widgetSchema` e `validatorSchema` estão vazias.

    [php]
    widgetSchema    => array()
    validatorSchema => array()

Cada `ProductPhotoForm`, no entanto, já está preparada com três campos (`id`, `filename`,
e `caption`) e três itens correspondentes em seu `widgetSchema` e `validatorSchema`.


    [php]
    widgetSchema    => array
    (
      [id]            => sfWidgetFormInputHidden,
      [filename]      => sfWidgetFormInputFile,
      [caption]       => sfWidgetFormInputText,
    )

    validatorSchema => array
    (
      [id]            => sfValidatorDoctrineChoice,
      [filename]      => sfValidatorFile,
      [caption]       => sfValidatorString,
    )

O método ~`sfForm::embedForm()`~ simplesmente adiciona os arrays `widgetSchema` e `validatorSchema`
de cada `ProductPhotoForm` aos arrays `widgetSchema` e `validatorSchema`
do objeto `ProductPhotoCollectionForm` vazio.

Quando terminar, os arrays `widgetSchema` e `validatorSchema` do formulário
"wrapper" (`ProductPhotoCollectionForm`) serão arrays multi-nível que mantem os
widgets e validadores de ambos os`ProductPhotoForm`.

    [php]
    widgetSchema    => array
    (
      [0]             => array
      (
        [id]            => sfWidgetFormInputHidden,
        [filename]      => sfWidgetFormInputFile,
        [caption]       => sfWidgetFormInputText,
      ),
      [1]             => array
      (
        [id]            => sfWidgetFormInputHidden,
        [filename]      => sfWidgetFormInputFile,
        [caption]       => sfWidgetFormInputText,
      ),
    )

    validatorSchema => array
    (
      [0]             => array
      (
        [id]            => sfValidatorDoctrineChoice,
        [filename]      => sfValidatorFile,
        [caption]       => sfValidatorString,
      ),
      [1]             => array
      (
        [id]            => sfValidatorDoctrineChoice,
        [filename]      => sfValidatorFile,
        [caption]       => sfValidatorString,
      ),
    )

Na etapa final do nosso processo, a formulário "wrapper" resultante,
`ProductPhotoCollectionForm`, é encaixado(`embedded`) diretamente no `ProductForm`.
Isso ocorre dentro de `ProductForm::configure()`, que tira proveito de
todo o trabalho que foi feito dentro de `ProductPhotoCollectionForm`:

    [php]
    $form = new ProductPhotoCollectionForm(null, array(
      'product' => $this->getObject(),
      'size'    => 2,
    ));

    $this->embedForm('newPhotos', $form);

Isso nos dá a estrutura final dos arrays `widgetSchema` e `validatorSchema`
vista acima. Observe que o método `embedForm()` é muito parecido com o simples
ato de combinar os arrays `widgetSchema` e `validatorSchema` manualmente:

    [php]
    $this->widgetSchema['newPhotos'] = $form->getWidgetSchema();
    $this->validatorSchema['newPhotos'] = $form->getValidatorSchema();

Renderizando formulários incorporados na Visão
----------------------------------------------

O template atual `_form.php` do modelo do `produto` é parecido com o
seguinte:

    [php]
    // apps/frontend/module/product/templates/_form.php
    <!-- ... -->

    <tbody>
      <?php echo $form ?>
    </tbody>

    <!-- ... -->

A declaração `<?php echo $ form?>` é a maneira mais simples de mostrar um formulário,
mesmo os mais complexos. São de grande ajuda durante a prototipagem, mas logo
que você desejar alterar o layout, sera necessário substituí-lo com sua própria
lógica. Remova esta linha agora, ja que iremos substituí-la nesta seção.

A coisa mais importante para compreender quando renderizando formulários incorporadas no
ponto de vista é a organização do multi-nível do array `widgetSchema` como explicado
nas seções anteriores. Para este exemplo, vamos começar renderizando os campos básicos
`name` e `price` do ProductForm` na visão:

    [php]
    // apps/frontend/module/product/templates/_form.php
    <?php echo $form['name']->renderRow() ?>

    <?php echo $form['price']->renderRow() ?>

    <?php echo $form->renderHiddenFields() ?>

Como o próprio nome indica, o `renderHiddenFields()` processa todos os campos ocultos
do formulário.

>**NOTA**
>O código da actions não foi mostrado propositadamente aqui, porque não requer especial
>atenção. Dê uma olhada no arquivo `apps/frontend/modules/product/actions/actions.class.php`.
>É como qualquer CRUD normal e pode ser gerado automaticamente via
>a tarefa(task) `doctrine:generate-module`.

Como já aprendemos, a classe `sfForm` guarda os arrays `widgetSchema` e 
`validatorSchema` que definem os nossos campos. Além disso, a classe `sfForm`
implementa a interface `ArrayAccess`, nativa do PHP 5, o que significa que pode acessar diretamente
campos do formulário usando a sintaxe chave array vista acima.

Para a saída de campos, você pode simplesmente acessá-los diretamente e chamar o método
`renderRow()`. Mas que tipo de objeto é `$form['name']`? Enquanto você pôde esperar
que a resposta seja um `sfWidgetFormInputText` para o campo `name`,
A resposta é realmente algo um pouco diferente.

### Renderizando cada campo de formulário com ~`sfFormField`~

Ao utilizar os arrays `widgetSchema` e `validatorSchema` definidos em cada 
classe de formulário, `sfForm` gera automaticamente um terceiro array chamado
`sfFormFieldSchema`. Esta array contém um objeto especial para cada campo
que atua como uma classe auxiliar responsável pela saída do campo. 
O objeto, do tipo ~`sfFormField`~, é uma combinação de cada elemento de campo
e de validação e é criado automaticamente.

    [php]
    <?php echo $form['name']->renderRow() ?>

No trecho acima, `$form['name']` é um objeto `sfFormField`, que abriga
o método `renderRow()` junto com diversas outras funções de processamento útil.

### Métodos de renderização do sfFormField 

Cada objeto `sfFormField pode ser usado para facilmente tornar cada aspecto do campo
que representa (por exemplo, o próprio campo, o rótulo, mensagens de erro, etc.)
Alguns dos métodos úteis dentro sfFormField `` incluem o seguinte. Outro:
podem ser encontrados através do [symfony 1,3 API] http://www.symfony-project.org/api/1_3/sfFormField ().

 * `sfFormField->render()`: Processa o campo do formulário (por exemplo, `input`, `select`)
   com o valor correto usando o objeto widget do campo.

 * `sfFormField->renderError()`: Processa quaisquer erros de validação no campo
   usando o objeto validador do campo.

 * `sfFormField->renderRow()`: Tudo-englobado: renderiza o rótulo, o formulário
   o campo, o erro e a mensagem de ajuda dentro de um invólucro de marcação XHTML.

>**NOTA**
>Na realidade, cada função de renderização da classe `sfFormField` também utiliza informação
>a partir da propriedade `widgetSchema` do formulário( o objeto `sfWidgetFormSchema` que
>agrega todos os widgets para o formulário). Esta classe auxilia na geração
>dos atributos de cada campo `name` e `id`, controla o rótulo para cada
>campo, e define a marcação XHTML usada com `renderRow()`.

Uma coisa importante a notar é que o array `formFieldSchema` sempre
espelha a estrutura do arrays `widgetSchema` e `validatorSchema` 
do formulário. Por exemplo, o array `formFieldSchema` do `ProductForm` 
concluído teria a seguinte estrutura, que é a chave para renderizar cada
campo na visão:

    [php]
    formFieldSchema    => array
    (
      [id]          => sfFormField
      [name]        => sfFormField,
      [price]       => sfFormField,
      [newPhotos]   => array(
        [0]           => array(
          [id]          => sfFormField,
          [filename]    => sfFormField,
          [caption]     => sfFormField,
        ),
        [1]           => array(
          [id]          => sfFormField,
          [filename]    => sfFormField,
          [caption]     => sfFormField,
        ),
      ),
    )

### Renderizando novo ProductForm

Usando a tabela acima como o nosso mapa, podemos saída facilmente incorporadas `` ProductPhotoForm
campos na vista por localizar e tornar o bom `` sfFormField objetos:

    [php]
    // apps/frontend/module/product/templates/_form.php
    <?php foreach ($form['newPhotos'] as $photo): ?>
      <?php echo $photo['caption']->renderRow() ?>
      <?php echo $photo['filename']->renderRow() ?>
    <?php endforeach; ?>

O bloco acima loops duas vezes: uma para o `0` array campo formulário e
uma vez para o `1` array campo de formulário. Como visto no diagrama acima,
os objetos subjacentes de cada array são `` sfFormField os objetos, que podemos
saída como quaisquer outros campos.

Saving Objeto Formulários
-------------------

Na maioria das circunstâncias, uma formulário que se relacionam diretamente a um ou mais banco de dados
tabelas e provocar alterações de dados nas tabelas apresentadas com base no
valores. Symfony gera automaticamente um objeto de formulário para um modelo de esquema,
que se estende tanto sfFormDoctrine `` ou `` sfFormPropel dependendo da sua
ORM. Cada classe de formulário semelhante e, finalmente, permite que os valores apresentados
ser facilmente mantido no banco de dados.

> ** NOTA **
> ~ `` ~ sfFormObject é uma nova classe adicionada no symfony 1,3 para tratar de todos os
> tarefas comuns sfFormDoctrine do `` e `` sfFormPropel. Cada classe se estende
> `sfFormObject», que agora administra parte do formulário de poupança processo descrito abaixo.

# # # O Formulário de processo de gravação

No nosso exemplo, o symfony automaticamente salva tanto o «produto de informulárioção» e
`novo` ProductPhoto objetos sem qualquer esforço adicional por parte do desenvolvedor.
O método que dispara a magia, ~ `sfFormObject:: save ()`~, executa uma variedade
de métodos nos bastidores. Entender este processo é fundamental para a prorrogação
o processo em situações mais avançadas.

A formulário de poupança processo consiste em uma série de métodos internamente executado,
tudo o que acontece depois de chamar ~ `sfFormObject:: save ()`~. A maioria
do trabalho está envolto na ~ `sfFormObject:: UpdateObject ()` ~ método, que
é chamado recursivamente em todas as suas formulários incorporado.

! [Forma processo de gravação] (http://www.symfony-project.org/images/more-with-symfony/advanced_forms_06.png "formulário pormenorizada processo de gravação")

> ** NOTA **
> A maioria do processo de poupança ocorre dentro do ~ `sfFormObject:: DoSave ()` ~
> método, que é chamado pelo `sfFormObject:: save ()` e envolto em um banco de dados
> transação. Se você precisar modificar o processo de poupança própria, `sfFormObject:: DoSave ()`
> geralmente é o melhor lugar para fazê-lo.

Ignorando as Formas Embedded
-----------------------

O actual ProductForm `` implementação tem um grande déficit. Porque o
`filename` e `` legenda campos são obrigatórios em `ProductPhotoForm», validação
do formulário principal será sempre a não ser que o usuário enviar duas novas fotos.
Em outras palavras, o usuário não pode simplesmente mudar o preço do produto »,« sem
também estão sendo obrigados a carregar duas novas fotos.

! [Tipo de produto não validação foto] (http://www.symfony-project.org/images/more-with-symfony/advanced_forms_04.png tipo de produto "falha de validação nas fotos")

Vamos redefinir os requisitos para incluir o seguinte. Se o usuário deixa
todos os campos de um `` ProductPhotoForm em branco, que formulário deve ser ignorado
completamente. No entanto, se pelo menos um campo tem dados (ou seja, legenda `` ou `filename`),
o formulário deve validar e salvar normalmente. Para conseguir isso, vamos contratar
uma técnica avançada que envolvam a utilização de um posto de validação personalizado.

O primeiro passo, no entanto, é modificar o `ProductPhotoForm« formulário de tornar o
`legenda` e `filename` campos opcionais:

    [php]
    / / Lib / form / doutrina / ProductPhotoForm.class.php
    public function configure ()
    (
      $ this-setValidator> ( 'sfValidatorFile filename', novo (array (
        'mime_types' => 'web_images',
        'path' => sfConfig:: get ( 'sf_upload_dir').'/ produtos,
        'required' => false,
      )));

      $ this-> validatorSchema [ 'legenda'] - setOption> ( 'required', false);
    )

No código acima, temos que definir a opção `` necessários para quando «falso»
substituindo o validador padrão para o `campo` filename. Além disso,
temos definir explicitamente a opção `` necessário `legenda do campo» a «falsa».

Agora, vamos adicionar o validador após a ProductPhotoCollectionForm `:

    [php]
    / / Lib / form / doutrina / ProductPhotoCollectionForm.class.php
    public function configure ()
    (
      / / ...

      mergePostValidator $ this-> (ProductPhotoValidatorSchema novo ());
    )

Um validador post é um tipo especial de validação que valida em todas as
os valores apresentados (em oposição ao validar o valor de um campo único).
Um dos validadores post mais comum é o `que` sfValidatorSchemaCompare
verifica, por exemplo, que uma área é inferior a outro campo.

# # # Criando um validador personalizado

Felizmente, criando um validador personalizado é realmente muito fácil. Crie uma
novo arquivo, `ProductPhotoValidatorSchema.class.php» e colocá-lo no
`validador / lib /` (você precisa criar este diretório):

    [php]
    / Lib validador / ProductPhotoValidatorSchema.class.php
    classe ProductPhotoValidatorSchema estende sfValidatorSchema
    (
      protected function configure ($ options = array (), $ messages = array ())
      (
        $ this-> addMessage ( 'caption', 'A legenda é necessária. ");
        $ this-> addMessage ( 'filename', 'O nome é obrigatório. ");
      )

      protegidos Doclean função ($ valores)
      (
        errorSchema $ = sfValidatorErrorSchema novo ($ this);

        foreach ($ valores as $ chave => $ value)
        (
          $ errorSchemaLocal sfValidatorErrorSchema = new ($ this);

          / / Nome do arquivo é preenchido, mas nenhuma legenda
          if ($ valor [ 'filename'] & &!$ value [ 'legenda'])
          (
            $ errorSchemaLocal-> addError sfValidatorError (novo ($ this, 'required'), 'caput');
          )

          / / Legenda é preenchido, mas nenhum nome de arquivo
          if ($ valor [ 'caption'] & &!$ value [ 'filename'])
          (
            $ errorSchemaLocal-> addError sfValidatorError (novo ($ this, 'required'), 'filename');
          )

          / / Sem legenda e não nome do arquivo, remova os valores vazios
          if (!$ value [ 'filename'] & &!$ value [ 'legenda'])
          (
            unset ($ values [$ key]);
          )

          / / Algum erro para este incorporado em formulário
          if (count ($ errorSchemaLocal))
          (
            errorSchema $-> addError ($ errorSchemaLocal, (string) $ key);
          )
        )

        / / Gera o erro para o formulário principal
        if (count ($ errorSchema))
        (
          throw sfValidatorErrorSchema novo (errorSchema $ this, $);
        )

        return $ valores;
      )
    )

> ** Dica **
> Todos os validadores estender `sfValidatorBase» e exigem apenas o Doclean `()`
> método. O configure () `método também pode ser utilizado para adicionar opções ou mensagem
> validador. Neste caso, duas mensagens são adicionadas ao validador.
> Da mesma formulário, opções adicionais podem ser adicionados através do addOption »()» método.

O `Doclean ()» método é responsável pela limpeza e validação dos vinculados
valores. A lógica do validador em si é bastante simples:

* Se uma foto é apresentada apenas com o nome do arquivo ou uma legenda, jogamos um
   erro ( `sfValidatorErrorSchema») com a mensagem adequada;

* Se uma foto é apresentado com nenhum nome de arquivo e sem legenda, que remova o
   valores completamente para evitar a salvar uma foto de vazio;

* Se não ocorreram erros de validação, o método retorna a array de
   valores limpos.

> ** Dica **
> Porque o validador personalizado nesta situação é para ser usado como um
> validador post, o Doclean «()» método espera um array do limite
> valores e retorna um array de valores limpos. Validadores personalizados, no entanto,
> podem ser facilmente criados para campos individuais. Nesse caso, o
> `Doclean ()» método irá esperar apenas um valor (o valor do apresentado
> campo) e irá retornar apenas um valor.

O último passo é substituir o saveEmbeddedForms »()» método de «ProductForm`
para remover as formulários photo vazio de não salvar uma foto em branco no banco de dados (que
caso contrário gera uma exceção como a legenda «coluna» é obrigatório):

    [php]
    saveEmbeddedForms função pública ($ con = null, $ formulário = null)
    (
      if (null === $ formulários)
      (
        fotos = $ this-> getValue ( 'newPhotos');
        formulários = $ this-> embeddedForms;
        foreach ($ this-> embeddedForms [ 'newPhotos'] as $ name => $ form)
        (
          if (!isset ($ fotos [$ name]))
          (
            unset ($ formulários [ 'newPhotos'] [$ name]);
          )
        )
      )

      return parent:: saveEmbeddedForms ($ con, $ formulários);
    )

Facilmente Embedding Doutrina-Formas Conexas
---------------------------------------

Novo no symfony 1,3 é a sfFormDoctrine ~ `:: embedRelation ()` ~ função que
permite ao desenvolvedor incorporar n-para-muitos em um formulário
automaticamente. Suponha, por exemplo, que além de permitir que o usuário
upload dois novos `ProductPhotos», queremos também permitir ao usuário modificar o
existente »ProductPhoto` objetos relacionados a este «produto».

Em seguida, use o embedRelation »()» método para adicionar um adicional
`` ProductPhotoForm objeto para cada existente »ProductPhoto» Objeto:

    [php]
    / / Lib / form / doutrina / ProductForm.class.php
    public function configure ()
    (
      / / ...

      $ this-embedRelation> ( 'Fotos');
    )

Internamente, sfFormDoctrine ~ `:: embedRelation ()` ~ é quase exatamente o que fizemos
manualmente para inserir nossos dois novos `` ProductPhotoForm objetos. Se dois ProductPhoto ``
relações já existem, então o resultado widgetSchema `` e `` validatorSchema
de nossa formulário tomaria a seguinte forma:

    [php]
    widgetSchema => array
    (
      [id] => sfWidgetFormInputHidden,
      [nome] = sfWidgetFormInputText>,
      [preço] = sfWidgetFormInputText>,
      [newPhotos] => array (...)
      [Fotos] => array (
        [0] => array (
          [id] => sfWidgetFormInputHidden,
          [legenda] = sfWidgetFormInputText>,
        ),
        [1] => array (
          [id] => sfWidgetFormInputHidden,
          [legenda] = sfWidgetFormInputText>,
        ),
      ),
    )

    validatorSchema => array
    (
      [ID] => sfValidatorDoctrineChoice,
      [nome] => sfValidatorString,
      [preço] = sfValidatorNumber>,
      [newPhotos] => array (...)
      [Fotos] => array (
        [0] => array (
          [ID] => sfValidatorDoctrineChoice,
          [legenda] => sfValidatorString,
        ),
        [1] => array (
          [id] => sfValidatorDoctrineChoice,
          [legenda] => sfValidatorString,
        ),
      ),
    )

! [Produto formulário com 2 fotos existentes] (http://www.symfony-project.org/images/more-with-symfony/advanced_forms_03.png tipo de produto ", com 2 fotos existentes")

A próxima etapa é adicionar código para o ponto de vista que irá processar o novo incorporado
* Foto formulários *:

    [php]
    / Apps / frontend / módulo / produto / templates / _form.php
    <?php foreach ($ form [ 'Photos'] as $ foto):?>
      <?php echo $ foto [ 'legenda'] - RenderRow> ()?>
      <?php echo $ foto [ 'filename'] -> RenderRow (array ( 'width' => 100))?>
    <?php endif;?>

Este trecho é exatamente o que usamos anteriormente para inserir as formulários nova foto.

O último passo é converter o arquivo de upload por um campo que permite ao usuário
Para ver a foto atual e mudá-lo por um novo
( `sfWidgetFormInputFileEditable»):

    [php]
    public function configure ()
    (
      $ this-> useFields (array ( 'filename', 'caption'));

      $ this-setValidator> ( 'sfValidatorFile arquivo', novo (array (
        'mime_types' => 'web_images',
        'path' => sfConfig:: get ( 'sf_upload_dir').'/ produtos,
        'required' => false,
      )));

      $ this-setWidget> ( 'filename', nova série (sfWidgetFormInputFileEditable (
        'file_src' => '/ uploads / produtos / ". $ this-> GetObject () - filename>,
        'edit_mode' =>!$ this-> isNew (),
        'is_image' => true,
        'with_delete' => false,
      )));

      $ this-> validatorSchema [ 'legenda'] - setOption> ( 'required', false);
    )

Formulário de Eventos
-----------

Novo no symfony 1.3 são eventos de formulário que pode ser usado para estender qualquer formulário
objeto de qualquer parte do projeto. Symfony expõe o seguinte formulário quatro
Ocorrências:

* `` Form.post_configure: Este evento é notificada após cada formulário está configurado
* `` Form.filter_values: Este evento filtros da concentração, os parâmetros de arquivos contaminados e arrayes pouco antes da ligação
* `` Form.validation_error: Este evento é notificado sempre que não validação do formulário
* `` Form.method_not_found: Este evento é notificado sempre que um método desconhecido é chamado

# # # Custom Logging via `` form.validation_error

Usando a eventos de formulário, é possível adicionar registo personalizado para validação
erros de qualquer formulário em seu projeto. Isto pode ser útil se você deseja acompanhar
quais as formulários e os campos estão causando confusão para os usuários.

Comece por registar um ouvinte com o despachante de eventos para o
`form.validation_error evento». Adicionar o seguinte à instalação »()» método
de «ProjectConfiguration`, que está localizado dentro do diretório `` config:

    [php]
    configuração da função pública ()
    (
      / / ...

      $ this-getEventDispatcher> () -> connect (
        'form.validation_error',
        array ( 'BaseForm', 'listenToValidationError')
      );
    )

`BaseForm», localizado em `lib / form`, é uma classe especial de formulário que todas as formas
Aulas de estender. Essencialmente, o `BaseForm» é uma classe em que o código pode ser colocado
e acessadas por todos os objetos de formulário através do projeto. Para activar o registo de
erros de validação, basta adicionar o seguinte para o `BaseForm classe»:

    [php]
    listenToValidationError public static function ($ evento)
    (
      foreach ($ eventos [ 'error'] as $ key => $ erro)
      (
        self:: getEventDispatcher () -> notify (sfEvent novo (
          $ event-> getSubject (),
          'application.log',
          array (
            «prioridade» sfLogger =>:: COMUNICAÇÃO,
            sprintf ( 'Erro de validação:% s:% s', $ key, (string) $ erro)
          )
        ));
      )
    )

! [Registo de erros de validação] (http://www.symfony-project.org/images/more-with-symfony/advanced_forms_05.png "Web ferramentas de depuração de erros de validação")

Custom Styling, quando um elemento de formulário tem um erro
-----------------------------------------------

Como exercício final, vamos voltar a um tema um pouco mais leve relacionados com a
estilização de elementos de formulário. Suponha, por exemplo, que o projeto para o Produto `
página inclui um estilo especial para campos que não conseguiram a validação.

! [Produto formulário com erros] (http://www.symfony-project.org/images/more-with-symfony/advanced_forms_02.png tipo de produto "com campos de erro denominado")

Suponha que o designer já implementou o estilo que será aplicado o erro
denominar a qualquer campo `input` `dentro de uma div com a classe` `` form_error_row.
Como podemos facilmente adicionar o `` form_row_error classe para os campos com erros?

A resposta está em um objeto especial chamado * formuláriotador esquema forma *. Every
formulário symfony usa um esquema de forma formatador * * para determinar a exata
formuláriotação HTML para usar quando a saída de elementos do formulário. Por padrão o symfony,
formuláriotador usa um formulário que utiliza tags HTML tabela.

Primeiro, vamos criar uma nova formulário de classe formatador esquema que emprega pouco
isqueiro marcação quando emitir o formulário. Criar um novo arquivo chamado
`sfWidgetFormSchemaFormatterAc2009.class.php» e colocá-lo no
`widget / lib /` (você precisa criar este diretório):

    [php]
    classe sfWidgetFormSchemaFormatterAc2009 estende sfWidgetFormSchemaFormatter
    (
      protegidos
        $ rowFormat = "<div class=\"form_row\">
                            %% marcador \ erro% n%%% <br/> campo
                            ajuda%%%% hidden_fields \ n </ div> \ n ",
        $ errorRowFormat = "<div>%% erros </ div>",
        $ helpFormat = '<div class="form_help">%% de ajuda </ div>',
        $ decoratorFormat = "<div> \% n% de conteúdo </ div>";
    )

Embora o formulárioto dessa classe é estranha, a idéia geral é que o RenderRow `()`
método irá utilizar o `$` rowFormat marcação para organizar sua saída. Um esquema de formulário
classe formuláriotador oferece muitas outras opções de formatação que não vou abordar aqui
em detalhe. Forma mais informulárioções, consultar o
[symfony 1,3 API] (http://www.symfony-project.org/api/1_3/sfWidgetFormSchemaFormatter).

Para usar o formuláriotador novo esquema forma em todos os objetos de formulário no seu projeto,
adicione o seguinte a `` ProjectConfiguration:

    [php]
    ProjectConfiguration classe estende sfProjectConfiguration
    (
      configuração da função pública ()
      (
        / / ...

        sfWidgetFormSchema:: setDefaultFormFormatterName ( 'ac2009');
      )
    )

O objetivo é adicionar uma classe form_row_error `` o `form_row` elemento div
apenas se um campo não validação. Adicionar um `%% row_class` token para o
`$ rowFormat propriedade» e substituir o sfWidgetFormSchemaFormatter ~ `:: formuláriotRow ()` ~
método da seguinte formulário:

    [php]
    classe sfWidgetFormSchemaFormatterAc2009 estende sfWidgetFormSchemaFormatter
    (
      protegidos
        $ rowFormat = "<div class=\"form_row%row_class%\">
                            %% \ label erro% n% <br/>% campo%
                            ajuda%%%% hidden_fields \ n </ div> \ n ",
        / / ...

      formuláriotRow função pública ($ label, field, $ erros = array (), $ help ='', $ = HiddenFields null)
      (
        $ row = parent:: formuláriotRow (
          label
          $ field,
          $ errors,
          Ajuda
          HiddenFields $
        );

        return strtr ($ row, array (
          '%% row_class' => count (($ erros)> 0)? 'Form_row_error':'',
        ));
      )
    )

Com esta adição, cada elemento que está de saída através da RenderRow () `método
será automaticamente cercado por um `form_row_error` div `se o campo tem
validação falha.

Reflexões finais
--------------

O quadro é simultaneamente uma formulário de o mais poderoso e mais
componentes complexos dentro symfony. O trade-off para a validação de formulário apertada,
CSRF proteção, e as formulários de objeto é que o alargamento do quadro pode rapidamente
tornar-se uma tarefa difícil. Gaining a deeper understanding of the form system,
No entanto, é a chave para libertar todo o seu potencial. Espero que este capítulo tem
exame de você um passo mais perto.

Evolução do quadro de formulário incidirá sobre a preservação do poder, enquanto
diminuindo a complexidade e dando mais flexibilidade para o desenvolvedor. O
quadro formulário só agora está em sua infância.
