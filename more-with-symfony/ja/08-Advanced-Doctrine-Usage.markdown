Doctrine の高度な使用方法
========================

*Jonathan H. Wage 著*

Doctrine のビヘイビアを作る
-------------------------

この節では Doctrine 1.2 を用いてビヘイビアを作成する例をご紹介します。
これから作るものは、リレーションシップの件数を簡単に保持しておくもので、毎回クエリを発行しなくても件数を取得できるようにするものです。

この機能はとてもシンプルです。リレーションシップの件数を保持したいと思うものすべてに対し、件数を保持するためのカラムを追加します。

### スキーマ

これから使うスキーマです。後ほどビヘイビアのために `actAs` という定義を追加していきます。

    [yml]
    # config/doctrine/schema.yml
    Thread:
      columns:
        title:
          type: string(255)
          notnull: true

    Post:
      columns:
        thread_id:
          type: integer
          notnull: true
        body:
          type: clob
          notnull: true
      relations:
        Thread:
          onDelete: CASCADE
          foreignAlias: Posts

スキーマをビルドするには以下のコマンドを実行します:

    $ php symfony doctrine:build --all

### テンプレート

まずは `Doctrine_Template` の子クラスを作り、件数を保持するカラムをモデルに追加する記述をします。

`lib/` ディレクトリ以下に配置すると symfony のオートロードの対象になりますので、そこに置きましょう:

    [php]
    // lib/count_cache/CountCache.class.php
    class CountCache extends Doctrine_Template
    {
      public function setTableDefinition()
      {
      }

      public function setUp()
      {
      }
    }

では `Post` モデルに `actAs` を定義して `CountCache` ビヘイビアを追加します:

    [yml]
    # config/doctrine/schema.yml
    Post:
      actAs:
        CountCache: ~
      # ...

これで `Post` モデルで `CountCache` ビヘイビアが使えるようになりましたので、ビヘイビアについて少し説明します。

モデルがインスタンス化されたとき、付属するビヘイビアの `setTableDefinition()` と `setUp()` メソッドが呼ばれ、マッピング情報が定義されます。これは `lib/model/doctrine/base/BasePost.class.php` にある `BasePost` クラスなどと同じ挙動です。
これによりプラグアンドプレイ形式でカラム、リレーションシップ、イベントリスナーなどの追加が可能です。

なんとなく仕組みがわかったかと思うので、`CountCache` ビヘイビアに処理を記述していきます:

    [php]
    class CountCache extends Doctrine_Template
    {
      protected $_options = array(
        'relations' => array()
      );

      public function setTableDefinition()
      {
        foreach ($this->_options['relations'] as $relation => $options)
        {
          // Build column name if one is not given
          if (!isset($options['columnName']))
          {
            $this->_options['relations'][$relation]['columnName'] = 'num_'.Doctrine_Inflector::tableize($relation);
          }

          // Add the column to the related model
          $columnName = $this->_options['relations'][$relation]['columnName'];
          $relatedTable = $this->_table->getRelation($relation)->getTable();
          $this->_options['relations'][$relation]['className'] = $relatedTable->getOption('name');
          $relatedTable->setColumn($columnName, 'integer', null, array('default' => 0));
        }
      }
    }

このコードはリレーションシップの件数を保持するカラムをモデルに追加するものです。
今回は `Post` モデルの `Thread` へのリレーションシップに対してビヘイビアを追加します。各 `Thread` の `num_posts` カラムにポストされた件数を保持させましょう。YAML スキーマにビヘイビアのためのオプションを定義します:

    [yml]
    # ...

    Post:
      actAs:
        CountCache:
          relations:
            Thread:
              columnName: num_posts
              foreignAlias: Posts
      # ...

これで `Thread` モデルに現在のポスト件数を保持するための `num_posts` カラムができました。

### イベントリスナー

次のステップは、レコードが新規作成されたときや削除されたときに常に最新の件数が正しく保持されるよう、イベントリスナーの記述を行います。

    [php]
    class CountCache extends Doctrine_Template
    {
      // ...

      public function setTableDefinition()
      {
        // ...

        $this->addListener(new CountCacheListener($this->_options));
      }
    }

何はともあれ、`Doctrine_Record_Listener` を継承した `CountCacheListener` クラスを定義しましょう。このクラスは Template クラスから配列形式のオプションを受け取ります。

    [php]
    // lib/model/count_cache/CountCacheListener.class.php

    class CountCacheListener extends Doctrine_Record_Listener
    {
      protected $_options;

      public function __construct(array $options)
      {
        $this->_options = $options;
      }
    }

