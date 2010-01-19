Introducción
============

*por Fabien Potencier*

En el momento de escribir estas líneas, el proyecto Symfony ha alcanzado un
hito muy significativo: su [cuarto aniversario](http://trac.symfony-project.org/changeset/1).
Cuatro años en los que el framework Symfony se ha posicionado como uno de los
frameworks PHP más populares en todo el mundo, utilizado para crear sitios como
[Delicious](http://sf-to.org/delicious), [Yahoo Bookmarks](http://sf-to.org/bookmarks)
y [Daily Motion](http://sf-to.org/dailymotion).

El reciente lanzamiento de Symfony 1.4 en noviembre de 2009 ha supuesto el fin
de un ciclo. Creemos que este libro es la mejor forma de concluir ese ciclo, por
lo que el libro que estás leyendo será el último que se publique oficialmente
sobre la rama Symfony 1.x. El próximo libro seguramente tratará sobre Symfony
2.0, cuyo lanzamiento está previsto para finales de 2010.

Por este motivo, y por muchos otros que se explican en esta introducción, este
libro es muy especial para nosotros.

¿Por qué otro libro?
--------------------

Recientemente hemos publicado dos libros sobre Symfony 1.3 y 1.4:
"[Practical symfony](http://books.sensiolabs.com/book/9782918390169)" y
"[The symfony reference guide](http://books.sensiolabs.com/book/9782918390145)".
El primero es una de las mejores formas de aprender a programar con Symfony, ya
que se explican todos los conceptos fundamentales del framework mediante el
desarrollo de un proyecto web real en forma de tutorial paso a paso. El segundo
libro es una referencia que contiene toda la información sobre la extensa
configuración de Symfony, por lo que es un libro imprescindible para consultar
en el día a día.

"Más con Symfony" es un libro que cubre temas muy avanzados de Symfony. Por tanto
no se trata del primer libro que deberías leer sobre Symfony, sino que solamente
será útil para aquellos programadores que ya hayan desarrollado algunos proyectos
sencillos con el framework. Si alguna vez te has preguntado cómo funciona
Symfony por dentro o si quieres extender el framework para cubrir alguna necesidad
específica de tu proyecto, este el libro que necesitas. En resumen, "Más con
Symfony" es ideal para mejorar tus nivel de Symfony.

Como el libro está formado por una colección de tutoriales sobre diversos temas,
puedes leer los capítulos en cualquier orden y por tanto puedes ir directamente
al tema más apropiado en función de lo que estés desarrollando con el framework.

Sobre este libro
----------------

Este libro es muy especial porque es *un libro escrito por y para la comunidad*.
En la elaboración del libro han participado docenas de personas, desde los
autores hasta los traductores y correctores, por lo que es el fruto de un esfuerzo
colectivo muy grande.

Este libro se ha publicado de forma simultánea en al menos cinco idiomas
(inglés, francés, italiano, español y japonés). Todo eso no hubiera sido posible
sin el incansable trabajo de nuestros equipos de traducción.

El *espíritu del software libre* impregna todo el libro y ha hecho posible que
se publique con una licencia de tipo software libre. Esta decisión cambia
radicalmente la forma en la que tradicionalmente se crean y publican los libros.
Ningún colaborador ha recibido ningún tipo de compensación por su trabajo: todos
los que han contribuido a su desarrollo lo han hecho porque así lo han querido.
Cada uno ha decidido ayudar para compartir parte de sus conocimientos, o para
devolver a la comunidad parte de lo que ha recibido, para ayudar a que Symfony
sea más popular y por supuesto para pasar un buen rato y hacerse famoso.

El libro ha sido escrito por diez autores que utilizan Symfony a diario como
programadores o como jefes de proyecto. Todos ellos tienen un conocimiento muy
amplio del framework y han decidido compartir una parte a través de los capítulos
de este libro.

Agradecimientos
---------------

Cuando en agosto de 2009 empecé a darle vueltas a la idea de escribir un nuevo
libro sobre Symfony, se me ocurrió una idea que parecía absurda: quería escribir
un libro en menos de dos meses y publicarlo en cinco idiomas de forma simultánea.
Obviamente era obligatorio contar con la colaboración de toda la comunidad
Symfony, así que empecé a comentar la idea durante la conferencia de PHP en Japón.
Por increíble que parezca, en unas pocas horas ¡el equipo de traducción japonés
ya estaba listo! La respuesta que recibí del resto de autores y traductores fue
igual de positiva, por lo que en poco tiempo "Más con Symfony" ya estaba en marcha.

Me gustaría agradecer su colaboración a todas las personas que han ayudado de
una forma u otra en la creación de este libro. Sin ningún orden particular, la
lista de colaboradores está compuesta por:

Ryan Weaver, Geoffrey Bachelet, Hugo Hamon, Jonathan Wage, Thomas Rabaix,
Fabrice Bernhard, Kris Wallsmith, Stefan Koopmanschap, Laurent Bonnet, Julien
Madelin, Franck Bodiot, Javier Eguiluz, Nicolas Ricci, Fabrizio Pucci,
Francesco Fullone, Massimiliano Arione, Daniel Londero, Xavier Briand,
Guillaume Bretou, Akky Akimoto, Hidenori Goto, Hideki Suzuki, Katsuhiro Ogawa,
Kousuke Ebihara, Masaki Kagaya, Masao Maeda, Shin Ohno, Tomohiro Mitsumune
y Yoshihiro Takahara.

Antes de empezar
----------------

El contenido del libro se ha elaborado para Symfony 1.3 y Symfony 1.4. Como no
es muy habitual escribir un libro para dos versiones diferentes de una misma
aplicación, seguidamente se explican las diferencias principales entre las dos
versiones y cómo saber cuál elegir para tus próximos proyectos.

Tanto Symfony 1.3 como Symfony 1.4 se han publicado aproximadamente al mismo
tiempo a finales de 2009. De hecho, las dos versiones tienen **exactamente las
mismas características**. La única diferencia entre las dos versiones es cómo
realizan la retro-compatibilidad con las versiones anteriores de Symfony.

Symfony 1.3 es la versión que debes utilizar si estás actualizando un proyecto
que utiliza una versión anterior de Symfony (1.0, 1.1, or 1.2). Esta versión
dispone de una capa de retro-compatibilidad que hace que todas las características
que se han declarado obsoletas durante el desarrollo de Symfony 1.3 sigan
estando disponibles. Por tanto, la actualización suele ser sencilla, fácil y
segura.

Por su parte, si empiezas un nuevo proyecto de Symfony deberías utilizar la
versión Symfony 1.4. Esta versión dispone de las mismas características de
Symfony 1.3 pero se han eliminado todas las características obsoletas, incluyendo
la capa de retro-compatibilidad. Esta versión es mucho más *limpia* y un poco
más rápida que Symfony 1.3. Otra de sus grandes ventajas es que el soporte de
Symfony 1.4 es de muy larga duración, ya que será mantenido por el equipo de
desarrollo de Symfony durante tres años (hasta noviembre de 2012).

También es posible migrar los proyectos rápidamente a Symfony 1.3 para actualizarlos
después de Symfony 1.4 eliminando poco a poco todas sus características obsoletas.
De esta forma los proyectos actuales se pueden beneficiar del soporte de Symfony
1.4. En cualquier caso, tienes tiempo suficiente para realizar la transición,
ya que Symfony 1.3 será mantenido durante un año (hasta noviembre de 2010).

Como en este libro no se utiliza ninguna característica obsoleta, todos los
ejemplos y todo el código funciona igual de bien en cualquier versión.
