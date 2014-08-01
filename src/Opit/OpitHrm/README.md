OPIT-HRM README
========================

OPIT-HRM is an open source HRM application based on the Symfony framework.
The actual edition supports requirements for the Hungarian market,
but it can be setup while installation not to use Hungary specific functionalities.

Requirements
------------

* PHP >= 5.3.9 (some required extensions - ldap, openssl, curl)
* MySql < 5.6
* Apache >=2.0
* Ruby
* Sass
* Nodejs
* Coffeescript
* Composer

Installation
------------

Clone the OPIT-HRM repository and run the following commands in your
project directory.

 * composer.phar install
 * app/console doctrine:database:create
 * app/console doctrine:migrations:migrate
 * app/console doctrine:fixtures:load  (optional command - will install dummy data)
 * app/console opithrm:currency-rates:insert [--start="..."]
    Option:
    --start  For initial set up start date of fetching should be the 15th of
    the previous month. Valid format: 2014-01-10


Login the application with the following credentials

Username: admin
Password: admin

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

Documentation
------------

Documentation for users and developers of OPIT-HRM can be found at [OPIT-HRM Documentation][1]

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

[1]: http://www.opit.hu/opithrm/documentation