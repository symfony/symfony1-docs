カスタムウィジェットとバリデータ
==================================

*Thomas Rabaix 著*

この章では、symfony のフォームフレームワークで使えるカスタムウィジェットとカスタムバリデータの作り方を説明します。まず、`sfWidgetForm` と `sfValidator` の内部を説明し、次に、シンプルなウィジェットの作り方を紹介します。最後に、より複雑なウィジェットの作り方を紹介します。

ウィジェットとバリデータの内部
--------------------------------

### sfWidgetForm の内部

~`sfWidgetForm`~ クラスのオブジェクトは、フォーム入力が必要なデータを編集する際の見た目の実装を行います。たとえば、ある文字列の値を編集する際には、シンプルなテキストボックスを使うかもしれませんし、高度な WYSIWYG エディタを使うかもしれません。ウィジェットは、`sfWidgetForm` の 2 つの重要なプロパティ `options` と `attributes` を使うことで、設定を柔軟にすることができます。

 * `options`: ウィジェットを設定する際に使われます (例: 選択フィールド用のリストを生成するために使うデータベースクエリー)。
 * `attributes`: 要素を出力する際に追加する HTML 属性です。

さらに、`sfWidgetForm` クラスは2つの重要なメソッドを実装しています:

 * `configure()`: *任意指定*、または、*必須指定*とするオプションを定義します。このクラスにおいては、コンストラクタをオーバーライドすることはよい習慣ではありません。代わりに`configure()`メソッドをオーバーライドしましょう。
 * `render()`: ウィジェットの HTML 出力を行います。第1引数は必須で、ウィジェット名を指定します。第2 引数は任意で、その値を指定します。

>*NOTE*:
> `sfWidgetForm` は、自分の名前やその値を知りません。`sfWidgetForm` は、ウィジェットを単に表示することのみを担当します。名前や値は、データとウィジェットをリンクさせる `sfFormFieldSchema` オブジェクトによって管理されています。

### sfValidator の内部

~`sfValidatorBase`~ クラスは、すべてのバリデータの基底クラスです。~`sfValidatorBase::clean()`~ メソッドは、値が正しいか否かのチェックを行うもっとも重要なメソッドです。その際のチェックの条件は、指定されたオプションによります。

内部的には、`clean()` メソッドは次の処理を行います。

 * 値が文字列の場合、入力値をトリミングします (`trim` オプションが指定されていた場合のみ)。
 * 値が空であるかチェックします。
 * バリデータの ~`sfValidatorBase::doClean()`~ メソッドを呼びます。

`doClean()` メソッドは、メインのバリデーションのロジックを実装します。`clean()` メソッドをオーバーライドするのはよい習慣ではありません。代わりに `doClean()` メソッドをオーバーライドして、カスタムロジックを指定するようにしましょう。

バリデータは、入力値をバリデーションするためのスタンドアローンコンポーネントとしても使うことができます。たとえば、`sfValidatorEmail` バリデータは、メールアドレスが正しいかどうかをチェックします:

    [php]
    $v = new sfValidatorEmail();

    try
    {
      $v->clean($request->getParameter("email"));
    }
    catch(sfValidatorError $e)
    {
      $this->forward404();
    }

>**NOTE**
> フォームはリクエストの値と結びついているので、`sfForm` オブジェクトは、受け取った元々の汚染されている値と、バリデーションを通した後のクリーンな値の両方を持っています。元々の値は、フォームを再表示する際に使われます。また、バリデーション後のクリーンな値は、アプリケーションによって使われます (たとえば、オブジェクトを保存する際などです)。

### `options` 属性

`sfWidgetForm` と `sfValidatorBase` オブジェクトは、双方とも多様なオプションがあります。オプションは、任意指定もしくは必須指定になります。これらのオプションは、それぞれのクラスの `configure()` メソッドを通して定義されます。

 * `addOption($name, $value)` : オプション名とデフォルト値を1つのオプションとして定義します。
 * `addRequiredOption($name)` : 必須指定のオプションを定義します。

これらの2つのメソッドはとても便利で、値が正しくバリデータやウィジェットに渡されたことを保証します。

シンプルなウィジェットとバリデータの作り方
--------------------------------------------

