Introdução
==========

*por Fabien Potencier*

Durante a escrita destas linhas, o projeto symfony comemorou um marco importante:
seu [quarto aniversário](http://trac.symfony-project.org/changeset/1). Em apenas
quatro anos, o framework symfony cresceu e se tornou um dos mais populares
Frameworks PHP no mundo, alimentando sites como o
[Delicious](http://sf-to.org/delicious),
[Yahoo Bookmarks](http://sf-to.org/bookmarks)
e
[Daily Motion](http://sf-to.org/dailymotion).
Mas, com o recente lançamento do symfony 1.4 (Novembro 2009), estamos próximos
de finalizar um ciclo. Este livro é a maneira perfeita de terminar o ciclo e, como tal,
você está prestes a ler o último livro sobre a série do symfony 1.x que será
publicado pela equipe do projeto symfony. O próximo livro será provavelmente centrado no
Symfony 2.0, e lançado no final de 2010.

Por este motivo, e muitos outros que vou explicar neste capítulo, este livro é
um pouco especial para nós.

Porque um outro livro?
---------------------

Já publicamos dois livros sobre symfony 1.3 e 1.4 recentemente:
"[Symfony Prático](http://books.sensiolabs.com/book/9782918390169)" e
"[O guia de referência symfony](http://books.sensiolabs.com/book/9782918390145)".
O primeiro é uma ótima maneira de começar a aprender symfony já que você aprende o básico do
framework através do desenvolvimento de um projeto real passo-a-passo.
Este último, é um livro de referência que detém quase toda a informação de configuração relacionada ao symfony,
que você pode precisar durante o seu desenvolvimento no dia-a-dia.

"Mais com o symfony" é um livro sobre os tópicos mais avançados do symfony. Este não é
o primeiro livro que você deve ler sobre symfony, mas é um livro que será útil para
pessoas que já desenvolveram vários pequenos projetos com o framework.
Se você sempre quis saber como funciona o symfony por dentro ou se você gostaria
de estender o framework de várias maneiras, para fazê-lo funcionar para suas necessidades específicas,
este livro é para você. Desta forma, "Mais com o symfony" tem tudo a ver
com elevar suas habilidades em symfony para o próximo nível.

Como este livro é uma coleção de tutoriais sobre vários temas, sinta-se livre para
ler os capítulos em qualquer ordem, com base no que você está tentando alcançar
com o framework.

Sobre este livro
---------------

Este livro é especial porque se trata de um *livro escrito pela comunidade* para a
comunidade. Dezenas de pessoas contribuíram para este livro: dos autores,
aos tradutores, aos revisores, uma grande quantidade de esforço
para a criação deste livro.

Este livro foi publicado simultaneamente em pelo menos cinco línguas
(inglês, francês, italiano, espanhol e japonês). Isto não teria sido
possível sem o trabalho benevolente das nossas equipes de tradução.

Este livro foi possível graças ao *espírito open-source* e o mesmo
é lançado sob uma licença open-source. Isto por si só, muda tudo.
Significa que ninguém foi pago para trabalhar neste livro: todos os colaboradores
trabalharam arduamente para finalizá-lo, porque eles estavam dispostos a fazê-lo. Cada um queria
compartilhar seus conhecimentos, dar algo de volta à comunidade, ajudar a popularizar
o symfony e, claro, se divertir e ficar famoso.

Este livro foi escrito por dez autores que utilizam symfony diariamente
como desenvolvedores ou gerentes de projeto. Eles têm um profundo conhecimento do
framework e tentaram compartilhar seu conhecimento e experiência através dos capítulos deste
livro.

Agradecimentos
---------------

Quando eu comecei a pensar em escrever outro livro sobre symfony, em agosto de 
2009, imediatamente tive uma idéia maluca: que tal escrever um livro em dois meses
e publicá-lo em cinco línguas simultaneamente! Claro, envolver
a comunidade em um projeto deste tamanho era quase obrigatório. Comecei a falar
sobre a idéia durante a conferência de PHP no Japão, e, em questão de horas, a
equipe de tradução para o japonês estava pronta para trabalhar. Isso foi incrível! A resposta
dos autores e tradutores foi igualmente encorajadora e, em pouco tempo,
"Mais com o symfony" havia nascido.

Eu quero agradecer à todos que participaram de uma forma ou de outra, durante a
criação deste livro. Isto inclui, em nenhuma ordem particular:

Ryan Weaver, Geoffrey Bachelet, Hugo Hamon, Jonathan Wage, Thomas Rabaix,
Fabrice Bernhard, Kris Wallsmith, Stefan Koopmanschap, Laurent Bonnet, Julien
Madelin, Franck Bodiot, Javier Eguiluz, Nicolas Ricci, Fabrizio Pucci,
Francesco Fullone, Massimiliano Arione, Daniel Londero, Xavier Briand,
Guillaume Bretou, Akky Akimoto, Hidenori Goto, Hideki Suzuki, Katsuhiro Ogawa,
Kousuke Ebihara, Masaki Kagaya, Masao Maeda, Shin Ohno, Tomohiro Mitsumune,
e Yoshihiro Takahara.

Antes de começarmos
---------------

Este livro foi escrito para ambos symfony 1.3 e symfony 1.4. Como escrever um
único livro de duas versões diferentes de um software é bastante incomum, esta
seção explica quais são as principais diferenças entre as duas versões, e
como fazer a melhor escolha para seus projetos.

Tanto a versão 1.3 quanto a versão 1.4 do symfony foram lançadas quase
ao mesmo tempo (no final de 2009). Na verdade, ambas possuem
**exatamente as mesmas funcionalidades**. A única diferença entre as duas versões
é como cada uma suporta a compatibilidade com versões mais antigas do symfony.

Symfony 1.3 é a versão que você deve usar, se você precisa atualizar um projeto legado
que usa uma versão mais antiga do symfony (1.0, 1.1 ou 1.2). Ela tem uma
camada de compatibilidade com versões anteriores e todas as funcionalidades que foram depreciadas
durante o período de desenvolvimento da versão 1.3 ainda estão disponíveis. Isso significa que a atualização
é fácil, simples e segura.

No entando, se você começar um novo projeto hoje, você deve usar o symfony 1.4. Essa
versão possui o mesmo conjunto de funcionalidades do symfony 1.3, exceto as funcionalidades depreciadas
e a camada de compatibilidade, que foram removidas. Esta versão
é mais limpa e também um pouco mais rápida do que o symfony 1.3. Outra grande vantagem
de usar o symfony 1.4 é o seu suporte estendido. Sendo uma versão de suporte estendido,
será mantida pela equipe principal do symfony por três anos (até novembro de
2012).

Claro, você pode migrar seus projetos para o symfony 1.3 e então lentamente atualizar
seu código para remover as funcionalidades depreciadas e eventualmente migrar para o symfony 1.4
a fim de se beneficiar do suporte estendido. Você tem tempo de sobra para
planejar a migração para o symfony 1.3, já que a mesma será mantida por um ano (até novembro de 2010).

Como este livro não descreve as funcionalidades depreciadas, todos os exemplos funcionam igualmente
bem em ambas as versões.
