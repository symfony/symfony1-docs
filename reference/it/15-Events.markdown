Eventi
======

I componenti principali di symfony sono disaccoppiati, grazie all'oggetto
`sfEventDispatcher`. Tale oggetto gestisce le comunicazioni tra i
componenti.

Ogni oggetto può notificare un evento al dispatcher e ogni altro oggetto
può connettersi al dispatcher per ascoltare un evento specifico.

Un evento non è altro che un nome composto da uno spazio di nomi e da un
nome, separati da un punto (`.`).

Uso
---

Si può notificare un evento iniziando con il creare un oggetto evento:

    [php]
    $event = new sfEvent($this, 'user.change_culture', array('culture' => $culture));

E notificarlo:

    $dispatcher->notify($event);

Il costruttore `sfEvent` accetta tre parametri:

  * L'"oggetto" di un evento (nella maggior parte dei casi, l'oggetto notificante,
    ma può anche essere `null`)
  * Il nome dell'evento
  * Un array di parametri da passare agli ascoltatori

Per ascoltare un evento, basta connettersi al nome dell'evento:

    [php]
    $dispatcher->connect('user.change_culture', array($this, 'listenToChangeCultureEvent'));

Il metodo `connect` accetta due parametri:

  * Il nome dell'evento
  * Un callable PHP da richiamare quando l'evento è notificato

Ecco un esempio di implementazione di un ascoltatore:

    [php]
    public function listenToChangeCultureEvent(sfEvent $event)
    {
      // change the message format object with the new culture
      $this->setCulture($event['culture']);
    }

L'ascoltatore prende l'evento come primo parametro. L'oggetto evento
ha diversi metodi per recuperare informazioni sull'evento:

  * `getSubject()`: Prende l'oggetto del metodo allegato all'evento
  * `getParameters()`: Restituisce i parametri dell'evento

Si può avere accesso all'oggetto evento anche come array, per ottenere
i suoi parametri.

Tipi di Evento
--------------

Gli eventi possono essere scatenati in tre diversi metodi:

 * `notify()`
 * `notifyUntil()`
 * `filter()`

### ~`notify`~

Il metodo `notify()` notifica a tutti gli ascoltatori. Gli ascoltatori non
possono restituire un valore e tutti gli ascoltatori hanno garanzia di
essere eseguiti.

### ~`notifyUntil`~

Il metodo `notifyUntil()` notifica a tutti gli ascoltatori, finché uno
non interrompe la catena, restituendo il valore `true`.

L'ascoltatore che interrompe la catena può anche richiamare il metodo
`setReturnValue()`.

Il notificatore può verificare se un ascoltatore ha processato l'evento,
richiamando il metodo `isProcessed()`:

    [php]
    if ($event->isProcessed())
    {
      // ...
    }

### ~`filter`~

Il metodo `filter()` notifica a tutti gli ascoltatori che possono filtrare
il valore dato, passato come secondo parametro dal notificatore e recuperato
dal callable dell'ascoltatore come secondo parametro. A tutti gli
ascoltatori è passato il valore e tutti devono restituire il valore
filtrato. Tutti gli ascoltatori hanno garanzia di essere eseguiti.

Il notificatore può ottenere il valore filtrato richiamando il metodo
`getReturnValue()`:

    [php]
    $ret = $event->getReturnValue();

<div class="pagebreak"></div>

Eventi
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

*Metodo di notifica*: `notify`

*Notificatori predefiniti*: diverse classi

| Parametro  | Descrizione
| ---------- | -----------
| `priority` | Il livello di priorità (`sfLogger::EMERG`, `sfLogger::ALERT`, `sfLogger::CRIT`, `sfLogger::ERR`, `sfLogger::WARNING`, `sfLogger::NOTICE`, `sfLogger::INFO`, or `sfLogger::DEBUG`)

L'evento `application.log` è il meccanismo usato da symfony per eseguire i log
delle richieste web (si veda il factory logger). L'evento è notificato dalla
maggior parte dei componenti principali di symfony.

### ~`application.throw_exception`~

*Metodo di notifica*: `notifyUntil`

*Notificatori predefiniti*: `sfException`

L'evento `application.throw_exception` è notificato quando un'eccezione viene
lanciata e non raccolta, durante l'esecuzione di una richiesta.

