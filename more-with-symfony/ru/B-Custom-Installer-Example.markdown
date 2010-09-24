Приложение B - Пример заказного установщика
===========================================

Ниже приведён пример PHP-кода, реализующего заказной установщик, используемы в [Главе 06](#chapter_06):

    [php]
    <?php

    $this->logSection('install', 'default to sqlite');
    $this->runTask('configure:database', sprintf("'sqlite:%s/database.db'", sfConfig::get('sf_data_dir')));

    $this->logSection('install', 'create an application');
    $this->runTask('generate:app', 'frontend');

    $this->setConfiguration($this->createConfiguration('frontend', 'dev'));

    $this->logSection('install', 'publish assets');
    $this->runTask('plugin:publish-assets');
    if (file_exists($dir = sfConfig::get('sf_symfony_lib_dir').'/../data'))
    {
      $this->installDir($dir);
    }

    $this->logSection('install', 'create the database schema');
    file_put_contents(sfConfig::get('sf_config_dir').'/doctrine/schema.yml', <<<EOF
    Product:
      columns:
        name:           { type: string(255), notnull: true }
        price:          { type: decimal, notnull: true }

    ProductPhoto:
      columns:
        product_id:     { type: integer }
        filename:       { type: string(255) }
        caption:        { type: string(255), notnull: true }
      relations:
        Product:
          alias:        Product
          foreignType:  many
          foreignAlias: Photos
          onDelete:     cascade
    EOF
    );

    $this->logSection('install', 'add some fixtures');
    file_put_contents(sfConfig::get('sf_data_dir').'/fixtures/fixtures.yml', <<<EOF
    Product:
      product_1:
        name:  Product Name
        price: 25.95
    EOF
    );

    $this->logSection('install', 'build the model');
    $this->runTask('doctrine:build', '--all --and-load --no-confirmation');

    $this->logSection('install', 'create a simple CRUD module');
    $this->runTask('doctrine:generate-module', 'frontend product Product --non-verbose-templates');

    $this->logSection('install', 'fix sqlite database permissions');
    chmod(sfConfig::get('sf_data_dir'), 0777);
    chmod(sfConfig::get('sf_data_dir').'/database.db', 0777);
