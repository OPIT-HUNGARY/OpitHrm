<?php

namespace Opit\Notes\HolidayBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * HolidayRequest
 *
 * @ORM\Table(name="notes_leaves")
 * @ORM\Entity
 */
class Leave
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
     * @var \DateTime
     *
     * @ORM\Column(name="start_date", type="date")
     * @Assert\NotBlank(message="Start date cannot be empty.", groups={"user"})
     * @Assert\Date()
     */
    protected $startDate;
    
    /**
     * @var \DateTime
     *
     * @ORM\Column(name="end_date", type="date")
     * @Assert\NotBlank(message="End date cannot be empty.", groups={"user"})
     * @Assert\Date()
     */
    protected $endDate;
    
    /**
     * @var \Text
     *
     * @ORM\Column(name="description", type="text", nullable=true)
     */
    protected $description;
    
    /**
     * @ORM\ManyToOne(targetEntity="HolidayCategory", inversedBy="requests")
     */
    protected $category;
    
    /**
     * @ORM\ManyToOne(targetEntity="LeaveRequest", inversedBy="leaves")
     */
    protected $leaveRequest;

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
     * Set startDate
     *
     * @param \DateTime $startDate
     * @return Leave
     */
    public function setStartDate($startDate)
    {
        $this->startDate = $startDate;

        return $this;
    }

    /**
     * Get startDate
     *
     * @return \DateTime 
     */
    public function getStartDate()
    {
        return $this->startDate;
    }

    /**
     * Set endDate
     *
     * @param \DateTime $endDate
     * @return Leave
     */
    public function setEndDate($endDate)
    {
        $this->endDate = $endDate;

        return $this;
    }

    /**
     * Get endDate
     *
     * @return \DateTime 
     */
    public function getEndDate()
    {
        return $this->endDate;
    }
    
    /**
     * Set description
     *
     * @param \Text $description
     * @return Leave
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return \Text $description 
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set category
     *
     * @param \Opit\Notes\HolidayBundle\Entity\HolidayCategory $category
     * @return Leave
     */
    public function setCategory(\Opit\Notes\HolidayBundle\Entity\HolidayCategory $category = null)
    {
        $this->category = $category;

        return $this;
    }

    /**
     * Get category
     *
     * @return \Opit\Notes\HolidayBundle\Entity\HolidayCategory 
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * Set leaveRequest
     *
     * @param \Opit\Notes\HolidayBundle\Entity\LeaveRequest $leaveRequest
     * @return Leave
     */
    public function setLeaveRequest(\Opit\Notes\HolidayBundle\Entity\LeaveRequest $leaveRequest = null)
    {
        $this->leaveRequest = $leaveRequest;

        return $this;
    }

    /**
     * Get leaveRequest
     *
     * @return \Opit\Notes\HolidayBundle\Entity\LeaveRequest 
     */
    public function getLeaveRequest()
    {
        return $this->leaveRequest;
    }
}
