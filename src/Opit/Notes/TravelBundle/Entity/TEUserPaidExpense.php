<?php

namespace Opit\Notes\TravelBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Opit\Notes\TravelBundle\Entity\TEPaidExpense;

/**
 * TEUserPaidExpense
 *
 * @ORM\Entity
 */
class TEUserPaidExpense extends TEPaidExpense
{
  
    /**
     * @var integer
     */
    protected $id;

    /**
     * @var \DateTime
     */
    protected $date;

    /**
     * @var integer
     */
    protected $amount;

    /**
     * @var string
     */
    protected $destination;

    /**
     * @var string
     */
    protected $description;

    /**
     * @var \Opit\Notes\TravelBundle\Entity\TEExpenseType
     */
    protected $expenseType;

    /**
     * @ORM\ManyToOne(targetEntity="TravelExpense", inversedBy="userPaidExpenses")
     */
    protected $travelExpense;

    /**
     * @var boolean
     *
     * @ORM\Column(name="paid_in_advance", type="boolean")
     * @Assert\GreaterThanOrEqual(value = 0)
     */
    protected $paidInAdvance;

    /**
     * @ORM\ManyToOne(targetEntity="Opit\Notes\TravelBundle\Model\TravelCurrencyInterface")
     * @var TravelCurrencyInterface
     */
    protected $currency;
    
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
     * Set date
     *
     * @param \DateTime $date
     * @return TEUserPaidExpense
     */
    public function setDate($date)
    {
        $this->date = $date;
    
        return $this;
    }

    /**
     * Get date
     *
     * @return \DateTime 
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * Set amount
     *
     * @param integer $amount
     * @return TEUserPaidExpense
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;
    
        return $this;
    }

    /**
     * Get amount
     *
     * @return integer 
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * Set destination
     *
     * @param string $destination
     * @return TEUserPaidExpense
     */
    public function setDestination($destination)
    {
        $this->destination = $destination;
    
        return $this;
    }

    /**
     * Get destination
     *
     * @return string 
     */
    public function getDestination()
    {
        return $this->destination;
    }

    /**
     * Set description
     *
     * @param string $description
     * @return TEUserPaidExpense
     */
    public function setDescription($description)
    {
        $this->description = $description;
    
        return $this;
    }

    /**
     * Get description
     *
     * @return string 
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set expenseType
     *
     * @param \Opit\Notes\TravelBundle\Entity\TEExpenseType $expenseType
     * @return TEUserPaidExpense
     */
    public function setExpenseType(\Opit\Notes\TravelBundle\Entity\TEExpenseType $expenseType = null)
    {
        $this->expenseType = $expenseType;
    
        return $this;
    }

    /**
     * Get expenseType
     *
     * @return \Opit\Notes\TravelBundle\Entity\TEExpenseType 
     */
    public function getExpenseType()
    {
        return $this->expenseType;
    }

    /**
     * Set travelExpense
     *
     * @param \Opit\Notes\TravelBundle\Entity\TravelExpense $travelExpense
     * @return TEUserPaidExpense
     */
    public function setTravelExpense(\Opit\Notes\TravelBundle\Entity\TravelExpense $travelExpense = null)
    {
        $this->travelExpense = $travelExpense;
    
        return $this;
    }

    /**
     * Get travelExpense
     *
     * @return \Opit\Notes\TravelBundle\Entity\TravelExpense
     */
    public function getTravelExpense()
    {
        return $this->travelExpense;
    }
    
    /**
     * Get paidInAdvance
     *
     * @return \Opit\Notes\TravelBundle\Entity\TravelExpense
     */
    public function getPaidInAdvance()
    {
        return $this->paidInAdvance;
    }
    
    /**
     * Set paidInAdvance
     *
     * @param type $paidInAdvance
     * @return \Opit\Notes\TravelBundle\Entity\TEUserPaidExpense
     */
    public function setPaidInAdvance($paidInAdvance)
    {
        $this->paidInAdvance = $paidInAdvance;
        
        return $this;
    }
    
    /**
     * Set currency
     *
     * @param \Opit\Notes\CurrencyRateBundle\Entity\Currency $currency
     * @return TEPaidExpense
     */
    public function setCurrency(\Opit\Notes\CurrencyRateBundle\Entity\Currency $currency = null)
    {
        $this->currency = $currency;
    
        return $this;
    }

    /**
     * Get currency
     *
     * @return \Opit\Notes\CurrencyRateBundle\Entity\Currency
     */
    public function getCurrency()
    {
        return $this->currency;
    }
}
