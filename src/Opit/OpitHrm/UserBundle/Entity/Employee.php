<?php

/*
 *  This file is part of the OPIT-HRM project.
 *
 *  (c) Opit Consulting Kft. <info@opit.hu>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace Opit\OpitHrm\UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Opit\OpitHrm\LeaveBundle\Model\LeaveEntitlementEmployeeInterface;
use Symfony\Component\Validator\ExecutionContextInterface;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Description of Employee
 *
 * @author OPIT Consulting Kft. - PHP Team - {@link http://www.opit.hu}
 * @package OPIT-HRM
 * @subpackage UserBundle
 *
 * @ORM\Table(name="opithrm_employees")
 * @ORM\Entity(repositoryClass="Opit\OpitHrm\UserBundle\Entity\EmployeeRepository")
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false)
 * @UniqueEntity(fields={"taxIdentification"}, message="The tax id is already used.", groups={"employee"})
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
     * @ORM\JoinColumn(name="job_title_id", referencedColumnName="id", nullable=true)
     * @ORM\ManyToOne(targetEntity="JobTitle")
     */
    protected $jobTitle;

    /**
     * @var string
     *
     * @ORM\Column(name="bank_account_number", type="string", length=50)
     * @Assert\NotBlank(message="The Bank account can not be blank.", groups={"employee"})
     */
    protected $bankAccountNumber;

    /**
     * @var string
     *
     * @ORM\Column(name="bank_name", type="string", length=30)
     * @Assert\NotBlank(message="The bank name can not be blank.", groups={"employee"})
     * @Assert\Length(
     *      max = "34",
     *      maxMessage = "The bank name must be less equal {{ limit }} characters.",
     *      groups={"employee"}
     * )
     */
    protected $bankName;

    /**
     * @var integer
     *
     * @ORM\Column(name="tax_identification", type="bigint", nullable=true)
     * @Assert\NotBlank(message="The tax identification can not be blank.", groups={"employee"})
     * @Assert\Type(type="integer", message="The value {{ value }} is not a valid {{ type }}.", groups={"employee"})
     */
    protected $taxIdentification;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_of_birth", type="date")
     * @Assert\NotBlank(message="Date of birth can not be empty.", groups={"employee"})
     * @Assert\Date()
     */
    protected $dateOfBirth;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="joining_date", type="date")
     * @Assert\NotBlank(message="Joining date can not be empty.", groups={"employee"})
     * @Assert\Date()
     */
    protected $joiningDate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="leaving_date", type="date", nullable=true)
     * @Assert\Date()
     */
    protected $leavingDate;

    /**
     * @var integer
     *
     * @ORM\Column(name="number_of_children", type="integer")
     * @Assert\NotBlank(message="Number of Children can not be empty.", groups={"employee"})
     * @Assert\Range(
     *      min = "0",
     *      max = "30",
     *      minMessage = "The number of children should be greater or equal 0.",
     *      maxMessage = "The number of children should be less or equal 30.",
     *      groups={"employee"}
     * )
     */
    protected $numberOfChildren;

    /**
     * @var integer
     *
     * @ORM\Column(name="working_hours", type="integer")
     * @Assert\NotBlank(message="Working hours can not be empty.", groups={"employee"})
     * @Assert\Range(
     *      min = "0",
     *      max = "24",
     *      minMessage = "The working hours should be greater or equal 0.",
     *      maxMessage = "The working hours should be less than 24.",
     *      groups={"employee"}
     * )
     */
    protected $workingHours;

    /**
     * @ORM\Column(name="entitled_leaves", type="integer", nullable=true)
     * @Assert\Range(
     *      min = "0",
     *      minMessage = "The entitled leave days should be greater than 0.",
     *      groups={"employee"}
     * )
     */
    protected $entitledLeaves;

    /**
     * @ORM\ManyToMany(targetEntity="Team", inversedBy="employees")
     * @ORM\JoinTable(name="opithrm_employees_teams")
     */
    protected $teams;

    /**
     * Employee leave requests
     *
     * @ORM\OneToMany(targetEntity="\Opit\OpitHrm\LeaveBundle\Entity\LeaveRequest", mappedBy="employee")
     * @Assert\Valid
     */
    protected $leaveRequests;

    /**
     * @ORM\Column(type="string", length=25)
     * @Assert\NotBlank(message="The employee name can not be blank.", groups={"employee"})
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
     * Set leavingDate
     *
     * @param \DateTime $leavingDate
     * @return Employee
     */
    public function setLeavingDate($leavingDate)
    {
        $this->leavingDate = $leavingDate;

        return $this;
    }

    /**
     * Get leavingDate
     *
     * @return \DateTime
     */
    public function getLeavingDate()
    {
        return $this->leavingDate;
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
     * @param \Opit\OpitHrm\UserBundle\Entity\Team $teams
     * @return Employee
     */
    public function addTeam(\Opit\OpitHrm\UserBundle\Entity\Team $teams)
    {
        $this->teams[] = $teams;

        return $this;
    }

    /**
     * Remove teams
     *
     * @param \Opit\OpitHrm\UserBundle\Entity\Team $teams
     */
    public function removeTeam(\Opit\OpitHrm\UserBundle\Entity\Team $teams)
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
     * @param \Opit\OpitHrm\LeaveBundle\Entity\LeaveRequest $leaveRequests
     * @return Employee
     */
    public function addLeaveRequest(\Opit\OpitHrm\LeaveBundle\Entity\LeaveRequest $leaveRequests)
    {
        $this->leaveRequests[] = $leaveRequests;
        $leaveRequests->setEmployee($this); // synchronously updating inverse side

        return $this;
    }

    /**
     * Remove leaveRequests
     *
     * @param \Opit\OpitHrm\LeaveBundle\Entity\LeaveRequest $leaveRequests
     */
    public function removeLeaveRequest(\Opit\OpitHrm\LeaveBundle\Entity\LeaveRequest $leaveRequests)
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

    /**
     * Set bankAccountNumber
     *
     * @param string $bankAccountNumber
     * @return Employee
     */
    public function setBankAccountNumber($bankAccountNumber)
    {
        $this->bankAccountNumber = $bankAccountNumber;

        return $this;
    }

    /**
     * Get bankAccountNumber
     *
     * @return string
     */
    public function getBankAccountNumber()
    {
        return $this->bankAccountNumber;
    }

    /**
     * Set bankName
     *
     * @param string $bankName
     * @return Employee
     */
    public function setBankName($bankName)
    {
        $this->bankName = $bankName;

        return $this;
    }

    /**
     * Get bankName
     *
     * @return string
     */
    public function getBankName()
    {
        return $this->bankName;
    }

    /**
     * Set taxIdentification
     *
     * @param integer $taxIdentification
     * @return Employee
     */
    public function setTaxIdentification($taxIdentification)
    {
        $this->taxIdentification = $taxIdentification;

        return $this;
    }

    /**
     * Get taxIdentification
     *
     * @return integer
     */
    public function getTaxIdentification()
    {
        return $this->taxIdentification;
    }

    /**
     * Set entitledLeaves
     *
     * @param integer $entitledLeaves
     * @return Employee
     */
    public function setEntitledLeaves($entitledLeaves)
    {
        $this->entitledLeaves = $entitledLeaves;

        return $this;
    }

    /**
     * Get entitledLeaves
     *
     * @return integer
     */
    public function getEntitledLeaves()
    {
        return $this->entitledLeaves;
    }

    /**
     * Set jobTitle
     *
     * @param \Opit\OpitHrm\UserBundle\Entity\JobTitle $jobTitle
     * @return Employee
     */
    public function setJobTitle(\Opit\OpitHrm\UserBundle\Entity\JobTitle $jobTitle = null)
    {
        $this->jobTitle = $jobTitle;

        return $this;
    }

    /**
     * Get jobTitle
     *
     * @return \Opit\OpitHrm\UserBundle\Entity\JobTitle
     */
    public function getJobTitle()
    {
        return $this->jobTitle;
    }

    /**
     * @Assert\Callback(groups={"employee"})
     */
    public function validateLeavingDate(ExecutionContextInterface $context)
    {
        if ($this->getLeavingDate() < $this->getJoiningDate()) {
            $context->addViolation(
                sprintf('Leaving date can not be before joining date')
            );
        }
    }
}
