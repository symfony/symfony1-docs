Il file di configurazione databases.yml
=======================================

La configurazione ~`databases.yml`~ consente la configurazione della
connessione al database. È usata da entrambi gli ORM preinstallati con symfony: Propel e
Doctrine.

Il file principale di configurazione `databases.yml` per un progetto può essere trovato
nella cartella `config/`.

>**NOTE**
>La maggior parte delle volte, tutte le applicazioni di un progetto condividono lo stesso
>database. Questo è il motivo per cui il principale file di configurazione del database è
>nella cartella `config/` del progetto. Naturalmente si può non tenere conto della configurazione
>predefinita, definendo un file di configurazione `databases.yml`
>nelle cartelle di configurazione dell'applicazione.

Come discusso in sede di introduzione, il file `databases.yml` è
[**consapevole dell'ambiente**](#chapter_03_consapevolezza_dell_ambiente), trae beneficio
dal [**meccanismo di configurazione a cascata**](#chapter_03_configurazione_a_cascata),
e può includere [**costanti**](#chapter_03_costanti).

Ogni connessione descritta nel file `databases.yml` deve comprendere un nome, un nome
del gestore di classe del database e un set di parametri (`param`) usati per configurare
l'oggetto database:

    [yml]
    CONNECTION_NAME:
      class: CLASS_NAME
      param: { ARRAY OF PARAMETERS }

Il nome `class` dovrebbe estendere la classe base `sfDatabase`.

Se la classe che gestisce il database non può essere autocaricata, un `file` percorso può essere
definito e sarà automaticamente incluso prima che il factory sia creato:

    [yml]
    CONNECTION_NAME:
      class: CLASS_NAME
      file:  ABSOLUTE_PATH_TO_FILE

>**NOTE**
>Il file di configurazione `databases.yml` è salvato nella cache come file PHP; il
>processo è gestito automaticamente dalla [classe](#chapter_14_config_handlers_yml)
>~`sfDatabaseConfigHandler`~.

-

>**TIP**
>La configurazione del database può anche essere configurata utilizzando
>il task `database:configure`.  Questo task aggiorna il file `databases.yml`
>in base ai parametri che gli vengono passati.

Propel
------

*Configurazione predefinita*:

    [yml]
    dev:
      propel:
        param:
          classname:  DebugPDO

    test:
      propel:
        param:
          classname:  DebugPDO

    all:
      propel:
        class:        sfPropelDatabase
        param:
          classname:  PropelPDO
          dsn:        mysql:dbname=##PROJECT_NAME##;host=localhost
          username:   root
          password:   
          encoding:   utf8
          persistent: true
          pooling:    true

I seguenti parametri possono essere personalizzati nella sezione `param`:

 | Chiave       | Descrizione                              | Valore predefinito |
 | ------------ | ---------------------------------------- | ------------------ |
 | `classname`  | La classe adattatore per Propel          | `PropelPDO`        |
 | `dsn`        | Il DSN PDO (obbligatorio)                | -                  |
 | `username`   | Nome utente per il database              | -                  |
 | `password`   | Password per il database                 | -                  |
 | `pooling`    | Abilita il pooling                       | `true`             |
 | `encoding`   | L'insieme di caratteri predefinito       | `UTF-8`            |
 | `persistent` | Per creare connessioni persistenti       | `false`            |
 | `options`    | Un insieme di opzioni per Propel         | -                  |

Doctrine
--------

*Configurazione predefinita*:

    [yml]
    all:
      doctrine:
        class:        sfDoctrineDatabase
        param:
          dsn:        mysql:dbname=##PROJECT_NAME##;host=localhost
          username:   root
          password:   
          attributes:
            quote_identifier: false
            use_native_enum: false
            validate: all
            idxname_format: %s_idx
            seqname_format: %s_seq
            tblname_format: %s

I seguenti parametri possono essere personalizzati sotto la sezione `param`:

 | Chiave       | Descrizione                              | Valore predefinito |
 | ------------ | ---------------------------------------- | ------------------ |
 | `dsn`        | Il DSN PDO (obbligatorio)                | -                  |
 | `username`   | Nome utente per il database              | -                  |
 | `password`   | Password per il database                 | -                  |
 | `encoding`   | L'insieme di caratteri predefinito       | `UTF-8`            |
 | `attributes` | Un insieme di attributi per Doctrine     | -                  |

I seguenti attributi possono essere personalizzati sotto la sezione `attributes`:

 | Chiave              | Descrizione                                   | Valore predefinito |
 | ------------------- | --------------------------------------------- | ------------------ |
 | `quote_identifier`  | Per mettere gli identificatori tra virgolette | `false`            |
 | `use_native_enum`   | Per usare l'enum nativo                       | `false`            |
 | `validate`          | Per abilitare la validazione dei dati         | `true`             |
 | `idxname_format`    | Formato per i nomi degli indici               | `%s_idx`           |
 | `seqname_format`    | Formato per i nomi delle sequenze             | `%s_seq`           |
 | `tblname_format`    | Formato per i nomi delle tabelle              | `%s`               |
