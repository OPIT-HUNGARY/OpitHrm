<?php

namespace Opit\Notes\LeaveBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Opit\Notes\CoreBundle\Entity\AbstractBase;
use Symfony\Component\Validator\ExecutionContextInterface;

/**
 * LeaveRequest
 *
 * @ORM\Table(name="notes_leave_request")
 * @ORM\Entity(repositoryClass="Opit\Notes\LeaveBundle\Entity\LeaveRequestRepository")
 */
class LeaveRequest extends AbstractBase
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\OneToMany(targetEntity="Leave", mappedBy="leaveRequest", cascade={"persist", "remove"})
     * @Assert\Valid
     */
    protected $leaves;

    /**
     * @ORM\ManyToOne(targetEntity="LeaveRequestGroup", inversedBy="leaveRequests")
     * @ORM\JoinColumn(name="leave_request_group_id", referencedColumnName="id")
     */
    protected $leaveRequestGroup;

    /**
     * @ORM\ManyToOne(targetEntity="Opit\Notes\UserBundle\Entity\Employee", inversedBy="leaveRequests")
     * @Assert\NotBlank(message="Employee cannot be empty.")
     */
    protected $employee;

    /**
     * @var text
     * @ORM\Column(name="leave_request_id", type="string", length=11, nullable=true)
     */
    protected $leaveRequestId;

    /**
     * @ORM\OneToMany(targetEntity="StatesLeaveRequests", mappedBy="leaveRequest", cascade={"persist", "remove"})
     */
    protected $states;

    /**
     * @ORM\ManyToOne(targetEntity="Opit\Notes\UserBundle\Entity\User", inversedBy="gmLeaveRequests")
     * @Assert\NotBlank(message="General manager cannot be empty.")
     */
    protected $generalManager;

    /**
     * @ORM\ManyToOne(targetEntity="Opit\Notes\UserBundle\Entity\User", inversedBy="tmLeaveRequests")
     */
    protected $teamManager;

    /**
     * @ORM\Column(type="boolean")
     */
    protected $isMassLeaveRequest;

    /**
     * @ORM\OneToMany(targetEntity="LRNotification", mappedBy="leaveRequest", cascade={"remove"})
     */
    protected $notifications;
    
    protected $isOverlapped;
    
    protected $rejectedGmName;

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->leaves = new \Doctrine\Common\Collections\ArrayCollection();
        $this->notifications = new \Doctrine\Common\Collections\ArrayCollection();
        $this->isMassLeaveRequest = false;
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
     * Set leaveRequestId

     * @param string $leaveRequestId
     * @return LeaveRequest
     */
    public function setLeaveRequestId($leaveRequestId = null)
    {
        $this->leaveRequestId = $leaveRequestId;

        return $this;
    }

    /**
     * Get leave request id
     *
     * @return string
     */
    public function getLeaveRequestId()
    {
        return $this->leaveRequestId;
    }

    /**
     * Add leaves
     *
     * @param \Opit\Notes\LeaveBundle\Entity\Leave $leaves
     * @return LeaveRequest
     */
    public function addLeaf(\Opit\Notes\LeaveBundle\Entity\Leave $leaves)
    {
        $this->leaves[] = $leaves;
        $leaves->setLeaveRequest($this); // synchronously updating inverse side

        return $this;
    }

    /**
     * Remove leaves
     *
     * @param \Opit\Notes\LeaveBundle\Entity\Leave $leaves
     */
    public function removeLeaf(\Opit\Notes\LeaveBundle\Entity\Leave $leaves)
    {
        $this->leaves->removeElement($leaves);
    }

    /**
     * Get leaves
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getLeaves()
    {
        return $this->leaves;
    }

    /**
     * Set employee
     *
     * @param \Opit\Notes\UserBundle\Entity\Employee $employee
     * @return LeaveRequest
     */
    public function setEmployee(\Opit\Notes\UserBundle\Entity\Employee $employee = null)
    {
        $this->employee = $employee;

        return $this;
    }

    /**
     * Get employee
     *
     * @return \Opit\Notes\UserBundle\Entity\Employee
     */
    public function getEmployee()
    {
        return $this->employee;
    }

    /**
     * Add states
     *
     * @param StatesLeaveRequests $states
     * @return TravelRequest
     */
    public function addState(StatesLeaveRequests $states)
    {
        $states->setLeaveRequest($this); // synchronously updating inverse side
        $this->states[] = $states;

        return $this;
    }

    /**
     * Remove states
     *
     * @param StatesLeaveRequests $states
     */
    public function removeState(StatesLeaveRequests $states)
    {
        $this->states->removeElement($states);
    }

    /**
     * Get states
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getStates()
    {
        return $this->states;
    }

    /**
     * Get generalManager
     *
     * @return \Opit\Notes\UserBundle\Entity\User
     */
    public function getGeneralManager()
    {
        return $this->generalManager;
    }

    /**
     * Set generalManager
     *
     * @param \Opit\Notes\UserBundle\Entity\User $generalManager
     * @return LeaveRequest
     */
    public function setGeneralManager(\Opit\Notes\UserBundle\Entity\User $generalManager = null)
    {
        $this->generalManager = $generalManager;

        return $this;
    }

    /**
     * Get generalManager
     *
     * @return \Opit\Notes\UserBundle\Entity\User
     */
    public function getTeamManager()
    {
        return $this->teamManager;
    }

    /**
     * Set generalManager
     *
     * @param \Opit\Notes\UserBundle\Entity\User $teamManager
     * @return LeaveRequest
     */
    public function setTeamManager(\Opit\Notes\UserBundle\Entity\User $teamManager = null)
    {
        $this->teamManager = $teamManager;

        return $this;
    }

    /**
     * Add notifications
     *
     * @param \Opit\Notes\LeaveBundle\Entity\LRNotification $notifications
     * @return TravelRequest
     */
    public function addNotification(\Opit\Notes\LeaveBundle\Entity\LRNotification $notifications)
    {
        $this->notifications[] = $notifications;
        $notifications->setLeaveRequest($this);

        return $this;
    }

    /**
     * Remove notifications
     *
     * @param \Opit\Notes\LeaveBundle\Entity\LRNotification $notifications
     */
    public function removeNotification(\Opit\Notes\LeaveBundle\Entity\LRNotification $notifications)
    {
        $this->notifications->removeElement($notifications);
    }

    /**
     * Get notifications
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getNotifications()
    {
        return $this->notifications;
    }

    /**
     * Set leaveRequestGroup
     *
     * @param \Opit\Notes\LeaveBundle\Entity\LeaveRequestGroup $leaveRequestGroup
     * @return LeaveRequest
     */
    public function setLeaveRequestGroup(\Opit\Notes\LeaveBundle\Entity\LeaveRequestGroup $leaveRequestGroup = null)
    {
        $this->leaveRequestGroup = $leaveRequestGroup;

        return $this;
    }

    /**
     * Get leaveRequestGroup
     *
     * @return \Opit\Notes\LeaveBundle\Entity\LeaveRequestGroup
     */
    public function getLeaveRequestGroup()
    {
        return $this->leaveRequestGroup;
    }

    /**
     * Set isMassLeaveRequest
     *
     * @param boolean $isMassLeaveRequest
     * @return LeaveRequest
     */
    public function setIsMassLeaveRequest($isMassLeaveRequest)
    {
        $this->isMassLeaveRequest = $isMassLeaveRequest;

        return $this;
    }

    /**
     * Get isMassLeaveRequest
     *
     * @return boolean
     */
    public function getIsMassLeaveRequest()
    {
        return $this->isMassLeaveRequest;
    }
    
    /**
     * Set isOverlapped
     *
     * @param boolean $isOverlapped
     * @return LeaveRequest
     */
    public function setIsOverlapped($isOverlapped)
    {
        $this->isOverlapped = $isOverlapped;

        return $this;
    }

    /**
     * Get isOverlapped
     *
     * @return boolean
     */
    public function getIsOverlapped()
    {
        return $this->isOverlapped;
    }
    
    /**
     * Set rejectedGmName
     *
     * @param boolean $rejectedGmName
     * @return LeaveRequest
     */
    public function setRejectedGmName($rejectedGmName)
    {
        $this->rejectedGmName = $rejectedGmName;

        return $this;
    }

    /**
     * Get rejectedGmName
     *
     * @return boolean
     */
    public function getRejectedGmName()
    {
        return $this->rejectedGmName;
    }
    
    /**
     * validate leave dates overlapping
     * An existing groups option must use in the assert annotation.
     * This groups must on a property in order to this assert callback works
     *
     * @Assert\Callback
     */
    public function validateLeaveDates(ExecutionContextInterface $context)
    {
        $collection = $this->getLeaves();
        $overlappingDates = array();
        
        // Checking the date overlapping
        foreach ($collection as $element) {
            $current = $element;

            foreach ($collection as $otherElement) {
                if ($current !== $otherElement) {
                    // Checking the date overlapping with other leaves.
                    if (($current->getStartDate() <= $otherElement->getEndDate()) &&
                        ($otherElement->getStartDate() <= $current->getEndDate())) {
                        $overlappingDates[] = array(
                            $otherElement->getStartDate(),
                            $otherElement->getEndDate());
                        break;
                    }
                }
            }
        }
        // Error messages.
        foreach ($overlappingDates as $dates) {
            $context->addViolation(
                sprintf('Leave dates are overlapping: %s and %s', $dates[0]->format('Y-m-d'), $dates[1]->format('Y-m-d'))
            );
        }
    }
}
