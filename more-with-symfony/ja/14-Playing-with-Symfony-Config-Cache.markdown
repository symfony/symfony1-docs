symfonyのコンフィグキャッシュで遊ぶ
==================================

*Kris Wallsmith 著*

symfony開発者として私のゴールの1つは、どのようなプロジェクトに対しても、同僚の仕事の流れを効率化することです。私自身は、symfony  のコードベースを全て把握していますが、チームのメンバー全てにそれを期待することはできません。ありがたいことに、symfony はプロジェクト内の機能を分離したり、集中したりするメカニズムをもっています。このメカニズムによって、 他の開発者が、少ない労力で変更ができるようになるのです。

フォームの設定
---------------

このメカニズムの素晴らしい例として、symfony のフォームフレームワークがあります。フォームフレームワークはパワフルな symfony のコンポーネントです。出力部分やバリデーション部分の実装を PHP オブジェクトに移動することにより、フォームのコントロールを柔軟に行うことができます。複雑なロジックを一つのフォームクラスとしてカプセル化できますし、他の場所でも拡張したり再利用ができるのです。これはもう神様からのアプリケーション開発者へのプレゼントと言ってもいいでしょう。

しかし、symfony のフォームの出力に関する抽象化は、テンプレート開発者にとってみれば、問題の種になります。次のフォームを見てみてください:

