Custom Widgets and Validators
=============================

*by Thomas Rabaix*

This chapter explains how to build a custom widget and validator for use
in the form framework. It will explain the internals of `sfWidgetForm` and
`sfValidator`, as well as how to build both a simple and complex widget.

Widget and Validator Internals
------------------------------

### `sfWidgetForm` Internals

An object of the ~`sfWidgetForm`~ class represents the visual implementation of how
related data will be edited. A string value, for example, might be edited
with a simple text box or an advanced WYSIWYG editor. In order to be fully configurable,
the `sfWidgetForm` class has two important properties: `options` and `attributes`.

 * `options`: used to configure the widget (e.g. the database query to be
   used when creating a list to be used in a select box)

 * `attributes`: HTML attributes added to the element upon rendering

Additionally, the `sfWidgetForm` class implements two important methods:

 * `configure()`: defines which options are *optional* or *mandatory*.
   While it is not a good practice to override the constructor, the `configure()`
   method can be safely overridden.

 * `render()`: outputs the HTML for the widget. The method has a mandatory
   first argument, the HTML widget name, and an optional second argument,
   the value.

>**NOTE**
>An `sfWidgetForm` object does not know anything about its name or its value.
>The component is responsible only for rendering the widget. The name and
>the value are managed by an `sfFormFieldSchema` object, which is the link
>between the data and the widgets.

### sfValidatorBase Internals

The ~`sfValidatorBase`~ class is the base class of each validator. The
~`sfValidatorBase::clean()`~ method is the most important method of this class
as it checks if the value is valid depending on the provided options.

Internally, the `clean()` method perform several different actions:

 * trims the input value for string values (if specified via the `trim` option)
 * checks if the value is empty
 * calls the validator's `doClean()` method.

The `doClean()` method is the method which implements the main validation
logic. It is not good practice to override the `clean()` method. Instead,
always perform any custom logic via the `doClean()` method.

A validator can also be used as a standalone component to check input integrity.
For instance, the `sfValidatorEmail` validator will check if the email is valid:

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
>When a form is bound to the request values, the `sfForm` object keeps
>references to the original (dirty) values and the validated (clean) values.
>The original values are used when the form is redrawn, while the cleaned
>values are used by the application (e.g. to save the object).

### The `options` Attribute

Both the `sfWidgetForm` and `sfValidatorBase` objects have a variety of options:
some are optional while others are mandatory. These options are defined
inside each class's `configure()` method via:

 * `addOption($name, $value)`: defines an option with a name and a default value
 * `addRequiredOption($name)`: defines a mandatory option

These two methods are very convenient as they ensure that dependency values
are correctly passed to the validator or the widget.

Building a Simple Widget and Validator
--------------------------------------

This section will explain how to build a simple widget. This particular widget
will be called a "Trilean" widget. The widget will display a select box with three choices:
`No`, `Yes` and `Null`.

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

The `configure()` method defines the option values list via the `choices` option.
This array can be redefined (i.e. to change the associated label of each value).
There is no limit to the number of option a widget can define. The base
widget class, however, declares a few standard options, which function like
de-facto reserved options:

 * `id_format`: the id format, default is '%s'

 * `is_hidden`: boolean value to define if the widget is a hidden field (used
   by `sfForm::renderHiddenFields()` to render all hidden fields at once)

 * `needs_multipart`: boolean value to define if the form tag should include
   the multipart option (i.e. for file uploads)

 * `default`: The default value that should be used to render the widget
   if no value is provided

 * `label`: The default widget label

The `render()` method generates the corresponding HTML for a select box. The
method calls the built-in `renderContentTag()` function to help render HTML tags.

The widget is now complete. Let's create the corresponding validator:

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

The `sfValidatorTrilean` validator defines three options in the `configure()`
method. Each option is a set of valid values. As these are defined as options,
the developer can customize the values depending on the specification.

The `doClean()` method checks if the value matches a set a valid values and
returns the cleaned value. If no value is matched, the method will raise an
`sfValidatorError` which is the standard validation error in the form framework.

The last method, `isEmpty()`, is overridden as the default behavior of this
method is to return `true` if `null` is provided. As the current widget allows
`null` as a valid value, the method must always return `false`.

>**Note**:
> If `isEmpty()` returns true, the `doClean()` method will never be called.

While this widget was fairly straightforward, it introduced some important base features
that will be needed as we go further. The next section will create a more
complex widget with multiple fields and JavaScript interaction.

The Google Address Map Widget
-----------------------------

In this section, we are going to build a complex widget. New methods will
be introduced and the widget will have some JavaScript interaction as well.
The widget will be called "GMAW": "Google Map Address Widget".

What do we want to achieve? The widget should provide an easy way for the
end user to add an address. By using an input text field and with google's
map services we can achieve this goal.

