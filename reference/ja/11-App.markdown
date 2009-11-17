app.yml設定ファイル
===================

symfonyフレームワークはアプリケーション固有の設定のために組み込みの設定ファイルの`app.yml`を提供します。

このYAMLファイルには特定のアプリケーションで使いたい任意の設定を格納できます。
コードにおいて、これらの設定はグローバルな`sfConfig`クラスを通して利用可能で、キーにはプレフィックスとして文字列の`app_`がつけられます:

    [php]
    sfConfig::get('app_active_days');

すべての設定にはプレフィックスの`app_`がつけられます。
`sfConfig`クラスは[symfonyの設定](#chapter_03_sub_configuration_settings)と[プロジェクトのディレクトリ](#chapter_03_sub_directorie)にアクセスする権限を提供するからです。

最初の章で説明したように、`app.yml`ファイルは[**環境を認識し**](#chapter_03_environment_awareness)、[**コンフィギュレーションカスケードのメカニズム**](#chapter_03_configuration_cascade)がはたらきます。

`app.yml`設定ファイルは環境に基づいて変化する設定(たとえばAPIキー)もしくは時間をかけて進化する可能性のある設定(たとえばEメールアドレス)を定義するのにふさわしい場所です。
このファイルはsymfonyもしくはPHPを必ずしも理解する必要のない人間(たとえばシステム管理者)によって変更する必要のある設定を定義するのにも最良の場所です。

>**TIP**
>アプリケーションのロジックを搭載するために`app.yml`を使うのはお控えください。

-

>**NOTE**
>`app.yml`設定ファイルはPHPファイルとしてキャッシュされます。
>処理は~`sfDefineEnvironmentConfigHandler`~[クラス](#chapter_14_config_handlers_yml)によって自動的に管理されます。