![デフォルト状態のフォーム](http://www.symfony-project.org/images/more-with-symfony/config_cache_form_default.png)

このフォームを構成するクラスは、次のようになります:

    [php]
    // lib/form/CommentForm.class.php
    class CommentForm extends BaseForm
    {
      public function configure()
      {
        $this->setWidget('body', new sfWidgetFormTextarea());
        $this->setValidator('body', new sfValidatorString(array(
          'min_length' => 12,
        )));
      }
    }

そして、フォームは PHP テンプレートで次のように出力されます:

    <!-- apps/frontend/modules/main/templates/indexSuccess.php -->
    <form action="#" method="post">
      <ul>
        <li>
          <?php echo $form['body']->renderLabel() ?>
          <?php echo $form['body'] ?>
          <?php echo $form['body']->renderError() ?>
        </li>
      </ul>
      <p><button type="submit">Post your comment now</button></p>
    </form>

テンプレート開発者は、上記のフォームの出力を調整することができます。たとえば、ラベルを変更して、もっとフレンドリーに見せることができます:

    <?php echo $form['body']->renderLabel('Please enter your comment') ?>

さらに入力フィールドにクラス属性を追加することができます:

    <?php echo $form['body']->render(array('class' => 'comment')) ?>

変更は、直感的でかつ簡単にすることができますが、エラーメッセージを変更しようとしたらどうでしょうか？

![エラー状態のフォーム](http://www.symfony-project.org/images/more-with-symfony/config_cache_form_error.png)

`->renderError()`メソッドは、引数の指定がありません。つまり、テンプレート開発者は、フォームクラスファイルを開いて、修正したいバリデータを作るコードを探さないといけません。そして、コンストラクタを修正して、対応するエラーコードに新しいエラーメッセージを関連づけしなければなりません。

テンプレート開発者は、次のような変更を行うことになります:

    [php]
    // before
    $this->setValidator('body', new sfValidatorString(array(
      'min_length' => 12,
    )));

    // after
    $this->setValidator('body', new sfValidatorString(array(
      'min_length' => 12,
    ), array(
      'min_length' => 'You haven't written enough',
    )));

問題にお気づきですか？おっと、シングルクォートで囲まれた文字列の中でアポストロフィを使ってしまいました。もちろんわたしやあなたはこんなマヌケなミスはしないでしょう。しかし、フォームクラスの内部を壊してしまうテンプレート開発者はどうでしょう？

真面目に言います。テンプレート開発者が正しくエラーメッセージを修正できるくらいに、symfony のフォームフレームワークをよく理解していると期待できますか？テンプレート部のビューのレイヤーしか触らない人に、バリデータのコンストラクタの特徴を知っていると期待すべきですか？

みんな「ノー」と言うでしょうね。もちろんテンプレート開発者は、たくさんの重要な仕事を行います。ただし、アプリケーションのコードを書かない彼らに、symfony フォームフレームワークの内部を学ぶべきだとするのは、期待しすぎで、不適切でしょう。

YAML: 解決方法
--------------

フォームの文字列を編集するプロセスを簡単にする方法として、YAML によるコンフィギュレーションのレイヤーを加えましょう。この YAML コンフィギュレーションは、フォームのそれぞれのオブジェクトを強化し、ビューにその内容を渡します。この設定ファイルは、次のようになります:

    [yml]
    # config/forms.yml
    CommentForm:
      body:
        label:        Please enter your comment
        attributes:   { class: comment }
        errors:
          min_length: You haven't written enough

より簡単になりましたね？このコンフィギュレーションは、自分自身のフォームクラスのオブジェクトを説明しています。さらに、先ほど出くわしたアポストロフィの問題は、もう関係ありません。

テンプレートの値のフィルタリング
----------------------------------

まず最初に挑戦することは、このコンフィギュレーションで設定したフォーム変数を、テンプレートにフィルタすることができるようにする symfony のフックを探すことです。テンプレートやテンプレートパーシャルを表示する直前に呼び出される `template.filter_parameters` イベントを使うことができますね。

    [php]
    // lib/form/sfFormYamlEnhancer.class.php
    class sfFormYamlEnhancer
    {
      public function connect(sfEventDispatcher $dispatcher)
      {
        $dispatcher->connect('template.filter_parameters',
          array($this, 'filterParameters'));
      }

      public function filterParameters(sfEvent $event, $parameters)
      {
        foreach ($parameters as $name => $param)
        {
          if ($param instanceof sfForm && !$param->getOption('is_enhanced'))
          {
            $this->enhance($param);
            $param->setOption('is_enhanced', true);
          }
        }

        return $parameters;
      }

      public function enhance(sfForm $form)
      {
        // ...
      }
    }

>**NOTE**
>`enhance()` メソッドを呼ぶ前に全てのフォームオブジェクトの `is_enhanced`オプションをチェックしています。こうすることで `enhance()` メソッドを重複して呼ぶことを防いでいます。

この強化クラスは、アプリケーションコンフィギュレーション内で、接続する必要があります:

    [php]
    // apps/frontend/config/frontendConfiguration.class.php
    class frontendConfiguration extends sfApplicationConfiguration
    {
      public function initialize()
      {
        $enhancer = new sfFormYamlEnhancer($this->getConfigCache());
        $enhancer->connect($this->dispatcher);
      }
    }

これでフォームの変数がテンプレートやパーシャルテンプレートに渡る直前に分離することができましたので、準備が整いました。そして、YAML で設定した内容を適用させましょう。

YAMLの設定の適用
----------------

このYAMLコンフィギュレーションをそれぞれのフォームに反映する一番簡単な方法は、配列としてロードし、それぞれのコンフィギュレーションをループで回すことです:

    [php]
    public function enhance(sfForm $form)
    {
      $config = sfYaml::load(sfConfig::get('sf_config_dir').'/forms.yml');

      foreach ($config as $class => $fieldConfigs)
      {
        if ($form instanceof $class)
        {
          foreach ($fieldConfigs as $fieldName => $fieldConfig)
          {
            if (isset($form[$fieldName]))
            {
              if (isset($fieldConfig['label']))
              {
                $form->getWidget($fieldName)->setLabel($fieldConfig['label']);
              }

              if (isset($fieldConfig['attributes']))
              {
                $form->getWidget($fieldName)->setAttributes(array_merge(
                  $form->getWidget($fieldName)->getAttributes(),
                  $fieldConfig['attributes']
                ));
              }

              if (isset($fieldConfig['errors']))
              {
                foreach ($fieldConfig['errors'] as $code => $msg)
                {
                  $form->getValidator($fieldName)->setMessage($code, $msg);
                }
              }
            }
          }
        }
      }
    }

しかし、この実装にはいくつか問題があります。まず、YAML ファイルは、フォームを強化する度に、ファイルシステムから読み込まれ、`sfYaml` にロードされます。このような方法でファイルシステムから読み込むことは避けるべきです。次に、アプリケーションを遅くさせる可能性のあるネストされたループや条件判断が複数あります。この2つの問題は、symfony のコンフィグキャッシュを用いれば、解決することができます。

コンフィグキャッシュ
-------------------

コンフィグキャッシュは、YAML 設定ファイルの使用を最適化するためのクラス群で構成されています。これらのクラスは、YAML コンフィギュレーションファイルを自動的に PHP コードへと翻訳し、キャッシュディレクトリに保存します。このメカニズムは、設定ファイルから `sfYaml` にロードさせるためにかかるオーバーヘッドをなくすことができます。

さぁ、フォーム強化役のためのコンフィグキャッシュを実装してみましょう。`forms.yml` を `sfYaml` にロードする代わりに、まず、現在のアプリケーションのコンフィグキャッシュに、前に作られたものがないか、調べてみましょう。

そのためには、`sfFormYamlEnhancer` クラスは、現在のアプリケーションのコンフィグキャッシュにアクセスする必要がありますので、コンストラクタで追加します。

    [php]
    class sfFormYamlEnhancer
    {
      protected
        $configCache = null;

      public function __construct(sfConfigCache $configCache)
      {
        $this->configCache = $configCache;
        $this->configCache->registerConfigHandler('config/forms.yml',
          'sfSimpleYamlConfigHandler');
      }

      // ...
    }

アプリケーションから特定のコンフィギュレーションファイルが要求された際に、コンフィグキャッシュに、何をしたらいいか教えてあげないといけません。今回は、`forms.yml` の処理するために、コンフィグキャッシュが `sfSimpleYamlConfigHandler` を使うように導いてあげました。このコンフィグハンドラは、YAML を配列にパースし、PHP コードとしてキャッシュをします。

このコンフィグハンドラと、その内容をキャッシュ化したコンフィグキャッシュを使うことで、`sfYaml` を使わずに `forms.yml` の内容を呼び出すことができます:

    [php]
    public function enhance(sfForm $form)
    {
      $config = include $this->configCache->checkConfig('config/forms.yml');

      // ...
    }

このほうがよりよいです。最初に一度だけ YAML をパースする必要がありますが、毎回する必要がなくなり、オーバーヘッドを取り除くことができました。さらに、オプコードキャッシュの恩恵が得られるように、`include` を使う方法に切り替えました。

>**SIDEBAR**
>開発環境 vs 運用環境
>
> アプリケーションのデバッグモードがオンかオフかによって、`->checkConfig()`の内部の処理は、変わります。デバッグモードがオフの `prod` 環境においては、このメソッドは、次のように動作します。
>
>  * リクエストされたファイルのキャッシュのバージョンをチェックします
>    * 存在すれば、そのキャッシュファイルへのパスを返します
>    * 存在しなければ:
>      * 設定ファイルを処理します
>      * コードの結果をキャッシュに保存します
>      * 新しく作成されたキャッシュファイルへのパスを返します
>
> デバッグモードがオンの際には、違った動作をします。開発時には、設定ファイルは編集されることがありますよね。そのため、`->checkConfig()`は、必ず最新のバージョンかどうかを調べ、オリジナルとキャッシュファイルの最終更新時間を比べます。この動作をするために、デバッグモードがオフの際には、少しステップを追加しています:
>
>  * リクエストされたファイルのキャッシュのバージョンをチェックします
>    * 存在しなければ:
>      * コンフィギュレーションファイルを処理します
>      * コードの結果をキャッシュに保存します
>
>    * 存在すれば:
>      * コンフィギュレーションファイルとキャッシュファイルの最終更新時間を比べます
>      * コンフィギュレーションファイルの編集時間が新しければ:
>        * コンフィギュレーションファイルを処理します
>        * コードの結果をキャッシュに保存します
>  * キャッシュファイルへのパスを返します

テストで、変更に備えましょう
---------------------------

ここでいったんテストを書きましょう。次のスクリプトで始めることができます:

    [php]
    // test/unit/form/sfFormYamlEnhancerTest.php
    include dirname(__FILE__).'/../../bootstrap/unit.php';

    $t = new lime_test(3);

    $configuration = $configuration->getApplicationConfiguration(
      'frontend', 'test', true, null, $configuration->getEventDispatcher());
    sfToolkit::clearDirectory(sfConfig::get('sf_app_cache_dir'));

    $enhancer = new sfFormYamlEnhancer($configuration->getConfigCache());

    // ->enhance()
    $t->diag('->enhance()');

    $form = new CommentForm();
    $form->bind(array('body' => '+1'));

    $enhancer->enhance($form);

    $t->like($form['body']->renderLabel(), '/Please enter your comment/',
      '->enhance() enhances labels');
    $t->like($form['body']->render(), '/class="comment"/',
      '->enhance() enhances widgets');
    $t->like($form['body']->renderError(), '/You haven\'t written enough/',
      '->enhance() enhances error messages');

このテストを、現在の `sfFormYamlEnhancer` に対して実行することで、正しく動作していることを検証します:

![テストの検証は OK](http://www.symfony-project.org/images/more-with-symfony/config_cache_tests_3_ok.png)

これで何かを壊してしまった際には、テストが失敗して教えてくれます。そして、自信をもってリファクタリングをすることができるようになりました。

カスタムコンフィグハンドラ
----------------------------

ここまで説明してきた強化役のコードでは、`forms.yml`で設定したフォームクラスの数だけループで回してテンプレートに渡すフォームの変数を調べていました。確かにこれで動作に限っては、問題ありません。しかし、1つのテンプレートに複数のフォームオブジェクトを渡したいときはどうでしょう？また、YAMLに設定するフォームの数が多くなってしまうときはどうでしょう？パフォーマンスの影響を考え始めることになります。処理を最適化するカスタムコンフィグハンドラを作るよい機会となりましたね。


>**SIDEBAR**
>なぜカスタムにするのか？
>
> カスタムコンフィグハンドラを書くことは、気弱な人には向きません。コードの自動生成において、コンフィグハンドラを使うことで、エラーが起こりやすくなったり、テストもしにくくなったりします。しかし、カスタムコンフィグハンドラによって得られる恩恵は大きいのです。
目的に沿った要点をピンポイントでまとめて処理を行うため、「ハードコーディングされた」ロジックを作ります。これは、YAML による柔軟な指定、オーバーヘッドの小さい PHP のネイティブコードの使用といったアドバンテージが得られることをもたらします。さらに、[APC](http://pecl.php.net/apc)や[XCache](http://xcache.lighttpd.net/)などのオプコードキャッシュのしくみと合わせることで、コンフィグハンドラは、使いやすさとパフォーマンスの面で強力になるのです。

コンフィグハンドラの魔法のほとんどは、目に見えないところで行われています。コンフィグキャッシュは、全てのコンフィグハンドラを実行する前に、キャッシュ化の処理を行います。このことにより、YAMLコンフィギュレーションに適用するのに必要なコードの生成のみに集中することができます。

コンフィグハンドラは、次の2つのメソッドを実装する必要があります:

 * `static public function getConfiguration(array $configFiles)`
 * `public function execute($configFiles)`

最初のメソッド `::getConfiguration()` は、ファイルパスの配列を受けとります。そして、それをパースして、自分の持つデータとしてマージします。上記で使用した `sfSimpleYamlConfigHandler` クラスでは、1行の追加のみでそれを行うことができます:

    [php]
    static public function getConfiguration(array $configFiles)
    {
      return self::parseYamls($configFiles);
    }

`sfSimpleYamlConfigHandler` クラスは、抽象クラスである `sfYamlConfigHandler` クラスを拡張します。そして、YAML 設定ファイルを処理するためのヘルパーメソッドを使えるようにします。

 * `::parseYamls($configFiles)`
 * `::parseYaml($configFile)`
 * `::flattenConfiguration($config)`
 * `::flattenConfigurationWithEnvironment($config)`

最初の2つのメソッドは、symfony の[コンフィギュレーションカスケード](http://www.symfony-project.org/reference/1_2/en/03-Configuration-Files-Principles#chapter_03_configuration_cascade)の実装です。
次の二つのメソッドは、symfony の[環境の認識](http://www.symfony-project.org/reference/1_2/en/03-Configuration-Files-Principles#chapter_03_environment_awareness)の実装です。

今回のコンフィグハンドラで使う `::getConfiguration()` メソッドは、クラス継承にもとづくコンフィギュレーションをマージするカスタムメソッドが必要になります。`::applyInheritance()` を作り、このロジックをカプセル化しましょう:

    [php]
    // lib/config/sfFormYamlEnhancementsConfigHandler.class.php
    class sfFormYamlEnhancementsConfigHandler extends sfYamlConfigHandler
    {
      public function execute($configFiles)
      {
        $config = self::getConfiguration($configFiles);

        // compile data
        $retval = "<?php\n".
                  "// auto-generated by %s\n".
                  "// date: %s\nreturn %s;\n";
        $retval = sprintf($retval, __CLASS__, date('Y/m/d H:i:s'),
          var_export($config, true));

        return $retval;
      }

      static public function getConfiguration(array $configFiles)
      {
        return self::applyInheritance(self::parseYamls($configFiles));
      }

      static public function applyInheritance($config)
      {
        $classes = array_keys($config);

        $merged = array();
        foreach ($classes as $class)
        {
          if (class_exists($class))
          {
            $merged[$class] = $config[$class];
            foreach (array_intersect(class_parents($class), $classes) as $parent)
            {
              $merged[$class] = sfToolkit::arrayDeepMerge(
                $config[$parent],
                $merged[$class]
              );
            }
          }
        }

        return $merged;
      }
    }

これで、クラス継承ごとにマージされた値をもつことができました。全コンフィギュレーションのフォームオブジェクトを `instanceof` で調べる必要がなくなりました。さらに、このマージはコンフィグハンドラの中で行われるのです。そして、キャッシュとして保存する際に一度だけしか実行されないのです。

これで、より簡単な方法で、マージされた配列をフォームオブジェクトに適用することができるようになりました:

    [php]
    class sfFormYamlEnhancer
    {
      protected
        $configCache = null;

      public function __construct(sfConfigCache $configCache)
      {
        $this->configCache = $configCache;
        $this->configCache->registerConfigHandler('config/forms.yml',
          'sfFormYamlEnhancementsConfigHandler');
      }

      // ...

      public function enhance(sfForm $form)
      {
        $config = include $this->configCache->checkConfig('config/forms.yml');

        $class = get_class($form);
        if (isset($config[$class]))
        {
          $fieldConfigs = $config[$class];
        }
        else if ($overlap = array_intersect(class_parents($class),
          array_keys($config)))
        {
          $fieldConfigs = $config[current($overlap)];
        }
        else
        {
          return;
        }

        foreach ($fieldConfigs as $fieldName => $fieldConfig)
        {
          // ...
        }
      }
    }

テストスクリプトを実行する前に、新しいクラスの継承ロジックのためのアサーションを追加しましょう。

    [yml]
    # config/forms.yml

    # ...

    BaseForm:
      body:
        errors:
          min_length: A base min_length message
          required:   A base required message

新しい `required` メッセージが適用されることをテストスクリプトで検証できます。そして、子クラスのフォームで何も設定していなくても、子クラスのフォームが親クラスの強化内容を受け取ることを確認できます。

    [php]
    $t = new lime_test(5);

    // ...

    $form = new CommentForm();
    $form->bind();
    $enhancer->enhance($form);
    $t->like($form['body']->renderError(), '/A base required message/',
      '->enhance() considers inheritance');

    class SpecialCommentForm extends CommentForm { }
    $form = new SpecialCommentForm();
    $form->bind();
    $enhancer->enhance($form);
    $t->like($form['body']->renderLabel(), '/Please enter your comment/',
      '->enhance() applies parent config');

この新しいテストスクリプトを実行させて、フォーム強化役が期待どおりに動くか検証しましょう。

![テストの検証は OK](http://www.symfony-project.org/images/more-with-symfony/config_cache_tests_5_ok.png)

埋め込みフォームを使って豪華にする
------------------------------------

symfony フォームフレームワークの重要な特徴である埋め込みフォームをまだ考慮していませんでした。`CommentForm` のインスタンスがほかのフォームに埋め込まれている場合には、`forms.yml` で行った強化内容は、適用されません。これはテストスクリプトで簡単に検証することができます:

    [php]
    $t = new lime_test(6);

    // ...

    $form = new BaseForm();
    $form->embedForm('comment', new CommentForm());
    $form->bind();
    $enhancer->enhance($form);
    $t->like($form['comment']['body']->renderLabel(),
      '/Please enter your comment/',
      '->enhance() enhances embedded forms');

この新しいアサーションは、埋め込みフォームが強化されていないことを実証します:

![テストの検証は NG](http://www.symfony-project.org/images/more-with-symfony/config_cache_tests_6_not_ok.png)

テストをパスさせるために、より高度なコンフィグハンドラが必要になります。埋め込みフォームにも `forms.yml` で設定された強化内容を適用できるようにする必要があります。そして、それぞれの設定されたフォームクラスに合う強化役メソッドを生成しましょう。新しい「ワーカー」クラスの中で、カスタムコンフィグハンドラによって、これらのメソッドを生成します。

    [php]
    class sfFormYamlEnhancementsConfigHandler extends sfYamlConfigHandler
    {
      // ...

      protected function getEnhancerCode($fields)
      {
        $code = array();
        foreach ($fields as $field => $config)
        {
          $code[] = sprintf('if (isset($fields[%s]))', var_export($field, true));
          $code[] = '{';

          if (isset($config['label']))
          {
            $code[] = sprintf('  $fields[%s]->getWidget()->setLabel(%s);',
              var_export($config['label'], true));
          }

          if (isset($config['attributes']))
          {
            $code[] = '  $fields[%s]->getWidget()->setAttributes(array_merge(';
            $code[] = '    $fields[%s]->getWidget()->getAttributes(),';
            $code[] = '    '.var_export($config['attributes'], true);
            $code[] = '  ));';
          }

          if (isset($config['errors']))
          {
            $code[] = sprintf('  if ($error = $fields[%s]->getError())',
              var_export($field, true));
            $code[] = '  {';
            $code[] = '    $error->getValidator()->setMessages(array_merge(';
            $code[] = '      $error->getValidator()->getMessages(),';
            $code[] = '      '.var_export($config['errors'], true);
            $code[] = '    ));';
            $code[] = '  }';
          }

          $code[] = '}';
        }

        return implode(PHP_EOL.'    ', $code);
      }
    }

実行時ではなくコード生成時に、コンフィグの配列は特定のキーでチェックされていることに気付いてください。これは、パフォーマンスを向上させることになります。

>**TIP**
>一般的なルールとして、コンフィギュレーションの条件をチェックするロジックは、生成されるコード内ではなく、コンフィグハンドラ内で実行されるべきです。ただ、実行時の条件をチェックするロジックは、実行時に実行されなければなりません。たとえば、強化されたフォームオブジェクト自体をチェックするロジックは、この一つの例となります。

生成されたコードは、クラス定義の中に書かれ、キャッシュディレクトリに保存されます。

    [php]
    class sfFormYamlEnhancementsConfigHandler extends sfYamlConfigHandler
    {
      public function execute($configFiles)
      {
        $forms = self::getConfiguration($configFiles);

        $code = array();
        $code[] = '<?php';
        $code[] = '// auto-generated by '.__CLASS__;
        $code[] = '// date: '.date('Y/m/d H:is');
        $code[] = 'class sfFormYamlEnhancementsWorker';
        $code[] = '{';
        $code[] = '  static public $enhancable = '.var_export(array_keys($forms), true).';';

        foreach ($forms as $class => $fields)
        {
          $code[] = '  static public function enhance'.$class.'(sfFormFieldSchema $fields)';
          $code[] = '  {';
          $code[] = '    '.$this->getEnhancerCode($fields);
          $code[] = '  }';
        }

        $code[] = '}';

        return implode(PHP_EOL, $code);
      }

      // ...
    }

これで、`sfFormYamlEnhancer` クラスは、フォームオブジェクトの操作を生成されたワーカークラスに委ねることになりました。しかし、まだ埋め込みフォームを再帰的に適用しなければいけません。そのためには、フォームのフィールドスキーマ（再帰的にイテレーションができるようにする）とフォームオブジェクト（埋め込みフォームも含む）を平行して実行しなければなりません。

    [php]
    class sfFormYamlEnhancer
    {
      // ...

      public function enhance(sfForm $form)
      {
        require_once $this->configCache->checkConfig('config/forms.yml');
        $this->doEnhance($form->getFormFieldSchema(), $form);
      }

      protected function doEnhance(sfFormFieldSchema $fieldSchema, sfForm $form)
      {
        if ($enhancer = $this->getEnhancer(get_class($form)))
        {
          call_user_func($enhancer, $fieldSchema);
        }

        foreach ($form->getEmbeddedForms() as $name => $form)
        {
          if (isset($fieldSchema[$name]))
          {
            $this->doEnhance($fieldSchema[$name], $form);
          }
        }
      }

      public function getEnhancer($class)
      {
        if (in_array($class, sfFormYamlEnhancementsWorker::$enhancable))
        {
          return array('sfFormYamlEnhancementsWorker', 'enhance'.$class);
        }
        else if ($overlap = array_intersect(class_parents($class),
          sfFormYamlEnhancementsWorker::$enhancable))
        {
          return array('sfFormYamlEnhancementsWorker', 'enhance'.current($overlap));
        }
      }
    }

>**NOTE**
>埋め込みフォームのフィールド群は、すでに埋め込まれている際には、変更されるべきではありません。埋め込みフォームは、目的によって、親フォームに格納されますが、親クラスの表示に関しては、影響はありません。

埋め込みフォームのサポートをしましたので、これでテストは通るようになったはずです。テストスクリプトを実行してみましょう:

![テストの検証は OK](http://www.symfony-project.org/images/more-with-symfony/config_cache_tests_6_ok.png)


カスタムコンフィグキャッシュの結果はどうでしょう？
------------------------------------------------

これまでやってきたことが無駄ではないということを確認するために、ベンチマークをしてみましょう。興味深い結果を得るために、`forms.yml` に PHP ループを使用してフォームクラスを追加してみます。

    [yml]
    # <?php for ($i = 0; $i < 100; $i++): ?> #
    Form<?php echo $i ?>: ~
    # <?php endfor; ?> #

次のコードスニペットを実行して、これらのクラスを作ってください:

    [php]
    mkdir($dir = sfConfig::get('sf_lib_dir').'/form/test_fixtures');
    for ($i = 0; $i < 100; $i++)
    {
      file_put_contents($dir.'/Form'.$i.'.class.php',
        '<?php class Form'.$i.' extends BaseForm { }');
    }

これで、ベンチマークをする準備ができました。次の (http://httpd.apache.org/docs/2.0/programs/ab.html)  のコマンドを実行して得た結果が下にあります。環境は私の MacBook で、標準偏差が2ミリセカンド以下になるまで複数回行いました。

    $ ab -t 60 -n 20 http://localhost/config_cache/web/index.php

まず、ベンチマークの基準として、強化役への接続を使わない状態で始めましょう。`frontendConfiguration` の `sfFormYamlEnhancer` をコメントアウトしてベンチマークを実行してみましょう:

    Connection Times (ms)
                  min  mean[+/-sd] median   max
    Connect:        0    0   0.0      0       0
    Processing:    62   63   1.5     63      69
    Waiting:       62   63   1.5     63      69
    Total:         62   63   1.5     63      69

次に、`sfYaml` を直接クラス内で呼び出していた `sfFormYamlEnhancer::enhance()` の最初のバージョンでベンチマーク実行してみましょう。

    Connection Times (ms)
                  min  mean[+/-sd] median   max
    Connect:        0    0   0.0      0       0
    Processing:    87   88   1.6     88      93
    Waiting:       87   88   1.6     88      93
    Total:         87   88   1.7     88      94

それぞれのリクエストで平均25ミリセカンド増えたことがわかりますね。つまり、約40%の増加です。次に、`->enhance()`を変更した内容を元に戻してみましょう。これで、カスタムコンフィグハンドラが元に戻ります。そして、もう一度ベンチマークを実行してみましょう:

    Connection Times (ms)
                  min  mean[+/-sd] median   max
    Connect:        0    0   0.0      0       0
    Processing:    62   63   1.6     63      70
    Waiting:       62   63   1.6     63      70
    Total:         62   64   1.6     63      70

カスタムコンフィグハンドラを作成したことで、処理時間を最初の基準まで減らすことができました。

Just For Fun: プラグインの作成
-------------------------------

これで、シンプルな YAML 設定ファイルを使い、フォームオブジェクトを強化することが可能になりました。ぜひ、プラグインとして公開し、コミュニティでシェアをするべきですね。まだプラグインを公開していない人にとっては、怯えさせてしまうかもしれませんが、ここでその恐怖をなくすことができると思います。

プラグインは、次のファイル構造になります:

    sfFormYamlEnhancementsPlugin/
      config/
        sfFormYamlEnhancementsPluginConfiguration.class.php
      lib/
        config/
          sfFormYamlEnhancementsConfigHandler.class.php
        form/
          sfFormYamlEnhancer.class.php
      test/
        unit/
          form/
            sfFormYamlEnhancerTest.php

プラグインのインストールの処理を簡単にするために、少し修正する必要があります。強化役オブジェクトの作成とそれへの接続は、プラグインのコンフィギュレーションクラスにカプセル化します:

    [php]
    class sfFormYamlEnhancementsPluginConfiguration extends sfPluginConfiguration
    {
      public function initialize()
      {
        if ($this->configuration instanceof sfApplicationConfiguration)
        {
          $enhancer = new sfFormYamlEnhancer($this->configuration->getConfigCache());
          $enhancer->connect($this->dispatcher);
        }
      }
    }

テストスクリプトは、プロジェクトのブートストラップスクリプトを参照するように修正しましょう:

    [php]
    include dirname(__FILE__).'/../../../../../test/bootstrap/unit.php';

    // ...

最後に、`ProjectConfiguration` で今回作成したプラグインを有効化させます:

    [php]
    class ProjectConfiguration extends sfProjectConfiguration
    {
      public function setup()
      {
        $this->enablePlugins('sfFormYamlEnhancementsPlugin');
      }
    }

プラグインからテストを実行したい際は、これからは、`ProjectConfiguration` で接続してください：

    [php]
    class ProjectConfiguration extends sfProjectConfiguration
    {
      // ...

      public function setupPlugins()
      {
        $this->pluginConfigurations['sfFormYamlEnhancementsPlugin']->connectTests();
      }
    }

これで、`test:*` タスクを使い、プラグインからのテストが実行できます。

![プラグインのテスト](http://www.symfony-project.org/images/more-with-symfony/config_cache_plugin_tests.png)

今回作成したすべてのクラスは、新しいプラグインのディレクトリに配置されました。しかし、まだ問題があります。テストスクリプトは、プロジェクトに配置されているファイルに依存してしまっています。つまり、誰かがこのテストを実行したいと思った際に、同じ構造でないと、テストができなくなってしまっています。

コンフィグキャッシュを呼び出す強化役クラスでコードを分割する必要があります。そして、オーバーライドしてフィクスチャの `forms.yml` を使うようにします。

    [php]
    class sfFormYamlEnhancer
    {
      // ...

      public function enhance(sfForm $form)
      {
        $this->loadWorker();
        $this->doEnhance($form->getFormFieldSchema(), $form);
      }

      public function loadWorker()
      {
        require_once $this->configCache->checkConfig('config/forms.yml');
      }

      // ...
    }

これで、カスタムコンフィグハンドラを直接呼び出すために、テストスクリプトのなかで `->loadWorker()` メソッドをオーバーロードすることができます。`CommentForm`  クラスは、テストスクリプトに移動します。そして `forms.yml` ファイルはプラグインの `test/fixtures` ディレクトリに移動しましょう。

    [php]
    include dirname(__FILE__).'/../../../../../test/bootstrap/unit.php';

    $t = new lime_test(6);

    class sfFormYamlEnhancerTest extends sfFormYamlEnhancer
    {
      public function loadWorker()
      {
        if (!class_exists('sfFormYamlEnhancementsWorker', false))
        {
          $configHandler = new sfFormYamlEnhancementsConfigHandler();
          $code = $configHandler->execute(array(dirname(__FILE__).'/../../fixtures/forms.yml'));

          $file = tempnam(sys_get_temp_dir(), 'sfFormYamlEnhancementsWorker');
          file_put_contents($file, $code);

          require $file;
        }
      }
    }

    class CommentForm extends BaseForm
    {
      public function configure()
      {
        $this->setWidget('body', new sfWidgetFormTextarea());
        $this->setValidator('body', new sfValidatorString(array('min_length' => 12)));
      }
    }

    $configuration = $configuration->getApplicationConfiguration(
      'frontend', 'test', true, null, $configuration->getEventDispatcher());

    $enhancer = new sfFormYamlEnhancerTest($configuration->getConfigCache());

    // ...

最後に、`sfTaskExtraPlugin` を使えば、プラグインをパッケージ化することは簡単です。`plugin:package` タスクを実行して、いくつかプロンプトに入力するだけでパッケージを作成することができます。

    $ php symfony plugin:package sfFormYamlEnhancementsPlugin

>**NOTE**
> この章で説明したコードは、プラグインとして公開されており、symfony 公式サイトのプラグイン用のページよりダウンロードをすることができます:
>
>    http://symfony-project.org/plugins/sfFormYamlEnhancementsPlugin
>
> このプラグインは、ここで説明した内容はもちろんですが、`widgets.yml` や `validators.yml` のサポートも追加されています。そして、さらにフォームの国際化対応を簡単にする `i18n:extract` タスクも統合されています。

最後に
------

ここでのベンチマークの結果を見ればわかりましたね。symfony のコンフィグキャッシュは、パフォーマンスを低下させることはありません。そして、単純なYAML設定ファイルを利用することが可能になるのです。
