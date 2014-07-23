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

/**
 * TEAdvancesReceived
 *
 * @author OPIT Consulting Kft. - PHP Team - {@link http://www.opit.hu}
 * @version 1.0
 * @package OPIT-HRM
 * @subpackage TravelBundle
 * 
 * @ORM\Table(name="opithrm_te_advances_received")
 * @ORM\Entity(repositoryClass="Opit\OpitHrm\TravelBundle\Entity\TEAdvancesReceived")
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
     * @ORM\Column(name="advances_received", type="decimal", scale=2)
     */
    protected $advancesReceived;
    
    /**
     * @ORM\ManyToOne(targetEntity="Opit\OpitHrm\TravelBundle\Model\TravelCurrencyInterface")
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
     * @param \Opit\OpitHrm\TravelBundle\Entity\TravelExpense $travelExpense
     * @return TEAdvancesReceived
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
     * @return TEAdvancesReceived
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
