<?php

namespace Opit\Notes\LeaveBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * LeaveCategory
 *
 * @ORM\Table(name="notes_leave_categories")
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false)
 * @ORM\Entity
 */
class LeaveCategory
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
     * @ORM\Column(name="deletedAt", type="datetime", nullable=true)
     */
    private $deletedAt;
    
    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=50)
     * @Assert\NotBlank(message="The name may not be blank.")
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="string", length=255)
     * @Assert\NotBlank(message="The description may not be blank.")
     */
    private $description;
    
    /**
     * @ORM\OneToMany(targetEntity="LeaveRequest", mappedBy="category", cascade={"persist"})
     */
    protected $requests;
    
    /**
     * 
     * @var integer
     *
     * @ORM\ManyToOne(targetEntity="\Opit\Notes\LeaveBundle\Entity\LeaveCategoryDuration")
     * @ORM\JoinColumn(referencedColumnName="id")
     */
    protected $leaveCategoryDuration;
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->requests = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Add requests
     *
     * @param \Opit\Notes\LeaveBundle\Entity\LeaveRequest $requests
     * @return LeaveCategory
     */
    public function addRequest(\Opit\Notes\LeaveBundle\Entity\LeaveRequest $requests)
    {
        $this->requests[] = $requests;

        return $this;
    }

    /**
     * Remove requests
     *
     * @param \Opit\Notes\LeaveBundle\Entity\LeaveRequest $requests
     */
    public function removeRequest(\Opit\Notes\LeaveBundle\Entity\LeaveRequest $requests)
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

    /**
     * Set leaveCategoryDuration
     *
     * @param \Opit\Notes\LeaveBundle\Entity\LeaveCategoryDuration $leaveCategoryDuration
     * @return LeaveCategory
     */
    public function setLeaveCategoryDuration(\Opit\Notes\LeaveBundle\Entity\LeaveCategoryDuration $leaveCategoryDuration = null)
    {
        $this->leaveCategoryDuration = $leaveCategoryDuration;

        return $this;
    }

    /**
     * Get leaveCategoryDuration
     *
     * @return \Opit\Notes\LeaveBundle\Entity\LeaveCategoryDuration 
     */
    public function getLeaveCategoryDuration()
    {
        return $this->leaveCategoryDuration;
    }
}
