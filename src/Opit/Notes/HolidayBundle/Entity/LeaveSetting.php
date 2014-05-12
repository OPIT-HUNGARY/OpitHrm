<?php

namespace Opit\Notes\HolidayBundle\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;

/**
 * LeaveSetting
 *
 * @ORM\Table(name="notes_leave_settings")
 * @ORM\Entity
 */
class LeaveSetting
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
     * @var integer
     * The number of settings groups
     * 
     * @ORM\Column(name="number", type="integer")
     * @Assert\NotBlank()
     * @Assert\Range(
     *      min = 0,
     *      minMessage = "The number must be at least 0"
     * )
     */
    private $number;

    /**
     * @var integer
     * The number Of leaves mean the leave days
     * 
     * @ORM\Column(name="number_of_leaves", type="integer")
     * @Assert\NotBlank()
     * @Assert\Range(
     *      min = 0,
     *      minMessage = "The number must be at least 0"
     * )
     */
    private $numberOfLeaves;

    /**
     * @ORM\JoinColumn(name="holiday_group_id", referencedColumnName="id")
     * @ORM\ManyToOne(targetEntity="\Opit\Notes\HolidayBundle\Entity\LeaveGroup")
     */
    protected $leaveGroup;


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
     * Set number
     *
     * @param integer $number
     * @return LeaveSetting
     */
    public function setNumber($number)
    {
        $this->number = $number;

        return $this;
    }

    /**
     * Get number
     *
     * @return integer 
     */
    public function getNumber()
    {
        return $this->number;
    }

    /**
     * Set numberOfLeaves
     *
     * @param integer $numberOfLeaves
     * @return LeaveSetting
     */
    public function setNumberOfLeaves($numberOfLeaves)
    {
        $this->numberOfLeaves = $numberOfLeaves;

        return $this;
    }

    /**
     * Get numberOfLeaves
     *
     * @return integer 
     */
    public function getNumberOfLeaves()
    {
        return $this->numberOfLeaves;
    }

    /**
     * Set leaveGroup
     *
     * @param \Opit\Notes\HolidayBundle\Entity\LeaveGroup $leaveGroup
     * @return LeaveSetting
     */
    public function setLeaveGroup(\Opit\Notes\HolidayBundle\Entity\LeaveGroup $leaveGroup = null)
    {
        $this->leaveGroup = $leaveGroup;

        return $this;
    }

    /**
     * Get leaveGroup
     *
     * @return \Opit\Notes\HolidayBundle\Entity\LeaveGroup 
     */
    public function getLeaveGroup()
    {
        return $this->leaveGroup;
    }
}
