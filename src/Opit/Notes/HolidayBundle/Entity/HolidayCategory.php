<?php

namespace Opit\Notes\HolidayBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * HolidayCategory
 *
 * @ORM\Table(name="notes_holiday_categories")
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false)
 * @ORM\Entity
 */
class HolidayCategory
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
     * @return HolidayCategory
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
     * @return HolidayCategory
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
     * @return HolidayCategory
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
     * @param \Opit\Notes\HolidayBundle\Entity\LeaveRequest $requests
     * @return HolidayCategory
     */
    public function addRequest(\Opit\Notes\HolidayBundle\Entity\LeaveRequest $requests)
    {
        $this->requests[] = $requests;

        return $this;
    }

    /**
     * Remove requests
     *
     * @param \Opit\Notes\HolidayBundle\Entity\LeaveRequest $requests
     */
    public function removeRequest(\Opit\Notes\HolidayBundle\Entity\LeaveRequest $requests)
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
