Taking Advantage of Doctrine Table Inheritance
==============================================

*by Hugo Hamon*

As of symfony 1.3 ~Doctrine~ has officially become the default ORM library
while Propel's development has slowed down over the last few months. The ~Propel~ project is still supported and continues to be improved thanks to the efforts of symfony community members.

The Doctrine 1.2 project became the new default symfony ORM library both because
it is easier to use than Propel and because it bundles a lot of great features
including behaviors, easy DQL queries, migrations and table inheritance.

This chapter describes what table inheritance is and how it is now
fully integrated in symfony 1.3. Thanks to a real-world example, this chapter
will illustrate how to leverage Doctrine table inheritance to make code more
flexible and better organized.

Doctrine Table Inheritance
--------------------------

Though not really known and used by many developers, table inheritance is
probably one of the most interesting features of Doctrine. Table inheritance allows the developer to create database tables that inherit from each other in the same way that classes inherit in an object oriented programming language.
Table inheritance provides an easy way to share data between two or more tables
in a single super table. Look at the diagram below to better understand the table inheritance principle.

![Doctrine table inheritance schema](http://www.symfony-project.org/images/more-with-symfony/01_table_inheritance.png "Doctrine table inheritance principle")

Doctrine provides three different strategies to manage table inheritances
depending on the application's needs (performance, atomicity, simplicity...):
__simple__, __column aggregation__ and __concrete__ table inheritance. While all
of these strategies are described in the
[Doctrine book](http://www.doctrine-project.org/documentation/1_2/en), some
further explanation will help to better understand each option and in which
circumstances they are useful.

### The Simple Table Inheritance Strategy

The simple table inheritance strategy is the simplest of all as it stores all
columns, including children tables columns, in the super parent table. If the
model schema looks like the following YAML code, Doctrine will generate one
single table `Person`, which includes both the `Professor` and `Student` tables'
columns.

    [yml]
    Person:
      columns:
        first_name:
          type:           string(50)
          notnull:        true
        last_name:
          type:           string(50)
          notnull:        true

    Professor:
      inheritance:
        type:             simple
        extends:          Person
      columns:
        specialty:
          type:           string(50)
          notnull:        true

    Student:
      inheritance:
        type:             simple
        extends:          Person
      columns:
        graduation:
          type:           string(20)
          notnull:        true
        promotion:
          type:           integer(4)
          notnull:        true


With the simple inheritance strategy, the extra columns `specialty`, `graduation` and `promotion` are automatically registered at the top level in the `Person` model even if Doctrine generates one model class for both `Student` and `Professor` tables.

![Simple table inheritance schema](http://www.symfony-project.org/images/more-with-symfony/02_simple_tables_inheritance.png "Doctrine simple inheritance principle")

This strategy has an important drawback as the super parent table `Person` does
not provide any column to identify each record's type. In other words, there is no way to retrieve only `Professor` or `Student` objects. The following Doctrine
statement returns a `Doctrine_Collection` of all table records (`Student` and
`Professor` records).

    [php]
    $professors = Doctrine_Core::getTable('Professor')->findAll();

The simple table inheritance strategy is not really useful in real world
examples as there is generally the need to select and hydrate objects of
a specific type. Consequently, it won't be used further in this chapter.

### The Column Aggregation Table Inheritance Strategy

The column aggregation table inheritance strategy is similar to the simple inheritance strategy except that it includes a `type` column to identify the different record types. Consequently, when a record is persisted to the database, a type value is added to it in order to store the class to which it belongs.

    [yml]
    Person:
      columns:
        first_name:
          type:           string(50)
          notnull:        true
        last_name:
          type:           string(50)
          notnull:        true

    Professor:
      inheritance:
        type:             column_aggregation
        extends:          Person
        keyField:         type
        keyValue:         1
      columns:
        specialty:
          type:           string(50)
          notnull:        true

    Student:
      inheritance:
        type:             column_aggregation
        extends:          Person
        keyField:         type
        keyValue:         2
      columns:
        graduation:
          type:           string(20)
          notnull:        true
        promotion:
          type:           integer(4)
          notnull:        true

In the above YAML schema, the inheritance type has been changed to
~`column_aggregation`~ and two new attributes have been added. The first
attribute, `keyField`, specifies the column that will be created to store the
type information for each record. The `keyField` is a string column
named `type`, which is the default column name if no `keyField` is specified.
The second attribute defines the type value for each record that belong to the
`Professor` or `Student` classes.

![Column aggregation table inheritance schema](http://www.symfony-project.org/images/more-with-symfony/03_columns_aggregation_tables_inheritance.png "Doctrine column aggregation inheritance principle")

The column aggregation strategy is a good method for table inheritance as it
creates one single table (`Person`) containing all defined fields plus the `type` field. Consequently, there is no need to make several tables and join them with an SQL query. Below are some examples of how to query tables and which type of results will be returned:

    [php]
    // Returns a Doctrine_Collection of Professor objects
    $professors = Doctrine_Core::getTable('Professor')->findAll();

    // Returns a Doctrine_Collection of Student objects
    $students = Doctrine_Core::getTable('Student')->findAll();

    // Returns a Professor object
    $professor = Doctrine_Core::getTable('Professor')->findOneBySpeciality('physics');

    // Returns a Student object
    $student = Doctrine_Core::getTable('Student')->find(42);

    // Returns a Student object
    $student = Doctrine_Core::getTable('Person')->findOneByIdAndType(array(42, 2));

When performing data retrieval from a subclass (`Professor`, `Student`),
Doctrine will automatically append the SQL `WHERE` clause to the query on the
`type` column with the corresponding value.

However, there are some drawbacks to using the column aggregation strategy in
certain cases. First, column aggregation prevents each sub-table's fields from
being set as `required`. Depending on how many fields there are, the `Person`
table may contain records with several empty values.

The second drawback relates to the number of sub-tables and fields. If the schema declares a lot of sub-tables, which in turn declare a lot of fields, the
final super table will consist of a very large number of columns. Consequently,
the table may be more difficult to maintain.

### The Concrete Table Inheritance Strategy

The concrete table inheritance strategy is a good compromise between the
advantages of the column aggregation strategy, performance and maintainability.
Indeed, this strategy creates independent tables for each subclass containing
all columns: both the shared columns and the model's independent columns.

    [yml]
    Person:
      columns:
        first_name:
          type:           string(50)
          notnull:        true
        last_name:
          type:           string(50)
          notnull:        true

    Professor:
      inheritance:
        type:             concrete
        extends:          Person
      columns:
        specialty:
          type:           string(50)
          notnull:        true

    Student:
      inheritance:
        type:             concrete
        extends:          Person
      columns:
        graduation:
          type:           string(20)
          notnull:        true
        promotion:
          type:           integer(4)
          notnull:        true

So, for the previous schema, the generated `Professor` table will contain the
following set of fields : `id`, `first_name`, `last_name` and `specialty`.

![Concrete table inheritance schema](http://www.symfony-project.org/images/more-with-symfony/04_concrete_tables_inheritance.png "Doctrine concrete inheritance principle")

This approach has several advantages against previous strategies. The first one
is that all tables are isolated and remain independent of each other. Additionally, there are no more blank fields and the extra `type` column is not
included. The result is that each table is lighter and isolated from the other tables.

>**NOTE**
>The fact that shared fields are duplicated in sub-tables is a gain for
>performance and scalability as Doctrine does not need to make an automatic
>SQL join on a super table to retrieve shared data belonging to a sub-tables
>record.

The only two drawbacks of the concrete table inheritance strategy are the
shared fields duplication (though duplication is generally the key for performance) and the fact that the generated super table will always be empty. Indeed, Doctrine has generated a `Person` table though it won't be filled or referenced by any query. No query will be performed on that table as everything is stored in subtables.

We just took the time to introduce the three Doctrine table inheritance
strategies but we've not yet tried them in a real world example with symfony.
The following part of this chapter explains how to take advantage of the Doctrine table inheritance in symfony 1.3, particularly within the model and the form framework.

Symfony Integration of Table Inheritance
-----------------------------------------

Before symfony 1.3, Doctrine table inheritance wasn't fully supported by the
framework as form and filter classes didn't correctly inherit from the base class. Consequently, developers who needed to use table inheritance were
forced to tweak forms and filters and were obliged to override lots of
methods to retrieve the inheritance behavior.

Thanks to community feedback, the symfony core team has improved the forms and
filters in order to easily and fully support Doctrine table inheritance
in symfony 1.3.

The remainder of this chapter will explain how to use Doctrine's table
inheritance and how to take advantage of it in several situations including in the models, forms, filters and admin generators. Real case study examples will help us to better understand how inheritance works with symfony so that you can
easily use it for your own needs.

### Introducing the Real World Case Studies

Throughout this chapter, several real world case studies will be presented to
expose the many advantages of the Doctrine table inheritance approach at several
levels: in `models`, `forms`, `filters` and the `admin generator`.

The first example comes from an application developed at Sensio
for a well known French company. It shows how Doctrine table inheritance is a
good solution to manage a dozen identical referential sets of data in order to
share methods and properties and avoid code duplication.

The second example shows how to take advantage of the concrete table inheritance strategy with forms by creating a simple model to manage digital files.

Finally, the third example will demonstrate how to take advantage of the table
inheritance with the Admin Generator, and how to make it more flexible.

### Table Inheritance at the Model Layer

Similar to the Object Oriented Programming concept, table inheritance
encourages data sharing. Consequently, it allows for the sharing of properties
and methods when dealing with generated models. Doctrine table inheritance
is a good way to share and override actions callable on inherited objects. Let's
explain this concept with a real world example.

#### The Problem ####

Lots of web applications require "referential" data in order to function. A
referential is generally a small set of data represented by a simple table
containing at least two fields (e.g. `id` and `label`). In some cases, however,
the referential contains extra data such as an `is_active` or `is_default` flag.
This was the case recently at Sensio with a customer application.

The customer wanted to manage a large set of data, which drove the main
forms and views of the application. All of these referential tables were built
around the same basic model: `id`, `label`, `position` and `is_default`. The
`position` field helps to rank records thanks to an ajax drag and drop
functionality. The `is_default` field represents a flag that indicates whether
or not a record should to be set as "selected" by default when it feeds an
HTML select dropdown box.

#### The Solution ####

Managing more than two equal tables is one of the best problems to solve with
table inheritance. In the above problem, concrete table inheritance
was selected to fit the needs and to share each object's methods in a
single class. Let's have a look at the following simplified schema, which
illustrates the problem.

    [yml]
    sfReferential:
      columns:
        id:
          type:        integer(2)
          notnull:     true
        label:
          type:        string(45)
          notnull:     true
        position:
          type:        integer(2)
          notnull:     true
        is_default:
          type:        boolean
          notnull:     true
          default:     false

    sfReferentialContractType:
      inheritance:
        type:          concrete
        extends:       sfReferential

    sfReferentialProductType:
      inheritance:
        type:          concrete
        extends:       sfReferential

Concrete table inheritance works perfectly here as it provides separate and
isolated tables, and because the `position` field must be managed for records
that share the same type.

Build the model and see what happens. Doctrine and symfony have generated
three SQL tables and six model classes in the `lib/model/doctrine` directory:

  * `sfReferential`: manages the `sf_referential` records,
  * `sfReferentialTable`: manages the `sf_referential` table,
  * `sfReferentialContractType`: manages the `sf_referential_contract_type`
    records.
  * `sfReferentialContractTypeTable`: manages the `sf_referential_contract_type`
    table.
  * `sfReferentialProductType`: manages the `sf_referential_product_type`
    records.
  * `sfReferentialProductTypeTable`: manages the `sf_referential_product_type`
    table.

Exploring the generated inheritance shows that both base classes of the
`sfReferentialContractType` and `sfReferentialProductType` model classes inherit
from the `sfReferential` class. So, all protected and public methods (including
properties) placed in the `sfReferential` class will be shared amongst the two
subclasses and can be overridden if necessary.

That's exactly the expected goal. The `sfReferential` class can now contain
methods to manage all referential data, for example:

    [php]
    // lib/model/doctrine/sfReferential.class.php
    class sfReferential extends BasesfReferential
    {
      public function promote()
      {
        // move up the record in the list
      }

      public function demote()
      {
        // move down the record in the list
      }

      public function moveToFirstPosition()
      {
        // move the record to the first position
      }

      public function moveToLastPosition()
      {
        // move the record to the last position
      }

      public function moveToPosition($position)
      {
        // move the record to a given position
      }

      public function makeDefault($forceSave = true, $conn = null)
      {
        $this->setIsDefault(true);

        if ($forceSave)
        {
          $this->save($conn);
        }
      }
    }

Thanks to the Doctrine concrete table inheritance, all the code is shared in
the same place. Code becomes easier to debug, maintain, improve and unit test.

That's the first real advantage when dealing with table inheritance. Additionally, thanks to this approach, model objects can be used to centralize actions code as shown below. The `sfBaseReferentialActions` is a special actions class inherited by each actions class that manages a referential model.

    [php]
    // lib/actions/sfBaseReferentialActions.class.php
    class sfBaseReferentialActions extends sfActions
    {
      /**
       * Ajax action that saves the new position as a result of the user
       * using a drag and drop in the list view.
       *
       * This action is linked thanks to an ~sfDoctrineRoute~ that
       * eases single referential object retrieval.
       *
       * @param sfWebRequest $request
       */
      public function executeMoveToPosition(sfWebRequest $request)
      {
        $this->forward404Unless($request->isXmlHttpRequest());

        $referential = $this->getRoute()->getObject();

        $referential->moveToPosition($request->getParameter('position', 1));

        return sfView::NONE;
      }
    }

What would happen if the schema did not use table inheritance? The code would
need to be duplicated in each referential subclass. This approach wouldn't be DRY, (Don't Repeat Yourself) especially for an application with a dozen referential tables.

### Table Inheritance at the Forms Layer ###

Let's continue the guided tour of Doctrine table inheritance's advantages.
The previous section demonstrated how this feature can be really useful to
share methods and properties between several inherited models. Let's have a look
now at how it behaves when dealing with symfony generated forms.

#### The Study Case's Model ####

The YAML schema below describes a model to manage digital documents. The aim is
to store generic information in the `File` table and specific data in sub-tables
like `Video` and `PDF`.

    [yml]
    File:
      columns:
        filename:
          type:            string(50)
          notnull:         true
        mime_type:
          type:            string(50)
          notnull:         true
        description:
          type:            clob
          notnull:         true
        size:
          type:            integer(8)
          notnull:         true
          default:         0

    Video:
      inheritance:
        type:              concrete
        extends:           File
      columns:
        format:
          type:            string(30)
          notnull:         true
        duration:
          type:            integer(8)
          notnull:         true
          default:         0
        encoding:
          type:            string(50)

    PDF:
      tableName:           pdf
      inheritance:
        type:              concrete
        extends:           File
      columns:
        pages:
          type:            integer(8)
          notnull:         true
          default:         0
        paper_size:
          type:            string(30)
        orientation:
          type:            enum
          default:         portrait
          values:          [portrait, landscape]
        is_encrypted:
          type:            boolean
          default:         false
          notnull:         true

Both the `PDF` and `Video` tables share the same `File` table, which contains global information about digital files. The `Video` model encapsulates data related to video objects such as `format` (4/3, 16/9...) or `duration`, whereas the `PDF` model contains the number of `pages` or the document's `orientation`. Let's build this model and generate the corresponding forms.

    $ php symfony doctrine:build --all

The following section describes how to take advantage of the table inheritance
in form classes thanks to the new ~`setupInheritance()`~ method.

#### Discover the ~setupInheritance()~ method ###

As expected, Doctrine has generated six form classes in the `lib/form/doctrine` and `lib/form/doctrine/base` directories:

  * `BaseFileForm`
  * `BaseVideoForm`
  * `BasePDFForm`

  * `FileForm`
  * `VideoForm`
  * `PDFForm`

Let's open the three `Base` form classes to discover something new in the
~`setup()`~ method. A new ~`setupInheritance()`~ method has been added for
symfony 1.3. This method is empty by default.

The most important thing to notice is that the form inheritance is preserved as
`BaseVideoForm` and `BasePDFForm` both extend the `FileForm` and `BaseFileForm`
classes. Consequently, each inherits from the `File` class and can share the same base methods.

The following listing overrides the `setupInheritance()` method and configures
the `FileForm` class so that it can be used in either subform more effectively.

    [php]
    // lib/form/doctrine/FileForm.class.php
    class FileForm extends BaseFileForm
    {
      protected function setupInheritance()
      {
        parent::setupInheritance();

        $this->useFields(array('filename', 'description'));

        $this->widgetSchema['filename']    = new sfWidgetFormInputFile();
        $this->validatorSchema['filename'] = new sfValidatorFile(array(
          'path' => sfConfig::get('sf_upload_dir')
        ));
      }
    }

The `setupInheritance()` method, which is called by both the `VideoForm` and
`PDFForm` subclasses, removes all fields except `filename` and `description`.
The `filename` field's widget has been turned into a file widget and its
corresponding validator has been changed to an ~`sfValidatorFile`~ validator.
This way, the user will be able to upload a file and save it to the server.

![Customizing inherited forms with the setupInheritance() method](http://www.symfony-project.org/images/more-with-symfony/05_table_inheritance_forms.png "Doctrine table inheritance with forms")

#### Set the Current File's Mime Type and Size

All the forms are now ready and customized. There is one more thing to configure, however, before being able to use them. As the `mime_type` and `size` fields have been removed from the `FileForm` object, they must be set programmatically. The best place to do this is in a new `generateFilenameFilename()` method in the `File` class.

    [php]
    // lib/model/doctrine/File.class.php
    class File extends BaseFile
    {
      /**
       * Generates a filename for the current file object.
       *
       * @param sfValidatedFile $file
       * @return string
       */
      public function generateFilenameFilename(sfValidatedFile $file)
      {
        $this->setMimeType($file->getType());
        $this->setSize($file->getSize());

        return $file->generateFilename();
      }
    }

This new method aims to generate a custom filename for the file to store on the
file system. Although the `generateFilenameFilename()` method returns a default
auto-generated filename, it also sets the `mime_type` and `size` properties on the fly thanks to the ~`sfValidatedFile`~ object passed as its first argument.

As symfony 1.3 entirely supports Doctrine table inheritance, forms are
now able to save an object and its inherited values. The native inheritance
support allows for powerful and functional forms with very few chunks of
customized code.

The above example could be widely and easily improved thanks to class
inheritance. For example, both the `VideoForm` and `PDFForm` classes could
override the `filename` validator to a more specific custom validator such
as `sfValidatorVideo` or `sfValidatorPDF`.

### Table Inheritance at the Filters Layer ###

Because filters are also forms, they too inherit the methods and properties of
parent form filters. Consequently, the `VideoFormFilter` and `PDFFormFilter`
objects extend the `FileFormFilter` class and can be customized by using
the ~`setupInheritance()`~ method.

In the same way, both `VideoFormFilter` and `PDFFormFilter` can share the same
custom methods in the `FileFormFilter` class.

### Table Inheritance at the Admin Generator Layer ###

It's now time to discover how to take advantage of Doctrine table inheritance
as well as one of the Admin Generator's new features: the __actions base class__
definition. The Admin Generator is one of the most improved features of
symfony since the 1.0 version.

In November 2008, symfony introduced the new Admin Generator system bundled with
version 1.2. This tool comes with a lot of functionality out of the box such as basic CRUD operations, list filtering and paging, batch deleting and so on... The Admin Generator is a powerful tool, which eases and accelerates backend generation and customization for any developer.

#### Practical Example Introduction

The aim of the last part of this chapter is to illustrate how to take advantage of the Doctrine table inheritance coupled with the Admin Generator. To achieve this, a simple backend area will be constructed to manage two tables, which both contain data that can be sorted / prioritized.

As symfony's mantra is to not reinvent the wheel every time, the Doctrine model
will use the [csDoctrineActAsSortablePlugin](http://www.symfony-project.org/plugins/csDoctrineActAsSortablePlugin "csDoctrineActAsSortablePlugin plugin page")
to provide all the needed API to sort objects between each other. The
~`csDoctrineActAsSortablePlugin`~ plugin is developed and maintained by
CentreSource, one of the most active companies in the symfony ecosystem.

The data model is quite simple. There are three model classes, `sfItem`,
`sfTodoItem` and `sfShoppingItem`, which help to manage a todo list and a
shopping list. Each item in both lists is sortable to allow items to be
prioritize within the list.

    [yml]
    sfItem:
      actAs:             [Timestampable]
      columns:
        name:
          type:          string(50)
          notnull:       true

    sfTodoItem:
      actAs:             [Sortable]
      inheritance:
        type:            concrete
        extends:         sfItem
      columns:
        priority:
          type:          string(20)
          notnull:       true
          default:       minor
        assigned_to:
          type:          string(30)
          notnull:       true
          default:       me

    sfShoppingItem:
      actAs:             [Sortable]
      inheritance:
        type:            concrete
        extends:         sfItem
      columns:
        quantity:
          type:          integer(3)
          notnull:       true
          default:       1

The above schema describes the data model split in three model classes. The two
children classes (`sfTodoItem`, `sfShoppingItem`) both use the `Sortable` and
`Timestampable` behaviors. The `Sortable` behavior is provided by the
`csDoctrineActAsSortablePlugin` plugin and adds a integer `position` column to
each table. Both classes extend the `sfItem` base class. This class contains an
`id` and `name` column.

Let's add some data fixtures so that we have some test data to play
with inside the backend. The data fixtures are, as usual, located in the
`data/fixtures.yml` file of the symfony project.

    [yml]
    sfTodoItem:
      sfTodoItem_1:
        name:           "Write a new symfony book"
        priority:       "medium"
        assigned_to:    "Fabien Potencier"
      sfTodoItem_2:
        name:           "Release Doctrine 2.0"
        priority:       "minor"
        assigned_to:    "Jonathan Wage"
      sfTodoItem_3:
        name:           "Release symfony 1.4"
        priority:       "major"
        assigned_to:    "Kris Wallsmith"
      sfTodoItem_4:
        name:           "Document Lime 2 Core API"
        priority:       "medium"
        assigned_to:    "Bernard Schussek"

    sfShoppingItem:
      sfShoppingItem_1:
        name:           "Apple MacBook Pro 15.4 inches"
        quantity:       3
      sfShoppingItem_2:
        name:           "External Hard Drive 320 GB"
        quantity:       5
      sfShoppingItem_3:
        name:           "USB Keyboards"
        quantity:       2
      sfShoppingItem_4:
        name:           "Laser Printer"
        quantity:       1

Once the `csDoctrineActAsSortablePlugin` plugin is installed and the data
model is ready, the new plugin needs to be activated in the ~`ProjectConfiguration`~ class
located in `config/ProjectConfiguration.class.php`:

    [php]
    class ProjectConfiguration extends sfProjectConfiguration
    {
      public function setup()
      {
        $this->enablePlugins(array(
          'sfDoctrinePlugin',
          'csDoctrineActAsSortablePlugin'
        ));
      }
    }

Next, the database, model, forms and filters can be generated and
the fixtures loaded into the database to feed the newly created tables.
This can be accomplished at once thanks to the ~`doctrine:build`~ task:

    $ php symfony doctrine:build --all --no-confirmation

The symfony cache must be cleared to complete the process and the plugin's assets have to be linked inside the `web` directory:

    $ php symfony cache:clear
    $ php symfony plugin:publish-assets

The following section explains how to build the backend modules with the
Admin Generator tools and how to benefit from the new actions base class feature.

#### Setup The Backend

This section describes the steps required to setup a new backend application
containing two generated modules that manage both the shopping and todo lists.
Consequently, the first thing to do is to generate a `backend` application to
house the coming modules:

    $ php symfony generate:app backend

Even though the Admin Generator is a great tool, prior to symfony 1.3, the
developer was forced to duplicate common code between generated modules. Now, however, the ~`doctrine:generate-admin`~ task introduces a new ~`--actions-base-class`~ option that allows the developer to define the module's base actions class.

As the two modules are quiet similar, they will certainly need to share some
generic actions code. This code can be located in a super actions class located
in the `lib/actions` directory as shown in the code below:

    [php]
    // lib/actions/sfSortableModuleActions.class.php
    class sfSortableModuleActions extends sfActions
    {

    }

Once the new `sfSortableModuleActions` class is created and the cache has
been cleared, the two modules can be generated in the backend application:

    $ php symfony doctrine:generate-admin --module=shopping --actions-base-class=sfSortableModuleActions backend sfShoppingItem

-

    $ php symfony doctrine:generate-admin --module=todo --actions-base-class=sfSortableModuleActions backend sfTodoItem

The Admin Generator generates modules in two separate directories. The
first directory is, of course, `apps/backend/modules`. The majority of the
generated module files, however, are located in the `cache/backend/dev/modules`
directory. Files located in this location are regenerated each time the cache
is cleared or when the module's configuration changes.

>**Note**
>Browsing the cached files is a great way to understand how symfony and the
>Admin Generator work together under the hood. Consequently, the
>new `sfSortableModuleActions` subclasses can be found in
>`cache/backend/dev/modules/autoShopping/actions/actions.class.php` and
>`cache/backend/dev/modules/autoTodo/actions/actions.class.php`. By default,
>symfony would generate these classes to inherit directly from ~`sfActions`~.

![Default todo list backend](http://www.symfony-project.org/images/more-with-symfony/06_table_inheritance_backoffice_todo_1.png "Todo list default backend")

![Default shopping list backend](http://www.symfony-project.org/images/more-with-symfony/07_table_inheritance_backoffice_shopping_1.png "Shopping list default backend")

The two backend modules are ready to be used and customized. It's not the goal
of this chapter, however, to explore the configuration of auto generated modules. Significant documentation exists on this topic, including in the
[symfony Reference Book](http://www.symfony-project.org/reference/1_3/en/06-Admin-Generator).

#### Changing an Item's Position

The previous section described how to setup two fully functional backend modules, which both inherit from the same actions class. The next goal is to create a shared action, which allows the developer to sort objects from a list between each other. This is quite easy as the installed plugin provides a full API to handle the resorting of the objects.

The first step is to create two new routes capable of moving a record up
or down in the list. As the Admin Generator uses the ~`sfDoctrineRouteCollection`~ route, new routes can be easily declared and attached to the collection via the `config/generator.yml` of both modules:

    [yml]
    # apps/backend/modules/shopping/config/generator.yml
    generator:
      class: sfDoctrineGenerator
      param:
        model_class:           sfShoppingItem
        theme:                 admin
        non_verbose_templates: true
        with_show:             false
        singular:              ~
        plural:                ~
        route_prefix:          sf_shopping_item
        with_doctrine_route:   true
        actions_base_class:    sfSortableModuleActions

        config:
          actions: ~
          fields:  ~
          list:
            max_per_page:      100
            sort:              [position, asc]
            display:           [position, name, quantity]
            object_actions:
              moveUp:          { label: "move up", action: "moveUp" }
              moveDown:        { label: "move down", action: "moveDown" }
              _edit:      ~
              _delete:    ~
          filter:  ~
          form:    ~
          edit:    ~
          new:     ~

Changes need to be repeated for the `todo` module:

    [yml]
    # apps/backend/modules/todo/config/generator.yml
    generator:
      class: sfDoctrineGenerator
      param:
        model_class:           sfTodoItem
        theme:                 admin
        non_verbose_templates: true
        with_show:             false
        singular:              ~
        plural:                ~
        route_prefix:          sf_todo_item
        with_doctrine_route:   true
        actions_base_class:    sfSortableModuleActions

        config:
          actions: ~
          fields:  ~
          list:
            max_per_page:      100
            sort:              [position, asc]
            display:           [position, name, priority, assigned_to]
            object_actions:
              moveUp:          { label: "move up", action: "moveUp" }
              moveDown:        { label: "move down", action: "moveDown" }
              _edit:      ~
              _delete:    ~
          filter:  ~
          form:    ~
          edit:    ~
          new:     ~


The two YAML files describe the configuration for both `shopping` and `todo`
modules. Each of these has been customized to fit the end user's needs. First, the list view is ordered on the `position` column with an `ascending` ordering. Next, the number of max items per page has been increased to 100 to avoid pagination.

Finally, the number of displayed columns has been reduced to the `position`, `name`, `priority`, `assigned_to` and `quantity` columns. Additionally, each module has two new actions: `moveUp` and `moveDown`. The final rendering should look like the following screenshots:

![Customized todo list backend](http://www.symfony-project.org/images/more-with-symfony/09_table_inheritance_backoffice_todo_2.png "Todo list custom backend")

![Customized shopping list backend](http://www.symfony-project.org/images/more-with-symfony/08_table_inheritance_backoffice_shopping_2.png "Shopping list custom backend")

These two new actions have been declared but for now don't do anything. Each
must be created in the shared actions class, `sfSortableModuleActions` as described below. The ~`csDoctrineActAsSortablePlugin`~ plugin provides two extra useful methods on each model class: `promote()` and `demote()`. Each is used to build the `moveUp` and `moveDown` actions.

    [php]
    // lib/actions/sfSortableModuleActions.class.php
    class sfSortableModuleActions extends sfActions
    {
      /**
       * Moves an item up in the list.
       *
       * @param sfWebRequest $request
       */
      public function executeMoveUp(sfWebRequest $request)
      {
        $this->item = $this->getRoute()->getObject();

        $this->item->promote();

        $this->redirect($this->getModuleName());
      }

      /**
       * Moves an item down in the list.
       *
       * @param sfWebRequest $request
       */
      public function executeMoveDown(sfWebRequest $request)
      {
        $this->item = $this->getRoute()->getObject();

        $this->item->demote();

        $this->redirect($this->getModuleName());
      }
    }

Thanks to these two shared actions, both the todo list and the shopping list are
sortable. Moreover, they are easy to maintain and test with functional tests.
Feel free to improve the look and feel of both modules by overriding the object's actions template to remove the first `move up` link and the last `move down` link.

#### Special Gift: Improve the User's Experience

Before finishing, let's polish the two lists to improve the user's experience.
Everybody agrees that moving a record up (or down) by clicking a link is not
really intuitive for the end user. A better approach is definitively to include
JavaScript ajax behaviors. In this case, all HTML table rows will be draggable
and droppable thanks to the `Table Drag and Drop` jQuery plugin. An ajax call will be performed whenever the user moves a row in the HTML table.

First grab and install the jQuery framework under the `web/js` directory and then repeat the operation for the `Table Drag and Drop` plugin, whose source code is hosted on a [Google Code](http://code.google.com/p/tablednd/) repository.

To work, the list view of each module must include a little JavaScript
snippet and both tables need an `id` attribute. As all admin generator templates
and partials can be overridden, the `_list.php` file, located in the cache by
default, should be copied to both modules.

But wait, copying the `_list.php` file under the `templates/` directory of each
module is not really DRY. Just copy the `cache/backend/dev/modules/autoShopping/templates/_list.php`
file to the `apps/backend/templates/` directory and rename it `_table.php`.
Replace its current content with the following code:

    [php]
    <div class="sf_admin_list">
      <?php if (!$pager->getNbResults()): ?>
        <p><?php echo __('No result', array(), 'sf_admin') ?></p>
      <?php else: ?>
        <table cellspacing="0" id="sf_item_table">
          <thead>
            <tr>
              <th id="sf_admin_list_batch_actions"><input id="sf_admin_list_batch_checkbox" type="checkbox" onclick="checkAll();" /></th>
              <?php include_partial(
                $sf_request->getParameter('module').'/list_th_tabular',
                array('sort' => $sort)
              ) ?>
              <th id="sf_admin_list_th_actions">
                <?php echo __('Actions', array(), 'sf_admin') ?>
              </th>
            </tr>
          </thead>
          <tfoot>
            <tr>
              <th colspan="<?php echo $colspan ?>">
                <?php if ($pager->haveToPaginate()): ?>
                  <?php include_partial(
                    $sf_request->getParameter('module').'/pagination',
                    array('pager' => $pager)
                  ) ?>
                <?php endif; ?>
                <?php echo format_number_choice(
                  '[0] no result|[1] 1 result|(1,+Inf] %1% results', 
                  array('%1%' => $pager->getNbResults()),
                  $pager->getNbResults(), 'sf_admin'
                ) ?>
                <?php if ($pager->haveToPaginate()): ?>
                  <?php echo __('(page %%page%%/%%nb_pages%%)', array(
                    '%%page%%' => $pager->getPage(), 
                    '%%nb_pages%%' => $pager->getLastPage()), 
                    'sf_admin'
                  ) ?>
                <?php endif; ?>
              </th>
            </tr>
          </tfoot>
          <tbody>
          <?php foreach ($pager->getResults() as $i => $item): ?>
            <?php $odd = fmod(++$i, 2) ? 'odd' : 'even' ?>
            <tr class="sf_admin_row <?php echo $odd ?>">
              <?php include_partial(
                $sf_request->getParameter('module').'/list_td_batch_actions',
                array(
                  'sf_'. $sf_request->getParameter('module') .'_item' => $item,
                  'helper' => $helper
              )) ?>
              <?php include_partial(
                $sf_request->getParameter('module').'/list_td_tabular', 
                array(
                  'sf_'. $sf_request->getParameter('module') .'_item' => $item
              )) ?>
                <?php include_partial(
                  $sf_request->getParameter('module').'/list_td_actions',
                  array(
                    'sf_'. $sf_request->getParameter('module') .'_item' => $item, 
                    'helper' => $helper
                )) ?>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      <?php endif; ?>
      </div>
      <script type="text/javascript">
        /* <![CDATA[ */
        function checkAll() {
          var boxes = document.getElementsByTagName('input'); 
          for (var index = 0; index < boxes.length; index++) { 
            box = boxes[index]; 
            if (
              box.type == 'checkbox' 
              && 
              box.className == 'sf_admin_batch_checkbox'
            ) 
            box.checked = document.getElementById('sf_admin_list_batch_checkbox').checked 
          }
          return true;
        }
        /* ]]> */
      </script>

Finally, create a `_list.php` file inside each module's `templates` directory,
and place the following code in each:

    [php]
    // apps/backend/modules/shopping/templates/_list.php
    <?php include_partial('global/table', array(
      'pager' => $pager,
      'helper' => $helper,
      'sort' => $sort,
      'colspan' => 5
    )) ?>
    
-

    // apps/backend/modules/shopping/templates/_list.php
    <?php include_partial('global/table', array(
      'pager' => $pager,
      'helper' => $helper,
      'sort' => $sort,
      'colspan' => 8
    )) ?>

To change the position of a row, both modules need to implement a new action
that processes the coming ajax request. As seen before, the new shared
`executeMove()` action will be placed in the `sfSortableModuleActions` actions
class:

    [php]
    // lib/actions/sfSortableModuleActions.class.php
    class sfSortableModuleActions extends sfActions
    {
      /**
       * Performs the Ajax request, moves an item to a new position.
       *
       * @param sfWebRequest $request
       */
      public function executeMove(sfWebRequest $request)
      {
        $this->forward404Unless($request->isXmlHttpRequest());
        $this->forward404Unless($item = Doctrine_Core::getTable($this->configuration->getModel())->find($request->getParameter('id')));

        $item->moveToPosition((int) $request->getParameter('rank', 1));

        return sfView::NONE;
      }
    }

The `executeMove()` action requires a `getModel()` method on the configuration
object. Implement this new method in both the `todoGeneratorConfiguration` and
`shoppingGeneratorConfiguration` classes as shown below:

    [php]
    // apps/backend/modules/shopping/lib/shoppingGeneratorConfiguration.class.php
    class shoppingGeneratorConfiguration extends BaseShoppingGeneratorConfiguration
    {
      public function getModel()
      {
        return 'sfShoppingItem';
      }
    }

-

    // apps/backend/modules/todo/lib/todoGeneratorConfiguration.class.php
    class todoGeneratorConfiguration extends BaseTodoGeneratorConfiguration
    {
      public function getModel()
      {
        return 'sfTodoItem';
      }
    }

There is one last operation to do. For now, the tables rows are not draggable and no ajax call is performed when a moved row is released. To achieve this, both modules need a specific route to access their corresponding `move` action. Consequently, the `apps/backend/config/routing.yml` file needs the following two new routes as shown below:

    [php]
    <?php foreach (array('shopping', 'todo') as $module) : ?>

    <?php echo $module ?>_move:
      class: sfRequestRoute
      url: /<?php echo $module ?>/move
      param:
        module: "<?php echo $module ?>"
        action: move
      requirements:
        sf_method: [get]

    <?php endforeach ?>

To avoid code duplication, the two routes are generated inside a `foreach`
statement and are based on the module name to easily retrieve it in the view.
Finally, the `apps/backend/templates/_table.php` must implement the JavaScript
snippet in order to initialize the drag and drop behavior and the corresponding
ajax request:

    [php]
    <script type="text/javascript" charset="utf-8">
      $().ready(function() {
        $("#sf_item_table").tableDnD({
          onDrop: function(table, row) {
            var rows = table.tBodies[0].rows;

            // Get the moved item's id
            var movedId = $(row).find('td input:checkbox').val();

            // Calculate the new row's position
            var pos = 1;
            for (var i = 0; i<rows.length; i++) {
              var cells = rows[i].childNodes;
              // Perform the ajax request for the new position
              if (movedId == $(cells[1]).find('input:checkbox').val()) {
                $.ajax({
                  url:"<?php echo url_for('@'. $sf_request->getParameter('module').'_move') ?>?id="+ movedId +"&rank="+ pos,
                  type:"GET"
                });
                break;
              }
              pos++;
            }
          },
        });
      });
    </script>

The HTML table is now fully functional. Rows are draggable and droppable, and
the new position of a row is automatically saved thanks to an ajax call. With just a few code chunks, the backend's usability has been greatly improved to offer the end user a better experience. The Admin Generator is flexible enough to be extended and customized and works perfectly with Doctrine's table inheritance.

Feel free to improve the two modules by removing the two obsolete `moveUp` and
`moveDown` actions and adding any other customizations that fit your needs.

Final Thoughts
--------------

This chapter described how Doctrine table inheritance is a powerful feature,
which helps the developer code faster and improve code organization. This
Doctrine functionality is fully integrated at several levels in symfony.
Developers are encouraged to take advantage of it to increase efficiency and
promote code organization.
