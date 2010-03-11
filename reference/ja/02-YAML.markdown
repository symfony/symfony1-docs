YAML フォーマット
================

symfony フレームワークの設定ファイルの大半は YAML フォーマットです。[YAML](http://yaml.org/) の公式サイトによれば、YAML は「人が読みやすいように最適化されたすべてのプログラミング言語のための標準のデータシリアライゼーション」です。

YAML はデータを記述するためのシンプルな言語です。PHP のように、文字列、ブール値、浮動小数点数、整数のようなシンプルなデータ型のための構文をもちます。しかし PHP とは異なり、配列 (シーケンス) とハッシュ (マッピング) のあいだに違いがあります。

YAML フォーマットは複雑な入れ子のデータ構造を記述することもできますが、この章では YAML を symfony の設定ファイルとして使うために必要最小限の内容のみを説明します。


スカラー
--------

スカラーの構文は PHP の構文と似ています。

### 文字列

    [yml]
    A string in YAML

-

    [yml]
    'A singled-quoted string in YAML'

>**TIP**
>シングルクォートで囲まれる文字列のなかでは、シングルクォート: `'` は2つ重ねなければなりません:
>
>     [yml]
>     'A single quote '' in a single-quoted string'

    [yml]
    "A double-quoted string in YAML\n"

文字列が1つ以上の適切なスペースで始まるもしくは終わるときにクォートスタイル (クォートで囲む方法) は便利です。

>**TIP**
>ダブルクォートスタイルはエスケープシーケンス (`\`) を使って任意の文字列を表現する方法も提供します。`\n` もしくは Unicode を文字列に埋め込むことが必要なときにとても役立ちます。

文字列に改行を入れるとき、パイプ (`|`) によって示されるリテラルスタイルを使うことができます。これは複数行にわたる文字列を示します。リテラルでは、改行は保たれます:

    [yml]
    |
      \/ /| |\/| |
      / / | |  | |__

代わりの方法として、文字列を大なり記号 (`>`) で示される折りたたみスタイルで書くことができます。それぞれの改行はスペースに置き換わります:

    [yml]
    >
      This is a very long sentence
      that spans several lines in the YAML
      but which will be rendered as a string
      without carriage returns.

>**NOTE**
>上記の例ではそれぞれの行頭の2つのスペースに注目してください。これらは出力結果の PHP 文字列には現れません。

### 数字

    [yml]
    # 整数
    12

-

    [yml]
    # 8進数
    014

-

    [yml]
    # 16進数
    0xC

-

    [yml]
    # 浮動小数点数
    13.4

-

    [yml]
    # 指数
    1.2e+34

-

    [yml]
    # 無限大
    .inf

### null

YAML のヌル値は `null` もしくはチルダ (`~`) で表現されます。

### ブール値

YAML のブール値は `true` と `false` で表現されます。

### 日付

YAML は日付の表現に ISO-8601 標準を使います:

    [yml]
    2001-12-14t21:59:43.10-05:00

-

    [yml]
    # シンプルな日付
    2002-12-14

コレクション
------------

シンプルなスカラーを記述するために YAML ファイルが使われることはめったにありません。たいていの場合、コレクションを記述することになります。コレクションは要素のシーケンスもしくはマッピングになります。シーケンスとマッピングは両方とも PHP 配列に変換されます。

シーケンスはダッシュ (`-`) の直後にスペースを使います:

    [yml]
    - PHP
    - Perl
    - Python

上記の YAML ファイルは次の PHP コードと同等です:

    [php]
    array('PHP', 'Perl', 'Python');

それぞれのキーと値のペアを記すためにマッピングはコロン (`:`) とスペースを使います:

    [yml]
    PHP: 5.2
    MySQL: 5.1
    Apache: 2.2.20

上記のコードは次の PHP コードと同等です:

    [php]
    array('PHP' => 5.2, 'MySQL' => 5.1, 'Apache' => '2.2.20');

>**NOTE**
>マッピングにおいて、キーは有効な YAML スカラーになります。

少なくともスペースが1つ入っていれば、コロンと値のあいだのスペースの数は問題ありません:

    [yml]
    PHP:    5.2
    MySQL:  5.1
    Apache: 2.2.20

入れ子のコレクションを記述するために YAML は1つもしくは複数のスペースによる字下げをします:

    [yml]
    "symfony 1.0":
      PHP:    5.0
      Propel: 1.2
    "symfony 1.2":
      PHP:    5.2
      Propel: 1.3

上記の YAML は次の PHP コードと同等です:

    [php]
    array(
      'symfony 1.0' => array(
        'PHP'    => 5.0,
        'Propel' => 1.2,
      ),
      'symfony 1.2' => array(
        'PHP'    => 5.2,
        'Propel' => 1.3,
      ),
    );

YAML ファイルで字下げするときに覚えておくことが1つあります: *字下げには1つもしくは複数のスペースを使い、タブを使ってはなりません*。

次のようにシーケンスとマッピングを入れ子にできます:

    [yml]
    'Chapter 1':
      - Introduction
      - Event Types
    'Chapter 2':
      - Introduction
      - Helpers

スコープを表現するために字下げよりも明確なインジケータを使うことで、コレクションの表現するためにフロースタイルを選ぶことができます。

シーケンスは角かっこ (`[]`) で囲まれカンマで区切られるリストとして記述できます:

    [yml]
    [PHP, Perl, Python]

マッピングは波かっこ (`{}`) で囲まれカンマで区切られるキーもしくは値として記述されます:

    [yml]
    { PHP: 5.2, MySQL: 5.1, Apache: 2.2.20 }

より見やすくするために複数のスタイルを混ぜることができます:

    [yml]
    'Chapter 1': [Introduction, Event Types]
    'Chapter 2': [Introduction, Helpers]

-

    [yml]
    "symfony 1.0": { PHP: 5.0, Propel: 1.2 }
    "symfony 1.2": { PHP: 5.2, Propel: 1.3 }

コメント
--------

コメントの行頭にハッシュ記号 (`#`) をつけることでコメントを YAML に追加できます:

    [yml]
    # 行コメント
    "symfony 1.0": { PHP: 5.0, Propel: 1.2 } # 行末のコメント
    "symfony 1.2": { PHP: 5.2, Propel: 1.3 }

>**NOTE**
>コメントは YAML パーサーによって無視されコレクションの入れ子の現在のレベルにしたがって字下げされます。

動的な YAML ファイル
--------------------

symfony では、解析直前に評価される PHP コードを YAML ファイルに入れることができます:

    [php]
    1.0:
      version: <?php echo file_get_contents('1.0/VERSION')."\n" ?>
    1.1:
      version: "<?php echo file_get_contents('1.1/VERSION') ?>"

字下げで散らかさないように気をつけてください。PHP コードを YAML ファイルに 追加するとき次のシンプルなティップスを思い出してください:

 * `<?php ?>` 命令文は行で始めるもしくは値に埋め込まなければなりません。

 * `<?php ?>` 命令文が単一行で終わるとき、改行 (`\n`) を明示的に出力する必要があります。

<div class="pagebreak"></div>

すべての例
----------

次の例はこのドキュメントで説明したほとんどの YAML 表記を記述しています:

    [yml]
    "symfony 1.0":
      end_of_maintenance: 2010-01-01
      is_stable:           true
      release_manager:     "Gregoire Hubert"
      description: >
        This stable version is the right choice for projects
        that need to be maintained for a long period of time.
      latest_beta:         ~
      latest_minor:        1.0.20
      supported_orms:      [Propel]
      archives:            { source: [zip, tgz], sandbox: [zip, tgz] }

    "symfony 1.2":
      end_of_maintenance: 2008-11-01
      is_stable:           true
      release_manager:     'Fabian Lange'
      description: >
        This stable version is the right choice
        if you start a new project today.
      latest_beta:         null
      latest_minor:        1.2.5
      supported_orms:
        - Propel
        - Doctrine
      archives:
        source:
          - zip
          - tgz
        sandbox:
          - zip
          - tgz
