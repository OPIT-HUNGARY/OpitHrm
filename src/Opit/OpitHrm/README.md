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
  * LeaveBundle
  * NotificationBundle
  * StatusBundle
  * TravelBundle
  * UserBundle

Contribution
------------

OPIT-HRM is an open source, community-driven project. Developers are
encouraged to contribute.

License
------------

OPIT-HRM is an released under The GNU Lesser General Public License, version 3.0 (LGPL-3.0)