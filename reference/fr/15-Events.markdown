Evénements
======

Les composants du noyau de symfony sont découplés grâce à un objet `sfEventDispatcher`.
Le contrôleur d'événement gère la communication entre les composants du noyau.

Tout objet peut notifier un événement au contrôleur, et n'importe quel objet peut
se connecter au contrôleur afin d'écouter un événement spécifique.

Un événement est juste un nom composé d'un espace de nommage et un nom séparé par un point
(`.`).

Usage
-----

Vous pouvez notifier un événement en créant d'abord un objet événement :

    [php]
    $event = new sfEvent($this, 'user.change_culture', array('culture' => $culture));

Et le notifier :

    $dispatcher->notify($event);

Le constructeur `sfEvent` prend trois arguments :

  * Le "sujet" de l'évènement (la plupart du temps, c'est la notification de l'objet
    de l'événement, mais il peut également être `null`)
  * Le nom de l'événement
  * Un tableau de paramètres à passer aux Listeners

Pour écouter un événement, connectez le au nom de l'évènement :

    [php]
    $dispatcher->connect('user.change_culture', array($this, 'listenToChangeCultureEvent'));

La méthode `connect` prend deux arguments :

  * Le nom de l'évènement
  * Un PHP accessible appelé quand l'événement est notifié

Voici un exemple de mise en œuvre d'un Listener :

    [php]
    public function listenToChangeCultureEvent(sfEvent $event)
    {
      // change l'objet format du message avec une nouvelle culture
      $this->setCulture($event['culture']);
    }

Le Listener reçoit l'événement en tant que premier argument. L'objet événement possède
plusieurs méthodes pour obtenir des informations sur l'événement :

  * `getSubject()`: Obtient l'objet sujet attaché à l'événement
  * `getParameters()`: Retourne les paramètres de l'événement

L'objet événement peut également être accédé comme un tableau pour obtenir ses paramètres.

Types d'événements
-----------

Les événements peuvent être déclenchés par trois méthodes différentes :

 * `notify()`
 * `notifyUntil()`
 * `filter()`

### ~`notify`~

La méthode `notify()` notifie à tous les Listeners. Les Listeners ne peuvent pas retourner une
valeur et tous les Listeners sont garantis d'être exécutés.

### ~`notifyUntil`~

La méthode `notifyUntil()` notifie à tous les Listeners jusqu'à ce que l'on arrête la chaîne
en renvoyant une valeur `true`.

L'écouteur qui interrompt la chaîne peut également appeler la méthode `setReturnValue()`.

Le notifiant peut vérifier si un Listener a traité l'événement en appelant la
méthode `isProcessed()` :

    [php]
    if ($event->isProcessed())
    {
      // ...
    }

### ~`filter`~

La méthode `filter()` notifie à tous les Listeners qu'ils peuvent filtrer la valeur
donnée, passée en second argument par le notifiant, et la récupérée par le
Listener appelable comme second argument. La valeur est passée à tous les Listeners
et ils doivent retourner la valeur filtrée. Tous les Listeners sont garantis d'être
exécutés.

Le notifiant peut obtenir la valeur filtrée en appelant la méthode
`getReturnValue()` :

    [php]
    $ret = $event->getReturnValue();

<div class="pagebreak"></div>

