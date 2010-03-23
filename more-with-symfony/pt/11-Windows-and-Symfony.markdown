Windows e symfony
=================

*por Laurent Bonnet*

Visão Geral
-----------

Este documento é um novo tutorial passo-a-passo cobrindo a instalação,
implantação e teste de funcionamento do framework symfony no Windows Server
2008.

A fim de preparar o desenvolvimento para Internet, o tutorial pode ser
executado em um ambiente de servidor dedicado, hospedado na Internet.

Naturalmente, é possível concluir o tutorial em um servidor local, ou uma
máquina virtual na estação de trabalho do leitor.

### A razão para um novo tutorial

Atualmente, existem duas fontes de informação relacionadas com o Microsoft Internet
Information Server (IIS) no symfony
[website](http://trac.symfony-project.org/wiki/symfonyOnIIS)
  [](http://www.symfony-project.org/cookbook/1_2/en/web_server_iis),
mas eles se referem às versões anteriores que não evoluíram com novas versões
de sistemas operacionais Microsoft Windows, especialmente Windows Server 2008
(lançado em fevereiro de 2008), que inclui muitas mudanças de interesse para desenvolvedores PHP:

* IIS versão 7, a versão embutida no Windows Server 2008, foi inteiramente
   reescrita para um design totalmente modular.

* IIS 7 provou ser muito confiável, com muito poucas correções necessárias de
   Windows Update, desde o lançamento do produto.

* O IIS 7 também inclui o acelerador FastCGI, um pool de aplicativos multi-threaded
   que tira proveito do modelo de segmentação nativo do sistemas operacionais Windows.

* A implementação do PHP FastCGI equivale a um desempenho 5x a 10x
   melhor na execução, sem cache, quando comparado ao tradicional ISAPI
   ou implantações CGI do PHP em Windows e IIS.

* Mais recentemente, a Microsoft mostrou um acelerador de cache para o PHP,
   que está em status de lançamento *Release Candidate* no momento da redação deste texto (02/11/2009).

>**SIDEBAR**
>Planejamento de Extensão para este tutorial
>
>Uma seção complementar deste capítulo está em construção e será liberada
>no site do symfony projeto web logo após a publicação 
>deste livro. Abrange a conexão com MS SQL Server através do PDO, algo que a
>Microsoft planeja melhorias em breve.
>
>[PHP_PDO_MSSQL]
>extension=php_pdo_mssql.dll
>
>Atualmente, o melhor desempenho na execução de código é obtido pelo 
>driver nativo do Microsoft SQL Server para PHP 5, um driver open-source disponível no Windows
>e atualmente disponível na versão 1.1. Isso é implementado como uma
>nova extensão DLL do PHP:
>
>[PHP_SQLSRV]
>extension=php_sqlsrv.dll
>
>É possível usar o Microsoft SQL Server 2005 ou 2008 como
>banco de dados. A extensão de tutorial planejada irá cobrir o uso da
>edição que está disponível de graça: SQL Server Express.

### Como utilizar este tutorial em diferentes sistemas Windows, incluindo 32-bit

Este documento foi escrito especificamente para edições do Windows Server 2008 de 64-bits 
. Entretanto, você deve ser capaz de usar as outras versões, sem quaisquer complicações.

>**NOTE**
>A versão exata do software operacional utilizado na tela é
>Windows Server 2008 Enterprise Edition com Service Pack 2, 64-bit edition.

#### Versões do Windows 32-bit

O tutorial é facilmente transportável para versões do Windows 32-bit, substituindo
as seguintes referências no texto:

* Em edições 64-bit: `C:\Program Files (x86)\` e `C:\Windows\SysWOW64\`

* Em edições de 32-bit: `C:\Program Files\` e `C:\Windows\System32\`

#### Sobre outras edições do Enterprise

Além disso, se você não tiver Enterprise Edition, este não é um problema. Essa
documentação é diretamente portável para outras edições do Windows Server:
Windows Server 2008 Web, Standard ou Datacenter Windows Server 2008 Web
Standard ou Datacenter, com Service Pack 2 do Windows Server 2008 R2 Web
Edições Standard, Enterprise ou Datacenter.

Por favor, note que todas as edições do Windows Server 2008 R2 estão disponíveis apenas como
sistemas operacionais 64-bit.

#### Sobre a Edições Internacionais

As configurações regionais usadas nas imagens são `en-US`. Nós também
instalamos um pacote de linguagem internacional para a França.

É possível executar o tutorial sobre sistemas operacionais Windows clientes:
Windows XP, Windows Vista e Windows Seven, tanto em modos x64 e x86.

### Servidor Web utilizado em todo o documento

O servidor web utilizado é o Microsoft Internet Information Server versão 7.0,
que é incluído em todas as edições como parte do Windows Server 2008. Começamos o
tutorial com um servidor Windows Server 2008 totalmente funcional e instalamos o IIS
a partir do zero. As etapas de instalação usam as opções padrões, bastando adicionar dois
módulos específicos que vem com o design modular do IIS 7.0: **FastCGI** e 
**URL Rewrite**.

### Bancos de Dados

SQLite é o banco de dados pré-configurado para no sandbox do symfony. No Windows,
não há nada específico para instalar: SQLite é diretamente implementado na
extensão PDO do PHP para o SQLite, que é instalado no momento da instalação do PHP.

Assim, não há necessidade de baixar e executar uma instância separada do SQLITE.EXE:

      [PHP_PDO_SQLITE]
      extension=php_pdo_sqlite.dll

### Configuração do Windows Server

É melhor usar uma nova instalação do Windows Server, a fim de corresponder
as capturas de telas (*screenshots*) do passo-a-passo neste capítulo.

Claro que você pode trabalhar diretamente em uma máquina existente, mas você pode encontrar
diferenças devido aos softwares instalados, tempo de execução, e as configurações regionais.

A fim de obter as mesmas telas que aparecem no
tutorial, é recomendável a obtenção de um Windows Server dedicado em um 
ambiente virtual, disponível gratuitamente na Internet por um período de 30 dias.

>**SIDEBAR** 
>Como obter um Windows Server Trial gratuito?
>
>É claro que é possível utilizar qualquer servidor dedicado com acesso à Internet. Um
> servidor físico ou mesmo servidor virtual dedicado (VDS) vai funcionar perfeitamente.
>
>Um servidor disponível para avaliação por 30 dias com o Windows está no Ikoula, um provedor francês, 
>que oferece uma lista abrangente de serviços para os desenvolvedores e
>designers. Esta avaliação começa em 0  / mês para uma máquina virtual Windows
>sendo executado em um ambiente Microsoft Hyper-V. Sim, você pode obter uma
> máquina virtual com o Windows Server 2008 Web plenamente funcional, Standard, Enterprise
>ou mesmo a edição Datacenter gratuitamente durante um período de 30 dias.
>
>Para solicitar, basta abrir no navegador http://www.ikoula.com/flex_server e
>clique no botão "Testez gratuitement".
>
>A fim de obter as mesmas mensagens descritas neste documento, o sistema operacional 
>que solicitamos ao servidor Flex é: "Windows Server 2008 Enterprise Edition
>64 bits". Esta é uma distribuição x64, entregues com as línguas fr-FR
>e en-US. É fácil mudar de `fr-FR` para `en-US` e vice-versa
>a partir do Painel de Controle do Windows. Especificamente, esta configuração pode ser encontrada
>em "Regional and Language Options", que fica sob a guia
>"Keyboards and Languages". Basta clicar em "Install/uninstall languages".

É necessário ter acesso de Administrador no servidor.

Se estiver trabalhando em uma estação de trabalho remota, o leitor deve executar Remote Desktop
Services (anteriormente conhecido como cliente do Terminal Server) e garantir que ele tem
acesso de Administrador.

A distribuição usada aqui é: Windows Server 2008 com Service Pack 2.

![Verifique o seu ambiente de início, com o comando Winver - aqui em Inglês](http://www.symfony-project.org/images/more-with-symfony/windows_01.png)

Windows Server 2008 foi instalado com o ambiente gráfico, que
utiliza visual do Windows Vista. Também é possível usar uma linha de comando
apenas para a versão do Windows Server 2008 com os mesmos serviços, a fim de reduzir o tamanho da distribuição (1,5 GB em vez de 6,5 GB). Isto também reduz a área de ataque e o número de patches do Windows Update, que terão de ser aplicados.

Verificações preliminares - Servidor Dedicado na Internet
-----------------------------------------------------

Uma vez que o servidor está diretamente acessível na Internet, é sempre uma boa
idéia verificar se o Firewall do Windows está fornecendo proteção ativa. As únicas
exceções que devem ser verificadas são:

* Core Networking
* Remote Desktop (se acessado remotamente)
* Secure World Wide Web Services (HTTPS)
* World Wide Web Services (HTTP)

![Verifique as configurações de firewall, diretamente no painel de controle.](http://www.symfony-project.org/images/more-with-symfony/windows_02.png)

Então, é sempre bom para executar o Windows Update para garantir que todos os pacotes do software 
estão instalados com as últimas correções, patches e documentação.

![Verificar status do Windows Update, diretamente no Painel de controle.](http://www.symfony-project.org/images/more-with-symfony/windows_03.png)

Como última etapa de preparação, e por uma questão de eliminar quaisquer potenciais
parâmetros conflitantes na distribuição existente no Windows ou configuração do IIS,
recomendamos que você desinstale o Serviço Web do servidor Windows, se previamente
instalado.

![Retire o serviço Servidor Web (*Web Server role*), a partir do Server Manager.](http://www.symfony-project.org/images/more-with-symfony/windows_04.png)

Instalando PHP - Apenas alguns cliques de distância
---------------------------------------

Agora, podemos instalar o IIS e o PHP em uma operação simples.

PHP não é uma parte da distribuição do Windows Server 2008, portanto, nós precisamos
instalar primeiro o Microsoft Web Platform Installer 2.0, denominada Web PI
nas seções seguintes.

Web PI toma cuidado de instalar todas as dependências necessárias para a execução de PHP
em qualquer Windows / sistema do IIS. Então, ele instala o IIS com o mínimo de Role Services 
para o servidor Web, e também oferece opções mínimas para o PHP runtime.

![http://www.microsoft.com/web - Faça o download agora.](http://www.symfony-project.org/images/more-with-symfony/windows_05.png)

A instalação do Microsoft Web Platform Installer 2.0 contém um
analisador de configuração, verifica módulos existentes, propõe as atualizações de módulos necessárias, 
e ainda permite que você faça um beta-teste de extensões ainda não lançadas da
Microsoft Web Platform.

![Web PI 2.0 - Primeira Visão.](http://www.symfony-project.org/images/more-with-symfony/windows_06.png)

Web PI 2.0 oferece a instalação do PHP runtime em um clique. A seleção
instala a implementação Win32 "non-thread safe" do PHP, que é
melhor associada ao IIS 7 e FastCGI. Também oferece o mais recente
runtime testado, aqui 5.2.11. Para encontrá-lo, basta selecionar a guia "Frameworks and 
Runtimes" à esquerda:

![PI Web 2.0 - Guia Frameworks e Runtimes.](http://www.symfony-project.org/images/more-with-symfony/windows_07.png)

Depois de selecionar o PHP, Web PI 2,0 automaticamente são selecionadas todas as dependências necessárias
para servir páginas web `.php` armazenadas no servidor, incluindo o mínimo
IIS 7.0 roles services:

![PI Web 2.0 - Dependências automaticamente adicionadas - 1 / 3.](http://www.symfony-project.org/images/more-with-symfony/windows_08.png)

![PI Web 2.0 - Dependências automaticamente adicionadas - 2 / 3.](http://www.symfony-project.org/images/more-with-symfony/windows_09.png)

![PI Web 2.0 - Dependências automaticamente adicionadas - 3 / 3.](http://www.symfony-project.org/images/more-with-symfony/windows_10.png)

Em seguida, clique em Install, em seguida, no botão "I Accept". A instalação dos componentes IIS 
começará enquanto, paralelamente, o PHP é transferido
[runtime](http://windows.php.net) e alguns módulos são atualizados (atualização para um
IIS FastCGI 7,0 por exemplo).

![PI Web 2.0 - Instalação dos componentes do IIS enquanto as atualizações são baixadas da web.](http://www.symfony-project.org/images/more-with-symfony/windows_11.png)

Finalmente, o programa de instalação do PHP é executado, e, após alguns minutos, deverá apresentar:

![PI Web 2.0 - Instalação do PHP foi finalizada.](http://www.symfony-project.org/images/more-with-symfony/windows_12.png)

Clique em "Finish".

O Windows Server está escutando e agora é capaz de ouvir e responder na porta 80.

Vamos verificar isso no navegador:

![Firefox - IIS 7.0 está respondendo na porta 80.](http://www.symfony-project.org/images/more-with-symfony/windows_13.png)

Agora, para verificar que o PHP está instalado corretamente, e disponível a partir do IIS, nós
criamos um pequeno arquivo `phpinfo.php` a ser acessado pelo servidor web padrão
na porta 80, em `C:\inetpub\wwwroot`.

Antes de fazer isso, garantimos que, no Windows Explorer, podemos ver as 
extensões corretas dos arquivos. Selecione "Unhide Extensions for Known Files Types".

![Windows Explorer - Unhide Extensions for Known Files Types.](http://www.symfony-project.org/images/more-with-symfony/windows_14.png)

Abra o Windows Explorer e vá para "C:\inetpub\wwwroot`. Clique com o botão direito do mouse e selecione
"New Text Document". Renomeie para `phpinfo.php` e copie a chamada de função 
usual:

![Windows Explorer - Criar phpinfo.php.](http://www.symfony-project.org/images/more-with-symfony/windows_15.png)

Em seguida, reabrir o navegador web, e colocar `/phpinfo.php` no final da
URL do servidor:

![Firefox - Execução do phpinfo.php está OK](http://www.symfony-project.org/images/more-with-symfony/windows_16.png)

Finalmente, para garantir que o symfony irá instalar sem problemas, faça o download
[http://sf-to.org/1.3/check.php]( `check_configuration.php`).

![PHP - Onde baixar check.php.](http://www.symfony-project.org/images/more-with-symfony/windows_17.png)

Copie para o mesmo diretório como `phpinfo.php` ( `C:\inetpub\wwwroot`) e
renomeie para `check_configuration.php` se necessário.

![PHP - Copie e renomeie o check_configuration.php.](http://www.symfony-project.org/images/more-with-symfony/windows_18.png)

Por fim, reabra o navegador web uma última vez para agora, e coloque
`/check_configuration.php` no final da URL do servidor:

![Firefox - Execução check_configuration.php está OK.] http://www.symfony-project.org/images/more-with-symfony/windows_19.png ()

Executando o PHP na interface de linha de comando
---------------------------------------------

Para depois executar as tarefas da linha de comando com o symfony, precisamos assegurar que o 
PHP.EXE é acessível a partir do prompt de comando e é executad corretamente.

Abra um prompt de comando para `C:\inetpub\wwwroot` e digite 

    PHP phpinfo.php

A seguinte mensagem de erro deve aparecer:

![PHP - MSVCR71.DLL não foi encontrado.](http://www.symfony-project.org/images/more-with-symfony/windows_20.png)

Se não fizermos nada, a execução de PHP.EXE trava na ausência do 
MSVCR71.DLL. Então, temos de encontrar o arquivo DLL e instalá-lo no local 
correto.

Este `MSVCR71.DLL` é uma versão do Microsoft Visual C + + runtime, que
remonta à época de 2003. Ele está contido no pacote redistribuível 
.NET Framework 1.1.

O pacote redistribuível .NET Framework 1.1, pode ser baixado em
[MSDN](http://msdn.microsoft.com/en-us/netframework/aa569264.aspx)

O arquivo que estamos procurando é instalado no seguinte diretório:
`C:\Windows\Microsoft.NET\Framework\v1.1.4322`

Basta copiar o para `MSVCR71.DLL` para o seguinte destino:

* Em sistemas x64: o diretório `C:\windows\syswow64` 
* Em sistemas x86: o diretório `C:\windows\system32`

Podemos agora desisntalar o .Net Framework 1.1.

O executável PHP.EXE agora pode ser executado no prompt de comando sem erro.
Por exemplo:

    PHP phpinfo.php
    PHP check_configuration.php

Mais tarde, nós vamos verificar que symfony.bat (a partir da distribuição Sandbox) também
dá a resposta esperada, que é a sintaxe do comando symfony.

Instalação e Uso do Sandbox do symfony 
--------------------------------------

O parágrafo seguinte é um trecho do "Guia de Introdução ao symfony",
["The Sandbox"](http://www.symfony-project.org/getting-started/1_3/en/A-Sandbox):
página: "O sandbox é um projeto symfony pré-empacotado super fácil de instalar,
já configurado com alguns padrões razoáveis. É uma ótima forma de praticar o
symfony, sem o incômodo de uma instalação adequada que respeite a
melhores práticas da web".

O sandbox é pré-configurado para usar o SQLite como banco de dados. No Windows,
não há nada específico para instalar: O SQLite é diretamente implementado na
extensão PDO do PHP para o SQLite, que é instalado juntamente com
o PHP. Nós já realizamos isto antes, quando o PHP runtime 
foi instalado através do Microsoft Web PI.

Basta verificar se a extensão SQLite está corretamente referida no arquivo 
PHP.INI, que reside no diretório `C:\Program Files (x86)\PHP`, e que
a DLL que implementa o suporte PDO para SQLite está definida como 
`C:\Program Files (x86)\PHP\ext\php_pdo_sqlite.dll`.

![PHP - Localização do arquivo de configuração php.ini.](http://www.symfony-project.org/images/more-with-symfony/windows_21.png)

### Baixar, criar Diretório, copiar todos os Arquivos

O projeto sandbox do symfony está "pronto para instalar e executar", e vem em um 
arquivo `.zip`.

Baixe o [arquivo](http://www.symfony-project.org/get/sf_sandbox_1_3.zip)
e extraia-o para em um local temporário, como o diretório "downloads",
que está disponível para leitura/escrita no diretório `C:\User\administrador`.

![sandbox - Baixe e descompacte o arquivo.](http://www.symfony-project.org/images/more-with-symfony/windows_22.png)

Crie um diretório para o destino final do sandbox, como `F:\dev\sfsandbox`:

![sandbox - Criar sfsandbox Directory.](http://www.symfony-project.org/images/more-with-symfony/windows_23.png)

Selecione todos os arquivos - `CTRL-A` no Windows Explorer - a partir do seu local de download
(fonte), e os copie para o diretório `F:\dev\sfsandbox`.

Você deverá ver 2599 itens copiados para o diretório de destino:

![sandbox - Cópia de 2599 itens.](http://www.symfony-project.org/images/more-with-symfony/windows_24.png)

### Teste de Execução

Abra o prompt de comando. Vá para `F:\dev\sfsandbox` e execute o seguinte comando:

    PHP symfony -V

Isso deve retornar:

    symfony versão 1.3.0 (F:\dev\sfsandbox\lib\symfony)

A partir do mesmo prompt de comando, execute:

    SYMFONY.BAT -V

Isso deve retornar o mesmo resultado:

    symfony version 1.3.0 (F:\dev\sfsandbox\lib\symfony)

![Sandbox - Teste de linha de comando - Sucesso.](http://www.symfony-project.org/images/more-with-symfony/windows_25.png)

### A criação de aplicativos Web

Para criar uma aplicação Web no servidor local, utilize o gerenciador do IIS7,
que é o painel de controle da interface gráfica do usuário para todas as atividades relacionadas com 
o IIS. Todas as ações realizadas a partir da interface do usuário que são efetivamente realizadas por trás dos bastidores
através da interface de linha de comando.

O Gerenciador do IIS é acessível a partir do Menu *Start* em *Programs*,
*Administrative Tools*, *Internet Information Server (IIS) Manager*.

#### Reconfigurando o "Default Web Site" de modo a não interferir na Porta 80

Queremos garantir que apenas o nosso symfony sandbox está respondendo na porta 80
(HTTP). Para fazer isso, altere a porta atual do "Default Web Site porta" para 8080.

![*IIS Manager* - Editar *Binding* para "Default Web Site".](http://www.symfony-project.org/images/more-with-symfony/windows_26.png)

Observe que, se o Firewall do Windows estiver ativo, você poderá ter de criar uma 
exceção para a porta 8080 para continuar a ser capaz de atingir o "Default Web Site". Para
esse efeito, vá para Windows Control Panel, selecione Windows Firewall, clique em
*"Allow a program through Windows Firewall"* e clique em *"Add port"* para criar
esta exceção. Marque a caixa para ativá-lo após a criação.

![Firewall do Windows - Criar Uma Exceção para a Porta 8080.](http://www.symfony-project.org/images/more-with-symfony/windows_27.png)

#### Adicionar um Novo Site para a Sandbox

Abra o *IIS Manager* a partir *Administration Tools*. No painel esquerdo, selecione o ícone "Sites"
e clique com o botão direito. Selecione *Add Web Site* a partir do menu de contexto. Digite, por
exemplo, "symfony Sandbox" como o nome do site, `D:\dev\sfsandbox` para a Physical 
Path, e deixe os outros campos inalterados. Você verá esta caixa de diálogo:

![IIS Manager - Adicionando o Web Site.](http://www.symfony-project.org/images/more-with-symfony/windows_28.png)

Clique em OK. Se um pequeno `X` aparece no ícone do site (em Features View /
Sites), não deixe de clicar em "Restart" no painel direito para fazê-lo 
desaparecer.

#### Verifique se o Site está Respondendo

Do IIS Manager, selecione o site "symfony Sandbox", e, no painel da direita,
Clique em "Browse *. 80 (http)".

![IIS Manager - Clique em Browse port 80.](http://www.symfony-project.org/images/more-with-symfony/windows_29.png)

Você deverá receber uma mensagem de erro explícita, isso não é inesperado:
`HTTP Error 403.14 - Forbidden`.
O servidor Web está configurado para não listar o conteúdo deste diretório.

Isto é originado a partir da configuração padrão do servidor web, que especifica
que o conteúdo deste diretório não deve ser listado. Uma vez que nenhum arquivo 
padrão como `index.php` ou `index.html` existe em `D:\dev\sfsandbox`, o
servidor retorna corretamente a mensagem de erro "Forbidden". Não tenha medo.

![Internet Explorer - Erro Normal.](http://www.symfony-project.org/images/more-with-symfony/windows_30.png)

Digite `http://localhost/web` na barra de endereços do seu navegador, em vez de apenas
http://localhost `. Agora você deve ver o seu navegador, por padrão o Internet
Explorer, exibindo "symfony Project Created":

![IIS Manager - Digite http://localhost/web na URL. Sucesso!](http://www.symfony-project.org/images/more-with-symfony/windows_31.png)

A propósito, você pode ver uma faixa amarela no topo dizendo 
"Intranet settings are now turned off by default". Configurações de Intranet são menos seguras do que
Configurações de Internet. Clique para ver opções. Não tenha medo desta mensagem.

Para fechá-lo permanentemente, clique com o botão direito do mouse na faixa amarela, e selecione a
opção apropriada.

Esta tela confirma que a página padrão `index.php` foi corretamente carregado
a partir de `D:\dev\sfsandbox\web\index.php`, executado corretamente, e que as bibliotecas symfony
estão corretamente configuradas.

Temos que realizar uma última tarefa antes de começar a brincar com o symfony
Sandbox: configurar a página web do front-end importando as regras de reescrita de URL.
Estas regras são implementadas como arquivos `.htaccess` e podem ser controladas em
apenas alguns cliques no Gerenciador do IIS.

### Sandbox: Configuração do Front-end Web

Nós queremos configurar aplicação sandbox a fim de começar a
brincar realmente com as coisas do symfony. Por padrão, a primeira página final pode ser
alcançada e executa corretamente quando solicitada a partir da máquina local
(isto é, o nome localhost ou o endereço `127.0.0.1`).

![Internet Explorer - página frontend_dev.php está OK a partir de localhost.](http://www.symfony-project.org/images/more-with-symfony/windows_32.png)

Vamos explorar as "configuration", "logs" e "timers" os painéis de debug web para
garantir que o sandbox esteja totalmente funcional no Windows Server 2008.

![uso do sandbox: configuration.](http://www.symfony-project.org/images/more-with-symfony/windows_33.png)

![uso do sandbox: logs.](http://www.symfony-project.org/images/more-with-symfony/windows_34.png)

![uso do sandbox: timers.](http://www.symfony-project.org/images/more-with-symfony/windows_35.png)

Enquanto nós poderíamos tentar acessar a aplicação sandbox da Internet ou
a partir de um endereço IP remoto, o sandbox é mais concebido como uma ferramenta para aprender o
framework symfony na máquina local. Portanto, nós vamos cobrir detalhes relacionados
ao acesso remoto na última seção: Projeto: Configuração do Front-end Web.

Criação de um novo Projeto symfony
---------------------------------

Criar um ambiente de projeto symfony para fins de desenvolvimento real é quase
tão simples como a instalação do sandbox. Vamos ver todo o
processo de instalação de um procedimento simplificado, que é equivalente a
instalação e implantação do sandbox.

A diferença é que, neste seção "projeto", vamos nos concentrar na
configuração da aplicação Web para fazer funcionar de qualquer lugar
Internet.

Como o sandbox, o projeto symfony vem pré-configurado para usar o SQLite como 
motor de base de dados. Este foi instalado e configurado no início deste capítulo.

### Baixar, criar um diretório e copiar os arquivos

Cada versão do symfony pode ser baixada como um arquivo zip e então usada para
criar um projeto do zero.

Baixe o arquivo contendo a biblioteca do
[website symfony](http://www.symfony-project.org/get/symfony-1.3.0.zip).
Em seguida, extraia o diretório contido em um local temporário, como a
diretório de "downloads".

![Windows Explorer - Baixe e descompacte o arquivo do projeto.](http://www.symfony-project.org/images/more-with-symfony/windows_37.png)

Agora precisamos criar uma árvore de diretórios para o destino final do
projeto. Isto é um pouco mais complicado do que o sandbox.

### Árvore de diretórios de instalação

Vamos criar uma árvore de diretórios para o projeto. Iniciar a partir da raiz do volume,
`D:` por exemplo.

Criar um diretório `\dev` em `D:`, e criar outro diretório chamado
`sfproject` lá:

    D:
    MD dev
    CD dev
    MD sfproject
    CD sfproject

Estamos agora em: `D:\dev\sfproject`

A partir daí, criar uma árvore de subdiretórios, criando os diretórios `lib`, `vendor`
e `symfony` em cascata:

    MD lib
    CD lib
    MD vendor
    CD vendor
    CD vendor
    CD symfony

Estamos agora em: `D:\dev\sfproject\lib\vendor\symfony`

![Windows Explorer - a árvore de diretórios do projeto.](http://www.symfony-project.org/images/more-with-symfony/windows_38.png)

Selecione todos os arquivos (CTRL + `A` no Windows Explorer) a partir do seu local de download
(fonte), e copie de Downloads para `D:\dev\sfproject\lib\vendor\symfony`.
Você deverá ver 3819 itens copiados para o diretório de destino:

![Windows Explorer - Cópia de 3819 itens.](http://www.symfony-project.org/images/more-with-symfony/windows_39.png)

### Criação e inicialização

Abra o prompt de comando. Mude para o diretório `D:\dev\sfproject` e execute
o seguinte comando:

    PHP lib\vendor\symfony\data\bin\symfony -V

Isso deve retornar:

    symfony version 1.3.0 (D:\dev\sfproject\lib\vendor\symfony\lib)

Para iniciar o projeto, basta executar o seguinte linha de comando PHP:

    PHP lib\vendor\symfony\data\bin\symfony generate:project sfproject

Isso deve retornar uma lista de operações de arquivo, incluindo alguns comandos 
`chmod 777`:

![Windows Explorer - Inicialização do Projeto OK.](http://www.symfony-project.org/images/more-with-symfony/windows_40.png)

Ainda no prompt de comando, crie uma aplicação symfony, executando o
seguinte comando:

    PHP lib\vendor\symfony\data\bin\symfony generate:app sfapp

Novamente, este deve retornar uma lista de operações de arquivo, incluindo alguns 
comandos `chmod 777`.

A partir deste ponto, ao invés de digitar `PHP lib\vendor\symfony\data\bin\symfony`
cada vez que for necessário, copie o arquivo `symfony.bat` desde a sua origem:

    copy lib\vendor\symfony\data\bin\symfony.bat

Temos agora um comando conveniente para ser executado na linha de comando no prompt
`D:\dev\sfproject`.

Ainda em `D:\dev\sfproject`, podemos agora executar o comando clássico:

    symfony -V

para obter a resposta clássica:

    symfony version 1.3.0 (D:\dev\sfproject\lib\vendor\symfony\lib)

### A criação de aplicativos Web

Nas linhas que se segue, vamos supor que você já leu em "Sandbox: Criação do Front-end Web" 
os passos preliminares para reconfigurar o" Default Web Site" para 
que não interfira na porta 80.

#### Adicione um novo Web Site para o Projeto

Abra o IIS Manager a partir do Administration Tools. No painel esquerdo, selecione o icone "Sites"
e clique com o botão direito do mouse. Selecione "Add Web Site" do menu popup. Digite, por
exemplo, "symfony Project" como o nome do site, `D:\dev\sfproject` para a
"Physical Path", e deixar os outros campos inalterados; você verá esta caixa 
de diálogo:

![IIS Manager - Add Web Site.](http://www.symfony-project.org/images/more-with-symfony/windows_41.png)

Clique em OK. Se um pequeno `x` aparece no ícone do site (em Features View / 
Sites), não deixe de clicar em "Restart" no painel direito para faze-lo
desaparecer.

#### Verifique se o Web Site está respondendo

A partir do *IIS Manager*, selecione o site "Symfony Project", e, no painel da direita,
clique em "Browse *. 80 (http)".

Você deve obter a mesma mensagem de erro explícita como você tinha quando se testava o sandbox:

    *HTTP Error 403.14 - Forbidden*

O servidor Web está configurado para não listar o conteúdo deste diretório.

Digite `http://localhost/web` na barra de endereços do seu navegador, você deve agora
ver a página "*Symfony Project Created*", mas com uma discreta diferença da 
mesma página resultante de inicialização do sandbox: não existem imagens:

![Internet Explorer - *symfony Project Created* - sem imagens.](http://www.symfony-project.org/images/more-with-symfony/windows_42.png)

As imagens não estão aqui por enquanto, porém elas estão localizadas em um diretório `sf`
na biblioteca symfony. É fácil ligá-los ao
diretório `/` web, adicionando um diretório virtual em `/web, nomeado
`sf`, e apontando para `D:\dev\sfproject\lib\vendor\symfony\data\web\sf`.

![IIS Manager - Adicionar o Diretório Virtual sf.](http://www.symfony-project.org/images/more-with-symfony/windows_43.png)

Agora temos a página "symfony Project Created" regular com imagens como
esperado:

![Internet Explorer - symfony Project Created- com imagens.](http://www.symfony-project.org/images/more-with-symfony/windows_44.png)

E, finalmente, toda a aplicação symfony está funcionando. A partir do navegador da Web,
digite o endereço da aplicação web, i.e. `http://localhost/web/sfapp_dev.php`:

![Internet Explorer - página sfapp_dev.php está OK a partir de localhost.](http://www.symfony-project.org/images/more-with-symfony/windows_45.png)

Vamos realizar um último teste no modo de local: verificar os painéis do web debug "configuration",
"logs" e "timers" para garantir que o projeto está totalmente
funcional.

![Internet Explorer - Página de logs está OK a partir de localhost.](http://www.symfony-project.org/images/more-with-symfony/windows_46.png)

### Configuração da Aplicação para Aplicações Prontas para Internet 

Nosso projeto symfony genérico está agora trabalhando localmente, como o sandbox, a partir da
servidor host local, localizado em `http://localhost` ou `http://127.0.0.1`.

Agora, nós gostaríamos de ser capazes de acessar o aplicativo da Internet.

A configuração padrão do projeto protege a aplicação de 
ser executada de um local remoto, embora, na realidade, tudo deve estar ok
para acessar os arquivos `index.php` e `sfapp_dev.php`. Vamos executar o
projeto a partir do navegador da Web, usando o endereço IP do servidor externo
(por exemplo `94.125.163.150`) e o FQDN do nosso Servidor Dedicado Virtual
(por exemplo, `12543hpv163150.ikoula.com`). Você ainda pode usar os dois endereços
a partir de dentro do servidor, uma vez que eles não estão mapeadas ao `127.0.0.1`:

![Internet Explorer - Acesso a index.php pela Internet está OK.](http://www.symfony-project.org/images/more-with-symfony/windows_47.png)

![Internet Explorer - A execução de sfapp_dev.php da Internet não está OK.](http://www.symfony-project.org/images/more-with-symfony/windows_48.png)

Como dissemos antes, o acesso a `index.php` e `sfapp_dev.php` de um
localização remota está ok. A execução do `sfapp_dev.php` entretanto falha, pois
não é permitida por padrão. Isso impede usuários maliciosos de
acessarem seu ambiente de desenvolvimento, que contém informações potencialmente sensíveis
sobre o projeto. Você pode editar o arquivo `sfapp_dev.php` para fazer
o trabalho, mas isto é fortemente desencorajado.

Finalmente, podemos simular um domínio real, editando o arquivo "hosts".

Este arquivo executa a resolução de nomes FQDN local, sem necessidade de instalar o
Serviço de DNS no Windows. O serviço de DNS está disponível em todas as edições do
Windows Server 2008 R2, e também no Windows Server 2008 Standard, Enterprise
e Datacenter.

Em sistemas operacionais Windows x64, o arquivo "hosts" está localizado por padrão em:
`C:\Windows\SysWOW64\Drivers\etc`

O arquivo "hosts" é pré-preenchido para a máquina poder resolver `localhost` para
`C:\Windows\SysWOW64\Drivers\etc`

Vamos adicionar um nome real de domínio falso, como o `sfwebapp.local`, e poder
resolvê-lo localmente.

![Alterações aplicadas ao arquivo "hosts".](http://www.symfony-project.org/images/more-with-symfony/windows_50.png)

Seu projeto symfony agora roda na Internet, sem DNS, a partir de uma sessão de navegador web
executada de dentro do servidor web.