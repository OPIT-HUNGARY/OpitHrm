<?php

/*
 * This file is part of the NOTES bundle.
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
use Opit\Notes\TravelBundle\Entity\TravelExpense;

/**
 * Description of TEUserPaidExpenses
 *
 * @author OPIT Consulting Kft. - PHP/NOTES Team - {@link http://www.opit.hu}
 * @version 1.0
 * @package Opit
 * @subpackage TravelBundle
 */
class TravelExpenseFixtures extends AbstractFixture implements OrderedFixtureInterface
{
    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        $travelExpense1 = new TravelExpense();
        $travelExpense1->setRechargeable(0)
            ->setUser($this->getReference('testUser-user'))
            ->setDepartureDateTime(new \DateTime('2013-12-15 11:14:15'))
            ->setDepartureCountry("Germany")
            ->setArrivalDateTime(new \DateTime('2013-12-17 21:26:45'))
            ->setArrivalCountry("Austria")
            ->setAdvancesRecieved(0)
            ->setAdvancesPayback(3000)
            ->setToSettle(2000)
            ->setPayInEuro(0)
            ->setBankAccountNumber("10401141-45562345-23456436")
            ->setBankName("K&H")
            ->setTaxIdentification(8456675674);
        $manager->persist($travelExpense1);
        
        $travelExpense2 = new TravelExpense();
        $travelExpense2->setRechargeable(1)
            ->setUser($this->getReference('testUser-user'))
            ->setDepartureDateTime(new \DateTime('2013-12-16 07:49:10'))
            ->setDepartureCountry("Finland")
            ->setArrivalDateTime(new \DateTime('2013-12-21 17:37:12'))
            ->setArrivalCountry("Estonia")
            ->setAdvancesRecieved(0)
            ->setAdvancesPayback(1500)
            ->setToSettle(1400)
            ->setPayInEuro(0)
            ->setBankAccountNumber("11111111-12646456-00000000")
            ->setBankName("OTP")
            ->setTaxIdentification(8413433674);
        $manager->persist($travelExpense2);
        
        $manager->flush();
    }
    
    /**
     * {@inheritDoc}
     */
    public function getOrder()
    {
        return 11; // the order in which fixtures will be loaded
    }
}
