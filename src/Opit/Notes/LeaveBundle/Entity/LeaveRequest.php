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
     * Constructor
     */
    public function __construct()
    {
        $this->leaves = new \Doctrine\Common\Collections\ArrayCollection();
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
}
