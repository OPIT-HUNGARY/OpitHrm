<?php

/*
 *  This file is part of the {Bundle}.
 *
 *  (c) Opit Consulting Kft. <info@opit.hu>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace Opit\Notes\TravelBundle\DataFixtures\ORM;

use Doctrine\Common\Persistence\ObjectManager;
use Opit\Notes\TravelBundle\Entity\TravelExpense;
use Opit\Notes\TravelBundle\Entity\StatesTravelExpenses;
use Opit\Notes\TravelBundle\Entity\TEUserPaidExpense;
use Opit\Notes\TravelBundle\Entity\TECompanyPaidExpense;
use Opit\Notes\CoreBundle\DataFixtures\ORM\AbstractDataFixture;

/**
 * Description of TravelExpenseFixtures
 *
 * Travel request fixtures require currency rates from last month 1. to be
 * synchronized. See exchange:rates commands for details.
 *
 * @author OPIT Consulting Kft. - PHP Team - {@link http://www.opit.hu}
 * @version 1.0
 * @package Opit
 * @subpackage Notes
 */
class TravelExpenseFixtures extends AbstractDataFixture
{
    /**
     * {@inheritDoc}
     */
    public function doLoad(ObjectManager $manager)
    {
        $travelRequest = $this->getReference('travel-request-approved');
        $employee = $travelRequest->getUser()->getEmployee();

        // First travel expense
        // Set values from travel request where needed
        $travelExpense1 = new TravelExpense();
        $travelExpense1->setUser($travelRequest->getUser());
        $travelExpense1->setTaxIdentification($employee->getTaxIdentification());
        $travelExpense1->setRechargeable(false);
        $travelExpense1->setPayInEuro(true);
        $travelExpense1->setBankName($employee->getBankName());
        $travelExpense1->setBankAccountNumber($employee->getBankAccountNumber());
        $travelExpense1->setDepartureCountry('Hungary');
        $travelExpense1->setArrivalCountry('UK');
        $travelExpense1->setDepartureDateTime($travelRequest->getDepartureDate());
        $travelExpense1->setArrivalDateTime($travelRequest->getArrivalDate());


        // Add expenses paid by me/company
        if ($this->hasReference('currency-gbp')) {
            $expense1 = new TEUserPaidExpense();

            $expense1->setDescription('London weekly pass');
            $expense1->setDate($travelRequest->getDepartureDate());
            $expense1->setExpenseType($this->getReference('expense-type-tickets'));
            $expense1->setAmount(16.50);
            $expense1->setCurrency($this->getReference('currency-gbp'));
            $expense1->setDestination('London');

            $travelExpense1->addUserPaidExpense($expense1);

            $expense2 = new TEUserPaidExpense();

            $expense2->setDescription('Taxi to hotel');
            $expense2->setDate($travelRequest->getDepartureDate());
            $expense2->setExpenseType($this->getReference('expense-type-taxi'));
            $expense2->setAmount(35);
            $expense2->setCurrency($this->getReference('currency-gbp'));
            $expense2->setDestination('London');

            $travelExpense1->addUserPaidExpense($expense2);

            $trStatusApproved = $this->getReference('travel-request-status-approved');

            $expense3 = new TECompanyPaidExpense();

            $expense3->setDescription('Westpoint Hotel');
            $expense3->setDate($trStatusApproved->getCreated());
            $expense3->setExpenseType($this->getReference('expense-type-hotel'));
            $expense3->setAmount(753);
            $expense3->setCurrency($this->getReference('currency-gbp'));
            $expense3->setDestination('London');

            $travelExpense1->addCompanyPaidExpense($expense3);
        }

        if ($this->hasReference('currency-huf')) {
            $expense4 = new TECompanyPaidExpense();

            $expense4->setDescription('Flight tickets');
            $expense4->setDate($trStatusApproved->getCreated());
            $expense4->setExpenseType($this->getReference('expense-type-tickets'));
            $expense4->setAmount(112500);
            $expense4->setCurrency($this->getReference('currency-huf'));
            $expense4->setDestination('London');

            $travelExpense1->addCompanyPaidExpense($expense4);
        }

        // Add travel request states
        $travelRequest1Status = new StatesTravelExpenses($this->getReference('created'));
        $travelRequest1Status->setCreatedUser($this->getReference('user'));
        $travelExpense1->addState($travelRequest1Status);

        $travelExpense1->setTravelRequest($travelRequest);

        $manager->persist($travelExpense1);
        $manager->flush();
    }

    /**
     * {@inheritDoc}
     */
    public function getOrder()
    {
        return 21; // the order in which fixtures will be loaded
    }

    /**
     *
     * @return array
     */
    protected function getEnvironments()
    {
        return array('dev', 'test');
    }
}
