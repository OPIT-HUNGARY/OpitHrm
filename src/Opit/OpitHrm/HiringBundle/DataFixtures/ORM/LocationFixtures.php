<?php

/*
 *  This file is part of the OPIT-HRM project.
 *
 *  (c) Opit Consulting Kft. <info@opit.hu>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace Opit\OpitHrm\TravelBundle\DataFixtures\ORM;

use Doctrine\Common\Persistence\ObjectManager;
use Opit\OpitHrm\CoreBundle\DataFixtures\ORM\AbstractDataFixture;
use Opit\OpitHrm\HiringBundle\Entity\Location;

/**
 * Description of LocationFixtures
 *
 * @author OPIT Consulting Kft. - PHP Team - {@link http://www.opit.hu}
 * @version 1.0
 * @package OPIT-HRM
 * @subpackage HiringBundle
 */
class LocationFixtures extends AbstractDataFixture
{
    /**
     * {@inheritDoc}
     */
    public function doLoad(ObjectManager $manager)
    {
        $locationNames = array('Budapest, Hungary', 'Berlin, Germany', 'Auckland, New Zealand', 'Luxembourg, Luxembourg');

        foreach ($locationNames as $locationName) {
            $location = new Location();
            $location->setName($locationName);
            $location->setSystem(false);
            $manager->persist($location);

            $this->addReference(
                current(explode(',', strtolower($locationName))),
                $location)
            ;
        }

        $manager->flush();
    }

    /**
     * {@inheritDoc}
     */
    public function getOrder()
    {
        return 10; // the order in which fixtures will be loaded
    }

    /**
     *
     * @return array
     */
    protected function getEnvironments()
    {
        return array('prod', 'dev', 'test');
    }
}
