symfony 1.3/1.4 の新しい機能
=============================

このチュートリアルでは symfony 1.3/1.4 のための技術的な内容をおおまかに紹介します。このチュートリアルは symfony 1.2 ですでに作業をしており、symfony 1.3/1.4 の新しい機能を速く学びたい開発者向けです。

最初に、symfony 1.3/1.4 は PHP 5.2.4 とそれ以降と互換性があることにご注意ください。

1.2からアップグレードしたいのであれば、symfony の配布ファイルの中で見つかる["プロジェクトを1.2から1.3/1.4にアップグレードする"](http://www.symfony-project.org/tutorial/1_4/ja/upgrade)ファイルをご覧ください。プロジェクトを symfony 1.3/1.4 に安全にアップグレードするために必要なすべての情報が手に入ります。


メーラー
--------

symfony 1.3/1.4 では SwiftMailer 4.1 に基づく新しい標準メーラーが用意されました。

メールの送信はシンプルでアクションから `composeAndSend()` メソッドを使うだけです:

    [php]
    $this->getMailer()->composeAndSend('from@example.com', 'to@example.com', 'Subject', 'Body');

より柔軟性をもたせる必要があれば、`compose()` メソッドを使い後で送信することもできます。添付ファイルをメッセージに追加する方法は次のとおりです:

    [php]
    $message = $this->getMailer()->
      compose('from@example.com', 'to@example.com', 'Subject', 'Body')->
      attach(Swift_Attachment::fromPath('/path/to/a/file.zip'))
    ;
    $this->getMailer()->send($message);

メーラーはとても強力なので、詳細な情報は公式マニュアルを参照してください。

セキュリティ
-----------

`generate:app` タスクで新しいアプリケーションを作るとき、セキュリティの設定項目はデフォルトで有効になるようになりました:

  * `escaping_strategy`: デフォルトではこの値は `true` です (`--escaping-strategy` オプションで無効にできます)。

  * `csrf_secret`: デフォルトでランダムなパスワードが生成されます。CSRF 防止機能は標準で有効です (`--csrf-secret` オプションで無効にできます)。`settings.yml` 設定ファイルを編集するか、`--csrf-secret` オプションを使うことで、初期パスワードを変更することを強くおすすめします。

ウィジット
---------

### 標準のラベル

ラベルがフィールド名で自動生成される場合、サフィックスの `_id` は削除されます:

  * `first_name` => First name (以前と同じ)
  * `author_id` => Author (以前は "Author id" )

### `sfWidgetFormInputText`

`sfWidgetFormInput` クラスは抽象クラスになりました。テキスト入力フィールドは `sfWidgetFormInputText` クラスで作られます。この変更によってフォームクラスのイントロスペクションはより簡単になりました。

### 国際化ウィジェット

次のウィジェットが追加されました:

  * `sfWidgetFormI18nChoiceLanguage`
  * `sfWidgetFormI18nChoiceCurrency`
  * `sfWidgetFormI18nChoiceCountry`
  * `sfWidgetFormI18nChoiceTimezone`

これらの最初の3つは廃止予定の `sfWidgetFormI18nSelectLanguage`、`sfWidgetFormI18nSelectCurrency` と `sfWidgetFormI18nSelectCountry` ウィジェットの置き換えです。

### 流れるようなインターフェイス

ウィジットは次のような流れるようなインターフェイスを実装するようになりました:

  * `sfWidgetForm`: `setDefault()`、`setLabel()`、`setIdFormat()`、`setHidden()`

  * `sfWidget`: `addRequiredOption()`、`addOption()`、`setOption()`、`setOptions()`、`setAttribute()`、`setAttributes()`

  * `sfWidgetFormSchema`: `setDefault()`、`setDefaults()`、`addFormFormatter()`、`setFormFormatterName()`、 `setNameFormat()`、`setLabels()`、`setLabel()`、`setHelps()`、`setHelp()`、`setParent()`

  * `sfWidgetFormSchemaDecorator`: `addFormFormatter()`、`setFormFormatterName()`、`setNameFormat()`、`setLabels()`、`setHelps()`、`setHelp()`、`setParent()`、`setPositions()`

バリデーター
------------

### `sfValidatorRegex`

`sfValidatorRegex` は新しい `must_match` オプションを持つようになりました。このオプションが `false` にセットされる場合、正規表現は渡すバリデーターにマッチしません。

`sfValidatorRegex` の `pattern` オプションは呼び出し時に正規表現を返す `sfCallable` のインスタンスにしなければならなくなりました。

### `sfValidatorUrl`

`sfValidatorUrl` は新しい `protocols` オプションを持つようになりました。次のように特定のプロトコルを許可することができるようになりました:

    [php]
    $validator = new sfValidatorUrl(array('protocols' => array('http', 'https')));

デフォルトでは次のプロトコルが許可されています:

 * `http`
 * `https`
 * `ftp`
 * `ftps`

### `sfValidatorSchemaCompare`

`sfValidatorSchemaCompare` クラスは２つの新しいコンパレーターを持つようになりました:

 * `IDENTICAL`、は`===`と同等;
 * `NOT_IDENTICAL`、は`!==`と同等;

### `sfValidatorChoice`、`sfValidatorPropelChoice`、`sfValidatorDoctrineChoice`

`sfValidatorChoice`、`sfValidatorPropelChoice` そして `sfValidatorDoctrineChoice` バリデーターは `multiple` オプションが `true` の場合のみ有効になるふたつの新しいオプションを持ちます:

 * `min` 選択される必要がある最小の数
 * `max` 選択される必要がある最大の数

### 国際化バリデーター

次のバリデーターが追加されました:

 * `sfValidatorI18nTimezone`

### 標準のエラーメッセージ

次のように `sfForm::setDefaultMessage()` メソッドを使うことでグローバル領域で標準のエラーメッセージを定義できるようになりました:

    [php]
    sfValidatorBase::setDefaultMessage('required', 'This field is required.');

上記のコードはすべてのバリデーターのためにデフォルトの'Required.'メッセージをオーバーライドします。標準のメッセージはどのバリデーターが作成される前に定義しておかなければならないことに注意してください (コンフィグレーションクラスがよい場所です)。

>**NOTE**
>`setRequiredMessage()` と `setInvalidMessage()` メソッドは廃止予定なので、新しい `setDefaultMessage()` メソッドを呼び出します。

symfony がエラーを表示するとき、使われるエラーメッセージは次のように決定されます:

  * symfony はバリデーターが作成されたときに渡されたメッセージを探します (バリデーターのコンストラクターの第２引数経由);

  * 定義されていなければ、`setDefaultMessage()` メソッドで定義される初期メッセージを探します;

  * もし、定義されていなければ、(メッセージが `addMessage()` メソッドで追加されているとき) バリデーター自身で定義された初期メッセージへ戻ります。

### 流れるようなインターフェイス

バリデーターは次のような流れるようなインターフェイスを実装するようになりました:

  * `sfValidatorSchema`: `setPreValidator()`、`setPostValidator()`

  * `sfValidatorErrorSchema`: `addError()`、`addErrors()`

  * `sfValidatorBase`: `addMessage()`、`setMessage()`、`setMessages()`、`addOption()`、`setOption()`、`setOptions()`、`addRequiredOption()`

### `sfValidatorFile`

`php.ini` で `file_uploads` が無効な場合 `sfValidatorFile` のインスタンスを作成するときに例外が投げられます。

フォーム
--------

### `sfForm::useFields()`

新しい `sfForm::useFields()` メソッドはフォームから引数として提供されるもの以外、隠しフィールドではないフィールドすべてを削除します。状況によって不要なフィールドの割り当てを解除する代わりにフォームで維持したいフィールドを明示的に指示するのが簡単になります。たとえば、新しいフィールドを基底フォームに追加するとき、これらは明示的に追加されるまでフォームで自動的に現われなくなります (モデルフォームで新しいカラムを関連テーブルに追加する場合を考えてください)。

    [php]
    class ArticleForm extends BaseArticleForm
    {
      public function configure()
      {
        $this->useFields(array('title', 'content'));
      }
    }

デフォルトでは、フィールドの配列はフィールドの順序を変更するためにも使われます。自動的な順序づけを無効にするには、`useFields()` に2番目の引数として `false` を 渡します。

### `sfForm::getEmbeddedForm($name)`

`->getEmbeddedForm()` メソッドを使って特定の組み込みフォームにアクセスできます。

### `sfForm::renderHiddenFields()`

`->renderHiddenFields()` メソッドは埋め込みフォームから隠しフィールドをレンダリングします。再帰を無効にする引数が追加されました。これはフォーマッターを使って埋め込みフォームをレンダリングする場合に便利です。

    [php]
    // 埋め込みフォームからのフィールドを含めて、すべての隠しフィールドをレンダリングする
    echo $form->renderHiddenFields();

    // 再帰なしで隠しフィールドをレンダリングする
    echo $form->renderHiddenFields(false);

### `sfFormSymfony`

新しい `sfFormSymfony` クラスはイベントディスパッチャーを symfony フォームに導入します。`self::$dispatcher` を通してフォームクラス内部のディスパッチャーにアクセスできます。次のフォームイベントは symfony によって通知されます:

  * `form.post_configure`:   このイベントはフォームが設定された後で通知される
  * `form.filter_values`:    このイベントは、マージされ汚染されたパラメーターと、バインドする直前のファイルの配列をフィルタリングする
  * `form.validation_error`: フォームバリデーションが通らないときこのイベントが通知される
  * `form.method_not_found`: 不明なメソッドが呼び出されるときにこのイベントが通知される


### `BaseForm`

symfony 1.3/1.4 のすべての新しいプロジェクトには Form コンポーネントを拡張するもしくはプロジェクト固有の機能を追加するために使うことができる `BaseForm` クラスが入ります。 `sfDoctrinePlugin` と `sfPropelPlugin` によって生成されるフォームは自動的にこのクラスを継承します。追加のフォームクラスを作るのであれば `sfForm` よりも `BaseForm` を継承すべきです。

### `sfForm::doBind()`

汚染されたパラメーターのクリーニングは開発者にわかりやすい `->doBind()` メソッドに隔離されました。このメソッドは `->bind()` からのパラメーターとファイルのマージされる配列を受け取ります。

### `sfForm(Doctrine|Propel)::doUpdateObject()`

Doctrine と Propel のフォームクラスに開発者が扱いやすい `->doUpdateObject()` メソッドが加えられました。このメソッドは すでに `->processValues()` によって処理された `->updateObject()` から値の配列を受けとります。

### `sfForm::enableLocalCSRFProtection()` と `sfForm::disableLocalCSRFProtection()`

`sfForm::enableLocalCSRFProtection()` と `sfForm::disableLocalCSRFProtection()` メソッドを使うとき、あなたのクラスの `configure()` メソッドから簡単に CSRF 防止機能を設定することができます。

CSRF 防止機能を無効にするには、次のような行を `configure()` メソッドに追加します:

    [php]
    $this->disableLocalCSRFProtection();

`disableLocalCSRFProtection()` を呼び出すことによって、フォームインスタンスを作成するときに CSRF 対策の秘密の文字列を渡したとしても CSRF 防止機能は無効になります。

### 流れるようなインターフェイス

`sfForm` メソッドは次のような流れるインターフェイスを実装するようになりました: `addCSRFProtection()`、`setValidators()`、`setValidator()`、`setValidatorSchema()`、`setWidgets()`、`setWidget()`、`setWidgetSchema()`、`setOption()`、`setDefault()`、そして `setDefaults()`

オートローダー
--------------

symfony のすべてのオートローダーは大文字と小文字を区別しないようになりました。PHP が大文字と小文字を区別をしないので、symfony はそれに合わせることにしたからです。

### `sfAutoloadAgain` (実験的な機能)

デバッグモードでの用途を目的とする特殊なオートローダーが追加されました。新しい `sfAutoloadAgain` クラスは symfony の標準オートローダーをリロードし問題のクラスを求めてファイルシステムを検索します。純粋な効果は新しいクラスをプロジェクトに追加した後に `symfony cc` を実行する必要がなくなることです。

テスト
-----

### テストのスピードアップ

大規模なテストスイートの場合、変更するたびに全てのテストを起動するのにとても時間をかかる可能性があります。特にテストが通らない場合などはそうでしょう。なぜならテストを修正するたびに、何も壊していないことを確認するためにテストスイート全体を再度実行することになるからです。しかし、テストが修正されない限り、全てのテストを再実行する必要はありません。symfony 1.3/1.4 では `test:all` と `symfony:test` タスクは前回の実行時に通らなかったテストだけを再実行する `--only-failed` (`-f` がショートカットになります) オプションを持つようになりました:

    $ php symfony test:all --only-failed

どのように動作するかを説明します: まず最初に、全てのテストはいつもどおりに実行されます。しかし引き続きテストを実行しても、最後のテストで通らなかったものだけが実行されます。コードを修正したら、テストは通り次回以降の実行からは除外されます。再び全てのテストが通ったら、あなたは完全なテストスイートを実行し、洗い流し繰り返すことができます。

### 機能テスト

リクエストが例外を生成するとき、レスポンステスターの `debug()` メソッドは HTML 標準出力の代わりに、人間が読める例外のテキストの説明を出力するようになりました。より簡単にデバッグできるようになります。

`sfTesterResponse` はレスポンスの内容全体に対して正規表現で検索を行える新しい `matches()` メソッドを持つようになりました。XML のようなレスポンスではないもの、それは `checkElement()` が使えないようなレスポンスですが、そういった場合にとても役立ちます。ひ弱だった `contains()` メソッドの代わりにも使うことができます:

    [php]
    $browser->with('response')->begin()->
      matches('/I have \d+ apples/')->    // 引数として正規表現をとる
      matches('!/I have \d+ apples/')->   // 冒頭の ! は正規表現がマッチしてはならないことを意味する
      matches('!/I have \d+ apples/i')->  // 追加の正規表現の修正子を追加することもできる
    end();

### JUnit と互換性のある XML 出力

テストタスクは `--xml` オプションを使うことで JUnit と互換性のある XML ファイルも出力できるようになりました:

    $ php symfony test:all --xml=log.xml

### 簡単なデバッグ

テストが通らなかったことをテストハーネスが報告するときにデバッグを簡単にするために、通らないものについて詳細な出力ができる `--trace` オプションを渡すことができるようになりました:

    $ php symfony test:all -t

### lime による出力の色づけ

symfony 1.3/1.4 では、lime は色づけを正しく行うようになりました。これが意味することは、ほとんどの場合において `lime_test` の lime コンストラクターの第２引数を省略できるということです:

    [php]
    $t = new lime_test(1);

### `sfTesterResponse::checkForm()`

フォームの全てのフィールドが正しくレンダリング処理されてレスポンスに含まれているかどうかをより簡単に確かめられるメソッドがレスポンステスターに収録されました:

    [php]
    $browser->with('response')->begin()->
      checkForm('ArticleForm')->
    end();

もしくは、望むのであれば、フォームオブジェクトを渡すことができます:


    [php]
    $browser->with('response')->begin()->
      checkForm($browser->getArticleForm())->
    end();

レスポンスが複数のフォームを含む場合は、どの DOM 部分をテストするかをピンポイントで指定する CSS セレクターを提供するオプションがあります:

    [php]
    $browser->with('response')->begin()->
      checkForm('ArticleForm', '#articleForm')->
    end();

### `sfTesterResponse::isValid()`

レスポンスが整形式の XML であるかをレスポンステスターの `->isValid()` メソッドでチェックできます:

    [php]
    $browser->with('response')->begin()->
      isValid()->
    end();

引数として `true` を渡すことでドキュメントの種類に対するレスポンスをバリデートすることもできます:

    [php]
    $browser->with('response')->begin()->
      isValid(true)->
    end();

代わりに、バリデートする XSD もしくは RelaxNG スキーマがある場合、このファイルへのパスを提供できます:

    [php]
    $browser->with('response')->begin()->
      isValid('/path/to/schema.xsd')->
    end();

### `context.load_factories` をリスニングする

`context.load_factories` イベントのリスナーを機能テストに追加できるようになりました。これは symfony の以前のバージョンではできませんでした。


    [php]
    $browser->addListener('context.load_factories', array($browser, 'listenForNewContext'));

### よりよい `->click()`

CSS セレクターを`->click()` メソッドに 渡すことが可能で、セマンティックにしたい要素をターゲットにするのがはるかに楽になります。

    [php]
    $browser
      ->get('/login')
      ->click('form[action$="/login"] input[type="submit"]')
    ;

タスク
------

symfony の CLI はターミナルウィンドウの幅を検出することを試み、ラインのフォーマットを合わせようとします。検出できない場合 CLI は幅をデフォルトの78カラムに合わせようとします。

### `sfTask::askAndValidate()`

ユーザーに質問をして得られた入力内容をバリデートする `sfTask::askAndValidate()` メソッドが新しくできました:

    [php]
    $anwser = $this->askAndValidate('What is you email?', new sfValidatorEmail());

このメソッドはオプションの配列を受けることもできます (より詳しい情報は API ドキュメントを参照)。

### `symfony:test`

ときどき、開発者は特定のプラットフォームで symfony が正しく動作するのかをチェックするために symfony のテストスイートを実行する必要があります。従来は、symfony に附属している `prove.php` スクリプトを実行し確認しなければなりませんでした。symfony 1.3/1.4 では組み込みのタスク、コマンドラインから symfony のコアテストスイートを起動できる `symfony:test` タスクが用意され、ほかのタスクと同じように使うことができます:

    $ php symfony symfony:test

`php test/bin/prove.php` に慣れていれば、同等の `php data/bin/symfony symfony:test` コマンドを使います。


### `project:deploy`

`project:deply` タスクはちょっと改良されました。リアルタイムでファイルの転送状況を表示するようになりました。ただし、`-t` オプションが渡されたときだけです。もしオプションが指定されていなければタスクは何も表示しません、もちろんエラーは除きます。エラー時には、エラーの情報を出力し簡単に問題を認識できるように赤色の背景に出力します。

### `generate:project`

symfony1.3/1.4 では、`generate:project` タスクを実行するとき、初期設定では ORM は Doctrine になります:

    $ php /path/to/symfony generate:project foo

Propel のプロジェクトを生成するには、`--orm` オプションを使います:


    $ php /path/to/symfony generate:project foo --orm=Propel

Propel もしくは Doctrine のどちらも使いたくない場合は、`--orm` オプションに `none` を渡します:

    $ php /path/to/symfony generate:project foo --orm=none

新しい `--installer` オプションのおかげで新しく生成されるプロジェクトをかなりカスタマイズできる PHP スクリプトを指定することができます。スクリプトはタスクで実行され、タスクのメソッドで使うことができます。次のようなより便利なメソッドがあります: `installDir()`、`runTask()`、`ask()`、`askConfirmation()`、`askAndValidate()`、`reloadTasks()`、 `enablePlugin()` そして `disablePlugin()`

より詳しい情報は公式ブログの[記事](http://www.symfony-project.org/blog/2009/06/10/new-in-symfony-1-3-project-creation-customization)にあります。

プロジェクトを生成するとき、2番目の引数として著者の名前を渡すことができます。これは symfony が新しいクラスを生成するときに phpdoc の `@author` タグに使う値を指定します。

    $ php /path/to/symfony generate:project foo "Joe Schmo"

### `sfFileSystem::execute()`

`sfFileSystem::execute()` メソッドは `sfFileSystem::sh()` メソッドをより強力な機能で置き換えます。このメソッドは `stdout` と `stderr` 出力のリアルタイム処理のコールバックをとります。また両方の出力を配列として返すこともできます。`sfProjectDeployTask` クラスで使い方の例を見つけることができます。

### `task.test.filter_test_files`

`test:*` タスクはこれらのタスクが実行される前に `task.test.filter_test_files` イベントを通過するようになりました。このイベントには `arguments` と `options` パラメーターがあります。

### `sfTask::run()` の強化

`sfTask:run()` に次のような引数の連想配列とオプションを渡すことができるようになりました:

    [php]
    $task = new sfDoctrineConfigureDatabaseTask($this->dispatcher, $this->formatter);
    $task->run(
      array('dsn' => 'mysql:dbname=mydb;host=localhost',
    ), array(
      'name' => 'master',
    ));

これまでのバージョンでは、次のようにすればまだ動きます:

    [php]
    $task->run(
      array('mysql:dbname=mydb;host=localhost'),
      array('--name=master')
    );

### `sfBaseTask::setConfiguration()`

PHP から `sfBaseTask` を継承するタスクを呼び出すとき、`->run()` に `--application` と `--env` オプションを渡す必要はもうありません。その代わりに、ただ `->setConfiguration()` を呼び出すだけで設定オブジェクトを直接セットすることができます。

    [php]
    $task = new sfDoctrineLoadDataTask($this->dispatcher, $this->formatter);
    $task->setConfiguration($this->configuration);
    $task->run();

これまでのバージョンでは、次のようにすればまだ動きます:

    [php]
    $task = new sfDoctrineLoadDataTask($this->dispatcher, $this->formatter);
    $task->run(array(), array(
      '--application='.$options['application'],
      '--env='.$options['env'],
    ));

### `project:disable` と `project:enable`

`project:disable` と `project:enable`タスクを使うことで、環境全体を無効、または有効にすることができるようになりました:

    $ php symfony project:disable prod
    $ php symfony project:enable prod

環境においてどのアプリケーションを無効にするかを指定することもできます:

    $ php symfony project:disable prod frontend backend
    $ php symfony project:enable prod frontend backend

これらのタスクはこれまでの機能と後方互換性があります:

    $ php symfony project:disable frontend prod
    $ php symfony project:enable frontend prod

### `help` と `list`

`help` と `list` タスクは情報を XML 形式で表示することができるようになりました:

    $ php symfony list --xml
    $ php symfony help test:all --xml

この出力は新しい `sfTask::asXml()` メソッドにもとづいており、これはタスクオブジェクトの XML 表現を返します。

たいていの場合において XML 出力は IDE のようなサードパーティーにとても役立つでしょう。

### `project:optimize`

このタスクを実行すればアプリケーションのテンプレートファイルの位置をキャッシュすることで実行時のディスクの読み込み回数を減らします。このタスクは運用サーバーでのみ使われます。プロジェクトを変更するたびにタスクを再実行することをお忘れなく。

    $ php symfony project:optimize frontend

### `generate:app`

`generate:app` タスクはコアに搭載されるデフォルトのスケルトンディレクトリの代わりにプロジェクトの `data/skeleton/app` ディレクトリのスケルトンディレクトリをチェックします。

### タスクからメールを送信する

`getMailer()` メソッドを使うことでタスクからメールを簡単に送信することができます。

### タスクでルーティングを使う

`getRouting()` メソッドを使うことでタスクからルーティングオブジェクトを簡単に取得できます。

例外
----

### オートローディング

オートロードの間に例外が投げられるとき、symfony はこれらを捕まえエラーをユーザーに出力します。これはいくつかの"真っ白な"ページの問題を解決します。

### Web デバッグツールバー

可能であれば、開発環境の例外ページで Web デバッグツールバーが表示されます。

Propel 統合
-----------

Propel はバージョン1.4にアップグレードされました。Propel のアップグレードに関する詳しい情報は[公式サイト](http://propel.phpdb.org/trac/wiki/Users/Documentation/1.4)を訪問してくださるようお願いします。

### Propel のビヘイビア

Propel を拡張するために symfony が依存するカスタムのビルダークラスは Propel 1.4 の新しいビヘイビアシステムに移植されました。

### `propel:insert-sql`

`propel:insert-sql` がデータベースから全てのデータを削除する前に確認を行います。このタスクは複数のデータベースからデータを削除することができるので、関連するデータベースの接続名も表示するようになりました。

### `propel:generate-module`、`propel:generate-admin`、`propel:generate-admin-for-route`

`propel:generate-module`、`propel:generate-admin` と `propel:generate-admin-for-route` タスクは生成モジュールのアクション基底クラスのコンフィギュレーションを可能にする `--actions-base-class` オプションをとります。

### Propel のビヘイビア

Propel 1.4 はビヘイビアの実装を導入しました。カスタムの symfony ビルダーはこの新しいシステムに移植されました。

Propel モデルネイティブなビヘイビアを Propel モデルに追加したい場合、`schema.yml` でも可能です:

    classes:
      Article:
        propel_behaviors:
          timestampable: ~

もしくは、古い `schema.yml` 構文を使う場合:

    propel:
      article:
        _propel_behaviors:
          timestampable: ~

### フォーム生成を無効にする

Propel の `symfony` ビヘイビアにパラメーターを渡すことで特定のモデルでのフォーム生成を無効にできます:

    classes:
      UserGroup:
        propel_behaviors:
          symfony:
            form: false
            filter: false

この設定が考慮される前にモデルをリビルドしなければならないことに注意してください。これはふるまいがモデルに添付されこれをリビルドした後でのみ存在するからです。

### 異なるバージョンの Propel を使う

Propel の異なるバージョンを使うのは簡単で `ProjectConfiguration` のなかで `sf_propel_runtime_path` と `sf_propel_generator_path` 設定変数をセットするだけです:

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

`column` オプションがデフォルトの `id` になっているとき、デフォルトの必須要件の `\d+` は `sfObjectRouteCollection` にだけ適用されるようになりました。(`slug` のような) 数字でないカラムが指定されているとき代わりの必須要件を用意する必要はないということです。

### `sfObjectRouteCollection` オプション

新しい `default_params` オプションが `sfObjectRouteCollection` に追加されました。これはそれぞれの生成ルートに登録されるデフォルトパラメーターを可能にします:

    [yml]
    forum_topic:
      class: sfDoctrineRouteCollection
      options:
        default_params:
          section: forum

CLI
---

### 出力の色づけ

symfony の CLI を使うとき、symfony はあなたが利用しているコンソールが色つき出力をサポートしているかどうかを推測しようとします。しかし、symfony は推測を間違える場合があります; たとえば、Cygwin を使っているときです (Windows プラットフォームではカラー出力は常に切られているからです)。

symfony 1.3/1.4 では、グローバルオプションの `--color` を渡すことで色つき出力することを強制できるようになりました。

国際化(I18N)
----

### データの更新

すべての国際化オペレーションに使われるデータは `ICU` プロジェクトから更新されました。symfony には約330のロケールファイルが付属しており、symfony 1.2 と比べると約70増えています。ですのでたとえば、言語リストの10番目の項目をチェックするテストケースが通らない可能性があることにご注意をお願いします。

### ユーザーロケールを基準にソートする

このロケールに依存するデータでのすべてのソートもロケールに依存して実行されます。この目的のために `sfCultureInfo->sortArray()` を使うことができます。

プラグイン
----------

symfony 1.3/1.4 以前では、`sfDoctrinePlugin` と `sfCompat10Plugin` 以外のすべてのプラグインはデフォルトで有効にされました:

    [php]
    class ProjectConfiguration extends sfProjectConfiguration
    {
      public function setup()
      {
        // 互換性のために望むプラグインだけ削除および有効にする
        $this->enableAllPluginsExcept(array('sfDoctrinePlugin', 'sfCompat10Plugin'));
      }
    }

symfony 1.3/1.4 では、新しく作られたプロジェクトでプラグインを使うためには `ProjectConfiguration` クラスで明示的に有効にしなければなりません:

    [php]
    class ProjectConfiguration extends sfProjectConfiguration
    {
      public function setup()
      {
        $this->enablePlugins('sfDoctrinePlugin');
      }
    }

`plugin:install` タスクはインストールするプラグインを自動的に有効にします (そして `plugin:uninstall` はプラグインを無効にします)。Subversion 経由でプラグインをインストールする場合、手動で有効にする必要があります。

`sfProtoculousPlugin` もしくは `sfCompat10Plugin` のようなコアプラグインを使いたい場合、必要なのは対応する `enablePlugins()` ステートメントを `ProjectConfiguration` クラスに追加することだけです。

>**NOTE**
>1.2からプロジェクトをアップグレードする場合、古いふるまいはアクティブなままです。これはアップグレードタスクが `ProjectConfiguration` ファイルを変更しないからです。このふるまいの変更は symfony 1.3/1.4 の新規プロジェクトのみです。

### `sfPluginConfiguration::connectTests()`

新しい `setupPlugins()` メソッドのなかでプラグインコンフィギュレーションの `->connectTests()` メソッドを呼び出すことで `test:*` タスクにプラグインのテストを接続することができます。

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

symfony 1.3/1.4 は可能であるときにファイルパスをクリック可能なリンクにフォーマットします (すなわちデバッグ例外のテンプレート)。`sf_file_link_format` がセットされる場合、この目的に使われ、そうでなければ、symfony は PHP コンフィギュレーションの `xdebug.file_link_format` の値を探します。

たとえば、TextMate でファイルを開きたい場合、次のコードを `settings.yml` に追加します:

    [yml]
    all:
      .settings:
        file_link_format: txmt://open?url=file://%f&line=%l

`%f` プレースホルダーはファイルの絶対パスに、`%l` プレースホルダーは行数に置き換えられます。

Doctrine との統合
-----------------

### フォームクラスを生成する

Doctrine は1.2にアップグレードされました。アップグレードに関する詳しい情報は [Doctrine のサイト](http://www.doctrine-project.org/documentation/1_2/ja)を訪問してくださるようお願いします。

### フォームクラスを生成する

symfony の追加オプションを Doctrine の YAML スキーマファイルで指定することが可能になりました。そしてフォームとフィルタークラスの生成を無効にするいくつかのオプションも追加しました。

たとえば、 典型的な多対多のリファレンスモデルでは、フォームもしくはフィルターフォームクラスを生成させる必要はありません。ですので次のようなことができます:

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

あなたのモデルクラスからフォームを生成するとき、モデルクラスは継承を含んでいます。生成された子クラスは継承を尊重し、同じ継承構造に続くフォームを生成します。

### 新しいタスク

Doctrine で開発するときに手助けしてくれる新しいタスクを導入しました。

#### モデルテーブルを作成する

指定モデルの配列のために個別のテーブルを作成することができるようになりました。テーブルを削除するときあなたに代わってテーブルを再作成してくれます。既存のプロジェクト/データベースで新しいモデルを開発するとき、データベース全体を一掃したくなく単にテーブル群を再構築したいときに役立ちます。

    $ php symfony doctrine:create-model-tables Model1 Model2 Model3

#### モデルファイルを削除する

YAML スキーマファイルでモデルや名前を変更したり、使わなくなったモデルを削除したりすることがよくあるでしょう。このようなことを行ったとき、孤児となったモデル、フォームそしてフィルタークラスが現れます。`doctrine:delete-model-files` タスクを使うことで、モデルに関連する生成ファイルを手動で掃除することができるようになりました。

    $ php symfony doctrine:delete-model-files ModelName

上記タスクは関連する生成ファイルを見つけ、そのファイルを削除したいかどうかあなたに確認する前にあなたに報告してくれます。

#### モデルファイルをクリーンにする

`doctrine:clean-model-files` タスクで上記プロセスを自動化しどのモデルがディスクに存在するが YAML スキーマファイルに存在しないかを見つけることができます。

    $ php symfony doctrine:clean-model-files

上記コマンドは YAML スキーマファイルと生成されたモデルやファイルと比較し、どれを削除すべきかを決定します。これらのモデルは `doctrine:delete-model-files` タスクに伝えられます。タスクは自動的に削除する前にどのファイルが削除されるのか確認を求めます。

#### データをリロードする

データフィクスチャを再読み込みするとき完全にデータベースを一掃したいことはよくあることです。`doctrine:build-all-reload` タスクはこれを行ってくれますが、モデルやフォームやフィルターの生成などほかのタスクを行っているだけです。そして、これは大規模なプロジェクトにおいては時間がかかるでしょう。そこで、単純に `doctrine:reload-data` タスクを使うことができるようになりました。

次のコマンドです。

    $ php symfony doctrine:reload-data

これは次のコマンド群を実行するのと同等です:

    $ php symfony doctrine:drop-db
    $ php symfony doctrine:build-db
    $ php symfony doctrine:insert-sql
    $ php symfony doctrine:data-load

#### 何でもビルドする

新しい `doctrine:build` タスクによって明確に symfony や Doctrine にビルドしてほしいものを何でも指定できます。このより柔軟性のある解決方法に合わせて廃止予定になった既存の多くのタスクを組み合わせることで得られる機能をこのタスクを複製します。

`doctrine:build` の使い方は次のとおりです:

    $ php symfony doctrine:build --db --and-load

これはデータベースを削除 (`:drop-db`) して作成 (`:build-db`) し、`schema.yml` にテーブル設定を作成 (`:insert-sql`) し、フィクスチャデータを読み込み (`:data-load`) ます。

    $ php symfony doctrine:build --all-classes --and-migrate

これはモデル (`:build-model`)、フォーム (`:build-forms`)、フォームフィルター (`:build-filters`) を生成し、保留されているマイグレーション (`:migrate`) を実行します。

    $ php symfony doctrine:build --model --and-migrate --and-append=data/fixtures/categories.yml

これはモデルを生成 (`:build-model`) し、データベースのマイグレーション (`:migrate`) を行い、そしてカテゴリーのフィクスチャデータ (`:data-load --append --dir=/data/fixtures/categories.yml`)を付け加えます。

より多くの情報は `doctrine:build` タスクのヘルプページを参照してください。

#### 新しいオプション: `--migrate`

次のタスクは `--migrate`オプションを受け入れるようになり、入れ子の `doctrine:insert-sql` タスクを `doctrine:migrate` に置き換えます。

  * `doctrine:build-all`
  * `doctrine:build-all-load`
  * `doctrine:build-all-reload`
  * `doctrine:build-all-reload-test-all`
  * `doctrine:rebuild-db`
  * `doctrine:reload-data`

#### `doctrine:generate-migration --editor-cmd`

`doctrine:generate-migration` タスクは `--editor-cmd` オプションを受け入れるようになりました。このオプションは編集を楽にするためにマイグレーションクラスが作成されると実行します。


    $ php symfony doctrine:generate-migration AddUserEmailColumn --editor-cmd=mate

この例はマイグレーションクラスを生成し TextMate で新しいファイルを開いています。

#### `doctrine:generate-migrations-diff`

この新しいタスクは新旧のスキーマをもとに、完全なマイグレーションクラスを自動的に生成します。

#### 特定の接続を作成もしくは削除する

`doctrine:build-db` と `doctrine:drop-db` を実行するときにデータベース接続を指定できるようになりました:

    $ php symfony doctrine:drop-db master slave1 slave2

### 日付のセッターとゲッター

Doctrine の日付とタイムスタンプの値を PHP の DateTime オブジェクトインスタンスとして取得するためのふたつの新しいメソッドが追加されました。

    [php]
    echo $article->getDateTimeObject('created_at')
      ->format('m/d/Y');

日付の値も `setDateTimeObject` メソッドを呼び出し有効な `DateTime` インスタンスを渡すだけでセットすることもできます。

    [php]
    $article->setDateTimeObject('created_at', new DateTime('09/01/1985'));

### `doctrine:migrate --down`

`doctrine:migrate` はスキーマをリクエストされる方向に一回でマイグレートする `up` と `down` オプションを受け入れます。

    $ php symfony doctrine:migrate --down

### `doctrine:migrate --dry-run`

データベースが DDL ステートメントのロールバックをサポートする場合 (MySQL はサポートしない)、新しい `dry-run` オプションを利用できます。

    $ php symfony doctrine:migrate --dry-run

### DQL タスクをテーブルのデータとして出力する

これまでは `doctrine:sql` コマンドを実行するとただ YAML 書式で出力されるだけでした。新しい `--table` オプションが追加されました。このオプションによってデータをテーブル表示で出力することができるようなり、MySQL のコマンドラインの出力に似たものになっています。

それで、次のようなことが可能になりました。

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

### クエリパラメータを `doctrine:dql` に渡す

`doctrine:dql` タスクもクエリパラメータを引数として受け取れるよう強化されました:

    $ php symfony doctrine:dql "FROM Article a WHERE name LIKE ?" John%

### 機能テストでクエリをデバッグする

`sfTesterDoctrine` クラスに `->debug()` メソッドが収録されました。このメソッドは現在のコンテクストで実行されたクエリの情報を出力します。

    [php]
    $browser->
      get('/articles')->
      with('doctrine')->debug()
    ;

メソッドに数値を渡すことで直近の実行されたクエリの履歴を見ることができ、文字列を渡すことで文字列の一部にマッチするものや正規表現にマッチするクエリだけを表示することができます。

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

`->setTableMethod()` (または `table_method` オプション) を通して指定されたテーブルメソッドはクエリオブジェクトを返す必要がありません。次はどれも有効な `sfFormFilterDoctrine` テーブルメソッドです:

    [php]
    // symfony >= 1.2 で動作する
    public function getQuery()
    {
      return $this->createQuery()->select('title, body');
    }

    // symfony >= 1.2　で動作する
    public function filterQuery(Doctrine_Query $query)
    {
      return $query->select('title, body');
    }

    // symfony >= 1.3　で動作する
    public function modifyQuery(Doctrine_Query $query)
    {
      $query->select('title, body');
    }

フォームフィルターのカスタマイズが簡単になりました。フィールドのフィルタリングを追加するには、必要なのはウィジェットとそれを処理するメソッドを追加することだけです。

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

以前のバージョンでは、これを動かすためにはウィジェットをメソッドを作ることに加えて`getFields()`を拡張する必要がありました。

### Doctrine を設定する

Doctrine を設定するために `doctrine.configure` と `doctrine.configure_connection` イベントをリスニングできます。このことはプラグインが `sfDoctrinePlugin` の前で有効にされている限り、プラグインから Doctrine のコンフィギュレーションを簡単にカスタマイズできることを意味します。

### `doctrine:generate-module`、`doctrine:generate-admin`、`doctrine:generate-admin-for-route`

`doctrine:generate-module`、`doctrine:generate-admin`、`doctrine:generate-admin-for-route` タスクは生成モジュールのアクション基底クラスのコンフィギュレーションを可能にする `--actions-base-class` オプションをとります。

### マジックメソッドの doc タグ

symfony が Doctrine モデルに追加するゲッターとセッターのマジックメソッドはそれぞれの生成基底クラスの PHPDoc ヘッダーに現れます。IDE がコード補完をサポートする場合、これらの `getFooBar()` と `setFooBar()` メソッドがモデルオブジェクトに現れることがわかります。`FooBar` はキャメルケースのフィールド名です。

### Doctrine の異なるバージョンを使う

Doctrine の異なるバージョンを使うのは簡単で `ProjectConfiguration` で `sf_doctrine_dir` 設定をセットするだけです:

    [php]
    // config/ProjectConfiguration.class.php
    public function setup()
    {
      $this->enablePlugins('sfDoctrinePlugin');

      sfConfig::set('sf_doctrine_dir', '/path/to/doctrine/lib');
    }

Web デバッグツールバー
----------------------

### `sfWebDebugPanel::setStatus()`

Web デバッグツールバーのそれぞれのパネルはタイトルの背景色に影響を及ぼすステータスを指定できるようになりました。たとえば、`sfLogger::INFO` よりも優先順位が高いメッセージがロギングされる場合、log パネルのタイトルの背景色は変わります。

### `sfWebDebugPanel` リクエストパラメーター

`sfWebDebugPanel` パラメーターを URL につけ加えることでページロードで開くパネルを指定できるようになりました。たとえば、`?sfWebDebugPanel=config` を追加すれば config パネルを開くように Web デバッグツールバーはレンダリングされます。

パネルは Web デバッグの `request_parameters` オプションにアクセスすることでリクエストパラメーターをインスペクトします:

    [php]
    $requestParameters = $this->webDebug->getOption('request_parameters');

パーシャル
---------

### スロットの改善

`get_slot()`と`include_slot()` ヘルパーはスロットが提供されない場合返すデフォルトのスロットの内容を指定するための2番目のパラメーターを受け取ります:

    [php]
    <?php echo get_slot('foo', 'bar') // もし`foo`スロットが定義されていなければ 'bar'が出力される ?>
    <?php include_slot('foo', 'bar') // もし`foo`スロットが定義されていなければ 'bar'が出力される ?>

ページャー
----------

`sfDoctrinePager` と `sfPropelPager` メソッドは `Iterator` と `Countable` インターフェイスを実装しています。

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
---------------

ビューキャッシュマネージャーは `factories.yml` でパラメーターを受け取ります。ビューのキャッシュキーの生成はクラスを楽に拡張できるように異なる方法でリファクタリングされてきました。

ふたつのパラメーターが `factories.yml` で利用できます:

  * `cache_key_use_vary_headers` (デフォルト: true): キャッシュキーが Vary ヘッダーの一部を含むか指定します。実際には、`vary` キャッシュパラメーターで指定されるので、これはページキャッシュが HTTP ヘッダーに依存するかどうかを伝えます。

  * `cache_key_use_host_name` (デフォルト: true): キャッシュキーがホスト名の部分を含むか指定します。実際には、これはページキャッシュがホスト名に依存するかどうかを伝えます。

### さらにキャッシュ

ビューキャッシュマネージャーは `$_GET` もしくは `$_POST` の配列に値が存在するのかによってキャッシュを拒絶することはありません。ロジックは現在のリクエストが `cache.yml` をチェックする前の GET リクエストメソッドであることを確認するだけです。このことは次のページがキャッシュ可能であることを意味します:

  * `/js/my_compiled_javascript.js?cachebuster123`
  * `/users?page=3`

リクエスト
---------

### `getContent()`

リクエストの内容は `getContent()` メソッドを通してアクセスできるようになりました。

### `PUT` と `DELETE` パラメーター

リクエストが `aplication/x-www-form-urlencoded` にセットされた Content-Type で HTTP の `PUT`、`DELETE` リクエストメソッドで来た場合に、symfony は生のボディを解析し、通常の `POST` パラメーターのようにアクセスできるパラメーターを作成します。

アクション
----------

### `redirect()`

`sfAction:redirect()` メソッドは symfony 1.2 で導入された `url_for()` の特徴と互換性を持つようになりました。

    [php]
    // symfony 1.2
    $this->redirect(array('sf_route' => 'article_show', 'sf_subject' => $article));

    // symfony 1.3
    $this->redirect('article_show', $article);

この強化は `redirectIf()` と `redirectUnless()` にも適用されました。

ヘルパー
--------

### `link_to_if()`、`link_to_unless()`

`link_to_if()` と `link_to_unless()` ヘルパーは symfony 1.2 で導入された `link_to()` メソッドと互換性を持つようになりました:

    [php]
    // symfony 1.2
    <?php echo link_to_unless($foo, '@article_show?id='.$article->getId()) ?>

    // symfony 1.3
    <?php echo link_to_unless($foo, 'article_show', $article) ?>

コンテキスト
-----------

`sfContext` にメソッドを動的に追加するために `context.method_not_found` をリスニングできます。プラグインから遅延ロードファクトリーを追加する場合に便利でしょう。

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
