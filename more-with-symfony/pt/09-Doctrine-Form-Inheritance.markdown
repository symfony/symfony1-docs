Vantagens da Herança de tabelas do Doctrine
===========================================

*por Hugo Hamon*

No symfony 1.3, o ~Doctrine~ tornou-se, oficialmente, a biblioteca ORM padrão 
enquanto houve queda com o desenvolvimento utilizando o Propel nos últimos meses. 
O suporte e melhorias ao projeto ~Propel~ ainda continuam graças aos esforços dos membros da comunidade symfony.

O projeto Doctrine 1.2 tornou-se a nova biblioteca ORM padrão symfony por
ser mais fácil de usar que Propel e por possuir uma grande quantidade de recursos
incluindo comportamentos (*behaviors*), facilidade nas consultas DQL, migrações (*migrations*) e herança de tabelas.

Este capítulo descreve o que é essa herança de tabelas e como agora ela está
totalmente integrada no symfony 1.3. Graças a um exemplo do mundo real, esse capítulo
irá ilustrar como tirar vantagem da herança de tabelas do Doctrine para tornar o código mais
flexível e organizado.

Herança de tabelas do Doctrine
--------------------------

Apesar de não ser muito conhecida e utilizada por muitos desenvolvedores, a herança da tabelas é, 
provavelmente, uma das características mais interessantes do Doctrine. 
A herança de tabelas permite ao desenvolvedor criar tabelas que herdam de outras, 
da mesma forma que as classes herdam em uma linguagem de programação orientada à objeto.
Herança de tabelas fornece uma maneira fácil de compartilhar dados entre duas ou mais tabelas
em uma única super tabela. Olhe para o diagrama abaixo para entender melhor o princípio de herança de tabelas.

