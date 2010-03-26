Formulários Avançados
=====================

*por Ryan Weaver, Fabien potencier*

O framework de formulários do Symfony equipa o desenvolvedor com as ferramentas 
necessárias para facilmente processar e validar os dados do formulário de uma 
forma orientada à objeto. Graças as classes ~`sfFormDoctrine`~ and ~`sfFormPropel`~ 
oferecidas por cada ORM, o framework de formulários pode facilmente mostrar e 
salvar formulários que estão intimamente relacionados a camada de dados.

Situações do mundo real, porém, muitas vezes exigem ao desenvolvedor personalizar
e estender os formulários. Neste capítulo iremos apresentar e resolver vários problemas comuns 
mas desafiadores. Também vamos dissecar o objeto ~`sfForm`~ e
remover alguns de seus mistérios.

Mini-Projeto: Produtos e Fotos
-------------------------------

O primeiro problema gira em torno da edição de um produto individual e um
número ilimitado de fotos para esse produto. O usuário deve ser capaz de editar
tanto o produto quanto as suas fotos no mesmo formulário. Nós também precisamos
permitir ao usuário fazer upload de até duas fotos novas do produto de cada vez.
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

Quando concluído, o formulário será parecido com o seguinte:

![Formulário de Produto e Foto](http://www.symfony-project.org/images/more-with-symfony/advanced_forms_01.png "Form de Produto com form ProductPhoto inserido")

Aprenda mais fazendo o Exemplos
-------------------------------

A melhor maneira de aprender técnicas avançadas é acompanhar e testar os exemplos
passo a passo. Graças ao recurso `--installer` do [symfony](#chapter_03), nós 
fornecemos uma maneira simples para você criar um projeto funcional com um banco de 
dados SQLite, o esquema do banco Doctrine, algumas fixtures, uma aplicação `frontend`
e um módulo `produto` para trabalhar.
Baixe o [script](http://www.symfony-project.org/images/more-with-symfony/advanced_form_installer.php.src)
do instalador e execute o seguinte comando para criar o projeto symfony:

    $ php symfony generate:project advanced_form --installer=/path/to/advanced_form_installer.php

Este comando cria um projeto totalmente funcional com o esquema de banco de dados 
que havíamos introduzido na seção anterior.

>**NOTE**
>Neste capítulo, os caminhos de arquivo são para um projeto symfony executando com o
>Doctrine, como gerado na tarefa anterior.

Configuração Básica do Formulário
---------------------------------

Como os requisitos envolvem mudanças em dois modelos diferentes (`Product`
e `ProductPhoto`), a solução terá que incorporar dois formulários do symfony diferentes 
(`ProductForm` e `ProductPhotoForm`). Felizmente, o framework de 
formulário pode facilmente combinar múltiplos formulários em um único formulário utilizando o ~`sfForm::embedForm()`~.
Primeiro, configure o `ProductPhotoForm` independentemente.  
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
requeridos, mas por razões diferentes. O campo `caption` é obrigatório porque
a coluna relacionada no esquema do banco de dados foi definida com `notnull`.
O campo `filename` é obrigatório porque, por padrão, um objeto validador seta a opção `required` para `true`.

>**NOTE**
>~`sfForm::useFields()`~ é uma nova função do symfony 1.3 que permite ao
>desenvolvedor especificar exatamente quais os campos do formulário deseja utilizar e, em que
>ordem eles devem ser exibidos. Todos os outros campos não ocultos são removidos
>do formulário.

Até agora, nós não fizemos nada mais do que a configuração simples de um formulário. 
A seguir, vamos combinar os formulários em um.

Embutindo Formulários
---------------------

Ao utilizar o ~`sfForm::embedForm()`~, o `ProductForm` e `ProductPhotoForms` 
podem ser combinados com um esforço muito pequeno. O trabalho é sempre feito
no *formulário* principal, que, neste caso, é `ProductForm`. O requisito é a 
capacidade de enviar até duas fotos do produto de uma vez.
Para realizar isso, insira dois objetos `ProductPhotoForm` no `ProductForm`:

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

Se você apontar seu navegador para o módulo `product`, agora você poderá fazer o
upload de dois `ProductPhoto`, bem como, modificar o próprio objeto `Product`.

O symfony salva automaticamente os novos objetos `ProductPhoto` relacionados 
ao objeto correspondente `Product`. Mesmo o upload do arquivo, definido em
`ProductPhotoForm`, é executado normalmente.

Verifique se os registros foram salvos corretamente no banco de dados:

    $ php symfony doctrine:dql --table "FROM Product"
    $ php symfony doctrine:dql --table "FROM ProductPhoto"

Na tabela `ProductPhoto`, você vai notar os nomes dos arquivos das fotos.
Tudo está funcionando como esperado se você puder encontrar os arquivos com os mesmos nomes
do banco de dados no diretório `web/upload/products/`.

>**NOTE**
>Devido aos campos `filename` e `caption` serem obrigatórios no `ProductPhotoForm`,
>a validação do formulário principal irá sempre falhar a não ser que o usuário faça o
>upload de duas novas fotos. Continue lendo para saber como corrigir este problema.

Refatoração (*Refactoring*)
---------------------------

Mesmo o formulário anterior funcionando como esperado, seria interessante refatorar
o código para facilitar os testes e permitir que o código seja facilmente reutilizado.

Primeiro, vamos criar um novo formulário que representa uma coleção do
`ProductPhotoForm`, com base no código já escrito:

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

 * `product`: O produto para o qual se cria uma coleção do `ProductPhotoForm`;

 * `size`: O número de `ProductPhotoForm` a ser criado (dois por padrão).

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
--------------------------

No sentido mais básico, um formulário web é uma coleção de campos que são processados
e enviados de volta para o servidor. No mesmo sentido, o objeto ~`sfForm`~  é
essencialmente um array de *campos* de formulário. Enquanto o ~`sfForm`~ gerencia o processo,
os campos individuais são responsáveis por definir o modo como cada um será processado
e validado.

No symfony, cada *campo* do formulário é definido por dois objetos diferentes:

  * Um *widget* que controla a marcação XHTML do campo;

  * Um *validator* que limpa e valida os dados enviados

>**TIP**
>No symfony, um *widget* é definido como qualquer objeto cujo único trabalho é saída
>de marcação XHTML. Embora mais comumente usado com formulários, um objeto Widget
>poderia ser criado para mostrar qualquer marcação.

### Um formulário é um array

Lembre-se que o objeto ~`sfForm`~ é "essencialmente um array de *campos* de formulário."
Para ser mais preciso, o `sfForm` guarda tanto um array de elementos como um array
de validadores para todos os campos do formulário. Estes dois arrays, chamados
`widgetSchema` e `validatorSchema` são propriedades da classe `sfForm`.
Para adicionar um campo a um formulário, basta adicionar o widget do campo no
array `widgetSchema` e o validador do campo no array `validatorSchema`.
Por exemplo, o código a seguir adiciona um campo `email` em um formulário:

    [php]
    public function configure()
    {
      $this->widgetSchema['email'] = new sfWidgetFormInputText();
      $this->validatorSchema['email'] = new sfValidatorEmail();
    }

>**NOTE**
>Os arrays `widgetSchema` e `validatorSchema` são, na verdade, classes especiais
>chamadas ~`sfWidgetFormSchema`~ e ~`sfValidatorSchema`~ que implementam a
>interface `ArrayAccess`.

### Dissecando o `ProductForm`

Como a classe `ProductForm` estende a `sfForm`, ela também abriga todos os
seus widgets e validadores nos arrays `widgetSchema` e `validatorSchema`.
Vamos analisar como cada array é organizado no objeto `ProductForm` finalizado.

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

>**TIP**
>Assim como o `widgetSchema` e `validatorSchema` são, na verdade, objetos que se comportam
>como arrays, os arrays acima definidos pelas chaves `newPhotos`, `0` e `1`
>também são objetos `sfWidgetSchema` e `sfValidatorSchema`.

Como esperado, os campos básicos (`id`, `name` e `price`) estão representados no primeiro
nível de cada array. Em um formulário que não incorpora outros formulários, os arrays `widgetSchema` 
e `validatorSchema` têm apenas um nível, que representa os campos básicos do formulário.
Os *widgets* e validadores de qualquer formulário incorporado são representados como arrays filhos
no `widgetSchema` e `validatorSchema` como visto acima. 
O método que gerencia este processo é explicado a seguir.

### Entendendo o método ~`sfForm::embedForm()`~

Tenha em mente que um formulário é composto por um array de *widgets* e um array de
validadores. A incorporação de um formulário em outro, essencialmente, significa que os 
arrays dos widgets e validadores de um formulário são adicionados aos arrays dos widgets
e validadores do formulário principal. Isto é inteiramente realizado pelo 
`sfForm::embedForm()`. O resultado é sempre uma adição multi-dimensional em `widgetSchema` 
e `validatorSchema` como visto acima.

A seguir, vamos discutir a configuração do `ProductPhotoCollectionForm`, que liga
objetos `ProductPhotoForm` em si. Esse formulário intermediário age como um "wrapper" de formulário
e contribui com a organização de forma global. Vamos começar com o seguinte código
do `ProductPhotoCollectionForm::configure()`:

    [php]
    $form = new ProductPhotoForm($productPhoto);
    $this->embedForm($i, $form);

O próprio formulário `ProductPhotoCollectionForm` começa como um novo objeto `sfForm`.
Como tal, seus arrays `widgetSchema` e `validatorSchema` estão vazios.

    [php]
    widgetSchema    => array()
    validatorSchema => array()

Cada `ProductPhotoForm`, no entanto, já está preparado com três campos (`id`, `filename`,
e `caption`) e três itens correspondentes em seus arrays `widgetSchema` e `validatorSchema`.

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

Quando finalizado, os arrays `widgetSchema` e `validatorSchema` do formulário
"wrapper" (`ProductPhotoCollectionForm`) serão arrays multi-dimensional que mantêm os
widgets e validadores de ambos os `ProductPhotoForm`.

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

Na etapa final do nosso processo, o formulário "wrapper" resultante,
`ProductPhotoCollectionForm`, é embutido (`embedded`) diretamente no `ProductForm`.
Isso ocorre dentro de `ProductForm::configure()`, que tira proveito de
todo o trabalho que foi feito dentro do `ProductPhotoCollectionForm`:

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

O template atual `_form.php` do módulo `product` é parecido com o
seguinte:

    [php]
    // apps/frontend/module/product/templates/_form.php
    <!-- ... -->

    <tbody>
      <?php echo $form ?>
    </tbody>

    <!-- ... -->

A declaração `<?php echo $form?>` é a forma mais simples de exibir um formulário,
mesmo os mais complexos. São de grande ajuda durante a prototipagem, mas logo
que você desejar alterar o layout, será necessário substituí-lo pela sua própria
lógica. Remova esta linha agora, já que iremos substituí-la nesta seção.

A coisa mais importante para compreender quando estiver renderizando formulários incorporados na
visão é a organização do array multi-dimensional `widgetSchema` explicado
nas seções anteriores. Para este exemplo, vamos começar renderizando os campos básicos
`name` e `price` do ProductForm` na visão:

    [php]
    // apps/frontend/module/product/templates/_form.php
    <?php echo $form['name']->renderRow() ?>

    <?php echo $form['price']->renderRow() ?>

    <?php echo $form->renderHiddenFields() ?>

Como o próprio nome indica, o `renderHiddenFields()` processa todos os campos ocultos
do formulário.

>**NOTE**
>O código das ações não foi mostrado aqui propositalmente, porque não requer especial
>atenção. Dê uma olhada no arquivo `apps/frontend/modules/product/actions/actions.class.php`.
>É como qualquer CRUD normal e pode ser gerado automaticamente através
>da tarefa `doctrine:generate-module`.

Como já aprendemos, a classe `sfForm` guarda os arrays `widgetSchema` e 
`validatorSchema` que definem os nossos campos. Além disso, a classe `sfForm`
implementa a interface `ArrayAccess`, nativa do PHP 5, o que significa que pode acessar diretamente
campos do formulário usando a sintaxe chave array vista acima.

Para a saída de campos, você pode simplesmente acessá-los diretamente e chamar o método
`renderRow()`. Mas que tipo de objeto é o `$form['name']`? Você pode esperar
que a resposta seja um `sfWidgetFormInputText` para o campo `name`, mas,
a resposta é um pouco diferente.

### Renderizando cada campo de formulário com o ~`sfFormField`~

Ao utilizar os arrays `widgetSchema` e `validatorSchema` definidos em cada 
classe de formulário, o `sfForm` gera automaticamente um terceiro array chamado
`sfFormFieldSchema`. Este array contém um objeto especial para cada campo
que atua como uma classe auxiliar responsável pela saída do campo. 
O objeto, do tipo ~`sfFormField`~, é uma combinação de cada elemento de campo
e da validação, e, é criado automaticamente.

    [php]
    <?php echo $form['name']->renderRow() ?>

No trecho acima, `$form['name']` é um objeto `sfFormField`, que abriga
o método `renderRow()` junto com diversas outras funções úteis de processamento.

### Métodos de renderização do sfFormField 

Cada objeto `sfFormField` pode ser usado para facilmente processar cada aspecto do campo
que representa (por exemplo, o próprio campo, o rótulo, mensagens de erro, etc.)
Alguns dos métodos úteis dentro sfFormField `` incluem o seguinte. Outro:
podem ser encontrados através do [symfony 1,3 API](http://www.symfony-project.org/api/1_3/sfFormField).

 * `sfFormField->render()`: Processa o campo do formulário (por exemplo, `input`, `select`)
   com o valor correto usando o objeto widget do campo.

 * `sfFormField->renderError()`: Processa quaisquer erros de validação no campo
   usando o objeto validador do campo.

 * `sfFormField->renderRow()`: Tudo-englobado: renderiza o rótulo, o formulário
   o campo, o erro e a mensagem de ajuda dentro de um *wrapper* de marcação XHTML.

>**NOTE**
>Na realidade, cada função de renderização da classe `sfFormField` também utiliza informação
>a partir da propriedade `widgetSchema` do formulário (o objeto `sfWidgetFormSchema` que
>agrega todos os widgets para o formulário). Esta classe auxilia na geração
>dos atributos de cada campo `name` e `id`, controla o rótulo para cada
>campo, e define a marcação XHTML usada com o `renderRow()`.

Uma coisa importante a notar é que o array `formFieldSchema` sempre
espelha a estrutura dos arrays `widgetSchema` e `validatorSchema` 
do formulário. Por exemplo, o array `formFieldSchema` do `ProductForm` 
concluído, teria a seguinte estrutura, que é a chave para renderizar cada
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

### Renderizando o novo ProductForm

Usando o array acima como o nosso mapa, podemos facilmente exibir os campos embutidos do `ProductPhotoForm` 
na visão localizando e processando os objetos `sfFormField` apropriados:

    [php]
    // apps/frontend/module/product/templates/_form.php
    <?php foreach ($form['newPhotos'] as $photo): ?>
      <?php echo $photo['caption']->renderRow() ?>
      <?php echo $photo['filename']->renderRow() ?>
    <?php endforeach; ?>

O bloco acima é executado duas vezes: uma para o array de campos de formulário `0` e
outra para o array de campos de formulário `1`. Como visto no diagrama acima,
os objetos subjacentes de cada array são objetos `sfFormField`, que podemos
exibir como quaisquer outros campos.

Salvando os Objetos de Formulários
-------------------------

Na maioria das circunstâncias, um formulário estará relacionado diretamente a uma ou mais tabelas do banco de dados
e provocará alterações aos dados nestas tabelas com base nos
valores submetidos. O symfony gera automaticamente um objeto de formulário para cada modelo do esquema,
que estende o `sfFormDoctrine` ou `sfFormPropel` dependendo do seu
ORM. Cada classe de formulário é semelhante e, finalmente, permite que os valores submetidos
sejam facilmente persistidos no banco de dados.

>**NOTE**
>O ~`sfFormObject`~  é uma nova classe adicionada no symfony 1.3 para manusear todas as
>tarefas comuns do `sfFormDoctrine` e `sfFormPropel`. Cada classe estende o
>`sfFormObject`, que agora administra parte do processo de salvar o formulário, como descrito abaixo.

### O Processo de salvar um Formulário

No nosso exemplo, o symfony automaticamente salva tanto as informações do `Product` quanto 
quanto as dos novos objetos `ProductPhoto` sem qualquer esforço adicional por parte do desenvolvedor.
O método que dispara a magia, ~`sfFormObject::save()`~, executa internamente
uma variedade de métodos. Entender este processo é fundamental para a estender 
o processo em situações mais avançadas.

O processo de salvar um formulário consiste em uma série de métodos internamente executados,
tudo acontece após a chamada do ~`sfFormObject::save()`~. A maioria
do trabalho acontece no método ~`sfFormObject::updateObject()`~, que
é chamado recursivamente em todos os seus formulários incorporados.

![Proceso de gravação do formulário](http://www.symfony-project.org/images/more-with-symfony/advanced_forms_06.png "Detalhe do processo de salvar um formulário")

>**NOTE**
>A maioria do processo de salvar ocorre dentro do método ~`sfFormObject::doSave()`~,
>que é chamado pelo `sfFormObject::save()` e dentro de uma transação do banco de dados.
>Se você precisar modificar o processo de salvar, o melhor lugar para fazê-lo
>é geralmente no `sfFormObject::doSave()`.

Ignorando os Formulários Embutidos
----------------------------

A implementação atual do `ProductForm` tem um grande déficit. Devido aos
campos `filename` e `caption` serem obrigatórios no `ProductPhotoForm`, a validação
do formulário principal irá sempre falhar a não ser que o usuário envie duas fotos novas.
Em outras palavras, o usuário não pode simplesmente mudar o preço do `Product` sem
também ser obrigado a carregar duas novas fotos.

![Falha na validação das fotos do formulário dos produtos](http://www.symfony-project.org/images/more-with-symfony/advanced_forms_04.png "Form dos produtos falha na validação das fotos")

Vamos redefinir os requisitos para incluir o seguinte. Se o usuário deixar
todos os campos do `ProductPhotoForm` em branco, o formulário deve ser ignorado
completamente. No entanto, se pelo menos um campo possuir dados (por exemplo, `caption` ou `filename`),
o formulário deve validar e salvar normalmente. Para obter isso, vamos empregar
uma técnica avançada que envolve a utilização de um *post* de validação personalizado.

O primeiro passo, no entanto, é modificar o formulário `ProductPhotoForm` para tornar os
campos `caption` e `filename` opcionais:

    [php]
    // lib/form/doctrine/ProductPhotoForm.class.php
    public function configure()
    {
      $this->setValidator('filename', new sfValidatorFile(array(
        'mime_types' => 'web_images',
        'path' => sfConfig::get('sf_upload_dir').'/products',
        'required' => false,
      )));

      $this->validatorSchema['caption']->setOption('required', false);
    }

No código acima, nos definimos a opção `required` como `false`
quando sobrescrevemos o validador padrão para o campo `filename`. Além disso,
temos que definir explicitamente a opção `required` do campo `caption` para `false`.

Agora, vamos adicionar um validador post para no formulário `ProductPhotoCollectionForm`:

    [php]
    // lib/form/doctrine/ProductPhotoCollectionForm.class.php
    public function configure()
    {
      // ...

      $this->mergePostValidator(new ProductPhotoValidatorSchema());
    }

Um validador post é um tipo especial de validador que valida todos 
os valores submetidos (em oposição a validação do valor de um campo único).
Um dos validadores post mais comum é o `sfValidatorSchemaCompare`
que verifica, por exemplo, se o valor de um campo inferior ao de outro campo.

### Criando um validador personalizado

Felizmente, criar um validador personalizado é realmente muito fácil. Crie um
novo arquivo `ProductPhotoValidatorSchema.class.php` e adicione-o no
diretório `lib/validator/` (você precisa criar este diretório):

    [php]
    // lib/validator/ProductPhotoValidatorSchema.class.php
    class ProductPhotoValidatorSchema extends sfValidatorSchema
    {
      protected function configure($options = array(), $messages = array())
      {
        $this->addMessage('caption', 'The caption is required.');
        $this->addMessage('filename', 'The filename is required.');
      }

      protected function doClean($values)
      {
        $errorSchema = new sfValidatorErrorSchema($this);

        foreach($values as $key => $value)
        {
          $errorSchemaLocal = new sfValidatorErrorSchema($this);

          // filename is filled but no caption
          if ($value['filename'] && !$value['caption'])
          {
            $errorSchemaLocal->addError(new sfValidatorError($this, 'required'), 'caption');
          }

          // caption is filled but no filename
          if ($value['caption'] && !$value['filename'])
          {
            $errorSchemaLocal->addError(new sfValidatorError($this, 'required'), 'filename');
          }

          // no caption and no filename, remove the empty values
          if (!$value['filename'] && !$value['caption'])
          {
            unset($values[$key]);
          }

          // some error for this embedded-form
          if (count($errorSchemaLocal))
          {
            $errorSchema->addError($errorSchemaLocal, (string) $key);
          }
        }

        // throws the error for the main form
        if (count($errorSchema))
        {
          throw new sfValidatorErrorSchema($this, $errorSchema);
        }

        return $values;
      }
    }

>**TIP**
>Todos os validadores estendem o `sfValidatorBase` e exigem apenas o método `doClean()`.
>O método `configure()` também pode ser utilizado para adicionar opções ou mensagem
>para o validador. Neste caso, duas mensagens são adicionadas ao validador.
>Da mesma forma, opções adicionais podem ser adicionadas através do método `addOption()`.

O método `doClean()` é responsável pela limpeza e validação dos valores vinculados.
A lógica do validador em si é bastante simples:

* Se uma foto é submetida com apenas o nome do arquivo ou uma legenda, é apresentado um
   erro (`sfValidatorErrorSchema`) com a mensagem adequada;

* Se uma foto é submetida sem nome de arquivo e sem legenda, removemos os
   valores completamente para evitar salvar uma foto vazia;

* Se não ocorreram erros de validação, o método retorna um array com os
   valores *limpos*.

>**TIP**
>Devido ao validador personalizado ser usado como um validador post
>nesta situação, o método `doClean()` espera um array dos valores
>e retorna um array de valores *limpos*. Validadores personalizados, no entanto,
>podem ser facilmente criados para campos individuais. Nesse caso, o
>método `doClean()` irá esperar apenas um valor (o valor do campo
>submetido) e irá retornar apenas um valor.

O último passo é sobrescrever o método `saveEmbeddedForms()` do `ProductForm`
para remover os formulários de fotos vazios e evitar salvar uma foto vazia no banco de dados (que,
caso contrário, gera uma exceção porque a coluna `caption` é obrigatória):

    [php]
    public function saveEmbeddedForms($con = null, $forms = null)
    {
      if (null === $forms)
      {
        $photos = $this->getValue('newPhotos');
        $forms = $this->embeddedForms;
        foreach ($this->embeddedForms['newPhotos'] as $name => $form)
        {
          if (!isset($photos[$name]))
          {
            unset($forms['newPhotos'][$name]);
          }
        }
      }

      return parent::saveEmbeddedForms($con, $forms);
    }

Embutindo facilmente formulários relacionados com o Doctrine
------------------------------------------------------------

Outra novidade no symfony 1.3 é a função ~`sfFormDoctrine::embedRelation()`~ que
permite ao desenvolvedor incorporar relacionamentos n-para-muitos automaticamente 
em um formulário. Suponha, por exemplo, que além de permitir que ao usuário o 
upload de dois novos `ProductPhoto`, queremos também permitir ao usuário modificar o
objetos `ProductPhoto` existentes relacionados a este produto.

Em seguida, use o método `embedRelation()` para adicionar um objeto 
`ProductPhotoForm` adicional para cada objeto `ProductPhoto` existente:

    [php]
    // lib/form/doctrine/ProductForm.class.php
    public function configure()
    {
      // ...

      $this->embedRelation('Photos');
    }

Internamente, o método ~`sfFormDoctrine::embedRelation()`~ faz exatamente o mesmo que fizemos
manualmente para inserir nossos dois novos objetos `ProductPhotoForm`. Se já existirem duas 
relações do `ProductPhoto`, então os arrays `widgetSchema` e `validatorSchema` resultantes
do nosso formulário teriam o seguinte aspecto:

    [php]
    widgetSchema    => array
    (
      [id]          => sfWidgetFormInputHidden,
      [name]        => sfWidgetFormInputText,
      [price]       => sfWidgetFormInputText,
      [newPhotos]   => array(...)
      [Photos]      => array(
        [0]           => array(
          [id]          => sfWidgetFormInputHidden,
          [caption]     => sfWidgetFormInputText,
        ),
        [1]           => array(
          [id]          => sfWidgetFormInputHidden,
          [caption]     => sfWidgetFormInputText,
        ),
      ),
    )

    validatorSchema => array
    (
      [id]          => sfValidatorDoctrineChoice,
      [name]        => sfValidatorString,
      [price]       => sfValidatorNumber,
      [newPhotos]   => array(...)
      [Photos]      => array(
        [0]           => array(
          [id]          => sfValidatorDoctrineChoice,
          [caption]     => sfValidatorString,
        ),
        [1]           => array(
          [id]          => sfValidatorDoctrineChoice,
          [caption]     => sfValidatorString,
        ),
      ),
    )

![Formulário de produto com duas fotos existentes](http://www.symfony-project.org/images/more-with-symfony/advanced_forms_03.png "Formulário de produto com duas fotos existentes")

A próxima etapa é adicionar código na visão que irá processar o novo formulário do tipo
*Photo* incorporado:

    [php]
    // apps/frontend/module/product/templates/_form.php
    <?php foreach ($form['Photos'] as $photo): ?>
      <?php echo $photo['caption']->renderRow() ?>
      <?php echo $photo['filename']->renderRow(array('width' => 100)) ?>
    <?php endforeach; ?>

O código anterior é exatamente o mesmo que usamos anteriormente para embutir aos formulários novas fotos.

O último passo é converter o campo de upload de arquivo por um campo que permite ao usuário
visualizar a foto atual e alterá-la para uma nova (`sfWidgetFormInputFileEditable`):

    [php]
    public function configure()
    {
      $this->useFields(array('filename', 'caption'));

      $this->setValidator('filename', new sfValidatorFile(array(
        'mime_types' => 'web_images',
        'path' => sfConfig::get('sf_upload_dir').'/products',
        'required' => false,
      )));

      $this->setWidget('filename', new sfWidgetFormInputFileEditable(array(
        'file_src'    => '/uploads/products/'.$this->getObject()->filename,
        'edit_mode'   => !$this->isNew(),
        'is_image'    => true,
        'with_delete' => false,
      )));

      $this->validatorSchema['caption']->setOption('required', false);
    }

Eventos de Formulário
---------------------

Os eventos de formulário são uma novidade no symfony 1.3. Eles podem ser usados para estender qualquer objeto de formulário
de qualquer parte do projeto. Symfony expõe os quatro seguintes eventos de formulário:

 * `form.post_configure`: este evento é notificado após cada formulário ser configurado
 * `form.filter_values`: este evento é notificado quando se combinam os valores e os
    arrays dos arquivos enviados pelos usuarios antes de associar os dados com o formulario
 * `form.validation_error`: este evento é notificado sempre que a validação do formulário falha
 * `form.method_not_found`: este evento é notificado sempre que um método desconhecido é chamado

### Mensagens de log personalizadas com o `form.validation_error`

Usando eventos de formulário, é possível adicionar log de mensagens personalizadas para erros 
de validação de qualquer formulário em seu projeto. Isto pode ser útil se você deseja acompanhar
quais formulários e campos estão causando confusão para os seus usuários.

Inicie registando um *listener* com o disparador de eventos (*event dispatcher*) para o
evento `form.validation_error`. Adicione ao método `setup()` do
`ProjectConfiguration`, que está localizado no diretório `config` o seguinte:

    [php]
    public function setup()
    {
      // ...

      $this->getEventDispatcher()->connect(
        'form.validation_error',
        array('BaseForm', 'listenToValidationError')
      );
    }

`BaseForm`, localizado em `lib/form`, é uma classe especial de formulário que todas as classes de formulário
estendem. Essencialmente, o `BaseForm` é uma classe em que o código pode ser colocado
e acessado por todos os objetos de formulário do projeto. Para ativar o log de mensagens de
erros de validação, basta adicionar o seguinte para na classe `BaseForm`:

    [php]
    public static function listenToValidationError($event)
    {
      foreach ($event['error'] as $key => $error)
      {
        self::getEventDispatcher()->notify(new sfEvent(
          $event->getSubject(),
          'application.log',
          array (
            'priority' => sfLogger::NOTICE,
            sprintf('Validation Error: %s: %s', $key, (string) $error)
          )
        ));
      }
    }

![Barra de debug web com erros de validação](http://www.symfony-project.org/images/more-with-symfony/advanced_forms_05.png "Barra de debug web com erros de validação")

Estilos personalizados, quando um elemento de formulário possui erro
------------------------------------------------------------

Como exercício final, vamos voltar a um tema um pouco mais leve relacionado com estilos dos
elementos de formulário. Suponha, por exemplo, que o *design* para a página `Product`
inclui um estilo especial para os campos que falharam na validação.

![Formulário de produtos com erros](http://www.symfony-project.org/images/more-with-symfony/advanced_forms_02.png "Formulário de produto com estilos aplicados aos campos com erro")

Suponha que o designer já implementou o *stylesheet* que será aplicado como o estilo de erro
para qualquer campo `input` dentro de um `div` com a classe `form_error_row`.
Como podemos facilmente adicionar a classe `form_row_error` aos campos com erros?

A resposta está em um objeto especial chamado formatador de esquema do formulário (*form schema formatter*). 
Cada formulário do symfony usa um *formatador de esquema do formulário* para determinar a exata
formatação HTML para usar quando na saída de elementos do formulário. Por padrão o formatador de formulário symfony,
que utiliza tags HTML de tabelas.

Primeiro, vamos criar uma nova classe *formatadora de esquema do formulário* que emprega
marcação mais leve para exibir o formulário. Crie um novo arquivo chamado
`sfWidgetFormSchemaFormatterAc2009.class.php` e adicione-o no
diretório `lib/widget/` (você precisa criar este diretório): 

    [php]
    class sfWidgetFormSchemaFormatterAc2009 extends sfWidgetFormSchemaFormatter
    {
      protected
        $rowFormat       = "<div class=\"form_row\">
                            %label% \n %error% <br/> %field%
                            %help% %hidden_fields%\n</div>\n",
        $errorRowFormat  = "<div>%errors%</div>",
        $helpFormat      = '<div class="form_help">%help%</div>',
        $decoratorFormat = "<div>\n  %content%</div>";
    }

Embora o formato dessa classe seja estranho, a idéia geral é que o método `renderRow()`
irá utilizar a marcação `$rowFormat` para organizar sua saída. Uma classe formatadora 
de esquema do formulário oferece muitas outras opções de formatação que não vou abordar aqui
em detalhes. Para mais informações, consultar a
[API do symfony 1.3](http://www.symfony-project.org/api/1_3/sfWidgetFormSchemaFormatter).

Para usar o *formatador de esquema do formulário* em todos os objetos de formulário no seu projeto,
adicione o seguinte no `ProjectConfiguration`:

    [php]
    class ProjectConfiguration extends sfProjectConfiguration
    {
      public function setup()
      {
        // ...

        sfWidgetFormSchema::setDefaultFormFormatterName('ac2009');
      }
    }

O objetivo é adicionar uma classe `form_row_error` no elemento div `form_row` 
apenas se um campo falhou na validação. Adicione um token %row_class% para a
propriedade `$rowFormat` e sobrescreva o método ~`sfWidgetFormSchemaFormatter::formatRow()`~
como a seguir:

    [php]
    class sfWidgetFormSchemaFormatterAc2009 extends sfWidgetFormSchemaFormatter
    {
      protected
        $rowFormat       = "<div class=\"form_row%row_class%\">
                            %label% \n %error% <br/> %field%
                            %help% %hidden_fields%\n</div>\n",
        // ...

      public function formatRow($label, $field, $errors = array(), $help = '', $hiddenFields = null)
      {
        $row = parent::formatRow(
          $label,
          $field,
          $errors,
          $help,
          $hiddenFields
        );

        return strtr($row, array(
          '%row_class%' => (count($errors) > 0) ? ' form_row_error' : '',
        ));
      }
    }

Com esta adição, cada elemento que tem saída através do método `renderRow()`
será automaticamente envolvido por um div `form_row_error` se o campo falhar na
validação.

Considerações finais
--------------

O *framework* de formulários é simultaneamente um dos mais poderosos e mais
complexos dos componentes dentro do symfony. O *trade-off* para validação de formulário,
proteção CSRF, e objetos de formulários que se estender o *framework* pode rapidamente
tornar-se uma tarefa difícil. Conhecer os detalhes do funcionamento do sistema de formulários,
é a chave para libertar todo o seu potencial. Espero que este capítulo tenha 
trazido você um passo mais perto.

O desenvolvimento futuro do *framework* de formulário será focado em preservar o poder, enquanto
diminui a complexidade e fornece mais flexibilidade para o desenvolvedor. O
*framework* de formulário está agora somente em sua infância.
