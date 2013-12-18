<?php

namespace Opit\Notes\TravelBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * TEPaidExpenses
 *
 * @ORM\Table(name="notes_te_paid_expenses")
 * @ORM\Entity
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="type", type="string")
 * @ORM\DiscriminatorMap({"userPaidExpenses" = "TEUserPaidExpenses", "companyPaidExpenses" = "TECompanyPaidExpenses"})
 */
class TEPaidExpenses
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(name="currency", type="string", length=30)
     */
    protected $currency;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date", type="date")
     * @Assert\Date()
     */
    protected $date;

    /**
     * @var integer
     *
     * @ORM\Column(name="excahnge_rate", type="integer")
     */
    protected $excahngeRate;

    /**
     * @var integer
     *
     * @ORM\Column(name="amount", type="integer")
     */
    protected $amount;

    /**
     * @var string
     *
     * @ORM\Column(name="destination", type="string", length=255)
     */
    protected $destination;
  
    /**
     * @var integer
     *
     * @ORM\JoinColumn(name="expense_type_id", referencedColumnName="id", nullable=true)
     * @ORM\ManyToOne(targetEntity="TEExpenseType")
     */
    protected $expenseType;

    /**
     * @var integer
     *
     * @ORM\Column(name="cost_huf", type="integer")
     */
    protected $costHuf;

    /**
     * @var integer
     *
     * @ORM\Column(name="cost_euro", type="integer")
     */
    protected $costEuro;

    /**
     * @ORM\ManyToOne(targetEntity="TravelExpense", inversedBy="paidExpenses")
     */
    protected $travelExpense;
  
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
     * Set currency
     *
     * @param string $currency
     * @return TEExpensesPaid
     */
    public function setCurrency($currency)
    {
        $this->currency = $currency;
    
        return $this;
    }

    /**
     * Get currency
     *
     * @return string
     */
    public function getCurrency()
    {
        return $this->currency;
    }

    /**
     * Set date
     *
     * @param \DateTime $date
     * @return TEExpensesPaid
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
     * Set excahngeRate
     *
     * @param integer $excahngeRate
     * @return TEExpensesPaid
     */
    public function setExcahngeRate($excahngeRate)
    {
        $this->excahngeRate = $excahngeRate;
    
        return $this;
    }

    /**
     * Get excahngeRate
     *
     * @return integer
     */
    public function getExcahngeRate()
    {
        return $this->excahngeRate;
    }

    /**
     * Set amount
     *
     * @param integer $amount
     * @return TEExpensesPaid
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
     * @return TEExpensesPaid
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
     * Set expenseType
     *
     * @param integer expenseType
     * @return TEExpensesPaid
     */
    public function setExpenseType($expenseType)
    {
        $this->expenseType = $expenseType;
    
        return $this;
    }

    /**
     * Get expenseType
     *
     * @return integer
     */
    public function getExpenseType()
    {
        return $this->expenseType;
    }

    /**
     * Set costHuf
     *
     * @param integer $costHuf
     * @return TEExpensesPaid
     */
    public function setCostHuf($costHuf)
    {
        $this->costHuf = $costHuf;
    
        return $this;
    }

    /**
     * Get costHuf
     *
     * @return integer
     */
    public function getCostHuf()
    {
        return $this->costHuf;
    }

    /**
     * Set costEuro
     *
     * @param integer $costEuro
     * @return TEExpensesPaid
     */
    public function setCostEuro($costEuro)
    {
        $this->costEuro = $costEuro;
    
        return $this;
    }

    /**
     * Get costEuro
     *
     * @return integer
     */
    public function getCostEuro()
    {
        return $this->costEuro;
    }

    /**
     * Set travelExpense
     *
     * @param \Opit\Notes\TravelBundle\Entity\TravelExpense $travelExpense
     * @return TEExpensesPaid
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
}