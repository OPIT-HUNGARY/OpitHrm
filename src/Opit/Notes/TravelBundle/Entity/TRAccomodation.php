<?php

/*
 *  This file is part of the {Bundle}.
 * 
 *  (c) Opit Consulting Kft. <info@opit.hu>
 * 
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace Opit\Notes\TravelBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * TRAccomodation
 *
 * @author OPIT Consulting Kft. - PHP Team - {@link http://www.opit.hu}
 * @version 1.0
 * @package Notes
 * @subpackage TravelBundle
 * 
 * @ORM\Table(name="notes_tr_accomodation")
 * @ORM\Entity(repositoryClass="Opit\Notes\TravelBundle\Entity\TRAccomodationRepository")
 */
class TRAccomodation
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
     * @var integer
     *
     * @ORM\Column(name="number_of_nights", type="integer")
     */
    private $numberOfNights;

    /**
     * @var decimal
     *
     * @ORM\Column(name="cost", type="decimal", scale=2)
     */
    private $cost;

    /**
     * @var string
     *
     * @ORM\Column(name="city", type="string", length=255)
     */
    private $city;

    /**
     * @var string
     *
     * @ORM\Column(name="hotel_name", type="string", length=255)
     */
    private $hotelName;

    /**
     * @ORM\ManyToOne(targetEntity="TravelRequest", inversedBy="accomodations")
     */
    protected $travelRequest;

    /**
     * @ORM\ManyToOne(targetEntity="Opit\Notes\TravelBundle\Model\TravelCurrencyInterface")
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
     * Set numberOfNights
     *
     * @param integer $numberOfNights
     * @return TRAccomodation
     */
    public function setNumberOfNights($numberOfNights)
    {
        $this->numberOfNights = $numberOfNights;
    
        return $this;
    }

    /**
     * Get numberOfNights
     *
     * @return integer
     */
    public function getNumberOfNights()
    {
        return $this->numberOfNights;
    }

    /**
     * Set cost
     *
     * @param integer $cost
     * @return TRAccomodation
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
     * Set city
     *
     * @param string $city
     * @return TRAccomodation
     */
    public function setCity($city)
    {
        $this->city = $city;
    
        return $this;
    }

    /**
     * Get city
     *
     * @return string
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * Set hotelName
     *
     * @param string $hotelName
     * @return TRAccomodation
     */
    public function setHotelName($hotelName)
    {
        $this->hotelName = $hotelName;
    
        return $this;
    }

    /**
     * Get hotelName
     *
     * @return string
     */
    public function getHotelName()
    {
        return $this->hotelName;
    }

    /**
     * Set travelRequest
     *
     * @param \Opit\Notes\TravelBundle\Entity\TravelRequest $travelRequest
     * @return TRAccomodation
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