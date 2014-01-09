<?php

/*
 * This file is part of the Travel bundle.
 *
 * (c) Opit Consulting Kft. <info@opit.hu>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Opit\Notes\TravelBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * This class is a container for the Travel Expense Status model
 *
 * @author OPIT Consulting Kft. - NOTES Team - {@link http://www.opit.hu}
 * @version 1.0
 * @package Opit
 * @subpackage TravelBundle
 *
 * @ORM\Table(name="notes_states_travel_expense")
 * @ORM\Entity()
 */
class StatesTravelExpenses
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="TravelExpense", inversedBy="states", fetch="EAGER")
     * @ORM\JoinColumn(name="travel_expense_id", referencedColumnName="id")
     */
    protected $travelExpense;

     /**
     * @ORM\ManyToOne(targetEntity="Status", inversedBy="travelExpenses", fetch="EAGER")
     * @ORM\JoinColumn(name="status_id", referencedColumnName="id")
     */
    protected $status;

    public function __construct(\Opit\TravelBundle\Entity\Status $status = null)
    {
        $this->setStatus($status);
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
     * Set travel expense
     *
     * @param \Opit\Notes\TravelBundle\Entity\TravelExpense $TravelExpense
     * @return StatesTravelExpenses
     */
    public function setTravelExpense(\Opit\Notes\TravelBundle\Entity\TravelExpense $TravelExpense = null)
    {
        $this->TravelExpense = $TravelExpense;

        return $this;
    }

    /**
     * Get travel expense
     *
     * @return \Opit\Notes\TravelBundle\Entity\TravelExpense
     */
    public function getTravelExpense()
    {
        return $this->TravelExpense;
    }

    /**
     * Set status
     *
     * @param \Opit\Notes\TravelBundle\Entity\Status $status
     * @return StatesTravelExpenses
     */
    public function setStatus(\Opit\Notes\TravelBundle\Entity\Status $status = null)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status
     *
     * @return \Opit\Notes\TravelBundle\Entity\Status
     */
    public function getStatus()
    {
        return $this->status;
    }

}
