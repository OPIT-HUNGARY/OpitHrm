OPIT-HRM README
========================

OPIT-HRM is a open source HRM application based on the Symfony framework.
The actual edition supports requirements for the Hungarian market.

Requirements
------------

OPIT-HRM requires a minimum version of PHP 5.3.3.
The following PHP extensions are required to enable all features:

  * php5-curl (http://www.php.net//manual/en/book.curl.php)
  * php5-ldap (http://www.php.net//manual/en/book.ldap.php)

Installation
------------

The best way to install OPIT-HRM is by cloning the repository. To get started
with a basic setup, run composer install, doctrine migrations and fixtures.
You can now login with admin/admin.

Bundles
------------

The OPIT-HRM application consists of the following bundles:

  * CoreBundle
  * CurrencyRateBundle
  * HiringBundle
  * LeaveBundle
  * NotificationBundle
  * StatusBundle
  * TravelBundle
  * UserBundle

Application versioning scheme
------------

Version numbers are composed of four (4) segments: 3 integers and a string respectively named major.minor.bugfix-qualifier.
Each segment captures a different intent:

  * The major segment indicates module additions
  * The minor segment indicates feature addition
  * The bugfix segment indicates bug fixes
  * The qualifier segment indicates the stage (alpha, beta, etc)

Contribution
------------

OPIT-HRM is an open source, community-driven project. Developers are
encouraged to contribute.

License
------------

OPIT-HRM is released under The GNU Lesser General Public License, version 3.0 (LGPL-3.0)