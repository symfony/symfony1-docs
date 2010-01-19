高度なフォーム
==============

*Ryan Weaver、Fabien Potencier 著*

symfony のフォームフレームワークを使うと、開発者は、オブジェクト指向的な手法で簡単にフォームデータを表示し、バリデートできます。ORM によって提供される ~`sfFormDoctrine`~ クラスや ~`sfFormPropel`~ クラスのおかげで、 データレイヤーに密接に関連するフォームの表示やデータの保存を簡単に行えます。

しかし実際には、開発者はしばしばフォームをカスタマイズしたり拡張しなくてはなりません。この章では、いくつかのよくある高度なフォームの問題について説明し、実装していきます。また、~`sfForm`~ オブジェクトの内部にも触れ、謎を解き明かします。

ミニプロジェクト: 製品と写真
---------------------------

最初の課題として、個々の製品と、製品ごとに枚数に制限のない写真の編集について取り上げます。ユーザーは、同一のフォームで製品 (Product) の情報と製品の写真 (ProductPhoto) の両方を編集できる必要があります。また、製品に対する新しい写真を一度に 2 枚までアップロードできるようにします。ここでは次のようなスキーマを使います:

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

この章を完了すると、次のようなフォームが表示されます:

![製品と写真フォーム](http://www.symfony-project.org/images/more-with-symfony/advanced_forms_01.png "埋め込み製品写真フォームのある製品フォーム")

例からより多くを学ぶ
-------------------

高度なテクニックを学ぶには、例に沿って順番にテストしていくことが重要です。[symfony](#chapter_03) の `--installer` 機能を使うと、SQLite データベース、Doctrine データベーススキーマ、フィクスチャー、`frontend` アプリケーション、`product` モジュールを使える準備が整った作業用のプロジェクトを簡単に構築できます。インストーラーを [script](http://www.symfony-project.org/images/more-with-symfony/advanced_form_installer.php.src) からダウンロードし、次のコマンドを実行して symfony プロジェクトを作ってください:

    $ php symfony generate:project advanced_form --installer=/path/to/advanced_form_installer.php

このコマンドにより、前の節で示したデータベーススキーマが設定された完全に動作するプロジェクトが作られます。

>**NOTE**
>この章で使うファイルのパスは、上のタスクで作られた Doctrine を使っている symfony プロジェクトに合わせています。

基本フォームのセットアップ
-------------------------

要件には 2 つの異なるモデル (`Product` と `ProductPhoto`) への変更処理が含まれているので、2 つの異なるフォーム (`ProductForm` と `ProductPhotoForm`) を結合する必要があります。幸い、フォームフレームワークの ~`sfForm::embedForm()`~ を使うことで、複数のフォームを簡単に 1 つに結合できます。まず、`ProductPhotoForm` のみをセットアップします。この例では、`filename` フィールドをファイルアップロードフィールドとして使います:

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

このフォームでは、`caption` フィールドと `filename` フィールドは自動的に必須になりますが、それぞれの理由は異なります。`caption` フィールドは、対応するデータベーススキーマで `notnull` プロパティが `true` に設定されているため、必須になります。`filename` フィールドは、バリデーターオブジェクトの `required` オプションのデフォルト値が `true` なので必須になります。

>**NOTE**
>~`sfForm::useFields()`~ は symfony 1.3 の新しい機能で、フォームでどのフィールドを使い、どの順番で表示するのかを厳密に指定できます。HIDDEN でなく、このメソッドに指定されていない他のフィールドは、フォームから削除されます。
    
ここまでは、通常のフォームのセットアップと変わりません。次は、これらのフォームを 1 つに結合します。

フォームを埋め込む
-----------------

~`sfForm::embedForm()`~ を使うと、個別の `ProductForm` フォームと `ProductPhotoForms` フォームをほとんど手間をかけずに結合できます。作業は常に*メインの*フォームに対して行います。ここでは `ProductForm` フォームがメインです。今回の要件では、一度に 2 枚まで製品の写真をアップロードできるよう求められています。この要件に従うために、2 つの `ProductPhotoForm` オブジェクトを `ProductForm` に埋め込みます:

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

ブラウザーで `product` モジュールにアクセスすると、`Product` オブジェクトの編集と同時に 2 つの `ProductPhoto` をアップロードできるようになっています。写真を選択してフォームを送信すると、symfony により新しい `ProductPhoto` オブジェクトが自動的に保存され、対応する `Product` オブジェクトにリンクされます。`ProductPhotoForm` で定義されているファイルアップロードについても、通常通り実行されます。

レコードがデータベースに正しく保存されていることを確認してみましょう:

    $ php symfony doctrine:dql --table "FROM Product"
    $ php symfony doctrine:dql --table "FROM ProductPhoto"

`ProductPhoto` テーブルで、写真のファイル名に注意してください。データベースにある名前のファイルが `web/uploads/products/` ディレクトリに存在すれば、すべては期待通りに動作しています。

>**NOTE**
>ファイル名フィールドとキャプションフィールドは `ProductPhotoForm` で必須なので、新しい写真を 2 枚ともアップロードしない限りメインフォームでのバリデーションは失敗します。後の節でこの問題を解決していきます。

リファクタリング
----------------

ここまでのフォームは期待通りに動作しましたが、よりテストしやすく、再利用しやすいようにコードをリファクタリングしておきましょう。

まず、すでに記述したコードをもとに、`ProductPhotoForm` のコレクションをあらわす新しいフォームを作ります:

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

このフォームでは次の 2 つのオプションを使います:

 * `product`: `ProductPhotoForm` のコレクションを作る対象の製品オブジェクト。

 * `size`: 作成する `ProductPhotoForm` の数 (デフォルトは 2)。

では、`ProductForm` の configure メソッドを次のように変更します:

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

sfForm オブジェクトの内部を調べる
-------------------------------

基本的な意味において、Web フォームとはブラウザに表示され、サーバーに送り返されるフィールドのコレクションです。同じように、~`sfForm`~ オブジェクトはフォーム*フィールド*の配列です。~`sfForm`~ はフォームの処理を管理し、個々のフィールドはそれ自身のレンダリングとバリデーションの定義を担当します。

symfony では、2 つの異なるオブジェクトで各フォーム*フィールド*を定義します:

  * *ウィジェット* フォームフィールドの XHTML マークアップを出力します。
  * *バリデーター* 送信されたフィールドデータをクリーンアップし、バリデートします。

>**TIP**
>symfony では、*ウィジェット*は XHTML マークアップを出力するだけのオブジェクトとして定義されています。もっとも一般的なフォームで使用する場合、ウィジェットオブジェクトを作り、マークアップを出力します。

### フォームは配列

~`sfForm`~ オブジェクトは、"基本的にはフォーム*フィールド*の配列である"ということを思い出してください。より厳密に言うと、`sfForm` にはフォームのすべてのフィールドに対応するウィジェットの配列とバリデーターの配列が格納されています。これらの 2 つの配列は、それぞれ `widgetSchema` および `validatorSchema` と呼ばれ、`sfForm` クラスのプロパティになっています。フォームにフィールドを追加するには、単純にフィールドのウィジェットを `widgetSchema` 配列に追加し、フィールドのバリデーターを `validatorSchema` 配列に追加するだけです。 たとえば、次のコードではフォームに `email` フィールドを追加しています:

    [php]
    public function configure()
    {
      $this->widgetSchema['email'] = new sfWidgetFormInputText();
      $this->validatorSchema['email'] = new sfValidatorEmail();
    }

>**NOTE**
>`widgetSchema` 配列と `validatorSchema` 配列は、実際にはそれぞれ ~`sfWidgetFormSchema`~ と ~`sfValidatorSchema`~ と呼ばれる特殊なクラスで、`ArrayAccess` インターフェイスを実装しています。

### `ProductForm` の内部を調べる

`ProductForm` クラスは `sfForm` を継承しているので、すべてのウィジェットとバリデーターが、このクラスの `widgetSchema` 配列と `validatorSchema` 配列に格納されています。最終的な `ProductForm` オブジェクトで、これらの配列の中身がどのようになっているのかを見てみましょう。

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
>実際には、`widgetSchema` と `validatorSchema` は配列として振る舞うオブジェクトです。上の `newPhotos` キー、`0` キー、`1` キーに格納されている配列は、それぞれ `sfWidgetSchema` オブジェクトと `sfValidatorSchema` オブジェクトです。

予想したとおり、基本的なフィールド (`id`、`name`、`price`) が各配列の最初の階層にあります。別のフォームを埋め込んでいないフォームの `widgetSchema` 配列と `validatorSchema` 配列は、フォームの基本的なフィールドを表す単一の階層のみで構成されます。埋め込みフォームのウィジェットとバリデーターは、上のように `widgetSchema` および `validatorSchema` の子配列となります。この処理を管理するメソッドについては、次の節で説明します。

### ~`sfForm::embedForm()`~ の内側

フォームはウィジェットの配列、およびバリデーターの配列で構成されていることを覚えておいてください。あるフォームを別のフォームに埋め込むということは、フォームのウィジェットとバリデーターの配列が、メインフォームのウィジェットとバリデーターの配列に追加されるということです。この処理は、すべて `sfForm::embedForm()` によって行われます。結果は、上で見たように `widgetSchema` 配列および `validatorSchema` 配列に多次元で追加されます。

以降では、個々の `ProductPhotoForm` オブジェクトを 1 つにまとめる `ProductPhotoCollectionForm` のセットアップについて説明します。この中間フォームは"ラッパー"フォームとして動作し、全体のフォームを構成するのに役立ちます。`ProductPhotoCollectionForm::configure()` 内の次のコードから説明を始めましょう:

    [php]
    $form = new ProductPhotoForm($productPhoto);
    $this->embedForm($i, $form);

`ProductPhotoCollectionForm` フォーム自身は新しい `sfForm` オブジェクトです。したがって、`widgetSchema` 配列、および `validatorSchema` 配列は空です。

    [php]
    widgetSchema    => array()
    validatorSchema => array()

しかし、各 `ProductPhotoForm` にはすでに 3 つのフィールド (`id`、`filename`、 `caption`) があり、対応する 3 つの要素が `widgetSchema` 配列、および `validatorSchema` 配列にあります。

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

~`sfForm::embedForm()`~ メソッドでは、空の `ProductPhotoCollectionForm` オブジェクトの `widgetSchema` 配列と `validatorSchema` 配列に、各 `ProductPhotoForm` の `widgetSchema` 配列と `validatorSchema` 配列を単純に追加します。 

ラッパーフォーム (`ProductPhotoCollectionForm`) への埋め込み処理が完了すると、ラッパーフォームの `widgetSchema` 配列と `validatorSchema` 配列は多次元配列になり、2 つの `ProductPhotoForm` のウィジェットとバリデーターが格納されています。

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

この処理の最後のステップでは、ここまでで生成されたラッパーフォームの `ProductPhotoCollectionForm` を、直接 `ProductForm` に埋め込みます。これは、`ProductPhotoCollectionForm` 内部の処理結果を利用すると、`ProductForm::configure()` で次のようにするだけです:

    [php]
    $form = new ProductPhotoCollectionForm(null, array(
      'product' => $this->getObject(),
      'size'    => 2,
    ));

    $this->embedForm('newPhotos', $form);

これで、上で示した最終的な `widgetSchema` 配列と `validatorSchema` 配列の構造が得られます。`embedForm()` メソッドの処理は、次のように手作業で `widgetSchema` 配列と `validatorSchema` 配列を結合するのとほとんど同じだということに気づくでしょう:

    [php]
    $this->widgetSchema['newPhotos'] = $form->getWidgetSchema();
    $this->validatorSchema['newPhotos'] = $form->getValidatorSchema();

埋め込みフォームをビューでレンダリングする
----------------------------------------

`product` モジュール用の現在の `_form.php` テンプレートは、次のようになっています:

    [php]
    // apps/frontend/module/product/templates/_form.php
    <!-- ... -->

    <tbody>
      <?php echo $form ?>
    </tbody>

    <!-- ... -->

`<?php echo $form ?>` ステートメントはフォームを表示するもっとも簡単な方法で、複雑なフォームの場合にも使えます。これはプロトタイプを開発する際に便利ですが、通常はレイアウトを変更し、独自の表示ロジックに置き換えるでしょう。この行を削除して置き換えていきます。

埋め込みフォームをビューでレンダリングする際に理解しておくべきもっとも重要なことは、前の節で説明した多次元の `widgetSchema` 配列の構成です。この例では、`ProductForm` の基本的なフィールドである `name` と `price` をビューでレンダリングするところから説明を始めます:

    [php]
    // apps/frontend/module/product/templates/_form.php
    <?php echo $form['name']->renderRow() ?>

    <?php echo $form['price']->renderRow() ?>

    <?php echo $form->renderHiddenFields() ?>

名前から分かるとおり、`renderHiddenFields()` によりフォームの HIDDEN フィールドがレンダリングされます。

>**NOTE**
>ここでは、アクションのコードについては特に注意する点がないため、意図的に示していません。`apps/frontend/modules/product/actions/actions.class.php` にあるアクションファイルを見てください。アクションのファイルは通常の CRUD と同じで、`doctrine:generate-module` タスクで生成できます。

すでに学んだように、`sfForm` クラスにはフィールドを定義している `widgetSchema` 配列と `validatorSchema` 配列が格納されています。さらに、`sfForm` クラスは PHP 5 ネイティブの `ArrayAccess` インターフェイスを実装しているので、上で見たように配列のキーを使う構文でフォームのフィールドに直接アクセスできます。

フィールドを出力するには、フィールドに直接アクセスして `renderRow()` メソッドを呼び出します。ところで、`$form['name']` はどの種類のオブジェクトなのでしょうか? `name` フィールドに対応する `sfWidgetFormInputText` ウィジェットだと想像するかもしれませんが、答えは若干異なります。

### ~`sfFormField`~ を使って各フォームフィールドをレンダリングする

`sfForm` によって、各フォームクラスで定義された `widgetSchema` 配列および `validatorSchema` 配列から、`sfFormFieldSchema` と呼ばれる 3 つめの配列が自動生成されます。この配列には、フィールドの出力を担うヘルパークラスとして動作する特殊なオブジェクトが、フィールドごとに格納されています。これは ~`sfFormField`~ クラスのオブジェクトで、各フィールドのウィジェットとバリデーターを組み合わせて自動的に作られます。

    [php]
    <?php echo $form['name']->renderRow() ?>

上のスニペットで、`$form['name']` は `sfFormField` オブジェクトです。このオブジェクトには、`renderRow()` メソッドや、いくつかのレンダリングに役立つ機能があります。

### sfFormField クラスのレンダリングメソッド

各 `sfFormField` オブジェクトは、フィールドのすべての側面、例えばフィールド自身、ラベル、エラーメッセージなどを簡単にレンダリングするのに使えます。`sfFormField` にある役立つメソッドを以下にいくつか紹介します。他の多くのメソッドについては、[symfony 1.3 API](http://www.symfony-project.org/api/1_3/sfFormField) を参照してください。

 * `sfFormField->render()`: フィールドのウィジェットオブジェクトを使って、正しい値のフォームフィールド (例えば `input` や `select`) をレンダリングします。

 * `sfFormField->renderError()`: フィールドのバリデーターを使って、フィールドのバリデーションエラーをレンダリングします。

 * `sfFormField->renderRow()`: すべてを網羅: ラベル、フォームフィールド、エラーおよびヘルプメッセージを、XHTML マークアップラッパー内にレンダリングします。

>**NOTE**
>実際には、`sfFormField` クラスの各レンダリングメソッドは、フォームの `widgetSchema` プロパティの情報も使います。`widgetSchema` プロパティは `sfWidgetFormSchema` オブジェクトで、フォームのすべてのウィジェットの情報が格納されています。`sfFormField` クラスは、各フィールドの `name` 属性や `id` 属性の生成をサポートし、各フィールドのラベルを追跡し、`renderRow()` で使用する XHTML マークアップを定義します。

`formFieldSchema` 配列が、フォームの `widgetSchema` 配列、および `validatorSchema` 配列の構造を常にミラーすることは重要です。たとえば、完成した `ProductForm` の `formFieldSchema` 配列は、次のような構造になります。配列のキーは、ビューで各フィールドをレンダリングするのに使います:

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

### 新しい ProductForm をレンダリングする

上の配列を参考にすると `sfFormField` オブジェクトの適切な格納位置が分かるので、埋め込んだ `ProductPhotoForm` のフィールドをビューで簡単に出力できます:

    [php]
    // apps/frontend/module/product/templates/_form.php
    <?php foreach ($form['newPhotos'] as $photo): ?>
      <?php echo $photo['caption']->renderRow() ?>
      <?php echo $photo['filename']->renderRow() ?>
    <?php endforeach; ?>

このブロックは 2 回ループします: 1 回目は `0` キーのフォームフィールド配列に対して、2 回目は `1` キーのフォームフィールド配列に対してです。上の図で見たように、各配列に格納されているオブジェクトは `sfFormField` オブジェクトで、他のフィールドと同じように出力できます。

オブジェクトフォームを保存する
-----------------------------

ほとんどの場合、フォームは 1 つ以上のデータベーステーブルと関連しており、送信されたデータに基づいて関連するテーブルのデータを変更します。symfony により、各スキーマモデルに対するフォームオブジェクトは自動生成されます。フォームオブジェクトは、使っている ORM によって `sfFormDoctrine` または `sfFormPropel` のいずれかを継承しています。それぞれのフォームクラスの中身はほとんど同じで、送信された値を簡単にデータベースに保存できます。

>**NOTE**
>~`sfFormObject`~ は、symfony 1.3 で追加された新しいクラスで、`sfFormDoctrine` と `sfFormPropel` の共通処理を扱います。`sfFormDoctrine` と `sfFormPropel` は `sfFormObject` を継承し、次の節で説明するフォームの保存処理の一部を担当します。

### フォームの保存処理

この章の例では、開発者が手を加えることなく、symfony によって自動的に `Product` の情報と新しい `ProductPhoto` オブジェクトが保存されます。まるで魔法のようなメソッド ~`sfFormObject::save()`~ は、舞台裏で様々なメソッドを実行します。この処理を理解することが、より高度な状況で処理を拡張するための鍵となります。

フォームの保存処理は ~`sfFormObject::save()`~ を呼び出すと実行され、内部では以下で説明するメソッドが実行されます。大部分の処理は、すべての埋め込みフォームについて再帰的に実行される ~`sfFormObject::updateObject()`~ メソッドで行われます。

![フォームの保存処理](http://www.symfony-project.org/images/more-with-symfony/advanced_forms_06_ja.png "フォームの保存処理の詳細")

>**NOTE**
>保存処理のほとんどは ~`sfFormObject::doSave()`~ メソッドで実行されます。このメソッドは `sfFormObject::save()` から呼び出され、データベーストランザクション処理が行われます。保存処理自体を変更する場合、通常は `sfFormObject::doSave()` を変更するのがよいでしょう。

埋め込みフォームを無視する
-------------------------

現在の `ProductForm` の実装には、大きな欠陥があります。`ProductPhotoForm` で `filename` フィールドと `caption` フィールドが必須なので、新しい写真を 2 つともアップロードしない限り、メインフォームでのバリデーションはエラーとなります。つまり、単に `Product` の価格を変更する場合でも、新しい写真を2つともアップロードしなくてはなりません。

![製品フォームの写真のバリデーションエラー](http://www.symfony-project.org/images/more-with-symfony/advanced_forms_04.png "製品フォームが写真でバリデーションエラー")

要件を次のように定義し直しましょう。埋め込んだ `ProductPhotoForms` でユーザーが `caption` フィールドと `filename` フィールドを両方とも空欄とした場合、そのフォームを完全に無視します。`caption` フィールドと `filename` フィールドの少なくとも 1 つにデータがある場合は、フォームは通常通りバリデーションを実行し、保存処理を行います。この要件を実装するために、カスタムポストバリデーターなどの高度なテクニックを使います。

`ProductPhotoForm` フォームを編集して、`caption` フィールドと `filename` フィールドのバリデーションで必須を解除するところから始めます:

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

上のコードでは、`filename` フィールドのデフォルトのバリデーターを置き換える際に `required` オプションを `false` に設定しました。また、`caption` フィールドの `required` オプションは個別に `false` に設定しています。

ではポストバリデーターを `ProductPhotoCollectionForm` に追加しましょう:

    [php]
    // lib/form/doctrine/ProductPhotoCollectionForm.class.php
    public function configure()
    {
      // ...

      $this->mergePostValidator(new ProductPhotoValidatorSchema());
    }

ポストバリデーターは特殊なバリデーターで、単一のフィールドの値のみをバリデートするのではなく、送信されたすべての値を横断してバリデートします。もっともよく使われるポストバリデーターは `sfValidatorSchemaCompare` で、たとえばあるフィールドが別のフィールドより小さいかどうかを検証できます。

### カスタムバリデーターを作る

カスタムバリデーターを作るのはとても簡単です。`ProductPhotoValidatorSchema.class.php` という名前の新しいファイルを `lib/validator/` ディレクトリに作ります (ディレクトリを作る必要があります):

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

          // ファイル名があるが、キャプションがない
          if ($value['filename'] && !$value['caption'])
          {
            $errorSchemaLocal->addError(new sfValidatorError($this, 'required'), 'caption');
          }

          // キャプションがあるが、ファイル名がない
          if ($value['caption'] && !$value['filename'])
          {
            $errorSchemaLocal->addError(new sfValidatorError($this, 'required'), 'filename');
          }

          // キャプションとファイル名の両方がない。空の値を削除
          if (!$value['filename'] && !$value['caption'])
          {
            unset($values[$key]);
          }

          // この埋め込みフォームでのエラー
          if (count($errorSchemaLocal))
          {
            $errorSchema->addError($errorSchemaLocal, (string) $key);
          }
        }

        // エラーをメインフォームへスロー
        if (count($errorSchema))
        {
          throw new sfValidatorErrorSchema($this, $errorSchema);
        }

        return $values;
      }
    }

>**TIP**
>すべてのバリデーターは `sfValidatorBase` を継承し、必要なのは `doClean()` メソッドの実装のみです。`configure()` メソッドを使うと、バリデーターにオプションやメッセージを追加することもできます。この例では、2 つのメッセージをバリデーターに追加しています。同様に、`addOption()` メソッドを使って追加のオプションを設定できます。

`doClean()` メソッドでは、フィールドにバインドされた値のクリーニングとバリデーションが実行されます。バリデーターのロジックは次のようにとても単純です:

 * 写真がファイル名のみ、またはキャプションのみで送信された場合、適切なメッセージを含むエラー (`sfValidatorErrorSchema`) をスローします。

 * 送信された写真にファイル名もキャプションもなかった場合、空の写真が保存されないように値をすべて削除します。

 * バリデーションエラーがなかった場合、クリーンアップされた値がメソッドから返されます。

>**TIP**
>この例でのカスタムバリデーターは、ポストバリデーターとして使うことを前提としています。したがって、`doClean()` メソッドには結合された値の配列が渡され、メソッドはクリーニングした値の配列を返します。単一のフィールド用のカスタムバリデーターを作るのはもっと簡単で、`doClean()` メソッドは送信されたフィールドの値のみを受け取り、単一の値のみを返します。

最後のステップでは、`ProductForm` の `saveEmbeddedForms()` メソッドをオーバーライドして、空の写真がデータベースに保存されないよう空の写真フォームを削除するように変更します (この変更を行わないと、データベースの `caption` カラムが必須なので例外がスローされます):

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

Doctrine フォームを簡単に埋め込む
-------------------------------

symfony 1.3 の新機能として、1 対多のリレーションシップをフォームに自動的に埋め込める、~`sfFormDoctrine::embedRelation()`~ メソッドがあります。たとえば、`Product` に関連する新しい `ProductPhotos` を2つアップロードできるだけでなく、関連する既存の `ProductPhoto` オブジェクトをユーザーが編集できるようにする場合を考えます。 

`embedRelation()` メソッドを使うと、既存の `ProductPhoto` オブジェクトごとに `ProductPhotoForm` オブジェクトを 1 つ追加します:

    [php]
    // lib/form/doctrine/ProductForm.class.php
    public function configure()
    {
      // ...

      $this->embedRelation('Photos');
    }

~`sfFormDoctrine::embedRelation()`~ の内部では、2 つの `ProductPhotoForm` オブジェクトを手作業で埋め込んだのとほとんど同じ処理が行われます。2 つの `ProductPhoto` リレーションがすでに存在している場合、この処理の結果、フォームの `widgetSchema` 配列と `validatorSchema` 配列は次のような構成になります:

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

![既存の写真が 2 つある製品フォーム](http://www.symfony-project.org/images/more-with-symfony/advanced_forms_03.png "既存の写真が 2 つある製品フォーム")    

次のステップでは、新しい *Photo* 埋め込みフォームをレンダリングするコードをビューに追加しましょう:

    [php]
    // apps/frontend/module/product/templates/_form.php
    <?php foreach ($form['Photos'] as $photo): ?>
      <?php echo $photo['caption']->renderRow() ?>
      <?php echo $photo['filename']->renderRow(array('width' => 100)) ?>
    <?php endforeach; ?>

このスニペットは、新規の写真フォームの埋め込みに使ったコードとほとんど同じです。

最後のステップでは、ファイルアップロードフィールドを `sfWidgetFormInputFileEditable` に変更して、ユーザーが現在の写真を確認し、別の写真に変更できるようにします:

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

フォームのイベント
-----------------

symfony 1.3 の新機能でフォームイベントが追加されました。このイベントを使うと、プロジェクトの任意の場所でフォームオブジェクトを拡張できます。次の 4 つのイベントを利用できます:

 * `form.post_configure`: フォームが設定された時に通知されるイベント
 * `form.filter_values`: バインドされる前に、マージまたは汚染されたパラメーターやファイルの配列をフィルタするイベント
 * `form.validation_error`: フォームのバリデーションでエラーが発生した場合に通知されるイベント
 * `form.method_not_found`: 未知のメソッドが呼び出された時に通知されるイベント

### `form.validation_error` を使ったカスタムロギング

フォームイベントを使うと、プロジェクトの任意のフォームにおけるバリデーションエラー用のカスタムロギングを追加できます。このログは、ユーザーがミスする頻度の高いフォームやフィールドを追跡するのに役立ちます。

はじめに、`form.validation_error` イベントのイベントディスパッチャーにリスナーを登録します。次のコードを、`config` ディレクトリにある `ProjectConfiguration` の `setup()` メソッドに追加してください:

    [php]
    public function setup()
    {
      // ...

      $this->getEventDispatcher()->connect(
        'form.validation_error',
        array('BaseForm', 'listenToValidationError')
      );
    }

`lib/form` にある `BaseForm` は、すべてのフォームクラスの基底となる特殊なフォームクラスです。基本的に、`BaseForm` にはプロジェクト全体のフォームオブジェクトからアクセスしたいコードを記述します。バリデーションエラー時のロギングを有効にするには、次のコードを `BaseForm` クラスに追加してください:

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

![バリデーションエラーのロギング](http://www.symfony-project.org/images/more-with-symfony/advanced_forms_05.png "バリデーションエラーの表示された Web デバッグツールバー")

フォーム要素でエラーが発生した場合のカスタムスタイル
--------------------------------------------------

最後に、フォーム要素のスタイルに関連する少しやさしいトピックを取り上げます。例として、`Product` ページのデザインで、バリデーションエラーが発生したフィールドを特別なスタイルで表示するとします。

![エラーのある製品フォーム](http://www.symfony-project.org/images/more-with-symfony/advanced_forms_02.png "スタイルが適用されたエラーのある製品フォーム")

デザイナーがすでにエラースタイルを、エラーのある `input` フィールドを含む `div` の `form_error_row` クラスとしてスタイルシートに実装しているとします。どうすれば、エラーの発生したフィールドだけに簡単に `form_row_error` クラスを追加できるでしょうか。

答えは、*フォームスキーマフォーマッター*と呼ばれる特別なオブジェクトにあります。すべての symfony フォームは、フォーム要素の出力に必要な HTML フォーマットを決定するために、*フォームスキーマフォーマッター*を使います。デフォルトでは、HTML テーブルタグを採用するフォームフォーマッターが使われます。

まず、少し簡易なフォーマットでフォームを出力するフォームスキーマフォーマッタークラスを作りましょう。`sfWidgetFormSchemaFormatterAc2009.class.php` という名前の新しいファイルを、`lib/widget/` ディレクトリに作ります (ディレクトリを作る必要があります):

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

このクラスのフォーマットは良くありませんが、`renderRow()` メソッドによる出力では `$rowFormat` のマークアップが使用されるということは分かるでしょう。ここでは詳細を説明しませんが、フォームスキーマフォーマッタークラスには他にも多くのフォーマットオプションがあります。詳細は、 [symfony 1.3 API](http://www.symfony-project.org/api/1_3/sfWidgetFormSchemaFormatter) を参照してください。

プロジェクトのすべてのフォームオブジェクトで新しいフォームスキーマフォーマッターを使うには、次のコードを `ProjectConfiguration` に追加します:

    [php]
    class ProjectConfiguration extends sfProjectConfiguration
    {
      public function setup()
      {
        // ...

        sfWidgetFormSchema::setDefaultFormFormatterName('ac2009');
      }
    }

次にしなければいけないことは、フィールドがバリデーションエラーだった場合にのみ、`form_row` DIV 要素に `form_row_error` クラスを追加することです。`$rowFormat` プロパティに `%row_class%` トークンを追加し、~`sfWidgetFormSchemaFormatter::formatRow()`~ メソッドを次のようにオーバーライドします:

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

この追加により、`renderRow()` メソッドによって出力される各要素は、フィールドにバリデーションエラーがある場合、自動的に `form_row_error` `div` で囲まれます。

最後に
------

フォームフレームワークは、symfony におけるもっとも強力で、かつ、もっとも複雑なコンポーネントの 1 つです。厳密なフォームバリデーション、CSRF 保護、オブジェクトフォームの便利さと引き替えに、フォームの拡張作業はとても困難になっています。しかし、フォームシステムの内部を理解することで、フォームフレームワークの持つ力を利用するための扉を開けることができます。この章で、みなさんがその扉に一歩近づけることを願っています。

将来的には、フォームフレームワークは現在の柔軟さを維持しつつ、複雑さを減らして、開発者がより拡張しやすくなることに焦点を置きます。フォームフレームワークは、まだ初期段階なのです。
