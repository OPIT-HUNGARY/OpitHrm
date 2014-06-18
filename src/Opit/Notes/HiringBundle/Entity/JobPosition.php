<?php

namespace Opit\Notes\HiringBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Opit\Notes\CoreBundle\Entity\AbstractBase;

/**
 * JobPosition
 *
 * @ORM\Table(name="notes_job_position")
 * @ORM\Entity()
 */
class JobPosition extends AbstractBase
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
     * @var text
     * @ORM\Column(name="job_position_id", type="string", length=11, nullable=true)
     */
    protected $jobPositionId;
    
    /**
     * @var \Text
     *
     * @ORM\Column(name="job_title", type="text")
     */
    protected $jobTitle;
    
    /**
     * @var integer
     *
     * @ORM\Column(name="number_of_positions", type="integer")
     */
    protected $numberOfPositions;
    
    /**
     * @ORM\ManyToOne(targetEntity="Opit\Notes\UserBundle\Entity\User", inversedBy="hmJobPositions")
     */
    protected $hiringManager;
    
    /**
     * @var \Text
     *
     * @ORM\Column(name="description", type="text")
     */
    protected $description;
    
    /**
     * @var boolean
     *
     * @ORM\Column(name="is_active", type="boolean")
     */
    protected $isActive;
    
    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->isActive = false;
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
     * Set numberOfPositions
     *
     * @param integer $numberOfPositions
     * @return JobPosition
     */
    public function setNumberOfPositions($numberOfPositions)
    {
        $this->numberOfPositions = $numberOfPositions;

        return $this;
    }

    /**
     * Get numberOfPositions
     *
     * @return integer 
     */
    public function getNumberOfPositions()
    {
        return $this->numberOfPositions;
    }

    /**
     * Set description
     *
     * @param string $description
     * @return JobPosition
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
     * Set job title
     *
     * @param string $jobTitle
     * @return JobPosition
     */
    public function setJobTitle($jobTitle)
    {
        $this->jobTitle = $jobTitle;

        return $this;
    }

    /**
     * Get description
     *
     * @return string 
     */
    public function getJobTitle()
    {
        return $this->jobTitle;
    }

    /**
     * Set isActive
     *
     * @param boolean $isActive
     * @return JobPosition
     */
    public function setIsActive($isActive)
    {
        $this->isActive = $isActive;

        return $this;
    }

    /**
     * Get isActive
     *
     * @return boolean 
     */
    public function getIsActive()
    {
        return $this->isActive;
    }

    /**
     * Set hiringManager
     *
     * @param \Opit\Notes\UserBundle\Entity\User $hiringManager
     * @return JobPosition
     */
    public function setHiringManager(\Opit\Notes\UserBundle\Entity\User $hiringManager = null)
    {
        $this->hiringManager = $hiringManager;

        return $this;
    }

    /**
     * Get hiringManager
     *
     * @return \Opit\Notes\UserBundle\Entity\User 
     */
    public function getHiringManager()
    {
        return $this->hiringManager;
    }

    /**
     * Set jobPositionId
     *
     * @param string $jobPositionId
     * @return JobPosition
     */
    public function setJobPositionId($jobPositionId)
    {
        $this->jobPositionId = $jobPositionId;

        return $this;
    }

    /**
     * Get jobPositionId
     *
     * @return string 
     */
    public function getJobPositionId()
    {
        return $this->jobPositionId;
    }
}