Si può ascoltare questo evento per fare qualcosa di speciale ogni volta
che viene lanciata un'eccezione non raccolta (come l'invio di una e-mail
o il log di un errore). Si può anche ridefinire il meccanismo predefinito
di gestione delle eccezioni di symfony, processando l'evento.

`command`
---------

### ~`command.log`~

*Metodo di notifica*: `notify`

*Notificatori predefiniti*: `sfCommand*` classes

| Parametro  | Descrizione
| ---------- | -----------
| `priority` | Il livello di priorità (`sfLogger::EMERG`, `sfLogger::ALERT`, `sfLogger::CRIT`, `sfLogger::ERR`, `sfLogger::WARNING`, `sfLogger::NOTICE`, `sfLogger::INFO`, or `sfLogger::DEBUG`)

L'evento `command.log` è il meccanismo usato da symfony per il log della
linea di comando (CLI) di symfony (si veda il factory logger).

### ~`command.pre_command`~

*Metodo di notifica*: `notifyUntil`

*Notificatori predefiniti*: `sfTask`

| Parametro   | Descrizione
| ----------- | -----------
| `arguments` | Un array di parametri da passare alla CLI
| `options`   | Un array di opzioni da passare alla CLI

L'evento `command.pre_command` è notificato subito prima che un task sia
eseguito.

### ~`command.post_command`~

*Metodo di notifica*: `notify`

*Notificatori predefiniti*: `sfTask`

L'evento `command.post_command` è notificato subito dopo che un task sia
eseguito.

### ~`command.filter_options`~

*Metodo di notifica*: `filter`

*Notificatori predefiniti*: `sfTask`

| Parametro         | Descrizione
| ----------------- | -----------
| `command_manager` | L'istanza `sfCommandManager`

L'evento `command.filter_options` è notificato prima che le opzioni del
task CLI siano analizzate. L'evento può essere usato per filtrare le
opzioni passate dall'utente.

`configuration`
---------------

### ~`configuration.method_not_found`~

*Metodo di notifica*: `notifyUntil`

*Notificatori predefiniti*: `sfProjectConfiguration`

| Parametro   | Descrizion
| ----------- | -----------
| `method`    | Il nome del metodo mancante richiamato
| `arguments` | I parametri passati al metodo

L'evento `configuration.method_not_found` è notificato quando un metodo non
è definito nella classe `sfProjectConfiguration`. Ascoltando questo evento,
si può aggiungere un metodo alla classe senza usare l'ereditarietà.

`component`
-----------

### ~`component.method_not_found`~

*Metodo di notifica*: `notifyUntil`

*Notificatori predefiniti*: `sfComponent`

| Parametro   | Descrizione
| ----------- | -----------
| `method`    | Il nome del metodo mancante richiamato
| `arguments` | I parametri passati al metodo

L'evento `component.method_not_found` è notificato quando un metodo non è
definito nella classe `sfComponent`. Ascoltando questo evento,
si può aggiungere un metodo alla classe senza usare l'ereditarietà.

`context`
---------

### ~`context.load_factories`~

*Metodo di notifica*: `notify`

*Notificatori predefiniti*: `sfContext`

L'evento `context.load_factories` è notificato una volta a richiesta
dall'oggetto `sfContext`, subito dopo che tutti i factory sono stati
inizializzati. È il primo evento a essere notificato con tutte le
classi principali inizializzate.

`controller`
------------

### ~`controller.change_action`~

*Metodo di notifica*: `notify`

*Notificatori predefiniti*: `sfController`

| Parametro | Descrizione
| --------- | -----------
| `module`  | Il nome del modulo da eseguire
| `action`  | Il nome dell'azione da eseguire

L'evento `controller.change_action` è notificato subito prima che
un'azione sia eseguita.

### ~`controller.method_not_found`~

*Metodo di notifica*: `notifyUntil`

*Notificatori predefiniti*: `sfController`

| Parametro   | Descrizione
| ----------- | -----------
| `method`    | Il nome del metodo mancante richiamato
| `arguments` | I parametri passati al modulo

L'evento `controller.method_not_found` è notificato quando un metodo
non è definito nella classe `sfController`. Ascoltando questo evento,
si può aggiungere un metodo alla classe senza usare l'ereditarietà.

### ~`controller.page_not_found`~

*Metodo di notifica*: `notify`

*Notificatori predefiniti*: `sfController`

| Parametro | Descrizione
| --------- | -----------
| `module`  | Il nome del modulo che ha generato l'errore 404
| `action`  | Il nome dell'azione che ha generato l'errore 404

