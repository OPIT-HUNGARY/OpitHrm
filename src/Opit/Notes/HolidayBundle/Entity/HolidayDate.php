<?php

namespace Opit\Notes\HolidayBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * HolidayDate
 *
 * @ORM\Table(name="notes_holiday_dates")
 * @ORM\Entity
 */
class HolidayDate
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
     * @ORM\Column(name="holidayDate", type="date")
     * @Assert\NotBlank(message="The holiday date may not be blank.")
     * @Assert\Type("\DateTime")
     */
    private $holidayDate;

    /**
     * @ORM\JoinColumn(name="holiday_type_id", referencedColumnName="id", nullable=true)
     * @ORM\ManyToOne(targetEntity="HolidayType")
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
     * Set holidayDate
     *
     * @param \DateTime $holidayDate
     * @return HolidayDate
     */
    public function setHolidayDate($holidayDate)
    {
        $this->holidayDate = $holidayDate;

        return $this;
    }

    /**
     * Get holidayDate
     *
     * @return \DateTime 
     */
    public function getHolidayDate()
    {
        return $this->holidayDate;
    }

    /**
     * Set holidayType
     *
     * @param \stdClass $holidayType
     * @return HolidayDate
     */
    public function setHolidayType($holidayType)
    {
        $this->holidayType = $holidayType;

        return $this;
    }

    /**
     * Get holidayType
     *
     * @return \stdClass 
     */
    public function getHolidayType()
    {
        return $this->holidayType;
    }
}
