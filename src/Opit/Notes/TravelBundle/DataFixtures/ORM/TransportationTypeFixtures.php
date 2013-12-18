<?php

/*
 * This file is part of the Edk bundle.
 *
 * (c) Opit Consulting Kft. <info@opit.hu>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Opit\Notes\TravelBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Opit\Notes\TravelBundle\Entity\TransportationType;

/**
 * Description of GroupFixtures
 *
 * @author OPIT Consulting Kft. - PHP/NOTES Team - {@link http://www.opit.hu}
 * @version 1.0
 * @package Opit
 * @subpackage UserBundle
 */
class TransportationTypeFixtures extends AbstractFixture implements OrderedFixtureInterface
{
    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        $transportationTypes = array('Airplane', 'Bus', 'Car');
        
        for ($i = 0; $i < count($transportationTypes); $i++) {
            $transportationType = new TransportationType();
            $transportationType->setName($transportationTypes[$i]);
            $manager->persist($transportationType);
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
}
