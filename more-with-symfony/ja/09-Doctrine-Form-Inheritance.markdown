Doctrine のテーブル継承の活用
==============================

*Hugo Hamon 著*

symfony 1.3 では ~Doctrine~ が正式にデフォルトの ORM となった一方、Propel の開発速度はここ数ヶ月で落ちてきています。~Propel~ プロジェクトは現在も symfony コミュニティメンバーのおかげでサポートと改善が続けられています。

Doctrine 1.2 が新たに symfony のデフォルト ORM となりました。Doctrine は Propel と比べてより使いやすく、またビヘイビア、DQL、マイグレーション、テーブル継承などの豊富な機能を備えています。

この章では~テーブル継承~とは何か、symfony 1.3 においてどう活用するかを説明します。この章では図を交えて、Doctrine のテーブル継承についてわかりやすく説明していきます。

Doctrineのテーブル継承
----------------------

あまり知られていませんが、テーブル継承は Doctrine の機能の中でもっともおもしろい機能の1つです。テーブル継承はオブジェクト指向プログラミングのクラス継承と同じように、継承したテーブルをデータベース上に作成できます。テーブル継承では 2 つ以上の情報を 1 つの親テーブルにて共有する簡単な方法を提供します。以下の図式をみるとテーブル継承の考え方がよりわかりやすいかと思います。

