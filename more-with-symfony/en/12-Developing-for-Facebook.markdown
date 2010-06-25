Developing for Facebook
=======================

*by Fabrice Bernhard*

Facebook, with more than 350 million members, has become the standard in
social websites on the Internet. One of its most interesting features is the
"Facebook Platform", an API which enables developers to create applications
inside the Facebook website as well as connect other websites with the
Facebook authentication system and social graph.

Since the Facebook frontend is in PHP, it is no wonder that the official client
library of this API is a PHP library. This de facto makes symfony a logical
solution for developing quick and clean Facebook applications or Facebook
Connect sites. But more than that, developing for Facebook really shows how
you can leverage symfony's functionalities to gain precious time while keeping
high standards of quality. This is what will be covered here in depth: after a
brief summary of what the Facebook API is and how it can be used, we will
cover how to use symfony at its best when developing Facebook applications,
how to benefit from the community's efforts and the `sfFacebookConnectPlugin`,
demonstrate it through a simple "Hello you!" application and finally give tips
and tricks to solve the most common problems.

Developing for Facebook
-----------------------

Although the API is basically the same in both cases, there are two very
different use-cases: creating a Facebook application inside Facebook and
implementing Facebook Connect on an external website.

### Facebook Applications

Facebook applications are web applications inside Facebook. Their main quality
is to be directly embedded into the 300 million users strong social website,
therefore allowing any viral application grow at incredible speed. Farmville is
the biggest and latest example, with more than 60 million monthly active users
and 2 million fans gained in a few months! This is the equivalent of the French
population coming back every month to work on their virtual farm! Facebook
applications interact with the Facebook website and its social graph in
different ways. Here is a brief look a the different places where the Facebook
application will be able to appear:

#### The Canvas

The canvas will usually be the main part of the application. It is
basically a small website embedded inside the Facebook frame.

#### The Profile Tab

The application can also be inside a tab on a user's profile or a fan page.
The main limitations are:

 * only one page. It is not possible to define links to sub-pages in the tab.

 * no dynamic flash or JavaScript at loading time. To provide dynamic
   functionalities, the application has to wait for the user to interact on
   the page by clicking on a link or button.

#### The Profile Box

This is more of a remain of the old Facebook, which nobody really uses
anymore. It is used to display some information in a box that can be found in
the "Boxes" tab of the profile.

#### The Information Tab's Addendum

Some static information linked to a specific user and the application can be
displayed in the information tab of the user's profile. This appears just
under the user's age, address, and curriculum.

#### Publishing Notices and information in the News Stream

The application can publish news, links, pictures, videos in the news stream,
on a user's friend's wall, or directly modify the user's status.

#### The Information page

This is the "profile page" of the application, automatically created by
Facebook. This is where the creator of the application will be able to
interact with their users in the usual Facebook way. This generally concerns
the marketing team more directly than the development team.

### Facebook Connect

Facebook Connect enables any website to bring some of the great
functionalities of Facebook to its own users. Already "connected" websites can
be recognized by the presence of a big blue "Connect with Facebook" button.
The most famous include digg.com, cnet.com, netvibes.com, yelp.com, etc. Here
is the list of the four main reasons to bring Facebook connect to an existing
site.

#### One-click Authentication System

Just like OpenID, Facebook Connect gives websites the opportunity to provide
automatic-login using their Facebook session. Once the "connection" between
the website and Facebook has been approved by the user, the Facebook session
is automatically provided to the website, saving him the cost of yet another
registration to do and password to remember.

#### Get more Information about the User

One other key feature of Facebook Connect is the quantity of information
provided. While a user will generally upload a minimum set of information to a
new website, Facebook Connect gives the opportunity to quickly get interesting
additional information like name, age, sex, location, profile picture, etc.
enriching the website. The terms of use of Facebook Connect clearly remind
that one should not store any personal information about the user without the
user explicitly agreeing about it, but the information provided can be used to
fill forms and ask for confirmation in a click. Additionally, the website can rely on
public information like name and profile picture without needing to store
them.

#### Viral Communication using the News Feed

