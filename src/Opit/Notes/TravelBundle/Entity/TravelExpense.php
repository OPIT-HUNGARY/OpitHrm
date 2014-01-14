<?php

namespace Opit\Notes\TravelBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * TravelExpense
 *
 * @ORM\Table(name="notes_travel_expense")
 * @ORM\Entity(repositoryClass="Opit\Notes\TravelBundle\Entity\TravelExpenseRepository")
 */
class TravelExpense
{
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
     * @ORM\ManyToOne(targetEntity="Opit\Notes\TravelBundle\Model\TravelRequestUserInterface", inversedBy="userTravelExpenses")
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
     * @ORM\Column(name="advances_recieved", type="float")
     */
    private $advancesRecieved;

    /**
     * @var float
     *
     * @ORM\Column(name="advances_payback", type="float")
     */
    private $advancesPayback;

    /**
     * @var float
     *
     * @ORM\Column(name="to_settle", type="float")
     */
    private $toSettle;

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
     * @param \Opit\Notes\UserBundle\Entity\User $user
     * @return TravelExpense
     */
    public function setUser(\Opit\Notes\UserBundle\Entity\User $user)
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
     * Set advancesRecieved
     *
     * @param boolean $advancesRecieved
     * @return TravelExpense
     */
    public function setAdvancesRecieved($advancesRecieved)
    {
        $this->advancesRecieved = $advancesRecieved;
    
        return $this;
    }

    /**
     * Get advancesRecieved
     *
     * @return boolean
     */
    public function getAdvancesRecieved()
    {
        return $this->advancesRecieved;
    }

    /**
     * Set advancesPayback
     *
     * @param float $advancesPayback
     * @return TravelExpense
     */
    public function setAdvancesPayback($advancesPayback)
    {
        $this->advancesPayback = $advancesPayback;
    
        return $this;
    }

    /**
     * Get advancesPayback
     *
     * @return float
     */
    public function getAdvancesPayback()
    {
        return $this->advancesPayback;
    }

    /**
     * Set toSettle
     *
     * @param float $toSettle
     * @return TravelExpense
     */
    public function setToSettle($toSettle)
    {
        $this->toSettle = $toSettle;
    
        return $this;
    }

    /**
     * Get toSettle
     *
     * @return float
     */
    public function getToSettle()
    {
        return $this->toSettle;
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
     * Constructor
     */
    public function __construct()
    {
        $this->userPaidExpenses = new \Doctrine\Common\Collections\ArrayCollection();
        $this->companyPaidExpenses = new \Doctrine\Common\Collections\ArrayCollection();
        $this->states = new ArrayCollection();
    }

    /**
     * Add companyPaidExpenses
     *
     * @param \Opit\Notes\TravelBundle\Entity\TECompanyPaidExpense $companyPaidExpenses
     * @return TravelExpense
     */
    public function addCompanyPaidExpense(\Opit\Notes\TravelBundle\Entity\TECompanyPaidExpense $companyPaidExpenses)
    {
        $this->companyPaidExpenses[] = $companyPaidExpenses;
        $companyPaidExpenses->setTravelExpense($this); // synchronously updating inverse side
        
        return $this;
    }

    /**
     * Remove companyPaidExpenses
     *
     * @param \Opit\Notes\TravelBundle\Entity\TECompanyPaidExpense $companyPaidExpenses
     */
    public function removeCompanyPaidExpense(\Opit\Notes\TravelBundle\Entity\TECompanyPaidExpense $companyPaidExpenses)
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
     * @param \Opit\Notes\TravelBundle\Entity\TEUserPaidExpense $userPaidExpenses
     * @return TravelExpense
     */
    public function addUserPaidExpense(\Opit\Notes\TravelBundle\Entity\TEUserPaidExpense $userPaidExpenses)
    {
        $this->userPaidExpenses[] = $userPaidExpenses;
        $userPaidExpenses->setTravelExpense($this); // synchronously updating inverse side
    
        return $this;
    }

    /**
     * Remove userPaidExpenses
     *
     * @param \Opit\Notes\TravelBundle\Entity\TEUserPaidExpense $userPaidExpenses
     */
    public function removeUserPaidExpense(\Opit\Notes\TravelBundle\Entity\TEUserPaidExpense $userPaidExpenses)
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
     * @param \Opit\Notes\TravelBundle\Entity\TravelRequest $travelRequest
     * @return TravelExpense
     */
    public function setTravelRequest(\Opit\Notes\TravelBundle\Entity\TravelRequest $travelRequest = null)
    {
        $this->travelRequest = $travelRequest;
    
        return $this;
    }

    /**
     * Get travelRequest
     *
     * @return \Opit\Notes\TravelBundle\Entity\TravelRequest
     */
    public function getTravelRequest()
    {
        return $this->travelRequest;
    }

    /**
     * Add states
     *
     * @param \Opit\Notes\TravelBundle\Entity\StatesTravelExpenses $states
     * @return TravelExpense
     */
    public function addState(\Opit\Notes\TravelBundle\Entity\StatesTravelExpenses $states)
    {
        $states->setTravelExpense($this); // synchronously updating inverse side
        $this->states[] = $states;

        return $this;
    }

    /**
     * Remove states
     *
     * @param \Opit\Notes\TravelBundle\Entity\StatesTravelExpenses $states
     */
    public function removeState(\Opit\Notes\TravelBundle\Entity\StatesTravelExpenses $states)
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
}
