<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Opit\Notes\TravelBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * TEAdvancesReceived
 *
 * @ORM\Table(name="notes_te_advances_received")
 * @ORM\Entity(repositoryClass="Opit\Notes\TravelBundle\Entity\TEAdvancesReceived")
 */
class TEAdvancesReceived
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
    
    /**
     * @ORM\ManyToOne(targetEntity="TravelExpense", inversedBy="advancesReceived")
     * @ORM\JoinColumn(name="travel_expense", referencedColumnName="id")
     */
    protected $travelExpense;
    
    /**
     * @var float
     *
     * @ORM\Column(name="advances_received", type="float")
     */
    protected $advancesReceived;
    
    /**
     * @ORM\ManyToOne(targetEntity="Opit\Notes\TravelBundle\Model\TravelCurrencyInterface")
     * @ORM\JoinColumn(name="currency", referencedColumnName="code")
     **/
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
     * Set travelExpense
     *
     * @param \Opit\Notes\TravelBundle\Entity\TravelExpense $travelExpense
     * @return TEAdvancesReceived
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
     * Set currency
     *
     * @param \Opit\Notes\CurrencyRateBundle\Entity\Currency $currency
     * @return TEAdvancesReceived
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

    /**
     * Set advancesReceived
     *
     * @param float $advancesReceived
     * @return TEAdvancesReceived
     */
    public function setAdvancesReceived($advancesReceived)
    {
        $this->advancesReceived = $advancesReceived;
    
        return $this;
    }

    /**
     * Get advancesReceived
     *
     * @return float 
     */
    public function getAdvancesReceived()
    {
        return $this->advancesReceived;
    }
}
