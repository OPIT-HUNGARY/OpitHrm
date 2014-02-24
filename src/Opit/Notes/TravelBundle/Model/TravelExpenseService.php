<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Opit\Notes\TravelBundle\Model;

use Opit\Notes\TravelBundle\Entity\TravelExpense;
use Opit\Notes\TravelBundle\Entity\TravelRequest;
use Doctrine\ORM\EntityManager;
use Opit\Notes\TravelBundle\Entity\Status;
use Doctrine\Common\Collections\ArrayCollection;
use Opit\Notes\TravelBundle\Helper\Utils;

/**
 * Description of TravelExpense
 *
 * @author OPIT\kaufmann
 */
class TravelExpenseService
{
    protected $securityContext;
    protected $entityManager;
    protected $config;
    
    public function __construct($securityContext, EntityManager $entityManager, $container, $config = array())
    {
        $this->securityContext = $securityContext;
        $this->entityManager = $entityManager;
        $this->container = $container;
        $this->config = $config;
    }
    
    /**
     * Method to calculate the advances for the travel
     * 
     * @param \Opit\Notes\TravelBundle\Entity\TravelExpense $travelExpense
     * @return \Opit\Notes\TravelBundle\Entity\TravelExpense
     */
    public function calculateAdvances(TravelExpense $travelExpense)
    {
        $totalAdvanceSpent = 0;

        foreach ($travelExpense->getUserPaidExpenses() as $userPaidExpenses) {
                $totalAdvanceSpent += $userPaidExpenses->getAmount();
        }
        
        return $travelExpense;
    }
    
    /**
     * Method to calculate the per diem for the travel expense
     * 
     * @param EntityManager $entityManager
     * @param string $arrivalDateTime
     * @param string $departureDateTime
     * @return array
     */
    public function calculatePerDiem(EntityManager $entityManager, $arrivalDateTime, $departureDateTime)
    {
        $departureTimeHour = intval($departureDateTime->format('H'));
        $departureDay = intval($departureDateTime->format('d'));
        $departureDate = $departureDateTime->format('Y-m-d');

        $arrivalTimeHour = intval($arrivalDateTime->format('H'));
        $arrivalDay = intval($arrivalDateTime->format('d'));
        $arrivalDate = $arrivalDateTime->format('Y-m-d');
        
        $perDiemAmount = 0;
        $daysBetweenArrivalDeparture = 0;
        $totalTravelHoursOnSameDay = 0;
        $daysBetweenPerDiem = 0;
        $departureDayTravelHours = 0;
        $arrivalDayTravelHours = 0;
        $departureDayTravelHours = 0;
        $departureDayPerDiem = 0;
        $arrivalDayTravelHours = 0;
        $arrivalDayPerDiem = 0;
        
        if ($departureDate !== $arrivalDate) {
            
            while ($departureTimeHour < 24) {
                $departureTimeHour++;
                $departureDayTravelHours++;
            }

            $departureDayPerDiem =
                $entityManager->getRepository('OpitNotesTravelBundle:TEPerDiem')->findAmountToPay(
                    $departureDayTravelHours
                );
            
            $perDiemAmount += $departureDayPerDiem;
            
            while ($arrivalTimeHour > 0) {
                $arrivalTimeHour--;
                $arrivalDayTravelHours++;
            }

            $arrivalDayPerDiem =
                $entityManager->getRepository('OpitNotesTravelBundle:TEPerDiem')
                ->findAmountToPay($arrivalDayTravelHours);
            
            $perDiemAmount += $arrivalDayPerDiem;
            
            $daysBetweenArrivalDeparture = ($arrivalDay - $departureDay) - 1;
            $daysBetweenPerDiem =
                ($entityManager->getRepository('OpitNotesTravelBundle:TEPerDiem')
                ->findAmountToPay(24)*$daysBetweenArrivalDeparture);
            
            $perDiemAmount += $daysBetweenPerDiem;
        } else {
            $totalTravelHoursOnSameDay = 0;
            while ($departureTimeHour < $arrivalTimeHour) {
                $departureTimeHour++;
                $totalTravelHoursOnSameDay++;
            }
            $perDiemAmount +=
                $entityManager->getRepository('OpitNotesTravelBundle:TEPerDiem')
                ->findAmountToPay($totalTravelHoursOnSameDay);
        }

        return array(
            'totalTravelHoursOnSameDay' => $totalTravelHoursOnSameDay,
            'departureHours' => $departureDayTravelHours,
            'departurePerDiem' => $departureDayPerDiem,
            'arrivalHours' => $arrivalDayTravelHours,
            'arrivalPerDiem' => $arrivalDayPerDiem,
            'daysBetween' => $daysBetweenArrivalDeparture,
            'daysBetweenPerDiem' => $daysBetweenPerDiem,
            'totalPerDiem' => $perDiemAmount
        );
    }
    
