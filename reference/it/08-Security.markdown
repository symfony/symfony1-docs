Il file di configurazione security.yml
======================================

Il file di configurazione ~`security.yml`~ descrive le regole di autenticazione
e autorizzazione per un'applicazione symfony.

>**TIP**
>Le informazioni di configurazione del file `security.yml` sono usate dalla
>classe factory [`user`](#chapter_05_user) (predefinita `sfBasicSecurityUser`).
>L'esecuzione dell'autenticazione e dell'autorizzazione è
>fatta da `security` [filter](#chapter_12_security).

Quando un'applicazione è creata, symfony genera un file predefinito
`security.yml`, nella cartella dell'applicazione `config/`, che descrive la sicurezza per
l'intera applicazione (sotto la chiave `default`):

    [yml]
    default:
      is_secure: false

Come discusso in sede di introduzione, il file `security.yml` trae benefici dal
 [**meccanismo di configurazione a cascata**](#chapter_03_configurazione_a_cascata)
e può includere [**costanti**](#chapter_03_costanti).

La configurazione predefinita dell'applicazione può essere sovrascritta per un modulo,
creando un file `security.yml` nella cartella `config/` del modulo stesso. Le
chiavi principali sono nomi di azioni senza il prefisso `execute` (ad esempio `index`
per il metodo `executeIndex`).

Per determinare se una azione è sicura o no, symfony cerca le informazioni
nel seguente ordine:

  * una configurazione per l'azione specifica nel file della configurazione del modulo,
    se esiste;

  * una configurazione per l'intero modulo nel file di configurazione del modulo,
    se esiste (sotto la chiave `all`);

  * la configurazione predefinita dell'applicazione (sotto la chiave `default`).

Le stesse regole di precedenza sono usate per determinare le credenziali necessarie per
accedere ad una azione.

>**NOTE**
>Il file di configurazione `security.yml` è memorizzato nella cache come file PHP; il
>processo è gestito automaticamente dalla [classe](#chapter_14_config_handlers_yml)
>~`sfSecurityConfigHandler`~.

~Autenticazione~
----------------

La configurazione predefinita di `security.yml`, installato in modo predefinito per ogni
applicazione, autorizza l'accesso a chiunque:

    [yml]
    default:
      is_secure: false

Con l'impostazione della chiave ~`is_secure`~ su `true` nel file dell'applicazione
 `security.yml`, l'intera applicazione richiederà l'autenticazione per tutti gli utenti.

>**NOTE**
>Quando un utente non autenticato prova ad accedere ad una azione messa in sicurezza, symfony
>inoltra la richiesta all'azione `login` configurata in `settings.yml`.

Per modificare i requisiti di autenticazione di un modulo, creare un file `security.yml`
nella cartella `config/` del modulo e definire una chiave `all`:

    [yml]
    all:
      is_secure: true

Per modificare i requisiti di autenticazione di una singola azione di un modulo, creare
un file `security.yml` nella cartella `config/` del modulo e definire una
chiave dopo il nome dell'azione:

    [yml]
    index:
      is_secure: false

>**TIP**
>Non è possibile mettere in sicurezza l'azione di login. Questo per evitare una ricorsione
>infinita.

~Autorizzazione~
----------------

Quando un utente è autenticato, l'accesso ad alcune azioni può essere maggiormente
limitato definendo delle *~credenziali~*. Quando le credenziali sono definite, un utente
deve avere le credenziali richieste, per accedere all'azione:

    [yml]
    all:
      is_secure:   true
      credentials: admin

Il sistema di credenziali di symfony è semplice e potente. Una credenziale è una
stringa che può rappresentare tutto quello di cui si ha bisogno per descrivere il modello
di sicurezza dell'applicazione (come gruppi e permessi).

La chiave `credentials` supporta operazioni booleane per descrivere complessi 
requisiti di credenziali, utilizzando la notazione array.

Se un utente deve avere la credenziale A **e** la credenziale B, inserire le
credenziali con parentesi quadre:

    [yml]
    index:
      credentials: [A, B]

Se un utente deve avere la credenziale A **o** la credenziale B, inserire le
credenziali con due paia di parentesi quadre:

    [yml]
    index:
      credentials: [[A, B]]

È possibile anche mischiare e combinare parentesi per descrivere ogni tipo di espressione booleana
con qualunque numero di credenziali.
