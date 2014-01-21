<?php

namespace Opit\Notes\CurrencyRateBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Opit\Notes\CurrencyRateBundle\Entity\AbstractBase;

/**
 * Rate
 *
 * @ORM\Table(name="notes_rates")
 * @ORM\Entity(repositoryClass="Opit\Notes\CurrencyRateBundle\Entity\RateRepository")
 */
class Rate extends AbstractBase
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
     * @ORM\JoinColumn(name="currency_code", referencedColumnName="code", nullable=true)
     * @ORM\ManyToOne(targetEntity="Currency")
     */
    private $currencyCode;

    /**
     * @var float
     *
     * @ORM\Column(name="rate", type="float")
     */
    private $rate;

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
     * Set currencyCode
     *
     * @param string $currencyCode
     * @return Rate
     */
    public function setCurrencyCode($currencyCode)
    {
        $this->currencyCode = $currencyCode;
    
        return $this;
    }

    /**
     * Get currencyCode
     *
     * @return string 
     */
    public function getCurrencyCode()
    {
        return $this->currencyCode;
    }

    /**
     * Set rate
     *
     * @param float $rate
     * @return Rate
     */
    public function setRate($rate)
    {
        $this->rate = $rate;
    
        return $this;
    }

    /**
     * Get rate
     *
     * @return float 
     */
    public function getRate()
    {
        return $this->rate;
    }
}
