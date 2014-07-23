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
use Opit\OpitHrm\TravelBundle\Entity\TEPaidExpense;

/**
 * TEUserPaidExpense
 *
 * @author OPIT Consulting Kft. - PHP Team - {@link http://www.opit.hu}
 * @version 1.0
 * @package OPIT-HRM
 * @subpackage TravelBundle
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
     * @var \Opit\OpitHrm\TravelBundle\Entity\TEExpenseType
     */
    protected $expenseType;

    /**
     * @ORM\ManyToOne(targetEntity="TravelExpense", inversedBy="userPaidExpenses")
     */
    protected $travelExpense;

    /**
     * @ORM\ManyToOne(targetEntity="Opit\OpitHrm\TravelBundle\Model\TravelCurrencyInterface")
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
     * @param \Opit\OpitHrm\TravelBundle\Entity\TEExpenseType $expenseType
     * @return TEUserPaidExpense
     */
    public function setExpenseType(\Opit\OpitHrm\TravelBundle\Entity\TEExpenseType $expenseType = null)
    {
        $this->expenseType = $expenseType;
    
        return $this;
    }

    /**
     * Get expenseType
     *
     * @return \Opit\OpitHrm\TravelBundle\Entity\TEExpenseType 
     */
    public function getExpenseType()
    {
        return $this->expenseType;
    }

    /**
     * Set travelExpense
     *
     * @param \Opit\OpitHrm\TravelBundle\Entity\TravelExpense $travelExpense
     * @return TEUserPaidExpense
     */
    public function setTravelExpense(\Opit\OpitHrm\TravelBundle\Entity\TravelExpense $travelExpense = null)
    {
        $this->travelExpense = $travelExpense;
    
        return $this;
    }

    /**
     * Get travelExpense
     *
     * @return \Opit\OpitHrm\TravelBundle\Entity\TravelExpense
     */
    public function getTravelExpense()
    {
        return $this->travelExpense;
    }
    
    /**
     * Set currency
     *
     * @param \Opit\OpitHrm\CurrencyRateBundle\Entity\Currency $currency
     * @return TEPaidExpense
     */
    public function setCurrency(\Opit\OpitHrm\CurrencyRateBundle\Entity\Currency $currency = null)
    {
        $this->currency = $currency;
    
        return $this;
    }

    /**
     * Get currency
     *
     * @return \Opit\OpitHrm\CurrencyRateBundle\Entity\Currency
     */
    public function getCurrency()
    {
        return $this->currency;
    }
}
