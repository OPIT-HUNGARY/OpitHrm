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
use Opit\OpitHrm\StatusBundle\Entity\Status;
use Opit\OpitHrm\CoreBundle\Entity\AbstractBase;
use Opit\OpitHrm\TravelBundle\Entity\TravelExpense;

/**
 * This class is a container for the Travel Expense Status model
 *
 * @author OPIT Consulting Kft. - PHP Team - {@link http://www.opit.hu}
 * @version 1.0
 * @package OPIT-HRM
 * @subpackage TravelBundle
 *
 * @ORM\Table(name="opithrm_states_travel_expense")
 * @ORM\Entity(repositoryClass="Opit\OpitHrm\TravelBundle\Entity\StatesTravelExpensesRepository")
 */
class StatesTravelExpenses extends AbstractBase
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
     * @ORM\ManyToOne(targetEntity="Opit\OpitHrm\TravelBundle\Model\TravelStatusInterface", fetch="EAGER")
     * @ORM\JoinColumn(name="status_id", referencedColumnName="id")
     */
    protected $status;

    /**
     * @ORM\OneToOne(targetEntity="CommentTEStatus", mappedBy="status", cascade={"persist", "remove"})
     */
    protected $comment;

    public function __construct(Status $status = null, TravelExpense $travelExpense = null)
    {
        parent::__construct();
        $this->setStatus($status);
        $this->setTravelExpense($travelExpense);
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
     * @param \Opit\OpitHrm\TravelBundle\Entity\TravelExpense $travelExpense
     * @return StatesTravelExpenses
     */
    public function setTravelExpense(TravelExpense $travelExpense = null)
    {
        $this->travelExpense = $travelExpense;

        return $this;
    }

    /**
     * Get travel expense
     *
     * @return \Opit\OpitHrm\TravelBundle\Entity\TravelExpense
     */
    public function getTravelExpense()
    {
        return $this->travelExpense;
    }

    /**
     * Set status
     *
     * @param \Opit\OpitHrm\StatusBundle\Entity\Status $status
     * @return StatesTravelExpenses
     */
    public function setStatus(Status $status = null)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status
     *
     * @return \Opit\OpitHrm\StatusBundle\Entity\Status
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set comment
     *
     * @param \Opit\OpitHrm\TravelBundle\Entity\CommentTEStatus $comment
     * @return StatesTravelExpenses
     */
    public function setComment(\Opit\OpitHrm\TravelBundle\Entity\CommentTEStatus $comment = null)
    {
        $comment->setStatus($this);
        $this->comment = $comment;

        return $this;
    }

    /**
     * Get comment
     *
     * @return \Opit\OpitHrm\TravelBundle\Entity\CommentTEStatus
     */
    public function getComment()
    {
        return $this->comment;
    }
}
