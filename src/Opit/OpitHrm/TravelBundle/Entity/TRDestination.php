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

/**
 * TRDestination
 *
 * @author OPIT Consulting Kft. - PHP Team - {@link http://www.opit.hu}
 * @version 1.0
 * @package OPIT-HRM
 * @subpackage TravelBundle
 * 
 * @ORM\Table(name="opithrm_tr_destination")
 * @ORM\Entity(repositoryClass="Opit\OpitHrm\TravelBundle\Entity\TRDestinationRepository")
 */
class TRDestination
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
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * @var decimal
     *
     * @ORM\Column(name="cost", type="decimal", scale=2)
     * @Assert\Type(type="number", message="The cost should be number.")
     */
    private $cost;

    /**
     * @ORM\ManyToOne(targetEntity="TravelRequest", inversedBy="destinations")
     */
    protected $travelRequest;
    
    /**
     * @ORM\ManyToOne(targetEntity="TransportationType", inversedBy="destinations")
     */
    private $transportationType;

    /**
     * @ORM\ManyToOne(targetEntity="Opit\OpitHrm\TravelBundle\Model\TravelCurrencyInterface")
     * @ORM\JoinColumn(name="currency_id", referencedColumnName="code")
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
     * Set cost
     *
     * @param integer $cost
     * @return TRDestination
     */
    public function setCost($cost)
    {
        $this->cost = $cost;
    
        return $this;
    }

    /**
     * Get cost
     *
     * @return integer
     */
    public function getCost()
    {
        return $this->cost;
    }

    /**
     * Set travelRequest
     *
     * @param \Opit\OpitHrm\TravelBundle\Entity\TravelRequest $travelRequest
     * @return TRDestination
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
     * Set name
     *
     * @param string $name
     * @return TRDestination
     */
    public function setName($name)
    {
        $this->name = $name;
    
        return $this;
    }

    /**
     * Get name
     *
     * @return string 
     */
    public function getName()
    {
        return $this->name;
    }


    /**
     * Set transportationType
     *
     * @param \Opit\OpitHrm\TravelBundle\Entity\TransportationType $transportationType
     * @return TRDestination
     */
    public function setTransportationType(\Opit\OpitHrm\TravelBundle\Entity\TransportationType $transportationType = null)
    {
        $this->transportationType = $transportationType;
    
        return $this;
    }

    /**
     * Get transportationType
     *
     * @return \Opit\OpitHrm\TravelBundle\Entity\TransportationType
     */
    public function getTransportationType()
    {
        return $this->transportationType;
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