L'evento `controller.page_not_found` è notificato quando si genera
un errore 404 durante la gestione di una richiesta.

Si può ascoltare questo evento per fare qualcosa di speciale quando
si verifica un errore 404, come ad esempio inviare una e-mail o
scrivere l'errore su un log.

`form`
------

### ~`form.post_configure`~

*Metodo di notifica*: `notify`

*Notificatori predefiniti*: `sfFormSymfony`

L'evento `form.post_configure` è notificato dopo dopo che ogni form è configurato.

### ~`form.filter_values`~

*Metodo di notifica*: `filter`

*Notificatori predefiniti*: `sfFormSymfony`

L'evento `form.filter_values` filtra gli array di parametri e file uniti
appena prima di fare il binding.

### ~`form.validation_error`~

*Metodo di notifica*: `notify`

*Notificatori predefiniti*: `sfFormSymfony`

| Parametro  | Descrizione
| ---------- | -------------------
| `error`    | L'istanza di errore

L'evento `form.validation_error` è notificato quando la validazione del form fallisce.

### ~`form.method_not_found`~

*Metodo di notifica*: `notifyUntil`

*Notificatori predefiniti*: `sfFormSymfony`

| Parametro   | Descrizione
| ----------- | -----------
| `method`    | Il nome del metodo chiamato mancante
| `arguments` | Gli argomento passati al metodo

L'evento `form.method_not_found` è notificato quando un metodo non è definito
nella classe `sfFormSymfony`. Ascoltando questo evento, un metodo può essere aggiunto
alla classe, senza utilizzare l'ereditarietà.

`plugin`
--------

### ~`plugin.pre_install`~

*Metodo di notifica*: `notify`

*Notificatori predefiniti*: `sfPluginManager`

| Parametro    | Descrizione
| ------------ | -----------
| `channel`    | Il canale del plugin
| `plugin`     | Il nome del plugin
| `is_package` | Se il plugin da installare è un pacchetto locale (`true`) o un pacchetto web (`false`)

L'evento `plugin.pre_install` è notificato subito prima che un plugin
sia installato.

### ~`plugin.post_install`~

*Metodo di notifica*: `notify`

*Notificatori predefiniti*: `sfPluginManager`

| Parametro    | Descrizione
| ------------ | -----------
| `channel`    | Il canale del plugin
| `plugin`     | Il nome del plugin

L'evento `plugin.post_install` è notificato subito dopo che un plugin
è stato installato.

### ~`plugin.pre_uninstall`~

*Metodo di notifica*: `notify`

*Notificatori predefiniti*: `sfPluginManager`

| Parametro    | Descrizione
| ------------ | -----------
| `channel`    | Il canale del plugin
| `plugin`     | Il nome del plugin

L'evento `plugin.pre_uninstall` è notificato subito prima che un plugin
sia disinstallato.

### ~`plugin.post_uninstall`~

*Metodo di notifica*: `notify`

*Notificatori predefiniti*: `sfPluginManager`

| Parametro    | Descrizione
| ------------ | -----------
| `channel`    | Il canale del plugin
| `plugin`     | Il nome del plugin

L'evento `plugin.post_uninstall` è notificato subito dopo che un plugin
sia disinstallato.

`request`
---------

### ~`request.filter_parameters`~

*Metodo di notifica*: `filter`

*Notificatori predefiniti*: `sfWebRequest`

| Parametro    | Descrizione
| ------------ | -----------
| `path_info`  | Il percorso della richiesta

L'evento `request.filter_parameters` è notificato quando i parametri
della richiesta sono inizializzati.

### ~`request.method_not_found`~

*Metodo di notifica*: `notifyUntil`

*Notificatori predefiniti*: `sfRequest`

| Parametro   | Descrizione
| ----------- | -----------
| `method`    | Il nome del metodo richiamato mancante
| `arguments` | I parametri passati al metodo

L'evento `request.method_not_found` è notificato quando un metodo non
è definito nella classe `sfRequest`. Ascoltando questo evento,
si può aggiungere un metodo alla classe senza usare l'ereditarietà.


`response`
----------

### ~`response.method_not_found`~

*Metodo di notifica*: `notifyUntil`

*Notificatori predefiniti*: `sfResponse`

| Parametro   | Descrizione
| ----------- | -----------
| `method`    | Il nome del metodo richiamato mancante
| `arguments` | I parametri passati al metodo

