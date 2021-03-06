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
use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Collections\ArrayCollection;
use Opit\OpitHrm\CurrencyRateBundle\Entity\Rate;
use JMS\Serializer\Annotation as Serializer;
/**
 * Currency
 *
 * @author OPIT Consulting Kft. - PHP Team - {@link http://www.opit.hu}
 * @version 1.0
 * @package OPIT-HRM
 * @subpackage CurrencyRateBundle
 *
 * @ORM\Table(name="opithrm_currencies")
 * @ORM\Entity(repositoryClass="Opit\OpitHrm\CurrencyRateBundle\Entity\CurrencyRepository")
 */
class Currency
{
    /**
     * @var string
     *
     * @ORM\Column(name="code", type="string", length=3)
     * @ORM\Id
     */
    private $code;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="string", length=100)
     * @Serializer\Exclude
     */
    private $description;

    /**
     * @Serializer\Exclude
     * @ORM\OneToMany(targetEntity="Rate", mappedBy="currencyCode", cascade={"persist", "remove"})
     */
    private $rates;

    /**
     * Set code
     *
     * @param string $code
     * @return Currency
     */
    public function setCode($code)
    {
        $this->code = $code;

        return $this;
    }

    /**
     * Get code
     *
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Set description
     *
     * @param string $description
     * @return Currency
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
     * Constructor
     */
    public function __construct()
    {
        $this->rates = new ArrayCollection();
    }

    /**
     * Add rates
     *
     * @param \Opit\OpitHrm\CurrencyRateBundle\Entity\Rate $rates
     * @return Currency
     */
    public function addRate(Rate $rates)
    {
        $this->rates[] = $rates;

        return $this;
    }

    /**
     * Remove rates
     *
     * @param \Opit\OpitHrm\CurrencyRateBundle\Entity\Rate $rates
     */
    public function removeRate(Rate $rates)
    {
        $this->rates->removeElement($rates);
    }

    /**
     * Get rates
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getRates()
    {
        return $this->rates;
    }

    /**
     * Get the today's rate
     *
     * @param \DateTime $datetime
     * @return Rate A rate object
     */
    public function getCurrentRate(\DateTime $datetime)
    {
        //create datetime interval
        $datetimeCopy = clone $datetime;
        $start = $datetime->setTime(0, 0, 0);
        $end = $datetimeCopy->setTime(23, 59, 59);

        $criteria = new Criteria();
        $criteria->where(Criteria::expr()->gte('created', $start));
        $criteria->andWhere(Criteria::expr()->lte('created', $end));

        $result = $this->getRates()->matching($criteria);

        return $result->isEmpty() ? null : $result->first();
    }
}
