<?php

namespace Opit\Notes\TravelBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use \Doctrine\Common\Collections\ArrayCollection;

/**
 * TransPortationTypes
 *
 * @ORM\Table(name="notes_transportation_type")
 * @ORM\Entity(repositoryClass="Opit\Notes\TravelBundle\Entity\TransportationTypeRepository")
 */
class TransportationType
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
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * @ORM\OneToMany(targetEntity="TRDestination", mappedBy="transportationType", cascade={"persist"})
     */
    protected $destinations;
    
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->destinations = new ArrayCollection();
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
     * @return TransportationType
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
     * Add destinations
     *
     * @param \Opit\Notes\TravelBundle\Entity\TRDestination $destinations
     * @return TransportationType
     */
    public function addDestination(\Opit\Notes\TravelBundle\Entity\TRDestination $destinations)
    {
        $this->destinations[] = $destinations;
    
        return $this;
    }

    /**
     * Remove destinations
     *
     * @param \Opit\Notes\TravelBundle\Entity\TRDestination $destinations
     */
    public function removeDestination(\Opit\Notes\TravelBundle\Entity\TRDestination $destinations)
    {
        $this->destinations->removeElement($destinations);
    }

    /**
     * Get destinations
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getDestinations()
    {
        return $this->destinations;
    }
}