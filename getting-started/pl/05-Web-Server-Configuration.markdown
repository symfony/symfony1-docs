Konfiguracja serwera WWW
========================

Brzydkie rozwiązanie
--------------------

W poprzednim rozdziale, utworzyłeś katalog w którym trzymasz projekt. 
Jeśli utworzyłeś go gdzieś w katalogu domowym na twoim serwerze WWW, możesz
już otworzyć projekt za pomocą przeglądarki internetowej.

Oczywiście, nie wymagało to żadnej konfiguracji i jest to bardzo szybkie do zrobienia,
ale spróbuj np. otworzyć plik `config/databases.yml` w przeglądarce, aby zrozumieć 
jak groźne konsekwencje ma takie leniwe działanie. Jeżeli któryś z Twoich użytkowników 
pracował kiedyś z symfony, będzie miał dostęp do wielu newralgicznych plików.

**Nigdy, ale to nigdy nie konfiguruj tak serwera produkcyjnego** i przeczytaj następny
dział, aby poznać jak poprawnie skonfigurować swój serwer WWW. 

Bezpieczne rozwiązanie
----------------------

Dobrą praktyką jest umieszczenie w katalogu domowy tylko tych plików, które są
potrzebne do otworzenia strony www, takie jak style, JavaSkrypty, czy obrazki. 
Domyślnie, zachęcamy do trzymania tych plików w katalogu `web/`, znajdującego się 
w katalogu symfony. 

Jeśli spojrzysz do tego katalogu, znajdziesz tam kilka podkatalogów do korzystania
na stronie (`css/` i `images/`) oraz dwa pliki kontrolerów dostępu zewnętrznego (frontend). 
Kontrolerami są pliki PHP, które jako jedyne powinny się znajdować w głównym katalogu. 
Wszystkie pozostałe pliki PHP powinny być niedostępne z przeglądarki, co stanowi dobre
rozwiązanie od strony bezpieczeństwa. 

### Konfiguracja serwera WWW

Nadszedł czas do zmiany konfiguracji Apacha, aby nasz nowy projekt był dostępny
dla użytkowników w Internecie. 

Wyszukaj, następnie otwórze plik konfiguracyjny `httpd.conf` i dodaj na jego końcu
następującą konfigurację:

    # Be sure to only have this line once in your configuration
    NameVirtualHost 127.0.0.1:8080

    # This is the configuration for your project
    Listen 127.0.0.1:8080

    <VirtualHost 127.0.0.1:8080>
      DocumentRoot "/home/sfproject/web"
      DirectoryIndex index.php
      <Directory "/home/sfproject/web">
        AllowOverride All
        Allow from All
      </Directory>

      Alias /sf /home/sfproject/lib/vendor/symfony/data/web/sf
      <Directory "/home/sfproject/lib/vendor/symfony/data/web/sf">
        AllowOverride All
        Allow from All
      </Directory>
    </VirtualHost>

>**NOTE**
>Alias `/sf` daje ci dostęp do grafik i javascriptów, które są potrzebne
>do poprawnego pokazywania domyślnych stron w symfony oraz paska debugowania. 
>
>W Windowsie, potrzebujesz zamienić linię `Alias` na coś takiego: 
>
>     Alias /sf "c:\dev\sfproject\lib\vendor\symfony\data\web\sf"
>
>Oraz `/home/sfproject/web` powinno być zmienione na:
>
>     c:\dev\sfproject\web

Ta konfiguracja pozwoli Apachowi słuchać na porcie `8080` na Twojej maszynie, 
dlatego strina będzie dostępna pod adresem URL:

    http://localhost:8080/

Możesz zmienić port `8080` na inny numer, ale pamiętaj że numery większe niż `1024` 
nie wymagają uprawnień administratora. 

>**SIDEBAR**
>Konfiguracja własnej domeny
>
>Jeżeli jesteś administratorem własnego serwera, dobrze jest tworzyć
>hosty virtualne, zamiast dodawać nowy port za każdym razem jak rozpoczynasz nowy
>projekt. Zamiast wybierać port i dodawać wartość `Listen`,
>wybierz nazwę domeny (na przykład istniejącą domenę z dopisanym na końcu
>`.localhost`) i dodaj wartość `ServerName`:
>
>     # This is the configuration for your project
>     <VirtualHost 127.0.0.1:80>
>       ServerName www.myproject.com.localhost
>       <!-- same configuration as before -->
>     </VirtualHost>
>
>Nazwa domeny `www.myproject.com.localhost` wykorzystywana w konfiguracji Apacha
>została zadeklarowana lokalnie. Jeżeli korzystasz z Linuksa, możesz ustawić 
>host w pliku `/etc/hosts`. Jeżeli masz Windowsa XP, plik ten znajduje się w
>w katalogu `C:\WINDOWS\system32\drivers\etc\`.
>
>Dopisz następującą linię:
>
>     127.0.0.1 www.myproject.com.localhost

### Test nowej konfiguracji 

Zrestartuj Apacha i sprawdź, czy teraz masz dostęp do Twojej nowej aplikacji, 
poprzez otwarcie przeglądarki i wpisanie `http://localhost:8080/index.php/`, lub
`http://www.myproject.com.localhost/index.php/` w zależności od ustawionej konfiguracji 
Apacha, jaką wybrałeś w poprzednim podrozdziale.  

![Konfiguracja](http://www.symfony-project.org/images/getting-started/1_3/congratulations.png)

>**TIP**
>Jeżeli posiadasz zainstalowany moduł `mod_rewrite`, możesz usunąć część
>`index.php/` z adresu URL. Jest to możliwe dzięki zastosowaniu reguł 
>przekierowujących, znajdujących się w pliku `web/.htaccess`.

Możesz również spróbować otworzyć aplikację w środowisku programistycznym
(zobacz następny rozdział na temat środowisk ) wpisując adres URL:

    http://www.myproject.com.localhost/frontend_dev.php/

Toolbar debugowania powinien się pojawić w prawym górnym rogu, razem z małymi 
ikonami, które świadczą o tym, że alias `sf` został skonfigurowany poprawnie. 

![toolbar debugowania](http://www.symfony-project.org/images/getting-started/1_3/web_debug_toolbar.png)

>**Note**
>Konfiguracja jest trochę inna, jeżeli próbujesz ustawić symfony na serwerze IIS
>w środowisku Windows. Zobacz jak możesz skonfigurować to w 
>[powiązanym poradniku](http://www.symfony-project.com/cookbook/1_0/web_server_iis).