    /**
     * Method to sum and add all employee and company paid expenses
     * 
     * @param \Opit\Notes\TravelBundle\Entity\TravelExpense $travelExpense
     * @return array
     */
    public function sumExpenses(TravelExpense $travelExpense)
    {
        $expensesPaidbyCompany = 0;
        $expensesPaidByEmployee = 0;
        $exchManager = $this->container->get('opit.service.exchange_rates');
        foreach ($travelExpense->getCompanyPaidExpenses() as $companyPaidExpenses) {
            $expensesPaidbyCompany += $exchManager->convertCurrency(
                $companyPaidExpenses->getCurrency()->getCode(),
                $this->config['default_currency'],
                $companyPaidExpenses->getAmount(),
                $this->getMidRate()
            );
        }

        foreach ($travelExpense->getUserPaidExpenses() as $userPaidExpenses) {
            $expensesPaidByEmployee += $exchManager->convertCurrency(
                $userPaidExpenses->getCurrency()->getCode(),
                $this->config['default_currency'],
                $userPaidExpenses->getAmount(),
                $this->getMidRate()
            );
        }
        
        return array(
            'companyPaidExpenses' => $expensesPaidbyCompany, 'employeePaidExpenses' => $expensesPaidByEmployee
        );
    }
    
    /**
     * Method to set edit rights for travel request depending on its current status
     * 
     * @param integer $travelRequestGM
     * @param integer $currentUser
     * @param integer $currentStatusId
     * @return array
     */
    public function setEditRights($travelRequestGM, $currentUser, $currentStatusId)
    {
        $isEditLocked = false;
        $isStatusLocked = false;
        if ($travelRequestGM === $currentUser || $this->securityContext->isGranted('ROLE_ADMIN')) {
            $isEditLocked = true;
            if (Status::CREATED === $currentStatusId || Status::REVISE === $currentStatusId) {
                $isStatusLocked = true;
            }
        } else {
            if (Status::CREATED !== $currentStatusId && Status::REVISE !== $currentStatusId) {
                $isEditLocked = true;
                $isStatusLocked = true;
            }
        }
        
        return array('isStatusLocked' => $isStatusLocked, 'isEditLocked' => $isEditLocked);
    }
    
    /**
     * Method to add company and employee paid expenses to travel expense
     * 
     * @param \Opit\Notes\TravelBundle\Entity\TravelExpense $travelExpense
     * @return \Opit\Notes\TravelBundle\Model\ArrayCollection
     */
    public function addChildNodes(TravelExpense $travelExpense)
    {
        $children = new ArrayCollection();
        
        foreach ($travelExpense->getCompanyPaidExpenses() as $companyPaidExpenses) {
            $children->add($companyPaidExpenses);
        }
            
        foreach ($travelExpense->getUserPaidExpenses() as $userPaidExpenses) {
            $children->add($userPaidExpenses);
        }
        
        $travelExpenseAdvancesReceived = $travelExpense->getTeAdvancesReceived();
        if (null !== $travelExpenseAdvancesReceived) {
            foreach ($travelExpense->getTeAdvancesReceived() as $teAdvancesReceived) {
                $children->add($teAdvancesReceived);
            }
        }
        
        return $children;
    }
    
    /**
     * Method to remove child nodes
     * 
     * @param EntityManager $entityManager
     * @param TravelExpense $travelExpense
     * @param ArrayCollection $children
     */
    public function removeChildNodes(EntityManager $entityManager, TravelExpense $travelExpense, $children)
    {
        foreach ($children as $child) {
            $className = Utils::getClassBasename($child);
            $getter = null;
            switch ($className){
                case 'TEUserPaidExpense':
                    $getter = 'getUserPaidExpenses';
                    break;
                case 'TECompanyPaidExpense':
                    $getter = 'getCompanyPaidExpenses';
                    break;
                case 'TEAdvancesReceived':
                    $getter = 'getTeAdvancesReceived';
                    break;
            }
            
            if (null !== $getter && false === $travelExpense->$getter()->contains($child)) {
                $child->setTravelExpense(null);
                $entityManager->remove($child);
            }
        }
    }
    