最新の件数を保持するためには以下のイベントを使う必要があります。

 * **postInsert()**: レコードが新規作成されたときに件数を増やします。

 * **postDelete()**: レコードが削除されたときに件数を減らします。

 * **preDqlDelete()**: DQL 経由で削除が実行されたときに件数を減らします。

まずは `postInsert()` メソッドの定義を行います。

    [php]
    class CountCacheListener extends Doctrine_Record_Listener
    {
      // ...

      public function postInsert(Doctrine_Event $event)
      {
        $invoker = $event->getInvoker();
        foreach ($this->_options['relations'] as $relation => $options)
        {
          $table = Doctrine::getTable($options['className']);
          $relation = $table->getRelation($options['foreignAlias']);

          $table
            ->createQuery()
            ->update()
            ->set($options['columnName'], $options['columnName'].' + 1')
            ->where($relation['local'].' = ?', $invoker->$relation['foreign'])
            ->execute();
        }
      }
    }

上記のコードは、設定したリレーションシップに該当するレコードが下記のようにして新規作成されたときに、DQL の UPDATE 文を用いて件数を増やすものです:

    [php]
    $post = new Post();
    $post->thread_id = 1;
    $post->body = 'body of the post';
    $post->save();

この場合は `id` が `1` の `Thread` の `num_posts` カラムが `1` 増加します。

これでレコードが作成されたときに件数が増えるようになりました。
次にレコードが削除されたときに件数を減らすために `postDelete()` メソッドを実装します:

    [php]
    class CountCacheListener extends Doctrine_Record_Listener
    {
      // ...

      public function postDelete(Doctrine_Event $event)
      {
        $invoker = $event->getInvoker();
        foreach ($this->_options['relations'] as $relation => $options)
        {
          $table = Doctrine::getTable($options['className']);
          $relation = $table->getRelation($options['foreignAlias']);

          $table
            ->createQuery()
            ->update()
            ->set($options['columnName'], $options['columnName'].' - 1')
            ->where($relation['local'].' = ?', $invoker->$relation['foreign'])
            ->execute();
        }
      }
    }

この `postDelete()` メソッドは `num_posts` の値を `1` 減らす以外、先ほどの `postInsert()` メソッドとほぼ同じものです。
下記は先ほど作成した `$post` レコードを削除するコードで、このような場合に上記のコードが実行されます:

    [php]
    $post->delete();

パズルの最後のピースとなるのは DQL を用いてレコードを削除した場合の処理です。
この場合は `preDqlDelete()` メソッドで対処できます:

    [php]
    class CountCacheListener extends Doctrine_Record_Listener
    {
      // ...

      public function preDqlDelete(Doctrine_Event $event)
      {
        foreach ($this->_options['relations'] as $relation => $options)
        {
          $table = Doctrine::getTable($options['className']);
          $relation = $table->getRelation($options['foreignAlias']);

          $q = clone $event->getQuery();
          $q->select($relation['foreign']);
          $ids = $q->execute(array(), Doctrine::HYDRATE_NONE);

          foreach ($ids as $id)
          {
            $id = $id[0];

            $table
              ->createQuery()
              ->update()
              ->set($options['columnName'], $options['columnName'].' - 1')
              ->where($relation['local'].' = ?', $id)
              ->execute();
          }
        }
      }
    }

このコードは発行された `DQL の DELETE 文`を複製して `SELECT文` に変換し、削除しようとしているレコードの `ID` を取得してきて、対応するレコードのリレーションシップ件数を減らしています。

下記のような場合にリレーションシップ件数が自動的に減るようになります:

    [php]
    Doctrine::getTable('Post')
      ->createQuery()
      ->delete()
      ->where('id = ?', 1)
      ->execute();

また、下記のように複数のレコードを削除することもできますが、その場合でもリレーションシップ件数は減ります:

    [php]
    Doctrine::getTable('Post')
      ->createQuery()
      ->delete()
      ->where('body LIKE ?', '%cool%')
      ->execute();

>**NOTE**
>`preDqlDelete()` などの DQL 発行時に実行されるメソッドは、パフォーマンスを考慮してデフォルトで無効に設定されています。これらのメソッドを有効にするためには以下のように設定の変更を行う必要があります。
>
>     [php]
>     $manager->setAttribute(Doctrine_Core::ATTR_USE_DQL_CALLBACKS, true);

これでビヘイビアの実装が完了しましたので、最後に少しテストをしましょう。

### テスト

先ほど実装したコードのテストのためにフィクスチャーを作成しましょう:

    [yml]
    # data/fixtures/data.yml

    Thread:
      thread1:
        title: Test Thread
        Posts:
          post1:
            body: This is the body of my test thread
          post2:
            body: This is really cool
          post3:
            body: Ya it is pretty cool

