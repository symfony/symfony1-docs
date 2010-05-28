The generator.yml Configuration File
====================================

The admin generator of symfony allows the creation of a backend interface for
your model classes. It works whether you use Propel or Doctrine as your ORM.

### Creation

Admin generator modules are created by the `propel:generate-admin` or
`doctrine:generate-admin` tasks:

    $ php symfony propel:generate-admin backend Article

    $ php symfony doctrine:generate-admin backend Article

The above command creates an `article` admin generator module for the
`Article` model class.

>**NOTE**
>The `generator.yml` configuration file is cached as a PHP file; the
>process is automatically managed by the ~`sfGeneratorConfigHandler`~
>[class](#chapter_14_config_handlers_yml).

### Configuration File

The configuration of such a module can be done in the
`apps/backend/modules/model/article/generator.yml` file:

    [yml]
    generator:
      class: sfPropelGenerator
      param:
        # An array of parameters

The file contains two main entries: `class` and `param`. The class is
`sfPropelGenerator` for Propel and `sfDoctrineGenerator` for Doctrine.

The `param` entry contains the configuration options for the generated module.
The `model_class` defines the model class bound to this module, and the
`theme` option defines the default theme to use.

But the main configuration is done under the `config` entry. It is organized
into seven sections:

  * `actions`: Default configuration for the actions found on the list and on the forms
  * `fields`:  Default configuration for the fields
  * `list`:    Configuration for the list
  * `filter`:  Configuration for the filters
  * `form`:    Configuration for the new/edit form
  * `edit`:    Specific configuration for the edit page
  * `new`:     Specific configuration for the new page

When first generated, all sections are defined as empty, as the admin
generator defines sensible defaults for all possible options:

    [yml]
    generator:
      param:
        config:
          actions: ~
          fields:  ~
          list:    ~
          filter:  ~
          form:    ~
          edit:    ~
          new:     ~

This document describes all possible options you can use to customize the
admin generator through the `config` entry.

>**NOTE**
>All options are available for both Propel and Doctrine and works the
>same if not stated otherwise.

### Fields

A lot of options take a list of fields as an argument. A field can be a real
column name, or a virtual one. In both cases, a getter must be defined in the
model class (`get` suffixed by the camel-cased field name).

Based on the context, the admin generator is smart enough to know how to
render fields. To customize the rendering, you can create a partial or a
component. By convention, partials are prefixed with an underscore (`_`), and
components by a tilde (`~`):

    [yml]
    display: [_title, ~content]

In the above example, the `title` field will be rendered by the `title`
partial, and the `content` field by the `content` component.

The admin generator passes some parameters to partials and components:

  * For the `new` and `edit` page:

    * `form`:       The form associated with the current model object
    * `attributes`: An array of HTML attributes to be applied to the widget

  * For the `list` page:

    * `type`:       `list`
    * `MODEL_NAME`: The current object instance, where `MODEL_NAME` is the 
                    singular name set in the generator options. If no explicit 
                    value is defined, singular name will default to the
                    underscored version of the model class name (i.e. CamelCase 
                    becomes camel_case)

In an `edit` or `new` page, if you want to keep the two column layout (field
label and widget), the partial or component template should follow this
template:

    [php]
    <div class="sf_admin_form_row">
      <label>
        <!-- Field label or content to be displayed in the first column -->
      </label>
      <!-- Field widget or content to be displayed in the second column -->
    </div>

### Object Placeholders

Some options can take model object placeholders. A placeholder is a string
which follows the pattern: `%%NAME%%`. The `NAME` string can be anything that
can be converted to a valid object getter method (`get` suffixed by the
camel-cased version of the `NAME` string). For instance, `%%title%%` will be
replaced by the value of `$article->getTitle()`. Placeholder values are
dynamically replaced at runtime according to the object associated with the
current context.

>**TIP**
>When a model has a foreign key to another model, Propel and Doctrine
>define a getter for the related object. As for any other getter, it
>can be used as a placeholder if you define a meaningful `__toString()`
>method that converts the object to a string.

### Configuration Inheritance

The admin generator configuration is based on a configuration cascade
principle. The inheritance rules are the following:

 * `new` and `edit` inherit from `form` which inherits from `fields`
 * `list` inherits from `fields`
 * `filter` inherits from `fields`

### ~Credentials~

Actions in the admin generator (on the list and on the forms) can be hidden,
based on the user credentials using the `credential` option (see below).
However, even if the link or button does not appear, the actions must
still be properly secured from illicit access. The credential management in
the admin generator only takes care of the display.

The `credential` option can also be used to hide columns on the list page.

### Actions Customization

When configuration is not sufficient, you can override the generated methods:

 | Method                 | Description
 | ---------------------- | -------------------------------------
 | `executeIndex()`       | `list` view action
 | `executeFilter()`      | Updates the filters
 | `executeNew()`         | `new` view action
 | `executeCreate()`      | Creates a new record
 | `executeEdit()`        | `edit` view action
 | `executeUpdate()`      | Updates a record
 | `executeDelete()`      | Deletes a record
 | `executeBatch()`       | Executes a batch action
 | `executeBatchDelete()` | Executes the `_delete` batch action
 | `processForm()`        | Processes the record form
 | `getFilters()`         | Returns the current filters
 | `setFilters()`         | Sets the filters
 | `getPager()`           | Returns the list pager
 | `getPage()`            | Gets the pager page
 | `setPage()`            | Sets the pager page
 | `buildCriteria()`      | Builds the `Criteria` for the list
 | `addSortCriteria()`    | Adds the sort `Criteria` for the list
 | `getSort()`            | Returns the current sort column
 | `setSort()`            | Sets the current sort column

### Templates Customization

Each generated template can be overridden:

 | Template                     | Description
 | ---------------------------- | -------------------------------------
 | `_assets.php`                | Renders the CSS and JS to use for templates
 | `_filters.php`               | Renders the filters box
 | `_filters_field.php`         | Renders a single filter field
 | `_flashes.php`               | Renders the flash messages
 | `_form.php`                  | Displays the form
 | `_form_actions.php`          | Displays the form actions
 | `_form_field.php`            | Displays a single form field
 | `_form_fieldset.php`         | Displays a form fieldset
 | `_form_footer.php`           | Displays the form footer
 | `_form_header.php`           | Displays the form header
 | `_list.php`                  | Displays the list
 | `_list_actions.php`          | Displays the list actions
 | `_list_batch_actions.php`    | Displays the list batch actions
 | `_list_field_boolean.php`    | Displays a single boolean field in the list
 | `_list_footer.php`           | Displays the list footer
 | `_list_header.php`           | Displays the list header
 | `_list_td_actions.php`       | Displays the object actions for a row
 | `_list_td_batch_actions.php` | Displays the checkbox for a row
 | `_list_td_stacked.php`       | Displays the stacked layout for a row
 | `_list_td_tabular.php`       | Displays a single field for the list
 | `_list_th_stacked.php`       | Displays a single column name for the header
 | `_list_th_tabular.php`       | Displays a single column name for the header
 | `_pagination.php`            | Displays the list pagination
 | `editSuccess.php`            | Displays the `edit` view
 | `indexSuccess.php`           | Displays the `list` view
 | `newSuccess.php`             | Displays the `new` view

### Look and Feel Customization

The look of the admin generator can be tweaked very easily as the generated
templates define a lot of `class` and `id` HTML attributes.

In the `edit` or `new` page, each field HTML container has the following
classes:

  * `sf_admin_form_row`
  * a class depending on the field type: `sf_admin_text`, `sf_admin_boolean`,
    `sf_admin_date`, `sf_admin_time`, or `sf_admin_foreignkey`.
  * `sf_admin_form_field_COLUMN` where `COLUMN` is the column name

In the `list` page, each field HTML container has the following classes:

  * a class depending on the field type: `sf_admin_text`, `sf_admin_boolean`,
    `sf_admin_date`, `sf_admin_time`, or `sf_admin_foreignkey`.
  * `sf_admin_form_field_COLUMN` where `COLUMN` is the column name

<div class="pagebreak"></div>

Available Configuration Options
-------------------------------

 * [`actions`](#chapter_06_actions)

   * [`label`](#chapter_06_sub_label)
   * [`action`](#chapter_06_sub_action)
   * [`credentials`](#chapter_06_sub_credentials)

 * [`fields`](#chapter_06_fields)

   * [`label`](#chapter_06_sub_label)
   * [`help`](#chapter_06_sub_help)
   * [`attributes`](#chapter_06_sub_attributes)
   * [`credentials`](#chapter_06_sub_credentials)
   * [`renderer`](#chapter_06_sub_renderer)
   * [`renderer_arguments`](#chapter_06_sub_renderer_arguments)
   * [`type`](#chapter_06_sub_type)
   * [`date_format`](#chapter_06_sub_date_format)

 * [`list`](#chapter_06_list)

   * [`title`](#chapter_06_sub_title)
   * [`display`](#chapter_06_sub_display)
   * [`hide`](#chapter_06_sub_hide)
   * [`layout`](#chapter_06_sub_layout)
   * [`params`](#chapter_06_sub_params)
   * [`sort`](#chapter_06_sub_sort)
   * [`max_per_page`](#chapter_06_sub_max_per_page)
   * [`pager_class`](#chapter_06_sub_pager_class)
   * [`batch_actions`](#chapter_06_sub_batch_actions)
   * [`object_actions`](#chapter_06_sub_object_actions)
   * [`actions`](#chapter_06_sub_actions)
   * [`peer_method`](#chapter_06_sub_peer_method)
   * [`peer_count_method`](#chapter_06_sub_peer_count_method)
   * [`table_method`](#chapter_06_sub_table_method)
   * [`table_count_method`](#chapter_06_sub_table_count_method)

 * [`filter`](#chapter_06_filter)

   * [`display`](#chapter_06_sub_display)
   * [`class`](#chapter_06_sub_class)

 * [`form`](#chapter_06_form)

   * [`display`](#chapter_06_sub_display)
   * [`class`](#chapter_06_sub_class)

 * [`edit`](#chapter_06_edit)

   * [`title`](#chapter_06_sub_title)
   * [`actions`](#chapter_06_sub_actions)

 * [`new`](#chapter_06_new)

   * [`title`](#chapter_06_sub_title)
   * [`actions`](#chapter_06_sub_actions)

<div class="pagebreak"></div>

`fields`
--------

The `fields` section defines the default configuration for each field. This
configuration is defined for all pages and can be overridden on a page per
page basis (`list`, `filter`, `form`, `edit`, and `new`).

### ~`label`~

*Default*: The humanized column name

The `label` option defines the label to use for the field:

    [yml]
    config:
      fields:
        slug: { label: "URL shortcut" }

### ~`help`~

*Default*: none

The `help` option defines the help text to display for the field.

### ~`attributes`~

*Default*: `array()`

The `attributes` option defines the HTML attributes to pass to the widget:

    [yml]
    config:
      fields:
        slug: { attributes: { class: foo } }

### ~`credentials`~

*Default*: none

The `credentials` option defines credentials the user must have for the field
to be displayed. The credentials are only enforced for the object list.

    [yml]
    config:
      fields:
        slug:      { credentials: [admin] }
        is_online: { credentials: [[admin, moderator]] }

>**NOTE**
>The credential are to be defined with the same rules as in the
>`security.yml` configuration file.

### ~`renderer`~

*Default*: none

The `renderer` option defines a PHP callback to call to render the field. If
defined, it overrides any other flag like the partial or component ones.

The callback is called with the value of the field and the arguments defined
by the `renderer_arguments` option.

### ~`renderer_arguments`~

*Default*: `array()`

The `renderer_arguments` option defines the arguments to pass to the
`renderer` PHP callback when rendering the field. It is only used if the
`renderer` option is defined.

### ~`type`~

*Default*: `Text` for virtual columns

The `type` option defines the type of the column. By default, symfony uses the
type defined in your model definition, but if you create a virtual column, you
can override the default `Text` type by one of the valid types:

  * `ForeignKey`
  * `Boolean`
  * `Date`
  * `Time`
  * `Text`
  * `Enum` (only available for Doctrine)

### ~`date_format`~

*Default*: `f`

The `date_format` option defines the format to use when displaying dates. It
can be any format recognized by the `sfDateFormat` class. This option is not
used when the field type is `Date`.

The following tokens can be used for the format:

 * `G`: Era
 * `y`: year
 * `M`: mon
 * `d`: mday
 * `h`: Hour12
 * `H`: hours
 * `m`: minutes
 * `s`: seconds
 * `E`: wday
 * `D`: yday
 * `F`: DayInMonth
 * `w`: WeekInYear
 * `W`: WeekInMonth
 * `a`: AMPM
 * `k`: HourInDay
 * `K`: HourInAMPM
 * `z`: TimeZone

`actions`
---------

The framework defines several built-in actions. They are all prefixed by an
underscore (`_`). Each action can be customized with the options described in
this section. The same options can be used when defining an action in the
`list`, `edit`, or `new` entries.

### ~`label`~

*Default*: The action key

The `label` option defines the label to use for the action.

### ~`action`~

*Default*: Defined based on the action name

The `action` option defines the action name to execute without the `execute`
prefix.

### ~`credentials`~

*Default*: none

The `credentials` option defines credentials the user must have for the action
to be displayed.

>**NOTE**
>The credentials are to be defined with the same rules as in the
>`security.yml` configuration file.

`list`
------

### ~`title`~

*Default*: The humanized model class name suffixed with "List"

The `title` option defines the title of the list page.

### ~`display`~

*Default*: All model columns, in the order of their definition in the schema
file

The `display` option defines an array of ordered columns to display in the
list.

An equal sign (`=`) before a column is a convention to convert the string to a
link that goes to the `edit` page of the current object.

    [yml]
    config:
      list:
        display: [=name, slug]

>**NOTE**
>Also see the `hide` option to hide some columns.

### ~`hide`~

*Default*: none

The `hide` option defines the columns to hide from the list. Instead of
specifying the columns to be displayed with the `display` option, it is
sometimes faster to hide some columns:

    [php]
    config:
      list:
        hide: [created_at, updated_at]

>**NOTE**
>If both the `display` and the `hide` options are provided, the `hide`
>option is ignored.

### ~`layout`~

*Default*: `tabular`

*Possible values*: ~`tabular`~ or ~`stacked`~

The `layout` option defines what layout to use to display the list.

With the `tabular` layout, each column value is in its own table column.

With the `stacked` layout, each object is represented by a single string,
which is defined by the `params` option (see below).

>**NOTE**
>The `display` option is still needed when using the `stacked` layout as
>it defines the columns that will be sortable by the user.

### ~`params`~

*Default value*: none

The `params` option is used to define the HTML string pattern to use when
using a `stacked` layout. This string can contain model object placeholders:

    [yml]
    config:
      list:
        params:  |
          %%title%% written by %%author%% and published on %%published_at%%.

An equal sign (`=`) before a column is a convention to convert the string to a
link that goes to the `edit` page of the current object.

### ~`sort`~

*Default value*: none

The `sort` option defines the default sort column. It is an array composed of
two components: the column name and the sort order: `asc` or `desc`:

    [yml]
    config:
      list:
        sort: [published_at, desc]

### ~`max_per_page`~

*Default value*: `20`

The `max_per_page` option defines the maximum number of objects to display on
one page.

### ~`pager_class`~

*Default value*: `sfPropelPager` for Propel and `sfDoctrinePager` for Doctrine

The `pager_class` option defines the pager class to use when displaying the
list.

### ~`batch_actions`~

*Default value*: `{ _delete: ~ }`

The `batch_actions` option defines the list of actions that can be executed
for a selection of objects in the list.

If you don't define an `action`, the admin generator will look for a method
named after the camel-cased name prefixed by `executeBatch`.

The executed method received the primary keys of the selected objects via the
`ids` request parameter.

>**TIP**
>The batch actions feature can be disabled by setting the option to an
>empty array: `{}`

### ~`object_actions`~

*Default value*: `{ _edit: ~, _delete: ~ }`

The `object_actions` option defines the list of actions that can be executed
on each object of the list. The list of actions is an associative array which
keys are the route names and values an array of methods:

    [yml]
    object_actions: { publish: get, publishBis: [get, post] }

If you don't define an `action`, the admin generator will look for a method
named after the camel-cased name prefixed by `executeList`.

>**TIP**
>The object actions feature can be disabled by setting the option to an
>empty array: `{}`

### ~`actions`~

*Default value*: `{ _new: ~ }`

The `actions` option defines actions that take no object, like the creation of
a new object.

If you don't define an `action`, the admin generator will look for a method
named after the camel-cased name prefixed by `executeList`.

>**TIP**
>The object actions feature can be disabled by setting the option to an
>empty array: `{}`

### ~`peer_method`~

*Default value*: `doSelect`

The `peer_method` option defines the method to call to retrieve the objects to
display in the list.

>**CAUTION**
>This option only exists for Propel. For Doctrine, use the `table_method`
>option.

### ~`table_method`~

*Default value*: `doSelect`

The `table_method` option defines the method to call to retrieve the objects
to display in the list.

>**CAUTION**
>This option only exists for Doctrine. For Propel, use the `peer_method`
>option.

### ~`peer_count_method`~

*Default value*: `doCount`

The `peer_count_method` option defines the method to call to compute the
number of objects for the current filter.

>**CAUTION**
>This option only exists for Propel. For Doctrine, use the
>`table_count_method` option.

### ~`table_count_method`~

*Default value*: `doCount`

The `table_count_method` option defines the method to call to compute the
number of objects for the current filter.

>**CAUTION**
>This option only exists for Doctrine. For Propel, use the
>`peer_count_method` option.

`filter`
--------

The `filter` section defines the configuration for the filtering form
displayed on the list page.

### ~`display`~

*Default value*: All fields defined in the filter form class, in the order of
their definition

The `display` option defines the ordered list of fields to display.

>**TIP**
>As filter fields are always optional, there is no need to override the
>filter form class to configure the fields to be displayed.

### ~`class`~

*Default value*: The model class name suffixed by `FormFilter`

The `class` option defines the form class to use for the `filter` form.

>**TIP**
>To completely remove the filtering feature, set the `class` to `false`.

`form`
------

The `form` section only exists as a fallback for the `edit` and `new` sections
(see the inheritance rules in the introduction).

>**NOTE**
>For form sections (`form`, `edit`, and `new`), the `label` and `help` options
>override the ones defined in the form classes.

### ~`display`~

*Default value*: All fields defined in the form class, in the order of their
definition

The `display` option defines the ordered list of fields to display.

This option can also be used to arrange fields into groups:

    [yml]
    # apps/backend/modules/model/config/generator.yml
    config:
      form:
        display:
          Content: [title, body, author]
          Admin:   [is_published, expires_at]

The above configuration defines two groups (`Content` and `Admin`), each
containing a subset of the form fields.

>**CAUTION**
>All the fields defined in the model form must be present in the `display`
>option. If not, it could lead to unexpected validation errors.

### ~`class`~

*Default value*: The model class name suffixed by `Form`

The `class` option defines the form class to use for the `edit` and `new`
pages.

>**TIP**
>Even though you can define a `class` option in both the `new` and `edit`
>sections, it is better to use one class and take care of the differences
>using conditional logic.

`edit`
------

The `edit` section takes the same options as the `form` section.

### ~`title`~

*Default*: "Edit " suffixed by the humanized model class name

The `title` option defines the title heading of the edit page. It can contain
model object placeholders.

### ~`actions`~

*Default value*: `{ _delete: ~, _list: ~, _save: ~ }`

The `actions` option defines actions available when submitting the form.

`new`
-----

The `new` section takes the same options as the `form` section.

### ~`title`~

*Default*: "New " suffixed by the humanized model class name

The `title` option defines the title of the new page. It can contain model
object placeholders.

>**TIP**
>Even if the object is new, it can have default values you want to
>output as part of the title.

### ~`actions`~

*Default value*: `{ _delete: ~, _list: ~, _save: ~, _save_and_add: ~ }`

The `actions` option defines actions available when submitting the form.
