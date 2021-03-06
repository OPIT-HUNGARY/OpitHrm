<?php

/*
 *  This file is part of the OPIT-HRM project.
 *
 *  (c) Opit Consulting Kft. <info@opit.hu>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace Opit\OpitHrm\CurrencyRateBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Opit\OpitHrm\CoreBundle\Entity\AbstractBase;
use JMS\Serializer\Annotation as Serializer;

/**
 * Rate
 *
 * @author OPIT Consulting Kft. - PHP Team - {@link http://www.opit.hu}
 * @version 1.0
 * @package OPIT-HRM
 * @subpackage CurrencyRateBundle
 *
 * @ORM\Table(name="opithrm_rates")
 * @ORM\Entity(repositoryClass="Opit\OpitHrm\CurrencyRateBundle\Entity\RateRepository")
 */
class Rate extends AbstractBase
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Serializer\Exclude
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\JoinColumn(name="currency_code", referencedColumnName="code", nullable=true)
     * @ORM\ManyToOne(targetEntity="Currency", inversedBy="rates")
     * @Serializer\XmlList(inline = true, entry = "code")
     */
    private $currencyCode;

    /**
     * @var float
     *
     * @ORM\Column(name="rate", type="float")
     */
    private $rate;

    public function __construct()
    {
        parent::__construct();
    }

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
