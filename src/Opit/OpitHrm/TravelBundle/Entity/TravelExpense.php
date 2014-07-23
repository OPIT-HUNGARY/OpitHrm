<?php

/*
 *  This file is part of the OPIT-HRM project.
 * 
 *  (c) Opit Consulting Kft. <info@opit.hu>
 * 
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace Opit\OpitHrm\TravelBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\ArrayCollection;
use Opit\OpitHrm\TravelBundle\Model\TravelResourceInterface;

/**
 * TravelExpense
 *
 * @author OPIT Consulting Kft. - PHP Team - {@link http://www.opit.hu}
 * @version 1.0
 * @package OPIT-HRM
 * @subpackage TravelBundle
 * 
 * @ORM\Table(name="opithrm_travel_expense")
 * @ORM\Entity(repositoryClass="Opit\OpitHrm\TravelBundle\Entity\TravelExpenseRepository")
 */
class TravelExpense implements TravelResourceInterface
{
    const TYPE = 'te';
    
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var User
     * @ORM\ManyToOne(targetEntity="Opit\OpitHrm\TravelBundle\Model\TravelRequestUserInterface", inversedBy="userTravelExpenses")
     */
    private $user;

    /**
     * @var boolean
     *
     * @ORM\Column(name="rechargeable", type="boolean")
     */
    private $rechargeable;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="departure_date_time", type="datetime")
     * @Assert\DateTime()
     */
    private $departureDateTime;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="arrival_date_time", type="datetime")
     * @Assert\DateTime()
     */
    private $arrivalDateTime;

    /**
     * @var string
     *
     * @ORM\Column(name="departure_country", type="string", length=30)
     */
    private $departureCountry;

    /**
     * @var string
     *
     * @ORM\Column(name="arrival_country", type="string", length=30)
     */
    private $arrivalCountry;

    /**
     * @var boolean
     *
     * @ORM\Column(name="pay_in_euro", type="boolean")
     */
    private $payInEuro;

    /**
     * @var string
     *
     * @ORM\Column(name="bank_account_number", type="string", length=50)
     */
    private $bankAccountNumber;

    /**
     * @var string
     *
     * @ORM\Column(name="bank_name", type="string", length=30)
     */
    private $bankName;

    /**
     * @var integer
     *
     * @ORM\Column(name="tax_identification", type="integer")
     */
    private $taxIdentification;
    
    /**
     * @ORM\OneToMany(targetEntity="TECompanyPaidExpense", mappedBy="travelExpense", cascade={"persist", "remove"})
     */
    private $companyPaidExpenses;
    
    /**
     * @ORM\OneToMany(targetEntity="TEUserPaidExpense", mappedBy="travelExpense", cascade={"persist", "remove"})
     */
    private $userPaidExpenses;
    
    /**
     * @var TravelRequest
     *
     * @ORM\OneToOne(targetEntity="TravelRequest", inversedBy="travelExpense")
     */
    private $travelRequest;
    

    /**
     * @ORM\OneToMany(targetEntity="StatesTravelExpenses", mappedBy="travelExpense", cascade={"persist", "remove"})
     */
    protected $states;
    
    /**
     * @ORM\OneToMany(targetEntity="TEAdvancesReceived", mappedBy="travelExpense", cascade={"persist", "remove"})
     */
    protected $advancesReceived;
    
    /**
     * @ORM\OneToMany(targetEntity="TENotification", mappedBy="travelExpense", cascade={"remove"})
     */
    protected $notifications;
    
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->userPaidExpenses = new ArrayCollection();
        $this->companyPaidExpenses = new ArrayCollection();
        $this->states = new ArrayCollection();
        $this->advancesReceived = new ArrayCollection();
        $this->notifications = new ArrayCollection();
    }
    
    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set user
     *
     * @param \Opit\OpitHrm\TravelBundle\Model\TravelRequestUserInterface $user
     * @return TravelExpense
     */
    public function setUser(\Opit\OpitHrm\TravelBundle\Model\TravelRequestUserInterface $user = null)
    {
        $this->user = $user;
    
        return $this;
    }

    /**
     * Get user
     *
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set rechargeable
     *
     * @param boolean $rechargeable
     * @return TravelExpense
     */
    public function setRechargeable($rechargeable)
    {
        $this->rechargeable = $rechargeable;
    
        return $this;
    }

    /**
     * Get rechargeable
     *
     * @return boolean
     */
    public function getRechargeable()
    {
        return $this->rechargeable;
    }

    /**
     * Set departureDateTime
     *
     * @param \DateTime $departureDateTime
     * @return TravelExpense
     */
    public function setDepartureDateTime($departureDateTime)
    {
        $this->departureDateTime = $departureDateTime;
    
        return $this;
    }

    /**
     * Get departureDateTime
     *
     * @return \DateTime
     */
    public function getDepartureDateTime()
    {
        return $this->departureDateTime;
    }

    /**
     * Set arrivalDateTime
     *
     * @param \DateTime $arrivalDateTime
     * @return TravelExpense
     */
    public function setArrivalDateTime($arrivalDateTime)
    {
        $this->arrivalDateTime = $arrivalDateTime;
    
        return $this;
    }

    /**
     * Get arrivalDateTime
     *
     * @return \DateTime
     */
    public function getArrivalDateTime()
    {
        return $this->arrivalDateTime;
    }

    /**
     * Set departureCountry
     *
     * @param string $departureCountry
     * @return TravelExpense
     */
    public function setDepartureCountry($departureCountry)
    {
        $this->departureCountry = $departureCountry;
    
        return $this;
    }

    /**
     * Get departureCountry
     *
     * @return string
     */
    public function getDepartureCountry()
    {
        return $this->departureCountry;
    }

    /**
     * Set arrivalCountry
     *
     * @param string $arrivalCountry
     * @return TravelExpense
     */
    public function setArrivalCountry($arrivalCountry)
    {
        $this->arrivalCountry = $arrivalCountry;
    
        return $this;
    }

    /**
     * Get arrivalCountry
     *
     * @return string
     */
    public function getArrivalCountry()
    {
        return $this->arrivalCountry;
    }
    
    /**
     * Set payInEuro
     *
     * @param boolean $payInEuro
     * @return TravelExpense
     */
    public function setPayInEuro($payInEuro)
    {
        $this->payInEuro = $payInEuro;
    
        return $this;
    }

    /**
     * Get payInEuro
     *
     * @return boolean
     */
    public function getPayInEuro()
    {
        return $this->payInEuro;
    }

    /**
     * Set bankAccountNumber
     *
     * @param string $bankAccountNumber
     * @return TravelExpense
     */
    public function setBankAccountNumber($bankAccountNumber)
    {
        $this->bankAccountNumber = $bankAccountNumber;
    
        return $this;
    }

    /**
     * Get bankAccountNumber
     *
     * @return string
     */
    public function getBankAccountNumber()
    {
        return $this->bankAccountNumber;
    }

    /**
     * Set bankName
     *
     * @param string $bankName
     * @return TravelExpense
     */
    public function setBankName($bankName)
    {
        $this->bankName = $bankName;
    
        return $this;
    }

    /**
     * Get bankName
     *
     * @return string
     */
    public function getBankName()
    {
        return $this->bankName;
    }

    /**
     * Set taxIdentification
     *
     * @param integer $taxIdentification
     * @return TravelExpense
     */
    public function setTaxIdentification($taxIdentification)
    {
        $this->taxIdentification = $taxIdentification;
    
        return $this;
    }

    /**
     * Get taxIdentification
     *
     * @return integer
     */
    public function getTaxIdentification()
    {
        return $this->taxIdentification;
    }

    /**
     * Add companyPaidExpenses
     *
     * @param \Opit\OpitHrm\TravelBundle\Entity\TECompanyPaidExpense $companyPaidExpenses
     * @return TravelExpense
     */
    public function addCompanyPaidExpense(\Opit\OpitHrm\TravelBundle\Entity\TECompanyPaidExpense $companyPaidExpenses)
    {
        $this->companyPaidExpenses[] = $companyPaidExpenses;
        $companyPaidExpenses->setTravelExpense($this); // synchronously updating inverse side
        
        return $this;
    }

    /**
     * Remove companyPaidExpenses
     *
     * @param \Opit\OpitHrm\TravelBundle\Entity\TECompanyPaidExpense $companyPaidExpenses
     */
    public function removeCompanyPaidExpense(\Opit\OpitHrm\TravelBundle\Entity\TECompanyPaidExpense $companyPaidExpenses)
    {
        $this->companyPaidExpenses->removeElement($companyPaidExpenses);
    }

    /**
     * Get companyPaidExpenses
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getCompanyPaidExpenses()
    {
        return $this->companyPaidExpenses;
    }

    /**
     * Add userPaidExpenses
     *
     * @param \Opit\OpitHrm\TravelBundle\Entity\TEUserPaidExpense $userPaidExpenses
     * @return TravelExpense
     */
    public function addUserPaidExpense(\Opit\OpitHrm\TravelBundle\Entity\TEUserPaidExpense $userPaidExpenses)
    {
        $this->userPaidExpenses[] = $userPaidExpenses;
        $userPaidExpenses->setTravelExpense($this); // synchronously updating inverse side
    
        return $this;
    }

    /**
     * Remove userPaidExpenses
     *
     * @param \Opit\OpitHrm\TravelBundle\Entity\TEUserPaidExpense $userPaidExpenses
     */
    public function removeUserPaidExpense(\Opit\OpitHrm\TravelBundle\Entity\TEUserPaidExpense $userPaidExpenses)
    {
        $this->userPaidExpenses->removeElement($userPaidExpenses);
    }

    /**
     * Get userPaidExpenses
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getUserPaidExpenses()
    {
        return $this->userPaidExpenses;
    }

    /**
     * Set travelRequest
     *
     * @param \Opit\OpitHrm\TravelBundle\Entity\TravelRequest $travelRequest
     * @return TravelExpense
     */
    public function setTravelRequest(\Opit\OpitHrm\TravelBundle\Entity\TravelRequest $travelRequest = null)
    {
        $this->travelRequest = $travelRequest;
    
        return $this;
    }

    /**
     * Get travelRequest
     *
     * @return \Opit\OpitHrm\TravelBundle\Entity\TravelRequest
     */
    public function getTravelRequest()
    {
        return $this->travelRequest;
    }

    /**
     * Add states
     *
     * @param \Opit\OpitHrm\TravelBundle\Entity\StatesTravelExpenses $states
     * @return TravelExpense
     */
    public function addState(\Opit\OpitHrm\TravelBundle\Entity\StatesTravelExpenses $states)
    {
        $states->setTravelExpense($this); // synchronously updating inverse side
        $this->states[] = $states;

        return $this;
    }

    /**
     * Remove states
     *
     * @param \Opit\OpitHrm\TravelBundle\Entity\StatesTravelExpenses $states
     */
    public function removeState(\Opit\OpitHrm\TravelBundle\Entity\StatesTravelExpenses $states)
    {
        $this->states->removeElement($states);
    }

    /**
     * Get states
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getStates()
    {
        return $this->states;
    }
    
    /**
     * Returns the travel type constant
     * 
     * @return string The travel entity type
     */
    public static function getType()
    {
        return self::TYPE;
    }

    /**
     * Add advancesReceived
     *
     * @param \Opit\OpitHrm\TravelBundle\Entity\TEAdvancesReceived $advancesReceived
     * @return TravelExpense
     */
    public function addAdvancesReceived(\Opit\OpitHrm\TravelBundle\Entity\TEAdvancesReceived $advancesReceived)
    {
        $this->advancesReceived[] = $advancesReceived;
        $advancesReceived->setTravelExpense($this);
    
        return $this;
    }

    /**
     * Remove advancesReceived
     *
     * @param \Opit\OpitHrm\TravelBundle\Entity\TEAdvancesReceived $advancesReceived
     */
    public function removeAdvancesReceived(\Opit\OpitHrm\TravelBundle\Entity\TEAdvancesReceived $advancesReceived)
    {
        $this->advancesReceived->removeElement($advancesReceived);
    }

    /**
     * Get advancesReceived
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getAdvancesReceived()
    {
        return $this->advancesReceived;
    }

    /**
     * Add notifications
     *
     * @param \Opit\OpitHrm\TravelBundle\Entity\TENotification $notifications
     * @return TravelExpense
     */
    public function addNotification(\Opit\OpitHrm\TravelBundle\Entity\TENotification $notifications)
    {
        $this->notifications[] = $notifications;
        $notifications->setTravelExpense($this);
    
        return $this;
    }

    /**
     * Remove notifications
     *
     * @param \Opit\OpitHrm\TravelBundle\Entity\TENotification $notifications
     */
    public function removeNotification(\Opit\OpitHrm\TravelBundle\Entity\TENotification $notifications)
    {
        $this->notifications->removeElement($notifications);
    }

    /**
     * Get notifications
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getNotifications()
    {
        return $this->notifications;
    }
}
