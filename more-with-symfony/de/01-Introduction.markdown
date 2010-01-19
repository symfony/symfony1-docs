Einführung
============

*von Fabien Potencier*

Zu dem Zeitpunkt als dieses Buch geschrieben wurde, erreichte das
symfony-Projekt einen bedeutenden Meilenstein - seinen
[vierten Geburtstag](http://trac.symfony-project.org/changeset/1).
In nur vier Jahren wuchs das symfony-Framework zu einem der beliebtesten
PHP-Frameworks weltweit und Dienste wie
[Delicious](http://sf-to.org/delicious),
[Yahoo Bookmarks](http://sf-to.org/bookmarks)
und
[Daily Motion](http://sf-to.org/dailymotion) basieren darauf.
Mit dem aktuellen symfony-Release 1.4 (November 2009) stehen wir kurz vor
dem Ende eines Zyklus. Und dieses Buch ist der ideale Weg um diesen Zyklus
zu beenden. Es ist das letzte vom symfony-Projekt-Team veröffentlichte
Buch, das den symfony 1 Zweig behandelt. Das nächste Buch wird sich
größten Teils um symfony 2.0 drehen und Ende 2010 erscheinen.

Aus diesem Grund, aber auch wegen vieler anderer Gründe, die ich in diesem
Kapitel erläutere, ist dieses Buch etwas ganz Besonderes für uns.

Warum also noch ein weiteres Buch?
----------------------------------

Wir veröffentlichten bereits zwei Bücher über symfony 1.3 und 1.4:
"[Practical symfony](http://books.sensiolabs.com/book/9782918390169)" und
"[The symfony reference guide](http://books.sensiolabs.com/book/9782918390145)".
Ersteres eignet sich optimal für den Einstieg in symfony, da man hier
schrittweise die Framework-Grundlagen anhand der Entwicklung eines realen
Projekts kennenlernt. Letzteres ist ein Referenz-Buch, welches nahezu alle
Konfigurations-Informationen beinhaltet, die für die alltägliche Entwicklung
mit symfony nötig sind.

"Mehr mit symfony" ist ein Buch über weiter fortgeschrittene Themen. Es sollte
nicht das erste Buch sein, das Sie über symfony lesen. Vielmehr wird es
denjenigen helfen, die bereits diverse kleine Projekte mit symfony verwirklicht
haben. Wenn Sie schon immer wissen wollten, wie symfony unter der Haube
funktioniert oder wie Sie das Framework so erweitern können, dass es perfekt
auf ihre Bedürfnisse abgestimmt ist, dann ist dieses genau das richtige Buch
für Sie. Insofern möchte "Mehr mit symfony" Ihre symfony-Kenntnisse auf die
nächst höhere Ebene bringen.

Da das Buch eine Sammlung von Tutorials verschiedenartiger Themen ist, zögern
Sie nicht, die Kapitel in beliebiger Reihenfolge zu lesen, abhängig davon, was Sie mit dem
symfony-Framework verwirklichen möchten.

Über dieses Buch
----------------

Das Buch ist etwas Besonderes, weil es ein Buch geschrieben *von der Community*
für die Community ist. Dutzende Personen haben zu diesem Buch beigetragen -
von den Autoren, über die Übersetzer bis hin zu den Korrekturlesern wurde
eine große Menge an Aufwand und Anstrengung in das Buch gesteckt.

Dieses Buch wurde in nicht weniger als fünf Sprachen (Englisch, Französisch,
Italienisch, Spanisch und Japanisch) gleichzeitig veröffentlicht. Ohne die
ehrenamtliche Arbeit unserer Übersetzungs-Teams wäre das nicht möglich
gewesen.

Das Buch wurde dank des Open-Source-Geistes ermöglicht und wurde unter einer
Open-Source-Lizenz veröffentlicht. Alleine diese Tatsache ändert alles.
Es bedeutet nämlich, dass niemand für seine Arbeit an diesem Buch bezahlt
wurde. Alle Mitwirkenden arbeiteten hart an der Erschaffung des Buches -
weil sie es wollten. Einige wollten einfach nur ihr Wissen teilen, andere
der Community etwas zurück geben, wieder andere wollten dabei helfen, das
symfony Framework bekannter zu machen oder halfen einfach nur aus Spaß oder
um berühmt zu werden.

Dieses Buch wurde von zehn Autorinnen und Autoren geschrieben, die das
symfony-Framework Tag für Tag als Entwickler oder Projekt-Manager nutzen.
Sie haben ein detailliertes Wissen über das Framework und möchten das Wissen und ihre
Erfahrungen in den folgenden Kapiteln mit Ihnen teilen.

Danksagung
----------

Als ich im August 2009 zum ersten Mal daran dachte, ein weiteres Buch über
symfony zu schreiben, hatte ich eine verrückte Idee: Wie wäre es ein Buch
in zwei Monaten zu schreiben und es gleichzeitig in fünf
verschiedenen Sprachen zu veröffentlichen!
Natürlich war ich bei einem Projekt dieser Größenordnung auf die Mithilfe
der Community angewiesen. Ich sprach meine Idee zum ersten Mal bei der
PHP-Konferenz in Japan an und nach wenigen Stunden war das japanische
Übersetzungsteam startbereit. Das war einfach einmalig! Die Reaktionen der
anderen Autoren und Übersetzer waren genauso viel versprechend, sodass
"Mehr mit symfony" in kürzester Zeit verwirklicht wurde.

Ich möchte jedem Einzelnen danken, der - egal in welcher Art und Weise -
dabei geholfen hat, dieses Buch zu verwirklichen. Dazu gehören in keiner
bestimmten Reihenfolge:

Ryan Weaver, Geoffrey Bachelet, Hugo Hamon, Jonathan Wage, Thomas Rabaix,
Fabrice Bernhard, Kris Wallsmith, Stefan Koopmanschap, Laurent Bonnet, Julien
Madelin, Franck Bodiot, Javier Eguiluz, Nicolas Ricci, Fabrizio Pucci,
Francesco Fullone, Massimiliano Arione, Daniel Londero, Xavier Briand,
Guillaume Bretou, Akky Akimoto, Hidenori Goto, Hideki Suzuki, Katsuhiro Ogawa,
Kousuke Ebihara, Masaki Kagaya, Masao Maeda, Shin Ohno, Tomohiro Mitsumune,
und Yoshihiro Takahara.

Bevor es losgeht
----------------

Das Buch befasst sich sowohl mit symfony 1.3 als auch mit symfony 1.4. Da es
unüblich ist, jeweils ein Buch für zwei unterschiedliche Versionen einer
Software zu schreiben, werden in diesem Abschnitt die gravierendsten
Unterschiede zwischen den beiden Versionen erläutert. Außerdem wird geklärt,
welche Version für Ihre Projekte am besten geeignet ist.

Sowohl symfony 1.3 als auch symfony 1.4 erschienen ungefähr zum selben
Zeitpunkt (Ende 2009). Um genau zu sein beinhalten beide Versionen **exakt
die gleichen Features**. Der einzige Unterschied besteht in der
Abwärtskompatibilität zu älteren symfony-Versionen.

Symfony 1.3 sollten sie verwenden, wenn sie ein bereits existierendes symfony
(1.0, 1.1 oder 1.2)-Projekt aktualisieren möchten. Es ist abwärtskompatibel und
alle Features, die durch die Entwicklung von symfony 1.3 veraltet sind,
können weiterhin verwendet werden. Insgesamt gesehen ist das Upgrade auf
symfony 1.3 einfach, bequem und sicher.

Wenn Sie jedoch ein komplett neues Projekt beginnen, so sollten sie
symfony 1.4 verwenden. Wie oben beschrieben, beinhaltet diese Version
die gleichen Features wie symfony 1.3. Allerdings wurden alle veralteten
Features - genauso wie der komplette Kompatibilitätsmodus - entfernt.
Diese Version ist sauberer und sogar ein wenig schneller als symfony 1.3.
Ein weiterer großer Vorteil für symfony 1.4 ist der längerfristige Support.
Da es sich um ein Langzeit-Support-Release handelt, wird es drei Jahre lang
(bis November 2012) vom symfony-Kernteam gewartet.

Natürlich haben Sie auch die Möglichkeit Ihre Projekte auf symfony 1.3 zu
migrieren und dann nach und nach Ihren Code zu aktualisieren und die
veralteten Features zu entfernen, um dann schließlich zu symfony 1.4 zu
wechseln. So profitieren Sie ebenfalls von dem Langzeit-Support.
Für diesen Vorgang haben Sie eine Menge Zeit. Denn der Support für
symfony 1.3 wird noch bis November 2010 erhalten bleiben.

Da dieses Buch keine veralteten Features beinhaltet, funktionieren alle
Beispiele problemlos in beiden Versionen.
