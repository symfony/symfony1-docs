Продвинутые формы
==============

*авторы: Ryan Weaver и Fabien Potencier; перевод на русский - BRIGADA*

Среда обработки форм symfony снабжает разработчика инструментами, позволяющими легко выводить формы и проверять введённые в них данные. Благодаря имеющимся в каждой из ORM-систем классам ~`sfFormDoctrine`~ и ~`sfFormPropel`~, среда обработки форми позволяет легко выводить и сохранять формы, которые очень близки уровню данных.

В реальных ситуациях зачастую требуется, чтобы разработчик имел возможность настраивать и расширять формы. В этой главе мы продемонстрируем решение нескольких общих проблем с формами. Также мы проанализируем объект ~`sfForm`~ и сбросим с него пелену загадочности.

Мини-проект: Продукты и Фотографии (Products & Photos)
-------------------------------

Первая проблема заключается в редактировании одного продукта и неограниченного числа фотографий это продукта. Пользователю может потребоваться возможность редактировать и описание продукта и его фотографии на одной и той же странице. Мы также позволим пользователю загружать до двух новых изображений продукта одновременно. Вот возможная схема:

    [yml]
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

Когда мы закончим, наша форма будет выглядеть приблизительно так:

![Product and photo form](http://www.symfony-project.org/images/more-with-symfony/advanced_forms_01.png "Форма Product с внедрёнными формами ProductPhoto")

Изучайте больше, делая примеры
--------------------------------

Лучший путь изучения продвинутых методик разработки заключается в последовательном изучении примеров и выполнении тестов. Благодаря встроенному в [symfony](#chapter_03) параметру командной строки `--installer`, мы предлагаем вам простой способ создания работающего проекта, с готовой к использованию базой данных SQLite, схемой базы данных Doctrine, некоторыми начальными данными, приложением `frontend` и модулем `product`. Для создания проекта symfony скачайте установочный [скрипт](http://www.symfony-project.org/images/more-with-symfony/advanced_form_installer.php.src) и выполните следующую команду:

    $ php symfony generate:project advanced_form --installer=/path/to/advanced_form_installer.php

Эта команда создаёт полностью работающий проект со схемой базы данных, которую мы привели в предыдущем разделе.

>**NOTE**
>В этой главе файловые пути соответствуют проекту symfony, полученному в предыдущей задаче.

Начальная настройка формы
----------------

Поскольку требования задают нам две различные модели (`Product` и `ProductPhoto`), решение должно будет включить две различные формы (`ProductForm` и `ProductPhotoForm`). К счастью, фреймворк форм позволяет легко комбинировать несколько форм в одну через вызов ~`sfForm::embedForm()`~. Сначала настроим ProductPhotoForm. В этом примере будем использовать поле `filename` в качестве поля загрузки файла:

    [php]
    // lib/form/doctrine/ProductPhotoForm.class.php
    public function configure()
    {
      $this->useFields(array('filename', 'caption'));

      $this->setWidget('filename', new sfWidgetFormInputFile());
      $this->setValidator('filename', new sfValidatorFile(array(
        'mime_types' => 'web_images',
        'path' => sfConfig::get('sf_upload_dir').'/products',
      )));
    }

Для этой формы поля `caption` и `filename` автоматически создаются «обязательными», но по разным причинам. Поле `caption` является обязательным, потому что соответствующая колонка в схеме базы данных содержит свойство `notnull` со значением `true`. Поле `filename` является обязательным, потому что объект валидатора использует значение `true` для параметра `required`.

>**NOTE**
>В symfony 1.3 появилась функция ~`sfForm::useFields()`~, которая позволяет 
>разработчику указать те поля формы (а также их порядок), которые будут отображаться
>Все прочие не скрытые поля удаляются из формы.

На данный момент мы сделали начальную настройку формы. Далее мы объединим их в одну.

Встраивание форм
---------------

Используя метод ~`sfForm::embedForm()`~ можно легко объединить независимые формы `ProductForm` и `ProductPhotoForms`. Эта работа всегда делается в *главной* форме, которой в нашем случае является `ProductForm`. В функциональных требованиях указано, что необходимо одновременно загружать две фотографии продукта.
Для реализации этого требования, встроим два объекта `ProductPhotoForm` в `ProductForm`:

    [php]
    // lib/form/doctrine/ProductForm.class.php
    public function configure()
    {
      $subForm = new sfForm();
      for ($i = 0; $i < 2; $i++)
      {
        $productPhoto = new ProductPhoto();
        $productPhoto->Product = $this->getObject();

        $form = new ProductPhotoForm($productPhoto);

        $subForm->embedForm($i, $form);
      }
      $this->embedForm('newPhotos', $subForm);
    }

Если вы направите свой браузер на модуль `product`, то увидите что теперь можно загружать два экземпляра `ProductPhoto`, а также изменять сам объект `Product`. Symfony автоматически сохраняет новые объекты `ProductPhoto` и связывает их с соответствующим объектом `Product`. Даже загрузка файла, определённого в `ProductPhotoForm` происходит нормально.

Проверьте корректность сохранения записей в базе данных:

    $ php symfony doctrine:dql --table "FROM Product"
    $ php symfony doctrine:dql --table "FROM ProductPhoto"

В таблице `ProductPhoto` следует посмотреть имена файлов фотографий. Всё работает правильно, если те же самые имена вы видите в каталоге `web/uploads/products/`.

>**NOTE**
>Так как поля с именем файла и описанием в таблице `ProductPhotoForm` описаны как обязательные, проверка главной формы всегда будет заканчиваться ошибкой, если пользователь не загружает две новые фотографии. Ниже мы покажем, как решить эту проблему.

Рефакторинг
-----------

Даже если бы созданная в предыдущем разделе форма работала корректно, было бы неплохо сделать рефакторинг кода для упрощения тестирования и повторного использования.

Во-первых, давайте на основе уже написанного кода создадим новую форму, представляющую собой коллекцию из `ProductPhotoForm`:

    [php]
    // lib/form/doctrine/ProductPhotoCollectionForm.class.php
    class ProductPhotoCollectionForm extends sfForm
    {
      public function configure()
      {
        if (!$product = $this->getOption('product'))
        {
          throw new InvalidArgumentException('You must provide a product object.');
        }

        for ($i = 0; $i < $this->getOption('size', 2); $i++)
        {
          $productPhoto = new ProductPhoto();
          $productPhoto->Product = $product;

          $form = new ProductPhotoForm($productPhoto);

          $this->embedForm($i, $form);
        }
      }
    }

Эта форма требует указания двух опций:

 * `product` - продукт, для которого создаётся коллекция объектов `ProductPhotoForm`;

 * `size` - число создаваемых объектов `ProductPhotoForm` (по умолчанию два).

Теперь вы можете изменить метод `configure` класса `ProductForm` следующим образом:

    [php]
    // lib/form/doctrine/ProductForm.class.php
    public function configure()
    {
      $form = new ProductPhotoCollectionForm(null, array(
        'product' => $this->getObject(),
        'size'    => 2,
      ));

      $this->embedForm('newPhotos', $form);
    }

Препарирование объекта sfForm
----------------------------

В самом базовом смысле, вэб-форма есть коллекция полей, которые вначале выводятся клиенту, а затем отсылаются обратно на сервер. В том же самом свете, объект ~`sfForm`~ в действительности есть массив *полей* формы. Сам объект ~`sfForm`~ управляет процессом, а содержащиеся в нём поля формы ответственны за определение того как именно они будут отображаться и проверяться.

В symfony, каждое *поле* формы определяется двумя разными объектами:

  * *виджет* (widget), который выводит поле формы в разметке XHTML;

  * *валидатор* (validator), который осуществляет очистку и проверку присланных в поле данных.

>**TIP**
>В symfony, *виджет* определён как любой объект, единственная задача которого заключается в выводе XHTML. Виджеты чаще всего используются в формах, однако такой объект можно создать для любого другого вывода разметки.

### Форма есть массив

Напомним, объект ~`sfForm`~ есть «массив *полей* формы». Если быть точнее, ~`sfForm`~ содержит все поля формы в массивах виджетов и валидаторов. Эти два массива, называемые `widgetSchema` и `validatorSchema`, являются свойствами класса ~`sfForm`~. Для добавления поля к форме, мы просто добавляем соответствующий виджет в массив `widgetSchema`, а валидатор в массив `validatorSchema`. Например, следующий код добавляет к форме поле `email`:

    [php]
    public function configure()
    {
      $this->widgetSchema['email'] = new sfWidgetFormInputText();
      $this->validatorSchema['email'] = new sfValidatorEmail();
    }

>**NOTE**
>Массивы `widgetSchema` и `validatorSchema` в действительности являются специальными
>классами `sfWidgetFormSchema` и `sfValidatorSchema`, которые реализуют интерфейс `ArrayAccess`.

### Препарирование `ProductForm`

Поскольку класс `ProductForm` наследуется от класса `sfForm`, он также содержит все свои виджеты и валидаторы в массивах `widgetSchema` и `validatorSchema`. Давайте посмотрим, как организованы эти массивы в объекте `ProductForm`.

    [php]
    widgetSchema    => array
    (
      [id]          => sfWidgetFormInputHidden,
      [name]        => sfWidgetFormInputText,
      [price]       => sfWidgetFormInputText,
      [newPhotos]   => array(
        [0]           => array(
          [id]          => sfWidgetFormInputHidden,
          [filename]    => sfWidgetFormInputFile,
          [caption]     => sfWidgetFormInputText,
        ),
        [1]           => array(
          [id]          => sfWidgetFormInputHidden,
          [filename]    => sfWidgetFormInputFile,
          [caption]     => sfWidgetFormInputText,
        ),
      ),
    )

    validatorSchema => array
    (
      [id]          => sfValidatorDoctrineChoice,
      [name]        => sfValidatorString,
      [price]       => sfValidatorNumber,
      [newPhotos]   => array(
        [0]           => array(
          [id]          => sfValidatorDoctrineChoice,
          [filename]    => sfValidatorFile,
          [caption]     => sfValidatorString,
        ),
        [1]           => array(
          [id]          => sfValidatorDoctrineChoice,
          [filename]    => sfValidatorFile,
          [caption]     => sfValidatorString,
        ),
      ),
    )

>**TIP**
>Так как `widgetSchema` и `validatorSchema` в действительности есть объекты,
>которые ведут себя как массивы, оба массива содержат ключ `newPhotos`,
>в котором `0` и `1`  также объекты `sfWidgetSchema` и `sfValidatorSchema`.

Как и ожидалось, основные поля (`id`, `name` и `price`) представлены на первом уровне каждого массива. В форме, в которую не внедрено других форм, массивы `widgetSchema` и `validatorSchema`  имеют только один уровень, представляющий основные поля формы. Виджеты и валидаторы внедрённых форм представляются как дочерние массивы в `widgetSchema` и `validatorSchema` (что и видно выше). Метод, который управляет этим процессом, описывается ниже.

### Метод ~`sfForm::embedForm()`~

Вы помните, что форма состоит из массива виджетов и массива валидаторов. Внедрение одной формы в другую означает, что массивы виджетов и валидаторов внедряемой формы добавляются к массивам виджетов и валидаторов главной формы. Это действие реализуется с помощью `sfForm::embedForm()`. В результате всегда происходит многомерное добавление к массивам `widgetSchema` и `validatorSchema`.

Ниже мы рассмотрим настройку формы `ProductPhotoCollectionForm`, которая объединяет в себе отдельные объекты `ProductPhotoForm`. Эта промежуточная форма действует как «обёртка» и помогает более прозрачно организовывать формы. Давайте начнём со следующего кода в `ProductPhotoCollectionForm::configure()`:

    [php]
    $form = new ProductPhotoForm($productPhoto);
    $this->embedForm($i, $form);

Форма `ProductPhotoCollectionForm` изначально есть новый объект `sfForm`. Таким образом, массивы `widgetSchema` и `validatorSchema` пусты.

    [php]
    widgetSchema    => array()
    validatorSchema => array()

Однако каждый экземпляр `ProductPhotoForm` уже содержит три поля (`id`, `filename` и `caption`), т.е. в массивах `widgetSchema` и `validatorSchema` есть соответствующие этим полям элементы.

    [php]
    widgetSchema    => array
    (
      [id]            => sfWidgetFormInputHidden,
      [filename]      => sfWidgetFormInputFile,
      [caption]       => sfWidgetFormInputText,
    )

    validatorSchema => array
    (
      [id]            => sfValidatorDoctrineChoice,
      [filename]      => sfValidatorFile,
      [caption]       => sfValidatorString,
    )

Метод ~`sfForm::embedForm()`~ просто добавляет массивы `widgetSchema` и `validatorSchema` каждого экземпляра `ProductPhotoForm` к массивам `widgetSchema` и `validatorSchema` изначально пустого объекта `ProductPhotoCollectionForm`.

В результате, массивы `widgetSchema` и `validatorSchema` формы-обёртки (`ProductPhotoCollectionForm`) будут многоуровневыми массивами, которые содержат виджеты и валидаторы из обоих `ProductPhotoForm`.

    [php]
    widgetSchema    => array
    (
      [0]             => array
      (
        [id]            => sfWidgetFormInputHidden,
        [filename]      => sfWidgetFormInputFile,
        [caption]       => sfWidgetFormInputText,
      ),
      [1]             => array
      (
        [id]            => sfWidgetFormInputHidden,
        [filename]      => sfWidgetFormInputFile,
        [caption]       => sfWidgetFormInputText,
      ),
    )

    validatorSchema => array
    (
      [0]             => array
      (
        [id]            => sfValidatorDoctrineChoice,
        [filename]      => sfValidatorFile,
        [caption]       => sfValidatorString,
      ),
      [1]             => array
      (
        [id]            => sfValidatorDoctrineChoice,
        [filename]      => sfValidatorFile,
        [caption]       => sfValidatorString,
      ),
    )

Последним шагом мы внедряем готовую форму-обёртку `ProductPhotoCollectionForm` напрямую в `ProductForm`. Это происходит в методе `ProductForm::configure()`:

    [php]
    $form = new ProductPhotoCollectionForm(null, array(
      'product' => $this->getObject(),
      'size'    => 2,
    ));

    $this->embedForm('newPhotos', $form);

Это позволяет нам получить массивы `widgetSchema` и `validatorSchema` с указанной выше структурой. Заметьте, что метод `embedForm()` действует подобно простому комбинированию массивов `widgetSchema` и `validatorSchema`:

    [php]
    $this->widgetSchema['newPhotos'] = $form->getWidgetSchema();
    $this->validatorSchema['newPhotos'] = $form->getValidatorSchema();

Вывод внедрённых форм в Виде
------------------------------------

Текущий шаблон `_form.php` модуля `product` выглядит следующим образом:

    [php]
    // apps/frontend/module/product/templates/_form.php
    <!-- ... -->

    <tbody>
      <?php echo $form ?>
    </tbody>

    <!-- ... -->

Выражение `<?php echo $form ?>` есть самый простой способ отображения даже сложных форм. Это очень помогает на этапе моделирования, однако когда вам потребуется изменить расположение элементов, вам нужно будет поменять этот код на собственную логику отображения. В этом разделе мы заменим эту строку на свой код.

Наиболее важной вещью для понимания процесса вывода внедрённых форм является внутренняя организация многоуровневого массива `widgetSchema`. В нашем случае, давайте начнём с вывода в **Виде** полей `name` и `price` из `ProductForm`:

    [php]
    // apps/frontend/module/product/templates/_form.php
    <?php echo $form['name']->renderRow() ?>

    <?php echo $form['price']->renderRow() ?>

    <?php echo $form->renderHiddenFields() ?>

Как можно догадаться по имени метода, `renderHiddenFields()` выводит все скрытые поля формы.

>**NOTE**
>Мы специально не приводим здесь код для действий, т.к. он не требует никакого специального внимания.
>Посмотрите файл `apps/frontend/modules/product/actions/actions.class.php`. Он выглядит как любое нормальное
>CRUD-приложение (т.е. приложение, реализующее 4 основных действия с данными - создание, чтение, обновление и удаление)
>и может быть сгенерировано автоматически через задачу `doctrine:generate-module`.

Как мы уже выяснили, класс `sfForm` содержит массивы `widgetSchema` и `validatorSchema`, которые определяют наши поля. Кроме того, класс `sfForm` реализует PHP5-интерфейс `ArrayAccess`, что позволяет нам обращаться к полям формы напрямую через ключи массива.

Для вывода полей вы можете просто напрямую обратиться к ним и вызвать метод `renderRow()`. Но какого типа объект `$form['name']`? Вы могли бы сказать, что ответом будет виджет `sfWidgetFormInputText` для поля `name`, однако в действительности ответ несколько иной.

### Вывод полей формы с помощью ~`sfFormField`~

Используя массивы `widgetSchema` и `validatorSchema`, определённые для каждого класса формы, `sfForm` автоматически генерирует третий массив `sfFormFieldSchema`. Этот массив содержит специальный объект для каждого поля, который действует как вспомогательный класс (хелпер), ответственный за вывод поля. Объект с типом `sfFormField` есть комбинация из соответствующего виджета и валидатора, который создаётся автоматически.

    [php]
    <?php echo $form['name']->renderRow() ?>

В приведённом выше коде, `$form['name']` является объектом `sfFormField`, который имеет метод `renderRow()`, а также другие полезные при выводе функции.

### Методы вывода sfFormField

Каждый объект `sfFormField` может быть использован для упрощения вывода любого связанного с полем аспекта (т.е. непосредственно поле, его метка, сообщения об ошибках и т.д.). Некоторые из методов `sfFormField` рассмотрены ниже. Остальные можно найти в документации [symfony 1.3 API](http://www.symfony-project.org/api/1_3/sfFormField).

 * `sfFormField->render()` - выводит поле формы (например `input`, `select`) с корректным значением, используя виджет-объект.

 * `sfFormField->renderError()` - выводит все ошибки валидации поля, используя соответствующий валидатор-объект.

 * `sfFormField->renderRow()` - выводит метку, поле формы, ошибки и сообщение-подсказку в XHTML-обёртке.

>**NOTE**
>В действительности, все функции вывода класса `sfFormField` используют информацию из свойства `widgetSchema` формы (объект `sfWidgetFormSchema` хранит все виджеты формы).
>Этот класс помогает при генерации атрибутов `name` и `id` каждого поля, следит за метками полей и определяет используемую в `renderRow()` XHTML-разметку.

Есть одна важная вещь, которую следует отметить: массив `formFieldSchema` всегда есть зеркальное отражение структуры массивов `widgetSchema` и `validatorSchema` формы. Например, массив `formFieldSchema` законченной формы `ProductForm` будет иметь следующую структуру, в которой выводимые в Виде поля являются ключами:

    [php]
    formFieldSchema    => array
    (
      [id]          => sfFormField
      [name]        => sfFormField,
      [price]       => sfFormField,
      [newPhotos]   => array(
        [0]           => array(
          [id]          => sfFormField,
          [filename]    => sfFormField,
          [caption]     => sfFormField,
        ),
        [1]           => array(
          [id]          => sfFormField,
          [filename]    => sfFormField,
          [caption]     => sfFormField,
        ),
      ),
    )

### Вывод новой формы ProductForm

Используя приведённый выше массив в качестве шпаргалки, мы можем очень легко вывести внедрённые в `ProductPhotoForm` поля, отыскивая и выводя соответствующие объекты `sfFormField`:

    [php]
    // apps/frontend/module/product/templates/_form.php
    <?php foreach ($form['newPhotos'] as $photo): ?>
      <?php echo $photo['caption']->renderRow() ?>
      <?php echo $photo['filename']->renderRow() ?>
    <?php endforeach; ?>

Этот цикл выполняется дважды: один раз для поля формы с ключом `0` и один раз для поля с ключом `1`. Как видно в диаграмме выше, массивы хранят объекты `sfFormField`, которые мы можем вывести, как и другие поля.

Сохранение объектов форм
-------------------

В большинстве случаев форма имеет прямое отношение к одной или нескольким таблицам базы данных и позволяет изменять данные в этих таблицах соответственно переданным значениям. Symfony автоматически генерирует объекты форм для каждой модели схемы данных, которые наследуются от `sfFormDoctrine` или `sfFormPropel`  в зависимости от используемой вами ORM. Каждый класс формы соответствующим строкам в таблицах, что позволяет легко размещать их в базе данных.

>**NOTE**
>~`sfFormObject`~ - это новый класс, добавленный в symfony 1.3. Он обрабатывает все общие задачи `sfFormDoctrine` и `sfFormPropel`. Оба класса наследуются от `sfFormObject`, который теперь управляет описанным ниже процессом сохранения форм.

### Процесс сохранения форм

В нашем примере symfony автоматически сохраняет информацию из объектов `Product` и `ProductPhoto` без каких-либо специальных усилий со стороны разработчика. Метод, который реализует это волшебство, называется ~`sfFormObject::save()`~, он в свою очередь вызывает множество других методов. Понимание этого процесса является ключом для перехода к более общим ситуациям.

Процесс сохранения формы состоит из серии внутренних вызовов методов, следующих за вызовом ~`sfFormObject::save()`~. Основная работа делается методом ~`sfFormObject::updateObject()`~, который рекурсивно вызывается для всех внедрённых форм.

*   _sfFormObject::save()_
    *   _sfFormObject::doSave()_
        *   _sfFormObject::updateObject()_
            1.  _sfFormDoctrine::processValues($values)_
                *   Передаваемый в этот метод массив _$values_ есть ассоциативный массив "сырых" значений.
                *   Этот метод выполняет обработку значений верхнего уровня.
                *   Для каждого поля вызывается метод _updateXXXColumn()_, если он существует.
                *   Вызывается метод _sfFormDoctrine::processUploadedFile()_, который преобразует все значения из загруженных полей в объекты _sfValidatedFile_.
            2.  _sfFormDoctrine::doUpdateObject($values)_
                *   Массив _$values_ - это массив, возвращенный _processValues()_.
                *   Это метод просто обновляет объект в соответствии с массивом _$values_.
            3.  _sfFormDoctrine::updateObjectEmbeddedForms($values)_
                *   Вызывается метод _sfFormObject::updateObject()_ для каждой внедрённой формы.
                *   Описанный в шагах 1-3 процесс повторяется рекурсивно.

>**NOTE**
>Основная часть процесса сохранения располагается в методе `sfFormObject::doSave()`,
>который вызывается `sfFormObject::save()` и обёрнут в транзакцию базы данных.
>Если вам необходимо изменить сам процесс сохранения, `sfFormObject::doSave()`
>обычно является наилучшим местом для этого.


Игнорирование внедрённых форм
-----------------------

У текущей реализации `ProductForm` есть один существенный недостаток. Так как поля `filename` и `caption` являются обязательными в `ProductPhotoForm`, валидация главной формы будет всегда завершаться ошибкой, пока пользователь не загрузит две новых фотографии. Другими словами, пользователь не сможет просто поменять цену в `Product` без загрузки пары новых фотографий.

![Форма Product с ошибками валидации](http://www.symfony-project.org/images/more-with-symfony/advanced_forms_04.png "Форма Product не принята из-за ошибки валидации фотографий")

Давайте переопределим требования, чтобы включить следующее. Если пользователь оставляет все поля `ProductPhotoForm` пустыми, то эта форма должна быть проигнорирована. Однако, если любое из полей содержит данные (например `caption` или `filename`), форма должна проверяться и сохраняться. Для достижения этого, мы будем использовать специальную технику, основанную на реализации своих собственных валидаторов.

Но вначале необходимо изменить форму `ProductPhotoForm` так, чтобы сделать поля `caption` и `filename` необязательными:

    [php]
    // lib/form/doctrine/ProductPhotoForm.class.php
    public function configure()
    {
      $this->setValidator('filename', new sfValidatorFile(array(
        'mime_types' => 'web_images',
        'path' => sfConfig::get('sf_upload_dir').'/products',
        'required' => false,
      )));

      $this->validatorSchema['caption']->setOption('required', false);
    }

В приведённом выше коде для поля `filename` мы устанавливаем значение опции `required` в `false` и изменяем валидатор по умолчанию, а для поля `caption` только устанавливаем значение опции `required` в `false`. 

Теперь, давайте добавим пост-валидатор к `ProductPhotoCollectionForm`:

    [php]
    // lib/form/doctrine/ProductPhotoCollectionForm.class.php
    public function configure()
    {
      // ...

      $this->mergePostValidator(new ProductPhotoValidatorSchema());
    }

Пост-валидатор - это специальный тип валидатора, который проверяет корректность всех присланных значений одновременно (а не по отдельности для каждого поля). Один из наиболее часто используемых пост-валидаторов - `sfValidatorSchemaCompare`. Он позволяет проверить, например, что одно поле меньше другого.

### Создание своего собственного валидатора

К счастью, создание собственного валидатора в действительности достаточно простая задача. Создайте новый файл, `ProductPhotoValidatorSchema.class.php` и поместите его в каталог lib/validator/ (возможно, вам потребуется создать этот каталог):

    [php]
    // lib/validator/ProductPhotoValidatorSchema.class.php
    class ProductPhotoValidatorSchema extends sfValidatorSchema
    {
      protected function configure($options = array(), $messages = array())
      {
        $this->addMessage('caption', 'The caption is required.');
        $this->addMessage('filename', 'The filename is required.');
      }

      protected function doClean($values)
      {
        $errorSchema = new sfValidatorErrorSchema($this);

        foreach($values as $key => $value)
        {
          $errorSchemaLocal = new sfValidatorErrorSchema($this);

          // поле filename заполнено, caption - нет
          if ($value['filename'] && !$value['caption'])
          {
            $errorSchemaLocal->addError(new sfValidatorError($this, 'required'), 'caption');
          }

          // поле caption заполнено, filename - нет
          if ($value['caption'] && !$value['filename'])
          {
            $errorSchemaLocal->addError(new sfValidatorError($this, 'required'), 'filename');
          }

          // поля caption и filename не заполнены, удаляем пустые значения
          if (!$value['filename'] && !$value['caption'])
          {
            unset($values[$key]);
          }

          // в этой внедрённой форме есть некоторые ошибки
          if (count($errorSchemaLocal))
          {
            $errorSchema->addError($errorSchemaLocal, (string) $key);
          }
        }

        // передаём ошибку в главную форму
        if (count($errorSchema))
        {
          throw new sfValidatorErrorSchema($this, $errorSchema);
        }

        return $values;
      }
    }

>**TIP**
>Все валидаторы наследуются от `sfValidatorBase` и требуют реализации метода `doClean()`.
>Метод `configure()` может также использоваться, если необходимо добавить параметры или сообщения к валидатору. В данном случае, к валидатору добавляется два сообщения.
>Аналогично добавляются параметры при помощи метода `addOption()`.

Метод `doClean()` отвечает за очистку и проверку значений. Логика работы нашего валидатора весьма проста:

 * Если фотография была отправлена с указанием только имени файла или описания, мы генерируем исключение (`sfValidatorErrorSchema`) с соответствующим сообщением;

 * Если фотография была отправлена без имени файла и без описания, мы удаляем значения для предотвращения сохранения «пустой» фотографии;

 * Если никаких ошибок валидации не произошло, метод возвращает массив «очищенных» значений.

>**TIP**
>Поскольку наш валидатор используется в качестве пост-валидатора, метод `doClean()` принимает ассоциативный массив значений и возвращает массив «очищенных» значений. Собственный валидатор также легко можно создать для конкретного поля. В этом случае, метод `doClean()` должен будет принимать одно значение (значение присланного поля) и должен будет вернуть только одно значение.

Наконец, мы перепишем метод `saveEmbeddedForms()` формы `ProductForm` для предотвращения сохранения в базе данных пустых форм фотографий (в противном случае, будет вызвано исключение, т.к. поле `caption` является обязательным):

    [php]
    public function saveEmbeddedForms($con = null, $forms = null)
    {
      if (null === $forms)
      {
        $photos = $this->getValue('newPhotos');
        $forms = $this->embeddedForms;
        foreach ($this->embeddedForms['newPhotos'] as $name => $form)
        {
          if (!isset($photos[$name]))
          {
            unset($forms['newPhotos'][$name]);
          }
        }
      }

      return parent::saveEmbeddedForms($con, $forms);
    }

Упрощённое внедрение Doctrine-форм
---------------------------------------

В symfony 1.3 появилась функция ~`sfFormDoctrine::embedRelation()`~, которая позволяет разработчику автоматически внедрять в форму взаимосвязи n-ко-многим. Предположим, например, что помимо возможности загрузки двух новых `ProductPhotos`, мы также хотим позволить пользователю изменять существующие объекты `ProductPhoto`, связанные с этим `Product`.

Используем метод `embedRelation()` для добавления одного дополнительного объекта `ProductPhotoForm` для каждого существующего объекта `ProductPhoto`:

    [php]
    // lib/form/doctrine/ProductForm.class.php
    public function configure()
    {
      // ...

      $this->embedRelation('Photos');
    }

Внутри `sfFormDoctrine::embedRelation()` делает почти тоже самое, что мы уже делали руками для внедрения двух новых объектов `ProductPhotoForm`. Если две фотографии `ProductPhoto` уже существуют, тогда массивы `widgetSchema` и `validatorSchema` нашей формы выглядят следующим образом:

    [php]
    widgetSchema    => array
    (
      [id]          => sfWidgetFormInputHidden,
      [name]        => sfWidgetFormInputText,
      [price]       => sfWidgetFormInputText,
      [newPhotos]   => array(...)
      [Photos]      => array(
        [0]           => array(
          [id]          => sfWidgetFormInputHidden,
          [caption]     => sfWidgetFormInputText,
        ),
        [1]           => array(
          [id]          => sfWidgetFormInputHidden,
          [caption]     => sfWidgetFormInputText,
        ),
      ),
    )

    validatorSchema => array
    (
      [id]          => sfValidatorDoctrineChoice,
      [name]        => sfValidatorString,
      [price]       => sfValidatorNumber,
      [newPhotos]   => array(...)
      [Photos]      => array(
        [0]           => array(
          [id]          => sfValidatorDoctrineChoice,
          [caption]     => sfValidatorString,
        ),
        [1]           => array(
          [id]          => sfValidatorDoctrineChoice,
          [caption]     => sfValidatorString,
        ),
      ),
    )

![Форма Product с 2 существующими фотографиями](http://www.symfony-project.org/images/more-with-symfony/advanced_forms_03.png "Форма Product с двумя существующими фотографиями")

Следующим шагом станет добавление к Виду кода, который выведет новые внедрённые формы *Photo*:

    [php]
    // apps/frontend/module/product/templates/_form.php
    <?php foreach ($form['Photos'] as $photo): ?>
      <?php echo $photo['caption']->renderRow() ?>
      <?php echo $photo['filename']->renderRow(array('width' => 100)) ?>
    <?php endforeach; ?>

Эта часть кода аналогична уже использованному нами при внедрении форм новых фотографий.

Последний шаг заключается в изменении поля загруженного файла таким образом, чтобы позволить пользователю смотреть текущую фотографию и иметь возможность редактировать новые (`sfWidgetFormInputFileEditable`):

    [php]
    public function configure()
    {
      $this->useFields(array('filename', 'caption'));

      $this->setValidator('filename', new sfValidatorFile(array(
        'mime_types' => 'web_images',
        'path' => sfConfig::get('sf_upload_dir').'/products',
        'required' => false,
      )));

      $this->setWidget('filename', new sfWidgetFormInputFileEditable(array(
        'file_src'    => '/uploads/products/'.$this->getObject()->filename,
        'edit_mode'   => !$this->isNew(),
        'is_image'    => true,
        'with_delete' => false,
      )));

      $this->validatorSchema['caption']->setOption('required', false);
    }

События формы
-----------

В symfony 1.3 появились события форм, которые можно использовать для расширения любой формы, откуда угодно в проекте. Symfony предоставляет следующие четыре события:

 * `form.post_configure` - это событие происходит после конфигурирования формы
 * `form.filter_values` - это событие происходит при фильтрации непосредственно перед ассоциированием
 * `form.validation_error` - это событие происходит, когда валидация формы завершается ошибкой
 * `form.method_not_found` - это событие происходит, когда делается попытка вызова несуществующего метода

### Организация логирования через `form.validation_error`

При помощи событий формы можно реализовать логирование ошибок валидации для любой формы в вашем проекте. Это может быть полезно, если вы хотите проследить за тем, какие формы и поля вызывают затруднения у ваших пользователей.

Начнём с регистрации "слушателя" диспетчера событий на событие `form.validation_error`. Добавьте следующий код в метод `setup()` класса `ProjectConfiguration`, который находится в каталоге `config`:

    [php]
    public function setup()
    {
      // ...

      $this->getEventDispatcher()->connect(
        'form.validation_error',
        array('BaseForm', 'listenToValidationError')
      );
    }

`BaseForm`, расположенный в `lib/form` - это специальный базовый класс формы, от которого наследуются все классы форм. По сути, `BaseForm` является классом, размещение кода в котором делает его (код) доступным любому объекту формы проекта. Для включения логирования ошибок валидации просто добавьте следующие строки в класс `BaseForm`:

    [php]
    public static function listenToValidationError($event)
    {
      foreach ($event['error'] as $key => $error)
      {
        self::getEventDispatcher()->notify(new sfEvent(
          $event->getSubject(),
          'application.log',
          array (
            'priority' => sfLogger::NOTICE,
            sprintf('Validation Error: %s: %s', $key, (string) $error)
          )
        ));
      }
    }

![Логирование ошибок валидации](http://www.symfony-project.org/images/more-with-symfony/advanced_forms_05.png "Отладочная web-панель с ошибками валидации")

Изменение стиля элементов формы с ошибками
-----------------------------------------------

В качестве заключительного упражнения, давайте обратимся к связанной с оформлением элементов формы теме. Пусть, например, дизайн страницы `Product` содержит специальный стиль для полей, которые не прошедшие проверку.

![Форма продукта с ошибками](http://www.symfony-project.org/images/more-with-symfony/advanced_forms_02.png "Форма продукта с выделенными ошибками")

Предположим, ваш дизайнер уже реализовал таблицу стилей, которая будет применяться при выделении ошибок любого поля `input` внутри `div` с классом `form_error_row`. Как мы можем упростить добавление класса `form_row_error` к полям, которые содержат ошибки?

Ответ заключается в специальном объекте, называемом *форматировщик формы*. Каждая форма в symfony использует *форматировщик формы* для определения верного html-форматирования при выводе элементов формы. По умолчанию, symfony
использует форматировщик, который выводит табличные теги HTML.

Для начала, давайте создадим новый класс форматировщика, который упростит разметку при выводе формы. Создайте новый файл с именем `sfWidgetFormSchemaFormatterAc2009.class.php` и поместите его в каталог `lib/widget/` (вам нужно будет создать этот каталог): 

    [php]
    class sfWidgetFormSchemaFormatterAc2009 extends sfWidgetFormSchemaFormatter
    {
      protected
        $rowFormat       = "<div class=\"form_row\">
                            %label% \n %error% <br/> %field%
                            %help% %hidden_fields%\n</div>\n",
        $errorRowFormat  = "<div>%errors%</div>",
        $helpFormat      = '<div class="form_help">%help%</div>',
        $decoratorFormat = "<div>\n  %content%</div>";
    }

Хотя формат этого класса выглядит странным, общая идея заключается в том, что `renderRow()` при выводе будет использовать указанную `$rowFormat` разметку. Класс форматировщика формы содержит множество других опций форматирования, которые мы не будем здесь подробно рассматривать. Для получения дополнительной информации, смотрите 
[symfony 1.3 API](http://www.symfony-project.org/api/1_3/sfWidgetFormSchemaFormatter).

Для использования нового форматировщика во всех объектах форм вашего проекта, добавьте следующий код в `ProjectConfiguration`:

    [php]
    class ProjectConfiguration extends sfProjectConfiguration
    {
      public function setup()
      {
        // ...

        sfWidgetFormSchema::setDefaultFormFormatterName('ac2009');
      }
    }

Цель состоит в том, чтобы добавить класс `form_row_error` к элементу div только если поле имеет ошибки валидации. Добавьте токен `%row_class%` к `$rowFormat` и измените метод ~`sfWidgetFormSchemaFormatter::formatRow()`~ следующим образом:

    [php]
    class sfWidgetFormSchemaFormatterAc2009 extends sfWidgetFormSchemaFormatter
    {
      protected
        $rowFormat       = "<div class=\"form_row%row_class%\">
                            %label% \n %error% <br/> %field%
                            %help% %hidden_fields%\n</div>\n",
        // ...

      public function formatRow($label, $field, $errors = array(), $help = '', $hiddenFields = null)
      {
        $row = parent::formatRow(
          $label,
          $field,
          $errors,
          $help,
          $hiddenFields
        );

        return strtr($row, array(
          '%row_class%' => (count($errors) > 0) ? ' form_row_error' : '',
        ));
      }
    }

С этим дополнением каждый элемент с ошибками валидации при выводе через метод `renderRow()` будет автоматически окружаться тэгом `div` с классом `form_row_error`.

Заключение
--------------

Фреймворк форм - один из самых мощных и одновременно сложных компонентов symfony. Более глубокое понимание фреймворка форм является ключом к раскрытию его потенциала. Я надеюсь, что эта глава помогла вам продвинуться на один шаг вперёд.

В будущем развитие среды разработки форм сосредоточится на том, чтобы при сохранении всей мощи уменьшить сложность и дать большую гибкость разработчику. Среда разработки форм в настоящее время находится всего лишь на начальном этапе развития.
