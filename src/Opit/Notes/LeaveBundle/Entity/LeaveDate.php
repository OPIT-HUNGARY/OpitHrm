<?php

namespace Opit\Notes\LeaveBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * LeaveDate
 *
 * @ORM\Table(name="notes_leave_dates")
 * @ORM\Entity
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
     * @ORM\Column(name="leaveDate", type="date")
     * @Assert\NotBlank(message="The leave date may not be blank.")
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
}
