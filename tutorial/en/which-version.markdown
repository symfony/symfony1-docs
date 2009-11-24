Which version of symfony?
=========================

The symfony documentation is the same for both symfony 1.3 and symfony 1.4. As
having a single documentation for two different versions of a software is
quite unusual, this section explains what the main differences are between the
two versions, and how to make the best choice for your projects.

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

As the documentation does not describe deprecated features, all examples work
equally well on both versions.
