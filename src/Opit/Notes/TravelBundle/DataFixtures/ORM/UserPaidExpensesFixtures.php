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
use Opit\Notes\TravelBundle\Entity\TEUserPaidExpense;

/**
 * Description of TEUserPaidExpenses
 *
 * @author OPIT Consulting Kft. - PHP/NOTES Team - {@link http://www.opit.hu}
 * @version 1.0
 * @package Opit
 * @subpackage TravelBundle
 */
class UserPaidExpensesFixtures extends AbstractFixture implements OrderedFixtureInterface
{
    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        $userPaidExpense1 = new TEUserPaidExpense();
        $userPaidExpense1->setDate(new \DateTime('2000-01-01'))
            ->setAmount(2)
            ->setDestination("Frankfurt")
            ->setDescription("To go accross the city")
            ->setExpenseType($this->getReference('other-expense-type'))
            ->setTravelExpense($this->getReference('travelExpense1'));
        $manager->persist($userPaidExpense1);
        
        $userPaidExpense2 = new TEUserPaidExpense();
        $userPaidExpense2->setDate(new \DateTime('2000-01-01'))
            ->setAmount(6)
            ->setDestination("Amsterdam")
            ->setDescription("Get our location")
            ->setExpenseType($this->getReference('other-expense-type'))
            ->setTravelExpense($this->getReference('travelExpense1'));
        $manager->persist($userPaidExpense2);
        
        $manager->flush();
    }
    
    /**
     * {@inheritDoc}
     */
    public function getOrder()
    {
        return 12; // the order in which fixtures will be loaded
    }
}
