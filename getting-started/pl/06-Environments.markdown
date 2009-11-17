Środowiska programistyczne
==========================

Jeśli spojrzysz w katalog `web/`, znajdziesz tam dwa plik PHP:
`index.php` oraz `frontend_dev.php`. Te pliki nazywają się **
kontorery dostępu zewnętrznego**; wszystkie zapytania do aplikacji 
odbywają się tylko przez nie. Ale do czego potrzebne są dwa kontrolery 
dostępu zewnętrznego dla każdej aplikacji?

Oba pliki dotyczą tej samej aplikacji, ale różnego **środowiska**.
Kiedy rozwijasz aplikację, z wyjątkiem kiedy rozwijasz ją bezpośrednio na 
serwerze produkcyjnym, potrzebujesz kilku środowisk:

  * **Środowisko programistyczne**: Środowisko wykorzystywane przez **programistów
    WWW** kiedy pracują nad aplikacją i dodają nową funkcjonalność, naprawiają
    błędy, ...

  * **Środowisko testowe**: Środowisko wykorzystywane podczas automatycznych testów
    aplikacjin.

  * **Środowisko startowe**: Środowisko wykorzystywane przez **użytkownika**
    który testuje aplikacje i zgłasza błędy oraz niepoprawną funkcjonalność. 

  * **Środowisko produkcyjne**: Środowisko dla **użytkowników końcowych** 
    korzystający z aplikacji.

Co powoduje że te środowiska są unikalne? W środowisku programistyczny na przykład,
aplikacja potrzebuje przechowywać logi wszystkich szczegółów o zapytaniu w celu
łatwego debugowania, ale za to system cache musi być wyłączony, aby wszystkie zmiany 
w kodzie były widoczne natychmiast. Czyli, środowisko programistyczne musi być zoptymalizowane
dla programistów. Najlepszy przykładem może być wystąpienie wyjątku. 
Aby ułatwić programiście szybsze znalezienie problemu, symfony pokazuje wyjątek, 
wraz z wszystkimi infromacjami dot. zapytania, bezpośrednio do przeglądarki:

![Błąd w środowisku programistycznym](http://www.symfony-project.org/images/getting-started/1_4/exception_dev.png)

Ale za to w środowisku produkcyjnym, cache musi zostać aktywowane i co jest istotne, 
aplikacja musi wyświetlić dostosowany komunikat błędu, bez pokazywania treści całego
wyjątku. Czyli, środowisko produkcyjne musi być zoptymalizowane na wydajność
oraz przyjazność użytkownika. 

![Bład w środowisku produkcyjnym](http://www.symfony-project.org/images/getting-started/1_4/exception_prod.png)

>**TIP**
>Jeżeli spróbujesz podejrzeć kontrolery w obu środowiskach, zobaczysz, że są one 
>identyczne, z wyjątkiem ustawienia środowiska:
>
>     [php]
>     // web/index.php
>     <?php
>
>     require_once(dirname(__FILE__).'/../config/ProjectConfiguration.class.php');
>
>     $configuration = ProjectConfiguration::getApplicationConfiguration('frontend', 'prod', false);
>     sfContext::createInstance($configuration)->dispatch();

Toolbar debugowania jest również wspaniałym przykładem wykorzystania środowisk. 
Pokazuje się on na wszystkich strona w środowisku programistycznym, dając Ci dostęp
do wielu informacji, za pomocą wyboru określonej zakładki: obecną konfigurację aplikacji
logi dla obecnego zapytania, listę zapytań SQL które zostały wysłane do bazy danych, 
wykorzystana pamięć oraz informacja o czasie generowania poszczególnych elementów.
