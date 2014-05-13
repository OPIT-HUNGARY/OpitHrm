<?php
/*
 * The MIT License
 *
 * Copyright 2014 Marton Kaufmann <kaufmann@opit.hu>.
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

/**
 * Description of Teams
 *
 * @author OPIT Consulting Kft. - PHP Team - {@link http://www.opit.hu}
 * @package Opit
 * @subpackage Notes
 */

namespace Opit\Notes\UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Employee
 *
 * @ORM\Table(name="notes_employees")
 * @ORM\Entity
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false)
 */
class Employee
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
     * @ORM\Column(name="deletedAt", type="datetime", nullable=true)
     */
    private $deletedAt;
    
    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_of_birth", type="date")
     * @Assert\NotBlank(message="Date of birth cannot be empty.", groups={"user"})
     * @Assert\Date()
     */
    protected $dateOfBirth;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="joining_date", type="date")
     * @Assert\NotBlank(message="Joining date cannot be empty.", groups={"user"})
     * @Assert\Date()
     */
    protected $joiningDate;

    /**
     * @var integer
     *
     * @ORM\Column(name="number_of_children", type="integer")
     * @Assert\NotBlank(message="Number of Children cannot be empty.", groups={"user"})
     * @Assert\Range(
     *      min = "0",
     *      max = "30",
     *      minMessage = "The number of children should be greater or equal 0.",
     *      maxMessage = "The number of children should be less than 30.",
     *      groups={"user"}
     * )
     */
    protected $numberOfChildren;
    
    /**
     * @ORM\ManyToMany(targetEntity="Team", inversedBy="employee")
     * @ORM\JoinTable(name="notes_employees_teams")
     */
    protected $teams;
    
    /**
     * Employee leave requests
     * 
     * @ORM\OneToMany(targetEntity="\Opit\Notes\HolidayBundle\Entity\LeaveRequest", mappedBy="employee")
     * @Assert\Valid
     */
    protected $leaveRequests;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->teams = new \Doctrine\Common\Collections\ArrayCollection();
        $this->leaveRequests = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Set deletedAt
     *
     * @param \DateTime $deletedAt
     * @return Employee
     */
    public function setDeletedAt($deletedAt)
    {
        $this->deletedAt = $deletedAt;

        return $this;
    }

    /**
     * Get deletedAt
     *
     * @return \DateTime 
     */
    public function getDeletedAt()
    {
        return $this->deletedAt;
    }

    /**
     * Set dateOfBirth
     *
     * @param \DateTime $dateOfBirth
     * @return Employee
     */
    public function setDateOfBirth($dateOfBirth)
    {
        $this->dateOfBirth = $dateOfBirth;

        return $this;
    }

    /**
     * Get dateOfBirth
     *
     * @return \DateTime 
     */
    public function getDateOfBirth()
    {
        return $this->dateOfBirth;
    }

    /**
     * Set joiningDate
     *
     * @param \DateTime $joiningDate
     * @return Employee
     */
    public function setJoiningDate($joiningDate)
    {
        $this->joiningDate = $joiningDate;

        return $this;
    }

    /**
     * Get joiningDate
     *
     * @return \DateTime 
     */
    public function getJoiningDate()
    {
        return $this->joiningDate;
    }

    /**
     * Set numberOfChildren
     *
     * @param integer $numberOfChildren
     * @return Employee
     */
    public function setNumberOfChildren($numberOfChildren)
    {
        $this->numberOfChildren = $numberOfChildren;

        return $this;
    }

    /**
     * Get numberOfChildren
     *
     * @return integer 
     */
    public function getNumberOfChildren()
    {
        return $this->numberOfChildren;
    }

    /**
     * Add teams
     *
     * @param \Opit\Notes\UserBundle\Entity\Team $teams
     * @return Employee
     */
    public function addTeam(\Opit\Notes\UserBundle\Entity\Team $teams)
    {
        $this->teams[] = $teams;

        return $this;
    }

    /**
     * Remove teams
     *
     * @param \Opit\Notes\UserBundle\Entity\Team $teams
     */
    public function removeTeam(\Opit\Notes\UserBundle\Entity\Team $teams)
    {
        $this->teams->removeElement($teams);
    }

    /**
     * Get teams
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getTeams()
    {
        return $this->teams;
    }

    /**
     * Add leaveRequests
     *
     * @param \Opit\Notes\HolidayBundle\Entity\LeaveRequest $leaveRequests
     * @return Employee
     */
    public function addLeaveRequest(\Opit\Notes\HolidayBundle\Entity\LeaveRequest $leaveRequests)
    {
        $this->leaveRequests[] = $leaveRequests;
        $leaveRequests->setEmployee($this); // synchronously updating inverse side

        return $this;
    }

    /**
     * Remove leaveRequests
     *
     * @param \Opit\Notes\HolidayBundle\Entity\LeaveRequest $leaveRequests
     */
    public function removeLeaveRequest(\Opit\Notes\HolidayBundle\Entity\LeaveRequest $leaveRequests)
    {
        $this->leaveRequests->removeElement($leaveRequests);
    }

    /**
     * Get leaveRequests
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getLeaveRequests()
    {
        return $this->leaveRequests;
    }
}