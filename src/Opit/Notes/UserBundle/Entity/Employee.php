<?php

/*
 *  This file is part of the {Bundle}.
 *
 *  (c) Opit Consulting Kft. <info@opit.hu>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace Opit\Notes\UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Opit\Notes\LeaveBundle\Model\LeaveEntitlementEmployeeInterface;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Description of Employee
 *
 * @author OPIT Consulting Kft. - PHP Team - {@link http://www.opit.hu}
 * @package Opit
 * @subpackage Notes
 *
 * @ORM\Table(name="notes_employees")
 * @ORM\Entity(repositoryClass="Opit\Notes\UserBundle\Entity\EmployeeRepository")
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false)
 */
class Employee implements LeaveEntitlementEmployeeInterface
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
     * @var integer
     *
     * @ORM\Column(name="working_hours", type="integer")
     * @Assert\NotBlank(message="Working hours cannot be empty.", groups={"user"})
     * @Assert\Range(
     *      min = "0",
     *      max = "24",
     *      minMessage = "The working hours should be greater or equal 0.",
     *      maxMessage = "The working hours should be less than 24.",
     *      groups={"user"}
     * )
     */
    protected $workingHours;

    /**
     * @ORM\ManyToMany(targetEntity="Team", inversedBy="employee")
     * @ORM\JoinTable(name="notes_employees_teams")
     */
    protected $teams;

    /**
     * Employee leave requests
     *
     * @ORM\OneToMany(targetEntity="\Opit\Notes\LeaveBundle\Entity\LeaveRequest", mappedBy="employee")
     * @Assert\Valid
     */
    protected $leaveRequests;

    /**
     * @ORM\Column(type="string", length=25)
     * @Assert\NotBlank(message="The employee name may not be blank.", groups={"user"})
     */
    protected $employeeName;

    /**
     * @ORM\OneToOne(targetEntity="User", mappedBy="employee")
     */
    protected $user;

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
     * Set working hours
     *
     * @param integer $workingHours
     * @return Employee
     */
    public function setWorkingHours($workingHours)
    {
        $this->workingHours = $workingHours;

        return $this;
    }

    /**
     * Get working hours
     *
     * @return integer
     */
    public function getWorkingHours()
    {
        return $this->workingHours;
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
     * @param \Opit\Notes\LeaveBundle\Entity\LeaveRequest $leaveRequests
     * @return Employee
     */
    public function addLeaveRequest(\Opit\Notes\LeaveBundle\Entity\LeaveRequest $leaveRequests)
    {
        $this->leaveRequests[] = $leaveRequests;
        $leaveRequests->setEmployee($this); // synchronously updating inverse side

        return $this;
    }

    /**
     * Remove leaveRequests
     *
     * @param \Opit\Notes\LeaveBundle\Entity\LeaveRequest $leaveRequests
     */
    public function removeLeaveRequest(\Opit\Notes\LeaveBundle\Entity\LeaveRequest $leaveRequests)
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

    /**
     * @inheritDoc
     */
    public function getEmployeeName()
    {
        return $this->employeeName;
    }

     public function getEmployeeNameFormatted()
    {
        return $this->employeeName . ' <' . $this->getUser()->getEmail() . '>';
    }

    /**
     * Set employee name
     *
     * @param  string $employeeName
     * @return Employee
     */
    public function setEmployeeName($employeeName)
    {
        $this->employeeName = $employeeName;

        return $this;
    }

    /**
     * Set user
     *
     * @param UserInterface $user
     * @return Employee
     */
    public function setUser(UserInterface $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return User object
     */
    public function getUser()
    {
        return $this->user;
    }
}
