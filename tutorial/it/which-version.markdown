Quale versione di symfony?
==========================

La documentazione di symfony è la stessa sia per symfony 1.3 che per symfony 1.4.
Essendo abbastanza insolito l'uso della stessa documentazione per versioni
diverse di un software, questa sezione spiega quali siano le principali
differenze tra le due versioni e come fare la scelta migliore per i propri
progetti.

Entrambe le versioni, symfony 1.3 e symfony 1.4, sono state rilasciate
contemporaneamente (alla fine del 2009). Di fatto, hanno entrambe lo
**stesso insieme di caratteristiche**. La sola differenza tra le due
versioni sta nel supporto della retrocompatibilità con le vecchie
versioni di symfony.

Symfony 1.3 è il rilascio che si dovrebbe usare se si ha bisogno di aggiornare
un vecchio progetto, che usa una vecchia versione di symfony (1.0, 1.1 o 1.2).
Ha uno strato di compatibilità e tutte le caratteristiche che sono state
deprecate durante il periodo di sviluppo della 1.3 sono ancora disponibili.
Questo vuol dire che l'aggiornamento è facile e sicuro.

Se si inizia un nuovo progetto, tuttavia, si dovrebbe usare symfony 1.4.
Questa versione ha le stesse caratteristiche di symfony 1.3, ma senza
tutte quelle deprecate, incluso l'intero livello di compatibilità.
Un altro grosso vantaggio in symfony 1.4 è il suo supporto prolungato.
Essendo un rilascio "Long Term Support" (Supporto a Lungo Termine),
avrà la manutenzione degli sviluppatori di symfony per tre anni (fino
a novembre 2012).

Ovviamente, si possono migrare i propri progetti a symfony 1.3 e poi
aggiornare gradualmente il codice, rimuovendo le caratteristiche deprecate
e infine spostarsi su symfony 1.4, per beneficiare del supporto a lungo
termine. Il tempo a disposizione è molto, perché symfony 1.3 sarà
supportato per un anno (fino a novembre 2010).

Siccome la documentazione non descrive le caratteristiche deprecate, tutti
gli esempi funzionano nello stesso modo in entrambe le versioni.
