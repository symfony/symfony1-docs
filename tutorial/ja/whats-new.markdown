symfony 1.3/1.4 の新しい機能
=============================

このチュートリアルでは symfony 1.3/1.4 に関する技術的な内容をおおまかに紹介します。このチュートリアルの対象者はすでに symfony 1.2 で作業をしており、symfony 1.3/1.4 の新しい機能を早く学びたい開発者です。

最初に、symfony 1.3/1.4 と互換性のある PHP のバージョンが 5.2.4 およびそれ以降であることにご注意ください。

symfony 1.2 からアップグレードしたいのであれば、[「プロジェクトを 1.2 から 1.3/1.4 にアップグレードする」](http://www.symfony-project.org/tutorial/1_4/ja/upgrade)のページをご覧ください。プロジェクトを symfony 1.3/1.4 に安全にアップグレードするために必要なすべての情報が手に入ります。


メーラー
--------

symfony 1.3/1.4 では SwiftMailer 4.1 をベースとする標準メーラーが新たに用意されました。

メールの送信方法はシンプルでアクションから `composeAndSend()` メソッドを使うだけです:

    [php]
    $this->getMailer()->composeAndSend('from@example.com', 'to@example.com', 'Subject', 'Body');

より柔軟性をもたせる必要があれば、`compose()` メソッドを使って後で送信することもできます。添付ファイルをメッセージに追加する方法は次のとおりです:

    [php]
    $message = $this->getMailer()->
      compose('from@example.com', 'to@example.com', 'Subject', 'Body')->
      attach(Swift_Attachment::fromPath('/path/to/a/file.zip'))
    ;
    $this->getMailer()->send($message);

メーラーの機能はとても強力なので、詳しい情報は公式マニュアルを参照してください。

セキュリティ
-----------

`generate:app` タスクで新しいアプリケーションを作るとき、セキュリティの設定項目はデフォルトで有効です:

  * `escaping_strategy`: デフォルト値は `true` です (`--escaping-strategy` オプションで無効にできます)。
  * `escaping_strategy`: デフォルト値は `true` です (`--escaping-strategy` オプションで無効にできます)。

  * `csrf_secret`: デフォルトでランダムなパスワードが生成されます。CSRF 防止機能は標準で有効です (`--csrf-secret` オプションで無効にできます)。`settings.yml` 設定ファイルを編集するか `--csrf-secret` オプションを指定することで、初期パスワードを変更することを提言します。

ウィジット
---------

### 標準ラベル

ラベルがフィールド名から自動生成される場合、接尾辞の `_id` はつかなくなりました:

  * `first_name` => First name (以前と同じ)
  * `author_id` => Author (以前は "Author id" )

### `sfWidgetFormInputText`

`sfWidgetFormInput` クラスは抽象クラスになりました。テキスト入力フィールドは `sfWidgetFormInputText` クラスのなかで作られます。この変更によってフォームクラスのイントロスペクションはより簡単になりました。

### 国際化ウィジェット

次のウィジェットが追加されました:

  * `sfWidgetFormI18nChoiceLanguage`
  * `sfWidgetFormI18nChoiceCurrency`
  * `sfWidgetFormI18nChoiceCountry`
  * `sfWidgetFormI18nChoiceTimezone`

これらのうち、最初の3つは廃止予定の `sfWidgetFormI18nSelectLanguage`、`sfWidgetFormI18nSelectCurrency` と `sfWidgetFormI18nSelectCountry` ウィジェットの置き換えです。

### 流れるようなインターフェイス

ウィジットは流れるようなインターフェイスを実装するようになりました:

  * `sfWidgetForm`: `setDefault()`、`setLabel()`、`setIdFormat()`、`setHidden()`

  * `sfWidget`: `addRequiredOption()`、`addOption()`、`setOption()`、
    `setOptions()`、`setAttribute()`、`setAttributes()`

  * `sfWidgetFormSchema`: `setDefault()`、`setDefaults()`、`addFormFormatter()`、
    `setFormFormatterName()`、 `setNameFormat()`、`setLabels()`、`setLabel()`、
    `setHelps()`、`setHelp()`、`setParent()`

  * `sfWidgetFormSchemaDecorator`: `addFormFormatter()`、`setFormFormatterName()`、
    `setNameFormat()`、`setLabels()`、`setHelps()`、
    `setHelp()`、`setParent()`、`setPositions()`

バリデータ
------------

### `sfValidatorRegex`

`sfValidatorRegex` に新しい `must_match` オプションが用意されました。このオプションが `false` にセットされている場合、正規表現は渡されるバリデータにマッチしません。

`sfValidatorRegex` の `pattern` オプションの値は呼び出されるときに正規表現を返す `sfCallable` のインスタンスでしなければならなくなりました。

### `sfValidatorUrl`

`sfValidatorUrl` に新しい `protocols` オプションが用意されました。次のようにプロトコルを特定のものに制限できるようになりました:

    [php]
    $validator = new sfValidatorUrl(array('protocols' => array('http', 'https')));

デフォルトでは次のプロトコルが許可されています:

 * `http`
 * `https`
 * `ftp`
 * `ftps`

### `sfValidatorSchemaCompare`

`sfValidatorSchemaCompare` クラスに2つの新しいコンパレータが用意されました:

 * `IDENTICAL` は `===` と同等です;
 * `NOT_IDENTICAL` は `!==` と同等です;

### `sfValidatorChoice`、`sfValidator(Propel|Doctrine)Choice`

`sfValidatorChoice`、`sfValidatorPropelChoice` そして `sfValidatorDoctrineChoice` バリデータには `multiple` オプションが `true` の場合のみ有効になる2つの新しいオプションが用意されました:

 * `min` 選択する必要がある最小数
 * `max` 選択する必要がある最大数

### 国際化対応バリデータ

次のバリデータが追加されました:

 * `sfValidatorI18nChoiceTimezone`

### デフォルトのエラーメッセージ

次のように `sfForm::setDefaultMessage()` メソッドを使うことでデフォルトのエラーメッセージをグローバルに定義できるようになりました:

    [php]
    sfValidatorBase::setDefaultMessage('required', 'This field is required.');

上記のコードはすべてのバリデータのデフォルトメッセージである 'Required.' をオーバーライドします。デフォルトメッセージはバリデータが作られる前に定義しておかなければならないことにご注意ください (コンフィギュレーションクラスがよい場所です)。

>**NOTE**
>`setRequiredMessage()` と `setInvalidMessage()` メソッドは廃止予定なので、代わりに新しい `setDefaultMessage()` メソッドを呼び出します。

symfony がエラーを表示するとき、使われるエラーメッセージは次のように決められます:

  * バリデータが作られるときに渡されるメッセージが探索されます (バリデータのコンストラクタの第2引数経由);

  * コンストラクタで定義されていなければ、`setDefaultMessage()` メソッドで定義される初期メッセージが探索されます;

  * `setDefaultMessage()` メソッドで定義されていなければ (メッセージが `addMessage()` メソッドで追加されているとき)、バリデータ自身で定義される初期メッセージに戻ります。

### 流れるようなインターフェイス

バリデータは流れるようなインターフェイスを実装するようになりました:

  * `sfValidatorSchema`: `setPreValidator()`、`setPostValidator()`

  * `sfValidatorErrorSchema`: `addError()`、`addErrors()`

  * `sfValidatorBase`: `addMessage()`、`setMessage()`、
  `setMessages()`、`addOption()`、`setOption()`、
  `setOptions()`、`addRequiredOption()`

### `sfValidatorFile`

`php.ini` で `file_uploads` が無効にされている場合 `sfValidatorFile` のインスタンスを作る際に例外が投げられます。

フォーム
--------

### `sfForm::useFields()`

新しい `sfForm::useFields()` メソッドは、引数として提供されるフィールド以外の、隠しフィールドではないすべてのフィールドをフォームから削除します。ときには不要なフィールドの割り当てを解除するよりもフォームで維持したいフィールドを明示的に指示するほうが簡単です。たとえば新しいフィールドを基底フォームに追加するとき、これらのフィールドは明示的に追加されるまでフォームに自動表示されることはありません (モデルフォームのなかで新しいカラムを関連テーブルに追加する場合を考えてください):

    [php]
    class ArticleForm extends BaseArticleForm
    {
      public function configure()
      {
        $this->useFields(array('title', 'content'));
      }
    }

デフォルトでは、フィールドの配列はフィールドの順序を変更するのにも使われます。自動的な並べ替えを無効にするには `useFields()` に2番目の引数として `false` を 渡します。

### `sfForm::getEmbeddedForm($name)`

`->getEmbeddedForm()` メソッドを使って特定の組み込みフォームにアクセスできます。

### `sfForm::renderHiddenFields()`

`->renderHiddenFields()` メソッドは組み込みフォームからの隠しフィールドをレンダリングします。フォーマッタを使って組み込みフォームをレンダリングする際に便利な再帰処理を無効にする引数が追加されました:

    [php]
    // 組み込みフォームからのフィールドを含めて、すべての隠しフィールドをレンダリングする
    echo $form->renderHiddenFields();

    // 再帰処理なしで隠しフィールドをレンダリングする
    echo $form->renderHiddenFields(false);

### `sfFormSymfony`

新しい `sfFormSymfony` クラスはイベントディスパッチャを symfony フォームに導入します。`self::$dispatcher` を通してフォームクラス内部のディスパッチャにアクセスできます。次のフォームイベントが symfony によって通知されます:

  * `form.post_configure`:   このイベントはフォームが設定された後で通知されます。
  * `form.filter_values`:    このイベントは、マージされ汚染されたパラメータと、バインドされる直前のファイルの配列をフィルタリングします。
  * `form.validation_error`: フォームバリデーションが通らないときにこのイベントが通知されます。
  * `form.method_not_found`: 見つからないメソッドが呼び出されるときにこのイベントが通知されます。


### `BaseForm`

symfony 1.3/1.4 では `BaseForm` クラスがすべての新しいプロジェクトに用意されます。このクラスは Form コンポーネントを拡張するもしくはプロジェクト固有の機能を追加するために使います。`sfDoctrinePlugin` と `sfPropelPlugin` によって生成されるフォームはこのクラスを自動的に継承します。追加のフォームクラスを作るのであれば継承するクラスは `sfForm` ではなく `BaseForm` です。

### `sfForm::doBind()`

ロジックをわかりやすくするために、汚染されたパラメータのクリーニングは `->doBind()` メソッドに隔離されました。このメソッドはパラメータとファイルのマージ済みの配列を `->bind()` から受け取ります。

### `sfForm(Doctrine|Propel)::doUpdateObject()`

ロジックをわかりやすくするために、Doctrine と Propel のフォームクラスに `->doUpdateObject()` メソッドが追加されました。このメソッドは すでに `->processValues()` によって処理済みの値の配列を `->updateObject()` から受け取ります。

### `sfForm::enableLocalCSRFProtection()` と `sfForm::disableLocalCSRFProtection()`

`sfForm::enableLocalCSRFProtection()` と `sfForm::disableLocalCSRFProtection()` メソッドを使うとき、あなたのクラスの `configure()` メソッドから CSRF 防止機能を簡単に設定できます。

CSRF 防止機能を無効にするには、次のコードを `configure()` メソッドに追加します:

    [php]
    $this->disableLocalCSRFProtection();

`disableLocalCSRFProtection()` を呼び出すことによって、フォームインスタンスを作る際に CSRF 対策の秘密の文字列を渡していたとしても CSRF 防止機能は無効になります。

### 流れるようなインターフェイス

`sfForm` メソッドは流れるようなインターフェイスを実装するようになりました: `addCSRFProtection()`、`setValidators()`、`setValidator()`、
`setValidatorSchema()`、`setWidgets()`、`setWidget()`、
`setWidgetSchema()`、`setOption()`、`setDefault()` そして `setDefaults()`

オートローダ
--------------

symfony のすべてのオートローダは PHP に合わせて大文字と小文字を区別しなくなりました。

### `sfAutoloadAgain` (実験的な機能)

デバッグモードでの用途を目的とする特殊なオートローダが追加されました。新しい `sfAutoloadAgain` クラスは symfony の標準オートローダをリロードし該当するクラスを求めてファイルシステムを探索します。純粋な効果は新しいクラスをプロジェクトに追加した後に `symfony cc` を実行する必要がなくなることです。

テスト
-----

### テストのスピードアップ

大規模なテストスイートで特にテストが通らない場合、変更するたびにすべてのテストを実行するのに時間がとてもかかる可能性があります。なぜならテストを修正するたびに、何も壊れていないことを確認するためにテストスイート全体を再度実行することになるからです。しかしながらテストが修正されないかぎり、すべてのテストを再実行する必要はありません。symfony 1.3/1.4 では、`test:all` と `symfony:test` タスクのために前回の実行時に通らなかったテストだけを再実行する `--only-failed` オプション (ショートカットは `-f`) が用意されました:

    $ php symfony test:all --only-failed

どのように動くのかを説明します。まず最初に、すべてのテストはいつもどおりに実行されます。引き続きテストを実行すると、最後のテストで通らなかったものだけが実行されます。コードを修正して一部のテストが通れば、次回以降の実行から除外されます。再びすべてのテストが通れば、完全なテストスイートが実行され、徹底的に繰り返すことができます。

### 機能テスト

リクエストが例外を生成するとき、レスポンステスターの `debug()` メソッドは標準出力の HTML 形式の代わりに、人間が理解できる例外の説明をテキスト形式で出力するようになり、より簡単にデバッグできるようになりました。

レスポンスの内容全体に対して正規表現で検索できる新しい `matches()` メソッドが `sfTesterResponse` に用意されました。このメソッドは `checkElement()` が使えない XML 形式ではないレスポンスに重宝します。力不足の `contains()` メソッドの代わりとして使うこともできます:

    [php]
    $browser->with('response')->begin()->
      matches('/I have \d+ apples/')->    // 引数として正規表現をとる
      matches('!/I have \d+ apples/')->   // 冒頭の ! は正規表現がマッチしてはならないことを意味する
      matches('!/I have \d+ apples/i')->  // 正規表現の修飾子も追加できる
    end();

### JUnit と互換性のある XML 出力

`--xml` オプションをつけることでテストタスクは JUnit と互換性のある XML ファイルも出力できるようになりました:

    $ php symfony test:all --xml=log.xml

### 簡単なデバッグ

テストが通らないことをテストハーネスが報告するときにデバッグを簡単にするために、通らないテストについて詳細な出力を指示する `--trace` オプションを指定できるようになりました:

    $ php symfony test:all -t

### lime によるカラー出力

symfony 1.3/1.4 では、lime はカラー出力を正しく行うようになりました。このことが意味するのは、ほとんどの場合において `lime_test` の lime コンストラクタの第2引数を省略できるということです:

    [php]
    $t = new lime_test(1);

### `sfTesterResponse::checkForm()`

フォームのすべてのフィールドが正しくレンダリングされてレスポンスに含まれているかどうかをより簡単に確かめられるように `->checkForm()` メソッドがレスポンステスターに追加されました:

    [php]
    $browser->with('response')->begin()->
      checkForm('ArticleForm')->
    end();

もしくは望むのであれば、フォームオブジェクトを渡すことができます:


    [php]
    $browser->with('response')->begin()->
      checkForm($browser->getArticleForm())->
    end();

複数のフォームがレスポンスに含まれる場合、どの DOM 部分をテストするのかをきめ細かく指定するために CSS セレクタを提供するオプションが用意されています:

    [php]
    $browser->with('response')->begin()->
      checkForm('ArticleForm', '#articleForm')->
    end();

### `sfTesterResponse::isValid()`

レスポンスが妥当な XML であるのかチェックするのにレスポンステスターの `->isValid()` メソッドを使うことができます:

    [php]
    $browser->with('response')->begin()->
      isValid()->
    end();

引数として `true` を渡すことでドキュメントの種類に対するレスポンスをバリデートすることもできます:

    [php]
    $browser->with('response')->begin()->
      isValid(true)->
    end();

バリデートする対象として XSD もしくは RelaxNG スキーマがある場合、このスキーマファイルへのパスを渡すことができます:

    [php]
    $browser->with('response')->begin()->
      isValid('/path/to/schema.xsd')->
    end();

### `context.load_factories` をリスニングする

`context.load_factories` イベントのリスナーを機能テストに追加できるようになりました。このリスナーは symfony の以前のバージョンでは利用できませんでした:

    [php]
    $browser->addListener('context.load_factories', array($browser, 'listenForNewContext'));

### 改良された `->click()`

`->click()` メソッドに CSS セレクタを渡すことが可能になり、セマンティックにしたい要素をターゲットにするのがとても簡単になりました:

    [php]
    $browser
      ->get('/login')
      ->click('form[action$="/login"] input[type="submit"]')
    ;

タスク
------

symfony CLI はターミナルウィンドウの幅を検出することを試み、フィットするように行を整えます。検出できない場合、CLI はウィンドウの幅をデフォルトの78カラムに合わせようとします。

### `sfTask::askAndValidate()`

ユーザーに質問して得られる入力内容をバリデートする `sfTask::askAndValidate()` メソッドが新たに用意されました:

    [php]
    $answer = $this->askAndValidate('What is you email?', new sfValidatorEmail());

このメソッドはオプションの配列を受け取ることもできます (詳しい情報は API ドキュメントを参照)。

### `symfony:test`

特定のプラットフォームで symfony が正しく動くのかをチェックするために、ときには、開発者は symfony のテストスイートを実行する必要があります。従来ではこの確認作業を行うために symfony に附属する `prove.php` スクリプトの存在を知らなければなりませんでした。symfony 1.3/1.4 では組み込みのタスク、コマンドラインから symfony のコアテストスイートを実行できる `symfony:test` タスクが用意され、ほかのタスクと同じように使うことができます:

    $ php symfony symfony:test

`php test/bin/prove.php` に慣れ親しんでいるのであれば、同等の `php data/bin/symfony symfony:test` コマンドを使うことができます。


### `project:deploy`

`project:deply` タスクは少し改良されました。ファイルの転送状況をリアルタイムで表示するようになりました。ただし、`-t` オプションが指定されているときだけです。オプションが指定されていなければタスクは何も表示しません。もちろんエラーの場合は除きます。エラーのときには、問題がわかりやすいように赤色を背景にエラー情報が出力されます。

### `generate:project`

symfony 1.3/1.4 で `generate:project` タスクを実行するとき、初期設定では ORM は Doctrine になります:

    $ php /path/to/symfony generate:project foo

Propel のプロジェクトを生成するには `--orm` オプションを渡します:

    $ php /path/to/symfony generate:project foo --orm=Propel

Propel もしくは Doctrine のどちらも使いたくなければ、`--orm` オプションに `none` を渡します:

    $ php /path/to/symfony generate:project foo --orm=none

新しい `--installer` オプションのおかげで、新たに生成されるプロジェクトの詳細をカスタマイズできる PHP スクリプトを指定することができます。スクリプトはタスクで実行され、タスクのメソッドのなかで使うことができます。次のようにもっと便利なメソッドがあります:

`installDir()`、`runTask()`、`ask()`、`askConfirmation()`、`askAndValidate()`、
`reloadTasks()`、`enablePlugin()` そして `disablePlugin()`

詳しい情報は公式ブログの[記事  (http://www.symfony-project.org/blog/2009/06/10/new-in-symfony-1-3-project-creation-customization)](http://www.symfony-project.org/blog/2009/06/10/new-in-symfony-1-3-project-creation-customization)に掲載されています。

プロジェクトを生成するとき、2番目の引数として著者の名前を渡すことができます。この引数は symfony が新しいクラスを生成するときに PHPDoc の `@author` タグに使う値を指定します:

    $ php /path/to/symfony generate:project foo "Joe Schmo"

### `sfFileSystem::execute()`

`sfFileSystem::sh()` メソッドはより強力な機能をもつ `sfFileSystem::execute()` メソッドに置き換わりました。このメソッドは `stdout` と `stderr` 出力のリアルタイム処理のコールバックを引数にとります。また両方の出力を配列として返すこともできます。使い方の例は `sfProjectDeployTask` クラスで見つけることができます。

### `task.test.filter_test_files`

`test:*` タスクは自分自身が実行される前に `task.test.filter_test_files` イベントを通るようになりました。このイベントには `arguments` と `options` パラメータが用意されています。

### `sfTask::run()` の強化

`sfTask:run()` に次のような引数とオプションの連想配列を渡せるようになりました:

    [php]
    $task = new sfDoctrineConfigureDatabaseTask($this->dispatcher, $this->formatter);
    $task->run(
      array('dsn' => 'mysql:dbname=mydb;host=localhost'),
      array('name' => 'master')
    );

以前のバージョンでは、次のように書けばまだ動きます:

    [php]
    $task->run(
      array('mysql:dbname=mydb;host=localhost'),
      array('--name=master')
    );

### `sfBaseTask::setConfiguration()`

PHP から `sfBaseTask` を継承するタスクを呼び出すとき、`->run()` に `--application` と `--env` オプションを渡す必要はもはやありません。その代わり、`->setConfiguration()` を呼び出すだけで設定オブジェクトを直接セットすることができます:

    [php]
    $task = new sfDoctrineLoadDataTask($this->dispatcher, $this->formatter);
    $task->setConfiguration($this->configuration);
    $task->run();

以前のバージョンでは、次のように書けばまだ動きます:

    [php]
    $task = new sfDoctrineLoadDataTask($this->dispatcher, $this->formatter);
    $task->run(array(), array(
      '--application='.$options['application'],
      '--env='.$options['env'],
    ));

### `project:disable` と `project:enable`

`project:disable` と `project:enable` タスクを使うことで環境全体を無効もしくは有効にできるようになりました:

    $ php symfony project:disable prod
    $ php symfony project:enable prod

特定の環境においてどのアプリケーションを無効にするかを指定することもできます:

    $ php symfony project:disable prod frontend backend
    $ php symfony project:enable prod frontend backend

これらのタスクは従来の機能と後方互換性があります:

    $ php symfony project:disable frontend prod
    $ php symfony project:enable frontend prod

### `help` と `list`

`help` と `list` タスクは情報を XML 形式で表示できるようになりました:

    $ php symfony list --xml
    $ php symfony help test:all --xml

この出力はこのタスクオブジェクトの XML 表現を返す新しい `sfTask::asXml()` メソッドにもとづいています。

ほとんどの場合において XML 出力は IDE のようなサードパーティに重宝するでしょう。

### `project:optimize`

このタスクを実行すると、アプリケーションのテンプレートファイルの保存場所がキャッシュされ、実行時におけるディスクの読み込み回数が減ります。このタスクを使う場所は運用サーバーに限定すべきです。プロジェクトを変更するたびにタスクを再実行することをお忘れなく:

    $ php symfony project:optimize frontend

### `generate:app`

`generate:app` タスクがスケルトンディレクトリをチェックする場所はコアではなくプロジェクトの `data/skeleton/app` ディレクトリになりました。

### タスクからメールを送信する

`getMailer()` メソッドを使うことでタスクからメールを簡単に送信できるようになりました。

### タスクでルーティングを使う

`getRouting()` メソッドを使うことでタスクからルーティングオブジェクトを簡単に得られるようになりました。

例外
----

### オートローディング

オートロードのあいだに例外が投げられるとき、symfony はこれらの例外を捕まえエラーをユーザーに出力します。この対応によっていくつかの「真っ白な」ページの問題が解決されます。

### Web デバッグツールバー

可能であれば、開発環境の例外ページでも Web デバッグツールバーが表示されるようになりました。

Propel との統合
---------------

Propel のバージョンは 1.4 にアップグレードされました。詳しいアップグレード情報に関しては[公式サイト](http://propel.phpdb.org/trac/wiki/Users/Documentation/1.4)を訪問してくださるようお願いします。

### Propel のビヘイビア

Propel を拡張するために symfony が依存するカスタムのビルダークラスは Propel 1.4 の新しいビヘイビアシステムに移植されました。

### `propel:insert-sql`

`propel:insert-sql` はデータベースからすべてのデータを削除する前に削除してよいのか確認の質問をします。このタスクは複数のデータベースからデータを削除することができますが、関連するデータベースの接続名も表示するようになりました。

### `propel:generate-module`、`propel:generate-admin`、`propel:generate-admin-for-route`

`propel:generate-module`、`propel:generate-admin` と `propel:generate-admin-for-route` タスクは生成モジュールのアクション基底クラスのコンフィギュレーションの変更を可能にする `--actions-base-class` オプションをとります。

### Propel のビヘイビア

Propel 1.4 はビヘイビアの実装を導入しました。カスタムの symfony ビルダーはこの新しいシステムに移植されました。

Propel モデルネイティブなビヘイビアを Propel モデルに追加したければ、`schema.yml` でも可能です:

    classes:
      Article:
        propel_behaviors:
          timestampable: ~

もしくは `schema.yml` の古い構文を使うのであれば次のように表記します:

    propel:
      article:
        _propel_behaviors:
          timestampable: ~

### フォーム生成を無効にする

Propel の `symfony` ビヘイビアにパラメータを渡すことで特定のモデルでのフォーム生成を無効にできます:

    classes:
      UserGroup:
        propel_behaviors:
          symfony:
            form: false
            filter: false

この設定が考慮される前にモデルをリビルドしなければならないことにご注意ください。ふるまいが存在するようになるのはふるまいが添付されているモデルがリビルドされた後であるからです。

### 異なるバージョンの Propel を使う

異なるバージョンの Propel を使うのは簡単で `ProjectConfiguration` のなかで設定変数の `sf_propel_runtime_path` と `sf_propel_generator_path` をセットするだけです:

    [php]
    // config/ProjectConfiguration.class.php
    public function setup()
    {
      $this->enablePlugins('sfPropelPlugin');

      sfConfig::set('sf_propel_runtime_path', '/path/to/propel/runtime');
      sfConfig::set('sf_propel_generator_path', '/path/to/propel/generator');
    }

ルーティング
------------

### デフォルトの要件

`column` オプションの値がデフォルトの `id` であるとき、デフォルトの必須要件の `\d+` は `sfObjectRouteCollection` にだけ適用されるようになりました。このことが意味するのは (`slug` のような) 数字ではないカラムが指定されているときに代わりの必須要件を用意する必要はないということです。

### `sfObjectRouteCollection` オプション

新しい `default_params` オプションが `sfObjectRouteCollection` に追加されました。このオプションによってデフォルトパラメータをそれぞれの生成ルートに登録できます:

    [yml]
    forum_topic:
      class: sfDoctrineRouteCollection
      options:
        default_params:
          section: forum

CLI
---

### カラー出力

symfony CLI を使うとき、symfony はご利用のコンソールがカラー出力をサポートしているかどうかを推測します。ただし、たとえば Cygwin を使っている場合には推測が間違うことがあります (Windows プラットフォームではカラー出力がつねにオフだからです)。

symfony 1.3/1.4 では、グローバルオプションの `--color` を渡すことでカラー出力を強制できるようになりました。

国際化対応
-----------

### データの更新

国際化対応オペレーションに使われているすべてのデータは `ICU` プロジェクトから更新されました。symfony には約330個のロケールファイルが付属しており、symfony 1.2 と比べると約70個増えています。ですので、たとえば言語リストの10番目の項目をチェックするテストケースが通らない可能性があることにご注意ください。

### ユーザーロケールを基準にソートする

ユーザーロケールに依存するデータのソートもすべてロケールに依存して実行されます。`sfCultureInfo->sortArray()` はこの用途に使うことができます。

プラグイン
----------

symfony 1.3/1.4 以前では、`sfDoctrinePlugin` と `sfCompat10Plugin` 以外のすべてのプラグインがデフォルトで有効でした:

    [php]
    class ProjectConfiguration extends sfProjectConfiguration
    {
      public function setup()
      {
        // 互換性のために望むプラグインだけ削除および有効にする
        $this->enableAllPluginsExcept(array('sfDoctrinePlugin', 'sfCompat10Plugin'));
      }
    }

symfony 1.3/1.4 では、新たなプロジェクトでプラグインを使うには `ProjectConfiguration` クラスのなかで明示的に有効にしなければなりません:

    [php]
    class ProjectConfiguration extends sfProjectConfiguration
    {
      public function setup()
      {
        $this->enablePlugins('sfDoctrinePlugin');
      }
    }

`plugin:install` タスクはインストールするプラグインを自動的に有効にします (そして `plugin:uninstall` はプラグインを無効にします)。Subversion 経由でプラグインをインストールする場合、手動で有効にする必要があります。

`sfProtoculousPlugin` もしくは `sfCompat10Plugin` のようなコアプラグインを使いたければ、必要なのは対応する `enablePlugins()` メソッドを `ProjectConfiguration` クラスに追加することだけです。

>**NOTE**
>symfony 1.2 からプロジェクトをアップグレードする場合、古いふるまいはアクティブなままです。これはアップグレードタスクが `ProjectConfiguration` ファイルを変更しないからです。新しいふるまいが適用されるのは symfony 1.3/1.4 の新規プロジェクトだけです。

### `sfPluginConfiguration::connectTests()`

新しい `setupPlugins()` メソッドのなかでプラグインコンフィギュレーションの `->connectTests()` メソッドを呼び出すことで、プラグインのテストを `test:*` タスクに結びつけることができます:

    [php]
    class ProjectConfiguration extends sfProjectConfiguration
    {
      public function setupPlugins()
      {
        $this->pluginConfigurations['sfExamplePlugin']->connectTests();
      }
    }

設定
----

### `sf_file_link_format`

symfony 1.3/1.4 は可能であればファイルパスをクリック可能なリンクの形式 (すなわちデバッグ例外のテンプレート) に整えます。`sf_file_link_format` がセットされていればこの設定が使われ、そうでなければ symfony は PHP コンフィギュレーションの `xdebug.file_link_format` の値を探します。

たとえばファイルを TextMate で開きたいのであれば、次のコードを `settings.yml` に追加します:

    [yml]
    all:
      .settings:
        file_link_format: txmt://open?url=file://%f&line=%l

`%f` プレースホルダはファイルの絶対パスに、`%l` プレースホルダは行数に置き換わります。

Doctrine との統合
-----------------

Doctrine のバージョンは 1.2 にアップグレードされました。アップグレードに関する詳しい情報は [Doctrine の公式サイト](http://www.doctrine-project.org/documentation/1_2/ja)を訪問してくださるようお願いします。

### フォームクラスを生成する

Doctrine の YAML スキーマファイルのなかで symfony の追加オプションを指定できるようになりました。そしてフォームとフィルタクラスの生成を無効にするオプションもいくつか追加されました。

たとえば典型的な多対多のリファレンスモデルでは、フォームもしくはフィルタフォームクラスを生成させる必要はありません。ですので次のようなことができます:

    UserGroup:
      options:
        symfony:
          form: false
          filter: false
      columns:
        user_id:
          type: integer
          primary: true
        group_id:
          type: integer
          primary: true

### フォームクラスの継承

モデルクラスからフォームを生成するとき、モデルクラスは継承を含んでいます。生成される子クラスは継承を尊重し、同じ継承構造にしたがうフォームを生成します。

### 新しいタスク

Doctrine で開発するときに助けになる新しいタスクが導入されました。

#### モデルテーブルを作る

指定モデルの配列に対応するテーブルを個別に作ることができるようになりました。Doctrine はテーブルを削除した後であなたに代わってテーブルを再作成してくれます。既存のプロジェクト/データベースで新しいモデルを開発するとき、データベース全体を消去せずにテーブル群を再構築する際に役立ちます:

    $ php symfony doctrine:create-model-tables Model1 Model2 Model3

#### モデルファイルを削除する

YAML スキーマファイルのなかで、モデルや名前を変更したり使われなくなったモデルを削除することがよくあります。このような作業を行うと、孤児になったモデル、フォームそしてフィルタクラスが出てきます。`doctrine:delete-model-files` タスクを使うことで、モデルに関連する生成ファイルを手作業で削除できるようになりました:

    $ php symfony doctrine:delete-model-files ModelName

上記のタスクは関連する生成ファイルを見つけ、そのファイルを削除したいかどうか尋ねてきます。

#### モデルファイルをきれいにする

上記のプロセスを `doctrine:clean-model-files` タスクで自動化することで、ディスクに存在するが YAML スキーマファイルには存在しないモデルを見つけることができます:

    $ php symfony doctrine:clean-model-files

上記のコマンドは YAML スキーマファイルと生成モデルやファイルを比較し、どれを削除するのかを決めます。これらのモデルは `doctrine:delete-model-files` タスクに渡されます。タスクはファイルを自動的に削除する前に削除されるファイルについて確認の質問をします。

#### 何でもビルドする

新しく用意された `doctrine:build` タスクによって symfony や Doctrine にビルドしてほしいものを明確に指定できます。このより柔軟性のある解決方法に合わせて廃止予定になった既存の多くのタスクを組み合わせることで得られる機能はこのタスクによって再現できます。

`doctrine:build` の使い方は次のとおりです:

    $ php symfony doctrine:build --db --and-load

これはデータベースを削除 (`:drop-db`) して作成 (`:build-db`) し、`schema.yml` でテーブル設定を作成 (`:insert-sql`) し、フィクスチャデータを読み込み (`:data-load`) ます:

    $ php symfony doctrine:build --all-classes --and-migrate

これはモデル (`:build-model`)、フォーム (`:build-forms`)、フォームフィルタ (`:build-filters`) を生成し、保留されているマイグレーション (`:migrate`) を実行します:

    $ php symfony doctrine:build --model --and-migrate --and-append=data/fixtures/categories.yml

これはモデルを生成 (`:build-model`) し、データベースのマイグレーション (`:migrate`) を行い、そしてカテゴリのフィクスチャデータ (`:data-load --append --dir=/data/fixtures/categories.yml`)をつけ加えます。

詳しい情報は `doctrine:build` タスクのヘルプページを参照してください。

#### 新しいオプション: `--migrate`

次のタスクは `--migrate` オプションを受け入れるようになり、入れ子の `doctrine:insert-sql` タスクを `doctrine:migrate` に置き換えます。

  * `doctrine:build-all`
  * `doctrine:build-all-load`
  * `doctrine:build-all-reload`
  * `doctrine:build-all-reload-test-all`
  * `doctrine:rebuild-db`
  * `doctrine:reload-data`

#### `doctrine:generate-migration --editor-cmd`

`doctrine:generate-migration` タスクは `--editor-cmd` オプションを受け入れるようになりました。このオプションは編集を楽にするために用意され、マイグレーションクラスが作られるときに実行されます:


    $ php symfony doctrine:generate-migration AddUserEmailColumn --editor-cmd=mate

この例ではマイグレーションクラスが生成され新しいファイルが TextMate で開かれます。

#### `doctrine:generate-migrations-diff`

この新しいタスクは、新旧のスキーマをもとに完全なマイグレーションクラスを自動的に生成します。

#### 特定の接続を作成もしくは削除する

`doctrine:build-db` と `doctrine:drop-db` を実行するときにデータベース接続を指定できるようになりました:

    $ php symfony doctrine:drop-db master slave1 slave2

### 日付のセッターとゲッター

Doctrine の日付とタイムスタンプの値を PHP の DateTime オブジェクトインスタンスとして取得するための2つの新しいメソッドが追加されました:

    [php]
    echo $article->getDateTimeObject('created_at')
      ->format('m/d/Y');

`setDateTimeObject` メソッドを呼び出して有効な `DateTime` インスタンスを渡すだけで日付の値もセットできます:

    [php]
    $article->setDateTimeObject('created_at', new DateTime('09/01/1985'));

### `doctrine:migrate --down`

`doctrine:migrate` はスキーマをリクエストされる方向に1回でマイグレートする `up` と `down` オプションを受け入れます:

    $ php symfony doctrine:migrate --down

### `doctrine:migrate --dry-run`

データベースが DDL 文のロールバックをサポートする場合 (MySQL はサポートしません)、新しい `dry-run` オプションを利用できます:

    $ php symfony doctrine:migrate --dry-run

### DQL タスクをテーブル形式のデータとして出力する

`doctrine:dql` コマンドを実行するとき、従来のデータ出力形式は YAML だけでした。新しく追加された `--table` オプションによって MySQL のコマンドライン出力と似たテーブル形式でもデータを出力できるようになりました。そのため、次のような表現が可能になりました:

    $ ./symfony doctrine:dql "FROM Article a" --table
    >> doctrine  executing dql query
    DQL: FROM Article a
    +----+-----------+----------------+---------------------+---------------------+
    | id | author_id | is_on_homepage | created_at          | updated_at          |
    +----+-----------+----------------+---------------------+---------------------+
    | 1  | 1         |                | 2009-07-07 18:02:24 | 2009-07-07 18:02:24 |
    | 2  | 2         |                | 2009-07-07 18:02:24 | 2009-07-07 18:02:24 |
    +----+-----------+----------------+---------------------+---------------------+
    (2 results)

### `doctrine:dql` にクエリパラメータを渡す

`doctrine:dql` タスクはクエリパラメータも引数にとるように強化されました:

    $ php symfony doctrine:dql "FROM Article a WHERE name LIKE ?" John%

### 機能テストでクエリをデバッグする

`sfTesterDoctrine` クラスに `->debug()` メソッドが用意されました。このメソッドは現在のコンテキストで実行されたクエリの情報を出力します:

    [php]
    $browser->
      get('/articles')->
      with('doctrine')->debug()
    ;

メソッドに数値を渡せば少し前に実行されたクエリの履歴が表示され、文字列を渡せば文字列の一部にマッチするクエリや正規表現にマッチするクエリだけが表示されます:

    [php]
    $browser->
      get('/articles')->
      with('doctrine')->debug('/from articles/i')
    ;

### `sfFormFilterDoctrine`

`sfFormFilterDoctrine` クラスは `query` オプションを通して `Doctrine_Query` のシードを提供できるようになりました:

    [php]
    $filter = new ArticleFormFilter(array(), array(
      'query' => $table->createQuery()->select('title, body'),
    ));

`->setTableMethod()` (もしくは `table_method` オプション) を通して指定されるテーブルメソッドはクエリオブジェクトを返す必要がありません。次のコードはどれも有効な `sfFormFilterDoctrine` テーブルメソッドです:

    [php]
    // symfony >= 1.2 で動く
    public function getQuery()
    {
      return $this->createQuery()->select('title, body');
    }

    // symfony >= 1.2 で動く
    public function filterQuery(Doctrine_Query $query)
    {
      return $query->select('title, body');
    }

    // symfony >= 1.3 で動く
    public function modifyQuery(Doctrine_Query $query)
    {
      $query->select('title, body');
    }

フォームフィルタのカスタマイズが簡単になりました。フィルタリングをフィールドに追加するために必要なのはウィジェットとそれを処理するメソッドを追加することだけです:

    [php]
    class UserFormFilter extends BaseUserFormFilter
    {
      public function configure()
      {
        $this->widgetSchema['name'] = new sfWidgetFormInputText();
        $this->validatorSchema['name'] = new sfValidatorString(array('required' => false));
      }

      public function addNameColumnQuery($query, $field, $value)
      {
        if (!empty($value))
        {
          $query->andWhere(sprintf('CONCAT(%s.f_name, %1$s.l_name) LIKE ?', $query->getRootAlias()), $value);
        }
      }
    }

以前のバージョンでこのコードを動かすには、ウィジェットとメソッドを作ることに加えて `getFields()` を拡張する必要がありました。

### Doctrine のコンフィギュレーションを変更する

Doctrine のコンフィギュレーションを変更するために `doctrine.configure` と `doctrine.configure_connection` イベントをリスニングできます。このことはプラグインが `sfDoctrinePlugin` の前で有効にされているかぎり、プラグインから Doctrine のコンフィギュレーションを簡単にカスタマイズできることを意味します。

### `doctrine:generate-module`、`doctrine:generate-admin`、`doctrine:generate-admin-for-route`

`doctrine:generate-module`、`doctrine:generate-admin`、`doctrine:generate-admin-for-route` タスクは生成モジュールにおけるアクション基底クラスのコンフィギュレーションの変更を可能にする `--actions-base-class` オプションをとります。

### マジックメソッドの PHPDoc タグ

symfony によって Doctrine モデルに追加されるマジックメソッドのゲッターとセッターはそれぞれの生成基底クラスの PHPDoc ヘッダーに現れます。IDE がコード入力補完をサポートする場合、これらの `getFooBar()` と `setFooBar()` メソッドはモデルオブジェクトで見つかります。`FooBar` はラクダ記法のフィールド名です。

### 異なるバージョンの Doctrine を使う

異なるバージョンの Doctrine を使うのは簡単で `ProjectConfiguration` のなかで `sf_doctrine_dir` 設定をセットするだけです:

    [php]
    // config/ProjectConfiguration.class.php
    public function setup()
    {
      $this->enablePlugins('sfDoctrinePlugin');

      sfConfig::set('sf_doctrine_dir', '/path/to/doctrine/lib');
    }

Web デバッグツールバー
------------------------

### `sfWebDebugPanel::setStatus()`

Web デバッグツールバーのそれぞれのパネルにおいてタイトルの背景色に影響を与えるステータスを指定できるようになりました。たとえば、`sfLogger::INFO` よりも優先順位が高いメッセージがロギングされている場合、log パネルのタイトルの背景色は変わります。

### `sfWebDebugPanel` リクエストパラメータ

`sfWebDebugPanel` パラメータを URL につけ足すことでページをロードするときに開くパネルを指定できるようになりました。たとえば、`?sfWebDebugPanel=config` を追加すれば config パネルを開くように Web デバッグツールバーはレンダリングされます。

パネルは Web デバッグツールバーの `request_parameters` オプションにアクセスすることでリクエストパラメータをインスペクトします:

    [php]
    $requestParameters = $this->webDebug->getOption('request_parameters');

パーシャル
---------

### スロットの改善

スロットが提供されない場合、`get_slot()` と `include_slot()` ヘルパーは2番目の引数として受け取ったスロットのデフォルト内容を返します:

    [php]
    <?php echo get_slot('foo', 'bar') // foo スロットが定義されていなければ  bar が出力される ?>
    <?php include_slot('foo', 'bar') // foo スロットが定義されていなければ  bar が出力される ?>

ページャ
---------

`sfDoctrinePager` と `sfPropelPager` メソッドは `Iterator` と `Countable` インターフェイスを実装するようになりました:

    <?php if (count($pager)): ?>
      <ul>
        <?php foreach ($pager as $article): ?>
          <li><?php echo link_to($article->getTitle(), 'article_show', $article) ?></li>
        <?php endforeach; ?>
      </ul>
    <?php else: ?>
      <p>No results.</p>
    <?php endif; ?>

ビューキャッシュ
-----------------

ビューキャッシュマネージャは `factories.yml` のなかのパラメータを受け取ります。クラスを異なる方法で簡単に拡張できるようにビューのキャッシュキーの生成方法はリファクタリングされました。

`factories.yml` で2つのパラメータが利用できます:

  * `cache_key_use_vary_headers` (デフォルト: true): キャッシュキーが Vary ヘッダーの一部を含むか指定します。実際には、`vary` キャッシュパラメータで指定されるので、このパラメータはページキャッシュが HTTP ヘッダーに依存するかどうかを伝えます。

  * `cache_key_use_host_name` (デフォルト: true): キャッシュキーがホスト名の部分を含むか指定します。実際には、このパラメータはページキャッシュがホスト名に依存するかどうかを伝えます。

### キャッシュの強化

ビューキャッシュマネージャはスーパーグローバルの `$_GET` もしくは `$_POST` に値が存在するのかによってキャッシュを拒否しなくなりました。ロジックは現在のリクエストが `cache.yml` をチェックする前の GET リクエストメソッドであることを確認するだけです。このことは次のページがキャッシュ可能であることを意味します:

  * `/js/my_compiled_javascript.js?cachebuster123`
  * `/users?page=3`

リクエスト
---------

### `getContent()`

リクエストの内容は `getContent()` メソッドを通してアクセスできるようになりました。

### `PUT` と `DELETE` パラメータ

HTTP リクエストにおいて `PUT`、`DELETE` メソッドの Content-Type が `application/x-www-form-urlencoded` である場合、symfony は生のボディを解析し、通常の `POST` パラメータのようにアクセスできるパラメータを用意します。

アクション
----------

### `redirect()`

`sfAction:redirect()` メソッドは symfony 1.2 で導入された `url_for()` の機能と互換性をもつようになりました:

    [php]
    // symfony 1.2
    $this->redirect(array('sf_route' => 'article_show', 'sf_subject' => $article));

    // symfony 1.3/1.4
    $this->redirect('article_show', $article);

この強化内容は `redirectIf()` と `redirectUnless()` にも適用されました。

ヘルパー
--------

### `link_to_if()`、`link_to_unless()`

`link_to_if()` と `link_to_unless()` ヘルパーは symfony 1.2 で導入された `link_to()` メソッドのシグネチャと互換性をもつようになりました:

    [php]
    // symfony 1.2
    <?php echo link_to_unless($foo, '@article_show?id='.$article->getId()) ?>

    // symfony 1.3/1.4
    <?php echo link_to_unless($foo, 'article_show', $article) ?>

コンテキスト
-----------

`sfContext` にメソッドを動的に追加するために `context.method_not_found` をリスニングできます。プラグインから遅延ロードファクトリを追加する場合に役立ちます:

    [php]
    class myContextListener
    {
      protected
        $factory = null;

      public function listenForMethodNotFound(sfEvent $event)
      {
        $context = $event->getSubject();

        if ('getLazyLoadingFactory' == $event['method'])
        {
          if (null === $this->factory)
          {
            $this->factory = new myLazyLoadingFactory($context->getEventDispatcher());
          }

          $event->setReturnValue($this->factory);

          return true;
        }
      }
    }