L'evento `response.method_not_found` è notificato quando un metodo non
è definito nella classe `sfResponse`. Ascoltando questo evento,
si può aggiungere un metodo alla classe senza usare l'ereditarietà.


### ~`response.filter_content`~

*Metodo di notifica*: `filter`

*Notificatori predefiniti*: `sfResponse`

L'evento `response.filter_content` è notificato prima che una risposta
sia inviata. Ascoltando questo evento, si può manipolare il contenuto
della risposta prima che sia inviata.

`routing`
---------

### ~`routing.load_configuration`~

*Metodo di notifica*: `notify`

*Notificatori predefiniti*: `sfRouting`

L'evento `routing.load_configuration` è notificato quando il factory del
routing carica la sua configurazione.

`task`
------

### ~`task.cache.clear`~

*Metodo di notifica*: `notifyUntil`

*Notificatori predefiniti*: `sfCacheClearTask`

| Parametro | Descrizione
| --------- | -----------
| `app`     | Il nome dell'applicazione
| `type`    | Il tipo di cache (`all`, `config`, `i18n`, `routing`, `module` e `template`)
| `env`     | L'ambiente

L'evento `task.cache.clear` è notificato quando l'utente pulisce la cache
dalla CLI con il task `cache:clear`.

`template`
----------

### ~`template.filter_parameters`~

*Metodo di notifica*: `filter`

*Notificatori predefiniti*: `sfViewParameterHolder`

L'evento `template.filter_parameters` è notificato prima che la vista di un
file sia resa. Ascoltando questo evento, si può accedere e manipolare le
variabili passate a un template.

`user`
------

### ~`user.change_culture`~

*Metodo di notifica*: `notify`

*Notificatori predefiniti*: `sfUser`

| Parametro | Descrizione
| --------- | -----------
| `culture` | The user culture

L'evento `user.change_culture` è notificato quando la cultura di un
utente viene cambiata durante una richiesta.

### ~`user.method_not_found`~

*Metodo di notifica*: `notifyUntil`

*Notificatori predefiniti*: `sfUser`

| Parametro   | Descrizione
| ----------- | -----------
| `method`    | Il nome del metodo richiamato mancante
| `arguments` | I parametri passati al metodo

L'evento `user.method_not_found` è notificato quando un metodo non
è definito nella classe `sfUser`. Ascoltando questo evento,
si può aggiungere un metodo alla classe senza usare l'ereditarietà.

### ~`user.change_authentication`~

*Metodo di notifica*: `notify`

*Notificatori predefiniti*: `sfBasicSecurityUser`

| Parametro       | Descrizione
| --------------- | -----------
| `authenticated` | Se l'utente è autenticato o meno

L'evento `user.change_authentication` è notificato quando lo stato
di autenticazione di un utente cambia.

`view`
------

### ~`view.configure_format`~

*Metodo di notifica*: `notify`

*Notificatori predefiniti*: `sfView`

| Parametro  | Descrizione
| ---------- | -----------
| `format`   | Il formato richiesto
| `response` | L'oggetto risposta
| `request`  | L'oggetto richiesta

L'evento `view.configure_format` è notificato dalla vista quando la
richiesta ha il parametro `sf_format` impostato. L'evento è
notificato dopo che symfony ha eseguito cose semplici, come cambiare,
impostare o de-impostare il layout. Questo evento consente agli oggetti
vista e risposta di essere modificati in base al formato richiesto.

### ~`view.method_not_found`~

*Metodo di notifica*: `notifyUntil`

*Notificatori predefiniti*: `sfView`

| Parametro   | Descrizione
| ----------- | -----------
| `method`    | Il nome del metodo richiamato mancante
| `arguments` | I parametri passati al metodo

L'evento `view.method_not_found` è notificato quando un metodo non
è definito nella classe `sfView`. Ascoltando questo evento,
si può aggiungere un metodo alla classe senza usare l'ereditarietà.

`view.cache`
------------

### ~`view.cache.filter_content`~

*Metodo di notifica*: `filter`

*Notificatori predefiniti*: `sfViewCacheManager`

| Parametro  | Descrizione
| ---------- | -----------
| `response` | L'oggetto risposta
| `uri`      | La URI del contenuto in cache
| `new`      | Se il contenuto in cache è nuovo o meno

L'evento `view.cache.filter_content` è notificato quando un contenuto
viene recuperato dalla cache.
