Introduction
============

*by Fabien Potencier*

As of this writing, the symfony project has celebrated a significant milestone:
its [fourth birthday](http://trac.symfony-project.org/changeset/1). In just
four years, the symfony framework has grown to become one of the most popular
PHP frameworks in the world, powering sites such as
[Delicious](http://sf-to.org/delicious),
[Yahoo Bookmarks](http://sf-to.org/bookmarks)
and
[Daily Motion](http://sf-to.org/dailymotion).
But, with the recent release of symfony 1.4 (November 2009), we are about
to end a cycle. This book is the perfect way to finish the cycle and as such,
you are about to read the last book on the symfony 1 branch that will be
published by the symfony project team. The next book will most likely center
around symfony 2.0, to be released late 2010.

For this reason, and many others I will explain in this chapter, this book is
a bit special for us.

Why yet another book?
---------------------

We have already published two books on symfony 1.3 and 1.4 recently:
"[Practical symfony](http://books.sensiolabs.com/book/9782918390169)" and
"[The symfony reference guide](http://books.sensiolabs.com/book/9782918390145)".
The former is a great way to start learning symfony as you learn the basics of
the framework through the development of a real project in a step-by-step
tutorial. The latter is a reference book that holds most any symfony-related
configuration information that you may need during your day-to-day development.

"More with symfony" is a book about more advanced symfony topics. This is not
the first book you should read about symfony, but is one that will be helpful for
people who have already developed several small projects with the framework.
If you've ever wanted to know how symfony works under the hood or if you'd like
to extend the framework in various ways to make it work for your specific
needs, this book is for you. In this way, "More with symfony" is all about
taking your symfony skills to the next level.

As this book is a collection of tutorials about various topics, feel free to
read the chapters in any order, based on what you are trying to accomplish
with the framework.

About this book
---------------

This book is special because this is *a book written by the community* for the
community. Dozens of people have contributed to this book: from the authors,
to the translators, to the proof-readers, a large collection of effort has
been put forth towards this book.

This book has been published simultaneously in no less than five languages
(English, French, Italian, Spanish, and Japanese). This would not have been
possible without the benevolent work of our translation teams.

This book has been made possible thanks to the *Open-Source spirit* and it
is released under an Open-Source license. This fact alone changes everything.
It means that nobody has been paid to work on this book: all contributors
worked hard to deliver it because they were willing to do so. Each wanted
to share their knowledge, give something back to the community, help spread
the word about symfony and, of course, have some fun and become famous.

This book has been written by ten authors who use symfony on a day-to-day
basis as developers or project managers. They have a deep knowledge of the
framework and have tried to share their knowledge and experience in these
chapters.

Acknowledgments
---------------

When I started to think about writing yet another book about symfony in August
2009, I immediately had a crazy idea: what about writing a book in two months
and publishing it in five languages simultaneously! Of course, involving
the community in a project this big was almost mandatory. I started to talk
about the idea during the PHP conference in Japan, and in a matter of hours the
Japanese translation team was ready to work. That was amazing! The response
from the authors and translators was equally encouraging and, in a short time,
"More with symfony" was born.

I want to thank everybody who participated in one way or another during the
creation of this book. This includes, in no particular order:

Ryan Weaver, Geoffrey Bachelet, Hugo Hamon, Jonathan Wage, Thomas Rabaix,
Fabrice Bernhard, Kris Wallsmith, Stefan Koopmanschap, Laurent Bonnet, Julien
Madelin, Franck Bodiot, Javier Eguiluz, Nicolas Ricci, Fabrizio Pucci,
Francesco Fullone, Massimiliano Arione, Daniel Londero, Xavier Briand,
Guillaume Bretou, Akky Akimoto, Hidenori Goto, Hideki Suzuki, Katsuhiro Ogawa,
Kousuke Ebihara, Masaki Kagaya, Masao Maeda, Shin Ohno, Tomohiro Mitsumune,
and Yoshihiro Takahara.

Before we start
---------------

This book has been written for both symfony 1.3 and symfony 1.4. As writing a
single book for two different versions of a software is quite unusual, this
section explains what the main differences are between the two versions, and
how to make the best choice for your projects.

Both the symfony 1.3 and symfony 1.4 versions have been released at about
the same time (at the end of 2009). As a matter of fact, they both have the
**exact same feature set**. The only difference between the two versions
is how each supports backward compatibility with older symfony versions.

Symfony 1.3 is the release you'll want to use if you need to upgrade a legacy
project that uses an older symfony version (1.0, 1.1, or 1.2). It has a
backward compatibility layer and all the features that have been deprecated
during the 1.3 development period are still available. It means that upgrading
is easy, simple, and safe.

If you start a new project today, however, you should use symfony 1.4. This
version has the same feature set as symfony 1.3 but all the deprecated features,
including the entire compatibility layer, have been removed. This version
is cleaner and also a bit faster than symfony 1.3. Another big advantage
of using symfony 1.4 is its longer support. Being a Long Term Support release,
it will be maintained by the symfony core team for three years (until November
2012).

Of course, you can migrate your projects to symfony 1.3 and then slowly update
your code to remove the deprecated features and eventually move to symfony 1.4
in order to benefit from the long term support. You have plenty of time to
plan the move as symfony 1.3 will be supported for a year (until November 2010).

As this book does not describe deprecated features, all examples work equally
well on both versions.