The ability to interact with the user's new feed, invite friends or publish on
friend's walls lets the website use the full viral potential of Facebook to
communicate. Any website with some social component can really benefit from
this feature, as long as the information published in the Facebook feed has
some social value that might interest friends and friends of friends.

#### Take advantage of the existing Social Graph

For a website whose service relies on a social graph (like a network of
friends or acquaintances), the cost to build a first community, with enough
links between users to interact and benefit from the service, is really high.
By giving easy access to the list of friends of a user, Facebook Connect
dramatically reduces this cost, removing the need to search for "already
registered friends".

Setting up a first Project using `sfFacebookConnectPlugin`
----------------------------------------------------------

### Create the Facebook Application

To begin, a Facebook account is needed with the
["Developer"](http://www.facebook.com/developers) application installed. To
create the application, the only information needed is a name. Once this is
done, no further configuration is needed.

### Install and Configure `sfFacebookConnectPlugin`

The next step is to link Facebook's users with `sfGuard` users. This is the
main feature of the `sfFacebookConnectPlugin`, which I started and to which
other symfony developers have quickly contributed. Once the plugin is
installed, there is an easy but necessary configuration step. The API key,
application secret, and application ID need to be setup in the `app.yml` file:

    [yml]
    # default values
    all:
      facebook:
        api_key: xxx
        api_secret: xxx
        api_id: xxx
        redirect_after_connect: false
        redirect_after_connect_url: ''
        connect_signin_url: 'sfFacebookConnectAuth/signin'
        app_url: '/my-app'
        guard_adapter: ~
        js_framework: none # none, jQuery or prototype.

      sf_guard_plugin:
        profile_class: sfGuardUserProfile
        profile_field_name: user_id
        profile_facebook_uid_name: facebook_uid # WARNING this column must be of type varchar! 100000398093902 is a valid uid for example!
        profile_email_name: email
        profile_email_hash_name: email_hash

      facebook_connect:
        load_routing:     true
        user_permissions: []

>**TIP**
>With older versions of symfony, remember to set the "load_routing" option
>to false, since it uses the new routing system.

### Configure a Facebook Application

If the project is a Facebook application, the only other important parameter
will be the `app_url` which points to the relative path of the application on
Facebook. For example, for the application `http://apps.facebook.com/my-app`
the value of the `app_url` parameter will be `/my-app`.

### Configure a Facebook Connect Website

If the project is a Facebook Connect website, the other configuration
parameters can be left with the default values most of the time:

 * `redirect_after_connect` enables tweaking the behaviour after clicking on
   the "Connect with Facebook" button. By default the plugin reproduces the
   behaviour of `sfGuardPlugin` after registration.

 * `js_framework` can be used to specify a specific JS framework to use. It is
   highly recommended to use a JS framework such as jQuery on Facebook Connect
   sites since the JavaScript of Facebook Connect is quite heavy and can cause
   fatal errors (!) on IE6 if not loaded at the right moment.

 * `user_permissions` is the array of permissions that will be given to new
   Facebook Connect users.

### Connecting sfGuard with Facebook

The link between a Facebook user and the `sfGuardPlugin` system is done quite
logically using a `facebook_uid` column in the `Profile` table. The plugin
assumes that the link between the `sfGuardUser` and its profile is done using
the `getProfile()` method. This is the default behaviour with
`sfPropelGuardPlugin` but needs to be configured as such with
`sfDoctrineGuardPlugin`. Here are possible `schema.yml`:

For Propel:

    [yml]
    sf_guard_user_profile:
      _attributes: { phpName: UserProfile }
      id:
      user_id:            { type: integer, foreignTable: sf_guard_user, foreignReference: id, onDelete: cascade }
      first_name:         { type: varchar, size: 30 }
      last_name:          { type: varchar, size: 30 }
      facebook_uid:       { type: varchar, size: 20 }
      email:              { type: varchar, size: 255 }
      email_hash:         { type: varchar, size: 255 }
      _uniques:
        facebook_uid_index: [facebook_uid]
        email_index:        [email]
        email_hash_index:   [email_hash]

For Doctrine:

    [yml]
    sfGuardUserProfile:
      tableName:     sf_guard_user_profile
      columns:
        user_id:          { type: integer(4), notnull: true }
        first_name:       { type: string(30) }
        last_name:        { type: string(30) }
        facebook_uid:     { type: string(20) }
        email:            { type: string(255) }
        email_hash:       { type: string(255) }
      indexes:
        facebook_uid_index:
          fields: [facebook_uid]
          unique: true
        email_index:
          fields: [email]
          unique: true
        email_hash_index:
          fields: [email_hash]
          unique: true
      relations:
        sfGuardUser:
          type: one
          foreignType: one
          class: sfGuardUser
          local: user_id
          foreign: id
          onDelete: cascade
          foreignAlias: Profile


>**TIP**
>What if the project uses Doctrine and the `foreignAlias` is not `Profile`. In
>that case the plugin will not work. But a simple `getProfile()` method in the
>`sfGuardUser.class.php` which points to the `Profile` table will solve the problem!

Please note that the `facebook_uid` column should be `varchar`, because new
profiles on Facebook have `uids` above `10^15`. Better play safe with an
indexed `varchar` column than try to make `bigint`s work with different ORMs.

The other two columns are less important: `email` and `email_hash` are only
required in the case of a Facebook Connect website with existing users. In
that case Facebook provides a complicated process to try to associate existing
accounts with new Facebook connect accounts using a hash of the email. Of
course the process is made simple thanks to a task provided by the
`sfFacebookConnectPlugin`, which is described in the last part of this
chapter.

### Choosing between FBML and XFBML: Problem solved by symfony

Now that everything is setup, we can start to code the actual application.
Facebook offers many special tags that can render entire functionalities, like
an "invite friends" form or a fully-working comment system. These tags are
called FBML or XFBML tags. FBML and XFBML tags are quite similar but the
choice depends on whether the application is rendered inside Facebook or not.
If the project is a Facebook connect website, there is only one choice: XFBML.
If it is a Facebook application, there are two choices:

 * Embed the application as a real IFrame inside the Facebook application
   page and use XFBML inside this IFrame;

 * Let Facebook embed it transparently inside the page, and use FBML.

Facebook encourages developers to use their "transparent embedding" or the
so-called "FBML application". Indeed , it has some interesting features:

 * No Iframe, which is always complicated to manage since you need to keep it in
   mind if your links concern the Iframe or the parent window;

 * Special tags called FBML tags are interpreted automatically by the Facebook
   server and enable you to display private information concerning the user
   without having to communicate with the Facebook server beforehand;

 * No need to pass the Facebook session from page to page manually.

But FBML has some serious drawbacks too:

 * Every JavaScript is embedded inside a sandbox, making it impossible to use
   outside libraries like a Google Maps, jQuery or any statistics system other
   than Google Analytics officially supported by Facebook;

 * It claims to be faster since some API calls can be replaced by FBML tags.
   However if the application is light, hosting it on its own server will be
   much faster;

 * It is harder to debug, especially error 500 which are caught by Facebook
   and replaced by a standard error.

So what is the recommended choice? The good news is that, with symfony and the
`sfFacebookConnectPlugin`, there is no choice to make! It is possible to write
agnostic applications and switch indifferently from an IFrame to an embedded
application to a Facebook Connect website with the same code. This is possible
because, technically, the main difference actually is in the layout... which is
very easy to switch in symfony. Here are the examples of the two different
layouts:

The layout for an FBML application:

    [html]
    <?php sfConfig::set('sf_web_debug', false); ?>
    <fb:title><?php echo sfContext::getInstance()->getResponse()->getTitle() ?></fb:title>
    <?php echo $sf_content ?>

The layout for an XFBML application or Facebook Connect website:

    [html]
    <?php use_helper('sfFacebookConnect')?>
    <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
    <html xmlns="http://www.w3.org/1999/xhtml" xmlns:fb="http://www.facebook.com/2008/fbml">
      <head>
        <?php include_http_metas() ?>
        <?php include_metas() ?>
        <?php include_title() ?>
        <script type="text/javascript" src="/sfFacebookConnectPlugin/js/animation/animation.js"></script>
      </head>
      <body>
        <?php echo $sf_content ?>
        <?php echo include_facebook_connect_script() ?>
      </body>
    </html>

To switch automatically between both, simply add this to you
`actions.class.php` file:

    [php]
    public function preExecute()
    {
      if (sfFacebook::isInsideFacebook())
      {
        $this->setLayout('layout_fbml');
      }
      else
      {
        $this->setLayout('layout_connect');
      }
    }

>**NOTE**
>There is one small difference between FBML and XFBML which is not located
>in the layout: FBML tags can be closed, not XFBML ones. So just replace
>tags like:
>
>     [html]
>     <fb:profile-pic uid="12345" size="normal" width="400" />
>
>by:
>
>     [html]
>     <fb:profile-pic uid="12345" size="normal" width="400"></fb:profile-pic>

Of course, to do this the application needs to be configured also as a
Facebook Connect application in the developer's settings, even if the application is
only intended for FBML purposes. But, the enormous advantage of doing this is
the possibility to test the application locally. If you are creating a
Facebook application and plan to use FBML tags, which is almost inevitable,
the only way to view the result is to put the code online and see the
result directly rendered in Facebook! Fortunately, thanks to Facebook Connect,
XFBML tags can be rendered outside of facebook.com. And, as was just described,
the only difference between FBML and XFBML tags is the layout.
Therefore, this solution enables FBML tags to be rendered locally, as long as there
is an Internet connection. Furthermore, with a development environment visible
on the Internet, such as a server or a simple computer with port 80 open, even the parts 
relying on Facebook's authentication system will work outside of the facebook.com domain 
thanks to Facebook connect. This allows to test the entire application before uploading it on Facebook.

### The simple Hello You Application

With the following code in the home template, the "Hello You" application is
finished:

    [php]
    <?php $sfGuardUser = sfFacebook::getSfGuardUserByFacebookSession(); ?>
    Hello <fb:name uid="<?php echo $sfGuardUser?$sfGuardUser->getProfile()->getFacebookUid():'' ?>"></fb:name>

The `sfFacebookConnectPlugin` automatically converts the visiting Facebook
user into a `sfGuard` user. This enables a very easy integration with existing
symfony code relying on the `sfGuardPlugin`.

Facebook Connect
----------------

### How Facebook Connect works and different Integration Strategies

Facebook Connect basically shares its session with the website's session. This is
done by copying authentication cookies from Facebook to the website by opening
an IFrame on the website that points to a Facebook page which itself opens an
IFrame to the website. To do this, Facebook Connect needs to have access to
the website, which makes it impossible to use or test Facebook Connect on a
local server or in an Intranet. The entry point is the `xd_receiver.htm` file,
which the `sfFacebookConnectPlugin` provides. Remember to use the
`plugin:publish-assets` task to make this file accessible.

Once this is done, the Facebook official library is able to use the Facebook
session. Additionally, `sfFacebookConnectPlugin` creates an `sfGuard` user
linked to the Facebook session, which seamlessly integrates with
the existing symfony website. This is why the plugin redirects by default to
the `sfFacebookConnectAuth/signIn` action after the Facebook Connect button has been
clicked and the Facebook Connect session has been validated. The plugin first
looks for an existing user with the same Facebook UID or same Email hash (see
"Connecting existing users with their Facebook account" at the end of the
article). If none is found, a new user is created.

Another common strategy is not to create the user directly, but first redirect
him to a specific registration form. There, one can use the Facebook session
to pre-fill common information, for example, by adding the following code to
the configuration method of the registration form:

    [php]
    public function setDefaultsFromFacebookSession()
    {
      if ($fb_uid = sfFacebook::getAnyFacebookUid())
      {
        $ret = sfFacebook::getFacebookApi()->users_getInfo(
          array(
            $fb_uid
          ),
          array(
            'first_name',
            'last_name',
          )
        );

        if ($ret && count($ret)>0)
        {
          if (array_key_exists('first_name', $ret[0]))
          {
            $this->setDefault('first_name',$ret[0]['first_name']);
          }
          if (array_key_exists('last_name', $ret[0]))
          {
            $this->setDefault('last_name',$ret[0]['last_name']);
          }
        }
      }

To use the second strategy, simply specify in the `app.yml` file to redirect
after Facebook Connect and the route to use for the redirection:

    [yml]
    # default values
    all:
      facebook:
        redirect_after_connect: true
        redirect_after_connect_url: '@register_with_facebook'

### The Facebook Connect Filter

Another important feature of Facebook Connect is that Facebook users are
very often logged into Facebook when browsing the Internet. This is
where the `sfFacebookConnectRememberMeFilter` proves very useful. If a user
visits the website and is already logged into Facebook, the
`sfFacebookConnectRememberMeFilter` will automatically log them into the
website just as the "Remember me" filter does.

    [php]
    $sfGuardUser = sfFacebook::getSfGuardUserByFacebookSession();
    if ($sfGuardUser)
    {
      $this->getContext()->getUser()->signIn($sfGuardUser, true);
    }

However this has one serious drawback: users can no longer logout from the
website, since as long as they are connected on Facebook, they will be logged
back automatically. Use this feature with caution.

### Clean implementation to avoid the Fatal IE JavaScript Bug

One of the most terrible bug you can have on a website is the IE "Operation
aborted" error which simply crashes the rendering of the website...
client-side! This is due to the bad quality of the rendering engine of IE6 and
IE7 which can crash if you append DOM elements to the `body` element from a
script which is not directly a child of the `body` element. Unfortunately, this is
typically the case if you load the Facebook Connect JavaScript without being
careful to load it only directly from the `body` element and at the end of
your document. But, this can easily be solved with symfony by using `slots`. Use
a `slot` to include the Facebook Connect script whenever necessary in
your template and render it in the layout at the end of the document, before
the `</body>` tag:

    [php]
    // in a template that uses a XFBML tag or a Facebook Connect button
    slot('fb_connect');
    include_facebook_connect_script();
    end_slot();

    // just before </body> in the layout to avoid problems in IE
    if (has_slot('fb_connect'))
    {
      include_slot('fb_connect');
    }

Best Practices for Facebook Applications
----------------------------------------

Thanks to the `sfFacebookConnectPlugin`, the integration with `sfGuardPlugin`
is made seamlessly and the choice of whether the application will be FBML, IFrame
or a Facebook Connect website can wait until the last minute. To go further
and create a real application using many more Facebook features, here are some
important tips that leverage symfony's features.

### Using symfony's Environments to set up multiple Facebook Connect test Servers

A very important aspect of the symfony philosophy is that of fast debugging and
quality testing of the application. Using Facebook can really make this
difficult since many features need an Internet connection to communicate with
the Facebook server, and an open 80 port to exchange authentication
cookies. Additionally, there is another constraint: a Facebook Connect application can
only be connected to one host. This is a real problem if the application is
developed on a machine, tested on another, put in pre-production on a third
server and used finally on a fourth one. In that case the most straightforward
solution is to actually create an application for each server and create a
symfony environment for each of them. This is very simple in symfony: do a
simple copy and paste of the `frontend_dev.php` file or its equivalent into
`frontend_preprod.php` and edit the line in the newly created file to change
the `dev` environment into the new `preprod` environment:

    [php]
    $configuration = ProjectConfiguration::getApplicationConfiguration('frontend', 'preprod', true);

Next, edit your `app.yml` file to configure the different Facebook applications
corresponding to each environment:

    [yml]
    prod:
      facebook:
        api_key: xxx
        api_secret: xxx
        api_id: xxx

    dev:
      facebook:
        api_key: xxx
        api_secret: xxx
        api_id: xxx

    preprod:
      facebook:
        api_key: xxx
        api_secret: xxx
        api_id: xxx

Now the application will be testable on every different server using the
corresponding `frontend_xxx.php` entry point.

### Using symfony's logging System for debugging FBML

The layout-switching solution enables development and testing of most of an
FBML application outside the Facebook website. However, the final test inside
Facebook can sometimes result, nonetheless, in the most obscure error message.
Indeed, the main problem of rendering FBML directly in Facebook is the fact
that error 500 are caught and replaced by a not-very-helpful standard error
message. On top of that, the web debug toolbar, to which symfony developers
quickly get addicted, is not rendered in the Facebook frame. Fortunately the
very good logging system of symfony is there to save us. The
`sfFacebookConnectPlugin` automatically logs many important actions and it is easy
to add lines in the logging file at any point in the application:

    [php]
    if (sfConfig::get('sf_logging_enabled'))
    {
      sfContext::getInstance()->getLogger()->info($message);
    }

### Using a Proxy to avoid wrong Facebook Redirections

One strange bug of Facebook is that once Facebook Connect is configured in the
application, the Facebook Connect server is considered as home of the
application. Although the home can be configured, it has to be in the domain
of the Facebook Connect host. So no other solution exists other than to surrender and
configure your home to a simple symfony action redirecting to wherever needed.
The following code will redirect to the Facebook application:

    [php]
    public function executeRedirect(sfWebRequest $request)
    {

      return $this->redirect('http://apps.facebook.com'.sfConfig::get('app_facebook_app_url'));
    }

### Using the `fb_url_for()` helper in Facebook applications

To keep an agnostic application that can be used as FBML in Facebook or XFBML
in an IFrame until the last minute, an important problem is the routing:

 * For an FBML application, the links inside the application need to point to
   `/app-name/symfony-route`;

 * for an IFrame application, it is important to pass the Facebook session
   information from page to page.

The `sfFacebookConnectPlugin` provides a special helper which can do
both, the `fb_url_for()` helper.

### Redirecting inside an FBML application

Symfony developers quickly become accustomed to redirecting after a successful post, a
good practice in web development to avoid double-posting. Redirecting in an
FBML application, however, does not work as expected. Instead, a special FBML tag
`<fb:redirect>` is needed to tell Facebook to do the redirection. To stay
agnostic depending on the context (the FBML tag or the normal symfony
redirect) a special redirect function exists in the `sfFacebook` class, which
can be used, for example, in a form saving action:

    [php]
    if ($form->isValid())
    {
      $form->save();

      return sfFacebook::redirect($url);
    }

### Connecting existing Users with their Facebook Account

One of the goals of Facebook Connect is to ease the registration process for
new users. However, another interesting use is to also connect existing
users with their Facebook account, either to get more information about them
or to communicate in their feed. This can be achieved in two ways:

 * Push existing sfGuard users to click on the "Connect with Facebook" button.
   The `sfFacebookConnectAuth/signIn` action will not create a new sfGuard
   user if it detects a currently logged-in user, but will simply save the
   newly Facebook Connected user to the current sfGuard user. It is that easy.

 * Use the email recognition system of Facebook. When a user uses Facebook
   Connect on a website, Facebook can provide a special hash of his emails,
   which can be compared to the email hashes in the existing database to
   recognize an account belonging to the user which has been created before.
   However, for security reasons most likely, Facebook only provides these
   email hashes if the user has previously registered using their API!
   Therefore, it is important to register all new users' emails regularly to be
   able to recognize them later on. This is what the `registerUsers` task does,
   which has been ported to 1.2 by Damien Alexandre. This task should run at
   least every night to register newly created users, or after a new user is
   created, using the `registerUsers` method of `sfFacebookConnect`:

        [php]
        sfFacebookConnect::registerUsers(array($sfGuardUser));

Going further
-------------

I hope this chapter managed to fulfill its purpose: help you start developing
a Facebook application using symfony and explain how to leverage symfony throughout
your Facebook development. However, the `sfFacebookConnectPlugin` does not
replace the Facebook API, and to learn about using the full power of the
Facebook development platform you will have to visit its
[website](http://developers.facebook.com/).

To conclude, I want to thank the symfony community for its quality and generosity,
especially those who already contributed to the `sfFacebookConnectPlugin` through
their comments and patches: Damien Alexandre, Thomas Parisot, Maxime Picaud,
Alban Creton and sorry to the others I might have forgotten. And of course, if you feel
there is something missing in the plugin, do not hesitate to contribute yourself!