このセクションでは、シンプルなウィジェットの作成方法を説明します。今回作成するウィジェットを「Trilean」ウィジェットと呼ぶことにしましょう。このウィジェットは `No`、`Yes`、`Null` といった3つの選択肢を持つ選択フィールドを表示します。

    [php]
    class sfWidgetFormTrilean extends sfWidgetForm
    {
      public function configure($options = array(), $attributes = array())
      {

        $this->addOption('choices', array(
          0 => 'No',
          1 => 'Yes',
          'null' => 'Null'
        ));
      }

      public function render($name, $value = null, $attributes = array(), $errors = array())
      {
        $value = $value === null ? 'null' : $value;

        $options = array();
        foreach ($this->getOption('choices') as $key => $option)
        {
          $attributes = array('value' => self::escapeOnce($key));
          if ($key == $value)
          {
            $attributes['selected'] = 'selected';
          }

          $options[] = $this->renderContentTag(
            'option',
            self::escapeOnce($option),
            $attributes
          );
        }

        return $this->renderContentTag(
          'select',
          "\n".implode("\n", $options)."\n",
          array_merge(array('name' => $name), $attributes
        ));
      }
    }

`configure()` メソッドは、`choices` オプションの値を使って HTML の OPTION タグに指定する値のセットを定義しています。`choices` に渡す配列は、再定義することができます （例: 各値のラベルを変更するなど)。ウィジェットがもつことのできるオプションの数には制限はありません。ただし、次に挙げるオプションは、予約オプションとして、ウィジェットの基底クラスで宣言されています

 * `id_format`: id のフォーマットで、デフォルトでは '%s' となります。
 * `is_hidden`: ウィジェットを hidden とするかどうかのブール値です (`sfForm::renderHiddenFields()` では、一度に hidden の属性を持つフィールドを指定する際に使っています)。
 * `needs_multipart`: フォームがファイルアップロードの際などに使うマルチパートのオプションをもつかどうかのブール値です。
 * `default`: 値を指定しなかった際にウィジェットを出力した際のデフォルト値です。
 * `label`: ウィジェットのデフォルトラベルです。

`render()` メソッドは、選択フィールドの HTML を生成します。このメソッドは HTML タグを表示するために組み込みメソッドの `renderContentTag()` を呼び出します。

これで、このシンプルなウィジェットは完成しました。次は、対応するバリデータをコーディングしましょう:

    [php]
    class sfValidatorTrilean extends sfValidatorBase
    {
      protected function configure($options = array(), $messages = array())
      {
        $this->addOption('true_values', array('true', 't', 'yes', 'y', 'on', '1'));
        $this->addOption('false_values', array('false', 'f', 'no', 'n', 'off', '0'));
        $this->addOption('null_values', array('null', null));
      }

      protected function doClean($value)
      {
        if (in_array($value, $this->getOption('true_values')))
        {
          return true;
        }

        if (in_array($value, $this->getOption('false_values')))
        {
          return false;
        }

        if (in_array($value, $this->getOption('null_values')))
        {
          return null;
        }

        throw new sfValidatorError($this, 'invalid', array('value' => $value));
      }

      public function isEmpty($value)
      {
        return false;
      }
    }

`sfValidatorTrilean` は `configure()` メソッドのなかで3つのオプションを定義しています。それぞれのオプションは、正しい値の集合です。これらはオプションとして定義しているため、開発者は仕様によってこれらの値をカスタマイズすることができます。

`doClean()` メソッドは、値が正しい範囲内にあることを調べ、クリーンな値を返すかどうかをチェックします。正しい範囲内になかった場合は、`sfValidatorError` を投げます。`sfValidatorError` は、フォームフレームワークの標準的なバリデーションエラーです。

`isEmpty()` は、親クラスのメソッドをオーバーライドしています。なぜなら、デフォルトの動作では、`null` を受け取った際に `true` を返すようになっているからです。今回のウィジェットにおいては、`null` は正しい値としますので、`false` を返すのが正しい動作になります。


>**Note**:
> `isEmpty()` が true を返した場合は、`doClean()` が呼ばれることはありません。

このウィジェットは簡単なものでしたが、いくつかの重要な基本機能を紹介しました。これらは、さらにウィジェットを使いこなすにあたって必要なものです。次のセクションでは、より複雑なウィジェットを作成します。それは、マルチフィールドで、JavaScript とインタラクションをするものです。

Google Address Map ウィジェット
------------------------------

このセクションでは、複雑なウィジェットを作成し、新しいメソッドを紹介します。また、今回のウィジェットは JavaScript とインタラクションを行います。このウィジェットは「Google Map Address Widget」の頭文字を取って 「GMAW」と呼ぶことにします。

このウィジェットでは、エンドユーザーの住所追加を簡単に行うしくみを提供することにしましょう。方法としては、入力テキストフィールドと Google Maps の API を使用した地図を使うことにします。

