<?php

/*
 *  This file is part of the {Bundle}.
 * 
 *  (c) Opit Consulting Kft. <info@opit.hu>
 * 
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace Opit\Notes\LeaveBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Opit\Notes\StatusBundle\Entity\Status;
use Opit\Notes\LeaveBundle\Entity\LeaveRequest;
use Opit\Notes\CoreBundle\Entity\AbstractBase;

/**
 * This class is a container for the Travel Expense Status model
 *
 * @author OPIT Consulting Kft. - PHP Team - {@link http://www.opit.hu}
 * @version 1.0
 * @package Notes
 * @subpackage TravelBundle
 *
 * @ORM\Table(name="notes_states_leave_request")
 * @ORM\Entity(repositoryClass="Opit\Notes\LeaveBundle\Entity\StatesLeaveRequestsRepository")
 */
class StatesLeaveRequests extends AbstractBase
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="LeaveRequest", inversedBy="states", fetch="EAGER")
     * @ORM\JoinColumn(name="leave_request_id", referencedColumnName="id")
     */
    protected $leaveRequest;

     /**
     * @ORM\ManyToOne(targetEntity="\Opit\Notes\StatusBundle\Entity\Status", fetch="EAGER")
     * @ORM\JoinColumn(name="status_id", referencedColumnName="id")
     */
    protected $status;

    public function __construct(Status $status = null, LeaveRequest $leaveRequest = null)
    {
        $this->setStatus($status);
        $this->setLeaveRequest($leaveRequest);
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
     * Set leaveRequest
     *
     * @param \Opit\Notes\LeaveBundle\Entity\LeaveRequest $leaveRequest
     * @return StatesLeaveRequest
     */
    public function setLeaveRequest(\Opit\Notes\LeaveBundle\Entity\LeaveRequest $leaveRequest = null)
    {
        $this->leaveRequest = $leaveRequest;

        return $this;
    }

    /**
     * Get leaveRequest
     *
     * @return \Opit\Notes\LeaveBundle\Entity\LeaveRequest 
     */
    public function getLeaveRequest()
    {
        return $this->leaveRequest;
    }

    /**
     * Set status
     *
     * @param \Opit\Notes\StatusBundle\Entity\Status $status
     * @return StatesLeaveRequest
     */
    public function setStatus(\Opit\Notes\StatusBundle\Entity\Status $status = null)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status
     *
     * @return \Opit\Notes\StatusBundle\Entity\Status 
     */
    public function getStatus()
    {
        return $this->status;
    }
}
