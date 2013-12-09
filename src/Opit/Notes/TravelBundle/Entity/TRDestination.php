<?php

namespace Opit\Notes\TravelBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * TRDestination
 *
 * @ORM\Table(name="notes_tr_destination")
 * @ORM\Entity(repositoryClass="Opit\Notes\TravelBundle\Entity\TRDestinationRepository")
 */
class TRDestination
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
     * @var integer
     *
     * @ORM\Column(name="cost", type="integer")
     */
    private $cost;

    /**
     * @ORM\ManyToOne(targetEntity="TravelRequest", inversedBy="destinations")
     */
    protected $travelRequest;
    
    /**
     * @ORM\ManyToOne(targetEntity="TransportationType", inversedBy="destinations")
     */
    private $transportationType;

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
     * Set cost
     *
     * @param integer $cost
     * @return TRDestination
     */
    public function setCost($cost)
    {
        $this->cost = $cost;
    
        return $this;
    }

    /**
     * Get cost
     *
     * @return integer
     */
    public function getCost()
    {
        return $this->cost;
    }

    /**
     * Set travelRequest
     *
     * @param \Opit\Notes\TravelBundle\Entity\TravelRequest $travelRequest
     * @return TRDestination
     */
    public function setTravelRequest(\Opit\Notes\TravelBundle\Entity\TravelRequest $travelRequest = null)
    {
        $this->travelRequest = $travelRequest;
    
        return $this;
    }

    /**
     * Get travelRequest
     *
     * @return \Opit\Notes\TravelBundle\Entity\TravelRequest
     */
    public function getTravelRequest()
    {
        return $this->travelRequest;
    }

    /**
     * Set name
     *
     * @param string $name
     * @return TRDestination
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
     * Set transportationType
     *
     * @param \Opit\Notes\TravelBundle\Entity\TransportationType $transportationType
     * @return TRDestination
     */
    public function setTransportationType(\Opit\Notes\TravelBundle\Entity\TransportationType $transportationType = null)
    {
        $this->transportationType = $transportationType;
    
        return $this;
    }

    /**
     * Get transportationType
     *
     * @return \Opit\Notes\TravelBundle\Entity\TransportationType
     */
    public function getTransportationType()
    {
        return $this->transportationType;
    }
}