Evénements
------

 * [`application`](#chapter_15_application)
   * [`application.log`](#chapter_15_sub_application_log)
 * [`command`](#chapter_15_command)
   * [`command.log`](#chapter_15_sub_command_log)
   * [`command.pre_command`](#chapter_15_sub_command_pre_command)
   * [`command.post_command`](#chapter_15_sub_command_post_command)
   * [`command.filter_options`](#chapter_15_sub_command_filter_options)
 * [`configuration`](#chapter_15_configuration)
   * [`configuration.method_not_found`](#chapter_15_sub_configuration_method_not_found)
 * [`component`](#chapter_15_component)
   * [`component.method_not_found`](#chapter_15_sub_component_method_not_found)
 * [`context`](#chapter_15_context)
   * [`context.load_factories`](#chapter_15_sub_context_load_factories)
 * [`controller`](#chapter_15_controller)
   * [`controller.change_action`](#chapter_15_sub_controller_change_action)
   * [`controller.method_not_found`](#chapter_15_sub_controller_method_not_found)
   * [`controller.page_not_found`](#chapter_15_sub_controller_page_not_found)
 * [`form`](#chapter_15_form)
   * [`form.post_configure`](#chapter_15_sub_form_post_configure)
   * [`form.filter_values`](#chapter_15_sub_form_filter_values)
   * [`form.validation_error`](#chapter_15_sub_form_validation_error)
   * [`form.method_not_found`](#chapter_15_sub_form_method_not_found)
 * [`plugin`](#chapter_15_plugin)
   * [`plugin.pre_install`](#chapter_15_sub_plugin_pre_install)
   * [`plugin.post_install`](#chapter_15_sub_plugin_post_install)
   * [`plugin.pre_uninstall`](#chapter_15_sub_plugin_pre_uninstall)
   * [`plugin.post_uninstall`](#chapter_15_sub_plugin_post_uninstall)
 * [`request`](#chapter_15_request)
   * [`request.filter_parameters`](#chapter_15_sub_request_filter_parameters)
   * [`request.method_not_found`](#chapter_15_sub_request_method_not_found)
 * [`response`](#chapter_15_response)
   * [`response.method_not_found`](#chapter_15_sub_response_method_not_found)
   * [`response.filter_content`](#chapter_15_sub_response_filter_content)
 * [`routing`](#chapter_15_routing)
   * [`routing.load_configuration`](#chapter_15_sub_routing_load_configuration)
 * [`task`](#chapter_15_task)
   * [`task.cache.clear`](#chapter_15_sub_task_cache_clear)
 * [`template`](#chapter_15_template)
   * [`template.filter_parameters`](#chapter_15_sub_template_filter_parameters)
 * [`user`](#chapter_15_user)
   * [`user.change_culture`](#chapter_15_sub_user_change_culture)
   * [`user.method_not_found`](#chapter_15_sub_user_method_not_found)
   * [`user.change_authentication`](#chapter_15_sub_user_change_authentication)
 * [`view`](#chapter_15_view)
   * [`view.configure_format`](#chapter_15_sub_view_configure_format)
   * [`view.method_not_found`](#chapter_15_sub_view_method_not_found)
 * [`view.cache`](#chapter_15_view_cache)
   * [`view.cache.filter_content`](#chapter_15_sub_view_cache_filter_content)

<div class="pagebreak"></div>

`application`
-------------

### ~`application.log`~

*Méthode de notification* : `notify`

*Notificateur par défaut* : beaucoup de classes

| Paramètre  | Description
| ---------- | -----------
| `priority` | Le niveau de priorité (`sfLogger::EMERG`, `sfLogger::ALERT`, `sfLogger::CRIT`, `sfLogger::ERR`, `sfLogger::WARNING`, `sfLogger::NOTICE`, `sfLogger::INFO`, ou `sfLogger::DEBUG`)

L'événement `application.log` est le mécanisme utilisé par symfony pour faire la journalisation
des requêtes web (Voir le factory logger). L'événement est notifié par la plupart
des composants de base de symfony.

### ~`application.throw_exception`~

*Méthode de notification* : `notifyUntil`

*Notificateur par défaut* : `sfException`

L'événement `application.throw_exception` est notifié lorsque une exception non capturée
est levée pendant le traitement d'une requête.

Vous pouvez écouter cet événement pour faire quelque chose de spécial chaque fois qu'une exception
non capturée est levé (comme l'envoi d'un email, ou la journalisation de l'erreur). Vous pouvez
également remplacer le mécanisme d'exception par défaut du gestionnaire de symfony par
le traitement de l'événement.

`command`
---------

### ~`command.log`~

*Méthode de notification* : `notify`

*Notificateur par défaut* : Les classes `sfCommand*`

| Paramètre  | Description
| ---------- | -----------
| `priority` | Le niveau de priorité (`sfLogger::EMERG`, `sfLogger::ALERT`, `sfLogger::CRIT`, `sfLogger::ERR`, `sfLogger::WARNING`, `sfLogger::NOTICE`, `sfLogger::INFO`, ou `sfLogger::DEBUG`)

L'événement `command.log` est le mécanisme utilisé par symfony pour faire la journalisation pour
l'utilitaire CLI de symfony (voir le factory logger).

### ~`command.pre_command`~

*Méthode de notification* : `notifyUntil`

*Notificateur par défaut* : `sfTask`

| Paramètre   | Description
| ----------- | -----------
| `arguments` | Un tableau d'arguments passé à CLI
| `options`   | Un tableau d'options passé à CLI

L'événement `command.pre_command` est notifié juste avant qu'une tâche soit exécutée.

### ~`command.post_command`~

*Méthode de notification* : `notify`

*Notificateur par défaut* : `sfTask`

L'événement  `command.post_command` est notifié juste après qu'une tâche soit exécutée.

### ~`command.filter_options`~

*Méthode de notification* : `filter`

*Notificateur par défaut* : `sfTask`

| Paramètre         | Description
| ----------------- | -----------
| `command_manager` | Les instances de `sfCommandManager`

L'événement `command.filter_options` est notifié juste avant que les options CLI d'une tâche soient
analysées. Cet événement peut être utilisé pour filtrer les options passées par l'utilisateur.

`configuration`
---------------

### ~`configuration.method_not_found`~

*Méthode de notification* : `notifyUntil`

*Notificateur par défaut* : `sfProjectConfiguration`

| Paramètre   | Description
| ----------- | -----------
| `method`    | Le nom de la méthode manquante appelée
| `arguments` | Les arguments passés à la méthode

L'événement `configuration.method_not_found` est notifié lorsqu'une méthode n'est pas
définie dans la classe `sfProjectConfiguration`. En écoutant cet événement, une
méthode peut être ajoutée à la classe, sans utiliser l'héritage.

`component`
-----------

### ~`component.method_not_found`~

*Méthode de notification* : `notifyUntil`

*Notificateur par défaut* : `sfComponent`

| Paramètre   | Description
| ----------- | -----------
| `method`    | Le nom de la méthode manquante appelée
| `arguments` | Les arguments passés à la méthode

L'événement `component.method_not_found` est notifié lorsqu'une méthode n'est pas
définie dans la classe `sfComponent`. En écoutant cet événement, une méthode peut
être ajoutée à la classe, sans utiliser l'héritage.

`context`
---------

### ~`context.load_factories`~

*Méthode de notification* : `notify`

*Notificateur par défaut* : `sfContext`

L'événement `context.load_factories` est notifié une fois par requête par
l'objet `sfContext` juste après que toutes les Factories soient initialisées. C'est le
premier événement à être notifiée à toutes les classes de base initialisées.

`controller`
------------

### ~`controller.change_action`~

*Méthode de notification* : `notify`

*Notificateur par défaut* : `sfController`

| Paramètre | Description
| --------- | -----------
| `module`  | Le nom du module a exécuté
| `action`  | Le nom de l'action a exécuté

Le `controller.change_action` est notifié juste avant que l'action soit exécutée.

### ~`controller.method_not_found`~

*Méthode de notification* : `notifyUntil`

*Notificateur par défaut* : `sfController`

| Paramètre   | Description
| ----------- | -----------
| `method`    | Le nom de la méthode manquante appelée
| `arguments` | Les arguments passés à la méthode

L'événement `controller.method_not_found` est notifié lorsqu'une méthode n'est pas
définie dans la classe `sfController`. En écoutant cet événement, une méthode peut
être ajoutée à la classe, sans utiliser l'héritage.

### ~`controller.page_not_found`~

*Méthode de notification* : `notify`

*Notificateur par défaut* : `sfController`

| Paramètre | Description
| --------- | -----------
| `module`  | Le nom du module qui génère l'erreur 404
| `action`  | Le nom de l'action qui génère l'erreur 404

Le `controller.page_not_found` est notifié quand une erreur 404 est générée
lors du traitement d'une requête.

Vous pouvez écouter cet événement pour faire quelque chose de spécial chaque fois que survient une
page 404, comme envoyer un email, ou la journalisation de l'erreur.

`form`
------

### ~`form.post_configure`~

*Méthode de notification* : `notify`

*Notificateur par défaut* : `sfFormSymfony`

L'événement `form.post_configure` est notifié après que chaque formulaire soit configuré.

### ~`form.filter_values`~

*Méthode de notification* : `filter`

*Notificateur par défaut* : `sfFormSymfony`

L'événement `form.filter_values` filtre les paramètres fusionnés, corrompus et
les tableaux de fichiers juste avant la liaison.

### ~`form.validation_error`~

*Méthode de notification* : `notify`

*Notificateur par défaut* : `sfFormSymfony`

| Paramètre  | Description
| ---------- | ------------------
| `error`    | L'instance de l'erreur

L'événement `form.validation_error` est notifié pour toutes les validations des formulaires qui échouent.

### ~`form.method_not_found`~

*Méthode de notification* : `notifyUntil`

*Notificateur par défaut* : `sfFormSymfony`

| Paramètre   | Description
| ----------- | -----------
| `method`    | Le nom de la méthode manquante appelée
| `arguments` | Les arguments passés à la méthode

L'événement `form.method_not_found` est notifié lorsqu'une méthode n'est pas
définie dans la classe `sfFormSymfony`. En écoutant cet événement, une méthode peut
être ajoutée à la classe, sans utiliser l'héritage.

`plugin`
--------

### ~`plugin.pre_install`~

*Méthode de notification* : `notify`

*Notificateur par défaut* : `sfPluginManager`

| Paramètre    | Description
| ------------ | -----------
| `channel`    | Le canal du plugin
| `plugin`     | Le nom du plugin
| `is_package` | Si le plugin à installer est dans un package local (`true`), ou un package web (`false`)

L'événement `plugin.pre_install` est notifié juste avant que le plugin soit
installé.

### ~`plugin.post_install`~

*Méthode de notification* : `notify`

*Notificateur par défaut* : `sfPluginManager`

| Paramètre    | Description
| ------------ | -----------
| `channel`    | Le canal du plugin
| `plugin`     | Le nom du plugin

L'événement `plugin.post_install` est notifié juste après que le plugin soit
installé.

### ~`plugin.pre_uninstall`~

*Méthode de notification* : `notify`

*Notificateur par défaut* : `sfPluginManager`

| Paramètre    | Description
| ------------ | -----------
| `channel`    | Le canal du plugin
| `plugin`     | Le nom du plugin

L'événement `plugin.pre_uninstall` est notifié juste avant que le plugin soit
désinstallé.

### ~`plugin.post_uninstall`~

*Méthode de notification* : `notify`

*Notificateur par défaut* : `sfPluginManager`

| Paramètre    | Description
| ------------ | -----------
| `channel`    | Le canal du plugin
| `plugin`     | Le nom du plugin

L'événement `plugin.post_uninstall` est notifié juste après que le plugin soit
désinstallé.

`request`
---------

### ~`request.filter_parameters`~

*Méthode de notification* : `filter`

*Notificateur par défaut* : `sfWebRequest`

| Paramètre    | Description
| ------------ | -----------
| `path_info`  | Le chemin de la requête

L'événement `request.filter_parameters` est notifié lorsque les paramètres de la requête
sont initialisés.

### ~`request.method_not_found`~

*Méthode de notification* : `notifyUntil`

*Notificateur par défaut* : `sfRequest`

| Paramètre   | Description
| ----------- | -----------
| `method`    | Le nom de la méthode manquante appelée
| `arguments` | Les arguments passés à la méthode

L'événement `request.method_not_found` est notifié lorsqu'une méthode n'est pas
définie dans la classe `sfRequest`. En écoutant cet événement, une méthode peut
être ajoutée à la classe, sans utiliser l'héritage.

`response`
----------

### ~`response.method_not_found`~

*Méthode de notification* : `notifyUntil`

*Notificateur par défaut* : `sfResponse`

| Paramètre   | Description
| ----------- | -----------
| `method`    | Le nom de la méthode manquante appelée
| `arguments` | Les arguments passés à la méthode

L'événement `response.method_not_found` est notifié lorsqu'une méthode n'est pas
définie dans la classe `sfResponse`. En écoutant cet événement, une méthode peut
être ajoutée à la classe, sans utiliser l'héritage.

### ~`response.filter_content`~

*Méthode de notification* : `filter`

*Notificateur par défaut* : `sfResponse`

L'événement `response.filter_content` est notifié avant que la réponse soit envoyée. En
écoutant cet événement, vous pouvez manipuler le contenu de la réponse avant
de l'envoyer.

`routing`
---------

### ~`routing.load_configuration`~

*Méthode de notification* : `notify`

*Notificateur par défaut* : `sfRouting`

L'événement `routing.load_configuration` est notifié quand le Factory de routage
charge la configuration du routage.

`task`
------

### ~`task.cache.clear`~

*Méthode de notification* : `notifyUntil`

*Notificateur par défaut* : `sfCacheClearTask`

| Paramètre | Description
| --------- | -----------
| `app`     | Le nom de l'application
| `type`    | Le type de cache (`all`, `config`, `i18n`, `routing`, `module`, et `template`)
| `env`     | L'environnement

L'événement `task.cache.clear` est notifié lorsque l'utilisateur vide le cache
avec la tâche CLI `cache:clear`.

`template`
----------

### ~`template.filter_parameters`~

*Méthode de notification* : `filter`

*Notificateur par défaut* : `sfViewParameterHolder`

L'événement `template.filter_parameters` est notifié avant que le fichier de la vue soit
rendu. En écoutant cet événement, vous pouvez accéder et manipuler les variables
passées au Template.

`user`
------

### ~`user.change_culture`~

*Méthode de notification* : `notify`

*Notificateur par défaut* : `sfUser`

| Paramètre | Description
| --------- | -----------
| `culture` | La culture de l'utilisateur

L'événement `user.change_culture` est notifié lorsque la culture de l'utilisateur est modifiée
lors de la requête.

### ~`user.method_not_found`~

*Méthode de notification* : `notifyUntil`

*Notificateur par défaut* : `sfUser`

| Paramètre   | Description
| ----------- | -----------
| `method`    | Le nom de la méthode manquante appelée
| `arguments` | Les arguments passés à la méthode

L'événement `user.method_not_found` est notifié lorsqu'une méthode n'est pas
définie dans la classe `sfUser`. En écoutant cet événement, une méthode peut
être ajoutée à la classe, sans utiliser l'héritage.

### ~`user.change_authentication`~

*Méthode de notification* : `notify`

*Notificateur par défaut* : `sfBasicSecurityUser`

| Paramètre       | Description
| --------------- | -----------
| `authenticated` | Si l'utilisateur est authentifié ou non

L'événement `user.change_authentication` est notifié lorsque le statut
d'authentification de l'utilisateur change.

`view`
------

### ~`view.configure_format`~

*Méthode de notification* : `notify`

*Notificateur par défaut* : `sfView`

| Paramètre  | Description
| ---------- | -----------
| `format`   | Le format requêté
| `response` | L'objet de la réponse
| `request`  | L'objet de la requête

L'événement `view.configure_format` est notifié par la vue quand la requête change
le paramètre 'sf_format'. L'événement est notifié après que symfony ait fait des
choses simples comme le changement ou le non changement de la mise en page. Cet événement permet
à la vue et l'objet de la réponse d'être changés selon le format
requêté.

### ~`view.method_not_found`~

*Méthode de notification* : `notifyUntil`

*Notificateur par défaut* : `sfView`

| Paramètre   | Description
| ----------- | -----------
| `method`    | Le nom de la méthode manquante appelée
| `arguments` | Les arguments passés à la méthode

L'événement `view.method_not_found` est notifié lorsqu'une méthode n'est pas
définie dans la classe `sfView`. En écoutant cet événement, une méthode peut
être ajoutée à la classe, sans utiliser l'héritage.

`view.cache`
------------

### ~`view.cache.filter_content`~

*Méthode de notification* : `filter`

*Notificateur par défaut* : `sfViewCacheManager`

| Paramètre  | Description
| ---------- | -----------
| `response` | L'objet de la réponse
| `uri`      | Le URI du contenu du cache
| `new`      | Si le contenu est nouveau ou non dans le cache

L'événement `view.cache.filter_content` est notifié lorsque le contenu est
récupéré du cache.
