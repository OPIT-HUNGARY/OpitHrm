<?php

namespace Opit\OpitHrm\LeaveBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Gedmo\Mapping\Annotation as Gedmo;
use Opit\OpitHrm\CoreBundle\Entity\AbstractBase;

/**
 * LeaveCategory
 *
 * @ORM\Table(name="opithrm_leave_categories")
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false)
 * @ORM\Entity(repositoryClass="Opit\OpitHrm\LeaveBundle\Entity\LeaveCategoryRepository")
 */
class LeaveCategory extends AbstractBase
{
    const FULL_DAY = 'Full day';
    const UNPAID = 'Unpaid leave';

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(name="deletedAt", type="datetime", nullable=true)
     */
    private $deletedAt;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=50)
     * @Assert\NotBlank(message="The name can not be blank.")
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="string", length=255)
     * @Assert\NotBlank(message="The description can not be blank.")
     */
    private $description;

    /**
     * @ORM\OneToMany(targetEntity="Leave", mappedBy="category", cascade={"persist"})
     */
    protected $requests;

    /**
     *
     * @var integer
     *
     * @ORM\ManyToOne(targetEntity="\Opit\OpitHrm\LeaveBundle\Entity\LeaveCategoryDuration")
     * @ORM\JoinColumn(referencedColumnName="id")
     */
    protected $leaveCategoryDuration;

    /**
     * @var boolean
     *
     * @ORM\Column(name="is_paid", type="boolean")
     */
    protected $isPaid;

    /**
     * @var boolean
     *
     * @ORM\Column(name="is_counted_as_leave", type="boolean")
     */
    protected $isCountedAsLeave;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->requests = new \Doctrine\Common\Collections\ArrayCollection();

        parent::__construct();
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
     * @return LeaveCategory
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
     * Set name
     *
     * @param string $name
     * @return LeaveCategory
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
     * Set description
     *
     * @param string $description
     * @return LeaveCategory
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set leaveCategoryDuration
     *
     * @param \Opit\OpitHrm\LeaveBundle\Entity\LeaveCategoryDuration $leaveCategoryDuration
     * @return LeaveCategory
     */
    public function setLeaveCategoryDuration(\Opit\OpitHrm\LeaveBundle\Entity\LeaveCategoryDuration $leaveCategoryDuration = null)
    {
        $this->leaveCategoryDuration = $leaveCategoryDuration;

        return $this;
    }

    /**
     * Get leaveCategoryDuration
     *
     * @return \Opit\OpitHrm\LeaveBundle\Entity\LeaveCategoryDuration
     */
    public function getLeaveCategoryDuration()
    {
        return $this->leaveCategoryDuration;
    }

    /**
     * Set isPaid
     *
     * @param boolean $isPaid
     * @return LeaveCategory
     */
    public function setIsPaid($isPaid)
    {
        $this->isPaid = $isPaid;

        return $this;
    }

    /**
     * Get isPaid
     *
     * @return boolean
     */
    public function getIsPaid()
    {
        return $this->isPaid;
    }

    /**
     * Set isCountedAsLeave
     *
     * @param boolean $isCountedAsLeave
     * @return LeaveCategory
     */
    public function setIsCountedAsLeave($isCountedAsLeave)
    {
        $this->isCountedAsLeave = $isCountedAsLeave;

        return $this;
    }

    /**
     * Get isCountedAsLeave
     *
     * @return boolean
     */
    public function getIsCountedAsLeave()
    {
        return $this->isCountedAsLeave;
    }

    /**
     * Add requests
     *
     * @param \Opit\OpitHrm\LeaveBundle\Entity\Leave $requests
     * @return LeaveCategory
     */
    public function addRequest(\Opit\OpitHrm\LeaveBundle\Entity\Leave $requests)
    {
        $this->requests[] = $requests;

        return $this;
    }

    /**
     * Remove requests
     *
     * @param \Opit\OpitHrm\LeaveBundle\Entity\Leave $requests
     */
    public function removeRequest(\Opit\OpitHrm\LeaveBundle\Entity\Leave $requests)
    {
        $this->requests->removeElement($requests);
    }

    /**
     * Get requests
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getRequests()
    {
        return $this->requests;
    }
}