    /**
     * 
     * @param \Opit\Notes\TravelBundle\Entity\TravelRequest $travelRequest
     * @param type $exchService
     * @return type
     */
    public function getTRCosts(TravelRequest $travelRequest, $exchService)
    {
        $approvedCostsEUR = 0;
        $approvedCostsHUF = 0;
        $midRate = $this->getMidRate();
        foreach ($travelRequest->getAccomodations() as $accomodation) {
            $accomodationCost = $accomodation->getCost();
            $accomodationCurrency = $accomodation->getCurrency();

            $approvedCostsHUF += $exchService->convertCurrency(
                $accomodationCurrency->getCode(),
                'HUF',
                $accomodationCost,
                $midRate
            );
            $approvedCostsEUR += $exchService->convertCurrency(
                $accomodationCurrency->getCode(),
                'EUR',
                $accomodationCost,
                $midRate
            );
        }

        foreach ($travelRequest->getDestinations() as $destination) {
            $destinationCost = $destination->getCost();
            $destinationCurrency = $destination->getCurrency();

            $approvedCostsHUF += $exchService->convertCurrency(
                $destinationCurrency->getCode(),
                'HUF',
                $destinationCost,
                $midRate
            );
            $approvedCostsEUR += $exchService->convertCurrency(
                $destinationCurrency->getCode(),
                'EUR',
                $destinationCost,
                $midRate
            );
        }
        
        return array('HUF' => $approvedCostsHUF, 'EUR' => $approvedCostsEUR);
    }
    
    /**
     * Get the travel expense's midrate
     * 
     * Today's date has to be taken unless the travel expense's for approval status was set.
     * ALWAYS the first "for approval" status fixes the expense's midrate.
     * 
     * @return \DateTime The midrate datetime object.
     */
    public function getMidRate()
    {
        $status = $this->entityManager->getRepository('OpitNotesTravelBundle:Status')->find(Status::FOR_APPROVAL);
        $teStatus = $this->entityManager->getRepository('OpitNotesTravelBundle:StatesTravelExpenses')
                                 ->findOneByStatus($status, array('id' => 'ASC'));
        
        // Set the midrate of last month
        $teDate = $teStatus ? $teStatus->getCreated() : new \DateTime('today');
        $midRateDate = clone $teDate;
        $midRateDate->setDate($midRateDate->format('Y'), $midRateDate->format('m'), $this->config['mid_rate']['day']);
        $midRateDate->modify($this->config['mid_rate']['modifier']);
        
        // TODO: handle empty rates.
        
        return $midRateDate;
    }
    
    /**
     * Get company and user paid expenses and put them in an array depending on the
     * currency they have
     * 
     * @param \Opit\Notes\TravelBundle\Entity\TravelExpense $travelExpense
     * @return array
     */
    public function getCostsByCurrencies(TravelExpense $travelExpense)
    {
        $currencies = $this->entityManager->getRepository('OpitNotesCurrencyRateBundle:Currency')->findAll();
        $sumOfCompanyPaidExpensesByCurrencies = array();
        $sumOfEmployeePaidExpensesByCurrencies = array();
        foreach ($currencies as $currency) {
            $sumOfCompanyPaidExpensesByCurrencies[$currency->getCode()] = 0;
            $sumOfEmployeePaidExpensesByCurrencies[$currency->getCode()] = 0;
        }
        foreach ($travelExpense->getCompanyPaidExpenses() as $companyPaidExpenses) {
            $companyPaidExpenseAmount = $companyPaidExpenses->getAmount();
            $companyPaidExpenseCurrency = $companyPaidExpenses->getCurrency()->getCode();
            $sumOfCompanyPaidExpensesByCurrencies[$companyPaidExpenseCurrency] += $companyPaidExpenseAmount;
        }
        foreach ($travelExpense->getUserPaidExpenses() as $employeePaidExpense) {
            $employeePaidExpenseAmount = $employeePaidExpense->getAmount();
            $employeePaidExpenseCurrency = $employeePaidExpense->getCurrency()->getCode();
            $sumOfEmployeePaidExpensesByCurrencies[$employeePaidExpenseCurrency] += $employeePaidExpenseAmount;
        }
        
        return array(
            'employeePaidExpenses' => $sumOfEmployeePaidExpensesByCurrencies,
            'companyPaidExpenses' => $sumOfCompanyPaidExpensesByCurrencies
        );
    }
}