ではモデルとデータを再構築して、フィクスチャーを読み込ませましょう:

    $ php symfony doctrine:build --all --and-load

これで再構築とフィクスチャーの読み込みが完了しました。まずはリレーションシップの件数が正しく保持されているか見てみましょう:

    $ php symfony doctrine:dql "FROM Thread t, t.Posts p"
    doctrine - executing: "FROM Thread t, t.Posts p" ()
    doctrine -   id: '1'
    doctrine -   title: 'Test Thread'
    doctrine -   num_posts: '3'
    doctrine -   Posts:
    doctrine -     -
    doctrine -       id: '1'
    doctrine -       thread_id: '1'
    doctrine -       body: 'This is the body of my test thread'
    doctrine -     -
    doctrine -       id: '2'
    doctrine -       thread_id: '1'
    doctrine -       body: 'This is really cool'
    doctrine -     -
    doctrine -       id: '3'
    doctrine -       thread_id: '1'
    doctrine -       body: 'Ya it is pretty cool'

`Thread` モデルの `num_posts` が `3` になっているのが確認できると思います。
次にポストを 1 件削除して件数が減っているかテストしましょう:

    [php]
    $post = Doctrine_Core::getTable('Post')->find(1);
    $post->delete();

レコードが削除され、件数が更新されているのが確認できると思います:

    $ php symfony doctrine:dql "FROM Thread t, t.Posts p"
    doctrine - executing: "FROM Thread t, t.Posts p" ()
    doctrine -   id: '1'
    doctrine -   title: 'Test Thread'
    doctrine -   num_posts: '2'
    doctrine -   Posts:
    doctrine -     -
    doctrine -       id: '2'
    doctrine -       thread_id: '1'
    doctrine -       body: 'This is really cool'
    doctrine -     -
    doctrine -       id: '3'
    doctrine -       thread_id: '1'
    doctrine -       body: 'Ya it is pretty cool'

DQL の DELETE 文で残りの 2 レコードをまとめて削除しても動作するかテストしましょう:

    [php]
    Doctrine_Core::getTable('Post')
      ->createQuery()
      ->delete()
      ->where('body LIKE ?', '%cool%')
      ->execute();

関連するすべてのポストを削除したので、`num_posts` は 0 になっていなければなりません:

    $ php symfony doctrine:dql "FROM Thread t, t.Posts p"
    doctrine - executing: "FROM Thread t, t.Posts p" ()
    doctrine -   id: '1'
    doctrine -   title: 'Test Thread'
    doctrine -   num_posts: '0'
    doctrine -   Posts: {  }

バッチリです！ここで学んだビヘイビアについてのことや、ビヘイビアそのものがお役に立てることを願っています！


結果キャッシュを使う
--------------------

トラフィックの多い Web アプリケーションではキャッシュを使ってリソースを節約する必要があります。最新の Doctrine 1.2 では結果セットキャッシングに対する数々の改良がされており、キャッシュドライバーからキャッシュの削除が可能になりました。これまでは削除の際にキャッシュのキーを特定することができず、削除することができませんでした。

この節では全ユーザーを取得するクエリの結果セットをキャッシュする方法と、データに変更が行われた際にイベントを用いてキャッシュをクリアする簡単な例をご紹介します。

### スキーマ

今回は以下のスキーマを使います:

    [yml]
    # config/doctrine/schema.yml
    User:
      columns:
        username:
          type: string(255)
          notnull: true
          unique: true
        password:
          type: string(255)
          notnull: true

スキーマをビルドするには以下のコマンドを実行します:

    $ php symfony doctrine:build --all

これで下記のような `User` クラスが作成されます:

    [php]
    // lib/model/doctrine/User.class.php
    /**
     * User
     *
     * This class has been auto-generated by the Doctrine ORM Framework
     *
     * @package    ##PACKAGE##
     * @subpackage ##SUBPACKAGE##
     * @author     ##NAME## <##EMAIL##>
     * @version    SVN: $Id: Builder.php 6508 2009-10-14 06:28:49Z jwage $
     */
    class User extends BaseUser
    {
    }

のちほどこのクラスにコードを追加しますので覚えておいてください。

### 結果キャッシュの設定

結果キャッシュを使うためにはまずキャッシュドライバーに問い合わせるための設定が必要です。これは `ATTR_RESULT_CACHE` 属性の設定で行えます。
今回は運用運用時にもっとも適しているであろう APC キャッシュドライバーを使います。もし APC が使えない場合は `Doctrine_Cache_Db` や `Doctrine_Cache_Array` がありますので試してみてください。

