Windows y Symfony
=================

*por Laurent Bonnet*

Introducción
------------

Este capítulo presenta un nuevo tutorial paso a paso sobre la instalación,
configuración y las pruebas funcionales del framework Symfony sobre Windows Server
2008.

Para poder seguir este tutorial, te recomendamos que utilices un servidor dedicado
conectado a Internet. Por supuesto también puedes seguirlo en un servidor local
o en una máquina virtual en tu ordenador.

### Los motivos de un nuevo tutorial

Actualmente el sitio web de Symfony incluye dos artículos sobre el uso de
Symfony sobre Microsoft Internet Information Server (IIS): 
*[symfony on IIS](http://trac.symfony-project.org/wiki/symfonyOnIIS)* y
*[Installing symfony on IIS](http://www.symfony-project.org/cookbook/1_2/en/web_server_iis)*.
El problema de estos artículos es que utilizan versiones antiguas de Microsoft
Windows, por lo que no hacen uso de Windows Server 2008 (lanzado en febrero de
2008) y que incluye muchos cambios interesantes para los programadores de PHP:

 * IIS 7, la versión que incluye Windows Server 2008, ha sido reescrita por
   completo para que su diseño sea modular.

 * IIS 7 ha demostrado ser muy fiable, necesitando muy pocos parches de
   Windows Update desde su lanzamiento.

 * IIS 7 también incluye el acelerador FastCGI, que aprovecha el modelo de hilos
   nativo de Windows gracias a su diseño multi-hilo.

 * La implementación FastCGI de PHP supone un rendimiento de ejecución entre 5
   y 10 veces mejor, sin cache, comparado con los habituales ISAPI o CGI de PHP
   sobre Windows y IIS.

 * Recientemente, Microsoft ha presentado un nuevo acelerador para PHP que en
   el momento de escribir estas líneas se encontraba en estado *release candidate*.

>**SIDEBAR**
>Ampliación prevista de este tutorial
>
>Se está preparando un suplemento de este capítulo que se publicará en el sitio
>web de Symfony poco después de la publicación de este libro. En ese apéndice
>se explicará la conexión con bases de datos MS SQL Server mediante PDO, algo
>sobre lo que Microsoft está trabajando muy activamente.
>
>      [PHP_PDO_MSSQL]
>      extension=php_pdo_mssql.dll
>
>Actualmente, el mejor rendimiento se obtiene utilizando el driver nativo para
>PHP 5 de SQL Server creado por Microsoft y publicado como software libre. La
>versión actual para Windows es la 1.1 y se instala en forma de una nueva
>extensión DLL de PHP:
>
>      [PHP_SQLSRV]
>      extension=php_sqlsrv.dll
>
>Se puede utilizar como base de datos tanto Microsoft SQL Server 2005 como la
>versión 2008. El tutorial planeado tratará el uso de la edición que está
>disponible gratuitamente: SQL Server Express.

### Cómo seguir este tutorial en diferentes sistemas Windows, incluyendo los de 32 bits

Este capítulo se ha escrito específicamente para la versión de 64 bits de
Windows Server 2008. No obstante, debería ser posible utilizar otras versiones
diferentes sin ningún tipo de problema.

>**NOTE**
>La versión exacta del sistema operativo utilizado en los pantallazos se llama
>*Windows Server 2008 Enterprise Edition con Service Pack 2, edición 64-bit*.

#### Versiones de 32 bits de Windows

Este tutorial se puede seguir fácilmente en las versiones de 32 bits de Windows
reemplazando las siguientes referencias en el texto:

 * En las ediciones de 64 bits: `C:\Program Files (x86)\` y `C:\Windows\SysWOW64\`

 * En las ediciones de 32 bits: `C:\Program Files\` y `C:\Windows\System32\`

#### Ediciones diferentes a la Enterprise

Si no dispones de la versión *Enterprise*, tampoco es un problema. El contenido
de este capítulo se puede portar fácilmente a otras ediciones de Windows Server:
Windows Server 2008 Web, Standard o Datacenter Windows Server 2008 Web,
Standard o Datacenter con Service Pack 2 Windows Server 2008 R2 Web,
Standard, Enterprise o Datacenter.

Todas las ediciones de Windows Server 2008 R2 están disponibles exclusivamente
como sistema operativo de 64 bits.

#### Sobre las ediciones internacionales

La configuración regional utilizada en los pantallazos es `en-US`. Para el
tutorial también se instaló el paquete de idioma para Francia.

También se puede ejecutar el tutorial en los sistemas operativos Windows de
tipo cliente: Windows XP, Windows Vista y Windows 7 tanto en versión x64 como x86.

### Servidor web utilizado

El servidor web utilizado en el tutorial es Microsoft Internet Information Server
versión 7.0, que se incluye en todas las ediciones de Windows Server 2008.
El tutorial comienza con un servidor Windows Server 2008 completamente funcional
e instala el servidor IIS desde cero. La instalación se realiza escogiendo las
opciones sugeridas por defecto, pero se añaden dos módulos específicos de los
varios que incluye IIS 7.0: **FastCGI** y **URL Rewrite**.

### Bases de datos

SQLite es la base de datos preconfigurada en el *sandbox* de Symfony. SQLite
funciona en Windows sin tener que instalar ni configurar nada, ya que existe una
extensión PDO que se instala durante la instalación de PHP.

Por tanto, no es necesario ni descargar ni ejecutar una instancia independiente
de SQLITE.EXE:

      [PHP_PDO_SQLITE]
      extension=php_pdo_sqlite.dll

### Configuración del Windows Server

Para seguir paso a paso las explicaciones de este tutorial, es mejor empezar
con un Windows Server recién instalado.

Obviamente también puedes trabajar con un servidor existente, pero puedes
encontrarte diferencias debido al software instalado y por la configuración
regional seleccionada.

Para obtener exactamente los mismos resultados que los que se muestran en los
siguientes pantallazos, te recomendamos que instales Windows Server como máquina
virtual aprovechando la versión que se puede descargar gratuitamente en Internet
y que es válida durante un período de 30 días.

>**SIDEBAR**
>¿Cómo se consigue gratuitamente una versión de pruebas de Windows Server?
>
>Si dispones de un servidor dedicado real o virtual con acceso a Internet, ya
>puedes seguir el resto del tutorial. Si no es tu caso, sigue leyendo para
>obtener gratuitamente una versión de pruebas de Windows Server.
>
>Ikoula, una empresa de hosting francesa, ofrece entre otros servicios para
>diseñadores y programadores, un servidor web con Windows gratuito durante 30
>días. La oferta tiene un coste de 0 euros y ofrece una máquina virtual de
>Windows corriendo sobre un entorno Microsoft Hyper-V. Así que puedes conseguir
>una máquina virtual completamente funcional con Windows Server 2008 Web, Standard,
>Enterprise o incluso la edición Datacenter gratis durante 30 días.
>
>Para aprovechar esta oferta, accede a la página http://www.ikoula.com/flex_server
>y pulsa sobre el botón *"Testez gratuitement"*.
>
>Si quieres obtener los mismos mensajes que se muestran en este tutorial, el
>sistema operativo que hemos utilizado es: "Windows Server 2008 Enterprise
>Edition 64 bits". Se trata de una versión de 64 bits que incluye la configuración
>regional fr-FR y en-US. Desde el panel de control de Windows es muy sencillo
>cambiar de una configuración a otra. En concreto, la opción se denomina
>*"Regional and Language Options"* y se encuentra bajo la pestaña *"Keyboards
>and Languages"*. Una vez dentro, sólo tienes que pinchar sobre *"Install/uninstall
>languages"*.

Para seguir el tutorial es obligatorio tener acceso como Administrador al servidor.
Si trabajas de forma remota, debes utilizar *Remote Desktop Services* (conocido
anteriormente como *Terminal Server Client*) y asegúrate de que tenga acceso
como Administrador.

La distribución utilizada en este capítulo es Windows Server 2008 con Service Pack 2,
tal y como puedes comprobar con el comando `Winver`:

![Comprueba la versión de tu sistema con el comando Winver](http://www.symfony-project.org/images/more-with-symfony/windows_01.png)

En este caso se ha instalado Windows Server 2008 incluyendo su entorno gráfico,
similar al de Windows Vista. También es posible utilizar una versión que sólo
dispone de la línea de comandos pero que tiene las mismas características con
un tamaño mucho más reducido (1.5 GB frente a 6.5 GB). Esta última versión
también reduce significativamente el número de parches de Windows Update que
se deben instalar.

Comprobaciones iniciales - Servidor dedicado en Internet
--------------------------------------------------------

Como el servidor se puede acceder directamente desde Internet, siempre es buena
idea comprobar que la protección del firewall de Windows está activada. Las únicas
excepciones que se deben marcar son:

 * Core Networking
 * Remote Desktop (si se accede de forma remota)
 * Secure World Wide Web Services (HTTPS)
 * World Wide Web Services (HTTP)

![Comprueba las opciones del firewall directamente desde el Panel de Control.](http://www.symfony-project.org/images/more-with-symfony/windows_02.png)

A continuación, es recomendable ejecutar el Windows Update para asegurar que todas
las aplicaciones están actualizadas con los últimos parches y correcciones de
errores.

![Comprueba el estado de Windows Update directamente desde el Panel de Control.](http://www.symfony-project.org/images/more-with-symfony/windows_03.png)

La última comprobación previa es la desinstalación del *Web Server* dentro de
la sección de roles de Windows. De esta forma se evita cualquier conflicto con
los parámetros de la distribución actual de Windows o de la configuración de IIS.

![Elimina el rol Web Server desde el Server Manager.](http://www.symfony-project.org/images/more-with-symfony/windows_04.png)

Instalando PHP en unos pocos clicks
-----------------------------------

Ahora ya podemos instalar IIS y PHP de una sola vez. Como PHP no forma parte de
la distribución de Windows Server 2008, en primer lugar debemos instalar el
*Microsoft Web Platform Installer 2.0* al que nos referiremos como "Web PI" en
las siguientes secciones.

Web PI se encarga de instalar todas las dependencias necesarias para ejecutar
PHP en cualquier sistema Windows/IIS. Por tanto, Web PI instala el servidor
IIS con los *Role Services* mínimos para un servidor web y también proporciona
las opciones imprescindibles para poder ejecutar PHP.

![http://www.microsoft.com/web - Descargar ahora.](http://www.symfony-project.org/images/more-with-symfony/windows_05.png)

La instalación del *Microsoft Web Platform Installer 2.0* incluye un analizador
de configuración, comprueba los módulos existentes, propone las actualizaciones
necesarias para los módulos e incluso permite probar extensiones en pruebas del
*Microsoft Web Platform*.

![Web PI 2.0 - Primera pantalla.](http://www.symfony-project.org/images/more-with-symfony/windows_06.png)

Web PI 2.0 permite instalar PHP con un solo click. La versión seleccionada
instala la implementación *segura para Win32 pero sin hilos* de PHP, que es la
que mejor funciona con IIS 7 y FastCGI. También ofrece la versión más reciente
(pero probada) de las librerías de PHP, en este caso la 5.2.11. Para seleccionarla,
pincha en la pestaña *"Frameworks and Runtimes"* de la izquierda:

![Web PI 2.0 - Pestaña Frameworks and Runtimes.](http://www.symfony-project.org/images/more-with-symfony/windows_07.png)

Después de seleccionar PHP, Web PI 2.0 selecciona automáticamente todas las
dependencias necesarias para servir correctamente las páginas `.php` del servidor
web, incluyendo los servicios de roles mínimos de IIS 7.0:

![Web PI 2.0 - Dependencias añadidas automáticamente - 1/3.](http://www.symfony-project.org/images/more-with-symfony/windows_08.png)

![Web PI 2.0 - Dependencias añadidas automáticamente - 2/3.](http://www.symfony-project.org/images/more-with-symfony/windows_09.png)

![Web PI 2.0 - Dependencias añadidas automáticamente - 3/3.](http://www.symfony-project.org/images/more-with-symfony/windows_10.png)

A continuación pulsa sobre *Install* y después sobre el botón *"I Accept"* para
comenzar la instalación de los componentes de IIS. Mientras tanto, se descarga
el *[runtime de PHP para Windows](http://windows.php.net)* y se actualizan algunos
módulos (como por ejemplo el módulo FastCGI IIS 7.0).

![Web PI 2.0 - Los componentes de IIS se instalan mientras se descargan las actualizaciones.](http://www.symfony-project.org/images/more-with-symfony/windows_11.png)

Por último se ejecuta el programa de instalación de PHP y después de unos minutos
se muestra el siguiente mensaje:

![Web PI 2.0 - Instalación de PHP completada.](http://www.symfony-project.org/images/more-with-symfony/windows_12.png)

Pincha sobre *Finish* y el Windows Server ya estará escuchando en el puerto 80.
Compruébalo mediante un navegador:

![Firefox - IIS 7.0 responde en el puerto 80.](http://www.symfony-project.org/images/more-with-symfony/windows_13.png)

Ahora, para comprobar que PHP se ha instalado correctamente y está disponible
dentro de IIS, crea un pequeño archivo llamado `phpinfo.php` y guárdalo en el
directorio `C:\inetpub\wwwroot` para que pueda ser accedido en el puerto 80.

Antes de crear este archivo, asegúrate que el explorador de Windows muestra las
extensiones de los archivos. Para ello, selecciona la opción *"Unhide Extensions
for Known Files Types"*.

![Explorador de Windows - Mostrar extensiones para los tipos de archivo conocidos.](http://www.symfony-project.org/images/more-with-symfony/windows_14.png)

Abre un explorador de Windows, accede al directorio `C:\inetpub\wwwroot`, pulsa
el botón derecho del ratón y selecciona *"New Text Document"*. Cambia su nombre
por `phpinfo.php` y añade la instrucción habitual para mostrar la información
de PHP:

![Explorador de Windows - Crear phpinfo.php.](http://www.symfony-project.org/images/more-with-symfony/windows_15.png)

A continuación, vuelve al navegador y accede a la URL `/phpinfo.php`:

![Firefox - phpinfo.php se ejecuta bien](http://www.symfony-project.org/images/more-with-symfony/windows_16.png)

Por último, para asegurarte de que Symfony se podrá instalar sin problemas,
descarga el archivo [`check_configuration.php`](http://sf-to.org/1.3/check.php).

![PHP - Cómo descargar check.php.](http://www.symfony-project.org/images/more-with-symfony/windows_17.png)

Copia este archivo en el mismo directorio que `phpinfo.php` (`C:\inetpub\wwwroot`)
y si quieres, cambia su nombre por `check_configuration.php`.

![PHP - Copiar y renombrar check_configuration.php.](http://www.symfony-project.org/images/more-with-symfony/windows_18.png)

Por último, vuelve al navegador y accede a la URL `/check_configuration.php`:

![Firefox - check_configuration.php se ejecuta bien.](http://www.symfony-project.org/images/more-with-symfony/windows_19.png)

Ejecutando PHP desde la línea de comandos
-----------------------------------------

Para poder ejecutar las tareas de la línea de comandos de Symfony, debemos
asegurarnos de que se puede acceder a `PHP.EXE` desde la consola de comandos
y que se ejecuta correctamente:

Abre la consola de comandos, entra en `C:\inetpub\wwwroot` y ejecuta lo siguiente:

    PHP phpinfo.php

Debería aparecer el siguiente mensaje de error:

![PHP - No se ha encontrado MSVCR71.DLL.](http://www.symfony-project.org/images/more-with-symfony/windows_20.png)

Si no se hace nada, la ejecución de `PHP.EXE` no es posible porque le falta la
librería MSVCR71.DLL. Por tanto, debemos encontrar ese archivo DLL e instalarlo
en el lugar adecuado.

El archivo `MSVCR71.DLL` es una vieja versión del *runtime* de Microsoft Visual
C++, que data del año 2003. Este archivo forma parte del paquete de *.Net
Framework 1.1*, que se puede [descargar desde el sitio MSDN](http://msdn.microsoft.com/en-us/netframework/aa569264.aspx).

El archivo que necesitamos se encuentra instalado en el siguiente directorio:
`C:\Windows\Microsoft.NET\Framework\v1.1.4322`

Así que copia el archivo y pégalo en alguno de los siguientes directorios:

 * en sistemas x64: directorio `C:\windows\syswow64`
 * en sistemas x86: directorio `C:\windows\system32`

Si quieres ya puedes desinstalar el *.Net Framework 1.1*.

El archivo `PHP.EXE` ya se puede ejecutar en cualquier consola de comandos sin
que muestre ningún error:

    PHP phpinfo.php
    PHP check_configuration.php

Más adelante, comprobaremos que el archivo `SYMFONY.BAT` incluido en el *sandbox*
también se ejecuta correctamente, ya que es la línea de comandos de Symfony.

Instalación y uso del sandbox de Symfony
----------------------------------------

El siguiente párrafo está extraído de la guía de inicio rápido de Symfony:
"El sandbox es un proyecto Symfony muy sencillo de instalar y preparado con las
opciones de configuración más habituales. Se trata de la mejor forma de probar
Symfony sin tener que realizar una instalación correcta que se adecue a las
mejores prácticas en el mundo web".

El sandbox está configurado para utilizar SQLite como base de datos. En Windows
no es necesario instalar nada para que funcione, ya que el soporte de SQLite lo
proporciona la extensión PDO de PHP que se instaló durante la instalación de
PHP. Por tanto, este requisito ya se cubrió al instalar las librerías de PHP
mediante el Microsoft Web PI.

Simplemente comprueba que la extensión SQLite está correctamente referenciada
en el archivo de configuración PHP.INI que se encuentra en el directorio
`C:\Program Files (x86)\PHP`. Además, comprueba que la DLL que implementa el
soporte PDO de SQLite sea `C:\Program Files (x86)\PHP\ext\php_pdo_sqlite.dll`.

![PHP - Localización del archivo de configuración php.ini.](http://www.symfony-project.org/images/more-with-symfony/windows_21.png)

### Descargar, crear el directorio y copiar los archivos

El proyecto del sandbox de Symfony ya viene preparado para ser ejecutado, pero
se encuentra dentro de un archivo comprimido `.zip`.

[Descarga el archivo del sandbox](http://www.symfony-project.org/get/sf_sandbox_1_3.zip)
y extrae sus contenidos a un directorio temporal, como por ejemplo el directorio
*downloads* que se encuentra dentro del directorio `C:\Users\Administrator`.

![sandbox - Descargar y descomprimir el archivo.](http://www.symfony-project.org/images/more-with-symfony/windows_22.png)

Crea un directorio para instalar el sandbox, como por ejemplo `F:\dev\sfsandbox`:

![sandbox - Crea el directorio sfsandbox.](http://www.symfony-project.org/images/more-with-symfony/windows_23.png)

Selecciona todos los archivos del directorio en el que descomprimiste el sandbox
y copialos en el nuevo directorio `F:\dev\sfsandbox`.

Si todo funciona bien, verás que se están copiando 2599 archivos:

![sandbox - Copiar 2599 archivos.](http://www.symfony-project.org/images/more-with-symfony/windows_24.png)

### Probando la ejecución

Abre una consola de comandos, entra en el directorio `F:\dev\sfsandbox` y
ejecuta el siguiente comando:

    PHP symfony -V

El resultado del comando anterior debería ser:

    symfony version 1.3.0 (F:\dev\sfsandbox\lib\symfony)

En la misma consola de comandos, ejecuta:

    SYMFONY.BAT -V

El resultado de este nuevo comando debería ser exactamente el mismo que antes:

    symfony version 1.3.0 (F:\dev\sfsandbox\lib\symfony)

![sandbox - Probando correctamente la línea de comandos.](http://www.symfony-project.org/images/more-with-symfony/windows_25.png)

### Creando la aplicación web

Para crear la aplicación web en el servidor local, se va a utilizar el gestor
de IIS7, que es una interfaz gráfica que hace las veces de panel de control
para todas las actividades relacionadas con IIS. En realidad, cualquier cambio
que se realiza en la interfaz gráfica se ejecuta en segundo plando mediante la
línea de comandos.

La consola del IIS Manager se puede acceder a través de Inicio > *Programs* >
*Administrative Tools* > *Internet Information Server (IIS) Manager*.

#### Configurar el sitio web por defecto para que no interfiera en el puerto 80

Para asegurarnos de que solamente nuestro sandbox de Symfony responde en el
puerto 80 (HTTP), modifica el puerto del *"Default Web Site"* existente al 8080.

![IIS Manager - Configurando el "Default Web Site".](http://www.symfony-project.org/images/more-with-symfony/windows_26.png)

Si el firewall de Windows está activo, es posible que debas crear una excepción
para el puerto 8080 de forma que todavía se pueda acceder al *"Default Web Site"*.
Para ello, accede al panel de control de Windows, pincha sobre *Windows Firewall*
selecciona la opción *"Allow a program through Windows Firewall"*, pulsa sobre
*"Add port"* y crea la excepción. Después de crearla, pulsa sobre el cuadrado
para activarla.

![Windows Firewall - Crear una excepción para el puerto 8080.](http://www.symfony-project.org/images/more-with-symfony/windows_27.png)

#### Añadir un nuevo sitio web al sandbox

Accede a las *Administration Tools* y abre el *IIS Manager*. En la parte de la
izquierda, selecciona el icono *"Sites"* y pulsa el botón derecho. Selecciona
la opción *Add Web Site* e introduce como nombre del sitio "Symfony Sandbox".
En la opción *Physical Path* selecciona `D:\dev\sfsandbox` y deja el resto de
opciones sin cambiar. Deberías ver la siguiente ventana de diálogo:

![IIS Manager - Añadir un nuevo sitio web.](http://www.symfony-project.org/images/more-with-symfony/windows_28.png)

Pincha sobre el botón OK. Si se muestra una pequeña `x` en el icono del sitio
web (dentro de *Features View / Sites*) puedes hacerla desaparecer pinchando
sobre el *"Restart"* que se muestra a la derecha.

#### Comprobando que el sitio web responde

Desde el *IIS Manager*, selecciona el sitio *"Symfony Sandbox"* y pulsa sobre
la opción *"Browse *.80 (http)"* del panel derecho.

![IIS Manager - Pincha en Browse port 80.](http://www.symfony-project.org/images/more-with-symfony/windows_29.png)

Si todo funciona bien, deberías ver el siguiente mensaje de error: `HTTP Error
403.14 - Forbidden`. El servidor web está configurado para no mostrar un listado
de los contenidos del directorio.

El origen del error es que la configuración por defecto del servidor web impide
mostrar un listado con todos los contenidos de los directorios. Como no existe
ningún archivo llamado `index.php` o `index.html` en `D:\dev\sfsandbox`, el
servidor muestra correctamente el error de tipo *"Forbidden"*.

![Internet Explorer - Error normal.](http://www.symfony-project.org/images/more-with-symfony/windows_30.png)

Accede a la URL `http://localhost/web` en tu navegador (en vez de simplemente
`http://localhost`). Ahora si que deberías ver en tu navegador la famosa página
*"Symfony Project Created"*:

![IIS Manager - Accediendo a la URL http://localhost/web.](http://www.symfony-project.org/images/more-with-symfony/windows_31.png)

En la parte superior de la página verás una barra de color amarillo claro con
el mensaje *"Intranet settings are now turned off by default. Intranet settings
are less secure than Internet settings. Click for options."*.

No te preocupes por ese mensaje. Si quieres cerrarlo permanentemente, pincha el
botón derecho sobre la barra amarilla y selecciona la opción apropiada.

Visualizar la página anterior confirma que la página `index.php` se ha cargado
correctamente desde `D:\dev\sfsandbox\web\index.php`, que se ha ejecutado bien
y que las librerías de Symfony están correctamente configuradas.

Antes de empezar a *jugar* con el sandbox de Symfony tenemos que realizar una
última tarea: configurar la página web del frontend mediante las reglas de
reescritura de URL. Estas reglas se encuentran en un archivo `.htaccess` y se
pueden incorporar fácilmente al IIS Manager.

### Configuración del frontend del sandbox

Para empezar a probar todas las opciones de Symfony, es necesario configurar
la aplicación frontend del sandbox. Inicialmente la portada del frontend se
puede acceder y se ejecuta correctamente cuando se accede desde el propio
ordenador donde se encuentra el servidor (es decir, desde `localhost` o desde
`127.0.0.1`).

![Internet Explorer - La página frontend_dev.php se puede acceder bien desde el localhost.](http://www.symfony-project.org/images/more-with-symfony/windows_32.png)

Para comprobar que el sandbox funciona correctamente bajo Windows Server 2008,
pincha sobre las secciones *"configuration"*, *"logs"* y *"timers"* de la barra
de depuración web.

![Configuración del sandbox.](http://www.symfony-project.org/images/more-with-symfony/windows_33.png)

![Mensajes de log del sandbox.](http://www.symfony-project.org/images/more-with-symfony/windows_34.png)

![Control del tiempo del sandbox.](http://www.symfony-project.org/images/more-with-symfony/windows_35.png)

Aunque podríamos intentar acceder al sandbox desde Internet o desde cualquier
dirección IP remota, el sandbox está pensado como una herramienta para aprender
a utilizar Symfony en una máquina local. Por tanto, los detalles sobre el acceso
remoto se mostrarán en la última sección de este capítulo.

Creando un nuevo proyecto Symfony
---------------------------------

Crear un entorno adecuado para desarrollar proyectos Symfony es casi tan sencillo
como la instalación del sandbox. De hecho, se va a explicar la versión simplificada
del proceso de instalación ya que es equivalente a la instalación del sandbox.

La principal diferencia es que en este caso nos vamos a centrar en la configuración
necesaria para que la aplicación web pueda ser accedida desde cualquier lugar
de Internet.

Al igual que el sandbox, el proyecto Symfony viene preconfigurado para utilizar
SQLite como base de datos, que ya se instaló y configuró en las secciones
previas de este capítulo.

### Descargar, crear el directorio y copiar los archivos

Cada versión de Symfony se puede descargar mediante un archivo `.zip` para
poder crear después cada proyecto desde cero.

[Descarga el archivo de Symfony](http://www.symfony-project.org/get/symfony-1.3.0.zip)
desde su sitio web y extrae sus contenidos en un directorio temporal, como por
ejemplo el directorio *"downloads"*.

![Explorador de Windows - Descargar y descomprimir el archivo del proyecto.](http://www.symfony-project.org/images/more-with-symfony/windows_37.png)

Ahora debemos crear el directorio definitivo donde residirán las librerías de
Symfony. Este paso es ahora un poco más complicado que para el sandbox.

### Estructura de directorios

A continuación se crea la estructura de directorios del proyecto. Si se comienza
por la raíz de la unidad `D:` por ejemplo, se crea un directorio llamado `\dev`
y después otro directorio llamado `sfproject`:

    D:
    MD dev
    CD dev
    MD sfproject
    CD sfproject

Ahora ya nos encontramos en `D:\dev\sfproject` y a partir de aquí se completa el
resto de la estructura creando los directorios `lib`, `vendor` y `symfony` de
forma consecutiva y en cascada:

    MD lib
    CD lib
    MD vendor
    CD vendor
    MD symfony
    CD symfony

Ahora ya nos encontramos en el directorio: `D:\dev\sfproject\lib\vendor\symfony`

![Explorador de Windows - La estructura de directorios del proyecto.](http://www.symfony-project.org/images/more-with-symfony/windows_38.png)

Selecciona todos los archivos descomprimidos en el directorio temporal y
cópialos en el directorio `D:\dev\sfproject\lib\vendor\symfony`. Verás que se
están copiando 3819 archivos en el directorio de destino:

![Explorador de Windows - Copiando 3819 archivos.](http://www.symfony-project.org/images/more-with-symfony/windows_39.png)

### Creación e inicialización

Abre la consola de comandos, dirígete al directorio `D:\dev\sfproject` y ejecuta
el siguiente comando:

    PHP lib\vendor\symfony\data\bin\symfony -V

El resultado del comando anterior debería ser el siguiente:

    symfony version 1.3.0 (D:\dev\sfproject\lib\vendor\symfony\lib)

Para inicializar el proyecto, ejecuta el siguiente comando:

    PHP lib\vendor\symfony\data\bin\symfony generate:project sfproject

El resultado del comando anterior es una serie de operaciones sobre los archivos
y directorios, incluyendo varios comandos de tipo `chmod 777`:

![Explorador de Windows - Inicialización correcta del proyecto.](http://www.symfony-project.org/images/more-with-symfony/windows_40.png)

Desde la misma consola de comandos, crea una nueva aplicación de Symfony mediante
el siguiente comando:

    PHP lib\vendor\symfony\data\bin\symfony generate:app sfapp

Una vez más deberías ver una lista de operaciones sobre archivos y directorios,
incluyendo varias operaciones de tipo `chmod 777`.

A partir de ahora, en vez de teclear `PHP lib\vendor\symfony\data\bin\symfony`
cada vez que ejecutes un comando, puedes copiar en este directorio el archivo
`symfony.bat`:

    copy lib\vendor\symfony\data\bin\symfony.bat

Ahora se dispone de un atajo para ejecutar todos los comandos de Symfony desde
el directorio `D:\dev\sfproject`. De hecho, ejecuta el siguiente comando desde
el directorio `D:\dev\sfproject`:

    symfony -V

Si todo funciona bien, deberías ver exactamente el mismo resultado que antes:

    symfony version 1.3.0 (D:\dev\sfproject\lib\vendor\symfony\lib)

### Creando la aplicación web

Las siguientes secciones asumen que has leído la parte anterior de este capítulo
en la que se explica cómo hacer que el *"Default Web Site"* no interfiera en
el puerto 80 del servidor web.

#### Añadir un nuevo sitio web para el proyecto

Accede a las *Administration Tools* para abrir el *IIS Manager*. En la columna
de la izquierda, pulsa el botón derecho sobre el icono *"Sites"*. Selecciona la
opción *"Add Web Site"* e introduce *"Symfony Project"* como nombre del sitio
web, `D:\dev\sfproject` como valor de la opción *"Physical Path"* y deja todos
las demás opciones sin cambiar:

![IIS Manager - Añadir un nuevo sitio web.](http://www.symfony-project.org/images/more-with-symfony/windows_41.png)

Pincha sobre el botón OK. Si se muestra una pequeña `x` en el icono del sitio
web (dentro de *Features View / Sites*) puedes hacerla desaparecer pinchando
sobre el *"Restart"* que se muestra a la derecha.

#### Comprobar si el sitio web responde

Selecciona el sitio *"Symfony Project"* en el *IIS Manager* y en la columna
derecha pincha sobre la opción *"Browse *.80 (http)"*.

Seguidamente verás el mismo mensaje de error que se producía al realizar la
misma operación sobre el sandbox:

    HTTP Error 403.14 - Forbidden

Al igual que en el caso del sandbox, el servidor web está configurado para no
mostrar un listado de los contenidos del directorio.

Accede con tu navegador a la URL `http://localhost/web` para ver la famosa página
*"Symfony Project Created"*, aunque en este caso existe una pequeña diferencia
respecto al sandbox ya que no se ve ninguna imagen:

![Internet Explorer - Creado el proyecto Symfony, pero sin imágenes.](http://www.symfony-project.org/images/more-with-symfony/windows_42.png)

Las imágenes no se muestran por pantalla, pero se encuentran en un directorio
llamado `sf` dentro de la librería de Symfony. Hacer que se vean es muy sencillo,
ya que solamente hay que crear un directorio virtual en `/web` llamado `sf` y
que apunte a `D:\dev\sfproject\lib\vendor\symfony\data\web\sf`.

![IIS Manager - Añadir un directorio virtual sf.](http://www.symfony-project.org/images/more-with-symfony/windows_43.png)

Después de realizar los cambios anteriores, ya puedes ver las imágenes en la
página *"Symfony Project Created"*:

![Internet Explorer - Creado el proyecto Symfony con imágenes.](http://www.symfony-project.org/images/more-with-symfony/windows_44.png)

Ahora la aplicación Symfony completa ya está funcionando. Para comprobarlo,
accede con un navegador a la URL de la aplicación en `http://localhost/web/sfapp_dev.php`:

![Internet Explorer - sfapp_dev.php se accede correctamente desde localhost.](http://www.symfony-project.org/images/more-with-symfony/windows_45.png)

Antes de concluir, podemos realizar una última comprobación en local: observa
los paneles "configuration", "logs" y "timers" de la barra de depuración web
para asegurar que el proyecto es completamente funcional.

![Internet Explorer - Los mensajes de log se pueden acceder desde localhost.](http://www.symfony-project.org/images/more-with-symfony/windows_46.png)

### Configurar la aplicación para que se pueda acceder desde Internet

Al igual que el sandox, el proyecto de Symfony ya funciona bien en local, por
lo que sólo se puede acceder desde `http://localhost` o `http://127.0.0.1`. A
continuación se configura para que se pueda acceder desde cualquier lugar de
Internet.

La configuración por defecto del proyecto impide que la aplicación se pueda
ejecutar desde un lugar remoto, aunque en principio debería permitirse ejecutar
tanto el archivo `index.php` como `sfapp_dev.php`. Si accedes al proyecto desde
el servidor web mediante la IP externa del servidor virtual dedicado (por ejemplo
`94.125.163.150`) o mediante su nombre completo (por ejemplo `12543hpv163150.ikoula.com`).
También puedes probar esas direcciones desde el propio servidor, ya que no están
mapeadas al `127.0.0.1`:

![Internet Explorer - Se permite el acceso a index.php desde Internet.](http://www.symfony-project.org/images/more-with-symfony/windows_47.png)

![Internet Explorer - No se permite el acceso a sfapp_dev.php desde Internet.](http://www.symfony-project.org/images/more-with-symfony/windows_48.png)

Anteriormente se comentó que sería posible acceder a `index.php` y `sfapp_dev.php`
desde un lugar remoto. No obstante, la ejecución de `sfapp_dev.php` falla porque
por defecto no es posible su acceso remoto. Se trata de una medida de seguridad
para impedir que los usuarios maliciosos puedan tener acceso al entorno de
desarrollo, que contiene información potencialmente sensible sobre tu proyecto.
Aunque puedes editar el archivo `sfapp_dev.php` para permitir el acceso remoto,
te recomendamos encarecidamente que no lo hagas nunca bajo ninguna circunstancia.

El último paso consiste en simular un nombre de dominio real mediante la
configuración del archivo *"hosts"*. Este archivo realiza la resolución local
de nombres sin tener que instalar el servicio DNS de Windows, que está disponible
en todas las ediciones de Windows Server 2008 R2 y también en las ediciones
Windows Server 2008 Standard, Enterprise y Datacenter.

En los sistemas operativos tipo Windows x64, el archivo `hosts` se encuentra en:
`C:\Windows\SysWOW64\Drivers\etc`

Inicialmente el archivo `hosts` incluye la información necesaria para que el
servidor pueda resolver `localhost` a `127.0.0.1` en IPv4 y a `::1` en IPv6.

A continuación se añade un nombre de dominio válido pero falso, como por ejemplo
`sfwebapp.local` y se resuelve como el propio servidor local.

![Cambios aplicados en el directorio "hosts".](http://www.symfony-project.org/images/more-with-symfony/windows_50.png)

El proyecto Symfony ya se puede acceder remotamente, sin necesidad de DNS, a
través de cualquier navegador instalado en el propio servidor web.