<?php

namespace Opit\Notes\TravelBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * TEPerDiem
 *
 * @ORM\Table(name="notes_te_per_diem")
 * @ORM\Entity(repositoryClass="Opit\Notes\TravelBundle\Entity\TEPerDiemRepository")
 * @UniqueEntity(fields={"hours"}, message="The hours is already used.")
 */
class TEPerDiem
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
     * @var float
     *
     * @ORM\Column(name="hours", type="float")
     * @Assert\NotBlank(message="The hours should not be blank.")
     * @Assert\Range(
     *      min = 1,
     *      max = 24,
     *      minMessage = "The hours should be least 1.",
     *      maxMessage = "The hours should be greatest 24."
     * )
     */
    private $hours;

    /**
     * @var float
     *
     * @ORM\Column(name="amount", type="decimal", scale=2)
     * @Assert\NotBlank(message="The amount should not be blank.")
     * @Assert\Range(
     *      min = 1,
     *      minMessage = "The amount should be least 1."
     * )
     */
    private $amount;

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
     * Set hours
     *
     * @param float $hours
     * @return TEPerDiem
     */
    public function setHours($hours)
    {
        $this->hours = $hours;
    
        return $this;
    }

    /**
     * Get hours
     *
     * @return float
     */
    public function getHours()
    {
        return $this->hours;
    }

    /**
     * Set amount
     *
     * @param float $amount
     * @return TEPerDiem
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;
    
        return $this;
    }

    /**
     * Get amount
     *
     * @return float
     */
    public function getAmount()
    {
        return $this->amount;
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