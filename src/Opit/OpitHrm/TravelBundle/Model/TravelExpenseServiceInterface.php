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
use Opit\OpitHrm\TravelBundle\Model\TravelRequestUserInterface;

/**
 * Description of TravelExpenseServiceInterface
 *
 * @author OPIT Consulting Kft. - PHP Team - {@link http://www.opit.hu}
 * @package OPIT-HRM
 * @subpackage TravelBundle
 */
interface TravelExpenseServiceInterface
{
    /**
     * Method to calculate the advances for the travel
     *
     * @param \Opit\OpitHrm\TravelBundle\Entity\TravelExpense $travelExpense
     * @return \Opit\OpitHrm\TravelBundle\Entity\TravelExpense
     */
    public function calculateAdvances(TravelExpense $travelExpense);

    /**
     * Method to calculate the per diem for the travel expense
     *
     * @param string $arrivalDateTime
     * @param string $departureDateTime
     * @return array
     */
    public function calculatePerDiem($arrivalDateTime, $departureDateTime);

    /**
     * Method to sum and add all employee and company paid expenses
     *
     * @param \Opit\OpitHrm\TravelBundle\Entity\TravelExpense $travelExpense
     * @return array
     */
    public function sumExpenses(TravelExpense $travelExpense);

    /**
     * Method to set edit rights for travel request depending on its current status
     *
     * @param integer $travelRequest
     * @param integer $currentUser
     * @param integer $currentStatusId
     * @return array
     */
    public function setEditRights(TravelRequest $travelRequest, TravelRequestUserInterface $currentUser, $currentStatusId);

    /**
     * Method to add company and employee paid expenses to travel expense
     *
     * @param \Opit\OpitHrm\TravelBundle\Entity\TravelExpense $travelExpense
     * @return \Opit\OpitHrm\TravelBundle\Model\ArrayCollection
     */
    public function addChildNodes(TravelExpense $travelExpense);

    /**
     * Method to remove child nodes
     *
     * @param TravelExpense $travelExpense
     * @param ArrayCollection $children
     */
    public function removeChildNodes(TravelExpense $travelExpense, $children);

    /**
     * Change status
     *
     * @param \Opit\OpitHrm\TravelBundle\Entity\TravelRequest $travelRequest
     * @param integer $statusId
     * @param boolean $validationDisabled
     * @param string $comment a status comment
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function changeStatus(TravelExpense $travelExpense, $statusId, $comment = null);

    /**
     * Get the travel expense's midrate
     *
     * Today's date has to be taken unless the travel expense's for approval status was set.
     * ALWAYS the first "for approval" status fixes the expense's prior month midrate.
     *
     * @param \Opit\OpitHrm\TravelBundle\Entity\TravelExpense $travelExpense travel expense
     * @return \DateTime The midrate datetime object.
     */
    public function getConversionDate($travelExpense);

    /**
     * Get company and user paid expenses and put them in an array depending on the
     * currency they have
     *
     * @param \Opit\OpitHrm\TravelBundle\Entity\TravelExpense $travelExpense
     * @return array
     */
    public function getCostsByCurrencies(TravelExpense $travelExpense);

    /**
     * Get advance amounts
     *
     * @param \Opit\OpitHrm\TravelBundle\Entity $employeePaidExpenses
     * @param \Opit\OpitHrm\TravelBundle\Entity\TravelExpense $travelExpense
     * @return mixin $advacesPayback
     */
    public function getAdvanceAmounts($employeePaidExpenses, $travelExpense);
}
