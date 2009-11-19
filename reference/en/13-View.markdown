The view.yml Configuration File
===============================

The View layer can be configured by editing the ~`view.yml`~ configuration
file.

As discussed in the introduction, the `view.yml` file benefits from the
[**configuration cascade mechanism**](#chapter_03_configuration_cascade), and
can include [**constants**](#chapter_03_constants).

>**CAUTION**
>This configuration file is mostly deprecated in favors of helpers used
>directly in the templates or methods called from actions.

The `view.yml` configuration file contains a list of view configurations:

    [yml]
    VIEW_NAME_1:
      # configuration

    VIEW_NAME_2:
      # configuration

    # ...

>**NOTE**
>The `view.yml` configuration file is cached as a PHP file; the
>process is automatically managed by the ~`sfViewConfigHandler`~
>[class](#chapter_14_config_handlers_yml).

Layout
------

*Default configuration*:

    [yml]
    default:
      has_layout: true
      layout:     layout

The `view.yml` configuration file defines the default ~layout~ used by the
application. By default, the name is `layout`, and so symfony decorates every
page with the `layout.php` file, found in the application `templates/`
directory. You can also disable the decoration process altogether by setting
the `~has_layout~` entry to `false`.

>**TIP**
>The layout is automatically disabled for XML HTTP requests and non-HTML
>content types, unless explicitly set for the view.

Stylesheets
-----------

*Default Configuration*:

    [yml]
    default:
      stylesheets: [main.css]

The `stylesheets` entry defines an array of stylesheets to use for the current
view.

>**NOTE**
>The inclusion of the stylesheets defined in `view.yml` can be done with the
>`include_stylesheets()` helper.

If many files are defined, symfony will include them in the same order as the
definition:

    [yml]
    stylesheets: [main.css, foo.css, bar.css]

You can also change the `media` attribute or omit the `.css` suffix:

    [yml]
    stylesheets: [main, foo.css, bar.css, print.css: { media: print }]

This setting is *deprecated* in favor of the `use_stylesheet()` helper:

    [php]
    <?php use_stylesheet('main.css') ?>

>**NOTE**
>In the default `view.yml` configuration file, the referenced file is
>`main.css`, and not `/css/main.css`. As a matter of fact, both definitions
>are equivalent as symfony prefixes relative paths with `/css/`.

JavaScripts
-----------

*Default Configuration*:

    [yml]
    default:
      javascripts: []

The `javascripts` entry defines an array of JavaScript files to use for the
current view.

>**NOTE**
>The inclusion of the JavaScript files defined in `view.yml` can be done with the
>`include_javascripts()` helper.

If many files are defined, symfony will include them in the same order as the
definition:

    [yml]
    javascripts: [foo.js, bar.js]

You can also omit the `.js` suffix:

    [yml]
    javascripts: [foo, bar]

This setting is *deprecated* in favor of the `use_javascript()` helper:

    [php]
    <?php use_javascript('foo.js') ?>

>**NOTE**
>When using relative paths, like `foo.js`, symfony prefixes them with
>`/js/`.

Metas and HTTP Metas
--------------------

*Default Configuration*:

    [yml]
    default:
      http_metas:
        content-type: text/html

      metas:
        #title:        symfony project
        #description:  symfony project
        #keywords:     symfony, project
        #language:     en
        #robots:       index, follow

The `http_metas` and `metas` settings allows the definition of meta tags to be
included in the layout.

>**NOTE**
>The inclusion of the meta tags defined in `view.yml` can be done manually
>with the `include_metas()` and `include_http_metas()` helpers.

These settings are *deprecated* in favor of pure HTML in the layout for static
metas (like the content type), or in favor of a slot for dynamic metas (like
the title or the description).

>**TIP**
>When it makes sense, the `content-type` HTTP meta is automatically modified
>to include the charset defined in the
>[`settings.yml` configuration file](#chapter_04_sub_charset) if not already present.
