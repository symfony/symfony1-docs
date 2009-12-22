Konfiguracja projektu
=====================

W symfony, **aplikacja** udostępnia identyczny model danych dla każdego z 
**projektów**. Dla większości projektów, będziesz mieć dwie różne aplikacje:
dostęp zewnętrzny (frontend) i panel zarządzania (backend).

### Tworzenie projektu

W katalogu `sfproject/`, uruchom zadanie: symfony `generate:project` aby 
utworzyć projekt symfony:

    $ php lib/vendor/symfony/data/bin/symfony generate:project PROJECT_NAME

W Windowsie:

    c:\> php lib\vendor\symfony\data\bin\symfony generate:project PROJECT_NAME

Zadanie `generate:project` generuje domyślną strukturę katalogów i plików, które 
są potrzebne w projekcie symfony:

 | Katalog     | Opis      
 | ----------- | -----------------------------------------
 | `apps/`     | Wszystkie aplikacje w projekcie
 | `cache/`    | Pliki cache - pamięć podręczna frameworka
 | `config/`   | Pliki konfiguracyjne projektu
 | `data/`     | -
 | `lib/`      | Biblioteki i klasy projektu
 | `log/`      | Logi frameworka
 | `plugins/`  | Zainstalowane dodatki 
 | `test/`     | Testy jednostkowe i funkcjonalne
 | `web/`      | Katalog domowy (czytaj niżej)

>**TIP
>Dlaczego symfony generuje tyle plików? Jednym głównym celem korzystania z 
>wartościowego frameworka jest wystandaryzowane środowisko. Dzięki 
>ujednoliconej strukturze plików i katalogów każdy programista posiadający
>trochę wiedzy o symfony może przejąć prace nad każdym projektem w symfony.
>Ma to znaczenie w czasie, jaki musiałby poświęcić na poznanie kodu, naprawie
>błędów czy dodaniu nowej funkcjonalności. 

Zadanie `generate:project` tworzy również w katalogu domowym projektu skrót 
`symfony`, zmniejszając tym samym ilość znaków jakie musisz napisać aby uruchomić 
kolejne zadania.   
 
Tak więc, od tego momentu, zamiast wpisywać pełną ścieżkę do skryptu symfony,
możesz użyć skrótu `symfony`.

### Konfiguracja bazy danych

Framework symfony wspiera wszystkie bazy danych w pakiecie [PDO](http://www.php.net/PDO)
(MySQL, PostgreSQL, SQLite, Oracle, MSSQL, ...). Do wsparcia PDO, symfony
korzysta z dwóch wbudowanych narzędzi do mapowania obiektowo-relacyjnych (ORM): Propel-a and Doctrine-a.

Kiedy tworzysz nowy projekt, Doctrine jest domyślnie włączony. Konfiguracja bazy
danych aby korzystała z Doctrina sprowadza się do uruchomienia zadania `configure:database`:

    $ php symfony configure:database "mysql:host=localhost;dbname=dbname" root mYsEcret

Zadanie `configure:database` zawiera trzy argumenty: 
[~PDO DSN~](http://www.php.net/manual/en/pdo.drivers.php), nazwę użytkownika oraz 
hasło dostępu do bazy danych. Jeżeli nie potrzebujesz hasła do dostępu do bazy danych
na serwerze testowym, po prostu pomić trzeci argument.

>**TIP**
>Jeśli chcesz korzystać z Propela zamiast z Doctrina, dopisz `--orm=Propel` przy tworzeniu
>projektu w zadaniu `generate:project`. Oraz, jeżeli nie chcesz korzystać z mapowania
>ORM, napisz `--orm=none`.

### Tworzenie Aplikacji

Aby utworzyć dostęp zewnętrzny aplikacji (frontend), należy wywołać zadanie `generate:app`:

    $ php symfony generate:app frontend

>**TIP**
>Ponieważ skrót symfony jest wykonywalny, użytkownicy Unixowi mogą od teraz 
>zamiast wpisów '`php symfony`' podawać '`./symfony`'.
>
>W Windowsach możesz skopiować plik '`symfony.bat`' do katalogu projektu i używać 
>'`symfony`' zamiast '`php symfony`':
>
>     c:\> copy lib\vendor\symfony\data\bin\symfony.bat .

W zależności od nazwy aplikacji podawanej jako *argument* w zadaniu `generate:app` 
tworzona jest domyślna struktura konieczna dla naszej aplikacji
w katalogu `apps/frontend/`:

 | Katalog      | Opis
 | ------------ | -------------------------------------
 | `config/`    | Pliki konfiguracyjne aplikacji 
 | `lib/`       | Biblioteki i klasy aplikacji
 | `modules/`   | Kod aplikacji (MVC)
 | `templates/` | Pliki głównego szablonu

>**SIDEBAR**
>Bezpieczeństwo
>
>Domyślnie, zadanie `generate:app` zabezpiecza naszą aplikację przed dwiema 
>najbardziej powszechymi sposobami ataków w internecie. Naprawdę, symfony
>automatycznie dba o ~bezpieczeństwo|Bezpieczeństwo~ zabezpieczając je za nas.
>
>Aby zapobiec atakom ~XSS~, wywoływanie nieporządanych akcji (output escaping) jest 
>domyślnie włączone; oraz żeby zapobiec atakom ~CSRF~, od razu generowany jest  
>losowy klucz CSRF.
>
>Oczywiście, możesz zmienić te ustawienia definiując poniższe *opcje*:
>
>  * `--escaping-strategy`: Włącza lub wyłącza output escaping
>  * `--csrf-secret`: Włącza żeton sesji (session token) w formularzach
>
>Jeżeli nie wiesz za wiele na temat 
>[XSS](http://pl.wikipedia.org/wiki/XSS) lub
>[CSRF](http://en.wikipedia.org/wiki/CSRF), poświęć trochę czasu na pozanie tych 
>luk w bezpieczeństwie.

### Uprawnienia w strukturze katalogów

Zanim spróbujesz otworzyć swój nowo utworzony projekt, musisz nadać odpowiednie 
uprawnienia w katalogach `cache/` and `log/` na odpowiedni poziom, tak aby serwer
stron WWW miał prawa do modyfikacji tych miejsc:

    $ chmod 777 cache/ log/

>**SIDEBAR**
>Podpowiedź dla osób korzystających z narzędzia SCM Tool
>
>symfony potrzebuje mieć dostęp tylko do dwóch katalogów w projekcie, 
>`cache/` oraz `log/`. Treść tych katalogów powinna być ignorowana w Twoim 
>narzędziu SCM (na przykład poprzez nadanie właściwości `svn:ignore`, w przypadku 
>narzędzia Subversion).