属性の設定は `ProjectConfiguration` で行います。`configureDoctrine()` を定義しましょう:

    [php]
    // config/ProjectConfiguration.class.php

    // ...
    class ProjectConfiguration extends sfProjectConfiguration
    {
      // ...

      public function configureDoctrine(Doctrine_Manager $manager)
      {
        $manager->setAttribute(Doctrine_Core::ATTR_RESULT_CACHE, new Doctrine_Cache_Apc());
      }
    }

キャッシュドライバーの設定ができたので、実際にクエリの結果セットをキャッシュしていきましょう。

### サンプルクエリー

多数のユーザーがクエリーに関連してくるアプリケーションがあったとして、ユーザーの情報に変更があった場合は毎回キャッシュの情報をクリアしたいとしましょう。

ここにアルファベット順にソートされたユーザの一覧を表示するためのものであろうクエリーがあります:

    [php]
    $q = Doctrine_Core::getTable('User')
        ->createQuery('u')
        ->orderBy('u.username ASC');

このクエリをキャッシュするには `useResultCache()` メソッドを使います:

    [php]
    $q->useResultCache(true, 3600, 'users_index');

>**NOTE**
>第 3 引数はキャッシュドライバーにキャッシュを保持させるためのキーになります。これによりキャッシュドライバーへの問い合わせや削除を簡単に行えるようになります。

クエリを実行してデータベースに問い合わせを行い、その結果を `users_index` というキーでキャッシュドライバーに保持させ、それ以降の結果はデータベースの代わりにキャッシュドライバーが返します:

    [php]
    $users = $q->execute();

>**NOTE**
>キャッシュにより Doctrine は既にハイドレーションされた情報を保持しているので、ハイドレーション処理は飛ばされます。これによりデータベースサーバーはもちろん、Web サーバーへの負荷も抑えられます。

キャッシュドライバーにキャッシュ情報があるかどうかは、`users_index` キーを用いてチェックできます:

    [php]
    if ($cacheDriver->contains('users_index'))
    {
      echo 'cache exists';
    }
    else
    {
      echo 'cache does not exist';
    }

### キャッシュの削除

クエリーはキャッシュされたので、削除について学ぶ必要があります。削除をするにはキャッシュドライバーの API を手動でたたくほか、ユーザー情報が保存・更新された際にイベントを用いて自動的にキャッシュを削除するやり方があります。

### キャッシュドライバー API

まずはキャッシュドライバーの API を使う例をご説明します。

>**TIP**
>結果キャッシュドライバーのインスタンスは `Doctrine_Manager` クラスのインスタンスから取得できます。
>
>     [php]
>     $cacheDriver = $manager->getAttribute(Doctrine_Core::ATTR_RESULT_CACHE);
>
>もし `$manager` にインスタンスが入っていない場合は以下のようにして取得できます。
>
>     [php]
>     $manager = Doctrine_Manager::getInstance();

では API を使って先ほどのキャッシュを削除してみましょう:

    [php]
    $cacheDriver->delete('users_index');

他に `users_` ではじまるキャッシュがある場合にそれらもまとめて削除したいと思いますが、これは `delete()` メソッド単独ではまとめて行えません。まとめて削除したい場合は `deleteByPrefix()` メソッドに接頭辞を渡すことで対応できます:

    [php]
    $cacheDriver->deleteByPrefix('users_');

他にもキャッシュを削除するための便利なメソッドが用意されています:

 * `deleteBySuffix($suffix)`: 接尾辞を渡してキャッシュを削除します。

 * `deleteByRegex($regex)`: 正規表現にマッチするキャッシュを削除します。

 * `deleteAll()`: すべてのキャッシュを削除します。

### イベントを用いて削除

ユーザー情報に変更があった場合には自動的にキャッシュがクリアされるのが望ましいでしょう。`User` モデルに `postSave()` イベントメソッドを実装して対応しましょう。

以前に `User` クラスについて話したのを思い出しましたか？ではクラスをお好きなエディターで開いて、以下のように `postSave()` メソッドを追加しましょう:

    [php]
    // lib/model/doctrine/User.class.php

    class User extends BaseUser
    {
      // ...

      public function postSave($event)
      {
        $cacheDriver = $this->getTable()->getAttribute(Doctrine_Core::ATTR_RESULT_CACHE);
        $cacheDriver->deleteByPrefix('users_');
      }
    }

これでユーザーの更新や新規作成がおこなわれたときにユーザー情報に関連するクエリのキャッシュが削除されるようになりました。

    [php]
    $user = new User();
    $user->username = 'jwage';
    $user->password = 'changeme';
    $user->save();

