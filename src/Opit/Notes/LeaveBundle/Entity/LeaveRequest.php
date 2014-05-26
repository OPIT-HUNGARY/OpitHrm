<?php

namespace Opit\Notes\LeaveBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * LeaveRequest
 *
 * @ORM\Table(name="notes_leave_request")
 * @ORM\Entity(repositoryClass="Opit\Notes\LeaveBundle\Entity\LeaveRequestRepository")
 */
class LeaveRequest
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
     * @ORM\OneToMany(targetEntity="Leave", mappedBy="leaveRequest", cascade={"persist", "remove"})
     * @Assert\Valid
     */
    protected $leaves;
    
    /**
     * @ORM\ManyToOne(targetEntity="Opit\Notes\UserBundle\Entity\Employee", inversedBy="leaveRequests")
     * @Assert\NotBlank(message="Employee cannot be empty.", groups={"user"})
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
    private $generalManager;
    
    /**
     * @ORM\ManyToOne(targetEntity="Opit\Notes\UserBundle\Entity\User", inversedBy="tmLeaveRequests")
     */
    private $teamManager;
    
    /**
     * @ORM\OneToMany(targetEntity="LRNotification", mappedBy="leaveRequest", cascade={"remove"})
     */
    protected $notifications;
    
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->leaves = new \Doctrine\Common\Collections\ArrayCollection();
        $this->notifications = new \Doctrine\Common\Collections\ArrayCollection();
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
}
