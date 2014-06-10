<?php

namespace Opit\Notes\LeaveBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * LeaveType
 *
 * @ORM\Table(name="notes_leave_types")
 * @ORM\Entity
 */
class LeaveType
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
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=100)
     * @Assert\NotBlank(message="The name can not be blank.")
     */
    protected $name;

    /**
     * @var boolean
     *
     * @ORM\Column(name="is_working_day", type="boolean")
     */
    protected $isWorkingDay;

    public function __toString()
    {
        return $this->getName();
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
     * Set name
     *
     * @param string $name
     * @return LeaveType
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set isWorkingDay
     *
     * @param boolean $isWorkingDay
     * @return LeaveType
     */
    public function setIsWorkingDay($isWorkingDay)
    {
        $this->isWorkingDay = $isWorkingDay;

        return $this;
    }

    /**
     * Get isWorkingDay
     *
     * @return boolean
     */
    public function getIsWorkingDay()
    {
        return $this->isWorkingDay;
    }
}
