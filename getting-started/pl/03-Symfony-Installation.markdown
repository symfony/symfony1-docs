Instalacja symfony
==================

Tworzenie katalogu projektu
---------------------------

Przed instalacją symfony, na początku musisz stworzyć katalog, w którym 
będziesz trzymać wszystkie plik związane z Twoim projektem: 

    $ mkdir -p /home/sfproject
    $ cd /home/sfproject

Lub w Windows-ie:

    c:\> mkdir c:\dev\sfproject
    c:\> cd c:\dev\sfproject

>**NOTE**
>Użytkownicy Windowsa powinni uruchamiać symfony oraz swój nowy projekt
>w ścieżce, nie zawierającej żadnych spacji w nazwie. 
>W szczególności katalogu `Documents and Settings`, oraz tym samym w  
>`Moje dokumenty`.

-

>**TIP**
>Jeżeli utworzysz projekt symfony w katalogu domowym serwera stron, 
>nie będziesz musiał wprowadzać żadnych zmian na serwerze stron WWW. 
>Oczywiście na serwer produkcyjny, bardzo zalecamy skonfigurować serwer
>stron wg instrukcji opisanej w części dot. konfiguracji serwera stron.  

Wybór wersji Symfony
--------------------

Teraz możesz zainstalować symfony. Framework symfony ma kilka stabilnych wersji 
i powinieneś wybrać, która z nich chcesz zainstalować, po przeczytaniu 
[strony o instalacji](http://www.symfony.pl/instalacja/) na symfony.pl lub  
[instalation page](http://www.symfony-project.org/installation) na na oficjalnej 
stronie symfony.

W tym przewodniku zakładamy, że chcesz zainstalować symfony w wersji 1.4.

Wybór katalogu do instalacji symfony
-------------------------------------

Możesz zainstalować symfony globalnie dla całej maszyny lub ograniczyć się do 
Twojego projektu. Druga metoda jest zalecana tylko gdy projekt jest całkowicie 
niezwiązany z pozostałymi. Aktualizacja symfony w takim odseparowam projekcie nie 
spowoduje żadnych problemów w pozostałych projektach. Oznacza to, że możesz mieć 
różne projekty, z różnymi wersjami symfony i aktualizować je jeden po drugim 
wg Twojej potrzeby. 

Wśród wielu użytkowników, przyjęła się praktyka, że framework symfony instalowany jest
w katalogu projektu `lib/vendor`. Na początku utwórzmy sobie taki katalog:

    $ mkdir -p lib/vendor

Instalacja Symfony
------------------

### Instalacja z spakowanego pliku

Najłatwiejszą formą instalacji symfony, jest pobranie spakowanego pliku z wybraną
na stronie wersją symfony. Aby to zrobić, przejdź na stronę instalacyjną wersji
symfony [1.4](http://www.symfony-project.org/installation/1_4) for instance.


Pod linkiem "**Źródło**" lub "**Source Download**", znajdziesz pliki archiwum `.tgz`
lub `.zip`. Pobierz plik archiwum i umieść go w nowo utworzonym katalogu
`lib/vendor/`, rozpakuj go i zmień nazwę katalogu na `symfony`:

    $ cd lib/vendor
    $ tar zxpf symfony-1.4.0.tgz
    $ mv symfony-1.4.0 symfony
    $ rm symfony-1.4.0.tgz

W Windowsie, możesz rozpakować plik zip, korzystając Explorera Windows.
Po zmianie nazwy katalogu na `symfony`, struktura katalogów powinna być
podobna do `c:\dev\sfproject\lib\vendor\symfony`.

### Instalacja z repozytorium Subversion (zalecane)

Jeśli korzystasz z Subversion, dużo lepiej jest skorzystać z atrybutu `svn:externals`
aby umiejścić symfony w projekcie w katalogu `lib/vendor/`:

    $ svn pe svn:externals lib/vendor/

Jeżeli nie będzie problemów, powyższa komenda uruchomi domyślny edytor, dając Ci
dostęp do konfiguracji zewnętrznego repozytorium Subversion.


>**TIP**
>W Windowsie, możesz skorzystać narzędzia typu [TortoiseSVN](http://tortoisesvn.net/),
>w którym możesz uruchomić komendy bez konieczności korzystania z konsoli.

Jeśli jesteś tradycjonallistą, możesz również ograniczyć się do ściśle określonego
wydania (subversion tag):

    svn checkout http://svn.symfony-project.com/tags/RELEASE_1_4_0

Za każdym razem, gdy zostanie wydana nowa wersja (dowiesz się o tym na oficjalnym
[blogu](http://www.symfony-project.org/blog/)), wtedy będziesz musiał/a zmienić adres URL
na nową wersję. 

Jeśli chcesz zaufać naszym aktualizacjion, skorzystać z branchy 1.4:

    svn checkout http://svn.symfony-project.com/branches/1.4/

Korzystając z branchy, ustrzeżesz projekt od odkrytych błędów dzięki automatycznej
aktualizacji, za każdym razem gdy wywołasz `svn update`.