![「Google Map Address Widget」マッシュアップ](http://www.symfony-project.org/images/more-with-symfony/widgets-figure-01.png "「Google Map Address Widget」マッシュアップ")

ユースケース 1:

* ユーザーは住所を入力します。
* 「lookup」ボタンを押します。
* それによって、緯度と経度の hidden フィールドが修正されます。新しいマーカーが地図上に追加されます。マーカーは、住所の位置に置かれます。Google のジオコーディングの API が住所を検索できなかった場合は、エラーメッセージをポップアップします。

ユースケース 2:

* ユーザーは地図のある位置をクリックします。
* 緯度と経度がその位置に基づいて修正されます。
* 逆検索として、位置の緯度と経度から住所を探します。

*次のフィールドは入力値を受け取るので、フォームで管理する必要があります:*

* `laitude`: float、90 から -90 の間
* `longitude`: float、180 から -180 の間
* `address`: string、プレーンテキストのみ

このウィジェットの機能に関する仕様はこれで完成です。次は技術的なツールやその扱う範囲を定義していきましょう:

* Google Maps and Geocoding services （Google Maps とジオコーディング API）: 地図を表示し、住所の情報を取得します。
* jQuery: フィールド間の JavaScript のインタラクションを追加します。
* sfForm: ウィジェットを表示し、入力値をバリデートします。

### sfWidgetFormGMapAddress ウィジェット

ウィジェットはデータの見た目を担当するので、Google Maps を用いて表した地図の微調整や、それぞれの要素のスタイルを調整するためのオプションが `configure()` メソッドに必要です。ここでの重要なオプションの1つとして `template.html` があります。これは、すべての要素を表示する順番を定義しています。ウィジェット一般に言えることですが、ウィジェットを作成する際に、再利用性と拡張性を考えることはとても重要です。

次の重要なこととして、外部のアセットの定義があります。`sfWidgetForm` は次の2つのメソッドを実装することができます。

 * `getJavascripts()` はJavaScript ファイルの配列を返します。

 * `getStylesheets()` はスタイルシートファイルの配列を返します （配列のキーは、そのファイルへのパスとなります。そしてその値は、スタイルシートの media の名前になります）。

今回のウィジェットは JavaScript のみ必要で、スタイルシートは不要です。このウィジェットは、Google ジオコーディングや Google Maps のサービスを使用しますが、Google Maps API を使用する際の初期化に関しては扱いません。ページで使えるように初期化するのは、開発者の責任とします。なぜなら、Google Maps API を使ったサービスは、このウィジェット以外に、ほかの要素によって使われることもあるからです。

では、コードを見てみましょう:

    [php]
    class sfWidgetFormGMapAddress extends sfWidgetForm
    {
      public function configure($options = array(), $attributes = array())
      {
        $this->addOption('address.options', array('style' => 'width:400px'));

        $this->setOption('default', array(
          'address' => '',
          'longitude' => '2.294359',
          'latitude' => '48.858205'
        ));

        $this->addOption('div.class', 'sf-gmap-widget');
        $this->addOption('map.height', '300px');
        $this->addOption('map.width', '500px');
        $this->addOption('map.style', "");
        $this->addOption('lookup.name', "Lookup");

        $this->addOption('template.html', '
          <div id="{div.id}" class="{div.class}">
            {input.search} <input type="submit" value="{input.lookup.name}"  id="{input.lookup.id}" /> <br />
            {input.longitude}
            {input.latitude}
            <div id="{map.id}" style="width:{map.width};height:{map.height};{map.style}"></div>
          </div>
        ');

         $this->addOption('template.javascript', '
          <script type="text/javascript">
            jQuery(window).bind("load", function() {
              new sfGmapWidgetWidget({
                longitude: "{input.longitude.id}",
                latitude: "{input.latitude.id}",
                address: "{input.address.id}",
                lookup: "{input.lookup.id}",
                map: "{map.id}"
              });
            })
          </script>
        ');
      }

      public function getJavascripts()
      {
        return array(
          '/sfFormExtraPlugin/js/sf_widget_gmap_address.js'
        );
      }

      public function render($name, $value = null, $attributes = array(), $errors = array())
      {
        // 主要なテンプレートの値を定義します
        $template_vars = array(
          '{div.id}'             => $this->generateId($name),
          '{div.class}'          => $this->getOption('div.class'),
          '{map.id}'             => $this->generateId($name.'[map]'),
          '{map.style}'          => $this->getOption('map.style'),
          '{map.height}'         => $this->getOption('map.height'),
          '{map.width}'          => $this->getOption('map.width'),
          '{input.lookup.id}'    => $this->generateId($name.'[lookup]'),
          '{input.lookup.name}'  => $this->getOption('lookup.name'),
          '{input.address.id}'   => $this->generateId($name.'[address]'),
          '{input.latitude.id}'  => $this->generateId($name.'[latitude]'),
          '{input.longitude.id}' => $this->generateId($name.'[longitude]'),
        );

        // $valueのフォーマットが無効の際にはエラーを表示しないようにします
        $value = !is_array($value) ? array() : $value;
        $value['address']   = isset($value['address'])   ? $value['address'] : '';
        $value['longitude'] = isset($value['longitude']) ? $value['longitude'] : '';
        $value['latitude']  = isset($value['latitude'])  ? $value['latitude'] : '';

        // address ウィジェットを定義します
        $address = new sfWidgetFormInputText(array(), $this->getOption('address.options'));
        $template_vars['{input.search}'] = $address->render($name.'[address]', $value['address']);

        // 緯度、経度フィールドを定義します
        $hidden = new sfWidgetFormInputHidden;
        $template_vars['{input.longitude}'] = $hidden->render($name.'[longitude]', $value['longitude']);
        $template_vars['{input.latitude}']  = $hidden->render($name.'[latitude]', $value['latitude']);

        // テンプレートと変数をマージします
        return strtr(
          $this->getOption('template.html').$this->getOption('template.javascript'),
          $template_vars
        );
      }
    }

ウィジェットは `generateId()` メソッドを使って各要素の `id` 属性の値を生成します。`$name` の値は、`sfFormFieldSchema` で定義されています。つまり、`configure()` メソッドで定義したフォームの名前、入れ子になっているウィジェットスキーマ名、そして、ウィジェット名によって構成されます。

>**NOTE**
> 例として、フォーム名が `user`、入れ子のスキーマ名が `location`、ウィジェット名が `address` の場合、`name` 属性の値は `user[location][address]` のようになります。そして、`id` 属性の値は `user_location_address` となります。このように `$this->generateId($name.'[latitude]')` は緯度のフィールドに関する有効で一意性のある `id` を生成します。

要素を使う際の `id` 属性に一意性があるということはとても重要です。それは、 (`template.js` の値を通して）JavaScript ブロックに渡して、JavaScript が異なる要素間の操作をできるようにしなければならないからです。

`render()` メソッドは、2つの内部ウィジェットをインスタンス化します。それは、`address` フィールドを出力する `sfWidgetFormInputText` と、 `latitude` と `longitude` の hidden フィールドを出力する `sfWidgetFormInputHidden` です。

ウィジェットは、次のコードですぐにテストをすることができます:

    [php]
    $widget = new sfWidgetFormGMapAddress();
    echo $widget->render('user[location][address]', array(
      'address' => '151 Rue montmartre, 75002 Paris',
      'longitude' => '2.294359',
      'latitude' => '48.858205'
    ));

出力結果は、次のようになります:

    [html]
    <div id="user_location_address" class="sf-gmap-widget">
      <input style="width:400px" type="text" name="user[location][address][address]" value="151 Rue montmartre, 75002 Paris" id="user_location_address_address" />
      <input type="submit" value="Lookup"  id="user_location_address_lookup" /> <br />
      <input type="hidden" name="user[location][address][longitude]" value="2.294359" id="user_location_address_longitude" />
      <input type="hidden" name="user[location][address][latitude]" value="48.858205" id="user_location_address_latitude" />
      <div id="user_location_address_map" style="width:500px;height:300px;"></div>
    </div>

    <script type="text/javascript">
      jQuery(window).bind("load", function() {
        new sfGmapWidgetWidget({
          longitude: "user_location_address_longitude",
          latitude: "user_location_address_latitude",
          address: "user_location_address_address",
          lookup: "user_location_address_lookup",
          map: "user_location_address_map"
        });
      })
    </script>

ウィジェットの JavaScript の部分は、異なる `id` 属性を受け取り、それらの値は jQuery のイベントリスナーに結びつけます。そして、何らかのアクションが起きた際に、イベントをトリガーさせます。つまり、ジオコーディングのサービスを使って取得した緯度と経度を、それぞれに対応する hidden フィールドに反映させます。

JavaScript のコードでは、次のメソッドを作りました:

 * `init()`: すべての変数が初期化され、イベントをそれぞれの input 要素に結びつけるメソッドです。

 * `lookupCallback()`: ユーザーによって入力された住所のジオコーダーのコールバックとして使われる*静的*メソッドです。

 * `reverseLookupCallback()`: 指定の緯度、経度から住所を引くためのジオコーダーのコールバックとして使われる*静的*メソッド。

JavaScript のコードは、付録 A にあります:

Google Maps の機能の詳細に関しては、Google Maps のドキュメントを参照してください。[API](http://code.google.com/apis/maps/)

### `sfValidatorGMapAddress` バリデータ

`sfValidatorGMapAddress` クラスは、`sfValidatorBase` クラスを継承しています。`sfValidatorBase` は、すでにバリデーションが1つあります。それは、フィールドが `required` と指定されていた際には、`null` になることはできないというものです。子クラスである `sfValidatorGMapAddress` は、`latitude`、`longitude`、`address` の値のバリデーションを行います。`$value` は、配列でなければなりませんが、ユーザーからの入力値をそのまま信頼して使うべきではありません。そこで、バリデータは、`latitude`、`longitude`、`address` といったすべてのキーの存在をチェックし、さらに内部のバリデータを使い、値が正しいかどうかを調べます。

    [php]
    class sfValidatorGMapAddress extends sfValidatorBase
    {
      protected function doClean($value)
      {
        if (!is_array($value))
        {
          throw new sfValidatorError($this, 'invalid');
        }

        try
        {
          $latitude = new sfValidatorNumber(array( 'min' => -90, 'max' => 90, 'required' => true ));
          $value['latitude'] = $latitude->clean(isset($value['latitude']) ? $value['latitude'] : null);

          $longitude = new sfValidatorNumber(array( 'min' => -180, 'max' => 180, 'required' => true ));
          $value['longitude'] = $longitude->clean(isset($value['longitude']) ? $value['longitude'] : null);

          $address = new sfValidatorString(array( 'min_length' => 10, 'max_length' => 255, 'required' => true ));
          $value['address'] = $address->clean(isset($value['address']) ? $value['address'] : null);
        }
        catch(sfValidatorError $e)
        {
          throw new sfValidatorError($this, 'invalid');
        }

        return $value;
      }
    }

>**NOTE**
> バリデータは、値が正しくない場合、常に`sfValidatorError`例外を投げます。そのため、`try/catch` のブロックで囲まれています。今回のバリデータでは、新しい `invalid` 例外を補足し、さらに投げるようにしています。つまり、`sfValidatorGMapAddress` の `invalid` バリデーションエラーとしているのです。

### テスト

なぜテストが重要なのでしょう？バリデータはユーザーの入力とアプリケーションをつなげる役割を担います。もし、バリデータに欠点があれば、アプリケーションは脆弱性の危険に晒されてしまいます。幸運なことにも symfony は、`lime` テストライブラリという簡単に使えるテストライブラリとセットになっています。

バリデータをテストしましょう。ここで述べたようにバリデータは、バリデーションエラーの際に、例外を投げます。つまり、正しい値、不正な値をバリデータに渡し、例外が投げられたかどうかをチェックすることで、テストが可能になります。

    [php]
    $t = new lime_test(7, new lime_output_color());

    $tests = array(
      array(false, '', 'empty value'),
      array(false, 'string value', 'string value'),
      array(false, array(), 'empty array'),
      array(false, array('address' => 'my awesome address'), 'incomplete address'),
      array(false, array('address' => 'my awesome address', 'latitude' => 'String', 'longitude' => 23), 'invalid values'),
      array(false, array('address' => 'my awesome address', 'latitude' => 200, 'longitude' => 23), 'invalid values'),
      array(true, array('address' => 'my awesome address', 'latitude' => '2.294359', 'longitude' => '48.858205'), 'valid value')
    );

    $v = new sfValidatorGMapAddress;

    $t->diag("Testing sfValidatorGMapAddress");

    foreach($tests as $test)
    {
      list($validity, $value, $message) = $test;

      try
      {
        $v->clean($value);
        $catched = false;
      }
      catch(sfValidatorError $e)
      {
        $catched = true;
      }

      $t->ok($validity != $catched, '::clean() '.$message);
    }

`sfForm::bind()` メソッドでは、フォームはそれぞれのバリデータの `clean()` メソッドを実行します。このテストでは、`sfValidatorGMapAddress` のバリデータを直接生成し、いろいろな値のテストを行ないます。このように、`sfForm::bind()` メソッドの動作をシミュレートします。

最後に
------

ウィジェットを作成する際のよくある間違いは、データベースに格納するデータに集中しすぎることです。symfony のフォームフレームワークは、単なるデータのコンテナとバリデーションのフレームワークにしか過ぎません。つまり、ウィジェットはそれに関係するデータのみを管理すべきなのです。データのバリデーションがパスすると、クリーンな値が渡されます。モデルやコントローラは、そのクリーンな値を使うことになるのです。