!["Google Map Address Widget" mashup](http://www.symfony-project.org/images/more-with-symfony/widgets-figure-01.png ""Google Map Address Widget" mashup")

Use case 1:

 * The user types an address.
 * The user clicks the "lookup" button.
 * The latitude and longitude hidden fields are updated and a new marker
   is created on the map. The marker is positioned at the location of the
   address. If the Google Geocoding service cannot find the address an error
   message will popup.

Use case 2:

 * The user clicks on the map.
 * The latitude and longitude hidden fields are updated.
 * Reverse lookup is used to find the address.

*The following fields need to be posted and handled by the form:*

 * `latitude`: float, between 90 and -90
 * `longitude`: float, between 180 and -180
 * `address`: string, plain text only

The widget's functional specifications have just been defined, now let's
define the technical tools and their scopes:

 * Google map and Geocoding services: displays the map and retrieves address information
 * jQuery: adds JavaScript interactions between the form and the field
 * sfForm: draws the widget and validates the inputs

### `sfWidgetFormGMapAddress` Widget

As a widget is the visual representation of data, the `configure()` method
of the widget must have different options to tweak the Google map or modify
the styles of each element. One of the most important options is the
`template.html` option, which defines how all elements are ordered.
When building a widget it is very important to think about reusability and
extensibility.

Another important thing is the external assets definition. An `sfWidgetForm`
class can implement two specific methods:

 * `getJavascripts()` must return an array of JavaScript files;

 * `getStylesheets()` must return an array of stylesheet files
   (where the key is the path and the value the media name).

The current widget only requires some JavaScript to work so no stylesheet is needed.
In this case, however, the widget will not handle the initialization of the
Google JavaScript, though the widget will make use of the Google geocoding
and map services. Instead, it will be the developer's responsibility
to include it on the page. The reason behind this is that Google's services
may be used by other elements on the page, and not only by the widget.

Let's jump to the code:

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
        // define main template variables
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

        // avoid any notice errors to invalid $value format
        $value = !is_array($value) ? array() : $value;
        $value['address']   = isset($value['address'])   ? $value['address'] : '';
        $value['longitude'] = isset($value['longitude']) ? $value['longitude'] : '';
        $value['latitude']  = isset($value['latitude'])  ? $value['latitude'] : '';

        // define the address widget
        $address = new sfWidgetFormInputText(array(), $this->getOption('address.options'));
        $template_vars['{input.search}'] = $address->render($name.'[address]', $value['address']);

        // define the longitude and latitude fields
        $hidden = new sfWidgetFormInputHidden;
        $template_vars['{input.longitude}'] = $hidden->render($name.'[longitude]', $value['longitude']);
        $template_vars['{input.latitude}']  = $hidden->render($name.'[latitude]', $value['latitude']);

        // merge templates and variables
        return strtr(
          $this->getOption('template.html').$this->getOption('template.javascript'),
          $template_vars
        );
      }
    }

The widget uses the `generateId()` method to generate the `id` of each element.
The `$name` variable is defined by the `sfFormFieldSchema`, so the `$name`
variable is composed of the name form, any nested widget schema names and
the name of the widget as defined in the form `configure()`.

>**NOTE**
>For instance, if the form name is `user`, the nested schema name is `location`
>and the widget name is `address`, the final `name` will be `user[location][address]`
>and the `id` will be `user_location_address`. In other words,
>`$this->generateId($name.'[latitude]')` will generate a valid and unique
>`id` for the `latitude` field.

The different element `id` attributes are quite important as there are passed
to the JavaScript block (via the `template.js` variable), so the JavaScript can
properly handle the different elements.

The `render()` method also instantiates two inner widgets: an `sfWidgetFormInputText`
widget, which is used to render the address field, and an `sfWidgetFormInputHidden`
widget, which is used to render the hidden fields.

The widget can be quickly tested with this small piece of code:

    [php]
    $widget = new sfWidgetFormGMapAddress();
    echo $widget->render('user[location][address]', array(
      'address' => '151 Rue montmartre, 75002 Paris',
      'longitude' => '2.294359',
      'latitude' => '48.858205'
    ));

The output result is:

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

The JavaScript part of the widget takes the different `id` attributes and
binds jQuery listeners to them so that certain JavaScript is triggered
when actions are performed. The JavaScript updates the hidden fields with
the longitude and latitude provided by the google geocoding service.

The JavaScript object has a few interesting methods:

 * `init()`: the method where all variables are initialized and events
   bound to different inputs

 * `lookupCallback()`: a *static* method used by the geocoder method to
   lookup the address provided by the user

 * `reverseLookupCallback()`: is another *static* method used by the geocoder
   to convert the given longitude and latitude into a valid address.

The final JavaScript code can be viewed in Appendix A.

Please refer to the Google map documentation for more details on the functionality
of the Google maps [API](http://code.google.com/apis/maps/).

### `sfValidatorGMapAddress` Validator

The `sfValidatorGMapAddress` class extends `sfValidatorBase` which already
performs one validation: specifically, if the field is set as required then
the value cannot be `null`. Thus, `sfValidatorGMapAddress` need only validate
the different values: `latitude`, `longitude` and `address`. The `$value` variable
should be an array, but as the user input should not be trusted, the validator
checks for the presence of all keys so that the inner validators are passed
valid values.

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
>A validator always raises an `sfValidatorError` exception when a value is not
>valid. That's why the validation is surrounded by a `try/catch` block.
>In this validator, the validator re-throws a new `invalid` exception, which
>equates to an `invalid` validation error on the `sfValidatorGMapAddress`
>validator.

### Testing

Why is testing important? The validator is the glue between the user input
and the application. If the validator is flawed, the application is vulnerable.
Fortunately, symfony comes with `lime` which is a testing library that is
very easy to use.

How can we test the validator? As stated before, a validator raises an exception
on a validation error. The test can send valid and invalid values to the validator
and check to see that the exception is thrown in the correct circumstances.

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

When the `sfForm::bind()` method is called, the form executes the `clean()`
method of each validator. This test reproduces this behavior by instantiating
the `sfValidatorGMapAddress` validator directly and testing different values.

Final Thoughts
--------------

The most common mistake when creating a widget is to be overly focused on
how the information will be stored in the database. The form framework is
simply a data container and validation framework. Therefore, a widget must
only manage its related information. If the data is valid then the different
cleaned values can then be used by the model or in the controller.
