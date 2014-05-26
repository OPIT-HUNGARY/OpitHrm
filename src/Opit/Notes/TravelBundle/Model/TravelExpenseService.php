<?php

/*
 *  This file is part of the {Bundle}.
 * 
 *  (c) Opit Consulting Kft. <info@opit.hu>
 * 
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace Opit\Notes\TravelBundle\Model;

use Opit\Notes\TravelBundle\Entity\TravelExpense;
use Opit\Notes\TravelBundle\Entity\TravelRequest;
use Doctrine\ORM\EntityManagerInterface;
use Opit\Notes\StatusBundle\Entity\Status;
use Doctrine\Common\Collections\ArrayCollection;
use Opit\Component\Utils\Utils;
use Opit\Notes\CurrencyRateBundle\Model\ExchangeRateInterface;
use Opit\Notes\TravelBundle\Model\TravelRequestUserInterface;

/**
 * Description of TravelExpense
 *
 * @author OPIT Consulting Kft. - PHP Team - {@link http://www.opit.hu}
 * @version 1.0
 * @package Notes
 * @subpackage TravelBundle
 */
class TravelExpenseService
{
    protected $securityContext;
    protected $entityManager;
    protected $config;
    protected $exchangeService;
    
    public function __construct($securityContext, EntityManagerInterface $entityManager, ExchangeRateInterface $exchangeService, $config = array())
    {
        $this->securityContext = $securityContext;
        $this->entityManager = $entityManager;
        $this->exchangeService = $exchangeService;
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
     * @param string $arrivalDateTime
     * @param string $departureDateTime
     * @return array
     */
    public function calculatePerDiem($arrivalDateTime, $departureDateTime)
    {
        $departureTimeHour = intval($departureDateTime->format('H'));
        $departureDate = $departureDateTime->format('Y-m-d');

        $arrivalTimeHour = intval($arrivalDateTime->format('H'));
        $arrivalDate = $arrivalDateTime->format('Y-m-d');
        
        $perDiemAmount = 0;
        $daysBetweenArrivalDeparture = 0;
        $totalTravelHoursOnSameDay = 0;
        $daysBetweenPerDiem = 0;
        $departureDayTravelHours = 0;
        $arrivalDayTravelHours = 0;
        $departureDayPerDiem = 0;
        $arrivalDayPerDiem = 0;
        
        if ($departureDate !== $arrivalDate) {
            
            while ($departureTimeHour < 24) {
                $departureTimeHour++;
                $departureDayTravelHours++;
            }

            $departureDayPerDiem =
                $this->entityManager->getRepository('OpitNotesTravelBundle:TEPerDiem')->findAmountToPay(
                    $departureDayTravelHours
                );
            
            $perDiemAmount += $departureDayPerDiem;
            
            while ($arrivalTimeHour > 0) {
                $arrivalTimeHour--;
                $arrivalDayTravelHours++;
            }

            $arrivalDayPerDiem =
                $this->entityManager->getRepository('OpitNotesTravelBundle:TEPerDiem')
                ->findAmountToPay($arrivalDayTravelHours);
            
            $perDiemAmount += $arrivalDayPerDiem;
            
            $daysBetweenArrivalDeparture = date_diff($departureDateTime, $arrivalDateTime);
            $fullDays = $daysBetweenArrivalDeparture->days - 1;

            $daysBetweenPerDiem =
                ($this->entityManager->getRepository('OpitNotesTravelBundle:TEPerDiem')
                ->findAmountToPay(24) * $fullDays);
            
            $perDiemAmount += $daysBetweenPerDiem;
        } else {
            $totalTravelHoursOnSameDay = 0;
            while ($departureTimeHour < $arrivalTimeHour) {
                $departureTimeHour++;
                $totalTravelHoursOnSameDay++;
            }
            $perDiemAmount +=
                $this->entityManager->getRepository('OpitNotesTravelBundle:TEPerDiem')
                ->findAmountToPay($totalTravelHoursOnSameDay);
        }

        return array(
            'totalTravelHoursOnSameDay' => $totalTravelHoursOnSameDay,
            'departureHours' => $departureDayTravelHours,
            'departurePerDiem' => $departureDayPerDiem,
            'arrivalHours' => $arrivalDayTravelHours,
            'arrivalPerDiem' => $arrivalDayPerDiem,
            'daysBetween' => $fullDays,
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
        foreach ($travelExpense->getCompanyPaidExpenses() as $companyPaidExpenses) {
            $expensesPaidbyCompany += $this->exchangeService->convertCurrency(
                $companyPaidExpenses->getCurrency()->getCode(),
                $this->config['default_currency'],
                $companyPaidExpenses->getAmount(),
                $this->getMidRate()
            );
        }

        foreach ($travelExpense->getUserPaidExpenses() as $userPaidExpenses) {
            $expensesPaidByEmployee += $this->exchangeService->convertCurrency(
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
     * @param integer $travelRequest
     * @param integer $currentUser
     * @param integer $currentStatusId
     * @return array
     */
    public function setEditRights(TravelRequest $travelRequest, TravelRequestUserInterface $currentUser, $currentStatusId)
    {
        $isEditLocked = true;
        $isStatusLocked = true;
        
        // If request was created by current user
        if ($travelRequest->getUser()->getId() === $currentUser->getId()) {
            if (in_array($currentStatusId, array(Status::CREATED, Status::REVISE))) {
                $isEditLocked = false;
                $isStatusLocked = false;
            } elseif ($travelRequest->getGeneralManager()->getId() === $travelRequest->getUser()->getId()) {
                $isStatusLocked = false;
            }
        } elseif ($travelRequest->getGeneralManager()->getId() === $currentUser->getId()) {
            if (!in_array($currentStatusId, array(Status::CREATED, Status::REVISE))) {
                $isStatusLocked = false;
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
        
        $travelExpenseAdvancesReceived = $travelExpense->getAdvancesReceived();
        if (null !== $travelExpenseAdvancesReceived) {
            foreach ($travelExpense->getAdvancesReceived() as $teAdvancesReceived) {
                $children->add($teAdvancesReceived);
            }
        }
        
        return $children;
    }
    
    /**
     * Method to remove child nodes
     * 
     * @param TravelExpense $travelExpense
     * @param ArrayCollection $children
     */
    public function removeChildNodes(TravelExpense $travelExpense, $children)
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
                    $getter = 'getAdvancesReceived';
                    break;
            }
            
            if (null !== $getter && false === $travelExpense->$getter()->contains($child)) {
                $child->setTravelExpense();
                $this->entityManager->remove($child);
            }
        }
    }
    
    /**
     * 
     * @param \Opit\Notes\TravelBundle\Entity\TravelRequest $travelRequest
     * @return array Travel request costs in HUF and EUR
     */
    public function getTRCosts(TravelRequest $travelRequest)
    {
        $approvedCostsEUR = 0;
        $approvedCostsHUF = 0;
        $midRate = $this->getMidRate();
        foreach ($travelRequest->getAccomodations() as $accomodation) {
            $accomodationCost = $accomodation->getCost();
            $accomodationCurrency = $accomodation->getCurrency();

            $approvedCostsHUF += $this->exchangeService->convertCurrency(
                $accomodationCurrency->getCode(),
                'HUF',
                $accomodationCost,
                $midRate
            );
            $approvedCostsEUR += $this->exchangeService->convertCurrency(
                $accomodationCurrency->getCode(),
                'EUR',
                $accomodationCost,
                $midRate
            );
        }

        foreach ($travelRequest->getDestinations() as $destination) {
            $destinationCost = $destination->getCost();
            $destinationCurrency = $destination->getCurrency();

            $approvedCostsHUF += $this->exchangeService->convertCurrency(
                $destinationCurrency->getCode(),
                'HUF',
                $destinationCost,
                $midRate
            );
            $approvedCostsEUR += $this->exchangeService->convertCurrency(
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
        $status = $this->entityManager->getRepository('OpitNotesStatusBundle:Status')->find(Status::FOR_APPROVAL);
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
    
    public function getAdvanceAmounts($employeePaidExpenses, $travelExpense)
    {
        $advacesPayback = array();
        
        // loop through all available currencies
        foreach ($employeePaidExpenses as $currency => $amount) {
            $isAdvanceReceived = false;
            // loop through all advances received
            foreach ($travelExpense->getAdvancesReceived() as $advanceReceived) {
                $advanceCurrencyCode = $advanceReceived->getCurrency()->getCode();
                if ($advanceCurrencyCode == $currency) {
                    
                    // set flag that an advance was received in currency
                    $isAdvanceReceived = true;
                    $advanceAmount = $advanceReceived->getAdvancesReceived();
                    
                    // calculate which needs to be paid back in currency
                    $advancePayback = $advanceAmount - $amount;
                    $payableToEmployee = 0;
                    
                    // if advance payback smaller 0, company needs to pay employee
                    if ($advancePayback < 0) {
                        // convert minus to plus
                        $payableToEmployee = abs($advancePayback);
                        $advancePayback = 0;
                    }
                    $advacesPayback[] = $this->getAmountsArray($advanceAmount, $amount, $advancePayback, $payableToEmployee, $currency);
                }
            }
            // if no amount was received in currency
            if (!$isAdvanceReceived) {
                if ($amount != 0) {
                    $advacesPayback[] = $this->getAmountsArray('0', $amount, '0', $amount, $currency);
                }
            }
        }
        
        return $advacesPayback;
    }
    
    private function getAmountsArray($advanceReceived, $amountSpent, $advancePayback, $payableToEmployee, $currency)
    {
        $amountInHUF = 0;
        if ('HUF' != $currency && 0 != $payableToEmployee) {
            $amountInHUF = $this->exchangeService->convertCurrency($currency, 'HUF', $payableToEmployee, $this->getMidRate());
        } elseif ('HUF' == $currency) {
            $amountInHUF = $amountSpent;
        }
        return array(
            'advanceReceived' => $advanceReceived,
            'amountSpent' => $amountSpent,
            'advancePayback' => $advancePayback,
            'payableToEmployee' => $payableToEmployee,
            'currency' => $currency,
            'amountInHUF' => $amountInHUF
        );
    }
}
