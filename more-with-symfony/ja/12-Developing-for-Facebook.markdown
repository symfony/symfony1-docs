Facebook のための開発
====================

*Fabrice Bernhard 著*

Facebook とは 3 億人のユーザーがいるインターネット上のソーシャル Web サイトとして標準的な存在になっています。Facebook の面白い機能の 1 つが "Facebook プラットフォーム" です。これは開発者が Facebook の Web サイト内にアプリケーションを作成したり他の Web サイトと Facebook の認証システムを使ってソーシャルグラフと結びつけることができる API です。

Facebook のフロントエンドは PHP で書かれているので、この API の公式クライアントライブラリが PHP ライブラリであるということは不思議なことではありません。事実上 symfony が素早くクリーンな Facebook アプリケーションや Facebook Connect サイトを開発するための論理的解決になります。しかし、これ以上に、Facebook 用の開発によって高品質基準を保ちつつ貴重な時間を稼ぐために symfony の機能をどのようにして活用するかということを教えてくれます。このことについてこの章で深く網羅します: つまり、Facebook API がどういったものでありどのように使われているかについて説明します。その後に、Facebook アプリケーションを開発するときに最高の状態で symfony をどのように使うか、コミュニティーの取り組みと `sfFacebookConnectplugin` からどのように恩恵を受けるかを説明します。そして、このプラグインを利用して簡単な "Hello you!" アプリケーションのデモンストレーションを行い、最後に最も一般的な問題を解決するための秘訣やコツを教えます。

Facebook のための開発
--------------------

API には 2 つのとても異なる使用事例があります。2 つの使用事例とは Facebook アプリケーションを Facebook 内に作成するときと Facebook Connnect を外部サイト上に実装するときです。しかし両場面とも API は基本的に同じです。

### Facebook アプリケーション

Facebook アプリケーションとは Facebook の内部にある Web アプリケーションです。このアプリケーションの主な品質は 3 億ものユーザーがいる強力なソーシャルサイト内に直接組み込むことです。したがってどんなバイラルアプリケーションも驚くべきスピードで成長します。Farmville はもっとも大きい最近の例で、毎月 6000 万人以上のアクティブユーザーが存在し数ヶ月で 200 万人のファンを獲得しています! 彼らの仮想農場で働くために毎月戻ってくるユーザー数はフランスの人口と等しいのです! Facebook アプリケーションは異なる方法で Facebook Web サイトやソーシャルグラフと相互に情報のやりとりを行っています。ここで Facebook アプリケーションを表示することができる各箇所を少しだけ見てみましょう:

#### キャンバス

キャンバスは通常アプリケーションの主要部分です。基本的に Facebook フレーム内に組み込まれている小さな Web サイトです。

#### プロフィールタブ

アプリケーションをユーザーのプロフィールやファンページ上のタブ内に配置することができます。主な制限は次の通りです:

 * 1 ページのみ。タブ内にサブページのようなリンクを定義することはできません。

 * 動的な flash や JavaScript を読み込時に利用できません。動的機能を提供するためには、アプリケーションはユーザーがリンクやボタンをクリックすることでページと情報のやりとりするのを待たなければなりません。

#### プロフィールボックス

これは古い Facebook の残り物であり、事実上誰も使っていません。プロフィールの"ボックス"タブにあるボックスに情報を表示するために使用されていました。

#### インフォメーションタブの付録

特定のユーザーやアプリケーションに結びついている静的な情報がユーザーのプロフィールのインフォメーションタブに表示されます。インフォメーションタブはユーザーの年齢、住所そしてカリキュラムの下に表示されます。

#### 通知の公開とニュースストリーム内への公開

アプリケーションでニュース、リンク、写真、ビデオをニュースストリーム、ユーザーの友人の掲示板内に公開したり、または直接ユーザーのステータスを修正することができます。

#### インフォメーションページ

これはアプリケーションの"プロフィールページ"で、Facebook によって自動的に生成されます。アプリケーションの製作者がよくある Facebook の方法でユーザーと相互に情報のやりとりを行うことができる場所です。これは一般的に開発チームよりはマーケティングチームにより直接関係があるでしょう。

