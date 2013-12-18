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
use Opit\Notes\TravelBundle\Entity\TECompanyPaidExpenses;
/**
 * Description of TEUserPaidExpenses
 *
 * @author OPIT Consulting Kft. - PHP/NOTES Team - {@link http://www.opit.hu}
 * @version 1.0
 * @package Opit
 * @subpackage TravelBundle
 */
class CompanyPaidExpensesTypeFixtures extends AbstractFixture implements OrderedFixtureInterface
{
    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        $userPaidExpense1 = new TECompanyPaidExpenses();
        $userPaidExpense1->setCurrency("HUF")
                        ->setDate(new \DateTime('2000-01-01'))
                        ->setExcahngeRate(300)
                        ->setAmount(2)
                        ->setDestination("Frankfurt")
                        ->setCostHuf(5000)
                        ->setCostEuro(180)
                        ->setDescription("To go accross the city");
        $manager->persist($userPaidExpense1);
        
        $userPaidExpense2 = new TECompanyPaidExpenses();
        $userPaidExpense2->setCurrency("EUR")
                        ->setDate(new \DateTime('2000-01-01'))
                        ->setExcahngeRate(300)
                        ->setAmount(6)
                        ->setDestination("Amsterdam")
                        ->setCostHuf(4000)
                        ->setCostEuro(140)
                        ->setDescription("Get our location");
        $manager->persist($userPaidExpense2);
        
        $manager->flush();
    }
    
    /**
     * {@inheritDoc}
     */
    public function getOrder()
    {
        return 1; // the order in which fixtures will be loaded
    }
}
