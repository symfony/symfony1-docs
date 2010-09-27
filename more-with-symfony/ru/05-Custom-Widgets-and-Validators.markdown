Свои собственные виджеты и валидаторы
=============================

*автор: Thomas Rabaix; перевод на русский &mdash; BRIGADA*

Этот раздел объясняет, как создать свой собственный виджет и валидатор для использования в фреймворке форм. Здесь обсуждаются внутренности классов `sfWidgetForm` и `sfValidator`, а также описывается процесс построения простого и сложного виджета.

Внутренности виджетов и валидаторов
------------------------------

### Внутренности `sfWidgetForm`

Объект класса ~`sfWidgetForm`~ представляет визуальную реализацию того, как соответствующие данные должны редактироваться. Например, строковое значение может быть изменено в простом текстовом поле, а может &mdash; в расширенном WYSIWYG-редакторе. Для обеспечения полноценного конфигурирования, класс `sfWidgetForm` имеет два важных свойства: `options` и `attributes`.

 * `options` &mdash; используется для конфигурирования виджета (например, задаётся запрос к базе данных для создания содержимого ниспадающего списка)

 * `attributes` &mdash; HTML-атрибуты, добавляемые к элементу при его выводе

Дополнительно в классе `sfWidgetForm` реализовано два важных метода:

 * `configure()` &mdash; определяет, какие опции являются *необязательными*, а какие &mdash; *важными*.
   Так как переопределение конструктора является не очень хорошей практикой, то лучше делать всю необходимую работу в методе `configure()` &mdash; это полностью безопасно.

 * `render()` &mdash; выводит HTML-код виджета. У этого метода есть один важный первый аргумент, имя виджета, а также необязательный второй аргумент &mdash; значение.

>**NOTE**
>Объект `sfWidgetForm` ничего не знает о своем имени и значении. Компонент отвечает только за вывод виджета. Именами и значениями управляет объект `sfFormFieldSchema`, который связывает данные с виджетами.

### Внутренности sfValidatorBase

Класс ~`sfValidatorBase`~ является базовым для любого валидатора. Метод ~`sfValidatorBase::clean()`~ &mdash; самый важный метод этого класса, так как он проверяет допустимость значения в соответствии с указанными опциями.

Внутри метод `clean()` выполняет несколько действий:

 * обрезает начальные и конечные пробелы в строковых значениях (если указана опция `trim`)
 * проверяет значение на пустоту
 * вызывает метод валидатора `doClean()`.

Метод `doClean()` определяет основную логику валидатора. Плохой практикой является переопределение метода `clean()`, свою логику работы валидатора следует реализовывать в методе `doClean()`.

Валидатор также может быть использован как самостоятельный компонент, проверяющий целостность ввода. Например, валидатор `sfValidatorEmail` проверяет допустимость электронной почты:

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
>Когда форма связывается со значениями запроса, объект `sfForm` сохраняет ссылки на оригинальные (грязные) и прошедшие валидатор (чистые) значения. Оригинальные значения используются при повторном выводе формы, а чистые используются приложением (например, при сохранении объекта).

### Атрибут `options`

Объекты `sfWidgetForm` и `sfValidatorBase` имеют различные опции: некоторые из них обязательны, а другие нет. Все эти опции определяются каждым классом в методе `configure()` через вызовы:

 * `addOption($name, $value)` &mdash; определяет опцию с именем `name` и значением по умолчанию `value`
 * `addRequiredOption($name)` &mdash; определяет обязательную опцию

Эти два метода очень удобны, поскольку гарантируют что зависимые значения корректно передаются в валидатор или виджет.

Построение простого виджета и валидатора
--------------------------------------

Этот раздел описывает процесс построения простого виджета. Наш специальный виджет будет называться "Trilean". Он будет отображать поле выбора с тремя вариантами: `No`, `Yes` и `Null`.

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

Метод `configure()` определяет список значений через опцию `choices`. Этот массив может быть переопределён (например, для изменения выводимых меток и соответствующих им значений). Никаких ограничений на число опций виджета не определяется. Базовый класс виджета, однако, определяет ряд стандартных опций, которые работают как зарезервированные:

 * `id_format` &mdash; формат идентификатора, по умолчанию '%s'

 * `is_hidden` &mdash; булево значение, определяющее видимость поля (используется `sfForm::renderHiddenFields()` для вывода всех скрытых полей разом)

 * `needs_multipart` &mdash; булево значение, определяющее необходимость включения в тэг form опции multipart (например, для загрузки файлов)

 * `default` &mdash; значение по умолчанию, которое используется при выводе виджета без заданного значения

 * `label` &mdash; метка виджета по умолчанию

Метод `render()` генерирует соответствующий полю выбора HTML-код. Для этого он вызывает встроенную функцию `renderContentTag()`.

