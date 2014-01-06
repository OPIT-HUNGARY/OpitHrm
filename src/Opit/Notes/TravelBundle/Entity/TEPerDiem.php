<?php

namespace Opit\Notes\TravelBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * TEPerDiem
 *
 * @ORM\Table(name="notes_te_per_diem")
 * @ORM\Entity
 */
class TEPerDiem
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
     * @var float
     *
     * @ORM\Column(name="hours", type="float")
     */
    private $hours;

    /**
     * @var float
     *
     * @ORM\Column(name="ammount", type="float")
     */
    private $ammount;


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
     * Set hours
     *
     * @param float $hours
     * @return TEPerDiem
     */
    public function setHours($hours)
    {
        $this->hours = $hours;
    
        return $this;
    }

    /**
     * Get hours
     *
     * @return float
     */
    public function getHours()
    {
        return $this->hours;
    }

    /**
     * Set ammount
     *
     * @param float $ammount
     * @return TEPerDiem
     */
    public function setAmmount($ammount)
    {
        $this->ammount = $ammount;
    
        return $this;
    }

    /**
     * Get ammount
     *
     * @return float
     */
    public function getAmmount()
    {
        return $this->ammount;
    }
}