次回クエリが発行されたときにはキャッシュは存在せず、新しい情報をデータベースから取得して再度キャッシュを行い、また以降そのキャッシュを使うようになります。

Doctrine のハイドレーターを作る
------------------------------

Doctrine の鍵となる機能のひとつに `Doctrine_Query` オブジェクトを様々な結果セット構造に変換する機能があります。これを行うのが Doctrine のハイドレーターですが、Doctrine 1.2 まではハイドレーターはハードコーディングされていて、自前のハイドレーターは作れませんでした。現在はそれが可能になっており、`Doctrine_Query` でデータベースから取得してきた情報を好きな構造にすることができます。

ここではとてもシンプルでわかりやすく、とっても使えるハイドレーターを作っていきます。今回作るものは 2 つのカラムを取得してきて、1 つめのカラムを配列のキーに、2 つめのカラムを配列の値にして返すハイドレーターを作りましょう。

### スキーマとフィクスチャー

まずはスキーマを作成します。今回はシンプルな `User` モデルを使います:

    [yml]
    # config/doctrine/schema.yml
    User:
      columns:
        username: string(255)
        is_active: string(255)

また、テストのためにフィクスチャーを用意したので、以下からコピーしましょう:

    [yml]
    # data/fixtures/data.yml
    User:
      user1:
        username: jwage
        password: changeme
        is_active: 1
      user2:
        username: jonwage
        password: changeme
        is_active: 0

これをビルドするには以下のコマンドを実行します:

    $ php symfony doctrine:build --all --and-load

### ハイドレーターを作る

ハイドレーターは `Doctrine_Hydrator_Abstract` を継承したクラスに `hydrateResultSet($stmt)` メソッドを実装するだけで作れます。このメソッドはクエリーオブジェクトによって作られた `PDOStatement` インスタンスを受け取ります。このステートメントに対してクエリーを発行し、返ってきた生の値を好きな構造に変換できます。

このステートメントからクエリーを発行して生の値を取得し、それを好きな構造に変換した後に

では `KeyValuePairHydrator` というクラスを作って、symfony のオートロードの対象となる `lib/` ディレクトリ以下に配置しましょう:

    [php]
    // lib/KeyValuePairHydrator.class.php
    class KeyValuePairHydrator extends Doctrine_Hydrator_Abstract
    {
      public function hydrateResultSet($stmt)
      {
        return $stmt->fetchAll(Doctrine_Core::FETCH_NUM);
      }
    }

上記のコードは PDO から取得した情報をそのまま返すもので、欲しい結果とは全く異なります。欲しいのは キー => 値 形式に変換された情報です。ではそれにあわせて `hydrateResultSet()` メソッドを変更していきましょう:

    [php]
    // lib/KeyValuePairHydrator.class.php
    class KeyValuePairHydrator extends Doctrine_Hydrator_Abstract
    {
      public function hydrateResultSet($stmt)
      {
        $results = $stmt->fetchAll(Doctrine_Core::FETCH_NUM);
        $array = array();
        foreach ($results as $result)
        {
          $array[$result[0]] = $result[1];
        }

        return $array;
      }
    }

とても簡単ですね！このハイドレーターのコードはこれで完成なので、テストをしていきましょう！

### ハイドレーターを使う

このハイドレーターを使ってクエリーを発行しテストをしていくためには、まず Doctrine にハイドレーターを登録する必要があります。

登録は `ProjectConfiguration` の内部で `Doctrine_Manager` のインスタンスに対して行います:

    [php]
    // config/ProjectConfiguration.class.php

    // ...
    class ProjectConfiguration extends sfProjectConfiguration
    {
      // ...

      public function configureDoctrine(Doctrine_Manager $manager)
      {
        $manager->registerHydrator('key_value_pair', 'KeyValuePairHydrator');
      }
    }

これでハイドレーターが登録できたので、`Doctrine_Query` インスタンスに対してこれを使うようにしてみましょう:

    [php]
    $q = Doctrine_Core::getTable('User')
      ->createQuery('u')
      ->select('u.username, u.is_active');

    $results = $q->execute(array(), 'key_value_pair');
    print_r($results);

先ほど定義したフィクスチャーを使って上記のクエリーを実行した場合の結果は以下のようになります:

    Array
    (
        [jwage] => 1
        [jonwage] => 0
    )

できました！とってもシンプルでしょう？この内容があなたのお役にたてることを願っていますし、このおかげでコミュニティーにも新たにいくつかのすばらしいハイドレーターが寄付されました。
