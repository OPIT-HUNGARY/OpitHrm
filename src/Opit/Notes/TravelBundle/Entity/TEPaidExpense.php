<?php

namespace Opit\Notes\TravelBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * TEPaidExpenses
 *
 * @ORM\Table(name="notes_te_paid_expense")
 * @ORM\Entity
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="type", type="string")
 * @ORM\DiscriminatorMap({"upe" = "TEUserPaidExpense", "cpe" = "TECompanyPaidExpense"})
 */
abstract class TEPaidExpense
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
     * @var \DateTime
     *
     * @ORM\Column(name="date", type="date")
     * @Assert\Date()
     */
    protected $date;

    /**
     * @var integer
     *
     * @ORM\Column(name="amount", type="float")
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
     * @ORM\ManyToOne(targetEntity="TravelExpense", inversedBy="paidExpenses")
     */
    protected $travelExpense;
  
    /**
     * @var string
     *
     * @ORM\Column(name="description", type="string", length=255)
     */
    protected $description;
   

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
     * @return TEPaidExpense
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
     * @return TEPaidExpense
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
     * @return TEPaidExpense
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
     * @return TEPaidExpense
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
     * @return TEPaidExpense
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
     * @return TEPaidExpense
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