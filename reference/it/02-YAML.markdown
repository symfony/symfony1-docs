Il Formato YAML
===============

La maggior parte dei file di configurazione in symfony sono in formato YAML.
Secondo il sito ufficiale di [YAML](http://yaml.org/), YAML è uno "standard
di serializzazione amichevole per tutti i linguaggi di programmazione".

YAML è un linguaggio semplice, che descrive dati. Come PHP, ha una sintassi
per i tipi semplici come stringhe, booleani, numeri a virgola mobile, interi.
Ma, diversamente da PHP, fa una distinzione tra array (sequenze) e hash
(mappature).

Questa sezione descrive l'insieme minimo di caratteristiche di cui si avrà
bisogno per utilizzare YAML come formato di file di configurazione in symfony,
sebbene il formato YAML sia in grado di descrivere strutture di dati molto
più complesse.

Scalari
-------

La sintassi per gli scalari è simile a quella del PHP.

### Stringhe

    [yml]
    Una stringa in YAML

-

    [yml]
    'Una stringa in YAML racchiusa tra apici singoli'

>**TIP**
>In una stringa tra apici singoli, un apice singolo `'` va raddoppiato:
>
>     [yml]
>     'Un apice singolo '' in una stringa tra apici singoli'

    [yml]
    "Una stringa in YAML tra apici doppi\n"

Gli stili tra apici sono utili quando una stringa inizia o finisce con uno
o più spazi significativi.

>**TIP**
>Lo stile con apici doppi fornisce un modo per esprimere stringhe arbitrarie,
> usando delle sequenze di escape `\`. È molto utile quando si ha bisogno
>di inserire un `\n` o un carattere unicode in una stringa.

Quando una stringa contiene degli "a capo", si può usare lo stile letterale,
indicato dalla barra verticale (`|`), per indicare che la stringa prosegue
su diverse righe. Nei letterali, gli "a capo" sono preservati:

    [yml]
    |
      \/ /| |\/| |
      / / | |  | |__

In alternativa, le stringhe possono essere scritte in stile indentato, usando
`>`, con ogni "a capo" sostituito da uno spazio:

    [yml]
    >
      Questa è una frase molto lunga
      che si estende su diverse righe in YAML
      ma che sarà resa come una stringa
      senza alcuna interruzione.

>**NOTE**
>Notare i due spazi prima di ogni riga nell'esempio precedente. Non
>appariranno nella risultante stringa PHP.

### Numeri

    [yml]
    # un intero
    12

-

    [yml]
    # un ottale
    014

-

    [yml]
    # un esadecimale
    0xC

-

    [yml]
    # un numero a virgola mobile
    13.4

-

    [yml]
    # un esponenziale
    1.2e+34

-

    [yml]
    # infinito
    .inf

### Valori nulli

I valori nulli in YAML possono essere espressi con `null` o `~`.

### Booleani

I booleani in YAML sono espressi con `true` e `false`.

### Date

YAML usa lo standard ISO-8601 per rappresentare le date:

    [yml]
    2001-12-14t21:59:43.10-05:00

-

    [yml]
    # data semplice
    2002-12-14

Insiemi
-------

Un file YAML è usato raramente per descrivere semplici scalari. La maggior
parte delle volte descrive un insieme. Un insieme può essere una sequenza
o una mappatura di elementi. Sequenze e mappature sono entrambe convertite
in array PHP.

Le sequenze usano un trattino seguito da uno spazio (`- `):

    [yml]
    - PHP
    - Perl
    - Python

Questo equivale al seguente codice PHP:

    [php]
    array('PHP', 'Perl', 'Python');

Le mappature usano "due punti" seguito da uno spazio (`: `) per denotare
ogni coppia chiave/valore:

    [yml]
    PHP: 5.2
    MySQL: 5.1
    Apache: 2.2.20

che equivale al seguente codice PHP:

    [php]
    array('PHP' => 5.2, 'MySQL' => 5.1, 'Apache' => '2.2.20');

>**NOTE**
>In una mappatura, una chiave può essere un qualsiasi scalare valido per YAML.

Il numero di spazi tra i "due punti" ed il valore non ha importanza, purché
ce ne sia almeno uno:

    [yml]
    PHP:    5.2
    MySQL:  5.1
    Apache: 2.2.20

YAML usa le indentazioni con uno o più spazi per descrivere degli insiemi
innestati:

    [yml]
    "symfony 1.0":
      PHP:    5.0
      Propel: 1.2
    "symfony 1.2":
      PHP:    5.2
      Propel: 1.3

Questo YAML è equivalente al seguente codice PHP:

    [php]
    array(
      'symfony 1.0' => array(
        'PHP'    => 5.0,
        'Propel' => 1.2,
      ),
      'symfony 1.2' => array(
        'PHP'    => 5.2,
        'Propel' => 1.3,
      ),
    );

C'è una cosa importante da ricordare quando si usa l'indentazione in un file
YAML: *l'indentazione deve essere fatta con uno o più spazi, mai con le
tabulazioni*.

Si possono innestare sequenze e mappature come si preferisce:

    [yml]
    'Capitolo 1':
      - Introduzione
      - Tipi di Evento
    'Capitolo 2':
      - Introduzione
      - Helper

YAML può anche usare degli stili continui per gli insiemi, utilizzando degli
indicatori espliciti al posto dell'indentazione, per denotare lo scope.

Una sequenza può essere scritta come lista separata da virgole e racchiusa
tra parentesi quadre (`[]`):

    [yml]
    [PHP, Perl, Python]

Una mappatura può essere scritta come lista separata da virgole con chiavi/valori
e racchiusa tra parentesi graffe (`{}`):

    [yml]
    { PHP: 5.2, MySQL: 5.1, Apache: 2.2.20 }

Si possono anche mescolare gli stili per aumentare la leggibilità:

    [yml]
    'Capitolo 1': [Introduzione, Tipi di Evento]
    'Capitolo 2': [Introduzione, Helper]

-

    [yml]
    "symfony 1.0": { PHP: 5.0, Propel: 1.2 }
    "symfony 1.2": { PHP: 5.2, Propel: 1.3 }

Commenti
--------

Si possono aggiungere commenti in YAML anteponendo un cancelletto (`#`):

    [yml]
    # Commento su una riga
    "symfony 1.0": { PHP: 5.0, Propel: 1.2 } # Commento a fine riga
    "symfony 1.2": { PHP: 5.2, Propel: 1.3 }

>**NOTE**
>I commenti sono semplicemente ignorati dall'interprete YAML e non hanno
>bisogno di essere indentati secondo il livello corrente di indentazione di
>un insieme.

File YAML dinamici
------------------

In symfony, un file YAML può contenere codice PHP, che viene valutato subito
prima dell'interpretazione:

    [php]
    1.0:
      versione: <?php echo file_get_contents('1.0/VERSION')."\n" ?>
    1.1:
      versione: "<?php echo file_get_contents('1.1/VERSION') ?>"

Occorre fare attenzione a non scombinare l'indentazione. Tenete a mente le
seguenti semplici regole quando aggiungete codice PHP ad un file YAML:


 * Le istruzioni `<?php ?>` devono sempre iniziare la riga o essere incluse in
   un valore.

 * Se un istruzione `<?php ?>` termina una riga, bisogna inserire esplicitamente
   un "a capo" ("\n").

<div class="pagebreak"></div>

Un Esempio Completo
-------------------

L'esempio seguente illustra la sintassi YAML spiegata in questa sezione:

    [yml]
    "symfony 1.0":
      end_of_maintainance: 2010-01-01
      is_stable:           true
      release_manager:     "Gregoire Hubert"
      description: >
        This stable version is the right choice for projects
        that need to be maintained for a long period of time.
      latest_beta:         ~
      latest_minor:        1.0.20
      supported_orms:      [Propel]
      archives:            { source: [zip, tgz], sandbox: [zip, tgz] }

    "symfony 1.2":
      end_of_maintainance: 2008-11-01
      is_stable:           true
      release_manager:     'Fabian Lange'
      description: >
        This stable version is the right choice
        if you start a new project today.
      latest_beta:         null
      latest_minor:        1.2.5
      supported_orms:
        - Propel
        - Doctrine
      archives:
        source:
          - zip
          - tgz
        sandbox:
          - zip
          - tgz
