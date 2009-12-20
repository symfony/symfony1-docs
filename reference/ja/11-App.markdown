app.yml 設定ファイル
====================

symfony フレームワークはアプリケーション固有の設定のために組み込みの設定ファイルの `app.yml` を提供します。

この YAML ファイルには特定のアプリケーションで使いたい任意の設定を収めることができます。コードにおいて、これらの設定はグローバルな `sfConfig` クラスを通して利用可能で、キーにはプレフィックスとして文字列の `app_` がつけられます:

    [php]
    sfConfig::get('app_active_days');

すべての設定にはプレフィックスの`app_`がつけられます。`sfConfig` クラスは [symfony の設定](#chapter_03_sub_configuration_settings)と[プロジェクトのディレクトリ](#chapter_03_sub_directorie)にアクセスする権限を提供するからです。

第3章で説明したように、`app.yml` ファイルは[**環境を認識し**](#chapter_03_environment_awareness)、[**コンフィギュレーションカスケードのメカニズム**](#chapter_03_configuration_cascade)がはたらきます。

`app.yml` 設定ファイルは環境にもとづいて変化する設定 (たとえば API キー) もしくは時間をかけて進化する可能性のある設定 (たとえばメールアドレス) を定義するのにふさわしい場所です。このファイルは symfony もしくは PHP を必ずしも理解する必要のない人間 (たとえばシステム管理者) によって変更する必要のある設定を定義するのにもベストな場所です。

>**TIP**
>アプリケーションのロジックを搭載するために `app.yml` を使うのはお控えください。

-

>**NOTE**
>`app.yml` 設定ファイルは PHP ファイルとしてキャッシュされます。処理は ~`sfDefineEnvironmentConfigHandler`~ [クラス](#chapter_14_config_handlers_yml)によって自動管理されます。
