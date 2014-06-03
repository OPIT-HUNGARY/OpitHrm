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
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Security\Core\User\UserInterface;
use \Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Opit\Notes\TravelBundle\Model\TravelRequestUserInterface;
use Opit\Notes\NotificationBundle\Model\NotificationUserInterface;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Description of User
 * Custom user entity to validata against a database
 *
 * @author OPIT Consulting Kft. - PHP Team - {@link http://www.opit.hu}
 * @version 1.0
 * @package Notes
 * @subpackage UserBundle
 *
 * @internal Must be implemented the: User, General Manager and Travel manager requests.
 *
 * @ORM\Table(name="notes_users")
 * @ORM\Entity(repositoryClass="Opit\Notes\UserBundle\Entity\UserRepository")
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false)
 * @UniqueEntity(fields={"username"}, message="The username is already used.", groups={"user"})
 * @UniqueEntity(fields={"email"}, message="The email is already used.", groups={"user"})
 * @UniqueEntity(fields={"taxIdentification"}, message="The tax id is already used.", groups={"user"})
 */
class User implements UserInterface, \Serializable, TravelRequestUserInterface, NotificationUserInterface
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(name="deletedAt", type="datetime", nullable=true)
     */
    private $deletedAt;

    /**
     * @ORM\Column(type="string", length=25)
     * @Assert\NotBlank(message="The username may not be blank.", groups={"user"})
     */
    protected $username;

    /**
     * @ORM\OneToOne(targetEntity="Employee", inversedBy="user", cascade={"persist", "remove"})
     * @ORM\JoinColumn(name="employee_id", referencedColumnName="id")
     * @Assert\Valid
     */
    protected $employee;

    /**
     * @ORM\Column(type="string", length=32)
     */
    private $salt;

    /**
     * @ORM\Column(type="string", length=64)
     * @Assert\NotBlank(message="The password may not be blank.", groups={"password"})
     * @Assert\Length(
     *      min = "6",
     *      max = "50",
     *      minMessage = "The password must be greater or equal to {{ limit }} characters",
     *      maxMessage = "The password must be less or equal to {{ limit }} characters",
     *      groups={"password"}
     * )
     */
    private $password;

    /**
     * @ORM\Column(type="string", length=60)
     * @Assert\NotBlank(message="The email may not be blank.", groups={"password"})
     * @Assert\Email(message = "The email '{{ value }}' is not a valid email address.", groups={"user"})
     */
    protected $email;

    /**
     * @ORM\Column(name="is_active", type="boolean")
     */
    private $isActive;

    /**
     * @ORM\JoinColumn(name="job_title_id", referencedColumnName="id", nullable=true)
     * @ORM\ManyToOne(targetEntity="JobTitle")
     */
    protected $jobTitle;

    /**
     * @var string
     *
     * @ORM\Column(name="bank_account_number", type="string", length=50)
     * @Assert\NotBlank(message="The Bank account may not be blank.", groups={"user"})
     * @Assert\Length(
     *      min = "16",
     *      max = "34",
     *      minMessage = "The Bank account number must be greater equal {{ limit }} characters",
     *      maxMessage = "The Bank account number must be less equal {{ limit }} characters",
     *      groups={"user"}
     * )
     */
    protected $bankAccountNumber;

    /**
     * @var string
     *
     * @ORM\Column(name="bank_name", type="string", length=30)
     * @Assert\NotBlank(message="The bank name may not be blank.", groups={"user"})
     * @Assert\Length(
     *      max = "34",
     *      maxMessage = "The bank name must be less equal {{ limit }} characters.",
     *      groups={"user"}
     * )
     */
    protected $bankName;

    /**
     * @var integer
     *
     * @ORM\Column(name="tax_identification", type="bigint", nullable=true)
     * @Assert\NotBlank(message="The tax identification can not be blank.", groups={"user"})
     * @Assert\Range(
     *      min = "1000000000",
     *      max = "9999999999",
     *      minMessage = "The tax identification should be greater than 1000000000.",
     *      maxMessage = "The tax identification should be less than 9999999999.",
     *      groups={"user"}
     * )
     */
    protected $taxIdentification;

    /**
     * @ORM\ManyToMany(targetEntity="Groups", inversedBy="users")
     * @ORM\JoinTable(name="notes_users_groups")
     */
    protected $groups;

    /**
     * User travel requests
     * @ORM\OneToMany(targetEntity="\Opit\Notes\TravelBundle\Entity\TravelRequest", mappedBy="user", cascade={"remove"})
     */
    protected $userTravelRequests;

    /**
     * General manager travel requests
     * @ORM\OneToMany(targetEntity="\Opit\Notes\TravelBundle\Entity\TravelRequest", mappedBy="generalManager", cascade={"remove"})
     */
    protected $gmTravelRequests;

    /**
     * Team manager travel requests
     * @ORM\OneToMany(targetEntity="\Opit\Notes\TravelBundle\Entity\TravelRequest", mappedBy="teamManager", cascade={"remove"})
     */
    protected $tmTravelRequests;

    /**
     * General manager leave requests
     * @ORM\OneToMany(targetEntity="\Opit\Notes\LeaveBundle\Entity\LeaveRequest", mappedBy="generalManager", cascade={"remove"})
     */
    protected $gmLeaveRequests;

    /**
     * Team manager leave requests
     * @ORM\OneToMany(targetEntity="\Opit\Notes\LeaveBundle\Entity\LeaveRequest", mappedBy="teamManager", cascade={"remove"})
     */
    protected $tmLeaveRequests;

    /**
     * Notifications sent by user
     * @ORM\OneToMany(targetEntity="\Opit\Notes\NotificationBundle\Entity\Notification", mappedBy="receiver", cascade={"remove"})
     */
    protected $notifications;

    /**
     * User travel expenses
     * @ORM\OneToMany(targetEntity="\Opit\Notes\TravelBundle\Entity\TravelExpense", mappedBy="user", cascade={"remove"})
     */
    protected $userTravelExpenses;

    /**
     * @ORM\Column(name="is_first_login", type="boolean")
     */
    protected $isFirstLogin;

    /**
     * @ORM\Column(name="entitled_leaves", type="integer", nullable=true)
     * @Assert\Range(
     *      min = "0",
     *      minMessage = "The entitled leave days should be greater than 0.",
     *      groups={"user"}
     * )
     */
    protected $entitledLeaves;

    /**
     * @ORM\Column(type="boolean", options={"default":false})
     */
    protected $ldapEnabled;

    public function __construct()
    {
        //$this->salt = md5(uniqid(null, true));
        $this->isActive = true;
        $this->groups = new ArrayCollection();
        $this->userTravelRequests = new ArrayCollection();
        $this->gmTravelRequests = new ArrayCollection();
        $this->tmTravelRequests = new ArrayCollection();
        $this->gmLeaveRequests = new ArrayCollection();
        $this->userTravelExpenses = new ArrayCollection();
        $this->setSalt("");
        // Set ldap required to handle the default values mapped to not null properties.
        $this->setLdapEnabled(false);
    }

    /**
     * @inheritDoc
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * @inheritDoc
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @inheritDoc
     */
    public function getStatus()
    {
        return $this->isActive;
    }

    /**
     * @inheritDoc
     */
    public function getIsActive()
    {
        return $this->isActive;
    }

    /**
     * @inheritDoc
     */
    public function getJobTitle()
    {
        return $this->jobTitle;
    }

    /**
     * @inheritDoc
     */
    public function getSalt()
    {
        return $this->salt;
    }

    /**
     * @inheritDoc
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @inheritDoc
     */
    public function getRoles()
    {
        return $this->groups->toArray();
    }

    /**
     * @inheritDoc
     */
    public function eraseCredentials()
    {
    }

    /**
     * @see \Serializable::serialize()
     */
    public function serialize()
    {
        return serialize(array(
            $this->id,
        ));
    }

    /**
     * @see \Serializable::unserialize()
     */
    public function unserialize($serialized)
    {
        list (
            $this->id,
        ) = unserialize($serialized);
    }

    /**
     * Set username
     *
     * @param  string $username
     * @return User
     */
    public function setUsername($username)
    {
        $this->username = $username;

        return $this;
    }

    /**
     * Set salt
     *
     * @param  string $salt
     * @return User
     */
    public function setSalt($salt)
    {
        $this->salt = $salt;

        return $this;
    }

    /**
     * Set job
     *
     * @param  string $job
     * @return User
     */
    public function setJobTitle($jobTitle)
    {
        $this->jobTitle = $jobTitle;

        return $this;
    }

    /**
     * Set password
     *
     * @param  string $password
     * @return User
     */
    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Set isActive
     *
     * @param  boolean $isActive
     * @return User
     */
    public function setIsActive($isActive)
    {
        $this->isActive = $isActive;

        return $this;
    }

    /**
     * Set email
     *
     * @param  string $email
     * @return User
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Set email
     *
     * @param  string $email
     * @return User
     */
    public function setRoles($role)
    {
        $this->groups[] = $role;

        return $this;
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
     * Add groups
     *
     * @param  \Opit\Notes\UserBundle\Entity\Groups $groups
     * @return User
     */
    public function addGroup(\Opit\Notes\UserBundle\Entity\Groups $groups)
    {
        $this->groups[] = $groups;

        return $this;
    }

    /**
     * Remove groups
     *
     * @param \Opit\Notes\UserBundle\Entity\Groups $groups
     */
    public function removeGroup(\Opit\Notes\UserBundle\Entity\Groups $groups)
    {
        $this->groups->removeElement($groups);
    }

    /**
     * Get groups
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getGroups()
    {
        return $this->groups;
    }

    /**
     * Get deletedAt
     *
     * @return datetime
     */
    public function getDeletedAt()
    {
        return $this->deletedAt;
    }

    /**
     * Set deletedAt
     *
     * @param datetime $deletedAt
     */
    public function setDeletedAt($deletedAt)
    {
        $this->deletedAt = $deletedAt;
    }

    /**
     * Set bankAccountNumber
     *
     * @param  string $bankAccountNumber
     * @return User
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
     * @param  string $bankName
     * @return User
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
     * @param  integer $taxIdentification
     * @return User
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
     * Set firstLogin
     *
     * @param  boolean $firstLogin
     * @return User
     */
    public function setIsFirstLogin($isFirstLogin)
    {
        $this->isFirstLogin = $isFirstLogin;

        return $this;
    }

    /**
     * Get firstLogin
     *
     * @return boolean
     */
    public function getIsFirstLogin()
    {
        return $this->isFirstLogin;
    }

    public function setEmployee($employee)
    {
        $this->employee = $employee;

        return $this;
    }

    public function getEmployee()
    {
        return $this->employee;
    }

    /**
     * Set ldapEnabled
     *
     * @param  boolean $ldapEnabled
     * @return User
     */
    public function setLdapEnabled($ldapEnabled)
    {
        $this->ldapEnabled = $ldapEnabled;

        return $this;
    }

    /**
     * Get ldapEnabled
     *
     * @return boolean
     */
    public function isLdapEnabled()
    {
        return $this->ldapEnabled;
    }

    /**
     * Set entitledLeaves
     *
     * @param integer $entitledLeaves
     * @return User
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
}
