<?php

namespace Opit\Notes\UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Employee
 *
 * @ORM\Table()
 * @ORM\Entity
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
    private $id; 
    
    /**
     * @var \DateTime
     *
     * @ORM\Column(name="dateOfBirth", type="date")
     */
    private $dateOfBirth;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="joiningDate", type="date")
     */
    private $joiningDate;

    /**
     * @var integer
     *
     * @ORM\Column(name="numberOfKids", type="smallint")
     */
    private $numberOfKids;


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
     * Set numberOfKids
     *
     * @param integer $numberOfKids
     * @return Employee
     */
    public function setNumberOfKids($numberOfKids)
    {
        $this->numberOfKids = $numberOfKids;

        return $this;
    }

    /**
     * Get numberOfKids
     *
     * @return integer 
     */
    public function getNumberOfKids()
    {
        return $this->numberOfKids;
    }
}