![Doctrine のテーブル継承の図式](http://www.symfony-project.org/images/more-with-symfony/01_table_inheritance.png "Doctrine のテーブル継承の方式")

Doctrine ではアプリケーション (パフォーマンス、アトミック性、単純性) によって、__単一__、__具象__、__カラム集約__の異なる3種類のテーブル継承の手法を提供しています。これらの手法については [Doctrine book](http://www.doctrine-project.org/documentation/1_2/ja) にて解説されており、より詳しい情報が知りたい場合はこちらにも目を通してみてください。

### 単一テーブル継承

`単一テーブル継承`はもっともシンプルな手法で、子テーブルのカラムも含めたすべてのカラムを、親となるテーブルに格納します。下記のYAMLスキーマの場合は `Professor` と `Student` テーブルのカラムも含めた単一の `Person` テーブルを作成するものです。

    [yml]
    Person:
      columns:
        first_name:
          type:           string(50)
          notnull:        true
        last_name:
          type:           string(50)
          notnull:        true

    Professor:
      inheritance:
        type:             simple
        extends:          Person
      columns:
        specialty:
          type:           string(50)
          notnull:        true

    Student:
      inheritance:
        type:             simple
        extends:          Person
      columns:
        graduation:
          type:           string(20)
          notnull:        true
        promotion:
          type:           integer(4)
          notnull:        true

単一継承では、`Student` や `Professor` モデルが作られたとしても、トップレベルの `Person` モデルに対して `specialty`、`graduation`、`promotion` といったカラムが自動的に追加されます。

![単一テーブル継承の図式](http://www.symfony-project.org/images/more-with-symfony/02_simple_tables_inheritance.png "Doctrine の単一継承方式")

この手法には重大な欠点があり、親テーブルの `Person` にはそれぞれのカラムがどのレコードの種類に対応するのか区別する手段がありません。言い換えると、`Professor` や `Student` オブジェクトのみを取得することができません。以下のコードではすべて(`Student` と `Professor` の両方)のレコードを含む `Doctrine_Collection` が返ってきます。

    [php]
    $professors = Doctrine_Core::getTable('Professor')->findAll();

単一テーブル継承は指定したオブジェクトの型でハイドレーションされるだけなのであまり実用性はありません。ですのでこの手法ついての説明はこれくらいにしておきます。

### カラム集約テーブル継承

~カラム集約テーブル継承~は単一テーブル継承と似たような手法ですが、`type` というレコードの型を特定するためのカラムが存在する点が異なります。レコードがデータベースに保存された際、`type` にクラスを特定するための値が入ります:

    [yml]
    Person:
      columns:
        first_name:
          type:           string(50)
          notnull:        true
        last_name:
          type:           string(50)
          notnull:        true

    Professor:
      inheritance:
        type:             column_aggregation
        extends:          Person
        keyField:         type
        keyValue:         1
      columns:
        specialty:
          type:           string(50)
          notnull:        true

    Student:
      inheritance:
        type:             column_aggregation
        extends:          Person
        keyField:         type
        keyValue:         2
      columns:
        graduation:
          type:           string(20)
          notnull:        true
        promotion:
          type:           integer(4)
          notnull:        true

上記の YAML スキーマでは `inheritance` の `type` という属性が ~`column_aggregation`~ になっており、さらに2つの属性が追加されています。1つめの属性は `keyField` で、各レコードの型情報を保持するために追加されるカラムの名前です。今回 `keyField` には `type` というカラム名を設定しましたが、`keyField` が指定されなかった場合にもこの名前が使われます。
2つめの属性にはレコードが `Professor` か `Student` のどちらにあたるのかを特定するための値を指定します。

![カラム集約テーブル継承の図式](http://www.symfony-project.org/images/more-with-symfony/03_columns_aggregation_tables_inheritance.png "Doctrine のカラム集約継承方式")

カラム集約継承はテーブル継承において有効な手法で、単一のテーブル(`Person`)に `type` フィールドを含むすべてのフィールドを定義します。したがって複数のテーブルを作って SQL で JOIN を行う必要はありません。下記はテーブルに問い合わせた際にどの型のオブジェクトが返ってくるかの例になります:

    [php]
    // Professor オブジェクトの Doctrine_Collection を返す
    $professors = Doctrine_Core::getTable('Professor')->findAll();

    // Student オブジェクト の Doctrine_Collection を返す
    $students = Doctrine_Core::getTable('Student')->findAll();

    // Professor オブジェクトを返す
    $professor = Doctrine_Core::getTable('Professor')->findOneBySpeciality('physics');

    // Student オブジェクトを返す
    $student = Doctrine_Core::getTable('Student')->find(42);

    // Student オブジェクトを返す
    $student = Doctrine_Core::getTable('Person')->findOneByIdAndType(array(42, 2));

サブクラス (`Professor`、`Student`) を通じて情報を取得する際、Doctrine は対応する `type` カラムの値を自動的に SQL の `WHERE` 句に対して指定します。

しかし、カラム集約にはとある状況においていくつか欠点があります。まずカラム集約では子テーブルのフィールドを`必須`にすることができません。`Person` テーブルのフィールド数次第では空の値だらけのレコードが作られる可能性もあります。

2 つめの欠点はたくさんの子テーブルやフィールドが関連づけられることです。もし、フィールドをたくさん含んだ子テーブルがいくつもあった場合、最終的に親テーブルは大量のカラムで構成されることになります。そうなるとテーブルの管理が大変になるでしょう。

### 具象テーブル継承

~具象テーブル継承~はカラム集約継承からパフォーマンスやメンテナンス性においてより歩み寄った手法です。
この手法ではまさに、子クラスごとに独立したテーブルを作成します。各テーブルは共通のカラムとそれぞれのモデルのカラムをすべて含んだ状態で作成されます。

    [yml]
    Person:
      columns:
        first_name:
          type:           string(50)
          notnull:        true
        last_name:
          type:           string(50)
          notnull:        true

    Professor:
      inheritance:
        type:             concrete
        extends:          Person
      columns:
        specialty:
          type:           string(50)
          notnull:        true

    Student:
      inheritance:
        type:             concrete
        extends:          Person
      columns:
        graduation:
          type:           string(20)
          notnull:        true
        promotion:
          type:           integer(4)
          notnull:        true

このスキーマの場合、`Professor` テーブルには `id`、`first_name`、`last_name`、そして `specialty` フィールドが含まれます。

![具象テーブル継承の図式](http://www.symfony-project.org/images/more-with-symfony/04_concrete_tables_inheritance.png "Doctrine の具象継承方式")

具象テーブル継承には先ほどの手法に比べていくつかの利点があります。まず1つめは各テーブルが分離され、それぞれが独立して作成されることです。また不要な空フィールドや `type` カラムも含まれません。その結果、それぞれのテーブルは軽くなります。

>**NOTE**
>この手法ではテーブルを結合して情報を共有するのではなく、それぞれの子テーブルにフィールドを重複して定義することにより、パフォーマンスとスケーラビリティの向上につなげています。

具象テーブル継承の2つの欠点は、共通フィールドが重複している(ただしパフォーマンスの点では利点につながる) ことと親テーブルが常に空になることです。たしかに Doctrine は使われることのないであろう `Person` テーブルを作成します。すべての情報は子テーブルに保存されるので、このテーブルに問い合わせることはないでしょう。

ここまでで 3 種類の Doctrine のテーブル継承の手法をご紹介してきましたが、symfony と組み合わせた実例はまだお見せしていません。次のパートでは symfony 1.3 においての Doctrine の~テーブル継承~の活用方法、特にフォームフレームワークでの活用について説明していきます。

Symfony とテーブル継承の統合
-----------------------------

symfony 1.3 より前までは Doctrine の~テーブル継承~は完全にサポートされておらず、フォームやフィルタクラスまでは継承されませんでした。したがってテーブル継承を使いたい場合はフォームやフィルタに修正を加える必要があり、継承の動作を実現するためにたくさんのメソッドのオーバーライドを余儀なくさせられました。

コミュニティのフィードバックによるおかげで、symfony 1.3 ではフォームとフィルタが改善され、Doctrine のテーブル継承がサポートされています。

この章での目的は Doctrine のテーブル継承の使い方と、モデルやフォーム、フィルタ、admin ジェネレータと組み合わせてさまざまな状況での活用方法を説明することです。実例をあげて、symfony での継承の働きについてよりわかりやすく、やりたいことが簡単に実現できるように説明していきましょう。

### 実例の紹介

この章の至るところで、`モデル`、`フォーム`、`フィルタ`、そして `adminジェネレータ` における Doctrine のテーブル継承のさまざまな活用方法についての実例をお見せします。

最初にご紹介する事例は、フランスでよく知られる Sensio 社で開発されたアプリケーションによるものです。いくつもの同形式なデータ集合を管理する際、メソッドやプロパティを共有し重複をさけるために、Doctrine のテーブル継承を用いて対処した例をご紹介します。

2つめの事例では、~具象継承~とフォームを組み合わせて活用し、シンプルなモデルを作成してファイルの管理を行う例をご紹介します。

最後に、3つめの事例としてアドミンジェネレータとテーブル継承を組み合わせた活用方法の実演と、それらをより柔軟に扱う方法の説明を行います。

### モデルレイヤーにおけるテーブル継承

オブジェクト指向プログラミングの概念と~テーブル継承~の情報共有のしくみは同じもので、モデルが生成された段階でメソッドやプロパティの共有が可能になります。Doctrine のテーブル継承はアクション間でのコードの共有やオーバーライドを扱うためのよりよい方法でもあります。ではこのしくみについて実例を交えて説明していきましょう。

#### 問題

多くの Web アプリケーションを機能させるためには「~リファレンシャル~」が必要です。多くの場合リファレンシャルは、少なくとも2つのフィールド (`id` と `label` など) を含むシンプルなテーブルからなる小さなデータ集合のことを指します。ときとして、リファレンシャルは `is_active` や `is_default` といったカラムを含むこともあります。今回のケースは Sencio 社で最近顧客向けに作られたアプリケーションのお話です。

顧客の要望によると、多数のデータ集合をアプリケーションの主要なビューとフォームにて管理をしたいとのことです。これらすべてのリファレンシャルは、`id`、`label`、`position`、`is_default` をもつ同じ内容のテーブルで構成されています。`position` フィールドはドラッグアンドドロップによって Ajax で並び順を変更する機能に使われます。`is_default` は HTML の選択フィールドにてデフォルトで選択状態になっているかを指し示すフラグです。

#### 解決策

2つ以上のテーブルを管理する際に、テーブル継承は最善な解決策の1つです。上記の問題は~具象継承~を用いて1つのクラスでメソッドを共有する方法がしっくりくるでしょう。下記にこの問題をあらわすスキーマがあります。

    [yml]
    sfReferential:
      columns:
        id:
          type:        integer(2)
          notnull:     true
        label:
          type:        string(45)
          notnull:     true
        position:
          type:        integer(2)
          notnull:     true
        is_default:
          type:        boolean
          notnull:     true
          default:     false

    sfReferentialContractType:
      inheritance:
        type:          concrete
        extends:       sfReferential

    sfReferentialProductType:
      inheritance:
        type:          concrete
        extends:       sfReferential

具象テーブル継承はこれで完全に動作し、テーブルは分離され、`position` フィールドはそれぞれのテーブルの中で共有されます。

モデルをビルドして、どうなっているかを見てみましょう。Doctrine と symfony はデータベース上に3つのテーブルと、`lib/model/doctrine` ディレクトリ内に6つのクラスを作りました。

  * `sfReferential`: `sf_referential` レコードを管理します。
  * `sfReferentialTable`: `sf_referential` テーブルを管理します。
  * `sfReferentialContractType`: `sf_referential_contract_type` レコードを管理します。
  * `sfReferentialContractTypeTable`: `sf_referential_contract_type` テーブルを管理します。
  * `sfReferentialProductType`: `sf_referential_product_type` レコードを管理します。
  * `sfReferentialProductTypeTable`: `sf_referential_product_type` テーブルを管理します。

`sfReferentialContractType` と `sfReferentialProductType` モデルクラスを見てみると、どちらも `sfReferential` クラスを継承していることがわかります。つまり `sfReferential` クラスに定義したメソッド (とプロパティ) は2つの子クラスでも共有され、必要であればオーバーライドすることも可能です。

これだけで目的は達成されました。`sfReferential` クラスにすべてのリファレンシャルを管理するためのメソッドを記述しましょう:

    [php]
    // lib/model/doctrine/sfReferential.class.php
    class sfReferential extends BasesfReferential
    {
      public function promote()
      {
        // レコードを上に移動させる
      }

      public function demote()
      {
        // レコードを下に移動させる
      }

      public function moveToFirstPosition()
      {
        // レコードを先頭に移動させる
      }

      public function moveToLastPosition()
      {
        // レコードを末尾に移動させる
      }

      public function moveToPosition($position)
      {
        // 指定した位置にレコードを移動させる
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

Doctrine の~具象継承~のおかげで、すべてのコードは 1 か所で管理されています。これでこのコードはデバッグや管理、改良、そしてユニットテストがやりやすくなりました。

これがテーブル継承を使った場合の 1 つめの真価です。またこのおかげで下記のような共通アクションを作ることも可能です。`sfBaseReferentialActions` はそれぞれのリファレンシャルモデルを管理するための親クラスです。

    [php]
    // lib/actions/sfBaseReferentialActions.class.php
    class sfBaseReferentialActions extends sfActions
    {
      /**
       * 一覧画面でドラッグアンドドロップからの Ajax リクエストを
       * 受けとって並び順を変更するアクション
       *
       * このアクションは ~sfDoctrineRoute~ からリファレンシャル
       * オブジェクトを受け取る
       *
       * @param sfWebRequest $request
       */
      public function executeMoveToPosition(sfWebRequest $request)
      {
        $this->forward404Unless($request->isXmlHttpRequest());

        $referential = $this->getRoute()->getObject();

        $referential->moveToPosition($request->getParameter('position', 1));

        return sfView::NONE;
      }
    }

もしテーブル継承を使わなかった場合はどうなっていたでしょうか？コードはリファレンシャルの子クラスごとに重複して作られていたことでしょう。これはいくつものリファレンシャルテーブルを扱うアプリケーションにおいて非常に DRY (Don't Repeat Yourself) ではありません。

### フォームレイヤーにおけるテーブル継承

Doctrine のテーブル継承の活用について話を続けましょう。前節ではこの機能が継承したモデル間でメソッドやプロパティを共有するとても実用的な機能であることの実演を行いました。次は symfony がフォームを作成した時の動きを見ていきましょう。

#### 事例となるモデル

下記はファイルを管理するモデルを表しています。ここでの目的は、共通の情報を `File` テーブルに、詳細な情報を `Video` や `PDF` といった子テーブルに格納することです。

    [yml]
    File:
      columns:
        filename:
          type:            string(50)
          notnull:         true
        mime_type:
          type:            string(50)
          notnull:         true
        description:
          type:            clob
          notnull:         true
        size:
          type:            integer(8)
          notnull:         true
          default:         0

    Video:
      inheritance:
        type:              concrete
        extends:           File
      columns:
        format:
          type:            string(30)
          notnull:         true
        duration:
          type:            integer(8)
          notnull:         true
          default:         0
        encoding:
          type:            string(50)

    PDF:
      tableName:           pdf
      inheritance:
        type:              concrete
        extends:           File
      columns:
        pages:
          type:            integer(8)
          notnull:         true
          default:         0
        paper_size:
          type:            string(30)
        orientation:
          type:            enum
          default:         portrait
          values:          [portrait, landscape]
        is_encrypted:
          type:            boolean
          default:         false
          notnull:         true

`PDF` と `Video` テーブルは、共通の情報を含む `File` テーブルを共有します。`Video` モデルは `format` (4/3、16/9...) や再生時間を表す `duration` といったビデオオブジェクトに関連する情報を含み、もう一方の `PDF` モデルはページ数を表す `pages` や書字方向を表す `orientation` を含みます。ではモデルと対応するフォームをビルドしましょう。

    $ php symfony doctrine:build --all

後節ではフォームクラスに新しく用意された `setupInheritance()` メソッドを用いたテーブル継承の活用方法を説明します。

#### setupInheritance() メソッドを理解する

予想どおり、Doctrine は `lib/form/doctrine` および `lib/form/doctrine/base` ディレクトリ以下に6つのフォームクラスを作ります。

  * `BaseFileForm`
  * `BaseVideoForm`
  * `BasePDFForm`

  * `FileForm`
  * `VideoForm`
  * `PDFForm`

では `Base` フォームクラスを開いて ~`setup()`~ メソッドのなかを見てみましょう。symfony 1.3 から新しく ~`setupInheritance()`~ メソッドが追加されています。このメソッドはデフォルトでは空っぽです。

ここでもっとも重要なことは、`BaseVideoForm` と `BasePDFForm` は `FileForm` と `BaseFileForm` クラスを継承しており、継承構造が保持されることです。つまり、`File` クラスを継承しているフォームはメソッドが共有できるということです。

以下のコードでは `FileForm` クラスの `setupInheritance()` メソッドをオーバーライドして、それぞれの子クラスから効率的に使えるように設定しています。

    [php]
    // lib/form/doctrine/FileForm.class.php
    class FileForm extends BaseFileForm
    {
      protected function setupInheritance()
      {
        parent::setupInheritance();

        $this->useFields(array('filename', 'description'));

        $this->widgetSchema['filename']    = new sfWidgetFormInputFile();
        $this->validatorSchema['filename'] = new sfValidatorFile(array(
          'path' => sfConfig::get('sf_upload_dir')
        ));
      }
    }

`setupInheritance()` メソッドは `VideoForm` と `PDFForm` から呼び出され、`filename` と `description` を除くすべてのフィールドを削除します。`filename` フィールドのウィジェットはファイルウィジェットに変えられ、対応するバリデータは ~`sfValidatorFile`~ バリデータに変更されています。これでファイルのアップロードとサーバーへの保存が可能になりました。

![setupInheritance() メソッドを用いて継承されたフォームをカスタマイズ](http://www.symfony-project.org/images/more-with-symfony/05_table_inheritance_forms.png "Doctrine のテーブル継承とフォーム")

#### ファイルの MIME タイプとサイズの設定

これですべてのフォームが準備できました。しかしこれらを使えるようにする前にもう1点設定が必要です。`mime_type` と `size` フィールドは `FileForm` オブジェクトから削除したので、これをプログラムのほうで設定する必要があります。この設定は`File` クラスに `generateFilenameFilename()` メソッドを作成して対応するのがよいでしょう。

    [php]
    // lib/model/doctrine/File.class.php
    class File extends BaseFile
    {
      /**
       * このファイルオブジェクトのファイル名を生成する
       *
       * @param sfValidatedFile $file
       * @return string
       */
      public function generateFilenameFilename(sfValidatedFile $file)
      {
        $this->setMimeType($file->getType());
        $this->setSize($file->getSize());

        return $file->generateFilename();
      }
    }

このメソッドは本来ファイルを保存する際のファイル名を指定するためのものです。ここではデフォルトの自動生成されたファイル名をそのまま返すようにしていますが、その際に第1引数として渡された ~`sfValidatorFile`~ オブジェクトから `mime_type` と `size` をとってきてこっそりと設定しています。

symfony 1.3 では Doctrine のテーブル継承を完全にサポートしているので、継承した値も含めてフォームの保存が可能です。ネイティブにサポートされているため、ほんの少しの変更でフォームをパワフルで機能的にすることができます。

上記の例は継承によって広い範囲を簡単に改良することができました。たとえば、`VideoForm` や `PDFForm` クラスの `filename` のバリデータを、`sfValidatorVideo` や `sfValidatorPDF` などのより特化したバリデータを作ってオーバーライドすることも可能です。

### フィルタレイヤーにおけるテーブル継承

フィルタもフォームと同じものなので、親のフォームフィルタからメソッドやプロパティが継承されます。つまり `VideoFormFilter` や `PDFFormFilter` は `FileFormFilter` オブジェクトを継承し、~`setupInheritance()`~ を使ってカスタマイズできます。

こちらも同様に、`VideoFormFilter` と `PDFFormFilter` は `FileFormFilter` にあるメソッドを共有します。

### アドミンジェネレータレイヤーにおけるテーブル継承

ここからはテーブル継承とアドミンジェネレータの新機能のひとつである、__アクションベースクラス__定義についてみていきましょう。アドミンジェネレータは symfony 1.0 からもっとも改良のくわえられた機能のひとつです。

2008年11月、symfony 1.2 に新しいアドミンジェネレータがバンドルされることを発表しました。このツールには CRUD、一覧画面でのフィルタリングやページング、一括削除などのさまざまな機能を備えています。admin ジェネレータはすべての開発者にとって、簡単で高速にアプリケーションのバックエンドを作成・カスタマイズできるツールです。

#### 実例紹介

この章の最終パートでの目的は、Doctrine のテーブル継承と admin ジェネレータを組み合わせた活用方法を説明することです。これを達成するために、シンプルなバックエンド上に、ソート/優先づけされた 2 つのテーブルを管理するものを構築しましょう。

symfony はいかなるときでも車輪の再発明は行わない主義なので、Doctrine のモデルには [csDoctrineActAsSortablePlugin](http://www.symfony-project.org/plugins/csDoctrineActAsSortablePlugin "csDoctrineActAsSortablePlugin プラグインページ") を使っていきましょう。これはオブジェクト間のソートに必要とされるすべての API を提供します。~`csDoctrineActAsSortablePlugin`~ は symfony 界隈でもっとも活発な企業の1つである CentreSource 社が開発とメンテナンスを行っています。

今回使うモデルはとてもシンプルです。`sfItem`、`sfTodoItem`、`sfShoppingItem` の3つのモデルクラスがあり、これで TODO リストと買い物リストを管理します。それぞれのリストはソート/優先づけが可能です。

    [yml]
    sfItem:
      actAs:             [Timestampable]
      columns:
        name:
          type:          string(50)
          notnull:       true

    sfTodoItem:
      actAs:             [Sortable]
      inheritance:
        type:            concrete
        extends:         sfItem
      columns:
        priority:
          type:          string(20)
          notnull:       true
          default:       minor
        assigned_to:
          type:          string(30)
          notnull:       true
          default:       me

    sfShoppingItem:
      actAs:             [Sortable]
      inheritance:
        type:            concrete
        extends:         sfItem
      columns:
        quantity:
          type:          integer(3)
          notnull:       true
          default:       1

上記のスキーマはモデルを3つに分割することを表しています。2つの子クラス (`sfTodoItem`、`sfShoppingItem`) は `Sortable` と `Timestampable` ビヘイビアを使います。`Sortable` ビヘイビアは `csDoctrineActAsSortablePlugin` に含まれており、それぞれのテーブルに `position` カラムを追加します。それぞれのクラスは `sfItem` を継承しており、このクラスには `id` と `name` カラムが含まれています。

バックエンドのなかでいくつかテストを行うためにフィクスチャを追加しましょう。データフィクスチャはいつもどおり、プロジェクトの `data/fixtures.yml` にあります。

    [yml]
    sfTodoItem:
      sfTodoItem_1:
        name:           "Write a new symfony book"
        priority:       "medium"
        assigned_to:    "Fabien Potencier"
      sfTodoItem_2:
        name:           "Release Doctrine 2.0"
        priority:       "minor"
        assigned_to:    "Jonathan Wage"
      sfTodoItem_3:
        name:           "Release symfony 1.4"
        priority:       "major"
        assigned_to:    "Kris Wallsmith"
      sfTodoItem_4:
        name:           "Document Lime 2 Core API"
        priority:       "medium"
        assigned_to:    "Bernard Schussek"

    sfShoppingItem:
      sfShoppingItem_1:
        name:           "Apple MacBook Pro 15.4 inches"
        quantity:       3
      sfShoppingItem_2:
        name:           "External Hard Drive 320 GB"
        quantity:       5
      sfShoppingItem_3:
        name:           "USB Keyboards"
        quantity:       2
      sfShoppingItem_4:
        name:           "Laser Printer"
        quantity:       1

`csDoctrineActAsSortablePlugin` プラグインをインストールして、モデルの準備ができたら、`config/ProjectConfiguration.class.php` にある ~`ProjectConfiguration`~ を書きかえてプラグインを有効化しましょう:

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

次にデータベース、モデル、フォーム、フィルタを作成し、フィクスチャを読み込ませましょう。~`doctrine:build`~ タスクを使えば一発で行えます:

    $ php symfony doctrine:build --all --no-confirmation

この処理が完了したらキャッシュのクリアと、プラグインのアセットへのリンクを `web` ディレクトリに作らなくてはなりません。

    $ php symfony cache:clear
    $ php symfony plugin:publish-assets

後節ではアドミンジェネレータを用いてバックエンドにモジュールを作る方法と、新機能であるアクションベースクラス機能の活用方法を説明します。

#### バックエンドのセットアップ

この節ではバックエンドのアプリケーションと、買い物と TODO の一覧を管理するモジュールのセットアップに必要な手順を説明していきます。まずは `backend` アプリケーションを作りましょう:

    $ php symfony generate:app backend

アドミンジェネレータはとてもすばらしいツールですが、symfony 1.3 より前までは作られたモジュール間でコードの重複を避ける手立てがありませんでした。現在は改善されており、~`doctrine:generate-admin`~ タスクに ~`--actions-base-class`~ オプションが追加されアクションの親クラスを指定できるようになっています。

2つのモジュールは似たような内容なので、共通のコードは重複しないようにすべきです。これらのモジュールの親となるアクションクラスを `lib/actions` ディレクトリに作って、共通のコードはここに書いていきましょう:

    [php]
    // lib/actions/sfSortableModuleActions.class.php
    class sfSortableModuleActions extends sfActions
    {

    }

`sfSortableModuleActions` を作り、キャッシュをクリアしたら、backend アプリケーションに2つのモジュールを作ります:

    $ php symfony doctrine:generate-admin --module=shopping --actions-base-class=sfSortableModuleActions backend sfShoppingItem

-

    $ php symfony doctrine:generate-admin --module=todo --actions-base-class=sfSortableModuleActions backend sfTodoItem

アドミンジェネレータはモジュールに対して2つのディレクトリを作ります。1つはもちろん `apps/backend/modules` ディレクトリです。しかし、大部分のモジュールのファイルは `cache/backend/dev/modules` ディレクトリに作られます。これらのファイルはモジュールのコンフィギュレーションが変更されたりキャッシュが削除された際に再構築されます。
アドミンジェネレータはモジュールに対して2つのディレクトリを作ります。1つはもちろん `apps/backend/modules` ディレクトリです。しかし、モジュールのファイルの大半は `cache/backend/dev/modules` ディレクトリに作られます。これらのファイルはモジュールのコンフィギュレーションが変更されたりキャッシュが削除された際に再構築されます。

>**Note**
>キャッシュファイルの中身を見ることは、symfony と admin ジェネレータの挙動を知るよい方法です。`sfSortableModuleActions` の子クラスも `cache/backend/dev/modules/autoShopping/actions/actions.class.php` と `cache/backend/dev/modules/autoTodo/actions/actions.class.php` に作られています。デフォルトではこれらは ~`sfActions`~ を直接継承します。

![デフォルトの TODO リスト](http://www.symfony-project.org/images/more-with-symfony/06_table_inheritance_backoffice_todo_1.png "デフォルトの TODO リスト")

![デフォルトの買い物リスト](http://www.symfony-project.org/images/more-with-symfony/07_table_inheritance_backoffice_shopping_1.png "デフォルトの買い物リスト")

これで 2 つのモジュールを扱うための準備が整いました。この章の目的ではありませんが、自動生成されたモジュールのコンフィギュレーションを探ってみましょう。[symfony リファレンス](http://www.symfony-project.org/reference/1_3/ja/06-Admin-Generator)の中にあるドキュメントがとても参考になります。

### アイテムの並び順を変更する

前節では同じアクションクラスを継承する2つのモジュールのセットアップを行いました。次の目的はそれぞれの一覧でオブジェクトをソートするためのアクションを作ることです。これは先ほどインストールしたプラグインの API を使えば簡単に作れます。

まずレコードを上下させるためのルートを新しく作りましょう。アドミンジェネレータは ~`sfDoctrineRouteCollection`~ を使っていますので、それぞれのモジュールの `config/generator.yml` にて簡単に追加できます。

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

`todo` モジュールにも同じ変更を加えましょう:

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

この2つのYAMLファイルは `shopping` と `todo` モジュールのコンフィギュレーションに相当します。やりたいことはこれで設定できました。まず一覧のビューは `position` カラムの`昇順`にソートされます。次にパジネーションを避けるために 1 ページに表示するアイテムの最大数を 100 に増やしました。

そして、一覧には `position`、`name`、`priority`、`assigned_to`、`quantity` のみを表示するようにしました。さらにそれぞれのモジュールに `moveUp` と `moveDown` アクションを追加しました。最終的には以下のスクリーンショットのように表示されているはずです:

![カスタマイズされたバックエンドの TODO リスト](http://www.symfony-project.org/images/more-with-symfony/09_table_inheritance_backoffice_todo_2.png "カスタマイズされたバックエンドの TODO リスト")

![カスタマイズされたバックエンドの買い物リスト](http://www.symfony-project.org/images/more-with-symfony/08_table_inheritance_backoffice_shopping_2.png "カスタマイズされたバックエンドの買い物リスト")

2 つのアクションはまだ宣言されただけで何も手をつけてません。後述のように、共通の `sfSortableModuleActions` アクションクラスにアクションを作る必要があります。ここでは ~`csDoctrineActAsSortablePlugin`~ プラグインの提供する2つの便利なメソッド、`promote()` と `demote()` メソッドを使って、`moveUp` と `moveDown` アクションを実装します。

    [php]
    // lib/actions/sfSortableModuleActions.class.php
    class sfSortableModuleActions extends sfActions
    {
      /**
       * アイテムを上に移動する
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
       * アイテムを下に移動する
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

これらの共通のアクションにより、TODO リストと買い物リストは共にソート可能になりました。その上、メンテナンスや機能テストが行いやすくなりました。

#### 特別な贈り物: ユーザーエクスペリエンスを改善する

これで終わらせる前に、ユーザーエクスペリエンスを改善していきましょう。リンクをクリックしてレコードを上下させるのは、直感的とはいえません。よりよい方法は Ajax で動作させることです。今回は jQuery の ~`Table Drag and Drop`~ プラグインを使って、HTML のテーブルをドラッグアンドドロップして行えるようにしましょう。テーブルの各行が移動した際に、毎回 Ajax を呼び出すようにします。

まずは jQuery を入手して `web/js` ディレクトリ以下にインストールし、同じようにして `Table Drag and Drop` プラグインのインストールも行います。このプラグインのソースコードは [Google Code](http://code.google.com/p/tablednd/) で管理されています。

これらを動作させるために、それぞれの一覧のビューに小さな JavaScript のスニペットと、各テーブルに `id` 属性を追加します。admin ジェネレータのテンプレートとパーシャルはすべてオーバーライド可能で、cache ディレクトリ内にある `_list.php` を各モジュールにコピーしましょう。

しかし、単純に `templates/` ディレクトリに `_list.php` をコピーしてくることは DRY ではありません。ここでは `cache/backend/dev/modules/autoShopping/templates/_list.php` を `_table.php` という名前に変えて `apps/backend/templates/` ディレクトリにコピーしましょう。コピーしてきたファイルを以下のように置き換えます:

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

そうしたら、各モジュールの `templates` ディレクトリに `_list.php` を作り、それぞれ以下のように記述します:

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

並び順を変更するためには、Ajax からのリクエストを受け付けるアクションを新たに実装する必要があります。`executeMove()` アクションを `sfSortableModuleActions` アクションクラスに作りましょう。

    [php]
    // lib/actions/sfSortableModuleActions.class.php
    class sfSortableModuleActions extends sfActions
    {
      /**
       * Ajax リクエストを受けてアイテムの並び順の変更を行う
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

`executeMove()` アクションはコンフィギュレーションオブジェクトから `getModel()` メソッドを呼び出しています。このメソッドを `todoGeneratorConfiguration` と `shoppingGeneratorConfiguration` クラスに対して以下のように追加しましょう:

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

やるべきことは残すところ1つです。テーブルの各行をドラッグアンドドロップ可能にすることと、ドロップした際に Ajax を動作させることです。そのためには各` move` アクションに対応するルートが必要になります。では `apps/backend/config/routing.yml` ファイルを開いて以下のように2つのルートを追加しましょう:

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

ここでは重複を避けるために、`foreach` 文にそれぞれのモジュール名を渡してルートを作っています。あとはドラッグアンドドロップと Ajax リクエストを動作させるために `apps/backend/templates/_table.php` に JavaScript を記述しましょう:

    [php]
    <script type="text/javascript" charset="utf-8">
      $().ready(function() {
        $("#sf_item_table").tableDnD({
          onDrop: function(table, row) {
            var rows = table.tBodies[0].rows;

            // 移動したアイテムのIDを取得
            var movedId = $(row).find('td input:checkbox').val();

            // 新しい順番の計算
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

これで HTML のテーブルは完全に機能を満たしました。各行はドラッグアンドドロップが可能になり、Ajax による並び順の更新が行えます。わずかな修正によりバックエンドのユーザビリティは改善され、ユーザーエクスペリエンスを向上することができました。admin ジェネレータは機能拡張やカスタマイズをするための十分な柔軟性を備え、Doctrine のテーブル継承も完全に動作しています。

もう使われない `moveUp` と `moveDown` アクションは削除したり、お好きなようにカスタマイズして、このモジュールを思うがままに改良してみましょう。

最後に
-------

この章では、コードの構成の改善や開発速度の向上に非常に役立つ Doctrine の~テーブル継承~機能についてご紹介してきました。この機能は symfony にも十分に統合されています。
ぜひこれらの機能を活用して、コードの改善や効率化を行っていきましょう。
