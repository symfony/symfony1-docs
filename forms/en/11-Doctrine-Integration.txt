Chapter 11 - Doctrine Integration
=================================

In a Web project, most forms are used to create or modify model objects. These 
objects are usually serialized in a database thanks to an ORM. Symfony's form 
system offers an additional layer for interfacing with Doctrine, symfony's 
built-in ORM, making the implementation of forms based on these model objects 
easier.

This chapter goes into detail about how to integrate forms with Doctrine object
models. It is highly recommended that you are already acquainted with Doctrine
and its integration with symfony. If this is not the case, refer to
[The symfony and Doctrine book](http://www.symfony-project.org/doctrine/1_2/).

Before we start
---------------

In this chapter, we will create an article management system. Let's start with the database schema. It is made of five tables: `article`, `author`, `category`, `tag`, and `article_tag`, as Listing 4-1 shows.

Listing 4-1 - Database Schema

    [yml]
    // config/doctrine/schema.yml
    Article:
      actAs: [Sluggable, Timestampable]
      columns:
        title:
          type: string(255)
          notnull: true
        content:
          type: clob
        status: string(255)
        author_id: integer
        category_id: integer
        published_at: timestamp
      relations:
        Author:
          foreignAlias: Articles
        Category:
          foreignAlias: Articles
        Tags:
          class: Tag
          refClass: ArticleTag
          foreignAlias: Articles
    Author:
      columns:
        first_name: string(20)
        last_name: string(20)
        email: string(255)
        active: boolean
    Category:
      columns:
        name: string(255)
    Tag:
      columns:
        name: string(255)
    ArticleTag:
      columns:
        article_id:
          type: integer
          primary: true
        tag_id:
          type: integer
          primary: true
      relations:
        Article:
          onDelete: CASCADE
        Tag:
          onDelete: CASCADE

Here are the relations between the tables:

  * 1-n relation between the `article` table and the `author` table: an article is written by one and only one author
  * 1-n relation between the `article` table and the `category` table: an article belongs to one or zero category
  * n-n relation between the `article` and `tag` tables

Generating Form Classes
-----------------------

We want to edit the information of the `article`, `author`, `category`, and `tag` tables. To do so, we need to create forms linked to each of these tables and configure widgets and validators related to the database schema. Even if it is possible to create these forms manually, it is a long, tedious task, and overall, it forces repetition of the same kind of information in several files (column and field name, maximum size of column and fields, ...). Furthermore, each time we change the model, we will also have to change the related form class. Fortunately, the Doctrine plugin has a built-in task `doctrine:build-forms` that automates this process generating the forms related to the object model:

    $ ./symfony doctrine:build-forms

During the form generation, the task creates one class per table with validators and widgets for each column using introspection of the model and taking into account relations between tables.

>**Note**
>The `doctrine:build-all` and `doctrine:build-all-load` also updates form classes, automatically invoking the `doctrine:build-forms` task.

After executing these tasks, a file structure is created in the `lib/form/` directory. Here are the files created for our example schema:

    lib/
      form/
        doctrine/
          ArticleForm.class.php
          ArticleTagForm.class.php
          AuthorForm.class.php
          CategoryForm.class.php
          TagForm.class.php
          base/
            BaseArticleForm.class.php
            BaseArticleTagForm.class.php
            BaseAuthorForm.class.php
            BaseCategoryForm.class.php
            BaseFormDoctrine.class.php
            BaseTagForm.class.php

The `doctrine:build-forms` task generates two classes for each table of the schema, one base class in the `lib/form/base` directory and one in the `lib/form/` directory. For example, the `author` table, consists of `BaseAuthorForm` and `AuthorForm` classes that were generated in the files `lib/form/base/BaseAuthorForm.class.php` and `lib/form/AuthorForm.class.php`.

Table below sums up the hierarchy among the different classes involved in the `AuthorForm` form definition.

  | **Class**        | **Package**     | **For**       | **Description**
  | ---------------- | --------------- | ------------- | ---------------
  | AuthorForm       | project         | developer     | Overrides generated form
  | BaseAuthorForm   | project         | symfony       | Based on the schema and overridden at each execution of the `doctrine:build-forms` task
  | BaseFormDoctrine | project         | developer     | Allows global Customization of Doctrine forms
  | sfFormDoctrine   | Doctrine plugin | symfony       | Base of Doctrine forms
  | sfForm           | symfony         | symfony       | Base of symfony forms

In order to create or edit an object from the `Author` class, we will use the `AuthorForm` class, described in Listing 4-2. As you can notice, this class does not contain any methods as it inherits from the `BaseAuthorForm` which is generated through the configuration. The `AuthorForm` class is the class we will use to Customize and override the form configuration.

Listing 4-2 - `AuthorForm` Class

    [php]
    class AuthorForm extends BaseAuthorForm
    {
      public function configure()
      {
      }
    }

Listing 4-3 shows the `BaseAuthorForm` class with the validators and widgets generated introspecting the model for the `author` table.

Listing 4-3 - `BaseAuthorForm` Class representing the Form for the `author` table

    [php]
    class BaseAuthorForm extends BaseFormDoctrine
    {
      public function setup()
      {
        $this->setWidgets(array(
          'id'         => new sfWidgetFormInputHidden(),
          'first_name' => new sfWidgetFormInputText(),
          'last_name'  => new sfWidgetFormInputText(),
          'email'      => new sfWidgetFormInputText(),
        ));

        $this->setValidators(array(
          'id'         => new sfValidatorDoctrineChoice(array('model' => 'Author', 'column' => 'id', 'required' => false)),
          'first_name' => new sfValidatorString(array('max_length' => 20, 'required' => false)),
          'last_name'  => new sfValidatorString(array('max_length' => 20, 'required' => false)),
          'email'      => new sfValidatorString(array('max_length' => 255)),
        ));

        $this->widgetSchema->setNameFormat('author[%s]');

        $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

        parent::setup();
      }

      public function getModelName()
      {
        return 'Author';
      }
    }

The generated class looks very similar to the forms we have already created in the previous chapters, except for a few things:

  * The base class is `BaseFormDoctrine` instead of `sfForm`
  * The validator and widget configuration takes place in the `setup()` method, rather than in the `configure()` method
  * The `getModelName()` method returns the Doctrine class related to this form

>**SIDEBAR**
>Global Customization of Doctrine Forms
>
>In addition to the classes generated for each table, the `doctrine:build-forms` also generates a `BaseFormDoctrine` class. This empty class is the base class of every other generated class in the `lib/form/base/` directory and allows to configure the behavior of every Doctrine form globally. For example, it is possible to easily change the default formatter for all Doctrine forms:
>
>     [php]
>     abstract class BaseFormDoctrine extends sfFormDoctrine
>     {
>       public function setup()
>       {
>         sfWidgetFormSchema::setDefaultFormFormatterName('div');
>       }
>     }
>
>You'll notice that the `BaseFormDoctrine` class inherits from the `sfFormDoctrine` class.
>This class incorporates functionality specific to Doctrine and among other things deals with the object serialization in database from the values submitted in the form.

>**TIP**
>Base classes use the `setup()` method for the configuration instead of the `configure()` method. This allows the developer to override the configuration of empty generated classes without handling the `parent::configure()` call.

The form field names are identical to the column names we set in the schema: `id`, `first_name`, `last_name`, and `email`.

For each column of the `author` table, the `doctrine:build-forms` task generates a widget and a validator according to the schema definition. The task always generates the most secure validators possible. Let's consider the `id` field. We could just check if the value is a valid integer. Instead the validator generated here allows us to also validate that the identifier actually exists (to edit an existing object) or that the identifier is empty (so that we could create a new object). This is a stronger validation.

The generated forms can be used immediately. Add a `<?php echo $form ?>` statement, and this will allow to create functional forms with validation **without writing a single line of code**.

Beyond the ability to quickly make prototypes, generated forms are easy to extend without having to modify the generated classes. This is thanks to the inheritance mechanism of the base and form classes.

At last at each evolution of the database schema, the task allows to generate again the forms to take into account the schema modifications, without overriding the Customization you might have made.

The CRUD Generator
------------------

Now that there are generated form classes, let's see how easy it is to create a symfony module to deal with the objects from a browser. We wish to create, modify, and delete objects from the `Article`, `Author`, `Category`, and `Tag` classes.
Let's start with the module creation for the `Author` class. Even if we can manually create a module, the Doctrine plugin provides the `doctrine:generate-module` task which generates a CRUD module based on a Doctrine object model class. Using the form we generated in the previous section:

    $ ./symfony doctrine:generate-module frontend author Author

The `doctrine:generate-module` takes three arguments:

  * `frontend` : name of the application you want to create the module in
  * `author`  : name of the module you want to create
  * `Author`  : name of the model class you want to create the module for 

>**Note**
>CRUD stands for Creation / Retrieval / Update / Deletion and sums up the four basic operations we can carry out with the model datas.

In Listing 4-4, we see that the task generated six actions allowing us to 
list (`index`), save new (`create`), display new (`new`), modify (`edit`),
save (`update`), and delete (`delete`) the objects of the `Author` class.

Listing 4-4 - The `authorActions` Class generated by the Task

    [php]
    // apps/frontend/modules/author/actions/actions.class.php
    class authorActions extends sfActions
    {
      public function executeIndex()
      {
        $this->author_list = Doctrine::getTable('Author')
          ->createQuery('a')
          ->execute();
      }

      public function executeNew(sfWebRequest $request)
      {
        $this->form = new AuthorForm();
      }

      public function executeCreate(sfWebRequest $request)
      {
        $this->forward404Unless($request->isMethod('post'));

        $this->form = new AuthorForm();

        $this->processForm($request, $this->form);

        $this->setTemplate('new');
      }

      public function executeEdit(sfWebRequest $request)
      {
        $this->forward404Unless($author = Doctrine::getTable('Author')->find($request->getParameter('id')), sprintf('Object author does not exist (%s).', $request->getParameter('id')));
        $this->form = new AuthorForm($author);
      }

      public function executeUpdate(sfWebRequest $request)
      {
        $this->forward404Unless($request->isMethod('post') || $request->isMethod('put'));
        $this->forward404Unless($author = Doctrine::getTable('Author')->find($request->getParameter('id')), sprintf('Object author does not exist (%s).', $request->getParameter('id')));
        $this->form = new AuthorForm($author);

        $this->processForm($request, $this->form);

        $this->setTemplate('edit');
      }

      public function executeDelete(sfWebRequest $request)
      {
        $request->checkCSRFProtection();

        $this->forward404Unless($author = Doctrine::getTable('Author')->find($request->getParameter('id')), sprintf('Object author does not exist (%s).', $request->getParameter('id')));
        $author->delete();

        $this->redirect('author/index');
      }

      protected function processForm(sfWebRequest $request, sfForm $form)
      {
        $form->bind($request->getParameter($form->getName()));
        if ($form->isValid())
        {
          $author = $form->save();

          $this->redirect('author/edit?id='.$author->getId());
        }
      }
    }

In this module, the form life cycle is handled by four methods: `create`,
`edit`, `update` and `processForm`. You may choose to make this less 
verbose by moving these 4 tasks into one method, listing 4-5 shows a 
simplified example of this.

Listing 4-5 - The form life cycle of the `authorActions` Class after some 
refactoring

    [php]
    // In authorActions, replacing the create, edit, update and processForm methods
    public function executeEdit($request)
    {
      $this->form = new AuthorForm(Doctrine::getTable('Author')->find($request->getParameter('id')));

      if ($request->isMethod('post'))
      {
        $this->form->bind($request->getParameter('author'));
        if ($this->form->isValid())
        {
          $author = $this->form->save();
          $this->redirect('author/edit?id='.$author->getId());
        }
      }
    }

>**NOTE**
>The examples that follow use the default, more verbose style so you will need
>to make adjustments accordingly if you wish to follow the approach in listing 
>4-5. For example, in your form template, you will only need to point the form
>to the edit action regardless of whether the object is new or old.

The task also generated three templates and a partial, `indexSuccess`, 
`editSuccess`, `newSuccess` and `_form`. The `_form` template was generated 
without using the `<?php echo $form ?>` statement. We can modify this behavior,
using the `--non-verbose-templates`:

    $ ./symfony doctrine:generate-module frontend author Author --non-verbose-templates

This option is helpful during prototyping phases, as Listing 4-6 shows.

Listing 4-6 - The `_form` Template

    [php]
    // apps/frontend/modules/author/templates/_form.php
    <?php include_stylesheets_for_form($form) ?>
    <?php include_javascripts_for_form($form) ?>

    <form action="<?php echo url_for('author/'.($form->getObject()->isNew() ? 'create' : 'update').(!$form->getObject()->isNew() ? '?id='.$form->getObject()->getId() : '')) ?>" method="post" <?php $form->isMultipart() and print 'enctype="multipart/form-data" ' ?>>
    <?php if (!$form->getObject()->isNew()): ?>
    <input type="hidden" name="sf_method" value="put" />
    <?php endif; ?>
      <table>
        <tfoot>
          <tr>
            <td colspan="2">
              &nbsp;<a href="<?php echo url_for('author/index') ?>">Cancel</a>
              <?php if (!$form->getObject()->isNew()): ?>
                &nbsp;<?php echo link_to('Delete', 'author/delete?id='.$form->getObject()->getId(), array('method' => 'delete', 'confirm' => 'Are you sure?')) ?>
              <?php endif; ?>
              <input type="submit" value="Save" />
            </td>
          </tr>
        </tfoot>
        <tbody>
          <?php echo $form ?>
        </tbody>
      </table>
      </form>

>**TIP**
>The `--with-show` option lets us generate an action and a template we can use
>to view an object (read only). 

You can now open the URL `/frontend_dev.php/author` in a browser to view the 
generated module (Figure 4-1 and Figure 4-2). Take time to play with the 
interface. Thanks to the generated module you can list the authors, add a new
one, edit, modify, and even delete. You will also notice that the validation
rules are also working. Note that in the following figures, we have chosen to
remove the "active" field.

Figure 4-1 - Authors List

![Authors List](/images/forms_book/en/04_01.png "Authors List")

Figure 4-2 - Editing an Author with Validation Errors

![Editing an Author with Validation Errors](/images/forms_book/en/04_02.png "Editing an Author with Validation Errors")

We can now repeat the operation with the `Article` class:

    $ ./symfony doctrine:generate-module frontend article Article --non-verbose-templates

The `ArticleForm` form uses the `sfWidgetFormDoctrineSelect` widget to represent
the relation between the `Article` object and the `Author` object. This widget
creates a drop-down list with the authors. During the display, the authors objects
are converted into a string of characters using the `__toString()` magic method,
which must be defined in the `Author` class as shown in Listing 4-7.

Listing 4-7 - Implementing the `__toString()` method for the `Author` class

    [php]
    class Author extends BaseAuthor
    {
      public function __toString()
      {
        return $this->getFirstName().' '.$this->getLastName();
      }
    }

Just like the `Author` class, you can create `__toString()` methods for the other
classes of our model: `Article`, `Category`, and `Tag`.

>**Note**
>sfDoctrineRecord will attempt to guess in the base __toString() method if you do
>not specify your own. It checks for columns named 'name', 'title', 'description',
>'subject', 'keywords' and finally 'id' to use as the string representation. If
>one of these fields is not found, Doctrine will return a default warning string.

-

>**Tip**
>The `method` option of the `sfWidgetFormDoctrineSelect` widget changes the method
>used to represent an object in text format.

Figure 4-4 Shows how to create an article after implementing the `__toString()`
method.

Figure 4-4 - Creating an Article

![Creating an Article](/images/forms_book/en/04_04.png "Creating an Article")

>**NOTE**
>In figure 4-4 you will notice that some fields do not appear on the form, for
>example `created_at` and `updated_at`. This is because we've customized the form
>class. You will learn how to do this in the next section.

Customizing the generated Forms
-------------------------------

The `doctrine:build-forms` and `doctrine:generate-module` tasks let us create functional symfony modules to list, create, edit, and delete model objects. These modules are taking into account not only the validation rules of the model but also the relationships between tables. All of this happens without writing a single line of code!

The time has now come to customize the generated code. If the form classes are already considering many elements, some aspects will need to be customized.

### Configuring validators and widgets

Let's start with configuring the validators and widgets generated by default.

The `ArticleForm` form has a `slug` field. The slug is a string of characters that uniquely representing the article in the URL. For instance, the slug of an article whose title is "Optimize the developments with symfony" is `12-optimize-the-developments-with-symfony`, `12` being the article `id`. This field is usually automatically computed when the object is saved, depending on the `title`, but it has the potential to be explicitly overridden by the user. Even if this field is required in the schema, it can not be compulsory to the form. That is why we modify the validator and make it optional, as in Listing 4-8. We will also customize the `content` field increasing its size and forcing the user to type in at least five characters.

Listing 4-8 - Customizing Validators and Widgets

    [php]
    class ArticleForm extends BaseArticleForm
    {
      public function configure()
      {
        $this->validatorSchema['slug']->setOption('required', false);
        $this->validatorSchema['content']->setOption('min_length', 5);

        $this->widgetSchema['content']->setAttributes(array('rows' => 10, 'cols' => 40));
      }
    }

We use here the `validatorSchema` and `widgetSchema` objects as PHP arrays. These arrays are taking the name of a field as key and return respectively the validator object and the related widget object. We can then Customize individually fields and widgets.

>**Note**
>In order to allow the use of objects as PHP arrays, the `sfValidatorSchema` and `sfWidgetFormSchema` classes implement the `ArrayAccess` interface, available in PHP since version 5.

To make sure two articles can not have the same `slug`, a uniqueness constraint has been added in the schema definition. This constraint on the database level is reflected in the `ArticleForm` form using the `sfValidatorDoctrineUnique` validator. This validator can check the uniqueness of any form field. It is helpful among other things to check the uniqueness of an email address of a login for instance. Listing 4-9 shows how to use it in the `ArticleForm` form.

Listing 4-9 - Using the `sfValidatorDoctrineUnique` validator to check the Uniqueness of a field

    [php]
    class BaseArticleForm extends BaseFormDoctrine
    {
      public function setup()
      {
        // ...

        $this->validatorSchema->setPostValidator(
          new sfValidatorDoctrineUnique(array('model' => 'Article', 'column' => array('slug')))
        );
      }
    }

The `sfValidatorDoctrineUnique` validator is a `postValidator` running on the
whole data set after the individual validation of each field. In order to 
validate the uniqueness of the `slug`, the validator must be able to access not
only the `slug` value, but also the value of the primary key(s). Validation
rules are indeed different throughout the creation and the edition since the
slug can stay the same during the update of an article.

Let's now Customize the `active` field of the `author` table, used to show if an
author is active. Listing 4-10 shows how to exclude inactive authors from the
`ArticleForm` form, modifying the `query` option of the `FormDoctrineSelect`
widget connected to the `author_id` field. The `query` option accepts a Doctrine
Query object, allowing us to narrow down the list of available options in the
rolling list.

Listing 4-10 - Customizing the `sfWidgetFormDoctrineSelect` widget

    [php]
    class ArticleForm extends BaseArticleForm
    {
      public function configure()
      {
        // ...

        $query = Doctrine_Query::create()
          ->from('Author a')
          ->where('a.active = ?', true);
        $this->widgetSchema['author_id']->setOption('query', $query);
      }
    }

Even if the widget customization can make us narrow down the list of available options, we must not forget to consider this narrowing on the validator level, as shown in Listing 4-11. Like the `sfWidgetProperSelect` widget, the `sfValidatorDoctrineChoice` validator accepts a `query` option to narrow down the options valid for a field.

Listing 4-11 - Customizing the `sfValidatorDoctrineChoice` validator

    [php]
    class ArticleForm extends BaseArticleForm
    {
      public function configure()
      {
        // ...

        $query = Doctrine_Query::create()
          ->from('Author a')
          ->where('a.active = ?', true);

        $this->widgetSchema['author_id']->setOption('query', $query);
        $this->validatorSchema['author_id']->setOption('query', $query);
      }
    }

In the previous example we defined the `Query` object directly in the `configure()` method. In our project, this query will certainly be helpful in other circumstances, so it is better to create a `getActiveAuthorsQuery()` method within the `AuthorTable` class and to call this method from `ArticleForm` as Listing 4-12 shows.

Listing 4-12 - Refactoring the `Query` in the Model

    [php]
    class AuthorTable extends Doctrine_Table
    {
      public function getActiveAuthorsQuery()
      {
        $query = Doctrine_Query::create()
          ->from('Author a')
          ->where('a.active = ?', true);

        return $query;
      }
    }

    class ArticleForm extends BaseArticleForm
    {
      public function configure()
      {
        // ...
      
        $authorQuery = Doctrine::getTable('Author')->getActiveAuthorsQuery();
        $this->widgetSchema['author_id']->setOption('query', $authorQuery);
        $this->validatorSchema['author_id']->setOption('query', $authorQuery);
      }
    }

### Changing a validator

Since the `email` is defined as a `string(255)` in the schema, symfony created
an `sfValidatorString()` validator restraining the maximum length to 255
characters. This field is also supposed to receive a valid email, Listing 
4-14 replaces the generated validator with an `sfValidatorEmail` validator.

Listing 4-13 - Changing the `email` field Validator of the `AuthorForm` class

    [php]
    class AuthorForm extends BaseAuthorForm
    {
      public function configure()
      {
        $this->validatorSchema['email'] = new sfValidatorEmail();
      }
    }

### Adding a validator

We observed in the previous chapter how to modify the generated validator. But
in the case of the `email` field, it would be useful to keep the maximum length
validation. In Listing 4-14, we use the `sfValidatorAnd` validator to guarantee
the email validity and check the maximum length allowed for the field.

Listing 4-14 - Using a multiple Validator

    [php]
    class AuthorForm extends BaseAuthorForm
    {
      public function configure()
      {
        $this->validatorSchema['email'] = new sfValidatorAnd(array(
          new sfValidatorString(array('max_length' => 255)),
          new sfValidatorEmail(),
        ));
      }
    }

The previous example is not perfect, because if we decide later to modify the length
of the `email` field in the database schema, we will have to think about doing
it also in the form. Instead of replacing the generated validator, it is better
to add one, as shown in Listing 4-15.

Listing 4-15 - Adding a Validator

    [php]
    class AuthorForm extends BaseAuthorForm
    {
      public function configure()
      {
        $this->validatorSchema['email'] = new sfValidatorAnd(array(
          $this->validatorSchema['email'],
          new sfValidatorEmail(),
        ));
      }
    }

### Changing a widget

In the database schema, the `status` field of the `article` table stores the
article status as a string of characters. The possible values were defined
in the `ArticeTable` class, as shown in Listing 4-16.

Listing 4-16 - Defining available Statuses in the `ArticleTable` class

    [php]
    class ArticleTable extends Doctrine_Table
    {
      static protected $statuses = array('draft', 'online', 'offline');

      static public function getStatuses()
      {
        return self::$statuses;
      }

      // ...
    }

When editing an article, the `status` field must be represented as a drop-down
list instead of a text field. To do so, let's change the widget we used, as
shown in Listing 4-17.

Listing 4-17 - Changing the Widget for the `status` field

    [php]
    class ArticleForm extends BaseArticleForm
    {
      public function configure()
      {
        // ...
        
        $this->widgetSchema['status'] = new sfWidgetFormSelect(array('choices' => ArticleTable::getStatuses()));
      }
    }

To be thorough we must also change the validator to make sure the chosen status
actually belongs to the list of possible options (Listing 4-18).

Listing 4-18 - Modifying the `status` Field Validator

    [php]
    class ArticleForm extends BaseArticleForm
    {
      public function configure()
      {
        // ...
        
        $statuses = ArticleTable::getStatuses();
        
        $this->widgetSchema['status']    = new sfWidgetFormSelect(array('choices' => $statuses));
        $this->validatorSchema['status'] = new sfValidatorChoice(array('choices' => array_keys($statuses)));
      }
    }

### Deleting a field

The `article` table has three special columns, `created_at`, `updated_at` 
and `published_at`. The first two are automatically handled by Doctrine as part
of the `timestampable` behaviour, the third we will handle at a later date in our
own code. We must delete them from the form as Listing 4-19 shows, to prevent
the user from modifying them.

Listing 4-19 - Deleting a Field

    [php]
    class ArticleForm extends BaseArticleForm
    {
      public function configure()
      {
        // ...
      
        unset($this->validatorSchema['created_at']);
        unset($this->widgetSchema['created_at']);

        unset($this->validatorSchema['updated_at']);
        unset($this->widgetSchema['updated_at']);

        unset($this->validatorSchema['published_at']);
        unset($this->widgetSchema['published_at']);
      }
    }

In order to delete a field, it is necessary to delete its validator and its 
widget. Listing 4-20 shows how it is also possible to delete both in one 
action, using the form as a PHP array.

Listing 4-20 - Deleting a Field using the Form as a PHP Array

    [php]
    class ArticleForm extends BaseArticleForm
    {
      public function configure()
      {
        // ...
      
        unset($this['created_at'], $this['updated_at'], $this['published_at']);
      }
    }

### Sum up

To sum up, Listing 4-21 and Listing 4-22 show the `ArticleForm` and
`AuthorForm` forms as we have customized them.

Listing 4-21 - `ArticleForm` Form

    [php]
    class ArticleForm extends BaseArticleForm
    {
      public function configure()
      {
        $authorQuery = Doctrine::getTable('Author')->getActiveAuthorsQuery();

        // widgets
        $this->widgetSchema['content']->setAttributes(array('rows' => 10, 'cols' => 40));
        $this->widgetSchema['status'] = new sfWidgetFormSelect(array('choices' => ArticleTable::getStatuses()));
        $this->widgetSchema['author_id']->setOption('query', $authorQuery);

        // validators
        $this->validatorSchema['slug']->setOption('required', false);
        $this->validatorSchema['content']->setOption('min_length', 5);
        $this->validatorSchema['status'] = new sfValidatorChoice(array('choices' => array_keys(ArticleTable::getStatuses())));
        $this->validatorSchema['author_id']->setOption('query', $authorQuery);

        unset($this['created_at'], $this['updated_at'], $this['published_at']);
      }
    }

Listing 4-22 - `AuthorForm` Form

    [php]
    class AuthorForm extends BaseAuthorForm
    {
      public function configure()
      {
        $this->validatorSchema['email'] = new sfValidatorAnd(array(
          $this->validatorSchema['email'],
          new sfValidatorEmail(),
        ));
      }
    }

Using the `doctrine:build-forms` task allows us to automatically generate most
of the elements which allow forms to introspect the object model. This 
automation is helpful for several reasons:

  * It makes the developer's life easier, saving him from repetitive and 
    redundant work. He can then focus on the validators and widget 
    customization according to the project's specific business rules.

  * When the database schema is updated, the generated forms will 
    automatically be updated. The developer will just have to tune 
    the customizations they made.

The next section will describe the customization of actions and templates 
generated by the `doctrine:generate-module` task.

Form Serialization
------------------

The previous section shows us how to customize forms generated by the task
`doctrine:build-forms`. In the current section, we will customize the life
cycle of forms, starting with the code generated by the 
`doctrine:generate-module` task.

### Default values

**A Doctrine form instance is always connected to a Doctrine object**. The 
linked Doctrine object always belongs to the class returned by the 
`getModelName()` method. For instance, the `AuthorForm` form can only be 
linked to objects belonging to the `Author` class. This object is either 
an empty object (a blank instance of the `Author` class), or the object 
sent to the constructor as its first argument. Whereas the constructor of 
a "standard" form takes an array of values as first argument, the constructor
of a Doctrine form takes a Doctrine object. This object is used  to define each
form field's default value. The `getObject()` method returns the object related
to the current instance and the `isNew()` method indicates whether the object
was sent via the constructor or not:

    [php]
    // creating a new object
    $authorForm = new AuthorForm();

    print $authorForm->getObject()->getId(); // outputs null
    print $authorForm->isNew();              // outputs true

    // modifying an existing object
    $author = Doctrine::getTable('Author')->find(1);
    $authorForm = new AuthorForm($author);

    print $authorForm->getObject()->getId(); // outputs 1
    print $authorForm->isNew();              // outputs false

### Handling life cycle

As we observed at the beginning of the chapter, the `new`, `edit` and `create`
actions, shown in Listing 4-23, handle the form life cycle.

Listing 4-23 - The `executeNew`, `executeEdit`, `executeCreate` and
`processForm` methods of the `author` Module

    [php]
    // apps/frontend/modules/author/actions/actions.class.php
    class authorActions extends sfActions
    {
      // ...
      public function executeNew(sfWebRequest $request)
      {
        $this->form = new AuthorForm();
      }

      public function executeCreate(sfWebRequest $request)
      {
        $this->forward404Unless($request->isMethod('post'));

        $this->form = new AuthorForm();

        $this->processForm($request, $this->form);

        $this->setTemplate('new');
      }

      public function executeEdit(sfWebRequest $request)
      {
        $this->forward404Unless($author = Doctrine::getTable('Author')->find($request->getParameter('id')), sprintf('Object author does not exist (%s).', $request->getParameter('id')));
        $this->form = new AuthorForm($author);
      }
      
      protected function processForm(sfWebRequest $request, sfForm $form)
      {
        $form->bind($request->getParameter($form->getName()));
        if ($form->isValid())
        {
          $author = $form->save();

          $this->redirect('author/edit?id='.$author->getId());
        }
      }
    }

Even if the `edit` action looks like the actions we might have described in the
previous chapters, we can point out a few differences:

  * A Doctrine object from the `Author` class is sent as first argument to the form constructor:

        [php]
        $author = Doctrine::getTable('Author')->find($request->getParameter('id'));
        $this->form = new AuthorForm($author);

  * The widget's `name` attribute format is automatically retrieved to allow for 
    the retrieval of the input data in a PHP array named after the related table:

        [php]
        $form->bind($request->getParameter($form->getName()));

  * When the form is valid, a mere call to the `save()` method creates or updates
    the Doctrine object related to the form:

        [php]
        $author = $form->save();

### Creating and Modifying a Doctrine Object

Listing 4-23 code handles the creation and modification of objects from the 
`Author` class:

  * Creation of a new `Author` object:

      * The `create` action is called

      * The `form` object is then linked to an empty `Author` Doctrine object

      * The `$form->save()` call creates consequently a new `Author` object
        when a valid form is submitted

  * Modification of an existing `Author` object:

      * The `update` action is called with an `id` parameter 
        (`$request->getParameter('id')` standing for the primary key the 
        `Author` object is to modify)

      * The call to the `find()` method returns the `Author` object related
        to the primary key

      * The `form` object is therefore linked to the previously found object

      * The `$form->save()` call updates the `Author` object when a valid form
        is submitted

### The `save()` method

When a Doctrine form is valid, the `save()` method updates the related object and stores it in the database. This method actually stores not only the main object but also the potentially related objects. For instance, the `ArticleForm` form updates the tags connected to an article. The relation between the ` article` table and the `tag` table being a n-n relation, the tags related to an article are saved in the `article_tag` table (using the `saveArticleTagList()` generated method).

In order to certify a consistent serialization, the `save()` method includes every update in one transaction.

>**Note**
>We will see in Chapter 9 that the `save()` method also automatically updates the internationalized tables.

-

>**SIDEBAR**
>Using the `bindAndSave()` method
>
>The `bindAndSave()` method binds the input data the user submitted to the form, validates this form and updates the related object in the database, all in one operation: 
>
>     [php]
>     class articleActions extends sfActions
>     {
>       public function executeCreate(sfWebRequest $request)
>       {
>         $this->form = new ArticleForm();
>
>         if ($request->isMethod('post') && $this->form->bindAndSave($request->getParameter('article')))
>         {
>           $this->redirect('article/created');
>         }
>       }
>     }

### Handling file uploads

The `save()` method automatically updates the Doctrine objects but can not 
handle side elements such as managing a file upload.

Let's see how to attach a file to each article. Files are stored in the 
`web/uploads` directory and a reference to the file path is kept in the
`file` field of the `article` table, as shown in Listing 4-24.

Listing 4-24 - Schema for the `article` Table with associated File

    [yml]
    // config/doctrine/schema.yml
    Article:
      // ...
      file: string(255)

After every schema update, you need to update the object model, the database and the related forms:

    $ ./symfony doctrine:build-all

>**Caution**
>Be aware that the `doctrine:build-all` task deletes every schema table before
>re-creating them. The data inside the tables are therefore overwritten. That 
>is why it is important to create test data (`fixtures`) that you can 
>load again after each model modification.

Listing 4-25 shows how to modify the `ArticleForm` class in order to link a widget and a validator to the `file` field.

Listing 4-25 - Modifying the `file` Field of the `ArticleForm` form.

    [php]
    class ArticleForm extends BaseArticleForm
    {
      public function configure()
      {
        // ...

        $this->widgetSchema['file'] = new sfWidgetFormInputFile();
        $this->validatorSchema['file'] = new sfValidatorFile();
      }
    }

As for every form allowing file upload, do not forget to add also the
`enctype` attribute to the `form` tag of the template (see Chapter 2 for 
further informations concerning file upload management).

>**TIP**
>When creating your form template, you can check if the form contains file
>fields, and add the `enctype` attribute automatically:
>
>     [PHP]
>     <?php if ($form->isMultipart() echo 'enctype="multipart/form-data" '; ?>
>
>This code is automatically added when your form is created by the generate-module task.

Listing 4-26 shows the modifications to apply when saving the form to upload the file onto  the server and store its path in the `article` object.

Listing 4-26 - Saving the `article` Object and the File uploaded in the Action

    [php]
    public function executeEdit($request)
    {
      $author = Doctrine::getTable('Author')->find($request->getParameter('id'));
      $this->form = new ArticleForm($author);

      if ($request->isMethod('post'))
      {
        $this->form->bind($request->getParameter('article'), $request->getFiles('article'));
        if ($this->form->isValid())
        {
          $file = $this->form->getValue('file');
          $filename = sha1($file->getOriginalName()).$file->getExtension($file->getOriginalExtension());
          $file->save(sfConfig::get('sf_upload_dir').'/'.$filename);

          $article = $this->form->save();

          $this->redirect('article/edit?id='.$article->getId());
        }
      }
    }

Saving the uploaded file on the filesystem allows the `sfValidatedFile` object to know the absolute path to the file. During the call to the `save()` method, the fields values are used to update the related object and, as for the `file` field, the `sfValidatedFile` object is converted in a character string thanks to the `__toString()` method, sending back the absolute path to the file. The `file` column of the `article` table will store this absolute path.

>**TIP**
>If you wish to store the path relative to the `sfConfig::get('sf_upload_dir')` directory, you can create a class inheriting from `sfValidatedFile` and use the `validated_file_class` option to send to the `sfValidatorFile` validator the name of the new class. The validator will then return an instance of your class. We will see in the rest of this chapter another approach, consisting in modifying the value of the `file` column before saving the object in database.

### Customizing the `save()` method

We observed in the previous section how to save the uploaded file in the `edit` action. One of the principles of the object oriented programming is the reusability of the code, thanks to its encapsulation in classes. Instead of duplicating the code used to save the file in each action using the `ArticleForm` form, it is better to move it in the `ArticleForm` class. Listing 4-27 shows how to override the `save()` method in order to also save the file and possibly to delete of an existing file.

Listing 4-27 - Overriding the `save()` Method of the `ArticleForm` Class

    [php]
    class ArticleForm extends BaseFormDoctrine
    {
      // ...
     
      public function save($con = null)
      {
        if (file_exists($this->getObject()->getFile()))
        {
          unlink($this->getObject()->getFile());
        }
   
        $file = $this->getValue('file');
        $filename = sha1($file->getOriginalName()).$file->getExtension($file->getOriginalExtension());
        $file->save(sfConfig::get('sf_upload_dir').'/'.$filename);
   
        return parent::save($con);
      }
    }

After moving the code to the form, the `edit` action is identical to the code initially generated by the `doctrine:generate-module` task.

>**SIDEBAR**
>Refactoring the Code in the Model of in the Form
>
>The actions generated by the `doctrine:generate-module` task shouldn't usually be modified.
>
>The logic you could add in the `edit` action, especially during form 
>serialization, should usually be moved to the model classes or the form class.
>
>We just went over an example of refactoring of the form class in order to 
>consider storing an uploaded file. Let's look at another example related to 
>the model. The `ArticleForm` form has a `slug` field. We observed that this 
>field should be automatically computed from the `title` field, and that it 
>could be potentially overridden by the user. This logic does not depend on
>the form. It belongs therefore to the model, as shown in the following code:
>
>     [php]
>     class Article extends BaseArticle
>     {
>       public function save($con = null)
>       {
>         if (!$this->getSlug())
>         {
>           $this->setSlugFromTitle();
>         }
>
>         return parent::save($con);
>       }
>
>       protected function setSlugFromTitle()
>       {
>         // ...
>       }
>     }
>
>The main goal of this refactoring is to respect the separation of application 
>layers, and to promote reusibility.

### Customizing the `doSave()` method

We observed that the saving of an object was made within a transaction in order to guarantee that each operation related to the saving is processed correctly. When overriding the `save()`method as we did in the previous section in order to save the uploaded file, the executed code is independent from this transaction.

Listing 4-28 shows how to use the `doSave()` method to insert in the global transaction our code saving the uploaded file. 

Listing 4-28 - Overriding the `doSave()` Method in the `ArticleForm` Form

    [php]
    class ArticleForm extends BaseFormDoctrine
    {
      // ...
     
      protected function doSave($con = null)
      {
        if (file_exists($this->getObject()->getFile()))
        {
          unlink($this->getObject()->getFile());
        }

        $file = $this->getValue('file');
        $filename = sha1($file->getOriginalName()).$file->getExtension($file->getOriginalExtension());
        $file->save(sfConfig::get('sf_upload_dir').'/'.$filename);

        return parent::doSave($con);
      }
    }

The `doSave()` method being called in the transaction created by the `save()` method, if the call to the `save()` method of the `file()` object throws an exception, the object will not be saved.

### Customizing the `updateObject()` Method

It is sometimes necessary to modify the object connected to the form between the update and the saving in database. 

In our file upload example, instead of storing the absolute path to the uploaded file in the `file` column, we wish to store the path relative to the `sfConfig::get('sf_upload_dir')` directory.

Listing 4-29 shows how to override the `updateObject()` method of the `ArticleForm` form in order to change the value of the `file` column after the automatic update object but before it is saved.

Listing 4-29 - Overriding the `updateObject()` Method and the `ArticleForm` Class

    [php]
    class ArticleForm extends BaseFormDoctrine
    {
      // ...

      public function updateObject($values = null)
      {
        $object = parent::updateObject($values);

        $object->setFile(str_replace(sfConfig::get('sf_upload_dir').'/', '', $object->getFile()));

        return $object;
      }
    }

The `updateObject()` method is called by the `doSave()` method before saving the object in database.