### Facebook コネクト

Facebook コネクトによってどの Web サイトでも Facebook のユーザーはすばらしい Facebook の機能を利用することができるようになります。すでに "connect された" Web サイトかどうかは大きな青い "Connect with Facebook" ボタンが表示されることで認識することができます。もっとも有名なところでは digg.com、cnet.com、netvibes.com、yelp.com などがあります。ここに Facebook コネクト が既存サイトにもたらす 4 つの主な理由があります。

#### ワンクリック認証システム

OpenID のように、Facebook コネクトによって Web サイトは Facebook のセッションを使い自動ログインを提供することができるようになります。Web サイトと Facebook との間で "connection" がユーザーによって承認されると、Facebook のセッションは自動的に Web サイトに提供され、さらにもう一度ログインを行ったりパスワードを覚えたりするコストを削減します。

#### ユーザーについてもっと多くの情報の取得

もうひとつ別の Facebook コネクトの重要な機能は提供される情報量です。一般的にユーザーが新しい Web サイトに最低限の情報をアップロードする一方で、Facebook コネクトは名前、年齢、性別、所在地、プロフィール写真などの付加情報を素早く取得する機会を与えてくれます。そして Web サイトをリッチにしてくれます。Facebook コネクトの利用規約はユーザーの明白な同意無しにユーザーのどんな個人情報も保存してはならないことをはっきりと指摘しています。そして、提供される個人情報はフォームの項目を埋めるために利用可能であり、ワンクリックで確認をとるだけです。さらに、Web サイトは名前やプロフィール写真などの公的な情報をそれらを保存する必要なく信用することができます。

#### ニュースフィードを利用したバイラルコミニュケーション

ユーザーの新しいフィードと相互通信したり、友達を招待したり友人の掲示板上に公開する能力によって Web サイトはコミュニケーションをとるために Facebook の完全なバイラルポテンシャルを利用することができます。Facebook フィードに公開されている情報が友人と友人の友人に興味にあるソーシャル価値を持っている限り、ソーシャルコンポーネントがあるどんな Web サイトも本当にこの機能によって恩恵を受けることができます。

#### 既存するソーシャルグラフの活用

(友達や知人のネットワークのような) ソーシャルグラフを信頼しているサービスを提供する Web サイトにとって、サービスから恩恵を受けて相互通信するための十分なユーザー間の繋がりを持つ最初のコミュニティーを構築するコストは本当に高くつきます。ユーザーの友人リストに容易にアクセスができることで、Facebook コネクトは劇的にこのコストを減らし、"すでに登録された友人"を探す手間がかかりません。

`sfFacebookConnectPlugin` を利用した最初のプロジェクトのセットアップ
-----------------------------------------------------------------

### Facebook アプリケーションの作成

