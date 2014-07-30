<?php

/*
 *  This file is part of the OPIT-HRM project.
 *
 *  (c) Opit Consulting Kft. <info@opit.hu>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace Opit\OpitHrm\TravelBundle\Model;

use Opit\OpitHrm\TravelBundle\Entity\TravelExpense;
use Opit\OpitHrm\TravelBundle\Entity\TravelRequest;
use Opit\OpitHrm\TravelBundle\Manager\TravelExpenseStatusManager;
use Doctrine\ORM\EntityManagerInterface;
use Opit\OpitHrm\StatusBundle\Entity\Status;
use Doctrine\Common\Collections\ArrayCollection;
use Opit\Component\Utils\Utils;
use Opit\OpitHrm\TravelBundle\Model\TravelRequestUserInterface;
use Opit\OpitHrm\TravelBundle\Model\TravelExpenseServiceInterface;

/**
 * Description of TravelExpense
 *
 * @author OPIT Consulting Kft. - PHP Team - {@link http://www.opit.hu}
 * @version 1.0
 * @package OPIT-HRM
 * @subpackage TravelBundle
 */
class TravelExpenseService extends TravelService implements TravelExpenseServiceInterface
{
    protected $securityContext;
    protected $entityManager;
    protected $statusManager;
    protected $config;

    public function __construct($securityContext, EntityManagerInterface $entityManager, TravelExpenseStatusManager $statusManager, $config = array())
    {
        $this->securityContext = $securityContext;
        $this->entityManager = $entityManager;
        $this->statusManager = $statusManager;
        $this->config = $config;
    }

    /**
     * @internal
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
     * @internal
     */
    public function calculatePerDiem($arrivalDateTime, $departureDateTime)
    {
        $currencyFormat = $this->config['currency_format'];
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
        $fullDays = 0;

        if ($departureDate !== $arrivalDate) {

            while ($departureTimeHour < 24) {
                $departureTimeHour++;
                $departureDayTravelHours++;
            }

            $departureDayPerDiem =
                $this->entityManager->getRepository('OpitOpitHrmTravelBundle:TEPerDiem')->findAmountToPay(
                    $departureDayTravelHours
                );

            $perDiemAmount += $departureDayPerDiem;

            while ($arrivalTimeHour > 0) {
                $arrivalTimeHour--;
                $arrivalDayTravelHours++;
            }

            $arrivalDayPerDiem =
                $this->entityManager->getRepository('OpitOpitHrmTravelBundle:TEPerDiem')
                ->findAmountToPay($arrivalDayTravelHours);

            $perDiemAmount += $arrivalDayPerDiem;

            $daysBetweenArrivalDeparture = date_diff($departureDateTime, $arrivalDateTime);
            $fullDays = $daysBetweenArrivalDeparture->days - 1;

            $daysBetweenPerDiem =
                ($this->entityManager->getRepository('OpitOpitHrmTravelBundle:TEPerDiem')
                ->findAmountToPay(24) * $fullDays);

            $perDiemAmount += $daysBetweenPerDiem;
        } else {
            $totalTravelHoursOnSameDay = 0;
            while ($departureTimeHour < $arrivalTimeHour) {
                $departureTimeHour++;
                $totalTravelHoursOnSameDay++;
            }
            $perDiemAmount +=
                $this->entityManager->getRepository('OpitOpitHrmTravelBundle:TEPerDiem')
                ->findAmountToPay($totalTravelHoursOnSameDay);
        }

        return array(
            'totalTravelHoursOnSameDay' => $totalTravelHoursOnSameDay,
            'departureHours' => $departureDayTravelHours,
            'departurePerDiem' => Utils::getformattedAmount(
                $departureDayPerDiem,
                $currencyFormat['decimals'],
                $currencyFormat['dec_point'],
                $currencyFormat['thousands_sep'],
                'EUR'
            ),
            'arrivalHours' => $arrivalDayTravelHours,
            'arrivalPerDiem' => Utils::getformattedAmount(
                $arrivalDayPerDiem,
                $currencyFormat['decimals'],
                $currencyFormat['dec_point'],
                $currencyFormat['thousands_sep'],
                'EUR'
            ),
            'daysBetween' => $fullDays,
            'daysBetweenPerDiem' => Utils::getformattedAmount(
                $daysBetweenPerDiem,
                $currencyFormat['decimals'],
                $currencyFormat['dec_point'],
                $currencyFormat['thousands_sep'],
                'EUR'
            ),
            'totalPerDiem' => Utils::getformattedAmount(
                $perDiemAmount,
                $currencyFormat['decimals'],
                $currencyFormat['dec_point'],
                $currencyFormat['thousands_sep'],
                'EUR'
            ),
        );
    }

