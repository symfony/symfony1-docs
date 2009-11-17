Wymagania
=========

Zanim rozpoczniesz instalacje symfony, sprawdź czy masz poprawnie wszystko  
zainstalowane i skonfigurowane. Poświęć trochę czasu na sumienne przeczytanie
tego rozdziału i przejdź przez wszystkie wymagane kroki, aby uniknąć trafienia 
w ślepy zaułek i tracenia czasu na szukanie przyczyny problemu. 

Wymagania oprogramowania
------------------------

Na początku, potrzebujesz sprawdzić, czy Twój komputer ma przygotowane środowisko
programistyczne do pracy. Przede wszystkim, potrzebujesz serwer stron WWW (na 
przykład Apache), serwer baz danych (MySQL, PostgreSQL, SQLite lub inny mechanizm 
kompatybilny z [PDO](http://www.php.net/PDO)), oraz PHP 5.2.4 lub starszy.

Interfejs wiersza pomeceń 
-------------------------

Framework symfony zarządzany jest przez wiersz poleceń (CLI), automatyzująć
większość Twojej pracy. Jeżeli jesteś użytkownikiem systemów UNIX-owych, pewnie
poczujesz się w domu.  Jeżeli korzystasz z systemu Windows, też Ci się spodoba, 
ale na początku konieczne będzie wywołanie kilku komend, po wywołaniu `cmd`.

>**NOTE**
>Środowisko UNIX-owe jest dużo bardziej przyjazne niż Windowsowe. 
>Jeżeli potrzebujesz korzystać z takich komend jak `tar`, `gzip` czy `grep` 
w Windowsie, możesz zainstalować program [Cygwin](http://cygwin.com/).
>Jeżeli lubisz eksperymentować, możesz również spróbować 
>[Windows Services for Unix](http://technet.microsoft.com/en-gb/interopmigration/bb380242.aspx).

Konfiguracja PHP
----------------

Konfiguracja PHP może znacznie się różnić w zależności od systemu operacyjnego 
a nawet różnej wersji systemu Linux, dlatego musisz ręcznie zweryfikować 
jakie są minimalne wymagania dla symfony.

Na początek, upewnij się że posiadasz zainstalowane PHP w wersji minimum 5.2.4
poprzez użycie w skrypcie funkcji `phpinfo()` lub uruchamiając komendę `php -v` 
w pasku komend. Pamiętaj, że czasem możesz mieć zainstalowane dwie różne różne 
wersje PHP: jedną w pasku komend i inną na stronie. 

Następnie pobierz plik weryfikujący konfigurację z adresu URL:

    http://sf-to.org/1.4/check.php

Zapisz skrypt gdzieś w katalogu domowym serwera stron.

Uruchom weryfikację konfiguracji wpisując w pasku komend:

    $ php check_configuration.php

Jeżeli pojawi się problem w konfiguracji PHP, otrzymasz na wyjściu informację
wraz z podpowiedzią co należy poprawić i jak to zrobić. 

Spróbuj również uruchomić ten skrypt w Twojej przeglądarce i popraw błędy, które 
mogą wystąpić. Należy to zrobić, ponieważ PHP może mieć różne pliki `php.ini`
dla tych dwóch środowisk, z różnymi konfiguracjami. 

>**NOTE**
>Nie zapomnij na koniec usunąć ten plik konfiguracyjny z Twojego 
>serwera. 

-

>**NOTE**
>Jeżeli twoim celem jest wypróbowanie symfony przez kilka godzin, możesz 
>zainstalować sandbox opisany w [Załączniku A](A-The-Sandbox). Jeżeli natomiast
>chcesz naprawdę rozpocząć działający projekt lub chcesz poznać dokładniej 
>symfony, kontynuuj tą lekturę. 
