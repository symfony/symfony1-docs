symfony 1.3/1.4 の新しい機能
=============================

このチュートリアルでは symfony 1.3/1.4 の新しい機能をおおまかに説明します。このチュートリアルの対象読者は symfony 1.2 で作業経験があり、symfony 1.3/1.4 の新しい機能をてっとり早く学びたい方です。

symfony 1.3/1.4 と互換性のある PHP のバージョンは5.2.4およびそれ以降であることにご了承願います。

symfony 1.2 からアップグレードする手順については、[「プロジェクトを1.2から1.3/1.4にアップグレードする」](http://www.symfony-project.org/tutorial/1_4/ja/upgrade)のページをご参照ください。プロジェクトを symfony 1.3/1.4 に安全にアップグレードするために必要な情報をすべて得られます。


メーラー
--------

Swift Mailer 4.0 をもとにした標準メーラーが新たに導入されました。

アクションクラスのなかで `composeAndSend()` メソッドを呼び出せば、メールをかんたんに送信できます。

    [php]
    $this->getMailer()->composeAndSend('from@example.com', 'to@example.com', 'Subject', 'Body');

メールを送信するタイミングは `compose()` メソッドを使って柔軟に調整できます。メッセージにファイルを添付する方法は次のようになります。

    [php]
    $message = $this->getMailer()->
      compose('from@example.com', 'to@example.com', 'Subject', 'Body')->
      attach(Swift_Attachment::fromPath('/path/to/a/file.zip'))
    ;
    $this->getMailer()->send($message);

メーラーの機能は充実しているので、くわしい説明は公式マニュアルをご参照ください。

セキュリティ
-----------

`generate:app` タスクで作られた直後のアプリケーションでは、セキュリティの設定はデフォルトで有効になっています。

  * `escaping_strategy`: デフォルトは `true` です (`--escaping-strategy` オプションで無効にできます)。
  * `escaping_strategy`: デフォルトは `true` です (`--escaping-strategy` オプションで無効にできます)。

  * `csrf_secret`: デフォルトでは、ランダムなパスワードが生成され、CSRF 防止機能は有効になっています (`--csrf-secret` オプションで無効にできます)。初期パスワードを変更しておくことをぜひおすすめします。パスワードを変更するには、`settings.yml` ファイルを編集するか、`--csrf-secret` オプションで指定します。

ウィジェット
------------

### 標準ラベル

フィールド名から自動生成されたラベルにサフィックスの `_id` がつかなくなりました。

  * `first_name` => First name (以前と同じ)
  * `author_id` => Author (以前は「Author id」)

### `sfWidgetFormInputText`

`sfWidgetFormInput` クラスは抽象クラスに変更され、テキスト入力フィールドは `sfWidgetFormInputText` クラスによって生成されるようになりました。この変更によってフォームクラスのイントロスペクションをおこないやすくなりました。

### 国際対応ウィジェット

次の４つのウィジェットが導入されました。

  * `sfWidgetFormI18nChoiceLanguage`
  * `sfWidgetFormI18nChoiceCurrency`
  * `sfWidgetFormI18nChoiceCountry`
  * `sfWidgetFormI18nChoiceTimezone`

これらのうち、最初の3つは廃止予定の `sfWidgetFormI18nSelectLanguage`、`sfWidgetFormI18nSelectCurrency` と `sfWidgetFormI18nSelectCountry` ウィジェットの置き換えです。

### 流れるようなインターフェイス

ウィジェットに流れるようなインターフェイスが実装されました。

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
----------

### `sfValidatorRegex`

`sfValidatorRegex` バリデータに `must_match` オプションが新たに導入され、このオプションに `false` をセットすると、正規表現は渡されるバリデータにマッチしなくなります。

`sfValidatorRegex` バリデータの `pattern` オプションにセットできる値の必須条件が呼び出されるときに正規表現を返す `sfCallable` のインスタンスに変更されました。

### `sfValidatorUrl`

`sfValidatorUrl` バリデータに `protocols` オプションが新たに導入されました。次のように特定のプロトコルに制限できるようになりました。

    [php]
    $validator = new sfValidatorUrl(array('protocols' => array('http', 'https')));

デフォルトでは、次のプロトコルが許可されています。

 * `http`
 * `https`
 * `ftp`
 * `ftps`

### `sfValidatorSchemaCompare`

`sfValidatorSchemaCompare` クラスに比較演算のための2つの定数が新たに導入されました。

 * `IDENTICAL` は `===` と同等です。
 * `NOT_IDENTICAL` は `!==` と同等です。

### `sfValidatorChoice`、`sfValidator(Propel|Doctrine)Choice`

`sfValidatorChoice`、`sfValidatorPropelChoice` そして `sfValidatorDoctrineChoice` バリデータに2つのオプションが新たに導入されました。 `multiple` オプションに `true` がセットされている場合にかぎり、これらのバリデータのオプションは有効です。

 * `min` 必須選択の最小数
 * `max` 必須選択の最大数

### 国際対応バリデータ

次のバリデータが導入されました。

 * `sfValidatorI18nChoiceTimezone`

### デフォルトのエラーメッセージ

`sfForm::setDefaultMessage()` メソッドを呼び出すことで、グローバルなエラーメッセージのデフォルトを定義できるようになりました。

    [php]
    sfValidatorBase::setDefaultMessage('required', 'This field is required.');

上記のコードはすべてのバリデータのデフォルトメッセージである 'Required.' をオーバーライドします。デフォルトメッセージはバリデータが生成される前に定義しておかなければならないことにご注意ください (最適な場所はコンフィギュレーションクラスです)。

>**NOTE**
>`setRequiredMessage()` と `setInvalidMessage()` メソッドが廃止予定になりましたので、新たに導入された `setDefaultMessage()` メソッドを代わりに呼び出します。

エラーメッセージは次のように決められます。

  * バリデータが初期化される際に渡されたメッセージが探索されます (バリデータのコンストラクタの第2引数経由)。

  * コンストラクタで指定されていなければ、`setDefaultMessage()` メソッドで指定されたデフォルトメッセージが探索されます。

  * `setDefaultMessage()` メソッドで指定されていなければ (メッセージが `addMessage()` メソッドで追加された場合)、バリデータ自身で定義されているデフォルトメッセージに戻ります。

### 流れるようなインターフェイス

バリデータに流れるようなインターフェイスが実装されるようになりました。

  * `sfValidatorSchema`: `setPreValidator()`、`setPostValidator()`

  * `sfValidatorErrorSchema`: `addError()`、`addErrors()`

  * `sfValidatorBase`: `addMessage()`、`setMessage()`、
  `setMessages()`、`addOption()`、`setOption()`、
  `setOptions()`、`addRequiredOption()`

### `sfValidatorFile`

`php.ini` ファイルの `file_uploads` ディレクティブが無効になっている場合、`sfValidatorFile` のインスタンスが生成される際に例外が投げられます。

フォーム
--------

### `sfForm::useFields()`

新たに導入された `sfForm::useFields()` メソッドは、引数に渡されたフィールドを除いて、隠しフィールドではないすべてのフィールドをフォームから削除します。不要なフィールドの割り当てを解除するよりも、フォームで保ちたいフィールドを明確に指定するほうがかんたんな場合があります。たとえば、基底フォームに新しいフィールドを追加する場合、これらのフィールドは明確に追加されるまでフォームに表示されることはありません (モデルフォームのなかで関連テーブルに新しいカラムを追加する場合を思い浮かべてください)。

    [php]
    class ArticleForm extends BaseArticleForm
    {
      public function configure()
      {
        $this->useFields(array('title', 'content'));
      }
    }

デフォルトでは、フィールドの配列はフィールドの順序変更にも使われます。自動的な並べ替えを無効にするには、`useFields()` メソッドの第2引数に `false` を渡します。

### `sfForm::getEmbeddedForm($name)`

`->getEmbeddedForm()` メソッドを使って特定の埋め込みフォームにアクセスできるようになりました。

### `sfForm::renderHiddenFields()`

`->renderHiddenFields()` メソッドは埋め込みフォームからの隠しフィールドをレンダリングするようになりました。また、再帰処理を無効にする引数が導入されました。これはフォーマッタを使って埋め込みフォームをレンダリングする際に役立ちます。

    [php]
    // 埋め込みフォームからのフィールドを含めて、すべての隠しフィールドをレンダリングします
    echo $form->renderHiddenFields();

    // 再帰処理なしで隠しフィールドをレンダリングします
    echo $form->renderHiddenFields(false);

### `sfFormSymfony`

新たに導入された `sfFormSymfony` クラスは symfony のフォームシステムにイベントディスパッチャをもたらします。`self::$dispatcher` を通じてフォームクラス内部のディスパッチャにアクセスできます。次のフォームイベントが symfony によって通知されます。

  * `form.post_configure`: このイベントはフォームが設定された後で通知されます。
  * `form.filter_values`: このイベントは、マージされ、汚染されているパラメータと、バインドされる直前のファイルの配列にフィルタをかけます。
  * `form.validation_error`: フォームバリデーションが通らないときに、このイベントが通知されます。
  * `form.method_not_found`: 見つからないメソッドが呼び出されるときに、このイベントが通知されます。


### `BaseForm`

`BaseForm` クラスがすべての新しいプロジェクトに用意されるようになりました。このクラスの用例は Form コンポーネントを拡張したり、プロジェクト固有の機能を追加することなどです。`sfDoctrinePlugin` と `sfPropelPlugin` クラスによって生成されるフォームはこのクラスを自動的に継承します。追加のフォームクラスを作るのであれば、継承させるクラスは `sfForm` ではなく `BaseForm` です。

### `sfForm::doBind()`

ロジックをわかりやすくするために、汚染されているパラメータのクリーニングは `->doBind()` メソッドに隔離されました。このメソッドは `->bind()` メソッドからパラメータとファイルのマージずみの配列を受けとります。

### `sfForm(Doctrine|Propel)::doUpdateObject()`

ロジックをわかりやすくするために、Doctrine と Propel 対応のフォームクラスに `->doUpdateObject()` メソッドが導入されました。このメソッドはすでに `->processValues()` メソッドによって処理ずみの値の配列を `->updateObject()` メソッドから受けとります。

### `sfForm::enableLocalCSRFProtection()` と `sfForm::disableLocalCSRFProtection()`

`sfForm::enableLocalCSRFProtection()` と `sfForm::disableLocalCSRFProtection()` メソッドを使えば、自前のフォームクラスの `configure()` メソッドから CSRF 防止機能をかんたんに設定できます。

CSRF 防止機能を無効にするには、`configure()` メソッド呼び出しのなかで次のコードを書き加えます。

    [php]
    $this->disableLocalCSRFProtection();

フォームインスタンスを作る際に CSRF 対策の秘密の文字列を渡していたとしても、`disableLocalCSRFProtection()` メソッドを呼び出して CSRF 防止機能を無効にすることができます。

### 流れるようなインターフェイス

`sfForm` に流れるようなインターフェイスが実装されました。

`addCSRFProtection()`、`setValidators()`、`setValidator()`、
`setValidatorSchema()`、`setWidgets()`、`setWidget()`、
`setWidgetSchema()`、`setOption()`、`setDefault()` そして `setDefaults()`

オートローダ
-------------

PHP にならい、symfony のすべてのオートローダは大文字と小文字を区別しなくなりました。

### `sfAutoloadAgain` (実験的な機能)

デバッグモードでの用途を目的とする特殊なオートローダが導入されました。新たに導入された `sfAutoloadAgain` クラスは symfony の標準オートローダをリロードし、該当するクラスを求めてファイルシステムを探索します。純粋な効果は、プロジェクトに新しいタスクを追加した後で `symfony cc` コマンドを実行しなくてすむことです。

テスト
-----

### テストの実行時間の短縮

大規模なテストスイートでテストが通らない場合、テストを修正するたびに、何も壊れていないことを確認するためにテストスイート全体を再度実行することになるので時間がかかります。しかしながら、通らなかったテストが修正されていないのであれば、すべてのテストを再実行するのは時間の無駄です。symfony 1.3/1.4 では、`test:all` と `symfony:test` タスクに `--only-failed` オプション (ショートカットは `-f`) が導入され、前回の実行時に通らなかったテストだけを再実行できるようになりました。

    $ php symfony test:all --only-failed

どのようなしくみなのか説明します。まず最初に、すべてのテストは通常どおりに実行されます。引きつづきテストを実行すると、最後に実行したテストで通らなかったものだけが実行されます。コードを修正して一部のテストが通れば、次回以降の実行から除外されます。すべてのテストが通るようになると、完全なテストスイートが徹底的に実行されます。

### 機能テスト

リクエストが例外を発生させる場合において、レスポンステスターの `debug()` メソッドが出力する例外の説明のフォーマットが HTML からテキストに変更され、デバッグ作業がしやすくなりました。

`sfTesterResponse` クラスに `matches()` メソッドが新たに導入され、レスポンスの内容全体を正規表現で検索できるようになりました。このメソッドは `checkElement()` メソッドが扱えない XML 形式ではないレスポンスの処理にとても役立ちます。力不足の `contains()` メソッドの代わりに使うこともできます。

    [php]
    $browser->with('response')->begin()->
      matches('/I have \d+ apples/')->    // 正規表現を引数にとります
      matches('!/I have \d+ apples/')->   // 冒頭の ! は正規表現がマッチしないことを意味します
      matches('!/I have \d+ apples/i')->  // 正規表現の修飾子も追加できます
    end();

### JUnit と互換性のある XML 出力

`--xml` オプションを指定して JUnit と互換性のある XML ファイルを出力できるようになりました。

    $ php symfony test:all --xml=log.xml

### デバッグ作業の軽減

`--trace` オプションを指定して通らないテストに関するくわしい内容を出力できるようになり、デバッグ作業がやりやすくなりました。

    $ php symfony test:all -t

### lime によるカラー出力

symfony 1.3/1.4 では、lime のカラー出力が正しくおこなわれるようになりました。このことが意味するのは、たいていの場合、`lime_test` のインスタンスを作る際にコンストラクタの第2引数を省略できるということです。

    [php]
    $t = new lime_test(1);

### `sfTesterResponse::checkForm()`

レスポンステスターに `->checkForm()` メソッドが導入され、フォームのフィールドがすべて正しくレンダリングされ、レスポンスに含まれているかどうかを確認しやすくなりました。

    [php]
    $browser->with('response')->begin()->
      checkForm('ArticleForm')->
    end();

お望みであれば、フォームオブジェクトを渡すことができます。


    [php]
    $browser->with('response')->begin()->
      checkForm($browser->getArticleForm())->
    end();

CSS セレクタを指定するオプションが用意され、複数のフォームがレスポンスに含まれる場合、DOM のどの部分をテストするのかをきめ細かく指定できるようになりました。

    [php]
    $browser->with('response')->begin()->
      checkForm('ArticleForm', '#articleForm')->
    end();

### `sfTesterResponse::isValid()`

レスポンステスターに `->isValid()` メソッドが追加され、レスポンスが妥当な XML であるのかどうかをチェックできるようになりました。

    [php]
    $browser->with('response')->begin()->
      isValid()->
    end();

引数に `true` を渡せば、ドキュメントの種類に対するレスポンスをバリデートすることもできます。

    [php]
    $browser->with('response')->begin()->
      isValid(true)->
    end();

XSD もしくは RelaxNG スキーマをバリデーションの対象に入れるには、スキーマファイルへのパスを渡します。

    [php]
    $browser->with('response')->begin()->
      isValid('/path/to/schema.xsd')->
    end();

### `context.load_factories` をリスニングする

機能テストに `context.load_factories` イベントのリスナーを追加できるようになりました。symfony の以前のバージョンではこのリスナーを利用できませんでした。

    [php]
    $browser->addListener('context.load_factories', array($browser, 'listenForNewContext'));

### 改良された `->click()`

`->click()` メソッドに CSS セレクタを渡せるようになり、セマンティックにしたい要素をターゲットに指定することがとてもかんたんになりました。

    [php]
    $browser
      ->get('/login')
      ->click('form[action$="/login"] input[type="submit"]')
    ;

タスク
------

symfony CLI はターミナルウィンドウの幅を検出することを試み、フィットするように行を整えます。検出できなければ、CLI はウィンドウの幅をデフォルトの78カラムに合わせようとします。

### `sfTask::askAndValidate()`

`sfTask::askAndValidate()` メソッドが新たに導入され、ユーザーに質問して得られる入力内容をバリデートできるようになりました。

    [php]
    $answer = $this->askAndValidate('What is you email?', new sfValidatorEmail());

このメソッドはオプションの配列も受けとることができます (くわしい説明は API のドキュメントをご参照ください)。

### `symfony:test`

特定のプラットフォームで symfony が正しく動くのかチェックするために、symfony のテストスイートを実行することが必要な場合があります。従来では、この確認作業をおこなうために、symfony に附属している `prove.php` スクリプトの存在を知らなければなりませんでした。symfony 1.3/1.4 では、ほかのタスクと同じように扱える `symfony:test` タスクが導入され、symfony コアのテストスイートを組み込みのタスク、コマンドラインから実行できます。

    $ php symfony symfony:test

`php test/bin/prove.php` に慣れ親しんでいれば、同等の `php data/bin/symfony symfony:test` コマンドを選ぶこともできます。


### `project:deploy`

`project:deply` タスクは少し改良され、`-t` オプションが指定されていれば、ファイルの転送状況がリアルタイムで表示されるようになりました。`-t` オプションが指定されていなければ、タスクは何も表示しません。もちろんエラーの場合は除きます。エラーの場合に表示されるエラー情報の背景が赤色になり、問題が把握しやすくなりました。

### `generate:project`

`generate:project` タスクを実行するときに選ばれるデフォルトの ORM は Doctrine になりました。

    $ php /path/to/symfony generate:project foo

Propel のプロジェクトを生成するには、`--orm` オプションで指定します。

    $ php /path/to/symfony generate:project foo --orm=Propel

Propel もしくは Doctrine のどちらも使いたくなければ、`--orm` オプションに `none` を指定します。

    $ php /path/to/symfony generate:project foo --orm=none

新たに導入された `--installer` オプションに PHP スクリプトを指定することで、新たに生成されるプロジェクトを細かくカスタマイズできるようになりました。PHP スクリプトはタスクのメソッドのなかでも使うことができます。便利なメソッドがとりそろえられています。

    installDir()、runTask()、ask()、askConfirmation()、askAndValidate()、
    reloadTasks()、enablePlugin() そして disablePlugin()

くわしい解説は[公式ブログ](http://www.symfony-project.org/blog/2009/06/10/new-in-symfony-1-3-project-creation-customization)に掲載されています。

プロジェクトを生成するとき、著者の名前を第2引数に渡すことができます。symfony は新しいクラスを生成する際に PHPDoc の `@author` タグの値にタスクに渡された引数を使います。

    $ php /path/to/symfony generate:project foo "Joe Schmo"

### `sfFileSystem::execute()`

`sfFileSystem::sh()` メソッドはより強力な `sfFileSystem::execute()` メソッドに置き換わりました。このメソッドは `stdout` と `stderr` 出力のリアルタイム処理のコールバックを引数にとります。そして、両方の出力を配列として返すこともできます。使いかたの実例は `sfProjectDeployTask` クラスで見つかります。

### `task.test.filter_test_files`

`test:*` タスクは自分自身が実行される前に `task.test.filter_test_files` イベントを通過するようになりました。このイベントのために `arguments` と `options` パラメータが用意されています。

### `sfTask::run()` の強化

`sfTask:run()` メソッドは次のような引数とオプションからなる連想配列をとるようになりました。

    [php]
    $task = new sfDoctrineConfigureDatabaseTask($this->dispatcher, $this->formatter);
    $task->run(
      array('dsn' => 'mysql:dbname=mydb;host=localhost'),
      array('name' => 'master')
    );

以前のバージョンでは、コードは次のように書きます。

    [php]
    $task->run(
      array('mysql:dbname=mydb;host=localhost'),
      array('--name=master')
    );

### `sfBaseTask::setConfiguration()`

`sfBaseTask` クラスを継承するタスクを呼び出すとき、`->run()` メソッドのなかで `--application` と `--env` オプションを指定する必要はなくなりました。代わりに、`->setConfiguration()` メソッドを直接呼び出すだけで、コンフィギュレーションオブジェクトを設定できます。

    [php]
    $task = new sfDoctrineLoadDataTask($this->dispatcher, $this->formatter);
    $task->setConfiguration($this->configuration);
    $task->run();

以前のバージョンでは、コードを次のように書けばまだ動きます。

    [php]
    $task = new sfDoctrineLoadDataTask($this->dispatcher, $this->formatter);
    $task->run(array(), array(
      '--application='.$options['application'],
      '--env='.$options['env'],
    ));

### `project:disable` と `project:enable`

`project:disable` と `project:enable` タスクを使うことで、環境全体を無効もしくは有効にすることができるようになりました。

    $ php symfony project:disable prod
    $ php symfony project:enable prod

無効にするアプリケーションを環境ごとに指定することもできます。

    $ php symfony project:disable prod frontend backend
    $ php symfony project:enable prod frontend backend

これらのタスクは従来の機能と後方互換性があります。

    $ php symfony project:disable frontend prod
    $ php symfony project:enable frontend prod

### `help` と `list`

`help` と `list` タスクの出力形式に XML を選べるようになりました。

    $ php symfony list --xml
    $ php symfony help test:all --xml

これは新たに導入された `sfTask::asXml()` メソッドによるもので、このメソッドはタスクオブジェクトの XML 表現を返します。

XML は IDE などを開発するサードパーティに役立つでしょう。

### `project:optimize`

このタスクを実行すると、アプリケーションにおけるテンプレートファイルの保存場所がキャッシュされ、実行時においてディスクの読み込み回数が減ります。このタスクを使う場所は運用サーバーにとどめておくべきです。プロジェクトを変更するたびにタスクを再実行することをお忘れなく。

    $ php symfony project:optimize frontend

### `generate:app`

`generate:app` タスクがスケルトンディレクトリをチェックする場所はコアからプロジェクトの `data/skeleton/app` ディレクトリに変更されました。

### タスクからメールを送信する

`getMailer()` メソッドが導入され、タスクからメールを送信しやすくなりました。

### タスクでルーティングを使う

`getRouting()` メソッドが導入され、タスクからルーティングオブジェクトを取得しやすくなりました。

例外
----

### オートローディング

オートロードのあいだに例外が投げられるとき、symfony はこれらの例外を捕まえて、ユーザーにエラーを報告するようになりました。この措置によって、いくつかの「真っ白な」ページの問題が解決されました。

### デバッグツールバー

可能であれば、開発環境の例外ページでもデバッグツールバーが表示されるようになりました。

Propel との統合
---------------

Propel のバージョンは1.4にアップグレードされました。アップグレードのくわしい手順については、[公式サイト](http://www.propelorm.org/wiki/Documentation/1.4)をご参照ください。

### Propel のビヘイビア

Propel を拡張するためのビルダークラスは Propel 1.4 の新しいビヘイビアシステムに移植されました。

### `propel:insert-sql`

`propel:insert-sql` タスクはデータベースからすべてのデータを削除する前に本当に実行してよいのかたずねてきます。このタスクは、複数のデータベースからデータを削除するだけでなく、関連するデータベースコネクションの名前を表示するようになりました。

### `propel:generate-module`、`propel:generate-admin`、`propel:generate-admin-for-route`

`--actions-base-class` オプションが `propel:generate-module`、`propel:generate-admin` と `propel:generate-admin-for-route` タスクに導入され、生成モジュールにおいて、アクションの基底クラスのコンフィギュレーションを変更できるようになりました。

### Propel のビヘイビア

Propel 1.4 にビヘイビアが導入されました。そして、専用のビルダークラスはこの新しいシステムに移植されました。

`schema.yml` ファイルのなかで Propel モデルネイティブなビヘイビアを追加できます。

    classes:
      Article:
        propel_behaviors:
          timestampable: ~

古い構文を使うのであれば、次のように書きます。

    propel:
      article:
        _propel_behaviors:
          timestampable: ~

### フォーム生成を無効にする

特定のモデルにおいて、フォーム生成を無効にするには、Propel の `symfony` ビヘイビアに次のパラメータを渡します。

    classes:
      UserGroup:
        propel_behaviors:
          symfony:
            form: false
            filter: false

設定の変更を反映させるために、モデルを再構築しなければならないことにご注意ください。ビヘイビアが存在するようになるのは、ビヘイビアが添付されているモデルが再構築された後であるからです。

### Propel の異なるバージョンに切り替える

 Propel の異なるバージョンに切り替えることはかんたんです。必要なのは `ProjectConfiguration` クラスのなかで `sf_propel_runtime_path` と `sf_propel_generator_path` 設定変数にパスをセットするだけです。

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

`column` オプションにセットされている値がデフォルトの `id` である場合、デフォルトの必須要件の `\d+` は `sfObjectRouteCollection` クラスだけに適用されるようになりました。このことが意味するのは、(`slug` のように) 数字ではないカラムが指定されていれば、代わりの必須要件を用意する必要はないということです。

### `sfObjectRouteCollection` のオプション

`sfObjectRouteCollection` クラスに `default_params` オプションが新たに導入され、ルートごとにデフォルトパラメータを登録できるようになりました。

    [yml]
    forum_topic:
      class: sfDoctrineRouteCollection
      options:
        default_params:
          section: forum

CLI
---

### カラー出力

symfony CLI を使うとき、symfony は使っているコンソールがカラー出力をサポートしているかどうかを推測します。ただし、たとえば Cygwin を使っている場合には推測がまちがっている可能性があります (Windows プラットフォームではカラー出力がつねにオフだからです)。

グローバルオプションの `--color` を渡して、カラー出力を強制できるようになりました。

国際対応
---------

### データの更新

国際対応オペレーションに使われる `ICU` プロジェクトからのデータはすべて更新されました。symfony には約330個のロケールファイルが収められており、symfony 1.2 と比べると約70個増えました。ですので、たとえば、言語リストの10番目の項目をチェックするテストケースが通らない可能性があることにご注意ください。

### ユーザーロケールを基準にソートする

ユーザーロケールに依存するデータのソートもすべてロケールに依存して実行されるようになりました。`sfCultureInfo` クラスの `sortArray()` メソッドはこの用途に使うことができます。

プラグイン
----------

symfony 1.3/1.4 以前では、`sfDoctrinePlugin` と `sfCompat10Plugin` 以外のすべてのプラグインがデフォルトで有効でした。

    [php]
    class ProjectConfiguration extends sfProjectConfiguration
    {
      public function setup()
      {
        // 互換性のために必要なプラグインだけ有効にします
        $this->enableAllPluginsExcept(array('sfDoctrinePlugin', 'sfCompat10Plugin'));
      }
    }

symfony 1.3/1.4 で新しいプロジェクトでプラグインを有効にするには、`ProjectConfiguration` クラスのなかで明確に宣言しなければなりません。

    [php]
    class ProjectConfiguration extends sfProjectConfiguration
    {
      public function setup()
      {
        $this->enablePlugins('sfDoctrinePlugin');
      }
    }

`plugin:install` タスクはインストールするプラグインを自動的に有効にします (そして `plugin:uninstall` タスクはプラグインを無効にします)。Subversion を通じてプラグインをインストールする場合、手動で有効にする必要があります。

`sfProtoculousPlugin` もしくは `sfCompat10Plugin` のようなコアプラグインを使うのであれば、必要なのは `ProjectConfiguration` クラスのなかで `enablePlugins()` メソッドを呼び出すだけです。

>**NOTE**
>symfony 1.2 からプロジェクトをアップグレードする場合、古いふるまいは有効な状態に保たれます。これはアップグレードタスクが `ProjectConfiguration` クラスのファイルを変更しないからです。新しいふるまいが適用されるのは symfony 1.3/1.4 の新規プロジェクトにかぎられます。

### `sfPluginConfiguration::connectTests()`

新たに導入された `setupPlugins()` メソッドのなかでプラグインコンフィギュレーションの `->connectTests()` メソッドを呼び出せば、プラグインのテストを `test:*` タスクに結びつけることができます。

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

`sf_file_link_format` 設定に値がセットされていれば、symfony はファイルパスをクリック可能なリンクの形式 (すなわちデバッグ例外のテンプレート) に整え、値がセットされていなければ、symfony は PHP コンフィギュレーションの `xdebug.file_link_format` ディレクティブの値を探すようになりました。

たとえば、ファイルを TextMate で開くには、`settings.yml` ファイルで次のコードを書き加えます。

    [yml]
    all:
      .settings:
        file_link_format: txmt://open?url=file://%f&line=%l

`%f` プレースホルダはファイルの絶対パスに、`%l` プレースホルダは行数に置き換わります。

Doctrine との統合
-----------------

Doctrine のバージョンは1.2にアップグレードされました。アップグレードのくわしい手順については、[Doctrine の公式サイト](http://www.doctrine-project.org/documentation/1_2/ja)をご参照ください。

### フォームクラスを生成する

Doctrine の YAML スキーマファイルのなかで symfony の追加オプションを指定できるようになりました。そして、フォームとフィルタクラスの生成を無効にするオプションもいくつか導入されました。

たとえば典型的な多対多のリファレンスモデルでは、フォームもしくはフィルタフォームクラスを生成させる必要はありません。ですので、スキーマを次のように書くことができます。

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

モデルクラスからフォームが生成される場合、生成される子クラスにはモデルクラスの継承関係が反映され、同じ継承構造にしたがうフォームが生み出されます。

### 新しいタスク

Doctrine で開発するときに役立つタスクが新たに導入されました。

#### モデルテーブルを作る

指定されたモデルの配列に対応するテーブルを個別に作れるようになりました。Doctrine はテーブルを削除した後で、あなたに代わってテーブルを再構築してくれます。既存のプロジェクト/データベースで新しいモデルを開発していて、データベース全体を消去せずにテーブル群を再構築する際に役立ちます。

    $ php symfony doctrine:create-model-tables Model1 Model2 Model3

#### モデルファイルを削除する

YAML スキーマファイルのなかで、モデルのコードや名前を変更したり、使わなくなったモデルを削除することがよくあります。このような作業をおこなうと、孤児となったモデル、フォームそしてフィルタクラスが発生します。`doctrine:delete-model-files` タスクを使って、モデルに関連する生成ファイルを手作業で削除できるようになりました。

    $ php symfony doctrine:delete-model-files ModelName

上記の例では、タスクは関連する生成ファイルを見つけ、削除してよいかたずねてきます。

#### モデルファイルをお掃除する

`doctrine:clean-model-files` タスクを実行すれば、上記のプロセスを自動化して、ディスクに存在するが YAML スキーマファイルには存在しないモデルを見つけることができます。

    $ php symfony doctrine:clean-model-files

上記のコマンドは YAML スキーマファイルと生成モデルやファイルを比較し、どれを削除するのかを決めます。これらのモデルは `doctrine:delete-model-files` タスクに渡されます。タスクは、ファイルを自動的に削除する前に本当に実行してよいのかたずねてきます。

#### 何でもビルドする

`doctrine:build` タスクが新たに導入され、symfony や Doctrine にビルドさせたいものを明確に指定できるようになりました。この柔軟性のある解決策が取り込まれたことにともない、さまざまなタスクが廃止予定になりましたが、これらのタスクを組み合わせて得られる機能はこのタスクによって再現できます。

`doctrine:build` タスクの使いかたは次のようになります。

    $ php symfony doctrine:build --db --and-load

上記の例では、データベースを削除してから (`:drop-db`)、作り直し (`:build-db`)、`schema.yml` ファイルのなかで設定されているテーブルを作り (`:insert-sql`)、フィクスチャデータをロードします (`:data-load`)。

    $ php symfony doctrine:build --all-classes --and-migrate

上記の例では、モデル (`:build-model`)、フォーム (`:build-forms`)、フォームフィルタ (`:build-filters`) を生成し、保留されているマイグレーション (`:migrate`) を実行します。

    $ php symfony doctrine:build --model --and-migrate --and-append=data/fixtures/categories.yml

上記の例では、モデルを生成し (`:build-model`)、データベースのマイグレーションを実行し (`:migrate`)、そしてカテゴリのフィクスチャデータをつけ加えます (`:data-load --append --dir=/data/fixtures/categories.yml`)。

くわしい説明は `doctrine:build` タスクのヘルプページをご参照ください。

#### 新しいオプション: `--migrate`

次の一連のタスクは `--migrate` オプションを受けつけるようになりましたので、ネストの `doctrine:insert-sql` タスクを `doctrine:migrate` タスクに置き換えます。

  * `doctrine:build-all`
  * `doctrine:build-all-load`
  * `doctrine:build-all-reload`
  * `doctrine:build-all-reload-test-all`
  * `doctrine:rebuild-db`
  * `doctrine:reload-data`

#### `doctrine:generate-migration --editor-cmd`

`doctrine:generate-migration` タスクに `--editor-cmd` オプションが導入され、生成されたマイグレーションクラスを編集しやすくなりました。

    $ php symfony doctrine:generate-migration AddUserEmailColumn --editor-cmd=mate

上記の例では、マイグレーションクラスが生成され、新しいファイルが TextMate で開かれます。

#### `doctrine:generate-migrations-diff`

新たに導入されたこのタスクは、新旧のスキーマをもとに完全なマイグレーションクラスを自動的に生成します。

#### 特定のコネクションを作成もしくは削除する

`doctrine:build-db` と `doctrine:drop-db` タスクを実行する際に、データベースコネクションを指定できるようになりました。

    $ php symfony doctrine:drop-db master slave1 slave2

### 日付のセッターとゲッター

2つのメソッドが新たに導入され、Doctrine の日付とタイムスタンプの値を PHP の DateTime インスタンスとして取得できるようになりました。

    [php]
    echo $article->getDateTimeObject('created_at')
      ->format('m/d/Y');

`setDateTimeObject()` メソッドを呼び出し、有効な `DateTime` インスタンスを渡すだけで、日付の値もセットできます。

    [php]
    $article->setDateTimeObject('created_at', new DateTime('09/01/1985'));

### `doctrine:migrate --down`

`doctrine:migrate` タスクは `--up` と `--down` オプションを受けつけるようになりました。これらのオプションをつければ、スキーマがリクエストされる方向に1回でマイグレートされます。

    $ php symfony doctrine:migrate --down

### `doctrine:migrate --dry-run`

データベースが DDL 文のロールバックをサポートしている場合 (MySQL はサポートしていません)、新たに導入された `--dry-run` オプションをつけることができます。

    $ php symfony doctrine:migrate --dry-run

### DQL タスクにおいて表をデータ出力形式に選ぶ

`doctrine:dql` タスクに `--table` オプションが新たに導入され、データ出力形式の選択肢として、従来の YAML に加えて MySQL のコマンドライン出力と似た表形式も追加されました。

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

`doctrine:dql` タスクはクエリパラメータも引数にとるように強化されました。

    $ php symfony doctrine:dql "FROM Article a WHERE name LIKE ?" John%

### 機能テストのなかでクエリをデバッグする

`sfTesterDoctrine` クラスに `->debug()` メソッドが新たに導入されました。このメソッドは現在のコンテキストで実行されたクエリの情報を出力します。

    [php]
    $browser->
      get('/articles')->
      with('doctrine')->debug()
    ;

メソッドに数値を渡せば、少し前に実行されたクエリの履歴が表示され、文字列を渡せば、文字列の一部にマッチするクエリや正規表現にマッチするクエリだけが表示されます。

    [php]
    $browser->
      get('/articles')->
      with('doctrine')->debug('/from articles/i')
    ;

### `sfFormFilterDoctrine`

`sfFormFilterDoctrine` のインスタンス作成時において `query` オプションを通じて `Doctrine_Query` のシードを提供できるようになりました。

    [php]
    $filter = new ArticleFormFilter(array(), array(
      'query' => $table->createQuery()->select('title, body'),
    ));

`->setTableMethod()` メソッド (もしくは `table_method` オプション) を通じて指定されたテーブルメソッドはクエリオブジェクトを返す必要はありません。次のコードはどれも有効な `sfFormFilterDoctrine` のテーブルメソッドです。

    [php]
    // symfony 1.2 およびそれ以降で動きます
    public function getQuery()
    {
      return $this->createQuery()->select('title, body');
    }

    // symfony 1.2 およびそれ以降で動きます
    public function filterQuery(Doctrine_Query $query)
    {
      return $query->select('title, body');
    }

    // symfony 1.3 およびそれ以降で動きます
    public function modifyQuery(Doctrine_Query $query)
    {
      $query->select('title, body');
    }

フォームフィルタをカスタマイズしやすくなりました。フィールドにフィルタをかけるために必要なのは、ウィジェットとそれを処理するメソッドを書き加えることだけです。

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

以前のバージョンでこのコードを動くようにするには、ウィジェットとメソッドを作ることに加えて、`getFields()` メソッドを拡張する必要がありました。

### Doctrine のコンフィギュレーションを変更する

`doctrine.configure` と `doctrine.configure_connection` イベントをリスニングしていれば、Doctrine のコンフィギュレーションを変更できます。このことが意味するのは、プラグインを有効にする場所が `sfDoctrinePlugin` クラスよりも優先される場所であるかぎり、プラグインから Doctrine のコンフィギュレーションをカスタマイズするのはかんたんであるということです。

### `doctrine:generate-module`、`doctrine:generate-admin`、`doctrine:generate-admin-for-route`

`--actions-base-class` オプションが `doctrine:generate-module`、`doctrine:generate-admin`、`doctrine:generate-admin-for-route` タスクに導入され、生成モジュールにおいて、アクションの基底クラスのコンフィギュレーションを変更できるようになりました。

### マジックメソッドの PHPDoc タグ

symfony によって Doctrine モデルに追加されるマジックメソッドのゲッターとセッターは生成された基底クラスの PHPDoc ヘッダーにあらわれます。IDE がコード入力補完をサポートしている場合、これらの `getFooBar()` と `setFooBar()` メソッドはモデルオブジェクトで見つかります。`FooBar` はキャメルケースのフィールド名です。

### Doctrine の異なるバージョンに切り替える

Doctrine の異なるバージョンに切り替えるのはかんたんで、必要なのは `ProjectConfiguration` クラスのなかで `sf_doctrine_dir` 設定変数にパスをセットするだけです。

    [php]
    // config/ProjectConfiguration.class.php
    public function setup()
    {
      $this->enablePlugins('sfDoctrinePlugin');

      sfConfig::set('sf_doctrine_dir', '/path/to/doctrine/lib');
    }

デバッグツールバー
------------------

### `sfWebDebugPanel::setStatus()`

デバッグツールバーにおいて、タイトルの背景色に影響を及ぼすステータスをパネルごとに指定できるようになりました。たとえば、`sfLogger::INFO` よりも優先順位が高いメッセージがログに記録されている場合、log パネルのタイトルの背景色は変わります。

### `sfWebDebugPanel` リクエストパラメータ

URL に `sfWebDebugPanel` パラメータをつけ足せば、ページをロードするときに開くパネルを指定できるようになりました。たとえば、`?sfWebDebugPanel=config` をつけ足せば、config パネルが開いた状態になるように、デバッグツールバーはレンダリングされます。

パネルはデバッグツールバーの `request_parameters` オプションにアクセスして、リクエストパラメータを検査します。

    [php]
    $requestParameters = $this->webDebug->getOption('request_parameters');

パーシャル
---------

### スロットの改善

スロットが提供されていなければ、`get_slot()` と `include_slot()` ヘルパーは第2引数に渡されたスロットのデフォルト内容を返すようになりました。

    [php]
    <?php echo get_slot('foo', 'bar') // foo スロットが定義されていなければ  bar が出力されます ?>
    <?php include_slot('foo', 'bar') // foo スロットが定義されていなければ  bar が出力されます ?>

ページャ
---------

`sfDoctrinePager` と `sfPropelPager` クラスは `Iterator` と `Countable` インターフェイスを実装するようになりました。

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

ビューキャッシュマネージャは `factories.yml` ファイルのなかのパラメータを受けとるようになりました。ビューのキャッシュキーの生成方法がリファクタリングされ、クラスを異なる方法でかんたんに拡張できるようになりました。

`factories.yml` ファイルのなかで2つのパラメータが利用できます。

  * `cache_key_use_vary_headers` (デフォルトは true): キャッシュキーが Vary ヘッダーの一部を含むか指定します。実例として、`vary` キャッシュパラメータのなかで指定することで、ページキャッシュが HTTP ヘッダーに依存しているのかどうかを伝えるのに使われていることがあげられます。

  * `cache_key_use_host_name` (デフォルトは true): キャッシュキーがホスト名の部分を含むか指定します。実例として、ページキャッシュをホスト名に依存しているかどうかを伝えることに使われていることがあげられます。

### キャッシュの強化

ビューキャッシュマネージャはスーパーグローバルの `$_GET` もしくは `$_POST` に値が存在しているのかどうかによってキャッシュを拒否しなくなりました。ロジックは現在のリクエストが `cache.yml` ファイルをチェックする前の GET リクエストメソッドであることを確認するだけです。このことが意味するのは、次のページをキャッシュできるようになったということです。

  * `/js/my_compiled_javascript.js?cachebuster123`
  * `/users?page=3`

リクエスト
---------

### `getContent()`

`getContent()` メソッドを通じてリクエストの内容にアクセスできるようになりました。

### `PUT` と `DELETE` メソッド

HTTP リクエストにおいて `PUT`、`DELETE` メソッドの Content-Type ヘッダーに `application/x-www-form-urlencoded` がセットされている場合、symfony は生のボディをパースし、通常の `POST` メソッドのようにアクセスできるパラメータを用意するようになりました。

アクション
----------

### `redirect()`

`sfAction:redirect()` メソッドは symfony 1.2 で導入された `url_for()` ヘルパーのシグネチャと互換性をもつようになりました。

    [php]
    // symfony 1.2
    $this->redirect(array('sf_route' => 'article_show', 'sf_subject' => $article));

    // symfony 1.3/1.4
    $this->redirect('article_show', $article);

この強化内容は `redirectIf()` と `redirectUnless()` メソッドにも適用されました。

ヘルパー
--------

### `link_to_if()`、`link_to_unless()`

`link_to_if()` と `link_to_unless()` ヘルパーは symfony 1.2 で導入された `link_to()` ヘルパーのシグネチャと互換性をもつようになりました。

    [php]
    // symfony 1.2
    <?php echo link_to_unless($foo, '@article_show?id='.$article->getId()) ?>

    // symfony 1.3/1.4
    <?php echo link_to_unless($foo, 'article_show', $article) ?>

コンテキスト
-----------

`context.method_not_found` イベントをリスニングしていれば、`sfContext` オブジェクトにメソッドを動的に追加できるようになりました。プラグインから遅延ロードファクトリを追加する際に役立ちます。

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
