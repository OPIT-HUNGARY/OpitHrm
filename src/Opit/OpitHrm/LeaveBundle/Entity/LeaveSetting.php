<?php

namespace Opit\OpitHrm\LeaveBundle\Entity;

/*
 * The MIT License
 *
 * Copyright 2014 OPIT\bota.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */


use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;

/**
 * LeaveSetting
 *
 * @ORM\Table(name="opithrm_leave_settings")
 * @ORM\Entity(repositoryClass="Opit\OpitHrm\LeaveBundle\Entity\LeaveSettingRepository")
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
     * @ORM\JoinColumn(name="leave_group_id", referencedColumnName="id")
     * @ORM\ManyToOne(targetEntity="\Opit\OpitHrm\LeaveBundle\Entity\LeaveGroup")
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
     * @param \Opit\OpitHrm\LeaveBundle\Entity\LeaveGroup $leaveGroup
     * @return LeaveSetting
     */
    public function setLeaveGroup(\Opit\OpitHrm\LeaveBundle\Entity\LeaveGroup $leaveGroup = null)
    {
        $this->leaveGroup = $leaveGroup;

        return $this;
    }

    /**
     * Get leaveGroup
     *
     * @return \Opit\OpitHrm\LeaveBundle\Entity\LeaveGroup 
     */
    public function getLeaveGroup()
    {
        return $this->leaveGroup;
    }
}
