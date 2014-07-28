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
use Doctrine\Common\Collections\ArrayCollection;

/**
 * TEExpenseType
 *
 * @author OPIT Consulting Kft. - PHP Team - {@link http://www.opit.hu}
 * @version 1.0
 * @package OPIT-HRM
 * @subpackage TravelBundle
 *
 * @ORM\Table(name="opithrm_te_expense_type")
 * @ORM\Entity
 */
class TEExpenseType
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
     *
     * @ORM\OneToMany(targetEntity="TEPaidExpense", mappedBy="expenseType")
     */
    private $tePaidExpenses;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->tePaidExpenses = new ArrayCollection();
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
     * Set name
     *
     * @param string $name
     * @return TESpecification
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
     * Add tePaidExpenses
     *
     * @param \Opit\OpitHrm\TravelBundle\Entity\TEPaidExpense $tePaidExpenses
     * @return TEExpenseType
     */
    public function addTEPaidExpense(\Opit\OpitHrm\TravelBundle\Entity\TEPaidExpense $tePaidExpenses)
    {
        $this->tePaidExpenses[] = $tePaidExpenses;

        return $this;
    }

    /**
     * Remove tePaidExpenses
     *
     * @param \Opit\OpitHrm\TravelBundle\Entity\TEPaidExpense $tePaidExpenses
     */
    public function removeTEPaidExpense(\Opit\OpitHrm\TravelBundle\Entity\TEPaidExpense $tePaidExpenses)
    {
        $this->tePaidExpenses->removeElement($tePaidExpenses);
    }

    /**
     * Get tePaidExpenses
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getTEPaidExpenses()
    {
        return $this->tePaidExpenses;
    }
}