    /**
     * @internal
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
                $this->getConversionDate($travelExpense)
            );
        }

        foreach ($travelExpense->getUserPaidExpenses() as $userPaidExpenses) {
            $expensesPaidByEmployee += $this->exchangeService->convertCurrency(
                $userPaidExpenses->getCurrency()->getCode(),
                $this->config['default_currency'],
                $userPaidExpenses->getAmount(),
                $this->getConversionDate($travelExpense)
            );
        }

        return array(
            'companyPaidExpenses' => $expensesPaidbyCompany, 'employeePaidExpenses' => $expensesPaidByEmployee
        );
    }

    /**
     * @internal
     */
    public function setEditRights(TravelRequest $travelRequest, TravelRequestUserInterface $currentUser, $currentStatusId)
    {
        $isEditLocked = true;
        $isStatusLocked = true;

        if (!$this->securityContext->isGranted('ROLE_ADMIN')) {
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
        } else {
            $isEditLocked = false;
            $isStatusLocked = false;
            if (in_array($currentStatusId, array(Status::APPROVED, Status::REJECTED))) {
                $isEditLocked = true;
                $isStatusLocked = true;
            } elseif ($currentStatusId === Status::FOR_APPROVAL) {
                $isEditLocked = true;
            }
        }

        return array('isStatusLocked' => $isStatusLocked, 'isEditLocked' => $isEditLocked);
    }

    /**
     * @internal
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
     * @internal
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
     * @internal
     */
    public function changeStatus(TravelExpense $travelExpense, $statusId, $comment = null)
    {
        $status = $this->statusManager->addStatus($travelExpense, $statusId, $comment);

        // Send email
        $this->prepareEmail($status, $travelExpense);

        // send a new notification when travel request or expense status changes
        $this->travelNotificationManager->addNewTravelNotification(
            $travelExpense,
            (Status::FOR_APPROVAL === $status->getId() ? true : false),
            $status
        );

        return $status;
    }

    /**
     * @internal
     */
    public function getConversionDate($travelExpense)
    {
        $teStatus = $this->entityManager->getRepository('OpitOpitHrmTravelBundle:StatesTravelExpenses')
            ->findStatusByStatusId($travelExpense->getId(), Status::FOR_APPROVAL, 'ASC');

        // Set the midrate of last month
        $teDate = $teStatus ? $teStatus->getCreated() : new \DateTime('today');
        $midRateDate = clone $teDate;
        $midRateDate->setDate($midRateDate->format('Y'), $midRateDate->format('m'), $this->config['mid_rate']['day']);
        $midRateDate->modify($this->config['mid_rate']['modifier']);

        return $midRateDate;
    }

    /**
     * @internal
     */
    public function getCostsByCurrencies(TravelExpense $travelExpense)
    {
        $currencies = $this->entityManager->getRepository('OpitOpitHrmCurrencyRateBundle:Currency')->findAll();
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

    /**
     * @internal
     */
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
                    $advacesPayback[] = $this->getAmountsArray(
                        $advanceAmount,
                        $amount,
                        $advancePayback,
                        $payableToEmployee,
                        $currency,
                        $travelExpense
                    );
                }
            }
            // if no amount was received in currency
            if (!$isAdvanceReceived) {
                if ($amount != 0) {
                    $advacesPayback[] = $this->getAmountsArray('0', $amount, '0', $amount, $currency, $travelExpense);
                }
            }
        }

        return $advacesPayback;
    }

    /**
     * Get amounts array
     *
     * @param float $advanceReceived
     * @param float $amountSpent
     * @param float $advancePayback
     * @param boolean $payableToEmployee
     * @param string $currency
     * @param \Opit\OpitHrm\TravelBundle\Entity\TravelExpense $travelExpense
     * @return mixin array.
     */
    private function getAmountsArray($advanceReceived, $amountSpent, $advancePayback, $payableToEmployee, $currency, $travelExpense)
    {
        $amountInHUF = 0;
        if ('HUF' != $currency && 0 != $payableToEmployee) {
            $amountInHUF = $this->exchangeService->convertCurrency(
                $currency,
                'HUF',
                $payableToEmployee,
                $this->getConversionDate($travelExpense)
            );
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
