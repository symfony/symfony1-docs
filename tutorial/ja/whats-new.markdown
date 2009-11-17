symfony 1.3では何が新しくなったの？
==================================

このチュートリアルではsymfony 1.3のための技術的な内容をおおまかに紹介します。
このチュートリアルはsymfony 1.2ですでに作業をしており、symfony 1.3の新しい機能を速く学びたい開発者向けです。

最初に、symfony 1.3はPHP 5.2.4とそれ以降と互換性があることにご注意ください。

1.2からアップグレードしたいのであれば、symfonyの配布ファイルの中で見つかる[UPGRADE](http://www.symfony-project.org/tutorial/1_3/en/upgrade)ファイルをご覧ください。
プロジェクトをsymfony 1.3に安全にアップグレードするために必要なすべての情報が手に入ります。


メーラー
--------

symfony 1.3ではSwiftMailer 4.1に基づく新しい標準のメーラーが用意されました。

Eメールの送信はシンプルでアクションから`composeAndSend()`メソッドを使うだけです:

    [php]
    $this->getMailer()->composeAndSend('from@example.com', 'to@example.com', 'Subject', 'Body');

より柔軟性をもたせるなら、`compose()`メソッドを使い後で送信することができます。メッセージに添付ファイルを追加する方法は次のとおりです:

    [php]
    $message = $this->getMailer()->
      compose('from@example.com', 'to@example.com', 'Subject', 'Body')->
      attach(Swift_Attachment::fromPath('/path/to/a/file.zip'))
    ;
    $this->getMailer()->send($message);

メーラーはとても強力なので、詳細な情報は公式マニュアルを参照してください。

セキュリティ
-----------

`generate:app`タスクで新しいアプリケーションをつくるとき、セキュリティー設定はデフォルトで有効になるようになりました:

  * `escaping_strategy`: この値はデフォルトで`true`です(`--escaping-strategy`オプションで無効にできる)。

  * `csrf_secret`: デフォルトでランダムなパスワードが生成されます。
    CSRFの保護機能は標準で有効です(`--csrf-secret`オプションで無効にすることができます)。
    `settings.yml`設定ファイルを編集するか、`--csrf-secret`オプションを使う事で、デフォルトのパスワードを変更することが強く勧められます。

ウィジット
---------

### 標準のラベル

ラベルがフィールド名で自動生成された場合、サフィックスの`_id`は削除されます:

  * `first_name` => First name (以前と同じ)
  * `author_id` => Author (以前は "Author id" )

### `sfWidgetFormInputText`

`sfWidgetFormInput`クラスは抽象クラスになりました。
テキスト入力フィールドは`sfWidgetFormInputText`クラスで作られます。
この変更によってフォームクラスの内観はより簡単になりました。

### 国際化ウィジェット

次のウィジェットが追加されました:

  * `sfWidgetFormI18nChoiceLanguage`
  * `sfWidgetFormI18nChoiceCurrency`
  * `sfWidgetFormI18nChoiceCountry`
  * `sfWidgetFormI18nChoiceTimezone`

これらの最初の3つは廃止予定の`sfWidgetFormI18nSelectLanguage`、`sfWidgetFormI18nSelectCurrency`と`sfWidgetFormI18nSelectCountry`ウィジェットの置き換えです。

### 流れるようなインターフェイス

ウィジットは次のように流れるようなインターフェイスを実装するようになりました:

  * `sfWidgetForm`: `setDefault()`, `setLabel()`, `setIdFormat()`,
    `setHidden()`

  * `sfWidget`: `addRequiredOption()`, `addOption()`, `setOption()`,
    `setOptions()`, `setAttribute()`, `setAttributes()`

  * `sfWidgetFormSchema`: `setDefault()`, `setDefaults()`,
    `addFormFormatter()`, `setFormFormatterName()`, `setNameFormat()`,
    `setLabels()`, `setLabel()`, `setHelps()`, `setHelp()`, `setParent()`

  * `sfWidgetFormSchemaDecorator`: `addFormFormatter()`,
    `setFormFormatterName()`, `setNameFormat()`, `setLabels()`, `setHelps()`,
    `setHelp()`, `setParent()`, `setPositions()`

バリデーター
----------

### `sfValidatorRegex`

`sfValidatorRegex`は新しい`must_match`オプションを持ちます。 
`false`にセットされる場合、正規表現は渡すバリデーターにマッチしません。

`sfValidatorRegex`の`pattern`オプションは呼び出し時に正規表現を返す`sfCallable`のインスタンスにしなければならなくなりました。

### `sfValidatorUrl`

`sfValidatorUrl` は新しい `protocols` オプションを持つようになりました。 次のように特定のプロトコルを許可することができるようになりました:

    [php]
    $validator = new sfValidatorUrl(array('protocols' => array('http', 'https')));

つぎのプロトコルがデフォルトで許可されています:

 * `http`
 * `https`
 * `ftp`
 * `ftps`

### `sfValidatorSchemaCompare`

`sfValidatorSchemaCompare` クラスは２つの新しいコンパレーターを持つようになりました。:

 * `IDENTICAL`、は`===`と同等;
 * `NOT_IDENTICAL`、は`!==`と同等;

### `sfValidatorChoice`, `sfValidatorPropelChoice`, `sfValidatorDoctrineChoice`

`sfValidatorChoice`、　`sfValidatorPropelChoice`そして`sfValidatorDoctrineChoice`バリデーターは`multiple`オプションが`true`の場合のみ有効になる2つの新しいオプションを持ちます:

 * `min` 選択される必要がある最小の数
 * `max` 選択される必要がある最大の数

### I18n バリデーター

次のバリデーターが追加されました:

 * `sfValidatorI18nTimezone`

### 標準のエラーメッセージ

次のように`sfForm::setDefaultMessage()`メソッドを使うことでグローバル領域で
標準のエラーメッセージを定義できるようになりました。:

    [php]
    sfValidatorBase::setDefaultMessage('required', 'This field is required.');

以前までのコードは標準の`Required.`メッセージを全てのバリデータのために上書きするでしょう。
標準のメッセージはどのバリデータが作成される前に定義しておかなければならないことに注意してください
(コンフィグレーションクラスが良い場所です)。

>**NOTE**
>`setRequiredMessage()`と`setInvalidMessage()`メソッドは
>非推奨になり、新しい`setDefaultMessage()`メソッドを呼ぶようになりました。

symfonyがエラーを表示するとき、次のように使用されるエラーメッセージは決定されます。:

  * symfonyはバリデーターが作成されたときに通過したメッセージを探します。
    (バリデーターのコンストラクターの第２引数を通して);

  * 定義されていないなら、`setDefaultMessage()`メソッドで標準の定義されたメッセージを探します。;

  * もし、定義されていないなら、(メッセージが`addMessage()`メソッドで追加され
    ているとき)バリデーター自身で定義された標準のメッセージへ戻ります。

### 流れるようなインターフェイス

バリデーターは次のように流れるようなインターフェイスを実装するようになりました:

  * `sfValidatorSchema`: `setPreValidator()`、`setPostValidator()`

  * `sfValidatorErrorSchema`: `addError()`、`addErrors()`

  * `sfValidatorBase`: `addMessage()`、`setMessage()`、`setMessages()`、`addOption()`、`setOption()`、`setOptions()`、`addRequiredOption()`

### `sfValidatorFile`

`php.ini`で`file_uploads`が無効な場合`sfValidatorFile`のインスタンスを作成するときに例外が投げられます。

フォーム
--------

### `sfForm::useFields()`

新しい`sfForm::useFields()`メソッドはフォームから引数として提供されるもの以外、すべてのhiddenではないフィールドを削除します。 
状況によって不要なフィールドの割り当てを解除する代わりにフォームで維持したいフィールドを明示的に指示するのが楽になります。
たとえば、新しいフィールドを基底フォームに追加するとき、これらは明示的に追加されるまでフォームで自動的に現われなくなります(モデルフォームで新しいカラムを関連テーブルに追加するを考えてください)。

    [php]
    class ArticleForm extends BaseArticleForm
    {
      public function configure()
      {
        $this->useFields(array('title', 'content'));
      }
    }

デフォルトでは、フィールドの配列はフィールドの順序を変更するためにも使われます。
自動的な順序づけを無効にするには2番目の引数として`false`を`useFields()`に渡します。

### `sfForm::getEmbeddedForm($name)`

`->getEmbeddedForm()`メソッドを使って特定の組み込みフォームにアクセスできます。

### `sfForm::renderHiddenFields()`

`->renderHiddenFields()`メソッドは組み込みフォームからの隠しフィールドをレンダリングします。

### `sfFormSymfony`

新しい`sfFormSymfony`クラスはイベントディスパッチャーをsymfonyフォームに導入します。
`self::$dispatcher`をとおしてフォームクラス内部のディスパッチャーにアクセスできます。
次のフォームイベントはsymfonyによって通知されます:

  * `form.post_configure`:   このイベントはフォームが設定された後で通知される
  * `form.filter_values`:    このイベントは、マージされ汚染されたパラメーターと、バインドする直前のファイルの配列をフィルタリングする
  * `form.validation_error`: フォームバリデーションが失敗するときこのイベントが通知される
  * `form.method_not_found`: 未知のメソッドが呼び出されるときにこのイベントが通知される


### `BaseForm`

symfony 1.3のすべての新しいプロジェクトにはFormコンポーネントを拡張するもしくはプロジェクト固有の機能を追加するために使うことができる`BaseForm`クラスが入ります。
`sfDoctrinePlugin`と`sfPropelPlugin`によって生成されるフォームは自動的にこのクラスを継承します。
追加のフォームクラスを作るのであれば`sfForm`よりも`BaseForm`を継承すべきです。

### `sfForm::doBind()`

汚染されたパラメーターのクリーニングは開発者にやさしい`->doBind()`メソッドに隔離されました。
このメソッドは`->bind()`からのパラメーターとファイルのマージされる配列を受け取ります。

### `sfForm(Doctrine|Propel)::doUpdateObject()`

DoctrineとPropelのフォームクラスは開発者が扱いやすい`->doUpdateObject()`メソッドを含むようになりました。このメソッドは`->updateObject()`から`->processValues()`ですでに処理された値の配列を受け取ります。


### `sfForm::enableLocalCSRFProtection()` and `sfForm::disableLocalCSRFProtection()`

`sfForm::enableLocalCSRFProtection()`と`sfForm::disableLocalCSRFProtection()`メソッドを使うとき、あなたのクラスの`configure()`メソッドから簡単にCSRFからの保護機能を設定することができます。

CSRFからの保護機能を無効にするためには、次のような行を`configure()`メソッドに追加します:

    [php]
    $this->disableLocalCSRFProtection();

`disableLocalCSRFProtection()`をコールすることによって、フォームインスタンスを作成するときにCSRFシークレットを渡していたとしてもCSRFからの保護機能は無効になります。

### 流れるようなインターフェイス

`sfForm` メソッドは次のような流れるインターフェイスを実装するようになりました: `addCSRFProtection()`,
`setValidators()`, `setValidator()`, `setValidatorSchema()`, `setWidgets()`,
`setWidget()`, `setWidgetSchema()`, `setOption()`, `setDefault()`, そして
`setDefaults()`.

オートローダー
-----------

symfonyのすべてのオートローダーは大文字と小文字を区別しないようになりました。
PHPが大文字と小文字を区別をしませんし、symfonyはそれに合わせまるようになりました。

### `sfAutoloadAgain` (実験的)

デバッグモードでの用途を目的とする特殊なオートローダーが追加されました。
新しい`sfAutoloadAgain`クラスはsymfonyの標準オートローダーをリロードし問題のクラスを求めてファイルシステムを検索します。
純粋な効果は新しいクラスをプロジェクトに追加した後に`symfony cc`を実行する必要はないことです。

テスト
-----

### テストのスピードアップ

大規模なテストスイートの場合、変更するたびに全てのテストを起動するのはとても時間を消費するはずです。特にテストが失敗した場合などはそうでしょう。なぜならテストを修正するたびに、何も壊していないことを確認するためにテストスイート全体を再度実行すべきだからです。しかし、テストが修正されない限り、全てのテストを再実行する必要はありません。symfony1.3では`test:all`と`symfony:test`タスクが前回の実行時に失敗したテストだけを再実行する`--only-failed`(`-f`がショートカットになります)オプションを持つようになりました:

    $ php symfony test:all --only-failed

どのように動作するかを説明します: まず最初に、全てのテストはいつも通りに実行されます。しかし引き続きテストを実行しても、最後のテストで失敗したものだけが実行されます。コードを修正したら、テストは通過し次回以降の実行からは除外されるかもしれません。
再び全てのテストがパスしたら、あなたは完全なテストスイートを実行し。。。洗い流し繰り返すことができます。

### 機能テスト

リクエストが例外を生成するとき、レスポンステスターの`debug()`メソッドは標準的なHTML出力の代わりに、人間が読めるような例外のテキストの説明を出力するようになりました。
より簡単にデバッグできるようになります。

`sfTesterResponse`はレスポンスの内容全体に対して正規表現で検索を行える新しい`matches()`メソッドを持つようになりました。
XMLのようでないレスポンス、それは`checkElement()`が使えないようなレスポンスですが、そういった場合にとても役立ちます。ひ弱だった`contains()`メソッドの代わりにも使うことができます。:

    [php]
    $browser->with('response')->begin()->
      matches('/I have \d+ apples/')->    // it takes a regex as an argument
      matches('!/I have \d+ apples/')->   // a ! at the beginning means that the regex must not match
      matches('!/I have \d+ apples/i')->  // you can also add regex modifiers
    end();

### JUnitと互換性のあるXML出力

テストタスクは`--xml`オプションを使うことでJUnit互換のXMLファイルを出力することもできるようになりました。:

    $ php symfony test:all --xml=log.xml

### 簡単なデバッグ

テストハーネスがテストが失敗したことを報告するときデバッグを簡単にするために、失敗について詳細な出力ができる`--trace`オプションを渡すことができるようになりました:

    $ php symfony test:all -t

### Limeの出力の色づけ

symfony1.3では、limeはカラー化に関していえば正しく行うようになりました。これが意味する事は、`lime_test`のlimeコンストラクターの第２引数をほとんどいつも省略することができるということです:

    [php]
    $t = new lime_test(1);

### `sfTesterResponse::checkForm()`

レスポンステスターはより簡単にフォームにある全てのフィールドがレスポンスに正しくレンダリング処理されているかどうかを確認できるメソッドを含むようになりました:

    [php]
    $browser->with('response')->begin()->
      checkForm('ArticleForm')->
    end();

もしくは、フォームオブジェクトを渡すことができます:


    [php]
    $browser->with('response')->begin()->
      checkForm($browser->getArticleForm())->
    end();

レスポンスがmultipleフォームを含む場合は、どのDOM部分をテストするかをピンポイントで指定するためにCSSセレクターを提供するためのオプションがあります:

    [php]
    $browser->with('response')->begin()->
      checkForm('ArticleForm', '#articleForm')->
    end();

### `sfTesterResponse::isValid()`

レスポンスが整形式XMLであるかをレスポンステスターの`->isValid()`メソッドでチェックできます:

    [php]
    $browser->with('response')->begin()->
      isValid()->
    end();

引数として`true`を渡すことでドキュメントの種類に対するレスポンスをバリデートすることもできます:

    [php]
    $browser->with('response')->begin()->
      isValid(true)->
    end();

代わりに、バリデートするXSDもしくはRelaxNGスキーマがある場合、ファイルへのパスを提供できます:

    [php]
    $browser->with('response')->begin()->
      isValid('/path/to/schema.xsd')->
    end();

### `context.load_factories`をリスニングする

機能テストに`context.load_factories`イベントへのリスナーが追加されました。
これまでのsymfonyのバージョンではできませんでした。


    [php]
    $browser->addListener('context.load_factories', array($browser, 'listenForNewContext'));

### よりよい`->click()`

`->click()`メソッドにCSSセレクターを渡すことが可能で、セマンティックにしたい要素をターゲットにするのが楽になります。

    [php]
    $browser
      ->get('/login')
      ->click('form[action$="/login"] input[type="submit"]')
    ;

タスク
-----

symfonyのCLIはターミナルウィンドウの幅を検出しようとし、ラインのフォーマットを合わせようとします。検出できない場合はCLIは標準の78カラム幅に合わせようとします。

### `sfTask::askAndValidate()`

ユーザーに質問し入力された内容をバリデートする`sfTask::askAndValidate()`メソッドが新しくできました。:

    [php]
    $anwser = $this->askAndValidate('What is you email?', new sfValidatorEmail());

このメソッドはオプションの配列を受ける事もできます(より詳しい情報はAPIドキュメントを参照)。

### `symfony:test`

ときどき、開発者は特定のプラットフォーム上でsymfonyが正しく動作するかチェックするためにsymfonyのテストスイートを実行する必要があります。今までは、symfonyに附属している`prove.php`スクリプトを実行し確認しなければなりませんでした。
symfony1.3では組み込みのタスク、コマンドラインからsymfonyのコアテストスイートを起動できる`symfony:test`タスクが用意され、他のタスクと同じように使うことができます:

    $ php symfony symfony:test

`php test/bin/prove.php`に慣れているならば、同等の`php data/bin/symfony symfony:test`コマンドを実行できるようになっています。


### `project:deploy`

`project:deply`タスクはわずかに改良されました。リアルタイムでファイルの転送状況を表示するようになりました。ただし、`-t`オプションが渡されたときだけです。もしオプションが指定されていなければタスクは何も表示しません、もちろんエラーは除きます。エラー時には、エラーについて出力し簡単に問題を認識できるように赤色の背景上に出力します。

### `generate:project`

symfony1.3では、`generate:project`タスクを実行するときDoctrineが標準の設定されたORMになります:

    $ php /path/to/symfony generate:project foo

Propelのためにプロジェクトを生成するためには、`--orm`オプションを使います:


    $ php /path/to/symfony generate:project foo --orm=Propel

PropelもDoctrineのどちらも使いたくない場合は、`--orm`オプションに`none`を渡すことができます:

    $ php /path/to/symfony generate:project foo --orm=none

新しい`--installer`オプションのおかげで新しく生成されるプロジェクトをかなりカスタマイズできるPHPスクリプトを指定することができます。スクリプトはタスク内で実行され、タスクのメソッドで使う事ができます。より利用できるメソッドは次のようなものです。:`installDir()`, `runTask()`, `ask()`, `askConfirmation()`,
`askAndValidate()`, `reloadTasks()`, `enablePlugin()`, and `disablePlugin()`.

より詳細な情報は公式ブログの[記事](http://www.symfony-project.org/blog/2009/06/10/new-in-symfony-1-3-project-creation-customization)にあります。

プロジェクトを生成するとき、2番目の"著者"の引数を含めることができます。which specifies a value to use for the `@author` doc tag when symfony generates new classes.

    $ php /path/to/symfony generate:project foo "Joe Schmo"

### `sfFileSystem::execute()`

`sfFileSystem::execute()`メソッドは`sfFileSystem::sh()`メソッドをより強力な機能で置き換えます。`stdout`と`stderr`出力のプロセスをリアルタイムでコールバックします。配列で両方の出力を返すこともできます。`sfProjectDeployTask`クラスで使い方の1例を見つけることができます。

### `task.test.filter_test_files`

`test:*`タスクはこれらのタスクが実行される前に`task.test.filter_test_files`イベントを通過するようになりました。
このイベントには`arguments` と `options` パラメーターがあります。

### `sfTask::run()` の強化

`sfTask:run()`に次のような引数の連想配列とオプションを渡すことができるようになりました:

    [php]
    $task = new sfDoctrineConfigureDatabaseTask($this->dispatcher, $this->formatter);
    $task->run(
      array('dsn' => 'mysql:dbname=mydb;host=localhost',
    ), array(
      'name' => 'master',
    ));

これまでのバージョンでは、次のようにすればまだ動作します:

    [php]
    $task->run(
      array('mysql:dbname=mydb;host=localhost'),
      array('--name=master')
    );

### `sfBaseTask::setConfiguration()`

PHPから`sfBaseTask`を拡張しているタスクをコールするとき、`->run()`に`--application` と `--env`オプションを渡す必要はもうありません。
その代わりに、ただ`->setConfiguration()`をコールするだけで直接設定オブジェクトをセットすることができます。

    [php]
    $task = new sfDoctrineLoadDataTask($this->dispatcher, $this->formatter);
    $task->setConfiguration($this->configuration);
    $task->run();

これまでのバージョンでは、次のようにすればまだ動作します:

    [php]
    $task = new sfDoctrineLoadDataTask($this->dispatcher, $this->formatter);
    $task->run(array(), array(
      '--application='.$options['application'],
      '--env='.$options['env'],
    ));

### `project:disable`と`project:enable`

`project:disable`と`project:enable`タスクを使うことで環境全体を無効、または有効にすることができるようになりました:

    $ php symfony project:disable prod
    $ php symfony project:enable prod

環境においてどのアプリケーションを無効にするかを指定することもできます:

    $ php symfony project:disable prod frontend backend
    $ php symfony project:enable prod frontend backend

これらのタスクはこれまでの機能と後方互換があります:

    $ php symfony project:disable frontend prod
    $ php symfony project:enable frontend prod

### `help`と`list`

`help`と`list`タスクはXMLで情報を表示することができるようになりました:

    $ php symfony list --xml
    $ php symfony help test:all --xml

この出力は新しい`sfTask::asXml()`メソッドに基づいており、これはタスクオブジェクトのXML表現を返します。

XML出力はIDEのようなサードパーティーにとって大抵の場合においてとても役立つでしょう。

### `project:optimize`

このタスクを実行すればアプリケーションのテンプレートファイルの位置をキャッシュすることで実行時のディスクの読み込み回数を減らします。
このタスクは運用サーバーでのみ使われます。 
プロジェクトを変更するたびにタスクを再実行することをお忘れなく。

    $ php symfony project:optimize frontend

### `generate:app`

`generate:app`タスクはコアに搭載されるデフォルトのスケルトンディレクトリの代わりにプロジェクトの`data/skeleton/app`ディレクトリのスケルトンディレクトリをチェックします。

### タスクからEメールを送信する

`getMailer()`メソッドを使うことでタスクからEメールを簡単に送信することができます。

### タスクでルーティングを使う

`getRouting()`メソッドを使うことでタスクからルーティングオブジェクトを簡単に取得できます。

例外
----

### オートローディング

オートロードの間に例外が投げられるとき、symfonyはこれらを捕らえエラーをユーザーに出力します。 
これは一部の"真っ白な"ページの問題を解決します。

### Webデバッグツールバー

可能であれば、Webデバッグツールバーは開発環境の例外ページに表示されます。

Propel統合
----------

Propelはバージョン1.4にアップグレードされました。Propelのアップグレードに関する詳しい情報は[公式サイト](http://propel.phpdb.org/trac/wiki/Users/Documentation/1.4)を訪問してくださるようお願いします。

### Propelのビヘイビア

Propelを拡張するためにsymfonyが依存するカスタムのビルダークラスはPropel 1.4の新しいビヘイビアシステムに移植されました。

### `propel:insert-sql`

`propel:insert-sql`がデータベースから全てのデータを削除する前に確認を行います。
このタスクは複数のデータベースからデータを削除することができるので、関連するデータベースの接続名も表示するようになりました。

### `propel:generate-module`、`propel:generate-admin`、`propel:generate-admin-for-route`

`propel:generate-module`、`propel:generate-admin`と`propel:generate-admin-for-route`タスクは生成モジュールのアクション基底クラスのコンフィギュレーションを可能にする`--actions-base-class`オプションをとります。

### Propelのビヘイビア

Propel 1.4はPropelのコードのビヘイビアの実装を導入しました。
カスタムのsymfonyビルダーはこの新しいシステムに移植されました。

PropelモデルネイティブなビヘイビアをPropelモデルに追加したい場合、`schema.yml`でもできます:

    classes:
      Article:
        propel_behaviors:
          timestampable: ~

もしくは、古い`schema.yml`構文を使う場合:

    propel:
      article:
        _propel_behaviors:
          timestampable: ~

### フォーム生成を無効にする

`symfony`のPropelビヘイビアにパラメーターを渡すことで特定のモデルでのフォーム生成を無効にできます:

    classes:
      UserGroup:
        propel_behaviors:
          symfony:
            form: false
            filter: false

ルーティング
------------

### デフォルトの要件

デフォルトの必須要件`\d+`は`column`オプションがデフォルトの`id`になっているとき`sfObjectRouteCollection`にだけ適用されるようになりました。
(`slug`のような)数字でないカラムが指定されているとき代わりの必須要件を用意する必要はないということです。

### `sfObjectRouteCollection`オプション

新しい`default_params`オプションが`sfObjectRouteCollection`に追加されました。 
これはそれぞれの生成ルートに登録されるデフォルトパラメーターを可能にします:

    [yml]
    forum_topic:
      class: sfDoctrineRouteCollection
      options:
        default_params:
          section: forum

CLI
---

### 出力の色づけ

symfonyのCLIを使用するとき、symfonyはあなたが利用しているコンソールがカラーの出力をサポートしているかどうかを推測しようとします。
しかし、symfonyは推測を間違える場合があります;例えば、Cygwinを使っているときです(Windowsプラットフォームではカラーの出力は常に切られているからです)。

symfony1.3では、`--color`グローバルオプションを渡すことでカラーで出力することを強制できるようになりました。

国際化(I18N)
----

### データの更新

すべての国際化オペレーションに使われるデータは`ICU`プロジェクトから更新されました。
symfonyには約330のロケールファイルが付属しており、symfony 1.2と比べると約70増えています。 
ですのでたとえば、言語リストの10番目の項目をチェックするテストケースが失敗する可能性があることにご注意をお願いします。

### ユーザーロケールを基準にソートする

このロケールに依存するデータでのすべてのソートもロケールに依存して実行されます。
この目的のために`sfCultureInfo->sortArray()`を使うことができます。

プラグイン
----------

symfony 1.3以前では、`sfDoctrinePlugin`と`sfCompat10Plugin`以外のすべてのプラグインはデフォルトで有効にされました:

    [php]
    class ProjectConfiguration extends sfProjectConfiguration
    {
      public function setup()
      {
        // 互換性のために望むプラグインだけ削除および有効にする
        $this->enableAllPluginsExcept(array('sfDoctrinePlugin', 'sfCompat10Plugin'));
      }
    }

symfony 1.3で新しく作られたプロジェクトでは、プラグインを使うためには`ProjectConfiguration`クラスで明示的に有効にしなければなりません:

    [php]
    class ProjectConfiguration extends sfProjectConfiguration
    {
      public function setup()
      {
        $this->enablePlugins('sfDoctrinePlugin');
      }
    }

`plugin:install`タスクはインストールするプラグインを自動的に有効にします(そして`plugin:uninstall`はプラグインを無効にします)。
Subversion経由でプラグインをインストールする場合、手動で有効にする必要があります。

`sfProtoculousPlugin`もしくは`sfCompat10Plugin`のような コアプラグインを使いたい場合、必要なのは対応する`enablePlugins()`ステートメントを`ProjectConfiguration`クラスに追加することだけです。

>**NOTE**
>1.2からプロジェクトをアップグレードする場合、古いふるまいはアクティブなままです。
>これはアップグレードタスクが`ProjectConfiguration`ファイルを変更しないからです。
>このふるまいの変更はsymfony 1.3の新規プロジェクトのみです。

### `sfPluginConfiguration::connectTests()`

新しい`setupPlugins()`メソッドでプラグインの設定の`->connectTests()`メソッドをコールすることで`test:*`タスクにプラグインのテストを接続することができます。

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

symfony 1.3は可能であるときにファイルパスをクリック可能なリンクにフォーマットします(すなわちデバッグ例外のテンプレート)。 
`sf_file_link_format`はセットされる場合、この目的に使われ、そうでなければ、symfonyはPHP設定の`xdebug.file_link_format`の値を探します。

たとえば、TextMateでファイルを開きたい場合、次のコードを`settings.yml`に追加します:

    [yml]
    all:
      .settings:
        file_link_format: txmt://open?url=file://%f&line=%l

`%f`プレースホルダーはファイルの絶対パスに、`%l`プレースホルダーは行数に置き換えられます。

Doctrineの統合
--------------

### フォームクラスを生成する

DoctrineのYAMLスキーマファイルにsymfonyのための追加オプションを指定することができるようになりました。
フォームとフィルタークラスの生成を無効にするオプション群を追加しました。

例えば、典型的な多対多の関係のモデルで、フォームやフィルターフォームクラスの生成は必要ないでしょう。そこで、次のように行うことができます。

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

あなたのモデルクラスからフォームを生成するとき、モデルクラスは継承を含んでいます。
生成された子クラスは継承を留意し、同じ継承構造に続くフォームを生成します。

### 新しいタスク

Doctrineで開発するときに手助けしてくれる新しいタスクを導入しました。

#### モデルテーブルを作成する

指定されたモデルの配列のために個々にテーブルを作成することができるようになりました。
テーブルを削除するときあなたに変わってテーブルを再作成してくれます。
既存のプロジェクト/データベースで新しいモデルを開発するとき、データベース全体を一掃したくなくテーブル群をただ再構築したいときに役立ちます。

    $ php symfony doctrine:create-model-tables Model1 Model2 Model3

#### モデルファイルを削除する

YAMLスキーマファイルでモデルを変更したり、名前を変えたり、使わなくなったモデルを削除したりすることがよくあるでしょう。
このようなことを行ったとき、孤児となったモデル、フォームそしてフィルタークラスができてしまいます。`doctrine:delete-model-files`タスクを使うことで、手動でモデルに関連づけられた生成された関連するファイルを掃除することができるようになりました。

    $ php symfony doctrine:delete-model-files ModelName

上記タスクは関連する生成されたファイルを見つけ、そのファイルを削除したいかどうかあなたに確認する前にあなたに報告してくれます。

#### モデルファイルをクリーンにする

`doctrine:clean-model-files`タスクで上記プロセスを自動化しどのモデルをディスクに存在し、YAMLスキーマファイルに存在しないかを見つけることができます。

    $ php symfony doctrine:clean-model-files

上記コマンドはYAMLスキーマファイルと生成されたモデルやファイルと比較し、どれを削除すべきかを決定します。
これらのモデルは`doctrine:delete-model-files`タスクに伝えられます。自動的に削除する前にどんなファイルが削除されるかの確認を行います。

#### データをリロードする

データフィクスチャーを再読み込みするとき完全にデータベースを一掃したいことはよくあることです。`doctrine:build-all-reload`タスクはこれを行ってくれますが、モデルやフォームやフィルターの生成などの他のタスクを行っているだけです。そして、これは大規模なプロジェクトにおいては時間がかかるでしょう。そこで、単純に`doctrine:reload-data`タスクを使うことができるようになりました。

次のコマンドです。

    $ php symfony doctrine:reload-data

これはつぎのコマンド群を実行するのと同等です:

    $ php symfony doctrine:drop-db
    $ php symfony doctrine:build-db
    $ php symfony doctrine:insert-sql
    $ php symfony doctrine:data-load

#### 何でもビルドする

新しい`doctrine:build`タスクによって明確にsymfonyやDoctrineに何をビルドしてほしいか指定できます。このタスクは多くの既存するコンビネーションタスク、これらはより柔軟性のある解決法に賛成して非推奨ですが、これらにおいて機能性を複製します。

以下が`doctrine:build`の使い方です:

    $ php symfony doctrine:build --db --and-load

これはデータベースを削除(`:drop-db`)して作成(`:build-db`)し、`schema.yml`にテーブル設定を作成(`:insert-sql`)し、フィクスチャーデータを読み込み(`:data-load`)します。

    $ php symfony doctrine:build --all-classes --and-migrate

これはモデル(`:build-model`)、フォーム(`:build-forms`)、フォームフィルター(`:build-filters`)を生成し、保留されていたマイグレーション(`:migrate`)を実行します。

    $ php symfony doctrine:build --model --and-migrate --and-append=data/fixtures/categories.yml

モデルを生成(`:build-model`)し、データベースのマイグレーション(`:migrate`)を行い、そしてカテゴリーのフィクスチャーデータ(`:data-load --append --dir=/data/fixtures/categories.yml`)を付け加えます。

より多くの情報は`doctrine:build`タスクのヘルプページを参照してください。

#### 新しいオプション: `--migrate`

つぎのタスクは `--migrate`オプションを格納するようになり、`doctrine:migrate`で入れ子になった`doctrine:insert-sql`タスクを置き換えます。

  * `doctrine:build-all`
  * `doctrine:build-all-load`
  * `doctrine:build-all-reload`
  * `doctrine:build-all-reload-test-all`
  * `doctrine:rebuild-db`
  * `doctrine:reload-data`

#### `doctrine:generate-migration --editor-cmd`

`doctrine:generate-migration`タスクは簡単に編集で一度だけマイグレーションクラスが生成される`--editor-cmd`オプションを格納するようになりました。

    $ php symfony doctrine:generate-migration AddUserEmailColumn --editor-cmd=mate

この例はマイグレーションクラスを生成しTextMateで新しいファイルを開いています。

#### `doctrine:generate-migrations-diff`

この新しいタスクは新旧のスキーマをもとに、完全なマイグレーションクラスを自動的に生成します。

### 日付のセッターとゲッター

Doctrineの日付とタイムスタンプの値をPHPのDateTimeオブジェクトインスタンスとして取得するための2つの新しいメソッドを追加しました。

    [php]
    echo $article->getDateTimeObject('created_at')
      ->format('m/d/Y');

日付の値も`setDateTimeObject`メソッドをコールし有効な`DateTime`インスタンスを渡すだけでセットすることもできます。

    [php]
    $article->setDateTimeObject('created_at', new DateTime('09/01/1985'));

### `doctrine:migrate --down`

`doctrine:migrate`はスキーマをリクエストされる方向に一回でマイグレートする`up`と`down`オプションを含みます。

    $ php symfony doctrine:migrate --down

### `doctrine:migrate --dry-run`

データベースがDDLステートメントのロールバックをサポートする場合(MySQLはサポートしない)、新しい`dry-run`オプションを利用できます。

    $ php symfony doctrine:migrate --dry-run

### DQLタスクをテーブルのデータとして出力する

これまでは`doctrine:sql`コマンドを実行するとただYAML書式で出力されるだけでした。
新しい`--table`オプションを追加しました。
このオプションによってデータをテーブル表示で出力することができるようなり、MySQLのコマンドラインの出力に似たものになっています。

それで、つぎのようなことが可能になりました。

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

### 機能テストでクエリをデバッグする

`sfTesterDoctrine`クラスは`->debug()`メソッドを含むようになりました。
このメソッドは現在のコンテクストで実行されたクエリについての情報を出力します。

    [php]
    $browser->
      get('/articles')->
      with('doctrine')->debug()
    ;

メソッドに数値を渡すことで直近の実行されたクエリの履歴を見ることができ、文字列を渡すことで部分列にマッチするものや正規表現にマッチするクエリだけを表示することができます。

    [php]
    $browser->
      get('/articles')->
      with('doctrine')->debug('/from articles/i')
    ;

### `sfFormFilterDoctrine`

`sfFormFilterDoctrine`クラスは`query`オプションを通して`Doctrine_Query`の種をまかれるようになりました(The `sfFormFilterDoctrine` class can now be seeded a `Doctrine_Query` object
via the `query` option):

    [php]
    $filter = new ArticleFormFilter(array(), array(
      'query' => $table->createQuery()->select('title, body'),
    ));

`->setTableMethod()`(または`table_method`オプション)を通して指定されたテーブルメソッドはクエリーオブジェクトを返す必要がありません。つぎはどれも有効な`sfFormFilterDoctrine`テーブルメソッドです:

    [php]
    // symfony >= 1.2　で動作します
    public function getQuery()
    {
      return $this->createQuery()->select('title, body');
    }

    // symfony >= 1.2　で動作します
    public function filterQuery(Doctrine_Query $query)
    {
      return $query->select('title, body');
    }

    // symfony >= 1.3　で動作します
    public function modifyQuery(Doctrine_Query $query)
    {
      $query->select('title, body');
    }

フォームフィルターのカスタマイズが簡単になりました。
フィールドのフィルタリングを追加するには、必要なことはウィジェットとそれを処理するメソッドを追加することだけです。

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

### Doctrineを設定する

Doctrineを設定するために`doctrine.configure`と`doctrine.configure_connection`イベントをリスニングできます。
このことはプラグインが`sfDoctrinePlugin`の先に有効にされている限り、プラグインからDoctrineのコンフィギュレーションを簡単にカスタマイズできることを意味します。

### `doctrine:generate-module`、`doctrine:generate-admin`、`doctrine:generate-admin-for-route`

`doctrine:generate-module`、`doctrine:generate-admin`、`doctrine:generate-admin-for-route`タスクは生成モジュールのアクション基底クラスのコンフィギュレーションを可能にする`--actions-base-class`オプションをとります。

### マジックメソッドはのdocタグ

symfonyがDoctrineモデルに追加するゲッターとセッターのマジックメソッドはそれぞれの生成基底クラスのdocヘッダーに現れます。 
IDEがコード補完をサポートする場合、これらの`getFooBar()`と`setFooBar()`メソッドがモデルオブジェクトに現れることがわかります。`FooBar`はキャメルケースのフィールド名です。

Webデバッグツールバー
---------------------

### `sfWebDebugPanel::setStatus()`

Webデバッグツールバーのそれぞれのパネルはタイトルの背景色に影響を及ぼすステータスを指定できるようになりました。
たとえば、`sfLogger::INFO`よりも優先順位が高いメッセージがロギングされる場合、logパネルのタイトルの背景色は変わります。

### `sfWebDebugPanel`リクエストパラメーター

`sfWebDebugPanel`パラメーターをURLにつけ加えることでページロードで開くパネルを指定できるようになりました。
たとえば、`?sfWebDebugPanel=config`を追加すればconfigパネルを開くようにWebデバッグツールバーはレンダリングされます。

パネルはWebデバッグの`request_parameters`オプションにアクセスすることでリクエストパラメーターをインスペクトします:

    [php]
    $requestParameters = $this->webDebug->getOption('request_parameters');

パーシャル
---------

### スロットの改善

`get_slot()`と`include_slot()`ヘルパーはスロットが提供されない場合返すデフォルトのスロットの内容を指定するための2番目のパラメーターを受け取ります:

    [php]
    <?php echo get_slot('foo', 'bar') // もし`foo`スロットが定義されていなければ 'bar'が出力されます ?>
    <?php include_slot('foo', 'bar') // もし`foo`スロットが定義されていなければ 'bar'が出力されます ?>

ページャー
----------

`sfDoctrinePager`と`sfPropelPager`メソッドは`Iterator`と`Countable`インターフェイスを実装しています。

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

ビューキャッシュマネージャーは`factories.yml`でパラメーターを受け取ります。
ビュー用のキャッシュキーの生成はクラスを楽に拡張できるように異なる方法でリファクタリングされてきました。

2つのパラメーターが`factories.yml`で利用できます:

  * `cache_key_use_vary_headers` (デフォルト: true): キャッシュキーがVaryヘッダーの部分を含むか指定します。
    実際には、`vary`キャッシュパラメーターで指定されるので、これはページキャッシュがHTTPヘッダーに依存するかどうかを伝えます。

  * `cache_key_use_host_name` (デフォルト: true): キャッシュキーがホスト名の部分を含むか指定します。
    実際には、これはページキャッシュがホスト名に依存するかどうかを伝えます。

### さらにキャッシュ

ビューキャッシュマネージャーは`$_GET`もしくは`$_POST`の配列に値が存在するのかによってキャッシュを拒絶することはありません。
ロジックは現在のリクエストが`cache.yml`をチェックする前のGETメソッドであることを確認するだけです。
このことは次のページがキャッシュ可能であることを意味します:

  * `/js/my_compiled_javascript.js?cachebuster123`
  * `/users?page=3`

リクエスト
---------

### `getContent()`

リクエストの内容は`getContent()`メソッドを通してアクセスできるようになりました。

### `PUT`と`DELETE`パラメーター

リクエストが`aplication/x-www-form-urlencoded`にセットされたContent-Typeで`PUT`、`DELETE` HTTPメソッドで来たばあいに、symfonyは生のボディを解析し、通常の`POST`パラメーターのようにアクセスできるパラメーターを作成します。

アクション
----------

### `redirect()`

`sfAction:redirect()`メソッド類はsymfony 1.2で導入された`url_for()`の特徴と互換を持つようになりました。

    [php]
    // symfony 1.2
    $this->redirect(array('sf_route' => 'article_show', 'sf_subject' => $article));

    // symfony 1.3
    $this->redirect('article_show', $article);

この強化は`redirectIf()`と`redirectUnless()`にも適用されました。

ヘルパー
--------

### `link_to_if()`, `link_to_unless()`

`link_to_if()`と`link_to_unless()`ヘルパーはsymfony 1.2で導入された`link_to()`メソッドと互換をもつようになりました:

    [php]
    // symfony 1.2
    <?php echo link_to_unless($foo, '@article_show?id='.$article->getId()) ?>

    // symfony 1.3
    <?php echo link_to_unless($foo, 'article_show', $article) ?>

コンテキスト
-----------

メソッドを動的に`sfContext`に追加するために`context.method_not_found`をリスニングできます。
プラグインから遅延ロードファクトリを追加する場合に便利でしょう。

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
