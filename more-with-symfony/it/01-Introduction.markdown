Introduzione
============

*di Fabien Potencier*

Mentre questo libro viene scritto, il progetto symfony celebra un importante
traguardo: il suo [quarto compleanno](http://trac.symfony-project.org/changeset/1). 
In soli 4 anni il framework symfony è cresciuto al punto tale da diventare uno
dei framework PHP più popolari al mondo, alimentando siti come
[Delicious](http://sf-to.org/delicious),
[Yahoo Bookmarks](http://sf-to.org/bookmarks) e
[Daily Motion](http://sf-to.org/dailymotion). Ma, con il recente rilascio di 
symfony 1.4 (Novembre 2009), si sta per chiudere un ciclo e questo libro è
il modo migliore per chiuderlo. Detto questo, state per leggere l'ultimo libro
pubblicato dal team del progetto symfony dedicato al ramo 1. Il prossimo sarà
molto probabilmente dedicato a Symfony 2.0 e verrà pubblicato nel tardo 2010.

Per questa ragione, e molte altre di cui si parlerà in seguito, questo
libro è un po' speciale per noi.

Perché un altro libro?
----------------------

Ultimamente sono stati già pubblicati due libri su symfony 1.3 e 1.4 :
"[Practical symfony](http://books.sensiolabs.com/book/9782918390169)" e
"[The symfony reference guide](http://books.sensiolabs.com/book/9782918390145)".
Il primo è un ottimo modo per iniziare a imparare symfony, visto che è possibile
iniziare dalle basi del framework, attraverso lo sviluppo di un progetto reale
con un tutorial passo-passo. Il secondo è una guida di riferimento che contiene
praticamente ogni configurazione relativa a symfony e che può tornare utile
nella pratica giornaliera di sviluppo.

"Di più con symfony" è un libro dedicato agli argomenti più avanzati di symfony.
Non è il primo libro su symfony che dovrebbe essere letto, è un libro che può
aiutare tutti quelli che hanno già sviluppato diversi piccoli progetti con il
framework. Se avete sempre voluto sapere come symfony funziona sotto al cofano
o se volete estendere il framework in diversi modi, per farlo rispondere alle
vostre esigenze, questo libro fa per voi. "Di più con symfony" è completamente
dedicato a portare le conoscenze di symfony a un livello superiore.

Dato che il libro è costituito da una serie di tutorial riguardanti argomenti
diversi, è possibile leggere i vari capitoli in qualsiasi ordine, in base a ciò
che si sta provando a fare con il framework.

Riguardo questo libro
---------------------

Questo libro è speciale perché è *un libro scritto dalla comunità* per la 
comunità. Dozzine di persone hanno contribuito: dagli autori, ai traduttori,
ai revisori, un vasto insieme di sforzi profusi unicamente verso il libro.

Questo libro viene pubblicato in non meno di cinque lingue (inglese, francese,
italiano, spagnolo e giapponese). Il tutto non sarebbe stato possibile senza
l'importantissimo lavoro delle nostre squadre di traduttori.

Questo libro è stato reso possibile grazie allo *spirito Open-Source* ed è
rilasciato sotto una licenza Open-Source. Questo fatto da solo è molto
significativo. Nessuno è stato pagato per lavorare su questo libro: tutti i
contributori hanno lavorato sodo per la sua realizzazione perché era
quello che volevano. Ognuno voleva condividere la propria esperienza, dare
qualcosa alla comunità, aiutare a diffondere il verbo di symfony e, sicuramente, 
divertirsi e diventare famoso.

Questo libro è stato scritto da dieci autori che usano symfony quotidianamente,
come sviluppatori o project manager. Hanno un'approfondita conoscenza del
framework e hanno provato a condividere essa e la loro esperienza in 
questi capitoli.

Ammissioni
----------

Quando iniziai a pensare di scrivere un nuovo libro su symfony, nell'agosto del
2009, ebbi immediatamente un'idea un po' pazza: scrivere un libro in due mesi e 
pubblicarlo in cinque diverse lingue simultaneamente! Sicuramente coinvolgere
la comunità in un progetto così grande è fondamentale. Ho inziato a parlare
dell'idea durante la PHP conference in Giappone e in un paio d'ore il team
di traduttori giapponesi era pronto a partecipare. È stato bellissimo! La
risposta dagli autori e dai traduttori è stata incoraggiante e in un brevissimo
tempo "Di più con symfony" è nato.

Voglio ringraziare tutti quelli che hanno partecipato in qualche modo alla
creazione di questo libro. Sono inclusi, in nessun ordine particolare:

Ryan Weaver, Geoffrey Bachelet, Hugo Hamon, Jonathan Wage, Thomas Rabaix,
Fabrice Bernhard, Kris Wallsmith, Stefan Koopmanschap, Laurent Bonnet, Julien
Madelin, Franck Bodiot, Javier Eguiluz, Nicolas Ricci, Fabrizio Pucci,
Francesco Fullone, Massimiliano Arione, Daniel Londero, Xavier Briand,
Guillaume Bretou, Akky Akimoto, Hidenori Goto, Hideki Suzuki, Katsuhiro Ogawa,
Kousuke Ebihara, Masaki Kagaya, Masao Maeda, Shin Ohno, Tomohiro Mitsumune,
and Yoshihiro Takahara.

Prima di iniziare
-----------------

Questo libro è stato scritto sia per symfony 1.3 che per symfony 1.4. Scrivere
un singolo libro per due diverse versioni di un software è abbastanza strano, 
questa sezione spiega quali sono le principali differenze tra le due versioni
e come fare la scelta giusta nei progetti.

Entrambe le versioni symfony 1.3 e symfony 1.4 sono state rilasciate nello stesso
periodo (alla fine del 2009). In verità hanno le **stesse esatte funzionalità**.
L'unica differenza è il modo in cui supportano la compatibilità con le vecchie
versioni di symfony.

Symfony 1.3 è il rilascio da utilizzare se si ha la necessità di aggiornare un
progetto obsoleto, che utilizza una vecchia versione di symfony (1.0, 1.1, o 1.2).
Ha un livello di compatibilità per le vecchie versioni e tutte le funzionalità
che sono segnalate come deprecate durante lo sviluppo di symfony 1.3 sono 
disponibili. Questo significa che l'aggiornamento è facile, semplice e sicuro.

Se si inizia un nuovo progetto oggi, invece, bisognerebbe utilizzare symfony 1.4. 
Questa versione ha le stesse funzionalità di symfony 1.3 ma quelle deprecate,
incluso l'intero livello di compatibilità, sono state rimosse. Questa versione è
più pulita e anche leggermente più veloce di symfony 1.3. Un altro grosso
vantaggio derivante dall'utilizzo di symfony 1.4 è il suo supporto a lungo
termine. Essendo una release con Supporto a Lungo Termine (LTS), verrà mantenuta dal 
team di symfony per tre anni (fino a novembre 2012).

Certamente è possibile migrare i propri progetti a symfony 1.3 e poi pian piano
aggiornare il codice per rimuovere le funzionalità deprecate e alla fine
passare a symfony 1.4 per beneficiare del supporto a lungo termine. Si ha molto
tempo per pianificare il passaggio a symfony 1.3, visto che verrà supportato
per un anno (fino novembre 2010).

Siccome questo libro non descrive le funzionalità deprecate, tutti gli esempi
funzionano ugualmente in entrambe le versioni.
