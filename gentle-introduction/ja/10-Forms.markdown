第10章 - フォーム
================

フォームへ入力した値の表示、フォームへ送信したデータのバリデーション、およびフォームに関連するすべての特殊なケースの扱いは、Web 開発における最も複雑な作業の 1 つです。幸い、symfony にはとても強力なフォームサブフレームワークを扱う単純なインターフェイスがあり、数行のコードでフォームをデザインし、単純なものから複雑なものまで、さまざまなフォームを扱うことができます。

フォームの表示
-------------

名前、メールアドレス、件名、およびメッセージのフィールドがある、次のような単純な問い合わせフォームを例にします:

![問い合わせフォーム](http://www.symfony-project.org/images/forms_book/en/01_07.png)

symfony では、フォームはオブジェクトで、アクションで定義され、テンプレートに渡されます。フォームを表示するには、まずフォームにあるフィールドを定義する必要があります。symfony ではこれを "ウィジェット" と呼びます。最も単純な方法は、アクションメソッド内で次のように `sfForm` オブジェクトを作ります。

    [php]
    // in modules/foo/actions/actions.class.php
    public function executeContact($request)
    {
      $this->form = new sfForm();
      $this->form->setWidgets(array(
        'name'    => new sfWidgetFormInputText(),
        'email'   => new sfWidgetFormInputText(array('default' => 'me@example.com')),
        'subject' => new sfWidgetFormChoice(array('choices' => array('Subject A', 'Subject B', 'Subject C'))),
        'message' => new sfWidgetFormTextarea(),
      ));
    }

`sfForm::setWidgets()` は、ウィジェットの名前とウィジェットオブジェクトの連想配列を受け取ります。`sfWidgetFormInputText`、`sfWidgetFormChoice`、`sfWidgetFormTextarea` は、symfony の多数あるウィジェットクラスの一部です。すべてのウィジェットの一覧は、この章の後半にあります。

上の例では、2 つのウィジェットオプションを使っています。`default` はすべてのウィジェットで使えるオプションで、ウィジェットの値を設定します。`choices` は `choice` ウィジェット(ドロップダウンリストをレンダリングします)で使うオプションで、ユーザーが選択可能なオプションを設定します。

`foo/contact` アクションでフォームオブジェクトを定義し、`contactSuccess` テンプレートに `$form` 変数として渡します。テンプレートでは、このオブジェクトを使ってフォームの様々なパーツを HTML にレンダリングできます。最も単純な方法は `echo $form` を呼び出して、フォームのすべてのフィールドをフォームのコントロールとラベルの組み合わせでレンダリングします。また、フォームオブジェクトを使って FORM タグをレンダリングすることもできます:

    [php]
    // modules/foo/templates/contactSuccess.php 内
    <?php echo $form->renderFormTag('foo/contact') ?>
      <table>
        <?php echo $form ?>
        <tr>
          <td colspan="2">
            <input type="submit" />
          </td>
        </tr>
      </table>
    </form>

`setWidgets()` に渡すパラメーターに応じて、symfony はフォームを適切に表示します。上のスクリーンショットと全く同じフォームを表示するには、次のようにコードを記述します:

    [php]
    <form action="/frontend_dev.php/foo/contact" method="POST">
      <table>
        <tr>
          <th><label for="name">Name</label></th>
          <td><input type="text" name="name" id="name" /></td>
        </tr>
        <tr>
          <th><label for="email">Email</label></th>
          <td><input type="text" name="email" id="email" value="me@example.com" /></td>
        </tr>
        <tr>
          <th><label for="subject">Subject</label></th>
          <td>
            <select name="subject" id="subject">
              <option value="0">Subject A</option>
              <option value="1">Subject B</option>
              <option value="2">Subject C</option>
            </select>
          </td>
        </tr>
        <tr>
          <th><label for="message">Message</label></th>
          <td><textarea rows="4" cols="30" name="message" id="message"></textarea></td>
        </tr>
        <tr>
          <td colspan="2">
            <input type="submit" />
          </td>
        </tr>
      </table>
    </form>

各ウィジェットはテーブルの各行になり、`<label>` タグとフォームの入力タグがあります。symfony により、ウィジェット名の先頭を大文字(ウィジェット名が `subject` の場合、ラベル名は 'Subject')にしてラベル名が自動的に付与されます。入力タグについては、ウィジェットのタイプに応じてレンダリングされます。また、各ウィジェット名前を元にした `id` 属性が自動的に付与されます。また、フォームのレンダリング結果は常に XHTML 互換です。

フォームの表示のカスタマイズ
----------------------------

`echo $form` はプロトタイプの作成には便利ですが、レンダリング結果の HTML コードを厳密にコントロールしたいはずです。フォームオブジェクトにはフィールドの配列が格納されており、`echo $form` の呼び出しは、実際にはすべてのフィールドを走査し、1 つ 1 つレンダリングしているにすぎません。これを細かく制御するには、手作業ですべてのフィールドを走査し、各フィールドに対して `renderRow()` を呼び出します。次のリストは上のリストと同じ HTML コードをレンダリングしますが、テンプレートでは個々のフィールドに対して echo しています:

    [php]
    // modules/foo/templates/contactSuccess.php内
    <?php echo $form->renderFormTag('foo/contact') ?>
      <table>
        <?php echo $form['name']->renderRow() ?>
        <?php echo $form['email']->renderRow() ?>
        <?php echo $form['subject']->renderRow() ?>
        <?php echo $form['message']->renderRow() ?>
        <tr>
          <td colspan="2">
            <input type="submit" />
          </td>
        </tr>
      </table>
    </form>

フィールドを個々にレンダリングする場合、フィールドの表示順は任意に変更でき、見た目のカスタマイズも可能です。`renderRow()` は最初の引数として HTML 属性のリストを受け取るので、希望する `class`、`id` または JavaScript イベントハンドラーなどを設定できます。`renderRow()` の第 2 引数にはラベルを指定でき、ウィジェット名から自動生成されたラベルを変更できます。次のコードはお問い合せフォームをカスタマイズする例です:

    [php]
    // modules/foo/templates/contactSuccess.php内
    <?php echo $form->renderFormTag('foo/contact') ?>
      <table>
        <?php echo $form['name']->renderRow(array('size' => 25, 'class' => 'foo'), 'Your Name') ?>
        <?php echo $form['email']->renderRow(array('onclick' => 'this.value = "";'), 'Your Email') ?>
        <?php echo $form['message']->renderRow() ?>
        <tr>
          <td colspan="2">
            <input type="submit" />
          </td>
        </tr>
      </table>
    </form>

各フィールドのラベルや入力要素を `<tr>` タグではなく、`<li>` タグと組み合わせて出力したい場合もあります。フィールドの "行" は、ラベル、任意のエラーメッセージ(この章の後半で説明するバリデーションシステムにより追加されます)、ヘルプメッセージ、およびウィジェットで構成されます(ウィジェットは、1 つ以上のフォームコントロールで構成されます)。フォームの様々なフィールドを 1 つずつ出力できるだけでなく、フォームフィールドの様々なパーツを個々にレンダリングできます。`renderRow()` を使う代わりに、`render()` (ウィジェットがレンダリングされます)、`renderError()`、`renderLabel()`、`renderHelp()` を使えます。例えば、`<li>` タグを使ってすべてのフォームをレンダリングしたい場合、テンプレートのコードは次のようになります:

    [php]
    // modules/foo/templates/contactSuccess.php内
    <?php echo $form->renderFormTag('foo/contact') ?>
      <ul>
        <?php foreach ($form as $field): ?>
        <li>
          <?php echo $field->renderLabel() ?>
          <?php echo $field->render() ?>
        </li>
        <?php endforeach; ?>
        <li>
          <input type="submit" />
        </li>
      </ul>
    </form>

このテンプレートコードで、次の HTML がレンダリングされます:

    [php]
    <form action="/frontend_dev.php/foo/contact" method="POST">
      <ul>
        <li>
          <label for="name">Name</label>
          <input type="text" name="name" id="name" />
        </li>
        <li>
          <label for="email">Email</label>
          <input type="text" name="email" id="email" />
        </li>
        <li>
          <label for="subject">Subject</label>
          <select name="subject" id="subject">
            <option value="0">Subject A</option>
            <option value="1">Subject B</option>
            <option value="2">Subject C</option>
          </select>
        </li>
        <li>
          <label for="message">Message</label>
          <textarea rows="4" cols="30" name="message" id="message"></textarea>
        </li>
        <li>
          <input type="submit" />
        </li>
      </ul>
    </form>

>**TIP**
>フィールドの行は、フォームフィールドのすべての要素(ラベル、エラーメッセージ、ヘルプメッセージ、フォームの入力要素)をフォーマッターで変換したものです。デフォルトでは、symfony は `table` フォーマッターを使うので、`renderRow()` により `<tr>` タグ、`<th>` タグ、`<td>` タグの組み合わせが出力されます。上の HTML コードと同じ出力は、次のように `list` フォーマッターをフォームに指定するだけで得られます:

    [php]
    // modules/foo/templates/contactSuccess.php内
    <?php echo $form->renderFormTag('foo/contact') ?>
      <ul>
        <?php echo $form->renderUsing('list') ?>
        <li>
          <input type="submit" />
        </li>
      </ul>
    </form>

-

>**TIP**
>独自のフォーマッターを作るには、`sfWidgetFormSchemaFormatter` クラスの API ドキュメントを参照してください。

フォームのウィジェット
----------------------

フォームの作成に自由に使えるウィジェットは多数あります。すべてのウィジェットには、少なくとも `default` オプションがあります。

フォームを作るとき、次のようにウィジェットのラベルや HTML 属性も定義できます:

    [php]
    $this->form = new sfForm();
    $this->form->setWidgets(array(
      'name'    => new sfWidgetFormInput(array('label' => 'Your Name'), array('size' => 25, 'class' => 'foo')),
      'email'   => new sfWidgetFormInput(array('default' => 'me@example.com', 'label' => 'Your Email'), array('onclick' => 'this.value = "";')),
      'subject' => new sfWidgetFormChoice(array('choices' => array('Subject A', 'Subject B', 'Subject C'))),
      'message' => new sfWidgetFormTextarea(array(), array('rows' => '20', 'cols' => 5)),
    ));

これらのパラメーターはウィジェットの表示に使われます。また、テンプレートで `renderRow()` にカスタムパラメーターを渡すことで、さらにこれらを上書きできます。

>**TIP**: 連想配列を渡す `setWidgets()` メソッドを呼び出す代わりに、`setWidget($name, $widget)` メソッドをウィジェットごとに呼び出す方法もあります。

### 標準ウィジェット

利用可能なウィジェットと、(`renderRow()` を使った)ウィジェットの HTML 出力結果の一覧は以下のとおりです:

    [php]
    // テキスト入力
    $form->setWidget('full_name', new sfWidgetFormInput(array('default' => 'John Doe')));
      <label for="full_name">Full Name</label>
      <input type="text" name="full_name" id="full_name" value="John Doe" />

    // テキストエリア
    $form->setWidget('address', new sfWidgetFormTextarea(array('default' => 'Enter your address here'), array('cols' => 20, 'rows' => 5)));
      <label for="address">Address</label>
      <textarea name="address" id="address" cols="20" rows="5">Enter your address here</textarea>

    // パスワード入力
    // セキュリティの観点から、'password' ウィジェットには 'default' パラメーターがないことに注意してください。
    $form->setWidget('pwd', new sfWidgetFormInputPassword());
      <label for="pwd">Pwd</label>
      <input type="password" name="pwd" id="pwd" />

    // HIDDEN
    $form->setWidget('id', new sfWidgetFormInputHidden(array('default' => 1234)));
      <input type="hidden" name="id" id="id" value="1234" />

    // チェックボックス
    $form->setWidget('single', new sfWidgetFormInputCheckbox(array('value_attribute_value' => 'single', 'default' => true)));
      <label for="single">Single</label>
      <input type="checkbox" name="single" id="single" value="true" checked="checked" />

上の例以外にも、各ウィジェットで利用可能なオプションがあります。各ウィジェットのオプションとそのレンダリング結果などについての詳細は、ウィジェットの API ドキュメントを参照してください。

### リストウィジェット

ユーザーに値の一覧の選択肢を表示する場合、リスト内の単一の値のみを選択するか、複数の値を選択できるかに関わらず、`choice` ウィジェットだけですべて解決できます。(`multiple` と `expanded`)の 2 つの任意のパラメーターによって、ウィジェットのレンダリング結果が変わります:

                      | multiple=false       | multiple=true
                      | (デフォルト)         |
      ----------------|----------------------|---------------------
      expanded=false  |ドロップダウンリスト  |ドロップダウンボックス
      (デフォルト)    |    (`<select>`)      | (`<select multiple>`)
      ----------------|----------------------|----------------------
      expanded=true   |ラジオボタンのリスト  |チェックボックスのリスト
                      |                      |

`choice` ウィジェットには、各オプションの値とテキストを定義する連想配列の `choices` パラメーターを少なくとも指定します。各構文の例を次に示します:

    [php]
    // ドロップダウンリスト(select)
    $form->setWidget('country', new sfWidgetFormChoice(array(
      'choices'   => array('' => 'Select from the list', 'us' => 'USA', 'ca' => 'Canada', 'uk' => 'UK', 'other'),
      'default'   => 'uk'
    )));
    // symfony により次の HTML がレンダリングされる
    <label for="country">Country</label>
    <select id="country" name="country">
      <option value="">Select from the list</option>
      <option value="us">USA</option>
      <option value="ca">Canada</option>
      <option value="uk" selected="selected">UK</option>
      <option value="0">other</option>
    </select>
    
    // 複数選択ドロップダウンリスト
    $form->setWidget('languages', new sfWidgetFormChoice(array(
      'multiple' => 'true',
      'choices'  => array('en' => 'English', 'fr' => 'French', 'other'),
      'default'  => array('en', 0)
    )));
    // symfony により次の HTML がレンダリングされる
    <label for="languages">Language</label>
    <select id="languages" multiple="multiple" name="languages[]">
      <option value="en" selected="selected">English</option>
      <option value="fr">French</option>
      <option value="0" selected="selected">other</option>
    </select>

    // ラジオボタンのリスト
    $form->setWidget('gender', new sfWidgetFormChoice(array(
      'expanded' => true,
      'choices'  => array('m' => 'Male', 'f' => 'Female'),
      'class'    => 'gender_list'
    )));
    // symfony により次の HTML がレンダリングされる
    <label for="gender">Gender</label>
    <ul class="gender_list">
      <li><input type="radio" name="gender" id="gender_m" value="m"><label for="gender_m">Male</label></li>
      <li><input type="radio" name="gender" id="gender_f" value="f"><label for="gender_f">Female</label></li>
    </ul>
    
    // チェックボックスのリスト
    $form->setWidget('interests', new sfWidgetFormChoice(array(
      'multiple' => 'true',
      'expanded' => true,
      'choices' => array('Programming', 'Other')
    )));
    // symfony により次の HTML がレンダリングされる
    <label for="interests">Interests</label>
    <ul class="interests_list">
      <li><input type="checkbox" name="interests[]" id="interests_0" value="0"><label for="interests_0">Programming</label></li>
      <li><input type="checkbox" name="interests[]" id="interests_1" value="1"><label for="interests_1">Other</label></li>
    </ul>

>**Tip**: 各フォーム入力要素の `id` 属性が、ウィジェットの名前と値を組み合わせて symfony により自動的に定義されます。ウィジェットごとに `id` 属性を上書きしたり、次のように `setIdFormat()` メソッドを使って、フォーム全体のルールを定義することもできます:

    [php]
    // modules/foo/actions/actions.class.php内
    $this->form = new sfForm();
    $this->form->setIdFormat('my_form_%s');

### 外部キーウィジェット

フォームを使ってモデルオブジェクトを編集する場合、現在のオブジェクトに関連付けられているオブジェクトのリストといった、特殊な選択肢のリストを使う必要があります。これは、モデル同士が 1 対多、または多対他のリレーションシップで関連付けられている場合に必要になります。幸い、symfony に組み込まれている `sfPropelPlugin` には、こういった状況で使える `sfWidgetFormPropelChoice` ウィジェットがあります(`sfDoctrinePlugin` には同じように `sfWidgetFormDoctrineChoice` ウィジェットがあります)。

たとえば、`Section` に多数の `Articles` がある場合、記事を編集する際に既存のセクションの一覧から選択します。こうするには、`ArticleForm` で次のように `sfWidgetFormPropelChoice` ウィジェットを使います:

    [php]
    $articleForm = new sfForm();
    $articleForm->setWidgets(array(
      'id'        => sfWidgetFormInputHidden(),
      'title'     => sfWidgetFormInputText(),
      'section_id' => sfWidgetFormPropelChoice(array(
        'model'  => 'Section',
        'column' => 'name'
      )
    )));

こうすると、既存のセクションの一覧が表示されます。一覧の表示には、`Section` モデルクラスに定義した`__toString()` メソッドが使われます。symfony により利用可能な `Section` オブジェクトの一覧が取得され、各オブジェクトに対して `echo` を呼び出して `choice` ウィジェットの生成が行われるからです。ですので、`Section` モデルに次のようなメソッドを追加する必要があります:

    [php]
    // lib/model/Section.php内
    public function __toString()
    {
      return $this->getName();
    }

`sfWidgetFormPropelChoice` ウィジェットは `sfWidgetFormChoice` ウィジェットを継承しているので、多対多のリレーションシップの場合は `multiple` オプションを使います。ウィジェットのレンダリング方法を変える場合は `expanded` オプションを使います。

選択肢の一覧を特定の順番に並べ変えたり、選択肢をフィルタリングして利用可能な選択肢の一部のみを表示するには、ウィジェットの `criteria` オプションに `Criteria` オブジェクトを指定します。Doctrine の場合は、ウィジェットの `query` オプションに `Doctrine_Query` オブジェクトを指定することで同様のカスタマイズができます。

### 日付ウィジェット

日付と時刻のウィジェットを使うと、利用可能な年、月、日、時、分から生成されたドロップダウンリストの組み合わせが出力されます。

    [php]
    // 日付
    $years = range(1950, 1990);
    $form->setWidget('dob', new sfWidgetFormDate(array(
      'label'   => 'Date of birth',
      'default' => '01/01/1950',  // can be a timestamp or a string understandable by strtotime()
      'years'   => array_combine($years, $years)
    )));
    // symfony により次の HTML がレンダリングされる
    <label for="dob">Date of birth</label>
    <select id="dob_month" name="dob[month]">
      <option value=""/>
      <option selected="selected" value="1">01</option>
      <option value="2">02</option>
      ...
      <option value="12">12</option>
    </select> /
    <select id="dob_day" name="dob[day]">
      <option value=""/>
      <option selected="selected" value="1">01</option>
      <option value="2">02</option>
      ...
      <option value="31">31</option>
    </select> /
    <select id="dob_year" name="dob[year]">
      <option value=""/>
      <option selected="selected" value="1950">1950</option>
      <option value="1951">1951</option>
      ...
      <option value="1990">1990</option>
    </select>
    
    // 時刻
    $form->setWidget('start', new sfWidgetFormTime(array('default' => '12:00')));
    // symfony により次の HTML がレンダリングされる
    <label for="start">Start</label>
    <select id="start_hour" name="start[hour]">
      <option value=""/>
      <option value="0">00</option>
      ...
      <option selected="selected" value="12">12</option>
      ...
      <option value="23">23</option>
    </select> :
    <select id="start_minute" name="start[minute]">
      <option value=""/>
      <option selected="selected" value="0">00</option>
      <option value="1">01</option>
      ...
      <option value="59">59</option>
    </select>

    // 日付と時刻
    $form->setWidget('end', new sfWidgetFormDateTime(array('default' => '01/01/2008 12:00')));
    // symfony により、年、月、日、時、分の 5 つのドロップダウンリストが HTML にレンダリングされる

もちろん、表示する日付のフォーマット(`format`)を、国際スタイルではなくヨーロッパスタイル(`%month%/%day%/%year%`ではなく`%day%/%month%/%year%`)にしたり、24時制ではなく12時制にしたり、各ドロップダウンボックスの先頭に独自の値を定義したり、値の範囲を定義したりできます。詳細は、日付と時刻ウィジェットに関する API ドキュメントを参照してください。

日付ウィジェットは、symfony の利点がわかりやすいサンプルです。ウィジェットは、単純なフォームの入力要素とは違います。複数のフォーム入力要素の組み合わせも可能で、symfony により透過的にレンダリングや値の取得が行えます。

### 国際化ウィジェット

多言語のアプリケーションでは、日付はユーザーのカルチャーに応じたフォーマットで表示する必要があります(カルチャーとローカライズに関する詳細は、第13章を参照してください)。フォームでローカライズ機能を使うために、symfony には `sfWidgetFormI18nDate` ウィジェットがあります。このウィジェットでは、日付のフォーマットパラメーターを決定するためにユーザーの `culture` を使います。同じように、`month_format` を指定すると、月のドロップダウンで数字の代わりに月の名前を表示できます。

    [php]
    // 日付
    $years = range(1950, 1990);
    $form->setWidget('dob', new sfWidgetFormI18nDate(array(
      'culture'      => $this->getUser()->getCulture(),
      'month_format' => 'name',   // Use any of 'name' (default), 'short_name', and 'number' 
      'label'        => 'Date of birth',
      'default'      => '01/01/1950',
      'years'        => array_combine($years, $years)
    )));
    // 英語ユーザーに対して、symfony により次の HTML がレンダリングされる
    <label for="dob">Date of birth</label>
    <select id="dob_month" name="dob[month]">
      <option value=""/>
      <option selected="selected" value="1">January</option>
      <option value="2">February</option>
      ...
      <option value="12">December</option>
    </select> /
    <select id="dob_day" name="dob[day]">...</select> /
    <select id="dob_year" name="dob[year]">...</select>
    // フランス語ユーザーに対して、symfony により次の HTML がレンダリングされる
    <label for="dob">Date of birth</label>
    <select id="dob_day" name="dob[day]">...</select> /
    <select id="dob_month" name="dob[month]">
      <option value=""/>
      <option selected="selected" value="1">Janvier</option>
      <option value="2">Février</option>
      ...
      <option value="12">Décembre</option>
    </select> /
    <select id="dob_year" name="dob[year]">...</select>

同様に、時刻の国際化ウィジェット(`sfWidgetFormI18nTime`)、日付と時刻の国際化ウィジェット(`sfWidgetFormI18nDateTime`)もあります。

国と言語を選択するドロップダウンリストも、ユーザーのカルチャーに依存するウィジェットで、多くのフォームで使われます。symfony には、国と言語の選択用に特殊なウィジェットが組み込まれています。これらのウィジェットでは `choices` を指定する必要がありません。symfony により、ユーザーの言語にある国と言語のリストから自動生成されます(symfony に組み込まれた 250 のリファレンス言語から使用する言語を任意に選択します)。

    // 国のリスト
    $form->setWidget('country', new sfWidgetFormI18nCountryChoice(array('default' => 'UK')));
    // 英語ユーザーに対して、symfony により次の HTML がレンダリングされる
    <label for="country">Country</label>
    <select id="country" name="country">
      <option value=""/>
      <option value="AD">Andorra</option>
      <option value="AE">United Arab Emirates</option>
      ...
      <option value="ZWD">Zimbabwe</option>
    </select>

    // 言語リスト
    $form->setWidget('language', new sfWidgetFormI18nLanguageChoice(array(
      'languages' => array('en', 'fr', 'de'),  // optional restricted list of languages
      'default'   => 'en'
    )));
    // 英語ユーザーに対して、symfony により次の HTML がレンダリングされる
    <label for="language">Language</label>
    <select id="language" name="language">
      <option value=""/>
      <option value="de">German</option>
      <option value="en" selected="selected">English</option>
      <option value="fr">French</option>
    </select>

### ファイルウィジェット

ファイル選択タグの処理は、他のウィジェットと比較してさほど難しいというわけではありません:

    [php]
    // ファイル選択
    $form->setWidget('picture', new sfWidgetFormInputFile());
    // symfony により次の HTML がレンダリングされる
    <label for="picture">Picture</label>
    <input id="picture" type="file" name="picture"/>
    // フォームにファイルウィジェットがある場合、renderFormTag() によりマルチパートオプションのある <form> タグがレンダリングされます
    
    // 編集可能なファイル選択
    $form->setWidget('picture', new sfWidgetFormInputFileEditable(array('default' => '/images/foo.png')));
    // symfony により、現在のファイルのプレビューとともに、HTML にファイル選択タグがレンダリングされる

>**TIP**: サードパーティ製のプラグインで、多くの追加ウィジェットが提供されています。リッチテキストエディタのウィジェット、カレンダーウィジェット、または JavaScript ライブラリを使った様々な "リッチ UI" 用のウィジェットを簡単に検索できます。詳細は、[プラグインリポジトリ](http://www.symfony-project.org/plugins/)を確認してください。

フォームの送信の処理
--------------------

ユーザーがフォームにデータを入力して送信すると、Web アプリケーションサーバーはリクエストからデータを抽出し、何らかの処理を行う必要があります。`sfForm` クラスには、数行のコードでこれらの処理をすべて行うためのメソッドがあります。

### 単純なフォームの処理

ウィジェットは通常の HTML フォームフィールドとして出力されるので、フォームの送信を受け取るアクションでフォームの値を取得するには、単に関連するリクエストパラメーターをチェックするだけです。サンプルのお問い合せフォームの場合、アクションのコードは次のようになります:

    [php]
    // modules/foo/actions/actions.class.php内
    public function executeContact($request)
    {
      // フォームを定義
      $this->form = new sfForm();
      $this->form->setWidgets(array(
        'name'    => new sfWidgetFormInputText(),
        'email'   => new sfWidgetFormInput(array('default' => 'me@example.com')),
        'subject' => new sfWidgetFormChoice(array('choices' => array('Subject A', 'Subject B', 'Subject C'))),
        'message' => new sfWidgetFormTextarea(),
      ));

      // リクエストを処理
      if ($request->isMethod('post'))
      {
        // フォームの送信を諸利
        $name = $request->getParameter('name');
        // 何か処理
        // ...
        $this->redirect('foo/bar');
      }
    }

リクエストメソッドが 'GET' の場合、このアクションは `sfView::SUCCESS` で終了し、`contactSuccess` テンプレートをレンダリングしてフォームを表示します。リクエストメソッドが 'POST' の場合、アクションはフォームの送信を処理し、別のアクションへリダイレクトしています。このアクションを動作させるためには、`<form>` の対象アクションが表示された時と同じである必要があります。ですので、先に示した例でフォームの対象を `foo/contact` としていました:

    [php]
    // modules/foo/templates/contactSuccess.php内
    <?php echo $form->renderFormTag('foo/contact') ?>
    ...

### データのバリデーションのあるフォームの処理

実際には、フォームから送信された値の処理は、単にユーザーが入力した値を取得するだけではありません。多くの場合、アプリケーションのコントローラーで次の処理が必要になります:

 1. データが事前に定義したルール(必須のフィールド、Eメールの形式化どうか、など)に沿っているかどうか確認する。
 2. 任意で、入力データの形式を変換する(前後の空白の除去、日付を PHP フォーマットへ変換、など)。
 3. データが有効ではない場合、フォームを再度表示し、エラーメッセージがあれば表示する。
 4. データが有効な場合、何らかの処理を行って別のアクションへリダイレクトする。

symfony により、送信された値を、事前に定義したルールで自動的にバリデートできます。まず、各フィールドに対してバリデーターを定義します。次に、フォームが送信されたら、フォームオブジェクトと送信された値を "バインド" します(つまり、ユーザーから送信された値を取得し、フォームに設定します)。最後に、フォームを使ってデータが有効化どうかをチェックします。次の例では、`email` ウィジェットから取得した値がメールアドレスであるかどうか、および、`message` に 4 文字以上入力されているかどうかを確認する方法を示します:

    [php]
    // modules/foo/actions/actions.class.php内
    public function executeContact($request)
    {
      // フォームを定義
      $this->form = new sfForm();
      $this->form->setWidgets(array(
        'name'    => new sfWidgetFormInputText(),
        'email'   => new sfWidgetFormInput(array('default' => 'me@example.com')),
        'subject' => new sfWidgetFormChoice(array('choices' => array('Subject A', 'Subject B', 'Subject C'))),
        'message' => new sfWidgetFormTextarea(),
      ));
      $this->form->setValidators(array(
        'name'    => new sfValidatorString(),
        'email'   => new sfValidatorEmail(),
        'subject' => new sfValidatorString(),
        'message' => new sfValidatorString(array('min_length' => 4))
      ));

      // リクエストを処理
      if ($request->isMethod('post'))
      {
        $this->form->bind(/* user submitted data */);
        if ($this->form->isValid())
        {
          // フォームの送信を諸利
          // ...

          $this->redirect('foo/bar');
        }
      }
    }

`setValidators()` の構文は `setWidgets()` メソッドと似ています。`sfValidatorEmail` と `sfValidatorString` は、数多くの symfony のバリデータークラスの一部です。バリデータークラスの一覧は、この章の後半にあります。`sfForm` には、バリデーターを 1 つずつ追加する `setValidator()` メソッドもあります。

リクエストのデータをフォームに設定してバインドするには、`sfForm::bind()` メソッドを使います。フォームは、データの有効性を検証するためにデータをバインドする必要があります。

`isValid()` は、登録されているすべてのバリデーターにエラーががないか確認します。`isValid()` が `true` を返した場合、アクションではフォームの送信を処理できます。フォームが有効ではない場合、アクションはデフォルトの `sfView::SUCCESS` を戻して終了し、再度フォームを表示します。この場合、フォームは最初に表示された時と同じく単にデフォルト値で表示されるのではなく、ユーザーが入力した値で埋められた状態で表示されます。また、バリデーターで検証に失敗した場合は、エラーメッセージも表示されます。

![無効なフォーム](http://www.symfony-project.org/images/forms_book/en/02_01.png)

>**TIP**: フォームに無効なフィールドがあっても、バリデーション処理はそこで処理を中断するわけではありません。`isValid()` ではすべてのフォームデータが処理され、すべてのフィールドのエラーが確認されます。ですので、ユーザーが間違いを修正して再度フォームを送信したときに、新たに別のエラーメッセージが表示されるということはありません。

### クリーンなフォームデータを使う

先のリストでは、バインド処理でフォームから受け取ったリクエストデータの定義は行っていませんでした。リクエストにはフォームデータ以外のものもあります。たとえば、ヘッダー、Cookie、GET の引数で渡されるパラメーターなどで、これらはすべてバインド処理の邪魔になります。`bind()`メソッドには、フォームデータのみを渡すようにしましょう。

幸い、symfony ではフォームの入力要素の名前に配列構文を使えます。アクションでフォームを定義する時に、`setNameFormat()` メソッドを使って次のように名前属性のフォーマットを定義します:

    [php]
    // modules/foo/actions/actions.class.php内
    // フォームを定義
    $this->form->setNameFormat('contact[%s]');

こうすると、生成されたフォームの入力要素は、すべて単純な `WIDGET_NAME` ではなく、`form[WIDGET_NAME]` という形式になります:

    [php]
    <label for="contact_name">Name</label>
    <input type="text" name="contact[name]" id="contact_name" />
    ...
    <label for="contact_email">Email</label>
    <input type="text" name="contact[email]" id="contact_email" value="me@example.com" />
    ...
    <label for="contact_subject">Subject</label>
    <select name="contact[subject]" id="contact_subject">
      <option value="0">Subject A</option>
      <option value="1">Subject B</option>
      <option value="2">Subject C</option>
    </select>
    ...
    <label for="contact_message">Message</label>
    <textarea rows="4" cols="30" name="contact[message]" id="contact_message"></textarea>

アクションでは、単一の変数で `contact` リクエストパラメーターを取得できるようになります。この変数には、フォームにユーザーが入力したすべてのデータの配列が格納されています:

    [php]
    // modules/foo/actions/actions.class.php内
    // リクエストの処理
    if ($request->isMethod('post'))
    {
      $this->form->bind($request->getParameter('contact'));
      if ($this->form->isValid())
      {
        // フォームの送信を処理
        $contact = $this->form->getValues();
        $name = $contact['name'];

        // 特定の値を取得
        $name = $this->form->getValue('name');

        // 何らかの処理
        // ...
        $this->redirect('foo/bar');
      }
    }

`bind()` メソッドでパラメーターの配列を受け取ると、クライアントサイドで注入された追加のフィールドを symfony により自動的に排除できます。このセキュリティ機能があるため、元のフォームで定義されていないフィールドが `contact` パラメーターに含まれていた場合、フォームのバリデーションは失敗します。

上のアクションのコードで、以前に記述したものと違う箇所がもう 1 つあることに気づいたでしょうか。アクションでは、リクエストの値を直接使うのではなく、`$form->getValues()` により渡された値の配列を使っています。バリデーターによって入力値はフィルタリングされ、クリーンになるので、リクエストから取得した値ではなく、(`getValues()` や `getValue()` を使って)フォームオブジェクトから取得した値を信頼するのは良い方法です。また、日付ウィジェットのような複合フィールドの場合は、`getValues()` から返されるデータはすでに再編成されており、ウィジェットの名前のみで値を取得できます:

    [php]
    // フォームが送信された時、次のような '日付' ウィジェットは...
    <label for="contact_dob">Date of birth</label>
    <select id="contact_dob_month" name="contact[dob][month]">...</select> /
    <select id="contact_dob_day" name="contact[dob][day]">...</select> /
    <select id="contact_dob_year" name="contact[dob][year]">...</select>
    // ...アクションで次のように 3 つのリクエストパラメーターになります。
    $contact = $request->getParameter('contact');
    $month = $contact['dob']['month'];
    $day = $contact['dob']['day'];
    $year = $contact['dob']['year'];
    $dateOfBirth = mktime(0, 0, 0, $month, $day, $year);
    // しかし、getValues() を使うと、直接正しい日付を取得できます。
    $contact = $this->form->getValues();
    $dateOfBirth = $contact['dob'];

ですので、(`setNameFormat()` を使って)フォームフィールドには必ず配列構文を使うようにし、(`getValues()` を使って)必ずクリーンなフォームデータを使うようにします。

### エラーメッセージの表示のカスタマイズ

上のスクリーンショットに表示されているエラーメッセージはどこで定義されたものでしょうか? ウィジェットは 4 つのコンポーネントで構成されていると説明しました。エラーメッセージはその 4 つのうちの 1 つです。実際、デフォルトの(テーブル)フォーマッターは、フィールドの行を次のようにレンダリングします:

    [php]
    <?php if ($field->hasError()): ?>
    <tr>
      <td colspan="2">
        <?php echo $field->renderError() ?>           // エラーのリスト
      </td>
    </tr>
    <?php endif; ?>
    <tr>
      <th><?php echo $field->renderLabel() ?></th>    // ラベル
      <td>
        <?php echo $field->render() ?>                // ウィジェット
        <?php if ($field->hasHelp()): ?>
        <br /><?php echo $field->renderHelp() ?>      // ヘルプ
        <?php endif; ?>
      </td>
    </tr>

上のメソッドのどれかを使うと、各フィールドでのエラーメッセージの表示方法をカスタマイズできます。また、フォームの一番上に、フォームが無効な場合のグローバルエラーメッセージを表示できます:

    [php]
    <?php if ($form->hasErrors()): ?>
      The form has some errors you need to fix.
    <?php endif; ?>

### バリデーターのカスタマイズ

フォームでは、すべてのフィールドにバリデータを設定する必要があり、デフォルトでは、すべてのフィールドは必須になります。フィールドの必須を解除するには、バリデーターに `required` オプションを渡し、値に `false` を設定します。たとえば、次のリストでは `name` フィールドを必須にし、`email` フィールドを任意にする方法を示しています:

    [php]
    $this->form->setValidators(array(
      'name'    => new sfValidatorString(),
      'email'   => new sfValidatorEmail(array('required' => false)),
      'subject' => new sfValidatorString(),
      'message' => new sfValidatorString(array('min_length' => 4))
    ));

1 つのフィールドに、1 つ以上のバリデーターを適用できます。たとえば、`email` フィールドでは `sfValidatorEmail` バリデーターと、最小 4 文字に指定した `sfValidatorString` バリデーターの両方で有効であることを検証したい場合などです。このような場合、`sfValidatorAnd` バリデーターを使ってバリデーターを結合し、`sfValidatorEmail` バリデーターと `sfValidatorString` バリデーターを引数で渡します:

    [php]
    $this->form->setValidators(array(
      'name'    => new sfValidatorString(),
      'email'   => new sfValidatorAnd(array(
        new sfValidatorEmail(),
        new sfValidatorString(array('min_length' => 4)),
      ), array('required' => false)),
      'subject' => new sfValidatorString(),
      'message' => new sfValidatorString(array('min_length' => 4))
    ));

両方のバリデーターで値が有効だった場合、`email` フィールドは有効となります。同じように `sfValidatorOr` バリデーターを使って複数のバリデーターを結合することもできます。この場合、複数のバリデーターのうちの 1 つが有効であれば、フィールドは有効となります。

無効なバリデーターがあると、フィールドにエラーメッセージが表示されます。エラーメッセージは英語ですが、symfony の国際化ヘルパーを使えます。プロジェクトで多の言語を使いたい場合、i18n ディレクトリでエラーメッセージを翻訳するだけです。別の方法として、すべてのバリデーターは第3引数でエラーメッセージをカスタマイズできます。各バリデーターには、少なくとも2つのエラーメセージが定義されています。`required` メッセージと `invalid` メッセージです。バリデーターの中にはエラーメッセージを異なる目的で使うものもありますが、第3引数を使ったエラーメッセージの上書きは常に可能です:

    [php]
    // modules/foo/actions/actions.class.php内
    $this->form->setValidators(array(
      'name'    => new sfValidatorString(),
      'email'   => new sfValidatorEmail(array(), array(
        'required'   => 'Please provide an email',
        'invalid'    => 'Please provide a valid email address (me@example.com)'
      )),
      'subject' => new sfValidatorString(),
      'message' => new sfValidatorString(array('min_length' => 4), array(
        'required'   => 'Please provide a message',
        'min_length' => 'Please provide a longer message (at least 4 characters)'
      ))
    ));

通常、これらのカスタムメッセージはテンプレートで i18n ヘルパーを使ってレンダリングされます。したがって、多言語アプリケーションでのカスタムエラーメッセージもディレクトリで翻訳します(詳細は第13章をご参照ください)。

### バリデーターを複数のフィールドに適用する

上のコードでフォームにバリデーターを定義するのに使った構文は、2 つのフィールドを*一緒に*バリデートすることはできません。たとえば、登録フォームでは通常、2 つの `password` フィールドがあり、これらの値が一致しないと登録できないようにします。各パスワードフィールドはそれ自体では有効ではなく、他のフィールドと関連付けられて始めて有効になります。

このように複数の値に対して機能するバリデーターを設定するには、`setPostValidator()` で複数のバリデーターを指定します。ポストバリデーターは他のすべてのバリデーターの後に実行され、クリーンアップされた値の配列を受け取ります。入力フォームのデータを直接バリデートする場合は、代わりに `setPreValidator()` メソッドでバリデーターを設定します。

典型的な登録フォームの定義は次のようになります:

    [php]
    // modules/foo/actions/actions.class.php内
    // フォームを定義
    $this->form = new sfForm();
    $this->form->setWidgets(array(
      'login'     => new sfWidgetFormInputText(),
      'password1' => new sfWidgetFormInputText(),
      'password2' => new sfWidgetFormInputText()
    );
    $this->form->setValidators(array(
      'login'     => new sfValidatorString(), // login is required
      'password1' => new sfValidatorString(), // password1 is required
      'password2' => new sfValidatorString(), // password2 is required
    ));
    $this->form->setPostValidators(new sfValidatorSchemaCompare('password1', '==', 'password2'));

`sfValidatorSchemaCompare` バリデーターは特殊な複数バリデーターで、すべてのクリーンアップ済みの値を受け取り、それらのうちの 2 つの値を比較します。また、2 つ以上のポストバリデーターを組み合わせる場合は、`sfValidatorAnd` バリデーターや `sfValidatorOr` バリデーターを使います。

バリデーター
------------

symfony には非常に多くのバリデーターがあります。各バリデーターは、オプションの配列とエラーの配列を引数で受け取るので、`required` や `invalid` エラーメッセージをカスタマイズできます。

    [php]
    // 文字列バリデーター
    $form->setValidator('message', new sfValidatorString(array(
      'min_length' => 4,
      'max_length' => 50,
    ),
    array(
      'min_length' => 'Please post a longer message',
      'max_length' => 'Please be less verbose',
    )));
    
    // 数字バリデーター
    $form->setValidator('age', new sfValidatorNumber(array( // 整数値を強制する場合は、代わりに 'sfValidatorInteger' を使います
      'min'  => 18,
      'max'  => 99.99,
    ),
    array(
      'min' => 'You must be 18 or more to use this service',
      'max' => 'Are you kidding me? People over 30 can\'t even use the Internet',
    )));
    
    // Eメールバリデーター
    $form->setValidator('email', new sfValidatorEmail());
    
    // URLバリデーター
    $form->setValidator('website', new sfValidatorUrl());
    
    // 正規表現バリデーター
    $form->setValidator('IP', new sfValidatorRegex(array(
      'pattern' => '^[0-9]{3}\.[0-9]{3}\.[0-9]{2}\.[0-9]{3}$'
    )));

いくつかのフォーム要素(たとえばドロップダウンリスト、チェックボックス、ラジオボタンのグループ)は選択肢が制限されていますが、悪意のあるユーザーはFirebugを使ったり、スクリプト言語からクエリーを送信したりしてフォームをハックします。ですので、制限された値のみを受け取るフィールドでも、必ずバリデートする必要があります:

    [php]
    // ブール値バリデーター
    $form->setValidator('has_signed_terms_of_service', new sfValidatorBoolean());
    
    // 選択肢バリデーター(値をリストの 1 つに制限)
    $form->setValidator('subject', new sfValidatorChoice(array(
      'choices' => array('Subject A', 'Subject B', 'Subject C')
    )));
    
    // 複数選択バリデーター
    $form->setValidator('languages', new sfValidatorChoice(array(
      'multiple' => true,
      'choices' => array('en' => 'English', 'fr' => 'French', 'other')
    )));

国際化の選択肢バリデーターは、国のリスト用のバリデーター(`sfValidatorI18nChoiceCountry`)、言語リスト用のバリデーター(`sfValidatorI18nChoiceLanguage`)があります。これらのバリデーターでは、オプションのリストをさらに制限したい場合、`countries` と `languages` オプションを使います。

`sfValidatorChoice` バリデーターは、`sfWidgetFormChoice` ウィジェットをバリデートするのに使われます。`sfWidgetFormChoice` ウィジェットで外部キーカラムを選択肢に使っているので、外部キーの値が外部テーブルに存在しているかどうかを検証するバリデーターも用意されています:

    [php]
    // Propel 選択肢バリデーター
    $form->setValidator('section_id', new sfValidatorPropelChoice(array(
      'model'  => 'Section',
      'column' => 'name'
    )));
    
    // Doctrine 選択肢バリデーター
    $form->setValidator('section_id', new sfValidatorDoctrineChoice(array(
      'model'  => 'Section',
      'column' => 'name'
    )));

他の有用なモデル関連のバリデーターは、`sfValidatorPropelUnique` バリデーターです。このバリデーターを使うと、フォームで入力された新しい値が、ユニークインデックスの設定されたデータベースのカラムで既存の値と衝突しないか確認できます。たとえば、2 人のユーザーは同一の `login` を使えないので、`User` オブジェクトをフォームで編集する場合は、このカラムに対して `sfValidatorPropelUnique` バリデーターを追加します:

    [php]
    // Propelユニークバリデーター
    $form->setValidator('nickname', new sfValidatorPropelUnique(array(
      'model'  => 'User', 
      'column' => 'login'
    )));
    
    $form->setValidator('nickname', new sfValidatorDoctrineUnique(array(
      'model'  => 'User', 
      'column' => 'login'
    )));


フォームをよりセキュアにし、[クロスサイトリクエストフォージェリ](http://en.wikipedia.org/wiki/Cross-site_request_forgery)攻撃を回避するには、CSRF保護を有効にします:

    [php]
    // CSRF保護 - 誰にも知られていないランダムな秘密の文字列を設定します
    $form->addCSRFProtection('flkd445rvvrGV34G');

>**TIP**: CSRF シークレットは、`settings.yml` ファイルにサイト全体で使うものを 1 つ設定します:

    [yml]
    # apps/myapp/config/settings.yml内
    all:
      .settings:
        # フォームのセキュリティシークレット(CSRF保護)
        csrf_secret:       ##CSRF_SECRET##     # ユニークな文字列で CSRF 保護を有効にするか、false で無効にします

複数バリデーターは単一の入力に対して機能するのではなく、フォーム全体に対して機能します。利用可能な複数バリデーターのリストは次のとおりです:

    [php]
    // 比較バリデーター - 2 つのフィールドを比較する 
    $form->setPostValidator(new sfValidatorSchemaCompare('password1', '==', 'password2'));
    
    // 追加フィールドのバリデーター: リクエストにあるフィールドが、フォームで定義されているかどうか調べる
    $form->setOption('allow_extra_fields', false);
    $form->setOption('filter_extra_fields', true);

フォームを使う別の方法
----------------------

### フォームクラス

すべてのウィジェットのオプション、バリデーター、フォームのパラメーターを記述すると、アクションクラスに記述するお問い合せフォームの定義はとても長くなります:

    [php]
    // modules/foo/actions/actions.class.php内
    // フォームの定義
    $this->form = new sfForm();
    $this->form->setNameFormat('contact[%s]');
    $this->form->setIdFormat('my_form_%s');

    $this->form->setWidgets(array(
      'name'    => new sfWidgetFormInputText(),
      'email'   => new sfWidgetFormInput(array('default' => 'me@example.com')),
      'subject' => new sfWidgetFormChoice(array('choices' => array('Subject A', 'Subject B', 'Subject C'))),
      'message' => new sfWidgetFormTextarea(),
    ));
    $this->form->setValidators(array(
      'name'    => new sfValidatorString(),
      'email'   => new sfValidatorEmail(),
      'subject' => new sfValidatorString(),
      'message' => new sfValidatorString(array('min_length' => 4))
    ));

これを解決するには、同じプロパティを持つフォームクラスを作り、これを使うすべてのアクションで、単にインスタンス化するようにします。たとえば、お問い合せフォーム用のフォームクラスは次のように作ります:

    [php]
    // lib/form/ContactForm.class.php内
    class ContactForm extends sfForm
    {
      protected static $subjects = array('Subject A', 'Subject B', 'Subject C');

      public function configure()
      {
        $this->setNameFormat('contact[%s]');
        $this->setIdFormat('my_form_%s');
        $this->setWidgets(array(
          'name'    => new sfWidgetFormInputText(),
          'email'   => new sfWidgetFormInput(array('default' => 'me@example.com')),
          'subject' => new sfWidgetFormChoice(array('choices' => array('Subject A', 'Subject B', 'Subject C'))),
          'message' => new sfWidgetFormTextarea(),
        ));
        $this->setValidators(array(
          'name'    => new sfValidatorString(),
          'email'   => new sfValidatorEmail(),
          'subject' => new sfValidatorString(),
          'message' => new sfValidatorString(array('min_length' => 4))
        ));
        $this->setDefaults(array(
          'email' => 'me@example.com'
        ));
      }
    }

アクションではお問い合せフォームオブジェクトを使うので、次のようにとても簡単になります:

    [php]
    // modules/foo/actions/actions.class.php内
    // フォームを定義
    $this->form = new ContactForm();

### フォームオブジェクトの変更

フォームクラスの定義を使う場合、フォームはアクションの外部で定義されています。この場合、動的なデフォルト値の割り当ては難しくなります。これを解決するために、フォームオブジェクトの第1引数でデフォルト値の配列を渡します:

    [php]
    // modules/foo/actions/actions.class.php内
    // フォームを定義
    $this->form = new ContactForm(array('email' => 'me@example.com'));

また、既存のフィールド名で `setWidget()` や `setValidator()` を呼び出して、既存のウィジェットやバリデーターを上書きすることもできます。

しかし、symfony ではウィジェットやバリデーターはオブジェクトです。プロパティを変更するクリーンな API があります:

    [php]
    // modules/foo/actions/actions.class.php内
    // フォームを定義
    $this->form = new ContactForm();

    // 言語の複数選択を可能にする
    $form->getWidget('language')->setOption('multiple', true);
    // オプションウィジェットに 'gender' リストを追加する
    $form->setWidget('gender', new sfWidgetFormChoice(array('expanded' => true, 'choices' => array('m' => 'Male', 'f' => 'Female')), array('class' => 'gender_list')));
    // 'subject' ウィジェットの HTML 属性を変更する
    $form->getWidget('subject')->setAttribute('disabled', 'disabled');
    // 'subject' フィールドを削除する
    unset($form['subject'])
    // メモ: ウィジェットだけを削除することはできません。ウィジェットを削除する場合は、関連するバリデーターも同時に削除します。

    // 'message' バリデーターの 'min_length' エラーを変更する
    $form->getValidator('message')->setMessage('min_length', 'Message too short');
    // 'name' を任意にする
    $form->getValidator('name')->setOption('required', false);

カスタムウィジェットクラスとカスタムバリデータークラス
------------------------------------------------------

カスタムウィジェットは、単純に `sfWidgetForm` を継承するクラスで、`configure()` メソッドと `render()` メソッドを定義します。ウィジェットシステムについて理解を深めるには、既存のウィジェットクラスのコードを確認すると良いでしょう。ウィジェットの構造を説明するために、`sfWidgetFormInput` ウィジェットのコードを次のリストに示します:

    [php]
    class sfWidgetFormInputText extends sfWidgetForm
    {
      /**
       * Configures the current widget.
       * This method allows each widget to add options or HTML attributes during widget creation.
       * Available options:
       *  * type: The widget type (text by default)
       *
       * @param array $options     An array of options
       * @param array $attributes  An array of default HTML attributes
       * @see sfWidgetForm
       */
      protected function configure($options = array(), $attributes = array())
      {
        $this->addOption('type', 'text');
        $this->setOption('is_hidden', false);
      }

      /**
       * Renders the widget as HTML
       *
       * @param  string $name        The element name
       * @param  string $value       The value displayed in this widget
       * @param  array  $attributes  An array of HTML attributes to be merged with the default HTML attributes
       * @param  array  $errors      An array of errors for the field
       * @return string An HTML tag string
       * @see sfWidgetForm
       */
      public function render($name, $value = null, $attributes = array(), $errors = array())
      {
        return $this->renderTag('input', array_merge(
          array('type' => $this->getOption('type'), 'name' => $name, 'value' => $value), 
          $attributes
        ));
      }
    }

A validator class must extend `sfValidatorBase` and provide a `configure()` and a `doClean()` methods. Why `doClean()` and not `validate()`? Because validators do two things: they check that the input fulfills a set of rules, and they optionally clean the input (for instance by forcing the type, trimming, converting date strings to timestamp, etc.). So the `doClean()` method must return the cleaned input, or throw a `sfValidatorError` exception if the input doesn't satisfy any of the validator rules. Here is an illustration of this concept, with the code of the `sfValidatorInteger` validator.

    [php]
    class sfValidatorInteger extends sfValidatorBase
    {
      /**
       * Configures the current validator.
       * This method allows each validator to add options and error messages during validator creation.
       * Available options:
       *  * max: The maximum value allowed
       *  * min: The minimum value allowed
       * Available error codes:
       *  * max
       *  * min
       *
       * @param array $options   An array of options
       * @param array $messages  An array of error messages
       * @see sfValidatorBase
       */
      protected function configure($options = array(), $messages = array())
      {
        $this->addOption('min');
        $this->addOption('max');
        $this->addMessage('max', '"%value%" must be less than %max%.');
        $this->addMessage('min', '"%value%" must be greater than %min%.');
        $this->setMessage('invalid', '"%value%" is not an integer.');
      }

      /**
       * Cleans the input value.
       *
       * @param  mixed $value  The input value
       * @return mixed The cleaned value
       * @throws sfValidatorError
       */
      protected function doClean($value)
      {
        $clean = intval($value);
        if (strval($clean) != $value)
        {
          throw new sfValidatorError($this, 'invalid', array('value' => $value));
        }
        if ($this->hasOption('max') && $clean > $this->getOption('max'))
        {
          throw new sfValidatorError($this, 'max', array('value' => $value, 'max' => $this->getOption('max')));
        }
        if ($this->hasOption('min') && $clean < $this->getOption('min'))
        {
          throw new sfValidatorError($this, 'min', array('value' => $value, 'min' => $this->getOption('min')));
        }

        return $clean;
      }
    }

Check the symfony API documentation for widget and validator classes names and syntax.

>**SIDEBAR**
>Use options to pass parameters to the form class
>
>A common issue with forms is to be able to use application parameters, such as the user's culture. The fastest but ugly way is to retrieve the user instance through the sfContext instance, using the `sfContext::getInstance()->getUser()` method. However, this solution create a big coupling between the form and the context, making the testing and reusability more difficult. To avoid this problem, you can simply use option to pass the `culture` value to the form :
>
>     // from an action
>     public function executeContact(sfWebRequest $request)
>     {
>       $this->form = new ContactForm(array(), array('culture' => $this->getUser()->getCulture()));
>     }
>
>     // from a unit test
>     $form = new ContactForm(array(), array('culture' => 'en'));
>
>     class ContactForm extends sfForm
>     {
>       public function configure()
>       {
>         /* ... */
>         $this->setWidget('country', new sfWidgetFormI18NCountry(array('culture' => $this->getOption('culture'))));
>         /* ... */
>       }
>     }
>

Forms Based on a Model
----------------------

Forms are the primary way to edit database records in web applications. And most forms in symfony applications allow the editing of a Model object. But the information necessary to build a form to edit a model already exists: it is in the schema. So symfony provides a form generator for model objects, that makes the creation of model-editing forms a snap.

>**Note**: Similar features to the ones described below exist for Doctrine.

### Generating Model Forms

Symfony can deduce the widget types and the validators to use for a model editing form, based on the schema. Take the following schema, for instance with the Propel ORM:

    [yml]
    // config/schema.yml
    propel:
      article:
        id:           ~
        title:        { type: varchar(255), required: true }
        slug:         { type: varchar(255), required: true, index: unique }
        content:      longvarchar
        is_published: { type: boolean, required: true }
        author_id:    { type: integer, required: true, foreignTable: author, foreignReference: id, OnDelete: cascade }
        created_at:   ~

      author:
        id:           ~
        first_name:   varchar(20)
        last_name:    varchar(20)
        email:        { type: varchar(255), required: true, index: unique }
        active:       boolean

A form to edit an `Article` object should use a hidden widget for the `id`, a text widget for the `title`, a string validator for the `title`, etc. Symfony generates the form for you, provided that you call the `propel:build-forms` task:

    // propel
    $ php symfony propel:build-forms
    
    // doctrine
    $ php symfony doctrine:build-forms

For each table in the model, this command creates two files under the `lib/form/` directory: a `BaseXXXForm` class, overridden each time you call the `propel:build-forms` task, and an empty `XXXForm` class, extending the previous one. It is the same system as the Propel model classes generation.

The generated `lib/form/base/BaseArticleForm.class.php` contains the translation into widgets and validators of the columns defined for the `article` table in the `schema.yml`:

    [php]
    class BaseArticleForm extends BaseFormPropel
    {
      public function setup()
      {
        $this->setWidgets(array(
          'id'           => new sfWidgetFormInputHidden(),
          'title'        => new sfWidgetFormInputText(),
          'slug'         => new sfWidgetFormInputText(),
          'content'      => new sfWidgetFormTextarea(),
          'is_published' => new sfWidgetFormInputCheckbox(),
          'author_id'    => new sfWidgetFormPropelChoice(array('model' => 'Author', 'add_empty' => false)),
          'created_at'   => new sfWidgetFormDatetime(),
        ));
        $this->setValidators(array(
          'id'           => new sfValidatorPropelChoice(array('model' => 'Article', 'column' => 'id', 'required' => false)),
          'title'        => new sfValidatorString(array('max_length' => 255)),
          'slug'         => new sfValidatorString(array('max_length' => 255)),
          'content'      => new sfValidatorString(array('max_length' => 255, 'required' => false)),
          'is_published' => new sfValidatorBoolean(),
          'author_id'    => new sfValidatorPropelChoice(array('model' => 'Author', 'column' => 'id')),
          'created_at'   => new sfValidatorDatetime(array('required' => false)),
        ));
        $this->setPostValidator(
          new sfValidatorPropelUnique(array('model' => 'Article', 'column' => array('slug')))
        );
        $this->setNameFormat('article[%s]');
        parent::setup();
      }

      public function getModelName()
      {
        return 'Article';
      }
    }

Notice that even though the `id` column is an Integer, symfony checks that the submitted id exists in the table using a `sfValidatorPropelChoice` validator. The form generator always sets the strongest validation rules, to ensure the cleanest data in the database.

### Using Model Forms

You can customize generated form classes for your entire project by adding code to the empty `ArticleForm::configure()` method.

Here is an example of model form handling in an action. In this form, the `slug` validator is modified to make it optional, and the `author_id` widget is customized to display only a subset of authors - the 'active' ones.

    [php]
    // in lib/form/ArticleForm.class.php
    public function configure()
    {
      $this->getWidget('author_id')->setOption('criteria', $this->getOption('criteria'));
      $this->getValidator('slug')->setOption('required', false);
    }

    // in modules/foo/actions/actions.class.php
    public function executeEditArticle($request)
    {
      $c = new Criteria();
      $c->add(AuthorPeer::ACTIVE, true);
      
      $this->form = new ArticleForm(
        ArticlePeer::retrieveByPk($request->getParameter('id')),
        array('criteria' => $c)
      );
      
      if ($request->isMethod('post'))
      {
        $this->form->bind($request->getParameter('article'));
        if ($this->form->isValid())
        {
          $article = $this->form->save();

          $this->redirect('article/edit?id='.$author->getId());
        }
      }
    }

Instead of setting default values through an associative array, Model forms use a Model object to initialize the widget values. To display an empty form, just pass a new Model object.

The form submission handling is greatly simplified by the fact that the form object has an embedded Model object. Calling `$this->form->save()` on a valid form updates the embedded `Article` object with the cleaned values and triggers the `save()` method on the `Article` object, as well as on the related objects if they exist.

>**TIP**: The action code required to deal with a form is pretty much always the same, but that's not a reason to copy it from one module to the other. Symfony provides a module generator that creates the whole action and template code to manipulate a Model object through symfony forms.

Conclusion
----------

The symfony form component is an entire framework on its own. It facilitates the display of forms in the view through widgets, it facilitates validation and handling of forms in the controller through validators, and it facilitates the edition of Model objects through Model forms. Although designed with a clear MVC separation, the form sub-framework is always easy to use. Most of the time, code generation will reduce your custom form code to a few lines.

There is much more in symfony form classes than what this chapter exposes. In fact, there is an [entire book](http://www.symfony-project.org/book/forms/1_4/en/) describing all its features through usage examples. And if the form framework itself doesn't provide the widget or the validator you need, it is designed in such an extensible way that you will only need to write a single class to get exactly what you need.
