<?php

/*
 *  This file is part of the OPIT-HRM project.
 *
 *  (c) Opit Consulting Kft. <info@opit.hu>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace Opit\OpitHrm\HiringBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Opit\OpitHrm\CoreBundle\Entity\CommonType;
use Opit\OpitHrm\HiringBundle\Entity\JobPosition;

/**
 * Location
 *
 * @ORM\Entity
 */
class Location extends CommonType
{

    /**
     * @var integer
     */
    protected $id;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $description;

    /**
     * @ORM\OneToMany(targetEntity="JobPosition", mappedBy="location", cascade={"persist"})
     */
    protected $jobPositions;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->jobPositions = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Set name
     *
     * @param string $name
     * @return Location
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
     * @return Location
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
     * Add jobPositions
     *
     * @param JobPosition $jobPositions
     * @return Location
     */
    public function addJobPosition(JobPosition $jobPositions)
    {
        $this->jobPositions[] = $jobPositions;

        return $this;
    }

    /**
     * Remove jobPositions
     *
     * @param JobPosition $jobPositions
     */
    public function removeJobPosition(JobPosition $jobPositions)
    {
        $this->jobPositions->removeElement($jobPositions);
    }

    /**
     * Get jobPositions
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getJobPositions()
    {
        return $this->jobPositions;
    }
}