Теперь наш виджет готов, давайте создадим соответствующий валидатор:

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

Валидатор `sfValidatorTrilean` в методе `configure()` определяет три опции. Каждая из этих опций является набором допустимых значений. Так как они определены в опциях, разработчик может изменить их в соответствии со своими нуждами.

Метод `doClean()` проверяет соответствие переданного значения наборам допустимых и возвращает очищенное значение. Если соответствия значения не обнаружено, генерируется исключение `sfValidatorError`, являющееся стандартной ошибкой валидации в фреймворке форм.

Последний метод, `isEmpty()`, переопределён, так как его поведение по умолчанию должно вернуть `true` если было передано значение `null`. А наш виджет считает `null` допустимым значением, поэтому этот метод должен всегда возвращать `false`.

>**Note**:
> Если бы `isEmpty()` возвращал `true`, то метод `doClean()` никогда не был бы вызван.

Несмотря на то, что разработанный нами виджет был достаточно простым, он позволил нам продемонстрировать некоторые важные особенности, которые потребуются в дальнейшем. В следующем разделе мы создадим более сложный виджет с несколькими полями и использованием JavaScript.

Виджет Google Address Map
-----------------------------

В этом разделе, мы будем создавать сложный виджет. Будут введены новые методы, а также виджет будет использовать JavaScript-взаимодействие. Мы назовём новый виджет "GMAW" &mdash; "Google Map Address Widget" (виджет адресов гугло-карт).

Чего мы хотим достичь? Виджет должен обеспечить конечному пользователю простой способ добавления адресов. С помощью поля ввода текста и услуг, предоставляемых сервисом карт Google, мы сможем достичь этой цели.

![Набросок "Google Map Address Widget"](http://www.symfony-project.org/images/more-with-symfony/widgets-figure-01.png "Набросок "Google Map Address Widget"")

Вариант использования 1:

 * Пользователь вводит адрес.
 * Пользователь щёлкает кнопку "поиск" (lookup).
 * Скрытые поля, хранящие значения широты и долготы обновляются, и на карте создаётся новый маркер. Маркер указывает на положение введённого адреса. Если сервис геолокации Google не может найти адрес, появляется сообщение об ошибке.

Вариант использования 2:

 * Пользователь кликает мышкой по карте.
 * Скрытые поля, хранящие значения широты и долготы обновляются.
 * Для поиска соответствующего адреса используется обратный запрос.

*Следующие поля должны выводиться и обрабатываться формой:*

 * `latitude` &mdash; число с плавающей точкой (float), в диапазоне от 90 до -90
 * `longitude` &mdash; число с плавающей точкой (float), в диапазоне от 180 до -180
 * `address` &mdash; строка (string), только простой текст

Функциональная спецификация виджета была только что дана, теперь давайте определим технические средства и области их применения:

 * Карты Google и сервис геолокации (Geocoding): отображение карты и получение адресной информации
 * jQuery: добавление JavaScript-взаимодействия между формой и полями
 * sfForm: рисование виджета и валидация ввода

### Виджет `sfWidgetFormGMapAddress`

Поскольку виджет &mdash; это визуальное представление данных, метод `configure()` виджета должен иметь различные опции для настройки карт Google или изменения стилей элементов. Одна из важнейших опций, `template.html`, определяет порядок элементов. Когда вы разрабатываете новый виджет, всегда помните о его повторном использовании и расширяемости.

Другая важная вещь &mdash; внешнее определение активов. Класс `sfWidgetForm` должен реализовывать два специальных метода: 

 * `getJavascripts()` должен вернуть массив JavaScript-файлов;

 * `getStylesheets()` должен вернуть массив CSS-файлов
   (где ключом будет путь, а значение &mdash; соответствующая величина для атрибута media).

Нашему виджету для работы потребуется только некоторый JavaScript-код. В данном случае, однако, виджет не будет обрабатывать инициализацию Google JavaScript, хотя и будет использовать соответствующие сервисы геолокации и карт. Вместо этого, мы обяжем разработчика включить их прямо на странице. Причина такого поведения заключается в том, что сервисы Google могут взаимодействовать с другими элементами на странице, а не только с нашим виджетом.

Давайте переходить к коду:

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
        // определяем главные переменные шаблона
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

        // для исключения уведомления об ошибках при неверном формате $value
        $value = !is_array($value) ? array() : $value;
        $value['address']   = isset($value['address'])   ? $value['address'] : '';
        $value['longitude'] = isset($value['longitude']) ? $value['longitude'] : '';
        $value['latitude']  = isset($value['latitude'])  ? $value['latitude'] : '';

        // определяем виджет поля адреса
        $address = new sfWidgetFormInputText(array(), $this->getOption('address.options'));
        $template_vars['{input.search}'] = $address->render($name.'[address]', $value['address']);

        // определяем поля широты и долготы
        $hidden = new sfWidgetFormInputHidden;
        $template_vars['{input.longitude}'] = $hidden->render($name.'[longitude]', $value['longitude']);
        $template_vars['{input.latitude}']  = $hidden->render($name.'[latitude]', $value['latitude']);

        // объединяем шаблоны и переменные
        return strtr(
          $this->getOption('template.html').$this->getOption('template.javascript'),
          $template_vars
        );
      }
    }