作成するために、["Developer"](http://www.facebook.com/developers) アプリケーションがインストールされている Facebook アカウントが必要です。アプリケーションを作成するために必要な情報は名前だけです。一度アカウントを作成すれば他に設定は必要ありません。

### `sfFacebookConnectPlugin` のインストールと設定

次のステップは `sfGuard` ユーザーと Facebook ユーザーを結びつけることです。ここで `sfFacebookConnectPlugin` というプラグインがあります。これは私が開発を始めて他の symfony 開発者が貢献してくれているプラグインです。このプラグインの主要な機能がこの結びつけを行うことです。プラグインをインストールすると、簡単ですが必要な設定作業があります。API キー、アプリケーションシークレット、そしてアプリケーション ID を `app.yml` ファイルにセットアップする必要があります:

    [yml]
    # default values
    all:
      facebook:
        api_key: xxx
        api_secret: xxx
        api_id: xxx
        redirect_after_connect: false
        redirect_after_connect_url: ''
        connect_signin_url: 'sfFacebookConnectAuth/signin'
        app_url: '/my-app'
        guard_adapter: ~
        js_framework: none # none, jQuery または prototype.

      sf_guard_plugin:
        profile_class: sfGuardUserProfile
        profile_field_name: user_id
        profile_facebook_uid_name: facebook_uid # [警告] このカラムは varchar 型でなければなりません! 例えば 100000398093902 は有効な uid です!
        profile_email_name: email
        profile_email_hash_name: email_hash

      facebook_connect:
        load_routing:     true
        user_permissions: []

>**TIP**
>symfony の古いバージョンでは、"load_routing" オプションを false にセットすることを覚えておいてください。なぜならこれは新しいルーティングシステムで使用されるオプションだからです。

### Facebook アプリケーションの設定

もしプロジェクトが Facebook アプリケーションであれば、他の重要な唯一のパラメーターは Facebook 上のアプリケーションの相対パスを指し示す `app_url` です。たとえば、`http://apps.facebook.com/my-app` アプリケーションでは、`app_url` パラメータの値は `/my-app` になるでしょう。

### Facebook コネクトのWebサイトの設定

もしプロジェクトが Facebook コネクトの Web サイトであれば、他の設定パラメータはほとんど標準の値のままになります:

 * `redirect_after_connect` で "Connect With Facebook" ボタンをクリックした後の振る舞いを調整することができます。標準ではプラグインは登録後に `sfGuardPlugin` の動作を複製します。

 * `js_framework` は使用する具体的な JS フレームワークを指定するために使用します。  Facebook コネクトの JavaScript は非常に重いので良いタイミングで読み込まれなければ IE6 だと fatal エラー (!) の原因になるので Facebook コネクトのサイトでは jQuery のような JS フレームワークを使うことが強く推奨されています。

 * `user_permissions` は新しい Facebook コネクトのユーザーに付与するパーミッションの配列です。

### Facebook で sfGuard の関連づけ

Facebook ユーザーと `sfGuardPlugin` システム間の関連づけは `Profile` テーブルにある `facebook_uid` カラムを論理的に利用して行われます。プラグインは `sfGuardUser` とそのプロフィール間の関連づけは `getProfile()` メソッドを使用することで行われることを想定しています。これは `sfPropelGuardPlugin` の標準の動作ですが `sfDoctrineGuardPlugin` では設定が必要です。この設定は `schema.yml` で行うことができます:

Propel では:

    [yml]
    sf_guard_user_profile:
      _attributes: { phpName: UserProfile }
      id:
      user_id:            { type: integer, foreignTable: sf_guard_user, foreignReference: id, onDelete: cascade }
      first_name:         { type: varchar, size: 30 }
      last_name:          { type: varchar, size: 30 }
      facebook_uid:       { type: varchar, size: 20 }
      email:              { type: varchar, size: 255 }
      email_hash:         { type: varchar, size: 255 }
      _uniques:
        facebook_uid_index: [facebook_uid]
        email_index:        [email]
        email_hash_index:   [email_hash]

Doctrine:

    [yml]
    sfGuardUserProfile:
      tableName:     sf_guard_user_profile
      columns:
        user_id:          { type: integer(4), notnull: true }
        first_name:       { type: string(30) }
        last_name:        { type: string(30) }
        facebook_uid:     { type: string(20) }
        email:            { type: string(255) }
        email_hash:       { type: string(255) }
      indexes:
        facebook_uid_index:
          fields: [facebook_uid]
          unique: true
        email_index:
          fields: [email]
          unique: true
        email_hash_index:
          fields: [email_hash]
          unique: true
      relations:
        sfGuardUser:
          type: one
          foreignType: one
          class: sfGuardUser
          local: user_id
          foreign: id
          onDelete: cascade
          foreignAlias: Profile

>**TIP**
>もしプロジェクトが Doctrine を使っていて `foreignAlias` が `Profile` でなければどうでしょうか。この場合においてプラグインは動作しないでしょう。しかし `Profile` テーブルを指し示す `sfGuardUser.class.php` にある簡単な `getProfile()` メソッドが問題を解決してくれるでしょう!

`facebook_uid` カラムは `varchar` にすべきであることに注意してください、なぜなら Facebook の新しいプロフィールは `10^15` を超える `uids` を持っているからです。異なる ORM で `bigint` で動作させようとするよりインデックスされた `varchar` カラムを使うことでより安全を期すようにしてください。

残りの 2 つのカラムはそれほど重要ではありません: `email` と `email_hash` は既存するユーザーを利用する Facebook コネクトの Web サイトの場合にのみ要求されます。この場合は Facebook は既存のアカウントを E メールのハッシュを用いて Facebook コネクトの新しいアカウントと関連づけようとするために複雑なプロセスを提供します。もちろん `sfFacebookConnectPlugin` (この章の最後の部分について説明しています) によって提供されるタスクのおかげでプロセスは簡単に行われます。

### FBML と XFBML 間の選択: symfony で解決される問題

全てのセットアップできてたので、実際のアプリケーションのコードを書き始めることができます。Facebook は全体の機能をレンダリングできる、例えば "invite friends" フォームや完全版のコメントシステムのような多くの特別なタグを提供します。これらのタグは FBML や XFBML タグと呼ばれます。FBML と XFBML タグは全く同じようなものですがアプリケーションが Facebook の中でレンダリングされるかどうかによって選択します。もしプロジェクトが Facebook コネクトウェブサイトであれば、たった 1 つしか選択肢がありません: それは XFBML です。もし Facebook アプリケーションであれば、2 つの選択肢があります:

 * アプリケーションを本当の IFrame として Facebook アプリケーションのページ内に取り込みこの IFrame の内部で XFBML を使う;

 * Facebook にアプリケーションを透過的にこのページの内部に取り込むときに FBML を利用する。

Facebook は開発者に "透過的な組み込み" か "FBML アプリケーション" と呼ばれる方法を推奨しています。実際、面白い機能があります:

 * IFrame ではないということ、これはリンク先が IFrame かそれとも親ウィンドウに関係しているかを覚えておく必要があるため管理するのが複雑であるということです;

 * FBML タグと呼ばれる特別なタグは Facebook サーバーによって自動的に解釈され、そして事前に Facebook サーバーと対話しなくてもユーザーに関する個人的な情報を表示することができるようになるということ;

 * ページを移動するときに手動で Facebook セッションを渡す必要が無いということ

しかし FBML には次のような深刻な欠点もあります:

 * JavaScript は sandbox の内部に組み込まれ、Google マップ、jQuery または Facebook で公式にサポートされている Google Analytics 以外のシステム統計のような外部ライブラリを使うことができないということ;

 * API の呼び出しが FBML タグによって置き換えることができるのでより速くなると主張しています。しかしながらアプリケーションが軽量であれば、自身のサーバーでホスティングするほうがもっと高速であるということ;

 * デバッグするのがより困難であるということ、特に Facebook によって捕獲される 500 エラーは標準エラーで置き換えられるということ

それでは最も推奨される選択は何でしょうか？ 吉報は、symfony と `sfFacebookConnectPlugin` を使用することで、どれを選択するか悩まなくても良いということです! これらの選択肢にとらわれないアプリケーションを書いてあっけなく IFrame から Facebook コネクト Web サイトへの組み込みアプリケーションへ同じコードで切り替えることができます。なぜなら、技術的には、実質的な主な違いはレイアウトにあるからです...これは symfony では切り替えるのは非常に簡単なことです。2 つの異なるレイアウトの例は次のようになります:

FBML アプリケーションのためのレイアウト:

    [html]
    <?php sfConfig::set('sf_web_debug', false); ?>
    <fb:title><?php echo sfContext::getInstance()->getResponse()->getTitle() ?></fb:title>
    <?php echo $sf_content ?>

XFBML アプリケーションや Facebook コネクト Web サイトのためのレイアウト:

    [html]
    <?php use_helper('sfFacebookConnect')?>
    <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
    <html xmlns="http://www.w3.org/1999/xhtml" xmlns:fb="http://www.facebook.com/2008/fbml">
      <head>
        <?php include_http_metas() ?>
        <?php include_metas() ?>
        <?php include_title() ?>
        <script type="text/javascript" src="/sfFacebookConnectPlugin/js/animation/animation.js"></script>
       <link rel="stylesheet" type="text/css" media="screen" href="/trajoin/css/main.css" />
       <link rel="stylesheet" type="text/css" media="screen" href="/trajoin/css/trajoin.css" />
     </head>
      <body>
        <?php echo $sf_content ?>
        <?php echo include_facebook_connect_script() ?>
      </body>
    </html>

自動的に両方を切り替えるために、次の内容を `actions.class.php` ファイルに追加するだけです:

    [php]
    public function preExecute()
    {
      if (sfFacebook::isInsideFacebook())
      {
        $this->setLayout('layout_fbml');
      }
      else
      {
        $this->setLayout('layout_connect');
      }
    }

>**NOTE**
>FBML と XFBML との間にはレイアウトの中には現れない 1 つの小さな違いがあります。それは FBML タグは閉じなければなりませんが、XFBML は閉じなくてもよいということです。そのため次のようなタグは:
>
>      [html]
>      <fb:profile-pic uid="12345" size="normal" width="400" />
>
>次のように差し替えるだけです:
>
>      [html]
>      <fb:profile-pic uid="12345" size="normal" width="400"></fb:profile-pic>

もちろん、アプリケーションが FBML だけで動作することを意図しているとしても、動作させるためにアプリケーションにも開発者の設定を Facebook コネクトアプリケーションのときと同じように設定する必要があります。しかし、設定を行う大きな利点はアプリケーションをローカルでテストすることができるということです。もし、Facebook アプリケーションを作成し FBML タグを使う計画であれば、ほとんど避けることができません。そして、結果を見るための唯一の方法はコードをオンラインに配置し Facebook に直接レンダリングされた結果を見て確認する方法だけです! 幸運なことに、Facebook コネクトのおかげで、XFBML タグは facebook.com の外部でレンダリングされます。そして説明したように FBML と XFBML タグとの間の違いはレイアウトだけです。したがってこの解決法によってインターネットに接続されている限りローカルに FBML タグをレンダリングすることができます。さらに、facebook.com ドメインの外部から Facebook の認証システムが動作する Facebook コネクトのおかげでインターネット上の開発環境は、80 番ポートを空けているサーバーやシンプルなコンピューターで確認することができます。このことで Facebook にアップロードする前にほとんどの機能をテストすることができるようになります。

### 簡単な Hello you アプリケーション

ホームテンプレートに次のコードを書けば、"Hello You" アプリケーションは完成です:

    [php]
    <?php $sfGuardUser = sfFacebook::getSfGuardUserByFacebookSession(); ?>
    Hello <fb:name uid="<?php echo $sfGuardUser?$sfGuardUser->getProfile()->getFacebookUid():'' ?>"></fb:name>

`sfFacebookConnectPlugin` は自動的に訪問中の Facebook ユーザーを `sfGuard` ユーザーに変換します。`sfGuardPlugin` を頼りにする symfony のコードでとても簡単に一体化することができます。

Facebook コネクト
----------------

### Facebook コネクトがどのように動作するかと異なる統合戦略

Facebook コネクトは基本的にセッションを Web サイトのセッションと共有しています。これは Web サイトに IFrame を開くことでその Web サイトに Facebook から認証 Cookie を複製することで行われます。
このようにするために、Facebook コネクトは Web サイトにアクセスする必要があり、ローカルサーバーやイントラネット上で Facebook コネクトを使ったりテストできないようになっています。エントリーポイントは `xd_receiver.htm` ファイルで、このファイルは `sfFacebookConnectPlugin` が提供しています。このファイルにアクセスできるようにするために `plugin:publish-assets` タスクを使うことを覚えておいてください。

これを一度行えば、Facebook の公式ライブラリは Facebook セッションを使うことができるようになります。さらに、`sfFacebookPlugin` は Facebook セッションに関連づけられている `sfGuard` ユーザーを作成します。そしてこの Facebook セッションは既存する symfony の Web サイトとシームレスに統合します。こういう理由で一度 Facebook コネクトボタンがクリックされ Facebook コネクトセッションが有効になった後で標準でプラグインによって `sfFacebookConnectAuth/signIn` アクションにリダイレクトされます。プラグインは最初に同じ Facebook UID か、同じ E メールハッシュを持つ既存ユーザーを探し (この章の最後にある "Facebook アカウントを持つ既存ユーザーの関連づけ" を参照)、もし見つからなければ新しいユーザーを作成します。

もうひとつ別の一般的な戦略は直接ユーザーを作成せずに最初に特定の登録フォームにリダイレクトする方法です。ここで、前もって一般的な情報でフォームを埋めるために Facebook セッションを使うことができます。たとえば、次のようなコードを登録フォームのコンフィギュレーションメソッドに追加することで可能です:

    [php]
    public function setDefaultsFromFacebookSession()
    {
      if ($fb_uid = sfFacebook::getAnyFacebookUid())
      {
        $ret = sfFacebook::getFacebookApi()->users_getInfo(
          array(
            $fb_uid
          ),
          array(
            'first_name',
            'last_name',
          )
        );

        if ($ret && count($ret)>0)
        {
          if (array_key_exists('first_name', $ret[0]))
          {
            $this->setDefault('first_name',$ret[0]['first_name']);
          }
          if (array_key_exists('last_name', $ret[0]))
          {
            $this->setDefault('last_name',$ret[0]['last_name']);
          }
        }
      }

2 番目の戦略を使うためには、Facebook コネクトの後にリダイレクトするために使用するルーティングを指定するために `app.yml` ファイルに指定するだけです:

    [yml]
    # default values
    all:
      facebook:
        redirect_after_connect: true
        redirect_after_connect_url: '@register_with_facebook'

### Facebook コネクトフィルター

もう一つ別の重要な Facebook コネクトの特徴は Facebook ユーザーは頻繁にインターネットをブラウジングするとき Facebook にログインしているということです。この点で `sfFacebookConnectRememberMeFilter` はとても役立つということがわかります。もしユーザーが Web サイトを訪れたとき既に Facebook にログインしているなら、`sfFacebookConnectRememberMeFilter` は "Remember me" フィルターが行うのと同じように自動的にウェブサイトにログインできるようにします。

    [php]
    $sfGuardUser = sfFacebook::getSfGuardUserByFacebookSession();
    if ($sfGuardUser)
    {
      $this->getContext()->getUser()->signIn($sfGuardUser, true);
    }

しかしながら深刻な欠点が 1 つあります: Facebook に接続されている限り自動的にログインされてしまい、ユーザーはウェブサイトからログアウトできなくなってしまうことです。この機能は注意して使わなければなりません。

### IE の JavaScript の致命的なバグを避けるための綺麗な実装

Web サイト上で遭遇する可能性があるもっともひどいバグの 1 つは IE の Web サイトのレンダリングをただ破壊する "Operation aborted (操作が中断されました)" というエラーです...しかもクライアント側のエラーです! このバグは `body` 要素の直接の子要素でないスクリプトから `body` 要素に DOM 要素を付け足したときにクラッシュする IE6 と IE7 のレンダリングエンジンの粗悪な品質が原因です。
不運なことに、これは Facebook コネクトの JavaScript を注意せずに直接的に `body` 要素やドキュメントの最後で読み込んだりした場合の典型的なケースです。しかし、syfmony の `slot` を利用することで簡単に解決することができます。テンプレートで Facebook コネクトスクリプトが必要であればいつでもインクルードするために `slot` を使用します、そして `</body>` タグの前に、ドキュメントの最後にレイアウト内でレンダリングされます:

    [php]
    // XFBML タグか Facebook コネクトボタンを使っているテンプレートにおいて
    slot('fb_connect');
    include_facebook_connect_script();
    end_slot();

    // IE の問題を避けるためにレイアウトの </body> タグの直前において
    if (has_slot('fb_connect'))
    {
      include_slot('fb_connect');
    }

Facebook アプリケーションのためのベストプラクティス
-------------------------------------------------

`sfFacebookConnectPlugin` のおかげで、`sfGuardPlugin` との統合はスムーズに行われアプリケーションを FBML、IFrame もしくは Facebook コネクト Web サイトのどれにするかという選択は最終段階になってから行うことができます。さらにより多くの Facebook の機能を使っている本当のアプリケーションを作成するために、これから symfony の機能を活用する重要な秘訣について説明します。

### 複数の Facebook コネクトのテストサーバーをセットアップするための symfony の環境の使い方

symfony にある哲学のとても重要な側面は速いデバッグとアプリケーションの品質検査です。
Facebook を使うことで本当にこの点が困難になります、なぜなら Facebook サーバーと対話する多くの機能はインターネット接続が、さらに認証 Cookie を変更するためには 80 番ポートを空ける必要があるからです。さらに、もうひとつ別の制限があります: それは Facebook コネクトアプリケーションは 1 つのホストにだけ接続することができるということです。アプリケーションを 1 つの機械上で開発し、別の機械上でテストし、3 台目のサーバー上にある試作上に配置し、最終的には 4 台目のサーバーで運用運用するような場合には本当に問題になります。このような場合でもっとも簡単な解決法は実際に各サーバーのためのアプリケーションを作成し symfony の環境を作成することです。これは symfony ではとても簡単なことです: `frontend_dev.php` ファイルの内容をコピーし、`frontend_preprod.php` ファイルにコピーしたものを貼付けて次のように `dev` 環境を新しい `preprod` 環境に変更するだけです:

    [php]
    $configuration = ProjectConfiguration::getApplicationConfiguration('frontend', 'preprod', true);

次に、`app.yml` ファイルを各環境に対応している異なる Facebook アプリケーションを設定するために編集します:

    [yml]
    prod:
      facebook:
        api_key: xxx
        api_secret: xxx
        api_id: xxx

    dev:
      facebook:
        api_key: xxx
        api_secret: xxx
        api_id: xxx

    preprod:
      facebook:
        api_key: xxx
        api_secret: xxx
        api_id: xxx

これでアプリケーションは対応している `frontend_xxx.php` エントリーポイントを使っている異なるサーバー上でテストできるようになります。

### FBML をデバッグするための symfony のログシステムの使用方法

レイアウトを切り替えることによる解決方法によって Facebook Web サイトの外部にあるほとんどの FBML アプリケーションの開発とテストを行うことができます。しかしながら、そうとはいえ Facebook の内部での最終的なテストにおいて時々不明瞭なエラーメッセージが表示されることがあります。
実際、FBML を Facebook で直接レンダリングすることの大きな問題点は 500 エラーが捕まえられるときに役に立たない標準的なエラーメッセージに置き換えられるという事実です。その上に、symfony 開発者が常習的に素早く情報を得るために利用している Web デバッグツールバーは Facebook のフレームにレンダリングされません。幸運にも symfony には私たちを助けてくれるとても良いロギングシステムがあります。`sfFacebookConnectPlugin` は自動的に多くの重要なアクションのログをとり、また、アプリケーションのどこでもログファイルに追加することは簡単です:

    [php]
    if (sfConfig::get('sf_logging_enabled'))
    {
      sfContext::getInstance()->getLogger()->info($message);
    }

### 間違った Facebook リダイレクトを避けるためのプロキシの利用方法

Facebook コネクトがアプリケーションで設定されると Facebook コネクトサーバーがアプリケーションのホームであると見なされる奇妙なバグがあります。ホームを設定できますが、Facebook コネクトホストのドメインの中にいなければなりません。そのため必要があるときはいつでもホームを引き渡し symfony のアクションでリダイレクトされるように設定するより他に解決法がありません。
次のようなコードは Facebook アプリケーションにリダイレクトします:

    [php]
    public function executeRedirect(sfWebRequest $request)
    {

      return $this->redirect('http://apps.facebook.com'.sfConfig::get('app_facebook_app_url'));
    }

### Facebook アプリケーションでの `fb_url_for()` ヘルパーの利用方法

アプリケーションが Facebook において FBML を使うか IFrame において XFBML を使うか最終段階までとらわれないようにするために、重要な問題となるのはルーティングです:

 * FBML アプリケーションにとって、アプリケーション内部のリンクは `/app-name/symfony-route` を指し示す必要があるということ;

 * IFrame アプリケーションにとって、ページからページへ遷移するとき Facebook セッション情報を渡すことが重要であること

`sfFacebookConnectPlugin` はこの両方で動作する `fb_url_for()` ヘルパーという特別なヘルパーを提供します。

### FBML アプリケーション内部でのリダイレクト

symfony の開発者は投稿処理が成功したあとにすぐにリダイレクトすることに慣れていますが、これは 2 重投稿を避けるための Web 開発におけるグッドプラクティスです。しかしながら FBML アプリケーションでリダイレクトは予期したとおりに動作しないので、代わりに、特別な FBML タグである `<fb:redirect>` で Facebook にリダイレクトすることを伝える必要があります。文脈にもよりますが (FBML タグもしくは一般的な symfony のリダイレクトに) とらわれないようにするために、`sfFacebook` クラスに存在する特別なリダイレクト関数であり、たとえば、アクションを保存するフォームで利用できます:

    [php]
    if ($form->isValid())
    {
      $form->save();

      return sfFacebook::redirect($url);
    }

### Facebook アカウントを持つ既存ユーザーの関連づけ

Facebook コネクトの目標の 1 つは新規ユーザーの登録プロセスを簡単にすることです。しかしながら、もう一つ別の興味深い利用方法は Facebook アカウントを持つ既存ユーザーを結びつけたり、彼らのプロフィール写真や友人のリストの情報を取得したり、彼らのフィードでコミュニケーションするかということです。これは 2 通りで達成することができます:

 * 既存の sfGuard ユーザー に "Connect with Facebook" ボタンをクリックさせる。もしログイン済みのユーザーであると感知し、ただ新しい Facebook コネクトのユーザーを現在の sfGuard ユーザーに保存するだけであれば、`sfFacebookConnectAuth/signIn` アクションは新しい sfGuard ユーザーを作成しません。簡単なことです。

 * Facebook の E メール認証システムの利用。ユーザーが Facebook コネクトを Web サイトで利用するとき、Facebook は彼の E メールの特別なハッシュを提供することができます。このハッシュは以前に作成したユーザーに所属しているアカウントを認識するために既存のデータベースにある E メールのハッシュと比較できるものです。しかしながら、ほとんどはセキュリティー上の理由から、Facebook は前もって API を使って登録されている場合にのみ E メールのハッシュを提供します! したがって後に認識できるようにするために全ての新しいユーザーの E メールは手続きに従って登録することが重要になります。これが registerUsers タスクが行うことで、Damien Alexandre によって 1.2 に移植されました。このタスクは最低でも毎晩、または新しいユーザーが作成された後に、`sfFacebookConnect` の `registerUsers` メソッドを使い、新しく作成されたユーザーを登録するために走らせなければなりません:

      [php]
      sfFacebookConnect::registerUsers(array($sfGuardUser));

さらに詳しく
-----------

この章が何とか目的に適ったことを願っています: つまりあなたが symfony を使った Facebook アプリケーションの開発を始めるのに役立ちどのように Facebook アプリケーション開発で symfony を至るところで活用するかを説明しています。しかしながら `sfFacebookConnectPlugin` は Facebook API を置き換えないので、Facebook アプリケーションの開発プラットフォームの完全な力を使うことを学ぶためには、[Web サイト](http://developers.facebook.com/)を訪れなければなりません。

最後に、私は symfony コミュニティーの人柄と寛容さに感謝しています、とくに既に `sfFacebookConnectPlugin` にコメントやパッチで貢献してくれている方々: Damien Alexandre、Thomas Parisot、Maxime Picaud、Alban Creton や名前を忘れてしまっているであろうその他大勢の方々に感謝しています。そしてもちろん、プラグインに欠けていることがあれば、恥ずかしがらずにあなた自身が貢献してください!
