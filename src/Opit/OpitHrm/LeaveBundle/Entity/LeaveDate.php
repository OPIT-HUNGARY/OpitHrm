<?php

namespace Opit\OpitHrm\LeaveBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * LeaveDate
 *
 * @ORM\Table(name="opithrm_leave_dates")
 * @ORM\Entity(repositoryClass="Opit\OpitHrm\LeaveBundle\Entity\LeaveDateRepository")
 * @UniqueEntity(fields={"holidayDate"}, message="The date is already in use.")
 */
class LeaveDate
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
     * @ORM\Column(name="leaveDate", type="date", unique=true)
     * @Assert\NotBlank(message="The leave date can not be blank.")
     * @Assert\Type("\DateTime")
     */
    private $holidayDate;

    /**
     * @ORM\JoinColumn(name="leave_type_id", referencedColumnName="id", nullable=true)
     * @ORM\ManyToOne(targetEntity="LeaveType")
     */
    private $holidayType;


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
     * Set leaveDate
     *
     * @param \DateTime $leaveDate
     * @return LeaveDate
     */
    public function setHolidayDate($holidayDate)
    {
        $this->holidayDate = $holidayDate;

        return $this;
    }

    /**
     * Get leaveDate
     *
     * @return \DateTime 
     */
    public function getHolidayDate()
    {
        return $this->holidayDate;
    }

    /**
     * Set leaveType
     *
     * @param \stdClass $leaveType
     * @return LeaveDate
     */
    public function setHolidayType($holidayType)
    {
        $this->holidayType = $holidayType;

        return $this;
    }

    /**
     * Get leaveType
     *
     * @return \stdClass 
     */
    public function getHolidayType()
    {
        return $this->holidayType;
    }

    /**
     * @Assert\True(message="The leave date must be in the future.")
     * @return boolean
     */
    public function isValidDate()
    {
        return ($this->getHolidayDate() > new \DateTime('today'));
    }
}