Виджет использует метод `generateId()` для генерации атрибута `id` каждого элемента. Переменная `$name` определяется `sfFormFieldSchema` так, что составляется из имени формы, имён других схем виджетов и имени самого виджета, как показано в методе `configure()`.

>**NOTE**
>Например, если имя формы есть `user`, имя вложенной схемы есть `location`, а имя виджета &mdash; `address`, то результирующее значение для атрибута `name` будет `user[location][address]`, а значение атрибута `id` соответственно будет `user_location_address`. Другими словами, строка `$this->generateId($name.'[latitude]')` генерирует допустимый и уникальный атрибут `id` для поля `latitude`.

Различные атрибуты `id` элементов очень важны, т.к. они передаются в блок JavaScript (через переменные `template.js`), что позволяет JavaScript-коду корректно обрабатывать различные элементы.

Метод `render()` также выводит два типа встроенных виджетов: `sfWidgetFormInpuМеtText`, который используется как поле ввода адреса, и `sfWidgetFormInputHidden`, используемый для хранения скрытых полей с широтой и долготой.

Виджет можно быстро протестировать следующим кодом:

    [php]
    $widget = new sfWidgetFormGMapAddress();
    echo $widget->render('user[location][address]', array(
      'address' => '151 Rue montmartre, 75002 Paris',
      'longitude' => '2.294359',
      'latitude' => '48.858205'
    ));

В результате будет выведено:

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

Часть виджета, реализованная на JavaScript, получает атрибуты `id` некоторых элементов и связывает их с библиотекой jQuery таким образом, что при обработке действий над этими элементами вызывается определённый JavaScript-код. Этот код обновляет скрытые поля со значениями долготы и широты в соответствии с ответом сервиса геолокации Google.

JavaScript-код содержит ряд интересных функций:

 * `init()` &mdash; метод, в котором инициализируются переменные, а также происходит связывание полей с событиями

 * `lookupCallback()` &mdash; *статический* метод, используемый сервисом геолокации для поиска введённого пользователем адреса

 * `reverseLookupCallback()` &mdash; другой *статический* метод, используемый сервисом геологации для преобразования указанной долготы и широты в адрес.

Готовый JavaScript-код можно посмотреть в Приложении A.

Пожалуйста, для получения дополнительной информации по использованию карт Google, ознакомьтесь с официальной [документацией](http://code.google.com/apis/maps/).

### Валидатор `sfValidatorGMapAddress`

Класс `sfValidatorGMapAddress` наследуется от `sfValidatorBase`, который уже выполняет одну проверку: если для поля установлена опция `required`, то значение не может быть `null`. Таким образом, классу `sfValidatorGMapAddress` необходимо поверить сами значения `latitude`, `longitude` и `address`. Переменная `$value` должна быть массивом, но так как пользовательскому вводу доверять нельзя, валидатор проверяет присутствие всех ключей, и в результате внутренним валидаторам передаются допустимые значения.

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
>Валидатор всегда генерирует исключение `sfValidatorError` при получении недопустимого значения. Именно из-за этого внутренний процесс валидации заключён в блок `try/catch`. В нашем валидаторе, при возникновении исключения в блоке `try` оно переповторяется как новое исключение `invalid`.

### Тестирование

Почему тестирование столь важно? Валидатор является связующим звеном между пользовательским вводом и приложением. Если валидатор реализован плохо, то всё приложение потенциально уязвимо. Однако symfony включает очень простую в использовании библиотеку тестирования `lime`.

Как мы можем протестировать валидатор? Как было рассказано выше, валидатор вызывает исключение при возникновении ошибок валидации. Тест может послать допустимые и недопустимые значения, чтобы увидеть возникновение исключения в правильных обстоятельствах.

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

Когда вызывается метод `sfForm::bind()`, форма выполняет метод `clean()` каждого валидатора. Этот тест воспроизводит такое поведение, путём непосредственного создания валидатора `sfValidatorGMapAddress` и тестирования в нём различных значений.

Заключение
--------------

Наиболее распространенная ошибка при создании виджета заключается в том, что чрезмерно много внимания уделяется вопросам хранения информации в базе данных. Фреймворк форм &mdash; это просто контейнер данных и среда валидации. Поэтому виджет должен управлять только своей собственной информацией. Если данные допустимы, то различные очищенные значения могут использоваться в модели или контроллере.