![Esquema de Herança de Tabelas do Doctrine](http://www.symfony-project.org/images/more-with-symfony/01_table_inheritance.png "Princípio de Herança de tabelas do Doctrine")

Doctrine oferece três estratégias diferentes para gerenciar herança de tabelas 
dependendo das necessidades da aplicação (desempenho, atomicidade, simplicidade, ... ):
__simple__, __column aggregation__ e herança de tabelas __concrete__. Embora todas estas
estratégias estarem descritas no
[Livro do Doctrine](http://www.Doctrine-project.org/documentation/1_2/en), alguma
explicação adicional ajudará a entender melhor as opções e em que
circunstâncias elas são úteis.

### A Estratégia Simples de Herança de tabelas

A estratégia de herança simples é a mais elementar de todas, uma vez que armazena todas as
colunas, incluindo as colunas das tabelas filhas, na super tabela pai. Se o
esquema do modelo for semelhante ao seguinte código YAML, o Doctrine irá gerar uma
única tabela `Pessoa`, que inclui as colunas das tabelas `Professor` e `Aluno`.

    [yml]
    Pessoa:
      columns:
        nome:
          type: string(50)
          notnull: true
        sobrenome:
          type: string(50)
          notnull: true

    Professor:
      inheritance:
        type: simple
        extends: Pessoa
      columns:
        especialidade:
          type: string(50)
          notnull: true

    Aluno:
      inheritance:
        type: simple
        extends: Pessoa
      columns:
        graduacao:
          type: string(20)
          notnull: true
        promocao:
          type: integer(4)
          notnull: true


Com a estratégia de herança simples, as colunas extras `especialidade`, `graduacao` e `promocao` são automaticamente registradas no nível superior no modelo de `Pessoa`, mesmo que o Doctrine gere uma classe de modelo para cada tabela `Aluno` e `Professor`.

![Esquema simples de herança de tabelas](http://www.symfony-project.org/images/more-with-symfony/02_simple_tables_inheritance.png "Princípio de herança simples do Doctrine")

Esta estratégia tem uma importante desvantagem com a super tabela `Pessoa` 
que não fornece qualquer coluna para identificar cada tipo de registro. 
Em outras palavras, não há nenhuma maneira de obter apenas os objetos `Professor` ou `Aluno`. 
A seguinte instrução Doctrine retorna um `Doctrine_Collection` de todos os 
registros da tabela (`Aluno` e `Professor`).

    [php]
    $professores = Doctrine::getTable('Professor')->findAll();

A estratégia de herança simples da tabela não é exemplo realmente útil no mundo real
como normalmente precisamos selecionar e hidratar os objetos de
um tipo específico. Conseqüentemente, não será abordado neste capítulo.

### A Estratégia Herança de Tabelas de Agregação de Coluna

A estratégia de herança de tabelas de agregação de coluna é semelhante à estratégia 
de herança simples, exceto que inclui uma coluna `tipo`, para identificar os diferentes 
tipos de registros. Consequentemente, quando um registro é mantido no banco de dados, 
um valor para o tipo é adicionado nele a fim de armazenar a classe a qual pertence.

    [yml]
    Pessoa:
      columns:
        nome:
          type: string(50)
          notnull: true
        sobrenome:
          type: string(50)
          notnull: true

    Professor:
      inheritance:
        type: column_aggregation
        extends: Pessoa
        keyField: type
        keyValue: 1
      columns:
        especialidade:
          type: string(50)
          notnull: true

    Aluno:
      inheritance:
        type: column_aggregation
        extends: Pessoa
        keyField: tipo
        keyValue: 2
      columns:
        graduacao:
          type: string(20)
          notnull: true
        promocao:
          type: integer(4)
          notnull: true

No esquema YAML acima, o tipo de herança foi alterado para
~`column_aggregation`~ e dois novos atributos foram adicionados. O primeiro
atributo, `keyField`, especifica a coluna que será criada para armazenar o
tipo de informação para cada registro. O `keyField` é uma coluna string coluna
chamada `type`, que é o nome padrão da coluna, se nenhum `keyField` for especificado.
O segundo atributo define o valor do tipo para cada registro que pertence às classes 
`Professor` ou `Aluno`.

![Esquema de herança de tabelas Agregação de Coluna](http://www.symfony-project.org/images/more-with-symfony/03_columns_aggregation_tables_inheritance.png "Princípio da agregação de coluna do Doctrine")

A estratégia de agregação de coluna é um bom método para herança de tabelas, uma vez que
cria uma única tabela (`Pessoa`), contendo todos os campos definidos mais o campo `type`. 
Por conseguinte, não há necessidade de criar várias tabelas e fazer joins com uma 
consulta SQL. Abaixo estão alguns exemplos de como realizar consultas nas tabelas 
e que tipo de resultados serão retornados:

    [php]
    //Retorna um objeto Doctrine_Collection do Professor
    $professores = Doctrine::getTable('Professor')->findAll();

    //Retorna um objeto Doctrine_Collection do Aluno
    $alunos = Doctrine::getTable('Aluno')->findAll();

    //Retorna um objeto professor
    $professor = Doctrine::getTable('Professor')->findOneBySpeciality('física');

    //Retorna um objeto Aluno
    $aluno = Doctrine::getTable('Aluno')->find(42);

    //Retorna um objeto Aluno
    $aluno = Doctrine::getTable('Pessoa')->findOneByIdAndType(array(42, 2));

Ao recuperar os dados de uma subclasse (`Professor`, `Aluno`), o 
Doctrine anexará automaticamente a cláusula SQL `WHERE` para a consulta na
coluna `type` com o valor correspondente.

No entanto, existem algumas desvantagens de usar a estratégia de agregação de coluna em 
determinados casos. Primeiro, a agregação de coluna impede que cada sub-campo da tabela 
seja definido como `obrigatório` (`required`). Dependendo de quantos campos existirem, a tabela `Pessoa`
pode conter vários registros com valores vazios.

A segunda desvantagem está relaciona com o número de sub-tabelas e campos. 
Se o esquema declara várias sub-tabelas, que por sua vez, declara vários campos, 
a super tabela final será composta de um número muito grande de colunas. 
Por conseqüência, a tabela pode ser mais difícil de manter.

### A Estratégia de Herança Concreta

A estratégia de herança concreta de tabelas é um bom meio-termo entre as
vantagens da estratégia de agregação de coluna, desempenho e manutenibilidade.
Na verdade, esta estratégia cria tabelas independentes para cada subclasse contendo 
todas as colunas: as próprias e as compartilhadas independente do modelo.

    [yml]
    Pessoa:
      columns:
        nome:
          type: string(50)
          notnull: true
        sobrenome:
          type: string(50)
          notnull: true

    Professor:
      inheritance:
        type: concrete
        extends: Pessoa
      columns:
        specialty:
          type: string(50)
          notnull: true

    Aluno:
      inheritance:
        type: concrete
        extends: Pessoa
      columns:
        graduacao:
          type: string(20)
          notnull: true
        promocao:
          type: integer(4)
          notnull: true

Assim, para o esquema anterior, a tabela gerada para `Professor` conterá o
seguinte conjunto de campos: `id`, `nome`, `sobrenome` e `especialidade`.

![Esquema de herança concreta de tabelas](http://www.symfony-project.org/images/more-with-symfony/04_concrete_tables_inheritance.png "Princípio da herança concreta do Doctrine")

Esta abordagem tem várias vantagens em relação as estratégias anteriores. A primeira
é que todas as tabelas são isoladas e permanecem independentes umas das outras. 
Além disso, não há mais campos em branco e a coluna extra `type` não é incluída. 
O resultado é que cada tabela será mais leve e isolada uma das outras.

>**NOTE**
>O fato de que os campos comuns são duplicados em sub-tabelas é um ganho para o
>desempenho e a escalabilidade já que o Doctrine não precisa criar um SQL automático 
>juntando tudo em uma super tabela para recuperar registros compartilhados pertencentes a uma sub-tabela.

As únicas duas desvantagens da estratégia de herança concreta de tabelas são a 
duplicação de campos compartilhados (embora a duplicação geralmente seja a chave para o desempenho) 
e o fato que a super tabela gerada estará sempre vazia. Na verdade, o Doctrine gerou 
uma tabela `Pessoa`, apesar de que ela não será preenchida ou referenciada por qualquer consulta. 
Nenhuma consulta será realizada na tabela já que tudo é armazenado nas sub-tabelas.

Nós só tivemos tempo para introduzir as três estratégias de Herança de tabelas do Doctrine, 
mas nós ainda não exercitamos em um exemplo do mundo real com o symfony.
A parte seguinte deste capítulo explica como tirar proveito da Herança de tabelas
com o Doctrine no symfony 1.3, especialmente no modelo e nos formulários.

Integração da Herança de tabelas no symfony
-----------------------------------------

Antes do symfony 1.3, a herança de tabelas não era totalmente suportada pela
estrutura das classes de formulários e filtros que não herdavam corretamente da 
classe base. Consequentemente, os programadores que precisavam usar a herança de 
tabelas eram forçados a ajustar os formulários e os filtros e foram obrigados a 
sobrescrever vários métodos para obter o comportamento de herança.

Graças ao feedback da comunidade, a equipe do núcleo do symfony melhorou os 
formulários e os filtros para suportar a herança de tabelas facilmente e completamente 
no symfony 1.3.

O restante deste capítulo irá explicar como usar herança de tabelas do Doctrine
e como aproveitá-la em várias situações, inclusive em modelos, formulários, filtros e 
geradores de interface administrativa. Exemplos de estudo de caso reais vão nos ajudar a 
entender melhor como a herança funciona com o symfony para que você utilize facilmente 
em suas necessidades.

### Introdução aos Estudos de Caso Reais

Ao longo deste capítulo, vários estudos de caso do mundo real serão apresentados
expondo as muitas vantagens da abordagem da herança de tabelas do Doctrine em diversos 
níveis: em `modelos`, `formulários`, `filtros` e no `gerador de interface administrativa`.

O primeiro exemplo vem de uma aplicação desenvolvida pela Sensio
para uma empresa francesa bem conhecida. Ele mostra como a herança de tabela com o Doctrine é uma 
boa solução para gerenciar dezenas de conjuntos de dados referenciais idênticos, a fim de
compartilhar métodos e propriedades e evitar a reescrita de código.

O segundo exemplo mostra como tirar proveito da estratégia de herança concreta de tabelas com formulários, 
criando um modelo simples para gerenciar inúmeros arquivos.

Finalmente, o terceiro exemplo irá demonstrar como tirar vantagem da herança de 
tabelas com o gerador de interface administrativa, e como torná-lo mais flexível.

### Herança de tabelas na Camada do Modelo

Similar ao conceito de Programação Orientada à Objetos, a herança de tabelas
encoraja o compartilhamento de dados. Conseqüentemente, permite o compartilhamento de atributos
e métodos quando se trata dos modelos gerados. A herança de tabelas do Doctrine
é uma boa maneira de compartilhar e substituir as ações que podem ser chamadas em objetos herdados. 
Vamos explicar este conceito com um exemplo do mundo real.

#### O Problema ####

Muitas aplicações web exigem dados "referenciais" para funcionar. Um 
referencial é geralmente um pequeno conjunto de dados representados por uma tabela simples
contendo pelo menos dois campos (por exemplo, `id` e `label`). Em alguns casos, porém,
o referencial contém dados adicionais, como um `is_active` ou uma tag `is_default`.
Este foi o caso recentemente na Sensio com uma aplicação de cliente.

O cliente queria administrar um grande conjunto de dados, que envolvia os principais
formulários e visões da aplicação. Todas estas tabelas referenciais foram construídas
em torno do mesmo modelo básico: `id`, `label`, `position` e `is_default`. O
campo `position` ajuda a classificar os registros, graças a uma função ajax *arraste e solte* (*drag and drop*). 
O campo `is_default` é um sinalizador que indica se um registro deve ou não estar 
"selecionado" por padrão, quando ele preenche um elemento HTML de caixa para seleção *dropdown*.

#### A Solução ####

O gerenciamento de mais do que duas tabelas iguais é um dos melhores problemas para resolver com
herança de tabelas. No problema acima, a herança das tabelas concreta 
foi selecionada para se adequar às necessidades e compartilhar os métodos de cada objeto em uma
única classe. Vamos dar uma olhada no seguinte esquema simplificado, que
ilustra o problema.

    [yml]
    sfReferential:
      columns:
        id:
          type: integer(2)
          notnull: true
        label
          type: string(45)
          notnull: true
        position:
          type: integer(2)
          notnull: true
        is_default:
          type: boolean
          notnull: true
          default: false

    sfReferentialContractType:
      inheritance:
        type: concrete
        extends: sfReferential

    sfReferentialProductType:
      inheritance:
        type: concrete
        extends: sfReferential

A herança concreta de tabelas funciona perfeitamente aqui, pois disponibiliza as tabelas separadas e
isoladas, e porque o campo `position` deve ser gerenciado pelos registros
que compartilham o mesmo tipo.

Vamos construir o modelo e ver o que acontece. O Doctrine e symfony geraram
SQL para três tabelas e seis classes de modelo no diretório `lib/model/doctrine`:

  * `SfReferential`: gerencia os registros `sf_referential`,
  * `SfReferentialTable`: gerencia a tabela `sf_referential`,
  * `SfReferentialContractType`: gerencia os registros `sf_referential_contract_type`.    
  * `SfReferentialContractTypeTable`: gerencia a tabela `sf_referential_contract_type`.
  * `SfReferentialProductType: gerencia os registros `sf_referential_product_type`.
  * `SfReferentialProductTypeTable: gerencia a tabela `sf_referential_product_type`.

Explorando a herança gerada vemos que ambas as classes base do
`sfReferentialContractType` e o modelo `sfReferentialProductType` herdaram
da classe `sfReferential`. Assim, todos os métodos protegidos e públicos (incluindo
propriedades) inseridos na classe `sfReferential` serão compartilhado entre as duas
subclasses e poderão ser sobrescritos, se necessário.

Isso é exatamente o objetivo esperado. A classe `sfReferential` agora pode conter
métodos para gerenciar todos os dados referenciais, como por exemplo:

    [php]
    //Lib/modelo Doctrine/sfReferential.class.php
    class sfReferential extends BasesfReferential
    {
      public function promote()
      {
        //Mover o registo para cima na lista
      }

      public function demote()
      {
        // Mover o registro para baixo na lista
      }

      public function moveToFirstPosition()
      {
        // Mover o registro para a primeira posição
      }

      public function moveToLastPosition()
      {
        // Mover o registro para a última posição
      }

      public function moveToPosition($position)
      {
        // Mover o registro para uma determinada posição
      }

      public function makeDefault($forceSave = true, $conn = null)
      {
        $this->setIsDefault(true);

        if ($forceSave)
        {
          $this->save($conn);
        }
      }
    }

Graças à Herança concreta de tabelas do Doctrine, todo o código é compartilhado em
um mesmo lugar. O código se torna mais fácil de depurar, manter, melhorar e realizar testes unitários.

Essa é a primeira vantagem real quando se trata de herança de tabelas. 
Além disso, graças a esta abordagem, os objetos do modelo podem ser usados para 
centralizar as ações de código como no exemplo abaixo. 
O `sfBaseReferentialActions` é uma classe de ações especiais herdada por cada 
classe de ações que gera um modelo referencial.

    [php]
    // lib/actions/sfBaseReferentialActions.class.php
    class sfBaseReferentialActions extends sfActions
    {
      /**
       * Ação ajax que salva a nova posição, como resultado do usuário
       * utilizando um arraste e solte na visualização da lista.
       *
       * Esta ação está relacionada graças a um ~sfDoctrineRoute~ que
       * facilita a recuperação de objeto referencial único.
       *
       * @param sfWebRequest $request
       */
      public function executeMoveToPosition(sfWebRequest $request)
      {
        $referential = $this->getRoute()->getObject();

        $referential->moveToPosition($request->getParameter('position', 1));

        return sfView::NONE;
      }
    }

O que aconteceria se o esquema não usasse a herança de tabelas? O código
precisaria ser repetido em cada subclasse referencial. Esta abordagem não 
contempla o conceito DRY, (*Don't Repeat Yourself*, ou não faça retrabalho), 
especialmente para uma aplicação com uma dúzia de tabelas referenciais.

### Herança de tabelas na camada dos Formulários ###

Vamos continuar o nosso *tour* pelas vantagens da Herança de tabelas do Doctrine.
A seção anterior demonstrou como esse recurso pode ser muito útil para
compartilhar métodos e propriedades entre vários modelos herdados. Agora vamos dar uma olhada
na forma como ele se comporta quando ao lidar com formulários gerados pelo symfony.

#### O Modelo do Estudo de Caso ####

O esquema YAML abaixo descreve um modelo para gerenciar inúmeros documentos. 
O objetivo é armazenar informações genéricas na tabela arquivos e dados específicos 
nas sub-tabelas `Vídeo` e `PDF`.

    [yml]
    Arquivo
      columns:
        nome_do_arquivo:
          type: string(50)
          notnull: true
        tipo_do_arquivo:
          type: string(50)
          notnull: true
        descricao:
          type: clob
          notnull: true
        tamanho:
          type: integer(8)
          notnull: true
          default: 0

    Video:
      inheritance:
        type: concrete
        extends: Arquivo
      columns:
        formato:
          type: string(30)
          notnull: true
        duracao:
          type: integer(8)
          notnull: true
          default: 0
        codificacao:
          type: string(50)

    PDF:
      tableName: pdf
      inheritance:
        type: concrete
        extends: Arquivo
      columns:
        paginas:
          type: integer(8)
          notnull: true
          default: 0
        tamanho_da_pagina:
          type: string(30)
        orientacao
          type: enum
          default: retrato
          values: [retrato, paisagem]
        esta_criptografado:
          type: boolean
          default: false
          notnull: true

Tanto a tabela `PDF` quanto a `Video` compartilham a mesma tabela `Arquivo`, que 
contém informações globais sobre os inúmeros arquivos. O modelo `Video` encapsula os dados 
relacionados à objetos de vídeo, tais como `formato` (4/3, 16/9 ...) ou `duracao`, enquanto  
o modelo `PDF` contém o número de `paginas` ou `orientacao` do documento. 
Vamos construir esse modelo e gerar os formulários correspondentes.

    $php symfony doctrine:build --all

A seção seguinte descreve como tirar vantagem da herança de tabelas
em classes de formulário graças ao novo método ~`setupInheritance()`~, método 
de configuração da herança.

### Descobrindo o método ~setupInheritance()~ ###

Como esperado, o Doctrine gerou seis classes de formulários nos diretórios 
`lib/form/doctrine` e `form/lib/doctrine/base`:

  * `BaseArquivoForm`
  * `BaseVideoForm`
  * `BasePDFForm`

  * `ArquivoForm`
  * `Videoform`
  * `PDFForm`

Vamos abrir as três classes `Base` dos formulário para descobrir algo novo no
método ~`setup()`~. Um novo método ~`setupInheritance()`~ foi adicionado pelo
symfony 1.3. Este método está vazio por padrão.

A coisa mais importante a notar é que a herança está presente nos formulários
`BaseVideoForm` e `BasePDFForm` ambos estendendo as classes `ArquivoForm` e `BaseArquivoForm`. 
Consequentemente, cada classe herdará da classe `Arquivo` e poderá compartilhar os mesmos métodos base.

A listagem a seguir sobrescreve o método `setupInheritance()` e configura 
a classe `ArquivoForm` para que ela possa ser usada em qualquer subformulário de modo mais eficaz.

    [php]
    // Lib/form/Doctrine/ArquivoForm.class.php
    class ArquivoForm extends BaseArquivoForm
    {
      protected function setupInheritance()
      {
        parent:: setupInheritance();

        $this->useFields(array('nome_do_arquivo', 'descricao'));

        $this->widgetSchema['nome_do_arquivo'] = new sfWidgetFormInputFile();
        $this->validatorSchema['nome_do_arquivo'] = new sfValidatorFile(array(
          'path' => sfConfig::get('sf_upload_dir')
        ));
      }
    }

O método `setupInheritance()`, que é chamado pelas subclasses `VideoForm` e
`PDFForm`, remove todos os campos, exceto `nome_do_arquivo` e `descricao`.
O widget do campo `nome_do_arquivo` foi transformado em um widget de arquivo e seu
validador correspondente foi alterado para um validador ~`sfValidatorFile`~.
Desta forma, o usuário será capaz de carregar um arquivo e salvá-lo no servidor.

![Formulários herdados com o método setupInheritance() personalizado](http://www.symfony-project.org/images/more-with-symfony/05_table_inheritance_forms.png "Formulários com herança de tabelas do Doctrine")

#### Configurando o tipo e tamanho do arquivo atual

Todos os formulários estão prontos e personalizados. No entanto, há mais uma coisa para 
configurar antes de poder utilizá-los. Como os campos `mime_type` e `tamanho` foram removidos 
do objeto `ArquivoForm`, eles devem ser definidos automaticamente. O melhor lugar 
para fazer isso é em um novo método `generateFilenameFilename()` na classe `Arquivo`.

    [php]
    // lib/model/doctrine/Arquivo.class.php
    class Arquivo extends BaseArquivo
    {
      / **
       * Gera um nome para o arquivo do objeto atual.
       *
       * @param sfValidatedFile $file
       * @return string
       */
      public function generateFilenameFilename(sfValidatedFile $file)
      {
        $this->setMimeType($file->GetType());
        $this->setSize($file->getSize());

        return $file->generateFilename();
      }
    }

Este novo método tem como objetivo gerar um nome personalizado para o arquivo para armazena-lo no
sistema de arquivos. Embora o método `generateFilenameFilename()` retorne, por padrão, 
o nome do arquivo gerado automaticamente, que também define o `mime_type` e `tamanho` do objeto, 
graças ao objeto ~`sfValidatedFile`~ passado como o primeiro argumento.

Devido ao symfony 1.3 ter suporte total à herança de tabelas do Doctrine, os formulários são 
agora capazes de salvar um objeto e seus valores herdados. O suporte à herança nativa
permite formulários poderosos e funcionais, com poucas linhas de código.

O exemplo acima pode ser amplamente e facilmente melhorado graças à herança da classe. 
Por exemplo, tanto as classes `Videoform` quanto `PDFForm` podem
substituir o validador `nome_do_arquivo` para validadores personalizados mais específicos, 
tais como `sfValidatorVideo` ou `sfValidatorPDF`.

### Herança de tabelas na Camada de Filtros ###

Devido aos filtros serem como os formulários, eles também herdam os métodos e propriedades dos
filtros dos formulários pai. Consequentemente, os objetos `VideoFormFilter` e `PDFFormFilter`
herdam de `ArquivoFormFilter` e podem ser personalizados usando
o método ~`setupInheritance()`~.

Da mesma forma, tanto o `VideoFormFilter` quanto o `PDFFormFilter` podem compartilhar os mesmos
métodos personalizados da classe `ArquivoFormFilter`.

### Herança de tabelas na Camada do Gerador de Interface Administrativa ###

Agora vamos descobrir como tirar proveito da Herança de tabelas do Doctrine
bem como das novas funcionalidades do Gerador de Interface Administrativa: a definição __actions base class__ . 
O Gerador de Interface Administrativa é uma das características que mais cresceu 
desde a versão symfony 1.0.

Em novembro de 2008, o symfony introduziu o novo sistema do Gerador de Interface Administrativa junto com a 
versão 1.2. Esta ferramenta vem com várias funcionalidades extras, como operações CRUD básicas, a lista de filtragem e paginação, exclusão em lote e assim por diante... 
O Gerador de Interface Administrativa é uma ferramenta poderosa, que facilita e acelera 
a geração de *backend* e a personalização para qualquer desenvolvedor.

#### Exemplo prático de Introdução

O objetivo da última parte deste capítulo é ilustrar como tirar proveito da herança 
de tabelas do Doctrine juntamente com o Gerador de Interface Administrativa. Para 
conseguir isso, uma simples área de *backend* será construída para gerenciar  
duas tabelas, que contêm dados que podem ser ordenados/priorizados.

Como o mantra symfony é para não reinventar a roda toda vez, o modelo do Doctrine
usará o [csDoctrineActAsSortablePlugin](http://www.symfony-project.org/plugins/csDoctrineActAsSortablePlugin "Página do plugin csDoctrineActAsSortablePlugin")
para fornecer todas a API necessária para classificar os objetos entre si. O
plugin ~`csDoctrineActAsSortablePlugin`~ é desenvolvido e mantido pela
CentreSource, uma das empresas mais ativas no ecossistema symfony.

O modelo de dados é bastante simples. Há três classes de modelo, `sfItem`,
`sfTodoItem` e `sfShoppingItem`, que ajudam a gerenciar uma lista de tarefas e uma
lista de compras. Cada item em ambas as listas é classificável para permitir que os itens sejam
priorizados dentro da lista.

    [yml]
    sfItem:
      actAs: [Timestampable]
      columns:
        name:
          type: string(50)
          notnull: true

    sfTodoItem:
      actAs: [Sortable]
      inheritance:
        type: concrete
        extends: sfItem
      columns:
        priority:
          type: string(20)
          notnull: true
          default: minor
        assigned_to:
          type: string(30)
          notnull: true
          default: me

    sfShoppingItem:
      actAs: [Sortable]
      inheritance:
        type: concrete
        extends: sfItem
      columns:
        quantity:
          type: integer(3)
          notnull: true
          default: 1

O esquema acima descreve o modelo de dados dividido em três classes de modelo. As duas
classes filhas (`sfTodoItem`, `sfShoppingItem`), usam comportamentos `Sortable` e 
`Timestampable`. O comportamento `Sortable` é fornecido pelo
plugin `csDoctrineActAsSortablePlugin` e adiciona uma coluna `position` do tipo inteiro para
cada tabela. Ambas as classes herdam da classe base `sfItem`. Esta classe contém as colunas
`id` e `name`.

Vamos adicionar alguns dados para testarmos o funcionamento
do backend. Os dados *fixtures*, como de costume, estão localizados no
arquivo `data/fixtures.yml` do projeto symfony.

    [yml]
    sfTodoItem:
      sfTodoItem_1:
        name: "Escrever um novo livro symfony"
        priority: "medium"
        assigned_to: "Fabien Potencier"
      sfTodoItem_2:
        name: "Release Doctrine 2.0"
        priority: "minor"
        assigned_to: "Jonathan Wage"
      sfTodoItem_3:
        name: "Release symfony 1.4"
        priority: "major"
        assigned_to: "Kris Wallsmith"
      sfTodoItem_4:
        name: "Documento Lime Core 2 API"
        priority: "medium"
        assigned_to: "Bernard Schussek"

    sfShoppingItem:
      sfShoppingItem_1:
        name: "MacBook Apple Pro de 15.4 polegadas"
        quantity: 3
      sfShoppingItem_2:
        name: "Disco rígido externo 320 GB"
        quantity: 5
      sfShoppingItem_3:
        name: "Teclados USB"
        quantity: 2
      sfShoppingItem_4:
        name: "impressora laser"
        quantity: 1

Uma vez que o plugin `csDoctrineActAsSortablePlugin` está instalado e os dados
do modelo estão prontos, o novo plugin precisa ser ativado na classe ~`ProjectConfiguration`~
localizada em `config/ProjectConfiguration.class.php`:

    [php]
    class ProjectConfiguration extends sfProjectConfiguration
    {
      public function setup()
      {
        $this->enablePlugins(array(
          'sfDoctrinePlugin',
          'csDoctrineActAsSortablePlugin'
        ));
      }
    }

Em seguida, o banco de dados, o modelo, os formulários e os filtros podem ser gerados e
os dados *fixtures* carregados no banco de dados para alimentar as tabelas recém-criadas.
Isso pode ser feito de uma só vez, graças a tarefa ~`doctrine:build`~

    $ php symfony doctrine:build --all --no-confirmation

O cache symfony deve ser limpo e os *assets* do plugin linkados no diretório `web` para concluir o processo:

    $ php symfony cache:clear
    $ php symfony plugin:publish-assets

A seção seguinte explica como construir os módulos do backend com a
ferramenta Gerador de Interface Administrativa e como tirar proveito do novo recurso de ações da classe base.

#### Configurando o Backend

Esta seção descreve os passos necessários para instalar a nova aplicação backend
contendo dois módulos gerados para gerenciar tanto as listas do shopping quanto do todo.
Por conseguinte, a primeira coisa a fazer é gerar uma aplicação `backend` 
para conter os módulos:

    $ php symfony generate:app backend

O Gerador de Interface Administrativa é uma poderosa ferramenta, antes do symfony 1.3, o 
desenvolvedor era forçado a duplicar código comum entre os módulos gerados. 
Agora, porém, a tarefa ~`doctrine:generate-admin`~ introduz uma nova opção 
~`--actions-base-class`~  que permite ao desenvolvedor definir a classe base das ações para o módulo.

Como os dois módulos são ligeramente semelhantes, eles certamente vão precisar compartilhar alguns
códigos de ações genéricas. Este código pode ser colocado em uma super classe de ações localizada
no diretório `lib/actions/`, como mostrado no código abaixo:

    [php]
    // lib/actions/sfSortableModuleActions.class.php
    class sfSortableModuleActions extends sfActions
    {

    }

Uma vez que a nova classe `sfSortableModuleActions` é criada e está no cache
os dois módulos podem ser gerados com a aplicação backend:

    $ php symfony doctrine:generate-admin --module=shopping --actions-base-class=sfSortableModuleActions backend sfShoppingItem

-

    $ php symfony doctrine:generate-admin --module=todo --actions-base-class=sfSortableModuleActions backend sfTodoItem

O Gerador de Interface Administrativa gera módulos em duas listas separadas. O
primeiro diretório é, naturalmente, `apps/backend/modules`. A maioria dos
arquivos gerados pelo módulo, no entanto, estão localizados no diretório `cache/backend/dev/modules`. 
Arquivos localizados neste local são regenerados toda vez que o cache
for limpo ou quando forem modificadas as configurações do módulo.

>**Note**
>Percorrer os arquivos em cache é uma ótima maneira de entender como symfony e o
>Gerador de Interface Administrativa trabalham juntos internamente. Consequentemente, as
>novas subclasses do `sfSortableModuleActions` podem ser encontradas em
>`cache/backend/dev/modules/autoShopping/actions/actions.class.php` e
>`cache/backend/dev/modules/autoTodo/actions/actions.class.php. Por padrão, o
>symfony gera essas classes para herdar diretamente de ~`sfActions`~.

![Backend padrão do lista todo](http://www.symfony-project.org/images/more-with-symfony/06_table_inheritance_backoffice_todo_1.png "Lista backend todo padrão")

![Backend padrão da lista de compras](http://www.symfony-project.org/images/more-with-symfony/07_table_inheritance_backoffice_shopping_1.png "Lista Shopping backend padrão")

Os dois módulos de backend estão prontos para serem utilizados e personalizados. Não é a meta
deste capítulo, no entanto, explorar a configuração dos módulos auto-gerados. 
Existe documentação relevante sobre esse assunto, inclusive no
[Livro de referência do symfony](http://www.symfony-project.org/reference/1_3/en/06-Admin-Generator).

#### Alterando a posição de um item

A seção anterior descreve como configurar dois módulos backend totalmente funcionais, 
tanto que herdam ações da mesma classe. A próxima meta é criar uma ação compartilhada, 
que permite ao desenvolvedor classificar os objetos a partir de uma lista entre si. 
Como o plugin instalado fornece uma API completa para reordenar 
os objetos, isto é muito fácil de fazer.

O primeiro passo é a criação de duas novas rotas capazes de mover um registo para cima 
ou para baixo na lista. Como o *Gerador de Interface Administrativa* usa a rota 
~`sfDoctrineRouteCollection`~, novas rotas podem ser facilmente declaradas à 
coleção através do `config/generator.yml` de ambos os módulos:

    [yml]
    # apps/backend/modules/shopping/config/generator.yml
    generator:
      class: sfDoctrineGenerator
      param:
        model_class:           sfShoppingItem
        theme:                 admin
        non_verbose_templates: true
        with_show:             false
        singular:              ~
        plural:                ~
        route_prefix:          sf_shopping_item
        with_doctrine_route:   true
        actions_base_class:    sfSortableModuleActions

        config:
          actions: ~
          fields:  ~
          list:
            max_per_page:      100
            sort:              [position, asc]
            display:           [position, name, quantity]
            object_actions:
              moveUp:          { label: "move up", action: "moveUp" }
              moveDown:        { label: "move down", action: "moveDown" }
              _edit:      ~
              _delete:    ~
          filter:  ~
          form:    ~
          edit:    ~
          new:     ~

As mudanças precisam ser repetidas para o módulo `todo`:

    [yml]
    # apps/backend/modules/todo/config/generator.yml
    generator:
      class: sfDoctrineGenerator
      param:
        model_class:           sfTodoItem
        theme:                 admin
        non_verbose_templates: true
        with_show:             false
        singular:              ~
        plural:                ~
        route_prefix:          sf_todo_item
        with_doctrine_route:   true
        actions_base_class:    sfSortableModuleActions

        config:
          actions: ~
          fields:  ~
          list:
            max_per_page:      100
            sort:              [position, asc]
            display:           [position, name, priority, assigned_to]
            object_actions:
              moveUp:          { label: "move up", action: "moveUp" }
              moveDown:        { label: "move down", action: "moveDown" }
              _edit:      ~
              _delete:    ~
          filter:  ~
          form:    ~
          edit:    ~
          new:     ~


Os dois arquivos YAML descrevem as configurações para ambos os módulos `shopping` e `todo`. 
Cada um foi customizado para atender às necessidades do usuário final. Primeiro, a 
exibição da lista é ordenada pela coluna `position` de forma ascendente. Em seguida, 
o número máximo de itens por página foi aumentado para 100 para evitar a paginação.

Finalmente, o número de colunas exibidas foi reduzido para somente `position`, `name`, 
`priority`, `assigned_to` e `quantity`. Além disso, cada módulo tem duas novas ações: 
`moveUp` e `moveDown`. A apresentação final deverá ser parecida com as imagens a seguir:

![Backend da lista todo personalizada](http://www.symfony-project.org/images/more-with-symfony/09_table_inheritance_backoffice_todo_2.png "Lista personalizada de todo no backend")

![Backend da lista de compras personalizada](http://www.symfony-project.org/images/more-with-symfony/08_table_inheritance_backoffice_shopping_2.png "Lista de compras personalizada do backend")

Essas duas novas ações foram declaradas, mas ainda não fazem nada. Cada uma
deve ser criada na classe de ações compartilhadas, `sfSortableModuleActions`, tal como descrito abaixo. 
O plugin extra ~`csDoctrineActAsSortablePlugin`~ fornece dois métodos úteis em cada 
classe do modelo: `promote()` e `demote()`. Cada uma é usada para construir as ações `moveUp` e `moveDown`.

    [php]
    // lib/actions/sfSortableModuleActions.class.php
    class sfSortableModuleActions extends sfActions
    {
      /**
       * Moves an item up in the list.
       *
       * @param sfWebRequest $request
       */
      public function executeMoveUp(sfWebRequest $request)
      {
        $this->item = $this->getRoute()->getObject();

        $this->item->promote();

        $this->redirect($this->getModuleName());
      }

      /**
       * Moves an item down in the list.
       *
       * @param sfWebRequest $request
       */
      public function executeMoveDown(sfWebRequest $request)
      {
        $this->item = $this->getRoute()->getObject();

        $this->item->demote();

        $this->redirect($this->getModuleName());
      }
    }

Graças a estas duas ações compartilhadas, tanto a lista todo quando a de compras são
classificáveis. Além disso, elas são fáceis de manter e realizar testes funcionais.
Sinta-se livre para alterar a aparência dos módulos, reescrever os métodos, substituindo as 
ações do objeto modelo, removendo os links `move up` e `move down`.

#### Presente Especial: Melhorando a Experiência do Usuário

Antes de terminar, vamos ilustrar as duas ações para melhorar a experiência do usuário.
Todos concordam que mover um registro para cima (ou para baixo), clicando em um link não é
realmente intuitivo para o usuário final. Uma melhor abordagem seria incluir
comportamentos JavaScript Ajax. Neste caso, todas as linhas de tabela HTML serão arrastáveis
e, isso é possível graças ao plugin `Table Drag and Drop` do jQuery. Uma requisição Ajax 
será feita sempre que o usuário mover uma linha na tabela HTML.

Primeiro baixe e instale o framework jQuery no diretório `web/js` e repita a operação 
para o plugin `Table Drag and Drop`, cujo código-fonte está hospedado em um repositório do [Google Code](http://code.google.com/p/tablednd/).

Para funcionar, a view da lista de cada módulo deverá incluir um pequeno código JavaScript
e ambas as tabelas precisam de um atributo `id`. Como todos os templates do Gerador de Interface Administrativa 
e partials podem ser sobrescritos, o arquivo `_list.php`, localizado no cache por 
padrão, deve ser copiado para ambos os módulos.

Mas espere, copiando o arquivo `_list.php` para o diretório `templates/` de cada
módulo você estará quebrando o conceito DRY. Basta copiar o arquivo do cache `/backend/dev/modules/autoShopping/templates/_list.php`
para o `apps/backend/templates/` e renomeá-lo para `_table.php`.
Vamos sobrescrever o conteúdo atual com o seguinte código:

    [php]
    <div class="sf_admin_list">
      <?php if (!$pager->getNbResults()): ?>
        <p><?php echo __('No result', array(), 'sf_admin') ?></p>
      <?php else: ?>
        <table cellspacing="0" id="sf_item_table">
          <thead>
            <tr>
              <th id="sf_admin_list_batch_actions"><input id="sf_admin_list_batch_checkbox" type="checkbox" onclick="checkAll();" /></th>
              <?php include_partial(
                $sf_request->getParameter('module').'/list_th_tabular',
                array('sort' => $sort)
              ) ?>
              <th id="sf_admin_list_th_actions">
                <?php echo __('Actions', array(), 'sf_admin') ?>
              </th>
            </tr>
          </thead>
          <tfoot>
            <tr>
              <th colspan="<?php echo $colspan ?>">
                <?php if ($pager->haveToPaginate()): ?>
                  <?php include_partial(
                    $sf_request->getParameter('module').'/pagination',
                    array('pager' => $pager)
                  ) ?>
                <?php endif; ?>
                <?php echo format_number_choice(
                  '[0] no result|[1] 1 result|(1,+Inf] %1% results', 
                  array('%1%' => $pager->getNbResults()),
                  $pager->getNbResults(), 'sf_admin'
                ) ?>
                <?php if ($pager->haveToPaginate()): ?>
                  <?php echo __('(page %%page%%/%%nb_pages%%)', array(
                    '%%page%%' => $pager->getPage(), 
                    '%%nb_pages%%' => $pager->getLastPage()), 
                    'sf_admin'
                  ) ?>
                <?php endif; ?>
              </th>
            </tr>
          </tfoot>
          <tbody>
          <?php foreach ($pager->getResults() as $i => $item): ?>
            <?php $odd = fmod(++$i, 2) ? 'odd' : 'even' ?>
            <tr class="sf_admin_row <?php echo $odd ?>">
              <?php include_partial(
                $sf_request->getParameter('module').'/list_td_batch_actions',
                array(
                  'sf_'. $sf_request->getParameter('module') .'_item' => $item,
                  'helper' => $helper
              )) ?>
              <?php include_partial(
                $sf_request->getParameter('module').'/list_td_tabular', 
                array(
                  'sf_'. $sf_request->getParameter('module') .'_item' => $item
              )) ?>
                <?php include_partial(
                  $sf_request->getParameter('module').'/list_td_actions',
                  array(
                    'sf_'. $sf_request->getParameter('module') .'_item' => $item, 
                    'helper' => $helper
                )) ?>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      <?php endif; ?>
      </div>
      <script type="text/javascript">
        /* <![CDATA[ */
        function checkAll() {
          var boxes = document.getElementsByTagName('input'); 
          for (var index = 0; index < boxes.length; index++) { 
            box = boxes[index]; 
            if (
              box.type == 'checkbox' 
              && 
              box.className == 'sf_admin_batch_checkbox'
            ) 
            box.checked = document.getElementById('sf_admin_list_batch_checkbox').checked 
          }
          return true;
        }
        /* ]]> */
      </script>

Finalmente, crie um arquivo `_list.php` dentro do diretório `templates` de cada módulo,
e coloque o seguinte código em cada um:

    [php]
    // apps/backend/modules/shopping/templates/_list.php
    <?php include_partial('global/table', array(
      'pager' => $pager,
      'helper' => $helper,
      'sort' => $sort,
      'colspan' => 5
    )) ?>
    
-

    // apps/backend/modules/shopping/templates/_list.php
    <?php include_partial('global/table', array(
      'pager' => $pager,
      'helper' => $helper,
      'sort' => $sort,
      'colspan' => 8
    )) ?>

Para alterar a posição de uma linha, é necessário implementar uma nova ação em ambos os módulos 
que processa a requisição Ajax. Como visto antes, a nova ação compartilhada
`executeMove()` será colocada na classe das ações `sfSortableModuleActions`:

    [php]
    // lib/actions/sfSortableModuleActions.class.php
    class sfSortableModuleActions extends sfActions
    {
      /**
       * Performs the Ajax request, moves an item to a new position.
       *
       * @param sfWebRequest $request
       */
      public function executeMove(sfWebRequest $request)
      {
        $this->forward404Unless($request->isXmlHttpRequest());
        $this->forward404Unless($item = Doctrine_Core::getTable($this->configuration->getModel())->find($request->getParameter('id')));

        $item->moveToPosition((int) $request->getParameter('rank', 1));

        return sfView::NONE;
      }
    }

A ação `executeMove()` exige o método `getModel()` no objeto de configuração. 
Implemente este novo método, tanto na classe `todoGeneratorConfiguration` quanto na
`shoppingGeneratorConfiguration` como a seguir:

    [php]
    // apps/backend/modules/shopping/lib/shoppingGeneratorConfiguration.class.php
    class shoppingGeneratorConfiguration extends BaseShoppingGeneratorConfiguration
    {
      public function getModel()
      {
        return 'sfShoppingItem';
      }
    }

-

    // apps/backend/modules/todo/lib/todoGeneratorConfiguration.class.php
    class todoGeneratorConfiguration extends BaseTodoGeneratorConfiguration
    {
      public function getModel()
      {
        return 'sfTodoItem';
      }
    }

Há uma última operação pendente. Até agora, as linhas de tabelas não são arrastáveis 
e nenhuma requisição ajax é executada quando uma linha movida é liberada. Para 
conseguir isso, ambos os módulos precisam de uma rota específica para acessar suas 
ações correspondentes `move`. Consequentemente, o arquivo `apps/backend/config/routing.yml` 
necessita das novas rotas seguintes:

    [php]
    <?php foreach (array('shopping', 'todo') as $module) : ?>

    <?php echo $module ?>_move:
      class: sfRequestRoute
      url: /<?php echo $module ?>/move
      param:
        module: "<?php echo $module ?>"
        action: move
      requirements:
        sf_method: [get]

    <?php endforeach ?>

Para evitar a duplicação de código, as duas rotas são geradas dentro de um `foreach`
e serão baseadas no nome do módulo para recuperá-las facilmente na exibição.
Finalmente, o `apps/backend/templates/_table.php` deve implementar o código JavaScript, 
a fim de inicializar o comportamento arraste e solte (*drag and drop*) e as requisições 
ajax correspondentes:

    [php]
    <script type="text/javascript" charset="utf-8">
      $().ready(function() {
        $("#sf_item_table").tableDnD({
          onDrop: function(table, row) {
            var rows = table.tBodies[0].rows;

            // Get the moved item's id
            var movedId = $(row).find('td input:checkbox').val();

            // Calculate the new row's position
            var pos = 1;
            for (var i = 0; i<rows.length; i++) {
              var cells = rows[i].childNodes;
              // Perform the ajax request for the new position
              if (movedId == $(cells[1]).find('input:checkbox').val()) {
                $.ajax({
                  url:"<?php echo url_for('@'. $sf_request->getParameter('module').'_move') ?>?id="+ movedId +"&rank="+ pos,
                  type:"GET"
                });
                break;
              }
              pos++;
            }
          },
        });
      });
    </script>

A tabela HTML está agora totalmente funcional. As linhas são "arraste e solte" (*draggable e droppable*) e
a nova posição de uma linha é automaticamente salva graças a uma requisição AJAX. 
Com apenas poucos pedaços de código, a usabilidade do backend foi significativamente melhorada 
para oferecer ao usuário final uma experiência melhor. O Gerador de Interface Administrativa
é suficientemente flexível para ser estendido e personalizado e funciona perfeitamente 
com a herança de tabela do Doctrine.

Sinta-se livre para melhorar os dois módulos, removendo as duas ações obsoletas `moveUp` e
`moveDown` e acrescentando outras customizações que se ajustem às suas necessidades.

Considerações finais
--------------

Este capítulo descreveu como a Herança de Tabelas do Doctrine é um recurso poderoso,
que ajuda o desenvolvedor a codificar mais rápido e melhor além de organizar o código. 
Essa funcionalidade do Doctrine é totalmente integrada em diversos níveis no symfony.
Os desenvolvedores são encorajados a aproveitá-la para aumentar a eficiência e
promover a organização de código.
