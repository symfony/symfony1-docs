Dodatek A - Sandbox
===================

Jeśli chcesz jedynie wypróbować symfony i poświęcic na to tylko kilka godzin, 
w tym rozdziale prezentujemy, jak można robić to szybko. Jeżeli natomiast zależy Tobie
na rozpoczęciu własnego projektu, zalecamy pominąć ten rozdział i od rozpocząć 
[Instalacji symfony](04-Instalacja-symfony#chapter_04).

Najszybszym sposobem na eksperymentowanie z symfony, jest zainstalowanie sandboxa. 
Sandbox to bardzo-łatwy-do-instalacji spakowany projekt symfony, od razu skonfigurowany
z kilkoma delikatnym domyślnymi ustawieniami. Jest to najlepszy sposób na wypróbowanie
symfony bez poświęcania czasu na poprawną instalację, które są zgodne z przyjętymi
praktykami. 

>**CAUTION**
>Sandbox domyślnie jest skonfigurowany z bazą danych SQLite, dlatego
>należy sprawdzić czy serwer stron WWW PHP wspiera SQLite (zobacz 
>[Wymagania](rozdział 02-Prerequisites#chapter_02)). Możesz również
>przeczytać rozdział [Konfiguracja bazy danych](05-Konfiguracja-projektu#chapter_05_sub_configuring_the_database)
>aby zobaczyć jak zmienić bazę danych w Sandboxie.

Możesz pobrać sandbox symfony w formatach `.tgz` lub `.zip` na stronie instalacji
symfony, na [stronie instalacji](http://www.symfony-project.org/installation/1_4)
lub też przechodząc na:

    http://www.symfony-project.org/get/sf_sandbox_1_4.tgz

    http://www.symfony-project.org/get/sf_sandbox_1_4.zip

Rozpakuj plik, gdzieś w Twoim katalogu domowym serwera stron, i gotowe. 
Twój projekt symfony jest już dostępny, gdy otworzysz skrypt `web/index.php`
w przeglądarce.

>**CAUTION**
>Trzymanie wszystkich plików symfony w katalogu domowym jest dopuszczalne tylko
>do testów na komputerze lokalnym. Jest to bardzo zła praktyka na serwerze
>produkcyjnym serwerze, udostępniając wszystkie wewnętrzne skrypty aplikacji
>widoczne dla użytkownika końcowego. 

Możesz zakończyć instalację, czytając rozdziały 
[Konfiguracja serwera WWW](06-Web-Server-Configuration#chapter_06)
oraz [Środowisko](07-Environments#chapter_07).

>**NOTE**
>Jako że sandbox jest normalnym projektem symfony, w którym niektóre działania
>zostały wykonane za Ciebie oraz konfiguracja została zmieniona, bardzo łatwo
>wykorzystać go jako miejsce startowe dla nowego projektu. Jednak należy pamiętać,
>że prawdopodobnie będzie konieczne dostosowanie konfiguracji; na przykład
>zmiana ustawień dotyczących bezpieczeństwa (zobacz poradnik dotyczący 
>konfiguracji XSS oraz CSRF).
