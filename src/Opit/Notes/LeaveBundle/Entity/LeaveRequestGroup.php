<?php

namespace Opit\Notes\LeaveBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Opit\Notes\CoreBundle\Entity\AbstractBase;

/**
 * LeaveRequestGroup
 *
 * @ORM\Table(name="notes_leave_request_groups")
 * @ORM\Entity
 */
class LeaveRequestGroup extends AbstractBase
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
     * @ORM\OneToMany(targetEntity="LeaveRequest", mappedBy="leaveRequestGroup", cascade={"persist", "remove"})
     */
    private $leaveRequests;

    public function __construct() {
        parent::__construct();
        $this->leaveRequests = new ArrayCollection();
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
     * Add leaveRequests
     *
     * @param \Opit\Notes\LeaveBundle\Entity\LeaveRequest $leaveRequests
     * @return LeaveRequestGroup
     */
    public function addLeaveRequest(\Opit\Notes\LeaveBundle\Entity\LeaveRequest $leaveRequests)
    {
        $this->leaveRequests[] = $leaveRequests;

        return $this;
    }

    /**
     * Remove leaveRequests
     *
     * @param \Opit\Notes\LeaveBundle\Entity\LeaveRequest $leaveRequests
     */
    public function removeLeaveRequest(\Opit\Notes\LeaveBundle\Entity\LeaveRequest $leaveRequests)
    {
        $this->leaveRequests->removeElement($leaveRequests);
    }

    /**
     * Get leaveRequests
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getLeaveRequests()
    {
        return $this->leaveRequests;
    }
}
