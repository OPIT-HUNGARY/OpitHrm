<?php

namespace Opit\Notes\TravelBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Opit\Notes\TravelBundle\Entity\TEPaidExpense;

/**
 * TECompanyPaidExpense
 *
 * @ORM\Entity
 */
class TECompanyPaidExpense extends TEPaidExpense
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
     * @var \Opit\Notes\TravelBundle\Entity\TravelExpense
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
     * Set date
     *
     * @param \DateTime $date
     * @return TECompanyPaidExpense
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
     * @return TECompanyPaidExpense
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
     * @return TECompanyPaidExpense
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
     * @return TECompanyPaidExpense
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
     * @return TECompanyPaidExpense
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
     * @return TECompanyPaidExpense
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
